<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * FcmTokenController — Endpoint untuk Flutter menyimpan FCM device token ke server.
 *
 * Flutter wajib memanggil endpoint ini setiap kali:
 * 1. User berhasil login
 * 2. Firebase menerbitkan token baru (onTokenRefresh)
 *
 * Endpoint: POST /api/v1/user/fcm-token
 * Auth: Bearer token (Sanctum)
 */
class FcmTokenController extends Controller
{
    /**
     * Simpan atau perbarui FCM token untuk user yang sedang login.
     *
     * Request body:
     * {
     *   "fcm_token": "string — FCM device token dari Firebase SDK"
     * }
     *
     * Response sukses:
     * {
     *   "status": "success",
     *   "message": "FCM token berhasil diperbarui."
     * }
     */
    public function update(Request $request)
    {
        $request->validate([
            'fcm_token' => ['required', 'string', 'min:10', 'max:500'],
        ]);

        $user = Auth::user();

        // Jika token sudah sama, tidak perlu update ke database
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
     * Hapus FCM token saat user logout (opsional — dipanggil dari Flutter sebelum logout).
     *
     * Berguna agar notifikasi tidak dikirim ke device setelah user logout.
     *
     * Endpoint: DELETE /api/v1/user/fcm-token
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
