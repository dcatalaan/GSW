# Bitácora — GSWStore

E-commerce completo con búsqueda semántica, chatbot RAG con IA y facturación electrónica estilo DTE de El Salvador. Proyecto final de la materia Gestión de Servidores Web.

Equipo: Ángel Fernández, Ricardo Retana, Kevin Umaña, Diego Catalán.

## Stack

| Capa | Tecnología |
|---|---|
| Backend | Laravel 11, PHP 8.3 |
| Base de datos | PostgreSQL + pgvector (producción), SQLite (desarrollo local) |
| Servidor | Debian 12, Nginx + PHP-FPM |
| Búsqueda semántica | sentence-transformers `all-MiniLM-L6-v2` (vectores de 384 dims) vía microservicio Flask |
| Chatbot IA | Groq API (modelos Llama 3 / Qwen, capa gratuita) |
| PDF | dompdf (facturas), BaconQrCode en SVG (código QR, sin necesidad de GD) |
| Estilo | Sistema de diseño propio inspirado en bloome: fondo crema `#FFFAF9`, coral `#FF6568`, DM Sans + Cormorant Garamond |

## Módulos

- **Catálogo**: 8 productos en 5 categorías, fichas con galería, stock, descuentos y productos relacionados.
- **Carrito y checkout**: IVA 13 %, tres métodos de pago. Tarjeta (detección de marca Visa/Mastercard/Amex/Discover por BIN + validación Luhn, solo se guardan marca y últimos 4), transferencia a cuenta ficticia de Banco Agrícola con foto del comprobante, y efectivo.
- **Pedidos y rastreo**: línea de tiempo (recibido, en preparación, enviado, entregado), paquetería asignada con guía y ETA, mapa de rastreo en vivo con progreso calculado por tiempo, historial de eventos.
- **Reseñas verificadas**: solo quien recibió el producto puede reseñarlo; formulario con estrellas, insignia de comprador verificado, invitación a reseñar desde el pedido entregado.
- **Chatbot RAG**: Retrieval (productos relevantes de la BD) + Generation (Groq). Responde cualquier tema y reconduce a productos con enlaces clicables. Historial en `sessionStorage` (se borra al cerrar la pestaña). Sin emojis en respuestas.
- **Perfil de cliente**: datos personales y de facturación (razón social, DUI/NIT, NRC, teléfono, dirección), que alimentan al receptor de la factura.
- **Factura electrónica PDF**: se genera al crear el pedido con formato DTE (códigos de generación/control/sello, QR, emisor/receptor, ítems, IVA, valor en letras, responsables). Descargable desde el pedido.
- **Panel admin**: dashboard con estadísticas, CRUD de productos y categorías, gestión de pedidos (estados, comprobantes) y regeneración de embeddings.
- **Roles**: cliente y administrador con middleware propio.

## Decisiones técnicas

- SQLite en Windows para desarrollo (sin pgvector); la búsqueda semántica real vive en producción con pgvector y el microservicio de embeddings.
- El LLM nunca recibe datos sensibles de tarjetas; del pago solo se persiste marca y últimos 4 dígitos.
- `exclude_unless` en la validación del checkout: los campos del método de pago no elegido se ignoran por completo.
- Timeouts acotados en llamadas a Groq (12s + 10s) para no superar el `max_execution_time` de PHP.
- CSP de Nginx permite imágenes de Unsplash (fotos del catálogo por URL, sin subir archivos).

## Despliegue en Debian 12

Scripts en `deploy/scripts/`, configuración en `deploy/nginx/gsw-ecommerce.conf`:

1. `sudo bash deploy/scripts/install.sh [--seed]` — instala Nginx, PHP 8.3 (repo Sury), PostgreSQL + pgvector (repo PGDG), Composer, configura `.env`, migraciones, storage link, permisos, SSL autofirmado y el servicio `gsw-embeddings` (systemd). La rama de Git usada es `master`.
2. Completar `GROQ_API_KEY` en `/var/www/gsw-ecommerce/.env` (clave gratuita en console.groq.com).
3. `sudo bash deploy/scripts/deploy.sh` — despliegues posteriores (modo mantenimiento, pull, migraciones, cachés, permisos).
4. `sudo bash deploy/scripts/backup.sh` — respaldo de BD (peer auth postgres), archivos, config de Nginx y `.env`. Retención 7 días.
5. `sudo bash deploy/scripts/monitor.sh` — estado de servicios, disco, memoria y últimos errores.

## Cuentas de prueba (seeders)

- Admin: `admin@gsw-ecommerce.local` / `password`
- Cliente: `cliente@gsw-ecommerce.local` / `password`

## Estructura relevante

- `app/Services/ChatbotRAGService.php` — RAG con Groq
- `app/Services/SemanticSearchService.php` — búsqueda híbrida texto + vectores
- `app/Services/InvoiceService.php` + `resources/views/invoices/dte.blade.php` — DTE
- `app/Models/` — Product, Order, Review, TrackingEvent, User, Category, CartItem
- `deploy/` — Nginx + scripts de instalación, despliegue, backup y monitoreo
- `public/css/app.css` — sistema de diseño (modo claro y oscuro)
- `Dockerfile`, `compose.yaml`, `.dockerignore`, `db/init/` — despliegue multicontenedor (Guía 6)

