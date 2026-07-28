<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\MarketplaceTransaction;
use App\Services\MidtransService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MidtransController extends Controller
{
    protected MidtransService $midtrans;

    public function __construct(MidtransService $midtrans)
    {
        $this->midtrans = $midtrans;
    }

    // =========================================================================
    // WEBHOOK — POST /payment/midtrans/callback
    // =========================================================================

    /**
     * Terima notifikasi pembayaran dari Midtrans.
     *
     * Route ini TIDAK menggunakan middleware auth karena dipanggil oleh server Midtrans.
     * Keamanan dijaga via verifikasi signature_key.
     */
    public function callback(Request $request)
    {
        $payload = $request->all();

        Log::info('MidtransController: Webhook received', [
            'order_id' => $payload['order_id'] ?? null,
            'status'   => $payload['transaction_status'] ?? null,
        ]);

        // 1. Verifikasi signature
        if (! $this->midtrans->verifySignature($payload)) {
            Log::warning('MidtransController: Invalid signature', ['order_id' => $payload['order_id'] ?? null]);
            return response()->json(['message' => 'Invalid signature'], 403);
        }

        $orderId = $payload['order_id'] ?? null;
        if (! $orderId) {
            return response()->json(['message' => 'Missing order_id'], 400);
        }

        // 2. Cari di tabel transactions (booking) dulu, lalu marketplace
        $bookingTransaction      = Transaction::where('transaction_code', $orderId)->first();
        $marketplaceTransaction  = MarketplaceTransaction::where('transaction_code', $orderId)->first();

        if ($bookingTransaction) {
            $this->handleBookingPayment($bookingTransaction, $payload);
        } elseif ($marketplaceTransaction) {
            $this->handleMarketplacePayment($marketplaceTransaction, $payload);
        } else {
            Log::warning('MidtransController: Order not found', ['order_id' => $orderId]);
            // Tetap return 200 agar Midtrans tidak retry terus-menerus
            return response()->json(['message' => 'Order not found'], 200);
        }

        return response()->json(['message' => 'OK'], 200);
    }

    // =========================================================================
    // BOOKING PAYMENT HANDLER
    // =========================================================================

    protected function handleBookingPayment(Transaction $transaction, array $payload): void
    {
        $booking = $transaction->booking;

        if (! $booking) {
            Log::error('MidtransController: Booking not found for transaction', ['transaction_id' => $transaction->id]);
            return;
        }

        // Jangan proses ulang yang sudah paid
        if ($transaction->payment_status === 'paid') {
            Log::info('MidtransController: Booking already paid, skip', ['transaction_code' => $transaction->transaction_code]);
            return;
        }

        if ($this->midtrans->isPaymentSuccess($payload)) {
            DB::transaction(function () use ($transaction, $booking, $payload) {
                $transaction->update([
                    'payment_status'        => 'paid',
                    'payment_method'        => $payload['payment_type'] ?? 'midtrans',
                    'midtrans_payment_type' => $payload['payment_type'] ?? null,
                ]);

                // Clear deadline setelah berhasil bayar
                $booking->update(['payment_deadline' => null]);

                // Notifikasi ke user dan provider
                NotificationService::sendBookingNotification($booking, 'payment_received');
            });

            Log::info('MidtransController: Booking payment success', ['order_id' => $payload['order_id']]);

        } elseif ($this->midtrans->isPaymentFailed($payload)) {
            DB::transaction(function () use ($transaction, $payload) {
                $transaction->update([
                    'payment_status' => 'failed',
                ]);
            });

            Log::info('MidtransController: Booking payment failed/expired', ['order_id' => $payload['order_id']]);
        }
    }

    // =========================================================================
    // MARKETPLACE PAYMENT HANDLER
    // =========================================================================

    protected function handleMarketplacePayment(MarketplaceTransaction $transaction, array $payload): void
    {
        // Jangan proses ulang yang sudah paid
        if ($transaction->payment_status === 'paid') {
            Log::info('MidtransController: Marketplace tx already paid, skip', ['transaction_code' => $transaction->transaction_code]);
            return;
        }

        if ($this->midtrans->isPaymentSuccess($payload)) {
            DB::transaction(function () use ($transaction, $payload) {
                $transaction->update([
                    'status'                => 'completed',
                    'completed_at'          => now(),
                    'payment_status'        => 'paid',
                    'payment_method'        => $payload['payment_type'] ?? 'midtrans',
                    'midtrans_payment_type' => $payload['payment_type'] ?? null,
                    'payment_deadline'      => null,
                ]);

                // Kurangi stok produk saat pembayaran berhasil & pesanan selesai
                if ($transaction->product) {
                    $transaction->product->decrement('stock_quantity', $transaction->quantity);
                }

                // Notifikasi ke buyer (lengkap dengan link rating)
                NotificationService::send(
                    $transaction->buyer_id,
                    'pesanan.dibayar',
                    "Pembayaran untuk pesanan \"{$transaction->product->name}\" berhasil! Pesanan Selesai. Silakan berikan ulasan/rating.",
                    route('user.marketplace.transactions.show', $transaction->id),
                    'fa-check-circle',
                    'green'
                );

                // Notifikasi ke seller
                NotificationService::send(
                    $transaction->seller_id,
                    'pesanan.dibayar',
                    "Pembayaran dari {$transaction->buyer->name} untuk \"{$transaction->product->name}\" telah diterima. Pesanan Selesai!",
                    route('user.marketplace.seller.orders.show', $transaction->id),
                    'fa-money-bill-wave',
                    'green'
                );
            });

            Log::info('MidtransController: Marketplace payment success & auto-completed', ['order_id' => $payload['order_id']]);

        } elseif ($this->midtrans->isPaymentFailed($payload)) {
            DB::transaction(function () use ($transaction, $payload) {
                $transaction->update([
                    'payment_status' => 'failed',
                ]);
            });

            Log::info('MidtransController: Marketplace payment failed/expired', ['order_id' => $payload['order_id']]);
        }
    }
}
