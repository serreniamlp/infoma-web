@extends('layouts.app')
@section('title', 'Laporan Penjualan — EduLiving')

@section('content')
<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Header --}}
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Laporan Penjualan</h1>
                <p class="text-gray-500 text-sm mt-1">Rekap transaksi dan revenue toko kamu</p>
            </div>
            <a href="{{ route('user.marketplace.seller.home') }}"
               class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1">
                <i class="fas fa-arrow-left text-xs"></i> Dashboard Seller
            </a>
        </div>

        {{-- Filter Periode --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-6">
            <form method="GET" class="flex gap-3 flex-wrap items-end">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Periode</label>
                    <select name="period" onchange="toggleCustomDate(this.value); this.form.submit()"
                            class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
                        <option value="this_week"  {{ $period === 'this_week'  ? 'selected' : '' }}>Minggu Ini</option>
                        <option value="this_month" {{ $period === 'this_month' ? 'selected' : '' }}>Bulan Ini</option>
                        <option value="last_month" {{ $period === 'last_month' ? 'selected' : '' }}>Bulan Lalu</option>
                        <option value="this_year"  {{ $period === 'this_year'  ? 'selected' : '' }}>Tahun Ini</option>
                        <option value="custom"     {{ $period === 'custom'     ? 'selected' : '' }}>Kustom</option>
                    </select>
                </div>
                <div id="custom-date" class="{{ $period === 'custom' ? '' : 'hidden' }} flex gap-3">
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Dari</label>
                        <input type="date" name="date_from"
                               value="{{ $period === 'custom' ? request('date_from') : '' }}"
                               class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Sampai</label>
                        <input type="date" name="date_to"
                               value="{{ $period === 'custom' ? request('date_to') : '' }}"
                               class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    </div>
                    <div class="self-end">
                        <button type="submit"
                                class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700">
                            Terapkan
                        </button>
                    </div>
                </div>
                <div class="ml-auto self-end text-sm text-gray-500">
                    <i class="fas fa-calendar-alt mr-1"></i>
                    {{ $dateFrom->format('d M Y') }} — {{ $dateTo->format('d M Y') }}
                </div>
            </form>
        </div>

        {{-- Summary Cards --}}
        <div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
            <div class="bg-green-50 rounded-xl border border-green-200 p-5 lg:col-span-1">
                <p class="text-xs text-green-600 mb-1">Total Revenue</p>
                <p class="text-xl font-bold text-green-700">
                    Rp {{ number_format($summary['total_revenue'], 0, ',', '.') }}
                </p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <p class="text-xs text-gray-500 mb-1">Total Pesanan</p>
                <p class="text-2xl font-bold text-gray-900">{{ $summary['total_orders'] }}</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <p class="text-xs text-gray-500 mb-1">Selesai</p>
                <p class="text-2xl font-bold text-green-600">{{ $summary['completed_orders'] }}</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <p class="text-xs text-gray-500 mb-1">Pending</p>
                <p class="text-2xl font-bold text-orange-500">{{ $summary['pending_orders'] }}</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <p class="text-xs text-gray-500 mb-1">Dibatalkan</p>
                <p class="text-2xl font-bold text-red-500">{{ $summary['cancelled_orders'] }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">

            {{-- Revenue per Produk --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="font-semibold text-gray-900">Revenue per Produk</h3>
                </div>
                <div class="divide-y divide-gray-50">
                    @forelse($revenuePerProduct as $i => $product)
                        <div class="px-6 py-3 flex items-center justify-between">
                            <div class="flex items-center gap-3 min-w-0">
                                <span class="text-xs font-bold text-gray-400 w-5 flex-shrink-0">
                                    {{ $i + 1 }}
                                </span>
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-gray-900 truncate">
                                        {{ $product->name }}
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        {{ $product->order_count }} pesanan
                                    </p>
                                </div>
                            </div>
                            <p class="text-sm font-semibold text-green-700 ml-2 flex-shrink-0">
                                Rp {{ number_format($product->revenue ?? 0, 0, ',', '.') }}
                            </p>
                        </div>
                    @empty
                        <div class="px-6 py-8 text-center text-gray-400 text-sm">
                            <i class="fas fa-boxes text-2xl mb-2 block opacity-30"></i>
                            Belum ada produk
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Tabel Detail Transaksi --}}
            <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="font-semibold text-gray-900">Detail Transaksi</h3>
                    <span class="text-xs text-gray-400">{{ $transactions->total() }} transaksi</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pembeli</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Produk</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @forelse($transactions as $tx)
                                @php
                                    $sc = match($tx->status) {
                                        'pending'     => 'bg-orange-100 text-orange-700',
                                        'confirmed'   => 'bg-blue-100 text-blue-700',
                                        'in_progress' => 'bg-indigo-100 text-indigo-700',
                                        'completed'   => 'bg-green-100 text-green-700',
                                        'cancelled'   => 'bg-red-100 text-red-700',
                                        default       => 'bg-gray-100 text-gray-600',
                                    };
                                    $sl = match($tx->status) {
                                        'pending'     => 'Pending',
                                        'confirmed'   => 'Dikonfirmasi',
                                        'in_progress' => 'Diproses',
                                        'completed'   => 'Selesai',
                                        'cancelled'   => 'Dibatalkan',
                                        default       => $tx->status,
                                    };
                                @endphp
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3">
                                        <p class="text-sm font-medium text-gray-900">
                                            {{ $tx->buyer->name ?? '—' }}
                                        </p>
                                        <p class="text-xs text-gray-400 font-mono">
                                            {{ $tx->transaction_code }}
                                        </p>
                                    </td>
                                    <td class="px-4 py-3">
                                        <p class="text-sm text-gray-700">{{ $tx->product->name ?? '—' }}</p>
                                        <p class="text-xs text-gray-400">Qty: {{ $tx->quantity }}</p>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-500">
                                        {{ $tx->created_at->format('d M Y') }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $sc }}">
                                            {{ $sl }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-right text-sm font-semibold text-gray-900">
                                        Rp {{ number_format($tx->total_amount, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-8 text-center text-gray-400 text-sm">
                                        Tidak ada transaksi pada periode ini
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($transactions->hasPages())
                    <div class="px-6 py-4 border-t border-gray-100">
                        {{ $transactions->links() }}
                    </div>
                @endif
            </div>
        </div>

        {{-- Revenue Harian --}}
        @if($dailyRevenue->count() > 0)
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="font-semibold text-gray-900 mb-4">Revenue Harian</h3>
                @php $maxRevenue = $dailyRevenue->max('revenue') ?: 1; @endphp
                <div class="space-y-2 max-h-64 overflow-y-auto">
                    @foreach($dailyRevenue as $day)
                        <div>
                            <div class="flex justify-between text-xs mb-1">
                                <span class="text-gray-600 w-24">
                                    {{ \Carbon\Carbon::parse($day->date)->format('d M Y') }}
                                </span>
                                <span class="text-gray-500">{{ $day->count }} transaksi</span>
                                <span class="font-semibold text-green-700">
                                    Rp {{ number_format($day->revenue, 0, ',', '.') }}
                                </span>
                            </div>
                            <div class="h-2 rounded-full bg-gray-100">
                                <div class="h-2 rounded-full bg-green-400 transition-all"
                                     style="width: {{ ($day->revenue / $maxRevenue) * 100 }}%">
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

    </div>
</div>

<script>
function toggleCustomDate(value) {
    const el = document.getElementById('custom-date');
    el.classList.toggle('hidden', value !== 'custom');
}
</script>
@endsection