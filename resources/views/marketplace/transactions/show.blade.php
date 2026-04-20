@extends('layouts.app')

@section('title', 'Detail Transaksi')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Breadcrumb -->
        <nav class="flex mb-8" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="{{ route('marketplace.index') }}"
                       class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path>
                        </svg>
                        Marketplace
                    </a>
                </li>
                <li>
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <a href="{{ auth()->user()->hasRole('provider') ? route('provider.marketplace.transactions.index') : route('user.marketplace.transactions.index') }}"
                           class="ml-1 text-sm font-medium text-gray-700 hover:text-blue-600 md:ml-2 transition-colors">
                            Transaksi Saya
                        </a>
                    </div>
                </li>
                <li aria-current="page">
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Detail Transaksi</span>
                    </div>
                </li>
            </ol>
        </nav>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left Column - Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Transaction Status Header -->
                <div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-xl shadow-lg p-6 text-white">
                    <div class="flex items-center justify-between mb-4">
                        <h1 class="text-2xl font-bold flex items-center">
                            <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Detail Transaksi
                        </h1>
                        <div class="text-right">
                            <div class="text-sm opacity-90">Kode Transaksi</div>
                            <div class="text-lg font-bold">{{ $transaction->transaction_code }}</div>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                                @if($transaction->status == 'completed') bg-green-500 text-white
                                @elseif($transaction->status == 'pending') bg-yellow-500 text-white
                                @elseif($transaction->status == 'cancelled') bg-red-500 text-white
                                @else bg-blue-500 text-white @endif">
                                {{ $transaction->status_label }}
                            </span>
                        </div>
                        <div class="text-right">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                                @if($transaction->payment_status == 'paid') bg-green-500 text-white
                                @else bg-yellow-500 text-white @endif">
                                {{ $transaction->payment_status_label }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Transaction Info -->
                <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                    <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Informasi Transaksi
                        </h2>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-4">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0 w-6 h-6 rounded-full bg-blue-100 flex items-center justify-center mr-3 mt-0.5">
                                        <svg class="w-3 h-3 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-500">Tanggal Transaksi</div>
                                        <div class="text-gray-900 font-semibold">{{ $transaction->created_at->format('d M Y H:i') }}</div>
                                    </div>
                                </div>
                                @if($transaction->completed_at)
                                <div class="flex items-start">
                                    <div class="flex-shrink-0 w-6 h-6 rounded-full bg-green-100 flex items-center justify-center mr-3 mt-0.5">
                                        <svg class="w-3 h-3 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-500">Tanggal Selesai</div>
                                        <div class="text-gray-900 font-semibold">{{ $transaction->completed_at->format('d M Y H:i') }}</div>
                                    </div>
                                </div>
                                @endif
                            </div>
                            <div class="space-y-4">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0 w-6 h-6 rounded-full bg-purple-100 flex items-center justify-center mr-3 mt-0.5">
                                        <svg class="w-3 h-3 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-500">Metode Pengambilan</div>
                                        <div class="text-gray-900 font-semibold">{{ $transaction->pickup_method_label }}</div>
                                    </div>
                                </div>
                                <div class="flex items-start">
                                    <div class="flex-shrink-0 w-6 h-6 rounded-full bg-yellow-100 flex items-center justify-center mr-3 mt-0.5">
                                        <svg class="w-3 h-3 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-500">Metode Pembayaran</div>
                                        <div class="text-gray-900 font-semibold">{{ $transaction->payment_method }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Product Info -->
                <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                    <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                            </svg>
                            Detail Produk
                        </h2>
                    </div>
                    <div class="p-6">
                        <div class="flex flex-col lg:flex-row gap-6">
                            <div class="lg:w-1/4 flex-shrink-0">
                                <img src="{{ $transaction->product->main_image }}"
                                     class="w-full h-48 lg:h-32 object-cover rounded-lg shadow-sm border border-gray-200"
                                     alt="{{ $transaction->product->name }}">
                            </div>
                            <div class="lg:w-3/4">
                                <h3 class="text-xl font-bold text-gray-900 mb-2">{{ $transaction->product->name }}</h3>
                                <p class="text-gray-600 mb-4 leading-relaxed">{{ $transaction->product->description }}</p>
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                                        <div>
                                            <div class="text-gray-500 font-medium">Harga Satuan</div>
                                            <div class="text-lg font-bold text-blue-600">Rp {{ number_format($transaction->unit_price, 0, ',', '.') }}</div>
                                        </div>
                                        <div>
                                            <div class="text-gray-500 font-medium">Jumlah</div>
                                            <div class="text-lg font-bold text-gray-900">{{ $transaction->quantity }}</div>
                                        </div>
                                        <div>
                                            <div class="text-gray-500 font-medium">Total Harga</div>
                                            <div class="text-lg font-bold text-green-600">Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Buyer Info -->
                <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                    <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            Informasi Pembeli
                        </h2>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-4">
                                <div>
                                    <div class="text-sm font-medium text-gray-500">Nama Pembeli</div>
                                    <div class="text-gray-900 font-semibold">{{ $transaction->buyer_name }}</div>
                                </div>
                                <div>
                                    <div class="text-sm font-medium text-gray-500">Nomor Telepon</div>
                                    <div class="text-gray-900 font-semibold">{{ $transaction->buyer_phone }}</div>
                                </div>
                            </div>
                            <div>
                                <div class="text-sm font-medium text-gray-500">Alamat Pembeli</div>
                                <div class="text-gray-900 font-semibold bg-gray-50 rounded-lg p-3 mt-1">{{ $transaction->buyer_address }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pickup Info -->
                <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                    <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            Informasi Pengambilan
                        </h2>
                    </div>
                    <div class="p-6 space-y-4">
                        <div>
                            <div class="text-sm font-medium text-gray-500">Metode Pengambilan</div>
                            <div class="text-gray-900 font-semibold">{{ $transaction->pickup_method_label }}</div>
                        </div>
                        @if($transaction->pickup_address)
                        <div>
                            <div class="text-sm font-medium text-gray-500">Alamat Pengambilan</div>
                            <div class="text-gray-900 font-semibold bg-gray-50 rounded-lg p-3 mt-1">{{ $transaction->pickup_address }}</div>
                        </div>
                        @endif
                        @if($transaction->pickup_notes)
                        <div>
                            <div class="text-sm font-medium text-gray-500">Catatan</div>
                            <div class="text-gray-900 bg-yellow-50 border-l-4 border-yellow-400 rounded-lg p-3 mt-1">{{ $transaction->pickup_notes }}</div>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Payment Proof -->
                @if($transaction->payment_proof)
                <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                    <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Bukti Pembayaran
                        </h2>
                    </div>
                    <div class="p-6">
                        <div class="max-w-md mx-auto">
                            <img src="{{ $transaction->payment_proof_url }}"
                                 class="w-full rounded-lg shadow-sm border border-gray-200"
                                 alt="Bukti Pembayaran"
                                 onclick="openImageModal(this.src)">
                        </div>
                    </div>
                </div>
                @endif

                <!-- Rating -->
                @if($transaction->rating)
                <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                    <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-yellow-600" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"></path>
                            </svg>
                            Rating & Ulasan
                        </h2>
                    </div>
                    <div class="p-6">
                        <div class="flex items-start space-x-4">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-blue-600 rounded-full flex items-center justify-center text-white font-bold text-lg">
                                    {{ substr($transaction->rating->user->name, 0, 1) }}
                                </div>
                            </div>
                            <div class="flex-grow">
                                <div class="flex items-center space-x-3 mb-2">
                                    <h3 class="font-semibold text-gray-900">{{ $transaction->rating->user->name }}</h3>
                                    <div class="flex items-center">
                                        @for($i = 1; $i <= 5; $i++)
                                            <svg class="w-5 h-5 {{ $i <= $transaction->rating->rating ? 'text-yellow-400' : 'text-gray-300' }}"
                                                 fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"></path>
                                            </svg>
                                        @endfor
                                    </div>
                                </div>
                                @if($transaction->rating->review)
                                <p class="text-gray-700 mb-2">{{ $transaction->rating->review }}</p>
                                @endif
                                <p class="text-sm text-gray-500">{{ $transaction->rating->created_at->format('d M Y') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <!-- Right Column - Actions -->
            <div class="lg:col-span-1">
                <div class="sticky top-6">
                    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                        <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                            <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4"></path>
                                </svg>
                                Aksi
                            </h2>
                        </div>
                        <div class="p-6 space-y-3">
                            @if($transaction->buyer_id === auth()->id())
                                <!-- Buyer Actions -->
                                @if($transaction->status === 'pending' && $transaction->payment_status === 'pending')
                                    <button type="button"
                                            class="w-full bg-gradient-to-r from-blue-600 to-blue-700 text-white py-3 px-4 rounded-lg font-semibold hover:from-blue-700 hover:to-blue-800 focus:outline-none focus:ring-4 focus:ring-blue-300 transition-all duration-200 flex items-center justify-center"
                                            onclick="openModal('uploadPaymentModal')">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                        </svg>
                                        Upload Bukti Pembayaran
                                    </button>
                                @endif

                                @if($transaction->status === 'completed' && !$transaction->rating)
                                    <button type="button"
                                            class="w-full bg-gradient-to-r from-yellow-500 to-yellow-600 text-white py-3 px-4 rounded-lg font-semibold hover:from-yellow-600 hover:to-yellow-700 focus:outline-none focus:ring-4 focus:ring-yellow-300 transition-all duration-200 flex items-center justify-center"
                                            onclick="openModal('ratingModal')">
                                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"></path>
                                        </svg>
                                        Beri Rating
                                    </button>
                                @endif

                                @if($transaction->canBeCancelled())
                                    <form method="POST" action="{{ route('marketplace.transactions.update-status', $transaction) }}" class="w-full">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="status" value="cancelled">
                                        <button type="button"
                                                class="w-full bg-gradient-to-r from-red-500 to-red-600 text-white py-3 px-4 rounded-lg font-semibold hover:from-red-600 hover:to-red-700 focus:outline-none focus:ring-4 focus:ring-red-300 transition-all duration-200 flex items-center justify-center"
                                                onclick="cancelTransaction(this)">
                                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                            Batalkan Transaksi
                                        </button>
                                    </form>
                                @endif
                            @else
                                <!-- Seller Actions -->
                                @if($transaction->status === 'pending')
                                    <form method="POST" action="{{ route('marketplace.transactions.update-status', $transaction) }}" class="w-full">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="status" value="confirmed">
                                        <button type="submit"
                                                class="w-full bg-gradient-to-r from-green-500 to-green-600 text-white py-3 px-4 rounded-lg font-semibold hover:from-green-600 hover:to-green-700 focus:outline-none focus:ring-4 focus:ring-green-300 transition-all duration-200 flex items-center justify-center">
                                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                            Konfirmasi Transaksi
                                        </button>
                                    </form>
                                @endif

                                @if($transaction->status === 'confirmed')
                                    <form method="POST" action="{{ route('marketplace.transactions.update-status', $transaction) }}" class="w-full">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="status" value="in_progress">
                                        <button type="submit"
                                                class="w-full bg-gradient-to-r from-blue-500 to-blue-600 text-white py-3 px-4 rounded-lg font-semibold hover:from-blue-600 hover:to-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-300 transition-all duration-200 flex items-center justify-center">
                                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            Proses Pengiriman
                                        </button>
                                    </form>
                                @endif

                                @if($transaction->status === 'in_progress')
                                    <form method="POST" action="{{ route('marketplace.transactions.update-status', $transaction) }}" class="w-full">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="status" value="completed">
                                        <button type="submit"
                                                class="w-full bg-gradient-to-r from-green-500 to-green-600 text-white py-3 px-4 rounded-lg font-semibold hover:from-green-600 hover:to-green-700 focus:outline-none focus:ring-4 focus:ring-green-300 transition-all duration-200 flex items-center justify-center">
                                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            Selesaikan Transaksi
                                        </button>
                                    </form>
                                @endif
                            @endif

                            <!-- View Product Button -->
                            <div class="pt-4 border-t border-gray-200">
                                <a href="{{ route('marketplace.show', $transaction->product) }}"
                                   class="w-full bg-gray-100 text-gray-700 py-3 px-4 rounded-lg font-medium hover:bg-gray-200 focus:outline-none focus:ring-4 focus:ring-gray-300 transition-colors duration-200 flex items-center justify-center">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                    Lihat Produk
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Upload Payment Proof Modal -->
<div id="uploadPaymentModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closeModal('uploadPaymentModal')"></div>

        <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <form method="POST" action="{{ route('marketplace.transactions.upload-payment-proof', $transaction) }}" enctype="multipart/form-data">
                @csrf
                <div class="bg-white px-6 pt-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                            </svg>
                            Upload Bukti Pembayaran
                        </h3>
                        <button type="button" class="text-gray-400 hover:text-gray-600" onclick="closeModal('uploadPaymentModal')">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    <div class="space-y-4">
                        <div>
                            <label for="payment_proof" class="block text-sm font-medium text-gray-700 mb-2">
                                Bukti Pembayaran <span class="text-red-500">*</span>
                            </label>
                            <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg hover:border-gray-400 transition-colors">
                                <div class="space-y-1 text-center">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <div class="flex text-sm text-gray-600">
                                        <label for="payment_proof" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                            <span>Upload a file</span>
                                            <input id="payment_proof" name="payment_proof" type="file" accept="image/*" class="sr-only" required onchange="previewImage(this)">
                                        </label>
                                        <p class="pl-1">or drag and drop</p>
                                    </div>
                                    <p class="text-xs text-gray-500">PNG, JPG, GIF up to 10MB</p>
                                </div>
                            </div>
                            <div id="image-preview" class="mt-4 hidden">
                                <img class="h-32 w-auto rounded-lg shadow-sm" alt="Preview">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-6 py-4 flex flex-col-reverse sm:flex-row sm:justify-end sm:space-x-2 space-y-2 space-y-reverse sm:space-y-0">
                    <button type="button"
                            class="w-full sm:w-auto px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                            onclick="closeModal('uploadPaymentModal')">
                        Batal
                    </button>
                    <button type="submit"
                            class="w-full sm:w-auto px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Upload
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Rating Modal -->
<div id="ratingModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closeModal('ratingModal')"></div>

        <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <form method="POST" action="{{ route('marketplace.transactions.rate', $transaction) }}" enctype="multipart/form-data">
                @csrf
                <div class="bg-white px-6 pt-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-yellow-500" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"></path>
                            </svg>
                            Beri Rating
                        </h3>
                        <button type="button" class="text-gray-400 hover:text-gray-600" onclick="closeModal('ratingModal')">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <div class="space-y-6">
                        <!-- Rating Stars -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-3">Rating <span class="text-red-500">*</span></label>
                            <div class="flex justify-center space-x-1">
                                <div class="rating-container">
                                    @for($i = 1; $i <= 5; $i++)
                                        <input type="radio" id="star{{ $i }}" name="rating" value="{{ $i }}" class="sr-only" required>
                                        <label for="star{{ $i }}" class="cursor-pointer text-3xl text-gray-300 hover:text-yellow-400 transition-colors duration-200">
                                            <svg class="w-8 h-8 fill-current" viewBox="0 0 24 24">
                                                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"></path>
                                            </svg>
                                        </label>
                                    @endfor
                                </div>
                            </div>
                            <p class="text-center text-sm text-gray-500 mt-2">Klik bintang untuk memberikan rating</p>
                        </div>

                        <!-- Review Text -->
                        <div>
                            <label for="review" class="block text-sm font-medium text-gray-700 mb-2">Ulasan</label>
                            <textarea class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 transition-colors"
                                      id="review" name="review" rows="4"
                                      placeholder="Bagikan pengalaman Anda dengan produk ini..."></textarea>
                        </div>

                        <!-- Images Upload -->
                        <div>
                            <label for="images" class="block text-sm font-medium text-gray-700 mb-2">Foto (Opsional)</label>
                            <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg hover:border-gray-400 transition-colors">
                                <div class="space-y-1 text-center">
                                    <svg class="mx-auto h-8 w-8 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <div class="flex text-sm text-gray-600">
                                        <label for="images" class="relative cursor-pointer bg-white rounded-md font-medium text-yellow-600 hover:text-yellow-500">
                                            <span>Upload files</span>
                                            <input id="images" name="images[]" type="file" multiple accept="image/*" class="sr-only">
                                        </label>
                                    </div>
                                    <p class="text-xs text-gray-500">PNG, JPG, GIF</p>
                                </div>
                            </div>
                        </div>

                        <!-- Recommendation -->
                        <div class="flex items-center">
                            <input id="is_recommended" name="is_recommended" type="checkbox" value="1"
                                   class="h-4 w-4 text-yellow-600 focus:ring-yellow-500 border-gray-300 rounded">
                            <label for="is_recommended" class="ml-2 block text-sm text-gray-700">
                                Rekomendasikan produk ini
                            </label>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50 px-6 py-4 flex flex-col-reverse sm:flex-row sm:justify-end sm:space-x-2 space-y-2 space-y-reverse sm:space-y-0">
                    <button type="button"
                            class="w-full sm:w-auto px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500"
                            onclick="closeModal('ratingModal')">
                        Batal
                    </button>
                    <button type="submit"
                            class="w-full sm:w-auto px-4 py-2 text-sm font-medium text-white bg-yellow-500 border border-transparent rounded-lg hover:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">
                        Kirim Rating
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Image Modal -->
<div id="imageModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-black bg-opacity-75 transition-opacity" onclick="closeImageModal()"></div>
        <div class="inline-block align-middle">
            <img id="modalImage" class="max-w-full max-h-screen rounded-lg shadow-xl" alt="Full size image">
        </div>
    </div>
</div>

<script>
// Modal Functions
function openModal(modalId) {
    document.getElementById(modalId).classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
}

// Image Preview
function previewImage(input) {
    const preview = document.getElementById('image-preview');
    const img = preview.querySelector('img');

    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            img.src = e.target.result;
            preview.classList.remove('hidden');
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// Full Image Modal
function openImageModal(src) {
    const modal = document.getElementById('imageModal');
    const modalImage = document.getElementById('modalImage');
    modalImage.src = src;
    modal.classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
}

function closeImageModal() {
    document.getElementById('imageModal').classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
}

// Cancel Transaction
function cancelTransaction(button) {
    if (confirm('Apakah Anda yakin ingin membatalkan transaksi ini?')) {
        const form = button.closest('form');
        const reason = prompt('Alasan pembatalan:');
        if (reason) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'cancellation_reason';
            input.value = reason;
            form.appendChild(input);
            form.submit();
        }
    }
}

// Rating Stars Interaction
document.addEventListener('DOMContentLoaded', function() {
    const ratingInputs = document.querySelectorAll('input[name="rating"]');
    const ratingLabels = document.querySelectorAll('.rating-container label');

    ratingLabels.forEach((label, index) => {
        label.addEventListener('click', function() {
            // Update visual state
            ratingLabels.forEach((l, i) => {
                if (i <= index) {
                    l.classList.remove('text-gray-300');
                    l.classList.add('text-yellow-400');
                } else {
                    l.classList.remove('text-yellow-400');
                    l.classList.add('text-gray-300');
                }
            });
        });

        label.addEventListener('mouseenter', function() {
            // Hover effect
            ratingLabels.forEach((l, i) => {
                if (i <= index) {
                    l.classList.add('text-yellow-300');
                }
            });
        });

        label.addEventListener('mouseleave', function() {
            // Remove hover effect
            ratingLabels.forEach((l) => {
                l.classList.remove('text-yellow-300');
            });

            // Restore selected state
            const checkedInput = document.querySelector('input[name="rating"]:checked');
            if (checkedInput) {
                const checkedIndex = parseInt(checkedInput.value) - 1;
                ratingLabels.forEach((l, i) => {
                    if (i <= checkedIndex) {
                        l.classList.remove('text-gray-300');
                        l.classList.add('text-yellow-400');
                    } else {
                        l.classList.remove('text-yellow-400');
                        l.classList.add('text-gray-300');
                    }
                });
            }
        });
    });
});

// Close modals on escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeModal('uploadPaymentModal');
        closeModal('ratingModal');
        closeImageModal();
    }
});
</script>
@endsection
