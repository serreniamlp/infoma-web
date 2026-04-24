@extends('layouts.app')

@section('title', 'Tambah Hunian - Infoma')

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder@1.13.0/dist/Control.Geocoder.css" />
    <link rel="stylesheet" href="{{ asset('css/leaflet-maps.css') }}">
    <style>
        .image-preview-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
            gap: 12px
        }

        .image-preview-item {
            position: relative;
            aspect-ratio: 1;
            border-radius: 8px;
            overflow: hidden;
            border: 2px solid #e5e7eb
        }

        .image-preview-item img {
            width: 100%;
            height: 100%;
            object-fit: cover
        }

        .image-preview-item .remove-btn {
            position: absolute;
            top: 4px;
            right: 4px;
            background: rgba(239, 68, 68, .9);
            color: #fff;
            border: none;
            border-radius: 50%;
            width: 22px;
            height: 22px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 11px
        }

        .image-preview-item .badge-utama {
            position: absolute;
            bottom: 4px;
            left: 4px;
            background: #2563eb;
            color: #fff;
            font-size: 10px;
            padding: 2px 6px;
            border-radius: 4px;
            font-weight: 600
        }

        .upload-zone {
            border: 2px dashed #d1d5db;
            border-radius: 12px;
            padding: 32px;
            text-align: center;
            cursor: pointer;
            transition: all .2s;
            background: #f9fafb
        }

        .upload-zone:hover,
        .upload-zone.drag-over {
            border-color: #2563eb;
            background: #eff6ff
        }

        .step-dot {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 700
        }
    </style>
@endpush

