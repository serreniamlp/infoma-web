<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Rating;
use App\Models\Residence;
use App\Models\Activity;
use Illuminate\Http\Request;

class RatingController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:residence,activity',
            'id' => 'required|integer',
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'nullable|string|max:1000'
        ]);

        $type = $request->type;
        $id = $request->id;

        $modelClass = $type === 'residence' ? Residence::class : Activity::class;

        // Check if item exists
        $item = $modelClass::findOrFail($id);

        // Check if user has approved booking and paid transaction for this item
        $eligibleToRate = auth()->user()->bookings()
            ->where('bookable_type', $modelClass)
            ->where('bookable_id', $id)
            ->where('status', 'approved')
            ->whereHas('transaction', function ($q) {
                $q->where('payment_status', 'paid');
            })
            ->exists();

        if (!$eligibleToRate) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda hanya dapat memberikan rating setelah booking disetujui dan dibayar'
            ], 403);
        }

        // Check if user already rated this item
        $existingRating = Rating::where('user_id', auth()->id())
            ->where('rateable_type', $modelClass)
            ->where('rateable_id', $id)
            ->first();

        if ($existingRating) {
            // Update existing rating
            $existingRating->update([
                'rating' => $request->rating,
                'review' => $request->review
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Rating berhasil diupdate',
                'data' => [
                    'rating' => [
                        'id' => $existingRating->id,
                        'rating' => $existingRating->rating,
                        'review' => $existingRating->review,
                        'created_at' => $existingRating->created_at,
                        'updated_at' => $existingRating->updated_at,
                    ]
                ]
            ], 200);
        }

        // Create new rating
        $rating = Rating::create([
            'user_id' => auth()->id(),
            'rateable_type' => $modelClass,
            'rateable_id' => $id,
            'rating' => $request->rating,
            'review' => $request->review
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Rating berhasil diberikan',
            'data' => [
                'rating' => [
                    'id' => $rating->id,
                    'rating' => $rating->rating,
                    'review' => $rating->review,
                    'created_at' => $rating->created_at,
                    'updated_at' => $rating->updated_at,
                ]
            ]
        ], 201);
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'type' => 'required|in:residence,activity',
            'id' => 'required|integer'
        ]);

        $type = $request->type;
        $id = $request->id;

        $modelClass = $type === 'residence' ? Residence::class : Activity::class;

        $rating = Rating::where('user_id', auth()->id())
            ->where('rateable_type', $modelClass)
            ->where('rateable_id', $id)
            ->first();

        if (!$rating) {
            return response()->json([
                'status' => 'error',
                'message' => 'Rating tidak ditemukan'
            ], 404);
        }

        $rating->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Rating berhasil dihapus'
        ], 200);
    }

    public function show(Request $request)
    {
        $request->validate([
            'type' => 'required|in:residence,activity',
            'id' => 'required|integer'
        ]);

        $type = $request->type;
        $id = $request->id;

        $modelClass = $type === 'residence' ? Residence::class : Activity::class;

        $rating = Rating::where('user_id', auth()->id())
            ->where('rateable_type', $modelClass)
            ->where('rateable_id', $id)
            ->first();

        if (!$rating) {
            return response()->json([
                'status' => 'error',
                'message' => 'Rating tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'rating' => [
                    'id' => $rating->id,
                    'rating' => $rating->rating,
                    'review' => $rating->review,
                    'created_at' => $rating->created_at,
                    'updated_at' => $rating->updated_at,
                ]
            ]
        ], 200);
    }
}



