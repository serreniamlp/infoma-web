@extends('layouts.app')
@section('title', 'Laporan Penjualan — EduLiving')

@section('content')
<div class="min-h-screen bg-gray-50 py-6 print:py-0 print:bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 print:px-0 print:max-w-full">

        {{-- ========================================================================= --}}
        {{-- KOP SURAT & HEADER RESMI E-STATEMENT (KHUSUS TAMPIL SAAT CETAK / PDF)     --}}
        {{-- ========================================================================= --}}
        <div class="hidden print:block mb-6 pb-4 border-b-2 border-orange-600">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-orange-600 text-white rounded-xl flex items-center justify-center font-black text-xl border border-orange-700">
                        EL
                    </div>
                    <div>
                        <h1 class="text-2xl font-black text-gray-900 tracking-wider">EDULIVING INDONESIA</h1>
                        <p class="text-xs text-gray-600 uppercase tracking-widest font-bold">Laporan Rekapitulasi Penjualan & e-Statement Keuangan FJB</p>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">NOMOR DOKUMEN REFERENSI</p>
                    <p class="text-xs font-mono font-bold text-gray-800">REF: EDL-FJB-{{ now()->format('Ym') }}-{{ sprintf('%04d', auth()->id()) }}</p>
                    <p class="text-[10px] text-gray-400 font-mono mt-0.5">TANGGAL CETAK: {{ now()->translatedFormat('d F Y H:i') }} WIB</p>
                </div>
            </div>

            <div class="mt-4 pt-3 border-t border-gray-200 grid grid-cols-2 gap-4 text-xs text-gray-700 font-medium">
                <div class="space-y-1">
                    <p><strong class="text-gray-900">Nama Penjual / Toko:</strong> {{ auth()->user()->name }}</p>
                    <p><strong class="text-gray-900">Email Akun:</strong> {{ auth()->user()->email }}</p>
                </div>
                <div class="text-right space-y-1">
                    <p><strong class="text-gray-900">Periode Laporan:</strong> {{ $dateFrom->translatedFormat('d F Y') }} — {{ $dateTo->translatedFormat('d F Y') }}</p>
                    <p><strong class="text-gray-900">Status Dokumen:</strong> <span class="text-green-700 font-bold">Sah Terverifikasi Sistem</span></p>
                </div>
            </div>
        </div>

        {{-- ========================================================================= --}}
        {{-- HEADER WEB (TAMPIL DI LAYAR, SEMBUNYI SAAT CETAK)                         --}}
        {{-- ========================================================================= --}}
        <div class="flex items-center justify-between mb-6 print:hidden">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                    <i class="fas fa-chart-bar text-orange-500"></i>
                    Laporan Penjualan Marketplace
                </h1>
                <p class="text-gray-500 text-sm mt-1">
                    Periode Laporan: <strong>{{ $dateFrom->format('d M Y') }}</strong> —
                    <strong>{{ $dateTo->format('d M Y') }}</strong>
                </p>
            </div>
            <div class="flex items-center gap-2">
                <button onclick="window.print()" class="px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white text-xs font-semibold rounded-lg shadow-sm transition-colors flex items-center gap-1.5">
                    <i class="fas fa-print"></i> Cetak PDF / e-Statement
                </button>
                <a href="{{ route('user.marketplace.seller.home') }}"
                   class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold rounded-lg transition-colors flex items-center gap-1">
                    <i class="fas fa-arrow-left text-xs"></i> Dashboard Seller
                </a>
            </div>
        </div>

        {{-- ========================================================================= --}}
        {{-- FILTER PERIODE WEB (TAMPIL DI LAYAR, SEMBUNYI SAAT CETAK)                 --}}
        {{-- ========================================================================= --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-6 print:hidden">
            <form method="GET" class="flex gap-3 flex-wrap items-end">
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Periode</label>
                    <select name="period" onchange="toggleCustomDate(this.value); this.form.submit()"
                            class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-orange-500">
                        <option value="this_week"  {{ $period === 'this_week'  ? 'selected' : '' }}>Minggu Ini</option>
                        <option value="this_month" {{ $period === 'this_month' ? 'selected' : '' }}>Bulan Ini</option>
                        <option value="last_month" {{ $period === 'last_month' ? 'selected' : '' }}>Bulan Lalu</option>
                        <option value="this_year"  {{ $period === 'this_year'  ? 'selected' : '' }}>Tahun Ini</option>
                        <option value="custom"     {{ $period === 'custom'     ? 'selected' : '' }}>Kustom</option>
                    </select>
                </div>
                <div id="custom-date" class="{{ $period === 'custom' ? '' : 'hidden' }} flex gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Dari</label>
                        <input type="date" name="date_from"
                               value="{{ $period === 'custom' ? request('date_from') : '' }}"
                               class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Sampai</label>
                        <input type="date" name="date_to"
                               value="{{ $period === 'custom' ? request('date_to') : '' }}"
                               class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    </div>
                    <div class="self-end">
                        <button type="submit"
                                class="px-4 py-2 bg-orange-500 text-white rounded-lg text-sm hover:bg-orange-600 font-semibold">
                            Terapkan
                        </button>
                    </div>
                </div>
            </form>
        </div>

        {{-- ========================================================================= --}}
        {{-- SUMMARY CARDS WEB (TAMPIL DI LAYAR, SEMBUNYI SAAT CETAK)                  --}}
        {{-- ========================================================================= --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6 print:hidden">

            {{-- Total Revenue --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5 col-span-2 lg:col-span-1">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 bg-green-50 rounded-xl flex items-center justify-center">
                        <i class="fas fa-wallet text-green-500"></i>
                    </div>
                    <p class="text-sm font-medium text-gray-500">Total Revenue</p>
                </div>
                <p class="text-2xl font-bold text-gray-900">
                    Rp {{ number_format($summary['total_revenue'], 0, ',', '.') }}
                </p>
                <p class="text-xs text-gray-400 mt-2">dari pesanan yang selesai</p>
            </div>

            {{-- Total Pesanan --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 bg-orange-50 rounded-xl flex items-center justify-center">
                        <i class="fas fa-shopping-bag text-orange-500"></i>
                    </div>
                    <p class="text-sm font-medium text-gray-500">Total Pesanan</p>
                </div>
                <p class="text-2xl font-bold text-gray-900">{{ $summary['total_orders'] }}</p>
                <div class="mt-3 pt-3 border-t border-gray-100 grid grid-cols-3 gap-1 text-xs text-center">
                    <div class="text-green-600">
                        <div class="font-bold">{{ $summary['completed_orders'] }}</div>
                        <div class="text-gray-400">Selesai</div>
                    </div>
                    <div class="text-orange-500">
                        <div class="font-bold">{{ $summary['pending_orders'] }}</div>
                        <div class="text-gray-400">Pending</div>
                    </div>
                    <div class="text-red-500">
                        <div class="font-bold">{{ $summary['cancelled_orders'] }}</div>
                        <div class="text-gray-400">Batal</div>
                    </div>
                </div>
            </div>

            {{-- Tingkat Penyelesaian --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 bg-blue-50 rounded-xl flex items-center justify-center">
                        <i class="fas fa-percentage text-blue-500"></i>
                    </div>
                    <p class="text-sm font-medium text-gray-500">Tingkat Penyelesaian</p>
                </div>
                @php
                    $completionRate = $summary['total_orders'] > 0
                        ? round($summary['completed_orders'] / $summary['total_orders'] * 100, 1)
                        : 0;
                @endphp
                <p class="text-2xl font-bold text-blue-600">{{ $completionRate }}%</p>
                <div class="mt-3 w-full bg-gray-100 rounded-full h-2">
                    <div class="bg-blue-500 h-2 rounded-full" style="width: {{ $completionRate }}%"></div>
                </div>
                <p class="text-xs text-gray-400 mt-1">{{ $summary['completed_orders'] }} dari {{ $summary['total_orders'] }} pesanan</p>
            </div>

            {{-- Rata-rata Nilai Pesanan --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 bg-purple-50 rounded-xl flex items-center justify-center">
                        <i class="fas fa-chart-line text-purple-500"></i>
                    </div>
                    <p class="text-sm font-medium text-gray-500">Rata-rata Pesanan</p>
                </div>
                @php
                    $avgOrder = $summary['completed_orders'] > 0
                        ? $summary['total_revenue'] / $summary['completed_orders']
                        : 0;
                @endphp
                <p class="text-2xl font-bold text-purple-600">
                    Rp {{ number_format($avgOrder, 0, ',', '.') }}
                </p>
                <p class="text-xs text-gray-400 mt-2">per transaksi selesai</p>
            </div>
        </div>

        {{-- ========================================================================= --}}
        {{-- TABEL IKHTISAR RINGKASAN KEUANGAN FORMAL (KHUSUS CETAK PDF / E-STATEMENT) --}}
        {{-- ========================================================================= --}}
        <div class="hidden print:block mb-6">
            <h3 class="text-xs font-bold text-gray-800 uppercase tracking-wider mb-2 border-b border-gray-300 pb-1">
                I. IKHTISAR REKAPITULASI KEUANGAN PERIODE
            </h3>
            <table class="w-full text-xs border border-gray-300">
                <thead class="bg-gray-100 font-bold text-gray-800 border-b border-gray-300">
                    <tr>
                        <th class="p-2 text-left border-r border-gray-300">INDIKATOR FINANSIAL</th>
                        <th class="p-2 text-center border-r border-gray-300">JUMLAH TRANSAKSI</th>
                        <th class="p-2 text-center border-r border-gray-300">TINGKAT PENYELESAIAN</th>
                        <th class="p-2 text-right">TOTAL REVENUE (RP)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-300 text-gray-800">
                    <tr>
                        <td class="p-2 border-r border-gray-300 font-bold">Total Transaksi Masuk</td>
                        <td class="p-2 border-r border-gray-300 text-center font-mono">{{ $summary['total_orders'] }} Pesanan</td>
                        <td class="p-2 border-r border-gray-300 text-center font-mono">{{ $completionRate }}%</td>
                        <td class="p-2 text-right font-mono font-bold text-gray-900">
                            Rp {{ number_format($summary['total_revenue'], 0, ',', '.') }}
                        </td>
                    </tr>
                    <tr class="bg-gray-50/50">
                        <td class="p-2 border-r border-gray-300">Pesanan Selesai (Paid & Completed)</td>
                        <td class="p-2 border-r border-gray-300 text-center font-mono text-green-700 font-bold">{{ $summary['completed_orders'] }} Pesanan</td>
                        <td class="p-2 border-r border-gray-300 text-center text-gray-500">—</td>
                        <td class="p-2 text-right font-mono text-green-800 font-bold">
                            Rp {{ number_format($summary['total_revenue'], 0, ',', '.') }}
                        </td>
                    </tr>
                    <tr>
                        <td class="p-2 border-r border-gray-300">Pesanan Dibatalkan (Cancelled)</td>
                        <td class="p-2 border-r border-gray-300 text-center font-mono text-red-600 font-bold">{{ $summary['cancelled_orders'] }} Pesanan</td>
                        <td class="p-2 border-r border-gray-300 text-center text-gray-500">—</td>
                        <td class="p-2 text-right font-mono text-gray-400">Rp 0</td>
                    </tr>
                    <tr class="bg-gray-100 font-bold text-gray-900 border-t border-gray-300">
                        <td class="p-2 border-r border-gray-300">RATA-RATA NILAI TRANSAKSI SELESAI</td>
                        <td class="p-2 border-r border-gray-300 text-center font-mono" colspan="2">Per Pesanan Selesai</td>
                        <td class="p-2 text-right font-mono font-bold text-gray-900">
                            Rp {{ number_format($avgOrder, 0, ',', '.') }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- ========================================================================= --}}
        {{-- REVENUE PER PRODUK (WEB VIEW)                                              --}}
        {{-- ========================================================================= --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6 print:hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-orange-50/30">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-orange-100 text-orange-600 rounded-lg flex items-center justify-center">
                        <i class="fas fa-boxes"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-900 text-base">Performa & Stok per Produk</h3>
                        <p class="text-xs text-gray-500">Omzet dan sisa stok barang pada periode ini</p>
                    </div>
                </div>
                <span class="text-xs bg-orange-100 text-orange-800 px-3 py-1 rounded-full font-semibold border border-orange-200">
                    {{ count($revenuePerProduct) }} Produk
                </span>
            </div>
            <div class="divide-y divide-gray-100">
                @forelse($revenuePerProduct as $i => $product)
                @php
                    $maxRevenue = $revenuePerProduct->max('revenue') ?: 1;
                    $barWidth   = $product->revenue > 0 ? round(($product->revenue / $maxRevenue) * 100) : 0;
                @endphp
                <div class="px-6 py-4 hover:bg-gray-50/60 transition-colors">
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center gap-3 min-w-0">
                            <span class="text-sm font-bold text-gray-400 w-5 flex-shrink-0">{{ $i + 1 }}</span>
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-gray-900 truncate">{{ $product->name }}</p>
                                <p class="text-xs text-gray-500 font-medium">
                                    {{ $product->order_count }} pesanan terjual • Sisa Stok: <span class="font-bold text-gray-800">{{ $product->stock_quantity }}</span>
                                </p>
                            </div>
                        </div>
                        <p class="text-sm font-extrabold text-emerald-700 ml-4 flex-shrink-0">
                            Rp {{ number_format($product->revenue ?? 0, 0, ',', '.') }}
                        </p>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-1.5">
                        <div class="bg-orange-400 h-1.5 rounded-full transition-all duration-500"
                             style="width: {{ $barWidth }}%"></div>
                    </div>
                </div>
                @empty
                <div class="px-6 py-12 text-center text-gray-400">
                    <i class="fas fa-boxes text-3xl mb-2"></i>
                    <p class="text-sm">Belum ada data penjualan per produk</p>
                </div>
                @endforelse
            </div>
        </div>

        {{-- ========================================================================= --}}
        {{-- TABEL FORMIL RINCIAN OMZET PER PRODUK (KHUSUS CETAK PDF / E-STATEMENT)   --}}
        {{-- ========================================================================= --}}
        <div class="hidden print:block mb-6">
            <h3 class="text-xs font-bold text-gray-800 uppercase tracking-wider mb-2 border-b border-gray-300 pb-1">
                II. RINCIAN PERFORMA OMZET DAN SISA STOK PER PRODUK
            </h3>
            <table class="w-full text-xs border border-gray-300">
                <thead class="bg-gray-100 font-bold text-gray-800 border-b border-gray-300">
                    <tr>
                        <th class="p-2 text-center border-r border-gray-300 w-8">NO</th>
                        <th class="p-2 text-left border-r border-gray-300">NAMA PRODUK / BARANG</th>
                        <th class="p-2 text-left border-r border-gray-300">KATEGORI</th>
                        <th class="p-2 text-center border-r border-gray-300">SISA STOK</th>
                        <th class="p-2 text-center border-r border-gray-300">TERJUAL</th>
                        <th class="p-2 text-right border-r border-gray-300">HARGA SATUAN</th>
                        <th class="p-2 text-right">TOTAL OMZET (RP)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-300 text-gray-800">
                    @forelse($revenuePerProduct as $i => $product)
                    <tr class="{{ $i % 2 === 1 ? 'bg-gray-50/50' : '' }}">
                        <td class="p-2 text-center border-r border-gray-300 font-mono">{{ $i + 1 }}</td>
                        <td class="p-2 border-r border-gray-300 font-semibold">{{ $product->name }}</td>
                        <td class="p-2 border-r border-gray-300 text-gray-600">{{ $product->category->name ?? '—' }}</td>
                        <td class="p-2 text-center border-r border-gray-300 font-mono font-bold">{{ $product->stock_quantity }}</td>
                        <td class="p-2 text-center border-r border-gray-300 font-mono text-green-700 font-bold">{{ $product->order_count }}</td>
                        <td class="p-2 text-right border-r border-gray-300 font-mono">Rp {{ number_format($product->price, 0, ',', '.') }}</td>
                        <td class="p-2 text-right font-mono font-bold text-gray-900">
                            Rp {{ number_format($product->revenue ?? 0, 0, ',', '.') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="p-4 text-center text-gray-500 italic">Belum ada data produk pada periode ini</td>
                    </tr>
                    @endforelse
                    <tr class="bg-gray-100 font-bold text-gray-900 border-t border-gray-300">
                        <td class="p-2 text-right border-r border-gray-300" colspan="6">TOTAL OMZET SELURUH PRODUK</td>
                        <td class="p-2 text-right font-mono font-bold text-green-800">
                            Rp {{ number_format($revenuePerProduct->sum('revenue'), 0, ',', '.') }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- ========================================================================= --}}
        {{-- TABEL DETAIL TRANSAKSI (WEB & PRINT VERSION)                              --}}
        {{-- ========================================================================= --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6 print:border print:border-gray-300 print:shadow-none">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between print:bg-gray-100 print:py-2 print:px-3 print:border-b-2 print:border-gray-300">
                <div>
                    <h3 class="font-semibold text-gray-900 print:text-xs print:font-bold print:uppercase print:tracking-wider">
                        <span class="hidden print:inline">III. JURNAL MUTASI DETAIL TRANSAKSI</span>
                        <span class="print:hidden">Riwayat Transaksi</span>
                    </h3>
                    <p class="text-xs text-gray-400 mt-0.5 print:hidden">Semua transaksi pada periode yang dipilih</p>
                </div>
                <span class="text-xs bg-orange-50 text-orange-600 border border-orange-100 px-3 py-1 rounded-full font-medium print:bg-transparent print:border-none print:text-gray-700 print:font-bold">
                    Total: {{ $transactions->total() }} Transaksi
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 print:divide-gray-300 print:text-xs">
                    <thead class="bg-gray-50 print:bg-gray-100 print:font-bold">
                        <tr>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase print:p-2 print:border-r print:border-gray-300 print:text-gray-900">Kode Transaksi</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase print:p-2 print:border-r print:border-gray-300 print:text-gray-900">Pembeli</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase print:p-2 print:border-r print:border-gray-300 print:text-gray-900">Produk</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase print:p-2 print:border-r print:border-gray-300 print:text-gray-900">Metode</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase print:p-2 print:border-r print:border-gray-300 print:text-gray-900">Tanggal</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase print:p-2 print:border-r print:border-gray-300 print:text-gray-900">Status</th>
                            <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase print:p-2 print:text-gray-900">Total (Rp)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 print:divide-gray-300">
                        @forelse($transactions as $tx)
                        <tr class="hover:bg-gray-50 transition-colors {{ $tx->status === 'cancelled' ? 'print:hidden' : '' }}">
                            <td class="px-5 py-3 print:p-2 print:border-r print:border-gray-300">
                                <span class="text-xs font-mono text-gray-900 font-semibold">{{ $tx->transaction_code }}</span>
                            </td>
                            <td class="px-5 py-3 print:p-2 print:border-r print:border-gray-300">
                                <div class="flex items-center gap-2">
                                    <div class="w-7 h-7 bg-orange-100 rounded-full flex items-center justify-center flex-shrink-0 print:hidden">
                                        <span class="text-xs font-bold text-orange-600">
                                            {{ substr($tx->buyer->name ?? 'U', 0, 1) }}
                                        </span>
                                    </div>
                                    <span class="text-sm print:text-xs text-gray-900 font-medium">{{ $tx->buyer->name ?? '—' }}</span>
                                </div>
                            </td>
                            <td class="px-5 py-3 print:p-2 print:border-r print:border-gray-300">
                                <p class="text-sm print:text-xs text-gray-900 font-semibold">{{ $tx->product->name ?? '—' }}</p>
                                <p class="text-xs text-gray-400 print:text-gray-600">Qty: {{ $tx->quantity }}</p>
                            </td>
                            <td class="px-5 py-3 print:p-2 print:border-r print:border-gray-300">
                                @php
                                    $methodLabel = match($tx->pickup_method ?? '') {
                                        'cod'      => ['label' => 'COD',           'class' => 'bg-green-100 text-green-700 print:bg-transparent print:text-green-800 print:font-bold'],
                                        'delivery' => ['label' => 'Diantar',       'class' => 'bg-blue-100 text-blue-700 print:bg-transparent print:text-blue-800 print:font-bold'],
                                        'pickup'   => ['label' => 'Ambil Sendiri', 'class' => 'bg-orange-100 text-orange-700 print:bg-transparent print:text-orange-800 print:font-bold'],
                                        default    => ['label' => $tx->pickup_method ?? '—', 'class' => 'bg-gray-100 text-gray-600 print:bg-transparent print:text-gray-700'],
                                    };
                                @endphp
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $methodLabel['class'] }}">
                                    {{ $methodLabel['label'] }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-sm print:text-xs text-gray-600 whitespace-nowrap print:p-2 print:border-r print:border-gray-300 font-mono">
                                {{ $tx->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-5 py-3 print:p-2 print:border-r print:border-gray-300">
                                @php
                                    $badge = match($tx->status) {
                                        'pending'     => 'bg-orange-100 text-orange-700 print:bg-transparent print:text-orange-800 print:font-bold',
                                        'confirmed'   => 'bg-blue-100 text-blue-700 print:bg-transparent print:text-blue-800 print:font-bold',
                                        'in_progress' => 'bg-indigo-100 text-indigo-700 print:bg-transparent print:text-indigo-800 print:font-bold',
                                        'completed'   => 'bg-green-100 text-green-700 print:bg-transparent print:text-green-800 print:font-bold',
                                        'cancelled'   => 'bg-red-100 text-red-700 print:bg-transparent print:text-red-800 print:font-bold',
                                        default       => 'bg-gray-100 text-gray-600 print:bg-transparent print:text-gray-700',
                                    };
                                    $label = match($tx->status) {
                                        'pending'     => 'Pending',
                                        'confirmed'   => 'Dikonfirmasi',
                                        'in_progress' => 'Diproses',
                                        'completed'   => 'Selesai',
                                        'cancelled'   => 'Dibatalkan',
                                        default       => ucfirst($tx->status),
                                    };
                                @endphp
                                <span class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $badge }}">
                                    {{ $label }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-right print:p-2 font-mono">
                                <span class="text-sm print:text-xs font-bold {{ $tx->status === 'completed' ? 'text-green-800' : 'text-gray-800' }}">
                                    Rp {{ number_format($tx->total_amount, 0, ',', '.') }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-5 py-12 text-center text-gray-400 print:p-4">
                                <p class="text-sm italic">Tidak ada transaksi pada periode ini</p>
                            </td>
                        </tr>
                        @endforelse

                        {{-- Baris Grand Total Khusus Cetak PDF --}}
                        <tr class="hidden print:table-row bg-gray-100 font-bold text-gray-900 border-t-2 border-gray-400">
                            <td colspan="6" class="p-2 text-right border-r border-gray-300 font-bold uppercase tracking-wider">
                                GRAND TOTAL REVENUE TRANSAKSI SELESAI
                            </td>
                            <td class="p-2 text-right font-mono font-bold text-green-800 text-sm">
                                Rp {{ number_format($summary['total_revenue'], 0, ',', '.') }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            @if($transactions->hasPages())
            <div class="px-6 py-4 border-t border-gray-100 bg-gray-50 print:hidden">
                {{ $transactions->links() }}
            </div>
            @endif
        </div>

        {{-- ========================================================================= --}}
        {{-- PENGESAHAN & TANDA TANGAN LAPORAN (KHUSUS CETAK PDF / E-STATEMENT)        --}}
        {{-- ========================================================================= --}}
        <div class="hidden print:block mt-8 pt-4 border-t-2 border-gray-300">
            <div class="grid grid-cols-2 gap-8 text-center text-xs text-gray-700">
                <div>
                    <p class="font-bold uppercase tracking-wider text-gray-500 mb-1">Diterbitkan Oleh (Penjual)</p>
                    <p class="font-bold text-gray-900 text-sm mt-12 underline">{{ auth()->user()->name }}</p>
                    <p class="text-gray-500">Seller Marketplace FJB</p>
                </div>
                <div>
                    <p class="font-bold uppercase tracking-wider text-gray-500 mb-1">Mengetahui & Disahkan</p>
                    <p class="font-bold text-gray-900 text-sm mt-12 underline">EduLiving Indonesia System</p>
                    <p class="text-gray-500">Platform Verifikator</p>
                </div>
            </div>

            <div class="mt-8 p-3 bg-gray-50 border border-gray-200 rounded text-center text-[10px] text-gray-600 font-mono">
                *** Dokumen e-Statement Laporan Penjualan ini dicetak dan diterbitkan secara elektronik oleh Sistem EduLiving Indonesia pada {{ now()->translatedFormat('d F Y H:i') }} WIB. Dokumen ini sah dan terverifikasi tanpa memerlukan tanda tangan basah. ***
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
function toggleCustomDate(value) {
    document.getElementById('custom-date').classList.toggle('hidden', value !== 'custom');
}
</script>
@endpush