@extends('layouts.app')

@section('title', 'Provider Dashboard - Infoma')

@section('content')
<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Script for Chart.js -->
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

        <!-- Performance Overview -->
        <div class="bg-white rounded-xl shadow-sm p-6 mb-8">
            <div class="mb-6">
                <div class="text-2xl font-bold text-gray-900 mb-1">
                    Selamat datang, {{ auth()->user()->name }}!
                </div>
                <div clas="text-gray-600 font-semibold">
                    Performa Bulan Ini
                </div>
            </div>
            <div class=" grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="text-center p-4 bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl">
                    <div class="text-4xl font-bold text-blue-600 mb-2">{{ $stats['monthly_bookings'] }}</div>
                    <div class="text-sm font-medium text-blue-700">Booking Bulan Ini</div>
                </div>
                <div class="text-center p-4 bg-gradient-to-br from-green-50 to-green-100 rounded-xl">
                    <div class="text-4xl font-bold text-green-600 mb-2">Rp
                        {{ number_format($stats['monthly_revenue']) }}</div>
                    <div class="text-sm font-medium text-green-700">Pendapatan Bulan Ini</div>
                </div>
                <div class="text-center p-4 bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl">
                    <div class="text-4xl font-bold text-purple-600 mb-2">{{ $stats['approval_rate'] }}%</div>
                    <div class="text-sm font-medium text-purple-700">Tingkat Persetujuan</div>
                </div>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-8 mb-8">
            <!-- Left Column - Charts -->
            <div class="xl:col-span-2 space-y-6">
                <!-- Revenue and Bookings Charts -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Pendapatan (6 Bulan)</h3>
                        <div class="relative" style="height: 250px;">
                            <canvas id="revenueChart"></canvas>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Booking (6 Bulan)</h3>
                        <div class="relative" style="height: 250px;">
                            <canvas id="bookingsChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Booking Status Chart -->
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Status Booking</h3>
                    <div class="flex justify-center">
                        <div style="width: 300px; height: 300px;">
                            <canvas id="statusChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column - Quick Actions -->
            <div class="space-y-6">
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6">Aksi Cepat</h3>
                    <div class="space-y-4">
                        <a href="{{ route('provider.residences.create') }}"
                            class="flex items-center p-4 bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl text-white hover:from-blue-600 hover:to-blue-700 transition-all duration-200 group">
                            <div class="bg-white/20 rounded-lg p-2 group-hover:bg-white/30 transition-colors">
                                <i class="fas fa-plus text-white text-lg"></i>
                            </div>
                            <div class="ml-4">
                                <h4 class="font-semibold">Tambah Residence</h4>
                                <p class="text-blue-100 text-sm">Buat residence baru</p>
                            </div>
                        </a>

                        <a href="{{ route('provider.activities.create') }}"
                            class="flex items-center p-4 bg-gradient-to-r from-green-500 to-green-600 rounded-xl text-white hover:from-green-600 hover:to-green-700 transition-all duration-200 group">
                            <div class="bg-white/20 rounded-lg p-2 group-hover:bg-white/30 transition-colors">
                                <i class="fas fa-plus text-white text-lg"></i>
                            </div>
                            <div class="ml-4">
                                <h4 class="font-semibold">Tambah Kegiatan</h4>
                                <p class="text-green-100 text-sm">Buat kegiatan baru</p>
                            </div>
                        </a>

                        <a href="{{ route('provider.bookings.index') }}"
                            class="flex items-center p-4 bg-gradient-to-r from-yellow-500 to-yellow-600 rounded-xl text-white hover:from-yellow-600 hover:to-yellow-700 transition-all duration-200 group">
                            <div class="bg-white/20 rounded-lg p-2 group-hover:bg-white/30 transition-colors">
                                <i class="fas fa-tasks text-white text-lg"></i>
                            </div>
                            <div class="ml-4">
                                <h4 class="font-semibold">Kelola Booking</h4>
                                <p class="text-yellow-100 text-sm">Approve/reject booking</p>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Recent Bookings -->
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="p-6 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900">Booking Terbaru</h3>
                        <a href="{{ route('provider.bookings.index') }}"
                            class="text-blue-600 hover:text-blue-700 text-sm font-medium flex items-center">
                            Lihat semua <i class="fas fa-arrow-right ml-1 text-xs"></i>
                        </a>
                    </div>
                </div>
                <div class="p-6">
                    @if($recentBookings->count() > 0)
                    <div class="space-y-4">
                        @foreach($recentBookings as $booking)
                        <div
                            class="flex items-center justify-between p-4 bg-gray-50 rounded-xl hover:bg-gray-100 transition-colors">
                            <div class="flex items-center">
                                <div class="bg-blue-100 rounded-lg p-2 mr-3">
                                    <i
                                        class="fas fa-{{ $booking->bookable_type === 'App\\Models\\Residence' ? 'building' : 'calendar-alt' }} text-blue-600"></i>
                                </div>
                                <div>
                                    <h4 class="font-medium text-gray-900">{{ $booking->bookable->name }}</h4>
                                    <p class="text-sm text-gray-600">{{ $booking->user->name }} •
                                        {{ $booking->created_at->format('d M Y') }}</p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-3">
                                <span class="px-3 py-1 rounded-full text-xs font-medium
                                        @if($booking->status === 'pending') bg-yellow-100 text-yellow-800
                                        @elseif($booking->status === 'approved') bg-green-100 text-green-800
                                        @elseif($booking->status === 'rejected') bg-red-100 text-red-800
                                        @else bg-gray-100 text-gray-800 @endif">
                                    {{ ucfirst($booking->status) }}
                                </span>
                                <a href="{{ route('provider.bookings.show', $booking) }}"
                                    class="text-blue-600 hover:text-blue-700 p-1">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="text-center py-12">
                        <i class="fas fa-bookmark text-gray-400 text-4xl mb-4"></i>
                        <p class="text-gray-600 font-medium">Belum ada booking</p>
                        <p class="text-gray-500 text-sm mt-1">Booking akan muncul di sini</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Recent Items -->
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="p-6 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900">Item Terbaru</h3>
                        <a href="{{ route('provider.residences.index') }}"
                            class="text-blue-600 hover:text-blue-700 text-sm font-medium flex items-center">
                            Lihat semua <i class="fas fa-arrow-right ml-1 text-xs"></i>
                        </a>
                    </div>
                </div>
                <div class="p-6">
                    @if($recentItems->count() > 0)
                    <div class="space-y-4">
                        @foreach($recentItems as $item)
                        <div
                            class="flex items-center justify-between p-4 bg-gray-50 rounded-xl hover:bg-gray-100 transition-colors">
                            <div class="flex items-center">
                                <div
                                    class="bg-{{ $item instanceof App\Models\Residence ? 'blue' : 'green' }}-100 rounded-lg p-2 mr-3">
                                    <i
                                        class="fas fa-{{ $item instanceof App\Models\Residence ? 'building' : 'calendar-alt' }} text-{{ $item instanceof App\Models\Residence ? 'blue' : 'green' }}-600"></i>
                                </div>
                                <div>
                                    <h4 class="font-medium text-gray-900">{{ $item->name }}</h4>
                                    <p class="text-sm text-gray-600">
                                        {{ $item instanceof App\Models\Residence ? 'Residence' : 'Kegiatan' }} •
                                        {{ $item->created_at->format('d M Y') }}
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-3">
                                <span
                                    class="px-3 py-1 rounded-full text-xs font-medium {{ $item->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $item->is_active ? 'Aktif' : 'Tidak Aktif' }}
                                </span>
                                <a href="{{ $item instanceof App\Models\Residence ? route('provider.residences.show', $item) : route('provider.activities.show', $item) }}"
                                    class="text-blue-600 hover:text-blue-700 p-1">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="text-center py-12">
                        <i class="fas fa-plus text-gray-400 text-4xl mb-4"></i>
                        <p class="text-gray-600 font-medium">Belum ada item</p>
                        <div class="mt-4 space-y-2">
                            <a href="{{ route('provider.residences.create') }}"
                                class="block text-blue-600 hover:text-blue-700 text-sm font-medium">
                                Buat residence pertama →
                            </a>
                            <a href="{{ route('provider.activities.create') }}"
                                class="block text-green-600 hover:text-green-700 text-sm font-medium">
                                Buat kegiatan pertama →
                            </a>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Charts Initialization -->
        <script>
        // Get chart data
        fetch('{{ route("provider.dashboard.charts") }}')
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    initializeCharts(data.data);
                }
            });

        function initializeCharts(data) {
            // Get labels for last 6 months
            const months = [];
            for (let i = 5; i >= 0; i--) {
                const date = new Date();
                date.setMonth(date.getMonth() - i);
                months.push(date.toLocaleString('id-ID', {
                    month: 'short'
                }));
            }

            // Common chart options
            const commonOptions = {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                }
            };

            // Revenue Chart
            new Chart(document.getElementById('revenueChart'), {
                type: 'line',
                data: {
                    labels: months,
                    datasets: [{
                        label: 'Pendapatan (Rp)',
                        data: data.revenue,
                        borderColor: '#3B82F6',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        tension: 0.4,
                        fill: true,
                        borderWidth: 3,
                        pointRadius: 4,
                        pointBackgroundColor: '#3B82F6'
                    }]
                },
                options: {
                    ...commonOptions,
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: '#f3f4f6'
                            },
                            ticks: {
                                callback: function(value) {
                                    return 'Rp ' + value.toLocaleString('id-ID');
                                },
                                font: {
                                    size: 11
                                }
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                font: {
                                    size: 11
                                }
                            }
                        }
                    }
                }
            });

            // Bookings Chart
            new Chart(document.getElementById('bookingsChart'), {
                type: 'bar',
                data: {
                    labels: months,
                    datasets: [{
                        label: 'Jumlah Booking',
                        data: data.bookings,
                        backgroundColor: '#10B981',
                        borderRadius: 6,
                        borderSkipped: false,
                    }]
                },
                options: {
                    ...commonOptions,
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: '#f3f4f6'
                            },
                            ticks: {
                                stepSize: 1,
                                font: {
                                    size: 11
                                }
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                font: {
                                    size: 11
                                }
                            }
                        }
                    }
                }
            });

            // Booking Status Chart
            new Chart(document.getElementById('statusChart'), {
                type: 'doughnut',
                data: {
                    labels: ['Disetujui', 'Menunggu', 'Ditolak'],
                    datasets: [{
                        data: data.status,
                        backgroundColor: [
                            '#10B981', // green for approved
                            '#F59E0B', // yellow for pending
                            '#EF4444' // red for rejected
                        ],
                        borderWidth: 3,
                        borderColor: '#ffffff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '60%',
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 20,
                                font: {
                                    size: 12
                                }
                            }
                        }
                    }
                }
            });
        }
        </script>
    </div>
</div>
@endsection