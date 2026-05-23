@extends('layouts.app')

@section('title', 'Alamat Saya - EduLiving')

@section('content')
<div class="min-h-screen bg-gray-50 py-10">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Header --}}
        <div class="flex items-center justify-between mb-8">
            <div>
                <nav class="flex items-center gap-2 text-sm text-gray-500 mb-2">
                    <a href="{{ route('user.profile.show') }}" class="hover:text-blue-600">Profil Saya</a>
                    <i class="fas fa-chevron-right text-xs"></i>
                    <span class="text-gray-900">Alamat Saya</span>
                </nav>
                <h1 class="text-2xl font-bold text-gray-900">Alamat Saya</h1>
                <p class="text-gray-500 text-sm mt-1">Kelola alamat pengiriman untuk transaksi marketplace</p>
            </div>
            <button onclick="bukaFormTambah()"
                    class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-xl text-sm font-semibold transition-colors shadow-sm">
                <i class="fas fa-plus"></i>Tambah Alamat
            </button>
        </div>

        {{-- Flash messages --}}
        @if(session('success'))
        <div class="mb-5 bg-green-50 border border-green-200 rounded-xl p-4 flex items-center gap-3">
            <i class="fas fa-check-circle text-green-500"></i>
            <p class="text-green-700 text-sm font-medium">{{ session('success') }}</p>
        </div>
        @endif

        @if($errors->any())
        <div class="mb-5 bg-red-50 border border-red-200 rounded-xl p-4">
            <p class="text-sm font-semibold text-red-700 mb-1 flex items-center gap-2">
                <i class="fas fa-exclamation-circle"></i>Ada kesalahan:
            </p>
            <ul class="text-sm text-red-600 space-y-0.5 list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        {{-- ── FORM TAMBAH ALAMAT ─────────────────────────────────────── --}}
        <div id="formTambah" class="{{ $errors->any() && !session('editId') ? '' : 'hidden' }}
             bg-white rounded-2xl shadow-sm border border-blue-100 overflow-hidden mb-6">
            <div class="px-6 py-4 bg-blue-50 border-b border-blue-100">
                <h2 class="font-semibold text-blue-800 flex items-center gap-2">
                    <i class="fas fa-plus-circle text-blue-500"></i>Tambah Alamat Baru
                </h2>
            </div>
            <form method="POST" action="{{ route('user.addresses.store') }}" class="p-6 space-y-4">
                @csrf
                @include('user.addresses._form', ['address' => null])
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="tutupFormTambah()"
                            class="px-5 py-2 border border-gray-300 rounded-xl text-gray-700 text-sm font-medium hover:bg-gray-50 transition-colors">
                        Batal
                    </button>
                    <button type="submit"
                            class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-semibold transition-colors">
                        <i class="fas fa-save mr-1.5"></i>Simpan Alamat
                    </button>
                </div>
            </form>
        </div>

        {{-- ── DAFTAR ALAMAT ──────────────────────────────────────────── --}}
        @if($addresses->isEmpty())
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-12 text-center">
            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-map-marker-alt text-gray-400 text-2xl"></i>
            </div>
            <h3 class="font-semibold text-gray-900 mb-1">Belum ada alamat tersimpan</h3>
            <p class="text-gray-500 text-sm mb-5">Tambahkan alamat pengiriman untuk mempermudah proses checkout.</p>
            <button onclick="bukaFormTambah()"
                    class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl text-sm font-semibold transition-colors">
                <i class="fas fa-plus"></i>Tambah Alamat Pertama
            </button>
        </div>
        @else
        <div class="space-y-4">
            @foreach($addresses as $addr)
            <div class="bg-white rounded-2xl shadow-sm border {{ $addr->is_default ? 'border-blue-300' : 'border-gray-100' }} overflow-hidden"
                 id="card-{{ $addr->id }}">

                {{-- Header card --}}
                <div class="px-5 py-3 {{ $addr->is_default ? 'bg-blue-50' : 'bg-gray-50' }} border-b {{ $addr->is_default ? 'border-blue-100' : 'border-gray-100' }} flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="font-semibold text-gray-900 text-sm">{{ $addr->label }}</span>
                        @if($addr->is_default)
                            <span class="inline-flex items-center gap-1 bg-blue-600 text-white text-xs font-semibold px-2 py-0.5 rounded-full">
                                <i class="fas fa-check text-xs"></i>Default
                            </span>
                        @endif
                    </div>
                    <div class="flex items-center gap-2">
                        @if(!$addr->is_default)
                        <form method="POST" action="{{ route('user.addresses.setDefault', $addr) }}">
                            @csrf @method('PATCH')
                            <button type="submit"
                                    class="text-xs text-blue-600 hover:text-blue-800 font-medium transition-colors">
                                Jadikan Default
                            </button>
                        </form>
                        @endif
                        <button onclick="bukaFormEdit({{ $addr->id }})"
                                class="text-xs text-gray-500 hover:text-gray-700 font-medium transition-colors">
                            <i class="fas fa-edit mr-0.5"></i>Edit
                        </button>
                        @if(!$addr->is_default || $addresses->count() === 1)
                        <form method="POST" action="{{ route('user.addresses.destroy', $addr) }}"
                              onsubmit="return confirm('Hapus alamat ini?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-xs text-red-500 hover:text-red-700 font-medium transition-colors">
                                <i class="fas fa-trash mr-0.5"></i>Hapus
                            </button>
                        </form>
                        @endif
                    </div>
                </div>

                {{-- Isi card --}}
                <div class="px-5 py-4" id="detail-{{ $addr->id }}">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                        <div>
                            <span class="text-gray-500 text-xs">Penerima</span>
                            <p class="font-semibold text-gray-900">{{ $addr->recipient_name }}</p>
                        </div>
                        <div>
                            <span class="text-gray-500 text-xs">Telepon</span>
                            <p class="font-semibold text-gray-900">{{ $addr->phone }}</p>
                        </div>
                        <div class="md:col-span-2">
                            <span class="text-gray-500 text-xs">Alamat Lengkap</span>
                            <p class="text-gray-800 leading-relaxed">{{ $addr->address }}</p>
                        </div>
                    </div>
                </div>

                {{-- Form edit (tersembunyi, muncul saat klik Edit) --}}
                <div id="formEdit-{{ $addr->id }}" class="hidden border-t border-gray-100 px-5 py-5 bg-gray-50">
                    <form method="POST" action="{{ route('user.addresses.update', $addr) }}">
                        @csrf @method('PUT')
                        @include('user.addresses._form', ['address' => $addr])
                        <div class="flex justify-end gap-3 mt-4">
                            <button type="button" onclick="tutupFormEdit({{ $addr->id }})"
                                    class="px-5 py-2 border border-gray-300 rounded-xl text-gray-700 text-sm font-medium hover:bg-white transition-colors">
                                Batal
                            </button>
                            <button type="submit"
                                    class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-semibold transition-colors">
                                <i class="fas fa-save mr-1.5"></i>Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
        @endif

        {{-- Link kembali --}}
        <div class="mt-8 text-center">
            <a href="{{ route('user.profile.show') }}"
               class="text-sm text-gray-500 hover:text-gray-700 transition-colors">
                <i class="fas fa-arrow-left mr-1.5"></i>Kembali ke Profil
            </a>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
function bukaFormTambah() {
    document.getElementById('formTambah').classList.remove('hidden');
    document.getElementById('formTambah').scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function tutupFormTambah() {
    document.getElementById('formTambah').classList.add('hidden');
}

function bukaFormEdit(id) {
    // Tutup semua form edit yang terbuka
    document.querySelectorAll('[id^="formEdit-"]').forEach(el => el.classList.add('hidden'));

    const formEdit = document.getElementById('formEdit-' + id);
    if (formEdit) {
        formEdit.classList.remove('hidden');
        formEdit.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
}

function tutupFormEdit(id) {
    const formEdit = document.getElementById('formEdit-' + id);
    if (formEdit) formEdit.classList.add('hidden');
}

// Jika ada error validasi, buka form yang relevan
@if($errors->any() && session('editId'))
bukaFormEdit({{ session('editId') }});
@elseif($errors->any())
bukaFormTambah();
@endif
</script>
@endpush