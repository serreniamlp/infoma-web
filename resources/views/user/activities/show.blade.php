@extends('layouts.app')

@section('title', $activity->name . ' - EduLiving')

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
    .thumb.active { border-color:#16a34a; opacity:1 }
    .thumb img { width:100%; height:100%; object-fit:cover }
    .gallery-count { position:absolute; bottom:12px; right:12px; background:rgba(0,0,0,.55); color:#fff; font-size:12px; padding:4px 10px; border-radius:20px; backdrop-filter:blur(4px) }
    .stat-row { display:flex; justify-content:space-between; align-items:center; padding:10px 0; border-bottom:1px solid #f3f4f6 }
    .stat-row:last-child { border-bottom:none }
    .sidebar-card { position:sticky; top:90px }
    .price-main { font-size:2rem; font-weight:700; color:#16a34a; line-height:1.1 }
    .price-old { text-decoration:line-through; color:#9ca3af; font-size:.875rem }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        {{-- Breadcrumb --}}
        <nav class="flex items-center gap-2 text-sm text-gray-500 mb-6">
            <a href="{{ route('home') }}" class="hover:text-green-600">Beranda</a>
            <i class="fas fa-chevron-right text-xs"></i>
            <a href="{{ route('activities.index') }}" class="hover:text-green-600">Kegiatan</a>
            <i class="fas fa-chevron-right text-xs"></i>
            <span class="text-gray-900 truncate max-w-xs">{{ $activity->name }}</span>
        </nav>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            {{-- ══ KOLOM KIRI ══ --}}
            <div class="lg:col-span-2 space-y-6">

                {{-- GALLERY --}}
                <div class="bg-white rounded-xl shadow-sm overflow-hidden p-4">
                    @php $images = $activity->images ?? []; @endphp
                    @if(count($images) > 0)
                        <div class="gallery-main">
                            <img src="{{ asset('storage/' . $images[0]) }}" alt="{{ $activity->name }}" id="mainGalleryImg">
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
                        <div class="w-full aspect-video bg-gradient-to-br from-green-50 to-green-100 rounded-xl flex items-center justify-center">
                            <div class="text-center text-green-300">
                                <i class="fas fa-calendar-alt text-5xl mb-2"></i>
                                <p class="text-sm">Belum ada foto</p>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- INFORMASI UTAMA --}}
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <div class="flex items-start justify-between">
                        <div class="flex-1 min-w-0">
                            {{-- Badge kategori --}}
                            <div class="flex flex-wrap gap-2 mb-3">
                                @if($activity->category)
                                    <span class="inline-flex items-center gap-1.5 border text-xs font-semibold px-3 py-1 rounded-full bg-green-50 text-green-700 border-green-200">
                                        <i class="fas fa-tag text-xs"></i>
                                        {{ $activity->category->name }}
                                    </span>
                                @endif
                                @if(!$isRegistrationOpen)
                                    <span class="inline-flex items-center gap-1.5 border text-xs font-semibold px-3 py-1 rounded-full bg-red-50 text-red-700 border-red-200">
                                        <i class="fas fa-lock text-xs"></i>Pendaftaran Ditutup
                                    </span>
                                @endif
                                @if($activity->available_slots <= 0)
                                    <span class="inline-flex items-center gap-1.5 border text-xs font-semibold px-3 py-1 rounded-full bg-orange-50 text-orange-700 border-orange-200">
                                        <i class="fas fa-users-slash text-xs"></i>Kuota Penuh
                                    </span>
                                @endif
                            </div>

                            <h1 class="text-2xl font-bold text-gray-900 mb-2">{{ $activity->name }}</h1>

                            <div class="flex items-start gap-2 text-gray-500 text-sm mb-3">
                                <i class="fas fa-map-marker-alt text-green-500 mt-0.5 flex-shrink-0"></i>
                                <span>{{ $activity->location }}</span>
                            </div>
                        </div>

                        @auth
                            <button onclick="toggleBookmark({{ $activity->id }}, 'activity')" id="bookmarkBtn"
                                class="flex-shrink-0 ml-4 p-2.5 rounded-full border transition-all
                                {{ $isBookmarked ? 'bg-red-50 border-red-200 text-red-500' : 'bg-gray-50 border-gray-200 text-gray-400 hover:border-red-200 hover:text-red-400' }}">
                                <i class="fas fa-heart text-lg"></i>
                            </button>
                        @endauth
                    </div>

                    <div class="mt-5 pt-5 border-t border-gray-100">
                        <h3 class="font-semibold text-gray-900 mb-2">Deskripsi</h3>
                        <p class="text-gray-600 leading-relaxed text-sm">{{ $activity->description }}</p>
                    </div>
                </div>

                {{-- DETAIL KEGIATAN --}}
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h3 class="font-semibold text-gray-900 mb-4 flex items-center gap-2">
                        <span class="w-7 h-7 bg-green-600 text-white rounded-full flex items-center justify-center text-xs">
                            <i class="fas fa-calendar-check"></i>
                        </span>
                        Informasi Kegiatan
                    </h3>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="bg-green-50 rounded-xl p-4 text-center border border-green-100">
                            <i class="fas fa-calendar-day text-green-500 text-xl mb-2"></i>
                            <div class="text-xs text-green-600 mb-0.5">Tanggal</div>
                            <div class="font-bold text-green-800 text-sm">{{ $activity->event_date->format('d M Y') }}</div>
                        </div>
                        <div class="bg-green-50 rounded-xl p-4 text-center border border-green-100">
                            <i class="fas fa-clock text-green-500 text-xl mb-2"></i>
                            <div class="text-xs text-green-600 mb-0.5">Waktu</div>
                            <div class="font-bold text-green-800 text-sm">{{ $activity->event_date->format('H:i') }} WIB</div>
                        </div>
                        <div class="bg-gray-50 rounded-xl p-4 text-center border border-gray-100">
                            <i class="fas fa-users text-gray-500 text-xl mb-2"></i>
                            <div class="text-xs text-gray-500 mb-0.5">Kapasitas</div>
                            <div class="font-bold text-gray-800 text-sm">{{ $activity->capacity }} peserta</div>
                        </div>
                        <div class="bg-gray-50 rounded-xl p-4 text-center border border-gray-100">
                            <i class="fas fa-ticket-alt text-gray-500 text-xl mb-2"></i>
                            <div class="text-xs text-gray-500 mb-0.5">Sisa Slot</div>
                            <div class="font-bold {{ $activity->available_slots > 0 ? 'text-green-600' : 'text-red-500' }} text-sm">
                                {{ $activity->available_slots }} slot
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 p-3 bg-amber-50 border border-amber-200 rounded-lg flex items-center gap-3 text-sm">
                        <i class="fas fa-hourglass-half text-amber-500 flex-shrink-0"></i>
                        <span class="text-amber-800">Batas pendaftaran: <strong>{{ $activity->registration_deadline->translatedFormat('l, d F Y') }} pukul {{ $activity->registration_deadline->format('H:i') }} WIB</strong></span>
                    </div>
                </div>

                {{-- PEMATERI --}}
                @if($activity->speakers && count($activity->speakers) > 0)
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h3 class="font-semibold text-gray-900 mb-4 flex items-center gap-2">
                        <span class="w-7 h-7 bg-green-600 text-white rounded-full flex items-center justify-center text-xs">
                            <i class="fas fa-chalkboard-teacher"></i>
                        </span>
                        Pemateri
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @foreach($activity->speakers as $speaker)
                            @if(!empty($speaker['name']))
                            <div class="flex items-center gap-4 p-4 bg-gray-50 rounded-xl border border-gray-100">
                                <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0">
                                    <span class="text-lg font-bold text-green-700">
                                        {{ strtoupper(substr($speaker['name'], 0, 1)) }}
                                    </span>
                                </div>
                                <div class="min-w-0">
                                    <p class="font-semibold text-gray-900 truncate">{{ $speaker['name'] }}</p>
                                    @if(!empty($speaker['title']))
                                        <p class="text-sm text-gray-500 truncate">{{ $speaker['title'] }}</p>
                                    @endif
                                </div>
                            </div>
                            @endif
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- BENEFIT --}}
                @if($activity->benefits && count(array_filter($activity->benefits)) > 0)
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h3 class="font-semibold text-gray-900 mb-4 flex items-center gap-2">
                        <span class="w-7 h-7 bg-green-600 text-white rounded-full flex items-center justify-center text-xs">
                            <i class="fas fa-gift"></i>
                        </span>
                        Yang Akan Kamu Dapatkan
                    </h3>
                    <ul class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        @foreach($activity->benefits as $benefit)
                            @if(!empty($benefit))
                            <li class="flex items-center gap-3">
                                <span class="flex-shrink-0 w-6 h-6 rounded-full bg-green-100 flex items-center justify-center">
                                    <i class="fas fa-check text-green-600 text-xs"></i>
                                </span>
                                <span class="text-gray-700 text-sm">{{ $benefit }}</span>
                            </li>
                            @endif
                        @endforeach
                    </ul>
                </div>
                @endif

                {{-- PETA LOKASI --}}
                @if($activity->latitude && $activity->longitude)
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h3 class="font-semibold text-gray-900 mb-4 flex items-center gap-2">
                        <span class="w-7 h-7 bg-green-600 text-white rounded-full flex items-center justify-center text-xs">
                            <i class="fas fa-map-marker-alt"></i>
                        </span>
                        Lokasi Kegiatan
                    </h3>
                    <div id="activity-detail-map" style="height: 350px; width: 100%; border-radius: 12px;"></div>
                    <div class="mt-3 text-sm text-gray-500">
                        <i class="fas fa-map-marker-alt text-green-500 mr-1"></i>{{ $activity->location }}
                    </div>
                </div>
                @endif

                {{-- ULASAN --}}
                @if($activity->ratings && $activity->ratings->count() > 0)
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h3 class="font-semibold text-gray-900 mb-4 flex items-center gap-2">
                        <span class="w-7 h-7 bg-green-600 text-white rounded-full flex items-center justify-center text-xs">
                            <i class="fas fa-star"></i>
                        </span>
                        Ulasan ({{ $activity->ratings->count() }})
                    </h3>
                    <div class="space-y-4">
                        @foreach($activity->ratings as $rating)
                        <div class="border-b border-gray-100 pb-4 last:border-b-0">
                            <div class="flex items-center justify-between mb-2">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                        <span class="text-sm font-medium text-green-600">{{ substr($rating->user->name, 0, 1) }}</span>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900 text-sm">{{ $rating->user->name }}</p>
                                        <div class="flex items-center gap-0.5">
                                            @for($i = 1; $i <= 5; $i++)
                                                <i class="fas fa-star {{ $i <= $rating->rating ? 'text-yellow-400' : 'text-gray-200' }} text-xs"></i>
                                            @endfor
                                        </div>
                                    </div>
                                </div>
                                <span class="text-xs text-gray-400">{{ $rating->created_at->format('d M Y') }}</span>
                            </div>
                            @if($rating->review)
                                <p class="text-gray-600 text-sm">{{ $rating->review }}</p>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- FORM ULASAN (hanya jika canRate) --}}
                @auth
                @if($canRate)
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h3 class="font-semibold text-gray-900 mb-4">Tulis Ulasan</h3>
                    <form id="ratingForm" class="space-y-4">
                        @csrf
                        <input type="hidden" name="type" value="activity">
                        <input type="hidden" name="id" value="{{ $activity->id }}">

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Rating</label>
                            <div class="flex space-x-2">
                                @for($i = 1; $i <= 5; $i++)
                                    <label class="cursor-pointer">
                                        <input type="radio" name="rating" value="{{ $i }}" class="hidden" {{ isset($userRating) && $userRating && $userRating->rating == $i ? 'checked' : '' }}>
                                        <i class="fas fa-star text-2xl {{ isset($userRating) && $userRating && $userRating->rating >= $i ? 'text-yellow-400' : 'text-gray-300' }}"
                                           onclick="this.previousElementSibling.checked = true; highlightStars(this, {{ $i }})"></i>
                                    </label>
                                @endfor
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Ulasan (opsional)</label>
                            <textarea name="review" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent text-sm">{{ $userRating->review ?? '' }}</textarea>
                        </div>

                        <div class="flex items-center gap-3">
                            <button type="button" onclick="submitRating()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium text-sm">Simpan Ulasan</button>
                            @if(isset($userRating) && $userRating)
                                <button type="button" onclick="deleteRating({{ $activity->id }}, 'activity')" class="text-red-600 hover:text-red-700 font-medium text-sm">Hapus Ulasan</button>
                            @endif
                        </div>
                    </form>
                </div>
                @endif
                @endauth

            </div>

            {{-- ══ KOLOM KANAN: SIDEBAR ══ --}}
            <div class="lg:col-span-1">
                <div class="sidebar-card space-y-4">

                    {{-- Kartu Harga & Daftar --}}
                    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                        <div class="mb-5">
                            @if($activity->discount_type && $activity->discount_value)
                                <div class="price-old mb-1">Rp {{ number_format($activity->price, 0, ',', '.') }}</div>
                                <div class="price-main">Rp {{ number_format($activity->getDiscountedPrice(), 0, ',', '.') }}</div>
                                <div class="mt-1 inline-flex items-center gap-1 bg-green-50 text-green-700 text-xs font-semibold px-2 py-0.5 rounded-full">
                                    <i class="fas fa-tag text-xs"></i>
                                    @if($activity->discount_type === 'percentage')
                                        Hemat {{ $activity->discount_value }}%
                                    @else
                                        Hemat Rp {{ number_format($activity->discount_value, 0, ',', '.') }}
                                    @endif
                                </div>
                            @else
                                <div class="price-main">Rp {{ number_format($activity->price, 0, ',', '.') }}</div>
                            @endif
                            <div class="text-sm text-gray-400 mt-1">per peserta</div>
                        </div>

                        {{-- Info singkat --}}
                        <div class="mb-5">
                            <div class="stat-row">
                                <span class="text-sm text-gray-500">Tanggal</span>
                                <span class="text-sm font-semibold text-gray-800">{{ $activity->event_date->format('d M Y') }}</span>
                            </div>
                            <div class="stat-row">
                                <span class="text-sm text-gray-500">Waktu</span>
                                <span class="text-sm font-semibold text-gray-800">{{ $activity->event_date->format('H:i') }} WIB</span>
                            </div>
                            <div class="stat-row">
                                <span class="text-sm text-gray-500">Lokasi</span>
                                <span class="text-sm font-semibold text-gray-800 text-right max-w-[140px]">{{ Str::limit($activity->location, 25) }}</span>
                            </div>
                            <div class="stat-row">
                                <span class="text-sm text-gray-500">Sisa Slot</span>
                                <span class="text-sm font-semibold {{ $activity->available_slots > 0 ? 'text-green-600' : 'text-red-500' }}">
                                    {{ $activity->available_slots }} slot
                                </span>
                            </div>
                            <div class="stat-row">
                                <span class="text-sm text-gray-500">Batas Daftar</span>
                                <span class="text-sm font-semibold text-gray-800">{{ $activity->registration_deadline->format('d M Y') }}</span>
                            </div>
                        </div>

                        {{-- Tombol Daftar --}}
                        @if($isRegistrationOpen)
                            @if($activity->available_slots > 0)
                                @auth
                                    <a href="{{ route('user.bookings.create', ['type' => 'activity', 'id' => $activity->id]) }}"
                                       class="w-full bg-green-600 hover:bg-green-700 text-white py-3 px-4 rounded-xl font-semibold text-sm transition-colors text-center block">
                                        <i class="fas fa-calendar-plus mr-2"></i>Daftar Sekarang
                                    </a>
                                @else
                                    <a href="{{ route('login') }}"
                                       class="w-full bg-green-600 hover:bg-green-700 text-white py-3 px-4 rounded-xl font-semibold text-sm transition-colors text-center block">
                                        <i class="fas fa-sign-in-alt mr-2"></i>Masuk untuk Daftar
                                    </a>
                                @endauth
                            @else
                                <div class="w-full bg-gray-100 text-gray-500 py-3 px-4 rounded-xl font-semibold text-sm text-center">
                                    <i class="fas fa-users-slash mr-2"></i>Kuota Penuh
                                </div>
                            @endif
                        @else
                            <div class="w-full bg-red-50 text-red-600 border border-red-200 py-3 px-4 rounded-xl font-semibold text-sm text-center">
                                <i class="fas fa-lock mr-2"></i>Pendaftaran Ditutup
                            </div>
                        @endif
                    </div>

                    {{-- Info Penyelenggara --}}
                    <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100">
                        <h3 class="font-semibold text-gray-900 mb-4 text-sm">Penyelenggara</h3>
                        <div class="flex items-center gap-3">
                            <div class="relative flex-shrink-0">
                                <div class="w-11 h-11 bg-green-100 rounded-full flex items-center justify-center">
                                    <span class="font-bold text-green-600">{{ substr($activity->provider->name, 0, 1) }}</span>
                                </div>
                                @if($activity->provider->isOnline())
                                    <span class="absolute -bottom-0.5 -right-0.5 w-3.5 h-3.5 bg-green-500 border-2 border-white rounded-full"></span>
                                @endif
                            </div>
                            <div class="min-w-0">
                                <p class="font-semibold text-gray-900 text-sm truncate">{{ $activity->provider->name }}</p>
                                <p class="text-xs text-gray-500 truncate">{{ $activity->provider->email }}</p>
                                <p class="text-xs mt-0.5 {{ $activity->provider->isOnline() ? 'text-green-600 font-medium' : 'text-gray-400' }}">
                                    <i class="fas fa-circle text-[8px] mr-1"></i>{{ $activity->provider->getLastSeenLabel() }}
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Bagikan --}}
                    <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100">
                        <p class="text-sm font-semibold text-gray-700 mb-3">Bagikan Kegiatan Ini</p>
                        <div class="flex gap-2">
                            <a href="https://wa.me/?text={{ urlencode($activity->name . ' - ' . request()->url()) }}"
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

