<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'product_id',
        'quantity',
        'price',
        'total',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'total' => 'decimal:2',
        'quantity' => 'integer',
    ];

    /**
     * Pedido al que pertenece
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Producto comprado
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
