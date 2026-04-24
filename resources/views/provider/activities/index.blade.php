@extends('layouts.app')

@section('title', 'Kelola Kegiatan - Infoma')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Kelola Kegiatan</h1>
                <p class="text-gray-600 mt-2">Kelola semua kegiatan yang Anda selenggarakan</p>
            </div>
            <a href="{{ route('provider.activities.create') }}"
               class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-medium transition-colors">
                <i class="fas fa-plus mr-2"></i>Tambah Kegiatan
            </a>
        </div>

        <!-- Filter and Search -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <form method="GET" action="{{ route('provider.activities.index') }}" class="flex flex-col md:flex-row gap-4">
                <div class="flex-1">
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Cari kegiatan..."
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                </div>
                <div>
                    <select name="category" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
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
                <div>
                    <select name="status" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                        <option value="">Semua Status</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Tidak Aktif</option>
                        <option value="upcoming" {{ request('status') === 'upcoming' ? 'selected' : '' }}>Akan Datang</option>
                        <option value="past" {{ request('status') === 'past' ? 'selected' : '' }}>Sudah Lewat</option>
                    </select>
                </div>
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg font-medium transition-colors">
                    <i class="fas fa-search mr-2"></i>Cari
                </button>
            </form>
        </div>

        <!-- Activities Grid -->
        @if($activities->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
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

                        <!-- Status Badge -->
                        <div class="absolute top-4 left-4">
                            @if($activity->event_date < now())
                                <span class="px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    Sudah Lewat
                                </span>
                            @elseif($activity->registration_deadline < now())
                                <span class="px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    Pendaftaran Ditutup
                                </span>
                            @elseif($activity->is_active)
                                <span class="px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    Aktif
                                </span>
                            @else
                                <span class="px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    Tidak Aktif
                                </span>
                            @endif
                        </div>

                        <!-- Actions Dropdown -->
                        <div class="absolute top-4 right-4">
                            <div class="relative" x-data="{ open: false }">
                                <button @click="open = !open"
                                        class="bg-white bg-opacity-90 hover:bg-opacity-100 p-2 rounded-full shadow-sm">
                                    <i class="fas fa-ellipsis-v text-gray-600"></i>
                                </button>
                                <div x-show="open" @click.away="open = false"
                                     class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-10">
                                    <a href="{{ route('provider.activities.show', $activity) }}"
                                       class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        <i class="fas fa-eye mr-2"></i>Lihat Detail
                                    </a>
                                    <a href="{{ route('provider.activities.edit', $activity) }}"
                                       class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        <i class="fas fa-edit mr-2"></i>Edit
                                    </a>
                                    <form method="POST" action="{{ route('provider.activities.toggleStatus', $activity) }}" class="block">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit"
                                                class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                            <i class="fas fa-{{ $activity->is_active ? 'pause' : 'play' }} mr-2"></i>
                                            {{ $activity->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('provider.activities.destroy', $activity) }}"
                                          class="block" onsubmit="return confirm('Apakah Anda yakin ingin menghapus kegiatan ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="w-full text-left px-4 py-2 text-sm text-red-700 hover:bg-red-100">
                                            <i class="fas fa-trash mr-2"></i>Hapus
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Date Badge -->
                        <div class="absolute bottom-4 right-4">
                            <span class="bg-green-600 text-white px-2 py-1 rounded-full text-xs font-medium">
                                {{ $activity->event_date->format('d M') }}
                            </span>
                        </div>
                    </div>

                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ $activity->name }}</h3>
                        <p class="text-gray-600 text-sm mb-3">{{ Str::limit($activity->description, 100) }}</p>

                        <div class="flex items-center text-sm text-gray-500 mb-3">
                            <i class="fas fa-calendar mr-2"></i>
                            <span>{{ $activity->event_date->format('d M Y, H:i') }}</span>
                        </div>

                        <div class="flex items-center text-sm text-gray-500 mb-3">
                            <i class="fas fa-map-marker-alt mr-2"></i>
                            <span>{{ Str::limit($activity->location, 30) }}</span>
                        </div>

                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center">
                                <i class="fas fa-users mr-2 text-gray-400"></i>
                                <span class="text-sm text-gray-600">{{ $activity->available_slots }}/{{ $activity->capacity }}</span>
                            </div>
                            <div class="flex items-center">
                                <i class="fas fa-clock mr-2 text-gray-400"></i>
                                <span class="text-sm text-gray-600">
                                    Daftar sampai {{ $activity->registration_deadline->format('d M') }}
                                </span>
                            </div>
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
                            <div class="flex items-center space-x-2">
                                <a href="{{ route('provider.activities.edit', $activity) }}"
                                   class="text-green-600 hover:text-green-700">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="{{ route('provider.activities.show', $activity) }}"
                                   class="text-blue-600 hover:text-blue-700">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </div>
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
                <h3 class="text-xl font-semibold text-gray-900 mb-2">
                    @if(request()->hasAny(['search', 'category', 'status']))
                        Tidak ada kegiatan ditemukan
                    @else
                        Belum ada kegiatan
                    @endif
                </h3>
                <p class="text-gray-600 mb-6">
                    @if(request()->hasAny(['search', 'category', 'status']))
                        Coba ubah filter pencarian atau kata kunci Anda.
                    @else
                        Mulai dengan membuat kegiatan pertama Anda.
                    @endif
                </p>
                <a href="{{ route('provider.activities.create') }}"
                   class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-medium transition-colors">
                    <i class="fas fa-plus mr-2"></i>Tambah Kegiatan
                </a>
            </div>
        @endif
    </div>
</div>

<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
@endsection
