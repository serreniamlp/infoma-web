<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\Rating;
use App\Models\Residence;
use App\Models\Activity;
use App\Services\NotificationService;
use Illuminate\Http\Request;

/**
 * RatingReplyController
 *
 * Provider dapat membalas ulasan yang ditulis oleh user
 * untuk hunian atau acara milik mereka.
 *
 * Proteksi: provider hanya bisa membalas ulasan untuk
 * hunian/acara yang dimilikinya sendiri.
 */
class RatingReplyController extends Controller
{
    /**
     * Simpan atau update balasan provider terhadap ulasan user.
     *
     * POST /provider/{type}/ratings/{rating}/reply
     */
    public function reply(Request $request, Rating $rating)
    {
        $request->validate([
            'provider_reply' => 'required|string|max:1000',
        ], [
            'provider_reply.required' => 'Balasan tidak boleh kosong.',
            'provider_reply.max'      => 'Balasan maksimal 1000 karakter.',
        ]);

        // Pastikan rating ini milik hunian/acara yang dimiliki provider yang login
        if (!$this->providerOwnsRating($rating)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Anda tidak memiliki akses untuk membalas ulasan ini.',
            ], 403);
        }

        $isNewReply = !$rating->provider_reply; // true = balasan pertama (bukan edit)

        $rating->update(['provider_reply' => $request->provider_reply]);

        // Kirim notifikasi ke customer — hanya saat pertama kali balas (bukan edit)
        if ($isNewReply) {
            $rating->loadMissing('rateable');
            $item        = $rating->rateable;
            $itemName    = $item?->name ?? 'listing';
            $provider    = auth()->user();

            // URL halaman detail di sisi user (berbeda untuk residence vs activity)
            if ($rating->rateable_type === Residence::class) {
                $userUrl = url("/residences/{$rating->rateable_id}");
            } else {
                $userUrl = url("/activities/{$rating->rateable_id}");
            }

            NotificationService::balasanUlasan(
                $rating->user_id,
                $provider->name,
                $itemName,
                $userUrl
            );
        }

        return response()->json([
            'status'         => 'success',
            'message'        => 'Balasan berhasil disimpan.',
            'provider_reply' => $rating->provider_reply,
        ]);
    }

    /**
     * Hapus balasan provider.
     *
     * DELETE /provider/{type}/ratings/{rating}/reply
     */
    public function deleteReply(Rating $rating)
    {
        if (!$this->providerOwnsRating($rating)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Anda tidak memiliki akses untuk menghapus balasan ini.',
            ], 403);
        }

        $rating->update(['provider_reply' => null]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Balasan berhasil dihapus.',
        ]);
    }

    /**
     * Cek apakah provider yang login memiliki hunian/acara yang diberi rating ini.
     */
    private function providerOwnsRating(Rating $rating): bool
    {
        $providerId = auth()->id();

        // Rating untuk hunian
        if ($rating->rateable_type === Residence::class) {
            return Residence::where('id', $rating->rateable_id)
                ->where('provider_id', $providerId)
                ->exists();
        }

        // Rating untuk acara
        if ($rating->rateable_type === Activity::class) {
            return Activity::where('id', $rating->rateable_id)
                ->where('provider_id', $providerId)
                ->exists();
        }

        return false;
    }
}
