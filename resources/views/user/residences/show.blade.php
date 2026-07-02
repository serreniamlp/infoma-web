@extends('layouts.app')

@section('title', $residence->name . ' - EduLiving')

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="{{ asset('css/leaflet-maps.css') }}">
    <style>
        .gallery-main { position:relative; border-radius:12px; overflow:hidden; background:#111; aspect-ratio:16/9 }
        .gallery-main img { width:100%; height:100%; object-fit:cover; transition:opacity .3s }
        .gallery-thumbs { display:flex; gap:8px; margin-top:8px; overflow-x:auto; padding-bottom:4px }
        .gallery-thumbs::-webkit-scrollbar { height:4px }
        .gallery-thumbs::-webkit-scrollbar-thumb { background:#cbd5e1; border-radius:4px }
        .thumb { flex-shrink:0; width:72px; height:56px; border-radius:8px; overflow:hidden; cursor:pointer; border:2px solid transparent; transition:border-color .2s; opacity:.7 }
        .thumb.active { border-color:#2563eb; opacity:1 }
        .thumb img { width:100%; height:100%; object-fit:cover }
        .gallery-count { position:absolute; bottom:12px; right:12px; background:rgba(0,0,0,.55); color:#fff; font-size:12px; padding:4px 10px; border-radius:20px; backdrop-filter:blur(4px) }
        .facility-tag { display:inline-flex; align-items:center; gap:6px; background:#f0f9ff; color:#0369a1; border:1px solid #bae6fd; border-radius:8px; padding:6px 12px; font-size:13px }
        .price-main { font-size:2rem; font-weight:700; color:#2563eb; line-height:1.1 }
        .price-old { text-decoration:line-through; color:#9ca3af; font-size:.875rem }
        .stat-row { display:flex; justify-content:space-between; align-items:center; padding:10px 0; border-bottom:1px solid #f3f4f6 }
        .stat-row:last-child { border-bottom:none }
        .sidebar-card { position:sticky; top:90px }
        .detail-badge { display:inline-flex; align-items:center; gap:6px; padding:6px 12px; border-radius:8px; font-size:13px; font-weight:500 }
    </style>
@endpush

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        {{-- Breadcrumb --}}
        <nav class="flex items-center gap-2 text-sm text-gray-500 mb-6">
            <a href="{{ route('home') }}" class="hover:text-blue-600">Beranda</a>
            <i class="fas fa-chevron-right text-xs"></i>
            <a href="{{ route('residences.index') }}" class="hover:text-blue-600">Hunian</a>
            <i class="fas fa-chevron-right text-xs"></i>
            <span class="text-gray-900">{{ $residence->name }}</span>
        </nav>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            {{-- ══ KOLOM KIRI ══ --}}
            <div class="lg:col-span-2 space-y-6">

                {{-- GALLERY --}}
                <div class="bg-white rounded-xl shadow-sm overflow-hidden p-4">
                    @php $images = $residence->images ?? []; @endphp
                    @if(count($images) > 0)
                        <div class="gallery-main">
                            <img src="{{ asset('storage/' . $images[0]) }}" alt="{{ $residence->name }}" id="mainGalleryImg">
                            @if(count($images) > 1)
                                <div class="gallery-count">
                                    <i class="fas fa-images mr-1"></i>
                                    <span id="imgCounter">1</span> / {{ count($images) }}
                                </div>
                            @endif
                        </div>
                        @if(count($images) > 1)
                            <div class="gallery-thumbs" id="thumbStrip">
                                @foreach($images as $i => $img)
                                    <div class="thumb {{ $i === 0 ? 'active' : '' }}"
                                         onclick="gantiGambar('{{ asset('storage/' . $img) }}', {{ $i }})">
                                        <img src="{{ asset('storage/' . $img) }}" alt="foto {{ $i + 1 }}">
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    @else
                        <div class="w-full aspect-video bg-gray-100 rounded-xl flex items-center justify-center">
                            <div class="text-center text-gray-400">
                                <i class="fas fa-home text-5xl mb-2"></i>
                                <p class="text-sm">Belum ada foto</p>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- INFORMASI UTAMA --}}
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <div class="flex items-start justify-between">
                        <div class="flex-1 min-w-0">
                            {{-- Badge tipe & kategori --}}
                            <div class="flex flex-wrap gap-2 mb-3">
                                @if($residence->residence_type)
                                    @php
                                        $typeColor = match($residence->residence_type) {
                                            'kos'       => 'bg-blue-50 text-blue-700 border-blue-200',
                                            'kontrakan' => 'bg-green-50 text-green-700 border-green-200',
                                            'apartemen' => 'bg-purple-50 text-purple-700 border-purple-200',
                                            default     => 'bg-gray-50 text-gray-700 border-gray-200',
                                        };
                                        $typeIcon = match($residence->residence_type) {
                                            'kos'       => 'fa-door-open',
                                            'kontrakan' => 'fa-home',
                                            'apartemen' => 'fa-building',
                                            default     => 'fa-bed',
                                        };
                                    @endphp
                                    <span class="inline-flex items-center gap-1.5 border text-xs font-semibold px-3 py-1 rounded-full {{ $typeColor }}">
                                        <i class="fas {{ $typeIcon }} text-xs"></i>
                                        {{ $residence->residence_type_label }}
                                        @if($residence->isKos() && $residence->kos_type)
                                            · {{ $residence->kos_type_label }}
                                        @endif
                                    </span>
                                @endif
                                @if($residence->category)
                                    <span class="inline-block bg-gray-50 text-gray-600 text-xs font-medium px-3 py-1 rounded-full border border-gray-200">
                                        {{ $residence->category->name }}
                                    </span>
                                @endif
                                @if($residence->furnish_status)
                                    <span class="inline-block bg-yellow-50 text-yellow-700 text-xs font-medium px-3 py-1 rounded-full border border-yellow-200">
                                        <i class="fas fa-couch text-xs mr-1"></i>{{ $residence->furnish_status_label }}
                                    </span>
                                @endif
                            </div>

                            <h1 class="text-2xl font-bold text-gray-900 mb-2">{{ $residence->name }}</h1>

                            <div class="flex items-start gap-2 text-gray-500 text-sm mb-3">
                                <i class="fas fa-map-marker-alt text-blue-500 mt-0.5 flex-shrink-0"></i>
                                <span>{{ $residence->address }}</span>
                            </div>

                            @if(isset($residence->ratings_avg_rating) && $residence->ratings_avg_rating)
                                <div class="flex items-center gap-2">
                                    <div class="flex">
                                        @for($i = 1; $i <= 5; $i++)
                                            <i class="fas fa-star text-sm {{ $i <= round($residence->ratings_avg_rating) ? 'text-yellow-400' : 'text-gray-200' }}"></i>
                                        @endfor
                                    </div>
                                    <span class="text-sm font-semibold text-gray-800">{{ number_format($residence->ratings_avg_rating, 1) }}</span>
                                    <span class="text-sm text-gray-500">({{ $residence->ratings_count ?? 0 }} ulasan)</span>
                                </div>
                            @endif
                        </div>

                        @auth
                            <button onclick="toggleBookmark({{ $residence->id }}, 'residence')" id="bookmarkBtn"
                                class="flex-shrink-0 ml-4 p-2.5 rounded-full border transition-all
                                {{ $isBookmarked ? 'bg-red-50 border-red-200 text-red-500' : 'bg-gray-50 border-gray-200 text-gray-400 hover:border-red-200 hover:text-red-400' }}">
                                <i class="fas fa-heart text-lg"></i>
                            </button>
                        @endauth
                    </div>

                    <div class="mt-5 pt-5 border-t border-gray-100">
                        <h3 class="font-semibold text-gray-900 mb-2">Deskripsi</h3>
                        <p class="text-gray-600 leading-relaxed text-sm">{{ $residence->description }}</p>
                    </div>
                </div>

                {{-- ══════════════════════════════════════════════════════ --}}
                {{-- DETAIL SPESIFIK PER TIPE HUNIAN (BARU)                --}}
                {{-- ══════════════════════════════════════════════════════ --}}
                @if($residence->residence_type)
                <div class="bg-white rounded-xl shadow-sm p-6">
                    @php
                        $headerColor = match($residence->residence_type) {
                            'kos'       => ['bg' => 'bg-blue-50',   'border' => 'border-blue-100',   'text' => 'text-blue-800',   'icon' => 'fa-door-open',   'badge' => 'bg-blue-600'],
                            'kontrakan' => ['bg' => 'bg-green-50',  'border' => 'border-green-100',  'text' => 'text-green-800',  'icon' => 'fa-home',        'badge' => 'bg-green-600'],
                            'apartemen' => ['bg' => 'bg-purple-50', 'border' => 'border-purple-100', 'text' => 'text-purple-800', 'icon' => 'fa-building',    'badge' => 'bg-purple-600'],
                            default     => ['bg' => 'bg-gray-50',   'border' => 'border-gray-100',   'text' => 'text-gray-800',   'icon' => 'fa-bed',         'badge' => 'bg-gray-500'],
                        };
                    @endphp

                    <h3 class="font-semibold text-gray-900 mb-4 flex items-center gap-2">
                        <span class="w-7 h-7 {{ $headerColor['badge'] }} text-white rounded-full flex items-center justify-center text-xs">
                            <i class="fas {{ $headerColor['icon'] }}"></i>
                        </span>
                        Detail {{ $residence->residence_type_label }}
                    </h3>

                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">

                        {{-- ── KOS ────────────────────────────────── --}}
                        @if($residence->isKos())
                            @if($residence->kos_type)
                            <div class="bg-blue-50 rounded-xl p-4 text-center border border-blue-100">
                                <i class="fas fa-{{ $residence->kos_type === 'putra' ? 'mars' : ($residence->kos_type === 'putri' ? 'venus' : 'venus-mars') }} text-blue-500 text-xl mb-2"></i>
                                <div class="text-xs text-blue-600 mb-0.5">Jenis Kos</div>
                                <div class="font-bold text-blue-800">{{ $residence->kos_type_label }}</div>
                            </div>
                            @endif
                            <div class="bg-gray-50 rounded-xl p-4 text-center border border-gray-100">
                                <i class="fas fa-door-open text-gray-500 text-xl mb-2"></i>
                                <div class="text-xs text-gray-500 mb-0.5">Jumlah Kamar</div>
                                <div class="font-bold text-gray-800">{{ $residence->capacity }} kamar</div>
                            </div>
                            @if($residence->room_size)
                            <div class="bg-gray-50 rounded-xl p-4 text-center border border-gray-100">
                                <i class="fas fa-ruler-combined text-gray-500 text-xl mb-2"></i>
                                <div class="text-xs text-gray-500 mb-0.5">Ukuran Kamar</div>
                                <div class="font-bold text-gray-800">{{ $residence->room_size }} m²</div>
                            </div>
                            @endif

                        {{-- ── KONTRAKAN ────────────────────────── --}}
                        @elseif($residence->isKontrakan())
                            <div class="bg-gray-50 rounded-xl p-4 text-center border border-gray-100">
                                <i class="fas fa-home text-gray-500 text-xl mb-2"></i>
                                <div class="text-xs text-gray-500 mb-0.5">Jumlah Unit</div>
                                <div class="font-bold text-gray-800">{{ $residence->capacity }} unit</div>
                            </div>
                            @if($residence->bedroom_count)
                            <div class="bg-gray-50 rounded-xl p-4 text-center border border-gray-100">
                                <i class="fas fa-bed text-gray-500 text-xl mb-2"></i>
                                <div class="text-xs text-gray-500 mb-0.5">Kamar Tidur</div>
                                <div class="font-bold text-gray-800">{{ $residence->bedroom_count }} kamar</div>
                            </div>
                            @endif
                            @if($residence->bathroom_count)
                            <div class="bg-gray-50 rounded-xl p-4 text-center border border-gray-100">
                                <i class="fas fa-bath text-gray-500 text-xl mb-2"></i>
                                <div class="text-xs text-gray-500 mb-0.5">Kamar Mandi</div>
                                <div class="font-bold text-gray-800">{{ $residence->bathroom_count }} kamar</div>
                            </div>
                            @endif
                            @if($residence->building_size)
                            <div class="bg-gray-50 rounded-xl p-4 text-center border border-gray-100">
                                <i class="fas fa-ruler-combined text-gray-500 text-xl mb-2"></i>
                                <div class="text-xs text-gray-500 mb-0.5">Luas Bangunan</div>
                                <div class="font-bold text-gray-800">{{ $residence->building_size }} m²</div>
                            </div>
                            @endif
                            @if($residence->land_size)
                            <div class="bg-gray-50 rounded-xl p-4 text-center border border-gray-100">
                                <i class="fas fa-expand-arrows-alt text-gray-500 text-xl mb-2"></i>
                                <div class="text-xs text-gray-500 mb-0.5">Luas Tanah</div>
                                <div class="font-bold text-gray-800">{{ $residence->land_size }} m²</div>
                            </div>
                            @endif

                        {{-- ── APARTEMEN ───────────────────────────── --}}
                        @elseif($residence->isApartemen())
                            @if($residence->unit_type)
                            <div class="bg-purple-50 rounded-xl p-4 text-center border border-purple-100">
                                <i class="fas fa-layer-group text-purple-500 text-xl mb-2"></i>
                                <div class="text-xs text-purple-600 mb-0.5">Tipe Unit</div>
                                <div class="font-bold text-purple-800">{{ $residence->unit_type }}</div>
                            </div>
                            @endif
                            @if($residence->floor_number)
                            <div class="bg-gray-50 rounded-xl p-4 text-center border border-gray-100">
                                <i class="fas fa-sort-numeric-up text-gray-500 text-xl mb-2"></i>
                                <div class="text-xs text-gray-500 mb-0.5">Lantai</div>
                                <div class="font-bold text-gray-800">Lantai {{ $residence->floor_number }}</div>
                            </div>
                            @endif
                            @if($residence->tower_name)
                            <div class="bg-gray-50 rounded-xl p-4 text-center border border-gray-100">
                                <i class="fas fa-building text-gray-500 text-xl mb-2"></i>
                                <div class="text-xs text-gray-500 mb-0.5">Tower/Gedung</div>
                                <div class="font-bold text-gray-800">{{ $residence->tower_name }}</div>
                            </div>
                            @endif
                            <div class="bg-gray-50 rounded-xl p-4 text-center border border-gray-100">
                                <i class="fas fa-clone text-gray-500 text-xl mb-2"></i>
                                <div class="text-xs text-gray-500 mb-0.5">Unit Tersedia</div>
                                <div class="font-bold text-gray-800">{{ $residence->capacity }} unit</div>
                            </div>
                            @if($residence->room_size)
                            <div class="bg-gray-50 rounded-xl p-4 text-center border border-gray-100">
                                <i class="fas fa-ruler-combined text-gray-500 text-xl mb-2"></i>
                                <div class="text-xs text-gray-500 mb-0.5">Luas Unit</div>
                                <div class="font-bold text-gray-800">{{ $residence->room_size }} m²</div>
                            </div>
                            @endif
                            @if($residence->bathroom_count)
                            <div class="bg-gray-50 rounded-xl p-4 text-center border border-gray-100">
                                <i class="fas fa-bath text-gray-500 text-xl mb-2"></i>
                                <div class="text-xs text-gray-500 mb-0.5">Kamar Mandi</div>
                                <div class="font-bold text-gray-800">{{ $residence->bathroom_count }}</div>
                            </div>
                            @endif
                        @endif

                        {{-- Status furnitur (semua tipe) --}}
                        @if($residence->furnish_status)
                        <div class="bg-yellow-50 rounded-xl p-4 text-center border border-yellow-100">
                            <i class="fas fa-couch text-yellow-500 text-xl mb-2"></i>
                            <div class="text-xs text-yellow-600 mb-0.5">Furnitur</div>
                            <div class="font-bold text-yellow-800">{{ $residence->furnish_status_label }}</div>
                        </div>
                        @endif
                    </div>
                </div>
                @endif

                {{-- FASILITAS --}}
                @if($residence->facilities && count($residence->facilities) > 0)
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h3 class="font-semibold text-gray-900 mb-4">
                        <i class="fas fa-concierge-bell text-blue-500 mr-2"></i>Fasilitas
                    </h3>
                    @php
                        $facilityIcons = ['AC'=>'fa-wind','WiFi'=>'fa-wifi','Kamar Mandi Dalam'=>'fa-bath','Lemari'=>'fa-box','Meja Belajar'=>'fa-chair','Kursi'=>'fa-chair','Kasur'=>'fa-bed','Bantal'=>'fa-bed','Selimut'=>'fa-bed','Dapur'=>'fa-utensils','Kulkas'=>'fa-snowflake','Mesin Cuci'=>'fa-tshirt','Parkir Motor'=>'fa-motorcycle','Parkir Mobil'=>'fa-car','Security 24 Jam'=>'fa-shield-alt','CCTV'=>'fa-video'];
                    @endphp
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                        @foreach($residence->facilities as $facility)
                            <div class="facility-tag">
                                <i class="fas {{ $facilityIcons[$facility] ?? 'fa-check-circle' }} text-blue-400 text-xs"></i>
                                <span>{{ $facility }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- PETA LOKASI --}}
                @if($residence->latitude && $residence->longitude)
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h3 class="font-semibold text-gray-900 mb-4">
                        <i class="fas fa-map-marker-alt text-blue-500 mr-2"></i>Lokasi Hunian
                    </h3>
                    <div id="residence-detail-map" style="height:320px; border-radius:10px; overflow:hidden;"></div>
                    <div class="mt-3 text-sm text-gray-600 space-y-1">
                        <p><i class="fas fa-map-pin text-gray-400 mr-2"></i>{{ $residence->address }}</p>
                    </div>
                </div>
                @endif

                {{-- ULASAN --}}
                @auth
                    @if($canRate)
                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <h3 class="font-semibold text-gray-900 mb-4">
                            <i class="fas fa-star text-yellow-400 mr-2"></i>Tulis Ulasan
                        </h3>
                        <form id="ratingForm" class="space-y-4">
                            @csrf
                            <input type="hidden" name="type" value="residence">
                            <input type="hidden" name="id" value="{{ $residence->id }}">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Penilaian</label>
                                <div class="flex gap-2" id="starContainer">
                                    @for($i = 1; $i <= 5; $i++)
                                        <label class="cursor-pointer">
                                            <input type="radio" name="rating" value="{{ $i }}" class="hidden"
                                                   {{ isset($userRating) && $userRating && $userRating->rating == $i ? 'checked' : '' }}>
                                            <i class="fas fa-star text-2xl transition-colors {{ isset($userRating) && $userRating && $userRating->rating >= $i ? 'text-yellow-400' : 'text-gray-200' }}"
                                               onclick="this.previousElementSibling.checked=true; tandaiBintang({{ $i }})"></i>
                                        </label>
                                    @endfor
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Komentar <span class="text-gray-400 font-normal">(opsional)</span></label>
                                <textarea name="review" rows="3"
                                          class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500"
                                          placeholder="Bagikan pengalaman Anda...">{{ $userRating->review ?? '' }}</textarea>
                            </div>
                            <div class="flex items-center gap-3">
                                <button type="button" onclick="kirimUlasan()"
                                        class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm rounded-lg font-medium transition-colors">
                                    Simpan Ulasan
                                </button>
                                @if(isset($userRating) && $userRating)
                                    <button type="button" onclick="hapusUlasan({{ $residence->id }}, 'residence')"
                                            class="text-red-500 hover:text-red-600 text-sm font-medium">Hapus Ulasan</button>
                                @endif
                            </div>
                        </form>
                    </div>
                    @endif
                @endauth

                @if($residence->ratings && $residence->ratings->count() > 0)
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h3 class="font-semibold text-gray-900 mb-5">
                        Ulasan <span class="text-gray-400 font-normal">({{ $residence->ratings->count() }})</span>
                    </h3>
                    <div class="space-y-5">
                        @foreach($residence->ratings as $rating)
                        <div class="flex gap-4 {{ !$loop->last ? 'pb-5 border-b border-gray-100' : '' }}">
                            <div class="w-9 h-9 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0">
                                <span class="text-sm font-bold text-blue-600">{{ substr($rating->user->name, 0, 1) }}</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between mb-1">
                                    <p class="font-semibold text-gray-900 text-sm">{{ $rating->user->name }}</p>
                                    <span class="text-xs text-gray-400">{{ $rating->created_at->format('d M Y') }}</span>
                                </div>
                                <div class="flex mb-2">
                                    @for($i = 1; $i <= 5; $i++)
                                        <i class="fas fa-star text-xs {{ $i <= $rating->rating ? 'text-yellow-400' : 'text-gray-200' }}"></i>
                                    @endfor
                                </div>
                                @if($rating->review)
                                    <p class="text-gray-600 text-sm leading-relaxed">{{ $rating->review }}</p>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

            </div>

            {{-- ══ KOLOM KANAN: SIDEBAR ══ --}}
            <div class="lg:col-span-1">
                <div class="sidebar-card space-y-4">

                    {{-- Kartu Harga & Booking --}}
                    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                        <div class="mb-5">
                            @if($residence->discount_type && $residence->discount_value)
                                <div class="price-old mb-1">
                                    Rp {{ number_format($residence->price_per_month ?? $residence->price, 0, ',', '.') }}
                                </div>
                                <div class="price-main">
                                    Rp {{ number_format($residence->getDiscountedPrice(), 0, ',', '.') }}
                                </div>
                                <div class="mt-1 inline-flex items-center gap-1 bg-green-50 text-green-700 text-xs font-semibold px-2 py-0.5 rounded-full">
                                    <i class="fas fa-tag text-xs"></i>
                                    @if($residence->discount_type === 'percentage')
                                        Hemat {{ $residence->discount_value }}%
                                    @else
                                        Hemat Rp {{ number_format($residence->discount_value, 0, ',', '.') }}
                                    @endif
                                </div>
                            @else
                                <div class="price-main">
                                    Rp {{ number_format($residence->price_per_month ?? $residence->price, 0, ',', '.') }}
                                </div>
                            @endif
                            <div class="text-sm text-gray-400 mt-1">
                                per {{ $residence->rental_period === 'monthly' ? 'bulan' : 'tahun' }}
                                @if($residence->isKos()) · per kamar @endif
                            </div>
                        </div>

                        {{-- Info singkat kontekstual --}}
                        <div class="mb-5">
                            <div class="stat-row">
                                <span class="text-sm text-gray-500">{{ $residence->capacity_label }}</span>
                                <span class="text-sm font-semibold text-gray-800">
                                    {{ $residence->capacity }}
                                    {{ $residence->isKos() ? 'kamar' : 'unit' }}
                                </span>
                            </div>
                            <div class="stat-row">
                                <span class="text-sm text-gray-500">
                                    {{ $residence->isKos() ? 'Kamar Kosong' : 'Unit Kosong' }}
                                </span>
                                <span class="text-sm font-semibold {{ $residence->available_slots > 0 ? 'text-green-600' : 'text-red-500' }}">
                                    {{ $residence->available_slots }}
                                    {{ $residence->isKos() ? 'kamar' : 'unit' }}
                                </span>
                            </div>
                            <div class="stat-row">
                                <span class="text-sm text-gray-500">Periode Sewa</span>
                                <span class="text-sm font-semibold text-gray-800">
                                    {{ $residence->rental_period === 'monthly' ? 'Bulanan' : 'Tahunan' }}
                                </span>
                            </div>
                            @if($residence->isKos() && $residence->kos_type)
                            <div class="stat-row">
                                <span class="text-sm text-gray-500">Jenis Kos</span>
                                <span class="text-sm font-semibold text-gray-800">{{ $residence->kos_type_label }}</span>
                            </div>
                            @endif
                            @if($residence->isApartemen() && $residence->unit_type)
                            <div class="stat-row">
                                <span class="text-sm text-gray-500">Tipe Unit</span>
                                <span class="text-sm font-semibold text-gray-800">{{ $residence->unit_type }}</span>
                            </div>
                            @endif
                        </div>

                        {{-- Tombol Booking --}}
                        @if($residence->available_slots > 0)
                            @auth
                                <a href="{{ route('user.bookings.create', ['type' => 'residence', 'id' => $residence->id]) }}"
                                   class="w-full bg-blue-600 hover:bg-blue-700 text-white py-3 px-4 rounded-xl font-semibold text-sm transition-colors text-center block">
                                    <i class="fas fa-calendar-plus mr-2"></i>
                                    {{ $residence->isKos() ? 'Booking Kamar' : 'Booking Unit' }}
                                </a>
                            @else
                                <a href="{{ route('login') }}"
                                   class="w-full bg-blue-600 hover:bg-blue-700 text-white py-3 px-4 rounded-xl font-semibold text-sm transition-colors text-center block">
                                    <i class="fas fa-sign-in-alt mr-2"></i>Masuk untuk Booking
                                </a>
                            @endauth
                        @else
                            <div class="w-full bg-gray-100 text-gray-500 py-3 px-4 rounded-xl font-semibold text-sm text-center">
                                <i class="fas fa-times-circle mr-2"></i>
                                {{ $residence->isKos() ? 'Kamar Penuh' : 'Unit Penuh' }}
                            </div>
                        @endif
                    </div>

                    {{-- Info Penyedia --}}
                    <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100">
                        <h3 class="font-semibold text-gray-900 mb-4 text-sm">Penyedia Hunian</h3>
                        <div class="flex items-center gap-3">
                            <div class="relative flex-shrink-0">
                                <div class="w-11 h-11 bg-blue-100 rounded-full flex items-center justify-center">
                                    <span class="font-bold text-blue-600">{{ substr($residence->provider->name, 0, 1) }}</span>
                                </div>
                                @if($residence->provider->isOnline())
                                    <span class="absolute -bottom-0.5 -right-0.5 w-3.5 h-3.5 bg-green-500 border-2 border-white rounded-full"></span>
                                @endif
                            </div>
                            <div class="min-w-0">
                                <p class="font-semibold text-gray-900 text-sm truncate">{{ $residence->provider->name }}</p>
                                <p class="text-xs text-gray-500 truncate">{{ $residence->provider->email }}</p>
                                <p class="text-xs mt-0.5 {{ $residence->provider->isOnline() ? 'text-green-600 font-medium' : 'text-gray-400' }}">
                                    <i class="fas fa-circle text-[8px] mr-1"></i>{{ $residence->provider->getLastSeenLabel() }}
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Bagikan --}}
                    <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100">
                        <p class="text-sm font-semibold text-gray-700 mb-3">Bagikan Hunian Ini</p>
                        <div class="flex gap-2">
                            <a href="https://wa.me/?text={{ urlencode($residence->name . ' - ' . request()->url()) }}"
                               target="_blank"
                               class="flex-1 flex items-center justify-center gap-1.5 bg-green-500 hover:bg-green-600 text-white text-xs font-medium py-2 rounded-lg transition-colors">
                                <i class="fab fa-whatsapp"></i> WhatsApp
                            </a>
                            <button onclick="salinLink()"
                                    class="flex-1 flex items-center justify-center gap-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-medium py-2 rounded-lg transition-colors">
                                <i class="fas fa-link"></i> Salin Link
                            </button>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
function gantiGambar(src, index) {
    const main = document.getElementById('mainGalleryImg');
    const counter = document.getElementById('imgCounter');
    const thumbs = document.querySelectorAll('.thumb');
    main.style.opacity = 0;
    setTimeout(() => { main.src = src; main.style.opacity = 1; }, 200);
    if (counter) counter.textContent = index + 1;
    thumbs.forEach((t, i) => t.classList.toggle('active', i === index));
}

function toggleBookmark(id, type) {
    const btn = document.getElementById('bookmarkBtn');
    const sudahDisimpan = btn.classList.contains('bg-red-50');
    const url = sudahDisimpan ? '{{ route('user.bookmarks.destroy') }}' : '{{ route('user.bookmarks.store') }}';
    fetch(url, {
        method: sudahDisimpan ? 'DELETE' : 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
        body: JSON.stringify({ type, id })
    }).then(r => r.json()).then(data => {
        if (data.message) {
            btn.classList.toggle('bg-red-50', !sudahDisimpan);
            btn.classList.toggle('border-red-200', !sudahDisimpan);
            btn.classList.toggle('text-red-500', !sudahDisimpan);
            btn.classList.toggle('bg-gray-50', sudahDisimpan);
            btn.classList.toggle('border-gray-200', sudahDisimpan);
            btn.classList.toggle('text-gray-400', sudahDisimpan);
            tampilToast(sudahDisimpan ? 'Dihapus dari simpanan' : 'Berhasil disimpan', 'success');
        }
    }).catch(() => tampilToast('Terjadi kesalahan.', 'error'));
}

function tandaiBintang(nilai) {
    document.querySelectorAll('#starContainer i').forEach((b, i) => {
        b.classList.toggle('text-yellow-400', i < nilai);
        b.classList.toggle('text-gray-200', i >= nilai);
    });
}

function kirimUlasan() {
    const form = document.getElementById('ratingForm');
    const btn = form.querySelector('button[onclick="kirimUlasan()"]');
    btn.textContent = 'Menyimpan...'; btn.disabled = true;
    fetch('{{ route('user.ratings.store') }}', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
        body: new FormData(form)
    }).then(r => r.json()).then(data => tampilToast(data.message || 'Ulasan disimpan', data.status === 'success' ? 'success' : 'error'))
    .catch(() => tampilToast('Terjadi kesalahan.', 'error'))
    .finally(() => { btn.textContent = 'Simpan Ulasan'; btn.disabled = false; });
}

function hapusUlasan(id, type) {
    if (!confirm('Hapus ulasan ini?')) return;
    fetch('{{ route('user.ratings.destroy') }}', {
        method: 'DELETE',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
        body: JSON.stringify({ type, id })
    }).then(r => r.json()).then(data => {
        tampilToast(data.message || 'Ulasan dihapus', data.status === 'success' ? 'success' : 'error');
        if (data.status === 'success') {
            document.querySelectorAll('#starContainer i').forEach(b => { b.classList.add('text-gray-200'); b.classList.remove('text-yellow-400'); });
            document.querySelector('#ratingForm textarea[name="review"]').value = '';
        }
    });
}

function salinLink() {
    navigator.clipboard.writeText(window.location.href).then(() => tampilToast('Link berhasil disalin!', 'success'));
}

function tampilToast(pesan, tipe = 'success') {
    const t = document.createElement('div');
    t.className = `fixed top-4 right-4 z-50 px-5 py-3 rounded-xl shadow-lg text-sm font-medium text-white ${tipe === 'success' ? 'bg-green-500' : 'bg-red-500'}`;
    t.innerHTML = `<i class="fas fa-${tipe === 'success' ? 'check' : 'exclamation'}-circle mr-2"></i>${pesan}`;
    document.body.appendChild(t);
    setTimeout(() => t.remove(), 3000);
}

@if($residence->latitude && $residence->longitude)
document.addEventListener('DOMContentLoaded', () => {
    const peta = L.map('residence-detail-map').setView([{{ $residence->latitude }}, {{ $residence->longitude }}], 15);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OpenStreetMap contributors' }).addTo(peta);
    L.marker([{{ $residence->latitude }}, {{ $residence->longitude }}])
        .addTo(peta)
        .bindPopup('<strong>{{ addslashes($residence->name) }}</strong><br>{{ addslashes($residence->address) }}')
        .openPopup();
});
@endif
</script>
@endpush