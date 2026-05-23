@extends('layouts.app')

@section('title', 'Pembayaran - EduLiving')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Header --}}
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Pembayaran Pesanan</h1>
            <p class="text-gray-600 mt-2">
                Transaksi <span class="font-semibold">#{{ $transaction->transaction_code }}</span>
            </p>
        </div>

        {{-- Deadline warning --}}
        @if($transaction->payment_deadline && !$transaction->isPaymentExpired())
            <div class="bg-yellow-50 border border-yellow-300 rounded-xl p-4 mb-6 flex items-start gap-3">
                <i class="fas fa-clock text-yellow-500 mt-0.5 text-lg"></i>
                <div class="flex-1">
                    <p class="font-semibold text-yellow-800">Selesaikan pembayaran sebelum waktu habis</p>
                    <p class="text-sm text-yellow-700 mt-1">
                        Batas: {{ $transaction->payment_deadline->format('d M Y, H:i') }} WIB
                        — pesanan akan dibatalkan otomatis jika terlewat.
                    </p>
                    <div class="mt-2 flex items-center gap-2">
                        <span class="text-sm text-yellow-700">Sisa waktu:</span>
                        <span id="paymentCountdown"
                              class="font-mono font-bold text-yellow-900 bg-yellow-100 px-3 py-1 rounded-md text-base">
                            --:--:--
                        </span>
                    </div>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            {{-- Panel kiri: tombol bayar --}}
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-xl shadow-sm p-8 text-center">
                    <div class="mb-6">
                        <div class="inline-flex items-center justify-center w-16 h-16 bg-green-100 rounded-full mb-4">
                            <i class="fas fa-shield-alt text-green-600 text-2xl"></i>
                        </div>
                        <h2 class="text-xl font-semibold text-gray-900">Pembayaran Aman via Midtrans</h2>
                        <p class="text-gray-500 mt-2 text-sm">
                            Pilih metode pembayaran yang paling nyaman — transfer bank, kartu kredit,
                            GoPay, OVO, DANA, ShopeePay, dan banyak lagi.
                        </p>
                    </div>

                    {{-- Badge metode --}}
                    <div class="flex flex-wrap justify-center gap-2 mb-8 opacity-70">
                        <span class="text-xs bg-gray-100 text-gray-600 px-3 py-1 rounded-full">Transfer Bank</span>
                        <span class="text-xs bg-gray-100 text-gray-600 px-3 py-1 rounded-full">GoPay</span>
                        <span class="text-xs bg-gray-100 text-gray-600 px-3 py-1 rounded-full">OVO</span>
                        <span class="text-xs bg-gray-100 text-gray-600 px-3 py-1 rounded-full">DANA</span>
                        <span class="text-xs bg-gray-100 text-gray-600 px-3 py-1 rounded-full">ShopeePay</span>
                        <span class="text-xs bg-gray-100 text-gray-600 px-3 py-1 rounded-full">QRIS</span>
                    </div>

                    {{-- Tombol bayar --}}
                    <button id="pay-button"
                            class="w-full bg-green-600 hover:bg-green-700 active:bg-green-800 text-white font-semibold
                                   py-4 px-8 rounded-xl text-lg transition-colors focus:outline-none focus:ring-4
                                   focus:ring-green-300 flex items-center justify-center gap-3">
                        <i class="fas fa-credit-card"></i>
                        Bayar Sekarang — Rp {{ number_format($transaction->total_amount) }}
                    </button>

                    <p class="text-xs text-gray-400 mt-4">
                        <i class="fas fa-lock mr-1"></i>
                        Transaksi diproses secara aman oleh Midtrans. EduLiving tidak menyimpan data kartu Anda.
                    </p>
                </div>

                {{-- Kembali --}}
                <div class="text-center">
                    <a href="{{ route('user.marketplace.transactions.show', $transaction) }}"
                       class="text-sm text-gray-500 hover:text-gray-700 underline">
                        ← Kembali ke detail pesanan
                    </a>
                </div>
            </div>

            {{-- Panel kanan: ringkasan --}}
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl shadow-sm p-6 sticky top-8">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Ringkasan Pesanan</h3>

                    <div class="space-y-3 text-sm">

                        {{-- Produk --}}
                        <div class="flex justify-between">
                            <span class="text-gray-500">Produk</span>
                            <span class="font-medium text-right max-w-[60%]">
                                {{ $transaction->product->name }}
                            </span>
                        </div>

                        <div class="flex justify-between">
                            <span class="text-gray-500">Jumlah</span>
                            <span class="font-medium">{{ $transaction->quantity }} pcs</span>
                        </div>

                        <div class="flex justify-between">
                            <span class="text-gray-500">Harga satuan</span>
                            <span class="font-medium">Rp {{ number_format($transaction->unit_price) }}</span>
                        </div>

                        {{-- Metode pengambilan --}}
                        <div class="flex justify-between">
                            <span class="text-gray-500">Pengambilan</span>
                            <span class="font-medium">{{ $transaction->pickup_method_label }}</span>
                        </div>

                        @if($transaction->buyer_address)
                            <div class="flex justify-between">
                                <span class="text-gray-500 shrink-0">Alamat</span>
                                <span class="text-right text-gray-700 max-w-[60%]">
                                    {{ $transaction->buyer_address }}
                                </span>
                            </div>
                        @endif

                        <div class="border-t border-gray-200 pt-3 flex justify-between text-base font-bold">
                            <span>Total</span>
                            <span class="text-green-700">
                                Rp {{ number_format($transaction->total_amount) }}
                            </span>
                        </div>
                    </div>

                    <div class="mt-6 p-3 bg-blue-50 rounded-lg text-xs text-blue-700">
                        <i class="fas fa-info-circle mr-1"></i>
                        Status pesanan diperbarui otomatis setelah pembayaran berhasil. Tidak perlu upload bukti manual.
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@push('scripts')
{{-- Snap.js Midtrans --}}
<script src="{{ $snapUrl }}" data-client-key="{{ $clientKey }}"></script>

