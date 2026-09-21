CREATE TABLE IF NOT EXISTS servicios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    estado VARCHAR(20) NOT NULL DEFAULT 'ACTIVO'
);

INSERT INTO servicios(nombre, estado) VALUES
('Tienda GSWStore', 'ACTIVO'),
('Chatbot RAG', 'ACTIVO'),
('Búsqueda semántica', 'ACTIVO'),
('Sitio de respaldo', 'INACTIVO');

CREATE TABLE IF NOT EXISTS users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    email_verified_at TIMESTAMP NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('customer', 'admin') NOT NULL DEFAULT 'customer',
    billing_name VARCHAR(150) NULL,
    doc_type VARCHAR(10) NULL,
    doc_number VARCHAR(20) NULL,
    nrc VARCHAR(20) NULL,
    phone VARCHAR(20) NULL,
    billing_address VARCHAR(500) NULL,
    city VARCHAR(100) NULL,
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

CREATE TABLE IF NOT EXISTS password_reset_tokens (
    email VARCHAR(255) NOT NULL PRIMARY KEY,
    token VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL
);

CREATE TABLE IF NOT EXISTS sessions (
    id VARCHAR(255) NOT NULL PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    payload LONGTEXT NOT NULL,
    last_activity INT NOT NULL,
    INDEX sessions_user_id_index (user_id),
    INDEX sessions_last_activity_index (last_activity)
);

CREATE TABLE IF NOT EXISTS categories (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    description TEXT NULL,
    image VARCHAR(255) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

CREATE TABLE IF NOT EXISTS products (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    description TEXT NOT NULL,
    short_description TEXT NULL,
    price DECIMAL(10, 2) NOT NULL,
    compare_price DECIMAL(10, 2) NULL,
    sku VARCHAR(255) NOT NULL UNIQUE,
    stock INT NOT NULL DEFAULT 0,
    image VARCHAR(255) NULL,
    images JSON NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    is_featured TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    CONSTRAINT products_category_id_foreign FOREIGN KEY (category_id) REFERENCES categories (id) ON DELETE CASCADE,
    INDEX products_is_active_index (is_active),
    INDEX products_is_featured_index (is_featured),
    INDEX products_category_id_is_active_index (category_id, is_active)
);

CREATE TABLE IF NOT EXISTS product_embeddings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id BIGINT UNSIGNED NOT NULL,
    embedding JSON NOT NULL,
    model_name VARCHAR(255) NOT NULL DEFAULT 'all-MiniLM-L6-v2',
    created_at TIMESTAMP NULL,
    CONSTRAINT product_embeddings_product_id_foreign FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE,
    INDEX product_embeddings_product_id_index (product_id)
);

CREATE TABLE IF NOT EXISTS cart_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL,
    product_id BIGINT UNSIGNED NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    session_id VARCHAR(255) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    CONSTRAINT cart_items_user_id_foreign FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
    CONSTRAINT cart_items_product_id_foreign FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE,
    UNIQUE KEY cart_user_product_unique (user_id, product_id),
    UNIQUE KEY cart_session_product_unique (session_id, product_id)
);

CREATE TABLE IF NOT EXISTS orders (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    order_number VARCHAR(255) NOT NULL UNIQUE,
    status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') NOT NULL DEFAULT 'pending',
    subtotal DECIMAL(10, 2) NOT NULL,
    tax DECIMAL(10, 2) NOT NULL DEFAULT 0,
    total DECIMAL(10, 2) NOT NULL,
    shipping_address TEXT NULL,
    billing_address TEXT NULL,
    payment_method VARCHAR(255) NOT NULL DEFAULT 'cod',
    payment_status ENUM('pending', 'paid', 'failed') NOT NULL DEFAULT 'pending',
    card_brand VARCHAR(20) NULL,
    card_last4 VARCHAR(4) NULL,
    receipt_image VARCHAR(255) NULL,
    courier VARCHAR(60) NULL,
    tracking_number VARCHAR(40) NULL,
    estimated_delivery DATETIME NULL,
    shipped_at DATETIME NULL,
    delivered_at DATETIME NULL,
    invoice_uuid VARCHAR(40) NULL,
    invoice_control VARCHAR(60) NULL,
    invoice_seal VARCHAR(80) NULL,
    notes TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    CONSTRAINT orders_user_id_foreign FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
    INDEX orders_status_index (status),
    INDEX orders_payment_status_index (payment_status)
);

