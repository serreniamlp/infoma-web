{{-- resources/views/auth/register.blade.php --}}

@extends('layouts.app')
@section('title', 'Daftar — EduLiving')

@section('content')
<div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 bg-gray-50">
    <div class="max-w-lg w-full space-y-8">

        {{-- Header --}}
        <div class="text-center">
            <img src="{{ asset('images/Infoma_Branding-blue.png') }}" alt="EduLiving" class="w-16 h-16 mx-auto mb-4">
            <h2 class="text-3xl font-bold text-gray-900 mb-2">Bergabung dengan EduLiving</h2>
            <p class="text-gray-600">Buat akun baru untuk mengakses layanan kami</p>
        </div>

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-xl flex items-center gap-3">
                <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
            </div>
        @endif

        <div class="bg-white rounded-2xl shadow-2xl p-8 border border-gray-100">
            <form method="POST" action="{{ route('register') }}"
                  enctype="multipart/form-data"
                  id="register-form">
                @csrf

                {{-- ---- Role ---- --}}
                <div class="mb-5">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-user-tag mr-2 text-blue-500"></i>Daftar sebagai
                    </label>
                    <div class="relative">
                        <select id="role" name="role" required
                                onchange="handleRoleChange(this.value)"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50 focus:bg-white appearance-none @error('role') border-red-500 @enderror">
                            <option value="" disabled {{ old('role') ? '' : 'selected' }}>Pilih peran Anda</option>
                            <option value="user"               {{ old('role') === 'user'               ? 'selected' : '' }}>Mahasiswa</option>
                            <option value="provider_residence" {{ old('role') === 'provider_residence' ? 'selected' : '' }}>Provider Hunian (Pemilik Kost/Kontrakan)</option>
                            <option value="provider_event"     {{ old('role') === 'provider_event'     ? 'selected' : '' }}>Provider Event (Penyelenggara Kegiatan)</option>
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none">
                            <i class="fas fa-chevron-down text-gray-400"></i>
                        </div>
                    </div>
                    @error('role')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- ---- Nama ---- --}}
                <div class="mb-5">
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-user mr-2 text-blue-500"></i>Nama Lengkap
                    </label>
                    <input id="name" name="name" type="text" required autofocus
                           value="{{ old('name') }}"
                           placeholder="Masukkan nama lengkap"
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50 focus:bg-white @error('name') border-red-500 @enderror">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- ---- Email ---- --}}
                <div class="mb-5">
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-envelope mr-2 text-blue-500"></i>Email
                    </label>
                    <input id="email" name="email" type="email" required
                           value="{{ old('email') }}"
                           placeholder="nama@email.com"
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50 focus:bg-white @error('email') border-red-500 @enderror">
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- ---- Password ---- --}}
                <div class="mb-5">
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-lock mr-2 text-blue-500"></i>Password
                    </label>
                    <div class="relative">
                        <input id="password" name="password" type="password" required
                               placeholder="Minimal 8 karakter"
                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50 focus:bg-white pr-12 @error('password') border-red-500 @enderror">
                        <button type="button" onclick="togglePassword('password','icon1')"
                                class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                            <i id="icon1" class="fas fa-eye"></i>
                        </button>
                    </div>
                    @error('password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- ---- Konfirmasi Password ---- --}}
                <div class="mb-6">
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-lock mr-2 text-blue-500"></i>Konfirmasi Password
                    </label>
                    <div class="relative">
                        <input id="password_confirmation" name="password_confirmation" type="password" required
                               placeholder="Ulangi password"
                               oninput="checkPasswordMatch()"
                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50 focus:bg-white pr-12">
                        <button type="button" onclick="togglePassword('password_confirmation','icon2')"
                                class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                            <i id="icon2" class="fas fa-eye"></i>
                        </button>
                    </div>
                    <p id="password-match-msg" class="mt-1 text-sm hidden"></p>
                </div>

                {{-- ================================================================ --}}
                {{-- SECTION VERIFIKASI PROVIDER — muncul saat pilih role provider  --}}
                {{-- ================================================================ --}}
                <div id="provider-verification" class="hidden mb-6">

                    <div class="border-t border-gray-100 mb-6"></div>

                    {{-- Info banner --}}
                    <div class="p-4 bg-blue-50 border border-blue-200 rounded-xl mb-5">
                        <div class="flex items-start gap-2">
                            <i class="fas fa-shield-alt text-blue-500 mt-0.5"></i>
                            <div>
                                <p class="text-sm font-semibold text-blue-800 mb-0.5">Verifikasi Identitas Provider</p>
                                <p class="text-xs text-blue-700">Isi data di bawah untuk proses verifikasi. Admin akan meninjau dalam 1×24 jam setelah kamu mendaftar.</p>
                            </div>
                        </div>
                    </div>

                    {{-- Errors khusus provider --}}
                    @if($errors->hasAny(['provider_nik','provider_ktp','provider_selfie']))
                        <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-xl">
                            <ul class="text-sm text-red-700 space-y-1">
                                @error('provider_nik')<li class="flex items-center gap-2"><i class="fas fa-exclamation-circle"></i>{{ $message }}</li>@enderror
                                @error('provider_ktp')<li class="flex items-center gap-2"><i class="fas fa-exclamation-circle"></i>{{ $message }}</li>@enderror
                                @error('provider_selfie')<li class="flex items-center gap-2"><i class="fas fa-exclamation-circle"></i>{{ $message }}</li>@enderror
                            </ul>
                        </div>
                    @endif

                    {{-- NIK --}}
                    <div class="mb-5">
                        <div class="flex items-center gap-2 mb-3">
                            <div class="w-6 h-6 rounded-full bg-blue-600 text-white text-xs font-bold flex items-center justify-center">1</div>
                            <h3 class="font-semibold text-gray-900 text-sm">NIK</h3>
                        </div>
                        <input type="text"
                               name="provider_nik"
                               id="provider_nik"
                               value="{{ old('provider_nik') }}"
                               maxlength="16"
                               inputmode="numeric"
                               placeholder="16 digit NIK sesuai KTP"
                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50 focus:bg-white text-sm @error('provider_nik') border-red-500 @enderror"
                               oninput="this.value = this.value.replace(/[^0-9]/g,'').slice(0,16); document.getElementById('nik-count').textContent = this.value.length">
                        <div class="flex justify-between mt-1">
                            <span class="text-xs text-red-500">@error('provider_nik'){{ $message }}@enderror</span>
                            <span class="text-xs text-gray-400"><span id="nik-count">0</span>/16 digit</span>
                        </div>
                    </div>

                    {{-- Nama (prefill readonly) --}}
                    <div class="mb-5">
                        <label class="block text-xs text-gray-500 mb-1">Nama Lengkap <span class="text-gray-400">(sesuai KTP)</span></label>
                        <div class="w-full px-4 py-3 bg-gray-100 border border-gray-200 rounded-xl text-sm text-gray-600 flex items-center gap-2">
                            <i class="fas fa-user text-gray-400"></i>
                            <span id="nama-preview">—</span>
                        </div>
                        <p class="text-xs text-gray-400 mt-1">Diambil otomatis dari nama lengkap yang kamu isi</p>
                    </div>

                    {{-- Foto KTP --}}
                    <div class="mb-5">
                        <div class="flex items-center gap-2 mb-3">
                            <div class="w-6 h-6 rounded-full bg-blue-600 text-white text-xs font-bold flex items-center justify-center">2</div>
                            <h3 class="font-semibold text-gray-900 text-sm">Foto KTP</h3>
                        </div>
                        <div class="relative border-2 border-dashed border-gray-300 rounded-xl p-5 text-center hover:border-blue-400 transition-colors cursor-pointer"
                             id="ktp-dropzone">
                            <input type="file" name="provider_ktp" id="provider_ktp" accept="image/*"
                                   class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                                   onchange="previewKtp(this)">
                            <div id="ktp-placeholder">
                                <i class="fas fa-id-card text-gray-300 text-3xl mb-2 block"></i>
                                <p class="text-sm font-medium text-gray-700">Klik atau drag foto KTP</p>
                                <p class="text-xs text-gray-400 mt-1">JPG, PNG — Maks. 2MB</p>
                            </div>
                            <div id="ktp-preview" class="hidden">
                                <img id="ktp-preview-img" src="" alt="KTP"
                                     class="max-h-36 mx-auto rounded-lg object-cover border border-gray-200">
                                <p class="text-xs text-green-600 mt-2 font-medium" id="ktp-name"></p>
                                <p class="text-xs text-gray-400">Klik untuk ganti</p>
                            </div>
                        </div>
                        <div class="mt-2 grid grid-cols-3 gap-1.5 text-center">
                            <div class="p-1.5 bg-green-50 rounded-lg"><p class="text-xs text-green-700"><i class="fas fa-check text-green-400 mr-1"></i>Jelas</p></div>
                            <div class="p-1.5 bg-green-50 rounded-lg"><p class="text-xs text-green-700"><i class="fas fa-check text-green-400 mr-1"></i>Tidak blur</p></div>
                            <div class="p-1.5 bg-green-50 rounded-lg"><p class="text-xs text-green-700"><i class="fas fa-check text-green-400 mr-1"></i>Lengkap</p></div>
                        </div>
                    </div>

                    {{-- Foto Selfie Kamera --}}
                    <div class="mb-2">
                        <div class="flex items-center gap-2 mb-3">
                            <div class="w-6 h-6 rounded-full bg-blue-600 text-white text-xs font-bold flex items-center justify-center">3</div>
                            <h3 class="font-semibold text-gray-900 text-sm">Foto Selfie Pegang KTP</h3>
                        </div>
                        <p class="text-xs text-gray-500 mb-3">Pastikan wajah dan tulisan KTP terlihat jelas.</p>

                        <input type="hidden" name="provider_selfie" id="selfie-input">

                        {{-- Idle --}}
                        <div id="selfie-idle" class="border-2 border-dashed border-gray-300 rounded-xl p-6 text-center">
                            <i class="fas fa-camera text-gray-300 text-3xl mb-2 block"></i>
                            <p class="text-sm font-medium text-gray-700 mb-3">Belum ada foto selfie</p>
                            <button type="button" onclick="startCamera()"
                                    class="px-5 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition-colors">
                                <i class="fas fa-camera mr-2"></i>Buka Kamera
                            </button>
                        </div>

                        {{-- Kamera aktif --}}
                        <div id="selfie-camera" class="hidden rounded-xl overflow-hidden border border-gray-200">
                            <div class="relative bg-black">
                                <video id="camera-video" autoplay playsinline
                                       class="w-full max-h-64 object-cover"></video>
                                <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                                    <div class="border-2 border-white/60 rounded-full w-28 h-28 flex items-center justify-center">
                                        <span class="text-white/60 text-xs text-center leading-tight">Posisikan<br>wajah</span>
                                    </div>
                                </div>
                            </div>
                            <div class="p-3 bg-gray-900 flex gap-2 justify-center">
                                <button type="button" onclick="capturePhoto()"
                                        class="px-5 py-2 bg-white text-gray-900 rounded-lg text-sm font-semibold hover:bg-gray-100 transition-colors">
                                    <i class="fas fa-camera mr-2"></i>Ambil Foto
                                </button>
                                <button type="button" onclick="stopCamera()"
                                        class="px-4 py-2 bg-gray-700 text-white rounded-lg text-sm font-medium hover:bg-gray-600 transition-colors">
                                    Batal
                                </button>
                            </div>
                        </div>

                        {{-- Hasil foto --}}
                        <div id="selfie-result" class="hidden rounded-xl overflow-hidden border border-green-200">
                            <div class="relative">
                                <img id="selfie-preview-img" src="" alt="Selfie"
                                     class="w-full max-h-64 object-cover">
                                <div class="absolute top-2 right-2">
                                    <span class="px-2 py-1 bg-green-500 text-white text-xs font-semibold rounded-full">
                                        <i class="fas fa-check mr-1"></i>Foto diambil
                                    </span>
                                </div>
                            </div>
                            <div class="p-3 bg-green-50 flex justify-center">
                                <button type="button" onclick="retakePhoto()"
                                        class="px-4 py-1.5 bg-white border border-gray-300 text-gray-700 rounded-lg text-sm hover:bg-gray-50 transition-colors">
                                    <i class="fas fa-redo mr-1"></i>Ambil Ulang
                                </button>
                            </div>
                        </div>

                        <canvas id="selfie-canvas" class="hidden"></canvas>

                        @error('provider_selfie')
                            <p class="mt-2 text-sm text-red-600"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                        @enderror
                    </div>

                </div>
                {{-- END SECTION PROVIDER --}}

                {{-- Terms --}}
                <div class="flex items-start mb-6">
                    <input id="terms" name="terms" type="checkbox" required
                           class="h-4 w-4 text-blue-600 border-gray-300 rounded mt-0.5">
                    <label for="terms" class="ml-3 text-sm text-gray-700">
                        Saya menyetujui
                        <a href="#" class="text-blue-600 hover:underline">Syarat & Ketentuan</a>
                        dan
                        <a href="#" class="text-blue-600 hover:underline">Kebijakan Privasi</a>
                        EduLiving
                    </label>
                </div>
                @error('terms')
                    <p class="mb-4 text-sm text-red-600">{{ $message }}</p>
                @enderror

                {{-- Submit --}}
                <button type="button" onclick="submitForm()" id="submit-btn"
                        class="w-full flex justify-center items-center gap-2 py-3 px-4 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 disabled:from-gray-400 disabled:to-gray-400 text-white font-semibold rounded-xl transition duration-200 shadow-lg">
                    <i class="fas fa-user-plus"></i>
                    <span id="submit-text">Daftar Sekarang</span>
                    <span id="submit-spinner" class="hidden"><i class="fas fa-spinner fa-spin"></i> Mendaftar...</span>
                </button>

                <div class="mt-6 text-center">
                    <p class="text-sm text-gray-600">
                        Sudah punya akun?
                        <a href="{{ route('login') }}" class="font-medium text-blue-600 hover:text-blue-500">Masuk di sini</a>
                    </p>
                </div>
            </form>
        </div>

        <div class="text-center text-sm text-gray-500">
            <p>&copy; {{ date('Y') }} EduLiving. Semua hak dilindungi.</p>
        </div>
    </div>
</div>

<script>
let stream     = null;
let isProvider = false;

// ---- Role change ----
function handleRoleChange(value) {
    const section = document.getElementById('provider-verification');
    const ktpInput  = document.getElementById('provider_ktp');
    const nikInput  = document.getElementById('provider_nik');

    isProvider = value === 'provider_residence' || value === 'provider_event';

    if (isProvider) {
        section.classList.remove('hidden');
        ktpInput.setAttribute('required', '');
        nikInput.setAttribute('required', '');
    } else {
        section.classList.add('hidden');
        ktpInput.removeAttribute('required');
        nikInput.removeAttribute('required');
    }
}

// Sync nama ke preview
document.getElementById('name').addEventListener('input', function () {
    const preview = document.getElementById('nama-preview');
    if (preview) preview.textContent = this.value || '—';
});

// Inisialisasi saat load (untuk old() value setelah validation error)
document.addEventListener('DOMContentLoaded', function () {
    const role = document.getElementById('role').value;
    if (role) handleRoleChange(role);

    // Sync nama jika sudah ada value
    const nameVal = document.getElementById('name').value;
    const preview = document.getElementById('nama-preview');
    if (preview && nameVal) preview.textContent = nameVal;

    // Update counter NIK jika ada old value
    const nik = document.getElementById('provider_nik');
    if (nik) document.getElementById('nik-count').textContent = nik.value.length;
});

// ---- Toggle password ----
function togglePassword(fieldId, iconId) {
    const input = document.getElementById(fieldId);
    const icon  = document.getElementById(iconId);
    const hide  = input.type === 'password';
    input.type  = hide ? 'text' : 'password';
    icon.classList.toggle('fa-eye', !hide);
    icon.classList.toggle('fa-eye-slash', hide);
}

// ---- Password match ----
function checkPasswordMatch() {
    const pw    = document.getElementById('password').value;
    const conf  = document.getElementById('password_confirmation').value;
    const msg   = document.getElementById('password-match-msg');
    const input = document.getElementById('password_confirmation');

    if (!conf) { msg.classList.add('hidden'); return true; }

    if (pw === conf) {
        msg.textContent = '✓ Password cocok';
        msg.className   = 'mt-1 text-sm text-green-600';
        input.classList.replace('border-red-500', 'border-green-500');
        return true;
    } else {
        msg.textContent = '✗ Password tidak cocok';
        msg.className   = 'mt-1 text-sm text-red-600';
        input.classList.replace('border-green-500', 'border-red-500');
        return false;
    }
}

// ---- Preview KTP ----
function previewKtp(input) {
    if (!input.files?.[0]) return;
    const reader = new FileReader();
    reader.onload = e => {
        document.getElementById('ktp-placeholder').classList.add('hidden');
        document.getElementById('ktp-preview').classList.remove('hidden');
        document.getElementById('ktp-preview-img').src = e.target.result;
        document.getElementById('ktp-name').textContent = input.files[0].name;
        document.getElementById('ktp-dropzone').classList.add('border-blue-400', 'bg-blue-50');
    };
    reader.readAsDataURL(input.files[0]);
}

// ---- Kamera ----
async function startCamera() {
    try {
        stream = await navigator.mediaDevices.getUserMedia({
            video: { facingMode: 'user', width: { ideal: 1280 }, height: { ideal: 720 } }
        });
        document.getElementById('camera-video').srcObject = stream;
        document.getElementById('selfie-idle').classList.add('hidden');
        document.getElementById('selfie-camera').classList.remove('hidden');
    } catch (err) {
        if (err.name === 'NotAllowedError') {
            alert('Akses kamera ditolak. Izinkan akses kamera di pengaturan browser, lalu coba lagi.');
        } else {
            alert('Tidak bisa membuka kamera: ' + err.message);
        }
    }
}

function capturePhoto() {
    const video  = document.getElementById('camera-video');
    const canvas = document.getElementById('selfie-canvas');
    canvas.width  = video.videoWidth;
    canvas.height = video.videoHeight;

    const ctx = canvas.getContext('2d');
    ctx.translate(canvas.width, 0);
    ctx.scale(-1, 1);
    ctx.drawImage(video, 0, 0);

    const dataUrl = canvas.toDataURL('image/jpeg', 0.85);
    document.getElementById('selfie-input').value        = dataUrl;
    document.getElementById('selfie-preview-img').src   = dataUrl;
    document.getElementById('selfie-camera').classList.add('hidden');
    document.getElementById('selfie-result').classList.remove('hidden');

    stopCamera();
}

function stopCamera() {
    stream?.getTracks().forEach(t => t.stop());
    stream = null;
    document.getElementById('selfie-camera').classList.add('hidden');
    if (!document.getElementById('selfie-input').value) {
        document.getElementById('selfie-idle').classList.remove('hidden');
    }
}

function retakePhoto() {
    document.getElementById('selfie-input').value = '';
    document.getElementById('selfie-result').classList.add('hidden');
    document.getElementById('selfie-idle').classList.remove('hidden');
}

// ---- Submit dengan validasi ----
function submitForm() {
    if (!checkPasswordMatch()) return;

    if (isProvider) {
        const nik    = document.getElementById('provider_nik').value;
        const ktp    = document.getElementById('provider_ktp').files[0];
        const selfie = document.getElementById('selfie-input').value;

        if (nik.length !== 16) {
            alert('NIK harus 16 digit.');
            document.getElementById('provider_nik').focus();
            return;
        }
        if (!ktp) {
            alert('Foto KTP wajib diunggah.');
            return;
        }
        if (!selfie) {
            alert('Foto selfie wajib diambil. Klik tombol "Buka Kamera".');
            return;
        }
    }

    const btn     = document.getElementById('submit-btn');
    const text    = document.getElementById('submit-text');
    const spinner = document.getElementById('submit-spinner');
    btn.disabled  = true;
    text.classList.add('hidden');
    spinner.classList.remove('hidden');

    document.getElementById('register-form').submit();
}

window.addEventListener('beforeunload', stopCamera);
</script>
@endsection