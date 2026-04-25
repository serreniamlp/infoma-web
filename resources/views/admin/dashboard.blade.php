@extends('layouts.app')
@section('title', 'Dashboard Admin — EduLiving')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        {{-- Header --}}
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Dashboard Admin</h1>
                <p class="text-gray-500 text-sm mt-1">Selamat datang, {{ auth()->user()->name }} — {{ now()->format('d F Y') }}</p>
            </div>
            <a href="{{ route('admin.analytics') }}"
               class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition-colors">
                <i class="fas fa-chart-bar mr-2"></i>Lihat Laporan Lengkap
            </a>
        </div>

        {{-- Alert: Pending Approvals --}}
        @if($pendingSellerApproval > 0 || $pendingProviderApproval > 0)
            <div class="mb-6 p-4 bg-orange-50 border border-orange-200 rounded-xl flex items-start gap-3">
                <i class="fas fa-bell text-orange-500 mt-0.5"></i>
                <div class="flex-1">
                    <p class="font-medium text-orange-800">Ada pengajuan yang perlu ditinjau</p>
                    <div class="flex gap-4 mt-1">
                        @if($pendingSellerApproval > 0)
                            <a href="{{ route('admin.users.index', ['role' => 'pending_seller']) }}"
                               class="text-sm text-orange-700 hover:underline">
                                {{ $pendingSellerApproval }} pengajuan seller FJB →
                            </a>
                        @endif
                        @if($pendingProviderApproval > 0)
                            <a href="{{ route('admin.users.index', ['role' => 'pending_provider']) }}"
                               class="text-sm text-orange-700 hover:underline">
                                {{ $pendingProviderApproval }} pengajuan provider →
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        {{-- Stat Cards: Pengguna --}}
        <div class="mb-2">
            <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3">Pengguna</h2>
        </div>
        <div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs text-gray-500">Total Pengguna</span>
                    <div class="h-8 w-8 rounded-lg bg-gray-100 flex items-center justify-center">
                        <i class="fas fa-users text-gray-600 text-sm"></i>
                    </div>
                </div>
                <p class="text-3xl font-bold text-gray-900">{{ number_format($totalUsers) }}</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs text-gray-500">Mahasiswa</span>
                    <div class="h-8 w-8 rounded-lg bg-green-100 flex items-center justify-center">
                        <i class="fas fa-user-graduate text-green-600 text-sm"></i>
                    </div>
                </div>
                <p class="text-3xl font-bold text-gray-900">{{ number_format($totalMahasiswa) }}</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs text-gray-500">Provider Hunian</span>
                    <div class="h-8 w-8 rounded-lg bg-blue-100 flex items-center justify-center">
                        <i class="fas fa-building text-blue-600 text-sm"></i>
                    </div>
                </div>
                <p class="text-3xl font-bold text-gray-900">{{ number_format($totalProviderResidence) }}</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs text-gray-500">Provider Acara</span>
                    <div class="h-8 w-8 rounded-lg bg-indigo-100 flex items-center justify-center">
                        <i class="fas fa-calendar-alt text-indigo-600 text-sm"></i>
                    </div>
                </div>
                <p class="text-3xl font-bold text-gray-900">{{ number_format($totalProviderEvent) }}</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs text-gray-500">Seller FJB</span>
                    <div class="h-8 w-8 rounded-lg bg-yellow-100 flex items-center justify-center">
                        <i class="fas fa-store text-yellow-600 text-sm"></i>
                    </div>
                </div>
                <p class="text-3xl font-bold text-gray-900">{{ number_format($totalSeller) }}</p>
            </div>
        </div>

        {{-- Stat Cards: Konten --}}
        <div class="mb-2">
            <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3">Konten</h2>
        </div>
        <div class="grid grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-sm font-medium text-gray-700">Hunian</span>
                    <a href="{{ route('admin.residences.index') }}" class="text-xs text-blue-600 hover:underline">Kelola →</a>
                </div>
                <div class="flex items-end gap-4">
                    <div>
                        <p class="text-3xl font-bold text-gray-900">{{ number_format($totalResidences) }}</p>
                        <p class="text-xs text-gray-500">Total listing</p>
                    </div>
                    <div class="pb-1">
                        <span class="text-sm text-green-600 font-medium">{{ $activeResidences }} aktif</span>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-sm font-medium text-gray-700">Acara</span>
                    <a href="{{ route('admin.activities.index') }}" class="text-xs text-blue-600 hover:underline">Kelola →</a>
                </div>
                <div class="flex items-end gap-4">
                    <div>
                        <p class="text-3xl font-bold text-gray-900">{{ number_format($totalActivities) }}</p>
                        <p class="text-xs text-gray-500">Total event</p>
                    </div>
                    <div class="pb-1">
                        <span class="text-sm text-green-600 font-medium">{{ $activeActivities }} aktif</span>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-sm font-medium text-gray-700">Produk Marketplace</span>
                    <a href="{{ route('admin.marketplace.products') }}" class="text-xs text-blue-600 hover:underline">Kelola →</a>
                </div>
                <div class="flex items-end gap-4">
                    <div>
                        <p class="text-3xl font-bold text-gray-900">{{ number_format($totalProducts) }}</p>
                        <p class="text-xs text-gray-500">Total produk</p>
                    </div>
                    <div class="pb-1">
                        <span class="text-sm text-green-600 font-medium">{{ $activeProducts }} aktif</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Stat Cards: Transaksi Bulan Ini --}}
        <div class="mb-2">
            <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3">Bulan Ini — {{ now()->format('F Y') }}</h2>
        </div>
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs text-gray-500">Booking Baru</span>
                    <i class="fas fa-bookmark text-blue-400"></i>
                </div>
                <p class="text-3xl font-bold text-gray-900">{{ $bookingsThisMonth }}</p>
                <p class="text-xs text-gray-400 mt-1">Total: {{ $totalBookings }}</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs text-gray-500">Booking Pending</span>
                    <i class="fas fa-clock text-orange-400"></i>
                </div>
                <p class="text-3xl font-bold text-orange-600">{{ $pendingBookings }}</p>
                <p class="text-xs text-gray-400 mt-1">Selesai: {{ $completedBookings }}</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs text-gray-500">Transaksi FJB</span>
                    <i class="fas fa-shopping-cart text-green-400"></i>
                </div>
                <p class="text-3xl font-bold text-gray-900">{{ $transactionsThisMonth }}</p>
                <p class="text-xs text-gray-400 mt-1">Total: {{ $totalTransactions }}</p>
            </div>
            <div class="bg-green-50 rounded-xl border border-green-200 p-5">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs text-green-600">Revenue FJB</span>
                    <i class="fas fa-coins text-green-500"></i>
                </div>
                <p class="text-2xl font-bold text-green-700">Rp {{ number_format($revenueThisMonth, 0, ',', '.') }}</p>
                <p class="text-xs text-green-500 mt-1">Total: Rp {{ number_format($marketplaceRevenue, 0, ',', '.') }}</p>
            </div>
        </div>

        {{-- Bottom section: Recent + Pending Approvals --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            {{-- Pending Approvals --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="font-semibold text-gray-900 flex items-center gap-2">
                        <i class="fas fa-user-clock text-orange-500"></i>Pengajuan Menunggu Review
                    </h3>
                    <a href="{{ route('admin.users.index', ['role' => 'pending_seller']) }}"
                       class="text-xs text-blue-600 hover:underline">Lihat semua →</a>
                </div>
                <div class="divide-y divide-gray-100">
                    @forelse($pendingApprovals as $pendingUser)
                        <div class="px-6 py-3 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="h-8 w-8 rounded-full bg-orange-100 flex items-center justify-center">
                                    <span class="text-orange-700 text-sm font-semibold">{{ substr($pendingUser->name, 0, 1) }}</span>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $pendingUser->name }}</p>
                                    <p class="text-xs text-gray-500">
                                        @if($pendingUser->seller_status === 'pending') Pengajuan Seller FJB
                                        @elseif($pendingUser->provider_status === 'pending') Pengajuan Provider
                                        @endif
                                    </p>
                                </div>
                            </div>
                            <a href="{{ route('admin.users.show', $pendingUser) }}"
                               class="text-xs px-3 py-1 bg-orange-50 text-orange-700 rounded-lg hover:bg-orange-100 transition-colors">
                                Review
                            </a>
                        </div>
                    @empty
                        <div class="px-6 py-8 text-center text-gray-400 text-sm">
                            <i class="fas fa-check-circle text-2xl mb-2 block text-green-400"></i>
                            Tidak ada pengajuan pending
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Recent Users --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="font-semibold text-gray-900 flex items-center gap-2">
                        <i class="fas fa-user-plus text-blue-500"></i>Pengguna Terbaru
                    </h3>
                    <a href="{{ route('admin.users.index') }}" class="text-xs text-blue-600 hover:underline">Lihat semua →</a>
                </div>
                <div class="divide-y divide-gray-100">
                    @foreach($recentUsers as $recentUser)
                        <div class="px-6 py-3 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="h-8 w-8 rounded-full bg-blue-600 flex items-center justify-center">
                                    <span class="text-white text-sm font-semibold">{{ substr($recentUser->name, 0, 1) }}</span>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $recentUser->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $recentUser->created_at->diffForHumans() }}</p>
                                </div>
                            </div>
                            <div class="flex gap-1">
                                @foreach($recentUser->roles->take(1) as $role)
                                    @php
                                        $rc = match($role->name) {
                                            'admin'              => 'bg-purple-100 text-purple-700',
                                            'provider_residence' => 'bg-blue-100 text-blue-700',
                                            'provider_event'     => 'bg-indigo-100 text-indigo-700',
                                            'user'               => 'bg-green-100 text-green-700',
                                            default              => 'bg-gray-100 text-gray-600',
                                        };
                                    @endphp
                                    <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $rc }}">
                                        {{ $role->display_name ?? $role->name }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Recent Bookings --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="font-semibold text-gray-900 flex items-center gap-2">
                        <i class="fas fa-bookmark text-indigo-500"></i>Booking Terbaru
                    </h3>
                </div>
                <div class="divide-y divide-gray-100">
                    @forelse($recentBookings as $booking)
                        <div class="px-6 py-3">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $booking->user->name ?? '—' }}</p>
                                    <p class="text-xs text-gray-500">{{ $booking->bookable->name ?? '—' }}</p>
                                </div>
                                <div class="text-right">
                                    @php
                                        $bc = match($booking->status) {
                                            'pending'   => 'bg-orange-100 text-orange-700',
                                            'approved'  => 'bg-blue-100 text-blue-700',
                                            'completed' => 'bg-green-100 text-green-700',
                                            'rejected'  => 'bg-red-100 text-red-700',
                                            default     => 'bg-gray-100 text-gray-600',
                                        };
                                    @endphp
                                    <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $bc }}">
                                        {{ ucfirst($booking->status) }}
                                    </span>
                                    <p class="text-xs text-gray-400 mt-0.5">{{ $booking->created_at->diffForHumans() }}</p>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="px-6 py-6 text-center text-gray-400 text-sm">Belum ada booking</div>
                    @endforelse
                </div>
            </div>

            {{-- Recent Transactions --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="font-semibold text-gray-900 flex items-center gap-2">
                        <i class="fas fa-shopping-cart text-green-500"></i>Transaksi FJB Terbaru
                    </h3>
                    <a href="{{ route('admin.marketplace.transactions') }}" class="text-xs text-blue-600 hover:underline">Lihat semua →</a>
                </div>
                <div class="divide-y divide-gray-100">
                    @forelse($recentTransactions as $tx)
                        <div class="px-6 py-3 flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $tx->product->name ?? '—' }}</p>
                                <p class="text-xs text-gray-500">{{ $tx->buyer->name ?? '—' }} → {{ $tx->product->seller->name ?? '—' }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-medium text-gray-900">Rp {{ number_format($tx->total_amount, 0, ',', '.') }}</p>
                                <p class="text-xs text-gray-400">{{ $tx->created_at->diffForHumans() }}</p>
                            </div>
                        </div>
                    @empty
                        <div class="px-6 py-6 text-center text-gray-400 text-sm">Belum ada transaksi</div>
                    @endforelse
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
