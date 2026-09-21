<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CartItem extends Model
{
    protected $fillable = [
        'user_id',
        'product_id',
        'quantity',
        'session_id',
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    /**
     * Usuario propietario del carrito
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Producto en el carrito
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Subtotal del item
     */
    public function getSubtotalAttribute(): float
    {
        return $this->product->price * $this->quantity;
    }

    /**
     * Obtener items del carrito (por usuario o por sesión)
     */
    public static function getCartItems(?int $userId, ?string $sessionId): \Illuminate\Support\Collection
    {
        $query = self::with('product.category');

        if ($userId) {
            $query->where('user_id', $userId);
        } else {
            $query->where('session_id', $sessionId)
                  ->whereNull('user_id');
        }

        return $query->get();
    }

    /**
     * Obtener total del carrito
     */
    public static function getCartTotal(?int $userId, ?string $sessionId): float
    {
        $items = self::getCartItems($userId, $sessionId);
        return $items->sum(function ($item) {
            return $item->product->price * $item->quantity;
        });
    }

    /**
     * Obtener cantidad total de items
     */
    public static function getCartCount(?int $userId, ?string $sessionId): int
    {
        $query = self::query();

        if ($userId) {
            $query->where('user_id', $userId);
        } else {
            $query->where('session_id', $sessionId)
                  ->whereNull('user_id');
        }

        return (int) $query->sum('quantity');
    }
}
