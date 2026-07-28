<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\MarketplaceProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    public function index()
    {
        $cartItems = CartItem::with('product.category')
            ->where('user_id', Auth::id())
            ->get();

        $total = $cartItems->sum(fn($item) => $item->quantity * ($item->product->price ?? 0));

        return view('user.marketplace.cart', compact('cartItems', 'total'));
    }

    public function add(Request $request, MarketplaceProduct $product)
    {
        $request->validate(['quantity' => ['nullable', 'integer', 'min:1', 'max:99']]);

        if ($product->seller_id == Auth::id()) {
            return back()->with('error', 'Anda tidak bisa menambahkan produk Anda sendiri ke keranjang.');
        }

        if (!$product->is_available || $product->status !== 'active') {
            return back()->with('error', 'Produk ini tidak tersedia.');
        }

        $qty = $request->input('quantity', 1);

        $cartItem = CartItem::where('user_id', Auth::id())
            ->where('product_id', $product->id)
            ->first();

        if ($cartItem) {
            $newQty = $cartItem->quantity + $qty;
            if ($newQty > $product->stock_quantity) {
                $newQty = $product->stock_quantity;
            }
            $cartItem->update(['quantity' => $newQty]);
        } else {
            CartItem::create([
                'user_id'    => Auth::id(),
                'product_id' => $product->id,
                'quantity'   => min($qty, $product->stock_quantity),
            ]);
        }

        return back()->with('success', 'Produk berhasil ditambahkan ke keranjang.');
    }

    public function update(Request $request, CartItem $cartItem)
    {
        if ($cartItem->user_id != Auth::id()) {
            abort(403);
        }

        $request->validate(['quantity' => ['required', 'integer', 'min:1', 'max:99']]);

        $qty = min($request->quantity, $cartItem->product->stock_quantity ?? 99);
        $cartItem->update(['quantity' => $qty]);

        if ($request->wantsJson()) {
            $total = CartItem::where('user_id', Auth::id())
                ->with('product')
                ->get()
                ->sum(fn($i) => $i->quantity * ($i->product->price ?? 0));

            return response()->json([
                'subtotal' => $cartItem->quantity * ($cartItem->product->price ?? 0),
                'total'    => $total,
            ]);
        }

        return back()->with('success', 'Jumlah diperbarui.');
    }

    public function remove(CartItem $cartItem)
    {
        if ($cartItem->user_id != Auth::id()) {
            abort(403);
        }

        $cartItem->delete();

        return back()->with('success', 'Produk dihapus dari keranjang.');
    }

    public function clear()
    {
        CartItem::where('user_id', Auth::id())->delete();
        return back()->with('success', 'Keranjang dikosongkan.');
    }

    public function count()
    {
        $count = CartItem::where('user_id', Auth::id())->sum('quantity');
        return response()->json(['count' => $count]);
    }
}
