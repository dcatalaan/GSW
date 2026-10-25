# Manual de Uso — GSWStore

Plataforma de comercio electrónico con búsqueda semántica, chatbot RAG con IA y sistema de pagos. Proyecto final de la asignatura de Gestión de Servidores Web.

---

## Índice

1. [Acceso al sistema](#1-acceso-al-sistema)
2. [Navegación general](#2-navegación-general)
3. [Catálogo y búsqueda](#3-catálogo-y-búsqueda)
4. [Carrito de compras](#4-carrito-de-compras)
5. [Checkout y métodos de pago](#5-checkout-y-métodos-de-pago)
6. [Pedidos y rastreo](#6-pedidos-y-rastreo)
7. [Reseñas de productos](#7-reseñas-de-productos)
8. [Favoritos (Wishlist)](#8-favoritos-wishlist)
9. [Perfil de usuario](#9-perfil-de-usuario)
10. [Chatbot inteligente (RAG)](#10-chatbot-inteligente-rag)
11. [Panel de administración](#11-panel-de-administración)
12. [Despliegue en producción](#12-despliegue-en-producción)
13. [Cuentas de prueba](#13-cuentas-de-prueba)
14. [Tecnologías utilizadas](#14-tecnologías-utilizadas)

---

## 1. Acceso al sistema

### URL local
```
http://127.0.0.1:8000
```

### Levantar el servidor (desarrollo)
```bash
cd C:\GSW\gsw-ecommerce
php artisan serve --host=127.0.0.1 --port=8000
```

### Detener el servidor
Presiona `Ctrl+C` en la terminal donde se ejecuta el servidor.

---

## 2. Navegación general

### Barra de navegación (navbar)
- **Logo GSWStore**: clic para ir al inicio
- **Productos**: acceso al catálogo completo
- **Tema claro/oscuro**: botón con icono de sol/luna para cambiar el tema
- **Carrito**: muestra el número de productos en el carrito
- **Menú de usuario** (solo si autenticado): Mi Perfil, Mis Pedidos, Favoritos, Panel Admin (solo admin), Cerrar Sesión

### Pie de página (footer)
- Navegación: Inicio, Productos, Carrito
- Copyright del proyecto

### Chatbot flotante
- Botón circular en la esquina inferior derecha
- Al hacer clic se abre la ventana de chat
- El historial se guarda en la sesión del navegador y se borra al cerrar la pestaña

---

## 3. Catálogo y búsqueda

### Catálogo de productos
- Ruta: `/productos`
- Filtros disponibles:
  - Búsqueda por nombre o descripción
  - Categoría (dropdown)
  - Precio (mínimo y máximo)
  - Ordenar por (más recientes, precio menor a mayor, precio mayor a menor, nombre)
- Cada tarjeta de producto muestra: imagen, nombre, categoría, descripción corta, precio y botón de agregar al carrito
- Productos con descuento muestran el porcentaje de descuento y el precio original tachado
- Productos destacados muestran una insignia de destacado

### Búsqueda semántica
- Ruta: `/buscar?q=término`
- Busca productos por significado, no solo por coincidencia exacta
- Los resultados muestran el porcentaje de relevancia (similitud)
- Si no hay resultados, sugiere categorías para explorar

### Detalle de producto
- Ruta: `/productos/{slug}`
- Muestra: imagen, nombre, categoría, calificación promedio, precio, stock, SKU, descripción, botón de agregar al carrito
- Selector de cantidad antes de agregar
- Reseñas de otros clientes con calificación y comentarios
- Productos relacionados por similitud semántica
- Botón "Guardar en favoritos" (solo para usuarios autenticados)

---

## 4. Carrito de compras

### Agregar productos
- Desde el catálogo: botón de carrito en cada tarjeta
- Desde el detalle: selector de cantidad + botón "Agregar al Carrito"
- Desde el chatbot: botón "Agregar" en las tarjetas de productos

### Ver el carrito
- Ruta: `/carrito`
- Muestra todos los productos agregados con imagen, nombre, categoría, precio, cantidad y subtotal
- Permite modificar la cantidad o eliminar productos
- Botón "Vaciar Carrito" para eliminar todo
- Botón "Seguir Comprando" para volver al catálogo

### Resumen del pedido
- Subtotal de todos los productos
- IVA (13%)
- Total final
- Formulario de dirección de envío
- Notas opcionales
- Selección de método de pago

---

## 5. Checkout y métodos de pago

### Tarjeta (crédito/débito)
- Seleccionar "Tarjeta" en el método de pago
- Ingresar número de tarjeta (se detecta automáticamente Visa, Mastercard, Amex, Discover)
- Ingresar nombre en la tarjeta
- Ingresar fecha de vencimiento (MM/AA)
- Ingresar CVC
- El sistema valida el número con el algoritmo de Luhn
- Solo se guardan los últimos 4 dígitos y la marca (nunca el número completo)
- El pago se marca como "pagado" (simulado)

### Transferencia bancaria
- Seleccionar "Transferencia" en el método de pago
- Se muestra la información de la cuenta ficticia:
  - **Banco:** Banco Agrícola
  - **Número de cuenta:** 123456789
  - **Titular:** Alexander Lopez
- Se debe subir una foto del comprobante de transferencia (obligatorio)
- El pago queda en estado "pendiente" hasta verificación

### Efectivo
- Seleccionar "Efectivo" en el método de pago
- El pago queda en estado "pendiente" hasta la entrega
- Se paga directamente al recibir el pedido

---

## 6. Pedidos y rastreo

### Historial de pedidos
- Ruta: `/pedidos` (requiere autenticación)
- Muestra todos los pedidos del usuario con:
  - Número de pedido
  - Fecha
  - Productos (chips con cantidad × nombre)
  - Total
  - Estado (pendiente, en proceso, enviado, entregado, cancelado)
  - Estado de pago (pagado, pendiente)
- Botón "Ver Detalle" para ver el pedido completo
- Botón para descargar resumen del pedido en PDF

### Detalle de pedido
- Ruta: `/pedidos/{número}`
- Muestra:
  - Número y fecha del pedido
  - Estado actual con insignia
  - Línea de tiempo del pedido (Recibido → En preparación → Enviado → Entregado)
  - Rastreo en vivo (solo cuando está enviado): mapa con ruta, marcador del repartidor, barra de progreso, paquetería, guía
  - Historial de eventos del pedido
  - Información de pago (método, marca de tarjeta, últimos 4 dígitos, comprobante si es transferencia)
  - Dirección de envío
  - Resumen de costos (subtotal, IVA 13%, total)
  - Lista de productos del pedido
  - Botón para descargar resumen

### Rastreo en vivo
- Solo visible cuando el pedido está en estado "enviado"
- Muestra un mapa SVG con la ruta del paquete
- El marcador del repartidor se posiciona según el progreso calculado por tiempo
- Muestra la paquetería asignada (Express SV, Cargo Rápido, Envíos Nacionales)
- Muestra el número de guía y la fecha estimada de entrega
- Se actualiza automáticamente cada 60 segundos
- Estados del paquete: En bodega de origen → Centro de distribución → En ruta de reparto → Cerca de tu dirección → En reparto final

---

## 7. Reseñas de productos

### Ver reseñas
- En la página de detalle de cada producto
- Muestra calificación promedio (estrellas)
- Distribución de calificaciones (barras 1-5 estrellas)
- Lista de reseñas con:
  - Avatar del usuario (inicial)
  - Nombre del usuario
  - Insignia "Comprador verificado" (si compró el producto)
  - Fecha de la reseña
  - Calificación (estrellas)
  - Título y comentario

### Dejar una reseña
- Solo disponible para usuarios que **recibieron** el producto (pedido entregado)
- Formulario con selector de estrellas (1-5)
- Título opcional y comentario opcional
- Después de publicar, aparece la insignia "Comprador verificado"
- Solo se puede dejar una reseña por producto por usuario

### Eliminar reseña
- El autor puede eliminar su propia reseña
- El administrador puede eliminar cualquier reseña

---

## 8. Favoritos (Wishlist)

### Guardar en favoritos
- Botón de corazón en las tarjetas de productos del catálogo
- Botón "Guardar en favoritos" en la página de detalle del producto
- Solo disponible para usuarios autenticados

### Ver favoritos
- Ruta: `/favoritos` (requiere autenticación)
- Muestra todos los productos guardados en formato de cuadrícula
- Si no hay favoritos, muestra un mensaje invitando a explorar productos

### Eliminar de favoritos
- Hacer clic en el corazón nuevamente para quitar de favoritos
- El botón cambia de color cuando el producto está en favoritos

---

## 9. Perfil de usuario

### Ver y editar perfil
- Ruta: `/perfil` (requiere autenticación)
- Datos personales:
  - Nombre (editable)
  - Correo electrónico (no editable)
- Datos de facturación:
  - Nombre / Razón social
  - Tipo de documento (DUI o NIT)
  - Número de documento
  - NRC (opcional)
  - Teléfono
  - Dirección
  - Municipio / Ciudad
- Los datos de facturación se usan para el resumen del pedido
- La dirección de facturación se pre-llena automáticamente en el checkout

---

## 10. Chatbot inteligente (RAG)

### Abrir el chatbot
- Botón circular en la esquina inferior derecha de todas las páginas
- Se abre una ventana de chat

### Cómo funciona
- El chatbot usa **RAG (Retrieval-Augmented Generation)**: busca productos relevantes en la base de datos y los usa como contexto para generar respuestas naturales con IA
- Puede responder **cualquier pregunta**, incluso las que no son sobre la tienda, y siempre redirige a productos relevantes
- Usa la API de Groq (Llama 3 / Qwen) para generar respuestas inteligentes

### Características
- **Historial**: las conversaciones se guardan en la sesión del navegador y se borran al cerrar la pestaña
- **Links clickeables**: cuando el asistente menciona un producto, su nombre es un enlace que lleva a la página del producto
- **Panel de productos**: muestra los productos específicos mencionados por el asistente con imagen, nombre, precio y botón "Agregar"
- **Botones rápidos**: Deportes, Baratos, Recomendar, Tecnología, Destacados
- **Indicador de escritura**: animación mientras el asistente genera la respuesta

### Ejemplos de uso
- "¿Qué productos de tecnología tienen?" → muestra audífonos y smartwatch
- "¿Qué tiempo hace hoy?" → responde sobre el clima y redirige a la chaqueta impermeable
- "Cuéntame un chiste" → responde con humor y redirige a un producto
- "¿Qué me recomiendan para hacer ejercicio?" → sugiere productos deportivos

---

## 11. Panel de administración

### Acceso
- Ruta: `/admin` (requiere rol de administrador)
- Login con credenciales de admin

### Dashboard
- Estadísticas: total de productos, pedidos totales, pedidos pendientes, usuarios, ingresos
- Pedidos recientes (últimos 5)
- Accesos rápidos: Gestionar Productos, Categorías, Pedidos, Búsqueda Semántica

### Gestión de productos
- Ruta: `/admin/productos`
- Lista todos los productos con imagen, nombre, SKU, categoría, precio, stock, estado
- Filtros: búsqueda por nombre, categoría
- Botones: Editar, Eliminar
- Crear nuevo producto: formulario con nombre, SKU, categoría, descripción, precio, stock, imagen (archivo o URL), activo/destacado
- Editar producto: mismo formulario con datos pre-llenados
- Regenerar embeddings: botón para recalcular los vectores semánticos de todos los productos

### Gestión de categorías
- Ruta: `/admin/categorias`
- Lista de categorías con conteo de productos
- Crear nueva categoría: nombre y descripción
- Eliminar categoría

### Gestión de pedidos
- Ruta: `/admin/pedidos`
- Lista de pedidos con número, cliente, items, total, pago, estado, fecha
- Filtro por estado
- Cambiar estado del pedido (dropdown que envía automáticamente)
- Al cambiar a "Enviado": se asigna paquetería, número de guía y fecha estimada de entrega
- Ver comprobante de transferencia (si aplica)

## 12. Despliegue en producción

### Debian 12 (manual)


### Debian 12 (manual)
```bash
# 1. Ejecutar script de instalación
sudo bash deploy/scripts/install.sh --seed

# 2. Configurar GROQ_API_KEY en .env
# Editar /var/www/gsw-ecommerce/.env y agregar:
# GROQ_API_KEY=gsk_tu_clave_aqui

# 3. Para despliegues posteriores
sudo bash deploy/scripts/deploy.sh

# 4. Backup
sudo bash deploy/scripts/backup.sh

# 5. Monitoreo
sudo bash deploy/scripts/monitor.sh
```

### Docker (multicontenedor)
```bash
# 1. Variables de laboratorio
cp .env.docker.example .env.docker

# 2. Validar configuración
docker compose config

# 3. Construir y desplegar
docker build -t gswstore-web:1.0 .
docker compose up -d --build

# 4. Con búsqueda semántica completa
docker compose --profile ia up -d --build

# 5. Migraciones + seeders (si la BD está vacía)
docker compose exec web php artisan migrate --force
docker compose exec web php artisan db:seed --force
```

### Estructura de archivos de despliegue
```
deploy/
├── nginx/
│   └── gsw-ecommerce.conf    # Configuración de Nginx
└── scripts/
    ├── install.sh             # Instalación completa en Debian 12
    ├── deploy.sh              # Despliegue/actualización
    ├── backup.sh              # Respaldo de BD y archivos
    └── monitor.sh             # Monitoreo de servicios
```

---

## 14. Cuentas de prueba

| Rol | Email | Contraseña | Acceso |
|---|---|---|---|
| Administrador | admin@gsw-ecommerce.local | password | Panel admin, gestión de productos/categorías/pedidos |
| Cliente | cliente@gsw-ecommerce.local | password | Catálogo, carrito, checkout, pedidos, favoritos, perfil, reseñas |

### Credenciales de base de datos (producción)
- **Nombre:** gsw_ecommerce
- **Usuario:** gsw_user
- **Contraseña:** generada automáticamente por install.sh

### Credenciales de base de datos (Docker)
- **Nombre:** gsw_ecommerce
- **Usuario:** gsw_user
- **Contraseña:** secret (configurable en .env.docker)

---

## Tecnologías utilizadas

| Componente | Tecnología |
|---|---|
| Sistema Operativo | Debian GNU/Linux 12 (Bookworm) |
| Servidor Web | Nginx (proxy inverso → PHP-FPM) |
| Lenguaje y Framework | PHP 8.3 con Laravel 11 |
| Base de Datos Relacional | PostgreSQL 15 + pgvector |
| Almacén Vectorial | PostgreSQL con extensión pgvector |
| Generación de Embeddings | sentence-transformers (all-MiniLM-L6-v2) |
| Chatbot IA | Groq API (Llama 3 / Qwen, capa gratuita) |
| PDF | dompdf (resúmenes), BaconQrCode en SVG |
| Diseño | Contemporary Design System (DM Sans + Cormorant Garamond) |
| Seguridad TLS | Let's Encrypt / certbot |
| Despliegue | Docker multicontenedor (Compose) |

---

## Estructura del proyecto

```
gsw-ecommerce/
├── app/
│   ├── Http/Controllers/
│   │   ├── Admin/AdminController.php
│   │   ├── Auth/AuthController.php
│   │   ├── CartController.php
│   │   ├── ChatbotController.php
│   │   ├── HomeController.php
│   │   ├── OrderController.php
│   │   ├── ProductController.php
│   │   ├── ProfileController.php
│   │   ├── ReviewController.php
│   │   └── WishlistController.php
│   ├── Mail/
│   │   └── OrderConfirmation.php
│   ├── Models/
│   │   ├── CartItem.php, Category.php, Order.php, OrderItem.php
│   │   ├── Product.php, ProductEmbedding.php, Review.php
│   │   ├── TrackingEvent.php, User.php, Wishlist.php
│   ├── Providers/
│   │   └── AppServiceProvider.php
│   └── Services/
│       ├── ChatbotRAGService.php
│       └── SemanticSearchService.php
├── config/
│   ├── semantic.php         # Configuración de búsqueda semántica
│   └── services.php         # Configuración de Groq API
├── database/
│   ├── migrations/          # 12 migraciones
│   └── seeders/
│       ├── DatabaseSeeder.php
│       ├── BulkProductSeeder.php
│       ├── DemoDataSeeder.php
│       └── ReviewSeeder.php
├── deploy/
│   ├── nginx/               # Configuración de Nginx
│   └── scripts/             # install.sh, deploy.sh, backup.sh, monitor.sh
├── docker/
│   ├── embeddings/          # Microservicio Flask de embeddings
│   └── entrypoint.sh        # Entrypoint del contenedor web
├── db/init/
│   └── 01-extensions.sql    # Crear extensión vector en PostgreSQL
├── public/
│   ├── css/app.css          # Sistema de diseño completo
│   ├── js/app.js            # JavaScript principal
│   └── images/              # Imágenes estáticas
├── resources/views/
│   ├── admin/               # Dashboard, productos, categorías, pedidos
│   ├── auth/                # Login, registro
│   ├── cart/                # Carrito y checkout
│   ├── components/          # Chatbot, product-card, icon
│   ├── emails/              # Correos transaccionales
│   ├── home.blade.php
│   ├── layouts/             # App, navbar, footer
│   ├── orders/              # Historial y detalle de pedidos
│   ├── products/            # Catálogo y detalle
│   ├── profile/             # Perfil y facturación
│   ├── search.blade.php
│   └── wishlist/            # Favoritos
├── routes/
│   └── web.php              # Todas las rutas
├── Dockerfile
├── compose.yaml
├── .dockerignore
├── .env.example
└── bitacora.md
```

---

*Manual generado para GSWStore — Proyecto final de Gestión de Servidores Web — 2026*
*Equipo: Ángel Fernández, Ricardo Retana, Kevin Umaña, Diego Catalán*
