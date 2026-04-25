@extends('layouts.app')
@section('title', 'Detail Transaksi — Admin EduLiving')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <div class="flex items-center gap-3 mb-6">
            <a href="{{ route('admin.marketplace.transactions') }}" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Detail Transaksi</h1>
                <p class="text-gray-500 text-sm font-mono mt-0.5">{{ $transaction->transaction_code }}</p>
            </div>
        </div>

        @php
            $statusConfig = [
                'pending'     => ['label' => 'Menunggu Konfirmasi', 'class' => 'bg-orange-100 text-orange-700 border-orange-200'],
                'confirmed'   => ['label' => 'Dikonfirmasi Seller',  'class' => 'bg-blue-100 text-blue-700 border-blue-200'],
                'in_progress' => ['label' => 'Sedang Diproses',      'class' => 'bg-indigo-100 text-indigo-700 border-indigo-200'],
                'completed'   => ['label' => 'Selesai',              'class' => 'bg-green-100 text-green-700 border-green-200'],
                'cancelled'   => ['label' => 'Dibatalkan',           'class' => 'bg-red-100 text-red-700 border-red-200'],
            ];
            $sc = $statusConfig[$transaction->status] ?? ['label' => $transaction->status, 'class' => 'bg-gray-100 text-gray-700 border-gray-200'];
        @endphp

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Info Transaksi --}}
            <div class="lg:col-span-2 space-y-6">

                {{-- Status Banner --}}
                <div class="bg-white rounded-xl border border-gray-200 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="font-semibold text-gray-900">Status Transaksi</h2>
                        <span class="px-3 py-1 rounded-full text-sm font-medium border {{ $sc['class'] }}">
                            {{ $sc['label'] }}
                        </span>
                    </div>
                    <div class="flex items-center gap-2 text-xs text-gray-500">
                        <i class="fas fa-clock"></i>
                        <span>Dibuat {{ $transaction->created_at->format('d M Y, H:i') }}</span>
                        @if($transaction->completed_at)
                            <span class="mx-1">•</span>
                            <span>Selesai {{ \Carbon\Carbon::parse($transaction->completed_at)->format('d M Y, H:i') }}</span>
                        @endif
                        @if($transaction->cancelled_at)
                            <span class="mx-1">•</span>
                            <span>Dibatalkan {{ \Carbon\Carbon::parse($transaction->cancelled_at)->format('d M Y, H:i') }}</span>
                        @endif
                    </div>
                    @if($transaction->cancellation_reason)
                        <div class="mt-3 p-3 bg-red-50 rounded-lg text-sm text-red-700">
                            <strong>Alasan pembatalan:</strong> {{ $transaction->cancellation_reason }}
                        </div>
                    @endif
                </div>

                {{-- Produk --}}
                <div class="bg-white rounded-xl border border-gray-200 p-6">
                    <h2 class="font-semibold text-gray-900 mb-4">Detail Produk</h2>
                    <div class="flex gap-4">
                        <div class="flex-1">
                            <p class="font-medium text-gray-900 text-lg">{{ $transaction->product->name ?? '—' }}</p>
                            @if($transaction->product?->category)
                                <p class="text-sm text-gray-500 mt-0.5">{{ $transaction->product->category->name }}</p>
                            @endif
                            <div class="mt-3 grid grid-cols-3 gap-4 text-sm">
                                <div>
                                    <p class="text-gray-500">Harga Satuan</p>
                                    <p class="font-medium text-gray-900">Rp {{ number_format($transaction->price, 0, ',', '.') }}</p>
                                </div>
                                <div>
                                    <p class="text-gray-500">Jumlah</p>
                                    <p class="font-medium text-gray-900">{{ $transaction->quantity }}</p>
                                </div>
                                <div>
                                    <p class="text-gray-500">Total</p>
                                    <p class="font-semibold text-green-700 text-lg">Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Catatan Seller --}}
                @if($transaction->seller_notes)
                    <div class="bg-white rounded-xl border border-gray-200 p-6">
                        <h2 class="font-semibold text-gray-900 mb-2">Catatan Seller</h2>
                        <p class="text-sm text-gray-700">{{ $transaction->seller_notes }}</p>
                    </div>
                @endif

                {{-- Bukti Pembayaran --}}
                @if($transaction->payment_proof)
                    <div class="bg-white rounded-xl border border-gray-200 p-6">
                        <h2 class="font-semibold text-gray-900 mb-3">Bukti Pembayaran</h2>
                        <img src="{{ Storage::url($transaction->payment_proof) }}" alt="Bukti Pembayaran"
                             class="rounded-lg border border-gray-200 max-h-64 object-cover">
                    </div>
                @endif

            </div>

            {{-- Sidebar: Buyer & Seller --}}
            <div class="space-y-6">

                <div class="bg-white rounded-xl border border-gray-200 p-5">
                    <h3 class="font-semibold text-gray-900 mb-3 flex items-center gap-2">
                        <i class="fas fa-user text-blue-500 text-sm"></i>Pembeli
                    </h3>
                    <div class="flex items-center gap-3 mb-3">
                        <div class="h-9 w-9 rounded-full bg-blue-100 flex items-center justify-center">
                            <span class="text-blue-700 font-semibold">{{ substr($transaction->buyer->name ?? 'U', 0, 1) }}</span>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ $transaction->buyer->name ?? '—' }}</p>
                            <p class="text-xs text-gray-500">{{ $transaction->buyer->email ?? '' }}</p>
                        </div>
                    </div>
                    @if($transaction->buyer)
                        <a href="{{ route('admin.users.show', $transaction->buyer) }}"
                           class="text-xs text-blue-600 hover:underline">Lihat profil →</a>
                    @endif
                </div>

                <div class="bg-white rounded-xl border border-gray-200 p-5">
                    <h3 class="font-semibold text-gray-900 mb-3 flex items-center gap-2">
                        <i class="fas fa-store text-yellow-500 text-sm"></i>Penjual
                    </h3>
                    <div class="flex items-center gap-3 mb-3">
                        <div class="h-9 w-9 rounded-full bg-yellow-100 flex items-center justify-center">
                            <span class="text-yellow-700 font-semibold">{{ substr($transaction->seller->name ?? 'S', 0, 1) }}</span>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ $transaction->seller->name ?? '—' }}</p>
                            <p class="text-xs text-gray-500">{{ $transaction->seller->email ?? '' }}</p>
                        </div>
                    </div>
                    @if($transaction->seller)
                        <a href="{{ route('admin.users.show', $transaction->seller) }}"
                           class="text-xs text-blue-600 hover:underline">Lihat profil →</a>
                    @endif
                </div>

                {{-- Alamat Pengiriman --}}
                @if($transaction->shipping_address)
                    <div class="bg-white rounded-xl border border-gray-200 p-5">
                        <h3 class="font-semibold text-gray-900 mb-2 flex items-center gap-2">
                            <i class="fas fa-map-marker-alt text-red-500 text-sm"></i>Alamat Pengiriman
                        </h3>
                        <p class="text-sm text-gray-700">{{ $transaction->shipping_address }}</p>
                    </div>
                @endif

            </div>
        </div>
    </div>
</div>
@endsection
