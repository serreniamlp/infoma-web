@extends('layouts.app')

@section('title', 'Kegiatan - Infoma')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Kegiatan Kampus</h1>
            <p class="text-gray-600 mt-2">Ikuti kegiatan kampus yang menarik dan bermanfaat</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            <!-- Filters Sidebar -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-sm p-6 sticky top-8">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Filter Pencarian</h3>

                    <form method="GET" action="{{ route('activities.index') }}" class="space-y-4">
                        <!-- Search -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Kata Kunci</label>
                            <input type="text" name="search" value="{{ request('search') }}"
                                   placeholder="Cari kegiatan..."
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                        </div>

                        <!-- Category -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Kategori</label>
                            <select name="category" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                <option value="">Semua Kategori</option>
                                @foreach($categories as $category)
                                    @if($category->type === 'activity')
                                        <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                        </div>

                        <!-- Date Range -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal</label>
                            <div class="space-y-2">
                                <input type="date" name="start_date" value="{{ request('start_date') }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                <input type="date" name="end_date" value="{{ request('end_date') }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                            </div>
                        </div>

                        <!-- Price Range -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Harga</label>
                            <div class="space-y-2">
                                <input type="number" name="min_price" value="{{ request('min_price') }}"
                                       placeholder="Min"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                <input type="number" name="max_price" value="{{ request('max_price') }}"
                                       placeholder="Max"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                            </div>
                        </div>

                        <!-- Location -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Lokasi</label>
                            <input type="text" name="location" value="{{ request('location') }}"
                                   placeholder="Kota atau area..."
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                        </div>

                        <!-- Sort -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Urutkan</label>
                            <select name="sort" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                <option value="date_asc" {{ request('sort') === 'date_asc' ? 'selected' : '' }}>Tanggal Terdekat</option>
                                <option value="date_desc" {{ request('sort') === 'date_desc' ? 'selected' : '' }}>Tanggal Terjauh</option>
                                <option value="price_low" {{ request('sort') === 'price_low' ? 'selected' : '' }}>Harga Terendah</option>
                                <option value="price_high" {{ request('sort') === 'price_high' ? 'selected' : '' }}>Harga Tertinggi</option>
                                <option value="rating" {{ request('sort') === 'rating' ? 'selected' : '' }}>Rating Tertinggi</option>
                            </select>
                        </div>

                        <div class="flex gap-2">
                            <button type="submit" class="flex-1 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                                Terapkan
                            </button>
                            <a href="{{ route('activities.index') }}" class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-lg font-medium transition-colors text-center">
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
                            {{ $activities->total() }} Kegiatan ditemukan
                        </h2>
                        @if(request()->hasAny(['search', 'category', 'start_date', 'end_date', 'min_price', 'max_price', 'location']))
                            <p class="text-sm text-gray-600 mt-1">Hasil berdasarkan filter yang dipilih</p>
                        @endif
                    </div>
                </div>

                <!-- Activities Grid -->
                @if($activities->count() > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                        @foreach($activities as $activity)
                        <div class="bg-white rounded-lg shadow-sm overflow-hidden hover:shadow-md transition-shadow duration-200">
                            <div class="h-48 bg-gray-200 relative">
                                @if($activity->images && count($activity->images) > 0)
                                    <img src="{{ asset('storage/' . $activity->images[0]) }}"
                                         alt="{{ $activity->name }}"
                                         class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center">
                                        <i class="fas fa-calendar-alt text-4xl text-gray-400"></i>
                                    </div>
                                @endif
                                <div class="absolute top-4 right-4 flex flex-col gap-2">
                                    <span class="bg-green-600 text-white px-2 py-1 rounded-full text-xs font-medium">
                                        {{ $activity->available_slots }} slot tersisa
                                    </span>
                                    @auth
                                        @if(auth()->user()->hasRole('user'))
                                            @php
                                                $isBookmarked = auth()->user()->bookmarks()
                                                    ->where('bookmarkable_type', 'App\\Models\\Activity')
                                                    ->where('bookmarkable_id', $activity->id)
                                                    ->exists();
                                            @endphp
                                            <button onclick="toggleBookmark('activity', {{ $activity->id }}, this)"
                                                    class="bg-white hover:bg-gray-100 text-gray-700 p-2 rounded-full shadow-md transition-colors {{ $isBookmarked ? 'text-red-500' : 'text-gray-400' }}">
                                                <i class="fas fa-heart {{ $isBookmarked ? 'fas' : 'far' }}"></i>
                                            </button>
                                        @endif
                                    @endauth
                                </div>
                                @if($activity->discount_type && $activity->discount_value)
                                    <div class="absolute top-4 left-4">
                                        <span class="bg-red-500 text-white px-2 py-1 rounded-full text-xs font-medium">
                                            @if($activity->discount_type === 'percentage')
                                                {{ $activity->discount_value }}% OFF
                                            @else
                                                Rp {{ number_format($activity->discount_value) }} OFF
                                            @endif
                                        </span>
                                    </div>
                                @endif
                            </div>
                            <div class="p-6">
                                <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ $activity->name }}</h3>
                                <p class="text-gray-600 text-sm mb-3">{{ Str::limit($activity->description, 100) }}</p>

                                <div class="flex items-center text-sm text-gray-500 mb-3">
                                    <i class="fas fa-calendar mr-2"></i>
                                    <span>{{ $activity->event_date->format('d M Y') }}</span>
                                </div>

                                <div class="flex items-center text-sm text-gray-500 mb-3">
                                    <i class="fas fa-map-marker-alt mr-2"></i>
                                    <span>{{ Str::limit($activity->location, 30) }}</span>
                                </div>

                                <div class="flex items-center justify-between mb-3">
                                    <div class="flex items-center">
                                        <i class="fas fa-clock mr-2 text-gray-400"></i>
                                        <span class="text-sm text-gray-600">
                                            Daftar sampai {{ $activity->registration_deadline->format('d M Y') }}
                                        </span>
                                    </div>
                                    @if($activity->ratings_avg_rating)
                                        <div class="flex items-center">
                                            <i class="fas fa-star text-yellow-400 mr-1"></i>
                                            <span class="text-sm font-medium">{{ number_format($activity->ratings_avg_rating, 1) }}</span>
                                            <span class="text-xs text-gray-500 ml-1">({{ $activity->ratings_count }})</span>
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
                                        @if($activity->discount_type && $activity->discount_value)
                                            <div class="text-sm text-gray-500 line-through">
                                                Rp {{ number_format($activity->price) }}
                                            </div>
                                            <div class="text-xl font-bold text-green-600">
                                                Rp {{ number_format($activity->getDiscountedPrice()) }}
                                            </div>
                                        @else
                                            <div class="text-xl font-bold text-green-600">
                                                Rp {{ number_format($activity->price) }}
                                            </div>
                                        @endif
                                    </div>
                                    <a href="{{ route('activities.show', $activity) }}"
                                       class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                                        Lihat Detail
                                    </a>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <!-- Pagination -->
                    <div class="mt-8">
                        {{ $activities->appends(request()->query())->links() }}
                    </div>
                @else
                    <div class="text-center py-12">
                        <i class="fas fa-calendar-alt text-gray-400 text-6xl mb-4"></i>
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">Tidak ada kegiatan ditemukan</h3>
                        <p class="text-gray-600 mb-6">Coba ubah filter pencarian atau kata kunci Anda.</p>
                        <a href="{{ route('activities.index') }}"
                           class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-medium transition-colors">
                            Lihat Semua Kegiatan
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
