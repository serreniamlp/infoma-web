@extends('layouts.app')

@section('title', 'Beli Produk - EduLiving')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Breadcrumb --}}
        <nav class="flex mb-8" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="{{ route('marketplace.index') }}"
                       class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 transition-colors">
                        <i class="fas fa-store w-4 h-4 mr-2"></i>Marketplace
                    </a>
                </li>
                <li>
                    <div class="flex items-center">
                        <i class="fas fa-chevron-right text-gray-400 text-xs mx-2"></i>
                        <a href="{{ route('marketplace.show', $product) }}"
                           class="text-sm font-medium text-gray-700 hover:text-blue-600 transition-colors">
                            {{ Str::limit($product->name, 30) }}
                        </a>
                    </div>
                </li>
                <li aria-current="page">
                    <div class="flex items-center">
                        <i class="fas fa-chevron-right text-gray-400 text-xs mx-2"></i>
                        <span class="text-sm font-medium text-gray-500">Beli Produk</span>
                    </div>
                </li>
            </ol>
        </nav>

        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            {{-- Header --}}
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4">
                <h1 class="text-2xl font-bold text-white flex items-center gap-3">
                    <i class="fas fa-shopping-cart"></i>Beli Produk
                </h1>
            </div>

            <div class="p-6">
                {{-- Product Summary --}}
                <div class="bg-gray-50 rounded-xl p-5 mb-8 border border-gray-100">
                    <div class="flex flex-col lg:flex-row gap-5">
                        <div class="lg:w-1/4 flex-shrink-0">
                            <img src="{{ $product->main_image }}"
                                 class="w-full h-48 lg:h-32 object-cover rounded-lg shadow-sm"
                                 alt="{{ $product->name }}">
                        </div>
                        <div class="lg:w-3/4">
                            <h2 class="text-xl font-bold text-gray-900 mb-1">{{ $product->name }}</h2>
                            <p class="text-gray-500 text-sm mb-3 leading-relaxed">{{ Str::limit($product->description, 100) }}</p>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-sm">
                                <div class="flex items-center gap-2">
                                    <span class="text-gray-500 w-24">Harga:</span>
                                    <span class="text-lg font-bold text-green-600">Rp {{ number_format($product->price, 0, ',', '.') }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-gray-500 w-24">Stok:</span>
                                    <span class="text-blue-600 font-semibold">{{ $product->stock_quantity }} tersedia</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-gray-500 w-24">Penjual:</span>
                                    <span class="font-medium text-gray-900">{{ $product->seller->name }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-gray-500 w-24">Lokasi:</span>
                                    <span class="text-gray-700">{{ $product->location }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <form action="{{ route('user.marketplace.transactions.store', $product) }}"
                      method="POST" class="space-y-6" id="checkoutForm">
                    @csrf

                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                        {{-- ── Kolom Kiri: Form ── --}}
                        <div class="lg:col-span-2 space-y-6">

                            {{-- Jumlah --}}
                            <div>
                                <label for="quantity" class="block text-sm font-semibold text-gray-700 mb-2">
                                    Jumlah <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <input type="number" id="quantity" name="quantity"
                                           value="{{ old('quantity', 1) }}"
                                           min="1" max="{{ $product->stock_quantity }}" required
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                                                  @error('quantity') border-red-500 ring-2 ring-red-200 @enderror">
                                    <span class="absolute inset-y-0 right-3 flex items-center text-xs text-gray-400">
                                        Maks: {{ $product->stock_quantity }}
                                    </span>
                                </div>
                                @error('quantity')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>

                            {{-- Informasi Pembeli --}}
                            <div class="bg-blue-50 rounded-xl p-5 border border-blue-100">
                                <h3 class="text-base font-semibold text-gray-900 mb-4 flex items-center gap-2">
                                    <i class="fas fa-user text-blue-600"></i>Informasi Pembeli
                                </h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                            Nama Lengkap <span class="text-red-500">*</span>
                                        </label>
                                        <input type="text" name="buyer_name" required
                                               value="{{ old('buyer_name', auth()->user()->name) }}"
                                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500
                                                      @error('buyer_name') border-red-500 @enderror">
                                        @error('buyer_name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                            Nomor Telepon <span class="text-red-500">*</span>
                                        </label>
                                        <input type="text" name="buyer_phone" required
                                               value="{{ old('buyer_phone', auth()->user()->phone) }}"
                                               placeholder="08xxxxxxxxxx"
                                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500
                                                      @error('buyer_phone') border-red-500 @enderror">
                                        @error('buyer_phone')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                                    </div>

                                    {{-- [MIDTRANS] Dropdown payment_method dihapus.
                                         Metode pembayaran dipilih langsung di popup Midtrans Snap
                                         setelah pesanan dibuat. COD ditentukan otomatis dari pickup_method. --}}
                                    <div class="md:col-span-2">
                                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 text-sm text-blue-700 flex items-center gap-2">
                                            <i class="fas fa-info-circle text-blue-500"></i>
                                            Metode pembayaran dipilih di langkah berikutnya.
                                            Tersedia transfer bank, GoPay, OVO, QRIS, dan lainnya.
                                            Pilih <strong>COD</strong> di metode pengambilan jika ingin bayar di tempat.
                                        </div>
                                    </div>
                                    {{-- [MIDTRANS-END] --}}
                                </div>
                            </div>

                            {{-- ══════════════════════════════════════════════ --}}
                            {{-- METODE PENGAMBILAN (DINAMIS SESUAI SELLER)    --}}
                            {{-- ══════════════════════════════════════════════ --}}
                            <div class="bg-green-50 rounded-xl p-5 border border-green-100">
                                <h3 class="text-base font-semibold text-gray-900 mb-1 flex items-center gap-2">
                                    <i class="fas fa-truck text-green-600"></i>Metode Pengambilan
                                    <span class="text-red-500">*</span>
                                </h3>
                                <p class="text-xs text-gray-500 mb-4">Penjual hanya menyediakan metode berikut:</p>

                                @php
                                    $activeMethods = $product->getActivePickupMethods();
                                @endphp

                                @if(count($activeMethods) === 0)
                                    {{-- Fallback jika seller belum set metode (data lama) --}}
                                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 text-sm text-yellow-800">
                                        <i class="fas fa-exclamation-triangle mr-1.5"></i>
                                        Penjual belum mengatur metode pengambilan. Hubungi penjual untuk informasi lebih lanjut.
                                    </div>
                                    {{-- Fallback ke select biasa jika seller belum set metode --}}
                                    <select name="pickup_method" required
                                            class="mt-3 w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                                        <option value="">-- Pilih Metode --</option>
                                        <option value="pickup"   {{ old('pickup_method') == 'pickup'   ? 'selected' : '' }}>Ambil Sendiri</option>
                                        <option value="delivery" {{ old('pickup_method') == 'delivery' ? 'selected' : '' }}>Diantar Seller</option>
                                        <option value="cod"      {{ old('pickup_method') == 'cod'      ? 'selected' : '' }}>COD (Bayar di Tempat)</option>
                                    </select>
                                @else
                                    {{-- Tampilkan hanya metode yang seller aktifkan --}}
                                    <input type="hidden" name="pickup_method" id="selectedPickupMethod"
                                           value="{{ old('pickup_method', count($activeMethods) === 1 ? array_key_first($activeMethods) : '') }}">

                                    <div class="space-y-2" id="pickupMethodOptions">
                                        @foreach($activeMethods as $key => $method)
                                        @php
                                            $isSelected = old('pickup_method', count($activeMethods) === 1 ? $key : '') === $key;
                                            $colorClass = match($method['color']) {
                                                'green'  => ['border' => 'border-green-400',  'bg' => 'bg-green-50',  'text' => 'text-green-700',  'icon' => 'text-green-500'],
                                                'blue'   => ['border' => 'border-blue-400',   'bg' => 'bg-blue-50',   'text' => 'text-blue-700',   'icon' => 'text-blue-500'],
                                                'orange' => ['border' => 'border-orange-400', 'bg' => 'bg-orange-50', 'text' => 'text-orange-700', 'icon' => 'text-orange-500'],
                                                default  => ['border' => 'border-gray-300',   'bg' => 'bg-white',     'text' => 'text-gray-700',   'icon' => 'text-gray-500'],
                                            };
                                        @endphp
                                        <div class="pickup-option border-2 rounded-xl p-4 cursor-pointer transition-all
                                                    {{ $isSelected ? $colorClass['border'] . ' ' . $colorClass['bg'] : 'border-gray-200 bg-white hover:border-gray-300' }}"
                                             data-method="{{ $key }}"
                                             data-need-address="{{ $method['need_address'] ? 'true' : 'false' }}"
                                             data-color="{{ $method['color'] }}"
                                             onclick="pilihMetode('{{ $key }}', {{ $method['need_address'] ? 'true' : 'false' }}, '{{ $method['color'] }}')">
                                            <div class="flex items-start gap-3">
                                                <div class="w-5 h-5 rounded-full border-2 border-gray-300 flex items-center justify-center mt-0.5 shrink-0 method-radio
                                                            {{ $isSelected ? 'border-' . $method['color'] . '-500 bg-' . $method['color'] . '-500' : '' }}"
                                                     id="radio-{{ $key }}">
                                                    @if($isSelected)
                                                    <div class="w-2 h-2 rounded-full bg-white"></div>
                                                    @endif
                                                </div>
                                                <div class="flex-1">
                                                    <div class="flex items-center gap-2 mb-0.5">
                                                        <i class="fas {{ $method['icon'] }} {{ $colorClass['icon'] }} text-sm"></i>
                                                        <span class="font-semibold text-gray-900 text-sm">{{ $method['label'] }}</span>
                                                    </div>
                                                    <p class="text-xs text-gray-500">{{ $method['description'] }}</p>

                                                    {{-- Info khusus COD --}}
                                                    @if($key === 'cod')
                                                    <p class="text-xs text-green-600 mt-1 font-medium">
                                                        <i class="fas fa-check-circle mr-1"></i>Tidak perlu transfer di muka
                                                    </p>
                                                    @endif

                                                    {{-- Info khusus pickup — tampilkan alamat seller --}}
                                                    @if($key === 'pickup' && $product->pickup_address)
                                                    <div class="mt-2 text-xs text-orange-700 bg-orange-50 border border-orange-100 rounded-lg px-3 py-2">
                                                        <i class="fas fa-map-marker-alt mr-1"></i>
                                                        <strong>Alamat pengambilan:</strong> {{ $product->pickup_address }}
                                                    </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>

                                    @error('pickup_method')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                @endif

                                {{-- Alamat tujuan — muncul hanya jika metode "Diantar" dipilih --}}
                                {{-- [REVISI-3] Alamat tujuan — dengan pilihan alamat tersimpan --}}
                                @php
                                    $savedAddresses = auth()->user()->addresses()
                                        ->orderByDesc('is_default')
                                        ->orderBy('label')
                                        ->get();
                                    $showDelivery = old('pickup_method') === 'delivery'
                                        || (count($activeMethods) === 1 && array_key_first($activeMethods) === 'delivery');
                                @endphp

                                <div id="deliveryAddressField"
                                     class="{{ $showDelivery ? '' : 'hidden' }} mt-5 space-y-4">
                                    <div class="border-t border-green-100 pt-4">
                                        <label class="block text-sm font-semibold text-gray-800 mb-3">
                                            <i class="fas fa-map-marker-alt text-blue-500 mr-1.5"></i>
                                            Alamat Tujuan Pengiriman <span class="text-red-500">*</span>
                                        </label>

                                        @if($savedAddresses->isNotEmpty())
                                        {{-- Pilih dari alamat tersimpan --}}
                                        <div class="mb-3">
                                            <p class="text-xs text-gray-500 mb-2">Pilih dari alamat tersimpan:</p>
                                            <div class="space-y-2" id="savedAddressList">
                                                @foreach($savedAddresses as $saved)
                                                <div class="saved-address-card border-2 rounded-xl p-3 cursor-pointer transition-all
                                                            {{ old('selected_address_id') == $saved->id ? 'border-blue-400 bg-blue-50' : 'border-gray-200 hover:border-gray-300' }}"
                                                     data-id="{{ $saved->id }}"
                                                     data-name="{{ $saved->recipient_name }}"
                                                     data-phone="{{ $saved->phone }}"
                                                     data-address="{{ $saved->address }}"
                                                     onclick="pilihAlamatTersimpan({{ $saved->id }}, '{{ addslashes($saved->recipient_name) }}', '{{ addslashes($saved->phone) }}', '{{ addslashes($saved->address) }}')">
                                                    <div class="flex items-start justify-between gap-2">
                                                        <div>
                                                            <div class="flex items-center gap-2 mb-0.5">
                                                                <span class="text-xs font-semibold text-gray-700">{{ $saved->label }}</span>
                                                                @if($saved->is_default)
                                                                    <span class="text-xs bg-blue-600 text-white px-1.5 py-0.5 rounded-full">Default</span>
                                                                @endif
                                                            </div>
                                                            <p class="text-sm font-medium text-gray-900">{{ $saved->recipient_name }}</p>
                                                            <p class="text-xs text-gray-500">{{ $saved->phone }}</p>
                                                            <p class="text-xs text-gray-600 mt-0.5">{{ Str::limit($saved->address, 60) }}</p>
                                                        </div>
                                                        <div class="w-5 h-5 rounded-full border-2 border-gray-300 shrink-0 mt-0.5 flex items-center justify-center
                                                                    {{ old('selected_address_id') == $saved->id ? 'border-blue-500 bg-blue-500' : '' }}"
                                                             id="radio-addr-{{ $saved->id }}">
                                                            @if(old('selected_address_id') == $saved->id)
                                                                <div class="w-2 h-2 rounded-full bg-white"></div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                                @endforeach

                                                {{-- Opsi isi manual --}}
                                                <div class="saved-address-card border-2 rounded-xl p-3 cursor-pointer transition-all
                                                            {{ old('selected_address_id') === 'manual' ? 'border-blue-400 bg-blue-50' : 'border-dashed border-gray-300 hover:border-gray-400' }}"
                                                     data-id="manual"
                                                     onclick="pilihAlamatManual()">
                                                    <div class="flex items-center gap-2">
                                                        <div class="w-5 h-5 rounded-full border-2 border-gray-300 shrink-0 flex items-center justify-center
                                                                    {{ old('selected_address_id') === 'manual' ? 'border-blue-500 bg-blue-500' : '' }}"
                                                             id="radio-addr-manual">
                                                            @if(old('selected_address_id') === 'manual')
                                                                <div class="w-2 h-2 rounded-full bg-white"></div>
                                                            @endif
                                                        </div>
                                                        <span class="text-sm text-gray-600 font-medium">
                                                            <i class="fas fa-pen text-xs mr-1.5 text-gray-400"></i>Isi alamat manual
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        @endif

                                        {{-- Hidden input untuk track pilihan --}}
                                        <input type="hidden" name="selected_address_id" id="selectedAddressId"
                                               value="{{ old('selected_address_id') }}">

                                        {{-- Field alamat manual --}}
                                        <div id="manualAddressField"
                                             class="{{ $savedAddresses->isEmpty() || old('selected_address_id') === 'manual' ? '' : 'hidden' }} space-y-2">
                                            @if($savedAddresses->isNotEmpty())
                                            <p class="text-xs text-gray-500 font-medium">Isi alamat baru:</p>
                                            @endif
                                            <textarea name="buyer_address" id="buyer_address" rows="3"
                                                      placeholder="Alamat lengkap tujuan pengiriman"
                                                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm
                                                             @error('buyer_address') border-red-500 @enderror">{{ old('buyer_address', $savedAddresses->isEmpty() ? auth()->user()->address : '') }}</textarea>
                                            @error('buyer_address')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                                            @if($savedAddresses->isNotEmpty())
                                            <p class="text-xs text-blue-600">
                                                <i class="fas fa-info-circle mr-1"></i>
                                                Ingin simpan alamat ini?
                                                <a href="{{ route('user.addresses.index') }}" target="_blank"
                                                   class="underline hover:text-blue-800">Tambah di Alamat Saya</a>
                                            </p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                {{-- [REVISI-3-END] --}}

                                {{-- Catatan ke seller --}}
                                <div class="mt-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                        Catatan <span class="text-gray-400 font-normal">(Opsional)</span>
                                    </label>
                                    <textarea name="pickup_notes" rows="2"
                                              placeholder="Catatan tambahan untuk penjual..."
                                              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm">{{ old('pickup_notes') }}</textarea>
                                </div>
                            </div>
                        </div>

                        {{-- ── Kolom Kanan: Ringkasan ── --}}
                        <div class="lg:col-span-1">
                            <div class="sticky top-6 space-y-4">

                                {{-- Ringkasan pesanan --}}
                                <div class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-xl p-5 border border-gray-200">
                                    <h3 class="text-base font-semibold text-gray-900 mb-4 flex items-center gap-2">
                                        <i class="fas fa-receipt text-gray-600"></i>Ringkasan
                                    </h3>
                                    <div class="space-y-2.5 text-sm">
                                        <div class="flex justify-between">
                                            <span class="text-gray-500">Harga Satuan</span>
                                            <span class="font-medium">Rp {{ number_format($product->price, 0, ',', '.') }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-500">Jumlah</span>
                                            <span id="quantity_display" class="font-medium text-blue-600">1</span>
                                        </div>
                                        <div class="border-t pt-2.5">
                                            <div class="flex justify-between">
                                                <span class="font-bold text-gray-900">Total</span>
                                                <span id="total_display" class="font-bold text-green-600 text-base">
                                                    Rp {{ number_format($product->price, 0, ',', '.') }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Info metode terpilih --}}
                                <div id="selectedMethodInfo" class="hidden bg-white rounded-xl p-4 border border-gray-200">
                                    <p class="text-xs text-gray-500 mb-1">Metode terpilih:</p>
                                    <p id="selectedMethodLabel" class="font-semibold text-sm text-gray-900"></p>
                                </div>

                                {{-- Tombol aksi --}}
                                <div class="space-y-2.5">
                                    <button type="submit"
                                            class="w-full bg-blue-600 hover:bg-blue-700 text-white py-3.5 px-6 rounded-xl
                                                   font-semibold text-sm transition-colors flex items-center justify-center gap-2 shadow-sm">
                                        <i class="fas fa-shopping-cart"></i>Buat Pesanan
                                    </button>
                                    <a href="{{ route('marketplace.show', $product) }}"
                                       class="w-full bg-gray-100 hover:bg-gray-200 text-gray-700 py-3 px-6 rounded-xl
                                              font-medium text-sm transition-colors flex items-center justify-center gap-2">
                                        <i class="fas fa-arrow-left"></i>Kembali
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
@endsection

@push('scripts')
<script>
const productPrice   = {{ $product->price }};
const methodLabels   = @json(collect($product->getActivePickupMethods())->map(fn($m) => $m['label']));
const methodColors   = {
    cod:      { border: 'border-green-400',  bg: 'bg-green-50'  },
    delivery: { border: 'border-blue-400',   bg: 'bg-blue-50'   },
    pickup:   { border: 'border-orange-400', bg: 'bg-orange-50' },
};

// ── Pilih metode pengambilan ───────────────────────────────────────────────
function pilihMetode(key, needAddress, color) {
    // Update hidden input
    document.getElementById('selectedPickupMethod').value = key;

    // Update visual cards
    document.querySelectorAll('.pickup-option').forEach(card => {
        const cardKey    = card.dataset.method;
        const cardColors = methodColors[cardKey] || { border: 'border-gray-300', bg: 'bg-white' };
        if (cardKey === key) {
            card.classList.remove('border-gray-200', 'bg-white');
            card.classList.add(cardColors.border, cardColors.bg);
        } else {
            card.classList.remove(cardColors.border, cardColors.bg);
            card.classList.add('border-gray-200', 'bg-white');
        }
    });

    // Tampilkan/sembunyikan field alamat tujuan
    const deliveryField   = document.getElementById('deliveryAddressField');
    const addressTextarea = document.getElementById('buyer_address');
    if (deliveryField) {
        deliveryField.classList.toggle('hidden', !needAddress);
        if (addressTextarea) addressTextarea.required = needAddress;
    }

    // Update info di sidebar
    const infoBox = document.getElementById('selectedMethodInfo');
    const infoLabel = document.getElementById('selectedMethodLabel');
    if (infoBox && infoLabel && methodLabels[key]) {
        infoLabel.textContent = methodLabels[key];
        infoBox.classList.remove('hidden');
    }
}

// ── Pilih alamat tersimpan ────────────────────────────────────────────────
function pilihAlamatTersimpan(id, name, phone, address) {
    document.getElementById('selectedAddressId').value = id;

    document.querySelectorAll('.saved-address-card').forEach(card => {
        const isThis = card.dataset.id == id;
        card.classList.toggle('border-blue-400', isThis);
        card.classList.toggle('bg-blue-50', isThis);
        card.classList.remove('border-gray-200', 'border-dashed', 'border-gray-300');
        if (!isThis) card.classList.add('border-gray-200');

        const radio = card.querySelector('[id^="radio-addr-"]');
        if (radio) {
            if (isThis) {
                radio.classList.add('border-blue-500', 'bg-blue-500');
                radio.innerHTML = '<div class="w-2 h-2 rounded-full bg-white"></div>';
            } else {
                radio.classList.remove('border-blue-500', 'bg-blue-500');
                radio.innerHTML = '';
            }
        }
    });

    // Isi textarea & sembunyikan field manual
    const textarea  = document.getElementById('buyer_address');
    const manualDiv = document.getElementById('manualAddressField');
    if (textarea)  textarea.value = address;
    if (manualDiv) manualDiv.classList.add('hidden');
}

// ── Pilih isi manual ──────────────────────────────────────────────────────
function pilihAlamatManual() {
    document.getElementById('selectedAddressId').value = 'manual';

    document.querySelectorAll('.saved-address-card').forEach(card => {
        const isManual = card.dataset.id === 'manual';
        card.classList.toggle('border-blue-400', isManual);
        card.classList.toggle('bg-blue-50', isManual);
        card.classList.remove('border-gray-200', 'border-dashed', 'border-gray-300');
        if (!isManual) card.classList.add('border-gray-200');

        const radio = card.querySelector('[id^="radio-addr-"]');
        if (radio) {
            if (isManual) {
                radio.classList.add('border-blue-500', 'bg-blue-500');
                radio.innerHTML = '<div class="w-2 h-2 rounded-full bg-white"></div>';
            } else {
                radio.classList.remove('border-blue-500', 'bg-blue-500');
                radio.innerHTML = '';
            }
        }
    });

    const manualDiv = document.getElementById('manualAddressField');
    const textarea  = document.getElementById('buyer_address');
    if (manualDiv) manualDiv.classList.remove('hidden');
    if (textarea)  { textarea.value = ''; textarea.focus(); }
}

// ── Update ringkasan harga ─────────────────────────────────────────────────
document.getElementById('quantity').addEventListener('input', function () {
    const qty   = parseInt(this.value) || 1;
    const total = productPrice * qty;
    document.getElementById('quantity_display').textContent = qty;
    document.getElementById('total_display').textContent    = 'Rp ' + total.toLocaleString('id-ID');
});

// ── Validasi sebelum submit ────────────────────────────────────────────────
document.getElementById('checkoutForm').addEventListener('submit', function (e) {
    const method = document.getElementById('selectedPickupMethod');
    if (method && !method.value) {
        e.preventDefault();
        alert('Pilih metode pengambilan terlebih dahulu.');
        document.getElementById('pickupMethodOptions')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
});

// ── Init: jika hanya ada 1 metode, otomatis terpilih ──────────────────────
document.addEventListener('DOMContentLoaded', function () {
    const options = document.querySelectorAll('.pickup-option');
    if (options.length === 1) {
        const key         = options[0].dataset.method;
        const needAddress = options[0].dataset.needAddress === 'true';
        pilihMetode(key, needAddress, options[0].dataset.color);
    } else {
        // Restore old selection jika ada
        const saved = document.getElementById('selectedPickupMethod')?.value;
        if (saved) {
            const savedOption = document.querySelector(`.pickup-option[data-method="${saved}"]`);
            if (savedOption) {
                pilihMetode(saved, savedOption.dataset.needAddress === 'true', savedOption.dataset.color);
            }
        }
    }

    // Auto-pilih alamat default jika ada saved addresses & belum ada pilihan
    const savedCards      = document.querySelectorAll('.saved-address-card[data-id]:not([data-id="manual"])');
    const currentSelected = document.getElementById('selectedAddressId')?.value;
    if (savedCards.length > 0 && !currentSelected) {
        const firstCard = savedCards[0];
        pilihAlamatTersimpan(
            firstCard.dataset.id,
            firstCard.dataset.name,
            firstCard.dataset.phone,
            firstCard.dataset.address
        );
    }
});
</script>
@endpush