<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreActivityRequest;
use App\Http\Requests\UpdateActivityRequest;
use App\Models\Activity;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ActivityController extends Controller
{
    public function index(Request $request)
    {
        $query = Activity::where('provider_id', auth()->id())
            ->with(['category'])
            ->withCount(['bookings', 'ratings'])
            ->withAvg('ratings', 'rating');

        // Filters
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true)
                      ->where('registration_deadline', '>', now());
            } elseif ($request->status === 'inactive') {
                $query->where(function ($q) {
                    $q->where('is_active', false)
                      ->orWhere('registration_deadline', '<=', now());
                });
            }
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $activities = $query->orderBy('event_date', 'asc')->paginate(10);
        $categories = Category::where('type', 'activity')->get();

        return view('provider.activities.index', compact('activities', 'categories'));
    }

    public function show(Activity $activity)
    {
        $this->authorize('view', $activity);

        $activity->load(['category', 'bookings.user', 'ratings.user']);

        return view('provider.activities.show', compact('activity'));
    }

    public function create()
    {
        $categories = Category::where('type', 'activity')->get();

        return view('provider.activities.create', compact('categories'));
    }

    public function store(StoreActivityRequest $request)
    {
        try {
            $data = $request->validated();
            $data['provider_id'] = auth()->id();

            // Handle image uploads
            if ($request->hasFile('images')) {
                $images = [];
                foreach ($request->file('images') as $image) {
                    $path = $image->store('activities', 'public');
                    $images[] = $path;
                }
                $data['images'] = $images; // rely on $casts to store as JSON
            }

            // Set available_slots to capacity initially
            $data['available_slots'] = $data['capacity'];

            $activity = Activity::create($data);

            return redirect()->route('provider.activities.show', $activity)
                ->with('success', 'Activity berhasil ditambahkan');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal menambahkan activity: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function edit(Activity $activity)
    {
        $this->authorize('update', $activity);

        $categories = Category::where('type', 'activity')->get();

        return view('provider.activities.edit', compact('activity', 'categories'));
    }

    public function update(UpdateActivityRequest $request, Activity $activity)
    {
        $this->authorize('update', $activity);

        try {
            $data = $request->validated();

            // Handle image uploads
            if ($request->hasFile('images')) {
                // Delete old images
                $oldImages = $activity->images ?? [];
                foreach ($oldImages as $oldImage) {
                    Storage::disk('public')->delete($oldImage);
                }

                $images = [];
                foreach ($request->file('images') as $image) {
                    $path = $image->store('activities', 'public');
                    $images[] = $path;
                }
                $data['images'] = $images; // rely on $casts to store as JSON
            }

            $activity->update($data);

            return redirect()->route('provider.activities.show', $activity)
                ->with('success', 'Activity berhasil diupdate');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal mengupdate activity: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function destroy(Activity $activity)
    {
        $this->authorize('delete', $activity);

        try {
            // Check if there are active bookings
            $activeBookings = $activity->bookings()
                ->whereIn('status', ['pending', 'approved'])
                ->count();

            if ($activeBookings > 0) {
                return redirect()->back()
                    ->with('error', 'Tidak dapat menghapus activity dengan booking aktif');
            }

            // Delete images
            $images = $activity->images ?? [];
            foreach ($images as $image) {
                Storage::disk('public')->delete($image);
            }

            $activity->delete();

            return redirect()->route('provider.activities.index')
                ->with('success', 'Activity berhasil dihapus');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal menghapus activity: ' . $e->getMessage());
        }
    }

    public function toggleStatus(Activity $activity)
    {
        $this->authorize('update', $activity);

        $activity->update([
            'is_active' => !$activity->is_active
        ]);

        $status = $activity->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()->back()
            ->with('success', "Activity berhasil {$status}");
    }
}

