<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\MarketplaceTransaction;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MidtransService
{
    protected string $serverKey;
    protected string $baseUrl;
    protected bool   $isProduction;

    public function __construct()
    {
        $this->serverKey    = config('midtrans.server_key');
        $this->baseUrl      = config('midtrans.base_url');
        $this->isProduction = config('midtrans.is_production');
    }

    // =========================================================================
    // SNAP TOKEN — BOOKING HUNIAN & EVENT
    // =========================================================================

    /**
     * Buat Snap token untuk pembayaran booking hunian/event.
     *
     * @throws \Exception jika Midtrans mengembalikan error
     */
    public function createSnapTokenForBooking(Booking $booking): string
    {
        $transaction = $booking->transaction;

        if (! $transaction) {
            throw new \Exception('Transaction untuk booking ini tidak ditemukan.');
        }

        $user    = $booking->user;
        $bookable = $booking->bookable;

        $payload = [
            'transaction_details' => [
                'order_id'     => $transaction->transaction_code,
                'gross_amount' => (int) $transaction->final_amount,
            ],
            'customer_details' => [
                'first_name' => $user->name,
                'email'      => $user->email,
                'phone'      => $user->phone ?? '',
            ],
            'item_details' => [
                [
                    'id'       => (string) $bookable->id,
                    'price'    => (int) $transaction->final_amount,
                    'quantity' => 1,
                    'name'     => $this->truncate('Booking: ' . ($bookable->name ?? 'Item'), 50),
                ],
            ],
            'expiry' => [
                'start_time' => now()->format('Y-m-d H:i:s O'),
                'unit'       => 'hour',
                'duration'   => 1,
            ],
        ];

        return $this->requestSnapToken($payload);
    }

    // =========================================================================
    // SNAP TOKEN — MARKETPLACE TRANSACTION
    // =========================================================================

    /**
     * Buat Snap token untuk pembayaran transaksi marketplace.
     *
     * Khusus COD: tidak perlu dipanggil — buyer bayar langsung saat menerima.
     *
     * @throws \Exception jika pickup_method adalah COD atau Midtrans error
     */
    public function createSnapTokenForMarketplace(MarketplaceTransaction $transaction): string
    {
        if ($transaction->pickup_method === 'cod') {
            throw new \Exception('Transaksi COD tidak memerlukan pembayaran online.');
        }

        $buyer   = $transaction->buyer;
        $product = $transaction->product;

        $payload = [
            'transaction_details' => [
                'order_id'     => $transaction->transaction_code,
                'gross_amount' => (int) $transaction->total_amount,
            ],
            'customer_details' => [
                'first_name' => $transaction->buyer_name ?? $buyer->name,
                'email'      => $buyer->email,
                'phone'      => $transaction->buyer_phone ?? $buyer->phone ?? '',
            ],
            'item_details' => [
                [
                    'id'       => (string) $product->id,
                    'price'    => (int) $transaction->unit_price,
                    'quantity' => (int) $transaction->quantity,
                    'name'     => $this->truncate($product->name, 50),
                ],
            ],
            'expiry' => [
                'start_time' => now()->format('Y-m-d H:i:s O'),
                'unit'       => 'hour',
                'duration'   => 1,
            ],
        ];

        return $this->requestSnapToken($payload);
    }

    // =========================================================================
    // HTTP REQUEST KE MIDTRANS SNAP API
    // =========================================================================

    /**
     * POST ke Midtrans Snap API dan kembalikan token.
     *
     * @throws \Exception
     */
    protected function requestSnapToken(array $payload): string
    {
        $snapUrl = $this->baseUrl . '/snap/v1/transactions';

        Log::info('MidtransService: Requesting snap token', [
            'url'        => $snapUrl,
            'server_key' => substr($this->serverKey, 0, 15) . '...',
            'order_id'   => $payload['transaction_details']['order_id'] ?? null,
        ]);

        $response = Http::withBasicAuth($this->serverKey, '')
            ->acceptJson()
            ->post($snapUrl, $payload);

        Log::info('MidtransService: Response', [
            'status' => $response->status(),
            'body'   => $response->json(),
        ]);

        if ($response->failed()) {
            $error = $response->json('error_messages', ['Midtrans API error']);
            $msg   = is_array($error) ? implode(', ', $error) : $error;

            Log::error('MidtransService: Snap token request failed', [
                'status'  => $response->status(),
                'message' => $msg,
                'payload' => $payload,
            ]);

            throw new \Exception('Gagal menghubungi payment gateway: ' . $msg);
        }

        $token = $response->json('token');

        if (! $token) {
            throw new \Exception('Midtrans tidak mengembalikan token yang valid.');
        }

        return $token;
    }

    // =========================================================================
    // VERIFIKASI SIGNATURE WEBHOOK
    // =========================================================================

    /**
     * Verifikasi bahwa notifikasi webhook benar-benar dari Midtrans.
     *
     * Signature: SHA512( order_id + status_code + gross_amount + server_key )
     */
    public function verifySignature(array $payload): bool
    {
        $orderId     = $payload['order_id']     ?? '';
        $statusCode  = $payload['status_code']  ?? '';
        $grossAmount = $payload['gross_amount'] ?? '';

        $expected = hash('sha512', $orderId . $statusCode . $grossAmount . $this->serverKey);
        $received = $payload['signature_key'] ?? '';

        return hash_equals($expected, $received);
    }

    // =========================================================================
    // PARSE STATUS DARI PAYLOAD WEBHOOK
    // =========================================================================

    /**
     * Tentukan apakah pembayaran dianggap berhasil berdasarkan payload Midtrans.
     *
     * Status yang dianggap sukses: settlement (transfer/gopay/dll)
     * atau capture dengan fraud_status clear/accept (kartu kredit).
     */
    public function isPaymentSuccess(array $payload): bool
    {
        $transactionStatus = $payload['transaction_status'] ?? '';
        $fraudStatus       = $payload['fraud_status']       ?? '';

        if ($transactionStatus === 'settlement') {
            return true;
        }

        if ($transactionStatus === 'capture' && in_array($fraudStatus, ['accept', 'challenge'])) {
            return true;
        }

        return false;
    }

    /**
     * Tentukan apakah pembayaran gagal/kadaluarsa.
     */
    public function isPaymentFailed(array $payload): bool
    {
        $status = $payload['transaction_status'] ?? '';
        return in_array($status, ['deny', 'cancel', 'expire', 'failure']);
    }

    // =========================================================================
    // HELPER
    // =========================================================================

    protected function truncate(string $text, int $max): string
    {
        return mb_strlen($text) > $max ? mb_substr($text, 0, $max - 3) . '...' : $text;
    }
}