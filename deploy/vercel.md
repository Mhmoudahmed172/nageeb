# Temporary Vercel container test

Nageeb can be deployed to Vercel **only as a container** (`Dockerfile.vercel` + `vercel.json`).

This is for short-lived testing. It is not a production media host.

## What Vercel runs

- One service: `nageeb`
- Runtime: `container`
- Entrypoint: `Dockerfile.vercel`
- All paths rewrite to that container
- HTTP on `$PORT` (default `80`)

## Project env vars (Vercel dashboard)

```
APP_NAME=نجيب
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:...
APP_URL=https://your-vercel-domain
APP_LOCALE=ar
APP_FALLBACK_LOCALE=ar

DB_CONNECTION=pgsql
DB_URL=<DATABASE_URL pooled, hostname contains -pooler>
DB_HOST=<Neon pooler host>
DB_PORT=5432
DB_DATABASE=neondb
DB_USERNAME=
DB_PASSWORD=
DB_SSLMODE=require
DATABASE_URL=<same pooled URL>
DATABASE_URL_UNPOOLED=<direct URL, no -pooler — local migrate only>

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=sync
SESSION_SECURE_COOKIE=true
TRUSTED_PROXIES=*

FILESYSTEM_DISK=local
MEDIA_DISK=local
```

Postgres is Neon project `nageeb` (`royal-frost-57338317`). Run migrations against the **direct** URL (`DATABASE_URL_UNPOOLED`): `php artisan migrate --force`. The container uses the pooled `DATABASE_URL` / `DB_URL`. It scales to zero and does not keep a local disk.

Media uploads on this test deploy use ephemeral local storage and will be lost on scale-down.

## Commands

```bash
vercel deploy
```

Or connect the Git repo with `Dockerfile.vercel` and `vercel.json` at the project root.
