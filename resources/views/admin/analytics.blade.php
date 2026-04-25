@extends('layouts.app')
@section('title', 'Laporan & Analytics — Admin EduLiving')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Laporan & Analytics</h1>
                <p class="text-gray-500 text-sm mt-1">Data pertumbuhan platform EduLiving</p>
            </div>
            <a href="{{ route('admin.marketplace.report') }}"
               class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition-colors">
                <i class="fas fa-store mr-2"></i>Laporan Revenue FJB
            </a>
        </div>

        {{-- Pertumbuhan User 30 Hari --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
            <h2 class="font-semibold text-gray-900 mb-4 flex items-center gap-2">
                <i class="fas fa-users text-blue-500"></i>Pertumbuhan Pengguna — 30 Hari Terakhir
            </h2>
            @if($userGrowth->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-100">
                                <th class="text-left py-2 text-gray-500 font-medium">Tanggal</th>
                                <th class="text-right py-2 text-gray-500 font-medium">Pengguna Baru</th>
                                <th class="py-2 pl-4 w-48"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $maxGrowth = $userGrowth->max('count'); @endphp
                            @foreach($userGrowth as $day)
                                <tr class="border-b border-gray-50 hover:bg-gray-50">
                                    <td class="py-2 text-gray-700">{{ \Carbon\Carbon::parse($day->date)->format('d M Y') }}</td>
                                    <td class="py-2 text-right font-semibold text-gray-900">{{ $day->count }}</td>
                                    <td class="py-2 pl-4">
                                        <div class="h-2 rounded-full bg-blue-100">
                                            <div class="h-2 rounded-full bg-blue-500"
                                                 style="width: {{ $maxGrowth > 0 ? ($day->count / $maxGrowth * 100) : 0 }}%"></div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-gray-400 text-sm text-center py-6">Belum ada data pendaftaran dalam 30 hari terakhir</p>
            @endif
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

            {{-- Distribusi Status Booking --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h2 class="font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <i class="fas fa-bookmark text-indigo-500"></i>Distribusi Status Booking
                </h2>
                @php $totalBookingCount = $bookingStatus->sum('count'); @endphp
                <div class="space-y-3">
                    @foreach($bookingStatus as $bs)
                        @php
                            $pct = $totalBookingCount > 0 ? round($bs->count / $totalBookingCount * 100) : 0;
                            $color = match($bs->status) {
                                'pending'   => ['bar' => 'bg-orange-400', 'text' => 'text-orange-700', 'bg' => 'bg-orange-50'],
                                'approved'  => ['bar' => 'bg-blue-400',   'text' => 'text-blue-700',   'bg' => 'bg-blue-50'],
                                'completed' => ['bar' => 'bg-green-400',  'text' => 'text-green-700',  'bg' => 'bg-green-50'],
                                'rejected'  => ['bar' => 'bg-red-400',    'text' => 'text-red-700',    'bg' => 'bg-red-50'],
                                default     => ['bar' => 'bg-gray-400',   'text' => 'text-gray-700',   'bg' => 'bg-gray-50'],
                            };
                        @endphp
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="font-medium text-gray-700 capitalize">{{ $bs->status }}</span>
                                <span class="{{ $color['text'] }} font-semibold">{{ $bs->count }} ({{ $pct }}%)</span>
                            </div>
                            <div class="h-2.5 rounded-full bg-gray-100">
                                <div class="h-2.5 rounded-full {{ $color['bar'] }}" style="width: {{ $pct }}%"></div>
                            </div>
                        </div>
                    @endforeach
                    @if($bookingStatus->isEmpty())
                        <p class="text-gray-400 text-sm text-center py-4">Belum ada data booking</p>
                    @endif
                </div>
                <p class="text-xs text-gray-400 mt-3">Total: {{ number_format($totalBookingCount) }} booking</p>
            </div>

            {{-- Distribusi Status Transaksi --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h2 class="font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <i class="fas fa-shopping-cart text-green-500"></i>Distribusi Status Transaksi FJB
                </h2>
                @php $totalTxCount = $transactionStatus->sum('count'); @endphp
                <div class="space-y-3">
                    @foreach($transactionStatus as $ts)
                        @php
                            $pct = $totalTxCount > 0 ? round($ts->count / $totalTxCount * 100) : 0;
                            $color = match($ts->status) {
                                'pending'     => ['bar' => 'bg-orange-400', 'text' => 'text-orange-700'],
                                'confirmed'   => ['bar' => 'bg-blue-400',   'text' => 'text-blue-700'],
                                'in_progress' => ['bar' => 'bg-indigo-400', 'text' => 'text-indigo-700'],
                                'completed'   => ['bar' => 'bg-green-400',  'text' => 'text-green-700'],
                                'cancelled'   => ['bar' => 'bg-red-400',    'text' => 'text-red-700'],
                                default       => ['bar' => 'bg-gray-400',   'text' => 'text-gray-700'],
                            };
                            $label = match($ts->status) {
                                'pending'     => 'Pending',
                                'confirmed'   => 'Dikonfirmasi',
                                'in_progress' => 'Diproses',
                                'completed'   => 'Selesai',
                                'cancelled'   => 'Dibatalkan',
                                default       => $ts->status,
                            };
                        @endphp
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="font-medium text-gray-700">{{ $label }}</span>
                                <span class="{{ $color['text'] }} font-semibold">{{ $ts->count }} ({{ $pct }}%)</span>
                            </div>
                            <div class="h-2.5 rounded-full bg-gray-100">
                                <div class="h-2.5 rounded-full {{ $color['bar'] }}" style="width: {{ $pct }}%"></div>
                            </div>
                        </div>
                    @endforeach
                    @if($transactionStatus->isEmpty())
                        <p class="text-gray-400 text-sm text-center py-4">Belum ada data transaksi</p>
                    @endif
                </div>
                <p class="text-xs text-gray-400 mt-3">Total: {{ number_format($totalTxCount) }} transaksi</p>
            </div>
        </div>

        {{-- Booking & Transaksi per Bulan --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
            <h2 class="font-semibold text-gray-900 mb-4 flex items-center gap-2">
                <i class="fas fa-chart-line text-blue-500"></i>Aktivitas 12 Bulan Terakhir
            </h2>
            @php
                $months = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
                $bookingByMonth = $monthlyBookings->keyBy(function($item) {
                    return $item->year . '-' . str_pad($item->month, 2, '0', STR_PAD_LEFT);
                });
                $txByMonth = $monthlyTransactions->keyBy(function($item) {
                    return $item->year . '-' . str_pad($item->month, 2, '0', STR_PAD_LEFT);
                });

                $chartData = [];
                for ($i = 11; $i >= 0; $i--) {
                    $date = now()->subMonths($i);
                    $key = $date->format('Y-m');
                    $chartData[] = [
                        'label'        => $months[$date->month - 1] . ' ' . $date->format('Y'),
                        'bookings'     => $bookingByMonth[$key]->count ?? 0,
                        'transactions' => $txByMonth[$key]->count ?? 0,
                        'revenue'      => $txByMonth[$key]->revenue ?? 0,
                    ];
                }
                $maxVal = max(array_merge(array_column($chartData, 'bookings'), array_column($chartData, 'transactions'), [1]));
            @endphp
            <div class="overflow-x-auto">
                <table class="w-full text-xs">
                    <thead>
                        <tr class="border-b border-gray-100">
                            <th class="text-left py-2 text-gray-500 font-medium pr-4">Bulan</th>
                            <th class="text-right py-2 text-gray-500 font-medium pr-6">Booking</th>
                            <th class="text-right py-2 text-gray-500 font-medium pr-6">Transaksi FJB</th>
                            <th class="text-right py-2 text-gray-500 font-medium">Revenue FJB</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($chartData as $row)
                            <tr class="border-b border-gray-50 hover:bg-gray-50">
                                <td class="py-2 text-gray-700 pr-4 font-medium">{{ $row['label'] }}</td>
                                <td class="py-2 pr-6">
                                    <div class="flex items-center justify-end gap-2">
                                        <div class="w-24 h-2 rounded-full bg-gray-100">
                                            <div class="h-2 rounded-full bg-indigo-400"
                                                 style="width: {{ $maxVal > 0 ? ($row['bookings'] / $maxVal * 100) : 0 }}%"></div>
                                        </div>
                                        <span class="text-gray-900 font-semibold w-6 text-right">{{ $row['bookings'] }}</span>
                                    </div>
                                </td>
                                <td class="py-2 pr-6">
                                    <div class="flex items-center justify-end gap-2">
                                        <div class="w-24 h-2 rounded-full bg-gray-100">
                                            <div class="h-2 rounded-full bg-green-400"
                                                 style="width: {{ $maxVal > 0 ? ($row['transactions'] / $maxVal * 100) : 0 }}%"></div>
                                        </div>
                                        <span class="text-gray-900 font-semibold w-6 text-right">{{ $row['transactions'] }}</span>
                                    </div>
                                </td>
                                <td class="py-2 text-right text-green-700 font-medium">
                                    {{ $row['revenue'] > 0 ? 'Rp ' . number_format($row['revenue'], 0, ',', '.') : '—' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
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
                            <th class="text-right py-2 text-gray-500 font-medium">Produk</th>
                            <th class="text-right py-2 text-gray-500 font-medium">Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($topSellers as $i => $seller)
                            <tr class="border-b border-gray-50 hover:bg-gray-50">
                                <td class="py-2 text-gray-400 font-medium">{{ $i + 1 }}</td>
                                <td class="py-2">
                                    <div class="flex items-center gap-2">
                                        <div class="h-7 w-7 rounded-full bg-yellow-100 flex items-center justify-center">
                                            <span class="text-yellow-700 text-xs font-semibold">{{ substr($seller->name, 0, 1) }}</span>
                                        </div>
                                        <div>
                                            <p class="font-medium text-gray-900">{{ $seller->name }}</p>
                                            <p class="text-xs text-gray-500">{{ $seller->email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-2 text-right text-gray-700">{{ $seller->product_count }}</td>
                                <td class="py-2 text-right font-semibold text-green-700">
                                    Rp {{ number_format($seller->total_revenue ?? 0, 0, ',', '.') }}
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
