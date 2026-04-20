@extends('layouts.app')

@section('title', 'Residence - Infoma')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Residence</h1>
            <p class="text-gray-600 mt-2">Temukan tempat tinggal terbaik untuk kebutuhan Anda</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            <!-- Filters Sidebar -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-sm p-6 sticky top-8">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Filter Pencarian</h3>

                    <form method="GET" action="{{ route('residences.index') }}" class="space-y-4">
                        <!-- Search -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Kata Kunci</label>
                            <input type="text" name="search" value="{{ request('search') }}"
                                   placeholder="Cari residence..."
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>

                        <!-- Category -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Kategori</label>
                            <select name="category" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="">Semua Kategori</option>
                                @foreach($categories as $category)
                                    @if($category->type === 'residence')
                                        <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                        </div>

                        <!-- Price Range -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Harga per Bulan</label>
                            <div class="space-y-2">
                                <input type="number" name="min_price" value="{{ request('min_price') }}"
                                       placeholder="Min"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <input type="number" name="max_price" value="{{ request('max_price') }}"
                                       placeholder="Max"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                        </div>

                        <!-- Rental Period -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Periode Sewa</label>
                            <select name="rental_period" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="">Semua Periode</option>
                                <option value="monthly" {{ request('rental_period') === 'monthly' ? 'selected' : '' }}>Bulanan</option>
                                <option value="yearly" {{ request('rental_period') === 'yearly' ? 'selected' : '' }}>Tahunan</option>
                            </select>
                        </div>

                        <!-- Location -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Lokasi</label>
                            <input type="text" name="location" value="{{ request('location') }}"
                                   placeholder="Kota atau area..."
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>

                        <!-- Sort -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Urutkan</label>
                            <select name="sort" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="newest" {{ request('sort') === 'newest' ? 'selected' : '' }}>Terbaru</option>
                                <option value="price_low" {{ request('sort') === 'price_low' ? 'selected' : '' }}>Harga Terendah</option>
                                <option value="price_high" {{ request('sort') === 'price_high' ? 'selected' : '' }}>Harga Tertinggi</option>
                                <option value="rating" {{ request('sort') === 'rating' ? 'selected' : '' }}>Rating Tertinggi</option>
                            </select>
                        </div>

                        <div class="flex gap-2">
                            <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                                Terapkan
                            </button>
                            <a href="{{ route('residences.index') }}" class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-lg font-medium transition-colors text-center">
                                Reset
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Results -->
            <div class="lg:col-span-3">
                <!-- Results Header -->
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2 class="text-xl font-semibold text-gray-900">
                            {{ $residences->total() }} Residence ditemukan
                        </h2>
                        @if(request()->hasAny(['search', 'category', 'min_price', 'max_price', 'rental_period', 'location']))
                            <p class="text-sm text-gray-600 mt-1">Hasil berdasarkan filter yang dipilih</p>
                        @endif
                    </div>
                </div>

                <!-- Residence Grid -->
                @if($residences->count() > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                        @foreach($residences as $residence)
                        <div class="bg-white rounded-lg shadow-sm overflow-hidden hover:shadow-md transition-shadow duration-200">
                            <div class="h-48 bg-gray-200 relative">
                                @if($residence->images && count($residence->images) > 0)
                                    <img src="{{ asset('storage/' . $residence->images[0]) }}"
                                         alt="{{ $residence->name }}"
                                         class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center">
                                        <i class="fas fa-home text-4xl text-gray-400"></i>
                                    </div>
                                @endif
                                <div class="absolute top-4 right-4 flex flex-col gap-2">
                                    <span class="bg-blue-600 text-white px-2 py-1 rounded-full text-xs font-medium">
                                        {{ $residence->available_slots }} tersedia
                                    </span>
                                    @auth
                                        @if(auth()->user()->hasRole('user'))
                                            @php
                                                $isBookmarked = auth()->user()->bookmarks()
                                                    ->where('bookmarkable_type', 'App\\Models\\Residence')
                                                    ->where('bookmarkable_id', $residence->id)
                                                    ->exists();
                                            @endphp
                                            <button onclick="toggleBookmark('residence', {{ $residence->id }}, this)"
                                                    class="bg-white hover:bg-gray-100 text-gray-700 p-2 rounded-full shadow-md transition-colors {{ $isBookmarked ? 'text-red-500' : 'text-gray-400' }}">
                                                <i class="fas fa-heart {{ $isBookmarked ? 'fas' : 'far' }}"></i>
                                            </button>
                                        @endif
                                    @endauth
                                </div>
                                @if($residence->discount_type && $residence->discount_value)
                                    <div class="absolute top-4 left-4">
                                        <span class="bg-red-500 text-white px-2 py-1 rounded-full text-xs font-medium">
                                            @if($residence->discount_type === 'percentage')
                                                {{ $residence->discount_value }}% OFF
                                            @else
                                                Rp {{ number_format($residence->discount_value) }} OFF
                                            @endif
                                        </span>
                                    </div>
                                @endif
                            </div>
                            <div class="p-6">
                                <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ $residence->name }}</h3>
                                <p class="text-gray-600 text-sm mb-3">{{ Str::limit($residence->description, 100) }}</p>

                                <div class="flex items-center text-sm text-gray-500 mb-3">
                                    <i class="fas fa-map-marker-alt mr-2"></i>
                                    <span>{{ Str::limit($residence->address, 30) }}</span>
                                </div>

                                <div class="flex items-center justify-between mb-3">
                                    <div class="flex items-center">
                                        <i class="fas fa-calendar mr-2 text-gray-400"></i>
                                        <span class="text-sm text-gray-600">{{ ucfirst($residence->rental_period) }}</span>
                                    </div>
                                    @if($residence->ratings_avg_rating)
                                        <div class="flex items-center">
                                            <i class="fas fa-star text-yellow-400 mr-1"></i>
                                            <span class="text-sm font-medium">{{ number_format($residence->ratings_avg_rating, 1) }}</span>
                                            <span class="text-xs text-gray-500 ml-1">({{ $residence->ratings_count }})</span>
                                        </div>
                                    @else
                                        <div class="flex items-center">
                                            <i class="fas fa-star text-gray-300 mr-1"></i>
                                            <span class="text-sm text-gray-500">Belum ada rating</span>
                                        </div>
                                    @endif
                                </div>

                                <div class="flex items-center justify-between">
                                    <div>
                                        @if($residence->discount_type && $residence->discount_value)
                                            <div class="text-sm text-gray-500 line-through">
                                                Rp {{ number_format($residence->price_per_month) }}
                                            </div>
                                            <div class="text-xl font-bold text-blue-600">
                                                Rp {{ number_format($residence->getDiscountedPrice()) }}
                                            </div>
                                        @else
                                            <div class="text-xl font-bold text-blue-600">
                                                Rp {{ number_format($residence->price_per_month) }}/bulan
                                            </div>
                                        @endif
                                    </div>
                                    <a href="{{ route('residences.show', $residence) }}"
                                       class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                                        Lihat Detail
                                    </a>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <!-- Pagination -->
                    <div class="mt-8">
                        {{ $residences->appends(request()->query())->links() }}
                    </div>
                @else
                    <div class="text-center py-12">
                        <i class="fas fa-home text-gray-400 text-6xl mb-4"></i>
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">Tidak ada residence ditemukan</h3>
                        <p class="text-gray-600 mb-6">Coba ubah filter pencarian atau kata kunci Anda.</p>
                        <a href="{{ route('residences.index') }}"
                           class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium transition-colors">
                            Lihat Semua Residence
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function toggleBookmark(type, id, button) {
    const icon = button.querySelector('i');
    const isBookmarked = icon.classList.contains('fas');

    const url = isBookmarked ? '{{ route("user.bookmarks.destroy") }}' : '{{ route("user.bookmarks.store") }}';
    const method = isBookmarked ? 'DELETE' : 'POST';

    fetch(url, {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            type: type,
            id: id
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.message) {
            if (isBookmarked) {
                // Remove bookmark
                icon.classList.remove('fas');
                icon.classList.add('far');
                button.classList.remove('text-red-500');
                button.classList.add('text-gray-400');
            } else {
                // Add bookmark
                icon.classList.remove('far');
                icon.classList.add('fas');
                button.classList.remove('text-gray-400');
                button.classList.add('text-red-500');
            }
        } else {
            alert('Gagal mengubah bookmark. Silakan coba lagi.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan. Silakan coba lagi.');
    });
}
</script>
@endpush
@endsection
