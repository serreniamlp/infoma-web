{{-- resources/views/marketplace/_pickup-methods-section.blade.php --}}
{{-- Di-include di step 4 create.blade.php dan edit.blade.php --}}
{{-- Variable yang dibutuhkan: $product (nullable untuk create) --}}

@php
    $methods        = \App\Models\MarketplaceProduct::availablePickupMethods();
    $selectedMethods = old('pickup_methods', isset($product) ? ($product->pickup_methods ?? []) : []);
    $savedPickupAddress = old('pickup_address', isset($product) ? $product->pickup_address : '');
@endphp

<div class="space-y-5">

    {{-- Penjelasan --}}
    <div class="bg-orange-50 border border-orange-100 rounded-xl p-4 flex items-start gap-3">
        <i class="fas fa-info-circle text-orange-400 mt-0.5 shrink-0"></i>
        <div class="text-sm text-orange-800">
            <p class="font-semibold mb-0.5">Tentukan cara pembeli mendapatkan barang</p>
            <p class="text-orange-700 text-xs">Kamu bisa aktifkan lebih dari satu metode. Buyer hanya bisa memilih dari metode yang kamu aktifkan.</p>
        </div>
    </div>

    @error('pickup_methods')
        <p class="text-sm text-red-600 flex items-center gap-1.5">
            <i class="fas fa-exclamation-circle"></i>{{ $message }}
        </p>
    @enderror

    {{-- Kartu pilihan metode --}}
    <div class="space-y-3" id="pickupMethodCards">
        @foreach($methods as $key => $method)
        @php
            $isChecked = in_array($key, $selectedMethods);
            $colorClass = match($method['color']) {
                'green'  => ['border' => 'border-green-300',  'bg' => 'bg-green-50',  'text' => 'text-green-700',  'icon' => 'text-green-500',  'badge' => 'bg-green-100 text-green-700'],
                'blue'   => ['border' => 'border-blue-300',   'bg' => 'bg-blue-50',   'text' => 'text-blue-700',   'icon' => 'text-blue-500',   'badge' => 'bg-blue-100 text-blue-700'],
                'orange' => ['border' => 'border-orange-300', 'bg' => 'bg-orange-50', 'text' => 'text-orange-700', 'icon' => 'text-orange-500', 'badge' => 'bg-orange-100 text-orange-700'],
                default  => ['border' => 'border-gray-300',   'bg' => 'bg-gray-50',   'text' => 'text-gray-700',   'icon' => 'text-gray-500',   'badge' => 'bg-gray-100 text-gray-700'],
            };
        @endphp
        <label class="pickup-method-card flex items-start gap-4 p-4 border-2 rounded-xl cursor-pointer transition-all
                       {{ $isChecked ? $colorClass['border'] . ' ' . $colorClass['bg'] : 'border-gray-200 hover:border-gray-300' }}"
               id="card-{{ $key }}"
               for="method-{{ $key }}">
            <input type="checkbox"
                   name="pickup_methods[]"
                   id="method-{{ $key }}"
                   value="{{ $key }}"
                   {{ $isChecked ? 'checked' : '' }}
                   onchange="toggleMethodCard('{{ $key }}', '{{ $method['color'] }}')"
                   class="mt-0.5 h-4 w-4 rounded border-gray-300 text-orange-500 shrink-0">
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 mb-1">
                    <i class="fas {{ $method['icon'] }} {{ $colorClass['icon'] }}"></i>
                    <span class="font-semibold text-gray-900 text-sm">{{ $method['label'] }}</span>
                    @if($method['need_address'])
                        <span class="text-xs px-2 py-0.5 rounded-full {{ $colorClass['badge'] }}">Butuh Alamat</span>
                    @else
                        <span class="text-xs px-2 py-0.5 rounded-full bg-gray-100 text-gray-600">Tidak Butuh Alamat</span>
                    @endif
                </div>
                <p class="text-xs text-gray-500">{{ $method['description'] }}</p>
            </div>
        </label>
        @endforeach
    </div>

    {{-- Field alamat pickup — muncul jika "Ambil Sendiri" dicentang --}}
    <div id="pickupAddressField"
         class="{{ in_array('pickup', $selectedMethods) ? '' : 'hidden' }} bg-white border border-orange-200 rounded-xl p-5">
        <label class="block text-sm font-semibold text-gray-700 mb-1.5">
            <i class="fas fa-map-marker-alt text-orange-400 mr-1.5"></i>
            Alamat Pengambilan Barang <span class="text-red-500">*</span>
        </label>
        <p class="text-xs text-gray-500 mb-3">
            Isi dengan alamat lengkap tempat buyer bisa datang untuk mengambil barang.
        </p>
        <textarea name="pickup_address"
                  id="pickup_address"
                  rows="3"
                  placeholder="Contoh: Jl. Ganesha No. 10, Tamansari, Bandung, Jawa Barat 40132"
                  class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm
                         focus:outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100
                         @error('pickup_address') border-red-400 bg-red-50 @enderror">{{ $savedPickupAddress }}</textarea>
        @error('pickup_address')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- Info jika tidak ada metode dipilih --}}
    <div id="noMethodWarning"
         class="{{ count($selectedMethods) > 0 ? 'hidden' : '' }} bg-yellow-50 border border-yellow-200 rounded-xl p-4 flex items-center gap-3">
        <i class="fas fa-exclamation-triangle text-yellow-500"></i>
        <p class="text-sm text-yellow-800">Pilih minimal satu metode pengambilan agar pembeli bisa memesan produkmu.</p>
    </div>

</div>

@push('scripts')
<script>
// Warna class per metode
const methodColors = {
    cod:      { border: 'border-green-300',  bg: 'bg-green-50'  },
    delivery: { border: 'border-blue-300',   bg: 'bg-blue-50'   },
    pickup:   { border: 'border-orange-300', bg: 'bg-orange-50' },
};

function toggleMethodCard(key, color) {
    const checkbox = document.getElementById('method-' + key);
    const card     = document.getElementById('card-' + key);
    const colors   = methodColors[key] || { border: 'border-gray-300', bg: 'bg-gray-50' };

    if (checkbox.checked) {
        card.classList.remove('border-gray-200');
        card.classList.add(colors.border, colors.bg);
    } else {
        card.classList.remove(colors.border, colors.bg);
        card.classList.add('border-gray-200');
    }

    // Tampilkan/sembunyikan field alamat pickup
    const pickupCheckbox = document.getElementById('method-pickup');
    const pickupField    = document.getElementById('pickupAddressField');
    if (pickupCheckbox && pickupField) {
        pickupField.classList.toggle('hidden', !pickupCheckbox.checked);
        document.getElementById('pickup_address').required = pickupCheckbox.checked;
    }

    // Cek apakah ada yang dipilih
    const anyChecked = ['cod', 'delivery', 'pickup'].some(m => {
        const el = document.getElementById('method-' + m);
        return el && el.checked;
    });
    const warning = document.getElementById('noMethodWarning');
    if (warning) warning.classList.toggle('hidden', anyChecked);
}

// Init saat load
document.addEventListener('DOMContentLoaded', function () {
    const pickupCheckbox = document.getElementById('method-pickup');
    const pickupField    = document.getElementById('pickupAddressField');
    const pickupInput    = document.getElementById('pickup_address');

    if (pickupCheckbox && pickupField && pickupInput) {
        pickupInput.required = pickupCheckbox.checked;
    }
});
</script>
@endpush