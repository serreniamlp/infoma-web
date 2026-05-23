{{-- resources/views/user/addresses/_profile-card.blade.php --}}
{{-- Di-include di resources/views/user/profile/show.blade.php --}}

@php
    $defaultAddress = auth()->user()->addresses()->where('is_default', true)->first();
    $totalAddresses = auth()->user()->addresses()->count();
@endphp

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mt-6">
    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
        <h3 class="font-semibold text-gray-800 flex items-center gap-2">
            <i class="fas fa-map-marker-alt text-blue-500"></i>
            Alamat Saya
            @if($totalAddresses > 0)
                <span class="text-xs bg-blue-100 text-blue-700 font-semibold px-2 py-0.5 rounded-full">
                    {{ $totalAddresses }} alamat
                </span>
            @endif
        </h3>
        <a href="{{ route('user.addresses.index') }}"
           class="text-sm text-blue-600 hover:text-blue-700 font-medium transition-colors">
            Kelola Alamat <i class="fas fa-arrow-right ml-1 text-xs"></i>
        </a>
    </div>

    <div class="p-6">
        @if($defaultAddress)
            {{-- Tampilkan alamat default --}}
            <div class="flex items-start gap-4">
                <div class="w-10 h-10 bg-blue-50 rounded-full flex items-center justify-center shrink-0">
                    <i class="fas fa-home text-blue-500"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="font-semibold text-gray-900 text-sm">{{ $defaultAddress->label }}</span>
                        <span class="inline-flex items-center gap-1 bg-blue-600 text-white text-xs font-semibold px-2 py-0.5 rounded-full">
                            <i class="fas fa-check text-xs"></i>Default
                        </span>
                    </div>
                    <p class="text-sm font-medium text-gray-800">{{ $defaultAddress->recipient_name }}</p>
                    <p class="text-xs text-gray-500">{{ $defaultAddress->phone }}</p>
                    <p class="text-sm text-gray-600 mt-1 leading-relaxed">{{ $defaultAddress->address }}</p>
                </div>
            </div>

            @if($totalAddresses > 1)
                <p class="text-xs text-gray-400 mt-4 text-center">
                    +{{ $totalAddresses - 1 }} alamat lainnya ·
                    <a href="{{ route('user.addresses.index') }}" class="text-blue-500 hover:underline">Lihat semua</a>
                </p>
            @endif
        @else
            {{-- Belum ada alamat --}}
            <div class="text-center py-4">
                <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-map-marker-alt text-gray-400 text-lg"></i>
                </div>
                <p class="text-sm text-gray-500 mb-3">Belum ada alamat tersimpan</p>
                <a href="{{ route('user.addresses.index') }}"
                   class="inline-flex items-center gap-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold px-4 py-2 rounded-xl transition-colors">
                    <i class="fas fa-plus"></i>Tambah Alamat
                </a>
            </div>
        @endif
    </div>
</div>