@extends('layouts.app')

@section('title', 'Pembayaran - EduLiving')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Header --}}
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Pembayaran Booking</h1>
            <p class="text-gray-600 mt-2">
                Booking <span class="font-semibold">#{{ $booking->booking_code }}</span>
            </p>
        </div>

        {{-- Deadline warning --}}
        @if($booking->payment_deadline && !$booking->isPaymentExpired())
            <div class="bg-yellow-50 border border-yellow-300 rounded-xl p-4 mb-6 flex items-start gap-3">
                <i class="fas fa-clock text-yellow-500 mt-0.5 text-lg"></i>
                <div>
                    <p class="font-semibold text-yellow-800">Selesaikan pembayaran sebelum waktu habis</p>
                    <p class="text-sm text-yellow-700 mt-1">
                        Batas: {{ $booking->payment_deadline->format('d M Y, H:i') }} WIB
                        — booking akan dibatalkan otomatis jika terlewat.
                    </p>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            {{-- Panel kiri: tombol bayar --}}
            <div class="lg:col-span-2 space-y-6">

                {{-- Pilihan Metode Pembayaran (Tab) --}}
                <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                    <div class="flex border-b border-gray-100">
                        <button onclick="switchTab('midtrans')" id="tab-btn-midtrans"
                                class="flex-1 py-4 px-6 font-semibold text-sm border-b-2 border-green-600 text-green-600 focus:outline-none flex items-center justify-center gap-2">
                            <i class="fas fa-bolt text-lg"></i>
                            Pembayaran Instan (Midtrans)
                        </button>
                        <button onclick="switchTab('manual')" id="tab-btn-manual"
                                class="flex-1 py-4 px-6 font-semibold text-sm border-b-2 border-transparent text-gray-500 hover:text-gray-700 focus:outline-none flex items-center justify-center gap-2">
                            <i class="fas fa-university text-lg"></i>
                            Transfer Bank Manual
                        </button>
                    </div>

                    {{-- Konten Tab 1: Midtrans --}}
                    <div id="tab-content-midtrans" class="p-8 text-center space-y-6">
                        <div class="mb-2">
                            <div class="inline-flex items-center justify-center w-16 h-16 bg-green-100 rounded-full mb-4">
                                <i class="fas fa-shield-alt text-green-600 text-2xl"></i>
                            </div>
                            <h2 class="text-xl font-semibold text-gray-900">Pembayaran Aman via Midtrans</h2>
                            <p class="text-gray-500 mt-2 text-sm">
                                Pilih metode pembayaran instan terverifikasi otomatis.
                            </p>
                        </div>

                        <div class="flex flex-wrap justify-center gap-2 opacity-70 mb-4">
                            <span class="text-xs bg-gray-100 text-gray-600 px-3 py-1 rounded-full">Transfer Bank</span>
                            <span class="text-xs bg-gray-100 text-gray-600 px-3 py-1 rounded-full">GoPay</span>
                            <span class="text-xs bg-gray-100 text-gray-600 px-3 py-1 rounded-full">OVO / DANA</span>
                            <span class="text-xs bg-gray-100 text-gray-600 px-3 py-1 rounded-full">QRIS</span>
                        </div>

                        <button id="pay-button"
                                class="w-full bg-green-600 hover:bg-green-700 active:bg-green-800 text-white font-semibold
                                       py-4 px-8 rounded-xl text-lg transition-colors focus:outline-none focus:ring-4
                                       focus:ring-green-300 flex items-center justify-center gap-3">
                            <i class="fas fa-credit-card"></i>
                            Bayar Sekarang — Rp {{ number_format($booking->transaction->final_amount) }}
                        </button>
                    </div>

                    {{-- Konten Tab 2: Transfer Manual --}}
                    <div id="tab-content-manual" class="p-8 hidden space-y-6">
                        <div class="bg-gray-50 rounded-xl p-5 border border-gray-100 text-left space-y-3">
                            <h4 class="font-semibold text-gray-900 text-sm">Rekening Tujuan Pembayaran:</h4>
                            
                            <div class="flex items-center justify-between border-b border-gray-100 pb-2">
                                <div>
                                    <p class="text-xs text-gray-400">BANK MANDIRI</p>
                                    <p class="font-bold text-gray-800 text-sm">137-00-1234567-8</p>
                                    <p class="text-xs text-gray-500">a.n. PT EduLiving Indonesia</p>
                                </div>
                                <button onclick="salinTeks('1370012345678')" class="text-xs text-blue-600 hover:underline">
                                    <i class="far fa-copy mr-1"></i>Salin
                                </button>
                            </div>

                            <div class="flex items-center justify-between pt-1">
                                <div>
                                    <p class="text-xs text-gray-400">BANK BCA</p>
                                    <p class="font-bold text-gray-800 text-sm">829-012-3456</p>
                                    <p class="text-xs text-gray-500">a.n. PT EduLiving Indonesia</p>
                                </div>
                                <button onclick="salinTeks('8290123456')" class="text-xs text-blue-600 hover:underline">
                                    <i class="far fa-copy mr-1"></i>Salin
                                </button>
                            </div>
                        </div>

                        <form action="{{ route('user.bookings.processPayment', $booking) }}" method="POST" enctype="multipart/form-data" class="text-left space-y-4">
                            @csrf
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Upload Bukti Transfer</label>
                                <div class="border-2 border-dashed border-gray-200 rounded-xl p-6 text-center hover:bg-gray-50 transition-colors relative cursor-pointer">
                                    <input type="file" name="payment_proof" accept="image/*" required class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" onchange="previewBukti(this)">
                                    <div id="upload-placeholder">
                                        <i class="fas fa-cloud-upload-alt text-gray-400 text-3xl mb-2"></i>
                                        <p class="text-sm font-medium text-gray-600">Pilih berkas bukti transfer Anda</p>
                                        <p class="text-xs text-gray-400 mt-1">Format: JPG, JPEG, PNG, WEBP (Maks 5MB)</p>
                                    </div>
                                    <div id="image-preview" class="hidden flex justify-center">
                                        <img id="preview-img" src="#" alt="Pratinjau Bukti" class="max-h-48 rounded-lg border border-gray-200">
                                    </div>
                                </div>
                                <p id="file-name" class="text-xs text-gray-400 mt-2 text-center"></p>
                            </div>

                            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-4 px-8 rounded-xl transition-colors flex items-center justify-center gap-2">
                                <i class="fas fa-upload"></i>
                                Kirim Bukti Transfer
                            </button>
                        </form>
                    </div>
                </div>

                {{-- Kembali --}}
                <div class="text-center">
                    <a href="{{ route('user.bookings.show', $booking) }}"
                       class="text-sm text-gray-500 hover:text-gray-700 underline">
                        ← Kembali ke detail booking
                    </a>
                </div>

            </div>

            {{-- Panel kanan: ringkasan --}}
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl shadow-sm p-6 sticky top-8">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Ringkasan Pembayaran</h3>

                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Item</span>
                            <span class="font-medium text-right max-w-[60%]">
                                {{ $booking->bookable->name ?? '-' }}
                            </span>
                        </div>

                        @if($booking->duration_months)
                            <div class="flex justify-between">
                                <span class="text-gray-500">Durasi</span>
                                <span class="font-medium">{{ $booking->duration_months }} bulan</span>
                            </div>
                        @endif

                        <div class="flex justify-between">
                            <span class="text-gray-500">Harga dasar</span>
                            <span class="font-medium">Rp {{ number_format($booking->transaction->original_amount) }}</span>
                        </div>

                        @if($booking->transaction->discount_amount > 0)
                            <div class="flex justify-between text-green-600">
                                <span>Diskon</span>
                                <span>− Rp {{ number_format($booking->transaction->discount_amount) }}</span>
                            </div>
                        @endif

                        <div class="border-t border-gray-200 pt-3 flex justify-between text-base font-bold">
                            <span>Total</span>
                            <span class="text-green-700">
                                Rp {{ number_format($booking->transaction->final_amount) }}
                            </span>
                        </div>
                    </div>

                    <div class="mt-6 p-3 bg-blue-50 rounded-lg text-xs text-blue-700">
                        <i class="fas fa-info-circle mr-1"></i>
                        Pembayaran dikonfirmasi otomatis setelah transaksi berhasil. Tidak perlu upload bukti manual.
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
document.getElementById('pay-button').addEventListener('click', function () {
    const btn = this;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Membuka pembayaran...';

    window.snap.pay('{{ $snapToken }}', {
        onSuccess: function (result) {
            window.location.href = '{{ route('user.bookings.show', $booking) }}?payment=success';
        },
        onPending: function (result) {
            window.location.href = '{{ route('user.bookings.show', $booking) }}?payment=pending';
        },
        onError: function (result) {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-credit-card mr-2"></i> Coba Lagi';
            alert('Pembayaran gagal. Silakan coba lagi atau pilih metode lain.');
        },
        onClose: function () {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-credit-card mr-2"></i>Bayar Sekarang — Rp {{ number_format($booking->transaction->final_amount) }}';
        }
    });
});

