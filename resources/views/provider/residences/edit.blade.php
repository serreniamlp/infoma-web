@extends('layouts.app')

@section('title', 'Edit Residence - Infoma')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder@1.13.0/dist/Control.Geocoder.css" />
<link rel="stylesheet" href="{{ asset('css/leaflet-maps.css') }}">
@endpush

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Edit Residence</h1>
            <p class="text-gray-600 mt-2">Ubah informasi residence "{{ $residence->name }}"</p>
        </div>

        <form method="POST" action="{{ route('provider.residences.update', $residence) }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Basic Information -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Informasi Dasar</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                            Nama Residence <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="name" id="name" required
                               value="{{ old('name', $residence->name) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('name') border-red-500 @enderror"
                               placeholder="Nama residence">
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                            Deskripsi <span class="text-red-500">*</span>
                        </label>
                        <textarea name="description" id="description" rows="4" required
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('description') border-red-500 @enderror"
                                  placeholder="Deskripsikan residence Anda...">{{ old('description', $residence->description) }}</textarea>
                        @error('description')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="category_id" class="block text-sm font-medium text-gray-700 mb-2">
                            Kategori <span class="text-red-500">*</span>
                        </label>
                        <select name="category_id" id="category_id" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('category_id') border-red-500 @enderror">
                            <option value="">Pilih Kategori</option>
                            @foreach($categories as $category)
                                @if($category->type === 'residence')
                                    <option value="{{ $category->id }}" {{ old('category_id', $residence->category_id) == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endif
                            @endforeach
                        </select>
                        @error('category_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="rental_period" class="block text-sm font-medium text-gray-700 mb-2">
                            Periode Sewa <span class="text-red-500">*</span>
                        </label>
                        <select name="rental_period" id="rental_period" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('rental_period') border-red-500 @enderror">
                            <option value="">Pilih Periode</option>
                            <option value="monthly" {{ old('rental_period', $residence->rental_period) === 'monthly' ? 'selected' : '' }}>Bulanan</option>
                            <option value="yearly" {{ old('rental_period', $residence->rental_period) === 'yearly' ? 'selected' : '' }}>Tahunan</option>
                        </select>
                        @error('rental_period')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Location Information -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Informasi Lokasi</h2>

                <div>
                    <label for="address" class="block text-sm font-medium text-gray-700 mb-2">
                        Alamat Lengkap <span class="text-red-500">*</span>
                    </label>
                    <textarea name="address" id="address" rows="3" required
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('address') border-red-500 @enderror"
                              placeholder="Alamat lengkap residence">{{ old('address', $residence->address) }}</textarea>
                    @error('address')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Peta Lokasi</h2>
                <p class="text-sm text-gray-600 mb-4">Pilih lokasi residence di peta atau gunakan pencarian untuk menemukan alamat</p>

                <div class="map-container">
                    <div class="map-controls">
                        <button type="button" onclick="residenceMap.getCurrentLocation()" class="btn btn-primary">
                            <i class="fas fa-location-arrow mr-2"></i>Lokasi Saat Ini
                        </button>
                        <button type="button" onclick="residenceMap.clearLocation()" class="btn btn-danger">
                            <i class="fas fa-times mr-2"></i>Hapus Lokasi
                        </button>
                    </div>

                    <div id="residence-map"></div>

                    <div class="coordinates-display">
                        <div class="form-group">
                            <label for="latitude">Latitude</label>
                            <input type="number" name="latitude" id="latitude" step="any" value="{{ old('latitude', $residence->latitude) }}" class="@error('latitude') border-red-500 @enderror" placeholder="0.000000">
                            @error('latitude')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div class="form-group">
                            <label for="longitude">Longitude</label>
                            <input type="number" name="longitude" id="longitude" step="any" value="{{ old('longitude', $residence->longitude) }}" class="@error('longitude') border-red-500 @enderror" placeholder="0.000000">
                            @error('longitude')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="map-info">
                        <h4><i class="fas fa-info-circle mr-2"></i>Cara Menggunakan Peta</h4>
                        <p>• Klik pada peta untuk memilih lokasi<br>
                        • Gunakan kotak pencarian di atas peta untuk mencari alamat<br>
                        • Ketik di kolom "Alamat" untuk mencari alamat secara otomatis<br>
                        • Koordinat akan terisi otomatis saat Anda memilih lokasi</p>
                    </div>
                </div>
            </div>

            <!-- Pricing Information -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Informasi Harga</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="price_per_month" class="block text-sm font-medium text-gray-700 mb-2">
                            Harga per Bulan <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="price_per_month" id="price_per_month" required min="0"
                               value="{{ old('price_per_month', $residence->price_per_month) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('price_per_month') border-red-500 @enderror"
                               placeholder="0">
                        @error('price_per_month')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="capacity" class="block text-sm font-medium text-gray-700 mb-2">
                            Kapasitas <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="capacity" id="capacity" required min="1"
                               value="{{ old('capacity', $residence->capacity) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('capacity') border-red-500 @enderror"
                               placeholder="1">
                        @error('capacity')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Discount Section -->
                <div class="mt-6">
                    <h3 class="text-md font-medium text-gray-900 mb-4">Diskon (Opsional)</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="discount_type" class="block text-sm font-medium text-gray-700 mb-2">
                                Jenis Diskon
                            </label>
                            <select name="discount_type" id="discount_type"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('discount_type') border-red-500 @enderror">
                                <option value="">Tidak ada diskon</option>
                                <option value="percentage" {{ old('discount_type', $residence->discount_type) === 'percentage' ? 'selected' : '' }}>Persentase (%)</option>
                                <option value="flat" {{ old('discount_type', $residence->discount_type) === 'flat' ? 'selected' : '' }}>Nominal (Rp)</option>
                            </select>
                            @error('discount_type')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="discount_value" class="block text-sm font-medium text-gray-700 mb-2">
                                Nilai Diskon
                            </label>
                            <input type="number" name="discount_value" id="discount_value" min="0"
                                   value="{{ old('discount_value', $residence->discount_value) }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('discount_value') border-red-500 @enderror"
                                   placeholder="0">
                            @error('discount_value')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Facilities -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Fasilitas</h2>

                <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                    @php
                        $commonFacilities = [
                            'AC', 'WiFi', 'Kamar Mandi Dalam', 'Lemari', 'Meja Belajar', 'Kursi',
                            'Kasur', 'Bantal', 'Selimut', 'Dapur', 'Kulkas', 'Mesin Cuci',
                            'Parkir Motor', 'Parkir Mobil', 'Security 24 Jam', 'CCTV'
                        ];
                        $currentFacilities = old('facilities', $residence->facilities ?? []);
                    @endphp

                    @foreach($commonFacilities as $facility)
                    <label class="flex items-center">
                        <input type="checkbox" name="facilities[]" value="{{ $facility }}"
                               {{ in_array($facility, $currentFacilities) ? 'checked' : '' }}
                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <span class="ml-2 text-sm text-gray-700">{{ $facility }}</span>
                    </label>
                    @endforeach
                </div>

                <div class="mt-4">
                    <label for="custom_facilities" class="block text-sm font-medium text-gray-700 mb-2">
                        Fasilitas Lainnya (pisahkan dengan koma)
                    </label>
                    <input type="text" name="custom_facilities" id="custom_facilities"
                           value="{{ old('custom_facilities') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           placeholder="Fasilitas 1, Fasilitas 2, ...">
                </div>
            </div>

            <!-- Current Images -->
            @if($residence->images && count($residence->images) > 0)
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Gambar Saat Ini</h2>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    @foreach($residence->images as $index => $image)
                    <div class="relative">
                        <img src="{{ asset('storage/' . $image) }}"
                             alt="{{ $residence->name }}"
                             class="w-full h-32 object-cover rounded-lg">
                        <button type="button" onclick="removeImage({{ $index }})"
                                class="absolute top-2 right-2 bg-red-500 hover:bg-red-600 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    @endforeach
                </div>

                <input type="hidden" name="existing_images" id="existing_images" value="{{ json_encode($residence->images) }}">
            </div>
            @endif

            <!-- New Images -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">
                    @if($residence->images && count($residence->images) > 0)
                        Tambah Gambar Baru
                    @else
                        Upload Gambar
                    @endif
                </h2>

                <div>
                    <label for="images" class="block text-sm font-medium text-gray-700 mb-2">
                        Upload Gambar
                    </label>
                    <input type="file" name="images[]" id="images" multiple accept="image/*"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('images.*') border-red-500 @enderror">
                    <p class="text-xs text-gray-500 mt-1">Format: JPG, PNG (Max: 5MB per file)</p>
                    @error('images.*')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Status -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Status</h2>

                <div class="flex items-center">
                    <input type="checkbox" name="is_active" id="is_active" value="1"
                           {{ old('is_active', $residence->is_active) ? 'checked' : '' }}
                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="is_active" class="ml-2 text-sm text-gray-700">
                        Aktifkan residence ini
                    </label>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="flex justify-end space-x-4">
                <a href="{{ route('provider.residences.show', $residence) }}"
                   class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 font-medium transition-colors">
                    Batal
                </a>
                <button type="submit"
                        class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors">
                    <i class="fas fa-save mr-2"></i>Update Residence
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet-control-geocoder@1.13.0/dist/Control.Geocoder.js"></script>
<script src="{{ asset('js/leaflet-maps.js') }}"></script>
<script>
let existingImages = @json($residence->images ?? []);
let imagesToRemove = [];

function removeImage(index) {
    imagesToRemove.push(index);
    event.target.closest('.relative').style.display = 'none';
}

// File size validation
document.getElementById('images').addEventListener('change', function() {
    const files = this.files;
    const maxSize = 5 * 1024 * 1024; // 5MB

    for (let i = 0; i < files.length; i++) {
        if (files[i].size > maxSize) {
            alert(`File ${files[i].name} terlalu besar. Maksimal 5MB per file.`);
            this.value = '';
            return;
        }
    }
});

// Show/hide discount value based on type
document.getElementById('discount_type').addEventListener('change', function() {
    const discountValue = document.getElementById('discount_value');
    const label = discountValue.previousElementSibling;

    if (this.value === 'percentage') {
        discountValue.placeholder = '10';
        label.textContent = 'Persentase Diskon (%)';
        discountValue.max = '100';
    } else if (this.value === 'flat') {
        discountValue.placeholder = '100000';
        label.textContent = 'Nominal Diskon (Rp)';
        discountValue.removeAttribute('max');
    } else {
        discountValue.placeholder = '0';
        label.textContent = 'Nilai Diskon';
        discountValue.value = '';
    }
});

// Add hidden input for removed images
document.querySelector('form').addEventListener('submit', function() {
    if (imagesToRemove.length > 0) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'removed_images';
        input.value = JSON.stringify(imagesToRemove);
        this.appendChild(input);
    }
});
</script>
@endpush
@endsection
