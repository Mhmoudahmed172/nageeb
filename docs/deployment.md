# Nageeb — production deployment

This is a Laravel 13 + Blade + Vite application. It is **not** a static site.

## 1. Environment

Copy `.env.example` and set at least:

| Variable | Production value |
|---|---|
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` |
| `APP_KEY` | `php artisan key:generate` |
| `APP_URL` | Public `https://` URL |
| `DB_CONNECTION` | `pgsql` |
| `DB_HOST` / `DB_PORT` / `DB_DATABASE` / `DB_USERNAME` / `DB_PASSWORD` | PostgreSQL |
| `MEDIA_DISK` | `s3` (private bucket) or `local` only if the disk is not web-reachable |
| `FILESYSTEM_DISK` | Usually same as `MEDIA_DISK` |
| `SESSION_DRIVER` | `database` |
| `SESSION_SECURE_COOKIE` | `true` on HTTPS |
| `CACHE_STORE` | `database` |
| `QUEUE_CONNECTION` | `sync` (see queues below) |
| `TRUSTED_PROXIES` | `*` when behind a load balancer |
| `AWS_*` | Required when `MEDIA_DISK=s3` |

Do not commit `.env`.

## 2. Database

Production **must** use PostgreSQL. SQLite is for local/dev and PHPUnit only.

```bash
php artisan migrate --force
```

Migrations already use portable types (`string` statuses, `json`, foreign keys, decimals). `->after()` is ignored on PostgreSQL and does not change schema meaning.

Seed/admin users are **not** created by migrations. Create the first admin through your existing process.

## 3. Storage

Protected videos, lesson files, and exam papers are stored via `MediaStore` on `MEDIA_DISK`.

- Disk `local` → `storage/app/private` (not `public/storage`)
- Disk `s3` → private bucket (`visibility=private`)
- Access is only through `GET /media/lesson-contents/{content}` and `GET /media/exams/{exam}`
- `ContentAccess`, `ExamAccess`, and policies are unchanged

Install the S3 adapter on the server (already in `composer.json` when you run `composer install`):

```bash
composer require league/flysystem-aws-s3-v3 "^3.0"
```

Do not enable a public ACL on the media bucket. Do not symlink private files into `public/`.

## 4. Queues

Notifications write to the **database** channel and run **synchronously** (they do not implement `ShouldQueue`).

No queue worker is required today. Keep `QUEUE_CONNECTION=sync`.

The `jobs` table exists if you later queue mail or processing. Then set `QUEUE_CONNECTION=database` and run:

```bash
php artisan queue:work --sleep=1 --tries=3
```

Video `processing_status` is stored as metadata (`ready` after upload). There is no background transcode worker in this codebase.

## 5. Scheduler

`routes/console.php` has no scheduled tasks. **No cron is required.**

If you add `Schedule::` later:

```cron
* * * * * cd /var/www/html && php artisan schedule:run >> /dev/null 2>&1
```

## 6. Frontend build

`public/build` is gitignored. Build during deploy:

```bash
npm ci --ignore-scripts
npm run build
```

Laravel loads assets through `@vite` / the Vite manifest.

## 7. Deploy commands

```bash
composer install --no-dev --optimize-autoloader
npm ci --ignore-scripts
npm run build
php artisan key:generate --force   # first deploy only
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link           # public disk only; not for private media
```

Health: `GET /health` returns `ok` (200) or `unavailable` (503). Laravel’s `/up` remains available.

PHP upload limits should match the app (512MB videos): `upload_max_filesize` and `post_max_size`.

## 8. Docker (optional)

```bash
cp .env.example .env
# set APP_KEY, APP_URL, APP_ENV=production, APP_DEBUG=false
docker compose up --build
docker compose exec app php artisan migrate --force
```

App listens via nginx on `http://localhost:8080`.

`docker-compose.yml` bind-mounts source for local testing. For a real release, build the image **without** overwriting `/var/www/html` with a host mount.

## 9. Vercel

Not recommended. See `deploy/vercel.md`.

## 10. Security checklist

- `APP_DEBUG=false`
- HTTPS + `SESSION_SECURE_COOKIE=true`
- CSRF remains on all web forms
- Auth/authorization/policies unchanged
- Private media never served from `/storage/...`
