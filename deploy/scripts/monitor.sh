#!/bin/bash

echo "=== Estado de Servicios GSW E-Commerce ==="
echo "Fecha: $(date)"
echo ""

echo "--- Nginx ---"
systemctl is-active nginx && echo "[OK] Nginx: Activo" || echo "[FAIL] Nginx: Inactivo"

echo "--- PHP-FPM ---"
systemctl is-active php8.3-fpm && echo "[OK] PHP-FPM: Activo" || echo "[FAIL] PHP-FPM: Inactivo"

echo "--- PostgreSQL ---"
systemctl is-active postgresql && echo "[OK] PostgreSQL: Activo" || echo "[FAIL] PostgreSQL: Inactivo"

echo "--- Servicio de Embeddings ---"
systemctl is-active gsw-embeddings && echo "[OK] Embeddings: Activo" || echo "[WARN] Embeddings: Inactivo"

echo ""
echo "--- Uso de Disco ---"
df -h / | tail -1

echo ""
echo "--- Memoria ---"
free -h | grep Mem

echo ""
echo "--- Conexiones PostgreSQL ---"
sudo -u postgres psql -c "SELECT count(*) as connections FROM pg_stat_activity WHERE datname='gsw_ecommerce';" 2>/dev/null || echo "No se pudo conectar"

echo ""
echo "--- Últimos errores de Laravel ---"
tail -5 /var/www/gsw-ecommerce/storage/logs/laravel.log 2>/dev/null || echo "No hay logs"

echo ""
echo "--- Servicio Embeddings ---"
systemctl status gsw-embeddings --no-pager | head -5 2>/dev/null || echo "Servicio no encontrado"