@auth
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

@if($canRate)
function highlightStars(el, rating) {
    const stars = el.parentElement.parentElement.querySelectorAll('i');
    stars.forEach((star, index) => {
        if (index < rating) {
            star.classList.remove('text-gray-300');
            star.classList.add('text-yellow-400');
        } else {
            star.classList.remove('text-yellow-400');
            star.classList.add('text-gray-300');
        }
    });
}

function submitRating() {
    const form = document.getElementById('ratingForm');
    const formData = new FormData(form);
    const submitBtn = form.querySelector('button[onclick="submitRating()"]');
    const originalText = submitBtn.textContent;
    submitBtn.textContent = 'Menyimpan...';
    submitBtn.disabled = true;

    fetch('{{ route('user.ratings.store') }}', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.status === 'success') {
            tampilToast(data.message || 'Ulasan disimpan', 'success');
        } else {
            tampilToast(data.message || 'Gagal menyimpan ulasan', 'error');
        }
    })
    .catch(() => tampilToast('Terjadi kesalahan saat menyimpan ulasan', 'error'))
    .finally(() => { submitBtn.textContent = originalText; submitBtn.disabled = false; });
}

function deleteRating(id, type) {
    if (!confirm('Apakah Anda yakin ingin menghapus ulasan ini?')) return;
    fetch('{{ route('user.ratings.destroy') }}', {
        method: 'DELETE',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
        body: JSON.stringify({ type, id })
    })
    .then(r => r.json())
    .then(data => {
        if (data.status === 'success') {
            tampilToast(data.message || 'Ulasan dihapus', 'success');
            document.querySelectorAll('#ratingForm input[name="rating"]').forEach(i => i.checked = false);
            document.querySelector('#ratingForm textarea[name="review"]').value = '';
            document.querySelectorAll('#ratingForm .fas.fa-star').forEach(s => { s.classList.remove('text-yellow-400'); s.classList.add('text-gray-300'); });
        } else {
            tampilToast(data.message || 'Gagal menghapus ulasan', 'error');
        }
    })
    .catch(() => tampilToast('Terjadi kesalahan saat menghapus ulasan', 'error'));
}
@endif
@endauth

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

@if($activity->latitude && $activity->longitude)
document.addEventListener('DOMContentLoaded', () => {
    const map = L.map('activity-detail-map').setView([{{ $activity->latitude }}, {{ $activity->longitude }}], 15);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OpenStreetMap contributors' }).addTo(map);
    L.marker([{{ $activity->latitude }}, {{ $activity->longitude }}])
        .addTo(map)
        .bindPopup('<strong>{{ addslashes($activity->name) }}</strong><br>{{ addslashes($activity->location) }}')
        .openPopup();
});
@endif
</script>
@endpush
