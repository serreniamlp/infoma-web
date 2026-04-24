@extends('layouts.app')

@section('title', 'Beranda Penjual - EduLiving')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        @if(session('success'))
        <div class="bg-green-50 border border-green-200 rounded-xl p-4 mb-6 flex items-center gap-3">
            <i class="fas fa-check-circle text-green-500"></i>
            <p class="text-green-800 font-medium">{{ session('success') }}</p>
        </div>
        @endif

        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-gray-900">Selamat datang, {{ Auth::user()->name }}!</h1>
            <p class="text-gray-600 mt-1">Kelola produk dan pesanan kamu di sini</p>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-8">
            <div class="bg-white rounded-xl shadow-sm p-5 text-center">
                <div class="text-2xl font-bold text-blue-600">{{ $stats['total_products'] }}</div>
                <div class="text-xs text-gray-500 mt-1">Total Produk</div>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-5 text-center">
                <div class="text-2xl font-bold text-green-600">{{ $stats['active_products'] }}</div>
                <div class="text-xs text-gray-500 mt-1">Produk Aktif</div>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-5 text-center">
                <div class="text-2xl font-bold text-gray-600">{{ $stats['total_orders'] }}</div>
                <div class="text-xs text-gray-500 mt-1">Total Pesanan</div>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-5 text-center">
                <div class="text-2xl font-bold text-yellow-600">{{ $stats['pending_orders'] }}</div>
                <div class="text-xs text-gray-500 mt-1">Pesanan Baru</div>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-5 text-center">
                <div class="text-2xl font-bold text-purple-600">{{ $stats['completed_orders'] }}</div>
                <div class="text-xs text-gray-500 mt-1">Selesai</div>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-5 text-center">
                <div class="text-2xl font-bold text-green-600 text-sm">Rp {{ number_format($stats['total_revenue']) }}</div>
                <div class="text-xs text-gray-500 mt-1">Total Pendapatan</div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
            <a href="{{ route('user.marketplace.seller.create') }}"
               class="flex items-center gap-4 p-5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl transition-colors">
                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-plus text-xl"></i>
                </div>
                <div>
                    <div class="font-semibold">Tambah Produk</div>
                    <div class="text-blue-100 text-sm">Jual barang baru</div>
                </div>
            </a>
            <a href="{{ route('user.marketplace.seller.orders') }}"
               class="flex items-center gap-4 p-5 bg-yellow-500 hover:bg-yellow-600 text-white rounded-xl transition-colors">
                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-clipboard-list text-xl"></i>
                </div>
                <div>
                    <div class="font-semibold">Kelola Pesanan</div>
                    <div class="text-yellow-100 text-sm">
                        {{ $stats['pending_orders'] > 0 ? $stats['pending_orders'] . ' pesanan baru' : 'Lihat semua pesanan' }}
                    </div>
                </div>
            </a>
            <a href="{{ route('user.marketplace.seller.my-products') }}"
               class="flex items-center gap-4 p-5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl transition-colors">
                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-boxes text-xl"></i>
                </div>
                <div>
                    <div class="font-semibold">Produk Saya</div>
                    <div class="text-indigo-100 text-sm">Kelola daftar produk</div>
                </div>
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Pesanan Terbaru -->
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="p-6 border-b border-gray-100 flex items-center justify-between">
                    <h2 class="font-semibold text-gray-900">Pesanan Terbaru</h2>
                    <a href="{{ route('user.marketplace.seller.orders') }}"
                       class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                        Lihat semua <i class="fas fa-arrow-right ml-1 text-xs"></i>
                    </a>
                </div>
                <div class="p-6">
                    @if($recentOrders->count() > 0)
                    <div class="space-y-4">
                        @foreach($recentOrders as $order)
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-shopping-bag text-blue-600 text-sm"></i>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900 text-sm">{{ Str::limit($order->product->name, 25) }}</p>
                                    <p class="text-xs text-gray-500">{{ $order->buyer->name }} • {{ $order->created_at->diffForHumans() }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-1 rounded-full text-xs font-medium
                                    @if($order->status === 'pending') bg-yellow-100 text-yellow-800
                                    @elseif($order->status === 'confirmed') bg-blue-100 text-blue-800
                                    @elseif($order->status === 'completed') bg-green-100 text-green-800
                                    @elseif($order->status === 'cancelled') bg-red-100 text-red-800
                                    @else bg-gray-100 text-gray-800 @endif">
                                    {{ $order->status_label }}
                                </span>
                                <a href="{{ route('user.marketplace.seller.orders.show', $order) }}"
                                   class="text-blue-600 hover:text-blue-700">
                                    <i class="fas fa-eye text-sm"></i>
                                </a>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="text-center py-8">
                        <i class="fas fa-inbox text-gray-400 text-3xl mb-3"></i>
                        <p class="text-gray-500 text-sm">Belum ada pesanan masuk</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Produk Terbaru -->
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="p-6 border-b border-gray-100 flex items-center justify-between">
                    <h2 class="font-semibold text-gray-900">Produk Saya</h2>
                    <a href="{{ route('user.marketplace.seller.my-products') }}"
                       class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                        Lihat semua <i class="fas fa-arrow-right ml-1 text-xs"></i>
                    </a>
                </div>
                <div class="p-6">
                    @if($recentProducts->count() > 0)
                    <div class="space-y-4">
                        @foreach($recentProducts as $product)
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div class="flex items-center gap-3">
                                @if($product->images && count($product->images) > 0)
                                <img src="{{ asset('storage/' . $product->images[0]) }}"
                                     class="w-10 h-10 object-cover rounded-lg flex-shrink-0"
                                     alt="{{ $product->name }}">
                                @else
                                <div class="w-10 h-10 bg-gray-200 rounded-lg flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-image text-gray-400 text-sm"></i>
                                </div>
                                @endif
                                <div>
                                    <p class="font-medium text-gray-900 text-sm">{{ Str::limit($product->name, 25) }}</p>
                                    <p class="text-xs text-gray-500">Rp {{ number_format($product->price) }}</p>
                                </div>
                            </div>
                            <span class="px-2 py-1 rounded-full text-xs font-medium
                                {{ $product->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                {{ $product->status === 'active' ? 'Aktif' : ucfirst($product->status) }}
                            </span>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="text-center py-8">
                        <i class="fas fa-box-open text-gray-400 text-3xl mb-3"></i>
                        <p class="text-gray-500 text-sm">Belum ada produk</p>
                        <a href="{{ route('user.marketplace.seller.create') }}"
                           class="text-blue-600 hover:text-blue-700 text-sm font-medium mt-2 inline-block">
                            Tambah produk pertama →
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection