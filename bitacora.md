# Bitácora — GSWStore

E-commerce completo con búsqueda semántica, chatbot RAG con IA. Proyecto final de la materia Gestión de Servidores Web.

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
