@extends('layouts.app')

@section('title', $residence->name . ' - Infoma')



@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">{{ $residence->name }}</h1>
                <p class="text-gray-600 mt-2">Detail residence Anda</p>
            </div>
            <div class="flex items-center space-x-4">
                <a href="{{ route('provider.residences.edit', $residence) }}"
                   class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                    <i class="fas fa-edit mr-2"></i>Edit
                </a>
                <a href="{{ route('provider.residences.index') }}"
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
                    @if($residence->images && count($residence->images) > 0)
                        <div class="relative">
                            <img src="{{ asset('storage/' . $residence->images[0]) }}"
                                 alt="{{ $residence->name }}"
                                 class="w-full h-96 object-cover" id="mainImage">
                            @if(count($residence->images) > 1)
                                <div class="absolute bottom-4 left-4 right-4">
                                    <div class="flex space-x-2 overflow-x-auto">
                                        @foreach($residence->images as $index => $image)
                                            <img src="{{ asset('storage/' . $image) }}"
                                                 alt="{{ $residence->name }}"
                                                 class="w-16 h-16 object-cover rounded cursor-pointer border-2 {{ $index === 0 ? 'border-blue-500' : 'border-white' }}"
                                                 onclick="changeMainImage('{{ asset('storage/' . $image) }}', this)">
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="w-full h-96 bg-gray-200 flex items-center justify-center">
                            <i class="fas fa-home text-6xl text-gray-400"></i>
                        </div>
                    @endif
                </div>

                <!-- Residence Details -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="flex items-start justify-between mb-4">
                        <div>
                            <h2 class="text-2xl font-semibold text-gray-900 mb-2">{{ $residence->name }}</h2>
                            <div class="flex items-center text-gray-600 mb-2">
                                <i class="fas fa-map-marker-alt mr-2"></i>
                                <span>{{ $residence->address }}</span>
                            </div>
                            <div class="flex items-center">
                                <span class="px-3 py-1 rounded-full text-sm font-medium {{ $residence->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $residence->is_active ? 'Aktif' : 'Tidak Aktif' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="prose max-w-none">
                        <h3 class="text-lg font-semibold text-gray-900 mb-3">Deskripsi</h3>
                        <p class="text-gray-700 leading-relaxed">{{ $residence->description }}</p>
                    </div>
                </div>

                <!-- Facilities -->
                @if($residence->facilities && count($residence->facilities) > 0)
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Fasilitas</h3>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                        @foreach($residence->facilities as $facility)
                        <div class="flex items-center">
                            <i class="fas fa-check text-green-500 mr-2"></i>
                            <span class="text-gray-700">{{ $facility }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Reviews -->
                @if($residence->ratings && $residence->ratings->count() > 0)
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Ulasan ({{ $residence->ratings->count() }})</h3>
                    <div class="space-y-4">
                        @foreach($residence->ratings as $rating)
                        <div class="border-b border-gray-200 pb-4 last:border-b-0">
                            <div class="flex items-center justify-between mb-2">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                                        <span class="text-sm font-medium text-blue-600">{{ substr($rating->user->name, 0, 1) }}</span>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900">{{ $rating->user->name }}</p>
                                        <div class="flex items-center">
                                            @for($i = 1; $i <= 5; $i++)
                                                <i class="fas fa-star {{ $i <= $rating->rating ? 'text-yellow-400' : 'text-gray-300' }} text-xs"></i>
                                            @endfor
                                        </div>
                                    </div>
                                </div>
                                <span class="text-sm text-gray-500">{{ $rating->created_at->format('d M Y') }}</span>
                            </div>
                            @if($rating->comment)
                                <p class="text-gray-700">{{ $rating->comment }}</p>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1">
                <!-- Price Info -->
                <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Informasi Harga</h3>

                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Harga Dasar</span>
                            <span class="font-medium">Rp {{ number_format($residence->price_per_month) }}</span>
                        </div>

                        @if($residence->discount_type && $residence->discount_value)
                            <div class="flex justify-between text-green-600">
                                <span>Diskon</span>
                                <span>
                                    @if($residence->discount_type === 'percentage')
                                        {{ $residence->discount_value }}%
                                    @else
                                        Rp {{ number_format($residence->discount_value) }}
                                    @endif
                                </span>
                            </div>

                            <div class="border-t border-gray-200 pt-3">
                                <div class="flex justify-between text-lg font-semibold">
                                    <span>Harga Akhir</span>
                                    <span>Rp {{ number_format($residence->getDiscountedPrice()) }}</span>
                                </div>
                            </div>
                        @endif

                        <div class="text-sm text-gray-500">
                            per {{ $residence->rental_period === 'monthly' ? 'bulan' : 'tahun' }}
                        </div>
                    </div>
                </div>

                <!-- Residence Stats -->
                <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Statistik</h3>

                    <div class="space-y-4">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Kapasitas</span>
                            <span class="font-medium">{{ $residence->capacity }} orang</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Tersedia</span>
                            <span class="font-medium text-green-600">{{ $residence->available_slots }} slot</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Periode Sewa</span>
                            <span class="font-medium">{{ ucfirst($residence->rental_period) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Total Booking</span>
                            <span class="font-medium">{{ $residence->bookings()->count() }}</span>
                        </div>
                        @if($residence->ratings_avg_rating)
                            <div class="flex justify-between">
                                <span class="text-gray-600">Rating Rata-rata</span>
                                <span class="font-medium">{{ number_format($residence->ratings_avg_rating, 1) }}/5</span>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Aksi Cepat</h3>

                    <div class="space-y-3">
                        <a href="{{ route('provider.residences.edit', $residence) }}"
                           class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-lg font-medium transition-colors text-center block">
                            <i class="fas fa-edit mr-2"></i>Edit Residence
                        </a>

                        <form method="POST" action="{{ route('provider.residences.toggleStatus', $residence) }}" class="block">
                            @csrf
                            @method('PATCH')
                            <button type="submit"
                                    class="w-full bg-{{ $residence->is_active ? 'yellow' : 'green' }}-600 hover:bg-{{ $residence->is_active ? 'yellow' : 'green' }}-700 text-white py-2 px-4 rounded-lg font-medium transition-colors">
                                <i class="fas fa-{{ $residence->is_active ? 'pause' : 'play' }} mr-2"></i>
                                {{ $residence->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                            </button>
                        </form>

                        <a href="{{ route('provider.bookings.index', ['residence' => $residence->id]) }}"
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
    document.querySelectorAll('.border-blue-500').forEach(el => el.classList.remove('border-blue-500', 'border-white'));
    element.classList.add('border-blue-500');
    element.classList.remove('border-white');
}
</script>
@endpush
@endsection
