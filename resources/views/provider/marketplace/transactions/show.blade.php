@extends('layouts.app')

@section('title', 'Detail Transaksi Marketplace')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Breadcrumb -->
        <nav class="flex mb-8" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="{{ route('provider.marketplace.transactions.index') }}"
                       class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-green-600 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
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
            <div class="bg-gradient-to-r from-green-600 to-green-700 px-8 py-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-white flex items-center">
                            <svg class="w-8 h-8 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                            Detail Transaksi
                        </h1>
                        <p class="text-green-100 mt-2">{{ $transaction->transaction_code }}</p>
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
                        <h2 class="text-xl font-bold text-gray-900 flex items-center">
                            <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                            </svg>
                            Informasi Produk
                        </h2>
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
                                        <span class="font-bold text-green-600 ml-2">Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Buyer Information -->
                <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                        <h2 class="text-xl font-bold text-gray-900 flex items-center">
                            <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            Informasi Pembeli
                        </h2>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Pembeli</label>
                                <p class="text-gray-900 font-medium">{{ $transaction->buyer_name }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nomor Telepon</label>
                                <p class="text-gray-900 font-medium">{{ $transaction->buyer_phone }}</p>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Alamat</label>
                                <p class="text-gray-900">{{ $transaction->buyer_address }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pickup Information -->
                <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                        <h2 class="text-xl font-bold text-gray-900 flex items-center">
                            <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            Informasi Pengambilan
                        </h2>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Metode Pengambilan</label>
                                <p class="text-gray-900 font-medium">{{ $transaction->pickup_method_label }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Metode Pembayaran</label>
                                <p class="text-gray-900 font-medium">{{ $transaction->payment_method }}</p>
                            </div>
                            @if($transaction->pickup_address)
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Alamat Pengambilan</label>
                                <p class="text-gray-900">{{ $transaction->pickup_address }}</p>
                            </div>
                            @endif
                            @if($transaction->pickup_notes)
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Catatan Pengambilan</label>
                                <p class="text-gray-900">{{ $transaction->pickup_notes }}</p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Payment Proof -->
                @if($transaction->payment_proof)
                <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                        <h2 class="text-xl font-bold text-gray-900 flex items-center">
                            <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                            </svg>
                            Bukti Pembayaran
                        </h2>
                    </div>
                    <div class="p-6">
                        <div class="text-center">
                            <img src="{{ $transaction->payment_proof_url }}"
                                 class="max-w-full h-auto rounded-lg shadow-md border border-gray-200 mx-auto"
                                 alt="Bukti Pembayaran">
                            <p class="text-sm text-gray-500 mt-2">Klik gambar untuk memperbesar</p>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Seller Notes -->
                @if($transaction->seller_notes)
                <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                        <h2 class="text-xl font-bold text-gray-900 flex items-center">
                            <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-1l-4 4z"></path>
                            </svg>
                            Catatan Penjual
                        </h2>
                    </div>
                    <div class="p-6">
                        <p class="text-gray-900">{{ $transaction->seller_notes }}</p>
                    </div>
                </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="space-y-8">
                <!-- Transaction Status Management -->
                <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                        <h2 class="text-xl font-bold text-gray-900 flex items-center">
                            <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Kelola Status
                        </h2>
                    </div>
                    <div class="p-6">
                        <form action="{{ route('provider.marketplace.transactions.update-status', $transaction) }}" method="POST" class="space-y-4">
                            @csrf
                            @method('PUT')

                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status Transaksi</label>
                                <select id="status" name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                    <option value="pending" {{ $transaction->status == 'pending' ? 'selected' : '' }}>Menunggu Konfirmasi</option>
                                    <option value="confirmed" {{ $transaction->status == 'confirmed' ? 'selected' : '' }}>Dikonfirmasi</option>
                                    <option value="in_progress" {{ $transaction->status == 'in_progress' ? 'selected' : '' }}>Dalam Proses</option>
                                    <option value="completed" {{ $transaction->status == 'completed' ? 'selected' : '' }}>Selesai</option>
                                    <option value="cancelled" {{ $transaction->status == 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                                </select>
                            </div>

                            <div>
                                <label for="seller_notes" class="block text-sm font-medium text-gray-700 mb-2">Catatan (Opsional)</label>
                                <textarea id="seller_notes" name="seller_notes" rows="3"
                                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                          placeholder="Tambahkan catatan untuk pembeli...">{{ $transaction->seller_notes }}</textarea>
                            </div>

                            <div id="cancellation_reason_div" style="display: none;">
                                <label for="cancellation_reason" class="block text-sm font-medium text-gray-700 mb-2">Alasan Pembatalan</label>
                                <textarea id="cancellation_reason" name="cancellation_reason" rows="3"
                                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                          placeholder="Jelaskan alasan pembatalan...">{{ $transaction->cancellation_reason }}</textarea>
                            </div>

                            <button type="submit" class="w-full px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition-colors duration-200">
                                Update Status
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Payment Status Management -->
                @if($transaction->payment_proof && $transaction->payment_status !== 'paid')
                <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                        <h2 class="text-xl font-bold text-gray-900 flex items-center">
                            <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                            </svg>
                            Konfirmasi Pembayaran
                        </h2>
                    </div>
                    <div class="p-6">
                        <form action="{{ route('provider.marketplace.transactions.confirm-payment', $transaction) }}" method="POST" class="space-y-4">
                            @csrf

                            <div>
                                <label for="payment_status" class="block text-sm font-medium text-gray-700 mb-2">Status Pembayaran</label>
                                <select id="payment_status" name="payment_status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                    <option value="paid">Pembayaran Valid</option>
                                    <option value="failed">Pembayaran Tidak Valid</option>
                                </select>
                            </div>

                            <button type="submit" class="w-full px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors duration-200">
                                Konfirmasi Pembayaran
                            </button>
                        </form>
                    </div>
                </div>
                @endif

                <!-- Transaction Timeline -->
                <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                        <h2 class="text-xl font-bold text-gray-900 flex items-center">
                            <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Timeline Transaksi
                        </h2>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            <div class="flex items-start space-x-3">
                                <div class="flex-shrink-0 w-2 h-2 bg-blue-500 rounded-full mt-2"></div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">Transaksi Dibuat</p>
                                    <p class="text-xs text-gray-500">{{ $transaction->created_at->format('d M Y H:i') }}</p>
                                </div>
                            </div>

                            @if($transaction->payment_proof)
                            <div class="flex items-start space-x-3">
                                <div class="flex-shrink-0 w-2 h-2 bg-yellow-500 rounded-full mt-2"></div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">Bukti Pembayaran Diupload</p>
                                    <p class="text-xs text-gray-500">{{ $transaction->updated_at->format('d M Y H:i') }}</p>
                                </div>
                            </div>
                            @endif

                            @if($transaction->status === 'completed' && $transaction->completed_at)
                            <div class="flex items-start space-x-3">
                                <div class="flex-shrink-0 w-2 h-2 bg-green-500 rounded-full mt-2"></div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">Transaksi Selesai</p>
                                    <p class="text-xs text-gray-500">{{ $transaction->completed_at->format('d M Y H:i') }}</p>
                                </div>
                            </div>
                            @endif

                            @if($transaction->status === 'cancelled' && $transaction->cancelled_at)
                            <div class="flex items-start space-x-3">
                                <div class="flex-shrink-0 w-2 h-2 bg-red-500 rounded-full mt-2"></div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">Transaksi Dibatalkan</p>
                                    <p class="text-xs text-gray-500">{{ $transaction->cancelled_at->format('d M Y H:i') }}</p>
                                    @if($transaction->cancellation_reason)
                                    <p class="text-xs text-gray-600 mt-1">{{ $transaction->cancellation_reason }}</p>
                                    @endif
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Transaction Summary -->
                <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                        <h2 class="text-xl font-bold text-gray-900 flex items-center">
                            <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                            </svg>
                            Ringkasan
                        </h2>
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
                            <span class="font-bold text-green-600">Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const statusSelect = document.getElementById('status');
    const cancellationDiv = document.getElementById('cancellation_reason_div');

    function toggleCancellationReason() {
        if (statusSelect.value === 'cancelled') {
            cancellationDiv.style.display = 'block';
        } else {
            cancellationDiv.style.display = 'none';
        }
    }

    statusSelect.addEventListener('change', toggleCancellationReason);
    toggleCancellationReason(); // Initial check
});
</script>
@endsection
