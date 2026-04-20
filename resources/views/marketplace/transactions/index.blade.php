@extends('layouts.app')

@section('title', 'Transaksi Marketplace')

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
                <li aria-current="page">
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Transaksi Saya</span>
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
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                            </svg>
                            Transaksi Marketplace
                        </h1>
                        <p class="text-blue-100 mt-2">Kelola dan pantau semua transaksi Anda</p>
                    </div>
                    <div class="hidden md:flex items-center space-x-4 text-white">
                        <div class="text-center">
                            <div class="text-2xl font-bold">{{ $transactions->total() }}</div>
                            <div class="text-sm text-blue-100">Total Transaksi</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if($transactions->count() > 0)
            <!-- Transactions List -->
            <div class="space-y-4">
                @foreach($transactions as $transaction)
                    <div class="bg-white rounded-xl shadow-lg hover:shadow-xl transition-shadow duration-300 overflow-hidden">
                        <div class="p-6">
                            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                                <!-- Product Info -->
                                <div class="flex items-start space-x-4 flex-grow">
                                    <div class="flex-shrink-0">
                                        <img src="{{ $transaction->product->main_image }}"
                                             class="w-20 h-20 rounded-lg object-cover shadow-sm border border-gray-200"
                                             alt="{{ $transaction->product->name }}">
                                    </div>
                                    <div class="flex-grow min-w-0">
                                        <h3 class="text-lg font-semibold text-gray-900 truncate">
                                            {{ $transaction->product->name }}
                                        </h3>
                                        <div class="flex items-center mt-1 text-sm text-gray-600">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                            </svg>
                                            @if($transaction->buyer_id === auth()->id())
                                                Dijual oleh: <span class="font-medium ml-1">{{ $transaction->seller->name }}</span>
                                            @else
                                                Dibeli oleh: <span class="font-medium ml-1">{{ $transaction->buyer->name }}</span>
                                            @endif
                                        </div>
                                        <div class="flex items-center mt-2 text-sm text-gray-500">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                            </svg>
                                            Qty: <span class="font-medium">{{ $transaction->quantity }}</span> ×
                                            Rp <span class="font-medium">{{ number_format($transaction->unit_price, 0, ',', '.') }}</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Transaction Details -->
                                <div class="flex flex-col sm:flex-row lg:flex-col gap-4 lg:items-end">
                                    <div class="text-center lg:text-right">
                                        <div class="text-2xl font-bold text-green-600 mb-2">
                                            Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}
                                        </div>
                                        <div class="flex flex-col sm:flex-row lg:flex-col gap-2">
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold
                                                @if($transaction->status == 'completed') bg-green-100 text-green-800
                                                @elseif($transaction->status == 'pending') bg-yellow-100 text-yellow-800
                                                @elseif($transaction->status == 'cancelled') bg-red-100 text-red-800
                                                @else bg-blue-100 text-blue-800 @endif">
                                                {{ $transaction->status_label }}
                                            </span>
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold
                                                @if($transaction->payment_status == 'paid') bg-green-100 text-green-800
                                                @else bg-orange-100 text-orange-800 @endif">
                                                {{ $transaction->payment_status_label }}
                                            </span>
                                        </div>
                                    </div>

                                    <div class="flex flex-col sm:flex-row lg:flex-col items-center gap-3">
                                        <div class="text-sm text-gray-500 flex items-center">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a2 2 0 012-2h4a2 2 0 012 2v4m-6 0V6a2 2 0 012-2h4a2 2 0 012 2v1m-6 0h6m-6 0l.4-2m5.6 2l-.4-2m0 0L18 8m0 0v.01m0-.01a2.97 2.97 0 000 .01v5.99c0 1.1-.9 2-2 2H8a2 2 0 01-2-2V8.01a2.97 2.97 0 000-.01z"></path>
                                            </svg>
                                            {{ $transaction->created_at->format('d M Y') }}
                                        </div>
                                        <a href="{{ route('marketplace.transactions.show', $transaction) }}"
                                           class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                            </svg>
                                            Detail
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- Transaction Code & Additional Info -->
                            <div class="mt-4 pt-4 border-t border-gray-200">
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                                    <div class="flex items-center text-sm text-gray-600">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"></path>
                                        </svg>
                                        <span class="font-mono text-xs bg-gray-100 px-2 py-1 rounded">{{ $transaction->transaction_code }}</span>
                                    </div>
                                    <div class="flex items-center space-x-4 text-sm text-gray-500">
                                        <div class="flex items-center">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            </svg>
                                            {{ $transaction->pickup_method_label }}
                                        </div>
                                        <div class="flex items-center">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                                            </svg>
                                            {{ $transaction->payment_method }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-8">
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <div class="flex items-center justify-center">
                        {{ $transactions->links() }}
                    </div>
                </div>
            </div>
        @else
            <!-- Empty State -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <div class="px-6 py-16">
                    <div class="text-center">
                        <div class="mx-auto flex items-center justify-center h-24 w-24 rounded-full bg-gray-100 mb-6">
                            <svg class="h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.5 5M7 13l2.5 5m6-5v6a2 2 0 01-2 2H9a2 2 0 01-2-2v-6m6 0V9a2 2 0 00-2-2H9a2 2 0 00-2 2v4.01"></path>
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900 mb-2">Belum ada transaksi</h3>
                        <p class="text-gray-600 mb-8 max-w-md mx-auto">
                            Anda belum memiliki transaksi apapun. Mulai jelajahi produk-produk menarik di marketplace kami.
                        </p>
                        <div class="flex flex-col sm:flex-row gap-4 justify-center">
                            <a href="{{ route('marketplace.index') }}"
                               class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-semibold rounded-lg hover:from-blue-700 hover:to-blue-800 focus:outline-none focus:ring-4 focus:ring-blue-300 transition-all duration-200">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l-1 12H6L5 9z"></path>
                                </svg>
                                Mulai Berbelanja
                            </a>
                            <a href="{{ route('marketplace.index') }}"
                               class="inline-flex items-center px-6 py-3 bg-white text-gray-700 font-semibold rounded-lg border border-gray-300 hover:bg-gray-50 focus:outline-none focus:ring-4 focus:ring-gray-300 transition-all duration-200">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                                Jelajahi Produk
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

<!-- Custom Tailwind CSS for pagination -->
<style>
/* Custom pagination styles */
.pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 0.25rem;
}

