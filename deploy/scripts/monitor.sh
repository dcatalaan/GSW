#!/bin/bash
# =============================================================================
# GSW E-Commerce — Script de Monitoreo
# =============================================================================

echo "=== Estado de Servicios GSW E-Commerce ==="
echo "Fecha: $(date)"
echo ""

# Nginx
echo "--- Nginx ---"
systemctl is-active nginx && echo "[OK] Nginx: Activo" || echo "[FAIL] Nginx: Inactivo"

# PHP-FPM
echo "--- PHP-FPM ---"
systemctl is-active php8.3-fpm && echo "[OK] PHP-FPM: Activo" || echo "[FAIL] PHP-FPM: Inactivo"

# PostgreSQL
echo "--- PostgreSQL ---"
systemctl is-active postgresql && echo "[OK] PostgreSQL: Activo" || echo "[FAIL] PostgreSQL: Inactivo"

# Embeddings Service
echo "--- Servicio de Embeddings ---"
systemctl is-active gsw-embeddings && echo "[OK] Embeddings: Activo" || echo "[WARN] Embeddings: Inactivo"

# Uso de disco
echo ""
echo "--- Uso de Disco ---"
df -h / | tail -1

# Uso de memoria
echo ""
echo "--- Memoria ---"
free -h | grep Mem

# Conexiones a la base de datos
echo ""
echo "--- Conexiones PostgreSQL ---"
sudo -u postgres psql -c "SELECT count(*) as connections FROM pg_stat_activity WHERE datname='gsw_ecommerce';" 2>/dev/null || echo "No se pudo conectar"

# Últimos errores de Laravel
echo ""
echo "--- Últimos errores de Laravel ---"
tail -5 /var/www/gsw-ecommerce/storage/logs/laravel.log 2>/dev/null || echo "No hay logs"

# Uso de CPU del servicio de embeddings
echo ""
echo "--- Servicio Embeddings ---"
systemctl status gsw-embeddings --no-pager | head -5 2>/dev/null || echo "Servicio no encontrado"
