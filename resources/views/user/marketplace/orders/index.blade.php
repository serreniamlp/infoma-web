@extends('layouts.app')

@section('title', 'Kelola Pesanan - EduLiving')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Kelola Pesanan</h1>
                <p class="text-gray-600 mt-1">Kelola semua pesanan yang masuk</p>
            </div>
            <a href="{{ route('user.marketplace.seller.home') }}"
               class="text-gray-600 hover:text-gray-900 text-sm flex items-center gap-2">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-xl shadow-sm p-4 text-center border-t-4 border-yellow-400">
                <div class="text-2xl font-bold text-yellow-600">{{ $stats['pending'] }}</div>
                <div class="text-xs text-gray-500 mt-1">Menunggu</div>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-4 text-center border-t-4 border-blue-400">
                <div class="text-2xl font-bold text-blue-600">{{ $stats['confirmed'] }}</div>
                <div class="text-xs text-gray-500 mt-1">Dikonfirmasi</div>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-4 text-center border-t-4 border-green-400">
                <div class="text-2xl font-bold text-green-600">{{ $stats['completed'] }}</div>
                <div class="text-xs text-gray-500 mt-1">Selesai</div>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-4 text-center border-t-4 border-red-400">
                <div class="text-2xl font-bold text-red-600">{{ $stats['cancelled'] }}</div>
                <div class="text-xs text-gray-500 mt-1">Dibatalkan</div>
            </div>
        </div>

        <!-- Filter -->
        <div class="bg-white rounded-xl shadow-sm p-5 mb-6">
            <form method="GET" action="{{ route('user.marketplace.seller.orders') }}" class="flex flex-col md:flex-row gap-4">
                <div class="flex-1">
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Cari kode transaksi, nama pembeli, atau produk..."
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                <div>
                    <select name="status" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Semua Status</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Menunggu</option>
                        <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>Dikonfirmasi</option>
                        <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>Diproses</option>
                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Selesai</option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                    </select>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium transition-colors">
                        <i class="fas fa-search mr-2"></i>Cari
                    </button>
                    @if(request()->hasAny(['search', 'status']))
                    <a href="{{ route('user.marketplace.seller.orders') }}"
                       class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg font-medium transition-colors">
                        Reset
                    </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Orders Table -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            @if($orders->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pesanan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Produk</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pembeli</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($orders as $order)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4">
                                <p class="font-medium text-gray-900 text-sm">{{ $order->transaction_code }}</p>
                                <p class="text-xs text-gray-500">{{ $order->created_at->format('d M Y, H:i') }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="font-medium text-gray-900 text-sm">{{ Str::limit($order->product->name, 30) }}</p>
                                <p class="text-xs text-gray-500">{{ $order->quantity }} item</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="font-medium text-gray-900 text-sm">{{ $order->buyer->name }}</p>
                                <p class="text-xs text-gray-500">{{ $order->buyer_phone }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="font-medium text-gray-900 text-sm">Rp {{ number_format($order->total_amount) }}</p>
                                <span class="text-xs px-2 py-0.5 rounded-full
                                    {{ $order->payment_status === 'paid' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                                    {{ $order->payment_status_label }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1 rounded-full text-xs font-medium
                                    @if($order->status === 'pending') bg-yellow-100 text-yellow-800
                                    @elseif($order->status === 'confirmed') bg-blue-100 text-blue-800
                                    @elseif($order->status === 'in_progress') bg-purple-100 text-purple-800
                                    @elseif($order->status === 'completed') bg-green-100 text-green-800
                                    @elseif($order->status === 'cancelled') bg-red-100 text-red-800
                                    @else bg-gray-100 text-gray-800 @endif">
                                    {{ $order->status_label }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <a href="{{ route('user.marketplace.seller.orders.show', $order) }}"
                                   class="text-blue-600 hover:text-blue-700 text-sm font-medium flex items-center gap-1">
                                    <i class="fas fa-eye"></i> Detail
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-6 border-t border-gray-100">
                {{ $orders->appends(request()->query())->links() }}
            </div>
            @else
            <div class="text-center py-16">
                <i class="fas fa-inbox text-gray-400 text-5xl mb-4"></i>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Belum ada pesanan</h3>
                <p class="text-gray-500 text-sm">Pesanan akan muncul di sini setelah pembeli melakukan transaksi</p>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection