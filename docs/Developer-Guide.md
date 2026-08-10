# Developer Quick Start

Goal: productive in ~30 minutes.

## 1. Setup

Prerequisites: PHP 8.1+ with **pdo_pgsql**, Composer, Node 18+, access to Shopify + Supabase credentials.

```bash
cd "Dainely-Premium-Wellness"
composer install
copy .env.example .env   # Windows
php artisan key:generate
npm install
```

Fill `.env`: `APP_URL`, Shopify tokens, `DB_SUPABASE_*`, Judge.me, Admin credentials. See [Environment](Environment.md).

---

## 2. Database

```bash
php artisan migrate
php artisan supabase:diagnose
```

If diagnose fails, fix PDO/hosting before working on Admin/CMS features. Storefront Shopify work can continue with `FEATURES_SUPABASE=false` only as a temporary measure.

---

## 3. Run locally

```bash
php artisan serve
npm run dev
```

Visit `http://127.0.0.1:8000` → redirects to `/en` (or geo).

Admin: `/admin/login`.

---

## 4. Queues

Default `sync` — no worker needed. For async:

```bash
php artisan queue:work
```

---

## 5. Common commands

```bash
php artisan shopify:health
php artisan shopify:sync-catalog
php artisan reviews:warm-cache
php artisan search:verify --reindex
php artisan analytics:verify
php artisan webhooks:verify
php artisan admin:verify
php artisan test
```

---

## 6. Where major code lives

| Concern | Location |
|---------|----------|
| Storefront routes | `routes/web.php` |
| Webhooks API | `routes/api.php` |
| Shopify | `app/Services/Shopify*.php` |
| CMS models | `app/Models/Supabase/` |
| Admin UI | `app/Http/Controllers/Admin/`, `resources/views/admin/` |
| Blade storefront | `resources/views/` |
| Jobs | `app/Jobs/` |
| Docs | `docs/` |

More: [CodeMap](CodeMap.md), [Architecture](Architecture.md).

---

## 7. Development workflow

1. Branch from main/dev.
2. Implement via **Service + Controller + Blade** (and Supabase model/migration if needed).
3. Add/adjust tests or `*:verify` coverage.
4. Update relevant `docs/*` in the same PR.
5. Run Pint / tests before push.
6. Never commit `.env`.

---

## 8. Mental model cheat sheet

- Shopify = products & checkout.
- Supabase = CMS overlays, landings, FAQs, search, analytics logs.
- Admin = session login editing Supabase.
- Search = Postgres FTS on `search_index`.