CREATE TABLE IF NOT EXISTS order_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id BIGINT UNSIGNED NOT NULL,
    product_id BIGINT UNSIGNED NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    total DECIMAL(10, 2) NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    CONSTRAINT order_items_order_id_foreign FOREIGN KEY (order_id) REFERENCES orders (id) ON DELETE CASCADE,
    CONSTRAINT order_items_product_id_foreign FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE RESTRICT
);

CREATE TABLE IF NOT EXISTS reviews (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    product_id BIGINT UNSIGNED NOT NULL,
    rating TINYINT UNSIGNED NOT NULL,
    title VARCHAR(255) NULL,
    comment TEXT NULL,
    is_verified TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    CONSTRAINT reviews_user_id_foreign FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
    CONSTRAINT reviews_product_id_foreign FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE,
    UNIQUE KEY reviews_user_product_unique (user_id, product_id)
);

CREATE TABLE IF NOT EXISTS tracking_events (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(120) NOT NULL,
    description VARCHAR(500) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    CONSTRAINT tracking_events_order_id_foreign FOREIGN KEY (order_id) REFERENCES orders (id) ON DELETE CASCADE
);

INSERT INTO users(name, email, password, role, created_at, updated_at) VALUES
('Administrador GSW', 'admin@gsw-ecommerce.local', '$2y$10$iRq3w6h5orkyXtv5j6/ldO7xg1QsbmLC/ng6dtCeh7d0GO50JR9Sa', 'admin', NOW(), NOW()),
('Cliente de Prueba', 'cliente@gsw-ecommerce.local', '$2y$10$iRq3w6h5orkyXtv5j6/ldO7xg1QsbmLC/ng6dtCeh7d0GO50JR9Sa', 'customer', NOW(), NOW());

INSERT INTO categories(name, slug, description, created_at, updated_at) VALUES
('Deportes', 'deportes', 'Artículos deportivos y fitness', NOW(), NOW()),
('Tecnología', 'tecnologia', 'Dispositivos electrónicos y gadgets', NOW(), NOW()),
('Hogar', 'hogar', 'Artículos para el hogar y decoración', NOW(), NOW()),
('Moda', 'moda', 'Ropa, calzado y accesorios', NOW(), NOW()),
('Outdoor', 'outdoor', 'Equipamiento para actividades al aire libre', NOW(), NOW());

