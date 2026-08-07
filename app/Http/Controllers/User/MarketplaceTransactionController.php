<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\ProductCategory;
use App\Models\MarketplaceProduct;
use App\Models\MarketplaceTransaction;
use App\Services\MidtransService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class MarketplaceTransactionController extends Controller
{
    public function index(Request $request)
    {
        $query = MarketplaceTransaction::with(['product', 'buyer', 'seller'])
            ->where('buyer_id', Auth::id());

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('transaction_code', 'like', "%{$search}%")
                  ->orWhereHas('product', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $transactions = $query->orderBy('created_at', 'desc')->paginate(10);

        $stats = [
            'total'       => MarketplaceTransaction::where('buyer_id', Auth::id())->count(),
            'pending'     => MarketplaceTransaction::where('buyer_id', Auth::id())->where('status', 'pending')->count(),
            'completed'   => MarketplaceTransaction::where('buyer_id', Auth::id())->where('status', 'completed')->count(),
            'total_spent' => MarketplaceTransaction::where('buyer_id', Auth::id())->where('status', 'completed')->sum('total_amount'),
        ];

        return view('user.marketplace.transactions.index', compact('transactions', 'stats'));
    }

    public function show(MarketplaceTransaction $transaction)
    {
        if ($transaction->buyer_id != Auth::id()) {
            abort(403, 'Unauthorized');
        }

        $transaction->load(['product', 'buyer', 'seller', 'rating']);

        return view('user.marketplace.transactions.show', compact('transaction'));
    }

    public function create(MarketplaceProduct $product)
    {
        if ($product->seller_id == Auth::id()) {
            return redirect()->back()->with('error', 'Anda tidak dapat membeli produk sendiri!');
        }

        if (!$product->is_available) {
            return redirect()->back()->with('error', 'Produk tidak tersedia!');
        }

        return view('marketplace.transactions.create', compact('product'));
    }

    public function store(Request $request, MarketplaceProduct $product)
    {
        if ($product->seller_id == Auth::id()) {
            return redirect()->back()->with('error', 'Anda tidak dapat membeli produk sendiri!');
        }

        if (!$product->is_available) {
            return redirect()->back()->with('error', 'Produk tidak tersedia!');
        }

        $request->validate([
            'quantity'       => 'required|integer|min:1|max:' . $product->stock_quantity,
            'buyer_name'     => 'required|string|max:255',
            'buyer_phone'    => 'required|string|max:20',
            // [REVISI-3-ZONE] buyer_address: saat Revisi 3 selesai, validasi ini
            // mungkin berubah menjadi address_id (FK ke user_addresses).
            // Untuk sementara tetap terima string bebas.
            'buyer_address'  => 'nullable|string',
            // [REVISI-3-ZONE-END]
            'pickup_method'  => 'required|in:cod,delivery,pickup,meetup',
            'pickup_notes'   => 'nullable|string',
            // [MIDTRANS] payment_method dihapus dari validasi form —
            // ditentukan oleh Midtrans Snap saat checkout, bukan dari input user.
            // Khusus COD diisi manual di bawah.
        ]);

        $totalAmount = $product->price * $request->quantity;

        // [MIDTRANS] COD, meetup, dan pickup = bayar langsung di tempat, tidak perlu Midtrans
        $isCashOnDelivery = in_array($request->pickup_method, ['cod', 'meetup', 'pickup']);
        $paymentMethod    = $isCashOnDelivery ? 'cod' : 'midtrans';

        $transaction = MarketplaceTransaction::create([
            'buyer_id'       => Auth::id(),
            'seller_id'      => $product->seller_id,
            'product_id'     => $product->id,
            'quantity'       => $request->quantity,
            'unit_price'     => $product->price,
            'total_amount'   => $totalAmount,
            'buyer_name'     => $request->buyer_name,
            'buyer_phone'    => $request->buyer_phone,
            // [REVISI-3-ZONE] buyer_address: setelah Revisi 3 selesai,
            // ini mungkin diisi dari user_addresses berdasarkan address_id.
            'buyer_address'  => $request->buyer_address,
            // [REVISI-3-ZONE-END]
            'pickup_method'  => $request->pickup_method,
            'pickup_notes'   => $request->pickup_notes,
            'payment_method' => $paymentMethod,
            'status'         => 'pending',
            // [MIDTRANS] COD & pickup tidak perlu pembayaran online — langsung set cod_pending
            'payment_status' => $isCashOnDelivery ? 'cod_pending' : 'pending',
            'payment_deadline' => null, // Deadline baru berjalan setelah penjual mengonfirmasi
        ]);

        // Notifikasi ke seller
        NotificationService::pesananBaru(
            $transaction->seller_id,
            Auth::user()->name,
            $product->name,
            route('user.marketplace.seller.orders.show', $transaction->id)
        );

        // Redirect ke detail transaksi
        if ($isCashOnDelivery) {
            return redirect()->route('user.marketplace.transactions.show', $transaction)
                ->with('success', 'Pesanan berhasil dibuat! Bayar langsung saat mengambil/menerima barang.');
        }

        return redirect()->route('user.marketplace.transactions.show', $transaction)
            ->with('success', 'Pesanan berhasil dibuat! Menunggu penjual mengonfirmasi ketersediaan barang sebelum pembayaran.');
    }

    // [MIDTRANS] Method baru — generate Snap token dan tampilkan halaman pembayaran.
    // Menggantikan uploadPaymentProof() yang lama untuk transaksi non-COD.
    public function initiatePayment(MarketplaceTransaction $transaction)
    {
        if ($transaction->buyer_id != Auth::id()) {
            abort(403, 'Unauthorized');
        }

        if (in_array($transaction->pickup_method, ['cod', 'meetup', 'pickup'])) {
            return redirect()->route('user.marketplace.transactions.show', $transaction)
                ->with('info', 'Transaksi COD / Ambil Sendiri tidak memerlukan pembayaran online.');
        }

        if ($transaction->status === 'pending') {
            return redirect()->route('user.marketplace.transactions.show', $transaction)
                ->with('info', 'Pesanan masih menunggu konfirmasi dari penjual. Pembayaran dapat dilakukan setelah penjual mengonfirmasi pesanan.');
        }

        if ($transaction->payment_status === 'paid') {
            return redirect()->route('user.marketplace.transactions.show', $transaction)
                ->with('info', 'Pembayaran untuk transaksi ini sudah selesai.');
        }

        if ($transaction->isPaymentExpired()) {
            return redirect()->route('user.marketplace.transactions.show', $transaction)
                ->with('error', 'Batas waktu pembayaran sudah habis.');
        }

        try {
            // Pakai snap_token lama jika masih ada & masih pending
            $snapToken = $transaction->snap_token;
            if (! $snapToken) {
                $midtrans  = app(MidtransService::class);
                $snapToken = $midtrans->createSnapTokenForMarketplace($transaction);
                $transaction->update(['snap_token' => $snapToken]);
            }
        } catch (\Exception $e) {
            return redirect()->route('user.marketplace.transactions.show', $transaction)
                ->with('error', 'Gagal menghubungi payment gateway: ' . $e->getMessage());
        }

        $snapUrl   = config('midtrans.snap_url');
        $clientKey = config('midtrans.client_key');

        return view('user.marketplace.transactions.payment', compact(
            'transaction', 'snapToken', 'snapUrl', 'clientKey'
        ));
    }

    /**
     * uploadPaymentProof() dipertahankan untuk kompatibilitas route yang sudah ada.
     * Untuk transaksi baru, pembayaran dikonfirmasi via webhook Midtrans.
     *
     * @deprecated Gunakan initiatePayment() + webhook Midtrans
     */
    public function uploadPaymentProof(Request $request, MarketplaceTransaction $transaction)
    {
        if ($transaction->buyer_id != Auth::id()) {
            abort(403, 'Unauthorized');
        }

        // Arahkan ke halaman pembayaran Midtrans jika belum punya bukti lama
        if (! $transaction->payment_proof) {
            return redirect()->route('user.marketplace.transactions.payment', $transaction);
        }

        // Fallback: jika ada bukti lama (transaksi sebelum Midtrans), tetap proses
        $request->validate([
            'payment_proof' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($transaction->payment_proof) {
            Storage::disk('public')->delete($transaction->payment_proof);
        }

        $path = $request->file('payment_proof')->store('marketplace/payment-proofs', 'public');

        $transaction->update([
            'payment_proof'  => $path,
            'payment_status' => 'paid',
        ]);

        return redirect()->back()->with('success', 'Bukti pembayaran berhasil diupload!');
    }
    // [MIDTRANS-END]

    public function rate(Request $request, MarketplaceTransaction $transaction)
    {
        if ($transaction->buyer_id != Auth::id()) {
            abort(403, 'Unauthorized');
        }

        if ($transaction->status !== 'completed') {
            return redirect()->back()->with('error', 'Anda hanya dapat memberikan rating untuk transaksi yang sudah selesai!');
        }

        if ($transaction->rating) {
            return redirect()->back()->with('error', 'Anda sudah memberikan rating untuk transaksi ini!');
        }

        $request->validate([
            'rating'         => 'required|integer|min:1|max:5',
            'review'         => 'nullable|string|max:1000',
            'images.*'       => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_recommended' => 'nullable|boolean',
        ]);

        $data = [
            'user_id'        => Auth::id(),
            'rateable_type'  => \App\Models\MarketplaceProduct::class,
            'rateable_id'    => $transaction->product_id,
            'transaction_id' => $transaction->id,
            'rating'         => $request->rating,
            'review'         => $request->review,
            'is_recommended' => $request->boolean('is_recommended'),
        ];

        if ($request->hasFile('images')) {
            $images = [];
            foreach ($request->file('images') as $image) {
                $images[] = $image->store('marketplace/ratings', 'public');
            }
            $data['images'] = $images;
        }

        $transaction->rating()->create($data);

        return redirect()->back()->with('success', 'Rating berhasil diberikan!');
    }

    public function cancel(Request $request, MarketplaceTransaction $transaction)
    {
        if ($transaction->buyer_id != Auth::id()) {
            abort(403, 'Unauthorized');
        }

        if (!$transaction->canBeCancelled()) {
            return redirect()->back()->with('error', 'Transaksi tidak dapat dibatalkan!');
        }

        $request->validate([
            'cancellation_reason' => 'required|string|max:1000',
        ]);

        $transaction->update([
            'status'              => 'cancelled',
            'cancellation_reason' => $request->cancellation_reason,
            'cancelled_at'        => now(),
        ]);

        // Kirim notifikasi ke seller bahwa pesanan dibatalkan buyer
        NotificationService::statusPesananDiupdate(
            $transaction->seller_id,
            $transaction->product->name ?? 'produk',
            'cancelled',
            route('user.marketplace.seller.orders.show', $transaction->id)
        );

        return redirect()->back()->with('success', 'Transaksi berhasil dibatalkan!');
    }
}