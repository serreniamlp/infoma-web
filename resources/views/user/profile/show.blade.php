@extends('layouts.app')

@section('title', 'Profil Saya - Infoma')

@section('content')
<div class="min-h-screen bg-gray-50 py-10">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

        @if(session('success'))
        <div class="mb-6 bg-green-50 border-l-4 border-green-500 p-4 rounded-r shadow-sm">
            <div class="flex">
                <i class="fas fa-check-circle text-green-500 mr-3 mt-0.5"></i>
                <p class="text-sm text-green-700 font-medium">{{ session('success') }}</p>
            </div>
        </div>
        @endif

        @if(session('info'))
        <div class="mb-6 bg-blue-50 border-l-4 border-blue-500 p-4 rounded-r shadow-sm">
            <div class="flex">
                <i class="fas fa-info-circle text-blue-500 mr-3 mt-0.5"></i>
                <p class="text-sm text-blue-700 font-medium">{{ session('info') }}</p>
            </div>
        </div>
        @endif

        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Profil Saya</h1>
                <p class="text-gray-500 mt-1">Lihat dan kelola informasi diri, serta aktivitas Anda</p>
            </div>
            <a href="{{ route('home') }}" class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-gray-700 transition">
                <i class="fas fa-arrow-left mr-2"></i>Kembali ke Beranda
            </a>
        </div>

        {{-- Profile Card --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-8">
            <div class="h-32 bg-gradient-to-r from-blue-600 to-indigo-700"></div>
            <div class="px-6 sm:px-10 relative">
                <div class="-mt-16 sm:-mt-20 sm:flex sm:items-end sm:space-x-5">
                    <div class="relative group inline-block">
                        @if($user->profile_picture)
                            <img class="h-32 w-32 rounded-full ring-4 ring-white object-cover sm:h-40 sm:w-40" src="{{ Storage::url($user->profile_picture) }}" alt="{{ $user->name }}">
                        @else
                            <div class="h-32 w-32 rounded-full ring-4 ring-white bg-blue-600 flex items-center justify-center text-white text-5xl font-bold sm:h-40 sm:w-40">
                                {{ substr(strtoupper($user->name), 0, 1) }}
                            </div>
                        @endif
                    </div>
                    <div class="mt-6 sm:flex-1 sm:min-w-0 sm:flex sm:items-center sm:justify-end sm:space-x-6 sm:pb-1">
                        <div class="sm:hidden 2xl:block mt-6 min-w-0 flex-1">
                            <h1 class="text-2xl font-bold text-gray-900 truncate">{{ $user->name }}</h1>
                            <div class="flex flex-wrap gap-1 mt-1">
                                @foreach($user->roles as $role)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 capitalize border border-indigo-200">
                                    {{ $role->display_name ?? $role->name }}
                                </span>
                                @endforeach
                                @if($user->isSeller())
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800 border border-orange-200">
                                    <i class="fas fa-store mr-1 text-xs"></i> Penjual Marketplace
                                </span>
                                @endif
                            </div>
                        </div>
                        <div class="mt-6 flex flex-col justify-stretch space-y-3 sm:flex-row sm:space-y-0 sm:space-x-4">
                            <a href="{{ route('user.profile.edit') }}" class="inline-flex justify-center items-center px-4 py-2 border border-blue-600 shadow-sm text-sm font-medium rounded-lg text-blue-600 bg-white hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                                <i class="fas fa-pencil-alt mr-2"></i> Edit Profil
                            </a>
                        </div>
                    </div>
                </div>

                <div class="hidden sm:block 2xl:hidden mt-6 min-w-0 flex-1">
                    <h1 class="text-2xl font-bold text-gray-900 truncate">{{ $user->name }}</h1>
                    <div class="flex flex-wrap gap-1 mt-1">
                        @foreach($user->roles as $role)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 capitalize border border-indigo-200">
                            {{ $role->display_name ?? $role->name }}
                        </span>
                        @endforeach
                        @if($user->isSeller())
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800 border border-orange-200">
                            <i class="fas fa-store mr-1 text-xs"></i> Penjual Marketplace
                        </span>
                        @endif
                    </div>
                </div>

                <div class="mt-8 pb-8 grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6 border-t border-gray-100 pt-8">
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 mb-1">Email <i class="fas fa-envelope text-gray-400 ml-1"></i></h3>
                        <p class="text-gray-900 font-medium">{{ $user->email }}</p>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 mb-1">Nomor Telepon <i class="fas fa-phone text-gray-400 ml-1"></i></h3>
                        <p class="text-gray-900 font-medium">{{ $user->phone ?: 'Belum diatur' }}</p>
                    </div>
                    <div class="md:col-span-2">
                        <h3 class="text-sm font-medium text-gray-500 mb-1">Alamat <i class="fas fa-map-marker-alt text-gray-400 ml-1"></i></h3>
                        <p class="text-gray-900 font-medium">{{ $user->address ?: 'Belum diatur' }}</p>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 mb-1">Terdaftar pada <i class="fas fa-calendar-alt text-gray-400 ml-1"></i></h3>
                        <p class="text-gray-900 font-medium">{{ $user->created_at ? $user->created_at->format('d F Y') : '-' }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- ====== MULTI-ROLE SWITCH ====== --}}
        <h2 class="text-xl font-bold text-gray-900 mb-2 flex items-center gap-2">
            <i class="fas fa-exchange-alt text-blue-500"></i> Kelola Peran Akun
        </h2>
        <p class="text-sm text-gray-500 mb-4">Satu akun dapat memiliki beberapa peran sekaligus. Daftarkan diri Anda untuk membuka akses fitur tambahan.</p>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">

            {{-- ── PENJUAL MARKETPLACE ──────────────────────────────────── --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 flex flex-col hover:shadow-md transition-shadow">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-11 h-11 rounded-xl bg-orange-50 flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-store text-orange-500 text-xl"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-900 text-sm leading-tight">Penjual Marketplace</h3>
                        <p class="text-xs text-gray-500">Jual barang bekas mahasiswa</p>
                    </div>
                </div>
                @if($user->isSeller())
                    <div class="flex items-center gap-1.5 mb-3">
                        <span class="w-2 h-2 rounded-full bg-green-500 flex-shrink-0"></span>
                        <span class="text-xs text-green-700 font-semibold">Aktif</span>
                    </div>
                    <a href="{{ route('user.marketplace.seller.home') }}"
                       class="mt-auto w-full text-center py-2.5 px-4 bg-orange-500 hover:bg-orange-600 text-white text-sm font-semibold rounded-xl transition-colors flex items-center justify-center gap-2">
                        <i class="fas fa-tachometer-alt"></i> Dashboard Penjual
                    </a>
                @elseif($user->seller_status === 'pending')
                    <div class="flex items-center gap-1.5 mb-3">
                        <span class="w-2 h-2 rounded-full bg-yellow-400 flex-shrink-0 animate-pulse"></span>
                        <span class="text-xs text-yellow-700 font-medium">Sedang ditinjau admin</span>
                    </div>
                    <div class="mt-auto w-full text-center py-2.5 px-4 bg-gray-100 text-gray-400 text-sm font-medium rounded-xl cursor-not-allowed">
                        <i class="fas fa-clock mr-1"></i> Menunggu Verifikasi
                    </div>
                @else
                    <div class="flex items-center gap-1.5 mb-3">
                        <span class="w-2 h-2 rounded-full bg-gray-300 flex-shrink-0"></span>
                        <span class="text-xs text-gray-400 font-medium">Belum terdaftar</span>
                    </div>
                    <a href="{{ route('user.marketplace.sell') }}"
                       class="mt-auto w-full text-center py-2.5 px-4 border border-orange-300 text-orange-600 hover:bg-orange-500 hover:text-white hover:border-orange-500 text-sm font-semibold rounded-xl transition-all flex items-center justify-center gap-2">
                        <i class="fas fa-plus"></i> Daftar Sekarang
                    </a>
                @endif
            </div>

            {{-- ── PENJUAL EVENT ────────────────────────────────────────── --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 flex flex-col hover:shadow-md transition-shadow">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-11 h-11 rounded-xl bg-purple-50 flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-calendar-alt text-purple-500 text-xl"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-900 text-sm leading-tight">Penjual Event</h3>
                        <p class="text-xs text-gray-500">Selenggarakan & jual tiket event</p>
                    </div>
                </div>
                @if($user->hasRole('provider_event'))
                    <div class="flex items-center gap-1.5 mb-3">
                        <span class="w-2 h-2 rounded-full bg-green-500 flex-shrink-0"></span>
                        <span class="text-xs text-green-700 font-semibold">Aktif</span>
                    </div>
                    <a href="{{ route('provider.event.dashboard') }}"
                       class="mt-auto w-full text-center py-2.5 px-4 bg-purple-600 hover:bg-purple-700 text-white text-sm font-semibold rounded-xl transition-colors flex items-center justify-center gap-2">
                        <i class="fas fa-tachometer-alt"></i> Dashboard Event
                    </a>
                @elseif($user->provider_status === 'pending' && ($user->pending_role === 'provider_event' || !$user->pending_role))
                    <div class="flex items-center gap-1.5 mb-3">
                        <span class="w-2 h-2 rounded-full bg-yellow-400 flex-shrink-0 animate-pulse"></span>
                        <span class="text-xs text-yellow-700 font-medium">Sedang ditinjau admin</span>
                    </div>
                    <div class="mt-auto w-full text-center py-2.5 px-4 bg-gray-100 text-gray-400 text-sm font-medium rounded-xl cursor-not-allowed">
                        <i class="fas fa-clock mr-1"></i> Menunggu Verifikasi
                    </div>
                @else
                    <div class="flex items-center gap-1.5 mb-3">
                        <span class="w-2 h-2 rounded-full bg-gray-300 flex-shrink-0"></span>
                        <span class="text-xs text-gray-400 font-medium">Belum terdaftar</span>
                    </div>
                    <a href="{{ route('role.switch.become.provider_event') }}"
                       class="mt-auto w-full text-center py-2.5 px-4 border border-purple-300 text-purple-600 hover:bg-purple-600 hover:text-white hover:border-purple-600 text-sm font-semibold rounded-xl transition-all flex items-center justify-center gap-2">
                        <i class="fas fa-plus"></i> Daftar Sekarang
                    </a>
                @endif
            </div>

            {{-- ── PENJUAL KOS-KOSAN ────────────────────────────────────── --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 flex flex-col hover:shadow-md transition-shadow">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-11 h-11 rounded-xl bg-teal-50 flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-home text-teal-500 text-xl"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-900 text-sm leading-tight">Penjual Kos-Kosan</h3>
                        <p class="text-xs text-gray-500">Sewakan hunian untuk mahasiswa</p>
                    </div>
                </div>
                @if($user->hasRole('provider_residence'))
                    <div class="flex items-center gap-1.5 mb-3">
                        <span class="w-2 h-2 rounded-full bg-green-500 flex-shrink-0"></span>
                        <span class="text-xs text-green-700 font-semibold">Aktif</span>
                    </div>
                    <a href="{{ route('provider.residence.dashboard') }}"
                       class="mt-auto w-full text-center py-2.5 px-4 bg-teal-600 hover:bg-teal-700 text-white text-sm font-semibold rounded-xl transition-colors flex items-center justify-center gap-2">
                        <i class="fas fa-tachometer-alt"></i> Dashboard Kos
                    </a>
                @elseif($user->provider_status === 'pending' && $user->pending_role === 'provider_residence')
                    <div class="flex items-center gap-1.5 mb-3">
                        <span class="w-2 h-2 rounded-full bg-yellow-400 flex-shrink-0 animate-pulse"></span>
                        <span class="text-xs text-yellow-700 font-medium">Sedang ditinjau admin</span>
                    </div>
                    <div class="mt-auto w-full text-center py-2.5 px-4 bg-gray-100 text-gray-400 text-sm font-medium rounded-xl cursor-not-allowed">
                        <i class="fas fa-clock mr-1"></i> Menunggu Verifikasi
                    </div>
                @else
                    <div class="flex items-center gap-1.5 mb-3">
                        <span class="w-2 h-2 rounded-full bg-gray-300 flex-shrink-0"></span>
                        <span class="text-xs text-gray-400 font-medium">Belum terdaftar</span>
                    </div>
                    <a href="{{ route('role.switch.become.provider_residence') }}"
                       class="mt-auto w-full text-center py-2.5 px-4 border border-teal-300 text-teal-600 hover:bg-teal-600 hover:text-white hover:border-teal-600 text-sm font-semibold rounded-xl transition-all flex items-center justify-center gap-2">
                        <i class="fas fa-plus"></i> Daftar Sekarang
                    </a>
                @endif
            </div>
        </div>

        {{-- ====== PINTASAN FITUR ====== --}}
        <h2 class="text-xl font-bold text-gray-900 mb-4">Pintasan Fitur</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <a href="{{ route('user.bookings.index') }}" class="group relative bg-white rounded-2xl shadow-sm border border-gray-100 p-6 hover:shadow-md hover:border-blue-200 transition-all duration-300">
                <div class="flex items-center space-x-5">
                    <div class="bg-yellow-50 rounded-xl p-4 group-hover:scale-110 group-hover:bg-yellow-100 transition duration-300">
                        <i class="fas fa-book-open text-yellow-600 text-2xl"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 group-hover:text-blue-600 transition">Booking Saya</h3>
                        <p class="text-gray-500 text-sm mt-1">Daftar kos atau aktivitas yang Anda pesan.</p>
                    </div>
                    <div class="ml-auto text-gray-400 group-hover:text-blue-500 transition group-hover:translate-x-1">
                        <i class="fas fa-chevron-right"></i>
                    </div>
                </div>
            </a>

            <a href="{{ route('user.bookmarks.index') }}" class="group relative bg-white rounded-2xl shadow-sm border border-gray-100 p-6 hover:shadow-md hover:border-blue-200 transition-all duration-300">
                <div class="flex items-center space-x-5">
                    <div class="bg-red-50 rounded-xl p-4 group-hover:scale-110 group-hover:bg-red-100 transition duration-300">
                        <i class="fas fa-heart text-red-500 text-2xl"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 group-hover:text-blue-600 transition">Bookmark</h3>
                        <p class="text-gray-500 text-sm mt-1">Koleksi properti yang Anda simpan.</p>
                    </div>
                    <div class="ml-auto text-gray-400 group-hover:text-blue-500 transition group-hover:translate-x-1">
                        <i class="fas fa-chevron-right"></i>
                    </div>
                </div>
            </a>

            <a href="{{ route('user.history') }}" class="group relative bg-white rounded-2xl shadow-sm border border-gray-100 p-6 hover:shadow-md hover:border-blue-200 transition-all duration-300">
                <div class="flex items-center space-x-5">
                    <div class="bg-indigo-50 rounded-xl p-4 group-hover:scale-110 group-hover:bg-indigo-100 transition duration-300">
                        <i class="fas fa-history text-indigo-600 text-2xl"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 group-hover:text-blue-600 transition">Riwayat Transaksi</h3>
                        <p class="text-gray-500 text-sm mt-1">Lihat riwayat pembelian di marketplace dll.</p>
                    </div>
                    <div class="ml-auto text-gray-400 group-hover:text-blue-500 transition group-hover:translate-x-1">
                        <i class="fas fa-chevron-right"></i>
                    </div>
                </div>
            </a>
        </div>
    </div>
</div>
@endsection
