<nav class="bg-white shadow-lg sticky top-0 z-50 transition-all duration-300" id="navbar">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">

            <!-- Logo -->
            <div class="flex-shrink-0">
                <a href="{{ route('home') }}" class="flex items-center">
                    <img src="{{ asset('images/Infoma_Branding-blue.png') }}" alt="EduLiving Logo" class="h-8 w-8">
                    <span class="ml-2 text-xl font-bold text-gray-900">EduLiving</span>
                </a>
            </div>

            <!-- Desktop Navigation -->
            <div class="hidden md:block">
                <div class="ml-10 flex items-baseline space-x-4">

                    @auth
                        @if(auth()->user()->hasRole('provider_residence'))
                            {{-- Navbar Provider Hunian --}}
                            <a href="{{ route('home') }}"
                               class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium transition-colors duration-200 {{ request()->routeIs('home') ? 'text-blue-600 bg-blue-50' : '' }}">
                                <i class="fas fa-home mr-1"></i>Beranda
                            </a>
                            <a href="{{ route('provider.residence.residences.index') }}"
                               class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium transition-colors duration-200 {{ request()->routeIs('provider.residence.residences.*') ? 'text-blue-600 bg-blue-50' : '' }}">
                                <i class="fas fa-building mr-1"></i>Hunian Saya
                            </a>
                            <a href="{{ route('provider.residence.bookings.index') }}"
                               class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium transition-colors duration-200 {{ request()->routeIs('provider.residence.bookings.*') ? 'text-blue-600 bg-blue-50' : '' }}">
                                <i class="fas fa-clipboard-list mr-1"></i>Kelola Booking
                            </a>

                        @elseif(auth()->user()->hasRole('provider_event'))
                            {{-- Navbar Provider Event --}}
                            <a href="{{ route('home') }}"
                               class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium transition-colors duration-200 {{ request()->routeIs('home') ? 'text-blue-600 bg-blue-50' : '' }}">
                                <i class="fas fa-home mr-1"></i>Beranda
                            </a>
                            <a href="{{ route('provider.event.activities.index') }}"
                               class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium transition-colors duration-200 {{ request()->routeIs('provider.event.activities.*') ? 'text-blue-600 bg-blue-50' : '' }}">
                                <i class="fas fa-calendar-alt mr-1"></i>Event Saya
                            </a>
                            <a href="{{ route('provider.event.bookings.index') }}"
                               class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium transition-colors duration-200 {{ request()->routeIs('provider.event.bookings.*') ? 'text-blue-600 bg-blue-50' : '' }}">
                                <i class="fas fa-clipboard-list mr-1"></i>Kelola Booking
                            </a>

                        @else
                            {{-- Navbar Mahasiswa & Guest --}}
                            <a href="{{ route('home') }}"
                               class="text-gray-900 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium transition-colors duration-200 {{ request()->routeIs('home') ? 'text-blue-600 bg-blue-50' : '' }}">
                                <i class="fas fa-home mr-1"></i>Beranda
                            </a>
                            <a href="{{ route('residences.index') }}"
                               class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium transition-colors duration-200 {{ request()->routeIs('residences.*') ? 'text-blue-600 bg-blue-50' : '' }}">
                                <i class="fas fa-building mr-1"></i>Residence
                            </a>
                            <a href="{{ route('activities.index') }}"
                               class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium transition-colors duration-200 {{ request()->routeIs('activities.*') ? 'text-blue-600 bg-blue-50' : '' }}">
                                <i class="fas fa-calendar-alt mr-1"></i>Kegiatan
                            </a>
                            <a href="{{ route('marketplace.index') }}"
                               class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium transition-colors duration-200 {{ request()->routeIs('marketplace.*') ? 'text-blue-600 bg-blue-50' : '' }}">
                                <i class="fas fa-store mr-1"></i>Marketplace
                            </a>
                            <a href="{{ route('search') }}"
                               class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium transition-colors duration-200 {{ request()->routeIs('search') ? 'text-blue-600 bg-blue-50' : '' }}">
                                <i class="fas fa-search mr-1"></i>Cari
                            </a>
                        @endif

                    @else
                        {{-- Guest --}}
                        <a href="{{ route('home') }}"
                           class="text-gray-900 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium transition-colors duration-200 {{ request()->routeIs('home') ? 'text-blue-600 bg-blue-50' : '' }}">
                            <i class="fas fa-home mr-1"></i>Beranda
                        </a>
                        <a href="{{ route('residences.index') }}"
                           class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium transition-colors duration-200 {{ request()->routeIs('residences.*') ? 'text-blue-600 bg-blue-50' : '' }}">
                            <i class="fas fa-building mr-1"></i>Residence
                        </a>
                        <a href="{{ route('activities.index') }}"
                           class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium transition-colors duration-200 {{ request()->routeIs('activities.*') ? 'text-blue-600 bg-blue-50' : '' }}">
                            <i class="fas fa-calendar-alt mr-1"></i>Kegiatan
                        </a>
                        <a href="{{ route('marketplace.index') }}"
                           class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium transition-colors duration-200 {{ request()->routeIs('marketplace.*') ? 'text-blue-600 bg-blue-50' : '' }}">
                            <i class="fas fa-store mr-1"></i>Marketplace
                        </a>
                        <a href="{{ route('search') }}"
                           class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium transition-colors duration-200 {{ request()->routeIs('search') ? 'text-blue-600 bg-blue-50' : '' }}">
                            <i class="fas fa-search mr-1"></i>Cari
                        </a>
                    @endauth
                </div>
            </div>

            <!-- Right side -->
            <div class="hidden md:block">
                @auth
                    <div class="ml-3 relative" x-data="{ open: false }">
                        <button @click="open = !open"
                                class="bg-white flex items-center text-sm rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <div class="h-8 w-8 rounded-full bg-blue-900 flex items-center justify-center">
                                <span class="text-white text-sm font-medium">
                                    {{ substr(auth()->user()->name, 0, 1) }}
                                </span>
                            </div>
                            <span class="ml-2 text-gray-700 font-medium">{{ auth()->user()->name }}</span>
                            <i class="fas fa-chevron-down ml-1 text-gray-400"></i>
                        </button>

                        <div x-show="open"
                             @click.away="open = false"
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="transform opacity-0 scale-95"
                             x-transition:enter-end="transform opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="transform opacity-100 scale-100"
                             x-transition:leave-end="transform opacity-0 scale-95"
                             class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg py-1 bg-white ring-1 ring-black ring-opacity-5 z-50">

                            @if(auth()->user()->hasRole('admin'))
                                <a href="{{ route('admin.dashboard') }}"
                                   class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-tachometer-alt mr-2"></i>Admin Dashboard
                                </a>

                            @elseif(auth()->user()->hasRole('provider_residence'))
                                <a href="{{ route('provider.residence.dashboard') }}"
                                   class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-tachometer-alt mr-2"></i>Dashboard
                                </a>

                            @elseif(auth()->user()->hasRole('provider_event'))
                                <a href="{{ route('provider.event.dashboard') }}"
                                   class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-tachometer-alt mr-2"></i>Dashboard
                                </a>

                            @else
                                <a href="{{ route('user.dashboard') }}"
                                   class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-tachometer-alt mr-2"></i>Dashboard
                                </a>
                                <a href="{{ route('user.history') }}"
                                   class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-history mr-2"></i>Riwayat
                                </a>
                                <a href="{{ route('user.bookings.index') }}"
                                   class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-bookmark mr-2"></i>Booking Saya
                                </a>
                                <a href="{{ route('user.bookmarks.index') }}"
                                   class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-heart mr-2"></i>Bookmark
                                </a>
                                <a href="{{ route('user.marketplace.transactions.index') }}"
                                   class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-shopping-cart mr-2"></i>Transaksi Marketplace
                                </a>
                                <a href="{{ route('user.marketplace.sell') }}"
                                   class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-store mr-2"></i>
                                    {{ auth()->user()->isSeller() ? 'Produk Saya' : 'Mulai Berjualan' }}
                                </a>
                            @endif

                            <div class="border-t border-gray-100 my-1"></div>
                            <a href="{{ route('user.profile.show') }}"
                               class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-user mr-2"></i>Profil
                            </a>
                            <form method="POST" action="{{ route('logout') }}" class="block">
                                @csrf
                                <button type="submit"
                                        class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-sign-out-alt mr-2"></i>Keluar
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    <div class="flex items-center space-x-4">
                        <a href="{{ route('login') }}"
                           class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium transition-colors duration-200">
                            <i class="fas fa-sign-in-alt mr-1"></i>Masuk
                        </a>
                        <a href="{{ route('register') }}"
                           class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200">
                            <i class="fas fa-user-plus mr-1"></i>Daftar
                        </a>
                    </div>
                @endauth
            </div>

            <!-- Mobile menu button -->
            <div class="md:hidden" x-data="{ mobileMenuOpen: false }">
                <button type="button"
                        @click="mobileMenuOpen = !mobileMenuOpen"
                        class="bg-white inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-blue-500">
                    <i class="fas fa-bars" x-show="!mobileMenuOpen"></i>
                    <i class="fas fa-times" x-show="mobileMenuOpen"></i>
                </button>

                <!-- Mobile menu -->
                <div x-show="mobileMenuOpen"
                     class="absolute top-16 left-0 right-0 bg-white border-t border-gray-200 shadow-lg z-50">
                    <div class="px-2 pt-2 pb-3 space-y-1">

                        @auth
                            @if(auth()->user()->hasRole('provider_residence'))
                                <a href="{{ route('home') }}"
                                   class="text-gray-700 hover:text-blue-600 block px-3 py-2 rounded-md text-base font-medium">
                                    <i class="fas fa-home mr-2"></i>Beranda
                                </a>
                                <a href="{{ route('provider.residence.residences.index') }}"
                                   class="text-gray-700 hover:text-blue-600 block px-3 py-2 rounded-md text-base font-medium">
                                    <i class="fas fa-building mr-2"></i>Hunian Saya
                                </a>
                                <a href="{{ route('provider.residence.bookings.index') }}"
                                   class="text-gray-700 hover:text-blue-600 block px-3 py-2 rounded-md text-base font-medium">
                                    <i class="fas fa-clipboard-list mr-2"></i>Kelola Booking
                                </a>
                                <div class="border-t border-gray-200 my-2"></div>
                                <a href="{{ route('provider.residence.dashboard') }}"
                                   class="text-gray-700 hover:text-blue-600 block px-3 py-2 rounded-md text-base font-medium">
                                    <i class="fas fa-tachometer-alt mr-2"></i>Dashboard
                                </a>

                            @elseif(auth()->user()->hasRole('provider_event'))
                                <a href="{{ route('home') }}"
                                   class="text-gray-700 hover:text-blue-600 block px-3 py-2 rounded-md text-base font-medium">
                                    <i class="fas fa-home mr-2"></i>Beranda
                                </a>
                                <a href="{{ route('provider.event.activities.index') }}"
                                   class="text-gray-700 hover:text-blue-600 block px-3 py-2 rounded-md text-base font-medium">
                                    <i class="fas fa-calendar-alt mr-2"></i>Event Saya
                                </a>
                                <a href="{{ route('provider.event.bookings.index') }}"
                                   class="text-gray-700 hover:text-blue-600 block px-3 py-2 rounded-md text-base font-medium">
                                    <i class="fas fa-clipboard-list mr-2"></i>Kelola Booking
                                </a>
                                <div class="border-t border-gray-200 my-2"></div>
                                <a href="{{ route('provider.event.dashboard') }}"
                                   class="text-gray-700 hover:text-blue-600 block px-3 py-2 rounded-md text-base font-medium">
                                    <i class="fas fa-tachometer-alt mr-2"></i>Dashboard
                                </a>

                            @else
                                <a href="{{ route('home') }}"
                                   class="text-gray-900 hover:text-blue-600 block px-3 py-2 rounded-md text-base font-medium">
                                    <i class="fas fa-home mr-2"></i>Beranda
                                </a>
                                <a href="{{ route('residences.index') }}"
                                   class="text-gray-700 hover:text-blue-600 block px-3 py-2 rounded-md text-base font-medium">
                                    <i class="fas fa-building mr-2"></i>Residence
                                </a>
                                <a href="{{ route('activities.index') }}"
                                   class="text-gray-700 hover:text-blue-600 block px-3 py-2 rounded-md text-base font-medium">
                                    <i class="fas fa-calendar-alt mr-2"></i>Kegiatan
                                </a>
                                <a href="{{ route('marketplace.index') }}"
                                   class="text-gray-700 hover:text-blue-600 block px-3 py-2 rounded-md text-base font-medium">
                                    <i class="fas fa-store mr-2"></i>Marketplace
                                </a>
                                <a href="{{ route('search') }}"
                                   class="text-gray-700 hover:text-blue-600 block px-3 py-2 rounded-md text-base font-medium">
                                    <i class="fas fa-search mr-2"></i>Cari
                                </a>
                                <div class="border-t border-gray-200 my-2"></div>
                                <a href="{{ route('user.dashboard') }}"
                                   class="text-gray-700 hover:text-blue-600 block px-3 py-2 rounded-md text-base font-medium">
                                    <i class="fas fa-tachometer-alt mr-2"></i>Dashboard
                                </a>
                                <a href="{{ route('user.bookings.index') }}"
                                   class="text-gray-700 hover:text-blue-600 block px-3 py-2 rounded-md text-base font-medium">
                                    <i class="fas fa-bookmark mr-2"></i>Booking Saya
                                </a>
                                <a href="{{ route('user.marketplace.transactions.index') }}"
                                   class="text-gray-700 hover:text-blue-600 block px-3 py-2 rounded-md text-base font-medium">
                                    <i class="fas fa-shopping-cart mr-2"></i>Transaksi Marketplace
                                </a>
                                <a href="{{ route('user.marketplace.sell') }}"
                                   class="text-gray-700 hover:text-blue-600 block px-3 py-2 rounded-md text-base font-medium">
                                    <i class="fas fa-store mr-2"></i>
                                    {{ auth()->user()->isSeller() ? 'Produk Saya' : 'Mulai Berjualan' }}
                                </a>
                            @endif

                            <div class="border-t border-gray-200 my-2"></div>
                            <a href="{{ route('user.profile.show') }}"
                               class="text-gray-700 hover:text-blue-600 block px-3 py-2 rounded-md text-base font-medium">
                                <i class="fas fa-user mr-2"></i>Profil
                            </a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit"
                                        class="w-full text-left text-gray-700 hover:text-blue-600 block px-3 py-2 rounded-md text-base font-medium">
                                    <i class="fas fa-sign-out-alt mr-2"></i>Keluar
                                </button>
                            </form>

                        @else
                            <a href="{{ route('home') }}"
                               class="text-gray-900 hover:text-blue-600 block px-3 py-2 rounded-md text-base font-medium">
                                <i class="fas fa-home mr-2"></i>Beranda
                            </a>
                            <a href="{{ route('residences.index') }}"
                               class="text-gray-700 hover:text-blue-600 block px-3 py-2 rounded-md text-base font-medium">
                                <i class="fas fa-building mr-2"></i>Residence
                            </a>
                            <a href="{{ route('activities.index') }}"
                               class="text-gray-700 hover:text-blue-600 block px-3 py-2 rounded-md text-base font-medium">
                                <i class="fas fa-calendar-alt mr-2"></i>Kegiatan
                            </a>
                            <a href="{{ route('marketplace.index') }}"
                               class="text-gray-700 hover:text-blue-600 block px-3 py-2 rounded-md text-base font-medium">
                                <i class="fas fa-store mr-2"></i>Marketplace
                            </a>
                            <a href="{{ route('search') }}"
                               class="text-gray-700 hover:text-blue-600 block px-3 py-2 rounded-md text-base font-medium">
                                <i class="fas fa-search mr-2"></i>Cari
                            </a>
                            <div class="border-t border-gray-200 my-2"></div>
                            <a href="{{ route('login') }}"
                               class="text-gray-700 hover:text-blue-600 block px-3 py-2 rounded-md text-base font-medium">
                                <i class="fas fa-sign-in-alt mr-2"></i>Masuk
                            </a>
                            <a href="{{ route('register') }}"
                               class="text-gray-700 hover:text-blue-600 block px-3 py-2 rounded-md text-base font-medium">
                                <i class="fas fa-user-plus mr-2"></i>Daftar
                            </a>
                        @endauth
                    </div>
                </div>
            </div>

        </div>
    </div>
</nav>

<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>