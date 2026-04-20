<?php

namespace App\Http\Controllers;

use App\Models\MarketplaceProduct;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MarketplaceController extends Controller
{
    public function index(Request $request)
    {
        $query = MarketplaceProduct::with(['seller', 'category'])
            ->active()
            ->available();

        // Search functionality
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Filter by category
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        // Filter by condition
        if ($request->filled('condition')) {
            $query->byCondition($request->condition);
        }

        // Filter by price range
        if ($request->filled('min_price') || $request->filled('max_price')) {
            $query->priceRange($request->min_price, $request->max_price);
        }

        // Filter by location
        if ($request->filled('location')) {
            $query->byLocation($request->location);
        }

        // Sort
        $sort = $request->get('sort', 'created_at');
        $direction = $request->get('direction', 'desc');

        switch ($sort) {
            case 'price_asc':
                $query->orderBy('price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('price', 'desc');
                break;
            case 'views':
                $query->orderBy('views_count', 'desc');
                break;
            case 'name':
                $query->orderBy('name', 'asc');
                break;
            default:
                $query->orderBy('created_at', 'desc');
        }

        $products = $query->paginate(12);
        $categories = ProductCategory::active()->get();

        return view('marketplace.index', compact('products', 'categories'));
    }

    public function show(MarketplaceProduct $product)
    {
        // Increment views
        $product->incrementViews(Auth::id(), request()->ip());

        $product->load(['seller', 'category', 'ratings.user']);

        // Get related products
        $relatedProducts = MarketplaceProduct::with(['seller', 'category'])
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->active()
            ->available()
            ->limit(4)
            ->get();

        // Check if user has bookmarked this product
        $isBookmarked = false;
        if (Auth::check()) {
            $isBookmarked = $product->isBookmarkedBy(Auth::id());
        }

        return view('marketplace.show', compact('product', 'relatedProducts', 'isBookmarked'));
    }

    public function create()
    {
        // Check if user has provider role
        if (!Auth::user()->hasRole('provider')) {
            abort(403, 'Hanya provider yang dapat menjual produk');
        }

        $categories = ProductCategory::active()->get();
        return view('marketplace.create', compact('categories'));
    }

    public function store(Request $request)
    {
        // Check if user has provider role
        if (!Auth::user()->hasRole('provider')) {
            abort(403, 'Hanya provider yang dapat menjual produk');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'category_id' => 'required|exists:product_categories,id',
            'condition' => 'required|in:new,like_new,good,fair,needs_repair',
            'price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:1',
            'location' => 'required|string|max:255',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'tags' => 'nullable|string',
        ]);

        $data = $request->all();
        $data['seller_id'] = Auth::id();
        $data['status'] = 'active';

        // Handle images
        if ($request->hasFile('images')) {
            $images = [];
            foreach ($request->file('images') as $image) {
                $path = $image->store('marketplace/products', 'public');
                $images[] = $path;
            }
            $data['images'] = $images;
        }

        // Handle tags
        if ($request->filled('tags')) {
            $data['tags'] = array_map('trim', explode(',', $request->tags));
        }

        MarketplaceProduct::create($data);

        return redirect()->route('provider.marketplace.my-products')
            ->with('success', 'Produk berhasil ditambahkan!');
    }

    public function edit(MarketplaceProduct $product)
    {
        // Check if user has provider role
        if (!Auth::user()->hasRole('provider')) {
            abort(403, 'Hanya provider yang dapat mengedit produk');
        }

        // Check if user owns this product
        if ($product->seller_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        $categories = ProductCategory::active()->get();
        return view('marketplace.edit', compact('product', 'categories'));
    }

    public function update(Request $request, MarketplaceProduct $product)
    {
        // Check if user has provider role
        if (!Auth::user()->hasRole('provider')) {
            abort(403, 'Hanya provider yang dapat mengupdate produk');
        }

        // Check if user owns this product
        if ($product->seller_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'category_id' => 'required|exists:product_categories,id',
            'condition' => 'required|in:new,like_new,good,fair,needs_repair',
            'price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'location' => 'required|string|max:255',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'tags' => 'nullable|string',
            'status' => 'required|in:draft,active,inactive',
        ]);

        $data = $request->all();

        // Handle images
        if ($request->hasFile('images')) {
            // Delete old images
            if ($product->images) {
                foreach ($product->images as $image) {
                    Storage::disk('public')->delete($image);
                }
            }

            $images = [];
            foreach ($request->file('images') as $image) {
                $path = $image->store('marketplace/products', 'public');
                $images[] = $path;
            }
            $data['images'] = $images;
        }

        // Handle tags
        if ($request->filled('tags')) {
            $data['tags'] = array_map('trim', explode(',', $request->tags));
        }

        $product->update($data);

        return redirect()->route('provider.marketplace.my-products')
            ->with('success', 'Produk berhasil diperbarui!');
    }

    public function destroy(MarketplaceProduct $product)
    {
        // Check if user has provider role
        if (!Auth::user()->hasRole('provider')) {
            abort(403, 'Hanya provider yang dapat menghapus produk');
        }

        // Check if user owns this product
        if ($product->seller_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        // Delete images
        if ($product->images) {
            foreach ($product->images as $image) {
                Storage::disk('public')->delete($image);
            }
        }

        $product->delete();

        return redirect()->route('provider.marketplace.my-products')
            ->with('success', 'Produk berhasil dihapus!');
    }

    public function myProducts()
    {
        // Check if user has provider role
        if (!Auth::user()->hasRole('provider')) {
            abort(403, 'Hanya provider yang dapat melihat produk mereka');
        }

        $products = MarketplaceProduct::with(['category'])
            ->where('seller_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        return view('marketplace.my-products', compact('products'));
    }

    public function toggleBookmark(MarketplaceProduct $product)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $bookmark = $product->bookmarks()->where('user_id', Auth::id())->first();

        if ($bookmark) {
            $bookmark->delete();
            $isBookmarked = false;
        } else {
            $product->bookmarks()->create(['user_id' => Auth::id()]);
            $isBookmarked = true;
        }

        return response()->json(['isBookmarked' => $isBookmarked]);
    }

    public function bookmarks()
    {
        $bookmarks = Auth::user()->bookmarks()
            ->where('bookmarkable_type', MarketplaceProduct::class)
            ->with(['bookmarkable.seller', 'bookmarkable.category'])
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        return view('marketplace.bookmarks', compact('bookmarks'));
    }
}
