{{-- resources/views/user/role-switch/_provider-form.blade.php --}}
{{-- Usage: @include('user.role-switch._provider-form', ['action' => route(...), 'submitLabel' => '...']) --}}

@if ($errors->any())
    <div class="mb-5 p-4 bg-red-50 border border-red-200 rounded-xl">
        <ul class="text-sm text-red-700 space-y-1">
            @foreach ($errors->all() as $error)
                <li class="flex items-center gap-2"><i class="fas fa-exclamation-circle"></i>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ $action }}" enctype="multipart/form-data" id="provider-form">
    @csrf

    <input type="hidden" name="provider_selfie" id="selfie-input">

    {{-- STEP 1 --}}
    <div class="mb-6">
        <div class="flex items-center gap-2 mb-4">
            <div class="w-7 h-7 rounded-full bg-blue-600 text-white text-sm font-bold flex items-center justify-center">1</div>
            <h3 class="font-semibold text-gray-900">Data Diri</h3>
        </div>
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">
                    Nama Lengkap <span class="text-xs text-gray-400">(sesuai KTP)</span>
                </label>
                <input type="text" value="{{ auth()->user()->name }}" readonly
                    class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-gray-700 text-sm cursor-not-allowed">
                <p class="text-xs text-gray-400 mt-1">Nama diambil dari profil akunmu</p>
            </div>
            <div>
                <label for="provider_nik" class="block text-sm font-medium text-gray-700 mb-1.5">
                    NIK <span class="text-red-500">*</span>
                    <span class="text-xs text-gray-400 font-normal">(16 digit, sesuai KTP)</span>
                </label>
                <input type="text" name="provider_nik" id="provider_nik"
                    value="{{ old('provider_nik', auth()->user()->provider_nik) }}"
                    maxlength="16" inputmode="numeric" pattern="[0-9]{16}"
                    placeholder="Contoh: 3201234567890001"
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('provider_nik') border-red-400 @enderror"
                    oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 16); updateNikCounter(this)">
                <div class="flex justify-between mt-1">
                    <span class="text-xs text-red-500">@error('provider_nik'){{ $message }}@enderror</span>
                    <span class="text-xs text-gray-400"><span id="nik-count">0</span>/16 digit</span>
                </div>
            </div>
        </div>
    </div>

    {{-- STEP 2 --}}
    <div class="mb-6">
        <div class="flex items-center gap-2 mb-4">
            <div class="w-7 h-7 rounded-full bg-blue-600 text-white text-sm font-bold flex items-center justify-center">2</div>
            <h3 class="font-semibold text-gray-900">Foto KTP</h3>
        </div>
        <div class="relative border-2 border-dashed border-gray-300 rounded-xl p-6 text-center hover:border-blue-400 transition-colors cursor-pointer" id="ktp-dropzone">
            <input type="file" name="provider_ktp" id="ktp_file" accept="image/*" required
                class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                onchange="previewKtp(this)">
            <div id="ktp-placeholder">
                <i class="fas fa-id-card text-gray-300 text-4xl mb-2 block"></i>
                <p class="text-sm font-medium text-gray-700">Klik atau drag foto KTP ke sini</p>
                <p class="text-xs text-gray-400 mt-1">JPG, PNG — Maks. 2MB</p>
            </div>
            <div id="ktp-preview" class="hidden">
                <img id="ktp-preview-img" src="" alt="KTP" class="max-h-40 mx-auto rounded-lg object-cover border border-gray-200">
                <p class="text-xs text-green-600 mt-2 font-medium" id="ktp-preview-name"></p>
                <p class="text-xs text-gray-400">Klik untuk ganti</p>
            </div>
        </div>
    </div>

    {{-- STEP 3 --}}
    <div class="mb-8">
        <div class="flex items-center gap-2 mb-4">
            <div class="w-7 h-7 rounded-full bg-blue-600 text-white text-sm font-bold flex items-center justify-center">3</div>
            <h3 class="font-semibold text-gray-900">Foto Selfie Pegang KTP</h3>
        </div>
        <p class="text-sm text-gray-500 mb-4">Ambil foto selfie sambil memegang KTP di depan wajahmu.</p>

        <div id="selfie-idle" class="border-2 border-dashed border-gray-300 rounded-xl p-8 text-center">
            <i class="fas fa-camera text-gray-300 text-4xl mb-3 block"></i>
            <p class="text-sm font-medium text-gray-700 mb-3">Belum ada foto selfie</p>
            <button type="button" onclick="startCamera()"
                class="px-5 py-2.5 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition-colors">
                <i class="fas fa-camera mr-2"></i>Buka Kamera
            </button>
        </div>

        <div id="selfie-camera" class="hidden rounded-xl overflow-hidden border border-gray-200">
            <div class="relative bg-black">
                <video id="camera-video" autoplay playsinline class="w-full max-h-72 object-cover"></video>
            </div>
            <div class="p-4 bg-gray-900 flex gap-3 justify-center">
                <button type="button" onclick="capturePhoto()"
                    class="px-6 py-2.5 bg-white text-gray-900 rounded-lg text-sm font-semibold hover:bg-gray-100 transition-colors">
                    <i class="fas fa-camera mr-2"></i>Ambil Foto
                </button>
                <button type="button" onclick="stopCamera()"
                    class="px-5 py-2.5 bg-gray-700 text-white rounded-lg text-sm font-medium hover:bg-gray-600 transition-colors">
                    Batal
                </button>
            </div>
        </div>

        <div id="selfie-result" class="hidden rounded-xl overflow-hidden border border-green-200">
            <div class="relative">
                <img id="selfie-preview-img" src="" alt="Selfie" class="w-full max-h-72 object-cover">
                <div class="absolute top-3 right-3">
                    <span class="px-2 py-1 bg-green-500 text-white text-xs font-semibold rounded-full">
                        <i class="fas fa-check mr-1"></i>Foto diambil
                    </span>
                </div>
            </div>
            <div class="p-3 bg-green-50 flex justify-center">
                <button type="button" onclick="retakePhoto()"
                    class="px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-50 transition-colors">
                    <i class="fas fa-redo mr-2"></i>Ambil Ulang
                </button>
            </div>
        </div>

        <canvas id="selfie-canvas" class="hidden"></canvas>
        @error('provider_selfie')
            <p class="mt-2 text-sm text-red-600"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
        @enderror
    </div>

    <div class="bg-gray-50 rounded-xl p-4 mb-6 text-xs text-gray-500 flex items-start gap-2">
        <i class="fas fa-lock text-gray-400 mt-0.5"></i>
        <span>Data identitasmu (NIK, foto KTP, foto selfie) hanya digunakan untuk verifikasi dan tidak akan dipublikasikan.</span>
    </div>

    <button type="button" onclick="submitProviderForm()" id="submit-btn"
        class="w-full bg-blue-600 hover:bg-blue-700 disabled:bg-gray-300 disabled:cursor-not-allowed text-white py-4 px-6 rounded-xl font-semibold text-lg transition-colors flex items-center justify-center gap-3">
        <i class="fas fa-paper-plane"></i>{{ $submitLabel ?? 'Kirim Pengajuan' }}
    </button>
