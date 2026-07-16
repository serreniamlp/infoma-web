<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * FcmTokenController
 *
 * Endpoint untuk Flutter menyimpan / menghapus FCM device token ke server.
 * Digunakan oleh SEMUA role (user, provider_residence, provider_event).
 *
 * Flutter wajib memanggil POST endpoint ini setiap kali:
 *   1. User berhasil login
 *   2. Firebase SDK menerbitkan token baru (onTokenRefresh callback)
 *
 * Flutter wajib memanggil DELETE endpoint ini saat:
 *   1. User logout — agar notif tidak dikirim ke device yang sudah logout
 */
class FcmTokenController extends Controller
{
    /**
     * Simpan atau perbarui FCM token.
     *
     * POST /api/v1/fcm-token
     * Authorization: Bearer {sanctum_token}
     *
     * Request body:
     * {
     *   "fcm_token": "string — FCM device token dari Firebase SDK"
     * }
     */
    public function update(Request $request)
    {
        $request->validate([
            'fcm_token' => ['required', 'string', 'min:10', 'max:500'],
        ], [
            'fcm_token.required' => 'FCM token wajib diisi.',
            'fcm_token.min'      => 'FCM token tidak valid.',
            'fcm_token.max'      => 'FCM token terlalu panjang.',
        ]);

        $user = Auth::user();

        // Jika token sudah sama, tidak perlu update — hemat query
        if ($user->fcm_token === $request->fcm_token) {
            return response()->json([
                'status'  => 'success',
                'message' => 'FCM token tidak berubah.',
            ]);
        }

        $user->update(['fcm_token' => $request->fcm_token]);

        return response()->json([
            'status'  => 'success',
            'message' => 'FCM token berhasil diperbarui.',
        ]);
    }

    /**
     * Hapus FCM token saat user logout.
     *
     * DELETE /api/v1/fcm-token
     * Authorization: Bearer {sanctum_token}
     *
     * Setelah token dihapus, push notification tidak akan dikirim
     * ke device ini sampai user login kembali dan kirim token baru.
     */
    public function destroy()
    {
        Auth::user()->update(['fcm_token' => null]);

        return response()->json([
            'status'  => 'success',
            'message' => 'FCM token berhasil dihapus.',
        ]);
    }
}
