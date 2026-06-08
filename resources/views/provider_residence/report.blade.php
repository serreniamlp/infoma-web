@extends('layouts.app')
@section('title', 'Laporan Pendapatan — Provider Hunian')

@section('content')
<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- ── Header ──────────────────────────────────────────────────── --}}
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                    <i class="fas fa-chart-bar text-blue-500"></i>
                    Laporan Pendapatan
                </h1>
                <p class="text-gray-500 text-sm mt-1">
                    Periode: <strong>{{ $dateFrom->format('d M Y') }}</strong> —
                    <strong>{{ $dateTo->format('d M Y') }}</strong>
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('provider.residence.dashboard') }}"
                   class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1">
                    <i class="fas fa-arrow-left text-xs"></i> Dashboard
                </a>
            </div>
        </div>

        {{-- ── Filter Periode ───────────────────────────────────────────── --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-6">
            <form method="GET" class="flex gap-3 flex-wrap items-end">
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Periode</label>
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
                                class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700">
                            Terapkan
                        </button>
                    </div>
                </div>
            </form>
        </div>

        {{-- ── Summary Cards ────────────────────────────────────────────── --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            {{-- Total Pendapatan --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5 col-span-2 lg:col-span-1">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 bg-green-50 rounded-xl flex items-center justify-center">
                        <i class="fas fa-wallet text-green-500"></i>
                    </div>
                    <p class="text-sm font-medium text-gray-500">Total Pendapatan</p>
                </div>
                <p class="text-2xl font-bold text-gray-900">
                    Rp {{ number_format($summary['total_revenue'], 0, ',', '.') }}
                </p>
                @if(($summary['marketplace_revenue'] ?? 0) > 0)
                <div class="mt-3 pt-3 border-t border-gray-100 space-y-1 text-xs text-gray-500">
                    <div class="flex justify-between">
                        <span>Booking</span>
                        <span class="font-medium">Rp {{ number_format($summary['booking_revenue'], 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Marketplace</span>
                        <span class="font-medium">Rp {{ number_format($summary['marketplace_revenue'], 0, ',', '.') }}</span>
                    </div>
                </div>
                @endif
            </div>

            {{-- Total Booking --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 bg-blue-50 rounded-xl flex items-center justify-center">
                        <i class="fas fa-calendar-check text-blue-500"></i>
                    </div>
                    <p class="text-sm font-medium text-gray-500">Total Booking</p>
                </div>
                <p class="text-2xl font-bold text-gray-900">{{ $summary['total_bookings'] }}</p>
                <div class="mt-3 pt-3 border-t border-gray-100 grid grid-cols-3 gap-1 text-xs text-center">
                    <div class="text-green-600">
                        <div class="font-bold">{{ $summary['approved_bookings'] }}</div>
                        <div class="text-gray-400">Aktif</div>
                    </div>
                    <div class="text-blue-600">
                        <div class="font-bold">{{ $summary['completed_bookings'] }}</div>
                        <div class="text-gray-400">Selesai</div>
                    </div>
                    <div class="text-red-500">
                        <div class="font-bold">{{ $summary['rejected_bookings'] }}</div>
                        <div class="text-gray-400">Ditolak</div>
                    </div>
                </div>
            </div>

            {{-- Tingkat Konversi --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 bg-purple-50 rounded-xl flex items-center justify-center">
                        <i class="fas fa-percentage text-purple-500"></i>
                    </div>
                    <p class="text-sm font-medium text-gray-500">Tingkat Persetujuan</p>
                </div>
                @php
                    $approvalRate = $summary['total_bookings'] > 0
                        ? round(($summary['approved_bookings'] + $summary['completed_bookings']) / $summary['total_bookings'] * 100, 1)
                        : 0;
                @endphp
                <p class="text-2xl font-bold text-purple-600">{{ $approvalRate }}%</p>
                <div class="mt-3 w-full bg-gray-100 rounded-full h-2">
                    <div class="bg-purple-500 h-2 rounded-full" style="width: {{ $approvalRate }}%"></div>
                </div>
                <p class="text-xs text-gray-400 mt-1">{{ $summary['approved_bookings'] + $summary['completed_bookings'] }} dari {{ $summary['total_bookings'] }} booking</p>
            </div>

            {{-- Rata-rata Nilai --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 bg-orange-50 rounded-xl flex items-center justify-center">
                        <i class="fas fa-chart-line text-orange-500"></i>
                    </div>
                    <p class="text-sm font-medium text-gray-500">Rata-rata Booking</p>
                </div>
                @php
                    $completedCount = $summary['completed_bookings'];
                    $avgBooking = $completedCount > 0 ? $summary['booking_revenue'] / $completedCount : 0;
                @endphp
                <p class="text-2xl font-bold text-orange-600">
                    Rp {{ number_format($avgBooking, 0, ',', '.') }}
                </p>
                <p class="text-xs text-gray-400 mt-2">per transaksi selesai</p>
            </div>
        </div>

        {{-- ── Grafik Pendapatan Harian ─────────────────────────────────── --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <div>
                    <h3 class="font-semibold text-gray-900">Tren Pendapatan</h3>
                    <p class="text-xs text-gray-400 mt-0.5">Pendapatan dari booking yang selesai per hari</p>
                </div>
                <span class="text-xs text-gray-400 bg-gray-50 px-3 py-1 rounded-full border border-gray-200">
                    {{ $dateFrom->format('d M') }} — {{ $dateTo->format('d M Y') }}
                </span>
            </div>
            <div class="p-6">
                @if($dailyRevenue->count() > 0)
                    <canvas id="revenueChart" height="100"></canvas>
                @else
                    <div class="flex flex-col items-center justify-center py-16 text-gray-400">
                        <i class="fas fa-chart-area text-4xl mb-3"></i>
                        <p class="text-sm">Belum ada data pendapatan pada periode ini</p>
                    </div>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">

            {{-- ── Grafik Status Booking (Donut) ────────────────────────── --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="font-semibold text-gray-900">Komposisi Booking</h3>
                    <p class="text-xs text-gray-400 mt-0.5">Distribusi status booking periode ini</p>
                </div>
                <div class="p-6">
                    @if($summary['total_bookings'] > 0)
                        <canvas id="statusChart" height="200"></canvas>
                        <div class="mt-4 space-y-2">
                            @php
                                $statusData = [
                                    ['label' => 'Selesai',  'value' => $summary['completed_bookings'], 'color' => 'bg-green-500'],
                                    ['label' => 'Aktif',    'value' => $summary['approved_bookings'],  'color' => 'bg-blue-500'],
                                    ['label' => 'Pending',  'value' => $summary['pending_bookings'],   'color' => 'bg-yellow-400'],
                                    ['label' => 'Ditolak',  'value' => $summary['rejected_bookings'],  'color' => 'bg-red-400'],
                                    ['label' => 'Dibatalkan','value'=> $summary['cancelled_bookings'] ?? 0, 'color' => 'bg-gray-400'],
                                ];
                            @endphp
                            @foreach($statusData as $s)
                                @if($s['value'] > 0)
                                <div class="flex items-center justify-between text-xs">
                                    <div class="flex items-center gap-2">
                                        <div class="w-3 h-3 rounded-full {{ $s['color'] }}"></div>
                                        <span class="text-gray-600">{{ $s['label'] }}</span>
                                    </div>
                                    <span class="font-semibold text-gray-900">{{ $s['value'] }}</span>
                                </div>
                                @endif
                            @endforeach
                        </div>
                    @else
                        <div class="flex flex-col items-center justify-center py-10 text-gray-400">
                            <i class="fas fa-chart-pie text-3xl mb-2"></i>
                            <p class="text-xs">Belum ada data</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- ── Revenue per Hunian ───────────────────────────────────── --}}
            <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="font-semibold text-gray-900">Performa per Hunian</h3>
                    <p class="text-xs text-gray-400 mt-0.5">Revenue dan jumlah booking per listing</p>
                </div>
                <div class="divide-y divide-gray-50">
                    @forelse($revenuePerItem as $i => $item)
                    @php
                        $maxRevenue = $revenuePerItem->max('revenue') ?: 1;
                        $barWidth   = $item->revenue > 0 ? round(($item->revenue / $maxRevenue) * 100) : 0;
                    @endphp
                    <div class="px-6 py-4">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-3 min-w-0">
                                <span class="text-sm font-bold text-gray-300 w-5 flex-shrink-0">{{ $i + 1 }}</span>
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold text-gray-900 truncate">{{ $item->name }}</p>
                                    <p class="text-xs text-gray-400">{{ $item->booking_count }} booking</p>
                                </div>
                            </div>
                            <div class="text-right ml-4 flex-shrink-0">
                                <p class="text-sm font-bold text-green-700">
                                    Rp {{ number_format($item->revenue, 0, ',', '.') }}
                                </p>
                            </div>
                        </div>
                        {{-- Progress bar --}}
                        <div class="w-full bg-gray-100 rounded-full h-1.5">
                            <div class="bg-green-500 h-1.5 rounded-full transition-all duration-500"
                                 style="width: {{ $barWidth }}%"></div>
                        </div>
                    </div>
                    @empty
                    <div class="px-6 py-12 text-center text-gray-400">
                        <i class="fas fa-building text-3xl mb-2"></i>
                        <p class="text-sm">Belum ada data pendapatan per hunian</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- ── Tabel Detail Booking ─────────────────────────────────────── --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <div>
                    <h3 class="font-semibold text-gray-900">Riwayat Booking</h3>
                    <p class="text-xs text-gray-400 mt-0.5">Semua booking pada periode yang dipilih</p>
                </div>
                <span class="text-xs bg-blue-50 text-blue-600 border border-blue-100 px-3 py-1 rounded-full font-medium">
                    {{ $bookingDetails->total() }} booking
                </span>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Kode</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Pemesan</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Hunian</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tgl Booking</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Check-in</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Nominal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($bookingDetails as $booking)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-5 py-3">
                                <span class="text-xs font-mono text-gray-500">{{ $booking->booking_code }}</span>
                            </td>
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-2">
                                    <div class="w-7 h-7 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0">
                                        <span class="text-xs font-bold text-blue-600">
                                            {{ substr($booking->user->name ?? 'U', 0, 1) }}
                                        </span>
                                    </div>
                                    <span class="text-sm text-gray-900">{{ $booking->user->name ?? '—' }}</span>
                                </div>
                            </td>
                            <td class="px-5 py-3 text-sm text-gray-700 max-w-[150px] truncate">
                                {{ $booking->bookable->name ?? '—' }}
                            </td>
                            <td class="px-5 py-3 text-sm text-gray-500 whitespace-nowrap">
                                {{ $booking->created_at->format('d M Y') }}
                            </td>
                            <td class="px-5 py-3 text-sm text-gray-500 whitespace-nowrap">
                                {{ $booking->check_in_date?->format('d M Y') ?? '—' }}
                            </td>
                            <td class="px-5 py-3">
                                @php
                                    $badge = match($booking->status) {
                                        'pending'   => 'bg-yellow-100 text-yellow-700',
                                        'approved'  => 'bg-blue-100 text-blue-700',
                                        'completed' => 'bg-green-100 text-green-700',
                                        'rejected'  => 'bg-red-100 text-red-700',
                                        'cancelled' => 'bg-gray-100 text-gray-600',
                                        default     => 'bg-gray-100 text-gray-600',
                                    };
                                    $label = match($booking->status) {
                                        'pending'   => 'Pending',
                                        'approved'  => 'Aktif',
                                        'completed' => 'Selesai',
                                        'rejected'  => 'Ditolak',
                                        'cancelled' => 'Dibatalkan',
                                        default     => ucfirst($booking->status),
                                    };
                                @endphp
                                <span class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $badge }}">
                                    {{ $label }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-right">
                                @if($booking->transaction)
                                    <span class="text-sm font-semibold {{ $booking->transaction->payment_status === 'paid' ? 'text-green-700' : 'text-gray-400' }}">
                                        Rp {{ number_format($booking->transaction->final_amount, 0, ',', '.') }}
                                    </span>
                                    @if($booking->transaction->payment_status !== 'paid')
                                        <div class="text-xs text-gray-400">Belum bayar</div>
                                    @endif
                                @else
                                    <span class="text-gray-300 text-sm">—</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-5 py-12 text-center">
                                <div class="flex flex-col items-center text-gray-400">
                                    <i class="fas fa-calendar-times text-3xl mb-2"></i>
                                    <p class="text-sm">Tidak ada booking pada periode ini</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($bookingDetails->hasPages())
            <div class="px-6 py-4 border-t border-gray-100 bg-gray-50">
                {{ $bookingDetails->links() }}
            </div>
            @endif
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

// ── Grafik Tren Pendapatan (Bar Chart) ───────────────────────────────────
@if($dailyRevenue->count() > 0)
const revenueCtx = document.getElementById('revenueChart').getContext('2d');
new Chart(revenueCtx, {
    type: 'bar',
    data: {
        labels: @json($dailyRevenue->pluck('date')->map(fn($d) => \Carbon\Carbon::parse($d)->format('d M'))),
        datasets: [{
            label: 'Pendapatan (Rp)',
            data: @json($dailyRevenue->pluck('revenue')),
            backgroundColor: 'rgba(59, 130, 246, 0.15)',
            borderColor: 'rgba(59, 130, 246, 0.8)',
            borderWidth: 2,
            borderRadius: 6,
            borderSkipped: false,
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false },
            tooltip: {
                callbacks: {
                    label: ctx => 'Rp ' + ctx.parsed.y.toLocaleString('id-ID'),
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: val => 'Rp ' + (val / 1000000).toFixed(1) + 'jt',
                    font: { size: 11 },
                },
                grid: { color: 'rgba(0,0,0,0.05)' },
            },
            x: {
                ticks: { font: { size: 11 } },
                grid: { display: false },
            }
        }
    }
});
@endif

// ── Grafik Status Booking (Donut) ─────────────────────────────────────────
@if($summary['total_bookings'] > 0)
const statusCtx = document.getElementById('statusChart').getContext('2d');
new Chart(statusCtx, {
    type: 'doughnut',
    data: {
        labels: ['Selesai', 'Aktif', 'Pending', 'Ditolak', 'Dibatalkan'],
        datasets: [{
            data: [
                {{ $summary['completed_bookings'] }},
                {{ $summary['approved_bookings'] }},
                {{ $summary['pending_bookings'] }},
                {{ $summary['rejected_bookings'] }},
                {{ $summary['cancelled_bookings'] ?? 0 }},
            ],
            backgroundColor: [
                'rgba(34, 197, 94, 0.8)',
                'rgba(59, 130, 246, 0.8)',
                'rgba(234, 179, 8, 0.8)',
                'rgba(239, 68, 68, 0.8)',
                'rgba(156, 163, 175, 0.8)',
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
                    label: ctx => ctx.label + ': ' + ctx.parsed + ' booking',
                }
            }
        }
    }
});
@endif
</script>
@endpush