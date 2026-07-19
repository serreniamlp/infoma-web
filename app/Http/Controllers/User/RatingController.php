<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Rating;
use App\Models\Residence;
use App\Models\Activity;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class RatingController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'type'      => 'required|in:residence,activity',
            'id'        => 'required|integer',
            'rating'    => 'required|integer|min:1|max:5',
            'review'    => 'nullable|string|max:1000',
            'photos'    => 'nullable|array|max:3',
            'photos.*'  => 'image|mimes:jpg,jpeg,png,webp|max:5120', // tiap foto maks 5MB
        ], [
            'photos.max'    => 'Maksimal 3 foto.',
            'photos.*.image'=> 'Setiap file harus berupa gambar.',
            'photos.*.max'  => 'Ukuran tiap foto maksimal 5MB.',
        ]);

        $type = $request->type;
        $id   = $request->id;

        $modelClass = $type === 'residence' ? Residence::class : Activity::class;

        // Pastikan item ada
        $item = $modelClass::findOrFail($id);

        // Cek kelayakan: harus punya booking approved + transaksi paid
        $booking = auth()->user()->bookings()
            ->where('bookable_type', $modelClass)
            ->where('bookable_id', $id)
            ->where('status', 'approved')
            ->whereHas('transaction', function ($q) {
                $q->where('payment_status', 'paid');
            })
            ->first();

        if (!$booking) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Anda hanya dapat memberikan rating setelah booking disetujui dan dibayar',
            ], 403);
        }

        // Khusus Residence: batasi minimal H-7 sewa berakhir
        if ($modelClass === Residence::class) {
            $checkoutDate = \Carbon\Carbon::parse($booking->check_out_date);
            $ratingStartDate = $checkoutDate->copy()->subDays(7);

            if (now()->startOfDay()->lt($ratingStartDate->startOfDay())) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Anda baru dapat memberikan rating mulai tanggal ' . $ratingStartDate->translatedFormat('d M Y') . ' (H-7 sebelum sewa berakhir)',
                ], 403);
            }
        }

        // Cek apakah user sudah pernah memberi rating
        $existingRating = Rating::where('user_id', auth()->id())
            ->where('rateable_type', $modelClass)
            ->where('rateable_id', $id)
            ->first();

        // Upload foto baru jika ada
        $newPhotoPaths = null;
        if ($request->hasFile('photos')) {
            // Hapus foto lama dari storage jika ini update
            if ($existingRating?->photo_path) {
                $oldPaths = json_decode($existingRating->photo_path, true) ?? [$existingRating->photo_path];
                foreach ($oldPaths as $old) {
                    Storage::disk('public')->delete($old);
                }
            }
            $newPhotoPaths = [];
            foreach (array_slice($request->file('photos'), 0, 3) as $file) {
                $newPhotoPaths[] = $file->store('ratings', 'public');
            }
            $newPhotoPaths = json_encode($newPhotoPaths);
        }

        if ($existingRating) {
            $existingRating->update([
                'rating'     => $request->rating,
                'review'     => $request->review,
                'photo_path' => $newPhotoPaths ?? $existingRating->photo_path,
            ]);

            return response()->json([
                'status'  => 'success',
                'message' => 'Rating berhasil diupdate',
                'data'    => $this->formatRating($existingRating->fresh()),
            ], 200);
        }

        // Buat rating baru
        $rating = Rating::create([
            'user_id'       => auth()->id(),
            'rateable_type' => $modelClass,
            'rateable_id'   => $id,
            'rating'        => $request->rating,
            'review'        => $request->review,
            'photo_path'    => $newPhotoPaths,
        ]);

        // Kirim notifikasi ke provider
        $providerId = $item->provider_id ?? null;
        if ($providerId && $providerId !== auth()->id()) {
            $isResidence = ($type === 'residence');
            $providerUrl = $isResidence
                ? url("/provider/residence/residences/{$id}")
                : url("/provider/event/activities/{$id}");
            NotificationService::ulasanBaru(
                $providerId,
                auth()->user()->name,
                $item->name,
                $providerUrl
            );
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Rating berhasil diberikan',
            'data'    => $this->formatRating($rating),
        ], 201);
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'type' => 'required|in:residence,activity',
            'id'   => 'required|integer',
        ]);

        $modelClass = $request->type === 'residence' ? Residence::class : Activity::class;

        $rating = Rating::where('user_id', auth()->id())
            ->where('rateable_type', $modelClass)
            ->where('rateable_id', $request->id)
            ->first();

        if (!$rating) {
            return response()->json(['status' => 'error', 'message' => 'Rating tidak ditemukan'], 404);
        }

        // Hapus semua foto dari storage
        if ($rating->photo_path) {
            $paths = json_decode($rating->photo_path, true) ?? [$rating->photo_path];
            foreach ($paths as $path) {
                Storage::disk('public')->delete($path);
            }
        }

        $rating->delete();

        return response()->json(['status' => 'success', 'message' => 'Rating berhasil dihapus'], 200);
    }

    public function show(Request $request)
    {
        $request->validate([
            'type' => 'required|in:residence,activity',
            'id'   => 'required|integer',
        ]);

        $modelClass = $request->type === 'residence' ? Residence::class : Activity::class;

        $rating = Rating::where('user_id', auth()->id())
            ->where('rateable_type', $modelClass)
            ->where('rateable_id', $request->id)
            ->first();

        if (!$rating) {
            return response()->json(['status' => 'error', 'message' => 'Rating tidak ditemukan'], 404);
        }

        return response()->json(['status' => 'success', 'data' => $this->formatRating($rating)], 200);
    }

    private function formatRating(Rating $rating): array
    {
        // photo_path bisa JSON array atau string lama (backward compat)
        $photoPaths = null;
        if ($rating->photo_path) {
            $decoded = json_decode($rating->photo_path, true);
            $paths   = is_array($decoded) ? $decoded : [$rating->photo_path];
            $photoPaths = array_map(fn($p) => asset('storage/' . $p), $paths);
        }

        return [
            'id'             => $rating->id,
            'rating'         => $rating->rating,
            'review'         => $rating->review,
            'photo_urls'     => $photoPaths,        // array URL (bisa null)
            'provider_reply' => $rating->provider_reply,
            'created_at'     => $rating->created_at,
            'updated_at'     => $rating->updated_at,
        ];
    }
}
