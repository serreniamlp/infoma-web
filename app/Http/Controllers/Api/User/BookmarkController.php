<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\Bookmark;
use App\Models\Residence;
use App\Models\Activity;
use App\Models\MarketplaceProduct;
use Illuminate\Http\Request;

class BookmarkController extends Controller
{
    // Resolve model class dari type string
    private function resolveModel(string $type): string
    {
        return match($type) {
            'residence'           => Residence::class,
            'activity'            => Activity::class,
            'marketplace_product' => MarketplaceProduct::class,
            default               => Residence::class,
        };
    }

    public function index(Request $request)
    {
        $query = auth()->user()->bookmarks()
            ->with('bookmarkable')
            ->orderBy('created_at', 'desc');

        if ($request->filled('type')) {
            $query->where('bookmarkable_type', $this->resolveModel($request->type));
        }

        $bookmarks = $query->paginate($request->get('per_page', 12));

        return response()->json([
            'status' => 'success',
            'data'   => [
                'bookmarks' => $bookmarks->getCollection()->map(function ($bookmark) {
                    $item = $bookmark->bookmarkable;
                    if (!$item) return null;

                    return [
                        'id'         => $bookmark->id,
                        'type'       => class_basename($bookmark->bookmarkable_type),
                        'created_at' => $bookmark->created_at,
                        'item'       => [
                            'id'              => $item->id,
                            'name'            => $item->name,
                            'price'           => $item->price,
                            'images'          => $item->images,
                            'is_active'       => $item->is_active ?? ($item->status === 'active'),
                            'available_slots' => $item->available_slots ?? $item->stock_quantity ?? null,
                            'address'         => $item->address ?? $item->location ?? null,
                        ],
                    ];
                })->filter()->values(),
                'pagination' => [
                    'current_page' => $bookmarks->currentPage(),
                    'last_page'    => $bookmarks->lastPage(),
                    'per_page'     => $bookmarks->perPage(),
                    'total'        => $bookmarks->total(),
                ],
            ],
        ]);
    }

    public function toggle(Request $request)
    {
        $request->validate([
            'type' => 'required|in:residence,activity,marketplace_product',
            'id'   => 'required|integer',
        ]);

        $modelClass = $this->resolveModel($request->type);

        // Pastikan item ada
        $modelClass::findOrFail($request->id);

        $bookmark = auth()->user()->bookmarks()
            ->where('bookmarkable_type', $modelClass)
            ->where('bookmarkable_id', $request->id)
            ->first();

        if ($bookmark) {
            $bookmark->delete();
            return response()->json([
                'status' => 'success',
                'data'   => [
                    'is_bookmarked' => false,
                    'bookmark_id'   => null,
                ],
            ]);
        }

        $bookmark = auth()->user()->bookmarks()->create([
            'bookmarkable_type' => $modelClass,
            'bookmarkable_id'   => $request->id,
        ]);

        return response()->json([
            'status' => 'success',
            'data'   => [
                'is_bookmarked' => true,
                'bookmark_id'   => $bookmark->id,
            ],
        ]);
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'type' => 'required|in:residence,activity,marketplace_product',
            'id'   => 'required|integer',
        ]);

        $modelClass = $this->resolveModel($request->type);

        $bookmark = auth()->user()->bookmarks()
            ->where('bookmarkable_type', $modelClass)
            ->where('bookmarkable_id', $request->id)
            ->first();

        if (!$bookmark) {
            return response()->json(['status' => 'error', 'message' => 'Bookmark tidak ditemukan.'], 404);
        }

        $bookmark->delete();

        return response()->json(['status' => 'success', 'message' => 'Bookmark berhasil dihapus.']);
    }
}