function switchTab(type) {
    const btnMidtrans = document.getElementById('tab-btn-midtrans');
    const btnManual = document.getElementById('tab-btn-manual');
    const contentMidtrans = document.getElementById('tab-content-midtrans');
    const contentManual = document.getElementById('tab-content-manual');

    if (type === 'midtrans') {
        btnMidtrans.className = "flex-1 py-4 px-6 font-semibold text-sm border-b-2 border-green-600 text-green-600 focus:outline-none flex items-center justify-center gap-2";
        btnManual.className = "flex-1 py-4 px-6 font-semibold text-sm border-b-2 border-transparent text-gray-500 hover:text-gray-700 focus:outline-none flex items-center justify-center gap-2";
        contentMidtrans.classList.remove('hidden');
        contentManual.classList.add('hidden');
    } else {
        btnManual.className = "flex-1 py-4 px-6 font-semibold text-sm border-b-2 border-blue-600 text-blue-600 focus:outline-none flex items-center justify-center gap-2";
        btnMidtrans.className = "flex-1 py-4 px-6 font-semibold text-sm border-b-2 border-transparent text-gray-500 hover:text-gray-700 focus:outline-none flex items-center justify-center gap-2";
        contentManual.classList.remove('hidden');
        contentMidtrans.classList.add('hidden');
    }
}

function previewBukti(input) {
    const placeholder = document.getElementById('upload-placeholder');
    const preview = document.getElementById('image-preview');
    const img = document.getElementById('preview-img');
    const filename = document.getElementById('file-name');

    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            img.src = e.target.result;
            placeholder.classList.add('hidden');
            preview.classList.remove('hidden');
        }
        reader.readAsDataURL(input.files[0]);
        filename.textContent = "Terpilih: " + input.files[0].name;
    }
}

function salinTeks(teks) {
    navigator.clipboard.writeText(teks).then(() => {
        alert("Nomor rekening berhasil disalin: " + teks);
    });
}
</script>
@endpush