@section('content')
    <div class="min-h-screen bg-gray-50 py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Header --}}
            <div class="mb-8">
                <nav class="flex items-center gap-2 text-sm text-gray-500 mb-3">
                    <a href="{{ route('provider.residence.dashboard') }}" class="hover:text-blue-600">Dashboard</a>
                    <i class="fas fa-chevron-right text-xs"></i>
                    <a href="{{ route('provider.residence.residences.index') }}" class="hover:text-blue-600">Hunian Saya</a>
                    <i class="fas fa-chevron-right text-xs"></i>
                    <span class="text-gray-900">Tambah Hunian</span>
                </nav>
                <h1 class="text-2xl font-bold text-gray-900">Tambah Hunian Baru</h1>
                <p class="text-gray-500 mt-1 text-sm">Isi informasi hunian yang akan ditawarkan kepada mahasiswa</p>
            </div>

            @if ($errors->any())
                <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
                    <div class="flex items-start gap-3">
                        <i class="fas fa-exclamation-circle text-red-500 mt-0.5"></i>
                        <div>
                            <p class="font-semibold text-red-700 text-sm">Mohon periksa kembali formulir:</p>
                            <ul class="mt-1 space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li class="text-red-600 text-sm">• {{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('provider.residence.residences.store') }}" enctype="multipart/form-data"
                class="space-y-6" id="hunianForm">
                @csrf

                {{-- 1. INFORMASI DASAR --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                        <h2 class="font-semibold text-gray-800 flex items-center gap-2">
                            <span class="step-dot bg-blue-600 text-white">1</span>
                            Informasi Dasar
                        </h2>
                    </div>
                    <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Nama Hunian <span
                                    class="text-red-500">*</span></label>
                            <input type="text" name="name" value="{{ old('name') }}" required
                                class="w-full px-4 py-2.5 border rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('name') border-red-400 bg-red-50 @else border-gray-300 @enderror"
                                placeholder="Contoh: Kos Putri Melati, Kontrakan 3 Kamar...">
                            @error('name')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Deskripsi <span
                                    class="text-red-500">*</span></label>
                            <textarea name="description" rows="4" required
                                class="w-full px-4 py-2.5 border rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('description') border-red-400 bg-red-50 @else border-gray-300 @enderror"
                                placeholder="Ceritakan keunggulan dan keunikan hunian Anda...">{{ old('description') }}</textarea>
                            @error('description')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Kategori <span
                                    class="text-red-500">*</span></label>
                            <select name="category_id" required
                                class="w-full px-4 py-2.5 border rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('category_id') border-red-400 bg-red-50 @else border-gray-300 @enderror">
                                <option value="">-- Pilih Kategori --</option>
                                @foreach ($categories as $cat)
                                    <option value="{{ $cat->id }}"
                                        {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category_id')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Periode Sewa <span
                                    class="text-red-500">*</span></label>
                            <select name="rental_period" required
                                class="w-full px-4 py-2.5 border rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('rental_period') border-red-400 @else border-gray-300 @enderror">
                                <option value="">-- Pilih Periode --</option>
                                <option value="monthly" {{ old('rental_period') === 'monthly' ? 'selected' : '' }}>Bulanan
                                </option>
                                <option value="yearly" {{ old('rental_period') === 'yearly' ? 'selected' : '' }}>Tahunan
                                </option>
                            </select>
                            @error('rental_period')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- 2. LOKASI --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                        <h2 class="font-semibold text-gray-800 flex items-center gap-2">
                            <span class="step-dot bg-blue-600 text-white">2</span>
                            Lokasi Hunian
                        </h2>
                    </div>
                    <div class="p-6 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Alamat Lengkap <span
                                    class="text-red-500">*</span></label>
                            <textarea name="address" rows="3" required
                                class="w-full px-4 py-2.5 border rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('address') border-red-400 bg-red-50 @else border-gray-300 @enderror"
                                placeholder="Jalan, nomor, RT/RW, kelurahan, kecamatan, kota...">{{ old('address') }}</textarea>
                            @error('address')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
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
                                    <label>Latitude</label>
                                    <input type="number" name="latitude" id="latitude" step="any"
                                        value="{{ old('latitude') }}" placeholder="0.000000">
                                </div>
                                <div class="form-group">
                                    <label>Longitude</label>
                                    <input type="number" name="longitude" id="longitude" step="any"
                                        value="{{ old('longitude') }}" placeholder="0.000000">
                                </div>
                            </div>
                            <div class="map-info">
                                <h4><i class="fas fa-info-circle mr-2"></i>Cara Menggunakan Peta</h4>
                                <p>• Klik pada peta untuk memilih lokasi<br>
                                    • Gunakan kotak pencarian untuk mencari alamat<br>
                                    • Koordinat akan terisi otomatis</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 3. HARGA & KAPASITAS --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                        <h2 class="font-semibold text-gray-800 flex items-center gap-2">
                            <span class="step-dot bg-blue-600 text-white">3</span>
                            Harga &amp; Kapasitas
                        </h2>
                    </div>
                    <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                Harga Sewa (Rp) <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <span
                                    class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm font-medium">Rp</span>
                                <input type="number" name="price_per_month" required min="0"
                                    value="{{ old('price_per_month') }}"
                                    class="w-full pl-10 pr-4 py-2.5 border rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('price_per_month') border-red-400 bg-red-50 @else border-gray-300 @enderror"
                                    placeholder="1500000">
                            </div>
                            @error('price_per_month')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                Kapasitas (orang) <span class="text-red-500">*</span>
                            </label>
                            <input type="number" name="capacity" required min="1" value="{{ old('capacity') }}"
                                class="w-full px-4 py-2.5 border rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('capacity') border-red-400 bg-red-50 @else border-gray-300 @enderror"
                                placeholder="1">
                            @error('capacity')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                Jenis Diskon <span class="text-gray-400 font-normal">(Opsional)</span>
                            </label>
                            <select name="discount_type" id="discount_type"
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="">Tidak ada diskon</option>
                                <option value="percentage" {{ old('discount_type') === 'percentage' ? 'selected' : '' }}>
                                    Persentase (%)</option>
                                <option value="flat" {{ old('discount_type') === 'flat' ? 'selected' : '' }}>
                                    Nominal (Rp)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5" id="discount_label">Nilai
                                Diskon</label>
                            <input type="number" name="discount_value" id="discount_value" min="0"
                                value="{{ old('discount_value') }}"
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="0">
                        </div>
                    </div>
                </div>

                {{-- 4. FASILITAS --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                        <h2 class="font-semibold text-gray-800 flex items-center gap-2">
                            <span class="step-dot bg-blue-600 text-white">4</span>
                            Fasilitas
                        </h2>
                    </div>
                    <div class="p-6">
                        @error('facilities')
                            <p class="mb-3 text-sm text-red-600 bg-red-50 border border-red-200 rounded-lg px-4 py-2">
                                <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                            </p>
                        @enderror

                        @php
                            $commonFacilities = [
                                'AC',
                                'WiFi',
                                'Kamar Mandi Dalam',
                                'Lemari',
                                'Meja Belajar',
                                'Kursi',
                                'Kasur',
                                'Bantal',
                                'Selimut',
                                'Dapur',
                                'Kulkas',
                                'Mesin Cuci',
                                'Parkir Motor',
                                'Parkir Mobil',
                                'Security 24 Jam',
                                'CCTV',
                            ];
                            $facilityIcons = [
                                'AC' => 'fa-wind',
                                'WiFi' => 'fa-wifi',
                                'Kamar Mandi Dalam' => 'fa-bath',
                                'Lemari' => 'fa-box',
                                'Meja Belajar' => 'fa-chair',
                                'Kursi' => 'fa-chair',
                                'Kasur' => 'fa-bed',
                                'Bantal' => 'fa-bed',
                                'Selimut' => 'fa-bed',
                                'Dapur' => 'fa-utensils',
                                'Kulkas' => 'fa-snowflake',
                                'Mesin Cuci' => 'fa-tshirt',
                                'Parkir Motor' => 'fa-motorcycle',
                                'Parkir Mobil' => 'fa-car',
                                'Security 24 Jam' => 'fa-shield-alt',
                                'CCTV' => 'fa-video',
                            ];
                        @endphp

                        <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                            @foreach ($commonFacilities as $facility)
                                <label
                                    class="flex items-center gap-2.5 px-3 py-2.5 rounded-lg border border-gray-200 cursor-pointer hover:border-blue-400 transition-colors">
                                    <input type="checkbox" name="facilities[]" value="{{ $facility }}"
                                        {{ in_array($facility, old('facilities', [])) ? 'checked' : '' }}
                                        class="h-4 w-4 text-blue-600 rounded border-gray-300">
                                    <i
                                        class="fas {{ $facilityIcons[$facility] ?? 'fa-check' }} text-gray-400 text-xs w-3"></i>
                                    <span class="text-sm text-gray-700">{{ $facility }}</span>
                                </label>
                            @endforeach
                        </div>

                        {{-- Input fasilitas tambahan yang sebelumnya tidak berfungsi --}}
                        <div class="mt-5 p-4 bg-gray-50 rounded-lg border border-gray-200">
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                <i class="fas fa-plus-circle text-blue-500 mr-1"></i>
                                Fasilitas Tambahan
                                <span class="text-gray-400 font-normal">(pisahkan dengan koma)</span>
                            </label>
                            <input type="text" name="custom_facilities" id="custom_facilities"
                                value="{{ old('custom_facilities') }}"
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="Contoh: Taman, Kolam Renang, Ruang Tamu Bersama">
                            <p class="text-xs text-gray-400 mt-1.5">
                                Fasilitas yang belum ada di daftar atas bisa ditambahkan di sini.
                            </p>
                            {{-- Tag preview muncul real-time saat mengetik --}}
                            <div id="customFacilityTags" class="flex flex-wrap gap-2 mt-3"></div>
                        </div>
                    </div>
                </div>

                {{-- 5. FOTO HUNIAN --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                        <h2 class="font-semibold text-gray-800 flex items-center gap-2">
                            <span class="step-dot bg-blue-600 text-white">5</span>
                            Foto Hunian
                            <span class="text-xs font-normal text-gray-500 ml-1">(Min. 1, Maks. 10 foto)</span>
                        </h2>
                    </div>
                    <div class="p-6">
                        @error('images')
                            <p class="mb-3 text-sm text-red-600 bg-red-50 border border-red-200 rounded-lg px-4 py-2">
                                <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                            </p>
                        @enderror

                        {{-- Area drag & drop / klik untuk pilih foto --}}
                        <div class="upload-zone" id="uploadZone" onclick="document.getElementById('images').click()">
                            <i class="fas fa-cloud-upload-alt text-3xl text-gray-300 mb-3"></i>
                            <p class="font-medium text-gray-600 text-sm">Klik atau seret foto ke sini</p>
                            <p class="text-xs text-gray-400 mt-1">Format: JPG, PNG &middot; Maks. 5MB per file</p>
                        </div>

                        <input type="file" name="images[]" id="images" multiple accept="image/*" class="hidden"
                            required>

                        {{-- Grid preview foto yang dipilih --}}
                        <div class="image-preview-grid mt-4" id="imagePreviewGrid"></div>

                        <p class="text-xs text-gray-400 mt-3">
                            <i class="fas fa-info-circle mr-1"></i>
                            Foto pertama akan menjadi foto utama yang ditampilkan kepada mahasiswa.
                        </p>
                    </div>
                </div>

                {{-- 6. STATUS TAYANG --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="font-semibold text-gray-800">Status Tayang</h2>
                            <p class="text-sm text-gray-500 mt-0.5">
                                Aktifkan agar hunian bisa dilihat dan dipesan mahasiswa
                            </p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="is_active" id="is_active" value="1"
                                {{ old('is_active', true) ? 'checked' : '' }} class="sr-only peer">
                            <div
                                class="w-11 h-6 bg-gray-200 peer-focus:ring-2 peer-focus:ring-blue-300 rounded-full peer
                        peer-checked:after:translate-x-full after:absolute after:top-0.5 after:left-[2px]
                        after:bg-white after:border after:rounded-full after:h-5 after:w-5
                        after:transition-all peer-checked:bg-blue-600">
                            </div>
                            <span class="ml-3 text-sm font-medium text-gray-700" id="statusLabel">Aktif</span>
                        </label>
                    </div>
                </div>

                {{-- TOMBOL AKSI --}}
                <div class="flex items-center justify-between pt-2 pb-6">
                    <a href="{{ route('provider.residence.residences.index') }}"
                        class="px-6 py-2.5 border border-gray-300 rounded-lg text-gray-700 text-sm hover:bg-gray-50 font-medium transition-colors">
                        <i class="fas fa-arrow-left mr-2"></i>Batal
                    </a>
                    <button type="submit"
                        class="px-8 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold text-sm transition-colors flex items-center gap-2 shadow-sm">
                        <i class="fas fa-save"></i>Simpan Hunian
                    </button>
                </div>

            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet-control-geocoder@1.13.0/dist/Control.Geocoder.js"></script>
    <script src="{{ asset('js/leaflet-maps.js') }}"></script>
    <script>
        // ── FITUR: Preview multi-foto sebelum upload ──────────────────
        // selectedFiles = array file yang dipilih user
        let selectedFiles = [];
        const imageInput = document.getElementById('images');
        const previewGrid = document.getElementById('imagePreviewGrid');
        const uploadZone = document.getElementById('uploadZone');

        // Ketika user memilih file lewat dialog
        imageInput.addEventListener('change', function() {
            addFiles(Array.from(this.files));
            this.value = ''; // reset agar file yang sama bisa dipilih lagi
        });

        // Drag & drop ke area upload
        uploadZone.addEventListener('dragover', e => {
            e.preventDefault();
            uploadZone.classList.add('drag-over');
        });
        uploadZone.addEventListener('dragleave', () => uploadZone.classList.remove('drag-over'));
        uploadZone.addEventListener('drop', e => {
            e.preventDefault();
            uploadZone.classList.remove('drag-over');
            addFiles(Array.from(e.dataTransfer.files).filter(f => f.type.startsWith('image/')));
        });

        function addFiles(files) {
            files.forEach(file => {
                if (selectedFiles.length >= 10) {
                    alert('Maksimal 10 foto!');
                    return;
                }
                if (file.size > 5 * 1024 * 1024) {
                    alert(`"${file.name}" melebihi 5MB.`);
                    return;
                }
                selectedFiles.push(file);
            });
            renderPreviews();
            syncFilesToInput();
        }

        // Hapus foto dari daftar pilihan
        function removeFile(index) {
            selectedFiles.splice(index, 1);
            renderPreviews();
            syncFilesToInput();
        }

        // Tampilkan grid thumbnail foto yang dipilih
        function renderPreviews() {
            previewGrid.innerHTML = '';
            selectedFiles.forEach((file, i) => {
                const reader = new FileReader();
                reader.onload = e => {
                    const div = document.createElement('div');
                    div.className = 'image-preview-item';
                    div.innerHTML = `
                <img src="${e.target.result}" alt="preview">
                ${i === 0 ? '<span class="badge-utama">Utama</span>' : ''}
                <button type="button" class="remove-btn" onclick="removeFile(${i})">
                    <i class="fas fa-times"></i>
                </button>`;
                    previewGrid.appendChild(div);
                };
                reader.readAsDataURL(file);
            });
            // Wajib isi foto jika belum ada yang dipilih
            imageInput.required = selectedFiles.length === 0;
        }

        // Sinkronkan array ke input file agar ikut terkirim saat submit
        function syncFilesToInput() {
            const dt = new DataTransfer();
            selectedFiles.forEach(f => dt.items.add(f));
            imageInput.files = dt.files;
        }

        // ── FITUR: Preview tag fasilitas tambahan saat mengetik ───────
        document.getElementById('custom_facilities').addEventListener('input', function() {
            const tags = document.getElementById('customFacilityTags');
            const items = this.value.split(',').map(s => s.trim()).filter(Boolean);
            tags.innerHTML = items.map(item =>
                `<span class="inline-flex items-center gap-1 bg-blue-100 text-blue-700 text-xs px-2.5 py-1 rounded-full font-medium">
            <i class="fas fa-check text-xs"></i>${item}
        </span>`
            ).join('');
        });

        // ── FITUR: Label diskon berubah sesuai jenis yang dipilih ─────
        document.getElementById('discount_type').addEventListener('change', function() {
            const val = document.getElementById('discount_value');
            const lbl = document.getElementById('discount_label');
            if (this.value === 'percentage') {
                lbl.textContent = 'Persentase Diskon (%)';
                val.placeholder = '10';
                val.max = '100';
            } else if (this.value === 'flat') {
                lbl.textContent = 'Nominal Diskon (Rp)';
                val.placeholder = '100000';
                val.removeAttribute('max');
            } else {
                lbl.textContent = 'Nilai Diskon';
                val.placeholder = '0';
                val.value = '';
            }
        });

        // ── FITUR: Label "Aktif/Nonaktif" mengikuti toggle ────────────
        document.getElementById('is_active').addEventListener('change', function() {
            document.getElementById('statusLabel').textContent = this.checked ? 'Aktif' : 'Nonaktif';
        });
    </script>
@endpush
