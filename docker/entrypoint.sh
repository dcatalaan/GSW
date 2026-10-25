#!/bin/bash
set -e

echo "[entrypoint] Esperando a db:5432..."
DBH="${DB_HOST:-db}"
DBN="${DB_DATABASE:-gsw_ecommerce}"
DBU="${DB_USERNAME:-gsw_user}"
DBP="${DB_PASSWORD:-secret}"
for i in $(seq 1 60); do
    if php -r "\$c = @pg_connect(\"host=$DBH port=5432 dbname=$DBN user=$DBU password='$DBP' connect_timeout=2\"); exit(\$c ? 0 : 1);" 2>/dev/null; then
        echo "[entrypoint] Base de datos disponible."
        break
    fi
    if [ "$i" -eq 60 ]; then
        echo "[entrypoint] ERROR: db no respondió en 60 s." >&2
        exit 1
    fi
    sleep 1
done

cd /var/www/html

if [ ! -f .env ] && [ -f .env.example ]; then
    cp .env.example .env
fi
if [ -z "$APP_KEY" ] && grep -q "^APP_KEY=$" .env 2>/dev/null; then
    echo "[entrypoint] Generando APP_KEY..."
    php artisan key:generate --force
fi

php artisan storage:link 2>/dev/null || true

php artisan migrate --force

php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "[entrypoint] Arrancando Apache..."
exec apache2-foreground
