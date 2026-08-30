#!/bin/sh
set -eu

if [ ! -f /app/public/build/manifest.json ] && [ -f /opt/vite-build/manifest.json ]; then
    mkdir -p /app/public/build
    cp -a /opt/vite-build/. /app/public/build/
fi

mkdir -p \
    /app/storage/framework/cache/data \
    /app/storage/framework/sessions \
    /app/storage/framework/views \
    /app/storage/logs \
    /app/bootstrap/cache

php artisan optimize --no-interaction || true

exec frankenphp run --config /etc/caddy/Caddyfile
