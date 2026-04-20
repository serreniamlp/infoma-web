@extends('layouts.app')

@section('title', 'Beli Produk')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Breadcrumb -->
        <nav class="flex mb-8" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="{{ route('marketplace.index') }}"
                       class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path>
                        </svg>
                        Marketplace
                    </a>
                </li>
                <li>
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <a href="{{ route('marketplace.show', $product) }}"
                           class="ml-1 text-sm font-medium text-gray-700 hover:text-blue-600 md:ml-2 transition-colors">
                            {{ $product->name }}
                        </a>
                    </div>
                </li>
                <li aria-current="page">
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Beli Produk</span>
                    </div>
                </li>
            </ol>
        </nav>

        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <!-- Header -->
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4">
                <h1 class="text-2xl font-bold text-white flex items-center">
                    <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.5 5M7 13l2.5 5m6-5v6a2 2 0 01-2 2H9a2 2 0 01-2-2v-6m6 0V9a2 2 0 00-2-2H9a2 2 0 00-2 2v4.01"></path>
                    </svg>
                    Beli Produk
                </h1>
            </div>

            <div class="p-6">
                <!-- Product Summary -->
                <div class="bg-gray-50 rounded-lg p-6 mb-8">
                    <div class="flex flex-col lg:flex-row gap-6">
                        <div class="lg:w-1/4 flex-shrink-0">
                            <img src="{{ $product->main_image }}"
                                 class="w-full h-48 lg:h-32 object-cover rounded-lg shadow-sm"
                                 alt="{{ $product->name }}">
                        </div>
                        <div class="lg:w-3/4">
                            <h2 class="text-xl font-bold text-gray-900 mb-2">{{ $product->name }}</h2>
                            <p class="text-gray-600 mb-4 leading-relaxed">{{ Str::limit($product->description, 100) }}</p>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                <div class="flex items-center">
                                    <span class="font-medium text-gray-700 w-24">Harga:</span>
                                    <span class="text-lg font-bold text-green-600">Rp {{ number_format($product->price, 0, ',', '.') }}</span>
                                </div>
                                <div class="flex items-center">
                                    <span class="font-medium text-gray-700 w-24">Stok:</span>
                                    <span class="text-blue-600 font-semibold">{{ $product->stock_quantity }} tersedia</span>
                                </div>
                                <div class="flex items-center md:col-span-2">
                                    <span class="font-medium text-gray-700 w-24">Penjual:</span>
                                    <span class="text-gray-900 font-medium">{{ $product->seller->name }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <form action="{{ route('marketplace.transactions.store', $product) }}" method="POST" class="space-y-6">
                    @csrf

                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                        <!-- Left Column - Form Fields -->
                        <div class="lg:col-span-2 space-y-6">
                            <!-- Quantity -->
                            <div>
                                <label for="quantity" class="block text-sm font-semibold text-gray-700 mb-2">
                                    Jumlah <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <input type="number"
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors @error('quantity') border-red-500 ring-2 ring-red-200 @enderror"
                                           id="quantity" name="quantity" value="{{ old('quantity', 1) }}"
                                           min="1" max="{{ $product->stock_quantity }}" required>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400">
                                        <span class="text-sm">Maks: {{ $product->stock_quantity }}</span>
                                    </div>
                                </div>
                                @error('quantity')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Buyer Information Section -->
                            <div class="bg-blue-50 rounded-lg p-6">
                                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                                    <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                    Informasi Pembeli
                                </h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="md:col-span-2">
                                        <label for="buyer_name" class="block text-sm font-medium text-gray-700 mb-2">
                                            Nama Lengkap <span class="text-red-500">*</span>
                                        </label>
                                        <input type="text"
                                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors @error('buyer_name') border-red-500 ring-2 ring-red-200 @enderror"
                                               id="buyer_name" name="buyer_name" value="{{ old('buyer_name', auth()->user()->name) }}" required>
                                        @error('buyer_name')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label for="buyer_phone" class="block text-sm font-medium text-gray-700 mb-2">
                                            Nomor Telepon <span class="text-red-500">*</span>
                                        </label>
                                        <input type="text"
                                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors @error('buyer_phone') border-red-500 ring-2 ring-red-200 @enderror"
                                               id="buyer_phone" name="buyer_phone" value="{{ old('buyer_phone', auth()->user()->phone) }}" required>
                                        @error('buyer_phone')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label for="payment_method" class="block text-sm font-medium text-gray-700 mb-2">
                                            Metode Pembayaran <span class="text-red-500">*</span>
                                        </label>
                                        <select class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors @error('payment_method') border-red-500 ring-2 ring-red-200 @enderror"
                                                id="payment_method" name="payment_method" required>
                                            <option value="">Pilih Metode</option>
                                            <option value="Bank Transfer" {{ old('payment_method') == 'Bank Transfer' ? 'selected' : '' }}>Bank Transfer</option>
                                            <option value="E-Wallet" {{ old('payment_method') == 'E-Wallet' ? 'selected' : '' }}>E-Wallet</option>
                                            <option value="Cash" {{ old('payment_method') == 'Cash' ? 'selected' : '' }}>Cash</option>
                                        </select>
                                        @error('payment_method')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div class="md:col-span-2">
                                        <label for="buyer_address" class="block text-sm font-medium text-gray-700 mb-2">
                                            Alamat Lengkap <span class="text-red-500">*</span>
                                        </label>
                                        <textarea class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors @error('buyer_address') border-red-500 ring-2 ring-red-200 @enderror"
                                                  id="buyer_address" name="buyer_address" rows="3" required
                                                  placeholder="Alamat lengkap untuk pengiriman">{{ old('buyer_address', auth()->user()->address) }}</textarea>
                                        @error('buyer_address')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Delivery Information Section -->
                            <div class="bg-green-50 rounded-lg p-6">
                                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                                    <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                    </svg>
                                    Informasi Pengiriman
                                </h3>
                                <div class="space-y-4">
                                    <div>
                                        <label for="pickup_method" class="block text-sm font-medium text-gray-700 mb-2">
                                            Metode Pengambilan <span class="text-red-500">*</span>
                                        </label>
                                        <select class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors @error('pickup_method') border-red-500 ring-2 ring-red-200 @enderror"
                                                id="pickup_method" name="pickup_method" required>
                                            <option value="">Pilih Metode</option>
                                            <option value="pickup" {{ old('pickup_method') == 'pickup' ? 'selected' : '' }}>Ambil Sendiri</option>
                                            <option value="delivery" {{ old('pickup_method') == 'delivery' ? 'selected' : '' }}>Diantar</option>
                                            <option value="meetup" {{ old('pickup_method') == 'meetup' ? 'selected' : '' }}>Bertemu</option>
                                        </select>
                                        @error('pickup_method')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <!-- Pickup Address (conditional) -->
                                    <div id="pickup_address_field" class="hidden">
                                        <label for="pickup_address" class="block text-sm font-medium text-gray-700 mb-2">
                                            Alamat Pengambilan
                                        </label>
                                        <textarea class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors @error('pickup_address') border-red-500 ring-2 ring-red-200 @enderror"
                                                  id="pickup_address" name="pickup_address" rows="2"
                                                  placeholder="Alamat tempat pengambilan barang">{{ old('pickup_address') }}</textarea>
                                        @error('pickup_address')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label for="pickup_notes" class="block text-sm font-medium text-gray-700 mb-2">
                                            Catatan (Opsional)
                                        </label>
                                        <textarea class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors @error('pickup_notes') border-red-500 ring-2 ring-red-200 @enderror"
                                                  id="pickup_notes" name="pickup_notes" rows="2"
                                                  placeholder="Catatan tambahan untuk penjual">{{ old('pickup_notes') }}</textarea>
                                        @error('pickup_notes')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column - Order Summary -->
                        <div class="lg:col-span-1">
                            <div class="sticky top-6">
                                <div class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-lg p-6 border border-gray-200">
                                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                                        <svg class="w-5 h-5 mr-2 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                        </svg>
                                        Ringkasan Pesanan
                                    </h3>
                                    <div class="space-y-3">
                                        <div class="flex justify-between items-center py-2">
                                            <span class="text-gray-600">Harga Satuan:</span>
                                            <span class="font-semibold text-gray-900">Rp {{ number_format($product->price, 0, ',', '.') }}</span>
                                        </div>
                                        <div class="flex justify-between items-center py-2">
                                            <span class="text-gray-600">Jumlah:</span>
                                            <span id="quantity_display" class="font-semibold text-blue-600">1</span>
                                        </div>
                                        <div class="border-t pt-3">
                                            <div class="flex justify-between items-center">
                                                <span class="text-lg font-bold text-gray-900">Total:</span>
                                                <span id="total_display" class="text-lg font-bold text-green-600">Rp {{ number_format($product->price, 0, ',', '.') }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <div class="mt-6 space-y-3">
                                    <button type="submit"
                                            class="w-full bg-gradient-to-r from-blue-600 to-blue-700 text-white py-4 px-6 rounded-lg font-semibold hover:from-blue-700 hover:to-blue-800 focus:outline-none focus:ring-4 focus:ring-blue-300 transition-all duration-200 flex items-center justify-center">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.5 5M7 13l2.5 5m6-5v6a2 2 0 01-2 2H9a2 2 0 01-2-2v-6m6 0V9a2 2 0 00-2-2H9a2 2 0 00-2 2v4.01"></path>
                                        </svg>
                                        Buat Pesanan
                                    </button>
                                    <a href="{{ route('marketplace.show', $product) }}"
                                       class="w-full bg-gray-200 text-gray-700 py-4 px-6 rounded-lg font-medium hover:bg-gray-300 focus:outline-none focus:ring-4 focus:ring-gray-300 transition-colors duration-200 flex items-center justify-center">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                                        </svg>
                                        Kembali
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const quantityInput = document.getElementById('quantity');
    const pickupMethodSelect = document.getElementById('pickup_method');
    const pickupAddressField = document.getElementById('pickup_address_field');
    const quantityDisplay = document.getElementById('quantity_display');
    const totalDisplay = document.getElementById('total_display');
    const productPrice = {{ $product->price }};

    // Update quantity and total with smooth animation
    function updateOrderSummary() {
        const quantity = parseInt(quantityInput.value) || 1;
        const total = productPrice * quantity;

        quantityDisplay.textContent = quantity;
        totalDisplay.textContent = 'Rp ' + total.toLocaleString('id-ID');

        // Add pulse animation to highlight changes
        quantityDisplay.classList.add('animate-pulse');
        totalDisplay.classList.add('animate-pulse');
        setTimeout(() => {
            quantityDisplay.classList.remove('animate-pulse');
            totalDisplay.classList.remove('animate-pulse');
        }, 500);
    }

    // Show/hide pickup address field with smooth transition
    function togglePickupAddress() {
        const pickupAddressInput = document.getElementById('pickup_address');

        if (pickupMethodSelect.value === 'pickup') {
            pickupAddressField.classList.remove('hidden');
            pickupAddressField.classList.add('animate-fadeIn');
            pickupAddressInput.required = true;
        } else {
            pickupAddressField.classList.add('hidden');
            pickupAddressField.classList.remove('animate-fadeIn');
            pickupAddressInput.required = false;
        }
    }

    // Event listeners with debounce for better performance
    let quantityTimeout;
    quantityInput.addEventListener('input', function() {
        clearTimeout(quantityTimeout);
        quantityTimeout = setTimeout(updateOrderSummary, 300);
    });

    pickupMethodSelect.addEventListener('change', togglePickupAddress);

    // Initial setup
    updateOrderSummary();
    togglePickupAddress();

    // Add floating label effect for better UX
    const inputs = document.querySelectorAll('input[type="text"], input[type="number"], textarea, select');
    inputs.forEach(input => {
        input.addEventListener('focus', function() {
            this.parentElement.classList.add('ring-2', 'ring-blue-500');
        });

        input.addEventListener('blur', function() {
            this.parentElement.classList.remove('ring-2', 'ring-blue-500');
        });
    });
});

// Add custom CSS for animations
const style = document.createElement('style');
style.textContent = `
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-fadeIn {
        animation: fadeIn 0.3s ease-out;
    }
`;
document.head.appendChild(style);
</script>
@endsection
