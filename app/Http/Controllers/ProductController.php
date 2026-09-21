<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Review;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Listado de productos con filtros
     */
    public function index(Request $request)
    {
        $query = Product::active()->with('category');

        // Filtro por categoría
        if ($request->filled('category')) {
            $query->whereHas('category', fn($q) => $q->where('slug', $request->category));
        }

        // Filtro por precio
        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        // Búsqueda textual
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Ordenamiento
        $sortBy = $request->get('sort', 'newest');
        $query = match($sortBy) {
            'price_low' => $query->orderBy('price', 'asc'),
            'price_high' => $query->orderBy('price', 'desc'),
            'name' => $query->orderBy('name', 'asc'),
            default => $query->latest(),
        };

        $products = $query->paginate(12)->withQueryString();
        $categories = Category::withCount('products')->get();

        return view('products.index', compact('products', 'categories'));
    }

    /**
     * Detalle de un producto
     */
    public function show(string $slug)
    {
        $product = Product::active()
            ->where('slug', $slug)
            ->with('category')
            ->firstOrFail();

        $relatedProducts = Product::active()
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->limit(4)
            ->get();

        $canReview = $product->userCanReview(auth()->user());
        $hasReviewed = auth()->check() && Review::where('user_id', auth()->id())
            ->where('product_id', $product->id)
            ->exists();

        return view('products.show', compact('product', 'relatedProducts', 'canReview', 'hasReviewed'));
    }
}