.pagination .page-link {
    display: flex;
    align-items: center;
    justify-content: center;
    min-width: 2.5rem;
    height: 2.5rem;
    padding: 0.5rem 0.75rem;
    font-size: 0.875rem;
    font-weight: 500;
    color: #374151;
    background-color: white;
    border: 1px solid #d1d5db;
    border-radius: 0.5rem;
    text-decoration: none;
    transition: all 0.2s;
}

.pagination .page-link:hover {
    background-color: #f3f4f6;
    border-color: #9ca3af;
    color: #1f2937;
}

.pagination .page-item.active .page-link {
    background-color: #2563eb;
    border-color: #2563eb;
    color: white;
}

.pagination .page-item.active .page-link:hover {
    background-color: #1d4ed8;
    border-color: #1d4ed8;
}

.pagination .page-item.disabled .page-link {
    color: #9ca3af;
    background-color: #f9fafb;
    border-color: #e5e7eb;
    cursor: not-allowed;
}

.pagination .page-item.disabled .page-link:hover {
    background-color: #f9fafb;
    border-color: #e5e7eb;
}

/* Animation for transaction cards */
@keyframes slideInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.transaction-card {
    animation: slideInUp 0.3s ease-out;
}

.transaction-card:nth-child(2) { animation-delay: 0.1s; }
.transaction-card:nth-child(3) { animation-delay: 0.2s; }
.transaction-card:nth-child(4) { animation-delay: 0.3s; }
.transaction-card:nth-child(5) { animation-delay: 0.4s; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add animation class to transaction cards
    const transactionCards = document.querySelectorAll('.bg-white.rounded-xl.shadow-lg');
    transactionCards.forEach(card => {
        card.classList.add('transaction-card');
    });

    // Add hover effects for better interactivity
    transactionCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px)';
        });

        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
});
</script>
@endsection
