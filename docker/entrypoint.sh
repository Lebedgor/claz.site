#!/bin/sh
set -e

if [ "$1" = "php-fpm" ] && [ "${MIGRATE_ON_START:-1}" = "1" ]; then
    php artisan migrate --force
    php artisan storage:link || true
    php artisan config:cache
    php artisan view:cache
fi

mkdir -p /srv/public
find /srv/public -mindepth 1 -maxdepth 1 -exec rm -rf {} +
cp -a public/. /srv/public/
rm -f /srv/public/storage

if [ -d /opt/uploads-seed ]; then
    mkdir -p /var/www/html/storage/app/public
    cp -a -n /opt/uploads-seed/. /var/www/html/storage/app/public/ || true
fi

exec "$@"
