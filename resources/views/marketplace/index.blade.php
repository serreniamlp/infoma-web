@extends('layouts.app')

@section('title', 'Marketplace Barang - INFOMA')

@section('content')

    {{-- HERO BANNER --}}
    <div class="relative bg-gradient-to-r from-orange-500 to-orange-600 overflow-hidden">
        <div class="absolute inset-0 opacity-10">
            <svg class="w-full h-full" viewBox="0 0 100 100" preserveAspectRatio="none">
                <defs>
                    <pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse">
                        <path d="M 10 0 L 0 0 0 10" fill="none" stroke="white" stroke-width="0.5" />
                    </pattern>
                </defs>
                <rect width="100" height="100" fill="url(#grid)" />
            </svg>
        </div>
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 text-center">
            <h1 class="text-3xl md:text-4xl font-bold text-white mb-2">Marketplace Barang Mahasiswa</h1>
            <p class="text-orange-100 mb-8 text-base md:text-lg">Jual & beli barang bekas berkualitas sesama mahasiswa</p>
            {{-- Search bar --}}
            <form method="GET" action="{{ route('marketplace.index') }}" class="max-w-2xl mx-auto">
                <div class="flex rounded-xl overflow-hidden shadow-lg">
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Cari produk, kategori, atau lokasi..."
                        class="flex-1 px-5 py-4 text-gray-900 text-sm focus:outline-none">
                    <button type="submit"
                        class="bg-orange-700 hover:bg-orange-800 text-white px-6 py-4 font-semibold transition-colors flex items-center gap-2 whitespace-nowrap">
                        <i class="fas fa-search"></i>
                        <span class="hidden sm:inline">Cari</span>
                    </button>
                </div>
                {{-- Preserve other filters --}}
                @foreach (['category', 'condition', 'min_price', 'max_price', 'location', 'sort'] as $param)
                    @if (request($param))
                        <input type="hidden" name="{{ $param }}" value="{{ request($param) }}">
                    @endif
                @endforeach
            </form>
            {{-- Quick stats --}}
            <div class="flex justify-center gap-8 mt-8 text-orange-100 text-sm">
                <div class="flex items-center gap-2">
                    <i class="fas fa-box"></i>
                    <span>{{ $products->total() }} Produk Aktif</span>
                </div>
                <div class="flex items-center gap-2">
                    <i class="fas fa-shield-alt"></i>
                    <span>Transaksi Aman</span>
                </div>
                <div class="flex items-center gap-2">
                    <i class="fas fa-users"></i>
                    <span>Sesama Mahasiswa</span>
                </div>
            </div>
        </div>
    </div>

    {{-- MAIN CONTENT --}}
    <div class="min-h-screen bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

            {{-- Flash Messages --}}
            @if (session('success'))
                <div
                    class="mb-4 flex items-center gap-3 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg">
                    <i class="fas fa-check-circle text-green-500"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            <div class="flex flex-col lg:flex-row gap-8">

                {{-- ==================== SIDEBAR FILTER ==================== --}}
                <aside class="w-full lg:w-72 flex-shrink-0">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden sticky top-24">
                        <div class="bg-orange-500 px-5 py-4">
                            <h2 class="text-white font-bold text-sm uppercase tracking-wider flex items-center gap-2">
                                <i class="fas fa-sliders-h"></i> Filter Produk
                            </h2>
                        </div>
                        <form method="GET" action="{{ route('marketplace.index') }}" class="p-5 space-y-5">
                            {{-- Preserve sort --}}
                            @if (request('sort'))
                                <input type="hidden" name="sort" value="{{ request('sort') }}">
                            @endif

                            {{-- Kategori --}}
                            <div>
                                <label
                                    class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-2">Kategori</label>
                                <select name="category"
                                    class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 bg-gray-50">
                                    <option value="">Semua Kategori</option>
                                    @foreach ($categories as $cat)
                                        <option value="{{ $cat->id }}"
                                            {{ request('category') == $cat->id ? 'selected' : '' }}>
                                            {{ $cat->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Kondisi --}}
                            <div>
                                <label
                                    class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-2">Kondisi</label>
                                <div class="space-y-2">
                                    @php
                                        $conditions = [
                                            'new' => ['label' => 'Baru', 'color' => 'text-green-700'],
                                            'like_new' => ['label' => 'Seperti Baru', 'color' => 'text-blue-700'],
                                            'good' => ['label' => 'Baik', 'color' => 'text-orange-700'],
                                            'fair' => ['label' => 'Cukup', 'color' => 'text-yellow-700'],
                                            'needs_repair' => ['label' => 'Perlu Perbaikan', 'color' => 'text-red-700'],
                                        ];
                                    @endphp
                                    @foreach ($conditions as $val => $info)
                                        <label class="flex items-center gap-3 cursor-pointer group">
                                            <input type="radio" name="condition" value="{{ $val }}"
                                                {{ request('condition') == $val ? 'checked' : '' }}
                                                class="accent-orange-500">
                                            <span
                                                class="text-sm text-gray-700 group-hover:text-orange-600">{{ $info['label'] }}</span>
                                        </label>
                                    @endforeach
                                    @if (request('condition'))
                                        <label class="flex items-center gap-3 cursor-pointer group">
                                            <input type="radio" name="condition" value=""
                                                {{ !request('condition') ? 'checked' : '' }} class="accent-orange-500">
                                            <span class="text-sm text-gray-500 group-hover:text-orange-600">Semua
                                                Kondisi</span>
                                        </label>
                                    @endif
                                </div>
                            </div>

                            {{-- Rentang Harga --}}
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-2">Rentang
                                    Harga</label>
                                <div class="flex gap-2">
                                    <div class="flex-1">
                                        <input type="number" name="min_price" value="{{ request('min_price') }}"
                                            placeholder="Min"
                                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 bg-gray-50">
                                    </div>
                                    <div class="flex-1">
                                        <input type="number" name="max_price" value="{{ request('max_price') }}"
                                            placeholder="Max"
                                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 bg-gray-50">
                                    </div>
                                </div>
                            </div>

                            {{-- Lokasi --}}
                            <div>
                                <label
                                    class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-2">Lokasi</label>
                                <input type="text" name="location" value="{{ request('location') }}"
                                    placeholder="Kota atau area..."
                                    class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 bg-gray-50">
                            </div>

                            {{-- Tombol aksi --}}
                            <div class="space-y-2 pt-1">
                                <button type="submit"
                                    class="w-full bg-orange-500 hover:bg-orange-600 text-white font-semibold py-2.5 rounded-lg transition-colors text-sm flex items-center justify-center gap-2">
                                    <i class="fas fa-filter"></i> Terapkan Filter
                                </button>
                                <a href="{{ route('marketplace.index') }}"
                                    class="w-full inline-block text-center border border-gray-200 text-gray-600 hover:bg-gray-50 font-medium py-2.5 rounded-lg transition-colors text-sm">
                                    <i class="fas fa-times mr-1"></i> Reset
                                </a>
                            </div>
                        </form>
                    </div>
                </aside>

                {{-- ==================== MAIN PRODUCTS ==================== --}}
                <div class="flex-1 min-w-0">

                    {{-- Top bar: count + sort + jual button --}}
                    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
                        <div>
                            <p class="text-sm text-gray-600">
                                Menampilkan <span class="font-semibold text-gray-900">{{ $products->count() }}</span>
                                dari <span class="font-semibold text-gray-900">{{ $products->total() }}</span> produk
                                @if (request()->hasAny(['search', 'category', 'condition', 'min_price', 'max_price', 'location']))
                                    <span class="ml-1 text-orange-600">(difilter)</span>
                                @endif
                            </p>
                        </div>
                        <div class="flex items-center gap-3 flex-wrap">
                            {{-- Sort --}}
                            <div class="flex items-center gap-2">
                                <label
                                    class="text-xs font-bold uppercase tracking-wider text-gray-500 whitespace-nowrap">Urutkan</label>
                                <select id="sortSelect"
                                    class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-orange-400 bg-white">
                                    <option value="created_at"
                                        {{ request('sort', 'created_at') == 'created_at' ? 'selected' : '' }}>Terbaru
                                    </option>
                                    <option value="price_asc" {{ request('sort') == 'price_asc' ? 'selected' : '' }}>Harga
                                        Terendah</option>
                                    <option value="price_desc" {{ request('sort') == 'price_desc' ? 'selected' : '' }}>
                                        Harga
                                        Tertinggi</option>
                                    <option value="views" {{ request('sort') == 'views' ? 'selected' : '' }}>Paling
                                        Dilihat</option>
                                    <option value="name" {{ request('sort') == 'name' ? 'selected' : '' }}>Nama A–Z
                                    </option>
                                </select>
                            </div>
                            {{-- Jual Produk button --}}
                            @auth
                                @if (auth()->user()->isSeller())
                                    <a href="{{ route('user.marketplace.seller.create') }}"
                                        class="inline-flex items-center gap-2 bg-orange-500 hover:bg-orange-600 text-white font-semibold px-4 py-2 rounded-lg text-sm transition-colors shadow-sm">
                                        <i class="fas fa-plus"></i> Jual Produk
                                    </a>
                                @endif
                            @endauth
                        </div>
                    </div>

                    {{-- ======== PRODUCT GRID ======== --}}
                    @if ($products->count() > 0)
                        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-5">
                            @foreach ($products as $product)
                                @php
                                    $conditionConfig = [
                                        'new' => [
                                            'label' => 'Baru',
                                            'bg' => 'bg-green-100',
                                            'text' => 'text-green-700',
                                        ],
                                        'like_new' => [
                                            'label' => 'Seperti Baru',
                                            'bg' => 'bg-blue-100',
                                            'text' => 'text-blue-700',
                                        ],
                                        'good' => [
                                            'label' => 'Baik',
                                            'bg' => 'bg-orange-100',
                                            'text' => 'text-orange-700',
                                        ],
                                        'fair' => [
                                            'label' => 'Cukup',
                                            'bg' => 'bg-yellow-100',
                                            'text' => 'text-yellow-700',
                                        ],
                                        'needs_repair' => [
                                            'label' => 'Perlu Perbaikan',
                                            'bg' => 'bg-red-100',
                                            'text' => 'text-red-700',
                                        ],
                                    ];
                                    $cond = $conditionConfig[$product->condition] ?? [
                                        'label' => $product->condition,
                                        'bg' => 'bg-gray-100',
                                        'text' => 'text-gray-700',
                                    ];
                                @endphp
                                <div
                                    class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 flex flex-col group">
                                    {{-- Image --}}
                                    <div class="relative overflow-hidden">
                                        <a href="{{ route('marketplace.show', $product) }}">
                                            <img src="{{ $product->main_image }}" alt="{{ $product->name }}"
                                                class="w-full h-48 object-cover group-hover:scale-105 transition-transform duration-300">
                                        </a>
                                        {{-- Condition badge --}}
                                        <div class="absolute top-3 left-3">
                                            <span
                                                class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $cond['bg'] }} {{ $cond['text'] }}">
                                                {{ $cond['label'] }}
                                            </span>
                                        </div>
                                        {{-- Bookmark button --}}
                                        @auth
                                            <button
                                                class="bookmark-btn absolute top-3 right-3 w-8 h-8 rounded-full bg-white shadow flex items-center justify-center transition-colors hover:bg-orange-50"
                                                data-product-id="{{ $product->id }}"
                                                data-bookmarked="{{ $product->isBookmarkedBy(auth()->id()) ? 'true' : 'false' }}">
                                                <i
                                                    class="fas fa-heart text-sm {{ $product->isBookmarkedBy(auth()->id()) ? 'text-red-500' : 'text-gray-300' }}"></i>
                                            </button>
                                        @endauth
                                    </div>

                                    {{-- Info --}}
                                    <div class="p-4 flex flex-col flex-1">
                                        {{-- Category --}}
                                        <p class="text-xs text-orange-600 font-medium uppercase tracking-wide mb-1">
                                            {{ $product->category->name ?? '—' }}
                                        </p>
                                        {{-- Name --}}
                                        <h3 class="text-gray-900 font-semibold text-sm leading-snug mb-1 line-clamp-2">
                                            <a href="{{ route('marketplace.show', $product) }}"
                                                class="hover:text-orange-600 transition-colors">
                                                {{ $product->name }}
                                            </a>
                                        </h3>
                                        {{-- Description --}}
                                        <p class="text-gray-500 text-xs line-clamp-2 mb-3 flex-1">
                                            {{ $product->description }}</p>

                                        {{-- Price --}}
                                        <p class="text-orange-600 font-semibold text-base mb-2">
                                            Rp {{ number_format($product->price, 0, ',', '.') }}
                                        </p>

                                        {{-- Meta row --}}
                                        <div
                                            class="flex items-center justify-between text-xs text-gray-400 border-t border-gray-50 pt-2">
                                            <span class="flex items-center gap-1">
                                                <i class="fas fa-map-marker-alt"></i>
                                                {{ Str::limit($product->location, 20) }}
                                            </span>
                                            <span class="flex items-center gap-3">
                                                <span><i class="fas fa-layer-group mr-0.5"></i> Stok
                                                    {{ $product->stock_quantity }}</span>
                                                <span><i class="fas fa-eye mr-0.5"></i> {{ $product->views_count }}</span>
                                            </span>
                                        </div>
                                    </div>

                                    {{-- CTA --}}
                                    <div class="px-4 pb-4">
                                        <a href="{{ route('marketplace.show', $product) }}"
                                            class="block w-full text-center py-2 rounded-lg bg-orange-50 hover:bg-orange-500 text-orange-600 hover:text-white border border-orange-200 hover:border-orange-500 text-sm font-semibold transition-all duration-200">
                                            Lihat Detail
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        {{-- Pagination --}}
                        <div class="mt-8 flex justify-center">
                            {{ $products->appends(request()->query())->links() }}
                        </div>
                    @else
                        {{-- Empty State --}}
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 py-20 text-center">
                            <div
                                class="w-20 h-20 bg-orange-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <i class="fas fa-search text-3xl text-orange-400"></i>
                            </div>
                            <h3 class="text-xl font-bold text-gray-900 mb-2">Produk tidak ditemukan</h3>
                            <p class="text-gray-500 mb-6 max-w-sm mx-auto">
                                @if (request()->hasAny(['search', 'category', 'condition', 'min_price', 'max_price', 'location']))
                                    Coba ubah atau reset filter pencarian Anda.
                                @else
                                    Belum ada produk yang tersedia saat ini.
                                @endif
                            </p>
                            <a href="{{ route('marketplace.index') }}"
                                class="inline-flex items-center gap-2 bg-orange-500 hover:bg-orange-600 text-white font-semibold px-6 py-3 rounded-lg transition-colors">
                                <i class="fas fa-times"></i> Reset Filter
                            </a>
                        </div>
                    @endif

                </div>{{-- end main --}}
            </div>{{-- end flex --}}
        </div>
    </div>

    {{-- Toast Notification --}}
    <div id="toastNotif" class="fixed top-4 right-4 z-50 hidden">
        <div class="bg-gray-900 text-white px-4 py-3 rounded-lg shadow-lg text-sm flex items-center gap-2">
            <i class="fas fa-check-circle text-green-400"></i>
            <span id="toastMsg"></span>
        </div>
    </div>

    <script>
        // Sort auto-submit
        document.getElementById('sortSelect').addEventListener('change', function() {
            const url = new URL(window.location.href);
            url.searchParams.set('sort', this.value);
            window.location.href = url.toString();
        });

        // Toast helper
        function tampilToast(msg) {
            const el = document.getElementById('toastNotif');
            document.getElementById('toastMsg').textContent = msg;
            el.classList.remove('hidden');
            setTimeout(() => el.classList.add('hidden'), 3000);
        }

        // Bookmark
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.bookmark-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const id = this.dataset.productId;
                    fetch(`/marketplace/${id}/bookmark`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector(
                                    'meta[name="csrf-token"]').content,
                                'Content-Type': 'application/json',
                            }
                        })
                        .then(r => r.json())
                        .then(data => {
                            const icon = this.querySelector('i');
                            if (data.isBookmarked) {
                                icon.classList.replace('text-gray-300', 'text-red-500');
                                this.dataset.bookmarked = 'true';
                                tampilToast('Produk disimpan ke bookmark');
                            } else {
                                icon.classList.replace('text-red-500', 'text-gray-300');
                                this.dataset.bookmarked = 'false';
                                tampilToast('Bookmark dihapus');
                            }
                        })
                        .catch(() => tampilToast('Gagal memperbarui bookmark'));
                });
            });
        });
    </script>

@endsection
