<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Bookmark;
use App\Models\Residence;
use App\Models\Activity;
use App\Models\MarketplaceProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookmarkController extends Controller
{
    public function index(Request $request)
    {
        $query = Auth::user()->bookmarks()->with('bookmarkable');

        // Filter by type
        if ($request->type && $request->type !== 'all') {
            switch ($request->type) {
                case 'residence':
                    $query->where('bookmarkable_type', 'App\\Models\\Residence');
                    break;
                case 'activity':
                    $query->where('bookmarkable_type', 'App\\Models\\Activity');
                    break;
                case 'marketplace':
                    $query->where('bookmarkable_type', 'App\\Models\\MarketplaceProduct');
                    break;
            }
        }

        $bookmarks = $query->orderBy('created_at', 'desc')->paginate(12);

        return view('user.bookmarks.index', compact('bookmarks'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:residence,activity,marketplace',
            'id' => 'required|integer'
        ]);

        $type = $request->type;
        $id = $request->id;

        // Determine the model class
        switch ($type) {
            case 'residence':
                $modelClass = Residence::class;
                break;
            case 'activity':
                $modelClass = Activity::class;
                break;
            case 'marketplace':
                $modelClass = \App\Models\MarketplaceProduct::class;
                break;
            default:
                return response()->json(['message' => 'Tipe tidak valid'], 400);
        }

        // Check if item exists
        $item = $modelClass::findOrFail($id);

        // Check if already bookmarked
        $existingBookmark = Auth::user()->bookmarks()
            ->where('bookmarkable_type', $modelClass)
            ->where('bookmarkable_id', $id)
            ->first();

        if ($existingBookmark) {
            return response()->json(['message' => 'Item sudah ada di bookmark'], 400);
        }

        // Create bookmark
        Auth::user()->bookmarks()->create([
            'bookmarkable_type' => $modelClass,
            'bookmarkable_id' => $id
        ]);

        return response()->json(['message' => 'Item berhasil ditambahkan ke bookmark']);
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'type' => 'required|in:residence,activity,marketplace',
            'id' => 'required|integer'
        ]);

        $type = $request->type;
        $id = $request->id;

        switch ($type) {
            case 'residence':
                $modelClass = Residence::class;
                break;
            case 'activity':
                $modelClass = Activity::class;
                break;
            case 'marketplace':
                $modelClass = \App\Models\MarketplaceProduct::class;
                break;
            default:
                return response()->json(['message' => 'Tipe tidak valid'], 400);
        }

        $bookmark = Auth::user()->bookmarks()
            ->where('bookmarkable_type', $modelClass)
            ->where('bookmarkable_id', $id)
            ->first();

        if (!$bookmark) {
            return response()->json(['message' => 'Bookmark tidak ditemukan'], 404);
        }

        $bookmark->delete();

        return response()->json(['message' => 'Item berhasil dihapus dari bookmark']);
    }
}



