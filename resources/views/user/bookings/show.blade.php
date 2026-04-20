@extends('layouts.app')

@section('title', 'Detail Booking - Infoma')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Detail Booking</h1>
                    <p class="text-gray-600 mt-2">Booking #{{ $booking->booking_code }}</p>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="{{ route('user.bookings.index') }}"
                       class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                        <i class="fas fa-arrow-left mr-2"></i>Kembali
                    </a>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Booking Status -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold text-gray-900">Status Booking</h2>
                        <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium
                            @if($booking->status === 'pending') bg-yellow-100 text-yellow-800
                            @elseif($booking->status === 'approved') bg-green-100 text-green-800
                            @elseif($booking->status === 'rejected') bg-red-100 text-red-800
                            @elseif($booking->status === 'completed') bg-blue-100 text-blue-800
                            @else bg-gray-100 text-gray-800 @endif">
                            {{ ucfirst($booking->status) }}
                        </span>
                    </div>

                    <div class="flex items-center text-sm text-gray-600">
                        <i class="fas fa-clock mr-2"></i>
                        <span>Dibuat pada {{ $booking->created_at->format('d M Y, H:i') }}</span>
                    </div>
                </div>

                <!-- Item Details -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">
                        {{ $booking->bookable_type === 'App\\Models\\Residence' ? 'Residence' : 'Kegiatan' }}
                    </h2>

                    <div class="flex items-start space-x-4">
                        <div class="w-20 h-20 bg-gray-200 rounded-lg flex items-center justify-center">
                            @if($booking->bookable->images && count($booking->bookable->images) > 0)
                                <img src="{{ asset('storage/' . $booking->bookable->images[0]) }}"
                                     alt="{{ $booking->bookable->name }}"
                                     class="w-full h-full object-cover rounded-lg">
                            @else
                                <i class="fas fa-{{ $booking->bookable_type === 'App\\Models\\Residence' ? 'building' : 'calendar-alt' }} text-gray-400 text-2xl"></i>
                            @endif
                        </div>
                        <div class="flex-1">
                            <h3 class="text-xl font-semibold text-gray-900 mb-2">{{ $booking->bookable->name }}</h3>
                            <p class="text-gray-600 mb-2">{{ Str::limit($booking->bookable->description, 150) }}</p>
                            <div class="flex items-center text-sm text-gray-500">
                                <i class="fas fa-map-marker-alt mr-2"></i>
                                <span>{{ $booking->bookable->address ?? $booking->bookable->location }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Booking Details -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Detail Booking</h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h3 class="text-sm font-medium text-gray-700 mb-2">Check-in</h3>
                            <p class="text-lg font-semibold text-gray-900">{{ $booking->check_in_date->format('d M Y') }}</p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-700 mb-2">Check-out</h3>
                            <p class="text-lg font-semibold text-gray-900">{{ $booking->check_out_date->format('d M Y') }}</p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-700 mb-2">Durasi</h3>
                            <p class="text-lg font-semibold text-gray-900">
                                {{ $booking->check_in_date->diffInDays($booking->check_out_date) }} hari
                            </p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-700 mb-2">Jumlah Peserta</h3>
                            <p class="text-lg font-semibold text-gray-900">1 orang</p>
                        </div>
                    </div>

                    @if($booking->notes)
                        <div class="mt-6">
                            <h3 class="text-sm font-medium text-gray-700 mb-2">Catatan</h3>
                            <div class="bg-gray-50 rounded-lg p-4">
                                <p class="text-gray-700">{{ $booking->notes }}</p>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Documents -->
                @if($booking->documents && count($booking->documents) > 0)
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Dokumen</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($booking->documents as $document)
                        <div class="flex items-center p-4 border border-gray-200 rounded-lg">
                            <i class="fas fa-file-pdf text-red-500 text-2xl mr-3"></i>
                            <div class="flex-1">
                                <p class="font-medium text-gray-900">{{ $document['name'] }}</p>
                                <p class="text-sm text-gray-500">{{ $document['type'] }}</p>
                            </div>
                            <a href="{{ asset('storage/' . $document['path']) }}"
                               target="_blank"
                               class="text-blue-600 hover:text-blue-700">
                                <i class="fas fa-download"></i>
                            </a>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Rejection Reason -->
                @if($booking->status === 'rejected' && $booking->rejection_reason)
                <div class="bg-red-50 border border-red-200 rounded-lg p-6">
                    <div class="flex">
                        <i class="fas fa-exclamation-circle text-red-400 mr-3 mt-0.5"></i>
                        <div>
                            <h3 class="text-lg font-medium text-red-800 mb-2">Alasan Penolakan</h3>
                            <p class="text-red-700">{{ $booking->rejection_reason }}</p>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1">
                <!-- Payment Info -->
                @if($booking->transaction)
                <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Informasi Pembayaran</h3>

                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Harga Awal</span>
                            <span class="font-medium">Rp {{ number_format($booking->transaction->original_amount) }}</span>
                        </div>

                        @if($booking->transaction->discount_amount > 0)
                        <div class="flex justify-between text-green-600">
                            <span>Diskon</span>
                            <span>- Rp {{ number_format($booking->transaction->discount_amount) }}</span>
                        </div>
                        @endif

                        <div class="border-t border-gray-200 pt-3">
                            <div class="flex justify-between text-lg font-semibold">
                                <span>Total</span>
                                <span>Rp {{ number_format($booking->transaction->final_amount) }}</span>
                            </div>
                        </div>

                        <div class="mt-4">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-gray-600">Status Pembayaran</span>
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                    @if($booking->transaction->payment_status === 'paid') bg-green-100 text-green-800
                                    @elseif($booking->transaction->payment_status === 'pending') bg-yellow-100 text-yellow-800
                                    @else bg-red-100 text-red-800 @endif">
                                    {{ ucfirst($booking->transaction->payment_status) }}
                                </span>
                            </div>

                            @if($booking->transaction->payment_method)
                                <p class="text-sm text-gray-600">Metode: {{ ucfirst($booking->transaction->payment_method) }}</p>
                            @endif
                        </div>
                    </div>
                </div>
                @endif

                <!-- Actions -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Aksi</h3>

                    <div class="space-y-3">
                        @if($booking->status === 'pending')
                            <form method="POST" action="{{ route('user.bookings.cancel', $booking) }}"
                                  onsubmit="return confirm('Apakah Anda yakin ingin membatalkan booking ini?')">
                                @csrf
                                @method('PATCH')
                                <button type="submit"
                                        class="w-full bg-red-600 hover:bg-red-700 text-white py-2 px-4 rounded-lg font-medium transition-colors">
                                    <i class="fas fa-times mr-2"></i>Batalkan Booking
                                </button>
                            </form>
                        @endif

                        @if($booking->status === 'approved' && $booking->transaction && $booking->transaction->payment_status === 'pending')
                            <a href="{{ route('user.bookings.payment', $booking) }}"
                               class="w-full bg-green-600 hover:bg-green-700 text-white py-2 px-4 rounded-lg font-medium transition-colors text-center block">
                                <i class="fas fa-credit-card mr-2"></i>Bayar Sekarang
                            </a>
                        @endif

                        @if($booking->status === 'completed' && $canRate)
                            <button onclick="openRatingModal()"
                                    class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-lg font-medium transition-colors">
                                <i class="fas fa-star mr-2"></i>Berikan Rating
                            </button>
                        @endif

                        <a href="{{ route($booking->bookable_type === 'App\\Models\\Residence' ? 'residences.show' : 'activities.show', $booking->bookable) }}"
                           class="w-full bg-gray-600 hover:bg-gray-700 text-white py-2 px-4 rounded-lg font-medium transition-colors text-center block">
                            <i class="fas fa-eye mr-2"></i>Lihat Item
                        </a>
                    </div>
                </div>

                <!-- Provider Info -->
                <div class="bg-white rounded-lg shadow-sm p-6 mt-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Kontak</h3>
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mr-4">
                            <i class="fas fa-user text-blue-600"></i>
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">{{ $booking->bookable->provider->name }}</p>
                            <p class="text-sm text-gray-600">{{ $booking->bookable->provider->email }}</p>
                            <p class="text-sm text-gray-600">{{ $booking->bookable->provider->phone }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Rating Modal -->
@if($booking->status === 'completed' && $canRate)
<div id="ratingModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 w-full max-w-md mx-4">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Berikan Rating</h3>

        <form id="ratingForm" method="POST" action="{{ route('user.ratings.store') }}">
            @csrf
            <input type="hidden" name="rateable_type" value="{{ $booking->bookable_type }}">
            <input type="hidden" name="rateable_id" value="{{ $booking->bookable_id }}">

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Rating</label>
                <div class="flex space-x-1" id="ratingStars">
                    @for($i = 1; $i <= 5; $i++)
                        <i class="fas fa-star text-2xl text-gray-300 cursor-pointer hover:text-yellow-400"
                           data-rating="{{ $i }}" onclick="setRating({{ $i }})"></i>
                    @endfor
                </div>
                <input type="hidden" name="rating" id="ratingInput" value="0">
            </div>

            <div class="mb-4">
                <label for="comment" class="block text-sm font-medium text-gray-700 mb-2">Komentar (Opsional)</label>
                <textarea name="comment" id="comment" rows="3"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                          placeholder="Bagikan pengalaman Anda..."></textarea>
            </div>

            <div class="flex justify-end space-x-3">
                <button type="button" onclick="closeRatingModal()"
                        class="px-4 py-2 text-gray-600 hover:text-gray-800 font-medium">
                    Batal
                </button>
                <button type="submit"
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium">
                    Kirim Rating
                </button>
            </div>
        </form>
    </div>
</div>
@endif

@push('scripts')
<script>
function openRatingModal() {
    document.getElementById('ratingModal').classList.remove('hidden');
    document.getElementById('ratingModal').classList.add('flex');
}

function closeRatingModal() {
    document.getElementById('ratingModal').classList.add('hidden');
    document.getElementById('ratingModal').classList.remove('flex');
}

function setRating(rating) {
    const stars = document.querySelectorAll('#ratingStars i');
    const ratingInput = document.getElementById('ratingInput');

    stars.forEach((star, index) => {
        if (index < rating) {
            star.classList.remove('text-gray-300');
            star.classList.add('text-yellow-400');
        } else {
            star.classList.remove('text-yellow-400');
            star.classList.add('text-gray-300');
        }
    });

    ratingInput.value = rating;
}

// Close modal when clicking outside
document.getElementById('ratingModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeRatingModal();
    }
});
</script>
@endpush
@endsection
