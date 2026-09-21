#!/bin/bash
# =============================================================================
# GSW E-Commerce — Script de Backup
# =============================================================================

set -e

BACKUP_DIR="/var/backups/gsw-ecommerce"
APP_DIR="/var/www/gsw-ecommerce"
DB_NAME="gsw_ecommerce"
RETENTION_DAYS=7
DATE=$(date +%Y%m%d_%H%M%S)

mkdir -p $BACKUP_DIR

echo "=== Backup GSW E-Commerce ==="
echo "Fecha: $(date)"

# 1. Backup de la base de datos (peer auth como postgres, sin contraseña)
echo "Respaldando base de datos..."
sudo -u postgres pg_dump $DB_NAME | gzip > "$BACKUP_DIR/db_${DATE}.sql.gz"

# 2. Backup de archivos de la aplicación (excluir vendor y node_modules)
echo "Respaldando archivos..."
tar -czf "$BACKUP_DIR/files_${DATE}.tar.gz" \
    --exclude='vendor' \
    --exclude='node_modules' \
    --exclude='.git' \
    --exclude='storage/logs/*' \
    --exclude='storage/framework/cache/*' \
    --exclude='storage/framework/sessions/*' \
    --exclude='storage/framework/views/*' \
    -C /var/www gsw-ecommerce

# 3. Backup de la configuración de Nginx
echo "Respaldando configuración..."
tar -czf "$BACKUP_DIR/nginx_${DATE}.tar.gz" \
    /etc/nginx/sites-available/gsw-ecommerce \
    /etc/nginx/sites-enabled/gsw-ecommerce 2>/dev/null || true

# 4. Eliminar backups antiguos
echo "Limpiando backups antiguos (>${RETENTION_DAYS} días)..."
find $BACKUP_DIR -name "*.gz" -mtime +$RETENTION_DAYS -delete

# 5. Resumen
echo ""
echo "=== Backup Completado ==="
echo "Archivos generados:"
ls -lh $BACKUP_DIR/*_${DATE}*
echo ""
echo "Espacio total en backup:"
du -sh $BACKUP_DIR