INSERT INTO products(category_id, name, slug, description, short_description, price, compare_price, sku, stock, image, is_active, is_featured, created_at, updated_at) VALUES
(1, 'Zapatillas Running Ultra', 'zapatillas-running-ultra', 'Zapatillas de running de alto rendimiento con amortiguación reactiva. Ideales para corredores que buscan velocidad y comodidad en superficies mixtas. Suela de caucho resistente con tracción multidireccional.', 'Running de alto rendimiento con amortiguación reactiva', 89.99, 119.99, 'DEP-001', 50, 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?q=80&w=800&auto=format&fit=crop', 1, 1, NOW(), NOW()),
(1, 'Chaqueta Impermeable Deportiva', 'chaqueta-impermeable-deportiva', 'Chaqueta deportiva impermeable y transpirable, perfecta para correr bajo la lluvia. Tela ligera con tecnología de repelencia al agua. Ideal para actividades outdoor en clima húmedo.', 'Protección contra lluvia para corredores', 65.00, NULL, 'DEP-002', 30, 'https://images.unsplash.com/photo-1591047139829-d91aecb6caea?q=80&w=800&auto=format&fit=crop', 1, 0, NOW(), NOW()),
(2, 'Audífonos Bluetooth Pro', 'audifonos-bluetooth-pro', 'Audífonos inalámbricos con cancelación de ruido activa. Batería de 30 horas, sonido envolvente con graves profundos. Resistentes al sudor, ideales para escuchar música mientras haces ejercicio.', 'Inalámbricos con cancelación de ruido para deporte', 45.99, 59.99, 'TEC-001', 100, 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?q=80&w=800&auto=format&fit=crop', 1, 1, NOW(), NOW()),
(2, 'Smartwatch Fitness Tracker', 'smartwatch-fitness-tracker', 'Reloj inteligente con monitor de ritmo cardíaco, GPS integrado y seguimiento de sueño. Resistente al agua hasta 50 metros. Más de 20 modos de ejercicio preconfigurados.', 'Reloj inteligente con GPS y monitor cardíaco', 129.99, 159.99, 'TEC-002', 40, 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?q=80&w=800&auto=format&fit=crop', 1, 1, NOW(), NOW()),
(3, 'Set de Cocina Antiadherente', 'set-de-cocina-antiadherente', 'Juego de 5 piezas de cocina antiadherente de alta calidad. Apta para todos los tipos de cocina incluyendo inducción. Fácil limpieza y distribución uniforme del calor.', 'Juego de 5 ollas antiadherentes para cocina', 79.99, NULL, 'HOG-001', 25, 'https://images.unsplash.com/photo-1556911220-bff31c812dba?q=80&w=800&auto=format&fit=crop', 1, 0, NOW(), NOW()),
(4, 'Mochila Urban Explorer', 'mochila-urban-explorer', 'Mochila resistente al agua con compartimento acolchado para laptop de 15 pulgadas. Diseño ergonómico con tirantes ajustables. Múltiples bolsillos organizadores para accesorios.', 'Mochila resistente con espacio para laptop', 39.99, 55.00, 'MOD-001', 60, 'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?q=80&w=800&auto=format&fit=crop', 1, 0, NOW(), NOW()),
(5, 'Bicicleta de Montaña 21V', 'bicicleta-de-montana-21v', 'Bicicleta de montaña con cuadro de aluminio liviano, 21 velocidades Shimano, frenos de disco hidráulicos. Suspension delantera ajustable. Ideal para senderos y ciclismo de aventura.', 'MTB con cuadro de aluminio y 21 velocidades', 349.99, 449.99, 'OUT-001', 15, 'https://images.unsplash.com/photo-1485965120184-e220f721d03e?q=80&w=800&auto=format&fit=crop', 1, 1, NOW(), NOW()),
(5, 'Carpa Camping 4 Personas', 'carpa-camping-4-personas', 'Carpa impermeable para 4 personas con doble pared. Fácil montaje con sistema de bastones pre-conectados. Ventilación superior y piso de polietileno. Ideal para acampar en cualquier clima.', 'Carpa impermeable fácil de montar para 4 personas', 89.99, NULL, 'OUT-002', 20, 'https://images.unsplash.com/photo-1504280390367-361c6d9f38f4?q=80&w=800&auto=format&fit=crop', 1, 0, NOW(), NOW());

INSERT INTO reviews(user_id, product_id, rating, title, comment, is_verified, created_at, updated_at) VALUES
(2, 1, 5, 'Excelentes para correr', 'Muy cómodas y con gran amortiguación. Las recomiendo.', 1, NOW(), NOW()),
(2, 3, 5, 'Gran sonido', 'La cancelación de ruido funciona muy bien.', 1, NOW(), NOW()),
(2, 6, 4, 'Buena mochila', 'Resistente y espaciosa, ideal para la laptop.', 1, NOW(), NOW());
