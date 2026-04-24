@extends('layouts.app')

@section('title', 'Detail Pesanan - EduLiving')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

        @if(session('success'))
        <div class="bg-green-50 border border-green-200 rounded-xl p-4 mb-6 flex items-center gap-3">
            <i class="fas fa-check-circle text-green-500"></i>
            <p class="text-green-800 font-medium">{{ session('success') }}</p>
        </div>
        @endif

        <!-- Header -->
        <div class="flex items-center gap-4 mb-8">
            <a href="{{ route('user.marketplace.seller.orders') }}"
               class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Detail Pesanan</h1>
                <p class="text-gray-500 text-sm">{{ $transaction->transaction_code }}</p>
            </div>
            <span class="ml-auto px-4 py-1.5 rounded-full text-sm font-medium
                @if($transaction->status === 'pending') bg-yellow-100 text-yellow-800
                @elseif($transaction->status === 'confirmed') bg-blue-100 text-blue-800
                @elseif($transaction->status === 'in_progress') bg-purple-100 text-purple-800
                @elseif($transaction->status === 'completed') bg-green-100 text-green-800
                @elseif($transaction->status === 'cancelled') bg-red-100 text-red-800
                @else bg-gray-100 text-gray-800 @endif">
                {{ $transaction->status_label }}
            </span>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Column -->
            <div class="lg:col-span-2 space-y-6">

                <!-- Info Produk -->
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h2 class="font-semibold text-gray-900 mb-4">Produk Dipesan</h2>
                    <div class="flex gap-4">
                        @if($transaction->product->images && count($transaction->product->images) > 0)
                        <img src="{{ asset('storage/' . $transaction->product->images[0]) }}"
                             class="w-20 h-20 object-cover rounded-lg flex-shrink-0"
                             alt="{{ $transaction->product->name }}">
                        @else
                        <div class="w-20 h-20 bg-gray-200 rounded-lg flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-image text-gray-400"></i>
                        </div>
                        @endif
                        <div class="flex-1">
                            <h3 class="font-medium text-gray-900">{{ $transaction->product->name }}</h3>
                            <p class="text-gray-500 text-sm mt-1">{{ $transaction->quantity }} x Rp {{ number_format($transaction->unit_price) }}</p>
                            <p class="text-blue-600 font-semibold mt-2">Total: Rp {{ number_format($transaction->total_amount) }}</p>
                        </div>
                    </div>
                </div>

                <!-- Info Pembeli -->
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h2 class="font-semibold text-gray-900 mb-4">Informasi Pembeli</h2>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <p class="text-gray-500">Nama</p>
                            <p class="font-medium text-gray-900 mt-0.5">{{ $transaction->buyer_name }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500">No. Telepon</p>
                            <p class="font-medium text-gray-900 mt-0.5">{{ $transaction->buyer_phone }}</p>
                        </div>
                        <div class="col-span-2">
                            <p class="text-gray-500">Alamat</p>
                            <p class="font-medium text-gray-900 mt-0.5">{{ $transaction->buyer_address }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500">Metode Pengambilan</p>
                            <p class="font-medium text-gray-900 mt-0.5">{{ $transaction->pickup_method_label }}</p>
                        </div>
                        @if($transaction->pickup_notes)
                        <div>
                            <p class="text-gray-500">Catatan</p>
                            <p class="font-medium text-gray-900 mt-0.5">{{ $transaction->pickup_notes }}</p>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Bukti Pembayaran -->
                @if($transaction->payment_proof)
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h2 class="font-semibold text-gray-900 mb-4">Bukti Pembayaran</h2>
                    <img src="{{ $transaction->payment_proof_url }}"
                         class="w-full max-w-sm rounded-lg border border-gray-200"
                         alt="Bukti pembayaran">
                </div>
                @endif

            </div>

            <!-- Right Column -->
            <div class="space-y-6">

                <!-- Info Pembayaran -->
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h2 class="font-semibold text-gray-900 mb-4">Pembayaran</h2>
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Metode</span>
                            <span class="font-medium">{{ $transaction->payment_method }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Status</span>
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium
                                {{ $transaction->payment_status === 'paid' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                                {{ $transaction->payment_status_label }}
                            </span>
                        </div>
                        <div class="flex justify-between border-t border-gray-100 pt-3">
                            <span class="font-semibold">Total</span>
                            <span class="font-bold text-blue-600">Rp {{ number_format($transaction->total_amount) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Update Status -->
                @if(!in_array($transaction->status, ['completed', 'cancelled']))
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h2 class="font-semibold text-gray-900 mb-4">Update Status</h2>
                    <form method="POST" action="{{ route('user.marketplace.seller.orders.updateStatus', $transaction) }}">
                        @csrf
                        @method('PATCH')

                        <div class="mb-3">
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Status Baru</label>
                            <select name="status" id="statusSelect"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm"
                                    onchange="toggleCancelReason(this.value)">
                                @if($transaction->status === 'pending')
                                    <option value="confirmed">Konfirmasi Pesanan</option>
                                    <option value="cancelled">Batalkan</option>
                                @elseif($transaction->status === 'confirmed')
                                    <option value="in_progress">Sedang Diproses</option>
                                    <option value="cancelled">Batalkan</option>
                                @elseif($transaction->status === 'in_progress')
                                    <option value="completed">Selesai</option>
                                    <option value="cancelled">Batalkan</option>
                                @endif
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Catatan (opsional)</label>
                            <textarea name="seller_notes" rows="2"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm resize-none"
                                      placeholder="Tambahkan catatan untuk pembeli...">{{ $transaction->seller_notes }}</textarea>
                        </div>

                        <div id="cancelReason" class="mb-3 hidden">
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                Alasan Pembatalan <span class="text-red-500">*</span>
                            </label>
                            <textarea name="cancellation_reason" rows="2"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm resize-none"
                                      placeholder="Jelaskan alasan pembatalan..."></textarea>
                        </div>

                        <button type="submit"
                                class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2.5 rounded-lg font-medium text-sm transition-colors">
                            Update Status
                        </button>
                    </form>
                </div>
                @endif

                <!-- Waktu -->
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h2 class="font-semibold text-gray-900 mb-4">Timeline</h2>
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Pesanan Dibuat</span>
                            <span class="font-medium">{{ $transaction->created_at->format('d M Y, H:i') }}</span>
                        </div>
                        @if($transaction->completed_at)
                        <div class="flex justify-between">
                            <span class="text-gray-500">Selesai</span>
                            <span class="font-medium">{{ $transaction->completed_at->format('d M Y, H:i') }}</span>
                        </div>
                        @endif
                        @if($transaction->cancelled_at)
                        <div class="flex justify-between">
                            <span class="text-gray-500">Dibatalkan</span>
                            <span class="font-medium text-red-600">{{ $transaction->cancelled_at->format('d M Y, H:i') }}</span>
                        </div>
                        @endif
                        @if($transaction->cancellation_reason)
                        <div class="border-t border-gray-100 pt-3">
                            <p class="text-gray-500">Alasan Pembatalan</p>
                            <p class="font-medium text-red-600 mt-0.5">{{ $transaction->cancellation_reason }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleCancelReason(value) {
    const cancelDiv = document.getElementById('cancelReason');
    if (value === 'cancelled') {
        cancelDiv.classList.remove('hidden');
    } else {
        cancelDiv.classList.add('hidden');
    }
}
</script>
@endsection