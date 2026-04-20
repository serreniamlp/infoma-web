@extends('layouts.app')

@section('title', $product->name)

@section('content')
<div class="container mx-auto px-4">
    <nav class="flex mb-4" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-3">
            <li class="inline-flex items-center">
                <a href="{{ route('marketplace.index') }}"
                    class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600">
                    Marketplace
                </a>
            </li>
            <li>
                <div class="flex items-center">
                    <svg class="w-3 h-3 text-gray-400 mx-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                        fill="none" viewBox="0 0 6 10">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="m1 9 4-4-4-4" />
                    </svg>
                    <a href="{{ route('marketplace.index', ['category' => $product->category_id]) }}"
                        class="ml-1 text-sm font-medium text-gray-700 hover:text-blue-600 md:ml-2">{{ $product->category->name }}</a>
                </div>
            </li>
            <li aria-current="page">
                <div class="flex items-center">
                    <svg class="w-3 h-3 text-gray-400 mx-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                        fill="none" viewBox="0 0 6 10">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="m1 9 4-4-4-4" />
                    </svg>
                    <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">{{ $product->name }}</span>
                </div>
            </li>
        </ol>
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Product Images -->
        <div class="w-full">
            <div id="productCarousel" class="relative w-full">
                <div class="relative h-96 overflow-hidden rounded-lg">
                    @if($product->images && count($product->images) > 0)
                    @foreach($product->images as $index => $image)
                    <div class="carousel-item absolute inset-0 transition-opacity duration-700 ease-in-out {{ $index === 0 ? 'opacity-100' : 'opacity-0' }}"
                        data-carousel-item>
                        <img src="{{ asset('storage/' . $image) }}" class="absolute block w-full h-full object-cover"
                            alt="{{ $product->name }}">
                    </div>
                    @endforeach
                    @else
                    <div class="carousel-item absolute inset-0 opacity-100" data-carousel-item>
                        <img src="{{ asset('images/no-image.png') }}" class="absolute block w-full h-full object-cover"
                            alt="{{ $product->name }}">
                    </div>
                    @endif
                </div>
                @if($product->images && count($product->images) > 1)
                <button type="button"
                    class="absolute top-0 left-0 z-30 flex items-center justify-center h-full px-4 cursor-pointer group focus:outline-none"
                    data-carousel-prev>
                    <span
                        class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-white/30 group-hover:bg-white/50 group-focus:ring-4 group-focus:ring-white group-focus:outline-none">
                        <svg class="w-4 h-4 text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                            fill="none" viewBox="0 0 6 10">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M5 1 1 5l4 4" />
                        </svg>
                    </span>
                </button>
                <button type="button"
                    class="absolute top-0 right-0 z-30 flex items-center justify-center h-full px-4 cursor-pointer group focus:outline-none"
                    data-carousel-next>
                    <span
                        class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-white/30 group-hover:bg-white/50 group-focus:ring-4 group-focus:ring-white group-focus:outline-none">
                        <svg class="w-4 h-4 text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                            fill="none" viewBox="0 0 6 10">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="m1 9 4-4-4-4" />
                        </svg>
                    </span>
                </button>
                @endif
            </div>
        </div>

        <!-- Product Info -->
        <div class="w-full">
            <div class="bg-white shadow-lg rounded-lg">
                <div class="p-6">
                    <div class="flex justify-between items-start mb-4">
                        <h1 class="text-3xl font-bold text-gray-900">{{ $product->name }}</h1>
                        @auth
                        <button class="p-2 text-gray-400 hover:text-red-500 bookmark-btn"
                            data-product-id="{{ $product->id }}"
                            data-bookmarked="{{ $isBookmarked ? 'true' : 'false' }}">
                            <i class="fas fa-heart text-xl {{ $isBookmarked ? 'text-red-500' : '' }}"></i>
                        </button>
                        @endauth
                    </div>

                    <div class="mb-4">
                        <span
                            class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium 
                            {{ $product->condition == 'new' ? 'bg-green-100 text-green-800' : 
                               ($product->condition == 'like_new' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800') }}">
                            {{ $product->condition_label }}
                        </span>
                    </div>

                    <div class="mb-4">
                        <h2 class="text-3xl font-bold text-blue-600">Rp
                            {{ number_format($product->price, 0, ',', '.') }}</h2>
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <span class="font-semibold text-gray-700">Stok:</span>
                            <span class="text-gray-600">{{ $product->stock_quantity }}</span>
                        </div>
                        <div>
                            <span class="font-semibold text-gray-700">Dilihat:</span>
                            <span class="text-gray-600">{{ $product->views_count }} kali</span>
                        </div>
                    </div>

                    <div class="mb-4">
                        <span class="font-semibold text-gray-700">Lokasi:</span>
                        <span class="text-gray-600">{{ $product->location }}</span>
                    </div>

                    <div class="mb-4">
                        <span class="font-semibold text-gray-700">Penjual:</span>
                        <a href="#"
                            class="text-blue-600 hover:text-blue-800 hover:underline">{{ $product->seller->name }}</a>
                    </div>

                    @if($product->tags && count($product->tags) > 0)
                    <div class="mb-4">
                        <span class="font-semibold text-gray-700 block mb-2">Tags:</span>
                        <div class="flex flex-wrap gap-2">
                            @foreach($product->tags as $tag)
                            <span
                                class="inline-flex items-center px-2 py-1 rounded bg-gray-100 text-gray-800 text-sm">#{{ $tag }}</span>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    @if($product->is_available)
                    @auth
                    @if($product->seller_id !== auth()->id())
                    <a href="{{ route('marketplace.transactions.create', $product) }}"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-4 rounded-lg inline-flex items-center justify-center mb-2">
                        <i class="fas fa-shopping-cart mr-2"></i> Beli Sekarang
                    </a>
                    @else
                    <div class="bg-blue-50 border border-blue-200 text-blue-800 px-4 py-3 rounded-lg">
                        <i class="fas fa-info-circle mr-2"></i> Ini adalah produk Anda
                    </div>
                    @endif
                    @else
                    <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-3 rounded-lg">
                        <i class="fas fa-lock mr-2"></i> Silakan login untuk membeli produk
                    </div>
                    @endauth
                    @else
                    <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg">
                        <i class="fas fa-times-circle mr-2"></i> Produk tidak tersedia
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Product Description -->
    <div class="mt-8">
        <div class="bg-white shadow-lg rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Deskripsi Produk</h3>
            </div>
            <div class="px-6 py-4">
                <p class="text-gray-600">{{ $product->description }}</p>
            </div>
        </div>
    </div>

    <!-- Ratings & Reviews -->
    @if($product->ratings->count() > 0)
    <div class="mt-8">
        <div class="bg-white shadow-lg rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Rating & Ulasan</h3>
            </div>
            <div class="px-6 py-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <div class="text-center">
                        <h2 class="text-4xl font-bold text-blue-600">{{ number_format($product->rating_average, 1) }}
                        </h2>
                        <div class="flex justify-center mb-2">
                            @for($i = 1; $i <= 5; $i++) <i
                                class="fas fa-star {{ $i <= $product->rating_average ? 'text-yellow-400' : 'text-gray-300' }}">
                                </i>
                                @endfor
                        </div>
                        <p class="text-gray-500">{{ $product->ratings_count }} ulasan</p>
                    </div>
                    <div class="md:col-span-2">
                        @for($rating = 5; $rating >= 1; $rating--)
                        @php
                        $count = $product->ratings->where('rating', $rating)->count();
                        $percentage = $product->ratings_count > 0 ? ($count / $product->ratings_count) * 100 : 0;
                        @endphp
                        <div class="flex items-center mb-2">
                            <span class="mr-3 text-sm">{{ $rating }}</span>
                            <i class="fas fa-star text-yellow-400 mr-3"></i>
                            <div class="flex-1 h-2 bg-gray-200 rounded mr-3">
                                <div class="h-2 bg-blue-600 rounded" style="width: {{ $percentage }}%"></div>
                            </div>
                            <span class="text-gray-500 text-sm">{{ $count }}</span>
                        </div>
                        @endfor
                    </div>
                </div>

                <div class="space-y-4">
                    @foreach($product->ratings as $rating)
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="flex justify-between items-start mb-3">
                            <div>
                                <h4 class="font-semibold text-gray-900">{{ $rating->user->name }}</h4>
                                <div class="flex text-yellow-400">
                                    @for($i = 1; $i <= 5; $i++) <i
                                        class="fas fa-star {{ $i <= $rating->rating ? '' : 'text-gray-300' }}"></i>
                                        @endfor
                                </div>
                            </div>
                            <span class="text-sm text-gray-500">{{ $rating->created_at->format('d M Y') }}</span>
                        </div>
                        @if($rating->review)
                        <p class="text-gray-600">{{ $rating->review }}</p>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Related Products -->
    @if($relatedProducts->count() > 0)
    <div class="mt-8">
        <div class="bg-white shadow-lg rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Produk Terkait</h3>
            </div>
            <div class="px-6 py-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    @foreach($relatedProducts as $relatedProduct)
                    <div
                        class="bg-white border border-gray-200 rounded-lg shadow hover:shadow-md transition-shadow duration-200">
                        <img src="{{ $relatedProduct->main_image }}" class="w-full h-40 object-cover rounded-t-lg"
                            alt="{{ $relatedProduct->name }}">
                        <div class="p-4">
                            <h4 class="text-sm font-medium text-gray-900 mb-2">
                                {{ Str::limit($relatedProduct->name, 30) }}</h4>
                            <p class="text-blue-600 font-bold">Rp
                                {{ number_format($relatedProduct->price, 0, ',', '.') }}</p>
                        </div>
                        <div class="px-4 pb-4">
                            <a href="{{ route('marketplace.show', $relatedProduct) }}"
                                class="w-full bg-blue-50 hover:bg-blue-100 text-blue-600 font-medium py-2 px-4 rounded border border-blue-200 inline-block text-center text-sm">
                                Lihat Detail
                            </a>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

<script>
// Bookmark functionality
document.addEventListener('DOMContentLoaded', function() {
    const bookmarkBtn = document.querySelector('.bookmark-btn');

    if (bookmarkBtn) {
        bookmarkBtn.addEventListener('click', function() {
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
                        icon.classList.add('text-red-500');
                        this.dataset.bookmarked = 'true';
                    } else {
                        icon.classList.remove('text-red-500');
                        this.dataset.bookmarked = 'false';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        });
    }

    // Simple carousel functionality for Tailwind
    const items = document.querySelectorAll('[data-carousel-item]');
    const prevButton = document.querySelector('[data-carousel-prev]');
    const nextButton = document.querySelector('[data-carousel-next]');

    if (items.length > 1) {
        let currentIndex = 0;

        function showItem(index) {
            items.forEach((item, i) => {
                if (i === index) {
                    item.classList.remove('opacity-0');
                    item.classList.add('opacity-100');
                } else {
                    item.classList.remove('opacity-100');
                    item.classList.add('opacity-0');
                }
            });
        }

        if (nextButton) {
            nextButton.addEventListener('click', () => {
                currentIndex = (currentIndex + 1) % items.length;
                showItem(currentIndex);
            });
        }

        if (prevButton) {
            prevButton.addEventListener('click', () => {
                currentIndex = (currentIndex - 1 + items.length) % items.length;
                showItem(currentIndex);
            });
        }
    }
});
</script>
@endsection