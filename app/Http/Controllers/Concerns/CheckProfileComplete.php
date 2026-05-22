<?php

namespace App\Http\Controllers\Concerns;

/**
 * Trait CheckProfileComplete
 *
 * Dipakai di:
 * - Provider/ResidenceController
 * - Provider/ActivityController
 * - MarketplaceController (untuk seller)
 *
 * Cek apakah user sudah melengkapi field wajib profil:
 * - phone
 * - address
 */
trait CheckProfileComplete
{
    /**
     * Cek apakah profil user sudah lengkap.
     * Jika belum, return redirect ke halaman edit profil.
     * Jika sudah, return null (lanjutkan proses).
     */
    protected function checkProfileComplete(?string $redirectBackRoute = null): ?\Illuminate\Http\RedirectResponse
    {
        $user = auth()->user();

        $missingFields = [];

        if (empty($user->phone)) {
            $missingFields[] = 'nomor telepon';
        }

        if (empty($user->address)) {
            $missingFields[] = 'alamat';
        }

        if (empty($missingFields)) {
            return null; // profil sudah lengkap, lanjutkan
        }

        $fieldList = implode(' dan ', $missingFields);

        return redirect()->route('user.profile.edit')
            ->with('profile_incomplete', "Lengkapi {$fieldList} kamu terlebih dahulu sebelum melanjutkan.")
            ->with('redirect_after_profile', $redirectBackRoute ?? url()->previous());
    }
}