# Production image: PHP 8.3-FPM + built Vite assets.
# Pair with nginx (see docker/nginx.conf) and PostgreSQL.

FROM node:22-alpine AS assets
WORKDIR /app
COPY package.json package-lock.json* ./
RUN npm ci --ignore-scripts
COPY resources ./resources
COPY vite.config.js ./
COPY public ./public
RUN npm run build

FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-interaction --prefer-dist --optimize-autoloader

FROM php:8.3-fpm-alpine
WORKDIR /var/www/html

RUN apk add --no-cache \
        icu-dev \
        libpq-dev \
        libzip-dev \
        oniguruma-dev \
        linux-headers \
    && docker-php-ext-install \
        intl \
        pdo_pgsql \
        pgsql \
        pcntl \
        bcmath \
        zip \
        opcache

COPY docker/php.ini /usr/local/etc/php/conf.d/nageeb.ini

COPY --from=vendor /app/vendor ./vendor
COPY . .
COPY --from=assets /app/public/build ./public/build

RUN mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && php artisan package:discover --ansi || true

USER www-data

EXPOSE 9000
CMD ["php-fpm"]
