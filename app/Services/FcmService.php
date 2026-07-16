<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

/**
 * FcmService — Kirim push notification via Firebase Cloud Messaging HTTP v1 API.
 *
 * Menggunakan FCM HTTP v1 API (bukan legacy) karena:
 * - Legacy API sudah deprecated oleh Google per Juni 2024
 * - HTTP v1 lebih aman (OAuth2 token, bukan server key statis)
 *
 * Tidak memerlukan package tambahan — hanya Http client bawaan Laravel.
 */
class FcmService
{
    /**
     * Kirim push notification ke satu device.
     *
     * @param  string  $token   FCM device token milik user
     * @param  string  $title   Judul notifikasi (tampil di status bar)
     * @param  string  $body    Isi pesan notifikasi
     * @param  array   $data    Data tambahan untuk Flutter (type, url, dll)
     * @return bool             True jika berhasil, false jika gagal
     */
    public function send(string $token, string $title, string $body, array $data = []): bool
    {
        $projectId = config('firebase.project_id');

        if (empty($projectId)) {
            Log::warning('[FCM] FIREBASE_PROJECT_ID belum dikonfigurasi di .env');
            return false;
        }

        if (empty(trim($token))) {
            return false;
        }

        try {
            $accessToken = $this->getAccessToken();

            $endpoint = str_replace('{project_id}', $projectId, config('firebase.fcm_endpoint'));

            // FCM hanya menerima nilai string di dalam 'data'
            $stringData = array_map('strval', $data);

            $payload = [
                'message' => [
                    'token'        => $token,
                    'notification' => [
                        'title' => $title,
                        'body'  => $body,
                    ],
                    'data'    => $stringData,
                    'android' => [
                        'priority'     => 'high',
                        'notification' => [
                            'channel_id'   => 'eduliving_default',
                            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                            'sound'        => 'default',
                        ],
                    ],
                    'apns' => [
                        'payload' => [
                            'aps' => [
                                'sound'             => 'default',
                                'content-available' => 1,
                            ],
                        ],
                    ],
                ],
            ];

            $response = Http::withToken($accessToken)
                ->timeout(10)
                ->post($endpoint, $payload);

            if ($response->successful()) {
                return true;
            }

            // Tangani token tidak valid — hapus dari database agar tidak dikirim lagi
            $errorDetails = $response->json('error.details', []);
            $errorCode    = collect($errorDetails)->pluck('errorCode')->first() ?? '';

            if (in_array($errorCode, ['UNREGISTERED', 'INVALID_ARGUMENT'])) {
                Log::info('[FCM] Token tidak valid / tidak terdaftar, dihapus dari database.', [
                    'token_prefix' => substr($token, 0, 20) . '...',
                ]);
                $this->invalidateToken($token);
            } else {
                Log::error('[FCM] Gagal kirim notifikasi.', [
                    'http_status' => $response->status(),
                    'response'    => $response->json(),
                ]);
            }

            return false;

        } catch (\Throwable $e) {
            // Fire-and-forget — log error tapi tidak melempar exception ke caller
            Log::error('[FCM] Exception saat kirim notifikasi.', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile() . ':' . $e->getLine(),
            ]);
            return false;
        }
    }

    /**
     * Kirim push notification ke banyak device sekaligus.
     *
     * @param  string[]  $tokens
     * @return int  Jumlah yang berhasil dikirim
     */
    public function sendToMultiple(array $tokens, string $title, string $body, array $data = []): int
    {
        $count = 0;
        foreach (array_filter($tokens) as $token) {
            if ($this->send($token, $title, $body, $data)) {
                $count++;
            }
        }
        return $count;
    }

    /**
     * Dapatkan OAuth2 access token dari Google menggunakan service account.
     *
     * Token di-cache 55 menit (token aslinya berlaku 60 menit).
     *
     * PENTING: JWT yang dikirim ke Google harus menggunakan Base64URL encoding
     * (RFC 4648), bukan Base64 biasa. Perbedaannya:
     *   - Base64 biasa   : menggunakan +, /, dan padding =
     *   - Base64URL       : menggunakan -, _, tanpa padding
     * Jika salah encoding, Google akan menolak token dengan error 400.
     *
     * @throws \RuntimeException jika credentials tidak ditemukan atau tidak valid
     */
    private function getAccessToken(): string
    {
        return Cache::remember('firebase_access_token', 55 * 60, function () {
            $credentialsPath = config('firebase.credentials');

            if (!file_exists($credentialsPath)) {
                throw new \RuntimeException(
                    "[FCM] File service account tidak ditemukan di: {$credentialsPath}\n" .
                    "Download dari: Firebase Console → Project Settings → Service Accounts → Generate new private key\n" .
                    "Simpan sebagai: storage/app/firebase-credentials.json"
                );
            }

            $credentials = json_decode(file_get_contents($credentialsPath), true);

            if (empty($credentials['private_key']) || empty($credentials['client_email'])) {
                throw new \RuntimeException(
                    '[FCM] File service account tidak valid — pastikan format JSON benar dan berisi private_key & client_email.'
                );
            }

            $now = time();

            // Header JWT
            $header = $this->base64url(json_encode([
                'alg' => 'RS256',
                'typ' => 'JWT',
            ]));

            // Payload JWT
            $payload = $this->base64url(json_encode([
                'iss'   => $credentials['client_email'],
                'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
                'aud'   => config('firebase.token_endpoint'),
                'iat'   => $now,
                'exp'   => $now + 3600,
            ]));

            // Tanda tangani menggunakan private key RS256
            $signingInput = $header . '.' . $payload;
            $signature    = '';
            openssl_sign($signingInput, $signature, $credentials['private_key'], OPENSSL_ALGO_SHA256);

            $jwt = $signingInput . '.' . $this->base64url($signature);

            // Tukar JWT dengan access token dari Google
            $response = Http::asForm()->timeout(10)->post(config('firebase.token_endpoint'), [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion'  => $jwt,
            ]);

            if (!$response->successful()) {
                throw new \RuntimeException(
                    '[FCM] Gagal mendapatkan access token dari Google: ' . $response->body()
                );
            }

            return $response->json('access_token');
        });
    }

    /**
     * Encode string ke Base64URL (RFC 4648) — dibutuhkan untuk JWT.
     *
     * Berbeda dengan base64_encode biasa:
     * - Ganti + dengan -
     * - Ganti / dengan _
     * - Hapus padding =
     */
    private function base64url(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Hapus fcm_token dari database jika token sudah tidak valid / tidak terdaftar.
     * Dipanggil otomatis saat FCM mengembalikan error UNREGISTERED.
     */
    private function invalidateToken(string $token): void
    {
        try {
            \App\Models\User::where('fcm_token', $token)->update(['fcm_token' => null]);
        } catch (\Throwable $e) {
            Log::warning('[FCM] Gagal menghapus token tidak valid.', ['error' => $e->getMessage()]);
        }
    }
}
