@extends('layouts.app')

@php use Illuminate\Support\Str; @endphp

@section('title', 'Dashboard - Infoma')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl pt-4 mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Banner Section with Auto Slider -->
        <div class="relative h-60 mb-8  rounded-lg overflow-hidden shadow-lg">
            <div class="banner-slider relative w-full h-full">
                <!-- Slide 1 -->
                <div class="banner-slide absolute inset-0 opacity-100 transition-opacity duration-1000">
                    <div class="relative w-full h-full bg-gradient-to-r from-blue-600 to-blue-900">
                        <div class="absolute inset-0 bg-black bg-opacity-30"></div>
                        <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='800' height='400' viewBox='0 0 800 400'%3E%3Cpath fill='%23ffffff' fill-opacity='0.1' d='M0 0h800v400H0z'/%3E%3Cpath fill='%23ffffff' fill-opacity='0.05' d='M100 100h200v200H100z M500 50h150v300H500z'/%3E%3C/svg%3E"
                            alt="Banner 1" class="w-full h-full object-cover">
                        <div class="absolute inset-0 flex items-center justify-center">
                            <div class="text-center text-white">
                                <h2 class="text-4xl font-bold mb-4">Selamat Datang di INFOMA</h2>
                                <p class="text-xl opacity-90">Temukan residence, kegiatan, dan produk terbaik</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Slide 2 -->
                <div class="banner-slide absolute inset-0 opacity-0 transition-opacity duration-1000">
                    <div class="relative w-full h-full bg-gradient-to-r from-green-600 to-teal-600">
                        <div class="absolute inset-0 bg-black bg-opacity-30"></div>
                        <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='800' height='400' viewBox='0 0 800 400'%3E%3Cpath fill='%23ffffff' fill-opacity='0.1' d='M0 0h800v400H0z'/%3E%3Ccircle fill='%23ffffff' fill-opacity='0.05' cx='150' cy='150' r='80'/%3E%3Ccircle fill='%23ffffff' fill-opacity='0.05' cx='650' cy='250' r='100'/%3E%3C/svg%3E"
                            alt="Banner 2" class="w-full h-full object-cover">
                        <div class="absolute inset-0 flex items-center justify-center">
                            <div class="text-center text-white">
                                <h2 class="text-4xl font-bold mb-4">Residence Terbaik</h2>
                                <p class="text-xl opacity-90">Cari tempat tinggal yang nyaman dan strategis</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Slide 3 -->
                <div class="banner-slide absolute inset-0 opacity-0 transition-opacity duration-1000">
                    <div class="relative w-full h-full bg-gradient-to-r from-orange-600 to-red-600">
                        <div class="absolute inset-0 bg-black bg-opacity-30"></div>
                        <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='800' height='400' viewBox='0 0 800 400'%3E%3Cpath fill='%23ffffff' fill-opacity='0.1' d='M0 0h800v400H0z'/%3E%3Cpolygon fill='%23ffffff' fill-opacity='0.05' points='200,100 300,100 350,200 150,200'/%3E%3Cpolygon fill='%23ffffff' fill-opacity='0.05' points='500,150 650,150 700,300 450,300'/%3E%3C/svg%3E"
                            alt="Banner 3" class="w-full h-full object-cover">
                        <div class="absolute inset-0 flex items-center justify-center">
                            <div class="text-center text-white">
                                <h2 class="text-4xl font-bold mb-4">Kegiatan & Marketplace</h2>
                                <p class="text-xl opacity-90">Ikuti kegiatan kampus dan belanja kebutuhan</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Banner Indicators -->
            <div class="absolute bottom-4 left-1/2 transform -translate-x-1/2 flex space-x-2">
                <div
                    class="banner-indicator w-2 h-2 rounded-full bg-white opacity-100 cursor-pointer transition-opacity">
                </div>
                <div
                    class="banner-indicator w-2 h-2 rounded-full bg-white opacity-50 cursor-pointer transition-opacity">
                </div>
                <div
                    class="banner-indicator w-2 h-2 rounded-full bg-white opacity-50 cursor-pointer transition-opacity">
                </div>
            </div>
        </div>

        <!-- Quick Actions - More Compact -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <a href="{{ route('residences.index') }}"
                class="bg-white rounded-lg shadow-sm p-4 hover:shadow-md transition-all duration-200 group hover:scale-105">
                <div class="flex items-center">
                    <div class="bg-blue-100 rounded-lg p-2 group-hover:bg-blue-200 transition-colors">
                        <i class="fas fa-building text-blue-600 text-lg"></i>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-base font-semibold text-gray-900">Residence</h3>
                        <p class="text-gray-600 text-xs">Cari tempat tinggal</p>
                    </div>
                </div>
            </a>

            <a href="{{ route('activities.index') }}"
                class="bg-white rounded-lg shadow-sm p-4 hover:shadow-md transition-all duration-200 group hover:scale-105">
                <div class="flex items-center">
                    <div class="bg-green-100 rounded-lg p-2 group-hover:bg-green-200 transition-colors">
                        <i class="fas fa-calendar-alt text-green-600 text-lg"></i>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-base font-semibold text-gray-900">Kegiatan</h3>
                        <p class="text-gray-600 text-xs">Ikuti kegiatan kampus</p>
                    </div>
                </div>
            </a>

            <a href="{{ route('marketplace.index') }}"
                class="bg-white rounded-lg shadow-sm p-4 hover:shadow-md transition-all duration-200 group hover:scale-105">
                <div class="flex items-center">
                    <div class="bg-orange-100 rounded-lg p-2 group-hover:bg-orange-200 transition-colors">
                        <i class="fas fa-shopping-bag text-orange-600 text-lg"></i>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-base font-semibold text-gray-900">Marketplace</h3>
                        <p class="text-gray-600 text-xs">Belanja produk</p>
                    </div>
                </div>
            </a>

            <a href="{{ route('user.marketplace.transactions.index') }}"
                class="bg-white rounded-lg shadow-sm p-4 hover:shadow-md transition-all duration-200 group hover:scale-105">
                <div class="flex items-center">
                    <div class="bg-indigo-100 rounded-lg p-2 group-hover:bg-indigo-200 transition-colors">
                        <i class="fas fa-receipt text-indigo-600 text-lg"></i>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-base font-semibold text-gray-900">Transaksi</h3>
                        <p class="text-gray-600 text-xs">Marketplace saya</p>
                    </div>
                </div>
            </a>
        </div>

        <!-- Marketplace Transaction Summary -->
        @if($marketplaceStats['total_transactions'] > 0)
        <div class="mb-8">
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-bold text-gray-900">Ringkasan Transaksi Marketplace</h2>
                    <a href="{{ route('user.marketplace.transactions.index') }}"
                        class="text-blue-600 hover:text-blue-700 font-medium text-sm flex items-center">
                        Lihat semua <i class="fas fa-arrow-right ml-1 text-xs"></i>
                    </a>
                </div>
                
                <!-- Stats Cards -->
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                    <div class="bg-blue-50 rounded-lg p-4">
                        <div class="flex items-center">
                            <div class="bg-blue-100 rounded-lg p-2">
                                <i class="fas fa-shopping-cart text-blue-600"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-600">Total Transaksi</p>
                                <p class="text-lg font-bold text-gray-900">{{ $marketplaceStats['total_transactions'] }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-yellow-50 rounded-lg p-4">
                        <div class="flex items-center">
                            <div class="bg-yellow-100 rounded-lg p-2">
                                <i class="fas fa-clock text-yellow-600"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-600">Menunggu</p>
                                <p class="text-lg font-bold text-gray-900">{{ $marketplaceStats['pending_transactions'] }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-green-50 rounded-lg p-4">
                        <div class="flex items-center">
                            <div class="bg-green-100 rounded-lg p-2">
                                <i class="fas fa-check-circle text-green-600"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-600">Selesai</p>
                                <p class="text-lg font-bold text-gray-900">{{ $marketplaceStats['completed_transactions'] }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-purple-50 rounded-lg p-4">
                        <div class="flex items-center">
                            <div class="bg-purple-100 rounded-lg p-2">
                                <i class="fas fa-money-bill-wave text-purple-600"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-600">Total Belanja</p>
                                <p class="text-lg font-bold text-gray-900">Rp {{ number_format($marketplaceStats['total_spent'], 0, ',', '.') }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Transactions -->
                @if($recentTransactions->count() > 0)
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-3">Transaksi Terbaru</h3>
                    <div class="space-y-3">
                        @foreach($recentTransactions as $transaction)
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div class="flex items-center space-x-3">
                                <div class="flex-shrink-0">
                                    <img src="{{ $transaction->product->main_image }}"
                                         class="w-12 h-12 rounded-lg object-cover"
                                         alt="{{ $transaction->product->name }}">
                                </div>
                                <div>
                                    <h4 class="text-sm font-semibold text-gray-900">{{ $transaction->product->name }}</h4>
                                    <p class="text-xs text-gray-600">{{ $transaction->created_at->format('d M Y') }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-bold text-blue-600">Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</p>
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                    @if($transaction->status == 'completed') bg-green-100 text-green-800
                                    @elseif($transaction->status == 'pending') bg-yellow-100 text-yellow-800
                                    @elseif($transaction->status == 'cancelled') bg-red-100 text-red-800
                                    @else bg-blue-100 text-blue-800 @endif">
                                    {{ $transaction->status_label }}
                                </span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>
        @endif

        <!-- Featured Residences -->
        @if($residences->count() > 0)
        <div class="mb-8">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-bold text-gray-900">Residence Terbaru</h2>
                <a href="{{ route('residences.index') }}"
                    class="text-blue-600 hover:text-blue-700 font-medium text-sm flex items-center">
                    Lihat semua <i class="fas fa-arrow-right ml-1 text-xs"></i>
                </a>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
                @foreach($residences as $residence)
                <div
                    class="bg-white rounded-lg shadow-sm overflow-hidden hover:shadow-md transition-all duration-200 hover:scale-105">
                    <div class="relative">
                        @if($residence->images && count($residence->images) > 0)
                        <img src="{{ asset('storage/' . $residence->images[0]) }}" alt="{{ $residence->name }}"
                            class="w-full h-32 object-cover">
                        @else
                        <div class="w-full h-32 bg-gray-200 flex items-center justify-center">
                            <i class="fas fa-building text-gray-400 text-2xl"></i>
                        </div>
                        @endif
                        @if($residence->discount_value)
                        <div class="absolute top-2 left-2 bg-red-500 text-white px-2 py-1 rounded text-xs font-medium">
                            @if($residence->discount_type === 'percentage')
                            -{{ $residence->discount_value }}%
                            @else
                            -{{ number_format($residence->discount_value/1000) }}K
                            @endif
                        </div>
                        @endif
                    </div>
                    <div class="p-3">
                        <h3 class="font-semibold text-gray-900 mb-1 text-sm line-clamp-1">{{ $residence->name }}</h3>
                        <p class="text-gray-600 text-xs mb-2 line-clamp-2">{{ Str::limit($residence->description, 60) }}
                        </p>
                        <div class="flex items-center text-gray-500 text-xs mb-2">
                            <i class="fas fa-map-marker-alt mr-1"></i>
                            <span class="truncate">{{ Str::limit($residence->address, 30) }}</span>
                        </div>
                        <div class="space-y-2">
                            <div>
                                <span class="text-sm font-bold text-blue-600">
                                    Rp {{ number_format($residence->getDiscountedPrice()/1000) }}K
                                </span>
                                @if($residence->discount_value)
                                <span class="text-xs text-gray-500 line-through ml-1">
                                    {{ number_format($residence->price/1000) }}K
                                </span>
                                @endif
                                <span class="text-xs text-gray-500">/bulan</span>
                            </div>
                            <a href="{{ route('residences.show', $residence) }}"
                                class="w-full bg-blue-600 text-white px-3 py-1.5 rounded text-xs hover:bg-blue-700 transition-colors text-center block">
                                Lihat Detail
                            </a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Upcoming Activities -->
        @if($activities->count() > 0)
        <div class="mb-8">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-bold text-gray-900">Kegiatan Mendatang</h2>
                <a href="{{ route('activities.index') }}"
                    class="text-blue-600 hover:text-blue-700 font-medium text-sm flex items-center">
                    Lihat semua <i class="fas fa-arrow-right ml-1 text-xs"></i>
                </a>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
                @foreach($activities as $activity)
                <div
                    class="bg-white rounded-lg shadow-sm overflow-hidden hover:shadow-md transition-all duration-200 hover:scale-105">
                    <div class="relative">
                        @if($activity->images && count($activity->images) > 0)
                        <img src="{{ asset('storage/' . $activity->images[0]) }}" alt="{{ $activity->name }}"
                            class="w-full h-32 object-cover">
                        @else
                        <div class="w-full h-32 bg-gray-200 flex items-center justify-center">
                            <i class="fas fa-calendar-alt text-gray-400 text-2xl"></i>
                        </div>
                        @endif
                        @if($activity->discount_value)
                        <div class="absolute top-2 left-2 bg-red-500 text-white px-2 py-1 rounded text-xs font-medium">
                            @if($activity->discount_type === 'percentage')
                            -{{ $activity->discount_value }}%
                            @else
                            -{{ number_format($activity->discount_value/1000) }}K
                            @endif
                        </div>
                        @endif
                    </div>
                    <div class="p-3">
                        <h3 class="font-semibold text-gray-900 mb-1 text-sm line-clamp-1">{{ $activity->name }}</h3>
                        <p class="text-gray-600 text-xs mb-2 line-clamp-2">{{ Str::limit($activity->description, 60) }}
                        </p>
                        <div class="flex items-center text-gray-500 text-xs mb-1">
                            <i class="fas fa-calendar mr-1"></i>
                            <span>{{ $activity->event_date->format('d M Y') }}</span>
                        </div>
                        <div class="flex items-center text-gray-500 text-xs mb-2">
                            <i class="fas fa-map-marker-alt mr-1"></i>
                            <span class="truncate">{{ Str::limit($activity->location, 25) }}</span>
                        </div>
                        <div class="space-y-2">
                            <div>
                                <span class="text-sm font-bold text-green-600">
                                    Rp {{ number_format($activity->getDiscountedPrice()/1000) }}K
                                </span>
                                @if($activity->discount_value)
                                <span class="text-xs text-gray-500 line-through ml-1">
                                    {{ number_format($activity->price/1000) }}K
                                </span>
                                @endif
                            </div>
                            <a href="{{ route('activities.show', $activity) }}"
                                class="w-full bg-green-600 text-white px-3 py-1.5 rounded text-xs hover:bg-green-700 transition-colors text-center block">
                                Lihat Detail
                            </a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Latest Marketplace Products -->
        @if($products->count() > 0)
        <div class="mb-8">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-bold text-gray-900">Produk Marketplace Terbaru</h2>
                <a href="{{ route('marketplace.index') }}"
                    class="text-blue-600 hover:text-blue-700 font-medium text-sm flex items-center">
                    Lihat semua <i class="fas fa-arrow-right ml-1 text-xs"></i>
                </a>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
                @foreach($products as $product)
                <div
                    class="bg-white rounded-lg shadow-sm overflow-hidden hover:shadow-md transition-all duration-200 hover:scale-105">
                    <div class="relative">
                        @if($product->images && count($product->images) > 0)
                        <img src="{{ asset('storage/' . $product->images[0]) }}" alt="{{ $product->name }}"
                            class="w-full h-32 object-cover">
                        @else
                        <div class="w-full h-32 bg-gray-200 flex items-center justify-center">
                            <i class="fas fa-shopping-bag text-gray-400 text-2xl"></i>
                        </div>
                        @endif
                        @if($product->discount_percentage > 0)
                        <div class="absolute top-2 left-2 bg-red-500 text-white px-2 py-1 rounded text-xs font-medium">
                            -{{ $product->discount_percentage }}%
                        </div>
                        @endif
                        <div
                            class="absolute top-2 right-2 bg-white bg-opacity-90 px-2 py-1 rounded text-xs font-medium">
                            {{ $product->condition_label }}
                        </div>
                    </div>
                    <div class="p-3">
                        <h3 class="font-semibold text-gray-900 mb-1 text-sm line-clamp-1">{{ $product->name }}</h3>
                        <p class="text-gray-600 text-xs mb-2 line-clamp-2">{{ Str::limit($product->description, 60) }}
                        </p>
                        <div class="flex items-center text-gray-500 text-xs mb-2">
                            <i class="fas fa-map-marker-alt mr-1"></i>
                            <span class="truncate">{{ Str::limit($product->location, 25) }}</span>
                        </div>
                        <div class="space-y-2">
                            <div>
                                <span class="text-sm font-bold text-orange-600">
                                    Rp {{ number_format($product->price/1000) }}K
                                </span>
                                <div class="text-xs text-gray-500">
                                    Stok: {{ $product->stock_quantity }}
                                </div>
                            </div>
                            <a href="{{ route('marketplace.show', $product) }}"
                                class="w-full bg-orange-600 text-white px-3 py-1.5 rounded text-xs hover:bg-orange-700 transition-colors text-center block">
                                Lihat Detail
                            </a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Empty State -->
        @if($residences->count() == 0 && $activities->count() == 0 && $products->count() == 0)
        <div class="text-center py-12">
            <div class="bg-white rounded-lg shadow-sm p-8">
                <i class="fas fa-store text-gray-400 text-6xl mb-4"></i>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Belum Ada Konten Tersedia</h3>
                <p class="text-gray-600 mb-6">Saat ini belum ada residence, kegiatan, atau produk yang tersedia.</p>
                <div class="flex flex-wrap justify-center gap-4">
                    <a href="{{ route('residences.index') }}"
                        class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                        Jelajahi Residence
                    </a>
                    <a href="{{ route('activities.index') }}"
                        class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition-colors">
                        Lihat Kegiatan
                    </a>
                    <a href="{{ route('marketplace.index') }}"
                        class="bg-orange-600 text-white px-6 py-2 rounded-lg hover:bg-orange-700 transition-colors">
                        Kunjungi Marketplace
                    </a>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const slides = document.querySelectorAll('.banner-slide');
    const indicators = document.querySelectorAll('.banner-indicator');
    let currentSlide = 0;

    function showSlide(index) {
        slides.forEach((slide, i) => {
            slide.style.opacity = i === index ? '1' : '0';
        });

        indicators.forEach((indicator, i) => {
            indicator.style.opacity = i === index ? '1' : '0.5';
        });
    }

    function nextSlide() {
        currentSlide = (currentSlide + 1) % slides.length;
        showSlide(currentSlide);
    }

    // Auto slide every 5 seconds
    setInterval(nextSlide, 5000);

    // Click indicators to change slide
    indicators.forEach((indicator, index) => {
        indicator.addEventListener('click', () => {
            currentSlide = index;
            showSlide(currentSlide);
        });
    });
});
</script>

<style>
.line-clamp-1 {
    display: -webkit-box;
    -webkit-line-clamp: 1;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.banner-slide {
    transition: opacity 1s ease-in-out;
}

.banner-indicator {
    transition: opacity 0.3s ease;
}

@media (max-width: 640px) {
    .grid.xl\\:grid-cols-5 {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}
</style>
@endsection