@extends('layouts.app')

@section('title', 'Tambah Hunian - EduLiving')

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder@1.13.0/dist/Control.Geocoder.css" />
    <link rel="stylesheet" href="{{ asset('css/leaflet-maps.css') }}">
    <style>
        .image-preview-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(120px,1fr)); gap:12px }
        .image-preview-item { position:relative; aspect-ratio:1; border-radius:8px; overflow:hidden; border:2px solid #e5e7eb }
        .image-preview-item img { width:100%; height:100%; object-fit:cover }
        .image-preview-item .remove-btn { position:absolute; top:4px; right:4px; background:rgba(239,68,68,.9); color:#fff; border:none; border-radius:50%; width:22px; height:22px; display:flex; align-items:center; justify-content:center; cursor:pointer; font-size:11px }
        .image-preview-item .badge-utama { position:absolute; bottom:4px; left:4px; background:#2563eb; color:#fff; font-size:10px; padding:2px 6px; border-radius:4px; font-weight:600 }
        .upload-zone { border:2px dashed #d1d5db; border-radius:12px; padding:32px; text-align:center; cursor:pointer; transition:all .2s; background:#f9fafb }
        .upload-zone:hover, .upload-zone.drag-over { border-color:#2563eb; background:#eff6ff }
        .step-dot { width:28px; height:28px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:12px; font-weight:700 }
        /* Sembunyikan section spesifik secara default */
        .type-section { display: none }
        .type-section.active { display: block }
        /* Highlight tipe yang dipilih */
        .type-card { cursor:pointer; border:2px solid #e5e7eb; border-radius:12px; padding:16px; text-align:center; transition:all .2s }
        .type-card:hover { border-color:#93c5fd; background:#eff6ff }
        .type-card.selected { border-color:#2563eb; background:#eff6ff }
        .type-card.selected .type-icon { color:#2563eb }
        .type-card.selected .type-label { color:#1d4ed8; font-weight:700 }
        .type-icon { font-size:1.75rem; color:#9ca3af; margin-bottom:6px; transition:color .2s }
        .type-label { font-size:0.8rem; color:#6b7280; transition:color .2s }
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

        <form method="POST" action="{{ route('provider.residence.residences.store') }}"
              enctype="multipart/form-data" class="space-y-6" id="hunianForm">
            @csrf

            {{-- ═══════════════════════════════════════════════════════════ --}}
            {{-- STEP 1: PILIH TIPE HUNIAN (BARU — harus dipilih pertama)   --}}
            {{-- ═══════════════════════════════════════════════════════════ --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                    <h2 class="font-semibold text-gray-800 flex items-center gap-2">
                        <span class="step-dot bg-blue-600 text-white">1</span>
                        Tipe Hunian <span class="text-red-500">*</span>
                    </h2>
                    <p class="text-xs text-gray-500 mt-1 ml-9">Pilih tipe hunian untuk menampilkan form yang sesuai</p>
                </div>
                <div class="p-6">
                    <input type="hidden" name="residence_type" id="residence_type" value="{{ old('residence_type') }}">
                    @error('residence_type')
                        <p class="text-xs text-red-600 mb-3">{{ $message }}</p>
                    @enderror

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">

                        {{-- KOS --}}
                        <div class="type-card {{ old('residence_type') === 'kos' ? 'selected' : '' }}"
                             data-type="kos" onclick="selectType('kos')">
                            <div class="type-icon"><i class="fas fa-door-open"></i></div>
                            <div class="type-label font-semibold text-gray-700 text-sm">Kos</div>
                            <div class="text-xs text-gray-400 mt-1">Per kamar<br>1 orang/kamar</div>
                        </div>

                        {{-- KONTRAKAN --}}
                        <div class="type-card {{ old('residence_type') === 'kontrakan' ? 'selected' : '' }}"
                             data-type="kontrakan" onclick="selectType('kontrakan')">
                            <div class="type-icon"><i class="fas fa-home"></i></div>
                            <div class="type-label font-semibold text-gray-700 text-sm">Kontrakan</div>
                            <div class="text-xs text-gray-400 mt-1">Per unit rumah<br>Keluarga/grup</div>
                        </div>

                        {{-- APARTEMEN --}}
                        <div class="type-card {{ old('residence_type') === 'apartemen' ? 'selected' : '' }}"
                             data-type="apartemen" onclick="selectType('apartemen')">
                            <div class="type-icon"><i class="fas fa-building"></i></div>
                            <div class="type-label font-semibold text-gray-700 text-sm">Apartemen</div>
                            <div class="text-xs text-gray-400 mt-1">Per unit gedung<br>Studio/1BR/2BR</div>
                        </div>

                        {{-- RUMAH SEWA --}}
                        <div class="type-card {{ old('residence_type') === 'rumah_sewa' ? 'selected' : '' }}"
                             data-type="rumah_sewa" onclick="selectType('rumah_sewa')">
                            <div class="type-icon"><i class="fas fa-house-user"></i></div>
                            <div class="type-label font-semibold text-gray-700 text-sm">Rumah Sewa</div>
                            <div class="text-xs text-gray-400 mt-1">Per rumah<br>Kapasitas bebas</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ═══════════════════════════════════════════════════════════ --}}
            {{-- STEP 2: INFORMASI DASAR                                     --}}
            {{-- ═══════════════════════════════════════════════════════════ --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                    <h2 class="font-semibold text-gray-800 flex items-center gap-2">
                        <span class="step-dot bg-blue-600 text-white">2</span>
                        Informasi Dasar
                    </h2>
                </div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Nama Hunian <span class="text-red-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name') }}" required
                               class="w-full px-4 py-2.5 border rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('name') border-red-400 bg-red-50 @else border-gray-300 @enderror"
                               placeholder="Contoh: Kos Putri Melati, Kontrakan 3 Kamar Pak Budi...">
                        @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Deskripsi <span class="text-red-500">*</span></label>
                        <textarea name="description" rows="4" required
                                  class="w-full px-4 py-2.5 border rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('description') border-red-400 bg-red-50 @else border-gray-300 @enderror"
                                  placeholder="Ceritakan keunggulan dan keunikan hunian kamu...">{{ old('description') }}</textarea>
                        @error('description')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Kategori <span class="text-red-500">*</span></label>
                        <select name="category_id" required
                                class="w-full px-4 py-2.5 border rounded-lg text-sm focus:ring-2 focus:ring-blue-500 @error('category_id') border-red-400 @else border-gray-300 @enderror">
                            <option value="">-- Pilih Kategori --</option>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('category_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Periode Sewa <span class="text-red-500">*</span></label>
                        <select name="rental_period" required
                                class="w-full px-4 py-2.5 border rounded-lg text-sm focus:ring-2 focus:ring-blue-500 @error('rental_period') border-red-400 @else border-gray-300 @enderror">
                            <option value="">-- Pilih Periode --</option>
                            <option value="monthly" {{ old('rental_period') === 'monthly' ? 'selected' : '' }}>Bulanan</option>
                            <option value="yearly"  {{ old('rental_period') === 'yearly'  ? 'selected' : '' }}>Tahunan</option>
                        </select>
                        @error('rental_period')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Status Furnitur</label>
                        <select name="furnish_status"
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
                            <option value="">-- Pilih Status --</option>
                            <option value="unfurnished"    {{ old('furnish_status') === 'unfurnished'    ? 'selected' : '' }}>Unfurnished</option>
                            <option value="semi_furnished" {{ old('furnish_status') === 'semi_furnished' ? 'selected' : '' }}>Semi Furnished</option>
                            <option value="full_furnished" {{ old('furnish_status') === 'full_furnished' ? 'selected' : '' }}>Full Furnished</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- ═══════════════════════════════════════════════════════════ --}}
            {{-- STEP 3: DETAIL SPESIFIK PER TIPE (muncul sesuai pilihan)   --}}
            {{-- ═══════════════════════════════════════════════════════════ --}}

            {{-- ── KHUSUS KOS ─────────────────────────────────────────── --}}
            <div id="section-kos" class="type-section {{ old('residence_type') === 'kos' ? 'active' : '' }}
                 bg-white rounded-xl shadow-sm border border-blue-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-blue-100 bg-blue-50">
                    <h2 class="font-semibold text-blue-800 flex items-center gap-2">
                        <span class="step-dot bg-blue-600 text-white">3</span>
                        <i class="fas fa-door-open mr-1"></i> Detail Kos
                    </h2>
                    <p class="text-xs text-blue-600 mt-1 ml-9">Informasi spesifik untuk hunian tipe kos</p>
                </div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Jenis Kos <span class="text-red-500">*</span></label>
                        <select name="kos_type"
                                class="w-full px-4 py-2.5 border rounded-lg text-sm focus:ring-2 focus:ring-blue-500 @error('kos_type') border-red-400 @else border-gray-300 @enderror">
                            <option value="">-- Pilih Jenis --</option>
                            <option value="putra"  {{ old('kos_type') === 'putra'  ? 'selected' : '' }}>Putra</option>
                            <option value="putri"  {{ old('kos_type') === 'putri'  ? 'selected' : '' }}>Putri</option>
                            <option value="campur" {{ old('kos_type') === 'campur' ? 'selected' : '' }}>Campur</option>
                        </select>
                        @error('kos_type')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        <p class="text-xs text-gray-400 mt-1">Jenis penghuni yang diperbolehkan</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Jumlah Kamar Tersedia <span class="text-red-500">*</span></label>
                        <input type="number" name="capacity" min="1" value="{{ old('capacity') }}"
                               class="w-full px-4 py-2.5 border rounded-lg text-sm focus:ring-2 focus:ring-blue-500 @error('capacity') border-red-400 @else border-gray-300 @enderror"
                               placeholder="10">
                        @error('capacity')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        <p class="text-xs text-gray-400 mt-1">Total kamar yang bisa disewa</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Ukuran Kamar (m²)</label>
                        <input type="number" name="room_size" min="1" step="0.1" value="{{ old('room_size') }}"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500"
                               placeholder="3 x 4 = 12">
                        <p class="text-xs text-gray-400 mt-1">Luas per kamar dalam m²</p>
                    </div>
                </div>
            </div>

            {{-- ── KHUSUS KONTRAKAN ────────────────────────────────────── --}}
            <div id="section-kontrakan" class="type-section {{ old('residence_type') === 'kontrakan' ? 'active' : '' }}
                 bg-white rounded-xl shadow-sm border border-green-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-green-100 bg-green-50">
                    <h2 class="font-semibold text-green-800 flex items-center gap-2">
                        <span class="step-dot bg-green-600 text-white">3</span>
                        <i class="fas fa-home mr-1"></i> Detail Kontrakan
                    </h2>
                    <p class="text-xs text-green-600 mt-1 ml-9">Informasi spesifik untuk hunian tipe kontrakan</p>
                </div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Jumlah Unit Tersedia <span class="text-red-500">*</span></label>
                        <input type="number" name="capacity" min="1" value="{{ old('capacity') }}"
                               class="w-full px-4 py-2.5 border rounded-lg text-sm focus:ring-2 focus:ring-blue-500 @error('capacity') border-red-400 @else border-gray-300 @enderror"
                               placeholder="1">
                        @error('capacity')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        <p class="text-xs text-gray-400 mt-1">Berapa unit/rumah yang bisa disewa</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Jumlah Kamar Tidur <span class="text-red-500">*</span></label>
                        <input type="number" name="bedroom_count" min="1" max="20" value="{{ old('bedroom_count') }}"
                               class="w-full px-4 py-2.5 border rounded-lg text-sm focus:ring-2 focus:ring-blue-500 @error('bedroom_count') border-red-400 @else border-gray-300 @enderror"
                               placeholder="3">
                        @error('bedroom_count')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Jumlah Kamar Mandi <span class="text-red-500">*</span></label>
                        <input type="number" name="bathroom_count" min="1" max="20" value="{{ old('bathroom_count') }}"
                               class="w-full px-4 py-2.5 border rounded-lg text-sm focus:ring-2 focus:ring-blue-500 @error('bathroom_count') border-red-400 @else border-gray-300 @enderror"
                               placeholder="2">
                        @error('bathroom_count')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Luas Bangunan (m²)</label>
                        <input type="number" name="building_size" min="1" step="0.1" value="{{ old('building_size') }}"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500"
                               placeholder="72">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Luas Tanah (m²)</label>
                        <input type="number" name="land_size" min="1" step="0.1" value="{{ old('land_size') }}"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500"
                               placeholder="90">
                    </div>
                </div>
            </div>

            {{-- ── KHUSUS APARTEMEN ────────────────────────────────────── --}}
            <div id="section-apartemen" class="type-section {{ old('residence_type') === 'apartemen' ? 'active' : '' }}
                 bg-white rounded-xl shadow-sm border border-purple-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-purple-100 bg-purple-50">
                    <h2 class="font-semibold text-purple-800 flex items-center gap-2">
                        <span class="step-dot bg-purple-600 text-white">3</span>
                        <i class="fas fa-building mr-1"></i> Detail Apartemen
                    </h2>
                    <p class="text-xs text-purple-600 mt-1 ml-9">Informasi spesifik untuk hunian tipe apartemen</p>
                </div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Tipe Unit <span class="text-red-500">*</span></label>
                        <select name="unit_type"
                                class="w-full px-4 py-2.5 border rounded-lg text-sm focus:ring-2 focus:ring-blue-500 @error('unit_type') border-red-400 @else border-gray-300 @enderror">
                            <option value="">-- Pilih Tipe --</option>
                            <option value="studio" {{ old('unit_type') === 'studio' ? 'selected' : '' }}>Studio</option>
                            <option value="1BR"    {{ old('unit_type') === '1BR'    ? 'selected' : '' }}>1 Bedroom (1BR)</option>
                            <option value="2BR"    {{ old('unit_type') === '2BR'    ? 'selected' : '' }}>2 Bedroom (2BR)</option>
                            <option value="3BR"    {{ old('unit_type') === '3BR'    ? 'selected' : '' }}>3 Bedroom (3BR)</option>
                            <option value="4BR"    {{ old('unit_type') === '4BR'    ? 'selected' : '' }}>4 Bedroom (4BR)</option>
                        </select>
                        @error('unit_type')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Jumlah Unit Tersedia <span class="text-red-500">*</span></label>
                        <input type="number" name="capacity" min="1" value="{{ old('capacity') }}"
                               class="w-full px-4 py-2.5 border rounded-lg text-sm focus:ring-2 focus:ring-blue-500 @error('capacity') border-red-400 @else border-gray-300 @enderror"
                               placeholder="1">
                        @error('capacity')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Lantai <span class="text-red-500">*</span></label>
                        <input type="number" name="floor_number" min="1" max="200" value="{{ old('floor_number') }}"
                               class="w-full px-4 py-2.5 border rounded-lg text-sm focus:ring-2 focus:ring-blue-500 @error('floor_number') border-red-400 @else border-gray-300 @enderror"
                               placeholder="5">
                        @error('floor_number')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Nama Tower/Gedung</label>
                        <input type="text" name="tower_name" value="{{ old('tower_name') }}"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500"
                               placeholder="Tower A">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Luas Unit (m²)</label>
                        <input type="number" name="room_size" min="1" step="0.1" value="{{ old('room_size') }}"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500"
                               placeholder="28">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Kamar Mandi</label>
                        <input type="number" name="bathroom_count" min="1" max="10" value="{{ old('bathroom_count') }}"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500"
                               placeholder="1">
                    </div>
                </div>
            </div>

            {{-- ── KHUSUS RUMAH SEWA ───────────────────────────────────── --}}
            <div id="section-rumah_sewa" class="type-section {{ old('residence_type') === 'rumah_sewa' ? 'active' : '' }}
                 bg-white rounded-xl shadow-sm border border-orange-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-orange-100 bg-orange-50">
                    <h2 class="font-semibold text-orange-800 flex items-center gap-2">
                        <span class="step-dot bg-orange-500 text-white">3</span>
                        <i class="fas fa-house-user mr-1"></i> Detail Rumah Sewa
                    </h2>
                    <p class="text-xs text-orange-600 mt-1 ml-9">Informasi spesifik untuk hunian tipe rumah sewa</p>
                </div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Jumlah Unit Tersedia <span class="text-red-500">*</span></label>
                        <input type="number" name="capacity" min="1" value="{{ old('capacity') }}"
                               class="w-full px-4 py-2.5 border rounded-lg text-sm focus:ring-2 focus:ring-blue-500 @error('capacity') border-red-400 @else border-gray-300 @enderror"
                               placeholder="1">
                        @error('capacity')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Jumlah Kamar Tidur <span class="text-red-500">*</span></label>
                        <input type="number" name="bedroom_count" min="1" max="20" value="{{ old('bedroom_count') }}"
                               class="w-full px-4 py-2.5 border rounded-lg text-sm focus:ring-2 focus:ring-blue-500 @error('bedroom_count') border-red-400 @else border-gray-300 @enderror"
                               placeholder="3">
                        @error('bedroom_count')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Jumlah Kamar Mandi <span class="text-red-500">*</span></label>
                        <input type="number" name="bathroom_count" min="1" max="20" value="{{ old('bathroom_count') }}"
                               class="w-full px-4 py-2.5 border rounded-lg text-sm focus:ring-2 focus:ring-blue-500 @error('bathroom_count') border-red-400 @else border-gray-300 @enderror"
                               placeholder="2">
                        @error('bathroom_count')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Luas Bangunan (m²)</label>
                        <input type="number" name="building_size" min="1" step="0.1" value="{{ old('building_size') }}"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500"
                               placeholder="80">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Luas Tanah (m²)</label>
                        <input type="number" name="land_size" min="1" step="0.1" value="{{ old('land_size') }}"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500"
                               placeholder="120">
                    </div>
                </div>
            </div>

            {{-- ═══════════════════════════════════════════════════════════ --}}
            {{-- STEP 4: LOKASI                                              --}}
            {{-- ═══════════════════════════════════════════════════════════ --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                    <h2 class="font-semibold text-gray-800 flex items-center gap-2">
                        <span class="step-dot bg-blue-600 text-white">4</span>
                        Lokasi Hunian
                    </h2>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Alamat Lengkap <span class="text-red-500">*</span></label>
                        <textarea name="address" id="address" rows="3" required
                                  class="w-full px-4 py-2.5 border rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('address') border-red-400 bg-red-50 @else border-gray-300 @enderror"
                                  placeholder="Jalan, nomor, RT/RW, kelurahan, kecamatan, kota...">{{ old('address') }}</textarea>
                        @error('address')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
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
                                <input type="number" name="latitude" id="latitude" step="any" value="{{ old('latitude') }}" placeholder="0.000000">
                            </div>
                            <div class="form-group">
                                <label>Longitude</label>
                                <input type="number" name="longitude" id="longitude" step="any" value="{{ old('longitude') }}" placeholder="0.000000">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ═══════════════════════════════════════════════════════════ --}}
            {{-- STEP 5: HARGA & DISKON                                      --}}
            {{-- ═══════════════════════════════════════════════════════════ --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                    <h2 class="font-semibold text-gray-800 flex items-center gap-2">
                        <span class="step-dot bg-blue-600 text-white">5</span>
                        Harga &amp; Diskon
                    </h2>
                </div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5" id="priceLabel">
                            Harga Sewa (Rp) <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm font-medium">Rp</span>
                            <input type="number" name="price_per_month" required min="0" value="{{ old('price_per_month') }}"
                                   class="w-full pl-10 pr-4 py-2.5 border rounded-lg text-sm focus:ring-2 focus:ring-blue-500 @error('price_per_month') border-red-400 @else border-gray-300 @enderror"
                                   placeholder="1500000">
                        </div>
                        <p class="text-xs text-gray-400 mt-1" id="priceHint">Harga per kamar per periode</p>
                        @error('price_per_month')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Jenis Diskon <span class="text-gray-400 font-normal">(Opsional)</span></label>
                        <select name="discount_type" id="discount_type"
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
                            <option value="">Tidak ada diskon</option>
                            <option value="percentage" {{ old('discount_type') === 'percentage' ? 'selected' : '' }}>Persentase (%)</option>
                            <option value="flat"       {{ old('discount_type') === 'flat'       ? 'selected' : '' }}>Nominal (Rp)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5" id="discount_label">Nilai Diskon</label>
                        <input type="number" name="discount_value" id="discount_value" min="0" value="{{ old('discount_value') }}"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500"
                               placeholder="0">
                    </div>
                </div>
            </div>

            {{-- ═══════════════════════════════════════════════════════════ --}}
            {{-- STEP 6: FASILITAS                                           --}}
            {{-- ═══════════════════════════════════════════════════════════ --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                    <h2 class="font-semibold text-gray-800 flex items-center gap-2">
                        <span class="step-dot bg-blue-600 text-white">6</span>
                        Fasilitas
                    </h2>
                </div>
                <div class="p-6">
                    @php
                        $commonFacilities = ['AC','WiFi','Kamar Mandi Dalam','Lemari','Meja Belajar','Kursi','Kasur','Bantal','Selimut','Dapur','Kulkas','Mesin Cuci','Parkir Motor','Parkir Mobil','Security 24 Jam','CCTV'];
                        $facilityIcons = ['AC'=>'fa-wind','WiFi'=>'fa-wifi','Kamar Mandi Dalam'=>'fa-bath','Lemari'=>'fa-box','Meja Belajar'=>'fa-chair','Kursi'=>'fa-chair','Kasur'=>'fa-bed','Bantal'=>'fa-bed','Selimut'=>'fa-bed','Dapur'=>'fa-utensils','Kulkas'=>'fa-snowflake','Mesin Cuci'=>'fa-tshirt','Parkir Motor'=>'fa-motorcycle','Parkir Mobil'=>'fa-car','Security 24 Jam'=>'fa-shield-alt','CCTV'=>'fa-video'];
                    @endphp
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                        @foreach ($commonFacilities as $facility)
                        <label class="flex items-center gap-2.5 px-3 py-2.5 rounded-lg border border-gray-200 cursor-pointer hover:border-blue-400 transition-colors">
                            <input type="checkbox" name="facilities[]" value="{{ $facility }}"
                                   {{ in_array($facility, old('facilities', [])) ? 'checked' : '' }}
                                   class="h-4 w-4 text-blue-600 rounded border-gray-300">
                            <i class="fas {{ $facilityIcons[$facility] ?? 'fa-check' }} text-gray-400 text-xs w-3"></i>
                            <span class="text-sm text-gray-700">{{ $facility }}</span>
                        </label>
                        @endforeach
                    </div>
                    <div class="mt-5 p-4 bg-gray-50 rounded-lg border border-gray-200">
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">
                            <i class="fas fa-plus-circle text-blue-500 mr-1"></i>
                            Fasilitas Tambahan <span class="text-gray-400 font-normal">(pisahkan dengan koma)</span>
                        </label>
                        <input type="text" name="custom_facilities" id="custom_facilities" value="{{ old('custom_facilities') }}"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500"
                               placeholder="Contoh: Taman, Kolam Renang, Ruang Tamu Bersama">
                        <div id="customFacilityTags" class="flex flex-wrap gap-2 mt-3"></div>
                    </div>
                </div>
            </div>

            {{-- ═══════════════════════════════════════════════════════════ --}}
            {{-- STEP 7: FOTO HUNIAN                                         --}}
            {{-- ═══════════════════════════════════════════════════════════ --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                    <h2 class="font-semibold text-gray-800 flex items-center gap-2">
                        <span class="step-dot bg-blue-600 text-white">7</span>
                        Foto Hunian <span class="text-xs font-normal text-gray-500 ml-1">(Min. 1, Maks. 10)</span>
                    </h2>
                </div>
                <div class="p-6">
                    <p id="imageError" class="hidden mb-3 text-sm text-red-600 bg-red-50 border border-red-200 rounded-lg px-4 py-2">
                        <i class="fas fa-exclamation-circle mr-1"></i>Foto hunian wajib diupload minimal 1 foto.
                    </p>
                    <div class="upload-zone" id="uploadZone" onclick="document.getElementById('images').click()">
                        <i class="fas fa-cloud-upload-alt text-3xl text-gray-300 mb-3"></i>
                        <p class="font-medium text-gray-600 text-sm">Klik atau seret foto ke sini</p>
                        <p class="text-xs text-gray-400 mt-1">Format: JPG, PNG · Maks. 5MB per file</p>
                    </div>
                    <input type="file" name="images[]" id="images" multiple accept="image/*" class="hidden">
                    <div class="image-preview-grid mt-4" id="imagePreviewGrid"></div>
                </div>
            </div>

            {{-- ═══════════════════════════════════════════════════════════ --}}
            {{-- STEP 8: STATUS TAYANG                                       --}}
            {{-- ═══════════════════════════════════════════════════════════ --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="font-semibold text-gray-800">Status Tayang</h2>
                        <p class="text-sm text-gray-500 mt-0.5">Aktifkan agar hunian bisa dilihat dan dipesan mahasiswa</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="is_active" id="is_active" value="1"
                               {{ old('is_active', true) ? 'checked' : '' }} class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:ring-2 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full after:absolute after:top-0.5 after:left-[2px] after:bg-white after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
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
// ── PILIH TIPE HUNIAN ────────────────────────────────────────────────────────
function selectType(type) {
    // Update hidden input
    document.getElementById('residence_type').value = type;

    // Update visual card selection
    document.querySelectorAll('.type-card').forEach(card => {
        card.classList.toggle('selected', card.dataset.type === type);
    });

    // Tampilkan section yang sesuai, sembunyikan yang lain
    document.querySelectorAll('.type-section').forEach(section => {
        section.classList.remove('active');
    });
    const target = document.getElementById('section-' + type);
    if (target) target.classList.add('active');

    // Update label harga & kapasitas sesuai tipe
    const priceLabel = document.getElementById('priceLabel');
    const priceHint  = document.getElementById('priceHint');
    if (type === 'kos') {
        priceHint.textContent = 'Harga per kamar per periode sewa';
    } else if (type === 'apartemen') {
        priceHint.textContent = 'Harga per unit apartemen per periode sewa';
    } else {
        priceHint.textContent = 'Harga per unit/rumah per periode sewa';
    }

    // Scroll ke section detail yang baru muncul
    if (target) {
        setTimeout(() => target.scrollIntoView({ behavior: 'smooth', block: 'nearest' }), 100);
    }
}

// Restore tipe saat old() ada (validasi gagal)
document.addEventListener('DOMContentLoaded', function () {
    const savedType = document.getElementById('residence_type').value;
    if (savedType) selectType(savedType);
});

// ── VALIDASI SEBELUM SUBMIT ──────────────────────────────────────────────────
document.getElementById('hunianForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    // Validasi tipe hunian
    const type = document.getElementById('residence_type').value;
    if (!type) {
        alert('Pilih tipe hunian terlebih dahulu.');
        document.querySelector('.type-card').scrollIntoView({ behavior: 'smooth', block: 'center' });
        return;
    }

    // Validasi foto
    if (selectedFiles.length === 0) {
        const err = document.getElementById('imageError');
        err.classList.remove('hidden');
        err.scrollIntoView({ behavior: 'smooth', block: 'center' });
        return;
    }

    const btn     = this.querySelector('button[type="submit"]');
    const btnText = btn.innerHTML;
    btn.disabled  = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Menyimpan...';

    // 1. Disable section yang tidak aktif dulu
    const inactiveSections = document.querySelectorAll('.type-section:not(.active) input, .type-section:not(.active) select');
    inactiveSections.forEach(el => el.disabled = true);

    // 2. Baru ambil FormData (hanya field aktif yang masuk)
    const formData = new FormData(this);
    formData.delete('images[]');
    selectedFiles.forEach(file => formData.append('images[]', file));

    // 3. Re-enable semua field kembali
    inactiveSections.forEach(el => el.disabled = false);

    try {
        const res = await fetch(this.action, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
            body: formData
        });
        const html = await res.text();
        window.history.pushState({}, '', res.url);
        document.open(); document.write(html); document.close();
    } catch (err) {
        btn.disabled  = false;
        btn.innerHTML = btnText;
        inactiveSections.forEach(el => el.disabled = false);
        alert('Error: ' + err.message);
        console.error(err);
    }
});

// ── UPLOAD FOTO ──────────────────────────────────────────────────────────────
let selectedFiles = [];
const imageInput  = document.getElementById('images');
const previewGrid = document.getElementById('imagePreviewGrid');
const uploadZone  = document.getElementById('uploadZone');

imageInput.addEventListener('change', function() { addFiles(Array.from(this.files)); this.value = ''; });
uploadZone.addEventListener('dragover',  e => { e.preventDefault(); uploadZone.classList.add('drag-over'); });
uploadZone.addEventListener('dragleave', () => uploadZone.classList.remove('drag-over'));
uploadZone.addEventListener('drop', e => {
    e.preventDefault();
    uploadZone.classList.remove('drag-over');
    addFiles(Array.from(e.dataTransfer.files).filter(f => f.type.startsWith('image/')));
});

function addFiles(files) {
    files.forEach(file => {
        if (selectedFiles.length >= 10) { alert('Maksimal 10 foto!'); return; }
        if (file.size > 5 * 1024 * 1024) { alert(`"${file.name}" melebihi 5MB.`); return; }
        selectedFiles.push(file);
    });
    renderPreviews();
}

function removeFile(index) { selectedFiles.splice(index, 1); renderPreviews(); }

function renderPreviews() {
    previewGrid.innerHTML = '';
    selectedFiles.forEach((file, i) => {
        const reader = new FileReader();
        reader.onload = e => {
            const div = document.createElement('div');
            div.className = 'image-preview-item';
            div.innerHTML = `<img src="${e.target.result}" alt="preview">
                ${i === 0 ? '<span class="badge-utama">Utama</span>' : ''}
                <button type="button" class="remove-btn" onclick="removeFile(${i})"><i class="fas fa-times"></i></button>`;
            previewGrid.appendChild(div);
        };
        reader.readAsDataURL(file);
    });
    document.getElementById('imageError').classList.add('hidden');
}

// ── FASILITAS TAG PREVIEW ────────────────────────────────────────────────────
document.getElementById('custom_facilities').addEventListener('input', function() {
    const tags  = document.getElementById('customFacilityTags');
    const items = this.value.split(',').map(s => s.trim()).filter(Boolean);
    tags.innerHTML = items.map(item =>
        `<span class="inline-flex items-center gap-1 bg-blue-100 text-blue-700 text-xs px-2.5 py-1 rounded-full font-medium">
            <i class="fas fa-check text-xs"></i>${item}</span>`
    ).join('');
});

// ── DISKON LABEL ─────────────────────────────────────────────────────────────
document.getElementById('discount_type').addEventListener('change', function() {
    const val = document.getElementById('discount_value');
    const lbl = document.getElementById('discount_label');
    if (this.value === 'percentage') { lbl.textContent = 'Persentase Diskon (%)'; val.placeholder = '10'; val.max = '100'; }
    else if (this.value === 'flat')  { lbl.textContent = 'Nominal Diskon (Rp)';   val.placeholder = '100000'; val.removeAttribute('max'); }
    else { lbl.textContent = 'Nilai Diskon'; val.placeholder = '0'; val.value = ''; }
});

// ── TOGGLE STATUS ────────────────────────────────────────────────────────────
document.getElementById('is_active').addEventListener('change', function() {
    document.getElementById('statusLabel').textContent = this.checked ? 'Aktif' : 'Nonaktif';
});
</script>
@endpush