@extends('layouts.app')

@section('title', 'Detail Transaksi Marketplace')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Breadcrumb -->
        <nav class="flex mb-8" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="{{ route('user.marketplace.transactions.index') }}"
                       class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l-1 12H6L5 9z"></path>
                        </svg>
                        Transaksi Marketplace
                    </a>
                </li>
                <li aria-current="page">
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">{{ $transaction->transaction_code }}</span>
                    </div>
                </li>
            </ol>
        </nav>

        <!-- Header -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden mb-8">
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-8 py-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-white flex items-center">
                            <svg class="w-8 h-8 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                            Detail Transaksi
                        </h1>
                        <p class="text-blue-100 mt-2">{{ $transaction->transaction_code }}</p>
                    </div>
                    <div class="flex items-center space-x-4">
                        <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold
                            @if($transaction->status == 'completed') bg-green-100 text-green-800
                            @elseif($transaction->status == 'pending') bg-yellow-100 text-yellow-800
                            @elseif($transaction->status == 'cancelled') bg-red-100 text-red-800
                            @else bg-blue-100 text-blue-800 @endif">
                            {{ $transaction->status_label }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-8">
                <!-- Product Information -->
                <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                        <h2 class="text-xl font-bold text-gray-900">Informasi Produk</h2>
                    </div>
                    <div class="p-6">
                        <div class="flex items-start space-x-6">
                            <div class="flex-shrink-0">
                                <img src="{{ $transaction->product->main_image }}"
                                     class="w-32 h-32 rounded-lg object-cover shadow-md border border-gray-200"
                                     alt="{{ $transaction->product->name }}">
                            </div>
                            <div class="flex-grow">
                                <h3 class="text-2xl font-bold text-gray-900 mb-2">{{ $transaction->product->name }}</h3>
                                <p class="text-gray-600 mb-4">{{ $transaction->product->description }}</p>
                                <div class="grid grid-cols-2 gap-4 text-sm">
                                    <div>
                                        <span class="text-gray-500">Kategori:</span>
                                        <span class="font-medium ml-2">{{ $transaction->product->category }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500">Harga Satuan:</span>
                                        <span class="font-medium ml-2">Rp {{ number_format($transaction->unit_price, 0, ',', '.') }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500">Jumlah:</span>
                                        <span class="font-medium ml-2">{{ $transaction->quantity }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500">Total:</span>
                                        <span class="font-bold text-blue-600 ml-2">Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Seller Information -->
                <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                        <h2 class="text-xl font-bold text-gray-900">Informasi Penjual</h2>
                    </div>
                    <div class="p-6">
                        <div class="flex items-center space-x-4">
                            <div class="w-16 h-16 bg-gray-200 rounded-full flex items-center justify-center">
                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">{{ $transaction->seller->name }}</h3>
                                <p class="text-gray-600">{{ $transaction->seller->email }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pickup Information -->
                <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                        <h2 class="text-xl font-bold text-gray-900">Informasi Pengambilan</h2>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Penerima</label>
                                <p class="text-gray-900 font-medium">{{ $transaction->buyer_name }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nomor Telepon</label>
                                <p class="text-gray-900 font-medium">{{ $transaction->buyer_phone }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Metode Pengambilan</label>
                                <p class="text-gray-900 font-medium">{{ $transaction->pickup_method_label }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Metode Pembayaran</label>
                                <p class="text-gray-900 font-medium">{{ $transaction->payment_method }}</p>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Alamat</label>
                                <p class="text-gray-900">{{ $transaction->buyer_address }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                @if($transaction->payment_proof)
                <!-- Payment Proof -->
                <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                        <h2 class="text-xl font-bold text-gray-900">Bukti Pembayaran</h2>
                    </div>
                    <div class="p-6">
                        <div class="text-center">
                            <img src="{{ $transaction->payment_proof_url }}"
                                 class="max-w-full h-auto rounded-lg shadow-md border border-gray-200 mx-auto"
                                 alt="Bukti Pembayaran">
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="space-y-8">
                <!-- Upload Payment Proof -->
                @if($transaction->status === 'pending' && $transaction->payment_status === 'pending')
                <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                        <h2 class="text-xl font-bold text-gray-900">Upload Bukti Pembayaran</h2>
                    </div>
                    <div class="p-6">
                        <form action="{{ route('user.marketplace.transactions.upload-payment-proof', $transaction) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                            @csrf
                            <div>
                                <label for="payment_proof" class="block text-sm font-medium text-gray-700 mb-2">Bukti Pembayaran</label>
                                <input type="file" id="payment_proof" name="payment_proof" accept="image/*" required
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <p class="text-xs text-gray-500 mt-1">Format: JPG, PNG, GIF. Maksimal 2MB</p>
                            </div>
                            <button type="submit" class="w-full px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors duration-200">
                                Upload Bukti Pembayaran
                            </button>
                        </form>
                    </div>
                </div>
                @endif

                <!-- Cancel Transaction -->
                @if($transaction->canBeCancelled())
                <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                        <h2 class="text-xl font-bold text-gray-900">Batalkan Transaksi</h2>
                    </div>
                    <div class="p-6">
                        <form action="{{ route('user.marketplace.transactions.cancel', $transaction) }}" method="POST" class="space-y-4">
                            @csrf
                            @method('PATCH')
                            <div>
                                <label for="cancellation_reason" class="block text-sm font-medium text-gray-700 mb-2">Alasan Pembatalan</label>
                                <textarea id="cancellation_reason" name="cancellation_reason" rows="3" required
                                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent"
                                          placeholder="Jelaskan alasan pembatalan..."></textarea>
                            </div>
                            <button type="submit" class="w-full px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition-colors duration-200"
                                    onclick="return confirm('Apakah Anda yakin ingin membatalkan transaksi ini?')">
                                Batalkan Transaksi
                            </button>
                        </form>
                    </div>
                </div>
                @endif

                <!-- Transaction Summary -->
                <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                        <h2 class="text-xl font-bold text-gray-900">Ringkasan</h2>
                    </div>
                    <div class="p-6 space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Kode Transaksi:</span>
                            <span class="font-mono text-sm bg-gray-100 px-2 py-1 rounded">{{ $transaction->transaction_code }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Status:</span>
                            <span class="font-medium">{{ $transaction->status_label }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Status Pembayaran:</span>
                            <span class="font-medium">{{ $transaction->payment_status_label }}</span>
                        </div>
                        <div class="flex justify-between border-t pt-3">
                            <span class="text-gray-600">Total:</span>
                            <span class="font-bold text-blue-600">Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
