@extends('layouts.app')

@section('title', 'Pencarian - Infoma')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Search Header -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-8">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Hasil Pencarian</h1>
                    <p class="text-gray-600 mt-1">
                        @if($query)
                            Menampilkan hasil untuk "<span class="font-medium text-blue-600">{{ $query }}</span>"
                        @else
                            Semua hasil pencarian
                        @endif
                    </p>
                </div>

                <!-- Search Form -->
                <form method="GET" action="{{ route('search') }}" class="flex gap-2">
                    <div class="flex-1">
                        <input type="text" name="q" value="{{ $query }}"
                               placeholder="Cari residence atau kegiatan..."
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <select name="type" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="all" {{ $type === 'all' ? 'selected' : '' }}>Semua</option>
                        <option value="residence" {{ $type === 'residence' ? 'selected' : '' }}>Residence</option>
                        <option value="activity" {{ $type === 'activity' ? 'selected' : '' }}>Kegiatan</option>
                    </select>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium transition-colors">
                        <i class="fas fa-search mr-2"></i>Cari
                    </button>
                </form>
            </div>
        </div>

        <!-- Results -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Residences Results -->
            @if($residences && $residences->count() > 0)
            <div class="lg:col-span-2">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-semibold text-gray-900">
                        <i class="fas fa-building mr-2 text-blue-600"></i>Residence ({{ $residences->total() }})
                    </h2>
                </div>

                <div class="space-y-6">
                    @foreach($residences as $residence)
                    <div class="bg-white rounded-lg shadow-sm overflow-hidden hover:shadow-md transition-shadow duration-200">
                        <div class="md:flex">
                            <div class="md:w-1/3">
                                <div class="h-48 md:h-full bg-gray-200 relative">
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
                                </div>
                            </div>
                            <div class="md:w-2/3 p-6">
                                <div class="flex items-start justify-between mb-2">
                                    <h3 class="text-xl font-semibold text-gray-900">{{ $residence->name }}</h3>
                                    @if($residence->ratings_avg_rating)
                                        <div class="flex items-center">
                                            <i class="fas fa-star text-yellow-400 mr-1"></i>
                                            <span class="text-sm font-medium">{{ number_format($residence->ratings_avg_rating, 1) }}</span>
                                        </div>
                                    @endif
                                </div>

                                <p class="text-gray-600 text-sm mb-3">{{ Str::limit($residence->description, 150) }}</p>

                                <div class="flex items-center text-sm text-gray-500 mb-3">
                                    <i class="fas fa-map-marker-alt mr-2"></i>
                                    <span>{{ $residence->address }}</span>
                                </div>

                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-4">
                                        <span class="text-2xl font-bold text-blue-600">
                                            Rp {{ number_format($residence->price_per_month) }}/bulan
                                        </span>
                                        @if($residence->discount_type && $residence->discount_value)
                                            <span class="text-sm text-green-600 font-medium">
                                                @if($residence->discount_type === 'percentage')
                                                    {{ $residence->discount_value }}% off
                                                @else
                                                    Rp {{ number_format($residence->discount_value) }} off
                                                @endif
                                            </span>
                                        @endif
                                    </div>
                                    <a href="{{ route('residences.show', $residence) }}"
                                       class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                                        Lihat Detail
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="mt-6">
                    {{ $residences->appends(request()->query())->links() }}
                </div>
            </div>
            @endif

            <!-- Activities Results -->
            @if($activities && $activities->count() > 0)
            <div class="lg:col-span-2">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-semibold text-gray-900">
                        <i class="fas fa-calendar-alt mr-2 text-green-600"></i>Kegiatan ({{ $activities->total() }})
                    </h2>
                </div>

                <div class="space-y-6">
                    @foreach($activities as $activity)
                    <div class="bg-white rounded-lg shadow-sm overflow-hidden hover:shadow-md transition-shadow duration-200">
                        <div class="md:flex">
                            <div class="md:w-1/3">
                                <div class="h-48 md:h-full bg-gray-200 relative">
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
                                </div>
                            </div>
                            <div class="md:w-2/3 p-6">
                                <div class="flex items-start justify-between mb-2">
                                    <h3 class="text-xl font-semibold text-gray-900">{{ $activity->name }}</h3>
                                    @if($activity->ratings_avg_rating)
                                        <div class="flex items-center">
                                            <i class="fas fa-star text-yellow-400 mr-1"></i>
                                            <span class="text-sm font-medium">{{ number_format($activity->ratings_avg_rating, 1) }}</span>
                                        </div>
                                    @endif
                                </div>

                                <p class="text-gray-600 text-sm mb-3">{{ Str::limit($activity->description, 150) }}</p>

                                <div class="flex items-center text-sm text-gray-500 mb-3">
                                    <i class="fas fa-calendar mr-2"></i>
                                    <span>{{ $activity->event_date->format('d M Y') }}</span>
                                    <i class="fas fa-map-marker-alt ml-4 mr-2"></i>
                                    <span>{{ $activity->location }}</span>
                                </div>

                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-4">
                                        <span class="text-2xl font-bold text-green-600">
                                            Rp {{ number_format($activity->price) }}
                                        </span>
                                        @if($activity->discount_type && $activity->discount_value)
                                            <span class="text-sm text-green-600 font-medium">
                                                @if($activity->discount_type === 'percentage')
                                                    {{ $activity->discount_value }}% off
                                                @else
                                                    Rp {{ number_format($activity->discount_value) }} off
                                                @endif
                                            </span>
                                        @endif
                                    </div>
                                    <a href="{{ route('activities.show', $activity) }}"
                                       class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                                        Lihat Detail
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="mt-6">
                    {{ $activities->appends(request()->query())->links() }}
                </div>
            </div>
            @endif

            <!-- No Results -->
            @if((!$residences || $residences->count() === 0) && (!$activities || $activities->count() === 0))
            <div class="col-span-full">
                <div class="text-center py-12">
                    <i class="fas fa-search text-gray-400 text-6xl mb-4"></i>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Tidak ada hasil ditemukan</h3>
                    <p class="text-gray-600 mb-6">
                        @if($query)
                            Coba gunakan kata kunci yang berbeda atau periksa ejaan kata kunci Anda.
                        @else
                            Mulai pencarian dengan mengetik kata kunci di atas.
                        @endif
                    </p>
                    <a href="{{ route('home') }}"
                       class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium transition-colors">
                        Kembali ke Beranda
                    </a>
                </div>
            </div>
            @endif
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
