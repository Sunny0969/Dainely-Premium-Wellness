# Deployment

## Required services

| Service | Why |
|---------|-----|
| PHP 8.1+ (8.3 OK) with extensions | App runtime |
| **`pdo_pgsql` + `pgsql`** | Supabase / Admin CMS |
| Composer | PHP deps |
| Node 18+ / npm | Frontend build |
| PostgreSQL (Supabase) | Phase 2 data |
| Optional MySQL/PG for default connection | Legacy tables |
| Queue worker | If not `sync` |
| Cron | `schedule:run` |
| HTTPS host | Live traffic |

Shopify, Judge.me, and analytics credentials must be present for full commerce/analytics.

---

## Typical deploy steps

1. Pull release / upload build artifact.
2. `composer install --no-dev --optimize-autoloader`
3. Copy/update `.env` (never overwrite secrets blindly).
4. `php artisan migrate --force` (ensure Supabase creds work first).
6. `npm ci && npm run build`
7. `php artisan optimize:production` (config + route + view + event cache; checks OPcache)
8. Restart PHP-FPM / queue workers.
9. Confirm cron for scheduler + Cloudflare cache rules ([Cloudflare.md](Cloudflare.md)).
10. Smoke: home, PDP, checkout redirect, `/admin/login`, `php artisan supabase:diagnose`.

PowerShell helpers may exist at repo root (`deploy-*.ps1`) for this team’s hosting workflow — read them before running.

---

## Composer & npm

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
```

---

## Migrations

- Phase 2 tables use connection `supabase`.
- Prefer Supabase **Session mode pooler** from shared hosting.
- If `pdo_pgsql` missing, migrations and Admin will fail — fix hosting first.

---

## Queues

- Local/dev: `QUEUE_CONNECTION=sync` is fine.
- Production: `database` or `redis` + Supervisor:

```ini
[program:dainely-worker]
command=php /path/to/artisan queue:work --sleep=1 --tries=3
autostart=true
autorestart=true
```

---

## Cron

```cron
* * * * * cd /path/to/app && php artisan schedule:run >> /dev/null 2>&1
```

Enables review cache warming and webhook retries.

---

## Cache & optimizations

### On every production deploy (required)

```bash
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan optimize:production
# equivalent to:
#   php artisan config:cache
#   php artisan route:cache
#   php artisan view:cache
#   php artisan event:cache
```

Or: `php artisan optimize:production`

After `.env` changes: `php artisan optimize:production --clear` then re-run without `--clear`.

### PHP OPcache

Enable OPcache on the server (biggest PHP win). Shared hosting: deploy `public/.user.ini` (already in repo) or set in the panel:

```ini
opcache.enable=1
opcache.memory_consumption=128
opcache.max_accelerated_files=20000
opcache.validate_timestamps=1
opcache.revalidate_freq=60
```

Confirm with `php artisan optimize:production` (prints OPcache status) or `php -i | grep opcache.enable`.

### Vite (frontend)

```bash
npm run build
```

Production build minifies JS/CSS, drops `console`, and emits hashed assets under `public/build/` for long CDN cache.

### Checkout performance notes

- Square JS loads **only on Payment step** (not in `<head>`).
- Tax quotes cache **30 minutes** (`SHOPIFY_TAX_CACHE_TTL`, plus session copy).
- Shipping rate quotes cache **30 minutes** (`SHOPIFY_SHIPPING_CACHE_TTL`).
- Stock checks use webhook-synced Supabase inventory (`CartInventory`), not live Shopify.
- Prefer `php artisan optimize:production` on the server after deploy.

---

## Document root

Point the vhost at `public/`. Some hosts need `APP_PUBLIC_PATH` — see `.env.example`.

---

## Rollback

1. Redeploy previous release artifact / git tag.
2. Restore `.env` if needed.
3. Reverse migrations only with care (some Phase 2 cleanups are one-way).
4. Clear caches; restart workers.
5. Re-verify Shopify webhook URLs still point at the live host.

---

## Post-deploy checklist

- [ ] `shopify:health`
- [ ] `supabase:diagnose`
- [ ] Admin login + dashboard non-zero when data exists
- [ ] Product page loads reviews
- [ ] Checkout reaches Shopify (or Square if intentional)
- [ ] Webhook endpoints reachable (Shopify admin)
