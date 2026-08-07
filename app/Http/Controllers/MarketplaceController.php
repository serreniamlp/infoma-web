<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\CheckProfileComplete;
use App\Models\MarketplaceProduct;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class MarketplaceController extends Controller
{
    use CheckProfileComplete;

    public function index(Request $request)
    {
        $query = MarketplaceProduct::with(['seller', 'category'])
            ->active()
            ->available();

        if ($request->filled('search'))   $query->search($request->search);
        if ($request->filled('category')) $query->where('category_id', $request->category);
        if ($request->filled('condition'))$query->byCondition($request->condition);
        if ($request->filled('min_price') || $request->filled('max_price'))
            $query->priceRange($request->min_price, $request->max_price);
        if ($request->filled('location')) $query->byLocation($request->location);

        switch ($request->get('sort', 'created_at')) {
            case 'price_asc':  $query->orderBy('price', 'asc');         break;
            case 'price_desc': $query->orderBy('price', 'desc');        break;
            case 'views':      $query->orderBy('views_count', 'desc');  break;
            case 'name':       $query->orderBy('name', 'asc');          break;
            default:           $query->orderBy('created_at', 'desc');
        }

        $products   = $query->paginate(12);
        $categories = ProductCategory::active()->get();

        return view('marketplace.index', compact('products', 'categories'));
    }

    public function show(MarketplaceProduct $product)
    {
        $product->incrementViews(Auth::id(), request()->ip());
        $product->load(['seller', 'category', 'ratings.user']);

        $relatedProducts = MarketplaceProduct::with(['seller', 'category'])
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->active()->available()->limit(4)->get();

        $isBookmarked = Auth::check() ? $product->isBookmarkedBy(Auth::id()) : false;

        return view('marketplace.show', compact('product', 'relatedProducts', 'isBookmarked'));
    }

    public function create()
    {
        if (!Auth::user()->isSeller()) {
            return redirect()->route('user.marketplace.sell')
                ->with('error', 'Aktifkan akun penjual terlebih dahulu.');
        }

        if ($redirect = $this->checkProfileComplete(route('user.marketplace.seller.create'))) {
            return $redirect;
        }

        $categories = ProductCategory::active()->get();
        return view('marketplace.create', compact('categories'));
    }

    public function store(Request $request)
    {
        if (!Auth::user()->isSeller()) {
            return redirect()->route('user.marketplace.sell')
                ->with('error', 'Aktifkan akun penjual terlebih dahulu.');
        }

        if ($redirect = $this->checkProfileComplete()) return $redirect;

        // ── Validasi ──────────────────────────────────────────────────────
        $rules = [
            'name'           => 'required|string|max:255',
            'description'    => 'required|string',
            'category_id'    => 'required|exists:product_categories,id',
            'condition'      => 'required|in:new,like_new,good,fair,needs_repair',
            'price'          => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:1',
            'location'       => 'required|string|max:255',
            'tags'           => 'nullable|string',
            // Metode pengambilan
            'pickup_methods'   => 'required|array|min:1',
            'pickup_methods.*' => 'in:cod,delivery,pickup',
            'pickup_address'   => 'required_if:pickup_methods.*,pickup|nullable|string|max:500',
        ];

        if ($request->hasFile('images')) {
            $rules['images']   = 'array|max:5';
            $rules['images.*'] = 'image|mimes:jpeg,png,jpg,gif,webp|max:2048';
        }

        $request->validate($rules, [
            'pickup_methods.required' => 'Pilih minimal satu metode pengambilan.',
            'pickup_methods.min'      => 'Pilih minimal satu metode pengambilan.',
            'pickup_address.required_if' => 'Alamat pengambilan wajib diisi jika metode "Ambil Sendiri" dipilih.',
        ]);

        // ── Simpan ────────────────────────────────────────────────────────
        $data              = $request->except(['_token', 'images', 'tags']);
        $data['seller_id'] = Auth::id();
        $data['status']    = 'active';

        // Pickup address hanya disimpan jika pickup tersedia
        if (!in_array('pickup', $request->input('pickup_methods', []))) {
            $data['pickup_address'] = null;
        }

        if ($request->hasFile('images')) {
            $images = [];
            foreach ($request->file('images') as $image) {
                $images[] = $image->store('marketplace/products', 'public');
            }
            $data['images'] = $images;
        } else {
            $data['images'] = [];
        }

        if ($request->filled('tags')) {
            $data['tags'] = array_map('trim', explode(',', $request->tags));
        }

        MarketplaceProduct::create($data);

        return redirect()->route('user.marketplace.seller.my-products')
            ->with('success', 'Produk berhasil ditambahkan!');
    }

    public function edit(MarketplaceProduct $product)
    {
        if (!Auth::user()->isSeller()) {
            return redirect()->route('user.marketplace.sell')
                ->with('error', 'Aktifkan akun penjual terlebih dahulu.');
        }

        if ($product->seller_id != Auth::id()) abort(403);

        $categories = ProductCategory::active()->get();
        return view('marketplace.edit', compact('product', 'categories'));
    }

    public function update(Request $request, MarketplaceProduct $product)
    {
        if (!Auth::user()->isSeller()) {
            return redirect()->route('user.marketplace.sell')
                ->with('error', 'Aktifkan akun penjual terlebih dahulu.');
        }

        if ($product->seller_id != Auth::id()) abort(403);

        $request->validate([
            'name'           => 'required|string|max:255',
            'description'    => 'required|string',
            'category_id'    => 'required|exists:product_categories,id',
            'condition'      => 'required|in:new,like_new,good,fair,needs_repair',
            'price'          => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'location'       => 'required|string|max:255',
            'images.*'       => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'tags'           => 'nullable|string',
            'status'         => 'required|in:draft,active,inactive',
            // Metode pengambilan
            'pickup_methods'   => 'required|array|min:1',
            'pickup_methods.*' => 'in:cod,delivery,pickup',
            'pickup_address'   => 'required_if:pickup_methods.*,pickup|nullable|string|max:500',
        ], [
            'pickup_methods.required' => 'Pilih minimal satu metode pengambilan.',
            'pickup_address.required_if' => 'Alamat pengambilan wajib diisi jika metode "Ambil Sendiri" dipilih.',
        ]);

        $data = $request->except(['_token', '_method', 'images', 'tags']);

        // Pickup address hanya disimpan jika pickup tersedia
        if (!in_array('pickup', $request->input('pickup_methods', []))) {
            $data['pickup_address'] = null;
        }

        if ($request->hasFile('images')) {
            if ($product->images) {
                foreach ($product->images as $image) {
                    Storage::disk('public')->delete($image);
                }
            }
            $images = [];
            foreach ($request->file('images') as $image) {
                $images[] = $image->store('marketplace/products', 'public');
            }
            $data['images'] = $images;
        }

        if ($request->filled('tags')) {
            $data['tags'] = array_map('trim', explode(',', $request->tags));
        }

        if ($request->filled('stock_quantity')) {
            $reservedQty = $product->active_reserved_quantity;
            if ((int) $request->stock_quantity < $reservedQty) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', "Stok produk tidak boleh kurang dari {$reservedQty} unit karena terdapat {$reservedQty} unit barang yang sedang dalam transaksi aktif.");
            }
        }

        $product->update($data);

        return redirect()->route('user.marketplace.seller.my-products')
            ->with('success', 'Produk berhasil diperbarui!');
    }

    public function destroy(MarketplaceProduct $product)
    {
        if (!Auth::user()->isSeller()) abort(403);
        if ($product->seller_id != Auth::id()) abort(403);

        $reservedQty = $product->active_reserved_quantity;
        if ($reservedQty > 0) {
            return redirect()->back()
                ->with('error', "Produk \"{$product->name}\" tidak dapat dihapus total karena terdapat {$reservedQty} unit barang yang sedang dalam transaksi aktif. Anda dapat menurunkan stok hingga minimal {$reservedQty} unit di menu Edit Produk, atau selesaikan/batalkan transaksi terlebih dahulu.");
        }

        if ($product->images) {
            foreach ($product->images as $image) {
                Storage::disk('public')->delete($image);
            }
        }

        $product->delete();

        return redirect()->route('user.marketplace.seller.my-products')
            ->with('success', 'Produk berhasil dihapus!');
    }

    public function myProducts()
    {
        if (!Auth::user()->isSeller()) {
            return redirect()->route('user.marketplace.sell')
                ->with('error', 'Aktifkan akun penjual terlebih dahulu.');
        }

        $products = MarketplaceProduct::with(['category'])
            ->where('seller_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        return view('marketplace.my-products', compact('products'));
    }

    public function toggleBookmark(MarketplaceProduct $product)
    {
        if (!Auth::check()) return response()->json(['error' => 'Unauthorized'], 401);

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