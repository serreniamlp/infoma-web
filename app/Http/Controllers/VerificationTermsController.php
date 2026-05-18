<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class VerificationTermsController extends Controller
{
    /**
     * Tampilkan halaman Syarat & Ketentuan.
     * Dipanggil dari: /user/marketplace/sell dan /provider//dashboard
     *
     * @param string $type  'seller' | 'provider'
     */
    public function show(Request $request, string $type = 'seller')
    {
        $user = auth()->user();

        // Kalau sudah accept terms sebelumnya, langsung redirect
        if ($user->hasAcceptedTerms()) {
            return redirect($request->query('redirect_to', '/'));
        }

        $redirectTo = $request->query('redirect_to', '/');

        return view('verification.terms', compact('type', 'redirectTo'));
    }

    /**
     * Simpan persetujuan S&K user ke database.
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

        auth()->user()->update([
            'terms_accepted_at' => now(),
        ]);

        $redirectTo = $request->input('redirect_to') ?: '/';

        return redirect($redirectTo)->with('success', 'Terima kasih telah menyetujui syarat & ketentuan EduLiving.');
    }
}