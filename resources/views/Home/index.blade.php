@extends('layouts.app')

@section('title', 'Infoma - Informasi Kebutuhan Mahasiswa')

@section('content')
    <!-- Hero Section -->
    <section id="home" class="bg-gradient-to-br from-blue-50 to-white py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                <div class="text-center lg:text-left">
                    <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-6 leading-tight">
                        Temukan <span class="text-blue-900">Residence</span> dan
                        <span class="text-blue-800">Kegiatan Kampus</span> Terbaik
                    </h1>
                    <p class="text-xl text-gray-600 mb-8 leading-relaxed">
                        Platform lengkap untuk mahasiswa dalam mencari kost, kontrakan, serta informasi kegiatan kampus
                        seperti seminar, webinar, dan lomba.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                        @auth
                            <a href="{{ route('residences.index') }}"
                                class="bg-blue-800 hover:bg-secondary text-white px-8 py-4 rounded-lg font-semibold text-lg transition-all duration-300 transform hover:scale-105 shadow-lg">
                                <i class="fas fa-building mr-2"></i>Lihat Residence
                            </a>
                            <a href="{{ route('activities.index') }}"
                                class="border-2 border-blue-800 text-blue-800 hover:bg-blue-800 hover:text-white px-8 py-4 rounded-lg font-semibold text-lg transition-all duration-300">
                                <i class="fas fa-calendar-alt mr-2"></i>Lihat Kegiatan
                            </a>
                        @else
                            <a href="{{ route('register') }}"
                                class="bg-blue-800 hover:bg-secondary text-white px-8 py-4 rounded-lg font-semibold text-lg transition-all duration-300 transform hover:scale-105 shadow-lg">
                                <i class="fas fa-rocket mr-2"></i>Mulai Sekarang
                            </a>
                            <a href="{{ route('login') }}"
                                class="border-2 border-blue-800 text-blue-800 hover:bg-blue-800 hover:text-white px-8 py-4 rounded-lg font-semibold text-lg transition-all duration-300">
                                <i class="fas fa-sign-in-alt mr-2"></i>Masuk
                            </a>
                        @endauth
                    </div>
                </div>
                <div class="text-center">
                    <div class="relative">
                        <div class="bg-gradient-to-r from-blue-700 to-blue-900 rounded-2xl p-8 shadow-2xl">
                            <div class="grid grid-cols-2 gap-4 text-white">
                                <div class="bg-white bg-opacity-20 rounded-lg p-4 backdrop-blur-sm">
                                    <i class="fas fa-home text-3xl mb-2"></i>
                                    <h3 class="font-bold">1000+</h3>
                                    <p class="text-sm">Residence</p>
                                </div>
                                <div class="bg-white bg-opacity-20 rounded-lg p-4 backdrop-blur-sm">
                                    <i class="fas fa-calendar text-3xl mb-2"></i>
                                    <h3 class="font-bold">500+</h3>
                                    <p class="text-sm">Kegiatan</p>
                                </div>
                                <div class="bg-white bg-opacity-20 rounded-lg p-4 backdrop-blur-sm">
                                    <i class="fas fa-users text-3xl mb-2"></i>
                                    <h3 class="font-bold">5000+</h3>
                                    <p class="text-sm">Mahasiswa</p>
                                </div>
                                <div class="bg-white bg-opacity-20 rounded-lg p-4 backdrop-blur-sm">
                                    <i class="fas fa-star text-3xl mb-2"></i>
                                    <h3 class="font-bold">4.8</h3>
                                    <p class="text-sm">Rating</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Residences Section -->
    @if($featuredResidences->count() > 0)
    <section id="residences" class="py-16 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-900 mb-4">Residence Terpopuler</h2>
                <p class="text-lg text-gray-600">Temukan tempat tinggal terbaik untuk kebutuhan Anda</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach($featuredResidences as $residence)
                <div class="bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition-shadow duration-300">
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
                    </div>
                    <div class="p-6">
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">{{ $residence->name }}</h3>
                        <p class="text-gray-600 text-sm mb-3">{{ Str::limit($residence->description, 100) }}</p>
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center">
                                <i class="fas fa-map-marker-alt text-gray-400 mr-1"></i>
                                <span class="text-sm text-gray-600">{{ $residence->address }}</span>
                            </div>
                            @if($residence->ratings_avg_rating)
                                <div class="flex items-center">
                                    <i class="fas fa-star text-yellow-400 mr-1"></i>
                                    <span class="text-sm font-medium">{{ number_format($residence->ratings_avg_rating, 1) }}</span>
                                </div>
                            @endif
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-2xl font-bold text-blue-600">Rp {{ number_format($residence->price_per_month) }}/bulan</span>
                            <a href="{{ route('residences.show', $residence) }}"
                               class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                                Lihat Detail
                            </a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <div class="text-center mt-8">
                <a href="{{ route('residences.index') }}"
                   class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium transition-colors">
                    Lihat Semua Residence
                </a>
            </div>
        </div>
    </section>
    @endif

    <!-- Featured Activities Section -->
    @if($featuredActivities->count() > 0)
    <section id="activities" class="py-16 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-900 mb-4">Kegiatan Terbaru</h2>
                <p class="text-lg text-gray-600">Ikuti kegiatan kampus yang menarik dan bermanfaat</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach($featuredActivities as $activity)
                <div class="bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition-shadow duration-300">
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
                    </div>
                    <div class="p-6">
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">{{ $activity->name }}</h3>
                        <p class="text-gray-600 text-sm mb-3">{{ Str::limit($activity->description, 100) }}</p>
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center">
                                <i class="fas fa-calendar text-gray-400 mr-1"></i>
                                <span class="text-sm text-gray-600">{{ $activity->event_date->format('d M Y') }}</span>
                            </div>
                            @if($activity->ratings_avg_rating)
                                <div class="flex items-center">
                                    <i class="fas fa-star text-yellow-400 mr-1"></i>
                                    <span class="text-sm font-medium">{{ number_format($activity->ratings_avg_rating, 1) }}</span>
                                </div>
                            @endif
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-2xl font-bold text-green-600">Rp {{ number_format($activity->price) }}</span>
                            <a href="{{ route('activities.show', $activity) }}"
                               class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                                Lihat Detail
                            </a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <div class="text-center mt-8">
                <a href="{{ route('activities.index') }}"
                   class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-medium transition-colors">
                    Lihat Semua Kegiatan
                </a>
            </div>
        </div>
    </section>
    @endif

    <!-- Categories Section -->
    <!-- @if($categories->count() > 0)
    <section id="categories" class="py-16 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-900 mb-4">Kategori Populer</h2>
                <p class="text-lg text-gray-600">Jelajahi berdasarkan kategori yang Anda minati</p>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-6">
                @foreach($categories as $category)
                <div class="text-center group cursor-pointer">
                    <div class="bg-blue-100 group-hover:bg-blue-200 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-3 transition-colors">
                        <i class="fas fa-{{ $category->icon ?? 'tag' }} text-blue-600 text-xl"></i>
                    </div>
                    <h3 class="text-sm font-medium text-gray-900 group-hover:text-blue-600 transition-colors">
                        {{ $category->name }}
                    </h3>
                </div>
                @endforeach
            </div>
        </div>
    </section> -->
    @endif

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
