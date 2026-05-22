<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Auth\RegisterController;

class VerificationTermsController extends Controller
{
    /**
     * Tampilkan halaman Syarat & Ketentuan.
     *
     * 2 konteks pemanggilan:
     * A) Dari register provider  → belum login, redirect_to = 'register.complete'
     * B) Dari seller/provider dashboard → sudah login, cek hasAcceptedTerms()
     */
    public function show(Request $request, string $type = 'seller')
    {
        // Konteks B: user sudah login & sudah pernah accept terms → skip
        if (auth()->check() && auth()->user()->hasAcceptedTerms()) {
            $redirectTo = $request->query('redirect_to', '/');
            if ($redirectTo !== 'register.complete') {
                return redirect($redirectTo);
            }
        }

        // Konteks A: dari register provider — cek session pending_provider masih ada
        $redirectTo = $request->query('redirect_to', '/');
        if ($redirectTo === 'register.complete') {
            $pending = session('pending_provider');
            if (!$pending) {
                return redirect()->route('register')
                    ->with('error', 'Sesi pendaftaran tidak ditemukan. Silakan daftar ulang.');
            }
        }

        return view('verification.terms', compact('type', 'redirectTo'));
    }

    /**
     * Simpan persetujuan S&K.
     *
     * Konteks A (register provider) → panggil completeProviderRegistration()
     * Konteks B (user sudah login)  → update terms_accepted_at, redirect balik
     */
    public function accept(Request $request)
    {
        $request->validate([
            'agree'       => 'required|accepted',
            'type'        => 'required|in:seller,provider',
            'redirect_to' => 'nullable|string',
        ], [
            'agree.accepted' => 'Kamu harus mencentang persetujuan untuk melanjutkan.',
        ]);

        $redirectTo = $request->input('redirect_to');

        // ── Konteks A: selesaikan registrasi provider ─────────────────────
        if ($redirectTo === 'register.complete') {
            $registerController = app(RegisterController::class);
            return $registerController->completeProviderRegistration();
        }

        // ── Konteks B: user sudah login, simpan terms_accepted_at ─────────
        if (auth()->check()) {
            auth()->user()->update([
                'terms_accepted_at' => now(),
            ]);
        }

        return redirect($redirectTo ?: '/')
            ->with('success', 'Terima kasih telah menyetujui syarat & ketentuan EduLiving.');
    }
}