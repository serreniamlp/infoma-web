@extends('layouts.app')
@section('title', 'Kelola Produk FJB — Admin EduLiving')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Kelola Produk Marketplace</h1>
                <p class="text-gray-500 text-sm mt-1">Moderasi semua produk FJB dari seller mahasiswa</p>
            </div>
            <a href="{{ route('admin.marketplace.transactions') }}"
               class="px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-50 transition-colors">
                <i class="fas fa-receipt mr-2"></i>Lihat Transaksi
            </a>
        </div>

        @if(session('success'))
            <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg text-green-700 flex items-center gap-2">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
            </div>
        @endif

        {{-- Stats --}}
        <div class="grid grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-xl border border-gray-200 p-4 flex items-center gap-3">
                <div class="h-10 w-10 rounded-lg bg-blue-100 flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-boxes text-blue-600"></i>
                </div>
                <div>
                    <p class="text-xl font-bold text-gray-900">{{ $stats['total'] }}</p>
                    <p class="text-xs text-gray-500">Total Produk</p>
                </div>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-4 flex items-center gap-3">
                <div class="h-10 w-10 rounded-lg bg-green-100 flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-check text-green-600"></i>
                </div>
                <div>
                    <p class="text-xl font-bold text-gray-900">{{ $stats['active'] }}</p>
                    <p class="text-xs text-gray-500">Aktif</p>
                </div>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-4 flex items-center gap-3">
                <div class="h-10 w-10 rounded-lg bg-gray-100 flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-eye-slash text-gray-600"></i>
                </div>
                <div>
                    <p class="text-xl font-bold text-gray-900">{{ $stats['inactive'] }}</p>
                    <p class="text-xs text-gray-500">Nonaktif</p>
                </div>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-4 flex items-center gap-3">
                <div class="h-10 w-10 rounded-lg bg-red-100 flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-times-circle text-red-600"></i>
                </div>
                <div>
                    <p class="text-xl font-bold text-gray-900">{{ $stats['sold_out'] }}</p>
                    <p class="text-xs text-gray-500">Stok Habis</p>
                </div>
            </div>
        </div>

        {{-- Filter --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6 p-4">
            <form method="GET" class="flex gap-3 flex-wrap">
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Cari nama produk, seller..."
                       class="flex-1 min-w-48 px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
                <select name="status" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    <option value="">Semua Status</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                </select>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700">
                    <i class="fas fa-search mr-1"></i>Cari
                </button>
                @if(request()->hasAny(['search','status']))
                    <a href="{{ route('admin.marketplace.products') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm hover:bg-gray-200">Reset</a>
                @endif
            </form>
        </div>

        {{-- Tabel --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produk</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Seller</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Harga</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stok</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Terjual</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($products as $product)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">{{ $product->name }}</p>
                                        @if($product->category)
                                            <p class="text-xs text-gray-500">{{ $product->category->name }}</p>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-sm text-gray-900">{{ $product->seller->name ?? '—' }}</p>
                                    <p class="text-xs text-gray-500">{{ $product->seller->email ?? '' }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-sm font-medium text-gray-900">Rp {{ number_format($product->price, 0, ',', '.') }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-sm {{ $product->stock_quantity == 0 ? 'text-red-600 font-semibold' : 'text-gray-900' }}">
                                        {{ $product->stock_quantity }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-sm text-gray-900">{{ $product->transactions_count }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    @if($product->status === 'active')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">
                                            <span class="w-1.5 h-1.5 rounded-full bg-green-500 mr-1.5"></span>Aktif
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                                            <span class="w-1.5 h-1.5 rounded-full bg-gray-400 mr-1.5"></span>Nonaktif
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <form method="POST" action="{{ route('admin.marketplace.products.toggleStatus', $product) }}" class="inline">
                                            @csrf @method('PATCH')
                                            <button type="submit"
                                                    class="px-3 py-1.5 text-xs font-medium rounded-lg transition-colors
                                                           {{ $product->status === 'active' ? 'bg-red-50 text-red-700 hover:bg-red-100' : 'bg-green-50 text-green-700 hover:bg-green-100' }}"
                                                    onclick="return confirm('{{ $product->status === 'active' ? 'Nonaktifkan' : 'Aktifkan' }} produk ini?')">
                                                {{ $product->status === 'active' ? 'Nonaktifkan' : 'Aktifkan' }}
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.marketplace.products.destroy', $product) }}" class="inline">
                                            @csrf @method('DELETE')
                                            <button type="submit"
                                                    class="px-3 py-1.5 text-xs font-medium bg-red-50 text-red-700 rounded-lg hover:bg-red-100"
                                                    onclick="return confirm('Hapus produk {{ $product->name }}?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-gray-400">
                                    <i class="fas fa-boxes text-4xl mb-3 block opacity-30"></i>
                                    Tidak ada produk ditemukan
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($products->hasPages())
                <div class="px-6 py-4 border-t border-gray-200">{{ $products->links() }}</div>
            @endif
        </div>

    </div>
</div>
@endsection
