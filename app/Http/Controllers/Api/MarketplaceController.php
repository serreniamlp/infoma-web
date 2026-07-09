<?php
// app/Http/Controllers/Api/MarketplaceController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MarketplaceProductResource;
use App\Models\MarketplaceProduct;
use Illuminate\Http\Request;

class MarketplaceController extends Controller
{
    public function index(Request $request)
    {
        $query = MarketplaceProduct::with(['seller', 'category'])
            ->where('status', 'active')
            ->where('stock_quantity', '>', 0);

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        if ($request->filled('condition')) {
            $query->where('condition', $request->condition);
        }

        if ($request->filled('sort')) {
            if ($request->sort === 'price_asc') {
                $query->orderBy('price', 'asc');
            } elseif ($request->sort === 'price_desc') {
                $query->orderBy('price', 'desc');
            } else {
                $query->orderBy('created_at', 'desc');
            }
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $products = $query->paginate(15);

        return MarketplaceProductResource::collection($products);
    }

    public function show(MarketplaceProduct $product)
    {
        $product->incrementViews(auth('sanctum')->id(), request()->ip());
        $product->load(['seller', 'category']);

        $relatedProducts = MarketplaceProduct::with(['seller', 'category'])
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->active()->available()->limit(4)->get();

        return response()->json([
            'status' => 'success',
            'data'   => new MarketplaceProductResource($product),
            'related_products' => MarketplaceProductResource::collection($relatedProducts),
        ]);
    }
}
