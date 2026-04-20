@extends('layouts.app')

@section('title', 'Booking Saya - Infoma')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Booking Saya</h1>
            <p class="text-gray-600 mt-2">Kelola semua booking residence dan kegiatan Anda</p>
        </div>

        <!-- Filter Tabs -->
        <div class="bg-white rounded-lg shadow-sm mb-6">
            <div class="border-b border-gray-200">
                <nav class="-mb-px flex space-x-8 px-6">
                    <a href="{{ route('user.bookings.index', ['status' => 'all']) }}"
                       class="py-4 px-1 border-b-2 font-medium text-sm {{ $status === 'all' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                        Semua ({{ auth()->user()->bookings()->count() }})
                    </a>
                    <a href="{{ route('user.bookings.index', ['status' => 'pending']) }}"
                       class="py-4 px-1 border-b-2 font-medium text-sm {{ $status === 'pending' ? 'border-yellow-500 text-yellow-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                        Pending ({{ auth()->user()->bookings()->where('status', 'pending')->count() }})
                    </a>
                    <a href="{{ route('user.bookings.index', ['status' => 'approved']) }}"
                       class="py-4 px-1 border-b-2 font-medium text-sm {{ $status === 'approved' ? 'border-green-500 text-green-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                        Disetujui ({{ auth()->user()->bookings()->where('status', 'approved')->count() }})
                    </a>
                    <a href="{{ route('user.bookings.index', ['status' => 'rejected']) }}"
                       class="py-4 px-1 border-b-2 font-medium text-sm {{ $status === 'rejected' ? 'border-red-500 text-red-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                        Ditolak ({{ auth()->user()->bookings()->where('status', 'rejected')->count() }})
                    </a>
                    <a href="{{ route('user.bookings.index', ['status' => 'completed']) }}"
                       class="py-4 px-1 border-b-2 font-medium text-sm {{ $status === 'completed' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                        Selesai ({{ auth()->user()->bookings()->where('status', 'completed')->count() }})
                    </a>
                    <a href="{{ route('user.bookings.index', ['status' => 'cancelled']) }}"
                       class="py-4 px-1 border-b-2 font-medium text-sm {{ $status === 'cancelled' ? 'border-gray-500 text-gray-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                        Dibatalkan ({{ auth()->user()->bookings()->where('status', 'cancelled')->count() }})
                    </a>
                </nav>
            </div>
        </div>

        <!-- Bookings List -->
        @if($bookings->count() > 0)
            <div class="space-y-6">
                @foreach($bookings as $booking)
                <div class="bg-white rounded-lg shadow-sm overflow-hidden hover:shadow-md transition-shadow duration-200">
                    <div class="p-6">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center mb-4">
                                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mr-4">
                                        <i class="fas fa-{{ $booking->bookable_type === 'App\\Models\\Residence' ? 'building' : 'calendar-alt' }} text-blue-600 text-xl"></i>
                                    </div>
                                    <div class="flex-1">
                                        <h3 class="text-lg font-semibold text-gray-900">{{ $booking->bookable->name }}</h3>
                                        <p class="text-sm text-gray-600">
                                            {{ $booking->bookable_type === 'App\\Models\\Residence' ? 'Residence' : 'Kegiatan' }} •
                                            Booking #{{ $booking->booking_code }}
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                                            @if($booking->status === 'pending') bg-yellow-100 text-yellow-800
                                            @elseif($booking->status === 'approved') bg-green-100 text-green-800
                                            @elseif($booking->status === 'rejected') bg-red-100 text-red-800
                                            @elseif($booking->status === 'completed') bg-blue-100 text-blue-800
                                            @else bg-gray-100 text-gray-800 @endif">
                                            {{ ucfirst($booking->status) }}
                                        </span>
                                        <p class="text-sm text-gray-500 mt-1">{{ $booking->created_at->format('d M Y, H:i') }}</p>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                                    <div>
                                        <p class="text-sm text-gray-600">Check-in</p>
                                        <p class="font-medium">{{ $booking->check_in_date->format('d M Y') }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-600">Check-out</p>
                                        <p class="font-medium">{{ $booking->check_out_date->format('d M Y') }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-600">Total Harga</p>
                                        <p class="font-medium text-lg">
                                            @if($booking->transaction)
                                                Rp {{ number_format($booking->transaction->final_amount) }}
                                            @else
                                                Rp {{ number_format($booking->bookable->price ?? $booking->bookable->price_per_month) }}
                                            @endif
                                        </p>
                                    </div>
                                </div>

                                @if($booking->status === 'rejected' && $booking->rejection_reason)
                                    <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4">
                                        <div class="flex">
                                            <i class="fas fa-exclamation-circle text-red-400 mr-2 mt-0.5"></i>
                                            <div>
                                                <h4 class="text-sm font-medium text-red-800">Alasan Penolakan</h4>
                                                <p class="text-sm text-red-700 mt-1">{{ $booking->rejection_reason }}</p>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                @if($booking->notes)
                                    <div class="bg-gray-50 rounded-lg p-4 mb-4">
                                        <h4 class="text-sm font-medium text-gray-800 mb-1">Catatan</h4>
                                        <p class="text-sm text-gray-700">{{ $booking->notes }}</p>
                                    </div>
                                @endif

                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-4">
                                        <a href="{{ route('user.bookings.show', $booking) }}"
                                           class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                                            <i class="fas fa-eye mr-1"></i>Lihat Detail
                                        </a>

                                        @if($booking->status === 'pending')
                                            <form method="POST" action="{{ route('user.bookings.cancel', $booking) }}"
                                                  class="inline" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan booking ini?')">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="text-red-600 hover:text-red-700 text-sm font-medium">
                                                    <i class="fas fa-times mr-1"></i>Batalkan
                                                </button>
                                            </form>
                                        @endif

                                        @if($booking->status === 'approved' && $booking->transaction && $booking->transaction->payment_status === 'pending')
                                            <a href="{{ route('user.bookings.payment', $booking) }}"
                                               class="text-green-600 hover:text-green-700 text-sm font-medium">
                                                <i class="fas fa-credit-card mr-1"></i>Bayar Sekarang
                                            </a>
                                        @endif
                                    </div>

                                    @if($booking->transaction)
                                        <div class="text-right">
                                            <p class="text-sm text-gray-600">Status Pembayaran</p>
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                                @if($booking->transaction->payment_status === 'paid') bg-green-100 text-green-800
                                                @elseif($booking->transaction->payment_status === 'pending') bg-yellow-100 text-yellow-800
                                                @else bg-red-100 text-red-800 @endif">
                                                {{ ucfirst($booking->transaction->payment_status) }}
                                            </span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-8">
                {{ $bookings->appends(request()->query())->links() }}
            </div>
        @else
            <div class="text-center py-12">
                <i class="fas fa-bookmark text-gray-400 text-6xl mb-4"></i>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">
                    @if($status === 'all')
                        Belum ada booking
                    @else
                        Tidak ada booking dengan status {{ ucfirst($status) }}
                    @endif
                </h3>
                <p class="text-gray-600 mb-6">
                    @if($status === 'all')
                        Mulai booking residence atau kegiatan favorit Anda.
                    @else
                        Coba lihat status booking lainnya.
                    @endif
                </p>
                <div class="flex justify-center space-x-4">
                    <a href="{{ route('residences.index') }}"
                       class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium transition-colors">
                        <i class="fas fa-building mr-2"></i>Lihat Residence
                    </a>
                    <a href="{{ route('activities.index') }}"
                       class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-medium transition-colors">
                        <i class="fas fa-calendar-alt mr-2"></i>Lihat Kegiatan
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
