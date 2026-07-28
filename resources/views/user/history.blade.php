@extends('layouts.app')

@section('title', 'Riwayat Transaksi - EduLiving')

@section('content')
<div class="min-h-screen bg-gray-50 pb-12">

    {{-- ── 1. Top Header Banner & Category Tabs (Gaya App Mobile Flutter) ───── --}}
    <div class="bg-blue-600 text-white pt-8 pb-6 px-4 sm:px-6 lg:px-8 shadow-md">
        <div class="max-w-4xl mx-auto">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-extrabold tracking-tight">Riwayat Transaksi</h1>
                    <p class="text-blue-100 text-xs sm:text-sm mt-1">Semua riwayat sewa hunian, pendaftaran acara, dan belanja barang FJB</p>
                </div>
                <div class="hidden sm:block">
                    <div class="w-12 h-12 bg-white/10 backdrop-blur-md rounded-2xl flex items-center justify-center text-white text-xl">
                        <i class="fas fa-history"></i>
                    </div>
                </div>
            </div>

            {{-- Top Category Selector Tabs (3 Kategori Utama) --}}
            <div class="grid grid-cols-3 gap-2 p-1.5 bg-blue-700/60 backdrop-blur-md rounded-2xl border border-blue-400/30">
                <a href="{{ route('user.history', ['category' => 'residence', 'status' => $status]) }}"
                   class="flex items-center justify-center gap-2 py-3 px-3 rounded-xl text-xs sm:text-sm font-bold transition-all duration-200 {{ $category === 'residence' ? 'bg-white text-blue-700 shadow-md scale-[1.02]' : 'text-blue-100 hover:bg-white/10' }}">
                    <i class="fas fa-home text-sm"></i>
                    <span>Hunian</span>
                    @if($residenceCount > 0)
                        <span class="ml-1 px-1.5 py-0.5 rounded-full text-[10px] {{ $category === 'residence' ? 'bg-blue-100 text-blue-700' : 'bg-blue-800 text-blue-200' }}">{{ $residenceCount }}</span>
                    @endif
                </a>

                <a href="{{ route('user.history', ['category' => 'activity', 'status' => $status]) }}"
                   class="flex items-center justify-center gap-2 py-3 px-3 rounded-xl text-xs sm:text-sm font-bold transition-all duration-200 {{ $category === 'activity' ? 'bg-white text-blue-700 shadow-md scale-[1.02]' : 'text-blue-100 hover:bg-white/10' }}">
                    <i class="fas fa-calendar-alt text-sm"></i>
                    <span>Acara</span>
                    @if($activityCount > 0)
                        <span class="ml-1 px-1.5 py-0.5 rounded-full text-[10px] {{ $category === 'activity' ? 'bg-blue-100 text-blue-700' : 'bg-blue-800 text-blue-200' }}">{{ $activityCount }}</span>
                    @endif
                </a>

                <a href="{{ route('user.history', ['category' => 'marketplace', 'status' => $status]) }}"
                   class="flex items-center justify-center gap-2 py-3 px-3 rounded-xl text-xs sm:text-sm font-bold transition-all duration-200 {{ $category === 'marketplace' ? 'bg-white text-blue-700 shadow-md scale-[1.02]' : 'text-blue-100 hover:bg-white/10' }}">
                    <i class="fas fa-store text-sm"></i>
                    <span>Barang</span>
                    @if($marketplaceCount > 0)
                        <span class="ml-1 px-1.5 py-0.5 rounded-full text-[10px] {{ $category === 'marketplace' ? 'bg-blue-100 text-blue-700' : 'bg-blue-800 text-blue-200' }}">{{ $marketplaceCount }}</span>
                    @endif
                </a>
            </div>
        </div>
    </div>

    {{-- ── 2. Status Sub-Filter Navigation ────────────────────────────────── --}}
    <div class="bg-white border-b border-gray-200 shadow-sm sticky top-0 z-10">
        <div class="max-w-4xl mx-auto px-4 overflow-x-auto no-scrollbar">
            <div class="flex items-center space-x-2 py-3">
                @php
                    $statusFilters = [
                        'all' => 'Semua',
                        'pending' => 'Menunggu',
                        'approved' => 'Aktif / Disetujui',
                        'completed' => 'Selesai',
                        'rejected_cancelled' => 'Ditolak / Batal',
                    ];
                @endphp

                @foreach($statusFilters as $stKey => $stLabel)
                <a href="{{ route('user.history', ['category' => $category, 'status' => $stKey]) }}"
                   class="px-4 py-2 rounded-full text-xs font-bold whitespace-nowrap transition-all duration-150 {{ $status === $stKey ? 'bg-blue-600 text-white shadow-sm' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                    {{ $stLabel }}
                </a>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ── 3. Transaction Items List (Card Layout) ───────────────────────── --}}
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 mt-6">

        @forelse($items as $item)
            @php
                // --- Determinisasi Data Berdasarkan Kategori ---
                if ($category === 'residence' || $category === 'activity') {
                    $isResidence = $item->bookable_type === 'App\\Models\\Residence' || str_contains($item->bookable_type, 'Residence');
                    $title       = $item->bookable->name ?? '—';
                    $imageUrl    = $item->bookable->first_image_url ?? null;
                    $createdAt   = $item->created_at->format('d M Y');
                    $amount      = $item->transaction->final_amount ?? ($item->bookable->price ?? 0);
                    $detailUrl   = route('user.bookings.show', $item->id);

                    // Badge Kategori
                    $catBadgeClass = $isResidence ? 'bg-blue-50 text-blue-600' : 'bg-purple-50 text-purple-600';
                    $catIcon       = $isResidence ? 'fa-home' : 'fa-calendar-alt';
                    $catLabel      = $isResidence ? 'Hunian' : 'Acara';

                    // Subtext info
                    if ($isResidence) {
                        $subInfo = $item->check_in_date && $item->check_out_date
                            ? $item->check_in_date->format('d M Y') . ' — ' . $item->check_out_date->format('d M Y')
                            : 'Booking Hunian';
                    } else {
                        $subInfo = $item->bookable->event_date
                            ? 'Pelaksanaan: ' . $item->bookable->event_date->format('d M Y, H:i') . ' WIB'
                            : 'Pendaftaran Event';
                    }

                    // Badge Status
                    $stBadgeClass = match($item->status) {
                        'pending'   => 'bg-amber-50 text-amber-700 border-amber-200',
                        'approved'  => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                        'completed' => 'bg-blue-50 text-blue-700 border-blue-200',
                        'rejected'  => 'bg-rose-50 text-rose-700 border-rose-200',
                        'cancelled' => 'bg-gray-100 text-gray-600 border-gray-200',
                        default     => 'bg-gray-100 text-gray-600 border-gray-200',
                    };
                    $stLabel = match($item->status) {
                        'pending'   => 'Menunggu',
                        'approved'  => 'Disetujui',
                        'completed' => 'Selesai',
                        'rejected'  => 'Ditolak',
                        'cancelled' => 'Dibatalkan',
                        default     => ucfirst($item->status),
                    };

                } else {
                    // Marketplace Barang
                    $title       = $item->product->name ?? 'Produk Barang';
                    $imageUrl    = $item->product->first_image_url ?? null;
                    $createdAt   = $item->created_at->format('d M Y');
                    $amount      = $item->total_amount;
                    $detailUrl   = route('user.marketplace.transactions.show', $item->id);

                    // Badge Kategori
                    $catBadgeClass = 'bg-amber-50 text-amber-600';
                    $catIcon       = 'fa-store';
                    $catLabel      = 'Barang FJB';

                    // Subtext info
                    $subInfo = 'Jumlah: ' . $item->quantity . 'x barang (' . strtoupper($item->pickup_method) . ')';

                    // Badge Status
                    $stBadgeClass = match($item->status) {
                        'pending'    => 'bg-amber-50 text-amber-700 border-amber-200',
                        'processing' => 'bg-purple-50 text-purple-700 border-purple-200',
                        'shipped'    => 'bg-blue-50 text-blue-700 border-blue-200',
                        'completed'  => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                        'cancelled'  => 'bg-gray-100 text-gray-600 border-gray-200',
                        default      => 'bg-gray-100 text-gray-600 border-gray-200',
                    };
                    $stLabel = match($item->status) {
                        'pending'    => 'Menunggu',
                        'processing' => 'Diproses',
                        'shipped'    => 'Dikirim',
                        'completed'  => 'Selesai',
                        'cancelled'  => 'Dibatalkan',
                        default      => ucfirst($item->status),
                    };
                }
            @endphp

            {{-- Card Frame --}}
            <div class="bg-white rounded-2xl border border-gray-200/80 shadow-sm p-4 sm:p-5 mb-4 hover:shadow-md transition-all duration-200">
                <div class="flex items-start gap-3 sm:gap-4">
                    {{-- Image Thumbnail --}}
                    <div class="w-16 h-16 sm:w-20 sm:h-20 bg-gray-100 rounded-xl overflow-hidden flex-shrink-0 border border-gray-100">
                        @if($imageUrl)
                            <img src="{{ $imageUrl }}" alt="{{ $title }}" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-gray-400 bg-gray-50">
                                <i class="fas {{ $catIcon }} text-xl"></i>
                            </div>
                        @endif
                    </div>

                    {{-- Card Info --}}
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between gap-2 mb-1">
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md text-[11px] font-bold {{ $catBadgeClass }}">
                                <i class="fas {{ $catIcon }} text-[10px]"></i> {{ $catLabel }}
                            </span>
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $stBadgeClass }}">
                                {{ $stLabel }}
                            </span>
                        </div>

                        <h3 class="text-sm sm:text-base font-bold text-gray-900 truncate mt-1">
                            {{ $title }}
                        </h3>

                        <p class="text-xs text-gray-400 mt-0.5">Dipesan {{ $createdAt }}</p>
                    </div>
                </div>

                {{-- Card Footer Divider --}}
                <div class="mt-4 pt-3 border-t border-gray-100 flex items-center justify-between gap-2">
                    <div>
                        <p class="text-xs text-gray-500 font-medium truncate max-w-[200px] sm:max-w-xs">{{ $subInfo }}</p>
                        <p class="text-base sm:text-lg font-extrabold text-blue-600 mt-0.5">
                            Rp {{ number_format($amount, 0, ',', '.') }}
                        </p>
                    </div>

                    <a href="{{ $detailUrl }}"
                       class="px-4 py-2 bg-blue-50 hover:bg-blue-100 text-blue-600 font-bold text-xs rounded-xl transition-colors flex items-center gap-1 flex-shrink-0">
                        Lihat Detail <i class="fas fa-chevron-right text-[10px]"></i>
                    </a>
                </div>
            </div>

        @empty
            {{-- State Kosong --}}
            <div class="bg-white rounded-2xl border border-gray-200 p-12 text-center my-6">
                <div class="w-16 h-16 bg-blue-50 text-blue-500 rounded-full flex items-center justify-center mx-auto mb-4 text-2xl">
                    <i class="fas fa-receipt"></i>
                </div>
                <h3 class="text-base font-bold text-gray-900">Belum Ada Transaksi</h3>
                <p class="text-xs text-gray-500 mt-1 max-w-sm mx-auto">
                    Anda belum memiliki riwayat transaksi pada kategori {{ ucfirst($category) }} dengan status {{ $status === 'all' ? 'apapun' : $status }}.
                </p>
                <div class="mt-5">
                    @if($category === 'residence')
                        <a href="{{ route('residences.index') }}" class="px-4 py-2 bg-blue-600 text-white rounded-xl text-xs font-bold hover:bg-blue-700 transition-colors">
                            Cari Hunian Sekarang
                        </a>
                    @elseif($category === 'activity')
                        <a href="{{ route('activities.index') }}" class="px-4 py-2 bg-blue-600 text-white rounded-xl text-xs font-bold hover:bg-blue-700 transition-colors">
                            Jelajahi Acara Kampus
                        </a>
                    @else
                        <a href="{{ route('marketplace.index') }}" class="px-4 py-2 bg-blue-600 text-white rounded-xl text-xs font-bold hover:bg-blue-700 transition-colors">
                            Belanja di Marketplace
                        </a>
                    @endif
                </div>
            </div>
        @endforelse

        {{-- Pagination --}}
        @if($items->hasPages())
        <div class="mt-6">
            {{ $items->links() }}
        </div>
        @endif

    </div>
</div>
@endsection