<script>
// ── Countdown timer deadline ──────────────────────────────────────────────
@if($transaction->payment_deadline && !$transaction->isPaymentExpired())
(function () {
    const deadline = {{ $transaction->payment_deadline->timestamp * 1000 }};
    const el = document.getElementById('paymentCountdown');
    if (!el) return;

    function pad(n) { return String(n).padStart(2, '0'); }

    function tick() {
        const remaining = deadline - Date.now();
        if (remaining <= 0) {
            el.textContent = '00:00:00';
            setTimeout(() => location.reload(), 2000);
            return;
        }
        const h = Math.floor(remaining / 3600000);
        const m = Math.floor((remaining % 3600000) / 60000);
        const s = Math.floor((remaining % 60000) / 1000);
        el.textContent = `${pad(h)}:${pad(m)}:${pad(s)}`;

        if (remaining < 600000) {
            el.classList.add('text-red-700', 'bg-red-100');
            el.classList.remove('text-yellow-900', 'bg-yellow-100');
        }
    }
    tick();
    setInterval(tick, 1000);
})();
@endif

// ── Snap popup ────────────────────────────────────────────────────────────
document.getElementById('pay-button').addEventListener('click', function () {
    const btn = this;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Membuka pembayaran...';

    window.snap.pay('{{ $snapToken }}', {
        onSuccess: function (result) {
            window.location.href = '{{ route('user.marketplace.transactions.show', $transaction) }}?payment=success';
        },
        onPending: function (result) {
            window.location.href = '{{ route('user.marketplace.transactions.show', $transaction) }}?payment=pending';
        },
        onError: function (result) {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-credit-card mr-2"></i> Coba Lagi';
            alert('Pembayaran gagal. Silakan coba lagi atau pilih metode lain.');
        },
        onClose: function () {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-credit-card mr-2"></i> Bayar Sekarang — Rp {{ number_format($transaction->total_amount) }}';
        }
    });
});
</script>
@endpush
