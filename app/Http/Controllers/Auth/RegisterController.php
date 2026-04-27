<?php
// app/Http/Controllers/Auth/RegisterController.php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;

class RegisterController extends Controller
{
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $isProvider = in_array($request->role, ['provider_residence', 'provider_event']);

        // Validasi dasar — berlaku untuk semua role
        $rules = [
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role'     => ['required', 'in:user,provider_residence,provider_event'],
            'terms'    => ['required', 'accepted'],
        ];

        // Validasi tambahan khusus provider
        if ($isProvider) {
            $rules['provider_nik']     = ['required', 'digits:16'];
            $rules['provider_ktp']     = ['required', 'image', 'mimes:jpg,jpeg,png', 'max:2048'];
            $rules['provider_selfie']  = ['required', 'string']; // base64
        }

        $request->validate($rules, [
            'name.required'            => 'Nama lengkap wajib diisi.',
            'email.required'           => 'Email wajib diisi.',
            'email.unique'             => 'Email sudah terdaftar.',
            'password.min'             => 'Password minimal 8 karakter.',
            'password.confirmed'       => 'Konfirmasi password tidak cocok.',
            'role.required'            => 'Pilih peran terlebih dahulu.',
            'terms.accepted'           => 'Kamu harus menyetujui syarat & ketentuan.',
            'provider_nik.required'    => 'NIK wajib diisi.',
            'provider_nik.digits'      => 'NIK harus 16 digit.',
            'provider_ktp.required'    => 'Foto KTP wajib diunggah.',
            'provider_ktp.image'       => 'File KTP harus berupa gambar.',
            'provider_ktp.max'         => 'Ukuran foto KTP maksimal 2MB.',
            'provider_selfie.required' => 'Foto selfie wajib diambil.',
        ]);

        // Siapkan data user
        $userData = [
            'name'              => $request->name,
            'email'             => $request->email,
            'password'          => Hash::make($request->password),
            'email_verified_at' => now(),
            'provider_status'   => $isProvider ? 'pending' : 'none',
        ];

        // Proses upload dokumen provider
        if ($isProvider) {
            // Foto KTP (upload biasa)
            $userData['provider_nik'] = $request->provider_nik;
            $userData['provider_ktp'] = $request->file('provider_ktp')
                ->store('provider-ktp', 'public');

            // Foto selfie (base64 dari kamera)
            $selfieBase64 = $request->provider_selfie;

            if (!preg_match('/^data:image\/(\w+);base64,/', $selfieBase64, $matches)) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['provider_selfie' => 'Format foto selfie tidak valid. Silakan ambil ulang.']);
            }

            $imageData   = base64_decode(substr($selfieBase64, strpos($selfieBase64, ',') + 1));
            $extension   = $matches[1] === 'jpeg' ? 'jpg' : $matches[1];
            $selfieName  = 'provider-selfie/' . uniqid('selfie_', true) . '.' . $extension;

            Storage::disk('public')->put($selfieName, $imageData);
            $userData['provider_selfie'] = $selfieName;
        }

        $user = User::create($userData);

        // Assign role
        $role = Role::where('name', $request->role)->first();
        if ($role) {
            $user->roles()->attach($role->id);
        }

        Auth::login($user);

        if ($user->hasRole('provider_residence')) {
            return redirect()->route('provider.residence.dashboard')
                ->with('info', 'Akun kamu sedang dalam proses verifikasi. Kamu bisa mulai buat listing setelah disetujui admin.');
        } elseif ($user->hasRole('provider_event')) {
            return redirect()->route('provider.event.dashboard')
                ->with('info', 'Akun kamu sedang dalam proses verifikasi. Kamu bisa mulai buat event setelah disetujui admin.');
        } else {
            return redirect()->route('user.dashboard')
                ->with('success', 'Selamat datang di EduLiving, ' . $user->name . '!');
        }
    }
}