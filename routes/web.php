<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\Auth\AuthController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Página principal
Route::get('/', [HomeController::class, 'index'])->name('home');

// Búsqueda semántica
Route::get('/buscar', [HomeController::class, 'search'])->name('search');

// Productos
Route::get('/productos', [ProductController::class, 'index'])->name('products.index');
Route::get('/productos/{slug}', [ProductController::class, 'show'])->name('products.show');

// Carrito de compras
Route::prefix('carrito')->name('cart.')->group(function () {
    Route::get('/', [CartController::class, 'index'])->name('index');
    Route::post('/agregar', [CartController::class, 'add'])->name('add');
    Route::put('/{cartItem}', [CartController::class, 'update'])->name('update');
    Route::delete('/{cartItem}', [CartController::class, 'remove'])->name('remove');
    Route::post('/vaciar', [CartController::class, 'clear'])->name('clear');
});

// Autenticación
Route::middleware('guest')->group(function () {
    Route::get('/registro', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/registro', [AuthController::class, 'register']);
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Páginas públicas
Route::view('/about', 'about')->name('about');
Route::post('/api/chat', [\App\Http\Controllers\ChatbotController::class, 'chat'])->name('chat.send');
Route::get('/api/chat/history', [\App\Http\Controllers\ChatbotController::class, 'history'])->name('chat.history');
Route::get('/api/chat/clear', [\App\Http\Controllers\ChatbotController::class, 'clear'])->name('chat.clear');

// Pedidos (requiere autenticación)
Route::middleware('auth')->group(function () {
    Route::get('/pedidos', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/pedidos/{orderNumber}', [OrderController::class, 'show'])->name('orders.show');
    Route::post('/pedidos', [OrderController::class, 'store'])->name('orders.store');
    Route::get('/pedidos/{orderNumber}/factura', [\App\Http\Controllers\InvoiceController::class, 'download'])->name('orders.invoice');

    // Perfil y facturación
    Route::get('/perfil', [\App\Http\Controllers\ProfileController::class, 'show'])->name('profile.show');
    Route::put('/perfil', [\App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');

    // Reseñas
    Route::post('/productos/{product:slug}/resenas', [ReviewController::class, 'store'])->name('reviews.store');
    Route::delete('/resenas/{review}', [ReviewController::class, 'destroy'])->name('reviews.destroy');
});

// Panel de administración
Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'admin'])
    ->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\AdminController::class, 'dashboard'])->name('dashboard');

        // Productos
        Route::get('/productos', [\App\Http\Controllers\Admin\AdminController::class, 'products'])->name('products');
        Route::get('/productos/crear', [\App\Http\Controllers\Admin\AdminController::class, 'productCreate'])->name('products.create');
        Route::post('/productos', [\App\Http\Controllers\Admin\AdminController::class, 'productStore'])->name('products.store');
        Route::get('/productos/{product}/editar', [\App\Http\Controllers\Admin\AdminController::class, 'productEdit'])->name('products.edit');
        Route::put('/productos/{product}', [\App\Http\Controllers\Admin\AdminController::class, 'productUpdate'])->name('products.update');
        Route::delete('/productos/{product}', [\App\Http\Controllers\Admin\AdminController::class, 'productDelete'])->name('products.delete');

        // Categorías
        Route::get('/categorias', [\App\Http\Controllers\Admin\AdminController::class, 'categories'])->name('categories');
        Route::post('/categorias', [\App\Http\Controllers\Admin\AdminController::class, 'categoryStore'])->name('categories.store');
        Route::delete('/categorias/{category}', [\App\Http\Controllers\Admin\AdminController::class, 'categoryDelete'])->name('categories.delete');

        // Pedidos
        Route::get('/pedidos', [\App\Http\Controllers\OrderController::class, 'adminIndex'])->name('orders');
        Route::put('/pedidos/{order}/estado', [\App\Http\Controllers\OrderController::class, 'updateStatus'])->name('orders.updateStatus');

        // Embeddings
        Route::post('/embeddings/regenerar', [\App\Http\Controllers\Admin\AdminController::class, 'regenerateEmbeddings'])->name('embeddings.regenerate');
    });
