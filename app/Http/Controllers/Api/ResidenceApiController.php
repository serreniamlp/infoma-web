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

        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        if ($request->filled('available_only') && $request->available_only) {
            $query->where('available_slots', '>', 0);
        }

        $query->orderBy('created_at', 'desc');
        $residences = $query->paginate(12);

        return ResidenceResource::collection($residences);
    }

    public function show(Residence $residence)
    {
        $residence->load(['provider', 'category', 'ratings.user']);

        return new ResidenceResource($residence);
    }
}
