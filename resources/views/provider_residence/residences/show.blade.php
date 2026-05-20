@extends('layouts.app')

@section('title', $residence->name . ' - EduLiving')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Header --}}
        <div class="flex items-center justify-between mb-8">
            <div>
                <nav class="flex items-center gap-2 text-sm text-gray-500 mb-2">
                    <a href="{{ route('provider.residence.dashboard') }}" class="hover:text-blue-600">Dashboard</a>
                    <i class="fas fa-chevron-right text-xs"></i>
                    <a href="{{ route('provider.residence.residences.index') }}" class="hover:text-blue-600">Hunian Saya</a>
                    <i class="fas fa-chevron-right text-xs"></i>
                    <span class="text-gray-900">{{ $residence->name }}</span>
                </nav>
                <h1 class="text-2xl font-bold text-gray-900">{{ $residence->name }}</h1>
                <div class="flex items-center gap-2 mt-2">
                    {{-- Badge tipe --}}
                    @if($residence->residence_type)
                        @php
                            $typeColor = match($residence->residence_type) {
                                'kos'        => 'bg-blue-100 text-blue-700',
                                'kontrakan'  => 'bg-green-100 text-green-700',
                                'apartemen'  => 'bg-purple-100 text-purple-700',
                                'rumah_sewa' => 'bg-orange-100 text-orange-700',
                                default      => 'bg-gray-100 text-gray-700',
                            };
                            $typeIcon = match($residence->residence_type) {
                                'kos'        => 'fa-door-open',
                                'kontrakan'  => 'fa-home',
                                'apartemen'  => 'fa-building',
                                'rumah_sewa' => 'fa-house-user',
                                default      => 'fa-bed',
                            };
                        @endphp
                        <span class="inline-flex items-center gap-1.5 {{ $typeColor }} text-xs font-semibold px-3 py-1 rounded-full">
                            <i class="fas {{ $typeIcon }} text-xs"></i>
                            {{ $residence->residence_type_label }}
                            @if($residence->isKos() && $residence->kos_type)
                                · {{ $residence->kos_type_label }}
                            @endif
                        </span>
                    @endif
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold
                        {{ $residence->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                        <i class="fas fa-circle text-xs mr-1.5"></i>
                        {{ $residence->is_active ? 'Aktif' : 'Nonaktif' }}
                    </span>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('provider.residence.residences.edit', $residence) }}"
                   class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                    <i class="fas fa-edit mr-2"></i>Edit
                </a>
                <a href="{{ route('provider.residence.residences.index') }}"
                   class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>Kembali
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            {{-- ══ KOLOM KIRI ══ --}}
            <div class="lg:col-span-2 space-y-6">

                {{-- GALLERY --}}
                <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                    @if($residence->images && count($residence->images) > 0)
                        <div class="relative">
                            <img src="{{ asset('storage/' . $residence->images[0]) }}"
                                 alt="{{ $residence->name }}"
                                 class="w-full h-80 object-cover" id="mainImage">
                            @if(count($residence->images) > 1)
                                <div class="absolute bottom-3 left-3 right-3">
                                    <div class="flex gap-2 overflow-x-auto">
                                        @foreach($residence->images as $index => $image)
                                            <img src="{{ asset('storage/' . $image) }}"
                                                 alt="foto {{ $index + 1 }}"
                                                 class="w-14 h-14 object-cover rounded-lg cursor-pointer border-2 flex-shrink-0
                                                        {{ $index === 0 ? 'border-blue-500' : 'border-white/70' }}"
                                                 onclick="changeMainImage('{{ asset('storage/' . $image) }}', this)">
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="w-full h-64 bg-gray-100 flex items-center justify-center">
                            <div class="text-center text-gray-400">
                                <i class="fas fa-home text-5xl mb-2"></i>
                                <p class="text-sm">Belum ada foto</p>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- DESKRIPSI --}}
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h3 class="font-semibold text-gray-900 mb-3">Deskripsi</h3>
                    <p class="text-gray-600 leading-relaxed text-sm">{{ $residence->description }}</p>

                    <div class="mt-4 pt-4 border-t border-gray-100 flex flex-wrap gap-2">
                        @if($residence->category)
                            <span class="inline-flex items-center gap-1.5 bg-gray-100 text-gray-600 text-xs px-3 py-1.5 rounded-full">
                                <i class="fas fa-tag text-xs"></i>{{ $residence->category->name }}
                            </span>
                        @endif
                        @if($residence->furnish_status)
                            <span class="inline-flex items-center gap-1.5 bg-yellow-50 text-yellow-700 text-xs px-3 py-1.5 rounded-full border border-yellow-100">
                                <i class="fas fa-couch text-xs"></i>{{ $residence->furnish_status_label }}
                            </span>
                        @endif
                        <span class="inline-flex items-center gap-1.5 bg-blue-50 text-blue-700 text-xs px-3 py-1.5 rounded-full">
                            <i class="fas fa-calendar text-xs"></i>
                            Sewa {{ $residence->rental_period === 'monthly' ? 'Bulanan' : 'Tahunan' }}
                        </span>
                    </div>
                </div>

                {{-- ══════════════════════════════════════════════════ --}}
                {{-- DETAIL SPESIFIK PER TIPE (BARU)                   --}}
                {{-- ══════════════════════════════════════════════════ --}}
                @if($residence->residence_type)
                <div class="bg-white rounded-xl shadow-sm p-6">
                    @php
                        $badgeColor = match($residence->residence_type) {
                            'kos'        => 'bg-blue-600',
                            'kontrakan'  => 'bg-green-600',
                            'apartemen'  => 'bg-purple-600',
                            'rumah_sewa' => 'bg-orange-500',
                            default      => 'bg-gray-500',
                        };
                        $badgeIcon = match($residence->residence_type) {
                            'kos'        => 'fa-door-open',
                            'kontrakan'  => 'fa-home',
                            'apartemen'  => 'fa-building',
                            'rumah_sewa' => 'fa-house-user',
                            default      => 'fa-bed',
                        };
                    @endphp
                    <h3 class="font-semibold text-gray-900 mb-4 flex items-center gap-2">
                        <span class="w-7 h-7 {{ $badgeColor }} text-white rounded-full flex items-center justify-center text-xs">
                            <i class="fas {{ $badgeIcon }}"></i>
                        </span>
                        Detail {{ $residence->residence_type_label }}
                    </h3>

                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">

                        {{-- ── KOS ──────────────────────────────────── --}}
                        @if($residence->isKos())
                            @if($residence->kos_type)
                            <div class="bg-blue-50 rounded-xl p-4 text-center border border-blue-100">
                                <i class="fas fa-{{ $residence->kos_type === 'putra' ? 'mars' : ($residence->kos_type === 'putri' ? 'venus' : 'venus-mars') }} text-blue-500 text-xl mb-2"></i>
                                <div class="text-xs text-blue-500 mb-0.5">Jenis Kos</div>
                                <div class="font-bold text-blue-800 text-sm">{{ $residence->kos_type_label }}</div>
                            </div>
                            @endif
                            <div class="bg-gray-50 rounded-xl p-4 text-center border border-gray-100">
                                <i class="fas fa-door-open text-gray-400 text-xl mb-2"></i>
                                <div class="text-xs text-gray-500 mb-0.5">Total Kamar</div>
                                <div class="font-bold text-gray-800 text-sm">{{ $residence->capacity }} kamar</div>
                            </div>
                            <div class="bg-{{ $residence->available_slots > 0 ? 'green' : 'red' }}-50 rounded-xl p-4 text-center border border-{{ $residence->available_slots > 0 ? 'green' : 'red' }}-100">
                                <i class="fas fa-door-{{ $residence->available_slots > 0 ? 'open' : 'closed' }} text-{{ $residence->available_slots > 0 ? 'green' : 'red' }}-500 text-xl mb-2"></i>
                                <div class="text-xs text-{{ $residence->available_slots > 0 ? 'green' : 'red' }}-500 mb-0.5">Kamar Kosong</div>
                                <div class="font-bold text-{{ $residence->available_slots > 0 ? 'green' : 'red' }}-700 text-sm">{{ $residence->available_slots }} kamar</div>
                            </div>
                            @if($residence->room_size)
                            <div class="bg-gray-50 rounded-xl p-4 text-center border border-gray-100">
                                <i class="fas fa-ruler-combined text-gray-400 text-xl mb-2"></i>
                                <div class="text-xs text-gray-500 mb-0.5">Ukuran Kamar</div>
                                <div class="font-bold text-gray-800 text-sm">{{ $residence->room_size }} m²</div>
                            </div>
                            @endif

                        {{-- ── KONTRAKAN / RUMAH SEWA ───────────────── --}}
                        @elseif($residence->isKontrakan() || $residence->isRumahSewa())
                            <div class="bg-gray-50 rounded-xl p-4 text-center border border-gray-100">
                                <i class="fas fa-home text-gray-400 text-xl mb-2"></i>
                                <div class="text-xs text-gray-500 mb-0.5">Total Unit</div>
                                <div class="font-bold text-gray-800 text-sm">{{ $residence->capacity }} unit</div>
                            </div>
                            <div class="bg-{{ $residence->available_slots > 0 ? 'green' : 'red' }}-50 rounded-xl p-4 text-center border border-{{ $residence->available_slots > 0 ? 'green' : 'red' }}-100">
                                <i class="fas fa-key text-{{ $residence->available_slots > 0 ? 'green' : 'red' }}-500 text-xl mb-2"></i>
                                <div class="text-xs text-{{ $residence->available_slots > 0 ? 'green' : 'red' }}-500 mb-0.5">Unit Kosong</div>
                                <div class="font-bold text-{{ $residence->available_slots > 0 ? 'green' : 'red' }}-700 text-sm">{{ $residence->available_slots }} unit</div>
                            </div>
                            @if($residence->bedroom_count)
                            <div class="bg-gray-50 rounded-xl p-4 text-center border border-gray-100">
                                <i class="fas fa-bed text-gray-400 text-xl mb-2"></i>
                                <div class="text-xs text-gray-500 mb-0.5">Kamar Tidur</div>
                                <div class="font-bold text-gray-800 text-sm">{{ $residence->bedroom_count }} kamar</div>
                            </div>
                            @endif
                            @if($residence->bathroom_count)
                            <div class="bg-gray-50 rounded-xl p-4 text-center border border-gray-100">
                                <i class="fas fa-bath text-gray-400 text-xl mb-2"></i>
                                <div class="text-xs text-gray-500 mb-0.5">Kamar Mandi</div>
                                <div class="font-bold text-gray-800 text-sm">{{ $residence->bathroom_count }} kamar</div>
                            </div>
                            @endif
                            @if($residence->building_size)
                            <div class="bg-gray-50 rounded-xl p-4 text-center border border-gray-100">
                                <i class="fas fa-ruler-combined text-gray-400 text-xl mb-2"></i>
                                <div class="text-xs text-gray-500 mb-0.5">Luas Bangunan</div>
                                <div class="font-bold text-gray-800 text-sm">{{ $residence->building_size }} m²</div>
                            </div>
                            @endif
                            @if($residence->land_size)
                            <div class="bg-gray-50 rounded-xl p-4 text-center border border-gray-100">
                                <i class="fas fa-expand-arrows-alt text-gray-400 text-xl mb-2"></i>
                                <div class="text-xs text-gray-500 mb-0.5">Luas Tanah</div>
                                <div class="font-bold text-gray-800 text-sm">{{ $residence->land_size }} m²</div>
                            </div>
                            @endif

                        {{-- ── APARTEMEN ────────────────────────────── --}}
                        @elseif($residence->isApartemen())
                            @if($residence->unit_type)
                            <div class="bg-purple-50 rounded-xl p-4 text-center border border-purple-100">
                                <i class="fas fa-layer-group text-purple-500 text-xl mb-2"></i>
                                <div class="text-xs text-purple-500 mb-0.5">Tipe Unit</div>
                                <div class="font-bold text-purple-800 text-sm">{{ $residence->unit_type }}</div>
                            </div>
                            @endif
                            @if($residence->floor_number)
                            <div class="bg-gray-50 rounded-xl p-4 text-center border border-gray-100">
                                <i class="fas fa-sort-numeric-up text-gray-400 text-xl mb-2"></i>
                                <div class="text-xs text-gray-500 mb-0.5">Lantai</div>
                                <div class="font-bold text-gray-800 text-sm">Lantai {{ $residence->floor_number }}</div>
                            </div>
                            @endif
                            @if($residence->tower_name)
                            <div class="bg-gray-50 rounded-xl p-4 text-center border border-gray-100">
                                <i class="fas fa-building text-gray-400 text-xl mb-2"></i>
                                <div class="text-xs text-gray-500 mb-0.5">Tower/Gedung</div>
                                <div class="font-bold text-gray-800 text-sm">{{ $residence->tower_name }}</div>
                            </div>
                            @endif
                            <div class="bg-gray-50 rounded-xl p-4 text-center border border-gray-100">
                                <i class="fas fa-clone text-gray-400 text-xl mb-2"></i>
                                <div class="text-xs text-gray-500 mb-0.5">Total Unit</div>
                                <div class="font-bold text-gray-800 text-sm">{{ $residence->capacity }} unit</div>
                            </div>
                            <div class="bg-{{ $residence->available_slots > 0 ? 'green' : 'red' }}-50 rounded-xl p-4 text-center border border-{{ $residence->available_slots > 0 ? 'green' : 'red' }}-100">
                                <i class="fas fa-key text-{{ $residence->available_slots > 0 ? 'green' : 'red' }}-500 text-xl mb-2"></i>
                                <div class="text-xs text-{{ $residence->available_slots > 0 ? 'green' : 'red' }}-500 mb-0.5">Unit Kosong</div>
                                <div class="font-bold text-{{ $residence->available_slots > 0 ? 'green' : 'red' }}-700 text-sm">{{ $residence->available_slots }} unit</div>
                            </div>
                            @if($residence->room_size)
                            <div class="bg-gray-50 rounded-xl p-4 text-center border border-gray-100">
                                <i class="fas fa-ruler-combined text-gray-400 text-xl mb-2"></i>
                                <div class="text-xs text-gray-500 mb-0.5">Luas Unit</div>
                                <div class="font-bold text-gray-800 text-sm">{{ $residence->room_size }} m²</div>
                            </div>
                            @endif
                            @if($residence->bathroom_count)
                            <div class="bg-gray-50 rounded-xl p-4 text-center border border-gray-100">
                                <i class="fas fa-bath text-gray-400 text-xl mb-2"></i>
                                <div class="text-xs text-gray-500 mb-0.5">Kamar Mandi</div>
                                <div class="font-bold text-gray-800 text-sm">{{ $residence->bathroom_count }}</div>
                            </div>
                            @endif
                        @endif

                        {{-- Status furnitur (semua tipe) --}}
                        @if($residence->furnish_status)
                        <div class="bg-yellow-50 rounded-xl p-4 text-center border border-yellow-100">
                            <i class="fas fa-couch text-yellow-500 text-xl mb-2"></i>
                            <div class="text-xs text-yellow-600 mb-0.5">Furnitur</div>
                            <div class="font-bold text-yellow-800 text-sm">{{ $residence->furnish_status_label }}</div>
                        </div>
                        @endif

                    </div>
                </div>
                @endif

                {{-- FASILITAS --}}
                @if($residence->facilities && count($residence->facilities) > 0)
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h3 class="font-semibold text-gray-900 mb-4">
                        <i class="fas fa-concierge-bell text-blue-500 mr-2"></i>Fasilitas
                    </h3>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                        @foreach($residence->facilities as $facility)
                            <div class="flex items-center gap-2 text-sm text-gray-700">
                                <i class="fas fa-check-circle text-green-500 flex-shrink-0"></i>
                                {{ $facility }}
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- ULASAN --}}
                @if($residence->ratings && $residence->ratings->count() > 0)
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h3 class="font-semibold text-gray-900 mb-4">
                        Ulasan <span class="text-gray-400 font-normal text-sm">({{ $residence->ratings->count() }})</span>
                    </h3>
                    <div class="space-y-4">
                        @foreach($residence->ratings as $rating)
                        <div class="flex gap-3 {{ !$loop->last ? 'pb-4 border-b border-gray-100' : '' }}">
                            <div class="w-9 h-9 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0">
                                <span class="text-sm font-bold text-blue-600">{{ substr($rating->user->name, 0, 1) }}</span>
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center justify-between mb-1">
                                    <p class="font-semibold text-gray-900 text-sm">{{ $rating->user->name }}</p>
                                    <span class="text-xs text-gray-400">{{ $rating->created_at->format('d M Y') }}</span>
                                </div>
                                <div class="flex mb-1.5">
                                    @for($i = 1; $i <= 5; $i++)
                                        <i class="fas fa-star text-xs {{ $i <= $rating->rating ? 'text-yellow-400' : 'text-gray-200' }}"></i>
                                    @endfor
                                </div>
                                @if($rating->comment ?? $rating->review)
                                    <p class="text-gray-600 text-sm">{{ $rating->comment ?? $rating->review }}</p>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

            </div>

            {{-- ══ KOLOM KANAN: SIDEBAR ══ --}}
            <div class="lg:col-span-1 space-y-5">

                {{-- Harga --}}
                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                    <h3 class="font-semibold text-gray-900 mb-4 text-sm">Informasi Harga</h3>
                    <div class="space-y-2.5">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Harga Dasar</span>
                            <span class="font-medium">Rp {{ number_format($residence->price, 0, ',', '.') }}</span>
                        </div>
                        @if($residence->discount_type && $residence->discount_value)
                            <div class="flex justify-between text-sm text-green-600">
                                <span>Diskon</span>
                                <span>
                                    @if($residence->discount_type === 'percentage')
                                        {{ $residence->discount_value }}%
                                    @else
                                        Rp {{ number_format($residence->discount_value, 0, ',', '.') }}
                                    @endif
                                </span>
                            </div>
                            <div class="border-t pt-2.5 flex justify-between font-semibold">
                                <span>Harga Akhir</span>
                                <span class="text-blue-600">Rp {{ number_format($residence->getDiscountedPrice(), 0, ',', '.') }}</span>
                            </div>
                        @endif
                        <p class="text-xs text-gray-400">
                            per {{ $residence->rental_period === 'monthly' ? 'bulan' : 'tahun' }}
                            @if($residence->isKos()) · per kamar @else · per unit @endif
                        </p>
                    </div>
                </div>

                {{-- Statistik --}}
                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                    <h3 class="font-semibold text-gray-900 mb-4 text-sm">Statistik</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">{{ $residence->isKos() ? 'Total Kamar' : 'Total Unit' }}</span>
                            <span class="font-semibold">{{ $residence->capacity }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">{{ $residence->isKos() ? 'Kamar Kosong' : 'Unit Kosong' }}</span>
                            <span class="font-semibold {{ $residence->available_slots > 0 ? 'text-green-600' : 'text-red-500' }}">
                                {{ $residence->available_slots }}
                            </span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">{{ $residence->isKos() ? 'Kamar Terisi' : 'Unit Terisi' }}</span>
                            <span class="font-semibold text-blue-600">
                                {{ $residence->capacity - $residence->available_slots }}
                            </span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Total Booking</span>
                            <span class="font-semibold">{{ $residence->bookings()->count() }}</span>
                        </div>
                        @if(isset($residence->ratings_avg_rating) && $residence->ratings_avg_rating)
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Rating Rata-rata</span>
                            <span class="font-semibold">
                                <i class="fas fa-star text-yellow-400 text-xs mr-0.5"></i>
                                {{ number_format($residence->ratings_avg_rating, 1) }}/5
                            </span>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Aksi Cepat --}}
                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                    <h3 class="font-semibold text-gray-900 mb-4 text-sm">Aksi Cepat</h3>
                    <div class="space-y-2.5">
                        <a href="{{ route('provider.residence.residences.edit', $residence) }}"
                           class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2.5 px-4 rounded-lg font-medium text-sm transition-colors text-center block">
                            <i class="fas fa-edit mr-2"></i>Edit Hunian
                        </a>

                        <form method="POST" action="{{ route('provider.residence.residences.toggleStatus', $residence) }}">
                            @csrf @method('PATCH')
                            <button type="submit"
                                    class="w-full py-2.5 px-4 rounded-lg font-medium text-sm transition-colors
                                           {{ $residence->is_active ? 'bg-yellow-50 hover:bg-yellow-100 text-yellow-700 border border-yellow-200' : 'bg-green-50 hover:bg-green-100 text-green-700 border border-green-200' }}">
                                <i class="fas fa-{{ $residence->is_active ? 'pause' : 'play' }} mr-2"></i>
                                {{ $residence->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                            </button>
                        </form>

                        <a href="{{ route('provider.residence.bookings.index', ['residence' => $residence->id]) }}"
                           class="w-full bg-purple-50 hover:bg-purple-100 text-purple-700 border border-purple-200 py-2.5 px-4 rounded-lg font-medium text-sm transition-colors text-center block">
                            <i class="fas fa-bookmark mr-2"></i>Lihat Semua Booking
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
    document.querySelectorAll('[onclick^="changeMainImage"]')
        .forEach(el => el.classList.replace('border-blue-500', 'border-white/70'));
    element.classList.replace('border-white/70', 'border-blue-500');
}
</script>
@endpush
@endsection