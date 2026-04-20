<?php

namespace App\Http\Controllers;

use App\Models\Residence;
use App\Models\Activity;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function index()
    {
        // Check if user is authenticated and redirect to appropriate dashboard
        if (auth()->check()) {
            $user = auth()->user();

            // Redirect based on user role
            if ($user->hasRole('admin')) {
                return redirect()->route('admin.dashboard');
            } elseif ($user->hasRole('provider')) {
                return redirect()->route('provider.dashboard');
            } elseif ($user->hasRole('user')) {
                return redirect()->route('user.dashboard');
            }
        }

        // Get featured residences and activities for guest users
        $featuredResidences = Residence::with(['provider', 'category'])
            ->where('is_active', true)
            ->where('available_slots', '>', 0)
            ->withAvg('ratings', 'rating')
            ->orderBy('created_at', 'desc')
            ->limit(6)
            ->get();

        $featuredActivities = Activity::with(['provider', 'category'])
            ->where('is_active', true)
            ->where('available_slots', '>', 0)
            ->where('registration_deadline', '>', now())
            ->withAvg('ratings', 'rating')
            ->orderBy('event_date', 'asc')
            ->limit(6)
            ->get();

        $categories = Category::all();

        return view('home/index', compact('featuredResidences', 'featuredActivities', 'categories'));
    }

    public function search(Request $request)
    {
        $query = $request->get('q', '');
        $type = $request->get('type', 'all'); // all, residence, activity

        $residences = collect();
        $activities = collect();

        if ($type === 'all' || $type === 'residence') {
            $residences = Residence::with(['provider', 'category'])
                ->where('is_active', true)
                ->when($query, function ($q) use ($query) {
                    $q->where('name', 'LIKE', "%{$query}%")
                      ->orWhere('description', 'LIKE', "%{$query}%")
                      ->orWhere('address', 'LIKE', "%{$query}%");
                })
                ->withAvg('ratings', 'rating')
                ->paginate(12);
        }

        if ($type === 'all' || $type === 'activity') {
            $activities = Activity::with(['provider', 'category'])
                ->where('is_active', true)
                ->where('registration_deadline', '>', now())
                ->when($query, function ($q) use ($query) {
                    $q->where('name', 'LIKE', "%{$query}%")
                      ->orWhere('description', 'LIKE', "%{$query}%")
                      ->orWhere('location', 'LIKE', "%{$query}%");
                })
                ->withAvg('ratings', 'rating')
                ->paginate(12);
        }

        return view('search', compact('residences', 'activities', 'query', 'type'));
    }
}
