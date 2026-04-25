@extends('layouts.app')
@section('title', 'Aktivitas Pengguna — Admin EduLiving')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <div class="flex items-center gap-3 mb-6">
            <a href="{{ route('admin.users.show', $user) }}" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Aktivitas: {{ $user->name }}</h1>
                <p class="text-gray-500 text-sm mt-0.5">{{ $user->email }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Booking --}}
            <div class="bg-white rounded-xl border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h2 class="font-semibold text-gray-900 flex items-center gap-2">
                        <i class="fas fa-bookmark text-indigo-500 text-sm"></i>Booking ({{ $bookings->count() }})
                    </h2>
                </div>
                <div class="divide-y divide-gray-100">
                    @forelse($bookings as $booking)
                        <div class="px-6 py-3">
                            <p class="text-sm font-medium text-gray-900">{{ $booking->bookable->name ?? '—' }}</p>
                            <p class="text-xs text-gray-500">{{ $booking->created_at->format('d M Y') }}</p>
                            @php
                                $bc = match($booking->status) {
                                    'pending'   => 'bg-orange-100 text-orange-700',
                                    'approved'  => 'bg-blue-100 text-blue-700',
                                    'completed' => 'bg-green-100 text-green-700',
                                    'rejected'  => 'bg-red-100 text-red-700',
                                    default     => 'bg-gray-100 text-gray-600',
                                };
                            @endphp
                            <span class="mt-1 inline-block px-2 py-0.5 rounded-full text-xs font-medium {{ $bc }}">{{ ucfirst($booking->status) }}</span>
                        </div>
                    @empty
                        <div class="px-6 py-6 text-center text-gray-400 text-sm">Belum ada booking</div>
                    @endforelse
                </div>
            </div>

            {{-- Transaksi sebagai Buyer --}}
            <div class="bg-white rounded-xl border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h2 class="font-semibold text-gray-900 flex items-center gap-2">
                        <i class="fas fa-shopping-cart text-green-500 text-sm"></i>Pembelian ({{ $transactions->count() }})
                    </h2>
                </div>
                <div class="divide-y divide-gray-100">
                    @forelse($transactions as $tx)
                        <div class="px-6 py-3">
                            <p class="text-sm font-medium text-gray-900">{{ $tx->product->name ?? '—' }}</p>
                            <p class="text-xs text-gray-500">{{ $tx->created_at->format('d M Y') }}</p>
                            <p class="text-xs font-medium text-green-700 mt-0.5">Rp {{ number_format($tx->total_amount, 0, ',', '.') }}</p>
                            @php
                                $tc = match($tx->status) {
                                    'pending'   => 'bg-orange-100 text-orange-700',
                                    'completed' => 'bg-green-100 text-green-700',
                                    'cancelled' => 'bg-red-100 text-red-700',
                                    default     => 'bg-gray-100 text-gray-600',
                                };
                            @endphp
                            <span class="mt-1 inline-block px-2 py-0.5 rounded-full text-xs font-medium {{ $tc }}">{{ ucfirst($tx->status) }}</span>
                        </div>
                    @empty
                        <div class="px-6 py-6 text-center text-gray-400 text-sm">Belum ada pembelian</div>
                    @endforelse
                </div>
            </div>

            {{-- Produk (jika seller) --}}
            <div class="bg-white rounded-xl border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h2 class="font-semibold text-gray-900 flex items-center gap-2">
                        <i class="fas fa-store text-yellow-500 text-sm"></i>Produk Dijual ({{ $products->count() }})
                    </h2>
                </div>
                <div class="divide-y divide-gray-100">
                    @forelse($products as $product)
                        <div class="px-6 py-3">
                            <p class="text-sm font-medium text-gray-900">{{ $product->name }}</p>
                            <p class="text-xs text-gray-500">Stok: {{ $product->stock_quantity }}</p>
                            <p class="text-xs font-medium text-blue-700 mt-0.5">Rp {{ number_format($product->price, 0, ',', '.') }}</p>
                            <span class="mt-1 inline-block px-2 py-0.5 rounded-full text-xs font-medium
                                         {{ $product->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                {{ $product->status === 'active' ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </div>
                    @empty
                        <div class="px-6 py-6 text-center text-gray-400 text-sm">
                            @if($user->is_seller)
                                Belum ada produk
                            @else
                                Bukan seller
                            @endif
                        </div>
                    @endforelse
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
