# Environment Variables

Values below are **purposes and examples only**. Never commit real secrets. Prefer `.env.example` as the checklist.

| Variable | Purpose | Required | Default | Example |
|----------|---------|----------|---------|---------|
| `APP_NAME` | App name | Yes | — | `Dainely` |
| `APP_ENV` | Environment | Yes | — | `local` / `production` |
| `APP_KEY` | Encryption key | Yes | — | `base64:…` |
| `APP_DEBUG` | Debug mode | Yes | — | `true` (never on prod) |
| `APP_URL` | Canonical URL | Yes | — | `http://localhost:8000` |
| `APP_PUBLIC_PATH` | Alternate docroot on some hosts | No | — | absolute path |
| `APP_LOCALE` | Default locale | No | `en` | `en` |
| `APP_SUPPORTED_LOCALES` | Locales list | No | `en,fr,de` | `en,fr,de` |
| `LOG_*` | Logging | No | stack/debug | — |
| `DB_CONNECTION` | Default DB driver | Yes | — | `pgsql` |
| `DB_HOST` / `PORT` / `DATABASE` / `USERNAME` / `PASSWORD` | Default DB | If used | — | — |
| `DB_SSLMODE` | PG SSL | No | `require` | `require` |
| `DB_SUPABASE_HOST` | Supabase/pooler host | For CMS | — | `aws-0-….pooler.supabase.com` |
| `DB_SUPABASE_PORT` | Port | No | `5432` | `5432` |
| `DB_SUPABASE_DATABASE` | DB name | Yes for CMS | `postgres` | `postgres` |
| `DB_SUPABASE_USERNAME` | User (`postgres.PROJECT` on pooler) | Yes for CMS | — | — |
| `DB_SUPABASE_PASSWORD` | Password | Yes for CMS | — | *(secret)* |
| `DB_SUPABASE_SSLMODE` | SSL | No | `require` | `require` |
| `DB_SUPABASE_TIMEOUT` | PDO timeout seconds | No | `15` | `15` |
| `FEATURES_SUPABASE` | Enable Supabase features | No | `true` | `true` |
| `SUPABASE_URL` | REST URL | Optional | — | `https://….supabase.co` |
| `SUPABASE_PUBLISHABLE_KEY` | Anon key | Optional | — | *(secret)* |
| `SUPABASE_SECRET_KEY` | Service key | Optional | — | *(secret)* |
| `CACHE_DRIVER` | Cache store | No | `file` | `file` / `redis` |
| `QUEUE_CONNECTION` | Queue | No | `sync` | `database` |
| `SESSION_DRIVER` | Sessions | No | `file` | `file` |
| `FILESYSTEM_DISK` | Files | No | `local` | `local` |
| `REDIS_*` | Redis | If used | — | — |
| `MAIL_*` | Mailer | For mail | — | Mailpit locally |
| `COMPANY_EMAIL` / `PHONE` | Contact display | No | — | — |
| `SQUARE_*` | Square credentials | If fallback | — | sandbox first |
| `SHOPIFY_SHOP_DOMAIN` | Shop | Yes for commerce | — | `….myshopify.com` |
| `SHOPIFY_CLIENT_ID` / `SECRET` | OAuth client creds | Often | — | — |
| `SHOPIFY_ADMIN_ACCESS_TOKEN` | Admin API | Preferred | — | `shpat_…` |
| `SHOPIFY_API_VERSION` | API version | No | `2024-01` | — |
| `SHOPIFY_STOREFRONT_*` | Storefront API | For native checkout | — | — |
| `SHOPIFY_NATIVE_CHECKOUT` | Prefer Shopify checkout | No | `true` | `true` |
| `SHOPIFY_WEBHOOK_SECRET` | HMAC | For webhooks | — | *(secret)* |
| `SHOPIFY_*` tax/cache flags | Tax & cache TTLs | No | see example | — |
| `FEATURES_SQUARE_FALLBACK` | Allow Square path | No | `true` | — |
| `OPEN_EXCHANGE_RATES_APP_ID` | FX | Recommended | — | — |
| `BASE_CURRENCY` | Base | No | `USD` | `USD` |
| `GA4_MEASUREMENT_ID` / `GA4_API_SECRET` | GA4 | Optional | — | — |
| `META_PIXEL_ID` / `META_CAPI_ACCESS_TOKEN` | Meta | Optional | — | — |
| `ANALYTICS_ENABLED` | Master switch | No | `true` | — |
| `TRUSTPILOT_BUSINESS_UNIT_ID` | Trustpilot | Optional | — | — |
| `GEO_LOCALE_*` / `IPAPI_KEY` | Geo redirect | Optional | enabled | — |
| `GEO_TEST_COUNTRY` | Local geo override | Dev only | — | `FR` |
| `JUDGEME_*` | Reviews | Recommended | — | — |
| `JUDGE_WEBHOOK_SECRET` / `JUDGEME_WEBHOOK_SECRET` | Judge webhooks | Optional | — | — |
| `VIDEO_AI_WEBHOOK_SECRET` / `WALLPASS_WEBHOOK_SECRET` | Other webhooks | Optional | — | — |
| `*_WEBHOOK_BEARER` | Bearer auth for webhooks | Optional | — | — |
| `ADMIN_EMAIL` | Admin login email | Yes for CMS | code fallback exists | `you@company.com` |
| `ADMIN_PASSWORD` | Admin login password | Yes for CMS | code fallback exists | *(strong secret)* |
| `ADMIN_MFA_ENABLED` | Present in `.env.example` only | No | `true` | **Not enforced in code today** |

`ADMIN_EMAIL` / `ADMIN_PASSWORD` are read by `AdminAuthController` but are **not listed in `.env.example`** — add them locally/production yourself. Prefer binding via `config/app.php` if present.

**Never** put production Admin passwords or API tokens in git, screenshots, or docs.
