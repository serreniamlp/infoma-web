@extends('layouts.app')

@section('title', 'Hunian - Infoma')

@section('content')
    <div class="min-h-screen bg-gray-50">

        {{-- ── Hero Search ── --}}
        <div class="bg-gradient-to-r from-blue-700 to-blue-500 py-10 px-4">
            <div class="max-w-5xl mx-auto text-center">
                <h1 class="text-3xl md:text-4xl font-bold text-white mb-1">Temukan Hunian Idealmu</h1>
                <p class="text-blue-100 mb-6 text-sm md:text-base">Kos, kontrakan, dan apartemen terjangkau di sekitar kampus
                </p>
                <form method="GET" action="{{ route('residences.index') }}"
                    class="flex flex-col sm:flex-row gap-3 max-w-2xl mx-auto">
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Cari nama hunian atau lokasi..."
                        class="flex-1 px-5 py-3 rounded-xl text-gray-800 text-sm shadow-md focus:outline-none focus:ring-2 focus:ring-blue-300">
                    <button type="submit"
                        class="bg-white text-blue-700 font-semibold px-6 py-3 rounded-xl shadow-md hover:bg-blue-50 transition text-sm whitespace-nowrap">
                        <i class="fas fa-search mr-2"></i>Cari Hunian
                    </button>
                </form>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex flex-col lg:flex-row gap-8">

                {{-- ── Sidebar Filter ── --}}
                <aside class="lg:w-72 flex-shrink-0">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 sticky top-20">
                        <h3 class="text-base font-bold text-gray-800 mb-4 flex items-center gap-2">
                            <i class="fas fa-sliders-h text-blue-500"></i> Filter Pencarian
                        </h3>

                        <form method="GET" action="{{ route('residences.index') }}" class="space-y-5">
                            {{-- Kategori --}}
                            <div>
                                <label
                                    class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Kategori</label>
                                <select name="category_id"
                                    class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-400 focus:border-transparent bg-gray-50">
                                    <option value="">Semua Kategori</option>
                                    @foreach ($categories as $category)
                                        @if ($category->type === 'residence')
                                            <option value="{{ $category->id }}"
                                                {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                                {{ $category->name }}
                                            </option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>

                            {{-- Periode Sewa --}}
                            <div>
                                <label
                                    class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Periode
                                    Sewa</label>
                                <div class="flex gap-2">
                                    <label class="flex-1 cursor-pointer">
                                        <input type="radio" name="rental_period" value="" class="hidden peer"
                                            {{ !request('rental_period') ? 'checked' : '' }}>
                                        <span
                                            class="block text-center py-2 text-xs font-medium border border-gray-200 rounded-lg cursor-pointer peer-checked:bg-blue-600 peer-checked:text-white peer-checked:border-blue-600 hover:border-blue-400 transition">Semua</span>
                                    </label>
                                    <label class="flex-1 cursor-pointer">
                                        <input type="radio" name="rental_period" value="monthly" class="hidden peer"
                                            {{ request('rental_period') === 'monthly' ? 'checked' : '' }}>
                                        <span
                                            class="block text-center py-2 text-xs font-medium border border-gray-200 rounded-lg cursor-pointer peer-checked:bg-blue-600 peer-checked:text-white peer-checked:border-blue-600 hover:border-blue-400 transition">Bulanan</span>
                                    </label>
                                    <label class="flex-1 cursor-pointer">
                                        <input type="radio" name="rental_period" value="yearly" class="hidden peer"
                                            {{ request('rental_period') === 'yearly' ? 'checked' : '' }}>
                                        <span
                                            class="block text-center py-2 text-xs font-medium border border-gray-200 rounded-lg cursor-pointer peer-checked:bg-blue-600 peer-checked:text-white peer-checked:border-blue-600 hover:border-blue-400 transition">Tahunan</span>
                                    </label>
                                </div>
                            </div>

                            {{-- Rentang Harga --}}
                            <div>
                                <label
                                    class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Rentang
                                    Harga (Rp)</label>
                                <div class="flex gap-2">
                                    <input type="number" name="min_price" value="{{ request('min_price') }}"
                                        placeholder="Min"
                                        class="w-1/2 px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-400 focus:border-transparent bg-gray-50">
                                    <input type="number" name="max_price" value="{{ request('max_price') }}"
                                        placeholder="Max"
                                        class="w-1/2 px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-400 focus:border-transparent bg-gray-50">
                                </div>
                            </div>

                            {{-- Lokasi --}}
                            <div>
                                <label
                                    class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Lokasi</label>
                                <div class="relative">
                                    <i
                                        class="fas fa-map-marker-alt absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm pointer-events-none"></i>
                                    <input type="text" name="location" value="{{ request('location') }}"
                                        placeholder="Kota atau area..."
                                        class="w-full pl-9 pr-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-400 focus:border-transparent bg-gray-50">
                                </div>
                            </div>

                            {{-- Urutkan --}}
                            <div>
                                <label
                                    class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Urutkan
                                    Berdasarkan</label>
                                <select name="sort"
                                    class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-400 focus:border-transparent bg-gray-50">
                                    <option value="newest" {{ request('sort') === 'newest' ? 'selected' : '' }}>Terbaru
                                    </option>
                                    <option value="price_low" {{ request('sort') === 'price_low' ? 'selected' : '' }}>Harga
                                        Terendah</option>
                                    <option value="price_high" {{ request('sort') === 'price_high' ? 'selected' : '' }}>
                                        Harga Tertinggi</option>
                                    <option value="rating" {{ request('sort') === 'rating' ? 'selected' : '' }}>Rating
                                        Tertinggi</option>
                                </select>
                            </div>

                            <div class="flex gap-2 pt-1">
                                <button type="submit"
                                    class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-2.5 rounded-lg text-sm font-semibold transition">
                                    <i class="fas fa-filter mr-1"></i> Terapkan
                                </button>
                                <a href="{{ route('residences.index') }}"
                                    class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 py-2.5 rounded-lg text-sm font-semibold transition text-center">
                                    Reset
                                </a>
                            </div>
                        </form>
                    </div>
                </aside>

                {{-- ── Konten Utama ── --}}
                <div class="flex-1 min-w-0">
                    {{-- Header hasil --}}
                    <div class="flex items-center justify-between mb-5 flex-wrap gap-2">
                        <p class="text-gray-700 font-medium text-sm">
                            <span class="text-blue-600 font-bold text-base">{{ $residences->total() }}</span> hunian
                            ditemukan
                        </p>
                        <div class="flex flex-wrap gap-1.5">
                            @if (request('rental_period'))
                                <span
                                    class="bg-blue-50 text-blue-700 text-xs px-3 py-1 rounded-full border border-blue-200">
                                    {{ request('rental_period') === 'monthly' ? 'Bulanan' : 'Tahunan' }}
                                </span>
                            @endif
                            @if (request('location'))
                                <span
                                    class="bg-blue-50 text-blue-700 text-xs px-3 py-1 rounded-full border border-blue-200">
                                    <i class="fas fa-map-marker-alt mr-1"></i>{{ request('location') }}
                                </span>
                            @endif
                        </div>
                    </div>

                    @if ($residences->count() > 0)
                        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-5">
                            @foreach ($residences as $residence)
                                @php
                                    $isBookmarked = false;
                                    if (auth()->check() && auth()->user()->hasRole('user')) {
                                        $isBookmarked = auth()
                                            ->user()
                                            ->bookmarks()
                                            ->where('bookmarkable_type', 'App\\Models\\Residence')
                                            ->where('bookmarkable_id', $residence->id)
                                            ->exists();
                                    }
                                    $hasDiscount = $residence->discount_type && $residence->discount_value;
                                @endphp
                                <div
                                    class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 flex flex-col">

                                    {{-- Gambar --}}
                                    <div class="relative h-48 bg-gray-100 flex-shrink-0">
                                        @if ($residence->images && count($residence->images) > 0)
                                            <img src="{{ asset('storage/' . $residence->images[0]) }}"
                                                alt="{{ $residence->name }}" class="w-full h-full object-cover">
                                        @else
                                            <div
                                                class="w-full h-full flex flex-col items-center justify-center bg-gray-100 text-gray-300">
                                                <i class="fas fa-home text-4xl mb-1"></i>
                                                <span class="text-xs">Foto belum tersedia</span>
                                            </div>
                                        @endif

                                        @if ($hasDiscount)
                                            <span
                                                class="absolute top-3 left-3 bg-red-500 text-white text-xs font-bold px-2.5 py-1 rounded-full shadow">
                                                @if ($residence->discount_type === 'percentage')
                                                    {{ $residence->discount_value }}% OFF
                                                @else
                                                    DISKON
                                                @endif
                                            </span>
                                        @endif

                                        <span
                                            class="absolute top-3 right-3 bg-blue-600/90 backdrop-blur-sm text-white text-xs font-semibold px-2.5 py-1 rounded-full">
                                            {{ $residence->available_slots }} tersedia
                                        </span>

                                        @auth
                                            @if (auth()->user()->hasRole('user'))
                                                <button onclick="toggleBookmark('residence', {{ $residence->id }}, this)"
                                                    class="absolute bottom-3 right-3 w-9 h-9 rounded-full bg-white/90 shadow-md flex items-center justify-center transition hover:scale-110 {{ $isBookmarked ? 'text-red-500' : 'text-gray-400 hover:text-red-400' }}">
                                                    <i class="{{ $isBookmarked ? 'fas' : 'far' }} fa-heart"></i>
                                                </button>
                                            @endif
                                        @endauth
                                    </div>

                                    {{-- Isi kartu --}}
                                    <div class="p-4 flex flex-col flex-1">
                                        <div class="mb-1.5">
                                            <span
                                                class="text-xs text-blue-600 font-medium bg-blue-50 px-2 py-0.5 rounded-full">
                                                {{ $residence->category->name ?? 'Hunian' }}
                                            </span>
                                        </div>

                                        <h3 class="text-base font-bold text-gray-900 mb-1 line-clamp-1">
                                            {{ $residence->name }}</h3>

                                        <div class="flex items-center text-xs text-gray-500 mb-2 gap-1">
                                            <i class="fas fa-map-marker-alt text-blue-400 flex-shrink-0"></i>
                                            <span class="truncate">{{ Str::limit($residence->address, 45) }}</span>
                                        </div>

                                        <div class="flex items-center justify-between mb-3 text-xs text-gray-500">
                                            <span class="flex items-center gap-1">
                                                <i class="fas fa-calendar-alt text-gray-400"></i>
                                                {{ $residence->rental_period === 'monthly' ? 'Bulanan' : 'Tahunan' }}
                                            </span>
                                            @if ($residence->ratings_avg_rating)
                                                <span class="flex items-center gap-1 font-medium text-gray-700">
                                                    <i class="fas fa-star text-yellow-400"></i>
                                                    {{ number_format($residence->ratings_avg_rating, 1) }}
                                                    <span class="text-gray-400">({{ $residence->ratings_count }})</span>
                                                </span>
                                            @else
                                                <span class="flex items-center gap-1 text-gray-400">
                                                    <i class="far fa-star"></i> Belum ada ulasan
                                                </span>
                                            @endif
                                        </div>

                                        {{-- Harga + tombol — selalu di bawah --}}
                                        <div
                                            class="mt-auto pt-3 border-t border-gray-100 flex items-end justify-between gap-2">
                                            <div class="min-w-0">
                                                @if ($hasDiscount)
                                                    <div class="text-xs text-gray-400 line-through">Rp
                                                        {{ number_format($residence->price) }}</div>
                                                    <div class="text-lg font-extrabold text-blue-600 leading-tight">Rp
                                                        {{ number_format($residence->getDiscountedPrice()) }}</div>
                                                @else
                                                    <div class="text-lg font-extrabold text-blue-600 leading-tight">Rp
                                                        {{ number_format($residence->price) }}</div>
                                                @endif
                                                <span
                                                    class="text-xs text-gray-400">/{{ $residence->rental_period === 'monthly' ? 'bulan' : 'tahun' }}</span>
                                            </div>
                                            <a href="{{ route('residences.show', $residence) }}"
                                                class="flex-shrink-0 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold px-4 py-2 rounded-xl transition">
                                                Lihat Detail
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-8">{{ $residences->appends(request()->query())->links() }}</div>
                    @else
                        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm text-center py-20 px-6">
                            <div class="w-20 h-20 bg-blue-50 rounded-full flex items-center justify-center mx-auto mb-4">
                                <i class="fas fa-home text-blue-300 text-3xl"></i>
                            </div>
                            <h3 class="text-xl font-bold text-gray-800 mb-2">Hunian tidak ditemukan</h3>
                            <p class="text-gray-500 text-sm mb-6 max-w-sm mx-auto">Coba ubah kata kunci atau hapus beberapa
                                filter.</p>
                            <a href="{{ route('residences.index') }}"
                                class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-xl font-semibold text-sm transition">
                                <i class="fas fa-redo"></i> Tampilkan Semua Hunian
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function toggleBookmark(type, id, button) {
                const icon = button.querySelector('i');
                const isSaved = icon.classList.contains('fas');
                fetch(isSaved ? '{{ route('user.bookmarks.destroy') }}' : '{{ route('user.bookmarks.store') }}', {
                    method: isSaved ? 'DELETE' : 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        type,
                        id
                    })
                }).then(r => r.json()).then(data => {
                    if (data.message) {
                        icon.classList.toggle('fas', !isSaved);
                        icon.classList.toggle('far', isSaved);
                        button.classList.toggle('text-red-500', !isSaved);
                        button.classList.toggle('text-gray-400', isSaved);
                    }
                });
            }
        </script>
    @endpush
@endsection
