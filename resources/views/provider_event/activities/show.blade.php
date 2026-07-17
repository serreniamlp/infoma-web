@extends('layouts.app')

@section('title', $activity->name . ' - Infoma')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">{{ $activity->name }}</h1>
                <p class="text-gray-600 mt-2">Detail kegiatan Anda</p>
            </div>
            <div class="flex items-center space-x-4">
                <a href="{{ route('provider.event.activities.edit', $activity) }}"
                   class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                    <i class="fas fa-edit mr-2"></i>Edit
                </a>
                <a href="{{ route('provider.event.activities.index') }}"
                   class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>Kembali
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Image Gallery -->
                <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                    @if($activity->images && count($activity->images) > 0)
                        <div class="relative">
                            <img src="{{ asset('storage/' . $activity->images[0]) }}"
                                 alt="{{ $activity->name }}"
                                 class="w-full h-96 object-cover" id="mainImage">
                            @if(count($activity->images) > 1)
                                <div class="absolute bottom-4 left-4 right-4">
                                    <div class="flex space-x-2 overflow-x-auto">
                                        @foreach($activity->images as $index => $image)
                                            <img src="{{ asset('storage/' . $image) }}"
                                                 alt="{{ $activity->name }}"
                                                 class="w-16 h-16 object-cover rounded cursor-pointer border-2 {{ $index === 0 ? 'border-green-500' : 'border-white' }}"
                                                 onclick="changeMainImage('{{ asset('storage/' . $image) }}', this)">
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="w-full h-96 bg-gray-200 flex items-center justify-center">
                            <i class="fas fa-calendar-alt text-6xl text-gray-400"></i>
                        </div>
                    @endif
                </div>

                <!-- Activity Details -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="flex items-start justify-between mb-4">
                        <div>
                            <h2 class="text-2xl font-semibold text-gray-900 mb-2">{{ $activity->name }}</h2>
                            <div class="flex items-center text-gray-600 mb-2">
                                <i class="fas fa-map-marker-alt mr-2"></i>
                                <span>{{ $activity->location }}</span>
                            </div>
                            <div class="flex items-center">
                                <span class="px-3 py-1 rounded-full text-sm font-medium {{ $activity->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $activity->is_active ? 'Aktif' : 'Tidak Aktif' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="prose max-w-none">
                        <h3 class="text-lg font-semibold text-gray-900 mb-3">Deskripsi</h3>
                        <p class="text-gray-700 leading-relaxed">{{ $activity->description }}</p>
                    </div>
                </div>

                {{-- PEMATERI --}}
                @if($activity->speakers && count($activity->speakers) > 0)
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        <i class="fas fa-chalkboard-teacher mr-2 text-green-600"></i>Pemateri
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @foreach($activity->speakers as $speaker)
                            @if(!empty($speaker['name']))
                            <div class="flex items-center gap-4 p-4 bg-gray-50 rounded-lg border border-gray-100">
                                <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0">
                                    <span class="text-lg font-bold text-green-700">
                                        {{ strtoupper(substr($speaker['name'], 0, 1)) }}
                                    </span>
                                </div>
                                <div class="min-w-0">
                                    <p class="font-semibold text-gray-900 truncate">{{ $speaker['name'] }}</p>
                                    @if(!empty($speaker['title']))
                                        <p class="text-sm text-gray-500 truncate">{{ $speaker['title'] }}</p>
                                    @endif
                                </div>
                            </div>
                            @endif
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- BENEFIT --}}
                @if($activity->benefits && count(array_filter($activity->benefits)) > 0)
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        <i class="fas fa-gift mr-2 text-green-600"></i>Yang Akan Kamu Dapatkan
                    </h3>
                    <ul class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        @foreach($activity->benefits as $benefit)
                            @if(!empty($benefit))
                            <li class="flex items-center gap-3">
                                <span class="flex-shrink-0 w-6 h-6 rounded-full bg-green-100 flex items-center justify-center">
                                    <i class="fas fa-check text-green-600 text-xs"></i>
                                </span>
                                <span class="text-gray-700 text-sm">{{ $benefit }}</span>
                            </li>
                            @endif
                        @endforeach
                    </ul>
                </div>
                @endif

                <!-- Event Details -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Detail Kegiatan</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="flex items-center">
                            <i class="fas fa-calendar text-green-500 mr-3"></i>
                            <div>
                                <p class="text-sm text-gray-600">Tanggal Kegiatan</p>
                                <p class="font-medium">{{ $activity->event_date->format('d M Y, H:i') }}</p>
                            </div>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-clock text-green-500 mr-3"></i>
                            <div>
                                <p class="text-sm text-gray-600">Batas Pendaftaran</p>
                                <p class="font-medium">{{ $activity->registration_deadline->format('d M Y, H:i') }}</p>
                            </div>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-users text-green-500 mr-3"></i>
                            <div>
                                <p class="text-sm text-gray-600">Kapasitas</p>
                                <p class="font-medium">{{ $activity->capacity }} peserta</p>
                            </div>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-ticket-alt text-green-500 mr-3"></i>
                            <div>
                                <p class="text-sm text-gray-600">Slot Tersisa</p>
                                <p class="font-medium text-green-600">{{ $activity->available_slots }} slot</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ULASAN DARI USER --}}
                @if($activity->ratings && $activity->ratings->count() > 0)
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        <i class="fas fa-star text-yellow-400 mr-2"></i>
                        Ulasan <span class="text-gray-400 font-normal text-base">({{ $activity->ratings->count() }})</span>
                    </h3>
                    <div class="space-y-5">
                        @foreach($activity->ratings as $rating)
                        <div class="{{ !$loop->last ? 'pb-5 border-b border-gray-100' : '' }}">
                            <div class="flex gap-3">
                                <div class="w-9 h-9 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0">
                                    <span class="text-sm font-bold text-green-700">{{ substr($rating->user->name, 0, 1) }}</span>
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
                                    @if($rating->review)
                                        <p class="text-gray-600 text-sm">{{ $rating->review }}</p>
                                    @endif

                                    {{-- Foto ulasan (support multiple/single) --}}
                                    @if($rating->photo_path)
                                        @php
                                            $ratingPhotos = is_array(json_decode($rating->photo_path, true))
                                                ? json_decode($rating->photo_path, true)
                                                : [$rating->photo_path];
                                        @endphp
                                        <div class="flex gap-2 flex-wrap mt-2">
                                            @foreach($ratingPhotos as $rFoto)
                                                <a href="{{ asset('storage/' . $rFoto) }}" target="_blank">
                                                    <img src="{{ asset('storage/' . $rFoto) }}"
                                                         class="h-20 w-20 rounded-lg object-cover border border-gray-200 hover:opacity-90 transition-opacity cursor-zoom-in"
                                                         alt="Foto ulasan {{ $rating->user->name }}">
                                                </a>
                                            @endforeach
                                        </div>
                                    @endif

                                    {{-- Balasan yang sudah ada --}}
                                    @if($rating->provider_reply)
                                        <div class="mt-3 bg-green-50 border border-green-100 rounded-lg p-3">
                                            <div class="flex items-start justify-between">
                                                <p class="text-xs font-semibold text-green-700 mb-1">
                                                    <i class="fas fa-reply mr-1"></i>Balasan Anda
                                                </p>
                                                <button type="button"
                                                        onclick="hapusBalasan({{ $rating->id }})"
                                                        class="text-xs text-red-400 hover:text-red-600 ml-2 flex-shrink-0">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </div>
                                            <p class="text-sm text-gray-700" id="replyText-{{ $rating->id }}">{{ $rating->provider_reply }}</p>
                                        </div>
                                    @endif

                                    {{-- Form balas ulasan --}}
                                    @if(!$rating->provider_reply)
                                        <div class="mt-3" id="replyForm-{{ $rating->id }}">
                                            <div class="flex gap-2">
                                                <input type="text"
                                                       id="replyInput-{{ $rating->id }}"
                                                       placeholder="Tulis balasan..."
                                                       class="flex-1 px-3 py-1.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500">
                                                <button type="button"
                                                        onclick="kirimBalasan({{ $rating->id }}, 'activity')"
                                                        class="px-3 py-1.5 bg-green-600 hover:bg-green-700 text-white text-xs rounded-lg font-medium transition-colors flex-shrink-0">
                                                    <i class="fas fa-reply mr-1"></i>Balas
                                                </button>
                                            </div>
                                        </div>
                                    @else
                                        <button type="button"
                                                onclick="editBalasan({{ $rating->id }})"
                                                class="mt-2 text-xs text-green-600 hover:text-green-700">
                                            <i class="fas fa-pen mr-1"></i>Edit Balasan
                                        </button>
                                        <div class="mt-2 hidden" id="replyFormEdit-{{ $rating->id }}">
                                            <div class="flex gap-2">
                                                <input type="text"
                                                       id="replyInputEdit-{{ $rating->id }}"
                                                       value="{{ $rating->provider_reply }}"
                                                       class="flex-1 px-3 py-1.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500">
                                                <button type="button"
                                                        onclick="kirimBalasan({{ $rating->id }}, 'activity', true)"
                                                        class="px-3 py-1.5 bg-green-600 hover:bg-green-700 text-white text-xs rounded-lg font-medium">
                                                    Simpan
                                                </button>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1">
                <!-- Price Info -->
                <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Informasi Harga</h3>

                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Harga Dasar</span>
                            <span class="font-medium">Rp {{ number_format($activity->price, 0, ',', '.') }}</span>
                        </div>

                        @if($activity->discount_type && $activity->discount_value)
                            <div class="flex justify-between text-green-600">
                                <span>Diskon</span>
                                <span>
                                    @if($activity->discount_type === 'percentage')
                                        {{ $activity->discount_value }}%
                                    @else
                                        Rp {{ number_format($activity->discount_value, 0, ',', '.') }}
                                    @endif
                                </span>
                            </div>

                            <div class="border-t border-gray-200 pt-3">
                                <div class="flex justify-between text-lg font-semibold">
                                    <span>Harga Akhir</span>
                                    <span>Rp {{ number_format($activity->getDiscountedPrice()) }}</span>
                                </div>
                            </div>
                        @endif

                        <div class="text-sm text-gray-500">per peserta</div>
                    </div>
                </div>

                <!-- Activity Stats -->
                <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Statistik</h3>

                    <div class="space-y-4">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Total Booking</span>
                            <span class="font-medium">{{ $activity->bookings()->count() }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Pendapatan</span>
                            <span class="font-medium">Rp {{ number_format($activity->approvedRevenue()) }}</span>
                        </div>
                        @if($activity->ratings_avg_rating)
                            <div class="flex justify-between">
                                <span class="text-gray-600">Rating Rata-rata</span>
                                <span class="font-medium">{{ number_format($activity->ratings_avg_rating, 1) }}/5</span>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Aksi Cepat</h3>

                    <div class="space-y-3">
                        <a href="{{ route('provider.event.activities.edit', $activity) }}"
                           class="w-full bg-green-600 hover:bg-green-700 text-white py-2 px-4 rounded-lg font-medium transition-colors text-center block">
                            <i class="fas fa-edit mr-2"></i>Edit Kegiatan
                        </a>

                        <form method="POST" action="{{ route('provider.event.activities.toggleStatus', $activity) }}" class="block">
                            @csrf
                            @method('PATCH')
                            <button type="submit"
                                    class="w-full bg-{{ $activity->is_active ? 'yellow' : 'green' }}-600 hover:bg-{{ $activity->is_active ? 'yellow' : 'green' }}-700 text-white py-2 px-4 rounded-lg font-medium transition-colors">
                                <i class="fas fa-{{ $activity->is_active ? 'pause' : 'play' }} mr-2"></i>
                                {{ $activity->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                            </button>
                        </form>

                        <a href="{{ route('provider.event.bookings.index', ['activity' => $activity->id]) }}"
                           class="w-full bg-purple-600 hover:bg-purple-700 text-white py-2 px-4 rounded-lg font-medium transition-colors text-center block">
                            <i class="fas fa-bookmark mr-2"></i>Lihat Booking
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
    document.querySelectorAll('.border-green-500').forEach(el => el.classList.remove('border-green-500', 'border-white'));
    element.classList.add('border-green-500');
    element.classList.remove('border-white');
}

const CSRF = document.querySelector('meta[name="csrf-token"]').content;

function kirimBalasan(ratingId, jenis, isEdit = false) {
    const inputId = isEdit ? `replyInputEdit-${ratingId}` : `replyInput-${ratingId}`;
    const teks = document.getElementById(inputId)?.value?.trim();
    if (!teks) { tampilToast('Balasan tidak boleh kosong.', 'error'); return; }

    fetch(`/provider/event/ratings/${ratingId}/reply`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
        body: JSON.stringify({ provider_reply: teks })
    })
    .then(r => r.json())
    .then(data => {
        if (data.status === 'success') {
            tampilToast('Balasan berhasil disimpan.', 'success');
            setTimeout(() => location.reload(), 800);
        } else {
            tampilToast(data.message || 'Gagal menyimpan balasan.', 'error');
        }
    })
    .catch(() => tampilToast('Terjadi kesalahan.', 'error'));
}

function hapusBalasan(ratingId) {
    if (!confirm('Hapus balasan ini?')) return;
    fetch(`/provider/event/ratings/${ratingId}/reply`, {
        method: 'DELETE',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF }
    })
    .then(r => r.json())
    .then(data => {
        if (data.status === 'success') {
            tampilToast('Balasan berhasil dihapus.', 'success');
            setTimeout(() => location.reload(), 800);
        } else {
            tampilToast(data.message || 'Gagal menghapus balasan.', 'error');
        }
    })
    .catch(() => tampilToast('Terjadi kesalahan.', 'error'));
}

function editBalasan(ratingId) {
    document.getElementById(`replyFormEdit-${ratingId}`)?.classList.toggle('hidden');
}

function tampilToast(pesan, tipe = 'success') {
    const t = document.createElement('div');
    t.className = `fixed top-4 right-4 z-50 px-5 py-3 rounded-xl shadow-lg text-sm font-medium text-white ${tipe === 'success' ? 'bg-green-500' : 'bg-red-500'}`;
    t.innerHTML = `<i class="fas fa-${tipe === 'success' ? 'check' : 'exclamation'}-circle mr-2"></i>${pesan}`;
    document.body.appendChild(t);
    setTimeout(() => t.remove(), 3000);
}
</script>
@endpush
@endsection
