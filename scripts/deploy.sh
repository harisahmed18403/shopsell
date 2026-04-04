#!/usr/bin/env bash

set -euo pipefail

APP_DIR="/var/www/shopsell"
BRANCH="${DEPLOY_BRANCH:-main}"

cd "$APP_DIR"

export COMPOSER_ALLOW_SUPERUSER=1

php artisan down --retry=60 || true

cleanup() {
    php artisan up || true
}

trap cleanup EXIT

git config core.filemode false
git fetch --prune origin
git reset --hard "origin/${BRANCH}"

composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev
npm ci
npm run build

php artisan migrate --force
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan queue:restart || true

install -m 644 deploy/apache/phoneworks.conf /etc/apache2/sites-available/phoneworks.conf
apachectl configtest
systemctl reload apache2

chown -R www-data:www-data storage bootstrap/cache database
find storage bootstrap/cache database -type d -exec chmod 775 {} \;
find storage bootstrap/cache database -type f -exec chmod 664 {} \;
chmod 644 .env
