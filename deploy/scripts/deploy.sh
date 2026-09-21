#!/bin/bash
# =============================================================================
# GSW E-Commerce — Script de Despliegue (Deploy)
# Ejecutar después de install.sh para actualizar la aplicación
# =============================================================================

set -e

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

log() { echo -e "${GREEN}[DEPLOY]${NC} $1"; }
warn() { echo -e "${YELLOW}[WARN]${NC} $1"; }
error() { echo -e "${RED}[ERROR]${NC} $1"; exit 1; }

APP_DIR="/var/www/gsw-ecommerce"
GIT_BRANCH="master"

log "Iniciando despliegue..."

# =============================================================================
# 1. MODO MANTENIMIENTO
# =============================================================================
log "Activando modo mantenimiento..."
cd $APP_DIR
php artisan down --message="Despliegue en curso. Volveremos pronto." --retry=60

# =============================================================================
# 2. ACTUALIZAR CÓDIGO
# =============================================================================
if [ -d ".git" ]; then
    log "Actualizando código del repositorio..."
    git fetch origin
    git reset --hard "origin/${GIT_BRANCH}"
    git clean -fd
else
    warn "No se detectó repositorio Git. Saltando actualización de código."
fi

# =============================================================================
# 3. INSTALAR/ACTUALIZAR DEPENDENCIAS
# =============================================================================
log "Instalando dependencias de Composer..."
composer install --no-dev --optimize-autoloader --no-interaction

# =============================================================================
# 4. EJECUTAR MIGRACIONES
# =============================================================================
log "Ejecutando migraciones..."
php artisan migrate --force

# =============================================================================
# 5. REGENERAR EMBEDDINGS (si hay cambios en productos)
# =============================================================================
log "Regenerando embeddings..."
php artisan tinker --execute="app(\App\Services\SemanticSearchService::class)->regenerateAllEmbeddings();" 2>/dev/null || warn "No se pudieron regenerar embeddings (servicio no disponible)"

# =============================================================================
# 6. LIMPIAR Y REGENERAR CACHÉ
# =============================================================================
log "Regenerando caché..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# =============================================================================
# 7. CREAR STORAGE LINK
# =============================================================================
php artisan storage:link 2>/dev/null || true

# =============================================================================
# 8. CONFIGURAR PERMISOS
# =============================================================================
log "Configurando permisos..."
chown -R www-data:www-data $APP_DIR
chmod -R 775 $APP_DIR/storage
chmod -R 775 $APP_DIR/bootstrap/cache

# =============================================================================
# 9. REINICIAR SERVICIOS
# =============================================================================
log "Reiniciando servicios..."
systemctl reload php8.3-fpm
systemctl reload nginx
systemctl restart gsw-embeddings 2>/dev/null || true

# =============================================================================
# 10. DESACTIVAR MODO MANTENIMIENTO
# =============================================================================
log "Desactivando modo mantenimiento..."
php artisan up

# =============================================================================
# RESUMEN
# =============================================================================
echo ""
echo -e "${GREEN} DESPLIEGUE COMPLETADO EXITOSAMENTE${NC}"
echo ""
echo " Fecha: $(date)"
echo " Directorio: ${APP_DIR}"
echo ""
