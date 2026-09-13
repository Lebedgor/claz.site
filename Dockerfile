FROM node:24-alpine AS assets
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY vite.config.js ./
COPY resources ./resources
RUN npm run build

FROM php:8.4-fpm-alpine AS app
RUN apk add --no-cache autoconf g++ make icu-dev libpq-dev libpng-dev libjpeg-turbo-dev freetype-dev libzip-dev oniguruma-dev rsync \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-configure exif \
    && docker-php-ext-install pdo_pgsql pgsql gd intl zip opcache pcntl exif \
    && pecl install redis && docker-php-ext-enable redis
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
WORKDIR /var/www/html
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-progress --no-scripts
COPY . .
COPY --from=assets /app/public/build ./public/build
COPY --chown=www-data:www-data storage/app/public /opt/uploads-seed
RUN chown -R www-data:www-data storage bootstrap/cache \
    && php artisan event:cache || true
COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh
ENTRYPOINT ["/entrypoint.sh"]
CMD ["php-fpm"]
