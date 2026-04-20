@extends('layouts.app')

@php
use App\Models\Bookmark;
use Illuminate\Support\Facades\Auth;
@endphp

@section('title', 'Bookmark - Infoma')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Bookmark Saya</h1>
            <p class="text-gray-600 mt-2">Item yang telah Anda simpan untuk referensi</p>
        </div>

        <!-- Filter Tabs -->
        <div class="bg-white rounded-lg shadow-sm mb-6">
            <div class="border-b border-gray-200">
                <nav class="-mb-px flex space-x-8 px-6">
                    <a href="{{ route('user.bookmarks.index', ['type' => 'all']) }}"
                       class="py-4 px-1 border-b-2 font-medium text-sm {{ request('type') === 'all' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                        Semua ({{ Bookmark::where('user_id', Auth::id())->whereIn('bookmarkable_type', ['App\\Models\\Residence', 'App\\Models\\Activity', 'App\\Models\\MarketplaceProduct'])->count() }})
                    </a>
                    <a href="{{ route('user.bookmarks.index', ['type' => 'residence']) }}"
                       class="py-4 px-1 border-b-2 font-medium text-sm {{ request('type') === 'residence' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                        Residence ({{ Bookmark::where('user_id', Auth::id())->where('bookmarkable_type', 'App\\Models\\Residence')->count() }})
                    </a>
                    <a href="{{ route('user.bookmarks.index', ['type' => 'activity']) }}"
                       class="py-4 px-1 border-b-2 font-medium text-sm {{ request('type') === 'activity' ? 'border-green-500 text-green-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                        Kegiatan ({{ Bookmark::where('user_id', Auth::id())->where('bookmarkable_type', 'App\\Models\\Activity')->count() }})
                    </a>
                    <a href="{{ route('user.bookmarks.index', ['type' => 'marketplace']) }}"
                       class="py-4 px-1 border-b-2 font-medium text-sm {{ request('type') === 'marketplace' ? 'border-orange-500 text-orange-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                        Marketplace ({{ Bookmark::where('user_id', Auth::id())->where('bookmarkable_type', 'App\\Models\\MarketplaceProduct')->count() }})
                    </a>
                </nav>
            </div>
        </div>

        <!-- Bookmarks Grid -->
        @if($bookmarks->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($bookmarks as $bookmark)
                <div class="bg-white rounded-lg shadow-sm overflow-hidden hover:shadow-md transition-shadow duration-200"
                     data-bookmark-id="{{ $bookmark->id }}"
                     data-type="{{ $bookmark->bookmarkable_type === 'App\\Models\\MarketplaceProduct' ? 'marketplace' : ($bookmark->bookmarkable_type === 'App\\Models\\Residence' ? 'residence' : 'activity') }}"
                     data-id="{{ $bookmark->bookmarkable_id }}">
                    <div class="h-48 bg-gray-200 relative">
                        @if($bookmark->bookmarkable->images && count($bookmark->bookmarkable->images) > 0)
                            <img src="{{ $bookmark->bookmarkable_type === 'App\\Models\\MarketplaceProduct' ? $bookmark->bookmarkable->main_image : asset('storage/' . $bookmark->bookmarkable->images[0]) }}"
                                 alt="{{ $bookmark->bookmarkable->name }}"
                                 class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex items-center justify-center">
                                <i class="fas fa-{{ $bookmark->bookmarkable_type === 'App\\Models\\MarketplaceProduct' ? 'shopping-bag' : ($bookmark->bookmarkable_type === 'App\\Models\\Residence' ? 'building' : 'calendar-alt') }} text-4xl text-gray-400"></i>
                            </div>
                        @endif

                        <!-- Bookmark Button -->
                        <button onclick="removeBookmark({{ $bookmark->id }})"
                                class="absolute top-4 right-4 bg-red-500 hover:bg-red-600 text-white p-2 rounded-full transition-colors">
                            <i class="fas fa-heart text-sm"></i>
                        </button>

                        <!-- Type Badge -->
                        <div class="absolute top-4 left-4">
                            <span class="bg-{{ $bookmark->bookmarkable_type === 'App\\Models\\Residence' ? 'blue' : 'green' }}-600 text-white px-2 py-1 rounded-full text-xs font-medium">
                                {{ $bookmark->bookmarkable_type === 'App\\Models\\Residence' ? 'Residence' : 'Kegiatan' }}
                            </span>
                        </div>

                        <!-- Availability Badge -->
                        @if($bookmark->bookmarkable_type === 'App\\Models\\MarketplaceProduct')
                            <div class="absolute bottom-4 right-4">
                                <span class="bg-orange-600 text-white px-2 py-1 rounded-full text-xs font-medium">
                                    Stok: {{ $bookmark->bookmarkable->stock_quantity }}
                                </span>
                            </div>
                        @elseif($bookmark->bookmarkable_type === 'App\\Models\\Residence')
                            <div class="absolute bottom-4 right-4">
                                <span class="bg-blue-600 text-white px-2 py-1 rounded-full text-xs font-medium">
                                    {{ $bookmark->bookmarkable->available_slots }} tersedia
                                </span>
                            </div>
                        @else
                            <div class="absolute bottom-4 right-4">
                                <span class="bg-green-600 text-white px-2 py-1 rounded-full text-xs font-medium">
                                    {{ $bookmark->bookmarkable->available_slots }} slot tersisa
                                </span>
                            </div>
                        @endif
                    </div>

                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ $bookmark->bookmarkable->name }}</h3>
                        <p class="text-gray-600 text-sm mb-3">{{ Str::limit($bookmark->bookmarkable->description, 100) }}</p>

                        @if($bookmark->bookmarkable_type === 'App\\Models\\MarketplaceProduct')
                            <div class="flex items-center text-sm text-gray-500 mb-3">
                                <i class="fas fa-map-marker-alt mr-2"></i>
                                <span>{{ Str::limit($bookmark->bookmarkable->location, 30) }}</span>
                            </div>

                            <div class="flex items-center justify-between mb-3">
                                <div class="flex items-center">
                                    <i class="fas fa-tag mr-2 text-gray-400"></i>
                                    <span class="text-sm text-gray-600">{{ $bookmark->bookmarkable->condition_label }}</span>
                                </div>
                                <div class="flex items-center">
                                    <i class="fas fa-eye text-gray-400 mr-1"></i>
                                    <span class="text-sm font-medium">{{ $bookmark->bookmarkable->views_count }}</span>
                                </div>
                            </div>

                            <div class="flex items-center justify-between">
                                <div>
                                    @if($bookmark->bookmarkable->discount_percentage > 0)
                                        <div class="text-sm text-gray-500 line-through">
                                            Rp {{ number_format($bookmark->bookmarkable->original_price) }}
                                        </div>
                                        <div class="text-xl font-bold text-orange-600">
                                            Rp {{ number_format($bookmark->bookmarkable->price) }}
                                        </div>
                                    @else
                                        <div class="text-xl font-bold text-orange-600">
                                            Rp {{ number_format($bookmark->bookmarkable->price) }}
                                        </div>
                                    @endif
                                </div>
                                <a href="{{ route('marketplace.show', $bookmark->bookmarkable) }}"
                                   class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                                    Lihat Detail
                                </a>
                            </div>

                        @elseif($bookmark->bookmarkable_type === 'App\\Models\\Residence')
                            <div class="flex items-center text-sm text-gray-500 mb-3">
                                <i class="fas fa-map-marker-alt mr-2"></i>
                                <span>{{ Str::limit($bookmark->bookmarkable->address, 30) }}</span>
                            </div>

                            <div class="flex items-center justify-between mb-3">
                                <div class="flex items-center">
                                    <i class="fas fa-calendar mr-2 text-gray-400"></i>
                                    <span class="text-sm text-gray-600">{{ ucfirst($bookmark->bookmarkable->rental_period) }}</span>
                                </div>
                                @if($bookmark->bookmarkable->ratings_avg_rating)
                                    <div class="flex items-center">
                                        <i class="fas fa-star text-yellow-400 mr-1"></i>
                                        <span class="text-sm font-medium">{{ number_format($bookmark->bookmarkable->ratings_avg_rating, 1) }}</span>
                                    </div>
                                @endif
                            </div>

                            <div class="flex items-center justify-between">
                                <div>
                                    @if($bookmark->bookmarkable->discount_type && $bookmark->bookmarkable->discount_value)
                                        <div class="text-sm text-gray-500 line-through">
                                            Rp {{ number_format($bookmark->bookmarkable->price_per_month) }}
                                        </div>
                                        <div class="text-xl font-bold text-blue-600">
                                            Rp {{ number_format($bookmark->bookmarkable->getDiscountedPrice()) }}
                                        </div>
                                    @else
                                        <div class="text-xl font-bold text-blue-600">
                                            Rp {{ number_format($bookmark->bookmarkable->price_per_month) }}/bulan
                                        </div>
                                    @endif
                                </div>
                                <a href="{{ route('residences.show', $bookmark->bookmarkable) }}"
                                   class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                                    Lihat Detail
                                </a>
                            </div>
                        @else
                            <div class="flex items-center text-sm text-gray-500 mb-3">
                                <i class="fas fa-calendar mr-2"></i>
                                <span>{{ $bookmark->bookmarkable->event_date->format('d M Y') }}</span>
                            </div>

                            <div class="flex items-center text-sm text-gray-500 mb-3">
                                <i class="fas fa-map-marker-alt mr-2"></i>
                                <span>{{ Str::limit($bookmark->bookmarkable->location, 30) }}</span>
                            </div>

                            <div class="flex items-center justify-between mb-3">
                                <div class="flex items-center">
                                    <i class="fas fa-clock mr-2 text-gray-400"></i>
                                    <span class="text-sm text-gray-600">
                                        Daftar sampai {{ $bookmark->bookmarkable->registration_deadline->format('d M Y') }}
                                    </span>
                                </div>
                                @if($bookmark->bookmarkable->ratings_avg_rating)
                                    <div class="flex items-center">
                                        <i class="fas fa-star text-yellow-400 mr-1"></i>
                                        <span class="text-sm font-medium">{{ number_format($bookmark->bookmarkable->ratings_avg_rating, 1) }}</span>
                                    </div>
                                @endif
                            </div>

                            <div class="flex items-center justify-between">
                                <div>
                                    @if($bookmark->bookmarkable->discount_type && $bookmark->bookmarkable->discount_value)
                                        <div class="text-sm text-gray-500 line-through">
                                            Rp {{ number_format($bookmark->bookmarkable->price) }}
                                        </div>
                                        <div class="text-xl font-bold text-green-600">
                                            Rp {{ number_format($bookmark->bookmarkable->getDiscountedPrice()) }}
                                        </div>
                                    @else
                                        <div class="text-xl font-bold text-green-600">
                                            Rp {{ number_format($bookmark->bookmarkable->price) }}
                                        </div>
                                    @endif
                                </div>
                                <a href="{{ route('activities.show', $bookmark->bookmarkable) }}"
                                   class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                                    Lihat Detail
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-8">
                {{ $bookmarks->appends(request()->query())->links() }}
            </div>
        @else
            <div class="text-center py-12">
                <i class="fas fa-heart text-gray-400 text-6xl mb-4"></i>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">
                    @if(request('type') === 'residence')
                        Belum ada residence yang disimpan
                    @elseif(request('type') === 'activity')
                        Belum ada kegiatan yang disimpan
                    @elseif(request('type') === 'marketplace')
                        Belum ada produk yang disimpan
                    @else
                        Belum ada bookmark
                    @endif
                </h3>
                <p class="text-gray-600 mb-6">
                    @if(request('type') === 'residence')
                        Simpan residence favorit Anda untuk akses cepat.
                    @elseif(request('type') === 'activity')
                        Simpan kegiatan menarik untuk referensi nanti.
                    @elseif(request('type') === 'marketplace')
                        Simpan produk favorit Anda untuk dilihat nanti.
                    @else
                        Mulai simpan residence, kegiatan, dan produk favorit Anda.
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

@push('scripts')
<script>
function removeBookmark(bookmarkId) {
    if (confirm('Apakah Anda yakin ingin menghapus item ini dari bookmark?')) {
        // Get the bookmark data from the DOM
        const bookmarkElement = document.querySelector(`[data-bookmark-id="${bookmarkId}"]`);
        const type = bookmarkElement.dataset.type;
        const id = bookmarkElement.dataset.id;

        fetch('{{ route("user.bookmarks.destroy") }}', {
            method: 'DELETE',
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
                location.reload();
            } else {
                alert('Gagal menghapus bookmark. Silakan coba lagi.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan. Silakan coba lagi.');
        });
    }
}
</script>
@endpush
@endsection
