<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\BannedIdentity;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class RegisterController extends Controller
{
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    /**
     * Step 1 — Validasi form registrasi.
     *
     * Untuk role user      : langsung buat akun & login.
     * Untuk role provider  : simpan data + upload file sementara ke session,
     *                        redirect ke halaman S&K. Akun BELUM dibuat.
     */
    public function register(Request $request)
    {
        \Log::info('Register attempt', [
            'role'  => $request->role,
            'email' => $request->email,
        ]);

        \Log::info('All input', $request->except(['password', 'password_confirmation', 'provider_selfie', 'provider_ktp']));
            

        $isProvider = in_array($request->role, ['provider_residence', 'provider_event']);

        // ── Validasi ──────────────────────────────────────────────────────
        $rules = [
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role'     => ['required', 'in:user,provider_residence,provider_event'],
        ];

        if ($isProvider) {
            $rules['provider_nik']    = ['required', 'digits:16'];
            $rules['provider_ktp']    = ['required', 'image', 'mimes:jpg,jpeg,png', 'max:2048'];
            $rules['provider_selfie'] = ['required', 'string'];
        }

        $request->validate($rules, [
            'name.required'            => 'Nama lengkap wajib diisi.',
            'email.required'           => 'Email wajib diisi.',
            'email.unique'             => 'Email sudah terdaftar.',
            'password.min'             => 'Password minimal 8 karakter.',
            'password.confirmed'       => 'Konfirmasi password tidak cocok.',
            'role.required'            => 'Pilih peran terlebih dahulu.',
            'provider_nik.required'    => 'NIK wajib diisi.',
            'provider_nik.digits'      => 'NIK harus 16 digit.',
            'provider_ktp.required'    => 'Foto KTP wajib diunggah.',
            'provider_ktp.image'       => 'File KTP harus berupa gambar.',
            'provider_ktp.max'         => 'Ukuran foto KTP maksimal 2MB.',
            'provider_selfie.required' => 'Foto selfie wajib diambil.',
        ]);

        // ── Cek blacklist ─────────────────────────────────────────────────
        if (BannedIdentity::isEmailBanned($request->email)) {
            throw ValidationException::withMessages([
                'email' => 'Email ini tidak dapat digunakan untuk mendaftar di EduLiving.',
            ]);
        }

        if ($isProvider && $request->filled('provider_nik')) {
            if (BannedIdentity::isNikBanned($request->provider_nik)) {
                throw ValidationException::withMessages([
                    'provider_nik' => 'NIK ini tidak dapat digunakan untuk mendaftar sebagai provider di EduLiving.',
                ]);
            }
        }

        // ── Role user: langsung buat akun ─────────────────────────────────
        if (!$isProvider) {
            return $this->createUser([
                'name'     => $request->name,
                'email'    => $request->email,
                'password' => $request->password,
                'role'     => $request->role,
            ]);
        }

        // ── Role provider: simpan ke session, redirect ke S&K ─────────────

        // Upload KTP ke folder sementara
        $ktpPath = $request->file('provider_ktp')
            ->store('temp/provider-ktp', 'public');

        // Selfie base64 → simpan ke folder sementara
        $selfieBase64 = $request->provider_selfie;
        if (!preg_match('/^data:image\/(\w+);base64,/', $selfieBase64, $matches)) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['provider_selfie' => 'Format foto selfie tidak valid. Silakan ambil ulang.']);
        }
        $imageData  = base64_decode(substr($selfieBase64, strpos($selfieBase64, ',') + 1));
        $extension  = $matches[1] === 'jpeg' ? 'jpg' : $matches[1];
        $selfiePath = 'temp/provider-selfie/' . uniqid('selfie_', true) . '.' . $extension;
        Storage::disk('public')->put($selfiePath, $imageData);

        // Simpan semua data ke session (password di-hash sekarang)
        session([
            'pending_provider' => [
                'name'             => $request->name,
                'email'            => $request->email,
                'password'         => Hash::make($request->password),
                'role'             => $request->role,
                'provider_nik'     => $request->provider_nik,
                'provider_ktp'     => $ktpPath,
                'provider_selfie'  => $selfiePath,
                'expires_at'       => now()->addMinutes(30)->timestamp, // session valid 30 menit
            ],
        ]);

        // Redirect ke halaman S&K khusus provider (sebelum akun dibuat)
        return redirect()->route('verification.terms', [
            'type'        => 'provider',
            'redirect_to' => 'register.complete',
        ]);
    }

    /**
     * Step 2 — Dipanggil dari VerificationTermsController::accept()
     * setelah provider menyetujui S&K.
     * Akun provider baru dibuat di sini.
     */
    public function completeProviderRegistration()
    {
        $pending = session('pending_provider');

        // Validasi session masih ada dan belum expired
        if (!$pending) {
            return redirect()->route('register')
                ->with('error', 'Sesi pendaftaran tidak ditemukan. Silakan daftar ulang.');
        }

        if (now()->timestamp > $pending['expires_at']) {
            session()->forget('pending_provider');
            // Hapus file sementara
            Storage::disk('public')->delete($pending['provider_ktp'] ?? '');
            Storage::disk('public')->delete($pending['provider_selfie'] ?? '');

            return redirect()->route('register')
                ->with('error', 'Sesi pendaftaran sudah kedaluwarsa (30 menit). Silakan daftar ulang.');
        }

        // Cek ulang email masih belum dipakai (race condition)
        if (User::where('email', $pending['email'])->exists()) {
            session()->forget('pending_provider');
            return redirect()->route('register')
                ->with('error', 'Email ' . $pending['email'] . ' sudah terdaftar. Silakan gunakan email lain.');
        }

        // Pindahkan file dari folder temp ke folder permanen
        $ktpFinal    = str_replace('temp/', '', $pending['provider_ktp']);
        $selfieFinal = str_replace('temp/', '', $pending['provider_selfie']);

        Storage::disk('public')->move($pending['provider_ktp'], $ktpFinal);
        Storage::disk('public')->move($pending['provider_selfie'], $selfieFinal);

        // Buat akun
        $user = User::create([
            'name'              => $pending['name'],
            'email'             => $pending['email'],
            'password'          => $pending['password'], // sudah di-hash
            'email_verified_at' => now(),
            'provider_status'   => 'pending',
            'provider_nik'      => $pending['provider_nik'],
            'provider_ktp'      => $ktpFinal,
            'provider_selfie'   => $selfieFinal,
            'terms_accepted_at' => now(), // S&K sudah disetujui
        ]);

        $role = Role::where('name', $pending['role'])->first();
        if ($role) {
            $user->roles()->attach($role->id);
        }

        // Bersihkan session
        session()->forget('pending_provider');

        Auth::login($user);

        $dashboardRoute = $pending['role'] === 'provider_residence'
            ? 'provider.residence.dashboard'
            : 'provider.event.dashboard';

        return redirect()->route($dashboardRoute)
            ->with('success', 'Selamat datang, ' . $user->name . '! Akun kamu sedang dalam proses verifikasi admin.');
    }

    /**
     * Buat akun user biasa (non-provider).
     */
    private function createUser(array $data): \Illuminate\Http\RedirectResponse
    {
        $user = User::create([
            'name'              => $data['name'],
            'email'             => $data['email'],
            'password'          => Hash::make($data['password']),
            'email_verified_at' => now(),
            'provider_status'   => 'none',
        ]);

        $role = Role::where('name', $data['role'])->first();
        if ($role) {
            $user->roles()->attach($role->id);
        }

        Auth::login($user);

        return redirect()->route('user.dashboard')
            ->with('success', 'Selamat datang di EduLiving, ' . $user->name . '!');
    }
}