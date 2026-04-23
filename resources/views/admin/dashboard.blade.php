@extends('layouts.app')

@section('title', 'Admin Dashboard - Infoma')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Welcome Section -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Selamat datang, {{ auth()->user()->name }}!</h1>
                    <p class="text-gray-600 mt-1">Kelola sistem dan monitor aktivitas pengguna</p>
                </div>
                <div class="hidden md:block">
                    <div class="bg-red-100 rounded-full p-3">
                        <i class="fas fa-shield-alt text-red-600 text-2xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center">
                    <div class="bg-blue-100 rounded-lg p-3">
                        <i class="fas fa-users text-blue-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-2xl font-bold text-gray-900">{{ $stats['total_users'] }}</h3>
                        <p class="text-gray-600 text-sm">Total Pengguna</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center">
                    <div class="bg-green-100 rounded-lg p-3">
                        <i class="fas fa-building text-green-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-2xl font-bold text-gray-900">{{ $totalResidences }}</h3>
                        <p class="text-gray-600 text-sm">Total Residence</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center">
                    <div class="bg-yellow-100 rounded-lg p-3">
                        <i class="fas fa-calendar-alt text-yellow-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-2xl font-bold text-gray-900">{{ $totalActivities }}</h3>
                        <p class="text-gray-600 text-sm">Total Kegiatan</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center">
                    <div class="bg-purple-100 rounded-lg p-3">
                        <i class="fas fa-bookmark text-purple-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-2xl font-bold text-gray-900">{{ $totalBookings }}</h3>
                        <p class="text-gray-600 text-sm">Total Booking</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <a href="{{ route('admin.users.index') }}"
               class="bg-white rounded-lg shadow-sm p-6 hover:shadow-md transition-shadow duration-200 group">
                <div class="flex items-center">
                    <div class="bg-blue-100 rounded-lg p-3 group-hover:bg-blue-200 transition-colors">
                        <i class="fas fa-users text-blue-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-gray-900">Kelola Pengguna</h3>
                        <p class="text-gray-600 text-sm">Manajemen user</p>
                    </div>
                </div>
            </a>

            <a href="{{ route('admin.analytics') }}"
               class="bg-white rounded-lg shadow-sm p-6 hover:shadow-md transition-shadow duration-200 group">
                <div class="flex items-center">
                    <div class="bg-green-100 rounded-lg p-3 group-hover:bg-green-200 transition-colors">
                        <i class="fas fa-chart-bar text-green-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-gray-900">Analytics</h3>
                        <p class="text-gray-600 text-sm">Laporan & statistik</p>
                    </div>
                </div>
            </a>

            <a href="{{ route('provider.residence.residences.index') }}"
               class="bg-white rounded-lg shadow-sm p-6 hover:shadow-md transition-shadow duration-200 group">
                <div class="flex items-center">
                    <div class="bg-yellow-100 rounded-lg p-3 group-hover:bg-yellow-200 transition-colors">
                        <i class="fas fa-building text-yellow-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-gray-900">Residence</h3>
                        <p class="text-gray-600 text-sm">Kelola residence</p>
                    </div>
                </div>
            </a>

            <a href="{{ route('provider.event.activities.index') }}"
               class="bg-white rounded-lg shadow-sm p-6 hover:shadow-md transition-shadow duration-200 group">
                <div class="flex items-center">
                    <div class="bg-purple-100 rounded-lg p-3 group-hover:bg-purple-200 transition-colors">
                        <i class="fas fa-calendar-alt text-purple-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-gray-900">Kegiatan</h3>
                        <p class="text-gray-600 text-sm">Kelola kegiatan</p>
                    </div>
                </div>
            </a>
        </div>

        <!-- Recent Activity -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Recent Users -->
            <div class="bg-white rounded-lg shadow-sm">
                <div class="p-6 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-gray-900">Pengguna Terbaru</h2>
                        <a href="{{ route('admin.users.index') }}"
                           class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                            Lihat semua →
                        </a>
                    </div>
                </div>
                <div class="p-6">
                    @if($recentUsers->count() > 0)
                        <div class="space-y-4">
                            @foreach($recentUsers as $user)
                            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                                        <span class="text-sm font-medium text-blue-600">{{ substr($user->name, 0, 1) }}</span>
                                    </div>
                                    <div>
                                        <h4 class="font-medium text-gray-900">{{ $user->name }}</h4>
                                        <p class="text-sm text-gray-600">{{ $user->email }} • {{ $user->created_at->format('d M Y') }}</p>
                                    </div>
                                </div>
                                <span class="px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    {{ $user->roles->first()->name ?? 'User' }}
                                </span>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8">
                            <i class="fas fa-users text-gray-400 text-4xl mb-4"></i>
                            <p class="text-gray-600">Belum ada pengguna</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- System Stats -->
            <div class="bg-white rounded-lg shadow-sm">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Statistik Sistem</h2>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Pengguna Aktif (30 hari)</span>
                            <span class="font-medium">{{ $stats['active_users_30_days'] }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Booking Bulan Ini</span>
                            <span class="font-medium">{{ $stats['bookings_this_month'] }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Pendapatan Bulan Ini</span>
                            <span class="font-medium">Rp {{ number_format($stats['revenue_this_month']) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Tingkat Konversi</span>
                            <span class="font-medium">{{ $stats['conversion_rate'] }}%</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
