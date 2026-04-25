<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use Illuminate\Http\Request;

class ActivityManagementController extends Controller
{
    public function index(Request $request)
    {
        $query = Activity::with(['provider']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%")
                  ->orWhereHas('provider', fn($q) => $q->where('name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true)->where('registration_deadline', '>', now());
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            } elseif ($request->status === 'expired') {
                $query->where('registration_deadline', '<=', now());
            }
        }

        $activities = $query->withCount('bookings')->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        $stats = [
            'total'   => Activity::count(),
            'active'  => Activity::where('is_active', true)->where('registration_deadline', '>', now())->count(),
            'expired' => Activity::where('registration_deadline', '<=', now())->count(),
        ];

        return view('admin.activities.index', compact('activities', 'stats'));
    }

    public function show(Activity $activity)
    {
        $activity->load(['provider', 'bookings.user']);
        return view('admin.activities.show', compact('activity'));
    }

    public function toggleStatus(Activity $activity)
    {
        $activity->update(['is_active' => !$activity->is_active]);
        $status = $activity->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return redirect()->back()->with('success', "Event \"{$activity->name}\" berhasil {$status}.");
    }

    public function destroy(Activity $activity)
    {
        $name = $activity->name;
        $activity->delete();
        return redirect()->route('admin.activities.index')->with('success', "Event \"{$name}\" berhasil dihapus.");
    }
}
