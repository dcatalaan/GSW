<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'short_description',
        'price',
        'compare_price',
        'sku',
        'stock',
        'category_id',
        'image',
        'images',
        'is_active',
        'is_featured',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'compare_price' => 'decimal:2',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'images' => 'array',
    ];

    /**
     * Categoría del producto
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Embeddings del producto para búsqueda semántica
     */
    public function embeddings()
    {
        return $this->hasMany(ProductEmbedding::class);
    }

    /**
     * Items del carrito que contienen este producto
     */
    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Reseñas del producto
     */
    public function reviews()
    {
        return $this->hasMany(Review::class)->latest();
    }

    /**
     * ¿Puede este usuario dejar una reseña? Solo quien RECIBIÓ el producto
     * (pedido entregado que lo contiene) y aún no lo reseñó.
     */
    public function userCanReview(?User $user): bool
    {
        if (!$user) return false;

        $received = $user->orders()
            ->where('status', Order::STATUS_DELIVERED)
            ->whereHas('items', fn($q) => $q->where('product_id', $this->id))
            ->exists();

        if (!$received) return false;

        return !Review::where('user_id', $user->id)
            ->where('product_id', $this->id)
            ->exists();
    }

    /**
     * Obtener la URL de la imagen principal.
     * Acepta URLs externas (http/https) o rutas del disco `public`.
     */
    public function getImageUrlAttribute(): string
    {
        if ($this->image && str_starts_with($this->image, 'http')) {
            return $this->image;
        }
        if ($this->image) {
            return asset('storage/' . $this->image);
        }
        return asset('images/placeholder-product.svg');
    }

    /**
     * Verificar si tiene descuento
     */
    public function hasDiscount(): bool
    {
        return $this->compare_price && $this->compare_price > $this->price;
    }

    /**
     * Obtener porcentaje de descuento
     */
    public function getDiscountPercentage(): int
    {
        if (!$this->hasDiscount()) return 0;
        return (int) round((($this->compare_price - $this->price) / $this->compare_price) * 100);
    }

    /**
     * Scope: productos activos
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: productos destacados
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope: búsqueda textual tradicional
     */
    public function scopeSearch($query, string $term)
    {
        $like = config('database.default') === 'sqlite' ? 'LIKE' : 'ilike';
        return $query->where(function ($q) use ($term, $like) {
            $q->where('name', $like, "%{$term}%")
              ->orWhere('description', $like, "%{$term}%")
              ->orWhere('short_description', $like, "%{$term}%")
              ->orWhere('sku', $like, "%{$term}%");
        });
    }
}
