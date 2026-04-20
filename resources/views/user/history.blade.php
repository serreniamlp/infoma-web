@extends('layouts.app')

@section('title', 'My History - Infoma')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">My History</h1>
                    <p class="text-gray-600 mt-1">Kelola booking, bookmark, dan riwayat aktivitas Anda</p>
                </div>
                <div class="hidden md:block">
                    <div class="bg-purple-100 rounded-full p-3">
                        <i class="fas fa-history text-purple-600 text-2xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <a href="{{ route('user.bookings.index') }}"
               class="bg-white rounded-lg shadow-sm p-6 hover:shadow-md transition-shadow duration-200 group">
                <div class="flex items-center">
                    <div class="bg-yellow-100 rounded-lg p-3 group-hover:bg-yellow-200 transition-colors">
                        <i class="fas fa-bookmark text-yellow-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-gray-900">Booking Saya</h3>
                        <p class="text-gray-600 text-sm">Kelola booking</p>
                    </div>
                </div>
            </a>

            <a href="{{ route('user.bookmarks.index') }}"
               class="bg-white rounded-lg shadow-sm p-6 hover:shadow-md transition-shadow duration-200 group">
                <div class="flex items-center">
                    <div class="bg-purple-100 rounded-lg p-3 group-hover:bg-purple-200 transition-colors">
                        <i class="fas fa-heart text-purple-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-gray-900">Bookmark</h3>
                        <p class="text-gray-600 text-sm">Item tersimpan</p>
                    </div>
                </div>
            </a>

            @if(isset($transactions) && $transactions->count() > 0)
            <a href="{{ route('user.marketplace.transactions.index') }}"
               class="bg-white rounded-lg shadow-sm p-6 hover:shadow-md transition-shadow duration-200 group">
                <div class="flex items-center">
                    <div class="bg-green-100 rounded-lg p-3 group-hover:bg-green-200 transition-colors">
                        <i class="fas fa-shopping-cart text-green-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-gray-900">Transaksi</h3>
                        <p class="text-gray-600 text-sm">Riwayat pembelian</p>
                    </div>
                </div>
            </a>
            @endif

            <a href="{{ route('user.ratings.show') }}"
               class="bg-white rounded-lg shadow-sm p-6 hover:shadow-md transition-shadow duration-200 group">
                <div class="flex items-center">
                    <div class="bg-blue-100 rounded-lg p-3 group-hover:bg-blue-200 transition-colors">
                        <i class="fas fa-star text-blue-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-gray-900">Rating</h3>
                        <p class="text-gray-600 text-sm">Ulasan saya</p>
                    </div>
                </div>
            </a>
        </div>

        <!-- Statistics -->
        <div class="bg-white rounded-lg shadow-sm mb-8">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Statistik</h2>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="text-center p-4 bg-blue-50 rounded-lg">
                        <div class="text-2xl font-bold text-blue-600">{{ auth()->user()->bookings()->count() }}</div>
                        <div class="text-sm text-gray-600">Total Booking</div>
                    </div>
                    <div class="text-center p-4 bg-green-50 rounded-lg">
                        <div class="text-2xl font-bold text-green-600">{{ auth()->user()->bookmarks()->count() }}</div>
                        <div class="text-sm text-gray-600">Bookmark</div>
                    </div>
                    <div class="text-center p-4 bg-yellow-50 rounded-lg">
                        <div class="text-2xl font-bold text-yellow-600">{{ auth()->user()->bookings()->where('status', 'pending')->count() }}</div>
                        <div class="text-sm text-gray-600">Pending</div>
                    </div>
                    <div class="text-center p-4 bg-purple-50 rounded-lg">
                        <div class="text-2xl font-bold text-purple-600">{{ auth()->user()->bookings()->where('status', 'approved')->count() }}</div>
                        <div class="text-sm text-gray-600">Disetujui</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Content Tabs -->
        <div class="bg-white rounded-lg shadow-sm">
            <div class="border-b border-gray-200">
                <nav class="-mb-px flex space-x-8 px-6" aria-label="Tabs">
                    <button class="tab-button border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm active" 
                            data-tab="bookings">
                        Booking Terbaru
                    </button>
                    <button class="tab-button border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm" 
                            data-tab="bookmarks">
                        Bookmark
                    </button>
                    @if(isset($transactions) && $transactions->count() > 0)
                    <button class="tab-button border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm" 
                            data-tab="transactions">
                        Transaksi
                    </button>
                    @endif
                </nav>
            </div>

            <!-- Bookings Tab -->
            <div id="bookings-tab" class="tab-content p-6">
                @if($bookings->count() > 0)
                    <div class="space-y-4">
                        @foreach($bookings as $booking)
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                            <div class="flex items-center">
                                <div class="bg-blue-100 rounded-lg p-2 mr-3">
                                    <i class="fas fa-{{ $booking->bookable_type === 'App\\Models\\Residence' ? 'building' : 'calendar-alt' }} text-blue-600"></i>
                                </div>
                                <div>
                                    <h4 class="font-medium text-gray-900">{{ $booking->bookable->name }}</h4>
                                    <p class="text-sm text-gray-600">{{ $booking->created_at->format('d M Y') }}</p>
                                    @if($booking->bookable_type === 'App\\Models\\Activity')
                                        <p class="text-sm text-gray-500">{{ $booking->bookable->event_date->format('d M Y, H:i') }}</p>
                                    @endif
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
                                <a href="{{ route('user.bookings.show', $booking) }}" 
                                   class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                                    Detail
                                </a>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    
                    <!-- Pagination -->
                    <div class="mt-6">
                        {{ $bookings->links() }}
                    </div>
                @else
                    <div class="text-center py-8">
                        <i class="fas fa-bookmark text-gray-400 text-4xl mb-4"></i>
                        <p class="text-gray-600">Belum ada booking</p>
                        <a href="{{ route('residences.index') }}"
                           class="text-blue-600 hover:text-blue-700 text-sm font-medium mt-2 inline-block">
                            Mulai booking sekarang
                        </a>
                    </div>
                @endif
            </div>

            <!-- Bookmarks Tab -->
            <div id="bookmarks-tab" class="tab-content p-6 hidden">
                @if($bookmarks->count() > 0)
                    <div class="space-y-4">
                        @foreach($bookmarks as $bookmark)
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                            <div class="flex items-center">
                                <div class="bg-purple-100 rounded-lg p-2 mr-3">
                                    <i class="fas fa-{{ 
                                        $bookmark->bookmarkable_type === 'App\\Models\\Residence' ? 'building' : 
                                        ($bookmark->bookmarkable_type === 'App\\Models\\Activity' ? 'calendar-alt' : 'shopping-bag') 
                                    }} text-purple-600"></i>
                                </div>
                                <div>
                                    <h4 class="font-medium text-gray-900">{{ $bookmark->bookmarkable->name }}</h4>
                                    <p class="text-sm text-gray-600">{{ $bookmark->created_at->format('d M Y') }}</p>
                                    <p class="text-sm text-gray-500">
                                        @if($bookmark->bookmarkable_type === 'App\\Models\\Residence')
                                            Residence
                                        @elseif($bookmark->bookmarkable_type === 'App\\Models\\Activity')
                                            Activity
                                        @else
                                            Marketplace Product
                                        @endif
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-3">
                                <a href="@if($bookmark->bookmarkable_type === 'App\\Models\\Residence')
                                            {{ route('residences.show', $bookmark->bookmarkable) }}
                                        @elseif($bookmark->bookmarkable_type === 'App\\Models\\Activity')
                                            {{ route('activities.show', $bookmark->bookmarkable) }}
                                        @else
                                            {{ route('marketplace.show', $bookmark->bookmarkable) }}
                                        @endif" 
                                   class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                                    Lihat
                                </a>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    
                    <!-- Pagination -->
                    <div class="mt-6">
                        {{ $bookmarks->links() }}
                    </div>
                @else
                    <div class="text-center py-8">
                        <i class="fas fa-heart text-gray-400 text-4xl mb-4"></i>
                        <p class="text-gray-600">Belum ada bookmark</p>
                        <a href="{{ route('user.dashboard') }}"
                           class="text-blue-600 hover:text-blue-700 text-sm font-medium mt-2 inline-block">
                            Jelajahi konten
                        </a>
                    </div>
                @endif
            </div>

            <!-- Transactions Tab -->
            @if(isset($transactions) && $transactions->count() > 0)
            <div id="transactions-tab" class="tab-content p-6 hidden">
                <div class="space-y-4">
                    @foreach($transactions as $transaction)
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                        <div class="flex items-center">
                            <div class="bg-green-100 rounded-lg p-2 mr-3">
                                <i class="fas fa-shopping-cart text-green-600"></i>
                            </div>
                            <div>
                                <h4 class="font-medium text-gray-900">{{ $transaction->product->name }}</h4>
                                <p class="text-sm text-gray-600">{{ $transaction->created_at->format('d M Y') }}</p>
                                <p class="text-sm text-gray-500">Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</p>
                            </div>
                        </div>
                        <div class="flex items-center space-x-3">
                            <span class="px-3 py-1 rounded-full text-xs font-medium
                                @if($transaction->status === 'pending') bg-yellow-100 text-yellow-800
                                @elseif($transaction->status === 'completed') bg-green-100 text-green-800
                                @elseif($transaction->status === 'cancelled') bg-red-100 text-red-800
                                @else bg-gray-100 text-gray-800 @endif">
                                {{ ucfirst($transaction->status) }}
                            </span>
                            <a href="{{ route('marketplace.transactions.show', $transaction) }}" 
                               class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                                Detail
                            </a>
                        </div>
                    </div>
                    @endforeach
                </div>
                
                <!-- Pagination -->
                <div class="mt-6">
                    {{ $transactions->links() }}
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tabButtons = document.querySelectorAll('.tab-button');
    const tabContents = document.querySelectorAll('.tab-content');

    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            const targetTab = this.getAttribute('data-tab');

            // Remove active class from all buttons
            tabButtons.forEach(btn => {
                btn.classList.remove('active', 'border-blue-500', 'text-blue-600');
                btn.classList.add('border-transparent', 'text-gray-500');
            });

            // Add active class to clicked button
            this.classList.add('active', 'border-blue-500', 'text-blue-600');
            this.classList.remove('border-transparent', 'text-gray-500');

            // Hide all tab contents
            tabContents.forEach(content => {
                content.classList.add('hidden');
            });

            // Show target tab content
            document.getElementById(targetTab + '-tab').classList.remove('hidden');
        });
    });

    // Set initial active state
    const activeButton = document.querySelector('.tab-button.active');
    if (activeButton) {
        activeButton.classList.add('border-blue-500', 'text-blue-600');
        activeButton.classList.remove('border-transparent', 'text-gray-500');
    }
});
</script>
@endsection
