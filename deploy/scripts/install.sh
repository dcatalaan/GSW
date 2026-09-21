#!/bin/bash

set -e

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

log() { echo -e "${GREEN}[GSW]${NC} $1"; }
warn() { echo -e "${YELLOW}[WARN]${NC} $1"; }
error() { echo -e "${RED}[ERROR]${NC} $1"; exit 1; }

APP_DIR="/var/www/gsw-ecommerce"
DB_NAME="gsw_ecommerce"
DB_USER="gsw_user"
DB_PASS=$(openssl rand -hex 16)
DOMAIN="gsw-ecommerce.local"
GIT_BRANCH="master"

log "Actualizando sistema..."
apt update && apt upgrade -y

log "Agregando repositorios Sury (PHP) y PGDG (PostgreSQL)..."
apt install -y lsb-release ca-certificates apt-transport-https software-properties-common gnupg2

curl -fsSL https://packages.sury.org/php/apt.gpg | gpg --dearmor -o /usr/share/keyrings/sury-php.gpg
echo "deb [signed-by=/usr/share/keyrings/sury-php.gpg] https://packages.sury.org/php/ $(lsb_release -sc) main" > /etc/apt/sources.list.d/sury-php.list

curl -fsSL https://www.postgresql.org/media/keys/ACCC4CF8.asc | gpg --dearmor -o /usr/share/keyrings/postgresql.gpg
echo "deb [signed-by=/usr/share/keyrings/postgresql.gpg] http://apt.postgresql.org/pub/repos/apt $(lsb_release -cs)-pgdg main" > /etc/apt/sources.list.d/pgdg.list

apt update

log "Instalando dependencias del sistema..."
apt install -y \
    nginx \
    php8.3-fpm \
    php8.3-cli \
    php8.3-common \
    php8.3-pgsql \
    php8.3-mbstring \
    php8.3-xml \
    php8.3-curl \
    php8.3-zip \
    php8.3-gd \
    php8.3-bcmath \
    php8.3-tokenizer \
    php8.3-fileinfo \
    php8.3-intl \
    php8.3-redis \
    postgresql \
    postgresql-contrib \
    python3 \
    python3-pip \
    python3-venv \
    git \
    curl \
    wget \
    unzip \
    ufw \
    certbot \
    python3-certbot-nginx

log "Configurando firewall..."
ufw allow OpenSSH
ufw allow 'Nginx Full'
ufw --force enable

log "Configurando PostgreSQL..."
systemctl enable postgresql
systemctl start postgresql

sudo -u postgres psql -c "CREATE USER ${DB_USER} WITH ENCRYPTED PASSWORD '${DB_PASS}';"
sudo -u postgres psql -c "CREATE DATABASE ${DB_NAME} OWNER ${DB_USER};"
sudo -u postgres psql -c "GRANT ALL PRIVILEGES ON DATABASE ${DB_NAME} TO ${DB_USER};"

log "Instalando extensión pgvector..."
PG_VER=$(ls /etc/postgresql/ 2>/dev/null | head -1)
if [ -z "$PG_VER" ]; then
    PG_VER=$(psql --version | grep -oP '\d+' | head -1)
fi
apt install -y "postgresql-${PG_VER}-pgvector"

sudo -u postgres psql -d ${DB_NAME} -c "CREATE EXTENSION IF NOT EXISTS vector;"
sudo -u postgres psql -d ${DB_NAME} -c "ALTER EXTENSION vector UPDATE;"

log "PostgreSQL y pgvector configurados."

log "Configurando PHP-FPM..."

PHP_FPM_POOL="/etc/php/8.3/fpm/pool.d/www.conf"
sed -i 's/upload_max_filesize = .*/upload_max_filesize = 20M/' /etc/php/8.3/fpm/php.ini
sed -i 's/post_max_size = .*/post_max_size = 25M/' /etc/php/8.3/fpm/php.ini
sed -i 's/memory_limit = .*/memory_limit = 256M/' /etc/php/8.3/fpm/php.ini
sed -i 's/max_execution_time = .*/max_execution_time = 60/' /etc/php/8.3/fpm/php.ini

systemctl restart php8.3-fpm
systemctl enable php8.3-fpm

log "Instalando Composer..."
curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

log "Desplegando aplicación..."
mkdir -p /var/www
cd /var/www

if [ -d "$APP_DIR/.git" ]; then
    cd $APP_DIR
    git pull origin $GIT_BRANCH
else
    mkdir -p $APP_DIR
fi

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
PROJECT_DIR="$(dirname "$SCRIPT_DIR")"

if [ -d "$PROJECT_DIR/app" ]; then
    rsync -av --exclude='.git' --exclude='node_modules' --exclude='vendor' \
        "$PROJECT_DIR/" "$APP_DIR/"
fi

cd $APP_DIR

log "Configurando Laravel..."

composer install --no-dev --optimize-autoloader --no-interaction

if [ ! -f .env ]; then
    cp .env.example .env
fi

php artisan key:generate --force

sed -i "s/APP_ENV=local/APP_ENV=production/" .env
sed -i "s/APP_DEBUG=true/APP_DEBUG=false/" .env
sed -i "s|APP_URL=.*|APP_URL=https://${DOMAIN}|" .env
sed -i "s/DB_CONNECTION=.*/DB_CONNECTION=pgsql/" .env
sed -i "s/DB_HOST=.*/DB_HOST=127.0.0.1/" .env
sed -i "s/DB_PORT=.*/DB_PORT=5432/" .env
sed -i "s/DB_DATABASE=.*/DB_DATABASE=${DB_NAME}/" .env
sed -i "s/DB_USERNAME=.*/DB_USERNAME=${DB_USER}/" .env
sed -i "s/DB_PASSWORD=.*/DB_PASSWORD=${DB_PASS}/" .env
sed -i "s|EMBEDDING_URL=.*|EMBEDDING_URL=http://127.0.0.1:8000/embed|" .env

