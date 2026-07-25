@extends('layouts.app')
@section('title', 'Laporan Keuangan & e-Statement — Provider Event')

@section('content')
<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- ── 1. Kop Surat Resmi untuk Cetak / PDF (Print Only) ──────────────── --}}
        <div class="hidden print:block border-b-4 border-double border-gray-800 pb-4 mb-6">
            <div class="text-center">
                <h1 class="text-3xl font-extrabold uppercase tracking-wider text-gray-900">EDULIVING INDONESIA</h1>
                <p class="text-xs text-gray-500 font-medium tracking-widest mt-1 uppercase">Platform Informasi & Penyewaan Mahasiswa Terintegrasi</p>
                <div class="text-xs text-gray-400 mt-0.5">Email: support@eduliving.com | Website: eduliving.com</div>
                <h2 class="text-xl font-bold mt-4 pt-3 border-t border-gray-200 uppercase tracking-wide text-gray-800">E-STATEMENT / LAPORAN KEUANGAN RESMI EVENT</h2>
                <p class="text-sm text-gray-600 mt-1">Periode Laporan: <strong>{{ $dateFrom->translatedFormat('d F Y') }}</strong> s/d <strong>{{ $dateTo->translatedFormat('d F Y') }}</strong></p>
            </div>
            <div class="mt-6 grid grid-cols-2 gap-4 text-xs text-gray-700">
                <div>
                    <p class="mb-1"><strong>Nama Provider:</strong> {{ auth()->user()->name }}</p>
                    <p class="mb-1"><strong>Email Mitra:</strong> {{ auth()->user()->email }}</p>
                    <p><strong>Status Kemitraan:</strong> Aktif (Provider Event)</p>
                </div>
                <div class="text-right">
                    <p class="mb-1"><strong>Tanggal Cetak:</strong> {{ now()->translatedFormat('d F Y, H:i') }} WIB</p>
                    <p><strong>Dicetak Oleh:</strong> {{ auth()->user()->name }}</p>
                </div>
            </div>
        </div>

        {{-- ── 2. Header Web & Quick Actions (Web Only) ──────────────────────── --}}
        <div class="flex items-center justify-between mb-6 print:hidden">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                    <i class="fas fa-file-invoice-dollar text-purple-600"></i>
                    Laporan Keuangan & e-Statement Event
                </h1>
                <p class="text-gray-500 text-sm mt-1">
                    Periode Laporan Aktif: <strong>{{ $dateFrom->translatedFormat('d M Y') }}</strong> — <strong>{{ $dateTo->translatedFormat('d M Y') }}</strong>
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('provider.event.dashboard') }}"
                   class="px-4 py-2 border border-gray-300 hover:bg-gray-50 text-gray-700 rounded-lg text-sm font-medium flex items-center gap-1.5 transition-all">
                    <i class="fas fa-arrow-left text-xs"></i> Kembali ke Dashboard
                </a>
            </div>
        </div>

        {{-- ── 3. Filter Periode Bebas & Action Bar (Web Only) ──────────────── --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 mb-6 print:hidden">
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
                <form method="GET" class="flex gap-3 flex-wrap items-end">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Pilih Periode Laporan</label>
                        <select name="period" onchange="toggleCustomDate(this.value); this.form.submit()"
                                class="px-3 py-2 border border-gray-300 rounded-lg text-sm font-medium focus:ring-2 focus:ring-purple-500">
                            <option value="this_month"    {{ $period === 'this_month'    ? 'selected' : '' }}>Bulan Ini ({{ now()->translatedFormat('F Y') }})</option>
                            <option value="last_month"    {{ $period === 'last_month'    ? 'selected' : '' }}>Bulan Lalu</option>
                            <option value="last_6_months" {{ $period === 'last_6_months' ? 'selected' : '' }}>6 Bulan Terakhir</option>
                            <option value="this_year"     {{ $period === 'this_year'     ? 'selected' : '' }}>Tahun Ini ({{ now()->year }})</option>
                            <option value="custom"        {{ $period === 'custom'        ? 'selected' : '' }}>Kustom Rentang Tanggal</option>
                        </select>
                    </div>
                    <div id="custom-date" class="{{ $period === 'custom' ? '' : 'hidden' }} flex gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Dari Tanggal</label>
                            <input type="date" name="date_from"
                                   value="{{ $period === 'custom' ? request('date_from') : '' }}"
                                   class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Sampai Tanggal</label>
                            <input type="date" name="date_to"
                                   value="{{ $period === 'custom' ? request('date_to') : '' }}"
                                   class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                        </div>
                        <div class="self-end">
                            <button type="submit"
                                    class="px-4 py-2 bg-purple-600 text-white rounded-lg text-sm font-medium hover:bg-purple-700 transition-colors">
                                Terapkan
                            </button>
                        </div>
                    </div>
                </form>

                <div class="flex items-center gap-2">
                    <a href="{{ route('provider.event.report.export', request()->all()) }}"
                       class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm font-semibold flex items-center gap-2 transition-colors shadow-sm">
                        <i class="fas fa-file-excel"></i> Unduh Excel
                    </a>
                    <button onclick="window.print()"
                            class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg text-sm font-semibold flex items-center gap-2 transition-colors shadow-sm">
                        <i class="fas fa-print"></i> Cetak / Simpan PDF
                    </button>
                </div>
            </div>
        </div>

        {{-- ── 4. Akses Cepat e-Statement Bulanan (Gaya Bank Mandiri - Web Only) ── --}}
        @if(isset($monthlyStatements) && count($monthlyStatements) > 0)
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 mb-6 print:hidden">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-purple-50 text-purple-600 rounded-lg flex items-center justify-center">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-900 text-sm">e-Statement Bulanan (Akses Cepat {{ now()->year }})</h3>
                        <p class="text-xs text-gray-500">Pilih bulan di bawah ini untuk langsung mengunduh atau mencetak e-Statement per bulan</p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3 mt-4">
                @foreach($monthlyStatements as $m)
                @php
                    $isCurrentMonthActive = $period === 'custom' && request('date_from') === $m['date_from'] && request('date_to') === $m['date_to'];
                @endphp
                <div class="border {{ $isCurrentMonthActive ? 'border-purple-500 bg-purple-50/40' : 'border-gray-200 bg-white' }} hover:border-purple-400 rounded-xl p-3.5 flex flex-col justify-between transition-all shadow-sm">
                    <div class="flex items-center justify-between mb-2">
                        <span class="font-bold text-gray-900 text-sm">{{ $m['month_name'] }}</span>
                        <span class="text-xs font-mono text-gray-400">{{ $m['year'] }}</span>
                    </div>
                    <div class="flex items-center gap-2 pt-2 border-t border-gray-100">
                        <a href="{{ route('provider.event.report.export', ['period' => 'custom', 'date_from' => $m['date_from'], 'date_to' => $m['date_to']]) }}"
                           class="flex-1 text-center py-1.5 px-2 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 rounded-lg text-xs font-semibold transition-colors flex items-center justify-center gap-1">
                            <i class="fas fa-file-excel text-xs"></i> Excel
                        </a>
                        <a href="{{ route('provider.event.report', ['period' => 'custom', 'date_from' => $m['date_from'], 'date_to' => $m['date_to']]) }}"
                           class="flex-1 text-center py-1.5 px-2 bg-purple-50 hover:bg-purple-100 text-purple-700 rounded-lg text-xs font-semibold transition-colors flex items-center justify-center gap-1">
                            <i class="fas fa-file-alt text-xs"></i> Laporan
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- ── 5. Ringkasan Finansial (4 Cards Utama - Web & Print) ─────────── --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            {{-- Total Pendapatan --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
                <div class="flex items-center gap-3 mb-2">
                    <div class="w-9 h-9 bg-green-50 rounded-lg flex items-center justify-center text-green-600">
                        <i class="fas fa-wallet"></i>
                    </div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Pendapatan</p>
                </div>
                <p class="text-2xl font-extrabold text-gray-900">
                    Rp {{ number_format($summary['total_revenue'], 0, ',', '.') }}
                </p>
                <p class="text-xs text-gray-400 mt-1">Pendaftaran Lunas Periode Ini</p>
            </div>

            {{-- Pendaftaran Lunas --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
                <div class="flex items-center gap-3 mb-2">
                    <div class="w-9 h-9 bg-purple-50 rounded-lg flex items-center justify-center text-purple-600">
                        <i class="fas fa-ticket-alt"></i>
                    </div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Peserta Terdaftar</p>
                </div>
                <p class="text-2xl font-extrabold text-purple-700">{{ $summary['approved_bookings'] + $summary['completed_bookings'] }}</p>
                <p class="text-xs text-gray-400 mt-1">Pendaftaran Terdaftar & Selesai</p>
            </div>

            {{-- Pending Pembayaran --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
                <div class="flex items-center gap-3 mb-2">
                    <div class="w-9 h-9 bg-amber-50 rounded-lg flex items-center justify-center text-amber-600">
                        <i class="fas fa-clock"></i>
                    </div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Pending / Menunggu</p>
                </div>
                <p class="text-2xl font-extrabold text-amber-600">{{ $summary['pending_bookings'] }}</p>
                <p class="text-xs text-gray-400 mt-1">Menunggu Pembayaran</p>
            </div>

            {{-- Rata-rata Nominal --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
                <div class="flex items-center gap-3 mb-2">
                    <div class="w-9 h-9 bg-blue-50 rounded-lg flex items-center justify-center text-blue-600">
                        <i class="fas fa-calculator"></i>
                    </div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Rata-rata Tiket</p>
                </div>
                @php
                    $paidCount = $summary['approved_bookings'] + $summary['completed_bookings'];
                    $avgBooking = $paidCount > 0 ? $summary['booking_revenue'] / $paidCount : 0;
                @endphp
                <p class="text-2xl font-extrabold text-blue-700">
                    Rp {{ number_format($avgBooking, 0, ',', '.') }}
                </p>
                <p class="text-xs text-gray-400 mt-1">Nominal Rata-rata</p>
            </div>
        </div>

        {{-- ── 6. Tabel Histori Transaksi (Gaya Bank BNI e-Statement) ────────── --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between bg-gray-50/50">
                <div>
                    <h3 class="font-bold text-gray-900 text-base">Histori & Rincian Pendaftaran Event</h3>
                    <p class="text-xs text-gray-500 mt-0.5">Daftar pendaftaran event pada periode <strong>{{ $dateFrom->format('d M Y') }}</strong> — <strong>{{ $dateTo->format('d M Y') }}</strong></p>
                </div>
                <span class="text-xs bg-gray-100 text-gray-700 px-3 py-1 rounded-full font-semibold border border-gray-200">
                    Total: {{ $bookingDetails->total() }} Pendaftaran
                </span>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-left">
                    <thead class="bg-gray-100/70">
                        <tr>
                            <th class="px-4 py-3.5 text-xs font-bold text-gray-700 uppercase tracking-wider">Tgl Transaksi</th>
                            <th class="px-4 py-3.5 text-xs font-bold text-gray-700 uppercase tracking-wider">Kode Booking</th>
                            <th class="px-4 py-3.5 text-xs font-bold text-gray-700 uppercase tracking-wider">Nama Peserta</th>
                            <th class="px-4 py-3.5 text-xs font-bold text-gray-700 uppercase tracking-wider">Nama Event</th>
                            <th class="px-4 py-3.5 text-xs font-bold text-gray-700 uppercase tracking-wider">Metode Bayar</th>
                            <th class="px-4 py-3.5 text-xs font-bold text-gray-700 uppercase tracking-wider">Status</th>
                            <th class="px-4 py-3.5 text-xs font-bold text-gray-700 uppercase tracking-wider text-right">Nominal (Rp)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @forelse($bookingDetails as $booking)
                        <tr class="hover:bg-gray-50/80 transition-colors {{ in_array($booking->status, ['rejected', 'cancelled']) ? 'print:hidden' : '' }}">
                            <td class="px-4 py-3.5 text-xs text-gray-600 whitespace-nowrap">
                                {{ $booking->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-4 py-3.5">
                                <span class="text-xs font-mono font-semibold text-gray-800">#{{ $booking->booking_code }}</span>
                            </td>
                            <td class="px-4 py-3.5 text-sm font-medium text-gray-900">
                                {{ $booking->user->name ?? '—' }}
                            </td>
                            <td class="px-4 py-3.5 text-xs text-gray-700 max-w-[180px] truncate">
                                {{ $booking->bookable->name ?? '—' }}
                            </td>
                            <td class="px-4 py-3.5 text-xs text-gray-600 whitespace-nowrap">
                                @if($booking->transaction && $booking->transaction->payment_method)
                                    {{ $booking->transaction->payment_method === 'manual_transfer' ? 'Transfer Bank' : ucfirst($booking->transaction->payment_method) }}
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                @php
                                    $badge = match($booking->status) {
                                        'pending'   => 'bg-amber-50 text-amber-700 border-amber-200',
                                        'approved'  => 'bg-purple-50 text-purple-700 border-purple-200',
                                        'completed' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                        'rejected'  => 'bg-rose-50 text-rose-700 border-rose-200',
                                        'cancelled' => 'bg-gray-100 text-gray-600 border-gray-200',
                                        default     => 'bg-gray-100 text-gray-600 border-gray-200',
                                    };
                                    $label = match($booking->status) {
                                        'pending'   => 'Pending',
                                        'approved'  => 'Terdaftar',
                                        'completed' => 'Selesai',
                                        'rejected'  => 'Ditolak',
                                        'cancelled' => 'Dibatalkan',
                                        default     => ucfirst($booking->status),
                                    };
                                @endphp
                                <span class="px-2.5 py-0.5 rounded-md text-xs font-semibold border {{ $badge }}">
                                    {{ $label }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 text-right font-semibold text-sm whitespace-nowrap">
                                @if($booking->transaction)
                                    <span class="{{ $booking->transaction->payment_status === 'paid' ? 'text-emerald-700 font-bold' : 'text-gray-400' }}">
                                        Rp {{ number_format($booking->transaction->final_amount, 0, ',', '.') }}
                                    </span>
                                @else
                                    <span class="text-gray-300">—</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-5 py-12 text-center text-gray-400">
                                <div class="flex flex-col items-center">
                                    <i class="fas fa-file-invoice text-4xl mb-2 text-gray-300"></i>
                                    <p class="text-sm font-medium">Tidak ada pendaftaran pada periode ini</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($bookingDetails->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 print:hidden">
                {{ $bookingDetails->links() }}
            </div>
            @endif
        </div>

        {{-- ── 7. Tanda Tangan / Lembar Pengesahan (Print Only) ──────────────── --}}
        <div class="hidden print:block mt-12 grid grid-cols-2 text-xs text-gray-700">
            <div>
                <p class="font-bold mb-1">Catatan Laporan e-Statement:</p>
                <p class="text-gray-500 max-w-sm leading-relaxed">
                    Dokumen e-Statement ini diterbitkan secara otomatis oleh sistem EduLiving Indonesia dan sah sebagai bukti rekapitulasi pendapatan provider event.
                </p>
            </div>
            <div class="text-center ml-auto w-56 mr-4">
                <p class="mb-16">Mitra Provider Event,</p>
                <p class="font-bold underline text-gray-900 text-sm">{{ auth()->user()->name }}</p>
                <p class="text-gray-500 mt-0.5">EduLiving Partner</p>
            </div>
        </div>

    </div>
</div>

<style>
@media print {
    /* Sembunyikan semua elemen navigasi, filter, dan tombol web */
    .print\:hidden, nav, footer, header, button, form, .no-print, [role="navigation"] {
        display: none !important;
    }
    body {
        background-color: white !important;
        color: black !important;
        font-size: 12px !important;
    }
    .min-h-screen, .max-w-7xl, .bg-gray-50, .bg-white {
        background: transparent !important;
        padding: 0 !important;
        margin: 0 !important;
        max-width: 100% !important;
        width: 100% !important;
    }
    .shadow-sm {
        box-shadow: none !important;
    }
    .border {
        border-color: #d1d5db !important;
    }
    /* Rapikan tabel cetak */
    table {
        width: 100% !important;
        border-collapse: collapse !important;
    }
    th, td {
        border-bottom: 1px solid #e5e7eb !important;
        padding: 6px 4px !important;
        font-size: 10.5px !important;
    }
    .print\:block {
        display: block !important;
    }
    .print\:grid {
        display: grid !important;
    }
}
</style>
@endsection

@push('scripts')
<script>
function toggleCustomDate(value) {
    document.getElementById('custom-date').classList.toggle('hidden', value !== 'custom');
}
</script>
@endpush