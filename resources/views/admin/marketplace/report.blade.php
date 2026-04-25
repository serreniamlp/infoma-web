@extends('layouts.app')
@section('title', 'Laporan Revenue Marketplace — Admin EduLiving')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <div class="flex items-center gap-3 mb-6">
            <a href="{{ route('admin.analytics') }}" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Laporan Revenue Marketplace FJB</h1>
                <p class="text-gray-500 text-sm mt-1">Data transaksi dan pendapatan per periode</p>
            </div>
        </div>

        {{-- Filter Periode --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-6">
            <form method="GET" class="flex gap-3 flex-wrap items-end">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Periode</label>
                    <select name="period" onchange="this.form.submit()"
                            class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
                        <option value="this_week"  {{ $period === 'this_week'  ? 'selected' : '' }}>Minggu Ini</option>
                        <option value="this_month" {{ $period === 'this_month' ? 'selected' : '' }}>Bulan Ini</option>
                        <option value="last_month" {{ $period === 'last_month' ? 'selected' : '' }}>Bulan Lalu</option>
                        <option value="this_year"  {{ $period === 'this_year'  ? 'selected' : '' }}>Tahun Ini</option>
                        <option value="custom"     {{ $period === 'custom'     ? 'selected' : '' }}>Kustom</option>
                    </select>
                </div>
                @if($period === 'custom')
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Dari</label>
                        <input type="date" name="date_from" value="{{ request('date_from') }}"
                               class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Sampai</label>
                        <input type="date" name="date_to" value="{{ request('date_to') }}"
                               class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    </div>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700">Terapkan</button>
                @endif
                <div class="ml-auto text-sm text-gray-500 self-center">
                    <i class="fas fa-calendar-alt mr-1"></i>
                    {{ $dateFrom->format('d M Y') }} — {{ $dateTo->format('d M Y') }}
                </div>
            </form>
        </div>

        {{-- Summary Cards --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <p class="text-xs text-gray-500 mb-1">Total Transaksi</p>
                <p class="text-3xl font-bold text-gray-900">{{ number_format($summary['total_transactions']) }}</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <p class="text-xs text-gray-500 mb-1">Selesai</p>
                <p class="text-3xl font-bold text-green-600">{{ number_format($summary['completed']) }}</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <p class="text-xs text-gray-500 mb-1">Dibatalkan</p>
                <p class="text-3xl font-bold text-red-600">{{ number_format($summary['cancelled']) }}</p>
            </div>
            <div class="bg-green-50 rounded-xl border border-green-200 p-5">
                <p class="text-xs text-green-600 mb-1">Total Revenue</p>
                <p class="text-2xl font-bold text-green-700">Rp {{ number_format($summary['revenue'], 0, ',', '.') }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

            {{-- Revenue Harian --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h2 class="font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <i class="fas fa-chart-bar text-blue-500"></i>Revenue Harian
                </h2>
                @if($dailyRevenue->count() > 0)
                    @php $maxRevenue = $dailyRevenue->max('revenue') ?: 1; @endphp
                    <div class="space-y-2 max-h-72 overflow-y-auto">
                        @foreach($dailyRevenue as $day)
                            <div>
                                <div class="flex justify-between text-xs mb-1">
                                    <span class="text-gray-600">{{ \Carbon\Carbon::parse($day->date)->format('d M') }}</span>
                                    <span class="text-gray-500">{{ $day->count }} transaksi</span>
                                    <span class="font-semibold text-green-700">Rp {{ number_format($day->revenue, 0, ',', '.') }}</span>
                                </div>
                                <div class="h-2 rounded-full bg-gray-100">
                                    <div class="h-2 rounded-full bg-green-400"
                                         style="width: {{ $maxRevenue > 0 ? ($day->revenue / $maxRevenue * 100) : 0 }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-400 text-sm text-center py-8">Tidak ada data pada periode ini</p>
                @endif
            </div>

            {{-- Top Produk --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h2 class="font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <i class="fas fa-boxes text-indigo-500"></i>Top 10 Produk Terlaris
                </h2>
                <div class="space-y-3">
                    @forelse($topProducts as $i => $product)
                        <div class="flex items-center gap-3">
                            <span class="text-xs font-bold text-gray-400 w-5">{{ $i + 1 }}</span>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 truncate">{{ $product->name }}</p>
                                <p class="text-xs text-gray-500">{{ $product->order_count }} pesanan</p>
                            </div>
                            <span class="text-sm font-semibold text-green-700 whitespace-nowrap">
                                Rp {{ number_format($product->revenue ?? 0, 0, ',', '.') }}
                            </span>
                        </div>
                    @empty
                        <p class="text-gray-400 text-sm text-center py-4">Tidak ada data</p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Top Seller --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="font-semibold text-gray-900 mb-4 flex items-center gap-2">
                <i class="fas fa-trophy text-yellow-500"></i>Top 10 Seller berdasarkan Revenue
            </h2>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100">
                            <th class="text-left py-2 text-gray-500 font-medium">#</th>
                            <th class="text-left py-2 text-gray-500 font-medium">Seller</th>
                            <th class="text-right py-2 text-gray-500 font-medium">Pesanan</th>
                            <th class="text-right py-2 text-gray-500 font-medium">Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($topSellers as $i => $seller)
                            <tr class="border-b border-gray-50 hover:bg-gray-50">
                                <td class="py-2 text-gray-400 font-bold">{{ $i + 1 }}</td>
                                <td class="py-2">
                                    <p class="font-medium text-gray-900">{{ $seller->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $seller->email }}</p>
                                </td>
                                <td class="py-2 text-right text-gray-700">{{ $seller->order_count }}</td>
                                <td class="py-2 text-right font-semibold text-green-700">
                                    Rp {{ number_format($seller->revenue ?? 0, 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-8 text-center text-gray-400 text-sm">Belum ada data seller</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>
@endsection
