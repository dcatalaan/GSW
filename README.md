# GSW E-Commerce

Plataforma de comercio electrónico con **búsqueda semántica potenciada por inteligencia artificial**, desarrollada como proyecto de la asignatura de Gestión de Servidores Web.

## Equipo

- Ángel Fernández (009016)
- Ricardo Retana (086822)
- Kevin Umaña (026321)
- Diego Catalán (043522)

## Tecnologías

| Componente | Tecnología |
|---|---|
| **Sistema Operativo** | Debian GNU/Linux 12 (Bookworm) |
| **Servidor Web** | Nginx (proxy inverso → PHP-FPM) |
| **Lenguaje y Framework** | PHP 8.3 con Laravel 11 |
| **Base de Datos Relacional** | PostgreSQL 15 |
| **Almacén Vectorial** | PostgreSQL con extensión pgvector |
| **Generación de Embeddings** | sentence-transformers (all-MiniLM-L6-v2) |
| **Diseño** | Contemporary Design System (Jost + Overpass Mono) |
| **Seguridad TLS** | Let's Encrypt / certbot |

## Arquitectura

```
┌─────────────────────────────────────────┐
│              Nginx (Reverse Proxy)       │
│         HTTPS / TLS / Static Files       │
├─────────────────────────────────────────┤
│              PHP-FPM 8.3                 │
│         Laravel 11 (MVC + Blade)        │
├──────────────┬──────────────────────────┤
│  PostgreSQL  │  Sentence-Transformers   │
│  + pgvector  │  (Flask API, port 8000)  │
│  (port 5432) │  Embeddings generation   │
└──────────────┴──────────────────────────┘
```

## Funcionalidades

### Público
- Página principal con productos destacados
- **Búsqueda semántica** potenciada por IA (entendimiento de intención)
- Catálogo de productos con filtros (categoría, precio, orden)
- Carrito de compras (persistente por sesión/usuario)
- Historial de pedidos

### Administración
- Dashboard con estadísticas
- CRUD de productos (con generación automática de embeddings)
- Gestión de categorías
- Gestión de pedidos (actualización de estado)
- Regeneración de embeddings

### Búsqueda Semántica
- Generación de embeddings vectoriales (384 dimensiones) con `all-MiniLM-L6-v2`
- Búsqueda por similitud coseno usando pgvector con índice HNSW
- Búsqueda híbrida: 70% semántica + 30% textual
- Ejemplo: "algo para correr en la lluvia" → encuentra "Chaqueta Impermeable Deportiva"

## Instalación

### Requisitos
- Debian 12 (Bookworm) con acceso root/sudo
- Dominio apuntando al servidor (o IP para pruebas)

### Instalación Automática

```bash
# Subir el proyecto al servidor
scp -r gsw-ecommerce/ root@tu-servidor:/tmp/

# Conectar al servidor
ssh root@tu-servidor

# Ejecutar script de instalación
chmod +x /tmp/gsw-ecommerce/deploy/scripts/install.sh
/tmp/gsw-ecommerce/deploy/scripts/install.sh --seed
```

### Instalación Manual

```bash
# 1. Actualizar sistema
apt update && apt upgrade -y

# 2. Instalar dependencias
apt install -y nginx php8.3-fpm php8.3-cli php8.3-pgsql \
    php8.3-mbstring php8.3-xml php8.3-curl php8.3-zip \
    php8.3-gd php8.3-bcmath php8.3-intl php8.3-redis \
    postgresql postgresql-contrib python3 python3-pip python3-venv \
    git curl wget unzip ufw certbot python3-certbot-nginx

# 3. PostgreSQL + pgvector
sudo -u postgres psql -c "CREATE USER gsw_user WITH ENCRYPTED PASSWORD 'tu_password';"
sudo -u postgres psql -c "CREATE DATABASE gsw_ecommerce OWNER gsw_user;"
apt install -y postgresql-15-pgvector
sudo -u postgres psql -d gsw_ecommerce -c "CREATE EXTENSION IF NOT EXISTS vector;"

# 4. Desplegar aplicación
cp -r /tmp/gsw-ecommerce /var/www/gsw-ecommerce
cd /var/www/gsw-ecommerce
composer install --no-dev --optimize-autoloader
cp .env.example .env
php artisan key:generate
# Editar .env con tus credenciales de BD
php artisan migrate --force
php artisan db:seed
php artisan storage:link

# 5. Configurar Nginx
cp /var/www/gsw-ecommerce/deploy/nginx/gsw-ecommerce.conf /etc/nginx/sites-available/
ln -sf /etc/nginx/sites-available/gsw-ecommerce.conf /etc/nginx/sites-enabled/
rm -f /etc/nginx/sites-enabled/default
nginx -t && systemctl reload nginx

# 6. SSL
certbot --nginx -d tu-dominio.com

# 7. Servicio de embeddings
cd /opt
python3 -m venv gsw-embeddings/venv
source gsw-embeddings/venv/bin/activate
pip install flask sentence-transformers numpy
# Copiar app.py del proyecto a /opt/gsw-embeddings/
systemctl enable gsw-embeddings
systemctl start gsw-embeddings
```

## Estructura del Proyecto

