@extends('layouts.app')

@section('title', 'Kelola Hunian')

@section('content')
    <div class="min-h-screen bg-gray-50 py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Kelola Hunian</h1>
                    <p class="text-gray-600 mt-2">Kelola semua hunian yang Anda tawarkan</p>
                </div>
                <a href="{{ route('provider.residence.residences.create') }}"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium transition-colors">
                    <i class="fas fa-plus mr-2"></i>Tambah Hunian
                </a>
            </div>

            <!-- Filter and Search -->
            <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                <form method="GET" action="{{ route('provider.residence.residences.index') }}"
                    class="flex flex-col md:flex-row gap-4">
                    <div class="flex-1">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari hunian..."
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div>
                        <select name="category_id"
                            class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">Semua Kategori</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}"
                                    {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <select name="status"
                            class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">Semua Status</option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Tidak Aktif
                            </option>
                        </select>
                    </div>
                    <div class="flex gap-2">
                        <button type="submit"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium transition-colors">
                            <i class="fas fa-search mr-2"></i>Cari
                        </button>
                        @if (request()->hasAny(['search', 'category_id', 'status']))
                            <a href="{{ route('provider.residence.residences.index') }}"
                                class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-2 rounded-lg font-medium transition-colors">
                                Reset
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            <!-- Residence Grid -->
            @if ($residences->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach ($residences as $residence)
                        <div
                            class="bg-white rounded-lg shadow-sm overflow-hidden hover:shadow-md transition-shadow duration-200">
                            <div class="h-48 bg-gray-200 relative">
                                @if ($residence->images && count($residence->images) > 0)
                                    <img src="{{ asset('storage/' . $residence->images[0]) }}" alt="{{ $residence->name }}"
                                        class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center">
                                        <i class="fas fa-home text-4xl text-gray-400"></i>
                                    </div>
                                @endif

                                <!-- Status Badge -->
                                <div class="absolute top-4 left-4">
                                    <span
                                        class="px-2 py-1 rounded-full text-xs font-medium {{ $residence->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $residence->is_active ? 'Aktif' : 'Tidak Aktif' }}
                                    </span>
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
                                            <a href="{{ route('provider.residence.residences.show', $residence) }}"
                                                class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                <i class="fas fa-eye mr-2"></i>Lihat Detail
                                            </a>
                                            <a href="{{ route('provider.residence.residences.edit', $residence) }}"
                                                class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                <i class="fas fa-edit mr-2"></i>Edit
                                            </a>
                                            <form method="POST"
                                                action="{{ route('provider.residence.residences.toggleStatus', $residence) }}"
                                                class="block">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit"
                                                    class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                    <i
                                                        class="fas fa-{{ $residence->is_active ? 'pause' : 'play' }} mr-2"></i>
                                                    {{ $residence->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                                </button>
                                            </form>
                                            <form method="POST"
                                                action="{{ route('provider.residence.residences.destroy', $residence) }}"
                                                class="block"
                                                onsubmit="return confirm('Yakin ingin menghapus hunian ini?')">
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
                            </div>

                            <div class="p-6">
                                <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ $residence->name }}</h3>
                                <p class="text-gray-600 text-sm mb-3">{{ Str::limit($residence->description, 100) }}</p>

                                <div class="flex items-center text-sm text-gray-500 mb-3">
                                    <i class="fas fa-map-marker-alt mr-2"></i>
                                    <span>{{ Str::limit($residence->address, 40) }}</span>
                                </div>

                                <div class="flex items-center justify-between mb-3">
                                    <div class="flex items-center">
                                        <i class="fas fa-calendar mr-2 text-gray-400"></i>
                                        <span class="text-sm text-gray-600">{{ ucfirst($residence->rental_period) }}</span>
                                    </div>
                                    <div class="flex items-center">
                                        <i class="fas fa-users mr-2 text-gray-400"></i>
                                        <span
                                            class="text-sm text-gray-600">{{ $residence->available_slots }}/{{ $residence->capacity }}</span>
                                    </div>
                                </div>

                                <div class="flex items-center justify-between">
                                    <div>
                                        @if ($residence->discount_type && $residence->discount_value)
                                            <div class="text-sm text-gray-500 line-through">
                                                Rp {{ number_format($residence->price, 0, ',', '.') }}
                                            </div>
                                            <div class="text-xl font-bold text-blue-600">
                                                Rp {{ number_format($residence->getDiscountedPrice(), 0, ',', '.') }}
                                            </div>
                                        @else
                                            <div class="text-xl font-bold text-blue-600">
                                                Rp {{ number_format($residence->price, 0, ',', '.') }}/bulan
                                            </div>
                                        @endif
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <a href="{{ route('provider.residence.residences.edit', $residence) }}"
                                            class="text-blue-600 hover:text-blue-700">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="{{ route('provider.residence.residences.show', $residence) }}"
                                            class="text-green-600 hover:text-green-700">
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
                    {{ $residences->appends(request()->query())->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <i class="fas fa-home text-gray-400 text-6xl mb-4"></i>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">
                        @if (request()->hasAny(['search', 'category_id', 'status']))
                            Tidak ada hunian ditemukan
                        @else
                            Belum ada hunian
                        @endif
                    </h3>
                    <p class="text-gray-600 mb-6">
                        @if (request()->hasAny(['search', 'category_id', 'status']))
                            Coba ubah filter pencarian Anda.
                        @else
                            Mulai dengan menambahkan hunian pertama Anda.
                        @endif
                    </p>
                    <a href="{{ route('provider.residence.residences.create') }}"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium transition-colors">
                        <i class="fas fa-plus mr-2"></i>Tambah Hunian
                    </a>
                </div>
            @endif
        </div>
    </div>

    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
@endsection
