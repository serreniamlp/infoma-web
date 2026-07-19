@extends('layouts.app')

@section('title', 'Detail Booking - Infoma')

@php
use Illuminate\Support\Str;
@endphp

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Detail Booking</h1>
                <p class="text-gray-600 mt-2">Booking #{{ $booking->booking_code }}</p>
            </div>
            <div class="flex items-center space-x-4">
                <a href="{{ route('provider.event.bookings.index') }}"
                   class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>Kembali
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Booking Status -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold text-gray-900">Status Booking</h2>
                        <span class="px-3 py-1 rounded-full text-sm font-medium
                            @if($booking->status === 'pending') bg-yellow-100 text-yellow-800
                            @elseif($booking->status === 'approved') bg-green-100 text-green-800
                            @elseif($booking->status === 'rejected') bg-red-100 text-red-800
                            @elseif($booking->status === 'completed') bg-blue-100 text-blue-800
                            @else bg-gray-100 text-gray-800 @endif">
                            {{ ucfirst($booking->status) }}
                        </span>
                    </div>

                    @if($booking->status === 'pending')
                        <div class="flex space-x-3">
                            <form method="POST" action="{{ route('provider.event.bookings.approve', $booking) }}" class="inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium transition-colors"
                                        onclick="return confirm('Setujui booking ini?')">
                                    <i class="fas fa-check mr-2"></i>Setujui
                                </button>
                            </form>
                            <button onclick="openRejectModal()" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                                <i class="fas fa-times mr-2"></i>Tolak
                            </button>
                        </div>
                    @endif

                    @if($booking->rejection_reason)
                        <div class="mt-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                            <h4 class="text-sm font-medium text-red-800 mb-2">Alasan Penolakan:</h4>
                            <p class="text-sm text-red-700">{{ $booking->rejection_reason }}</p>
                        </div>
                    @endif
                </div>

                <!-- Item Details -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Detail Item</h2>

                    <div class="flex items-start space-x-4">
                        <div class="flex-shrink-0">
                            @if($booking->bookable->images && is_array($booking->bookable->images) && count($booking->bookable->images) > 0)
                                <img src="{{ asset('storage/' . $booking->bookable->images[0]) }}"
                                     alt="{{ $booking->bookable->name }}"
                                     class="w-20 h-20 object-cover rounded-lg">
                            @else
                                <div class="w-20 h-20 bg-gray-200 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-{{ $booking->bookable_type === 'App\\Models\\Residence' ? 'building' : 'calendar-alt' }} text-gray-400 text-xl"></i>
                                </div>
                            @endif
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-medium text-gray-900">{{ $booking->bookable->name }}</h3>
                            <p class="text-sm text-gray-600 mb-2">{{ $booking->bookable_type === 'App\\Models\\Residence' ? 'Residence' : 'Kegiatan' }}</p>
                            <p class="text-sm text-gray-700">{{ Str::limit($booking->bookable->description, 150) }}</p>
                        </div>
                    </div>
                </div>

                <!-- Booking Details -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Detail Booking</h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-medium text-gray-600">Tanggal Booking</label>
                            <p class="text-sm text-gray-900">{{ $booking->created_at->format('d M Y, H:i') }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-600">Kode Booking</label>
                            <p class="text-sm text-gray-900 font-mono">#{{ $booking->booking_code }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-600">
                                @if($booking->bookable_type === 'App\\Models\\Residence')
                                    Check-in Date
                                @else
                                    Tanggal Kegiatan
                                @endif
                            </label>
                            <p class="text-sm text-gray-900">{{ $booking->check_in_date->format('d M Y, H:i') }}</p>
                        </div>
                        @if($booking->check_out_date)
                        <div>
                            <label class="text-sm font-medium text-gray-600">Check-out Date</label>
                            <p class="text-sm text-gray-900">{{ $booking->check_out_date->format('d M Y, H:i') }}</p>
                        </div>
                        @endif
                    </div>

                    @if($booking->notes)
                        <div class="mt-4">
                            <label class="text-sm font-medium text-gray-600">Catatan</label>
                            <p class="text-sm text-gray-900 mt-1">{{ $booking->notes }}</p>
                        </div>
                    @endif
                </div>

                <!-- Documents -->
                @if($booking->documents && count($booking->documents) > 0)
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center justify-between">
                        <span><i class="fas fa-id-card text-blue-600 mr-2"></i>Dokumen Peserta / Pendaftar</span>
                        <span class="text-xs font-normal text-gray-500">{{ count($booking->documents) }} dokumen</span>
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @foreach($booking->documents as $index => $document)
                            @php
                                $docPath = is_array($document) ? ($document['path'] ?? '') : $document;
                                $docName = is_array($document) ? ($document['name'] ?? 'Dokumen ' . ($index + 1)) : 'Dokumen ' . ($index + 1);
                                $docType = is_array($document) ? ($document['type'] ?? '') : '';
                                $ext = strtolower(pathinfo($docPath, PATHINFO_EXTENSION));
                                $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'webp']) || str_contains($docType, 'image');
                                $isPdf = $ext === 'pdf' || str_contains($docType, 'pdf');
                                $fileUrl = asset('storage/' . $docPath);
                            @endphp

                            <div class="border border-gray-200 rounded-xl overflow-hidden bg-gray-50 flex flex-col shadow-sm">
                                <div class="p-3 bg-white border-b border-gray-200 flex items-center justify-between">
                                    <div class="flex items-center min-w-0 mr-2">
                                        @if($isPdf)
                                            <i class="fas fa-file-pdf text-red-500 text-lg mr-2 flex-shrink-0"></i>
                                        @elseif($isImage)
                                            <i class="fas fa-file-image text-blue-500 text-lg mr-2 flex-shrink-0"></i>
                                        @else
                                            <i class="fas fa-file-alt text-gray-500 text-lg mr-2 flex-shrink-0"></i>
                                        @endif
                                        <span class="text-sm font-medium text-gray-900 truncate" title="{{ $docName }}">{{ $docName }}</span>
                                    </div>
                                    <div class="flex items-center gap-2 flex-shrink-0">
                                        <a href="{{ $fileUrl }}" target="_blank" class="text-xs bg-blue-50 text-blue-600 hover:bg-blue-100 px-2.5 py-1 rounded font-medium transition-colors inline-flex items-center" title="Buka di Tab Baru">
                                            <i class="fas fa-external-link-alt mr-1"></i>Buka
                                        </a>
                                        <a href="{{ $fileUrl }}" download class="text-xs bg-gray-100 text-gray-600 hover:bg-gray-200 px-2.5 py-1 rounded font-medium transition-colors inline-flex items-center" title="Unduh File">
                                            <i class="fas fa-download"></i>
                                        </a>
                                    </div>
                                </div>

                                {{-- Preview Content --}}
                                <div class="flex-1 flex items-center justify-center p-3 bg-gray-100 min-h-[220px]">
                                    @if($isImage)
                                        <a href="{{ $fileUrl }}" target="_blank" class="group relative w-full h-56 flex items-center justify-center overflow-hidden rounded-lg bg-gray-900/5 border border-gray-200">
                                            <img src="{{ $fileUrl }}" alt="{{ $docName }}" class="max-h-56 w-auto object-contain transition-transform duration-200 group-hover:scale-105">
                                            <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 flex items-center justify-center text-white text-xs font-semibold transition-opacity duration-200">
                                                <i class="fas fa-search-plus mr-1.5 text-base"></i> Klik untuk Memperbesar
                                            </div>
                                        </a>
                                    @elseif($isPdf)
                                        <div class="w-full h-56 rounded-lg overflow-hidden border border-gray-200 bg-white">
                                            <iframe src="{{ $fileUrl }}#toolbar=0" class="w-full h-full border-none"></iframe>
                                        </div>
                                    @else
                                        <div class="text-center p-6">
                                            <i class="fas fa-file-alt text-gray-400 text-4xl mb-2"></i>
                                            <p class="text-xs text-gray-500">Pratinjau tidak tersedia</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1">
                <!-- User Information -->
                <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Informasi User</h3>

                    <div class="space-y-3">
                        <div>
                            <label class="text-sm font-medium text-gray-600">Nama</label>
                            <p class="text-sm text-gray-900">{{ $booking->user->name }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-600">Email</label>
                            <p class="text-sm text-gray-900">{{ $booking->user->email }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-600">Member Sejak</label>
                            <p class="text-sm text-gray-900">{{ $booking->user->created_at->format('d M Y') }}</p>
                        </div>
                    </div>
                </div>

                <!-- Payment Information -->
                @if($booking->transaction)
                <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Informasi Pembayaran</h3>

                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">Status Pembayaran</span>
                            <span class="px-2 py-1 rounded-full text-xs font-medium
                                @if($booking->transaction->payment_status === 'paid') bg-green-100 text-green-800
                                @elseif($booking->transaction->payment_status === 'pending') bg-yellow-100 text-yellow-800
                                @else bg-red-100 text-red-800 @endif">
                                {{ ucfirst($booking->transaction->payment_status) }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">Total Harga</span>
                            <span class="text-sm font-medium">Rp {{ number_format($booking->transaction->total_amount) }}</span>
                        </div>
                        @if($booking->transaction->discount_amount > 0)
                        <div class="flex justify-between text-green-600">
                            <span class="text-sm">Diskon</span>
                            <span class="text-sm">- Rp {{ number_format($booking->transaction->discount_amount) }}</span>
                        </div>
                        @endif
                        <div class="border-t border-gray-200 pt-3">
                            <div class="flex justify-between">
                                <span class="text-sm font-medium text-gray-900">Total Bayar</span>
                                <span class="text-sm font-bold text-gray-900">Rp {{ number_format($booking->transaction->final_amount) }}</span>
                            </div>
                        </div>
                        @if($booking->transaction->payment_method)
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">Metode Pembayaran</span>
                            <span class="text-sm text-gray-900">
                                {{ $booking->transaction->payment_method === 'manual_transfer' ? 'Transfer Bank Manual' : ucfirst($booking->transaction->payment_method) }}
                            </span>
                        </div>
                        @endif

                        {{-- Bukti Transfer Manual --}}
                        @if($booking->transaction->payment_method === 'manual_transfer' && $booking->transaction->payment_proof)
                        <div class="border-t border-gray-150 pt-3 space-y-2">
                            <span class="block text-sm font-semibold text-gray-700">Bukti Pembayaran</span>
                            <a href="{{ asset('storage/' . $booking->transaction->payment_proof) }}" target="_blank" class="block group relative overflow-hidden rounded-lg border border-gray-200">
                                <img src="{{ asset('storage/' . $booking->transaction->payment_proof) }}" alt="Bukti Transfer" class="w-full h-32 object-cover group-hover:scale-105 transition-transform duration-200">
                                <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 flex items-center justify-center text-white text-xs font-semibold transition-opacity duration-200">
                                    <i class="fas fa-search-plus mr-1"></i> Perbesar Bukti
                                </div>
                            </a>
                        </div>

                        {{-- Tombol Verifikasi & Penolakan --}}
                        @if($booking->transaction->payment_status === 'pending')
                        <div class="pt-3 space-y-2">
                            <form method="POST" action="{{ route('provider.event.bookings.verifyPayment', $booking) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-2.5 px-4 rounded-lg text-sm transition-colors flex items-center justify-center gap-2" onclick="return confirm('Apakah Anda yakin ingin memverifikasi dan menyetujui pembayaran manual ini?')">
                                    <i class="fas fa-check-circle"></i>
                                    Setujui Pembayaran
                                </button>
                            </form>

                            <form method="POST" action="{{ route('provider.event.bookings.rejectPayment', $booking) }}" onsubmit="event.preventDefault(); let reason = prompt('Masukkan alasan penolakan bukti transfer ini:'); if (reason === null) return false; if (!reason.trim()) { alert('Alasan penolakan tidak boleh kosong.'); return false; } let input = document.createElement('input'); input.type = 'hidden'; input.name = 'payment_rejection_reason'; input.value = reason; this.appendChild(input); this.submit();">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white font-semibold py-2.5 px-4 rounded-lg text-sm transition-colors flex items-center justify-center gap-2">
                                    <i class="fas fa-times-circle"></i>
                                    Tolak Pembayaran
                                </button>
                            </form>
                        </div>
                        @endif
                        @endif
                    </div>
                </div>
                @endif

                <!-- Quick Actions -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Aksi Cepat</h3>

                    <div class="space-y-3">
                        <a href="{{ route('provider.event.bookings.index') }}"
                           class="w-full bg-gray-600 hover:bg-gray-700 text-white py-2 px-4 rounded-lg font-medium transition-colors text-center block">
                            <i class="fas fa-list mr-2"></i>Lihat Semua Booking
                        </a>

                        @if($booking->bookable_type === 'App\\Models\\Residence')
                            <a href="{{ route('provider.residence.residences.show', $booking->bookable) }}"
                               class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-lg font-medium transition-colors text-center block">
                                <i class="fas fa-building mr-2"></i>Lihat Residence
                            </a>
                        @else
                            <a href="{{ route('provider.event.activities.show', $booking->bookable) }}"
                               class="w-full bg-green-600 hover:bg-green-700 text-white py-2 px-4 rounded-lg font-medium transition-colors text-center block">
                                <i class="fas fa-calendar-alt mr-2"></i>Lihat Kegiatan
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div id="rejectModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Tolak Booking</h3>
            <form method="POST" action="{{ route('provider.event.bookings.reject', $booking) }}">
                @csrf
                @method('PATCH')
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Alasan Penolakan</label>
                    <textarea name="rejection_reason" rows="3" required
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent"
                              placeholder="Berikan alasan penolakan..."></textarea>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Catatan (Opsional)</label>
                    <textarea name="notes" rows="2"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent"
                              placeholder="Catatan tambahan..."></textarea>
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeRejectModal()"
                            class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400">
                        Batal
                    </button>
                    <button type="submit"
                            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                        Tolak Booking
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function openRejectModal() {
    document.getElementById('rejectModal').classList.remove('hidden');
}

function closeRejectModal() {
    document.getElementById('rejectModal').classList.add('hidden');
    document.querySelector('#rejectModal form').reset();
}

// Close modal when clicking outside
document.getElementById('rejectModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeRejectModal();
    }
});
</script>
@endpush
@endsection
