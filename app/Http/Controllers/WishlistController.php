<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function index(Request $request)
    {
        $items = Wishlist::where('user_id', $request->user()->id)
            ->with('product.category')
            ->latest()
            ->get();

        return view('wishlist.index', compact('items'));
    }

    public function toggle(Request $request, Product $product)
    {
        $existing = Wishlist::where('user_id', $request->user()->id)
            ->where('product_id', $product->id)
            ->first();

        if ($existing) {
            $existing->delete();
            $message = 'Eliminado de favoritos';
            $active = false;
        } else {
            Wishlist::create([
                'user_id' => $request->user()->id,
                'product_id' => $product->id,
            ]);
            $message = 'Agregado a favoritos';
            $active = true;
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'active' => $active,
                'message' => $message,
                'count' => Wishlist::where('user_id', $request->user()->id)->count(),
            ]);
        }

        return back()->with('success', $message);
    }

    public function destroy(Request $request, Product $product)
    {
        Wishlist::where('user_id', $request->user()->id)
            ->where('product_id', $product->id)
            ->delete();

        return back()->with('success', 'Eliminado de favoritos');
    }
}
