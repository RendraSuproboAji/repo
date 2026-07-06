#!/bin/bash
# Menyiapkan aplikasi saat container start:
# struktur storage (volume kosong saat pertama kali), .env dari variabel
# lingkungan, APP_KEY persisten, migrasi, seeder opsional, lalu cache config.
set -e

cd /var/www/html

# volume storage yang baru dibuat masih kosong — bentuk ulang strukturnya
mkdir -p storage/app/public \
         storage/framework/{cache/data,sessions,testing,views} \
         storage/logs \
         bootstrap/cache

DB_DATABASE="${DB_DATABASE:-/var/www/html/storage/app/database.sqlite}"
DB_CONNECTION="${DB_CONNECTION:-sqlite}"

if [ "$DB_CONNECTION" = "sqlite" ] && [ ! -f "$DB_DATABASE" ]; then
    touch "$DB_DATABASE"
fi

# APP_KEY: pakai dari environment; kalau kosong, generate sekali dan
# simpan di volume storage supaya sesi/enkripsi tetap valid antar restart
KEY_FILE=storage/app/app.key
if [ -z "$APP_KEY" ]; then
    if [ ! -s "$KEY_FILE" ]; then
        php -r "echo 'base64:'.base64_encode(random_bytes(32));" > "$KEY_FILE"
        echo "APP_KEY baru dibuat dan disimpan di $KEY_FILE"
    fi
    APP_KEY=$(cat "$KEY_FILE")
fi

# tulis .env dari variabel lingkungan (deterministik, aman untuk mod_php)
cat > .env <<ENV
APP_NAME="${APP_NAME:-Splat Gallery}"
APP_ENV=${APP_ENV:-production}
APP_KEY=${APP_KEY}
APP_DEBUG=${APP_DEBUG:-false}
APP_URL=${APP_URL:-http://localhost:8080}
APP_LOCALE=${APP_LOCALE:-en}

LOG_CHANNEL=${LOG_CHANNEL:-stderr}
LOG_LEVEL=${LOG_LEVEL:-info}

DB_CONNECTION=${DB_CONNECTION}
DB_DATABASE=${DB_DATABASE}
DB_HOST=${DB_HOST:-}
DB_PORT=${DB_PORT:-}
DB_USERNAME=${DB_USERNAME:-}
DB_PASSWORD=${DB_PASSWORD:-}

SESSION_DRIVER=${SESSION_DRIVER:-database}
CACHE_STORE=${CACHE_STORE:-database}
QUEUE_CONNECTION=${QUEUE_CONNECTION:-sync}
FILESYSTEM_DISK=${FILESYSTEM_DISK:-local}

FEATURE_LOCAL_CONVERT=${FEATURE_LOCAL_CONVERT:-true}
SPLAT_TRANSFORM_COMMAND="${SPLAT_TRANSFORM_COMMAND:-splat-transform}"
CONVERT_MAX_MB=${CONVERT_MAX_MB:-200}
UPLOAD_MAX_MB=${UPLOAD_MAX_MB:-500}

SITE_LINK_EDITOR=${SITE_LINK_EDITOR:-https://superspl.at/editor}
SITE_LINK_GITHUB=${SITE_LINK_GITHUB:-https://github.com/RendraSuproboAji/repo}
SITE_LINK_FEEDBACK=${SITE_LINK_FEEDBACK:-https://github.com/RendraSuproboAji/repo/issues}
SITE_LINK_DISCORD=${SITE_LINK_DISCORD:-}
ENV

php artisan storage:link --force
php artisan migrate --force

# seed demo hanya sekali (dicatat lewat flag di volume storage)
SEED_FLAG=storage/app/.seeded
if [ "${SEED_DEMO:-true}" = "true" ] && [ ! -f "$SEED_FLAG" ]; then
    php artisan db:seed --force
    touch "$SEED_FLAG"
fi

php artisan config:cache
php artisan route:cache
php artisan view:cache

chown -R www-data:www-data storage bootstrap/cache

exec "$@"
