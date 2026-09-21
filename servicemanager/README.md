# ServiceManager — Sistema Web Multicontenedor (Guía 6)

Sistema web para consultar el estado de servicios, desplegado con Dockerfile y Docker Compose: servicio web PHP + Apache y base MySQL en red interna.

## A. Diseño inicial

1. **Proyecto**: `servicemanager`. **Servicios**: `web` (PHP 8.3 + Apache, imagen propia `gswstore-web:1.0`) y `db` (MySQL 8.4).
2. **Arquitectura**: `navegador → localhost:8080 → web:80 → db:3306` (red interna `app-net`); `db_data` persiste `/var/lib/mysql`.
3. **Puertos**: externo `8080`, interno `80`. MySQL **no** publica 3306 al host.
4. **Red interna**: `app-net` (bridge, creada por Compose). **Volumen**: `db_data` (nombrado) para MySQL.
5. **Comunicación**: `web` usa `DB_HOST=db` (DNS interno de Compose). `localhost` dentro del contenedor web apuntaría al propio contenedor, no a MySQL.

## B. Estructura y función de cada archivo

| Archivo | Función |
|---|---|
| `app/index.php` | Página ServiceManager: estado web, conexión/versión MySQL, conteo y tabla `servicios` (503 si cae la BD) |
| `db/init/01-schema.sql` | Tabla `servicios` + esquema completo del proyecto (usuarios, categorías, productos, pedidos, reseñas) con datos iniciales |
| `Dockerfile` | Receta de la imagen web propia |
| `compose.yaml` | Define servicios, red, volumen, healthcheck, dependencias y reinicio |
| `.dockerignore` | Excluye archivos del contexto de build |
| `.gitignore` | Excluye `.env`, logs y evidencias del repo |
| `.env` / `.env.example` | Credenciales ficticias de laboratorio (el real no se versiona) |
| `README.md` | Este documento |
| `respuestas-discusion.txt` | Respuestas 58–64 |

## C. Imagen web

```bash
docker build -t gswstore-web:1.0 .
docker image ls gswstore-web
docker image inspect gswstore-web:1.0 --format '{{.Id}} {{.Config.ExposedPorts}}'
```

**Caché (punto 11)**: repetir `docker build` sin cambios reutiliza todas las capas (`CACHED`). Si solo cambia `index.php`, se reutilizan las capas de extensiones y solo se reconstruyen `COPY app/` en adelante; si cambia el `Dockerfile`, se invalida desde esa instrucción. Por eso dependencias estables van antes que el código. Nueva versión:

```bash
docker build -t gswstore-web:1.1 .
docker image ls gswstore-web
```

## F–J. Despliegue, pruebas y cierre

```bash
docker compose config                    # 27. validar (db no debe mostrar ports)
docker compose up -d --build             # 28. desplegar
docker compose ps                        # estado
docker compose logs -f db                # esperar "ready for connections" (Ctrl+C para salir)
docker compose logs --tail=50 web
# Abrir http://IP_DEL_SERVIDOR:8080  -> web operativo, MySQL conectado, 4 registros

docker network inspect servicemanager_app-net     # 29. misma red (el prefijo varía)
docker compose exec web getent hosts db           # 30. resolución DNS
# 31. la página confirma la conexión web-MySQL

# H. Persistencia: agregar registro, bajar, subir, comprobar
docker compose exec db mysql -ugswuser -p  # clave de .env
# USE gswstore; INSERT INTO servicios(nombre, estado) VALUES ('Sitio espejo','ACTIVO');
docker compose down                               # conserva volúmenes
docker volume ls | grep db_data                   # el volumen sigue existiendo
docker compose up -d                              # el registro permanece

# I. Fallo y recuperación
docker compose up -d --force-recreate web         # recrear web sin tocar db
docker compose stop db                            # la página responde 503
docker compose start db && docker compose ps      # esperar healthy, la app se recupera

# K. Actualización controlada (1.0 -> 1.1)
# editar app/index.php ("Versión 1.1"), cambiar image: en compose.yaml
docker compose build web && docker compose up -d web

# L. Cierre y evidencias
docker compose config > evidencias-compose.txt
docker compose images && docker compose ps
docker stats --no-stream
git status                                        # .env no debe aparecer
```

`docker compose stop` pausa sin borrar; `down` elimina contenedores y red pero conserva volúmenes; nunca `down -v` con datos importantes.
