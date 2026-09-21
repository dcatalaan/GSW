<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    /**
     * Guardar una reseña (solo quien recibió el producto)
     */
    public function store(Request $request, Product $product)
    {
        $request->validate([
            'rating'  => 'required|integer|min:1|max:5',
            'title'   => 'nullable|string|max:100',
            'comment' => 'nullable|string|max:1000',
        ]);

        if (!$product->userCanReview(Auth::user())) {
            return back()->with('error', 'Solo puedes reseñar productos que hayas recibido.');
        }

        Review::create([
            'user_id'     => Auth::id(),
            'product_id'  => $product->id,
            'rating'      => $request->rating,
            'title'       => $request->title,
            'comment'     => $request->comment,
            'is_verified' => true, // verificado: proviene de un pedido entregado
        ]);

        return back()->with('success', '¡Reseña publicada! Gracias por tu opinión.');
    }

    /**
     * Eliminar una reseña (solo la propia o admin)
     */
    public function destroy(Review $review)
    {
        if (Auth::id() !== $review->user_id && !Auth::user()->isAdmin()) {
            abort(403);
        }

        $productId = $review->product_id;
        $review->delete();

        return redirect()->route('products.show', $productId)
            ->with('success', 'Reseña eliminada.');
    }
}
