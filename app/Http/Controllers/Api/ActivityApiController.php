<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Http\Resources\ActivityResource;
use Illuminate\Http\Request;

class ActivityApiController extends Controller
{
    public function index(Request $request)
    {
        $query = Activity::with(['provider', 'category'])
            ->where('is_active', true)
            ->where('registration_deadline', '>', now())
            ->withAvg('ratings', 'rating');

        // Apply filters
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        if ($request->filled('date_from')) {
            $query->where('event_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('event_date', '<=', $request->date_to);
        }

        if ($request->filled('available_only') && $request->available_only) {
            $query->where('available_slots', '>', 0);
        }

        $activities = $query->paginate(12);

        return ActivityResource::collection($activities);
    }

    public function show(Activity $activity)
    {
        $activity->load(['provider', 'category', 'ratings.user']);

        return new ActivityResource($activity);
    }
}

