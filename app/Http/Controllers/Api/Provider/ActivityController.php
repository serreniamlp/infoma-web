<?php
// app/Http/Controllers/Api/Provider/ActivityController.php
// TULIS ULANG

namespace App\Http\Controllers\Api\Provider;

use App\Http\Controllers\Controller;
use App\Http\Resources\ActivityResource;
use App\Models\Activity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ActivityController extends Controller
{
    private function checkApproved()
    {
        if (Auth::user()->provider_status !== 'approved') {
            return response()->json([
                'status'  => 'error',
                'message' => 'Akun kamu belum diverifikasi admin.',
            ], 403);
        }
        return null;
    }

    public function index()
    {
        $activities = Activity::with(['category'])
            ->where('provider_id', Auth::id())
            ->withCount('bookings')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return ActivityResource::collection($activities);
    }

    public function show(Activity $activity)
    {
        if ($activity->provider_id !== Auth::id()) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }

        return response()->json([
            'status' => 'success',
            'data'   => new ActivityResource($activity->load('category')),
        ]);
    }

    public function store(Request $request)
    {
        if ($err = $this->checkApproved()) return $err;

        $request->validate([
            'name'                  => 'required|string|max:255',
            'description'           => 'required|string',
            'category_id'           => 'required|exists:categories,id',
            'location'              => 'required|string|max:500',
            'event_date'            => 'required|date|after:now',
            'registration_deadline' => 'required|date|before:event_date',
            'price'                 => 'required|numeric|min:0',
            'capacity'              => 'required|integer|min:1',
            'images'                => 'required|array|max:10',
            'images.*'              => 'image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $data                    = $request->except(['images']);
        $data['provider_id']     = Auth::id();
        $data['available_slots'] = $request->capacity;
        $data['is_active']       = true;

        if ($request->hasFile('images')) {
            $images = [];
            foreach ($request->file('images') as $img) {
                $images[] = $img->store('activities', 'public');
            }
            $data['images'] = $images;
        }

        $activity = Activity::create($data);

        return response()->json([
            'status'  => 'success',
            'message' => 'Event berhasil ditambahkan.',
            'data'    => new ActivityResource($activity->load('category')),
        ], 201);
    }

    public function update(Request $request, Activity $activity)
    {
        if ($activity->provider_id !== Auth::id()) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }

        $activity->update($request->except(['images', '_method']));

        return response()->json([
            'status'  => 'success',
            'message' => 'Event berhasil diupdate.',
            'data'    => new ActivityResource($activity->load('category')),
        ]);
    }

    public function destroy(Activity $activity)
    {
        if ($activity->provider_id !== Auth::id()) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }

        $activity->delete();

        return response()->json(['status' => 'success', 'message' => 'Event berhasil dihapus.']);
    }

    public function toggleStatus(Activity $activity)
    {
        if ($activity->provider_id !== Auth::id()) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }

        $activity->update(['is_active' => !$activity->is_active]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Status event berhasil diubah.',
            'data'    => ['is_active' => $activity->is_active],
        ]);
    }
}