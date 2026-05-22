@extends('layouts.app')

@section('title', 'Edit Profil - EduLiving')

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        #profile-map {
            height: 280px;
            border-radius: 10px;
            border: 1px solid #e5e7eb;
            z-index: 0;
        }
        .leaflet-control-geocoder {
            border-radius: 8px !important;
        }
    </style>
@endpush

@section('content')
<div class="min-h-screen bg-gray-50 py-10">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Edit Profil</h1>
                <p class="text-gray-500 mt-1">Perbarui informasi profil dan kontak kamu</p>
            </div>
            <a href="{{ route('user.profile.show') }}"
               class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-gray-700 transition">
                <i class="fas fa-arrow-left mr-2"></i>Kembali ke Profil
            </a>
        </div>

        {{-- Banner profil belum lengkap (dari Revisi 1) --}}
        @include('user.profile._incomplete-banner')

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <form action="{{ route('user.profile.update') }}" method="POST"
                  enctype="multipart/form-data" class="p-8" id="profile-form">
                @csrf
                @method('PUT')

                {{-- Field redirect_after_save dari banner incomplete --}}
                @if(session('redirect_after_profile'))
                    <input type="hidden" name="redirect_after_save" value="{{ session('redirect_after_profile') }}">
                @endif

                @if ($errors->any())
                    <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200">
                        <div class="flex">
                            <i class="fas fa-exclamation-circle text-red-500 mt-0.5 shrink-0"></i>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-red-800">Terdapat kesalahan:</h3>
                                <ul class="mt-2 text-sm text-red-700 list-disc list-inside">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="space-y-6">

                    {{-- ── Foto Profil ── --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Foto Profil <span class="text-gray-400 font-normal">(Opsional)</span>
                        </label>
                        <div class="flex items-center gap-4 mb-2">
                            @if($user->profile_picture)
                                <img class="h-16 w-16 rounded-full object-cover ring-2 ring-gray-100"
                                     src="{{ Storage::url($user->profile_picture) }}" alt="Foto Profil">
                            @else
                                <div class="h-16 w-16 rounded-full bg-gray-100 flex items-center justify-center">
                                    <i class="fas fa-user text-gray-300 text-2xl"></i>
                                </div>
                            @endif
                            <input type="file" name="profile_picture" id="profile_picture" accept="image/*"
                                   class="block w-full text-sm text-gray-500
                                          file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0
                                          file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700
                                          hover:file:bg-blue-100 cursor-pointer">
                        </div>
                        <p class="text-xs text-gray-400">Format: JPG, PNG. Maks. 2MB.</p>
                    </div>

                    {{-- ── Nama ── --}}
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">
                            Nama Lengkap <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="name" id="name"
                               value="{{ old('name', $user->name) }}" required
                               class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm
                                      focus:ring-blue-500 focus:border-blue-500 sm:text-sm
                                      @error('name') border-red-400 bg-red-50 @enderror">
                        @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>

                    {{-- ── Email ── --}}
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">
                            Alamat Email <span class="text-red-500">*</span>
                        </label>
                        <input type="email" name="email" id="email"
                               value="{{ old('email', $user->email) }}" required
                               class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm
                                      focus:ring-blue-500 focus:border-blue-500 sm:text-sm
                                      @error('email') border-red-400 bg-red-50 @enderror">
                        @error('email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>

                    {{-- ── Nomor Telepon ── --}}
                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700">
                            Nomor Telepon
                            @if(empty($user->phone))
                                <span class="ml-1 text-xs text-amber-600 font-normal">
                                    <i class="fas fa-exclamation-circle mr-0.5"></i>Belum diisi
                                </span>
                            @endif
                        </label>
                        <input type="text" name="phone" id="phone"
                               value="{{ old('phone', $user->phone) }}"
                               placeholder="Contoh: 081234567890"
                               class="mt-1 block w-full rounded-lg shadow-sm
                                      focus:ring-blue-500 focus:border-blue-500 sm:text-sm
                                      @error('phone') border-red-400 bg-red-50 @else border-gray-300 @enderror">
                        @error('phone')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>

                    {{-- ═══════════════════════════════════════════════════ --}}
                    {{-- ALAMAT + MAP (BARU)                                 --}}
                    {{-- ═══════════════════════════════════════════════════ --}}
                    <div>
                        <label for="address" class="block text-sm font-medium text-gray-700 mb-1.5">
                            Alamat
                            @if(empty($user->address))
                                <span class="ml-1 text-xs text-amber-600 font-normal">
                                    <i class="fas fa-exclamation-circle mr-0.5"></i>Belum diisi
                                </span>
                            @endif
                        </label>

                        {{-- Input teks alamat --}}
                        <textarea name="address" id="address" rows="3"
                                  placeholder="Isi alamat atau klik peta di bawah untuk mengisi otomatis"
                                  class="block w-full rounded-lg shadow-sm sm:text-sm mb-3
                                         focus:ring-blue-500 focus:border-blue-500
                                         @error('address') border-red-400 bg-red-50 @else border-gray-300 @enderror">{{ old('address', $user->address) }}</textarea>
                        @error('address')<p class="mt-1 text-xs text-red-600 mb-2">{{ $message }}</p>@enderror

                        {{-- Tombol bantuan --}}
                        <div class="flex flex-wrap gap-2 mb-3">
                            <button type="button" onclick="gunakanLokasiSekarang()"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-50 hover:bg-blue-100
                                           text-blue-700 text-xs font-medium rounded-lg border border-blue-200 transition-colors">
                                <i class="fas fa-location-arrow text-xs"></i>Gunakan Lokasi Saya
                            </button>
                            <button type="button" onclick="bersihkanMap()"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-gray-50 hover:bg-gray-100
                                           text-gray-600 text-xs font-medium rounded-lg border border-gray-200 transition-colors">
                                <i class="fas fa-times text-xs"></i>Hapus Pin
                            </button>
                        </div>

                        {{-- Peta --}}
                        <div id="profile-map"></div>
                        <p class="text-xs text-gray-400 mt-2">
                            <i class="fas fa-info-circle mr-1"></i>
                            Klik lokasi di peta atau gunakan tombol "Lokasi Saya" untuk mengisi alamat otomatis.
                            Pin di peta hanya membantu mengisi teks alamat — koordinat tidak disimpan.
                        </p>
                    </div>

                </div>

                <div class="mt-8 pt-5 border-t border-gray-100 flex justify-end gap-3">
                    <a href="{{ route('user.profile.show') }}"
                       class="py-2 px-4 border border-gray-300 rounded-lg shadow-sm text-sm font-medium
                              text-gray-700 hover:bg-gray-50 transition">
                        Batal
                    </a>
                    <button type="submit"
                            class="inline-flex justify-center py-2 px-5 border border-transparent shadow-sm
                                   text-sm font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700
                                   focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition">
                        <i class="fas fa-save mr-2"></i>Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet-control-geocoder@1.13.0/dist/Control.Geocoder.js"></script>
<script>
// ── Inisialisasi peta ─────────────────────────────────────────────────────
// Default center: Bandung (bisa disesuaikan)
const DEFAULT_LAT = -6.9175;
const DEFAULT_LNG = 107.6191;
const DEFAULT_ZOOM = 13;

let peta   = null;
let marker = null;

document.addEventListener('DOMContentLoaded', function () {
    peta = L.map('profile-map').setView([DEFAULT_LAT, DEFAULT_LNG], DEFAULT_ZOOM);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
        maxZoom: 19,
    }).addTo(peta);

    // Geocoder search box
    const geocoder = L.Control.geocoder({
        defaultMarkGeocode: false,
        placeholder: 'Cari alamat...',
        geocoder: L.Control.Geocoder.nominatim(),
    })
    .on('markgeocode', function (e) {
        const latlng = e.geocode.center;
        pindahkanMarker(latlng.lat, latlng.lng);
        peta.setView(latlng, 16);
        isiAlamatDariKoordinat(latlng.lat, latlng.lng);
    })
    .addTo(peta);

    // Klik di peta → pasang/pindah marker + isi alamat
    peta.on('click', function (e) {
        pindahkanMarker(e.latlng.lat, e.latlng.lng);
        isiAlamatDariKoordinat(e.latlng.lat, e.latlng.lng);
    });

    // Jika user sudah punya alamat teks, coba geocode untuk tampilkan pin
    const alamatAwal = document.getElementById('address').value.trim();
    if (alamatAwal) {
        geocodeTampilPin(alamatAwal);
    }
});

// ── Pindahkan / buat marker ───────────────────────────────────────────────
function pindahkanMarker(lat, lng) {
    if (marker) {
        marker.setLatLng([lat, lng]);
    } else {
        marker = L.marker([lat, lng], { draggable: true }).addTo(peta);

        // Drag marker → update alamat
        marker.on('dragend', function () {
            const pos = marker.getLatLng();
            isiAlamatDariKoordinat(pos.lat, pos.lng);
        });
    }
}

// ── Reverse geocode: koordinat → teks alamat ─────────────────────────────
function isiAlamatDariKoordinat(lat, lng) {
    fetch(`https://nominatim.openstreetmap.org/reverse?lat=${lat}&lon=${lng}&format=json&accept-language=id`)
        .then(r => r.json())
        .then(data => {
            if (data && data.display_name) {
                document.getElementById('address').value = data.display_name;
            }
        })
        .catch(() => {
            // Kalau reverse geocode gagal, biarkan user isi manual
        });
}

// ── Forward geocode: teks alamat → pin di peta ───────────────────────────
function geocodeTampilPin(alamat) {
    fetch(`https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(alamat)}&format=json&limit=1&accept-language=id`)
        .then(r => r.json())
        .then(data => {
            if (data && data.length > 0) {
                const lat = parseFloat(data[0].lat);
                const lng = parseFloat(data[0].lon);
                pindahkanMarker(lat, lng);
                peta.setView([lat, lng], 15);
            }
        })
        .catch(() => {});
}

// ── Tombol "Gunakan Lokasi Saya" ─────────────────────────────────────────
function gunakanLokasiSekarang() {
    if (!navigator.geolocation) {
        alert('Browser kamu tidak mendukung geolokasi.');
        return;
    }

    navigator.geolocation.getCurrentPosition(
        function (pos) {
            const lat = pos.coords.latitude;
            const lng = pos.coords.longitude;
            pindahkanMarker(lat, lng);
            peta.setView([lat, lng], 16);
            isiAlamatDariKoordinat(lat, lng);
        },
        function (err) {
            if (err.code === err.PERMISSION_DENIED) {
                alert('Izin lokasi ditolak. Aktifkan izin lokasi di browser kamu.');
            } else {
                alert('Tidak bisa mendapatkan lokasi: ' + err.message);
            }
        },
        { enableHighAccuracy: true, timeout: 10000 }
    );
}

// ── Tombol "Hapus Pin" ───────────────────────────────────────────────────
function bersihkanMap() {
    if (marker) {
        peta.removeLayer(marker);
        marker = null;
    }
    document.getElementById('address').value = '';
}
</script>
@endpush