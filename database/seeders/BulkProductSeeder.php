<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * 100 productos adicionales (20 por categoría) con descripciones ricas
 * para la búsqueda semántica. Idempotente por SKU: omite los existentes.
 *
 * Uso: php artisan db:seed --class=BulkProductSeeder
 */
class BulkProductSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [];
        foreach (['deportes', 'tecnologia', 'hogar', 'moda', 'outdoor'] as $slug) {
            $cat = Category::where('slug', $slug)->first();
            if (!$cat) {
                $this->command->error("Categoría '{$slug}' no existe. Ejecuta DatabaseSeeder primero.");
                return;
            }
            $categories[$slug] = $cat;
        }

        $items = array_merge(
            $this->deportes(),
            $this->tecnologia(),
            $this->hogar(),
            $this->moda(),
            $this->outdoor(),
        );

        $created = 0;
        $skipped = 0;
        foreach ($items as [$catSlug, $sku, $name, $short, $desc, $price, $compare, $stock, $featured]) {
            if (Product::where('sku', $sku)->exists()) {
                $skipped++;
                continue;
            }
            Product::create([
                'name' => $name,
                'slug' => Str::slug($name),
                'description' => $desc,
                'short_description' => $short,
                'price' => $price,
                'compare_price' => $compare,
                'sku' => $sku,
                'stock' => $stock,
                'image' => 'https://picsum.photos/seed/' . strtolower($sku) . '/800/600',
                'category_id' => $categories[$catSlug]->id,
                'is_active' => true,
                'is_featured' => $featured,
            ]);
            $created++;
        }

        $this->command->info("Creados: {$created} | Omitidos (ya existían): {$skipped}");
    }

    /** [categoria, sku, nombre, corta, descripción, precio, compare, stock, destacado] */
    private function deportes(): array
    {
        return [
            ['deportes', 'DEP-003', 'Medias de Compresión Running', 'Soporte muscular para largas distancias', 'Medias de compresión graduada que mejoran la circulación y reducen la fatiga en carreras largas. Tejido transpirable con refuerzo en talón y puntera.', 14.99, null, 80, false],
            ['deportes', 'DEP-004', 'Guantes de Gimnasio Pro', 'Agarre firme y protección palmar', 'Guantes de entrenamiento con acolchado palmar de gel y muñequeras ajustables. Ideales para pesas, dominadas y crossfit.', 19.99, 24.99, 65, false],
            ['deportes', 'DEP-005', 'Balón de Fútbol Oficial', 'Tamaño 5, cosido a máquina', 'Balón de fútbol tamaño oficial con cubierta resistente al agua y cámara de butilo que retiene el aire por semanas. Para cancha natural y sintética.', 29.99, null, 45, false],
            ['deportes', 'DEP-006', 'Balón de Baloncesto Interior', 'Agarre premium para duela', 'Balón de baloncesto para interiores con superficie de composite suave y surcos profundos para mejor control del drible.', 34.99, null, 40, false],
            ['deportes', 'DEP-007', 'Cuerda para Saltar Ajustable', 'Cardio intenso en poco espacio', 'Cuerda de saltar con cable de acero recubierto y mangos ergonómicos con rodamientos. Longitud ajustable para cualquier estatura.', 12.99, null, 90, false],
            ['deportes', 'DEP-008', 'Esterilla de Yoga Antideslizante', '6mm de grosor con correa', 'Esterilla de yoga de 6 milímetros con superficie antideslizante de doble cara. Incluye correa de transporte y es libre de PVC.', 27.99, 34.99, 55, false],
            ['deportes', 'DEP-009', 'Par de Mancuernas 5kg', 'Recubiertas de neopreno', 'Par de mancuernas hexagonales de 5 kilogramos con recubrimiento de neopreno que protege el piso y da agarre seguro.', 39.99, null, 35, false],
            ['deportes', 'DEP-010', 'Set Bandas de Resistencia x5', 'De 10 a 50 libras de tensión', 'Set de 5 bandas elásticas de resistencia progresiva con asas, anclaje de puerta y bolsa. Para entrenar fuerza en casa o el parque.', 24.99, 29.99, 70, true],
            ['deportes', 'DEP-011', 'Botella Térmica Deportiva 1L', 'Frío 24h, acero inoxidable', 'Botella térmica de acero inoxidable de doble pared que mantiene bebidas frías 24 horas. Tapa deportiva antiderrames de un litro.', 22.99, null, 100, false],
            ['deportes', 'DEP-012', 'Gorra de Running Ultraligera', 'Con paneles de ventilación', 'Gorra de running ultraligera con paneles de malla para ventilación y banda absorbente de sudor. Ajuste por velcro.', 16.99, null, 75, false],
            ['deportes', 'DEP-013', 'Calcetines Running Pack x3', 'Sin costuras, antiampollas', 'Pack de 3 pares de calcetines técnicos sin costuras que previenen ampollas. Compresión en arco y ventilación superior.', 13.99, 17.99, 110, false],
            ['deportes', 'DEP-014', 'Mochila de Hidratación 2L', 'Para trail y ciclismo', 'Mochila chaleco de hidratación con bolsa de agua de 2 litros y múltiples bolsillos para geles, celular y llaves. Ideal para trail running.', 49.99, null, 28, false],
            ['deportes', 'DEP-015', 'Rodillera Deportiva Ajustable', 'Soporte para articulaciones', 'Rodillera con estabilizadores laterales y correas ajustables que dan soporte a la articulación sin limitar el movimiento.', 18.99, null, 60, false],
            ['deportes', 'DEP-016', 'Cronómetro Deportivo con Alarma', '100 memorias de vueltas', 'Cronómetro con capacidad para 100 vueltas, alarma, hora dual y resistencia a salpicaduras. Para entrenadores y atletas.', 15.99, null, 50, false],
            ['deportes', 'DEP-017', 'Pelotas de Tenis Pack x3', 'Bote consistente en arcilla', 'Tubo de 3 pelotas de tenis presurizadas con fieltro duradero y bote consistente en canchas de arcilla y dura.', 9.99, null, 120, false],
            ['deportes', 'DEP-018', 'Raqueta de Tenis 270g', 'Marco ligero de grafito', 'Raqueta de tenis de 270 gramos con marco de grafito y encordado de potencia. Equilibrio perfecto para jugadores intermedios.', 79.99, 99.99, 22, true],
            ['deportes', 'DEP-019', 'Patineta Completa Maple', 'Trucks de aluminio 5 pulgadas', 'Patineta completa de 7 capas de maple canadiense con trucks de aluminio y ruedas de uretano de 54 milímetros.', 69.99, null, 18, false],
            ['deportes', 'DEP-020', 'Guayos de Fútbol Césped', 'Tacos firmes de TPU', 'Guayos de fútbol con tacos firmes de TPU para tracción en césped natural. Corte clásico con cuello acolchado.', 59.99, 74.99, 32, false],
            ['deportes', 'DEP-021', 'Camiseta Dry-Fit Entreno', 'Secado rápido y ligero', 'Camiseta de entrenamiento con tecnología de secado rápido que expulsa el sudor. Ligera, fresca y sin etiquetas que irriten.', 21.99, null, 85, false],
            ['deportes', 'DEP-022', 'Short Deportivo con Bolsillo', 'Con malla interior y cierre', 'Short deportivo con malla interior transpirable y bolsillo lateral con cierre para llevar el celular al correr.', 24.99, null, 70, false],
        ];
    }

    private function tecnologia(): array
    {
        return [
            ['tecnologia', 'TEC-003', 'Mouse Inalámbrico Ergonómico', 'Silencioso con 6 botones', 'Mouse inalámbrico ergonómico de 2.4 GHz con clics silenciosos y 6 botones programables. Batería de hasta 18 meses.', 25.99, null, 90, false],
            ['tecnologia', 'TEC-004', 'Teclado Mecánico RGB', 'Switches azules intercambiables', 'Teclado mecánico con switches azules, retroiluminación RGB por tecla y estructura de aluminio. Ideal para escribir y jugar.', 74.99, 89.99, 30, true],
            ['tecnologia', 'TEC-005', 'Hub USB-C 7 en 1', 'HDMI 4K, SD y 100W', 'Adaptador hub USB-C con salida HDMI 4K, lector SD y microSD, 3 puertos USB y carga pasante de 100 vatios para laptops.', 42.99, null, 55, false],
            ['tecnologia', 'TEC-006', 'Cargador GaN 65W Dual', 'Carga rápida para laptop y móvil', 'Cargador de pared con tecnología de nitruro de galio, 65 vatios en dos puertos USB-C. Carga una laptop y un celular a la vez.', 36.99, 44.99, 70, false],
            ['tecnologia', 'TEC-007', 'Batería Externa 20000mAh', 'Carga rápida 22.5W con display', 'Power bank de 20000 miliamperios con carga rápida, pantalla digital de porcentaje y dos salidas USB más una tipo C.', 32.99, null, 85, false],
            ['tecnologia', 'TEC-008', 'Cable USB-C Trenzado 2m', 'Nylon reforzado de 2 metros', 'Cable USB-C a USB-C trenzado de nylon de 2 metros, soporta carga rápida y transferencia de datos a alta velocidad.', 11.99, null, 150, false],
            ['tecnologia', 'TEC-009', 'Webcam Full HD 1080p', 'Con micrófono y tapa de privacidad', 'Cámara web Full HD con enfoque automático, micrófono dual con reducción de ruido y tapa física de privacidad.', 54.99, null, 40, false],
            ['tecnologia', 'TEC-010', 'Micrófono Condensador USB', 'Ideal para podcast y streaming', 'Micrófono condensador USB con patrón cardioide, control de ganancia y salida de audífonos para monitoreo en vivo.', 69.99, 84.99, 25, true],
            ['tecnologia', 'TEC-011', 'Parlante Bluetooth Portátil', 'Sonido 360 y 20h de batería', 'Parlante portátil con sonido envolvente de 360 grados, 20 horas de batería y resistencia al agua IPX7 para exteriores.', 59.99, null, 45, false],
            ['tecnologia', 'TEC-012', 'Tablet 10 Pulgadas 128GB', 'Pantalla Full HD y 8GB RAM', 'Tableta de 10 pulgadas con pantalla Full HD, 128 gigas de almacenamiento y batería para todo el día. Ideal para estudiar y ver series.', 189.99, 229.99, 20, false],
            ['tecnologia', 'TEC-013', 'Funda Protectora para Laptop 15', 'Neopreno acolchado repelente', 'Funda de neopreno acolchado para laptops de hasta 15.6 pulgadas, repelente al agua con bolsillo extra para accesorios.', 19.99, null, 95, false],
            ['tecnologia', 'TEC-014', 'Soporte de Aluminio para Laptop', 'Plegable con 6 niveles de altura', 'Soporte plegable de aluminio con 6 niveles de altura ajustable que mejora la postura y la ventilación de la laptop.', 28.99, null, 60, false],
            ['tecnologia', 'TEC-015', 'SSD Externo 1TB USB-C', 'Hasta 1050 MB/s de lectura', 'Disco sólido externo de 1 terabyte con conexión USB-C y velocidades de hasta 1050 megabytes por segundo. Compacto y resistente a golpes.', 89.99, 109.99, 38, false],
            ['tecnologia', 'TEC-016', 'Memoria MicroSD 128GB', 'Clase 10 para 4K y drones', 'Tarjeta microSD de 128 gigas clase 10 con adaptador, ideal para cámaras de acción, drones y celulares.', 17.99, null, 130, false],
            ['tecnologia', 'TEC-017', 'Lámpara LED de Escritorio USB', '3 temperaturas de color táctil', 'Lámpara de escritorio LED con control táctil, 3 temperaturas de color y brazo flexible. Se alimenta por USB.', 23.99, null, 65, false],
            ['tecnologia', 'TEC-018', 'Base Refrigerante para Laptop', '5 ventiladores silenciosos', 'Base refrigerante con 5 ventiladores silenciosos y ajuste de altura para mantener fresca la laptop en sesiones largas.', 31.99, null, 48, false],
            ['tecnologia', 'TEC-019', 'Router WiFi 6 Doble Banda', 'Cobertura hasta 120 m2', 'Router WiFi 6 de doble banda con 4 antenas de alta ganancia y control parental desde la app. Cobertura estable para toda la casa.', 79.99, 94.99, 26, false],
            ['tecnologia', 'TEC-020', 'Cámara de Seguridad WiFi 360', 'Visión nocturna y detección', 'Cámara de seguridad WiFi con rotación de 360 grados, visión nocturna infrarroja y alertas de movimiento al celular.', 44.99, null, 52, false],
            ['tecnologia', 'TEC-021', 'Mini Dron con Cámara 4K', '25 min de vuelo y retorno', 'Mini dron plegable con cámara 4K estabilizada, 25 minutos de vuelo y retorno automático al perder señal.', 149.99, 179.99, 15, true],
            ['tecnologia', 'TEC-022', 'Lector de Libros 6 Pulgadas', 'Tinta electrónica sin reflejos', 'Lector electrónico de 6 pulgadas con pantalla de tinta electrónica sin reflejos y luz cálida ajustable para leer de noche.', 119.99, null, 24, false],
        ];
    }

    private function hogar(): array
    {
        return [
            ['hogar', 'HOG-002', 'Juego de Sábanas Algodón', 'Queen 4 piezas 400 hilos', 'Juego de sábanas de algodón de 400 hilos para cama queen: sábana plana, ajustable y dos fundas. Suaves y transpirables.', 49.99, 64.99, 42, false],
            ['hogar', 'HOG-003', 'Almohada Memory Foam', 'Ergonómica cervical', 'Almohada de espuma viscoelástica con diseño ergonómico que alinea cuello y columna. Funda lavable de bambú.', 29.99, null, 58, false],
            ['hogar', 'HOG-004', 'Manta Polar Extra Suave', '220x240 cm antipeeling', 'Manta polar extra suave de 220 por 240 centímetros con tratamiento antipeeling. Abriga sin pesar.', 24.99, null, 66, false],
            ['hogar', 'HOG-005', 'Cortina Blackout Térmica', 'Bloquea 99% de luz', 'Par de cortinas blackout térmicas que bloquean el 99 por ciento de la luz y aíslan del calor. Ojillos metálicos incluidos.', 39.99, 49.99, 36, false],
            ['hogar', 'HOG-006', 'Lámpara de Pie Moderna', 'Pantalla de lino regulable', 'Lámpara de pie con pantalla de lino y base metálica. Compatible con focos LED regulables para ambientar la sala.', 69.99, null, 22, false],
            ['hogar', 'HOG-007', 'Reloj de Pared Minimalista', '30 cm silencioso', 'Reloj de pared minimalista de 30 centímetros con movimiento silencioso, ideal para sala u oficina.', 21.99, null, 48, false],
            ['hogar', 'HOG-008', 'Espejo Decorativo Redondo', '60 cm con marco dorado', 'Espejo redondo de 60 centímetros con marco metálico dorado que amplía visualmente cualquier habitación.', 54.99, null, 20, false],
            ['hogar', 'HOG-009', 'Maceta de Cerámica con Plato', 'Diseño nórdico 20 cm', 'Maceta de cerámica estilo nórdico de 20 centímetros con plato y orificio de drenaje para plantas de interior.', 16.99, null, 72, false],
            ['hogar', 'HOG-010', 'Aspiradora Robot Inteligente', 'Mapeo láser y app', 'Aspiradora robot con navegación láser, mapeo de la casa por app y 120 minutos de autonomía. Vuelve sola a su base.', 249.99, 299.99, 12, true],
            ['hogar', 'HOG-011', 'Plancha de Vapor Vertical', 'Calienta en 30 segundos', 'Plancha de vapor vertical que calienta en 30 segundos y elimina arrugas sin tabla de planchar. Depósito de 260 mililitros.', 44.99, null, 34, false],
            ['hogar', 'HOG-012', 'Tendedero Plegable de Alas', '15 metros de tendedero', 'Tendedero plegable tipo alas con 15 metros de espacio para colgar ropa. Estructura de acero resistente a la intemperie.', 38.99, null, 28, false],
            ['hogar', 'HOG-013', 'Organizador de Zapatos 10 Pares', 'Torre apilable de tela', 'Torre organizadora de tela no tejida para 10 pares de zapatos, apilable y con estructura firme.', 18.99, 23.99, 54, false],
            ['hogar', 'HOG-014', 'Set de Cuchillos 6 Piezas', 'Acero inoxidable con bloque', 'Set de 6 cuchillos de acero inoxidable con bloque de madera: chef, pan, multiusos y pelador.', 59.99, null, 30, false],
            ['hogar', 'HOG-015', 'Tabla para Picar Bambú', 'Grande con ranura jugos', 'Tabla para picar de bambú grande con ranura para jugos y asa para colgar. Antibacteriana y duradera.', 15.99, null, 80, false],
            ['hogar', 'HOG-016', 'Tazas de Cerámica Pack x4', '350 ml colores mate', 'Pack de 4 tazas de cerámica de 350 mililitros en colores mate. Aptas para microondas y lavavajillas.', 26.99, null, 62, false],
            ['hogar', 'HOG-017', 'Difusor de Aromas Ultrasónico', '300 ml con luces LED', 'Difusor ultrasónico de 300 mililitros con luces LED de colores y apagado automático. Para aceites esenciales.', 31.99, 39.99, 44, false],
            ['hogar', 'HOG-018', 'Velas Aromáticas Pack x3', 'Cera de soya 30h c/u', 'Pack de 3 velas aromáticas de cera de soya con 30 horas de duración cada una. Aromas de vainilla, lavanda y café.', 19.99, null, 58, false],
            ['hogar', 'HOG-019', 'Alfombra de Sala 160x230', 'Pelo corto antideslizante', 'Alfombra de sala de 160 por 230 centímetros con pelo corto y base antideslizante. Fácil de aspirar.', 89.99, 109.99, 16, false],
            ['hogar', 'HOG-020', 'Perchero de Pie Metálico', '8 ganchos giratorios', 'Perchero de pie metálico con 8 ganchos giratorios y base estable. Para abrigos, bolsos y sombreros.', 34.99, null, 32, false],
            ['hogar', 'HOG-021', 'Juego de Toallas 3 Piezas', 'Algodón egipcio absorbente', 'Juego de 3 toallas de algodón egipcio de alta absorción: cuerpo, manos y cara. Secado rápido.', 28.99, null, 50, false],
        ];
    }

    private function moda(): array
    {
        return [
            ['moda', 'MOD-002', 'Jeans Slim Hombre', 'Denim elástico oscuro', 'Jeans slim de denim elástico oscuro con corte moderno y costuras reforzadas. Comodidad todo el día.', 44.99, 54.99, 46, false],
            ['moda', 'MOD-003', 'Camisetas Algodón Pack x3', 'Cuello redondo varios colores', 'Pack de 3 camisetas 100 por ciento algodón de cuello redondo en colores básicos. Preencogidas y suaves.', 29.99, null, 78, false],
            ['moda', 'MOD-004', 'Sudadera con Capucha', 'Felpa perchada abrigadora', 'Sudadera hoodie de felpa perchada con capucha forrada y bolsillo canguro. Abrigo ideal para clima fresco.', 39.99, null, 52, false],
            ['moda', 'MOD-005', 'Zapatos Casuales de Cuero', 'Suela antideslizante cosida', 'Zapatos casuales de cuero genuino con suela antideslizante cosida y plantilla acolchada para caminar todo el día.', 74.99, 89.99, 26, true],
            ['moda', 'MOD-006', 'Sandalias de Verano', 'Plantilla anatómica EVA', 'Sandalias con plantilla anatómica de EVA y correas ajustables. Ligeras y frescas para el verano.', 24.99, null, 64, false],
            ['moda', 'MOD-007', 'Cinturón de Cuero Clásico', 'Hebilla metálica ajustable', 'Cinturón de cuero genuino con hebilla metálica clásica y 5 ojales. Ancho estándar de 3.5 centímetros.', 22.99, null, 70, false],
            ['moda', 'MOD-008', 'Billetera de Cuero Bifold', '8 tarjetas + RFID', 'Billetera bifold de cuero con espacio para 8 tarjetas, protección RFID contra clonación y billetes.', 27.99, 34.99, 56, false],
            ['moda', 'MOD-009', 'Gafas de Sol Polarizadas', 'Protección UV400', 'Gafas de sol polarizadas con protección UV400 y montura ligera. Reducen el reflejo al conducir.', 19.99, null, 88, false],
            ['moda', 'MOD-010', 'Gorra Plana Snapback', 'Talla ajustable', 'Gorra plana estilo snapback con cierre ajustable y visera rígida. Diseño urbano unisex.', 15.99, null, 92, false],
            ['moda', 'MOD-011', 'Bufanda de Lana Tejida', 'Extra larga y abrigadora', 'Bufanda de lana tejida extra larga, suave y abrigadora para el frío. No pica ni pierde forma.', 18.99, null, 44, false],
            ['moda', 'MOD-012', 'Guantes de Invierno Táctiles', 'Compatibles con pantallas', 'Guantes de invierno con puntas táctiles compatibles con pantallas y forro polar interior.', 16.99, null, 60, false],
            ['moda', 'MOD-013', 'Chaqueta de Denim Clásica', 'Mezclilla rígida premium', 'Chaqueta de mezclilla rígida premium con botones metálicos y dos bolsillos de pecho. Estilo atemporal.', 64.99, 79.99, 30, false],
            ['moda', 'MOD-014', 'Vestido de Verano Floral', 'Tela fresca con vuelo', 'Vestido de verano con estampado floral, tela fresca con vuelo y tirantes ajustables.', 42.99, null, 38, false],
            ['moda', 'MOD-015', 'Falda Plisada Midi', 'Cintura elástica', 'Falda plisada midi con cintura elástica y forro interior. Elegante para oficina o salidas.', 34.99, null, 42, false],
            ['moda', 'MOD-016', 'Blusa de Satén', 'Manga larga elegante', 'Blusa de satén suave con manga larga y botones forrados. Brillo sutil para ocasiones especiales.', 31.99, 38.99, 36, false],
            ['moda', 'MOD-017', 'Traje de Baño Deportivo', 'Secado rápido + UV', 'Traje de baño deportivo de secado rápido con protección UV y soporte interno. Ideal para natación.', 36.99, null, 40, false],
            ['moda', 'MOD-018', 'Reloj Casual Minimalista', 'Correa de malla ajustable', 'Reloj casual minimalista con esfera limpia y correa de malla ajustable. Resistente a salpicaduras.', 49.99, 59.99, 34, false],
            ['moda', 'MOD-019', 'Pulsera de Acero Trenzada', 'Hipoalergénica ajustable', 'Pulsera de acero inoxidable trenzado, hipoalergénica y ajustable. No se oxida ni pierde color.', 14.99, null, 96, false],
            ['moda', 'MOD-020', 'Collar de Plata 925', 'Cadena 45 cm con dije', 'Collar de plata ley 925 con cadena de 45 centímetros y dije minimalista. Incluye estuche de regalo.', 33.99, null, 48, false],
            ['moda', 'MOD-021', 'Anillo de Acero Mate', 'Tallas 7 a 12', 'Anillo de acero inoxidable con acabado mate disponible en tallas 7 a 12. Hipoalergénico.', 12.99, 15.99, 104, false],
        ];
    }

    private function outdoor(): array
    {
        return [
            ['outdoor', 'OUT-003', 'Mochila de Senderismo 60L', 'Con cubrelluvia incluido', 'Mochila de senderismo de 60 litros con espalda ventilada, cubrelluvia incluido y múltiples accesos al compartimento principal.', 119.99, 139.99, 18, true],
            ['outdoor', 'OUT-004', 'Saco de Dormir -5°C', 'Compacto tipo momia', 'Saco de dormir tipo momia para temperaturas de hasta menos 5 grados. Relleno sintético que se comprime a la mitad.', 84.99, null, 24, false],
            ['outdoor', 'OUT-005', 'Colchoneta Inflable Aislante', 'Con almohada integrada', 'Colchoneta inflable con aislamiento térmico y almohada integrada. Se infla en minutos y cabe en la mochila.', 46.99, null, 32, false],
            ['outdoor', 'OUT-006', 'Linterna Frontal 1000 Lúmenes', 'Recargable USB con sensor', 'Linterna frontal de 1000 lúmenes recargable por USB con sensor de movimiento para encendido sin manos.', 27.99, 33.99, 58, false],
            ['outdoor', 'OUT-007', 'Navaja Multiusos 12 en 1', 'Acero inoxidable plegable', 'Navaja multiusos plegable de acero inoxidable con 12 funciones: cuchilla, sierra, destornilladores, abrelatas y más.', 21.99, null, 74, false],
            ['outdoor', 'OUT-008', 'Cantimplora de Acero 1L', 'Doble pared térmica', 'Cantimplora de acero inoxidable de 1 litro con doble pared que conserva la temperatura por horas.', 19.99, null, 82, false],
            ['outdoor', 'OUT-009', 'Cocina Portátil de Gas', 'Con encendido piezoeléctrico', 'Cocina portátil de gas con encendido piezoeléctrico y válvula reguladora. Compatible con cartuchos estándar.', 39.99, null, 36, false],
            ['outdoor', 'OUT-010', 'Set de Cubiertos para Camping', 'Acero con estuche', 'Set de cubiertos de acero inoxidable para camping con estuche de transporte. Incluye cuchara, tenedor, cuchillo y destapador.', 13.99, null, 90, false],
            ['outdoor', 'OUT-011', 'Hamaca Doble con Mosquitero', 'Soporta 200 kg', 'Hamaca doble con mosquitero integrado que soporta hasta 200 kilogramos. Incluye correas para árboles.', 54.99, 64.99, 26, false],
            ['outdoor', 'OUT-012', 'Bastones de Trekking Par', 'Aluminio ajustables', 'Par de bastones de trekking de aluminio con altura ajustable y empuñaduras ergonómicas de corcho.', 42.99, null, 30, false],
            ['outdoor', 'OUT-013', 'Poncho de Lluvia Reutilizable', 'Con capucha y bolsa', 'Poncho impermeable reutilizable con capucha ajustable que cubre cuerpo y mochila. Incluye su propia bolsa.', 12.99, null, 98, false],
            ['outdoor', 'OUT-014', 'Botas de Senderismo Impermeables', 'Membrana y suela Vibram', 'Botas de senderismo impermeables con membrana transpirable y suela de alto agarre para terrenos difíciles.', 129.99, 149.99, 20, true],
            ['outdoor', 'OUT-015', 'Silla Plegable de Camping', 'Soporta 120 kg con portavasos', 'Silla plegable de camping que soporta 120 kilogramos, con portavasos y bolsa de transporte.', 32.99, null, 44, false],
            ['outdoor', 'OUT-016', 'Mesa Plegable de Aluminio', 'Enrollable ultraligera', 'Mesa plegable de aluminio con superficie enrollable ultraligera. Perfecta para campamento y picnic.', 48.99, null, 28, false],
            ['outdoor', 'OUT-017', 'Hielera Rígida 28L', 'Hielo hasta 5 días', 'Hielera rígida de 28 litros que conserva el hielo hasta 5 días. Tapa con portavasos y drenaje.', 74.99, 89.99, 18, false],
            ['outdoor', 'OUT-018', 'Binoculares 10x42 HD', 'Prismas BaK-4 impermeables', 'Binoculares 10 por 42 con prismas BaK-4, óptica HD e impermeables para avistamiento de aves y paisajes.', 89.99, null, 22, false],
            ['outdoor', 'OUT-019', 'Brújula Lensática Militar', 'Con clinómetro y lupa', 'Brújula lensática de grado militar con clinómetro, lupa y mira de precisión. Carcasa metálica resistente.', 24.99, null, 52, false],
            ['outdoor', 'OUT-020', 'Kit de Primeros Auxilios 120pz', 'Estuche impermeable', 'Kit de primeros auxilios con 120 piezas en estuche impermeable compacto. Esencial para senderismo y campamento.', 26.99, null, 62, false],
            ['outdoor', 'OUT-021', 'Cuerda Paracord 30m', '550 lb de resistencia', 'Cuerda paracord de 30 metros con resistencia de 550 libras y 7 hilos internos. Múltiples usos en campamento.', 11.99, null, 108, false],
            ['outdoor', 'OUT-022', 'Tienda de Campaña 2 Personas', 'Ultraligera 3 estaciones', 'Tienda de campaña ultraligera para 2 personas, apta para 3 estaciones con vestíbulo para equipo.', 99.99, 119.99, 16, false],
        ];
    }
}
