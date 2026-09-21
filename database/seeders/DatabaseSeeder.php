<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Services\SemanticSearchService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Crear admin
        User::create([
            'name' => 'Administrador GSW',
            'email' => 'admin@gsw-ecommerce.local',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // Crear usuario de prueba
        User::create([
            'name' => 'Cliente de Prueba',
            'email' => 'cliente@gsw-ecommerce.local',
            'password' => Hash::make('password'),
            'role' => 'customer',
        ]);

        // Crear categorías
        $categories = [
            ['name' => 'Deportes', 'description' => 'Artículos deportivos y fitness'],
            ['name' => 'Tecnología', 'description' => 'Dispositivos electrónicos y gadgets'],
            ['name' => 'Hogar', 'description' => 'Artículos para el hogar y decoración'],
            ['name' => 'Moda', 'description' => 'Ropa, calzado y accesorios'],
            ['name' => 'Outdoor', 'description' => 'Equipamiento para actividades al aire libre'],
        ];

        $categoryModels = [];
        foreach ($categories as $cat) {
            $categoryModels[] = Category::create([
                'name' => $cat['name'],
                'slug' => Str::slug($cat['name']),
                'description' => $cat['description'],
            ]);
        }

        // Crear productos de ejemplo con descripciones ricas para búsqueda semántica
        // Imágenes: URLs directas de Unsplash CDN (verificadas, no requieren descarga)
        $img = fn(string $id) => "https://images.unsplash.com/{$id}?q=80&w=800&auto=format&fit=crop";
        $products = [
            [
                'category' => 0,
                'name' => 'Zapatillas Running Ultra',
                'description' => 'Zapatillas de running de alto rendimiento con amortiguación reactiva. Ideales para corredores que buscan velocidad y comodidad en superficies mixtas. Suela de caucho resistente con tracción multidireccional.',
                'short_description' => 'Running de alto rendimiento con amortiguación reactiva',
                'price' => 89.99,
                'compare_price' => 119.99,
                'sku' => 'DEP-001',
                'stock' => 50,
                'image' => $img('photo-1542291026-7eec264c27ff'),
            ],
            [
                'category' => 0,
                'name' => 'Chaqueta Impermeable Deportiva',
                'description' => 'Chaqueta deportiva impermeable y transpirable, perfecta para correr bajo la lluvia. Tela ligera con tecnología de repelencia al agua. Ideal para actividades outdoor en clima húmedo.',
                'short_description' => 'Protección contra lluvia para corredores',
                'price' => 65.00,
                'compare_price' => null,
                'sku' => 'DEP-002',
                'stock' => 30,
                'image' => $img('photo-1591047139829-d91aecb6caea'),
            ],
            [
                'category' => 1,
                'name' => 'Audífonos Bluetooth Pro',
                'description' => 'Audífonos inalámbricos con cancelación de ruido activa. Batería de 30 horas, sonido envolvente con graves profundos. Resistentes al sudor, ideales para escuchar música mientras haces ejercicio.',
                'short_description' => 'Inalámbricos con cancelación de ruido para deporte',
                'price' => 45.99,
                'compare_price' => 59.99,
                'sku' => 'TEC-001',
                'stock' => 100,
                'image' => $img('photo-1505740420928-5e560c06d30e'),
            ],
            [
                'category' => 1,
                'name' => 'Smartwatch Fitness Tracker',
                'description' => 'Reloj inteligente con monitor de ritmo cardíaco, GPS integrado y seguimiento de sueño. Resistente al agua hasta 50 metros. Más de 20 modos de ejercicio preconfigurados.',
                'short_description' => 'Reloj inteligente con GPS y monitor cardíaco',
                'price' => 129.99,
                'compare_price' => 159.99,
                'sku' => 'TEC-002',
                'stock' => 40,
                'image' => $img('photo-1523275335684-37898b6baf30'),
            ],
            [
                'category' => 2,
                'name' => 'Set de Cocina Antiadherente',
                'description' => 'Juego de 5 piezas de cocina antiadherente de alta calidad. Apta para todos los tipos de cocina incluyendo inducción. Fácil limpieza y distribución uniforme del calor.',
                'short_description' => 'Juego de 5 ollas antiadherentes para cocina',
                'price' => 79.99,
                'compare_price' => null,
                'sku' => 'HOG-001',
                'stock' => 25,
                'image' => $img('photo-1556911220-bff31c812dba'),
            ],
            [
                'category' => 3,
                'name' => 'Mochila Urban Explorer',
                'description' => 'Mochila resistente al agua con compartimento acolchado para laptop de 15 pulgadas. Diseño ergonómico con tirantes ajustables. Múltiples bolsillos organizadores para accesorios.',
                'short_description' => 'Mochila resistente con espacio para laptop',
                'price' => 39.99,
                'compare_price' => 55.00,
                'sku' => 'MOD-001',
                'stock' => 60,
                'image' => $img('photo-1553062407-98eeb64c6a62'),
            ],
            [
                'category' => 4,
                'name' => 'Bicicleta de Montaña 21V',
                'description' => 'Bicicleta de montaña con cuadro de aluminio liviano, 21 velocidades Shimano, frenos de disco hidráulicos. Suspension delantera ajustable. Ideal para senderos y ciclismo de aventura.',
                'short_description' => 'MTB con cuadro de aluminio y 21 velocidades',
                'price' => 349.99,
                'compare_price' => 449.99,
                'sku' => 'OUT-001',
                'stock' => 15,
                'image' => $img('photo-1485965120184-e220f721d03e'),
            ],
            [
                'category' => 4,
                'name' => 'Carpa Camping 4 Personas',
                'description' => 'Carpa impermeable para 4 personas con doble pared. Fácil montaje con sistema de bastones pre-conectados. Ventilación superior y piso de polietileno. Ideal para acampar en cualquier clima.',
                'short_description' => 'Carpa impermeable fácil de montar para 4 personas',
                'price' => 89.99,
                'compare_price' => null,
                'sku' => 'OUT-002',
                'stock' => 20,
                'image' => $img('photo-1504280390367-361c6d9f38f4'),
            ],
        ];

        $productModels = [];
        foreach ($products as $prod) {
            $category = $categoryModels[$prod['category']];
            $productModels[] = Product::create([
                'name' => $prod['name'],
                'slug' => Str::slug($prod['name']),
                'description' => $prod['description'],
                'short_description' => $prod['short_description'],
                'price' => $prod['price'],
                'compare_price' => $prod['compare_price'],
                'sku' => $prod['sku'],
                'stock' => $prod['stock'],
                'image' => $prod['image'] ?? null,
                'category_id' => $category->id,
                'is_active' => true,
                'is_featured' => in_array($prod['sku'], ['DEP-001', 'TEC-001', 'TEC-002', 'OUT-001']),
            ]);
        }

        // Generar embeddings para todos los productos
        $this->command->info('Generando embeddings para productos...');
        try {
            $searchService = app(SemanticSearchService::class);
            foreach ($productModels as $product) {
                $searchService->generateProductEmbedding($product);
            }
            $this->command->info('Embeddings generados exitosamente');
        } catch (\Exception $e) {
            $this->command->warn('No se pudieron generar embeddings: ' . $e->getMessage());
        }

        // Reseñas de ejemplo
        $this->call(\Database\Seeders\ReviewSeeder::class);
    }
}
