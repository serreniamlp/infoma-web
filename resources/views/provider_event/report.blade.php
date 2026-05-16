@extends('layouts.app')
@section('title', 'Laporan — Provider Event')

@section('content')
<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Header --}}
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Laporan Pendapatan</h1>
                <p class="text-gray-500 text-sm mt-1">Rekap booking dan revenue event kamu</p>
            </div>
            <a href="{{ route('provider.event.dashboard') }}"
               class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1">
                <i class="fas fa-arrow-left text-xs"></i> Dashboard
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
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <p class="text-xs text-gray-500 mb-1">Total Pendapatan</p>
                <p class="text-2xl font-bold text-gray-900">Rp {{ number_format($summary['total_revenue'], 0, ',', '.') }}</p>
                @if($summary['marketplace_revenue'] > 0)
                    <div class="mt-2 pt-2 border-t border-gray-100 space-y-0.5 text-xs text-gray-500">
                        <div>Booking: Rp {{ number_format($summary['booking_revenue'], 0, ',', '.') }}</div>
                        <div>FJB: Rp {{ number_format($summary['marketplace_revenue'], 0, ',', '.') }}</div>
                    </div>
                @endif
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <p class="text-xs text-gray-500 mb-1">Total Booking</p>
                <p class="text-2xl font-bold text-gray-900">{{ $summary['total_bookings'] }}</p>
                <div class="mt-2 pt-2 border-t border-gray-100 space-y-0.5 text-xs text-gray-500">
                    <div class="text-green-600">✓ Disetujui: {{ $summary['approved_bookings'] }}</div>
                    <div class="text-red-500">✗ Ditolak: {{ $summary['rejected_bookings'] }}</div>
                    <div class="text-orange-500">⏳ Pending: {{ $summary['pending_bookings'] }}</div>
                </div>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <p class="text-xs text-gray-500 mb-1">Booking Selesai</p>
                <p class="text-2xl font-bold text-green-600">{{ $summary['completed_bookings'] }}</p>
            </div>
            @if($summary['marketplace_orders'] > 0)
                <div class="bg-yellow-50 rounded-xl border border-yellow-200 p-5">
                    <p class="text-xs text-yellow-600 mb-1">Pesanan FJB</p>
                    <p class="text-2xl font-bold text-yellow-700">{{ $summary['marketplace_orders'] }}</p>
                    <p class="text-xs text-yellow-600 mt-1">Revenue: Rp {{ number_format($summary['marketplace_revenue'], 0, ',', '.') }}</p>
                </div>
            @else
                <div class="bg-white rounded-xl border border-gray-200 p-5">
                    <p class="text-xs text-gray-500 mb-1">Tingkat Persetujuan</p>
                    <p class="text-2xl font-bold text-blue-600">
                        {{ $summary['total_bookings'] > 0 ? round($summary['approved_bookings'] / $summary['total_bookings'] * 100, 1) : 0 }}%
                    </p>
                </div>
            @endif
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">

            {{-- Revenue per Event --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="font-semibold text-gray-900">Revenue per Event</h3>
                </div>
                <div class="divide-y divide-gray-50">
                    @forelse($revenuePerItem as $i => $item)
                        <div class="px-6 py-3 flex items-center justify-between">
                            <div class="flex items-center gap-3 min-w-0">
                                <span class="text-xs font-bold text-gray-400 w-5 flex-shrink-0">{{ $i + 1 }}</span>
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-gray-900 truncate">{{ $item->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $item->booking_count }} booking</p>
                                </div>
                            </div>
                            <p class="text-sm font-semibold text-green-700 ml-2 flex-shrink-0">
                                Rp {{ number_format($item->revenue, 0, ',', '.') }}
                            </p>
                        </div>
                    @empty
                        <div class="px-6 py-8 text-center text-gray-400 text-sm">
                            Belum ada data
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Tabel Booking Detail --}}
            <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="font-semibold text-gray-900">Detail Booking</h3>
                    <span class="text-xs text-gray-400">{{ $bookingDetails->total() }} booking</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pemesan</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Hunian</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Nominal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @forelse($bookingDetails as $booking)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3">
                                        <p class="text-sm font-medium text-gray-900">{{ $booking->user->name ?? '—' }}</p>
                                        <p class="text-xs text-gray-400">{{ $booking->booking_code }}</p>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-700">{{ $booking->bookable->name ?? '—' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-500">{{ $booking->created_at->format('d M Y') }}</td>
                                    <td class="px-4 py-3">
                                        @php
                                            $sc = match($booking->status) {
                                                'pending'   => 'bg-orange-100 text-orange-700',
                                                'approved'  => 'bg-blue-100 text-blue-700',
                                                'completed' => 'bg-green-100 text-green-700',
                                                'rejected'  => 'bg-red-100 text-red-700',
                                                default     => 'bg-gray-100 text-gray-600',
                                            };
                                        @endphp
                                        <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $sc }}">
                                            {{ ucfirst($booking->status) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-right text-sm font-medium text-gray-900">
                                        @if($booking->transaction)
                                            Rp {{ number_format($booking->transaction->final_amount, 0, ',', '.') }}
                                        @else
                                            <span class="text-gray-400">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-8 text-center text-gray-400 text-sm">
                                        Tidak ada booking pada periode ini
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($bookingDetails->hasPages())
                    <div class="px-6 py-4 border-t border-gray-100">
                        {{ $bookingDetails->links() }}
                    </div>
                @endif
            </div>
        </div>

    </div>
</div>

<script>
function toggleCustomDate(value) {
    const el = document.getElementById('custom-date');
    el.classList.toggle('hidden', value !== 'custom');
}
</script>
@endsection