<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'product_id', 'rating', 'title', 'comment', 'is_verified'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Obtener promedio de rating de un producto
     */
    public static function getAverageRating(int $productId): ?float
    {
        return self::where('product_id', $productId)->avg('rating');
    }

    /**
     * Obtener total de reseñas de un producto
     */
    public static function getReviewCount(int $productId): int
    {
        return self::where('product_id', $productId)->count();
    }

    /**
     * Obtener distribución de ratings (1-5)
     */
    public static function getRatingDistribution(int $productId): array
    {
        $dist = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];
        $counts = self::where('product_id', $productId)
            ->selectRaw('rating, count(*) as total')
            ->groupBy('rating')
            ->pluck('total', 'rating')
            ->toArray();

        foreach ($counts as $rating => $count) {
            $dist[(int)$rating] = (int)$count;
        }

        return $dist;
    }
}
