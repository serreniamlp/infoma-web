<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Residence;
use App\Http\Resources\ResidenceResource;
use Illuminate\Http\Request;

class ResidenceApiController extends Controller
{
    public function index(Request $request)
    {
        $query = Residence::with(['provider', 'category'])
            ->where('is_active', true)
            ->withAvg('ratings', 'rating');

        if ($request->filled('search')) {
            $keyword = $request->search;
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                    ->orWhere('address', 'like', "%{$keyword}%");
            });
        }
        // Apply filters
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('residence_type')) {
            $query->where('residence_type', $request->residence_type);
        }

        if ($request->filled('kos_type')) {
            $query->where('kos_type', $request->kos_type);
        }

        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        if ($request->filled('available_only') && $request->available_only) {
            $query->where('available_slots', '>', 0);
        }

        // Apply sorting
        if ($request->filled('sort')) {
            if ($request->sort === 'price_asc') {
                $query->orderBy('price', 'asc');
            } elseif ($request->sort === 'price_desc') {
                $query->orderBy('price', 'desc');
            } elseif ($request->sort === 'rating_desc') {
                $query->orderBy('ratings_avg_rating', 'desc');
            } else {
                $query->orderBy('created_at', 'desc');
            }
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $residences = $query->paginate(12);

        return ResidenceResource::collection($residences);
    }

    public function show(Residence $residence)
    {
        $residence->load(['provider', 'category', 'ratings.user']);

        $hasActiveBooking = false;
        if (auth()->check()) {
            $hasActiveBooking = \App\Models\Booking::where('user_id', auth()->id())
                ->where('bookable_type', \App\Models\Residence::class)
                ->where('bookable_id', $residence->id)
                ->whereIn('status', ['pending', 'approved'])
                ->exists();
        }

        $data = (new \App\Http\Resources\ResidenceResource($residence))->toArray(request());
        $data['has_active_booking'] = $hasActiveBooking;

        return response()->json(['data' => $data]);
    }
}
