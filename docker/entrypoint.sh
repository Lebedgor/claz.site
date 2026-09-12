#!/bin/sh
set -e

if [ "$1" = "php-fpm" ] && [ "${MIGRATE_ON_START:-1}" = "1" ]; then
    php artisan migrate --force
    php artisan storage:link || true
    php artisan config:cache
    php artisan view:cache
fi

mkdir -p /srv/public
rm -rf /srv/public/build
cp -r public/build /srv/public/build

exec "$@"
