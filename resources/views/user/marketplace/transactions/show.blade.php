@extends('layouts.app')

@section('title', 'Detail Transaksi Marketplace')

@section('content')
@php
    $sellerPhone = $transaction->seller->phone ?? null;
    $cleanSellerPhone = $sellerPhone ? preg_replace('/[^0-9]/', '', $sellerPhone) : null;
    if ($cleanSellerPhone && str_starts_with($cleanSellerPhone, '0')) {
        $cleanSellerPhone = '62' . substr($cleanSellerPhone, 1);
    }
    $waBuyerMsg = "Halo {$transaction->seller->name}, saya pembeli ({$transaction->buyer_name}) dari EduLiving untuk pesanan \"{$transaction->product->name}\" (Kode: {$transaction->transaction_code}). Izin bertanya terkait pesanan / lokasi titik temu COD.";
    $sellerWaUrl = $cleanSellerPhone ? "https://wa.me/{$cleanSellerPhone}?text=" . urlencode($waBuyerMsg) : null;
@endphp
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Breadcrumb -->
        <nav class="flex mb-8" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="{{ route('user.marketplace.transactions.index') }}"
                       class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l-1 12H6L5 9z"></path>
                        </svg>
                        Transaksi Marketplace
                    </a>
                </li>
                <li aria-current="page">
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">{{ $transaction->transaction_code }}</span>
                    </div>
                </li>
            </ol>
        </nav>

        {{-- Banner: Menunggu Konfirmasi Penjual --}}
        @if($transaction->status === 'pending' && !in_array($transaction->pickup_method, ['cod', 'meetup', 'pickup']))
            <div class="mb-6 bg-blue-50 border border-blue-200 rounded-lg p-4 flex items-start gap-4">
                <div class="text-blue-500 text-2xl mt-0.5">
                    <i class="fas fa-user-clock"></i>
                </div>
                <div class="flex-1">
                    <h3 class="font-semibold text-blue-800 text-base">Menunggu Konfirmasi Penjual</h3>
                    <p class="text-blue-700 text-sm mt-1">
                        Pesanan kamu berhasil dibuat. Penjual sedang memeriksa ketersediaan barang.
                        Tombol pembayaran akan terbuka otomatis setelah penjual mengonfirmasi pesanan.
                    </p>
                </div>
            </div>
        @elseif($transaction->status === 'pending' && in_array($transaction->pickup_method, ['cod', 'meetup', 'pickup']))
            <div class="mb-6 bg-green-50 border border-green-200 rounded-lg p-4 flex items-start gap-4">
                <div class="text-green-500 text-2xl mt-0.5">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="flex-1">
                    <h3 class="font-semibold text-green-800 text-base">Menunggu Konfirmasi Pesanan Penjual</h3>
                    <p class="text-green-700 text-sm mt-1">
                        Pesanan berhasil dibuat! Penjual akan memeriksa ketersediaan barang. Pembayaran dilakukan langsung saat bertemu/mengambil barang.
                    </p>
                </div>
            </div>
        @endif

        {{-- ================================================================ --}}
        {{-- BANNER: COUNTDOWN PEMBAYARAN                                     --}}
        {{-- Muncul jika: status confirmed + payment pending + deadline belum lewat --}}
        {{-- ================================================================ --}}
        @if($transaction->status === 'confirmed' && $transaction->payment_status === 'pending' && $transaction->payment_deadline && !in_array($transaction->pickup_method, ['cod', 'meetup', 'pickup']))
            @php $isExpired = $transaction->isPaymentExpired(); @endphp

            @if(!$isExpired)
            <div id="paymentCountdownBanner"
                 class="mb-6 bg-amber-50 border border-amber-300 rounded-lg p-4 flex items-start gap-4">
                <div class="text-amber-500 text-2xl mt-0.5">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="flex-1">
                    <h3 class="font-semibold text-amber-800 text-base">Pesanan Dikonfirmasi! Segera Selesaikan Pembayaran</h3>
                    <p class="text-amber-700 text-sm mt-1">
                        Penjual telah mengonfirmasi kesiapan barang. Selesaikan pembayaran sebelum batas waktu habis.
                    </p>
                    <div class="mt-3 flex items-center gap-3">
                        <span class="text-amber-700 text-sm font-medium">Sisa waktu:</span>
                        <span id="paymentCountdown"
                              class="font-mono font-bold text-amber-900 text-lg bg-amber-100 px-3 py-1 rounded-md">
                            --:--:--
                        </span>
                    </div>
                    <p class="text-amber-600 text-xs mt-2">
                        Batas akhir: {{ $transaction->payment_deadline->format('d M Y, H:i') }} WIB
                    </p>
                </div>
                <a href="{{ route('user.marketplace.transactions.payment', $transaction) }}"
                   class="shrink-0 bg-amber-500 hover:bg-amber-600 text-white px-4 py-2 rounded-lg font-medium text-sm transition-colors">
                    Bayar Sekarang
                </a>
            </div>
            @else
            <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4 flex items-start gap-3">
                <i class="fas fa-clock text-red-400 text-xl mt-0.5"></i>
                <div>
                    <h3 class="font-semibold text-red-800">Batas Waktu Pembayaran Habis</h3>
                    <p class="text-red-700 text-sm mt-1">
                        Pesanan ini akan segera dibatalkan otomatis karena batas waktu pembayaran sudah terlewat.
                    </p>
                </div>
            </div>
            @endif
        @endif

        {{-- Banner: Pesanan Selesai --}}
        @if($transaction->status === 'completed')
            <div class="mb-6 bg-green-50 border border-green-200 rounded-lg p-4 flex items-start gap-4">
                <div class="text-green-500 text-2xl mt-0.5">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="flex-1">
                    <h3 class="font-semibold text-green-800 text-base">Pesanan Selesai!</h3>
                    <p class="text-green-700 text-sm mt-1">
                        Pembayaran telah berhasil diproses dan transaksi telah selesai. Terima kasih telah berbelanja di EduLiving!
                    </p>
                </div>
            </div>
        @endif

        {{-- Banner: dibatalkan otomatis --}}
        @if($transaction->status === 'cancelled' && str_contains($transaction->cancellation_reason ?? '', 'otomatis'))
            <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4 flex items-start gap-3">
                <i class="fas fa-clock text-red-400 text-xl mt-0.5"></i>
                <div>
                    <h3 class="font-semibold text-red-800">Pesanan Dibatalkan Otomatis</h3>
                    <p class="text-red-700 text-sm mt-1">{{ $transaction->cancellation_reason }}</p>
                    <a href="{{ route('marketplace.index') }}"
                       class="inline-block mt-3 text-sm text-red-700 underline hover:text-red-900">
                        Cari produk lain →
                    </a>
                </div>
            </div>
        @endif

        <!-- Header -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden mb-8">
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-8 py-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-white flex items-center">
                            <svg class="w-8 h-8 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                            Detail Transaksi
                        </h1>
                        <p class="text-blue-100 mt-2">{{ $transaction->transaction_code }}</p>
                    </div>
                    <div class="flex items-center space-x-4">
                        <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold
                            @if($transaction->status == 'completed') bg-green-100 text-green-800
                            @elseif($transaction->status == 'pending') bg-yellow-100 text-yellow-800
                            @elseif($transaction->status == 'cancelled') bg-red-100 text-red-800
                            @else bg-blue-100 text-blue-800 @endif">
                            {{ $transaction->status_label }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-8">
                <!-- Product Information -->
                <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                        <h2 class="text-xl font-bold text-gray-900">Informasi Produk</h2>
                    </div>
                    <div class="p-6">
                        <div class="flex items-start space-x-6">
                            <div class="flex-shrink-0">
                                <img src="{{ $transaction->product->main_image }}"
                                     class="w-32 h-32 rounded-lg object-cover shadow-md border border-gray-200"
                                     alt="{{ $transaction->product->name }}">
                            </div>
                            <div class="flex-grow">
                                <h3 class="text-2xl font-bold text-gray-900 mb-2">{{ $transaction->product->name }}</h3>
                                <p class="text-gray-600 mb-4">{{ $transaction->product->description }}</p>
                                <div class="grid grid-cols-2 gap-4 text-sm">
                                    <div>
                                        <span class="text-gray-500">Kategori:</span>
                                        <span class="font-medium ml-2">{{ $transaction->product->category->name ?? '-' }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500">Harga Satuan:</span>
                                        <span class="font-medium ml-2">Rp {{ number_format($transaction->unit_price, 0, ',', '.') }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500">Jumlah:</span>
                                        <span class="font-medium ml-2">{{ $transaction->quantity }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500">Total:</span>
                                        <span class="font-bold text-blue-600 ml-2">Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Seller Information -->
                <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200 flex items-center justify-between">
                        <h2 class="text-xl font-bold text-gray-900">Informasi Penjual</h2>
                        @if($transaction->seller && $transaction->seller->isOnline())
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                <span class="w-2 h-2 mr-1.5 bg-green-500 rounded-full animate-pulse"></span>Online
                            </span>
                        @endif
                    </div>
                    <div class="p-6">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div class="flex items-center space-x-4">
                                <div class="w-14 h-14 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center text-white font-bold text-xl shadow">
                                    {{ strtoupper(substr($transaction->seller->name ?? 'U', 0, 1)) }}
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">{{ $transaction->seller->name }}</h3>
                                    <p class="text-gray-500 text-sm">{{ $transaction->seller->email }}</p>
                                    @if($transaction->seller->phone)
                                        <p class="text-gray-600 text-xs mt-1">
                                            <i class="fas fa-phone-alt mr-1 text-gray-400"></i>{{ $transaction->seller->phone }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                            @if($sellerWaUrl)
                                <a href="{{ $sellerWaUrl }}" target="_blank" rel="noopener noreferrer"
                                   class="inline-flex items-center justify-center gap-2 bg-green-600 hover:bg-green-700 text-white font-semibold px-4 py-2.5 rounded-xl text-sm transition-colors shadow-sm shrink-0">
                                    <i class="fab fa-whatsapp text-lg"></i> Hubungi Penjual (WhatsApp)
                                </a>
                            @else
                                <span class="text-xs text-gray-400 italic">Nomor HP Penjual tidak tersedia</span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Pickup Information -->
                <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                        <h2 class="text-xl font-bold text-gray-900">Informasi Pengambilan</h2>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Penerima</label>
                                <p class="text-gray-900 font-medium">{{ $transaction->buyer_name }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nomor Telepon</label>
                                <p class="text-gray-900 font-medium">{{ $transaction->buyer_phone }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Metode Pengambilan</label>
                                <p class="text-gray-900 font-medium">{{ $transaction->pickup_method_label }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Metode Pembayaran</label>
                                <p class="text-gray-900 font-medium">{{ $transaction->payment_method }}</p>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Alamat</label>
                                {{-- [REVISI-3-ZONE] Setelah Revisi 3 selesai, ini mungkin
                                     menampilkan data dari user_addresses, bukan teks bebas --}}
                                <p class="text-gray-900">{{ $transaction->buyer_address }}</p>
                                {{-- [REVISI-3-ZONE-END] --}}
                            </div>
                        </div>
                    </div>
                </div>

                @if($transaction->payment_proof)
                <!-- Payment Proof -->
                <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                        <h2 class="text-xl font-bold text-gray-900">Bukti Pembayaran</h2>
                    </div>
                    <div class="p-6">
                        <div class="text-center">
                            <img src="{{ $transaction->payment_proof_url }}"
                                 class="max-w-full h-auto rounded-lg shadow-md border border-gray-200 mx-auto"
                                 alt="Bukti Pembayaran">
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="space-y-8">
                <!-- [MIDTRANS] Blok pembayaran — menggantikan form upload bukti manual -->
                @if($transaction->status === 'confirmed' && $transaction->payment_status === 'pending' && $transaction->pickup_method !== 'cod' && !$transaction->isPaymentExpired())
                <div id="upload-payment" class="bg-white rounded-xl shadow-lg overflow-hidden">
                    <div class="px-6 py-4 bg-green-50 border-b border-green-200">
                        <h2 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                            <i class="fas fa-credit-card text-green-600"></i>
                            Selesaikan Pembayaran
                        </h2>
                    </div>
                    <div class="p-6 space-y-4">
                        <p class="text-sm text-gray-600">
                            Klik tombol di bawah untuk membuka halaman pembayaran.
                            Tersedia berbagai metode — transfer bank, e-wallet, QRIS, dan lainnya.
                        </p>
                        <a href="{{ route('user.marketplace.transactions.payment', $transaction) }}"
                           class="block w-full text-center bg-green-600 hover:bg-green-700 text-white
                                  py-3 px-4 rounded-lg font-semibold transition-colors">
                            <i class="fas fa-credit-card mr-2"></i>
                            Bayar Sekarang
                            — Rp {{ number_format($transaction->total_amount) }}
                        </a>
                        <p class="text-xs text-gray-400 text-center">
                            <i class="fas fa-lock mr-1"></i>
                            Pembayaran diproses aman via Midtrans
                        </p>
                    </div>
                </div>

                @elseif(in_array($transaction->pickup_method, ['cod', 'meetup', 'pickup']) || $transaction->payment_method === 'cod')
                {{-- [MIDTRANS] COD & Pickup: tidak perlu bayar online --}}
                <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                    <div class="px-6 py-4 bg-green-50 border-b border-green-200">
                        <h2 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                            <i class="fas {{ $transaction->pickup_method === 'pickup' ? 'fa-walking' : 'fa-handshake' }} text-green-600"></i>
                            {{ $transaction->pickup_method === 'pickup' ? 'Pembayaran Pas Ambil (Di Lokasi)' : 'Pembayaran COD (Bayar di Tempat)' }}
                        </h2>
                    </div>
                    <div class="p-6 space-y-4">
                        <div class="flex items-start gap-3 text-sm text-green-800">
                            <i class="fas fa-check-circle text-green-500 mt-0.5 text-lg"></i>
                            <p>
                                @if($transaction->pickup_method === 'pickup')
                                    Pesanan ini menggunakan metode <strong>Ambil Sendiri</strong>. Silakan datangi alamat penjual dan bayar langsung di lokasi saat mengambil barang.
                                @else
                                    Pesanan ini menggunakan metode <strong>bayar di tempat (COD)</strong>. Siapkan uang tunai saat bertemu dan menerima barang dari penjual.
                                @endif
                            </p>
                        </div>
                        @if($sellerWaUrl)
                            <a href="{{ $sellerWaUrl }}" target="_blank" rel="noopener noreferrer"
                               class="block w-full text-center bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-4 rounded-xl transition-colors shadow-sm">
                                <i class="fab fa-whatsapp text-lg mr-2"></i>
                                Hubungi Penjual via WhatsApp
                            </a>
                        @endif
                    </div>
                </div>
                @endif
                <!-- [MIDTRANS-END] -->

                {{-- [REVISI-3-ZONE] Blok ini mungkin perlu penyesuaian setelah
                     Revisi 3 selesai jika ada perubahan pada tampilan alamat pembeli --}}

                <!-- Cancel Transaction -->
                @if($transaction->canBeCancelled() && !$transaction->isPaymentExpired())
                <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                        <h2 class="text-xl font-bold text-gray-900">Batalkan Transaksi</h2>
                    </div>
                    <div class="p-6">
                        <form action="{{ route('user.marketplace.transactions.cancel', $transaction) }}" method="POST" class="space-y-4">
                            @csrf
                            @method('PATCH')
                            <div>
                                <label for="cancellation_reason" class="block text-sm font-medium text-gray-700 mb-2">Alasan Pembatalan</label>
                                <textarea id="cancellation_reason" name="cancellation_reason" rows="3" required
                                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent"
                                          placeholder="Jelaskan alasan pembatalan..."></textarea>
                            </div>
                            <button type="submit" class="w-full px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition-colors duration-200"
                                    onclick="return confirm('Apakah Anda yakin ingin membatalkan transaksi ini?')">
                                Batalkan Transaksi
                            </button>
                        </form>
                    </div>
                </div>
                @endif

                <!-- Transaction Summary -->
                <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                        <h2 class="text-xl font-bold text-gray-900">Ringkasan</h2>
                    </div>
                    <div class="p-6 space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Kode Transaksi:</span>
                            <span class="font-mono text-sm bg-gray-100 px-2 py-1 rounded">{{ $transaction->transaction_code }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Status:</span>
                            <span class="font-medium">{{ $transaction->status_label }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Status Pembayaran:</span>
                            <span class="font-medium">{{ $transaction->payment_status_label }}</span>
                        </div>
                        @if($transaction->payment_deadline && $transaction->status === 'pending' && $transaction->payment_status === 'pending')
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Batas Pembayaran:</span>
                            <span class="font-medium {{ $transaction->isPaymentExpired() ? 'text-red-600' : 'text-amber-600' }}">
                                {{ $transaction->payment_deadline->format('H:i, d M') }}
                            </span>
                        </div>
                        @endif
                        <div class="flex justify-between border-t pt-3">
                            <span class="text-gray-600">Total:</span>
                            <span class="font-bold text-blue-600">Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// ── Payment Countdown Timer (Marketplace) ────────────────────────────────────
@if($transaction->status === 'pending' && $transaction->payment_status === 'pending' && $transaction->payment_deadline && !$transaction->isPaymentExpired())
(function () {
    const deadline = {{ $transaction->payment_deadline->timestamp * 1000 }};
    const el = document.getElementById('paymentCountdown');

    if (!el) return;

    function pad(n) { return String(n).padStart(2, '0'); }

    function updateCountdown() {
        const remaining = deadline - Date.now();

        if (remaining <= 0) {
            el.textContent = '00:00:00';
            el.classList.add('text-red-700', 'bg-red-100');
            el.classList.remove('text-amber-900', 'bg-amber-100');
            setTimeout(() => location.reload(), 2000);
            return;
        }

        const h = Math.floor(remaining / 3600000);
        const m = Math.floor((remaining % 3600000) / 60000);
        const s = Math.floor((remaining % 60000) / 1000);

        el.textContent = `${pad(h)}:${pad(m)}:${pad(s)}`;

        if (remaining < 600000) {
            el.classList.add('text-red-700', 'bg-red-100');
            el.classList.remove('text-amber-900', 'bg-amber-100');
        }
    }

    updateCountdown();
    setInterval(updateCountdown, 1000);
})();
@endif
</script>
@endpush
@endsection