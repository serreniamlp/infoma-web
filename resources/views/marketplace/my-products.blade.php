@extends('layouts.app')

@section('title', 'Produk Saya')

@section('content')
<div class="container mx-auto px-4">
    <nav class="flex mb-4" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-3">
            <li class="inline-flex items-center">
                <a href="{{ route('marketplace.index') }}"
                    class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600">
                    Marketplace
                </a>
            </li>
            <li aria-current="page">
                <div class="flex items-center">
                    <svg class="w-3 h-3 text-gray-400 mx-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                        fill="none" viewBox="0 0 6 10">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="m1 9 4-4-4-4" />
                    </svg>
                    <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Produk Saya</span>
                </div>
            </li>
        </ol>
    </nav>

    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
        <h1 class="text-3xl font-bold text-gray-900">Produk Saya</h1>
        <a href="{{ route('provider.marketplace.create') }}"
            class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition duration-200">
            <i class="fas fa-plus mr-2"></i> Tambah Produk
        </a>
    </div>

    @if($products->count() > 0)
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($products as $product)
        <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow duration-200 flex flex-col">
            <div class="relative">
                <img src="{{ $product->main_image }}" class="w-full h-48 object-cover rounded-t-lg"
                    alt="{{ $product->name }}">
                <div class="absolute top-3 right-3">
                    <span
                        class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium
                        {{ $product->status == 'active' ? 'bg-green-100 text-green-800' : 
                           ($product->status == 'draft' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800') }}">
                        {{ $product->status_label }}
                    </span>
                </div>
            </div>
            <div class="p-4 flex flex-col flex-1">
                <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ Str::limit($product->name, 50) }}</h3>
                <p class="text-gray-600 text-sm mb-4 flex-1">{{ Str::limit($product->description, 80) }}</p>

                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <h4 class="text-xl font-bold text-blue-600">Rp {{ number_format($product->price, 0, ',', '.') }}
                        </h4>
                        <span class="text-sm text-gray-500 flex items-center">
                            <i class="fas fa-eye mr-1"></i> {{ $product->views_count }}
                        </span>
                    </div>
                    <div class="flex justify-between items-center text-sm text-gray-500">
                        <span>Stok: {{ $product->stock_quantity }}</span>
                        <span>{{ $product->created_at->format('d M Y') }}</span>
                    </div>
                </div>
            </div>
            <div class="p-4 pt-0 space-y-2">
                <a href="{{ route('marketplace.show', $product) }}"
                    class="w-full inline-flex items-center justify-center px-4 py-2 border border-blue-300 text-blue-700 bg-blue-50 hover:bg-blue-100 font-medium rounded-lg text-sm transition duration-200">
                    <i class="fas fa-eye mr-2"></i> Lihat
                </a>
                <div class="grid grid-cols-2 gap-2">
                    <a href="{{ route('provider.marketplace.edit', $product) }}"
                        class="inline-flex items-center justify-center px-4 py-2 border border-yellow-300 text-yellow-700 bg-yellow-50 hover:bg-yellow-100 font-medium rounded-lg text-sm transition duration-200">
                        <i class="fas fa-edit mr-2"></i> Edit
                    </a>
                    <button type="button"
                        class="inline-flex items-center justify-center px-4 py-2 border border-red-300 text-red-700 bg-red-50 hover:bg-red-100 font-medium rounded-lg text-sm transition duration-200"
                        onclick="deleteProduct({{ $product->id }}, '{{ $product->name }}')">
                        <i class="fas fa-trash mr-2"></i> Hapus
                    </button>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Pagination -->
    <div class="flex justify-center mt-8">
        {{ $products->links() }}
    </div>
    @else
    <div class="text-center py-12">
        <i class="fas fa-box text-6xl text-gray-400 mb-4"></i>
        <h2 class="text-2xl font-bold text-gray-900 mb-2">Belum ada produk</h2>
        <p class="text-gray-600 mb-6">Mulai jual produk Anda di marketplace</p>
        <a href="{{ route('provider.marketplace.create') }}"
            class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition duration-200">
            <i class="fas fa-plus mr-2"></i> Tambah Produk Pertama
        </a>
    </div>
    @endif
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="flex justify-between items-center pb-3">
            <h3 class="text-lg font-semibold text-gray-900">Konfirmasi Hapus</h3>
            <button type="button"
                class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center"
                onclick="closeDeleteModal()">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd"
                        d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                        clip-rule="evenodd"></path>
                </svg>
            </button>
        </div>
        <div class="py-4">
            <p class="text-gray-600 mb-3">Apakah Anda yakin ingin menghapus produk <strong id="productName"
                    class="text-gray-900"></strong>?</p>
            <p class="text-red-600 text-sm">Tindakan ini tidak dapat dibatalkan.</p>
        </div>
        <div class="flex items-center pt-3 space-x-2 border-t border-gray-200 rounded-b">
            <button type="button"
                class="text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-blue-300 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10"
                onclick="closeDeleteModal()">
                Batal
            </button>
            <form id="deleteForm" method="POST" class="inline">
                @csrf
                @method('DELETE')
                <button type="submit"
                    class="text-white bg-red-600 hover:bg-red-700 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">
                    Hapus
                </button>
            </form>
        </div>
    </div>
</div>

<script>
function deleteProduct(productId, productName) {
    document.getElementById('productName').textContent = productName;
    document.getElementById('deleteForm').action = `/marketplace/${productId}`;
    document.getElementById('deleteModal').classList.remove('hidden');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
}

// Close modal when clicking outside
document.getElementById('deleteModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeDeleteModal();
    }
});

// Close modal with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeDeleteModal();
    }
});
</script>
@endsection