<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class CartController extends Controller
{
    /**
     * Mostrar el carrito
     */
    public function index(Request $request)
    {
        $userId = $request->user()?->id;
        $sessionId = Session::getId();

        $items = CartItem::getCartItems($userId, $sessionId);
        $total = CartItem::getCartTotal($userId, $sessionId);
        $count = CartItem::getCartCount($userId, $sessionId);

        return view('cart.index', compact('items', 'total', 'count'));
    }

    /**
     * Agregar producto al carrito
     */
    public function add(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1|max:100',
        ]);

        $userId = $request->user()?->id;
        $sessionId = Session::getId();
        $productId = $request->product_id;
        $quantity = $request->quantity;

        // Verificar stock
        $product = Product::findOrFail($productId);
        if ($product->stock < $quantity) {
            return back()->with('error', 'No hay suficiente stock disponible.');
        }

        // Buscar item existente en el carrito
        $existingItem = CartItem::where('product_id', $productId)
            ->when($userId, fn($q) => $q->where('user_id', $userId))
            ->when(!$userId, fn($q) => $q->where('session_id', $sessionId)->whereNull('user_id'))
            ->first();

        if ($existingItem) {
            $newQuantity = $existingItem->quantity + $quantity;
            if ($newQuantity > $product->stock) {
                return back()->with('error', 'No hay suficiente stock para esta cantidad.');
            }
            $existingItem->update(['quantity' => $newQuantity]);
        } else {
            CartItem::create([
                'user_id' => $userId,
                'product_id' => $productId,
                'quantity' => $quantity,
                'session_id' => $userId ? null : $sessionId,
            ]);
        }

        $count = CartItem::getCartCount($userId, $sessionId);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Producto agregado al carrito',
                'cart_count' => $count,
            ]);
        }

        return back()->with('success', 'Producto agregado al carrito');
    }

    /**
     * Actualizar cantidad de un item
     */
    public function update(Request $request, CartItem $cartItem)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1|max:100',
        ]);

        $quantity = $request->quantity;

        if ($cartItem->product->stock < $quantity) {
            return back()->with('error', 'No hay suficiente stock disponible.');
        }

        $cartItem->update(['quantity' => $quantity]);

        $userId = $request->user()?->id;
        $sessionId = Session::getId();
        $total = CartItem::getCartTotal($userId, $sessionId);
        $count = CartItem::getCartCount($userId, $sessionId);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'item_total' => number_format($cartItem->product->price * $quantity, 2),
                'cart_total' => number_format($total, 2),
                'cart_count' => $count,
            ]);
        }

        return back()->with('success', 'Carrito actualizado');
    }

    /**
     * Eliminar item del carrito
     */
    public function remove(Request $request, CartItem $cartItem)
    {
        $cartItem->delete();

        $userId = $request->user()?->id;
        $sessionId = Session::getId();
        $total = CartItem::getCartTotal($userId, $sessionId);
        $count = CartItem::getCartCount($userId, $sessionId);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'cart_total' => number_format($total, 2),
                'cart_count' => $count,
            ]);
        }

        return back()->with('success', 'Producto eliminado del carrito');
    }

    /**
     * Vaciar el carrito
     */
    public function clear(Request $request)
    {
        $userId = $request->user()?->id;
        $sessionId = Session::getId();

        CartItem::when($userId, function ($query) use ($userId) {
            $query->where('user_id', $userId);
        }, function ($query) use ($sessionId) {
            $query->where('session_id', $sessionId)->whereNull('user_id');
        })->delete();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'cart_count' => 0,
                'cart_total' => '0.00',
            ]);
        }

        return back()->with('success', 'Carrito vaciado');
    }
}
