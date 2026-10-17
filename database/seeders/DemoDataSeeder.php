<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Datos de demostración: clientes extra, pedidos entregados y reseñas
 * distribuidas por todo el catálogo. Determinista e idempotente.
 *
 * Uso: php artisan db:seed --class=DemoDataSeeder
 */
class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $customers = $this->ensureCustomers();
        $products = Product::active()->orderBy('id')->get();

        if ($products->isEmpty()) {
            $this->command->warn('Sin productos: ejecuta DatabaseSeeder primero.');
            return;
        }

        $this->seedOrders($customers, $products);
        $this->seedReviews($customers, $products);

        $this->command->info('DemoDataSeeder completado.');
    }

    private function ensureCustomers(): array
    {
        $names = [
            ['María Fernández', 'maria@gsw-ecommerce.local'],
            ['Carlos Ramírez', 'carlos@gsw-ecommerce.local'],
            ['Ana Martínez', 'ana@gsw-ecommerce.local'],
            ['José Hernández', 'jose@gsw-ecommerce.local'],
            ['Lucía Gómez', 'lucia@gsw-ecommerce.local'],
        ];

        $customers = [];
        foreach ($names as [$name, $email]) {
            $customers[] = User::firstOrCreate(
                ['email' => $email],
                ['name' => $name, 'password' => Hash::make('password'), 'role' => 'customer']
            );
        }

        return array_merge(User::where('role', 'customer')->get()->all(), $customers);
    }

    private function seedOrders(array $customers, $products): void
    {
        $statuses = [Order::STATUS_DELIVERED, Order::STATUS_DELIVERED, Order::STATUS_DELIVERED, Order::STATUS_SHIPPED, Order::STATUS_PROCESSING];
        $created = 0;

        for ($i = 0; $i < 25; $i++) {
            $user = $customers[$i % count($customers)];
            $product = $products[($i * 7) % count($products)];
            $qty = ($i % 3) + 1;
            $status = $statuses[$i % count($statuses)];

            $subtotal = round($product->price * $qty, 2);
            $tax = round($subtotal * 0.13, 2);

            $order = Order::create([
                'user_id' => $user->id,
                'order_number' => Order::generateOrderNumber(),
                'status' => $status,
                'subtotal' => $subtotal,
                'tax' => $tax,
                'total' => round($subtotal + $tax, 2),
                'shipping_address' => 'Dirección de prueba ' . ($i + 1) . ', San Salvador',
                'payment_method' => Order::PAY_CARD,
                'payment_status' => Order::PAYMENT_PAID,
                'card_brand' => 'Visa',
                'card_last4' => str_pad((string) (1000 + $i), 4, '0', STR_PAD_LEFT),
                'created_at' => now()->subDays(30 - ($i % 30)),
                'updated_at' => now()->subDays(30 - ($i % 30)),
            ]);

            $order->items()->create([
                'product_id' => $product->id,
                'quantity' => $qty,
                'price' => $product->price,
                'total' => $subtotal,
            ]);

            if ($status === Order::STATUS_DELIVERED) {
                $order->update(['delivered_at' => now()->subDays(20 - ($i % 20))]);
            }

            $created++;
        }

        $this->command->info("Pedidos demo creados: {$created}");
    }

    private function seedReviews(array $customers, $products): void
    {
        $titles = [
            5 => ['Excelente compra', 'Superó mis expectativas', 'Lo recomiendo', 'Calidad top'],
            4 => ['Muy bueno', 'Buena relación precio-calidad', 'Casi perfecto', 'Me gustó mucho'],
            3 => ['Aceptable', 'Cumple lo básico', 'Regular', 'Podría mejorar'],
        ];
        $comments = [
            5 => ['Llegó rápido y en perfecto estado. Lo volvería a comprar sin dudarlo.', 'La calidad es mejor de lo que esperaba por este precio. Totalmente recomendado.', 'Funciona de maravilla. El chatbot me ayudó a elegirlo y acertó.'],
            4 => ['Buen producto en general, solo el empaque podría mejorar.', 'Cumple con lo descrito. Buena compra por el precio.', 'Me gusta mucho, aunque tardó un poco en llegar.'],
            3 => ['Está bien por el precio, pero esperaba un poco más de calidad.', 'Funciona, aunque hay detalles a mejorar.', 'Aceptable. Cumple su función sin destacar.'],
        ];

        $created = 0;
        foreach ($products as $index => $product) {
            $numReviews = $index % 4 === 0 ? 3 : ($index % 2 === 0 ? 2 : 1);

            for ($r = 0; $r < $numReviews; $r++) {
                $user = $customers[($index + $r) % count($customers)];

                $exists = Review::where('user_id', $user->id)
                    ->where('product_id', $product->id)
                    ->exists();
                if ($exists) continue;

                $rating = [5, 5, 4, 4, 4, 3][($index + $r) % 6];

                Review::create([
                    'user_id' => $user->id,
                    'product_id' => $product->id,
                    'rating' => $rating,
                    'title' => $titles[$rating][($index + $r) % 4],
                    'comment' => $comments[$rating][($index + $r) % 3],
                    'is_verified' => true,
                ]);
                $created++;
            }
        }

        $this->command->info("Reseñas demo creadas: {$created}");
    }
}
