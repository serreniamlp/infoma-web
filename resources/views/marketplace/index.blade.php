@extends('layouts.app')

@section('title', 'Marketplace')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex flex-col md:flex-row gap-8">
        <!-- Sidebar Filter -->
        <div class="w-full md:w-1/4">
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
                    <h5 class="text-lg font-semibold text-gray-900">Filter Produk</h5>
                </div>
                <div class="p-4">
                    <form method="GET" action="{{ route('marketplace.index') }}">
                        <!-- Search -->
                        <div class="mb-4">
                            <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Cari Produk</label>
                            <input type="text"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50"
                                id="search" name="search" value="{{ request('search') }}" placeholder="Nama produk...">
                        </div>

                        <!-- Category -->
                        <div class="mb-4">
                            <label for="category" class="block text-sm font-medium text-gray-700 mb-1">Kategori</label>
                            <select
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50"
                                id="category" name="category">
                                <option value="">Semua Kategori</option>
                                @foreach($categories as $category)
                                <option value="{{ $category->id }}"
                                    {{ request('category') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Condition -->
                        <div class="mb-4">
                            <label for="condition" class="block text-sm font-medium text-gray-700 mb-1">Kondisi</label>
                            <select
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50"
                                id="condition" name="condition">
                                <option value="">Semua Kondisi</option>
                                <option value="new" {{ request('condition') == 'new' ? 'selected' : '' }}>Baru</option>
                                <option value="like_new" {{ request('condition') == 'like_new' ? 'selected' : '' }}>
                                    Seperti Baru</option>
                                <option value="good" {{ request('condition') == 'good' ? 'selected' : '' }}>Baik
                                </option>
                                <option value="fair" {{ request('condition') == 'fair' ? 'selected' : '' }}>Cukup
                                </option>
                                <option value="needs_repair"
                                    {{ request('condition') == 'needs_repair' ? 'selected' : '' }}>Perlu Perbaikan
                                </option>
                            </select>
                        </div>

                        <!-- Price Range -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Harga</label>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <input type="number"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50"
                                        name="min_price" value="{{ request('min_price') }}" placeholder="Min">
                                </div>
                                <div>
                                    <input type="number"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50"
                                        name="max_price" value="{{ request('max_price') }}" placeholder="Max">
                                </div>
                            </div>
                        </div>

                        <!-- Location -->
                        <div class="mb-4">
                            <label for="location" class="block text-sm font-medium text-gray-700 mb-1">Lokasi</label>
                            <input type="text"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50"
                                id="location" name="location" value="{{ request('location') }}"
                                placeholder="Kota, Provinsi...">
                        </div>

                        <button type="submit"
                            class="w-full bg-primary text-white rounded-md py-2 px-4 hover:bg-secondary transition-colors mb-2">Filter</button>
                        <a href="{{ route('marketplace.index') }}"
                            class="w-full inline-block text-center border border-gray-300 text-gray-700 rounded-md py-2 px-4 hover:bg-gray-50 transition-colors">Reset</a>
                    </form>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="w-full md:w-3/4">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-gray-900">Marketplace</h2>
                @auth
                    @if(auth()->user()->hasRole('provider'))
                        <div class="space-x-4">
                            <a href="{{ route('provider.marketplace.create') }}"
                                class="inline-flex items-center px-4 py-2 bg-primary text-white rounded-md hover:bg-secondary transition-colors">
                                <i class="fas fa-plus mr-2"></i> Jual Produk
                            </a>
                            <a href="{{ route('provider.marketplace.my-products') }}"
                                class="inline-flex items-center px-4 py-2 border border-primary text-primary rounded-md hover:bg-primary hover:text-white transition-colors">
                                <i class="fas fa-box mr-2"></i> Produk Saya
                            </a>
                        </div>
                    @endif
                @endauth
            </div>

            <!-- Sort Options -->
            <div class="flex justify-between items-center mb-4">
                <p class="text-sm text-gray-600">Menampilkan {{ $products->count() }} dari {{ $products->total() }}
                    produk</p>
                <div class="flex items-center space-x-4">
                    <label for="sort" class="text-sm text-gray-600">Urutkan:</label>
                    <select
                        class="rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50 text-sm"
                        id="sort" onchange="sortProducts()">
                        <option value="created_at" {{ request('sort') == 'created_at' ? 'selected' : '' }}>Terbaru
                        </option>
                        <option value="price_asc" {{ request('sort') == 'price_asc' ? 'selected' : '' }}>Harga Terendah
                        </option>
                        <option value="price_desc" {{ request('sort') == 'price_desc' ? 'selected' : '' }}>Harga
                            Tertinggi</option>
                        <option value="views" {{ request('sort') == 'views' ? 'selected' : '' }}>Paling Dilihat</option>
                        <option value="name" {{ request('sort') == 'name' ? 'selected' : '' }}>Nama A-Z</option>
                    </select>
                </div>
            </div>

            <!-- Products Grid -->
            @if($products->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($products as $product)
                <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                    <div class="relative">
                        <img src="{{ $product->main_image }}" class="w-full h-48 object-cover"
                            alt="{{ $product->name }}">
                        <div class="absolute top-2 right-2">
                            <span
                                class="px-2 py-1 text-xs font-semibold rounded-full
                                    {{ $product->condition == 'new' ? 'bg-green-500 text-white' :
                                       ($product->condition == 'like_new' ? 'bg-primary text-white' : 'bg-gray-500 text-white') }}">
                                {{ $product->condition_label }}
                            </span>
                        </div>
                        @auth
                        <button
                            class="absolute top-2 left-2 p-2 rounded-full bg-white bg-opacity-75 hover:bg-opacity-100 transition-colors bookmark-btn"
                            data-product-id="{{ $product->id }}"
                            data-bookmarked="{{ $product->isBookmarkedBy(auth()->id()) ? 'true' : 'false' }}">
                            <i
                                class="fas fa-heart {{ $product->isBookmarkedBy(auth()->id()) ? 'text-red-500' : 'text-gray-400' }}"></i>
                        </button>
                        @endauth
                    </div>
                    <div class="p-4">
                        <h3 class="font-semibold text-gray-900 mb-2">{{ Str::limit($product->name, 50) }}</h3>
                        <p class="text-gray-600 text-sm mb-4">{{ Str::limit($product->description, 80) }}</p>
                        <div class="mt-auto">
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-lg font-bold text-primary">Rp
                                    {{ number_format($product->price, 0, ',', '.') }}</span>
                                <span class="text-sm text-gray-500 flex items-center">
                                    <i class="fas fa-eye mr-1"></i> {{ $product->views_count }}
                                </span>
                            </div>
                            <div class="flex justify-between items-center text-sm text-gray-500">
                                <span class="flex items-center">
                                    <i class="fas fa-map-marker-alt mr-1"></i> {{ $product->location }}
                                </span>
                                <span>
                                    Stok: {{ $product->stock_quantity }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="px-4 py-3 bg-gray-50 border-t border-gray-200">
                        <a href="{{ route('marketplace.show', $product) }}"
                            class="block w-full text-center py-2 px-4 bg-primary text-white rounded-md hover:bg-secondary transition-colors">
                            Lihat Detail
                        </a>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="flex justify-center mt-8">
                {{ $products->appends(request()->query())->links() }}
            </div>
            @else
            <div class="text-center py-16">
                <i class="fas fa-search text-4xl text-gray-400 mb-4"></i>
                <h4 class="text-xl font-semibold text-gray-900 mb-2">Tidak ada produk ditemukan</h4>
                <p class="text-gray-600">Coba ubah filter pencarian Anda</p>
            </div>
            @endif
        </div>
    </div>
</div>

<script>
function sortProducts() {
    const sort = document.getElementById('sort').value;
    const url = new URL(window.location);
    url.searchParams.set('sort', sort);
    window.location.href = url.toString();
}

// Bookmark functionality
document.addEventListener('DOMContentLoaded', function() {
    const bookmarkBtns = document.querySelectorAll('.bookmark-btn');

    bookmarkBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const productId = this.dataset.productId;
            const isBookmarked = this.dataset.bookmarked === 'true';

            fetch(`/marketplace/${productId}/bookmark`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                            .getAttribute('content'),
                        'Content-Type': 'application/json',
                    },
                })
                .then(response => response.json())
                .then(data => {
                    const icon = this.querySelector('i');
                    if (data.isBookmarked) {
                        icon.classList.remove('text-gray-400');
                        icon.classList.add('text-red-500');
                        this.dataset.bookmarked = 'true';
                    } else {
                        icon.classList.remove('text-red-500');
                        icon.classList.add('text-gray-400');
                        this.dataset.bookmarked = 'false';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        });
    });
});
</script>
@endsection
