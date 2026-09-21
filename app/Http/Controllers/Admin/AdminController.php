<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\Order;
use App\Models\User;
use App\Services\SemanticSearchService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    /**
     * Dashboard del administrador
     */
    public function dashboard()
    {
        $stats = [
            'total_products' => Product::count(),
            'active_products' => Product::active()->count(),
            'total_orders' => Order::count(),
            'pending_orders' => Order::where('status', 'pending')->count(),
            'total_users' => User::count(),
            'total_revenue' => Order::where('payment_status', 'paid')->sum('total'),
        ];

        $recentOrders = Order::with('user')
            ->latest()
            ->limit(5)
            ->get();

        return view('admin.dashboard', compact('stats', 'recentOrders'));
    }

    // ==================== PRODUCTOS ====================

    public function products(Request $request)
    {
        $query = Product::with('category');

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        $products = $query->latest()->paginate(15);
        $categories = Category::all();

        return view('admin.products.index', compact('products', 'categories'));
    }

    public function productCreate()
    {
        $categories = Category::all();
        return view('admin.products.create', compact('categories'));
    }

    public function productStore(Request $request, SemanticSearchService $searchService)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'description' => 'required|string',
            'short_description' => 'nullable|string|max:500',
            'price' => 'required|numeric|min:0',
            'compare_price' => 'nullable|numeric|min:0',
            'sku' => 'required|string|unique:products',
            'stock' => 'required|integer|min:0',
            'image' => 'nullable|image|max:2048',
            'image_url' => 'nullable|url|max:2048',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
        ]);

        $data = $request->except(['image', 'image_url']);
        $data['slug'] = Str::slug($request->name);
        $data['is_active'] = $request->boolean('is_active', true);
        $data['is_featured'] = $request->boolean('is_featured', false);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('products', 'public');
        } elseif ($request->filled('image_url')) {
            $data['image'] = $request->input('image_url');
        }

        $product = Product::create($data);

        // Generar embedding para búsqueda semántica
        $searchService->generateProductEmbedding($product);

        return redirect()->route('admin.products')
            ->with('success', 'Producto creado exitosamente');
    }

    public function productEdit(Product $product)
    {
        $categories = Category::all();
        return view('admin.products.edit', compact('product', 'categories'));
    }

    public function productUpdate(Request $request, Product $product, SemanticSearchService $searchService)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'description' => 'required|string',
            'short_description' => 'nullable|string|max:500',
            'price' => 'required|numeric|min:0',
            'compare_price' => 'nullable|numeric|min:0',
            'sku' => 'required|string|unique:products,sku,' . $product->id,
            'stock' => 'required|integer|min:0',
            'image' => 'nullable|image|max:2048',
            'image_url' => 'nullable|url|max:2048',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
        ]);

        $data = $request->except(['image', 'image_url']);
        $data['slug'] = Str::slug($request->name);
        $data['is_active'] = $request->boolean('is_active', true);
        $data['is_featured'] = $request->boolean('is_featured', false);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('products', 'public');
        } elseif ($request->filled('image_url')) {
            $data['image'] = $request->input('image_url');
        }

        $product->update($data);

        // Regenerar embedding
        $searchService->generateProductEmbedding($product);

        return redirect()->route('admin.products')
            ->with('success', 'Producto actualizado exitosamente');
    }

    public function productDelete(Product $product)
    {
        $product->delete();
        return redirect()->route('admin.products')
            ->with('success', 'Producto eliminado exitosamente');
    }

    // ==================== CATEGORÍAS ====================

    public function categories()
    {
        $categories = Category::withCount('products')->latest()->get();
        return view('admin.categories.index', compact('categories'));
    }

    public function categoryStore(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
        ]);

        Category::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
        ]);

        return back()->with('success', 'Categoría creada exitosamente');
    }

    public function categoryDelete(Category $category)
    {
        $category->delete();
        return back()->with('success', 'Categoría eliminada exitosamente');
    }

    // ==================== BÚSQUEDA SEMÁNTICA ====================

    public function regenerateEmbeddings(SemanticSearchService $searchService)
    {
        $count = $searchService->regenerateAllEmbeddings();
        return back()->with('success', "Se regeneraron embeddings para {$count} productos");
    }
}
