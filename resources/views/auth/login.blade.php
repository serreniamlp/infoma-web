@extends('layouts.app')

@section('title', 'Login - Infoma')

@section('content')
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <!-- Header -->
            <div class="text-center">
                <div class="mx-auto flex items-center justify-center mb-4">
                    <img src="{{ asset('images/Infoma_Branding-blue.png') }}" alt="Infoma Logo" class="w-16 h-16">
                </div>
                <h2 class="text-3xl font-bold text-gray-900 mb-2">Selamat Datang di Infoma</h2>
                <p class="text-gray-600">Masuk ke akun Anda untuk melanjutkan</p>
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

            <!-- Login Form -->
            <div class="bg-white rounded-2xl shadow-2xl p-8 border border-gray-100">
                <form class="space-y-6" method="POST" action="{{ route('login') }}">
                    @csrf

                    <!-- Email Field -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-envelope mr-2 text-blue-500"></i>Email
                        </label>
                        <input id="email" name="email" type="email" required autocomplete="email" autofocus
                            value="{{ old('email') }}"
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 bg-gray-50 focus:bg-white @error('email') border-red-500 @enderror"
                            placeholder="Masukkan email Anda">
                        @error('email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Password Field -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-lock mr-2 text-blue-500"></i>Password
                        </label>
                        <div class="relative">
                            <input id="password" name="password" type="password" required autocomplete="current-password"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 bg-gray-50 focus:bg-white pr-12 @error('password') border-red-500 @enderror"
                                placeholder="Masukkan password Anda">
                            <button type="button" onclick="togglePassword()"
                                class="absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                <i id="toggleIcon" class="fas fa-eye"></i>
                            </button>
                        </div>
                        @error('password')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Remember Me & Forgot Password -->
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <input id="remember_me" name="remember" type="checkbox" {{ old('remember') ? 'checked' : '' }}
                                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="remember_me" class="ml-2 block text-sm text-gray-700">
                                Ingat saya
                            </label>
                        </div>
                        @if (Route::has('password.request'))
                            <div class="text-sm">
                                <a href="{{ route('password.request') }}"
                                    class="font-medium text-blue-600 hover:text-blue-500 transition duration-200">
                                    Lupa password?
                                </a>
                            </div>
                        @endif
                    </div>

                    <!-- Submit Button -->
                    <div>
                        <button type="submit"
                            class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-xl text-white bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                            <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                                <i class="fas fa-sign-in-alt text-blue-200 group-hover:text-blue-100"></i>
                            </span>
                            Masuk
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

                    <!-- Register Link -->
                    <div class="text-center">
                        <p class="text-sm text-gray-600">
                            Belum punya akun?
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}"
                                    class="font-medium text-blue-600 hover:text-blue-500 transition duration-200">
                                    Daftar sekarang
                                </a>
                            @endif
                        </p>
                    </div>
                </form>

                <!-- Demo Accounts Info -->
                <div class="mt-6 p-4 bg-gray-50 rounded-lg">
                    <h3 class="text-sm font-medium text-gray-900 mb-3">
                        <i class="fas fa-info-circle mr-2 text-blue-500"></i>Akun Demo
                    </h3>
                    <div class="space-y-2 text-xs text-gray-600">
                        <div class="flex justify-between">
                            <span class="font-medium">Admin:</span>
                            <span>admin@infoma.com | password</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-medium">Provider:</span>
                            <span>provider@infoma.com | password</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-medium">User:</span>
                            <span>user@infoma.com | password</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Loading Overlay -->
    <div id="loadingOverlay" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white p-6 rounded-lg shadow-lg text-center">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto mb-3"></div>
            <p class="text-gray-600">Sedang masuk...</p>
        </div>
    </div>
@endsection

@push('scripts')
<script>
function togglePassword() {
    const passwordInput = document.getElementById('password');
    const toggleIcon = document.getElementById('toggleIcon');

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

// Show loading overlay on form submit
document.querySelector('form').addEventListener('submit', function() {
    document.getElementById('loadingOverlay').classList.remove('hidden');
});

// Demo account quick login
function quickLogin(email, password) {
    document.getElementById('email').value = email;
    document.getElementById('password').value = password;
}

// Add click handlers to demo account info
document.addEventListener('DOMContentLoaded', function() {
    const demoAccounts = document.querySelectorAll('.space-y-2 > div');
    demoAccounts.forEach(account => {
        account.style.cursor = 'pointer';
        account.classList.add('hover:bg-gray-100', 'p-2', 'rounded', 'transition', 'duration-200');

        account.addEventListener('click', function() {
            const emailPassword = this.querySelector('span:last-child').textContent.split(' | ');
            quickLogin(emailPassword[0], emailPassword[1]);
        });
    });
});
</script>
@endpush
