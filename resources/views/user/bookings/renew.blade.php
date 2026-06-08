@extends('layouts.app')

@section('title', 'Perpanjang Sewa - EduLiving')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Header --}}
        <div class="mb-8">
            <nav class="mb-3">
                <ol class="flex items-center space-x-2 text-sm text-gray-500">
                    <li><a href="{{ route('user.bookings.index') }}" class="hover:text-green-600">Booking Saya</a></li>
                    <li><i class="fas fa-chevron-right text-xs"></i></li>
                    <li><a href="{{ route('user.bookings.show', $booking) }}" class="hover:text-green-600">Detail Booking</a></li>
                    <li><i class="fas fa-chevron-right text-xs"></i></li>
                    <li class="text-gray-900">Perpanjang Sewa</li>
                </ol>
            </nav>
            <h1 class="text-3xl font-bold text-gray-900">Perpanjang Sewa</h1>
            <p class="text-gray-600 mt-1">Pilih durasi perpanjangan untuk hunian Anda</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            {{-- Form --}}
            <div class="lg:col-span-2 space-y-6">

                {{-- Info hunian --}}
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Hunian</h2>
                    <div class="flex items-start gap-4">
                        <div class="w-16 h-16 bg-gray-100 rounded-lg overflow-hidden flex-shrink-0">
                            @if($residence->images && count($residence->images) > 0)
                                <img src="{{ asset('storage/' . $residence->images[0]) }}"
                                     alt="{{ $residence->name }}"
                                     class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center">
                                    <i class="fas fa-building text-gray-400 text-xl"></i>
                                </div>
                            @endif
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900">{{ $residence->name }}</h3>
                            <p class="text-sm text-gray-500 mt-0.5">
                                <i class="fas fa-map-marker-alt mr-1 text-green-500"></i>
                                {{ $residence->address }}
                            </p>
                            <p class="text-sm text-gray-500 mt-1">
                                Sewa aktif: {{ $booking->check_in_date->format('d M Y') }} —
                                {{ $booking->check_out_date->format('d M Y') }}
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Form perpanjangan --}}
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-5">Pilih Durasi Perpanjangan</h2>

                    <form method="POST" action="{{ route('user.bookings.renew.store', $booking) }}">
                        @csrf

                        <div class="mb-6">
                            <label for="duration_months" class="block text-sm font-medium text-gray-700 mb-2">
                                Durasi <span class="text-red-500">*</span>
                            </label>

                            @if($residence->rental_period === 'yearly')
                                <select name="duration_months" id="duration_months" required
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                                    @foreach([12, 24, 36] as $m)
                                        <option value="{{ $m }}" {{ old('duration_months', 12) == $m ? 'selected' : '' }}>
                                            {{ $m / 12 }} Tahun
                                        </option>
                                    @endforeach
                                </select>
                                <p class="text-xs text-gray-400 mt-1">Hunian ini disewakan per tahun</p>
                            @else
                                <select name="duration_months" id="duration_months" required
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                                    @foreach([1,2,3,4,5,6,7,8,9,10,11,12] as $m)
                                        <option value="{{ $m }}" {{ old('duration_months', 1) == $m ? 'selected' : '' }}>
                                            {{ $m }} Bulan
                                        </option>
                                    @endforeach
                                    <option value="18" {{ old('duration_months') == 18 ? 'selected' : '' }}>18 Bulan</option>
                                    <option value="24" {{ old('duration_months') == 24 ? 'selected' : '' }}>24 Bulan</option>
                                </select>
                            @endif

                            @error('duration_months')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Info periode baru --}}
                        <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg text-sm">
                            <p class="font-medium text-green-800 mb-2">
                                <i class="fas fa-calendar-alt mr-1"></i> Periode Sewa Baru
                            </p>
                            <div class="flex justify-between text-green-700">
                                <span>Mulai</span>
                                <span class="font-medium">{{ $booking->check_out_date->format('d M Y') }}</span>
                            </div>
                            <div class="flex justify-between text-green-700 mt-1">
                                <span>Berakhir</span>
                                <span class="font-medium" id="new-checkout">-</span>
                            </div>
                        </div>

                        <div class="flex justify-end gap-3">
                            <a href="{{ route('user.bookings.show', $booking) }}"
                               class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 font-medium transition-colors">
                                Batal
                            </a>
                            <button type="submit"
                                    class="px-6 py-3 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition-colors">
                                <i class="fas fa-redo mr-2"></i>Ajukan Perpanjangan
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Sidebar ringkasan --}}
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl shadow-sm p-6 sticky top-8">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Estimasi Biaya</h3>

                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-500">
                                Harga per {{ $residence->rental_period === 'yearly' ? 'Tahun' : 'Bulan' }}
                            </span>
                            <span class="font-medium">Rp {{ number_format($residence->getDiscountedPrice(), 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between text-gray-500">
                            <span>Durasi</span>
                            <span id="summary-duration">
                                {{ $residence->rental_period === 'yearly' ? '1 tahun' : '1 bulan' }}
                            </span>
                        </div>
                        <div class="border-t border-gray-200 pt-3 flex justify-between text-base font-bold">
                            <span>Estimasi Total</span>
                            <span class="text-green-700" id="summary-total">
                                Rp {{ number_format($residence->getDiscountedPrice(), 0, ',', '.') }}
                            </span>
                        </div>
                    </div>

                    <div class="mt-5 p-3 bg-blue-50 rounded-lg text-xs text-blue-700">
                        <i class="fas fa-info-circle mr-1"></i>
                        Perpanjangan perlu disetujui penyedia terlebih dahulu sebelum pembayaran dilakukan.
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const DISCOUNTED_PRICE  = {{ $residence->getDiscountedPrice() }};
const IS_YEARLY         = {{ $residence->rental_period === 'yearly' ? 'true' : 'false' }};
const BASE_CHECKOUT     = '{{ $booking->check_out_date->toDateString() }}';

const months = ['Januari','Februari','Maret','April','Mei','Juni',
                'Juli','Agustus','September','Oktober','November','Desember'];

function formatTanggal(dateStr) {
    const d = new Date(dateStr + 'T00:00:00');
    return d.getDate() + ' ' + months[d.getMonth()] + ' ' + d.getFullYear();
}

function updateSummary() {
    const durationMonths = parseInt(document.getElementById('duration_months').value) || (IS_YEARLY ? 12 : 1);

    // Hitung total
    const total = IS_YEARLY
        ? DISCOUNTED_PRICE * (durationMonths / 12)
        : DISCOUNTED_PRICE * durationMonths;

    document.getElementById('summary-total').textContent =
        'Rp ' + Math.round(total).toLocaleString('id-ID');

    // Label durasi
    document.getElementById('summary-duration').textContent = IS_YEARLY
        ? (durationMonths / 12) + ' tahun'
        : durationMonths + ' bulan';

    // Hitung tanggal checkout baru
    const parts = BASE_CHECKOUT.split('-');
    const checkOut = new Date(Date.UTC(+parts[0], +parts[1] - 1, +parts[2]));
    checkOut.setUTCMonth(checkOut.getUTCMonth() + durationMonths);

    const y = checkOut.getUTCFullYear();
    const m = String(checkOut.getUTCMonth() + 1).padStart(2, '0');
    const dd = String(checkOut.getUTCDate()).padStart(2, '0');

    document.getElementById('new-checkout').textContent = formatTanggal(`${y}-${m}-${dd}`);
}

document.getElementById('duration_months').addEventListener('change', updateSummary);
updateSummary();
</script>
@endpush
