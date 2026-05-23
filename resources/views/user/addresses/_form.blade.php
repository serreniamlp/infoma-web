{{-- resources/views/user/addresses/_form.blade.php --}}
{{-- Variable: $address (null untuk tambah, UserAddress untuk edit) --}}

@php
    $labelOptions = ['Rumah', 'Kos', 'Kantor', 'Apartemen', 'Lainnya'];
@endphp

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">

    {{-- Label --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">
            Label <span class="text-red-500">*</span>
        </label>
        <div class="flex flex-wrap gap-2 mb-2">
            @foreach($labelOptions as $opt)
            <label class="cursor-pointer">
                <input type="radio" name="label" value="{{ $opt }}" class="sr-only peer"
                       {{ old('label', $address?->label ?? 'Rumah') === $opt ? 'checked' : '' }}>
                <span class="inline-block px-3 py-1.5 text-xs font-medium rounded-full border transition-all cursor-pointer
                             peer-checked:bg-blue-600 peer-checked:text-white peer-checked:border-blue-600
                             border-gray-300 text-gray-600 hover:border-blue-400">
                    {{ $opt }}
                </span>
            </label>
            @endforeach
        </div>
        @error('label')<p class="text-xs text-red-600">{{ $message }}</p>@enderror
    </div>

    {{-- Is Default --}}
    <div class="flex items-end pb-1">
        <label class="flex items-center gap-2.5 cursor-pointer">
            <input type="checkbox" name="is_default" value="1"
                   {{ old('is_default', $address?->is_default) ? 'checked' : '' }}
                   class="h-4 w-4 text-blue-600 rounded border-gray-300">
            <span class="text-sm text-gray-700 font-medium">Jadikan alamat default</span>
        </label>
    </div>

    {{-- Nama Penerima --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">
            Nama Penerima <span class="text-red-500">*</span>
        </label>
        <input type="text" name="recipient_name"
               value="{{ old('recipient_name', $address?->recipient_name ?? auth()->user()->name) }}"
               placeholder="Nama lengkap penerima"
               class="w-full px-4 py-2.5 border rounded-xl text-sm focus:ring-2 focus:ring-blue-500
                      @error('recipient_name') border-red-400 bg-red-50 @else border-gray-300 @enderror">
        @error('recipient_name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>

    {{-- Nomor Telepon --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">
            Nomor Telepon <span class="text-red-500">*</span>
        </label>
        <input type="text" name="phone"
               value="{{ old('phone', $address?->phone ?? auth()->user()->phone) }}"
               placeholder="08xxxxxxxxxx"
               class="w-full px-4 py-2.5 border rounded-xl text-sm focus:ring-2 focus:ring-blue-500
                      @error('phone') border-red-400 bg-red-50 @else border-gray-300 @enderror">
        @error('phone')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>

    {{-- Alamat Lengkap --}}
    <div class="md:col-span-2">
        <label class="block text-sm font-medium text-gray-700 mb-1.5">
            Alamat Lengkap <span class="text-red-500">*</span>
        </label>
        <textarea name="address" rows="3"
                  placeholder="Nama jalan, nomor rumah, RT/RW, kelurahan, kecamatan, kota, kode pos"
                  class="w-full px-4 py-2.5 border rounded-xl text-sm focus:ring-2 focus:ring-blue-500
                         @error('address') border-red-400 bg-red-50 @else border-gray-300 @enderror">{{ old('address', $address?->address) }}</textarea>
        @error('address')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>

</div>