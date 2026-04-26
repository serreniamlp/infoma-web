@extends('layouts.app')

@section('title', 'Daftar Kegiatan - Infoma')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Header --}}
        <div class="mb-8">
            <nav class="mb-3">
                <ol class="flex items-center space-x-2 text-sm text-gray-500">
                    <li><a href="{{ route('home') }}" class="hover:text-green-600">Beranda</a></li>
                    <li><i class="fas fa-chevron-right text-xs"></i></li>
                    @if($type === 'residence')
                        <li><a href="{{ route('residences.index') }}" class="hover:text-green-600">Hunian</a></li>
                    @else
                        <li><a href="{{ route('activities.index') }}" class="hover:text-green-600">Acara</a></li>
                    @endif
                    <li><i class="fas fa-chevron-right text-xs"></i></li>
                    <li class="text-gray-900">{{ $type === 'residence' ? 'Buat Booking' : 'Daftar Acara' }}</li>
                </ol>
            </nav>
            <h1 class="text-3xl font-bold text-gray-900">
                {{ $type === 'residence' ? 'Buat Booking Hunian' : 'Daftar Acara' }}
            </h1>
            <p class="text-gray-600 mt-1">
                {{ $type === 'residence' ? 'Lengkapi informasi untuk booking hunian Anda' : 'Lengkapi data diri untuk mendaftar acara ini' }}
            </p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            {{-- ── MAIN FORM ── --}}
            <div class="lg:col-span-2 space-y-6">
                <form method="POST" action="{{ route('user.bookings.store') }}"
                      enctype="{{ $type === 'residence' ? 'multipart/form-data' : 'application/x-www-form-urlencoded' }}"
                      class="space-y-6">
                    @csrf
                    <input type="hidden" name="bookable_type"
                           value="{{ $type === 'residence' ? 'App\\Models\\Residence' : 'App\\Models\\Activity' }}">
                    <input type="hidden" name="bookable_id" value="{{ $bookable->id }}">

                    {{-- Item Summary --}}
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">
                            {{ $type === 'residence' ? 'Hunian yang Dipilih' : 'Acara yang Dipilih' }}
                        </h2>
                        <div class="flex items-start gap-4">
                            <div class="w-20 h-20 bg-gray-100 rounded-lg flex items-center justify-center flex-shrink-0 overflow-hidden">
                                @if($bookable->images && count($bookable->images) > 0)
                                    <img src="{{ asset('storage/' . $bookable->images[0]) }}"
                                         alt="{{ $bookable->name }}"
                                         class="w-full h-full object-cover">
                                @else
                                    <i class="fas fa-{{ $type === 'residence' ? 'building' : 'calendar-alt' }} text-gray-400 text-2xl"></i>
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="text-lg font-semibold text-gray-900 truncate">{{ $bookable->name }}</h3>
                                <p class="text-sm text-gray-500 mt-1 line-clamp-2">{{ Str::limit($bookable->description, 100) }}</p>
                                <div class="flex items-center text-sm text-gray-500 mt-2">
                                    <i class="fas fa-map-marker-alt mr-2 text-green-500"></i>
                                    <span class="truncate">{{ $bookable->address ?? $bookable->location }}</span>
                                </div>
                            </div>
                            <div class="text-right flex-shrink-0">
                                @if($bookable->discount_type && $bookable->discount_value)
                                    <div class="text-sm text-gray-400 line-through">
                                        Rp {{ number_format($bookable->price ?? $bookable->price_per_month) }}
                                    </div>
                                    <div class="text-xl font-bold text-green-600">
                                        Rp {{ number_format($bookable->getDiscountedPrice()) }}
                                    </div>
                                @else
                                    <div class="text-xl font-bold text-green-600">
                                        Rp {{ number_format($bookable->price ?? $bookable->price_per_month) }}
                                    </div>
                                @endif
                                <div class="text-xs text-gray-500 mt-0.5">
                                    {{ $type === 'residence' ? 'per ' . ($bookable->rental_period === 'monthly' ? 'bulan' : 'tahun') : 'per peserta' }}
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Tanggal (residence) / Info Tanggal Event (activity) --}}
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Detail Pendaftar</h2>

                        @if($type === 'residence')
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="check_in_date" class="block text-sm font-medium text-gray-700 mb-2">
                                        Tanggal Masuk <span class="text-red-500">*</span>
                                    </label>
                                    <input type="date" name="check_in_date" id="check_in_date" required
                                           min="{{ date('Y-m-d') }}" value="{{ old('check_in_date') }}"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent @error('check_in_date') border-red-500 @enderror">
                                    @error('check_in_date')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="check_out_date" class="block text-sm font-medium text-gray-700 mb-2">
                                        Tanggal Keluar <span class="text-red-500">*</span>
                                    </label>
                                    <input type="date" name="check_out_date" id="check_out_date" required
                                           min="{{ date('Y-m-d') }}" value="{{ old('check_out_date') }}"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent @error('check_out_date') border-red-500 @enderror">
                                    @error('check_out_date')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        @else
                            {{-- Activity: tanggal otomatis dari event --}}
                            <input type="hidden" name="check_in_date" value="{{ $bookable->event_date->format('Y-m-d') }}">
                            <div class="flex items-start gap-3 p-4 bg-green-50 border border-green-200 rounded-lg">
                                <i class="fas fa-calendar-check text-green-500 mt-0.5"></i>
                                <div>
                                    <p class="text-sm font-medium text-green-800">Tanggal Acara</p>
                                    <p class="text-sm text-green-700 mt-0.5">
                                        {{ $bookable->event_date->translatedFormat('l, d F Y') }} · {{ $bookable->event_date->format('H:i') }} WIB
                                    </p>
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- ── DATA DIRI (khusus activity) ── --}}
                    @if($type === 'activity')
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-1">Data Diri Peserta</h2>
                        <p class="text-sm text-gray-500 mb-5">Pastikan data yang kamu isi sudah benar</p>

                        <div class="space-y-5">
                            {{-- Nama Lengkap --}}
                            <div>
                                <label for="participant_name" class="block text-sm font-medium text-gray-700 mb-2">
                                    Nama Lengkap <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                                        <i class="fas fa-user"></i>
                                    </span>
                                    <input type="text"
                                           name="participant_name"
                                           id="participant_name"
                                           required
                                           value="{{ old('participant_name', auth()->user()->name) }}"
                                           placeholder="Masukkan nama lengkap Anda"
                                           class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent @error('participant_name') border-red-500 @enderror">
                                </div>
                                @error('participant_name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Email --}}
                            <div>
                                <label for="participant_email" class="block text-sm font-medium text-gray-700 mb-2">
                                    Email <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                                        <i class="fas fa-envelope"></i>
                                    </span>
                                    <input type="email"
                                           name="participant_email"
                                           id="participant_email"
                                           required
                                           value="{{ old('participant_email', auth()->user()->email) }}"
                                           placeholder="contoh@email.com"
                                           class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent @error('participant_email') border-red-500 @enderror">
                                </div>
                                @error('participant_email')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Nomor Telepon --}}
                            <div>
                                <label for="participant_phone" class="block text-sm font-medium text-gray-700 mb-2">
                                    Nomor Telepon <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                                        <i class="fas fa-phone"></i>
                                    </span>
                                    <input type="tel"
                                           name="participant_phone"
                                           id="participant_phone"
                                           required
                                           value="{{ old('participant_phone', auth()->user()->phone ?? '') }}"
                                           placeholder="08xxxxxxxxxx"
                                           inputmode="numeric"
                                           class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent @error('participant_phone') border-red-500 @enderror">
                                </div>
                                <p class="mt-1 text-xs text-gray-400">Hanya boleh berisi angka, 8–15 digit</p>
                                @error('participant_phone')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                    @endif

                    {{-- ── DOKUMEN (khusus residence) ── --}}
                    @if($type === 'residence')
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-1">Upload Dokumen</h2>
                        <p class="text-sm text-gray-600 mb-5">Upload dokumen yang diperlukan untuk proses booking</p>

                        <div class="space-y-4">
                            <div>
                                <label for="ktp" class="block text-sm font-medium text-gray-700 mb-2">
                                    KTP <span class="text-red-500">*</span>
                                </label>
                                <input type="file" name="documents[]" id="ktp" accept=".pdf,.jpg,.jpeg,.png" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 @error('documents.*') border-red-500 @enderror">
                                <p class="text-xs text-gray-400 mt-1">Format: PDF, JPG, PNG · Maks. 2MB</p>
                                @error('documents.*')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="family_card" class="block text-sm font-medium text-gray-700 mb-2">
                                    Kartu Keluarga
                                </label>
                                <input type="file" name="documents[]" id="family_card" accept=".pdf,.jpg,.jpeg,.png"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                                <p class="text-xs text-gray-400 mt-1">Format: PDF, JPG, PNG · Maks. 2MB</p>
                            </div>
                        </div>
                    </div>
                    @endif

                    {{-- Syarat & Ketentuan --}}
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <div class="flex items-start gap-3">
                            <input type="checkbox" name="terms" id="terms" required
                                   class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded mt-0.5 flex-shrink-0">
                            <label for="terms" class="text-sm text-gray-700">
                                Saya menyetujui
                                <a href="#" class="text-green-600 hover:underline">Syarat & Ketentuan</a>
                                dan
                                <a href="#" class="text-green-600 hover:underline">Kebijakan Privasi</a>
                                EduLiving
                            </label>
                        </div>
                        @error('terms')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Submit --}}
                    <div class="flex justify-end gap-3">
                        <a href="{{ $type === 'residence' ? route('residences.show', $bookable) : route('activities.show', $bookable) }}"
                           class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 font-medium transition-colors">
                            Batal
                        </a>
                        <button type="submit"
                                class="px-6 py-3 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition-colors">
                            <i class="fas fa-{{ $type === 'residence' ? 'calendar-plus' : 'paper-plane' }} mr-2"></i>
                            {{ $type === 'residence' ? 'Buat Booking' : 'Kirim Pendaftaran' }}
                        </button>
                    </div>
                </form>
            </div>

            {{-- ── SIDEBAR ── --}}
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-sm p-6 sticky top-8">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Ringkasan</h3>

                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Harga Dasar</span>
                            <span class="font-medium">Rp {{ number_format($bookable->price ?? $bookable->price_per_month) }}</span>
                        </div>

                        @if($bookable->discount_type && $bookable->discount_value)
                            <div class="flex justify-between text-green-600">
                                <span>Diskon</span>
                                <span>
                                    @if($bookable->discount_type === 'percentage')
                                        &minus;{{ $bookable->discount_value }}%
                                    @else
                                        &minus;Rp {{ number_format($bookable->discount_value) }}
                                    @endif
                                </span>
                            </div>
                        @endif

                        <div class="border-t border-gray-200 pt-3">
                            <div class="flex justify-between text-base font-semibold">
                                <span>Total</span>
                                <span class="text-green-600">Rp {{ number_format($bookable->getDiscountedPrice()) }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-5 p-4 bg-green-50 rounded-lg border border-green-100">
                        <div class="flex gap-3">
                            <i class="fas fa-info-circle text-green-500 mt-0.5 flex-shrink-0"></i>
                            <div class="text-xs text-green-700 space-y-1">
                                @if($type === 'residence')
                                    <p>• Booking diproses dalam 1×24 jam</p>
                                    <p>• Pembayaran dilakukan setelah booking disetujui</p>
                                    <p>• Batalkan booking maks. 24 jam sebelum check-in</p>
                                @else
                                    <p>• Pendaftaran diproses dalam 1×24 jam</p>
                                    <p>• Pembayaran dilakukan setelah pendaftaran disetujui</p>
                                    <p>• Batas pendaftaran: <strong>{{ $bookable->registration_deadline->format('d M Y, H:i') }}</strong></p>
                                    <p>• Sisa slot: <strong>{{ $bookable->available_slots }} tempat</strong></p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

@push('scripts')
<script>
// Auto-calculate check-out date untuk residence
@if($type === 'residence')
document.getElementById('check_in_date').addEventListener('change', function () {
    const checkIn = new Date(this.value);
    if (!checkIn) return;

    const rentalPeriod = '{{ $bookable->rental_period }}';
    const checkOut = new Date(checkIn);
    if (rentalPeriod === 'monthly') {
        checkOut.setMonth(checkOut.getMonth() + 1);
    } else {
        checkOut.setFullYear(checkOut.getFullYear() + 1);
    }

    const formatted = checkOut.toISOString().split('T')[0];
    document.getElementById('check_out_date').value = formatted;
    document.getElementById('check_out_date').min   = formatted;
});

// Validasi ukuran file
document.querySelectorAll('input[type="file"]').forEach(input => {
    input.addEventListener('change', function () {
        const file = this.files[0];
        if (file && file.size > 2 * 1024 * 1024) {
            alert('File terlalu besar. Maksimal 2MB.');
            this.value = '';
        }
    });
});
@endif

// Hanya izinkan angka di field telepon
@if($type === 'activity')
document.getElementById('participant_phone').addEventListener('input', function () {
    this.value = this.value.replace(/\D/g, '');
});
@endif
</script>
@endpush
@endsection
