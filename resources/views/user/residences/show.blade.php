@extends('layouts.app')

@section('title', $residence->name . ' - Infoma')

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="{{ asset('css/leaflet-maps.css') }}">
    <style>
        /* ── Gallery Foto ── */
        .gallery-main {
            position: relative;
            border-radius: 12px;
            overflow: hidden;
            background: #111;
            aspect-ratio: 16/9;
        }

        .gallery-main img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: opacity .3s;
        }

        .gallery-thumbs {
            display: flex;
            gap: 8px;
            margin-top: 8px;
            overflow-x: auto;
            padding-bottom: 4px;
        }

        .gallery-thumbs::-webkit-scrollbar {
            height: 4px;
        }

        .gallery-thumbs::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }

        .thumb {
            flex-shrink: 0;
            width: 72px;
            height: 56px;
            border-radius: 8px;
            overflow: hidden;
            cursor: pointer;
            border: 2px solid transparent;
            transition: border-color .2s;
            opacity: .7;
        }

        .thumb.active {
            border-color: #2563eb;
            opacity: 1;
        }

        .thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .gallery-count {
            position: absolute;
            bottom: 12px;
            right: 12px;
            background: rgba(0, 0, 0, .55);
            color: #fff;
            font-size: 12px;
            padding: 4px 10px;
            border-radius: 20px;
            backdrop-filter: blur(4px);
        }

        /* ── Badge Fasilitas ── */
        .facility-tag {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #f0f9ff;
            color: #0369a1;
            border: 1px solid #bae6fd;
            border-radius: 8px;
            padding: 6px 12px;
            font-size: 13px;
        }

        /* ── Kartu Booking (sidebar) ── */
        .price-main {
            font-size: 2rem;
            font-weight: 700;
            color: #2563eb;
            line-height: 1.1;
        }

        .price-old {
            text-decoration: line-through;
            color: #9ca3af;
            font-size: .875rem;
        }

        .stat-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #f3f4f6;
        }

        .stat-row:last-child {
            border-bottom: none;
        }

        .sidebar-card {
            position: sticky;
            top: 90px;
        }
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

                {{-- ══ KOLOM KIRI: Konten Utama ══ --}}
                <div class="lg:col-span-2 space-y-6">

                    {{-- === GALLERY FOTO === --}}
                    <div class="bg-white rounded-xl shadow-sm overflow-hidden p-4">
                        @php $images = $residence->images ?? []; @endphp

                        @if (count($images) > 0)
                            {{-- Foto Utama (besar) --}}
                            <div class="gallery-main">
                                <img src="{{ asset('storage/' . $images[0]) }}" alt="{{ $residence->name }}"
                                    id="mainGalleryImg">
                                @if (count($images) > 1)
                                    <div class="gallery-count">
                                        <i class="fas fa-images mr-1"></i>
                                        <span id="imgCounter">1</span> / {{ count($images) }}
                                    </div>
                                @endif
                            </div>

                            {{-- Thumbnail di bawah (klik untuk ganti foto utama) --}}
                            @if (count($images) > 1)
                                <div class="gallery-thumbs" id="thumbStrip">
                                    @foreach ($images as $i => $img)
                                        <div class="thumb {{ $i === 0 ? 'active' : '' }}"
                                            onclick="gantiGambar('{{ asset('storage/' . $img) }}', {{ $i }})">
                                            <img src="{{ asset('storage/' . $img) }}" alt="foto {{ $i + 1 }}">
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        @else
                            {{-- Tidak ada foto --}}
                            <div class="w-full aspect-video bg-gray-100 rounded-xl flex items-center justify-center">
                                <div class="text-center text-gray-400">
                                    <i class="fas fa-home text-5xl mb-2"></i>
                                    <p class="text-sm">Belum ada foto</p>
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- === INFORMASI HUNIAN === --}}
                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <div class="flex items-start justify-between">
                            <div class="flex-1 min-w-0">
                                {{-- Badge kategori --}}
                                @if ($residence->category)
                                    <span
                                        class="inline-block bg-blue-50 text-blue-700 text-xs font-semibold px-3 py-1 rounded-full mb-3">
                                        {{ $residence->category->name }}
                                    </span>
                                @endif

                                <h1 class="text-2xl font-bold text-gray-900 mb-2">{{ $residence->name }}</h1>

                                <div class="flex items-start gap-2 text-gray-500 text-sm mb-3">
                                    <i class="fas fa-map-marker-alt text-blue-500 mt-0.5 flex-shrink-0"></i>
                                    <span>{{ $residence->address }}</span>
                                </div>

                                {{-- Rating bintang --}}
                                @if (isset($residence->ratings_avg_rating) && $residence->ratings_avg_rating)
                                    <div class="flex items-center gap-2">
                                        <div class="flex">
                                            @for ($i = 1; $i <= 5; $i++)
                                                <i
                                                    class="fas fa-star text-sm {{ $i <= round($residence->ratings_avg_rating) ? 'text-yellow-400' : 'text-gray-200' }}"></i>
                                            @endfor
                                        </div>
                                        <span class="text-sm font-semibold text-gray-800">
                                            {{ number_format($residence->ratings_avg_rating, 1) }}
                                        </span>
                                        <span class="text-sm text-gray-500">
                                            ({{ $residence->ratings_count ?? 0 }} ulasan)
                                        </span>
                                    </div>
                                @endif
                            </div>

                            {{-- Tombol Simpan (Bookmark) --}}
                            @auth
                                <button onclick="toggleBookmark({{ $residence->id }}, 'residence')" id="bookmarkBtn"
                                    class="flex-shrink-0 ml-4 p-2.5 rounded-full border transition-all
                                {{ $isBookmarked
                                    ? 'bg-red-50 border-red-200 text-red-500'
                                    : 'bg-gray-50 border-gray-200 text-gray-400 hover:border-red-200 hover:text-red-400' }}">
                                    <i class="fas fa-heart text-lg"></i>
                                </button>
                            @endauth
                        </div>

                        {{-- Deskripsi --}}
                        <div class="mt-5 pt-5 border-t border-gray-100">
                            <h3 class="font-semibold text-gray-900 mb-2">Deskripsi</h3>
                            <p class="text-gray-600 leading-relaxed text-sm">{{ $residence->description }}</p>
                        </div>
                    </div>

                    {{-- === FASILITAS === --}}
                    @if ($residence->facilities && count($residence->facilities) > 0)
                        <div class="bg-white rounded-xl shadow-sm p-6">
                            <h3 class="font-semibold text-gray-900 mb-4">
                                <i class="fas fa-concierge-bell text-blue-500 mr-2"></i>Fasilitas
                            </h3>
                            @php
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
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                                @foreach ($residence->facilities as $facility)
                                    <div class="facility-tag">
                                        <i
                                            class="fas {{ $facilityIcons[$facility] ?? 'fa-check-circle' }} text-blue-400 text-xs"></i>
                                        <span>{{ $facility }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- === PETA LOKASI === --}}
                    @if ($residence->latitude && $residence->longitude)
                        <div class="bg-white rounded-xl shadow-sm p-6">
                            <h3 class="font-semibold text-gray-900 mb-4">
                                <i class="fas fa-map-marker-alt text-blue-500 mr-2"></i>Lokasi Hunian
                            </h3>
                            <div id="residence-detail-map" style="height:320px; border-radius:10px; overflow:hidden;"></div>
                            <div class="mt-3 text-sm text-gray-600 space-y-1">
                                <p><i class="fas fa-map-pin text-gray-400 mr-2"></i>{{ $residence->address }}</p>
                                <p class="text-xs text-gray-400">
                                    Koordinat: {{ number_format($residence->latitude, 6) }},
                                    {{ number_format($residence->longitude, 6) }}
                                </p>
                            </div>
                        </div>
                    @endif

                    {{-- === FORM ULASAN === --}}
                    @auth
                        @if ($canRate)
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
                                            @for ($i = 1; $i <= 5; $i++)
                                                <label class="cursor-pointer">
                                                    <input type="radio" name="rating" value="{{ $i }}"
                                                        class="hidden"
                                                        {{ isset($userRating) && $userRating && $userRating->rating == $i ? 'checked' : '' }}>
                                                    <i class="fas fa-star text-2xl transition-colors
                                        {{ isset($userRating) && $userRating && $userRating->rating >= $i ? 'text-yellow-400' : 'text-gray-200' }}"
                                                        onclick="this.previousElementSibling.checked=true; tandaiBintang({{ $i }})"></i>
                                                </label>
                                            @endfor
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            Komentar <span class="text-gray-400 font-normal">(opsional)</span>
                                        </label>
                                        <textarea name="review" rows="3"
                                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                            placeholder="Bagikan pengalaman Anda tentang hunian ini...">{{ $userRating->review ?? '' }}</textarea>
                                    </div>

                                    <div class="flex items-center gap-3">
                                        <button type="button" onclick="kirimUlasan()"
                                            class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm rounded-lg font-medium transition-colors">
                                            Simpan Ulasan
                                        </button>
                                        @if (isset($userRating) && $userRating)
                                            <button type="button" onclick="hapusUlasan({{ $residence->id }}, 'residence')"
                                                class="text-red-500 hover:text-red-600 text-sm font-medium">
                                                Hapus Ulasan
                                            </button>
                                        @endif
                                    </div>
                                </form>
                            </div>
                        @endif
                    @endauth

                    {{-- === DAFTAR ULASAN === --}}
                    @if ($residence->ratings && $residence->ratings->count() > 0)
                        <div class="bg-white rounded-xl shadow-sm p-6">
                            <h3 class="font-semibold text-gray-900 mb-5">
                                Ulasan <span class="text-gray-400 font-normal">({{ $residence->ratings->count() }})</span>
                            </h3>
                            <div class="space-y-5">
                                @foreach ($residence->ratings as $rating)
                                    <div class="flex gap-4 {{ !$loop->last ? 'pb-5 border-b border-gray-100' : '' }}">
                                        <div
                                            class="w-9 h-9 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0">
                                            <span class="text-sm font-bold text-blue-600">
                                                {{ substr($rating->user->name, 0, 1) }}
                                            </span>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center justify-between mb-1">
                                                <p class="font-semibold text-gray-900 text-sm">{{ $rating->user->name }}
                                                </p>
                                                <span class="text-xs text-gray-400">
                                                    {{ $rating->created_at->format('d M Y') }}
                                                </span>
                                            </div>
                                            <div class="flex mb-2">
                                                @for ($i = 1; $i <= 5; $i++)
                                                    <i
                                                        class="fas fa-star text-xs {{ $i <= $rating->rating ? 'text-yellow-400' : 'text-gray-200' }}"></i>
                                                @endfor
                                            </div>
                                            @if ($rating->review)
                                                <p class="text-gray-600 text-sm leading-relaxed">{{ $rating->review }}</p>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                </div>

                {{-- ══ KOLOM KANAN: Sidebar ══ --}}
                <div class="lg:col-span-1">
                    <div class="sidebar-card space-y-4">

                        {{-- Kartu Harga & Booking --}}
                        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                            {{-- Harga --}}
                            <div class="mb-5">
                                @if ($residence->discount_type && $residence->discount_value)
                                    <div class="price-old mb-1">
                                        Rp {{ number_format($residence->price_per_month ?? $residence->price) }}
                                    </div>
                                    <div class="price-main">
                                        Rp {{ number_format($residence->getDiscountedPrice()) }}
                                    </div>
                                    <div
                                        class="mt-1 inline-flex items-center gap-1 bg-green-50 text-green-700 text-xs font-semibold px-2 py-0.5 rounded-full">
                                        <i class="fas fa-tag text-xs"></i>
                                        @if ($residence->discount_type === 'percentage')
                                            Hemat {{ $residence->discount_value }}%
                                        @else
                                            Hemat Rp {{ number_format($residence->discount_value) }}
                                        @endif
                                    </div>
                                @else
                                    <div class="price-main">
                                        Rp {{ number_format($residence->price_per_month ?? $residence->price) }}
                                    </div>
                                @endif
                                <div class="text-sm text-gray-400 mt-1">
                                    per {{ $residence->rental_period === 'monthly' ? 'bulan' : 'tahun' }}
                                </div>
                            </div>

                            {{-- Info singkat --}}
                            <div class="mb-5">
                                <div class="stat-row">
                                    <span class="text-sm text-gray-500">Kapasitas</span>
                                    <span class="text-sm font-semibold text-gray-800">{{ $residence->capacity }}
                                        orang</span>
                                </div>
                                <div class="stat-row">
                                    <span class="text-sm text-gray-500">Slot Tersedia</span>
                                    <span
                                        class="text-sm font-semibold {{ $residence->available_slots > 0 ? 'text-green-600' : 'text-red-500' }}">
                                        {{ $residence->available_slots }} slot
                                    </span>
                                </div>
                                <div class="stat-row">
                                    <span class="text-sm text-gray-500">Periode Sewa</span>
                                    <span class="text-sm font-semibold text-gray-800">
                                        {{ $residence->rental_period === 'monthly' ? 'Bulanan' : 'Tahunan' }}
                                    </span>
                                </div>
                            </div>

                            {{-- Tombol Booking --}}
                            @if ($residence->available_slots > 0)
                                @auth
                                    <a href="{{ route('user.bookings.create', ['type' => 'residence', 'id' => $residence->id]) }}"
                                        class="w-full bg-blue-600 hover:bg-blue-700 text-white py-3 px-4 rounded-xl font-semibold text-sm transition-colors text-center block">
                                        <i class="fas fa-calendar-plus mr-2"></i>Booking Sekarang
                                    </a>
                                @else
                                    <a href="{{ route('login') }}"
                                        class="w-full bg-blue-600 hover:bg-blue-700 text-white py-3 px-4 rounded-xl font-semibold text-sm transition-colors text-center block">
                                        <i class="fas fa-sign-in-alt mr-2"></i>Masuk untuk Booking
                                    </a>
                                @endauth
                            @else
                                <div
                                    class="w-full bg-gray-100 text-gray-500 py-3 px-4 rounded-xl font-semibold text-sm text-center">
                                    <i class="fas fa-times-circle mr-2"></i>Tidak Tersedia
                                </div>
                            @endif
                        </div>

                        {{-- Info Penyedia --}}
                        <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100">
                            <h3 class="font-semibold text-gray-900 mb-4 text-sm">Penyedia Hunian</h3>
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-11 h-11 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0">
                                    <span class="font-bold text-blue-600">
                                        {{ substr($residence->provider->name, 0, 1) }}
                                    </span>
                                </div>
                                <div class="min-w-0">
                                    <p class="font-semibold text-gray-900 text-sm truncate">
                                        {{ $residence->provider->name }}
                                    </p>
                                    <p class="text-xs text-gray-500 truncate">
                                        {{ $residence->provider->email }}
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
        // ── FITUR: Ganti foto utama saat thumbnail diklik ─────────────
        function gantiGambar(src, index) {
            const main = document.getElementById('mainGalleryImg');
            const counter = document.getElementById('imgCounter');
            const thumbs = document.querySelectorAll('.thumb');

            // Efek fade saat ganti foto
            main.style.opacity = 0;
            setTimeout(() => {
                main.src = src;
                main.style.opacity = 1;
            }, 200);

            // Update counter & border thumbnail aktif
            if (counter) counter.textContent = index + 1;
            thumbs.forEach((t, i) => t.classList.toggle('active', i === index));
        }

        // ── FITUR: Simpan / batalkan bookmark ─────────────────────────
        function toggleBookmark(id, type) {
            const btn = document.getElementById('bookmarkBtn');
            const sudahDisimpan = btn.classList.contains('bg-red-50');
            const url = sudahDisimpan ? '{{ route('user.bookmarks.destroy') }}' : '{{ route('user.bookmarks.store') }}';
            const method = sudahDisimpan ? 'DELETE' : 'POST';

            fetch(url, {
                    method,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        type,
                        id
                    })
                })
                .then(r => r.json())
                .then(data => {
                    if (data.message) {
                        // Toggle tampilan tombol
                        btn.classList.toggle('bg-red-50', !sudahDisimpan);
                        btn.classList.toggle('border-red-200', !sudahDisimpan);
                        btn.classList.toggle('text-red-500', !sudahDisimpan);
                        btn.classList.toggle('bg-gray-50', sudahDisimpan);
                        btn.classList.toggle('border-gray-200', sudahDisimpan);
                        btn.classList.toggle('text-gray-400', sudahDisimpan);
                        tampilToast(sudahDisimpan ? 'Dihapus dari simpanan' : 'Berhasil disimpan', 'success');
                    }
                })
                .catch(() => tampilToast('Terjadi kesalahan.', 'error'));
        }

        // ── FITUR: Sorot bintang saat memilih rating ──────────────────
        function tandaiBintang(nilai) {
            document.querySelectorAll('#starContainer i').forEach((bintang, i) => {
                bintang.classList.toggle('text-yellow-400', i < nilai);
                bintang.classList.toggle('text-gray-200', i >= nilai);
            });
        }

        // ── FITUR: Kirim ulasan via AJAX ──────────────────────────────
        function kirimUlasan() {
            const form = document.getElementById('ratingForm');
            const btn = form.querySelector('button[onclick="kirimUlasan()"]');
            btn.textContent = 'Menyimpan...';
            btn.disabled = true;

            fetch('{{ route('user.ratings.store') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: new FormData(form)
                })
                .then(r => r.json())
                .then(data => tampilToast(data.message || 'Ulasan disimpan', data.status === 'success' ? 'success' :
                    'error'))
                .catch(() => tampilToast('Terjadi kesalahan.', 'error'))
                .finally(() => {
                    btn.textContent = 'Simpan Ulasan';
                    btn.disabled = false;
                });
        }

        // ── FITUR: Hapus ulasan via AJAX ──────────────────────────────
        function hapusUlasan(id, type) {
            if (!confirm('Hapus ulasan ini?')) return;
            fetch('{{ route('user.ratings.destroy') }}', {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        type,
                        id
                    })
                })
                .then(r => r.json())
                .then(data => {
                    tampilToast(data.message || 'Ulasan dihapus', data.status === 'success' ? 'success' : 'error');
                    if (data.status === 'success') {
                        document.querySelectorAll('#starContainer i').forEach(b => {
                            b.classList.add('text-gray-200');
                            b.classList.remove('text-yellow-400');
                        });
                        document.querySelector('#ratingForm textarea[name="review"]').value = '';
                    }
                });
        }

        // ── FITUR: Salin link halaman ini ─────────────────────────────
        function salinLink() {
            navigator.clipboard.writeText(window.location.href)
                .then(() => tampilToast('Link berhasil disalin!', 'success'));
        }

        // ── UTILITAS: Tampilkan notifikasi sementara ──────────────────
        function tampilToast(pesan, tipe = 'success') {
            const t = document.createElement('div');
            t.className = `fixed top-4 right-4 z-50 px-5 py-3 rounded-xl shadow-lg text-sm font-medium text-white
        ${tipe === 'success' ? 'bg-green-500' : 'bg-red-500'}`;
            t.innerHTML = `<i class="fas fa-${tipe === 'success' ? 'check' : 'exclamation'}-circle mr-2"></i>${pesan}`;
            document.body.appendChild(t);
            setTimeout(() => t.remove(), 3000);
        }

        // ── Inisialisasi peta lokasi ──────────────────────────────────
        @if ($residence->latitude && $residence->longitude)
            document.addEventListener('DOMContentLoaded', () => {
                const peta = L.map('residence-detail-map').setView(
                    [{{ $residence->latitude }}, {{ $residence->longitude }}], 15
                );
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© OpenStreetMap contributors'
                }).addTo(peta);
                L.marker([{{ $residence->latitude }}, {{ $residence->longitude }}])
                    .addTo(peta)
                    .bindPopup(
                        '<strong>{{ addslashes($residence->name) }}</strong><br>{{ addslashes($residence->address) }}'
                        )
                    .openPopup();
            });
        @endif
    </script>
@endpush
