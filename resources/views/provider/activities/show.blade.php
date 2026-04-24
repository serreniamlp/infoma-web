@extends('layouts.app')

@section('title', $activity->name . ' - Infoma')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">{{ $activity->name }}</h1>
                <p class="text-gray-600 mt-2">Detail kegiatan Anda</p>
            </div>
            <div class="flex items-center space-x-4">
                <a href="{{ route('provider.activities.edit', $activity) }}"
                   class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                    <i class="fas fa-edit mr-2"></i>Edit
                </a>
                <a href="{{ route('provider.activities.index') }}"
                   class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>Kembali
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Image Gallery -->
                <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                    @if($activity->images && count($activity->images) > 0)
                        <div class="relative">
                            <img src="{{ asset('storage/' . $activity->images[0]) }}"
                                 alt="{{ $activity->name }}"
                                 class="w-full h-96 object-cover" id="mainImage">
                            @if(count($activity->images) > 1)
                                <div class="absolute bottom-4 left-4 right-4">
                                    <div class="flex space-x-2 overflow-x-auto">
                                        @foreach($activity->images as $index => $image)
                                            <img src="{{ asset('storage/' . $image) }}"
                                                 alt="{{ $activity->name }}"
                                                 class="w-16 h-16 object-cover rounded cursor-pointer border-2 {{ $index === 0 ? 'border-green-500' : 'border-white' }}"
                                                 onclick="changeMainImage('{{ asset('storage/' . $image) }}', this)">
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="w-full h-96 bg-gray-200 flex items-center justify-center">
                            <i class="fas fa-calendar-alt text-6xl text-gray-400"></i>
                        </div>
                    @endif
                </div>

                <!-- Activity Details -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="flex items-start justify-between mb-4">
                        <div>
                            <h2 class="text-2xl font-semibold text-gray-900 mb-2">{{ $activity->name }}</h2>
                            <div class="flex items-center text-gray-600 mb-2">
                                <i class="fas fa-map-marker-alt mr-2"></i>
                                <span>{{ $activity->location }}</span>
                            </div>
                            <div class="flex items-center">
                                <span class="px-3 py-1 rounded-full text-sm font-medium {{ $activity->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $activity->is_active ? 'Aktif' : 'Tidak Aktif' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="prose max-w-none">
                        <h3 class="text-lg font-semibold text-gray-900 mb-3">Deskripsi</h3>
                        <p class="text-gray-700 leading-relaxed">{{ $activity->description }}</p>
                    </div>
                </div>

                <!-- Event Details -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Detail Kegiatan</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="flex items-center">
                            <i class="fas fa-calendar text-green-500 mr-3"></i>
                            <div>
                                <p class="text-sm text-gray-600">Tanggal Kegiatan</p>
                                <p class="font-medium">{{ $activity->event_date->format('d M Y, H:i') }}</p>
                            </div>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-clock text-green-500 mr-3"></i>
                            <div>
                                <p class="text-sm text-gray-600">Batas Pendaftaran</p>
                                <p class="font-medium">{{ $activity->registration_deadline->format('d M Y, H:i') }}</p>
                            </div>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-users text-green-500 mr-3"></i>
                            <div>
                                <p class="text-sm text-gray-600">Kapasitas</p>
                                <p class="font-medium">{{ $activity->capacity }} peserta</p>
                            </div>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-ticket-alt text-green-500 mr-3"></i>
                            <div>
                                <p class="text-sm text-gray-600">Slot Tersisa</p>
                                <p class="font-medium text-green-600">{{ $activity->available_slots }} slot</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1">
                <!-- Price Info -->
                <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Informasi Harga</h3>

                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Harga Dasar</span>
                            <span class="font-medium">Rp {{ number_format($activity->price) }}</span>
                        </div>

                        @if($activity->discount_type && $activity->discount_value)
                            <div class="flex justify-between text-green-600">
                                <span>Diskon</span>
                                <span>
                                    @if($activity->discount_type === 'percentage')
                                        {{ $activity->discount_value }}%
                                    @else
                                        Rp {{ number_format($activity->discount_value) }}
                                    @endif
                                </span>
                            </div>

                            <div class="border-t border-gray-200 pt-3">
                                <div class="flex justify-between text-lg font-semibold">
                                    <span>Harga Akhir</span>
                                    <span>Rp {{ number_format($activity->getDiscountedPrice()) }}</span>
                                </div>
                            </div>
                        @endif

                        <div class="text-sm text-gray-500">per peserta</div>
                    </div>
                </div>

                <!-- Activity Stats -->
                <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Statistik</h3>

                    <div class="space-y-4">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Total Booking</span>
                            <span class="font-medium">{{ $activity->bookings()->count() }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Pendapatan</span>
                            <span class="font-medium">Rp {{ number_format($activity->approvedRevenue()) }}</span>
                        </div>
                        @if($activity->ratings_avg_rating)
                            <div class="flex justify-between">
                                <span class="text-gray-600">Rating Rata-rata</span>
                                <span class="font-medium">{{ number_format($activity->ratings_avg_rating, 1) }}/5</span>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Aksi Cepat</h3>

                    <div class="space-y-3">
                        <a href="{{ route('provider.activities.edit', $activity) }}"
                           class="w-full bg-green-600 hover:bg-green-700 text-white py-2 px-4 rounded-lg font-medium transition-colors text-center block">
                            <i class="fas fa-edit mr-2"></i>Edit Kegiatan
                        </a>

                        <form method="POST" action="{{ route('provider.activities.toggleStatus', $activity) }}" class="block">
                            @csrf
                            @method('PATCH')
                            <button type="submit"
                                    class="w-full bg-{{ $activity->is_active ? 'yellow' : 'green' }}-600 hover:bg-{{ $activity->is_active ? 'yellow' : 'green' }}-700 text-white py-2 px-4 rounded-lg font-medium transition-colors">
                                <i class="fas fa-{{ $activity->is_active ? 'pause' : 'play' }} mr-2"></i>
                                {{ $activity->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                            </button>
                        </form>

                        <a href="{{ route('provider.bookings.index', ['activity' => $activity->id]) }}"
                           class="w-full bg-purple-600 hover:bg-purple-700 text-white py-2 px-4 rounded-lg font-medium transition-colors text-center block">
                            <i class="fas fa-bookmark mr-2"></i>Lihat Booking
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function changeMainImage(src, element) {
    document.getElementById('mainImage').src = src;
    document.querySelectorAll('.border-green-500').forEach(el => el.classList.remove('border-green-500', 'border-white'));
    element.classList.add('border-green-500');
    element.classList.remove('border-white');
}
</script>
@endpush
@endsection
