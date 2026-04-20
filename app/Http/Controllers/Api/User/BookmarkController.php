<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\Bookmark;
use App\Models\Residence;
use App\Models\Activity;
use Illuminate\Http\Request;

class BookmarkController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 12);
        $type = $request->get('type'); // 'residence', 'activity', or null for all

        $query = auth()->user()->bookmarks()
            ->with('bookmarkable')
            ->when($type, function ($q) use ($type) {
                $modelClass = $type === 'residence' ? Residence::class : Activity::class;
                $q->where('bookmarkable_type', $modelClass);
            })
            ->orderBy('created_at', 'desc');

        $bookmarks = $query->paginate($perPage);

        return response()->json([
            'status' => 'success',
            'data' => [
                'bookmarks' => [
                    'data' => $bookmarks->getCollection()->map(function ($bookmark) {
                        $bookmarkable = $bookmark->bookmarkable;

                        return [
                            'id' => $bookmark->id,
                            'type' => class_basename($bookmark->bookmarkable_type),
                            'created_at' => $bookmark->created_at,
                            'item' => [
                                'id' => $bookmarkable->id,
                                'name' => $bookmarkable->name,
                                'description' => $bookmarkable->description,
                                'price' => $bookmarkable->price,
                                'images' => $bookmarkable->images,
                                'address' => $bookmarkable->address ?? $bookmarkable->location ?? null,
                                'is_active' => $bookmarkable->is_active,
                                'available_slots' => $bookmarkable->available_slots,
                                'created_at' => $bookmarkable->created_at,
                            ]
                        ];
                    }),
                    'pagination' => [
                        'current_page' => $bookmarks->currentPage(),
                        'last_page' => $bookmarks->lastPage(),
                        'per_page' => $bookmarks->perPage(),
                        'total' => $bookmarks->total(),
                        'from' => $bookmarks->firstItem(),
                        'to' => $bookmarks->lastItem(),
                    ]
                ]
            ]
        ], 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:residence,activity',
            'id' => 'required|integer'
        ]);

        $type = $request->type;
        $id = $request->id;

        // Determine the model class
        $modelClass = $type === 'residence' ? Residence::class : Activity::class;

        // Check if item exists
        $item = $modelClass::findOrFail($id);

        // Check if already bookmarked
        $existingBookmark = auth()->user()->bookmarks()
            ->where('bookmarkable_type', $modelClass)
            ->where('bookmarkable_id', $id)
            ->first();

        if ($existingBookmark) {
            return response()->json([
                'status' => 'error',
                'message' => 'Item already bookmarked'
            ], 400);
        }

        // Create bookmark
        $bookmark = auth()->user()->bookmarks()->create([
            'bookmarkable_type' => $modelClass,
            'bookmarkable_id' => $id
        ]);

        $bookmark->load('bookmarkable');

        return response()->json([
            'status' => 'success',
            'message' => 'Item bookmarked successfully',
            'data' => [
                'bookmark' => [
                    'id' => $bookmark->id,
                    'type' => class_basename($bookmark->bookmarkable_type),
                    'created_at' => $bookmark->created_at,
                    'item' => [
                        'id' => $bookmark->bookmarkable->id,
                        'name' => $bookmark->bookmarkable->name,
                        'price' => $bookmark->bookmarkable->price,
                        'images' => $bookmark->bookmarkable->images,
                    ]
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

        $bookmark = auth()->user()->bookmarks()
            ->where('bookmarkable_type', $modelClass)
            ->where('bookmarkable_id', $id)
            ->first();

        if (!$bookmark) {
            return response()->json([
                'status' => 'error',
                'message' => 'Bookmark not found'
            ], 404);
        }

        $bookmark->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Bookmark removed successfully'
        ], 200);
    }

    public function toggle(Request $request)
    {
        $request->validate([
            'type' => 'required|in:residence,activity',
            'id' => 'required|integer'
        ]);

        $type = $request->type;
        $id = $request->id;

        $modelClass = $type === 'residence' ? Residence::class : Activity::class;

        // Check if item exists
        $item = $modelClass::findOrFail($id);

        $bookmark = auth()->user()->bookmarks()
            ->where('bookmarkable_type', $modelClass)
            ->where('bookmarkable_id', $id)
            ->first();

        if ($bookmark) {
            // Remove bookmark
            $bookmark->delete();
            $action = 'removed';
        } else {
            // Add bookmark
            $bookmark = auth()->user()->bookmarks()->create([
                'bookmarkable_type' => $modelClass,
                'bookmarkable_id' => $id
            ]);
            $action = 'added';
        }

        return response()->json([
            'status' => 'success',
            'message' => "Bookmark {$action} successfully",
            'data' => [
                'is_bookmarked' => $action === 'added',
                'bookmark_id' => $action === 'added' ? $bookmark->id : null,
            ]
        ], 200);
    }
}
