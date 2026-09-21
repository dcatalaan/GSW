<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Services\SemanticSearchService;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $featuredProducts = Product::active()
            ->featured()
            ->with('category')
            ->limit(8)
            ->get();

        $categories = Category::withCount('products')
            ->orderByDesc('products_count')
            ->get();

        return view('home', compact('featuredProducts', 'categories'));
    }

    /**
     * Búsqueda semántica de productos
     */
    public function search(Request $request, SemanticSearchService $searchService)
    {
        $request->validate([
            'q' => 'required|string|min:2|max:200',
        ]);

        $query = $request->input('q');

        // En PostgreSQL con pgvector: búsqueda semántica real
        if (config('database.default') === 'pgsql') {
            $results = $searchService->hybridSearch($query, 20);
        } else {
            // En SQLite: búsqueda textual mejorada con priorización
            $results = $this->enhancedTextSearch($query, 20);
        }

        if ($request->ajax()) {
            return response()->json([
                'query' => $query,
                'results' => $results,
                'count' => count($results),
            ]);
        }

        return view('search', compact('query', 'results'));
    }

    /**
     * Búsqueda textual mejorada (para SQLite sin pgvector)
     * Prioriza coincidencias exactas y por categoría
     */
    private function enhancedTextSearch(string $query, int $limit): array
    {
        $words = array_filter(explode(' ', mb_strtolower($query)), fn($w) => mb_strlen($w) > 1);

        if (empty($words)) {
            return Product::active()->with('category')->latest()->limit($limit)->get()->toArray();
        }

        // 1. Coincidencia exacta en nombre (mayor peso)
        $exactName = Product::active()
            ->with('category')
            ->where(function ($q) use ($words) {
                foreach ($words as $word) {
                    $q->orWhere('name', 'LIKE', "%{$word}%");
                }
            })
            ->get()
            ->toArray();

        // 2. Coincidencia en descripción
        $descMatch = Product::active()
            ->with('category')
            ->where(function ($q) use ($words) {
                foreach ($words as $word) {
                    $q->orWhere('description', 'LIKE', "%{$word}%")
                      ->orWhere('short_description', 'LIKE', "%{$word}%");
                }
            })
            ->get()
            ->toArray();

        // Combinar sin duplicados
        $ids = [];
        $results = [];
        foreach (array_merge($exactName, $descMatch) as $item) {
            if (!in_array($item['id'], $ids)) {
                $ids[] = $item['id'];
                $results[] = $item;
            }
        }

        return array_slice($results, 0, $limit);
    }
}
