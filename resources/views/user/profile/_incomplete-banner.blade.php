{{-- resources/views/user/profile/_incomplete-banner.blade.php --}}
{{-- Include di bagian atas edit.blade.php, setelah header --}}

@if(session('profile_incomplete'))
<div class="mb-6 bg-amber-50 border border-amber-300 rounded-xl p-5 flex items-start gap-4">
    <div class="w-10 h-10 bg-amber-100 rounded-full flex items-center justify-center shrink-0">
        <i class="fas fa-exclamation-triangle text-amber-500"></i>
    </div>
    <div class="flex-1">
        <h3 class="font-semibold text-amber-800 text-sm">Profil Belum Lengkap</h3>
        <p class="text-amber-700 text-sm mt-1">{{ session('profile_incomplete') }}</p>

        @if(session('redirect_after_profile'))
            <p class="text-amber-600 text-xs mt-2">
                <i class="fas fa-info-circle mr-1"></i>
                Setelah melengkapi profil, kamu akan diarahkan kembali ke halaman sebelumnya.
            </p>
            {{-- Simpan redirect_after_profile ke hidden input agar bisa digunakan setelah save --}}
            <input type="hidden" name="redirect_after_save" value="{{ session('redirect_after_profile') }}"
                   form="profile-form">
        @endif
    </div>
</div>
@endif

{{-- Banner info field yang masih kosong --}}
@php
    $user = auth()->user();
    $emptyFields = [];
    if (empty($user->phone))   $emptyFields[] = ['icon' => 'fa-phone',     'label' => 'Nomor Telepon'];
    if (empty($user->address)) $emptyFields[] = ['icon' => 'fa-map-marker-alt', 'label' => 'Alamat'];
@endphp

@if(count($emptyFields) > 0)
<div class="mb-6 bg-blue-50 border border-blue-200 rounded-xl p-4">
    <p class="text-sm font-medium text-blue-800 mb-2">
        <i class="fas fa-clipboard-list mr-1.5"></i>
        Field yang perlu dilengkapi:
    </p>
    <div class="flex flex-wrap gap-2">
        @foreach($emptyFields as $field)
            <span class="inline-flex items-center gap-1.5 bg-white border border-blue-200 text-blue-700 text-xs font-medium px-3 py-1.5 rounded-full">
                <i class="fas {{ $field['icon'] }} text-blue-400"></i>
                {{ $field['label'] }}
            </span>
        @endforeach
    </div>
</div>
@endif