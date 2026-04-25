@extends('layouts.app')
@section('title', 'Transaksi Marketplace — Admin EduLiving')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Transaksi Marketplace FJB</h1>
                <p class="text-gray-500 text-sm mt-1">Monitor semua transaksi jual-beli mahasiswa</p>
            </div>
            <a href="{{ route('admin.marketplace.report') }}"
               class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition-colors">
                <i class="fas fa-chart-bar mr-2"></i>Lihat Laporan
            </a>
        </div>

        {{-- Stats --}}
        <div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <p class="text-2xl font-bold text-gray-900">{{ $stats['total'] }}</p>
                <p class="text-xs text-gray-500">Total Transaksi</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <p class="text-2xl font-bold text-orange-600">{{ $stats['pending'] }}</p>
                <p class="text-xs text-gray-500">Pending</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <p class="text-2xl font-bold text-green-600">{{ $stats['completed'] }}</p>
                <p class="text-xs text-gray-500">Selesai</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <p class="text-2xl font-bold text-red-600">{{ $stats['cancelled'] }}</p>
                <p class="text-xs text-gray-500">Dibatalkan</p>
            </div>
            <div class="bg-white rounded-xl border border-green-200 bg-green-50 p-4">
                <p class="text-xl font-bold text-green-700">Rp {{ number_format($stats['revenue'], 0, ',', '.') }}</p>
                <p class="text-xs text-green-600">Total Revenue</p>
            </div>
        </div>

        {{-- Filter --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6 p-4">
            <form method="GET" class="flex gap-3 flex-wrap">
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Cari kode transaksi, nama buyer, produk..."
                       class="flex-1 min-w-48 px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
                <select name="status" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    <option value="">Semua Status</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>Dikonfirmasi</option>
                    <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>Diproses</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Selesai</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                </select>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700">
                    <i class="fas fa-search mr-1"></i>Cari
                </button>
                @if(request()->hasAny(['search','status']))
                    <a href="{{ route('admin.marketplace.transactions') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm hover:bg-gray-200">Reset</a>
                @endif
            </form>
        </div>

        {{-- Tabel --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kode</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produk</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Buyer</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Seller</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($transactions as $tx)
                            @php
                                $statusConfig = [
                                    'pending'     => ['label' => 'Pending',     'class' => 'bg-orange-100 text-orange-700'],
                                    'confirmed'   => ['label' => 'Dikonfirmasi','class' => 'bg-blue-100 text-blue-700'],
                                    'in_progress' => ['label' => 'Diproses',    'class' => 'bg-indigo-100 text-indigo-700'],
                                    'completed'   => ['label' => 'Selesai',     'class' => 'bg-green-100 text-green-700'],
                                    'cancelled'   => ['label' => 'Dibatalkan',  'class' => 'bg-red-100 text-red-700'],
                                ];
                                $sc = $statusConfig[$tx->status] ?? ['label' => $tx->status, 'class' => 'bg-gray-100 text-gray-600'];
                            @endphp
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 text-sm font-mono text-gray-700">{{ $tx->transaction_code }}</td>
                                <td class="px-6 py-4">
                                    <p class="text-sm font-medium text-gray-900">{{ $tx->product->name ?? '—' }}</p>
                                    <p class="text-xs text-gray-500">Qty: {{ $tx->quantity }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-sm text-gray-900">{{ $tx->buyer->name ?? '—' }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-sm text-gray-900">{{ $tx->seller->name ?? '—' }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-sm font-medium text-gray-900">Rp {{ number_format($tx->total_amount, 0, ',', '.') }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $sc['class'] }}">
                                        {{ $sc['label'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    {{ $tx->created_at->format('d M Y') }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('admin.marketplace.transactions.show', $tx) }}"
                                       class="px-3 py-1.5 text-xs font-medium bg-blue-50 text-blue-700 rounded-lg hover:bg-blue-100">
                                        <i class="fas fa-eye mr-1"></i>Detail
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-12 text-center text-gray-400">
                                    <i class="fas fa-receipt text-4xl mb-3 block opacity-30"></i>
                                    Tidak ada transaksi ditemukan
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($transactions->hasPages())
                <div class="px-6 py-4 border-t border-gray-200">{{ $transactions->links() }}</div>
            @endif
        </div>

    </div>
</div>
@endsection
