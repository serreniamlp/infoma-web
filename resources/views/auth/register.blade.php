@extends('layouts.app')

@section('title', 'Register - Infoma')

@section('content')
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-lg w-full space-y-8">
            <!-- Header -->
            <div class="text-center">
                <div class="mx-auto flex items-center justify-center mb-4">
                    <img src="{{ asset('images/Infoma_Branding-blue.png') }}" alt="Infoma Logo" class="w-16 h-16">
                </div>
                <h2 class="text-3xl font-bold text-gray-900 mb-2">Bergabung dengan Infoma</h2>
                <p class="text-gray-600">Buat akun baru untuk mengakses layanan kami</p>
            </div>

            <!-- Success Messages -->
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-xl mb-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-check-circle text-green-500"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm">{{ session('success') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Error Messages -->
            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-xl mb-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-circle text-red-500"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm">{{ session('error') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Register Form -->
            <div class="bg-white rounded-2xl shadow-2xl p-8 border border-gray-100">
                <form class="space-y-6" method="POST" action="{{ route('register') }}">
                    @csrf

                    <!-- Role Selection Dropdown -->
                    <div>
                        <label for="role" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-user-tag mr-2 text-blue-500"></i>Daftar sebagai
                        </label>
                        <div class="relative">
                            <select id="role" name="role" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 bg-gray-50 focus:bg-white appearance-none @error('role') border-red-500 @enderror">
                                <option value="" disabled {{ old('role') ? '' : 'selected' }}>Pilih peran Anda</option>
                                <option value="user" {{ old('role', 'user') == 'user' ? 'selected' : '' }}>
                                    👨‍🎓 Mahasiswa - Cari tempat tinggal & kegiatan
                                </option>
                                <option value="provider" {{ old('role') == 'provider' ? 'selected' : '' }}>
                                    🏪 Penyedia - Sediakan tempat & kegiatan
                                </option>
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none">
                                <i class="fas fa-chevron-down text-gray-400"></i>
                            </div>
                        </div>
                        @error('role')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Name Field -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-user mr-2 text-blue-500"></i>Nama Lengkap
                        </label>
                        <input id="name" name="name" type="text" required autofocus
                            value="{{ old('name') }}"
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 bg-gray-50 focus:bg-white @error('name') border-red-500 @enderror"
                            placeholder="Masukkan nama lengkap">
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Email Field -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-envelope mr-2 text-blue-500"></i>Email
                        </label>
                        <input id="email" name="email" type="email" required autocomplete="email"
                            value="{{ old('email') }}"
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 bg-gray-50 focus:bg-white @error('email') border-red-500 @enderror"
                            placeholder="nama@email.com">
                        @error('email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Phone Field -->
                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-phone mr-2 text-blue-500"></i>Nomor Telepon
                        </label>
                        <input id="phone" name="phone" type="tel" required
                            value="{{ old('phone') }}"
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 bg-gray-50 focus:bg-white @error('phone') border-red-500 @enderror"
                            placeholder="08xxxxxxxxxx">
                        @error('phone')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Address Field -->
                    <div>
                        <label for="address" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-map-marker-alt mr-2 text-blue-500"></i>Alamat
                        </label>
                        <textarea id="address" name="address" rows="3" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 bg-gray-50 focus:bg-white @error('address') border-red-500 @enderror"
                            placeholder="Masukkan alamat lengkap">{{ old('address') }}</textarea>
                        @error('address')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Password Fields -->
                    <div class="grid grid-cols-1 gap-4">
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-lock mr-2 text-blue-500"></i>Password
                            </label>
                            <div class="relative">
                                <input id="password" name="password" type="password" required autocomplete="new-password"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 bg-gray-50 focus:bg-white pr-12 @error('password') border-red-500 @enderror"
                                    placeholder="Minimal 8 karakter">
                                <button type="button" onclick="togglePassword('password')"
                                    class="absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                    <i id="toggleIcon1" class="fas fa-eye"></i>
                                </button>
                            </div>
                            @error('password')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-lock mr-2 text-blue-500"></i>Konfirmasi Password
                            </label>
                            <div class="relative">
                                <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 bg-gray-50 focus:bg-white pr-12"
                                    placeholder="Ulangi password">
                                <button type="button" onclick="togglePassword('password_confirmation')"
                                    class="absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                    <i id="toggleIcon2" class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div id="password-match-message" class="mt-1 text-sm hidden"></div>
                        </div>
                    </div>

                    <!-- Terms and Privacy -->
                    <div class="flex items-start">
                        <input id="terms" name="terms" type="checkbox" required
                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded mt-1">
                        <label for="terms" class="ml-3 block text-sm text-gray-700">
                            Saya menyetujui <a href="#" class="text-blue-600 hover:text-blue-500">Syarat & Ketentuan</a>
                            dan <a href="#" class="text-blue-600 hover:text-blue-500">Kebijakan Privasi</a> Infoma
                        </label>
                        @error('terms')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Submit Button -->
                    <div>
                        <button type="submit" id="submitBtn"
                            class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-xl text-white bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                            <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                                <i class="fas fa-user-plus text-blue-200 group-hover:text-blue-100"></i>
                            </span>
                            <span id="submitText">Daftar Sekarang</span>
                            <span id="submitSpinner" class="hidden">
                                <i class="fas fa-spinner fa-spin mr-2"></i>Mendaftar...
                            </span>
                        </button>
                    </div>

                    <!-- Divider -->
                    <div class="relative">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t border-gray-300"></div>
                        </div>
                        <div class="relative flex justify-center text-sm">
                            <span class="px-2 bg-white text-gray-500">atau</span>
                        </div>
                    </div>

                    <!-- Login Link -->
                    <div class="text-center">
                        <p class="text-sm text-gray-600">
                            Sudah punya akun?
                            <a href="{{ route('login') }}"
                                class="font-medium text-blue-600 hover:text-blue-500 transition duration-200">
                                Masuk di sini
                            </a>
                        </p>
                    </div>
                </form>
            </div>

            <!-- Footer -->
            <div class="text-center text-sm text-gray-500">
                <p>&copy; {{ date('Y') }} Infoma. Semua hak dilindungi.</p>
            </div>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div id="loadingOverlay" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white p-6 rounded-lg shadow-lg text-center">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto mb-3"></div>
            <p class="text-gray-600">Sedang mendaftarkan akun...</p>
        </div>
    </div>

    <script>
    function togglePassword(fieldId) {
        const passwordInput = document.getElementById(fieldId);
        const iconNumber = fieldId === 'password' ? '1' : '2';
        const toggleIcon = document.getElementById('toggleIcon' + iconNumber);

        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            toggleIcon.classList.remove('fa-eye');
            toggleIcon.classList.add('fa-eye-slash');
        } else {
            passwordInput.type = 'password';
            toggleIcon.classList.remove('fa-eye-slash');
            toggleIcon.classList.add('fa-eye');
        }
    }

    // Role selection functionality (simplified for dropdown)
    document.getElementById('role').addEventListener('change', function() {
        const selectedValue = this.value;
        if (selectedValue) {
            this.classList.add('text-gray-900');
            this.classList.remove('text-gray-500');
        }
    });

    // Set initial selection styling
    const roleSelect = document.getElementById('role');
    if (roleSelect.value) {
        roleSelect.classList.add('text-gray-900');
        roleSelect.classList.remove('text-gray-500');
    }

    // Password confirmation validation
    function validatePassword() {
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('password_confirmation').value;
        const messageDiv = document.getElementById('password-match-message');
        const confirmInput = document.getElementById('password_confirmation');

        if (confirmPassword && password !== confirmPassword) {
            messageDiv.textContent = 'Password tidak cocok';
            messageDiv.classList.remove('hidden', 'text-green-600');
            messageDiv.classList.add('text-red-600');
            confirmInput.classList.add('border-red-500');
            confirmInput.classList.remove('border-gray-300', 'border-green-500');
            return false;
        } else if (confirmPassword && password === confirmPassword) {
            messageDiv.textContent = 'Password cocok';
            messageDiv.classList.remove('hidden', 'text-red-600');
            messageDiv.classList.add('text-green-600');
            confirmInput.classList.add('border-green-500');
            confirmInput.classList.remove('border-gray-300', 'border-red-500');
            return true;
        } else {
            messageDiv.classList.add('hidden');
            confirmInput.classList.remove('border-red-500', 'border-green-500');
            confirmInput.classList.add('border-gray-300');
            return true;
        }
    }

    document.getElementById('password').addEventListener('input', validatePassword);
    document.getElementById('password_confirmation').addEventListener('input', validatePassword);

    // Form submission handling
    document.querySelector('form').addEventListener('submit', function(e) {
        const submitBtn = document.getElementById('submitBtn');
        const submitText = document.getElementById('submitText');
        const submitSpinner = document.getElementById('submitSpinner');
        const loadingOverlay = document.getElementById('loadingOverlay');

        // Validate password match before submission
        if (!validatePassword()) {
            e.preventDefault();
            return false;
        }

        // Show loading states
        submitBtn.disabled = true;
        submitText.classList.add('hidden');
        submitSpinner.classList.remove('hidden');
        loadingOverlay.classList.remove('hidden');
    });

    // Phone number formatting
    document.getElementById('phone').addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');

        // Format Indonesian phone number
        if (value.startsWith('08')) {
            // Keep as is for mobile numbers
        } else if (value.startsWith('8')) {
            value = '0' + value;
        } else if (value.startsWith('628')) {
            value = '0' + value.substring(2);
        }

        e.target.value = value;
    });

    // Auto hide alerts after 5 seconds
    setTimeout(function() {
        const alerts = document.querySelectorAll('.bg-green-100, .bg-red-100');
        alerts.forEach(alert => {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        });
    }, 5000);

    // Password strength indicator
    document.getElementById('password').addEventListener('input', function() {
        const password = this.value;
        const strengthDiv = document.getElementById('password-strength');

        if (password.length === 0) {
            if (strengthDiv) strengthDiv.classList.add('hidden');
            return;
        }

        let strength = 0;
        let feedback = [];

        if (password.length >= 8) strength++;
        else feedback.push('Minimal 8 karakter');

        if (/[a-z]/.test(password)) strength++;
        else feedback.push('Huruf kecil');

        if (/[A-Z]/.test(password)) strength++;
        else feedback.push('Huruf besar');

        if (/\d/.test(password)) strength++;
        else feedback.push('Angka');

        if (/[^A-Za-z0-9]/.test(password)) strength++;
        else feedback.push('Simbol');

        // You can add password strength indicator here if needed
    });
    </script>
@endsection

@push('scripts')
<script>
// Password strength indicator
document.getElementById('password').addEventListener('input', function() {
    const password = this.value;
    const strengthDiv = document.getElementById('password-strength');

    if (password.length === 0) {
        if (strengthDiv) strengthDiv.classList.add('hidden');
        return;
    }

    let strength = 0;
    let feedback = [];

    if (password.length >= 8) strength++;
    else feedback.push('Minimal 8 karakter');

    if (/[a-z]/.test(password)) strength++;
    else feedback.push('Huruf kecil');

    if (/[A-Z]/.test(password)) strength++;
    else feedback.push('Huruf besar');

    if (/\d/.test(password)) strength++;
    else feedback.push('Angka');

    if (/[^A-Za-z0-9]/.test(password)) strength++;
    else feedback.push('Simbol');

    // You can add password strength indicator here if needed
});
</script>
@endpush