if grep -q "^GROQ_API_KEY=$" .env 2>/dev/null || ! grep -q "^GROQ_API_KEY=gsk_" .env 2>/dev/null; then
    warn "Configura tu clave gratuita en https://console.groq.com y agregala al .env:"
    warn "  GROQ_API_KEY=gsk_..."
fi

php artisan config:cache
php artisan route:cache
php artisan view:cache

php artisan migrate --force

if [ "$1" = "--seed" ]; then
    php artisan db:seed --force
fi

php artisan storage:link

log "Configurando permisos..."
chown -R www-data:www-data $APP_DIR
find $APP_DIR -type d -exec chmod 755 {} \;
find $APP_DIR -type f -exec chmod 644 {} \;
chmod -R 775 $APP_DIR/storage
chmod -R 775 $APP_DIR/bootstrap/cache

log "Configurando Nginx..."

cp "$SCRIPT_DIR/../nginx/gsw-ecommerce.conf" /etc/nginx/sites-available/gsw-ecommerce

ln -sf /etc/nginx/sites-available/gsw-ecommerce /etc/nginx/sites-enabled/

rm -f /etc/nginx/sites-enabled/default

nginx -t

systemctl restart nginx
systemctl enable nginx

log "Configurando SSL..."

if [ ! -d "/etc/letsencrypt/live/${DOMAIN}" ]; then
    warn "Para producción, ejecuta:"
    warn "certbot --nginx -d ${DOMAIN} -d www.${DOMAIN}"

    mkdir -p /etc/letsencrypt/live/${DOMAIN}
    openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
        -keyout /etc/letsencrypt/live/${DOMAIN}/privkey.pem \
        -out /etc/letsencrypt/live/${DOMAIN}/fullchain.pem \
        -subj "/C=SV/ST=Santa Tecla/L=Santa Tecla/O=GSW/CN=${DOMAIN}"
fi

log "Configurando servicio de embeddings..."

EMBEDDING_DIR="/opt/gsw-embeddings"
mkdir -p $EMBEDDING_DIR

python3 -m venv $EMBEDDING_DIR/venv
source $EMBEDDING_DIR/venv/bin/activate

pip install --upgrade pip
pip install flask sentence-transformers numpy

cat > $EMBEDDING_DIR/app.py << 'PYEOF'
from flask import Flask, request, jsonify
from sentence_transformers import SentenceTransformer
import numpy as np
import os

app = Flask(__name__)

model_name = os.environ.get('EMBEDDING_MODEL', 'all-MiniLM-L6-v2')
model = SentenceTransformer(model_name)

@app.route('/embed', methods=['POST'])
def embed():
    data = request.get_json()
    text = data.get('text', '')
    
    if not text:
        return jsonify({'error': 'No text provided'}), 400
    
    embedding = model.encode(text)
    
    return jsonify({
        'embedding': embedding.tolist(),
        'dimensions': len(embedding),
        'model': model_name
    })

@app.route('/health', methods=['GET'])
def health():
    return jsonify({'status': 'ok', 'model': model_name})

if __name__ == '__main__':
    app.run(host='127.0.0.1', port=8000, debug=False)
PYEOF

cat > /etc/systemd/system/gsw-embeddings.service << SVCEOF
[Unit]
Description=GSW E-Commerce Embedding Service
After=network.target

[Service]
Type=simple
User=www-data
WorkingDirectory=${EMBEDDING_DIR}
Environment="PATH=${EMBEDDING_DIR}/venv/bin"
ExecStart=${EMBEDDING_DIR}/venv/bin/python app.py
Restart=always
RestartSec=10

[Install]
WantedBy=multi-user.target
SVCEOF

systemctl daemon-reload
systemctl enable gsw-embeddings
systemctl start gsw-embeddings

log "Configurando Cron..."
(crontab -l 2>/dev/null; echo "* * * * * cd ${APP_DIR} && php artisan schedule:run >> /dev/null 2>&1") | crontab -

cat > /etc/logrotate.d/gsw-ecommerce << LOGEOF
${APP_DIR}/storage/logs/*.log {
    daily
    missingok
    rotate 14
    compress
    delaycompress
    notifempty
    create 0664 www-data www-data
    sharedscripts
}
LOGEOF

echo ""
echo "============================================="
echo -e "${GREEN} INSTALACIÓN COMPLETADA${NC}"
echo "============================================="
echo ""
echo " Base de datos:"
echo "   Nombre: ${DB_NAME}"
echo "   Usuario: ${DB_USER}"
echo "   Contraseña: ${DB_PASS}"
echo ""
echo " URLs:"
echo "   https://${DOMAIN}"
echo "   https://${DOMAIN}/admin"
echo ""
echo " Cuentas de prueba:"
echo "   Admin: admin@gsw-ecommerce.local / password"
echo "   Cliente: cliente@gsw-ecommerce.local / password"
echo ""
echo " Servicios:"
echo "   systemctl status nginx"
echo "   systemctl status php8.3-fpm"
echo "   systemctl status postgresql"
echo "   systemctl status gsw-embeddings"
echo ""
echo " Logs:"
echo "   /var/log/nginx/gsw-ecommerce-error.log"
echo "   ${APP_DIR}/storage/logs/laravel.log"
echo ""
echo -e "${YELLOW} IMPORTANTE: Guarda la contraseña de la base de datos:${NC}"
echo -e "${YELLOW} ${DB_PASS}${NC}"
echo ""
echo " Para SSL real, ejecuta:"
echo "   certbot --nginx -d ${DOMAIN}"
echo ""
