<?php

namespace Database\Seeders;

use App\Models\Review;
use App\Models\User;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::where('role', 'customer')->get();
        $products = Product::all();

        if ($users->isEmpty()) return;

        $reviews = [
            ['product_slug' => 'zapatillas-running-ultra', 'user_idx' => 0, 'rating' => 5, 'title' => '¡Las mejores zapatillas!', 'comment' => 'Compré estas zapatillas para correr y son increíbles. Muy cómodas y el agarre es perfecto incluso en mojado.', 'verified' => true],
            ['product_slug' => 'chaqueta-impermeable-deportiva', 'user_idx' => 0, 'rating' => 4, 'title' => 'Buena chaqueta', 'comment' => 'Muy buena calidad, impermeable de verdad. Solo le doy 4 estrellas porque la talla corre un poco grande.', 'verified' => true],
            ['product_slug' => 'audifonos-bluetooth-pro', 'user_idx' => 1, 'rating' => 5, 'title' => 'Sonido brutal', 'comment' => 'La cancelación de ruido es impresionante. La batería dura lo que dicen y el sonido es muy nítido.', 'verified' => true],
            ['product_slug' => 'smartwatch-fitness-tracker', 'user_idx' => 1, 'rating' => 4, 'title' => 'Muy completo', 'comment' => 'El GPS es preciso y tiene muchos modos de ejercicio. La pantalla es brillante y se ve bien incluso con sol.', 'verified' => true],
            ['product_slug' => 'mochila-urban-explorer', 'user_idx' => 0, 'rating' => 5, 'title' => 'Perfecta para el día a día', 'comment' => 'La uso todos los días para ir a la universidad. El espacio para laptop es generoso y es realmente resistente al agua.', 'verified' => true],
            ['product_slug' => 'olla-cocina-inteligente', 'user_idx' => 1, 'rating' => 4, 'title' => 'Muy práctica', 'comment' => 'Cocinar con ella es súper fácil. El recubrimiento antiadherente funciona de maravilla y se lava rápido.', 'verified' => true],
            ['product_slug' => 'bicicleta-montana-pro', 'user_idx' => 0, 'rating' => 5, 'title' => 'Aventura garantizada', 'comment' => 'Esta bici es una bestia. Los frenos de disco son increíbles y la suspensión absorbe todo.', 'verified' => true],
            ['product_slug' => 'smartwatch-fitness-tracker', 'user_idx' => 2, 'rating' => 5, 'title' => 'Regalo perfecto', 'comment' => 'Se lo regalé a mi novia y encantado. Los sensores de salud son muy completos por el precio.', 'verified' => false],
        ];

        foreach ($reviews as $r) {
            if (!isset($users[$r['user_idx']])) continue;
            $product = $products->firstWhere('slug', $r['product_slug']);
            if (!$product) continue;

            Review::firstOrCreate(
                ['user_id' => $users[$r['user_idx']]->id, 'product_id' => $product->id],
                [
                    'rating' => $r['rating'],
                    'title' => $r['title'],
                    'comment' => $r['comment'],
                    'is_verified' => $r['verified'],
                ]
            );
        }
    }
}
