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
 * - HTTP v1 mendukung fitur lebih lengkap
 *
 * Tidak memerlukan package tambahan — hanya menggunakan Http client bawaan Laravel
 * dan JWT manual untuk autentikasi service account.
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

        if (empty($token)) {
            return false;
        }

        try {
            $accessToken = $this->getAccessToken();

            $endpoint = str_replace(
                '{project_id}',
                $projectId,
                config('firebase.fcm_endpoint')
            );

            $payload = [
                'message' => [
                    'token'        => $token,
                    'notification' => [
                        'title' => $title,
                        'body'  => $body,
                    ],
                    // Data dikirim ke Flutter — bisa diakses di semua state (foreground/background/terminated)
                    'data' => array_map('strval', $data), // FCM hanya terima string di data
                    'android' => [
                        'priority' => 'high',
                        'notification' => [
                            'channel_id'   => 'eduliving_default',
                            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
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

            // Token tidak valid / expired — hapus dari database
            $responseBody = $response->json();
            $errorCode    = $responseBody['error']['details'][0]['errorCode'] ?? '';

            if (in_array($errorCode, ['UNREGISTERED', 'INVALID_ARGUMENT'])) {
                Log::info('[FCM] Token tidak valid, dihapus dari database.', ['token_prefix' => substr($token, 0, 20)]);
                $this->invalidateToken($token);
            } else {
                Log::error('[FCM] Gagal kirim notifikasi.', [
                    'status'   => $response->status(),
                    'response' => $responseBody,
                ]);
            }

            return false;

        } catch (\Throwable $e) {
            // Fire-and-forget: log error tapi tidak lempar exception ke caller
            Log::error('[FCM] Exception saat kirim notifikasi.', [
                'message' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Kirim push notification ke banyak device sekaligus.
     *
     * Gunakan ini jika perlu broadcast ke banyak user (misal: pengumuman admin).
     *
     * @param  string[]  $tokens  Array FCM device token
     * @param  string    $title
     * @param  string    $body
     * @param  array     $data
     * @return int                Jumlah yang berhasil dikirim
     */
    public function sendToMultiple(array $tokens, string $title, string $body, array $data = []): int
    {
        $successCount = 0;

        foreach (array_filter($tokens) as $token) {
            if ($this->send($token, $title, $body, $data)) {
                $successCount++;
            }
        }

        return $successCount;
    }

    /**
     * Dapatkan OAuth2 access token dari Google menggunakan service account.
     *
     * Token di-cache selama 55 menit (token asli berlaku 60 menit).
     *
     * @throws \RuntimeException jika file credentials tidak ditemukan atau tidak valid
     */
    private function getAccessToken(): string
    {
        return Cache::remember('firebase_access_token', 55 * 60, function () {
            $credentialsPath = config('firebase.credentials');

            if (!file_exists($credentialsPath)) {
                throw new \RuntimeException(
                    "[FCM] File service account tidak ditemukan di: {$credentialsPath}. " .
                    "Download dari Firebase Console → Project Settings → Service Accounts → Generate new private key."
                );
            }

            $credentials = json_decode(file_get_contents($credentialsPath), true);

            if (empty($credentials['private_key']) || empty($credentials['client_email'])) {
                throw new \RuntimeException('[FCM] File service account tidak valid — pastikan format JSON benar.');
            }

            // Buat JWT assertion untuk OAuth2
            $now = time();
            $jwtHeader  = base64_encode(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
            $jwtPayload = base64_encode(json_encode([
                'iss'   => $credentials['client_email'],
                'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
                'aud'   => config('firebase.token_endpoint'),
                'iat'   => $now,
                'exp'   => $now + 3600,
            ]));

            $jwtUnsigned = $jwtHeader . '.' . $jwtPayload;

            // Tanda tangani JWT dengan private key dari service account
            openssl_sign($jwtUnsigned, $signature, $credentials['private_key'], 'SHA256');
            $jwtSigned = $jwtUnsigned . '.' . base64_encode($signature);

            // Tukar JWT dengan access token
            $response = Http::asForm()->post(config('firebase.token_endpoint'), [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion'  => $jwtSigned,
            ]);

            if (!$response->successful()) {
                throw new \RuntimeException('[FCM] Gagal mendapatkan access token: ' . $response->body());
            }

            return $response->json('access_token');
        });
    }

    /**
     * Hapus fcm_token dari database jika token sudah tidak valid.
     *
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
