@extends('layouts.app')

@section('title', $product->name . ' - Marketplace INFOMA')

@section('content')
    {{-- @php

        // Hitung dari collection yang sudah di-eager load (with(['ratings.user']))
        // Tidak trigger extra query ke database

        $ratingsCollection = $product->ratings;
        $ratingsTotal = $ratingsCollection->count();
        $ratingsAvg = $ratingsTotal > 0 ? round($ratingsCollection->avg('rating'), 1) : 0;
    @endphp --}}

    <div class="min-h-screen bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

            {{-- Breadcrumb --}}
            <nav class="mb-6">
                <ol class="flex items-center flex-wrap gap-1 text-sm text-gray-500">
                    <li><a href="{{ route('home') }}" class="hover:text-orange-600 transition-colors">Beranda</a></li>
                    <li><i class="fas fa-chevron-right text-xs text-gray-300 mx-1"></i></li>
                    <li><a href="{{ route('marketplace.index') }}"
                            class="hover:text-orange-600 transition-colors">Marketplace</a></li>
                    <li><i class="fas fa-chevron-right text-xs text-gray-300 mx-1"></i></li>
                    @if ($product->category)
                        <li>
                            <a href="{{ route('marketplace.index', ['category' => $product->category_id]) }}"
                                class="hover:text-orange-600 transition-colors">
                                {{ $product->category->name }}
                            </a>
                        </li>
                        <li><i class="fas fa-chevron-right text-xs text-gray-300 mx-1"></i></li>
                    @endif
                    <li class="text-gray-900 font-medium truncate max-w-xs">{{ $product->name }}</li>
                </ol>
            </nav>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                {{-- ===================== MAIN (col-span-2) ===================== --}}
                <div class="lg:col-span-2 space-y-6">

                    {{-- === GALLERY === --}}
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                        @php
                            $images = $product->images ?? [];
                            $hasImages = count($images) > 0;
                        @endphp

                        @if ($hasImages)
                            <div class="relative">
                                <img id="mainImage" src="{{ asset('storage/' . $images[0]) }}" alt="{{ $product->name }}"
                                    class="w-full aspect-[4/3] object-cover transition-opacity duration-300">

                                @if (count($images) > 1)
                                    <div
                                        class="absolute bottom-3 right-3 bg-black bg-opacity-50 text-white text-xs px-2.5 py-1 rounded-full">
                                        <span id="imgCounter">1</span> / {{ count($images) }}
                                    </div>
                                @endif

                                {{-- Condition badge --}}
                                @php
                                    $condConf = [
                                        'new' => ['label' => 'Baru', 'bg' => 'bg-green-500'],
                                        'like_new' => ['label' => 'Seperti Baru', 'bg' => 'bg-blue-500'],
                                        'good' => ['label' => 'Baik', 'bg' => 'bg-orange-500'],
                                        'fair' => ['label' => 'Cukup', 'bg' => 'bg-yellow-500'],
                                        'needs_repair' => ['label' => 'Perlu Perbaikan', 'bg' => 'bg-red-500'],
                                    ];
                                    $c = $condConf[$product->condition] ?? [
                                        'label' => $product->condition_label,
                                        'bg' => 'bg-gray-500',
                                    ];
                                @endphp
                                <div class="absolute top-3 left-3">
                                    <span
                                        class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold text-white {{ $c['bg'] }}">
                                        {{ $c['label'] }}
                                    </span>
                                </div>
                            </div>

                            {{-- Thumbnail strip --}}
                            @if (count($images) > 1)
                                <div class="flex gap-2 p-3 overflow-x-auto scrollbar-hide">
                                    @foreach ($images as $idx => $image)
                                        <button type="button"
                                            onclick="galeriGanti('{{ asset('storage/' . $image) }}', this, {{ $idx + 1 }})"
                                            class="thumb-btn flex-shrink-0 w-16 h-16 rounded-lg overflow-hidden border-2 {{ $idx === 0 ? 'border-orange-500' : 'border-transparent' }} hover:border-orange-400 transition-all">
                                            <img src="{{ asset('storage/' . $image) }}" alt="{{ $product->name }}"
                                                class="w-full h-full object-cover">
                                        </button>
                                    @endforeach
                                </div>
                            @endif
                        @else
                            <div class="w-full aspect-[4/3] bg-gray-100 flex flex-col items-center justify-center">
                                <i class="fas fa-box text-6xl text-gray-300 mb-3"></i>
                                <p class="text-sm text-gray-400">Belum ada foto</p>
                            </div>
                        @endif
                    </div>

                    {{-- === PRODUCT INFO === --}}
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                        <div class="flex items-start justify-between gap-4 mb-3">
                            <div class="flex-1">
                                <p class="text-xs text-orange-600 font-semibold uppercase tracking-wide mb-1">
                                    {{ $product->category->name ?? '—' }}
                                </p>
                                <h1 class="text-2xl font-bold text-gray-900 leading-tight">{{ $product->name }}</h1>
                            </div>
                            @auth
                                <button id="bookmarkBtn"
                                    class="flex-shrink-0 p-2.5 rounded-full transition-colors {{ $isBookmarked ? 'bg-red-100 text-red-500' : 'bg-gray-100 text-gray-400' }} hover:bg-red-100 hover:text-red-500"
                                    data-product-id="{{ $product->id }}" title="Simpan ke bookmark">
                                    <i class="fas fa-heart text-lg"></i>
                                </button>
                            @endauth
                        </div>

                        {{-- Rating summary
                        @if ($ratingsTotal > 0)
                            <div class="flex items-center gap-2 mb-4">
                                <div class="flex items-center">
                                    @for ($i = 1; $i <= 5; $i++)
                                        <i
                                            class="fas fa-star text-sm {{ $i <= round($ratingsAvg) ? 'text-yellow-400' : 'text-gray-200' }}"></i>
                                    @endfor
                                </div>
                                <span
                                    class="text-sm font-semibold text-gray-700">{{ number_format($ratingsAvg, 1) }}</span>
                                <span class="text-sm text-gray-400">({{ $ratingsTotal }} ulasan)</span>
                            </div>
                        @endif --}}

                        {{-- Tags --}}
                        @if ($product->tags && count($product->tags) > 0)
                            <div class="flex flex-wrap gap-2 mb-4">
                                @foreach ($product->tags as $tag)
                                    <a href="{{ route('marketplace.index', ['search' => $tag]) }}"
                                        class="inline-flex items-center px-2.5 py-1 rounded-full bg-orange-50 text-orange-700 text-xs font-medium hover:bg-orange-100 transition-colors">
                                        #{{ $tag }}
                                    </a>
                                @endforeach
                            </div>
                        @endif

                        <hr class="border-gray-100 mb-4">

                        <h3 class="text-base font-bold text-gray-900 mb-2">Deskripsi Produk</h3>
                        <p class="text-gray-600 leading-relaxed whitespace-pre-line text-sm">{{ $product->description }}
                        </p>
                    </div>

                    {{-- === PRODUCT SPECS === --}}
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                        <h3 class="text-base font-bold text-gray-900 mb-4">Informasi Produk</h3>
                        <div class="grid grid-cols-2 gap-3">
                            <div class="bg-gray-50 rounded-xl p-3">
                                <p class="text-xs text-gray-500 mb-1">Kondisi</p>
                                <p class="text-sm font-semibold text-gray-900">{{ $product->condition_label }}</p>
                            </div>
                            <div class="bg-gray-50 rounded-xl p-3">
                                <p class="text-xs text-gray-500 mb-1">Stok Tersedia</p>
                                <p
                                    class="text-sm font-semibold {{ $product->stock_quantity > 0 ? 'text-green-700' : 'text-red-600' }}">
                                    {{ $product->stock_quantity > 0 ? $product->stock_quantity . ' unit' : 'Habis' }}
                                </p>
                            </div>
                            <div class="bg-gray-50 rounded-xl p-3">
                                <p class="text-xs text-gray-500 mb-1">Lokasi</p>
                                <p class="text-sm font-semibold text-gray-900">{{ $product->location }}</p>
                            </div>
                            <div class="bg-gray-50 rounded-xl p-3">
                                <p class="text-xs text-gray-500 mb-1">Total Dilihat</p>
                                <p class="text-sm font-semibold text-gray-900">{{ number_format($product->views_count) }}
                                    kali</p>
                            </div>
                            <div class="bg-gray-50 rounded-xl p-3">
                                <p class="text-xs text-gray-500 mb-1">Kategori</p>
                                <p class="text-sm font-semibold text-gray-900">{{ $product->category->name ?? '—' }}</p>
                            </div>
                            <div class="bg-gray-50 rounded-xl p-3">
                                <p class="text-xs text-gray-500 mb-1">Diposting</p>
                                <p class="text-sm font-semibold text-gray-900">
                                    {{ $product->created_at->translatedFormat('d M Y') }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- === ULASAN ===
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6" id="ulasan">
                        <h3 class="text-base font-bold text-gray-900 mb-5">Rating & Ulasan</h3> --}}

                    {{-- @if ($ratingsTotal > 0)
                            Summary
                            <div
                                class="flex flex-col sm:flex-row gap-6 mb-6 p-4 bg-orange-50 rounded-xl border border-orange-100">
                                <div class="text-center flex-shrink-0">
                                    <p class="text-5xl font-bold text-orange-600 leading-none mb-1">
                                        {{ number_format($ratingsAvg, 1) }}</p>
                                    <div class="flex justify-center gap-0.5 mb-1">
                                        @for ($i = 1; $i <= 5; $i++)
                                            <i
                                                class="fas fa-star text-sm {{ $i <= round($ratingsAvg) ? 'text-yellow-400' : 'text-gray-300' }}"></i>
                                        @endfor
                                    </div>
                                    <p class="text-xs text-gray-500">{{ $ratingsTotal }} ulasan</p>
                                </div>
                                <div class="flex-1 space-y-2 justify-center flex flex-col">
                                    @for ($star = 5; $star >= 1; $star--)
                                        @php
                                            $cnt = $ratingsCollection->where('rating', $star)->count();
                                            $pct = $ratingsTotal > 0 ? ($cnt / $ratingsTotal) * 100 : 0;
                                        @endphp
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs text-gray-600 w-4 text-right">{{ $star }}</span>
                                            <i class="fas fa-star text-xs text-yellow-400"></i>
                                            <div class="flex-1 h-2 bg-gray-200 rounded-full overflow-hidden">
                                                <div class="h-full bg-orange-400 rounded-full transition-all duration-500"
                                                    style="width: {{ $pct }}%"></div>
                                            </div>
                                            <span class="text-xs text-gray-500 w-5 text-right">{{ $cnt }}</span>
                                        </div>
                                    @endfor
                                </div>
                            </div> --}}

                    {{-- Review list --}}
                    {{-- <div class="space-y-4">
                                @foreach ($ratingsCollection as $rating)
                                    <div class="flex gap-3 border-b border-gray-50 pb-4 last:border-0 last:pb-0">
                                        <div
                                            class="flex-shrink-0 w-10 h-10 rounded-full bg-orange-500 flex items-center justify-center text-white font-bold text-sm">
                                            {{ strtoupper(substr($rating->user->name ?? 'U', 0, 1)) }}
                                        </div>
                                        <div class="flex-1">
                                            <div class="flex items-center justify-between mb-1">
                                                <p class="text-sm font-semibold text-gray-900">
                                                    {{ $rating->user->name ?? 'Pengguna' }}</p>
                                                <span
                                                    class="text-xs text-gray-400">{{ $rating->created_at->format('d M Y') }}</span>
                                            </div>
                                            <div class="flex gap-0.5 mb-2">
                                                @for ($i = 1; $i <= 5; $i++)
                                                    <i
                                                        class="fas fa-star text-xs {{ $i <= $rating->rating ? 'text-yellow-400' : 'text-gray-200' }}"></i>
                                                @endfor
                                            </div>
                                            @if ($rating->review)
                                                <p class="text-sm text-gray-600 leading-relaxed">{{ $rating->review }}</p>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-10">
                                <div
                                    class="w-14 h-14 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-3">
                                    <i class="fas fa-star text-2xl text-gray-300"></i>
                                </div>
                                <p class="text-sm text-gray-500">Belum ada ulasan untuk produk ini</p>
                            </div>
                        @endif
                    </div> --}}

                </div>{{-- end col-span-2 --}}

                {{-- ===================== STICKY SIDEBAR ===================== --}}
                <div class="lg:col-span-1">
                    <div class="sticky top-24 space-y-4">

                        {{-- Price card --}}
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                            <p class="text-3xl font-semibold text-orange-600 mb-1">
                                Rp {{ number_format($product->price, 0, ',', '.') }}
                            </p>
                            <p class="text-xs text-gray-400 mb-5">Harga sudah termasuk biaya platform</p>

                            <div class="space-y-3 mb-5">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-500 flex items-center gap-2">
                                        <i class="fas fa-tag w-4 text-gray-400"></i> Kondisi
                                    </span>
                                    <span
                                        class="text-sm font-semibold text-gray-800">{{ $product->condition_label }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-500 flex items-center gap-2">
                                        <i class="fas fa-layer-group w-4 text-gray-400"></i> Stok
                                    </span>
                                    <span
                                        class="text-sm font-semibold {{ $product->stock_quantity > 0 ? 'text-green-700' : 'text-red-600' }}">
                                        {{ $product->stock_quantity > 0 ? $product->stock_quantity . ' unit' : 'Habis' }}
                                    </span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-500 flex items-center gap-2">
                                        <i class="fas fa-map-marker-alt w-4 text-gray-400"></i> Lokasi
                                    </span>
                                    <span
                                        class="text-sm font-semibold text-gray-800 text-right max-w-[150px] leading-snug">{{ $product->location }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-500 flex items-center gap-2">
                                        <i class="fas fa-eye w-4 text-gray-400"></i> Dilihat
                                    </span>
                                    <span
                                        class="text-sm font-semibold text-gray-800">{{ number_format($product->views_count) }}×</span>
                                </div>
                            </div>

                            {{-- CTA Buttons --}}
                            @if ($product->is_available)
                                @auth
                                    @if ($product->seller_id !== auth()->id())
                                        <a href="{{ route('user.marketplace.transactions.create', $product) }}"
                                            class="w-full bg-orange-500 hover:bg-orange-600 text-white font-bold py-3 rounded-xl flex items-center justify-center gap-2 transition-colors shadow-sm mb-3">
                                            <i class="fas fa-shopping-cart"></i> Beli Sekarang
                                        </a>
                                        <a href="#ulasan"
                                            class="w-full border border-orange-200 text-orange-600 hover:bg-orange-50 font-semibold py-2.5 rounded-xl flex items-center justify-center gap-2 transition-colors text-sm">
                                            <i class="fas fa-star"></i> Lihat Ulasan
                                        </a>
                                    @else
                                        <div
                                            class="bg-orange-50 border border-orange-200 text-orange-800 px-4 py-3 rounded-xl text-sm text-center mb-3">
                                            <i class="fas fa-info-circle mr-2"></i> Ini adalah produk Anda
                                        </div>
                                        <div class="flex gap-2">
                                            <a href="{{ route('user.marketplace.seller.edit', $product) }}"
                                                class="flex-1 border border-gray-200 text-gray-700 hover:bg-gray-50 font-medium py-2.5 rounded-xl text-center text-sm transition-colors">
                                                <i class="fas fa-edit mr-1"></i> Edit
                                            </a>
                                            <a href="{{ route('user.marketplace.seller.my-products') }}"
                                                class="flex-1 border border-gray-200 text-gray-700 hover:bg-gray-50 font-medium py-2.5 rounded-xl text-center text-sm transition-colors">
                                                <i class="fas fa-box mr-1"></i> Produk Saya
                                            </a>
                                        </div>
                                    @endif
                                @else
                                    <a href="{{ route('login') }}"
                                        class="w-full bg-orange-500 hover:bg-orange-600 text-white font-bold py-3 rounded-xl flex items-center justify-center gap-2 transition-colors shadow-sm">
                                        <i class="fas fa-sign-in-alt"></i> Login untuk Membeli
                                    </a>
                                @endauth
                            @else
                                <div
                                    class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm text-center">
                                    <i class="fas fa-times-circle mr-2"></i> Produk tidak tersedia
                                </div>
                            @endif
                        </div>

                        {{-- Seller card --}}
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                            <h3 class="text-sm font-bold text-gray-900 mb-3 flex items-center gap-2">
                                <i class="fas fa-user-circle text-orange-400"></i> Informasi Penjual
                            </h3>
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-10 h-10 rounded-full bg-orange-500 flex items-center justify-center text-white font-bold text-sm flex-shrink-0">
                                    {{ strtoupper(substr($product->seller->name ?? 'U', 0, 1)) }}
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-gray-900">{{ $product->seller->name ?? '—' }}</p>
                                    <p class="text-xs text-gray-500">
                                        Anggota sejak {{ $product->seller?->created_at?->format('Y') ?? '—' }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        {{-- Share card --}}
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                            <h3 class="text-sm font-bold text-gray-900 mb-3 flex items-center gap-2">
                                <i class="fas fa-share-alt text-orange-400"></i> Bagikan
                            </h3>
                            <div class="flex gap-2">
                                <a href="https://wa.me/?text={{ urlencode($product->name . ' — Rp ' . number_format($product->price, 0, ',', '.') . "\n" . url()->current()) }}"
                                    target="_blank" rel="noopener noreferrer"
                                    class="flex-1 flex items-center justify-center gap-2 bg-green-500 hover:bg-green-600 text-white text-sm font-semibold py-2.5 rounded-lg transition-colors">
                                    <i class="fab fa-whatsapp"></i> WhatsApp
                                </a>
                                <button onclick="salinLink()"
                                    class="flex-1 flex items-center justify-center gap-2 border border-gray-200 text-gray-700 hover:bg-gray-50 text-sm font-semibold py-2.5 rounded-lg transition-colors">
                                    <i class="fas fa-link"></i> Salin Link
                                </button>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            {{-- === RELATED PRODUCTS === --}}
            @if (isset($relatedProducts) && $relatedProducts->count() > 0)
                <div class="mt-10">
                    <h2 class="text-xl font-bold text-gray-900 mb-5 flex items-center gap-3">
                        <span class="w-1 h-6 bg-orange-500 rounded-full inline-block"></span>
                        Produk Terkait
                    </h2>
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                        @foreach ($relatedProducts as $rel)
                            <a href="{{ route('marketplace.show', $rel) }}"
                                class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 group block">
                                <img src="{{ $rel->main_image }}" alt="{{ $rel->name }}"
                                    class="w-full h-36 object-cover group-hover:scale-105 transition-transform duration-300">
                                <div class="p-3">
                                    <p class="text-xs text-orange-600 font-medium mb-0.5">
                                        {{ $rel->category->name ?? '—' }}</p>
                                    <p class="text-sm font-semibold text-gray-900 line-clamp-1 mb-1">{{ $rel->name }}
                                    </p>
                                    <p class="text-sm font-semibold text-orange-600">
                                        Rp {{ number_format($rel->price, 0, ',', '.') }}
                                    </p>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

        </div>
    </div>

    {{-- Toast --}}
    <div id="toastNotif" class="fixed top-4 right-4 z-50 hidden pointer-events-none">
        <div class="bg-gray-900 text-white px-4 py-3 rounded-xl shadow-lg text-sm flex items-center gap-2">
            <i class="fas fa-check-circle text-green-400"></i>
            <span id="toastMsg">Berhasil</span>
        </div>
    </div>

    <script>
        function galeriGanti(src, el, num) {
            const mainImg = document.getElementById('mainImage');
            mainImg.style.opacity = '0';
            setTimeout(function() {
                mainImg.src = src;
                mainImg.style.opacity = '1';
            }, 150);
            const counter = document.getElementById('imgCounter');
            if (counter) counter.textContent = num;
            document.querySelectorAll('.thumb-btn').forEach(function(btn) {
                btn.classList.remove('border-orange-500');
                btn.classList.add('border-transparent');
            });
            el.classList.remove('border-transparent');
            el.classList.add('border-orange-500');
        }

        function tampilToast(msg) {
            const el = document.getElementById('toastNotif');
            document.getElementById('toastMsg').textContent = msg;
            el.classList.remove('hidden');
            setTimeout(function() {
                el.classList.add('hidden');
            }, 3000);
        }

        function salinLink() {
            navigator.clipboard.writeText(window.location.href)
                .then(function() {
                    tampilToast('Link berhasil disalin!');
                })
                .catch(function() {
                    tampilToast('Gagal menyalin link');
                });
        }

        document.addEventListener('DOMContentLoaded', function() {
            var btn = document.getElementById('bookmarkBtn');
            if (!btn) return;

            btn.addEventListener('click', function() {
                var id = this.dataset.productId;
                fetch('/marketplace/' + id + '/bookmark', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Content-Type': 'application/json',
                        }
                    })
                    .then(function(r) {
                        return r.json();
                    })
                    .then(function(data) {
                        if (data.isBookmarked) {
                            btn.classList.add('bg-red-100', 'text-red-500');
                            btn.classList.remove('bg-gray-100', 'text-gray-400');
                            tampilToast('Produk disimpan ke bookmark');
                        } else {
                            btn.classList.remove('bg-red-100', 'text-red-500');
                            btn.classList.add('bg-gray-100', 'text-gray-400');
                            tampilToast('Bookmark dihapus');
                        }
                    })
                    .catch(function() {
                        tampilToast('Gagal memperbarui bookmark');
                    });
            });
        });
    </script>

@endsection
