@extends('layouts.app')

@section('title', 'Tambah Kegiatan - Infoma')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder@1.13.0/dist/Control.Geocoder.css" />
<link rel="stylesheet" href="{{ asset('css/leaflet-maps.css') }}">
@endpush

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Tambah Kegiatan</h1>
            <p class="text-gray-600 mt-2">Buat kegiatan baru untuk mahasiswa</p>
        </div>

        <form method="POST" action="{{ route('provider.event.activities.store') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf

            {{-- INFORMASI DASAR --}}
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Informasi Dasar</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nama Kegiatan <span class="text-red-500">*</span></label>
                        <input type="text" name="name" required value="{{ old('name') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent @error('name') border-red-500 @enderror" placeholder="Nama kegiatan">
                        @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Deskripsi <span class="text-red-500">*</span></label>
                        <textarea name="description" rows="4" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent @error('description') border-red-500 @enderror" placeholder="Deskripsikan kegiatan Anda...">{{ old('description') }}</textarea>
                        @error('description')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Kategori <span class="text-red-500">*</span></label>
                        <select name="category_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent @error('category_id') border-red-500 @enderror">
                            <option value="">Pilih Kategori</option>
                            @foreach($categories as $category)
                                @if($category->type === 'activity')
                                    <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                @endif
                            @endforeach
                        </select>
                        @error('category_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Lokasi <span class="text-red-500">*</span></label>
                        <input type="text" name="location" id="location" required value="{{ old('location') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent @error('location') border-red-500 @enderror" placeholder="Lokasi kegiatan">
                        @error('location')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Event <span class="text-red-500">*</span></label>
                        <input type="datetime-local" name="event_date" required value="{{ old('event_date') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent @error('event_date') border-red-500 @enderror">
                        @error('event_date')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Batas Registrasi <span class="text-red-500">*</span></label>
                        <input type="datetime-local" name="registration_deadline" required value="{{ old('registration_deadline') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent @error('registration_deadline') border-red-500 @enderror">
                        @error('registration_deadline')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            {{-- PETA LOKASI --}}
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Peta Lokasi</h2>
                <p class="text-sm text-gray-600 mb-4">Pilih lokasi kegiatan di peta atau gunakan pencarian untuk menemukan alamat</p>

                <div class="map-container">
                    <div class="map-controls">
                        <button type="button" onclick="activityMap.getCurrentLocation()" class="btn btn-primary">
                            <i class="fas fa-location-arrow mr-2"></i>Lokasi Saat Ini
                        </button>
                        <button type="button" onclick="activityMap.clearLocation()" class="btn btn-danger">
                            <i class="fas fa-times mr-2"></i>Hapus Lokasi
                        </button>
                    </div>

                    <div id="activity-map"></div>

                    <div class="coordinates-display">
                        <div class="form-group">
                            <label for="latitude">Latitude</label>
                            <input type="number" name="latitude" id="latitude" step="any" value="{{ old('latitude') }}" class="@error('latitude') border-red-500 @enderror" placeholder="0.000000">
                            @error('latitude')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div class="form-group">
                            <label for="longitude">Longitude</label>
                            <input type="number" name="longitude" id="longitude" step="any" value="{{ old('longitude') }}" class="@error('longitude') border-red-500 @enderror" placeholder="0.000000">
                            @error('longitude')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="map-info">
                        <h4><i class="fas fa-info-circle mr-2"></i>Cara Menggunakan Peta</h4>
                        <p>• Klik pada peta untuk memilih lokasi<br>
                        • Gunakan kotak pencarian di atas peta untuk mencari alamat<br>
                        • Ketik di kolom "Lokasi" untuk mencari alamat secara otomatis<br>
                        • Koordinat akan terisi otomatis saat Anda memilih lokasi</p>
                    </div>
                </div>
            </div>

            {{-- KAPASITAS & HARGA --}}
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Kapasitas & Harga</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Harga (Rp) <span class="text-red-500">*</span></label>
                        <input type="number" name="price" required min="0" value="{{ old('price') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent @error('price') border-red-500 @enderror" placeholder="0">
                        @error('price')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Kapasitas <span class="text-red-500">*</span></label>
                        <input type="number" name="capacity" required min="1" value="{{ old('capacity') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent @error('capacity') border-red-500 @enderror" placeholder="1">
                        @error('capacity')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="mt-6">
                    <h3 class="text-md font-medium text-gray-900 mb-4">Diskon (Opsional)</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Jenis Diskon</label>
                            <select name="discount_type" id="discount_type" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent @error('discount_type') border-red-500 @enderror">
                                <option value="">Tidak ada diskon</option>
                                <option value="percentage" {{ old('discount_type') === 'percentage' ? 'selected' : '' }}>Persentase (%)</option>
                                <option value="flat" {{ old('discount_type') === 'flat' ? 'selected' : '' }}>Nominal (Rp)</option>
                            </select>
                            @error('discount_type')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nilai Diskon</label>
                            <input type="number" name="discount_value" id="discount_value" min="0" value="{{ old('discount_value') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent @error('discount_value') border-red-500 @enderror" placeholder="0">
                            @error('discount_value')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- GAMBAR --}}
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Gambar</h2>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Upload Gambar <span class="text-red-500">*</span></label>
                    <input type="file" name="images[]" multiple accept="image/*" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent @error('images.*') border-red-500 @enderror">
                    <p class="text-xs text-gray-500 mt-1">Format: JPG, PNG (Max: 5MB per file, minimal 1 gambar)</p>
                    @error('images.*')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            {{-- PEMATERI (SPEAKERS) --}}
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">Pemateri</h2>
                        <p class="text-sm text-gray-500 mt-0.5">Tambahkan satu atau lebih pemateri untuk kegiatan ini</p>
                    </div>
                    <button type="button" id="add-speaker-btn"
                        class="inline-flex items-center px-3 py-2 border border-green-600 text-green-600 rounded-lg text-sm font-medium hover:bg-green-50 transition-colors">
                        <i class="fas fa-plus mr-2"></i>Tambah Pemateri
                    </button>
                </div>

                <div id="speakers-container" class="space-y-3">
                    @if(old('speakers'))
                        @foreach(old('speakers') as $i => $speaker)
                        <div class="speaker-item flex gap-3 items-start p-4 bg-gray-50 rounded-lg border border-gray-200">
                            <div class="flex-1 grid grid-cols-1 md:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Nama Pemateri <span class="text-red-500">*</span></label>
                                    <input type="text" name="speakers[{{ $i }}][name]" value="{{ $speaker['name'] ?? '' }}"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                        placeholder="Nama lengkap pemateri">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Jabatan / Profesi</label>
                                    <input type="text" name="speakers[{{ $i }}][title]" value="{{ $speaker['title'] ?? '' }}"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                        placeholder="Contoh: CEO di Perusahaan X">
                                </div>
                            </div>
                            <button type="button" onclick="removeSpeaker(this)"
                                class="mt-6 p-2 text-red-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors flex-shrink-0" title="Hapus pemateri">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                        @endforeach
                    @else
                        {{-- Default: satu baris kosong --}}
                        <div class="speaker-item flex gap-3 items-start p-4 bg-gray-50 rounded-lg border border-gray-200">
                            <div class="flex-1 grid grid-cols-1 md:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Nama Pemateri <span class="text-red-500">*</span></label>
                                    <input type="text" name="speakers[0][name]"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                        placeholder="Nama lengkap pemateri">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Jabatan / Profesi</label>
                                    <input type="text" name="speakers[0][title]"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                        placeholder="Contoh: CEO di Perusahaan X">
                                </div>
                            </div>
                            <button type="button" onclick="removeSpeaker(this)"
                                class="mt-6 p-2 text-red-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors flex-shrink-0" title="Hapus pemateri">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                    @endif
                </div>

                @error('speakers.*.name')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- BENEFIT --}}
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">Benefit</h2>
                        <p class="text-sm text-gray-500 mt-0.5">Apa yang akan didapatkan peserta dari kegiatan ini?</p>
                    </div>
                    <button type="button" id="add-benefit-btn"
                        class="inline-flex items-center px-3 py-2 border border-green-600 text-green-600 rounded-lg text-sm font-medium hover:bg-green-50 transition-colors">
                        <i class="fas fa-plus mr-2"></i>Tambah Benefit
                    </button>
                </div>

                <div id="benefits-container" class="space-y-2">
                    @if(old('benefits'))
                        @foreach(old('benefits') as $i => $benefit)
                        <div class="benefit-item flex gap-2 items-center">
                            <span class="text-green-500 flex-shrink-0"><i class="fas fa-check-circle"></i></span>
                            <input type="text" name="benefits[{{ $i }}]" value="{{ $benefit }}"
                                class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                placeholder="Contoh: E-sertifikat">
                            <button type="button" onclick="removeBenefit(this)"
                                class="p-2 text-red-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors flex-shrink-0" title="Hapus benefit">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        @endforeach
                    @else
                        {{-- Default: tiga baris kosong --}}
                        @foreach(['E-sertifikat', 'Ilmu dan insight', 'Networking'] as $i => $placeholder)
                        <div class="benefit-item flex gap-2 items-center">
                            <span class="text-green-500 flex-shrink-0"><i class="fas fa-check-circle"></i></span>
                            <input type="text" name="benefits[{{ $i }}]"
                                class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                placeholder="{{ $placeholder }}">
                            <button type="button" onclick="removeBenefit(this)"
                                class="p-2 text-red-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors flex-shrink-0" title="Hapus benefit">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        @endforeach
                    @endif
                </div>

                @error('benefits.*')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- STATUS --}}
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Status</h2>
                <div class="flex items-center">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded">
                    <label class="ml-2 text-sm text-gray-700">Aktifkan kegiatan ini</label>
                </div>
            </div>

            <div class="flex justify-end space-x-4">
                <a href="{{ route('provider.event.activities.index') }}" class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 font-medium transition-colors">Batal</a>
                <button type="submit" class="px-6 py-3 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition-colors">
                    <i class="fas fa-save mr-2"></i>Simpan Kegiatan
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
// ── Discount toggle ──────────────────────────────────────────────
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

