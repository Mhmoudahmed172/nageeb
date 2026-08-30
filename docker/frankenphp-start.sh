#!/bin/sh
set -eu

if [ -f /opt/vite-build/manifest.json ]; then
    mkdir -p /app/public/build
    cp -a /opt/vite-build/. /app/public/build/
fi

exec frankenphp run --config /etc/caddy/Caddyfile
