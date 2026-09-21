#!/bin/bash

set -e

BACKUP_DIR="/var/backups/gsw-ecommerce"
APP_DIR="/var/www/gsw-ecommerce"
DB_NAME="gsw_ecommerce"
RETENTION_DAYS=7
DATE=$(date +%Y%m%d_%H%M%S)

mkdir -p $BACKUP_DIR

echo "=== Backup GSW E-Commerce ==="
echo "Fecha: $(date)"

echo "Respaldando base de datos..."
sudo -u postgres pg_dump $DB_NAME | gzip > "$BACKUP_DIR/db_${DATE}.sql.gz"

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

echo "Respaldando configuración..."
tar -czf "$BACKUP_DIR/nginx_${DATE}.tar.gz" \
    /etc/nginx/sites-available/gsw-ecommerce \
    /etc/nginx/sites-enabled/gsw-ecommerce 2>/dev/null || true

echo "Limpiando backups antiguos (>${RETENTION_DAYS} días)..."
find $BACKUP_DIR -name "*.gz" -mtime +$RETENTION_DAYS -delete

echo ""
echo "=== Backup Completado ==="
echo "Archivos generados:"
ls -lh $BACKUP_DIR/*_${DATE}*
echo ""
echo "Espacio total en backup:"
du -sh $BACKUP_DIR
