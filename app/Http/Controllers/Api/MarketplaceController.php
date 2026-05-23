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

        $products = $query->orderBy('created_at', 'desc')->paginate(15);

        return MarketplaceProductResource::collection($products);
    }

    public function show(MarketplaceProduct $product)
    {
        $product->load(['seller', 'category']);

        return response()->json([
            'status' => 'success',
            'data'   => new MarketplaceProductResource($product),
        ]);
    }
}
