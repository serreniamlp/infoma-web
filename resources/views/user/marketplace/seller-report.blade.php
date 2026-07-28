@extends('layouts.app')
@section('title', 'Laporan Penjualan — EduLiving')

@section('content')
<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- ── Kop Surat Resmi (Tampil Saat Cetak PDF / e-Statement) ────────── --}}
        <div class="hidden print:block mb-8 pb-4 border-b-2 border-orange-500">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-orange-500 text-white rounded-xl flex items-center justify-center font-black text-xl">
                        EL
                    </div>
                    <div>
                        <h1 class="text-2xl font-black text-gray-900 tracking-wider">EDULIVING INDONESIA</h1>
                        <p class="text-xs text-gray-500 uppercase tracking-widest font-semibold">e-Statement Penjualan Marketplace FJB</p>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-xs font-bold text-gray-800">TANGGAL CETAK</p>
                    <p class="text-xs font-mono text-gray-600">{{ now()->translatedFormat('d F Y H:i') }} WIB</p>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-gray-100 flex justify-between text-xs text-gray-600">
                <div><strong>NAMA PENJUAL:</strong> {{ auth()->user()->name }} ({{ auth()->user()->email }})</div>
                <div><strong>PERIODE LAPORAN:</strong> {{ $dateFrom->translatedFormat('d F Y') }} — {{ $dateTo->translatedFormat('d F Y') }}</div>
            </div>
        </div>

        {{-- ── Header ──────────────────────────────────────────────────── --}}
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                    <i class="fas fa-chart-bar text-orange-500 print:hidden"></i>
                    Laporan Penjualan Marketplace
                </h1>
                <p class="text-gray-500 text-sm mt-1">
                    Periode Laporan: <strong>{{ $dateFrom->format('d M Y') }}</strong> —
                    <strong>{{ $dateTo->format('d M Y') }}</strong>
                </p>
            </div>
            <div class="flex items-center gap-2 print:hidden">
                <button onclick="window.print()" class="px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white text-xs font-semibold rounded-lg shadow-sm transition-colors flex items-center gap-1.5">
                    <i class="fas fa-print"></i> Cetak PDF / e-Statement
                </button>
                <a href="{{ route('user.marketplace.seller.home') }}"
                   class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold rounded-lg transition-colors flex items-center gap-1">
                    <i class="fas fa-arrow-left text-xs"></i> Dashboard Seller
                </a>
            </div>
        </div>

        {{-- ── Filter Periode ───────────────────────────────────────────── --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-6">
            <form method="GET" class="flex gap-3 flex-wrap items-end">
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Periode</label>
                    <select name="period" onchange="toggleCustomDate(this.value); this.form.submit()"
                            class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-orange-500">
                        <option value="this_week"  {{ $period === 'this_week'  ? 'selected' : '' }}>Minggu Ini</option>
                        <option value="this_month" {{ $period === 'this_month' ? 'selected' : '' }}>Bulan Ini</option>
                        <option value="last_month" {{ $period === 'last_month' ? 'selected' : '' }}>Bulan Lalu</option>
                        <option value="this_year"  {{ $period === 'this_year'  ? 'selected' : '' }}>Tahun Ini</option>
                        <option value="custom"     {{ $period === 'custom'     ? 'selected' : '' }}>Kustom</option>
                    </select>
                </div>
                <div id="custom-date" class="{{ $period === 'custom' ? '' : 'hidden' }} flex gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Dari</label>
                        <input type="date" name="date_from"
                               value="{{ $period === 'custom' ? request('date_from') : '' }}"
                               class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Sampai</label>
                        <input type="date" name="date_to"
                               value="{{ $period === 'custom' ? request('date_to') : '' }}"
                               class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    </div>
                    <div class="self-end">
                        <button type="submit"
                                class="px-4 py-2 bg-orange-500 text-white rounded-lg text-sm hover:bg-orange-600">
                            Terapkan
                        </button>
                    </div>
                </div>
            </form>
        </div>

        {{-- ── Summary Cards ────────────────────────────────────────────── --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

            {{-- Total Revenue --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5 col-span-2 lg:col-span-1">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 bg-green-50 rounded-xl flex items-center justify-center">
                        <i class="fas fa-wallet text-green-500"></i>
                    </div>
                    <p class="text-sm font-medium text-gray-500">Total Revenue</p>
                </div>
                <p class="text-2xl font-bold text-gray-900">
                    Rp {{ number_format($summary['total_revenue'], 0, ',', '.') }}
                </p>
                <p class="text-xs text-gray-400 mt-2">dari pesanan yang selesai</p>
            </div>

            {{-- Total Pesanan --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 bg-orange-50 rounded-xl flex items-center justify-center">
                        <i class="fas fa-shopping-bag text-orange-500"></i>
                    </div>
                    <p class="text-sm font-medium text-gray-500">Total Pesanan</p>
                </div>
                <p class="text-2xl font-bold text-gray-900">{{ $summary['total_orders'] }}</p>
                <div class="mt-3 pt-3 border-t border-gray-100 grid grid-cols-3 gap-1 text-xs text-center">
                    <div class="text-green-600">
                        <div class="font-bold">{{ $summary['completed_orders'] }}</div>
                        <div class="text-gray-400">Selesai</div>
                    </div>
                    <div class="text-orange-500">
                        <div class="font-bold">{{ $summary['pending_orders'] }}</div>
                        <div class="text-gray-400">Pending</div>
                    </div>
                    <div class="text-red-500">
                        <div class="font-bold">{{ $summary['cancelled_orders'] }}</div>
                        <div class="text-gray-400">Batal</div>
                    </div>
                </div>
            </div>

            {{-- Tingkat Penyelesaian --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 bg-blue-50 rounded-xl flex items-center justify-center">
                        <i class="fas fa-percentage text-blue-500"></i>
                    </div>
                    <p class="text-sm font-medium text-gray-500">Tingkat Penyelesaian</p>
                </div>
                @php
                    $completionRate = $summary['total_orders'] > 0
                        ? round($summary['completed_orders'] / $summary['total_orders'] * 100, 1)
                        : 0;
                @endphp
                <p class="text-2xl font-bold text-blue-600">{{ $completionRate }}%</p>
                <div class="mt-3 w-full bg-gray-100 rounded-full h-2">
                    <div class="bg-blue-500 h-2 rounded-full" style="width: {{ $completionRate }}%"></div>
                </div>
                <p class="text-xs text-gray-400 mt-1">{{ $summary['completed_orders'] }} dari {{ $summary['total_orders'] }} pesanan</p>
            </div>

            {{-- Rata-rata Nilai Pesanan --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 bg-purple-50 rounded-xl flex items-center justify-center">
                        <i class="fas fa-chart-line text-purple-500"></i>
                    </div>
                    <p class="text-sm font-medium text-gray-500">Rata-rata Pesanan</p>
                </div>
                @php
                    $avgOrder = $summary['completed_orders'] > 0
                        ? $summary['total_revenue'] / $summary['completed_orders']
                        : 0;
                @endphp
                <p class="text-2xl font-bold text-purple-600">
                    Rp {{ number_format($avgOrder, 0, ',', '.') }}
                </p>
                <p class="text-xs text-gray-400 mt-2">per transaksi selesai</p>
            </div>
        </div>

        {{-- ── Revenue per Produk (Full Width) ───────────────────────────────── --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-orange-50/30">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-orange-100 text-orange-600 rounded-lg flex items-center justify-center">
                        <i class="fas fa-boxes"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-900 text-base">Performa & Stok per Produk</h3>
                        <p class="text-xs text-gray-500">Omzet dan sisa stok barang pada periode ini</p>
                    </div>
                </div>
                <span class="text-xs bg-orange-100 text-orange-800 px-3 py-1 rounded-full font-semibold border border-orange-200">
                    {{ count($revenuePerProduct) }} Produk
                </span>
            </div>
            <div class="divide-y divide-gray-100">
                @forelse($revenuePerProduct as $i => $product)
                @php
                    $maxRevenue = $revenuePerProduct->max('revenue') ?: 1;
                    $barWidth   = $product->revenue > 0 ? round(($product->revenue / $maxRevenue) * 100) : 0;
                @endphp
                <div class="px-6 py-4 hover:bg-gray-50/60 transition-colors">
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center gap-3 min-w-0">
                            <span class="text-sm font-bold text-gray-400 w-5 flex-shrink-0">{{ $i + 1 }}</span>
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-gray-900 truncate">{{ $product->name }}</p>
                                <p class="text-xs text-gray-500 font-medium">
                                    {{ $product->order_count }} pesanan terjual • Sisa Stok: <span class="font-bold text-gray-800">{{ $product->stock_quantity }}</span>
                                </p>
                            </div>
                        </div>
                        <p class="text-sm font-extrabold text-emerald-700 ml-4 flex-shrink-0">
                            Rp {{ number_format($product->revenue ?? 0, 0, ',', '.') }}
                        </p>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-1.5">
                        <div class="bg-orange-400 h-1.5 rounded-full transition-all duration-500"
                             style="width: {{ $barWidth }}%"></div>
                    </div>
                </div>
                @empty
                <div class="px-6 py-12 text-center text-gray-400">
                    <i class="fas fa-boxes text-3xl mb-2"></i>
                    <p class="text-sm">Belum ada data penjualan per produk</p>
                </div>
                @endforelse
            </div>
        </div>

        {{-- ── Tabel Detail Transaksi ───────────────────────────────────── --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <div>
                    <h3 class="font-semibold text-gray-900">Riwayat Transaksi</h3>
                    <p class="text-xs text-gray-400 mt-0.5">Semua transaksi pada periode yang dipilih</p>
                </div>
                <span class="text-xs bg-orange-50 text-orange-600 border border-orange-100 px-3 py-1 rounded-full font-medium">
                    {{ $transactions->total() }} transaksi
                </span>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Kode</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Pembeli</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Produk</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Metode</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Tanggal</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
                            <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($transactions as $tx)
                        <tr class="hover:bg-gray-50 transition-colors {{ $tx->status === 'cancelled' ? 'print:hidden' : '' }}">
                            <td class="px-5 py-3">
                                <span class="text-xs font-mono text-gray-500">{{ $tx->transaction_code }}</span>
                            </td>
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-2">
                                    <div class="w-7 h-7 bg-orange-100 rounded-full flex items-center justify-center flex-shrink-0">
                                        <span class="text-xs font-bold text-orange-600">
                                            {{ substr($tx->buyer->name ?? 'U', 0, 1) }}
                                        </span>
                                    </div>
                                    <span class="text-sm text-gray-900">{{ $tx->buyer->name ?? '—' }}</span>
                                </div>
                            </td>
                            <td class="px-5 py-3">
                                <p class="text-sm text-gray-700">{{ $tx->product->name ?? '—' }}</p>
                                <p class="text-xs text-gray-400">Qty: {{ $tx->quantity }}</p>
                            </td>
                            <td class="px-5 py-3">
                                @php
                                    $methodLabel = match($tx->pickup_method ?? '') {
                                        'cod'      => ['label' => 'COD',           'class' => 'bg-green-100 text-green-700'],
                                        'delivery' => ['label' => 'Diantar',       'class' => 'bg-blue-100 text-blue-700'],
                                        'pickup'   => ['label' => 'Ambil Sendiri', 'class' => 'bg-orange-100 text-orange-700'],
                                        default    => ['label' => $tx->pickup_method ?? '—', 'class' => 'bg-gray-100 text-gray-600'],
                                    };
                                @endphp
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $methodLabel['class'] }}">
                                    {{ $methodLabel['label'] }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-sm text-gray-500 whitespace-nowrap">
                                {{ $tx->created_at->format('d M Y') }}
                            </td>
                            <td class="px-5 py-3">
                                @php
                                    $badge = match($tx->status) {
                                        'pending'     => 'bg-orange-100 text-orange-700',
                                        'confirmed'   => 'bg-blue-100 text-blue-700',
                                        'in_progress' => 'bg-indigo-100 text-indigo-700',
                                        'completed'   => 'bg-green-100 text-green-700',
                                        'cancelled'   => 'bg-red-100 text-red-700',
                                        default       => 'bg-gray-100 text-gray-600',
                                    };
                                    $label = match($tx->status) {
                                        'pending'     => 'Pending',
                                        'confirmed'   => 'Dikonfirmasi',
                                        'in_progress' => 'Diproses',
                                        'completed'   => 'Selesai',
                                        'cancelled'   => 'Dibatalkan',
                                        default       => ucfirst($tx->status),
                                    };
                                @endphp
                                <span class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $badge }}">
                                    {{ $label }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-right">
                                <span class="text-sm font-semibold {{ $tx->status === 'completed' ? 'text-green-700' : 'text-gray-700' }}">
                                    Rp {{ number_format($tx->total_amount, 0, ',', '.') }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-5 py-12 text-center">
                                <div class="flex flex-col items-center text-gray-400">
                                    <i class="fas fa-shopping-bag text-3xl mb-2"></i>
                                    <p class="text-sm">Tidak ada transaksi pada periode ini</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($transactions->hasPages())
            <div class="px-6 py-4 border-t border-gray-100 bg-gray-50">
                {{ $transactions->links() }}
            </div>
            @endif
        {{-- ── Pengesahan & Tanda Tangan Laporan (Tampil Saat Cetak PDF) ───── --}}
        <div class="hidden print:block mt-12 pt-6 border-t border-gray-300">
            <div class="grid grid-cols-2 gap-8 text-center text-xs text-gray-700">
                <div>
                    <p class="font-bold uppercase tracking-wider text-gray-500 mb-1">Dibuat Oleh (Penjual)</p>
                    <p class="font-bold text-gray-900 text-sm mt-12 underline">{{ auth()->user()->name }}</p>
                    <p class="text-gray-500">Seller Marketplace FJB</p>
                </div>
                <div>
                    <p class="font-bold uppercase tracking-wider text-gray-500 mb-1">Mengetahui & Disahkan</p>
                    <p class="font-bold text-gray-900 text-sm mt-12 underline">EduLiving Partner Admin</p>
                    <p class="text-gray-500">Platform Verifikator</p>
                </div>
            </div>
            <div class="mt-8 text-center text-[10px] text-gray-400 font-mono">
                *** Dokumen ini secara otomatis dihasilkan oleh Sistem EduLiving Indonesia dan sah secara elektronik ***
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
function toggleCustomDate(value) {
    document.getElementById('custom-date').classList.toggle('hidden', value !== 'custom');
}

// ── Grafik Tren Penjualan (Bar + Line) ───────────────────────────────────
@if($dailyRevenue->count() > 0)
const revenueCtx = document.getElementById('revenueChart').getContext('2d');
new Chart(revenueCtx, {
    type: 'bar',
    data: {
        labels: @json($dailyRevenue->pluck('date')->map(fn($d) => \Carbon\Carbon::parse($d)->format('d M'))),
        datasets: [
            {
                label: 'Revenue (Rp)',
                data: @json($dailyRevenue->pluck('revenue')),
                backgroundColor: 'rgba(249, 115, 22, 0.15)',
                borderColor: 'rgba(249, 115, 22, 0.8)',
                borderWidth: 2,
                borderRadius: 6,
                borderSkipped: false,
                yAxisID: 'y',
            },
            {
                label: 'Jumlah Transaksi',
                data: @json($dailyRevenue->pluck('count')),
                type: 'line',
                borderColor: 'rgba(59, 130, 246, 0.8)',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                borderWidth: 2,
                pointRadius: 3,
                tension: 0.3,
                yAxisID: 'y1',
                fill: false,
            }
        ]
    },
    options: {
        responsive: true,
        interaction: { mode: 'index', intersect: false },
        plugins: {
            legend: {
                display: true,
                position: 'top',
                labels: { font: { size: 11 }, boxWidth: 12 }
            },
            tooltip: {
                callbacks: {
                    label: ctx => {
                        if (ctx.datasetIndex === 0) return 'Revenue: Rp ' + ctx.parsed.y.toLocaleString('id-ID');
                        return 'Transaksi: ' + ctx.parsed.y;
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                position: 'left',
                ticks: {
                    callback: val => 'Rp ' + (val / 1000000).toFixed(1) + 'jt',
                    font: { size: 11 },
                },
                grid: { color: 'rgba(0,0,0,0.05)' },
            },
            y1: {
                beginAtZero: true,
                position: 'right',
                ticks: {
                    stepSize: 1,
                    font: { size: 11 },
                },
                grid: { display: false },
            },
            x: {
                ticks: { font: { size: 11 } },
                grid: { display: false },
            }
        }
    }
});
@endif

// ── Grafik Status Pesanan (Donut) ─────────────────────────────────────────
@if($summary['total_orders'] > 0)
const statusCtx = document.getElementById('statusChart').getContext('2d');
new Chart(statusCtx, {
    type: 'doughnut',
    data: {
        labels: ['Selesai', 'Pending', 'Dikonfirmasi', 'Dibatalkan'],
        datasets: [{
            data: [
                {{ $summary['completed_orders'] }},
                {{ $summary['pending_orders'] }},
                {{ $summary['confirmed_orders'] ?? 0 }},
                {{ $summary['cancelled_orders'] }},
            ],
            backgroundColor: [
                'rgba(34, 197, 94, 0.8)',
                'rgba(249, 115, 22, 0.8)',
                'rgba(59, 130, 246, 0.8)',
                'rgba(239, 68, 68, 0.8)',
            ],
            borderWidth: 0,
            hoverOffset: 4,
        }]
    },
    options: {
        responsive: true,
        cutout: '65%',
        plugins: {
            legend: { display: false },
            tooltip: {
                callbacks: {
                    label: ctx => ctx.label + ': ' + ctx.parsed + ' pesanan',
                }
            }
        }
    }
});
@endif
</script>
@endpush