<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductEmbedding extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'product_id',
        'embedding',
        'model_name',
        'created_at',
    ];

    protected $casts = [
        'embedding' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Producto asociado
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Convertir el embedding a string para pgvector
     */
    public function getEmbeddingStringAttribute(): string
    {
        return '[' . implode(',', $this->embedding) . ']';
    }
}