```
gsw-ecommerce/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/AdminController.php    # Panel administrativo
│   │   │   ├── Auth/AuthController.php      # Autenticación
│   │   │   ├── CartController.php           # Carrito de compras
│   │   │   ├── HomeController.php           # Inicio + búsqueda
│   │   │   ├── OrderController.php          # Pedidos
│   │   │   └── ProductController.php        # Productos
│   │   └── Middleware/
│   │       └── EnsureUserIsAdmin.php
│   ├── Models/                              # Eloquent models
│   │   ├── User.php
│   │   ├── Product.php
│   │   ├── ProductEmbedding.php
│   │   ├── Category.php
│   │   ├── CartItem.php
│   │   ├── Order.php
│   │   └── OrderItem.php
│   ├── Providers/
│   │   └── AppServiceProvider.php
│   └── Services/
│       └── SemanticSearchService.php        # Motor de búsqueda semántica
├── config/
│   ├── app.php
│   ├── database.php
│   └── semantic.php                         # Config de embeddings
├── database/
│   ├── migrations/                          # 7 migraciones
│   └── seeders/
│       └── DatabaseSeeder.php               # 8 productos de ejemplo
├── deploy/
│   ├── nginx/
│   │   └── gsw-ecommerce.conf              # Config Nginx
│   └── scripts/
│       ├── install.sh                       # Instalación completa
│       ├── deploy.sh                        # Despliegue/actualización
│       ├── backup.sh                        # Respaldo
│       └── monitor.sh                       # Monitoreo
├── public/
│   ├── css/app.css                          # Contemporary Design System
│   ├── js/app.js                            # Interacciones
│   └── index.php
├── resources/views/                         # Plantillas Blade
│   ├── layouts/ (app, navbar, footer)
│   ├── components/ (product-card)
│   ├── home.blade.php
│   ├── search.blade.php
│   ├── products/ (index, show)
│   ├── cart/index.blade.php
│   ├── orders/ (index, show)
│   ├── auth/ (login, register)
│   └── admin/ (dashboard, products/*, categories, orders)
├── routes/web.php
├── .env / .env.example
├── composer.json
└── README.md
```

## URLs Principales

| URL | Descripción |
|---|---|
| `/` | Página principal |
| `/buscar?q=query` | Búsqueda semántica |
| `/productos` | Catálogo de productos |
| `/productos/{slug}` | Detalle de producto |
| `/carrito` | Carrito de compras |
| `/pedidos` | Historial de pedidos (auth) |
| `/login` | Iniciar sesión |
| `/registro` | Crear cuenta |
| `/admin` | Panel de administración (admin) |

## Cuentas de Prueba

| Rol | Email | Contraseña |
|---|---|---|
| Administrador | admin@gsw-ecommerce.local | password |
| Cliente | cliente@gsw-ecommerce.local | password |

## Scripts de Administración

```bash
# Instalación completa
./deploy/scripts/install.sh --seed

# Despliegue/actualización
./deploy/scripts/deploy.sh

# Respaldo de BD y archivos
./deploy/scripts/backup.sh

# Monitoreo de servicios
./deploy/scripts/monitor.sh

# Regenerar embeddings
php artisan tinker --execute="app(\App\Services\SemanticSearchService::class)->regenerateAllEmbeddings();"
```

## Diseño

El proyecto utiliza el **Contemporary Design System** con:
- **Colores**: Primary `#C800DF`, Secondary `#E60076`
- **Tipografía**: Jost (principal), Overpass Mono (monoespaciada)
- **Estilo**: Minimalista, moderno, bold, playful
- **Layout**: Bento grids, responsive, dark mode ready
- **Accesibilidad**: WCAG 2.2 AA, focus states, semantic HTML

## Despliegue con Docker (Guía 6)

Aplicación multicontenedor equivalente al despliegue en VM: `web` (Laravel + PHP 8.3 + Apache, imagen propia) + `db` (PostgreSQL + pgvector, interna) + `embeddings` (Flask, perfil `ia`).

```
navegador → localhost:8080 → web:80 → db:5432 (red interna app-net)
                                       ↘ embeddings:8000 (red interna)
```

```bash
# 1. Variables de laboratorio (no versionar el archivo real)
cp .env.docker.example .env.docker

# 2. Validar la configuración antes de desplegar
docker compose config

# 3. Construir imagen propia y desplegar
docker build -t gswstore-web:1.0 .
docker compose up -d --build

# 4. Estado y logs
docker compose ps
docker compose logs -f db
docker compose logs --tail=50 web

# 5. Migraciones + seeders iniciales (si la BD está vacía)
docker compose exec web php artisan migrate --force
docker compose exec web php artisan db:seed --force

# 6. Con IA (búsqueda semántica completa)
docker compose --profile ia up -d --build
```

### Pruebas (matriz Guía 6)

| Prueba | Comando | Resultado esperado |
|---|---|---|
| Red interna | `docker network inspect gsw-ecommerce_app-net` | `web` y `db` en `app-net` |
| DNS interno | `docker compose exec web getent hosts db` | Resuelve IP de `db` |
| Aislamiento | `docker compose ps` / `config` | `db` sin puertos publicados |
| Persistencia | insertar registro, `down`, `up -d` | El registro permanece (volumen `db_data`) |
| Fallo/recuperación | `docker compose stop db` (app responde 503) luego `start db` | La app se recupera sola |
| Recreación web | `docker compose up -d --force-recreate web` | Sin pérdida de datos |

### Detención

```bash
docker compose stop    # pausa sin borrar nada
docker compose down    # elimina contenedores y red, conserva volúmenes
```

Nunca usar `down -v` con datos importantes: elimina también los volúmenes nombrados.

## Licencia

MIT License — Proyecto Académico 2026
