@extends('layouts.app')

@section('title', 'Buat Booking - Infoma')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Buat Booking</h1>
            <p class="text-gray-600 mt-2">Lengkapi informasi untuk booking {{ $type === 'residence' ? 'residence' : 'kegiatan' }} Anda</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Form -->
            <div class="lg:col-span-2">
                <form method="POST" action="{{ route('user.bookings.store') }}" enctype="multipart/form-data" class="space-y-6">
                    @csrf
                    <input type="hidden" name="bookable_type" value="{{ $type === 'residence' ? 'App\\Models\\Residence' : 'App\\Models\\Activity' }}">
                    <input type="hidden" name="bookable_id" value="{{ $bookable->id }}">

                    <!-- Item Summary -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Item yang Dibooking</h2>

                        <div class="flex items-start space-x-4">
                            <div class="w-20 h-20 bg-gray-200 rounded-lg flex items-center justify-center">
                                @if($bookable->images && count($bookable->images) > 0)
                                    <img src="{{ asset('storage/' . $bookable->images[0]) }}"
                                         alt="{{ $bookable->name }}"
                                         class="w-full h-full object-cover rounded-lg">
                                @else
                                    <i class="fas fa-{{ $type === 'residence' ? 'building' : 'calendar-alt' }} text-gray-400 text-2xl"></i>
                                @endif
                            </div>
                            <div class="flex-1">
                                <h3 class="text-xl font-semibold text-gray-900">{{ $bookable->name }}</h3>
                                <p class="text-gray-600 mb-2">{{ Str::limit($bookable->description, 100) }}</p>
                                <div class="flex items-center text-sm text-gray-500">
                                    <i class="fas fa-map-marker-alt mr-2"></i>
                                    <span>{{ $bookable->address ?? $bookable->location }}</span>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-2xl font-bold text-blue-600">
                                    @if($bookable->discount_type && $bookable->discount_value)
                                        Rp {{ number_format($bookable->getDiscountedPrice()) }}
                                        <div class="text-sm text-gray-500 line-through">
                                            Rp {{ number_format($bookable->price ?? $bookable->price_per_month) }}
                                        </div>
                                    @else
                                        Rp {{ number_format($bookable->price ?? $bookable->price_per_month) }}
                                    @endif
                                </div>
                                <div class="text-sm text-gray-600">
                                    @if($type === 'residence')
                                        per {{ $bookable->rental_period === 'monthly' ? 'bulan' : 'tahun' }}
                                    @else
                                        per peserta
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Booking Details -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Detail Booking</h2>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            @if($type === 'residence')
                                <div>
                                    <label for="check_in_date" class="block text-sm font-medium text-gray-700 mb-2">
                                        Tanggal Masuk <span class="text-red-500">*</span>
                                    </label>
                                    <input type="date" name="check_in_date" id="check_in_date" required
                                           min="{{ date('Y-m-d') }}"
                                           value="{{ old('check_in_date') }}"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('check_in_date') border-red-500 @enderror">
                                    @error('check_in_date')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="check_out_date" class="block text-sm font-medium text-gray-700 mb-2">
                                        Tanggal Keluar <span class="text-red-500">*</span>
                                    </label>
                                    <input type="date" name="check_out_date" id="check_out_date" required
                                           min="{{ date('Y-m-d') }}"
                                           value="{{ old('check_out_date') }}"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('check_out_date') border-red-500 @enderror">
                                    @error('check_out_date')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            @else
                                <div class="md:col-span-2">
                                    <input type="hidden" name="check_in_date" value="{{ $bookable->event_date->format('Y-m-d') }}">
                                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                        <div class="flex">
                                            <i class="fas fa-info-circle text-blue-400 mr-3 mt-0.5"></i>
                                            <div>
                                                <h4 class="text-sm font-medium text-blue-800">Tanggal Kegiatan</h4>
                                                <p class="text-sm text-blue-700 mt-1">
                                                    {{ $bookable->event_date->format('d M Y, H:i') }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <div class="md:col-span-2">
                                <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                                    Catatan Tambahan
                                </label>
                                <textarea name="notes" id="notes" rows="3"
                                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('notes') border-red-500 @enderror"
                                          placeholder="Masukkan catatan atau permintaan khusus...">{{ old('notes') }}</textarea>
                                @error('notes')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Document Upload -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Upload Dokumen</h2>
                        <p class="text-sm text-gray-600 mb-4">Upload dokumen yang diperlukan untuk proses booking</p>

                        <div class="space-y-4">
                            <div>
                                <label for="ktp" class="block text-sm font-medium text-gray-700 mb-2">
                                    KTP <span class="text-red-500">*</span>
                                </label>
                                <input type="file" name="documents[]" id="ktp" accept=".pdf,.jpg,.jpeg,.png" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('documents.*') border-red-500 @enderror">
                                <p class="text-xs text-gray-500 mt-1">Format: PDF, JPG, PNG (Max: 2MB)</p>
                                @error('documents.*')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="student_card" class="block text-sm font-medium text-gray-700 mb-2">
                                    Kartu Mahasiswa
                                </label>
                                <input type="file" name="documents[]" id="student_card" accept=".pdf,.jpg,.jpeg,.png"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <p class="text-xs text-gray-500 mt-1">Format: PDF, JPG, PNG (Max: 2MB)</p>
                            </div>

                            @if($type === 'residence')
                            <div>
                                <label for="family_card" class="block text-sm font-medium text-gray-700 mb-2">
                                    Kartu Keluarga
                                </label>
                                <input type="file" name="documents[]" id="family_card" accept=".pdf,.jpg,.jpeg,.png"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <p class="text-xs text-gray-500 mt-1">Format: PDF, JPG, PNG (Max: 2MB)</p>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Terms and Conditions -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <div class="flex items-start">
                            <input type="checkbox" name="terms" id="terms" required
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded mt-1">
                            <label for="terms" class="ml-3 text-sm text-gray-700">
                                Saya menyetujui <a href="#" class="text-blue-600 hover:text-blue-500">Syarat & Ketentuan</a>
                                dan <a href="#" class="text-blue-600 hover:text-blue-500">Kebijakan Privasi</a> Infoma
                            </label>
                        </div>
                        @error('terms')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-end space-x-4">
                        <a href="{{ $type === 'residence' ? route('residences.show', $bookable) : route('activities.show', $bookable) }}"
                           class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 font-medium transition-colors">
                            Batal
                        </a>
                        <button type="submit"
                                class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors">
                            <i class="fas fa-calendar-plus mr-2"></i>Buat Booking
                        </button>
                    </div>
                </form>
            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1">
                <!-- Price Summary -->
                <div class="bg-white rounded-lg shadow-sm p-6 sticky top-8">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Ringkasan Harga</h3>

                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Harga Dasar</span>
                            <span class="font-medium">Rp {{ number_format($bookable->price ?? $bookable->price_per_month) }}</span>
                        </div>

                        @if($bookable->discount_type && $bookable->discount_value)
                            <div class="flex justify-between text-green-600">
                                <span>Diskon</span>
                                <span>
                                    @if($bookable->discount_type === 'percentage')
                                        - {{ $bookable->discount_value }}%
                                    @else
                                        - Rp {{ number_format($bookable->discount_value) }}
                                    @endif
                                </span>
                            </div>
                        @endif

                        <div class="border-t border-gray-200 pt-3">
                            <div class="flex justify-between text-lg font-semibold">
                                <span>Total</span>
                                <span>Rp {{ number_format($bookable->getDiscountedPrice()) }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 p-4 bg-blue-50 rounded-lg">
                        <div class="flex">
                            <i class="fas fa-info-circle text-blue-400 mr-3 mt-0.5"></i>
                            <div>
                                <h4 class="text-sm font-medium text-blue-800">Informasi Penting</h4>
                                <ul class="text-xs text-blue-700 mt-1 space-y-1">
                                    <li>• Booking akan diproses dalam 1x24 jam</li>
                                    <li>• Pembayaran dilakukan setelah booking disetujui</li>
                                    <li>• Batalkan booking maksimal 24 jam sebelum check-in</li>
                                </ul>
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
// Auto calculate check-out date for residence
@if($type === 'residence')
document.getElementById('check_in_date').addEventListener('change', function() {
    const checkInDate = new Date(this.value);
    const rentalPeriod = '{{ $bookable->rental_period }}';

    if (checkInDate) {
        let checkOutDate = new Date(checkInDate);

        if (rentalPeriod === 'monthly') {
            checkOutDate.setMonth(checkOutDate.getMonth() + 1);
        } else if (rentalPeriod === 'yearly') {
            checkOutDate.setFullYear(checkOutDate.getFullYear() + 1);
        }

        document.getElementById('check_out_date').value = checkOutDate.toISOString().split('T')[0];
        document.getElementById('check_out_date').min = checkOutDate.toISOString().split('T')[0];
    }
});
@endif

// File size validation
document.querySelectorAll('input[type="file"]').forEach(input => {
    input.addEventListener('change', function() {
        const file = this.files[0];
        if (file && file.size > 2 * 1024 * 1024) { // 2MB
            alert('File terlalu besar. Maksimal 2MB.');
            this.value = '';
        }
    });
});
</script>
@endpush
@endsection
