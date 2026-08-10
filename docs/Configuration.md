# Configuration Guide

Laravel loads `config/*.php`. Values usually read `env()`. In production, run `php artisan config:cache` after deploy.

## Important config files

| File | Role |
|------|------|
| `app.php` | Name, URL, locales, admin credentials bindings |
| `database.php` | `default` + `supabase` connections |
| `shopify.php` | Shop domain, tokens, checkout, cache TTLs |
| `shopify_tax_fallback.php` | Offline tax fallback tables |
| `supabase.php` | Supabase API-related settings / feature flags |
| `square.php` | Square credentials & charge currency |
| `judgeme.php` | Reviews API & cache behavior |
| `analytics.php` | GA4 / Meta / enable flag |
| `webhooks.php` | Shared webhook secrets & bearer tokens |
| `currency.php` | FX provider & display rules |
| `geo.php` | Locale geo detection |
| `company.php` | Contact details for header/footer |
| `products.php` | Product presentation helpers / maps |
| `postal.php` | Filesystem disks |
| `cache.php` / `session.php` / `queue.php` | Infra drivers |
| `mail.php` / `logging.php` | Mail & logs |
| `cors.php` / `sanctum.php` | API-ish scaffolding |

---

## How configuration works

1. `.env` supplies secrets and environment-specific values.
2. `config/foo.php` maps them into typed arrays.
3. Code should call `config('shopify.shop_domain')`, **not** `env('…')` outside config files (so `config:cache` stays correct).

---

## Caching

- Default driver: `file` (`CACHE_DRIVER`).
- Shopify product responses and Judge.me stats use application cache with TTLs from config.
- After env changes on a cached host: `php artisan config:clear` or rebuild `config:cache`.

---

## Sessions

- Default: `file`, lifetime 120 minutes.
- Admin auth is **session-based** (`admin_authenticated`).

---

## Storage

- Default disk `local` → `storage/app`.
- Public assets for Vite build go to `public/build`.
- Logs: `storage/logs/laravel.log`.

---

## Logging

- Channel `stack` by default.
- Use `Log::info/warning/error` in jobs/webhooks; avoid logging full webhook secrets or card data.

---

## Localization

- Supported: `en`, `fr`, `de` (`APP_SUPPORTED_LOCALES`).
- Route prefix drives locale; `LocaleMiddleware` applies it.
- Lang files under `lang/` / `resources/lang` as present; much marketing copy also lives in Blade + Supabase overlays.

---

## Feature flags (env-driven)

| Flag | Effect |
|------|--------|
| `FEATURES_SUPABASE` | Disable Supabase-dependent CMS paths when PDO unavailable |
| `SHOPIFY_NATIVE_CHECKOUT` | Prefer Shopify checkout URLs |
| `FEATURES_SQUARE_FALLBACK` | Allow Square payment path |
| `ANALYTICS_ENABLED` | Master analytics switch |
| `GEO_LOCALE_ENABLED` | IP locale redirect |
