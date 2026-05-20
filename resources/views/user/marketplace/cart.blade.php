@extends('layouts.app')

@section('title', 'Keranjang Belanja - INFOMA')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Breadcrumb --}}
        <nav class="flex mb-6" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li>
                    <a href="{{ route('marketplace.index') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-orange-600 transition-colors">
                        <i class="fas fa-store mr-2"></i>Marketplace
                    </a>
                </li>
                <li>
                    <div class="flex items-center">
                        <i class="fas fa-chevron-right text-gray-400 mx-2 text-xs"></i>
                        <span class="text-sm font-medium text-gray-500">Keranjang Belanja</span>
                    </div>
                </li>
            </ol>
        </nav>

        {{-- Header --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-6">
            <div class="bg-gradient-to-r from-orange-500 to-orange-600 px-8 py-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-white flex items-center gap-3">
                            <i class="fas fa-shopping-cart"></i> Keranjang Belanja
                        </h1>
                        <p class="text-orange-100 mt-1 text-sm">
                            {{ $cartItems->count() }} jenis produk di keranjang Anda
                        </p>
                    </div>
                    @if($cartItems->count() > 0)
                    <form method="POST" action="{{ route('user.marketplace.cart.clear') }}" onsubmit="return confirm('Kosongkan semua keranjang?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-orange-100 hover:text-white text-sm flex items-center gap-1 transition-colors">
                            <i class="fas fa-trash-alt"></i> Kosongkan
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>

        {{-- Flash Messages --}}
        @if(session('success'))
        <div class="mb-4 flex items-center gap-3 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-xl">
            <i class="fas fa-check-circle text-green-500"></i> {{ session('success') }}
        </div>
        @endif
        @if(session('error'))
        <div class="mb-4 flex items-center gap-3 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl">
            <i class="fas fa-exclamation-circle text-red-500"></i> {{ session('error') }}
        </div>
        @endif

        @if($cartItems->count() > 0)
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Cart Items --}}
            <div class="lg:col-span-2 space-y-4">
                @foreach($cartItems as $item)
                @php $product = $item->product; @endphp
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 flex gap-4 items-start">
                    {{-- Image --}}
                    <a href="{{ route('marketplace.show', $product) }}" class="flex-shrink-0">
                        <img src="{{ $product->main_image }}" alt="{{ $product->name }}"
                            class="w-20 h-20 object-cover rounded-lg border border-gray-100">
                    </a>

                    {{-- Info --}}
                    <div class="flex-1 min-w-0">
                        <p class="text-xs text-orange-600 font-medium uppercase tracking-wide mb-0.5">
                            {{ $product->category->name ?? '—' }}
                        </p>
                        <a href="{{ route('marketplace.show', $product) }}"
                           class="text-gray-900 font-semibold text-sm hover:text-orange-600 transition-colors line-clamp-2">
                            {{ $product->name }}
                        </a>
                        <p class="text-orange-600 font-bold text-base mt-1">
                            Rp {{ number_format($product->price, 0, ',', '.') }}
                        </p>
                        <p class="text-xs text-gray-400 mt-0.5">
                            Stok tersedia: {{ $product->stock_quantity }}
                        </p>

                        {{-- Quantity & Actions --}}
                        <div class="flex items-center gap-3 mt-3">
                            <form method="POST" action="{{ route('user.marketplace.cart.update', $item) }}" class="flex items-center gap-2">
                                @csrf
                                @method('PATCH')
                                <label class="text-xs text-gray-500">Jumlah:</label>
                                <div class="flex items-center border border-gray-200 rounded-lg overflow-hidden">
                                    <button type="button" onclick="changeQty(this, -1)" class="px-2 py-1 text-gray-500 hover:bg-gray-100 transition text-sm">−</button>
                                    <input type="number" name="quantity" value="{{ $item->quantity }}" min="1" max="{{ $product->stock_quantity }}"
                                        class="w-12 text-center text-sm border-x border-gray-200 py-1 focus:outline-none qty-input">
                                    <button type="button" onclick="changeQty(this, 1)" class="px-2 py-1 text-gray-500 hover:bg-gray-100 transition text-sm">+</button>
                                </div>
                                <button type="submit" class="text-xs text-blue-600 hover:text-blue-700 transition">
                                    <i class="fas fa-sync-alt"></i> Update
                                </button>
                            </form>

                            <form method="POST" action="{{ route('user.marketplace.cart.remove', $item) }}" onsubmit="return confirm('Hapus produk dari keranjang?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs text-red-500 hover:text-red-700 transition flex items-center gap-1">
                                    <i class="fas fa-times"></i> Hapus
                                </button>
                            </form>
                        </div>
                    </div>

                    {{-- Subtotal --}}
                    <div class="flex-shrink-0 text-right">
                        <p class="text-xs text-gray-400">Subtotal</p>
                        <p class="text-gray-900 font-bold text-sm">
                            Rp {{ number_format($item->quantity * $product->price, 0, ',', '.') }}
                        </p>
                    </div>
                </div>
                @endforeach
            </div>

            {{-- Order Summary --}}
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 sticky top-24">
                    <h2 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <i class="fas fa-receipt text-orange-500"></i> Ringkasan
                    </h2>

                    <div class="space-y-3 mb-4">
                        @foreach($cartItems as $item)
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600 truncate flex-1 mr-2">{{ Str::limit($item->product->name, 24) }} ×{{ $item->quantity }}</span>
                            <span class="text-gray-800 font-medium flex-shrink-0">Rp {{ number_format($item->quantity * $item->product->price, 0, ',', '.') }}</span>
                        </div>
                        @endforeach
                    </div>

                    <div class="border-t border-gray-100 pt-3 mb-5">
                        <div class="flex justify-between">
                            <span class="font-bold text-gray-900">Total</span>
                            <span class="font-bold text-orange-600 text-lg">Rp {{ number_format($total, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    {{-- Note: cart items go to individual product checkout --}}
                    <div class="bg-orange-50 border border-orange-100 rounded-lg p-3 mb-4 text-xs text-orange-700">
                        <i class="fas fa-info-circle mr-1"></i>
                        Setiap produk diproses secara terpisah melalui halaman detail produk.
                    </div>

                    <div class="space-y-2">
                        @foreach($cartItems as $item)
                        <a href="{{ route('user.marketplace.transactions.create', $item->product) }}"
                           class="block w-full text-center py-2 px-4 bg-orange-500 hover:bg-orange-600 text-white text-sm font-semibold rounded-lg transition-colors truncate">
                            Beli: {{ Str::limit($item->product->name, 20) }}
                        </a>
                        @endforeach
                    </div>

                    <a href="{{ route('marketplace.index') }}" class="block w-full text-center mt-3 py-2 px-4 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition-colors">
                        <i class="fas fa-arrow-left mr-1"></i> Lanjut Belanja
                    </a>
                </div>
            </div>
        </div>

        @else
        {{-- Empty Cart --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-16 text-center">
            <div class="w-24 h-24 bg-orange-50 rounded-full flex items-center justify-center mx-auto mb-6">
                <i class="fas fa-shopping-cart text-orange-300 text-4xl"></i>
            </div>
            <h2 class="text-xl font-bold text-gray-900 mb-2">Keranjang Masih Kosong</h2>
            <p class="text-gray-500 mb-8">Tambahkan produk dari marketplace ke keranjang Anda.</p>
            <a href="{{ route('marketplace.index') }}"
               class="inline-flex items-center gap-2 bg-orange-500 hover:bg-orange-600 text-white px-6 py-3 rounded-xl font-semibold transition-colors">
                <i class="fas fa-store"></i> Jelajahi Marketplace
            </a>
        </div>
        @endif
    </div>
</div>

<script>
function changeQty(btn, delta) {
    const container = btn.closest('form');
    const input = container.querySelector('.qty-input');
    const max = parseInt(input.getAttribute('max')) || 99;
    let val = parseInt(input.value) + delta;
    if (val < 1) val = 1;
    if (val > max) val = max;
    input.value = val;
}
</script>
@endsection
