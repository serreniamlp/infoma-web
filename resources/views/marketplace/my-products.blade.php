@extends('layouts.app')

@section('title', 'Produk Saya - Marketplace INFOMA')

@section('content')
    <div class="min-h-screen bg-gray-50 py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Breadcrumb --}}
            <nav class="mb-6">
                <ol class="flex items-center gap-1 text-sm text-gray-500">
                    <li><a href="{{ route('marketplace.index') }}"
                            class="hover:text-orange-600 transition-colors">Marketplace</a></li>
                    <li><i class="fas fa-chevron-right text-xs text-gray-300 mx-1"></i></li>
                    <li class="text-gray-900 font-medium">Produk Saya</li>
                </ol>
            </nav>

            {{-- Flash messages --}}
            @if (session('success'))
                <div
                    class="mb-5 flex items-center gap-3 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-xl text-sm">
                    <i class="fas fa-check-circle text-green-500 text-base"></i>
                    {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div
                    class="mb-5 flex items-center gap-3 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm">
                    <i class="fas fa-exclamation-circle text-red-500 text-base"></i>
                    {{ session('error') }}
                </div>
            @endif

            {{-- Header row --}}
            <div class="flex flex-wrap items-center justify-between gap-4 mb-8">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Produk Saya</h1>
                    <p class="text-sm text-gray-500 mt-0.5">Kelola semua produk yang Anda jual</p>
                </div>
                <a href="{{ route('user.marketplace.seller.create') }}"
                    class="inline-flex items-center gap-2 bg-orange-500 hover:bg-orange-600 text-white font-semibold px-5 py-2.5 rounded-xl transition-colors shadow-sm text-sm">
                    <i class="fas fa-plus"></i> Tambah Produk
                </a>
            </div>

            {{--
            Stats: dikirim dari controller sebagai $stats
            (tidak ada query langsung di blade)
        --}}
            @if (isset($stats))
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-8">
                    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 text-center">
                        <p class="text-2xl font-bold text-green-600">{{ $stats['active'] }}</p>
                        <p class="text-xs text-gray-500 mt-1">Aktif</p>
                    </div>
                    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 text-center">
                        <p class="text-2xl font-bold text-yellow-600">{{ $stats['draft'] }}</p>
                        <p class="text-xs text-gray-500 mt-1">Draft</p>
                    </div>
                    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 text-center">
                        <p class="text-2xl font-bold text-blue-600">{{ $stats['sold'] }}</p>
                        <p class="text-xs text-gray-500 mt-1">Terjual</p>
                    </div>
                    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 text-center">
                        <p class="text-2xl font-bold text-gray-400">{{ $stats['inactive'] }}</p>
                        <p class="text-xs text-gray-500 mt-1">Nonaktif</p>
                    </div>
                </div>
            @endif

            @if ($products->count() > 0)

                {{-- Products grid --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
                    @foreach ($products as $product)
                        @php
                            $statusConf = [
                                'active' => ['label' => 'Aktif', 'bg' => 'bg-green-100', 'text' => 'text-green-700'],
                                'draft' => ['label' => 'Draft', 'bg' => 'bg-yellow-100', 'text' => 'text-yellow-700'],
                                'sold' => ['label' => 'Terjual', 'bg' => 'bg-blue-100', 'text' => 'text-blue-700'],
                                'inactive' => ['label' => 'Nonaktif', 'bg' => 'bg-gray-100', 'text' => 'text-gray-600'],
                                'pending_approval' => [
                                    'label' => 'Menunggu',
                                    'bg' => 'bg-orange-100',
                                    'text' => 'text-orange-700',
                                ],
                            ];
                            $st = $statusConf[$product->status] ?? [
                                'label' => $product->status_label,
                                'bg' => 'bg-gray-100',
                                'text' => 'text-gray-600',
                            ];
                            $deleteUrl = route('user.marketplace.seller.destroy', $product);
                        @endphp
                        <div
                            class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden hover:shadow-md transition-shadow duration-200 flex flex-col group">

                            {{-- Image --}}
                            <div class="relative overflow-hidden">
                                <img src="{{ $product->main_image }}" alt="{{ $product->name }}"
                                    class="w-full h-44 object-cover group-hover:scale-105 transition-transform duration-300">
                                <div class="absolute top-3 left-3">
                                    <span
                                        class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $st['bg'] }} {{ $st['text'] }}">
                                        {{ $st['label'] }}
                                    </span>
                                </div>
                                <div class="absolute bottom-3 right-3">
                                    <span
                                        class="flex items-center gap-1 bg-black bg-opacity-40 text-white text-xs px-2 py-0.5 rounded-full">
                                        <i class="fas fa-eye text-xs"></i> {{ number_format($product->views_count) }}
                                    </span>
                                </div>
                            </div>

                            {{-- Content --}}
                            <div class="p-4 flex flex-col flex-1">
                                <p class="text-xs text-orange-600 font-medium uppercase tracking-wide mb-1">
                                    {{ $product->category->name ?? '—' }}
                                </p>
                                <h3 class="text-sm font-bold text-gray-900 line-clamp-2 leading-snug mb-2 flex-1">
                                    {{ $product->name }}
                                </h3>
                                <p class="text-orange-600 font-semibold text-base mb-3">
                                    Rp {{ number_format($product->price, 0, ',', '.') }}
                                </p>

                                {{-- Stats row --}}
                                <div
                                    class="flex items-center justify-between text-xs text-gray-400 border-t border-gray-50 pt-2.5 mb-4">
                                    <span class="flex items-center gap-1">
                                        <i class="fas fa-layer-group"></i>
                                        Stok <span
                                            class="font-semibold text-gray-600 ml-0.5">{{ $product->stock_quantity }}</span>
                                    </span>
                                    <span class="flex items-center gap-1">
                                        <i class="fas fa-calendar"></i>
                                        {{ $product->created_at->format('d M Y') }}
                                    </span>
                                </div>

                                {{-- Action buttons --}}
                                <a href="{{ route('marketplace.show', $product) }}"
                                    class="w-full inline-flex items-center justify-center gap-2 mb-2 py-2 border border-orange-200 text-orange-600 hover:bg-orange-500 hover:text-white hover:border-orange-500 font-semibold rounded-lg text-xs transition-all duration-200">
                                    <i class="fas fa-eye"></i> Lihat Produk
                                </a>
                                <div class="grid grid-cols-2 gap-2">
                                    <a href="{{ route('user.marketplace.seller.edit', $product) }}"
                                        class="inline-flex items-center justify-center gap-1.5 py-2 border border-yellow-200 text-yellow-700 bg-yellow-50 hover:bg-yellow-100 font-semibold rounded-lg text-xs transition-all">
                                        <i class="fas fa-pen"></i> Edit
                                    </a>
                                    {{--
                                    PENTING: data-delete-url diisi dari PHP route() helper
                                    Tidak ada hardcode URL di JavaScript
                                --}}
                                    <button type="button"
                                        class="btn-hapus inline-flex items-center justify-center gap-1.5 py-2 border border-red-200 text-red-600 bg-red-50 hover:bg-red-100 font-semibold rounded-lg text-xs transition-all"
                                        data-delete-url="{{ $deleteUrl }}"
                                        data-product-name="{{ addslashes($product->name) }}">
                                        <i class="fas fa-trash"></i> Hapus
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Pagination --}}
                <div class="mt-10 flex justify-center">
                    {{ $products->links() }}
                </div>
            @else
                {{-- Empty State --}}
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm py-20 px-6 text-center max-w-lg mx-auto">
                    <div class="relative mx-auto w-24 h-24 mb-6">
                        <div class="w-24 h-24 bg-orange-100 rounded-3xl flex items-center justify-center">
                            <i class="fas fa-store text-4xl text-orange-400"></i>
                        </div>
                        <div
                            class="absolute -top-2 -right-2 w-9 h-9 bg-orange-500 rounded-xl flex items-center justify-center shadow-md">
                            <i class="fas fa-plus text-white text-sm"></i>
                        </div>
                    </div>
                    <h2 class="text-xl font-bold text-gray-900 mb-2">Belum ada produk</h2>
                    <p class="text-gray-500 text-sm mb-8 leading-relaxed">
                        Mulai berjualan dan hasilkan uang dari barang yang tidak terpakai.
                        Upload produk pertama Anda sekarang!
                    </p>
                    <a href="{{ route('user.marketplace.seller.create') }}"
                        class="inline-flex items-center gap-2 bg-orange-500 hover:bg-orange-600 text-white font-bold px-8 py-3 rounded-xl transition-colors shadow-sm">
                        <i class="fas fa-plus"></i> Jual Produk Pertama
                    </a>
                    <div class="mt-6 pt-6 border-t border-gray-100 flex justify-center gap-6 text-xs text-gray-400">
                        <span class="flex items-center gap-1"><i class="fas fa-shield-alt text-orange-400"></i> Transaksi
                            Aman</span>
                        <span class="flex items-center gap-1"><i class="fas fa-bolt text-orange-400"></i> Upload
                            Cepat</span>
                        <span class="flex items-center gap-1"><i class="fas fa-users text-orange-400"></i> Ribuan
                            Pembeli</span>
                    </div>
                </div>
            @endif

        </div>
    </div>

    {{-- ==================== DELETE MODAL ==================== --}}
    <div id="deleteModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="fixed inset-0 bg-black bg-opacity-40" onclick="tutupDeleteModal()"></div>
        <div class="relative min-h-screen flex items-center justify-center p-4">
            <div class="relative bg-white rounded-2xl shadow-xl max-w-md w-full p-6">
                <div class="flex items-start justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-red-100 rounded-xl flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-trash text-red-500"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-gray-900">Hapus Produk</h3>
                            <p class="text-xs text-gray-500">Tindakan ini tidak dapat dibatalkan</p>
                        </div>
                    </div>
                    <button type="button" onclick="tutupDeleteModal()"
                        class="text-gray-400 hover:text-gray-600 hover:bg-gray-100 p-1.5 rounded-lg transition-colors ml-2">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div class="bg-red-50 border border-red-100 rounded-xl p-4 mb-5">
                    <p class="text-sm text-gray-700">Anda akan menghapus produk:</p>
                    <p class="font-bold text-gray-900 mt-1 text-base" id="deleteProductName"></p>
                    <p class="text-xs text-red-600 mt-2 flex items-center gap-1.5">
                        <i class="fas fa-exclamation-triangle"></i>
                        Foto dan data produk akan ikut terhapus permanen.
                    </p>
                </div>

                <div class="flex gap-3">
                    <button type="button" onclick="tutupDeleteModal()"
                        class="flex-1 border border-gray-200 text-gray-700 hover:bg-gray-50 font-semibold py-2.5 rounded-xl text-sm transition-colors">
                        Batal
                    </button>
                    <form id="deleteForm" method="POST" class="flex-1">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="w-full bg-red-500 hover:bg-red-600 text-white font-bold py-2.5 rounded-xl text-sm transition-colors flex items-center justify-center gap-2">
                            <i class="fas fa-trash"></i> Ya, Hapus
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Gunakan data attribute dari PHP — tidak ada hardcode URL di sini
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.btn-hapus').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    const url = this.getAttribute('data-delete-url');
                    const nama = this.getAttribute('data-product-name');
                    bukaDeletModal(url, nama);
                });
            });
        });

        function bukaDeletModal(deleteUrl, nama) {
            document.getElementById('deleteProductName').textContent = nama;
            document.getElementById('deleteForm').action = deleteUrl; // URL dari PHP route()
            document.getElementById('deleteModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function tutupDeleteModal() {
            document.getElementById('deleteModal').classList.add('hidden');
            document.body.style.overflow = '';
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') tutupDeleteModal();
        });
    </script>

@endsection