## Guía 6 — Despliegue multicontenedor con Docker

### Arquitectura

```
navegador → localhost:8080 → web:80 → db:5432 (red interna app-net)
                                     ↘ embeddings:8000 (red interna, perfil ia)
```

- `web`: imagen propia `gswstore-web:1.0` (PHP 8.3 + Apache + Laravel). Único servicio publicado.
- `db`: `pgvector/pgvector:pg16` sin puertos al host. Volumen `db_data` en `/var/lib/postgresql/data` + init `db/init/01-extensions.sql` (extensión `vector`; el esquema lo crean las migraciones).
- `embeddings`: Flask + sentence-transformers, solo red interna, healthcheck en `/health`.
- `web` usa `DB_HOST=db` (DNS interno de Compose); `localhost` dentro del contenedor web apunta al propio contenedor, no a MySQL/Postgres.
- `depends_on` con `condition: service_healthy` + `restart: unless-stopped` en los tres servicios.
- `storage_data` conserva comprobantes e imágenes subidas ante recreaciones del contenedor web.

### Decisiones

- Dockerfile de una etapa ordenado para caché: extensiones → composer install → código → entrypoint. Cambiar solo Blade/PHP reutiliza todas las capas previas.
- El entrypoint espera a `db` (hasta 60 s), genera `APP_KEY` si falta, crea el storage link, migra y cachea config en runtime (así respetan las env vars).
- `.env.docker` real excluido de Git (`.env.docker.example` versionado con valores ficticios).
- `down` conserva volúmenes; `down -v` los destruye (no usar con datos).

### Comandos y resultados (plantilla de bitácora de despliegue)

| Fecha | Comando | Resultado esperado |
|---|---|---|
| — | `docker build -t gswstore-web:1.0 .` | Imagen construida sin errores |
| — | `docker compose config` | Configuración válida, `db` sin `ports` |
| — | `docker compose up -d --build` | `web` + `db` en ejecución |
| — | `docker compose exec web getent hosts db` | Resuelve IP interna de `db` |
| — | insertar registro, `down`, `up -d` | El registro permanece |
| — | `docker compose stop db` → 503 → `start db` | La app se recupera sola |

### Respuestas de discusión (Guía 6 / ejercicio)

- **58. ¿Por qué no `localhost`?** Dentro del contenedor, `localhost` es el propio contenedor; `db` lo resuelve el DNS interno de Compose al contenedor de la base.
- **59. ¿Red interna?** Aisla el tráfico entre servicios y permite resolución por nombre sin exponer nada al host.
- **60. ¿Contenedor vs volumen?** Eliminar el contenedor borra su capa efímera; el volumen nombrado sobrevive y conserva los datos.
- **61. ¿Riesgo de publicar 3306?** Expone la base a la red del laboratorio/internet (escaneos, fuerza bruta); solo `web` necesita llegar a ella.
- **62. ¿Ventaja del Dockerfile?** Construcción reproducible y versionada en vez de servidores configurados a mano.
- **63. ¿Fuera del repositorio?** `.env` / `.env.docker` con credenciales, logs, evidencias locales.
- **64. ¿Varias réplicas web?** Escalar `web` tras un balanceador, sesiones y `storage/app/public` compartidos (volumen externo o S3) y migraciones ejecutadas una sola vez.

## Ejercicio ServiceManager (puntos 1–43)

Implementación literal del ejercicio en `servicemanager/`: página de estado PHP + MySQL 8.4 por Compose.

- **A. Diseño**: proyecto `servicemanager`; servicios `web` (imagen propia `gswstore-web:1.0`, PHP 8.3 + Apache) y `db` (MySQL 8.4); `8080:80`; red interna `app-net`; volumen `db_data`; `web` usa `DB_HOST=db`.
- **B. Estructura**: `app/index.php`, `db/init/01-schema.sql`, `Dockerfile`, `compose.yaml`, `.dockerignore`, `.gitignore`, `.env` (+ `.env.example`), `README.md`, `respuestas-discusion.txt`.
- **C. Imagen**: `docker build -t gswstore-web:1.0 .`; verificar con `docker image ls/inspect`; el re-build sin cambios reutiliza caché (las dependencias van antes que el código).
- **D. App**: `index.php` muestra estado web, conexión/versión MySQL, conteo y tabla `servicios`; responde 503 si cae la BD.
- **E. BD**: tabla `servicios(id, nombre, estado)` + 4 registros del proyecto en `01-schema.sql`, que además replica el esquema real (usuarios, categorías, productos, pedidos, reseñas) con datos iniciales; conexión por variables de entorno.
- **F. Compose**: `web`/`db`, red, volumen, `depends_on` healthy, `restart: unless-stopped`; validar con `docker compose config`; desplegar con `up -d --build`.
- **G–I**: comandos de red/DNS, persistencia (`down`/`up -d`) y fallo/recuperación (`stop db` → 503 → `start db`) documentados en `servicemanager/README.md`.
- **J**: README actualizado; respuestas 58–64 en `servicemanager/respuestas-discusion.txt`; `.env` excluido de Git.
