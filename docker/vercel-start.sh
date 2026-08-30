#!/bin/sh
set -eu

PORT="${PORT:-80}"

mkdir -p \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache

if [ -f artisan ]; then
    php artisan optimize --no-interaction || true
fi

sed "s/__LISTEN_PORT__/${PORT}/g" /etc/nginx/nginx-laravel.conf.template \
    > /etc/nginx/http.d/default.conf

php-fpm -D
exec nginx -g 'daemon off;'
