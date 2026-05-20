<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class RoleSwitchController extends Controller
{
    // ── PROVIDER EVENT ───────────────────────────────────────────────────

    public function becomeProviderEventForm()
    {
        $user = Auth::user();

        if ($user->hasRole('provider_event')) {
            return redirect()->route('provider.event.dashboard')
                ->with('info', 'Anda sudah terdaftar sebagai Penjual Event.');
        }

        $status = $user->provider_status ?? 'none';
        return view('user.role-switch.become-provider-event', compact('status', 'user'));
    }

    public function becomeProviderEventSubmit(Request $request)
    {
        $user = Auth::user();

        if ($user->hasRole('provider_event')) {
            return redirect()->route('provider.event.dashboard');
        }

        if ($user->provider_status === 'pending') {
            return back()->with('info', 'Pengajuan Anda sedang dalam proses peninjauan admin.');
        }

        $request->validate([
            'provider_nik'    => ['required', 'digits:16'],
            'provider_ktp'    => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'provider_selfie' => ['required', 'string'],
        ], [
            'provider_nik.required'    => 'NIK wajib diisi.',
            'provider_nik.digits'      => 'NIK harus 16 digit.',
            'provider_ktp.required'    => 'Foto KTP wajib diunggah.',
            'provider_ktp.image'       => 'File KTP harus berupa gambar.',
            'provider_ktp.max'         => 'Ukuran foto KTP maksimal 2MB.',
            'provider_selfie.required' => 'Foto selfie wajib diambil.',
        ]);

        $ktpPath = $request->file('provider_ktp')->store('provider-ktp', 'public');

        $selfieBase64 = $request->provider_selfie;
        if (!preg_match('/^data:image\/(\w+);base64,/', $selfieBase64, $matches)) {
            return back()->withInput()
                ->withErrors(['provider_selfie' => 'Format foto selfie tidak valid.']);
        }
        $imageData  = base64_decode(substr($selfieBase64, strpos($selfieBase64, ',') + 1));
        $extension  = $matches[1] === 'jpeg' ? 'jpg' : $matches[1];
        $selfieName = 'provider-selfie/' . uniqid('selfie_', true) . '.' . $extension;
        Storage::disk('public')->put($selfieName, $imageData);

        $user->update([
            'provider_nik'    => $request->provider_nik,
            'provider_ktp'    => $ktpPath,
            'provider_selfie' => $selfieName,
            'provider_status' => 'pending',
            // simpan tipe yang diajukan agar admin tahu
            'pending_role'    => 'provider_event',
        ]);

        return redirect()->route('user.profile.show')
            ->with('success', 'Pengajuan sebagai Penjual Event berhasil dikirim. Tunggu verifikasi admin.');
    }

    // ── PROVIDER RESIDENCE ───────────────────────────────────────────────

    public function becomeProviderResidenceForm()
    {
        $user = Auth::user();

        if ($user->hasRole('provider_residence')) {
            return redirect()->route('provider.residence.dashboard')
                ->with('info', 'Anda sudah terdaftar sebagai Penjual Kos-Kosan.');
        }

        $status = $user->provider_status ?? 'none';
        return view('user.role-switch.become-provider-residence', compact('status', 'user'));
    }

    public function becomeProviderResidenceSubmit(Request $request)
    {
        $user = Auth::user();

        if ($user->hasRole('provider_residence')) {
            return redirect()->route('provider.residence.dashboard');
        }

        if ($user->provider_status === 'pending') {
            return back()->with('info', 'Pengajuan Anda sedang dalam proses peninjauan admin.');
        }

        $request->validate([
            'provider_nik'    => ['required', 'digits:16'],
            'provider_ktp'    => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'provider_selfie' => ['required', 'string'],
        ], [
            'provider_nik.required'    => 'NIK wajib diisi.',
            'provider_nik.digits'      => 'NIK harus 16 digit.',
            'provider_ktp.required'    => 'Foto KTP wajib diunggah.',
            'provider_ktp.image'       => 'File KTP harus berupa gambar.',
            'provider_ktp.max'         => 'Ukuran foto KTP maksimal 2MB.',
            'provider_selfie.required' => 'Foto selfie wajib diambil.',
        ]);

        $ktpPath = $request->file('provider_ktp')->store('provider-ktp', 'public');

        $selfieBase64 = $request->provider_selfie;
        if (!preg_match('/^data:image\/(\w+);base64,/', $selfieBase64, $matches)) {
            return back()->withInput()
                ->withErrors(['provider_selfie' => 'Format foto selfie tidak valid.']);
        }
        $imageData  = base64_decode(substr($selfieBase64, strpos($selfieBase64, ',') + 1));
        $extension  = $matches[1] === 'jpeg' ? 'jpg' : $matches[1];
        $selfieName = 'provider-selfie/' . uniqid('selfie_', true) . '.' . $extension;
        Storage::disk('public')->put($selfieName, $imageData);

        $user->update([
            'provider_nik'    => $request->provider_nik,
            'provider_ktp'    => $ktpPath,
            'provider_selfie' => $selfieName,
            'provider_status' => 'pending',
            'pending_role'    => 'provider_residence',
        ]);

        return redirect()->route('user.profile.show')
            ->with('success', 'Pengajuan sebagai Penjual Kos-Kosan berhasil dikirim. Tunggu verifikasi admin.');
    }
}