// ── Speakers ────────────────────────────────────────────────────
let speakerIndex = {{ old('speakers') ? count(old('speakers')) : 1 }};

document.getElementById('add-speaker-btn').addEventListener('click', function () {
    const container = document.getElementById('speakers-container');
    const html = `
        <div class="speaker-item flex gap-3 items-start p-4 bg-gray-50 rounded-lg border border-gray-200">
            <div class="flex-1 grid grid-cols-1 md:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Nama Pemateri <span class="text-red-500">*</span></label>
                    <input type="text" name="speakers[${speakerIndex}][name]"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:border-transparent"
                        placeholder="Nama lengkap pemateri">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Jabatan / Profesi</label>
                    <input type="text" name="speakers[${speakerIndex}][title]"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:border-transparent"
                        placeholder="Contoh: CEO di Perusahaan X">
                </div>
            </div>
            <button type="button" onclick="removeSpeaker(this)"
                class="mt-6 p-2 text-red-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors flex-shrink-0" title="Hapus pemateri">
                <i class="fas fa-trash-alt"></i>
            </button>
        </div>`;
    container.insertAdjacentHTML('beforeend', html);
    speakerIndex++;
});

function removeSpeaker(btn) {
    const items = document.querySelectorAll('.speaker-item');
    if (items.length <= 1) {
        // Kosongkan saja, jangan hapus baris terakhir
        btn.closest('.speaker-item').querySelectorAll('input').forEach(i => i.value = '');
        return;
    }
    btn.closest('.speaker-item').remove();
}

// ── Benefits ─────────────────────────────────────────────────────
let benefitIndex = {{ old('benefits') ? count(old('benefits')) : 3 }};

document.getElementById('add-benefit-btn').addEventListener('click', function () {
    const container = document.getElementById('benefits-container');
    const html = `
        <div class="benefit-item flex gap-2 items-center">
            <span class="text-green-500 flex-shrink-0"><i class="fas fa-check-circle"></i></span>
            <input type="text" name="benefits[${benefitIndex}]"
                class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:border-transparent"
                placeholder="Tambahkan benefit...">
            <button type="button" onclick="removeBenefit(this)"
                class="p-2 text-red-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors flex-shrink-0" title="Hapus benefit">
                <i class="fas fa-times"></i>
            </button>
        </div>`;
    container.insertAdjacentHTML('beforeend', html);
    benefitIndex++;
    // Auto-focus ke input baru
    container.lastElementChild.querySelector('input').focus();
});

function removeBenefit(btn) {
    const items = document.querySelectorAll('.benefit-item');
    if (items.length <= 1) {
        btn.closest('.benefit-item').querySelector('input').value = '';
        return;
    }
    btn.closest('.benefit-item').remove();
}
</script>
@endpush
@endsection
