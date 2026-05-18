{{-- resources/views/verification/terms.blade.php --}}
{{-- Dipakai oleh: seller activation (/user/marketplace/sell) dan provider dashboard --}}
{{-- Hanya ditampilkan jika user belum pernah accept terms (terms_accepted_at = null) --}}

@extends('layouts.app')
@section('title', 'Syarat & Ketentuan Verifikasi — EduLiving')

@section('content')
<div class="min-h-screen bg-gray-50 py-12">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="bg-white rounded-2xl shadow-sm overflow-hidden">

            {{-- Header --}}
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-8 py-10 text-center">
                <div class="w-20 h-20 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-shield-alt text-white text-3xl"></i>
                </div>
                <h1 class="text-2xl font-bold text-white mb-2">Syarat & Ketentuan Verifikasi</h1>
                <p class="text-blue-100 text-sm">
                    Baca dan pahami sebelum melanjutkan proses verifikasi sebagai
                    <strong>{{ $type === 'seller' ? 'Seller Marketplace' : 'Provider' }}</strong>
                </p>
            </div>

            <div class="px-8 py-8 space-y-6">

                {{-- Intro --}}
                <div class="bg-blue-50 border border-blue-200 rounded-xl p-5">
                    <div class="flex items-start gap-3">
                        <i class="fas fa-info-circle text-blue-500 mt-0.5 text-lg shrink-0"></i>
                        <p class="text-sm text-blue-800">
                            Dengan melanjutkan proses verifikasi, kamu menyatakan bahwa seluruh data yang diberikan
                            adalah <strong>benar, asli, dan dapat dipertanggungjawabkan</strong>. Pelanggaran terhadap
                            ketentuan di bawah dapat mengakibatkan sanksi tegas terhadap akunmu.
                        </p>
                    </div>
                </div>

                {{-- Ketentuan Umum --}}
                <div>
                    <h2 class="font-bold text-gray-900 text-base mb-3 flex items-center gap-2">
                        <span class="w-6 h-6 bg-blue-600 text-white rounded-full flex items-center justify-center text-xs font-bold">1</span>
                        Ketentuan Umum
                    </h2>
                    <ul class="space-y-2 text-sm text-gray-700 pl-8">
                        <li class="flex items-start gap-2">
                            <i class="fas fa-check-circle text-green-500 mt-0.5 shrink-0"></i>
                            Data identitas (NIK, foto KTP, foto selfie) yang diunggah harus <strong>asli dan sesuai dengan identitas kamu</strong>. Dilarang keras menggunakan identitas orang lain.
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="fas fa-check-circle text-green-500 mt-0.5 shrink-0"></i>
                            Foto KTP harus <strong>jelas, tidak buram, tidak terpotong</strong>, dan merupakan KTP yang masih berlaku.
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="fas fa-check-circle text-green-500 mt-0.5 shrink-0"></i>
                            Foto selfie harus menampilkan <strong>wajah kamu secara jelas</strong> dan diambil saat proses verifikasi (bukan foto lama).
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="fas fa-check-circle text-green-500 mt-0.5 shrink-0"></i>
                            Satu NIK hanya dapat digunakan untuk <strong>satu akun</strong> di EduLiving.
                        </li>
                    </ul>
                </div>

                @if($type === 'seller')
                {{-- Ketentuan Khusus Seller --}}
                <div>
                    <h2 class="font-bold text-gray-900 text-base mb-3 flex items-center gap-2">
                        <span class="w-6 h-6 bg-blue-600 text-white rounded-full flex items-center justify-center text-xs font-bold">2</span>
                        Ketentuan Khusus Seller Marketplace
                    </h2>
                    <ul class="space-y-2 text-sm text-gray-700 pl-8">
                        <li class="flex items-start gap-2">
                            <i class="fas fa-check-circle text-green-500 mt-0.5 shrink-0"></i>
                            Hanya menjual <strong>barang yang halal, legal, dan tidak melanggar hukum</strong>. Dilarang menjual barang palsu, bajakan, atau terlarang.
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="fas fa-check-circle text-green-500 mt-0.5 shrink-0"></i>
                            Deskripsi dan foto produk harus sesuai dengan kondisi barang yang sebenarnya. <strong>Dilarang menipu pembeli</strong>.
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="fas fa-check-circle text-green-500 mt-0.5 shrink-0"></i>
                            Wajib menyelesaikan transaksi yang sudah disepakati. Pembatalan sepihak tanpa alasan yang sah dapat dikenakan sanksi.
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="fas fa-check-circle text-green-500 mt-0.5 shrink-0"></i>
                            Dilarang melakukan transaksi di luar platform EduLiving untuk menghindari jaminan keamanan.
                        </li>
                    </ul>
                </div>
                @else
                {{-- Ketentuan Khusus Provider --}}
                <div>
                    <h2 class="font-bold text-gray-900 text-base mb-3 flex items-center gap-2">
                        <span class="w-6 h-6 bg-blue-600 text-white rounded-full flex items-center justify-center text-xs font-bold">2</span>
                        Ketentuan Khusus Provider
                    </h2>
                    <ul class="space-y-2 text-sm text-gray-700 pl-8">
                        <li class="flex items-start gap-2">
                            <i class="fas fa-check-circle text-green-500 mt-0.5 shrink-0"></i>
                            Informasi hunian/event yang didaftarkan harus <strong>akurat dan sesuai kondisi nyata</strong>. Dilarang mencantumkan fasilitas atau harga yang menyesatkan.
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="fas fa-check-circle text-green-500 mt-0.5 shrink-0"></i>
                            Foto-foto yang diunggah merupakan <strong>dokumentasi asli</strong> dari hunian/event yang ditawarkan. Dilarang menggunakan foto dari internet atau milik orang lain.
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="fas fa-check-circle text-green-500 mt-0.5 shrink-0"></i>
                            Wajib memenuhi booking yang sudah disetujui. Pembatalan sepihak harus disertai alasan yang valid dan pemberitahuan tepat waktu.
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="fas fa-check-circle text-green-500 mt-0.5 shrink-0"></i>
                            Hunian/event yang ditawarkan tidak boleh mengandung unsur diskriminasi, SARA, atau melanggar hukum yang berlaku.
                        </li>
                    </ul>
                </div>
                @endif

                {{-- Sanksi --}}
                <div class="bg-red-50 border border-red-200 rounded-xl p-5">
                    <h2 class="font-bold text-red-800 text-base mb-3 flex items-center gap-2">
                        <i class="fas fa-exclamation-triangle text-red-500"></i>
                        Sanksi atas Pelanggaran
                    </h2>
                    <p class="text-sm text-red-700 mb-3">
                        Pelanggaran terhadap syarat & ketentuan di atas akan dikenakan sanksi berupa:
                    </p>
                    <div class="space-y-3">
                        <div class="flex items-start gap-3 bg-white rounded-lg p-3 border border-red-100">
                            <div class="w-8 h-8 bg-orange-100 rounded-full flex items-center justify-center shrink-0">
                                <i class="fas fa-clock text-orange-500 text-sm"></i>
                            </div>
                            <div>
                                <p class="font-semibold text-red-800 text-sm">Penangguhan Sementara</p>
                                <p class="text-xs text-red-600 mt-0.5">
                                    Akun dinonaktifkan selama periode tertentu (ditentukan oleh admin) tergantung
                                    tingkat pelanggaran. Selama masa penangguhan, kamu tidak dapat mengakses platform.
                                </p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3 bg-white rounded-lg p-3 border border-red-100">
                            <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center shrink-0">
                                <i class="fas fa-ban text-red-500 text-sm"></i>
                            </div>
                            <div>
                                <p class="font-semibold text-red-800 text-sm">Pemblokiran Permanen</p>
                                <p class="text-xs text-red-600 mt-0.5">
                                    Untuk pelanggaran berat (pemalsuan identitas, penipuan, dll), akun akan diblokir
                                    secara permanen. <strong>Email dan NIK kamu akan masuk daftar hitam</strong> dan
                                    tidak dapat digunakan untuk mendaftar kembali di EduLiving.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Form persetujuan --}}
                <form method="POST" action="{{ route('verification.accept-terms') }}" id="termsForm">
                    @csrf
                    <input type="hidden" name="type" value="{{ $type }}">
                    <input type="hidden" name="redirect_to" value="{{ $redirectTo ?? '' }}">

                    {{-- Checkbox wajib baca --}}
                    <div class="flex items-start gap-3 p-4 bg-gray-50 rounded-xl border border-gray-200 mb-5">
                        <input type="checkbox" name="agree" id="agree" value="1" required
                               class="mt-0.5 h-4 w-4 text-blue-600 rounded border-gray-300 cursor-pointer">
                        <label for="agree" class="text-sm text-gray-700 cursor-pointer">
                            Saya telah membaca, memahami, dan <strong>menyetujui seluruh syarat & ketentuan</strong>
                            di atas. Saya bertanggung jawab penuh atas kebenaran data yang saya berikan dan bersedia
                            menerima sanksi jika terbukti melanggar ketentuan tersebut.
                        </label>
                    </div>

                    @error('agree')
                        <p class="text-sm text-red-600 mb-3">{{ $message }}</p>
                    @enderror

                    <div class="flex gap-3">
                        <a href="{{ url()->previous() }}"
                           class="flex-1 py-3 px-5 border border-gray-300 rounded-xl text-gray-700 font-medium text-sm text-center hover:bg-gray-50 transition-colors">
                            <i class="fas fa-arrow-left mr-2"></i>Kembali
                        </a>
                        <button type="submit" id="acceptBtn" disabled
                                class="flex-1 py-3 px-5 bg-blue-600 text-white rounded-xl font-semibold text-sm hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                            <i class="fas fa-check mr-2"></i>Saya Setuju & Lanjutkan
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Tombol "Setuju" aktif hanya setelah checkbox dicentang
document.getElementById('agree').addEventListener('change', function () {
    document.getElementById('acceptBtn').disabled = !this.checked;
});
</script>
@endpush
@endsection 