</form>

<script>
let stream = null;

function updateNikCounter(input) {
    document.getElementById('nik-count').textContent = input.value.length;
}
document.addEventListener('DOMContentLoaded', function() {
    const nikInput = document.getElementById('provider_nik');
    if (nikInput) updateNikCounter(nikInput);
});

function previewKtp(input) {
    if (!input.files || !input.files[0]) return;
    const reader = new FileReader();
    reader.onload = function(e) {
        document.getElementById('ktp-placeholder').classList.add('hidden');
        document.getElementById('ktp-preview').classList.remove('hidden');
        document.getElementById('ktp-preview-img').src = e.target.result;
        document.getElementById('ktp-preview-name').textContent = input.files[0].name;
    };
    reader.readAsDataURL(input.files[0]);
}

async function startCamera() {
    try {
        stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user' } });
        document.getElementById('camera-video').srcObject = stream;
        document.getElementById('selfie-idle').classList.add('hidden');
        document.getElementById('selfie-camera').classList.remove('hidden');
        document.getElementById('selfie-result').classList.add('hidden');
    } catch (err) {
        alert('Tidak bisa membuka kamera: ' + err.message);
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
    document.getElementById('selfie-input').value = dataUrl;
    document.getElementById('selfie-preview-img').src = dataUrl;
    document.getElementById('selfie-camera').classList.add('hidden');
    document.getElementById('selfie-result').classList.remove('hidden');
    stopCamera();
}

function stopCamera() {
    if (stream) { stream.getTracks().forEach(t => t.stop()); stream = null; }
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

function submitProviderForm() {
    const nik    = document.getElementById('provider_nik').value;
    const ktp    = document.getElementById('ktp_file').files[0];
    const selfie = document.getElementById('selfie-input').value;

    if (nik.length !== 16) { alert('NIK harus 16 digit.'); document.getElementById('provider_nik').focus(); return; }
    if (!ktp)    { alert('Foto KTP wajib diunggah.'); return; }
    if (!selfie) { alert('Foto selfie wajib diambil.'); return; }

    document.getElementById('submit-btn').disabled = true;
    document.getElementById('submit-btn').innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Mengirim pengajuan...';
    document.getElementById('provider-form').submit();
}

window.addEventListener('beforeunload', stopCamera);
</script>
