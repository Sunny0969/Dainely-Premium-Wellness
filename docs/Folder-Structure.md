# Folder Structure Guide

Root: `Dainely-Premium-Wellness/`

## Top-level directories

| Path | Purpose |
|------|---------|
| `app/` | Application code (HTTP, services, models, jobs, console) |
| `bootstrap/` | Laravel bootstrap / app cache |
| `config/` | Configuration (Shopify, Supabase, analytics, etc.) |
| `database/` | Migrations, seeders, factories |
| `docs/` | This technical documentation |
| `public/` | Web root (`index.php`, assets, `llms_*.txt`) |
| `resources/` | Blade views, CSS/JS sources, lang files |
| `routes/` | `web.php`, `api.php`, `console.php`, `channels.php` |
| `storage/` | Logs, cache, uploads, framework files |
| `tests/` | PHPUnit feature & unit tests |
| `scripts/` | Ops / helper scripts |
| `vendor/` | Composer dependencies (do not edit) |
| `node_modules/` | npm dependencies (do not edit) |

Deploy helpers at root (`deploy-*.ps1`, zip artifacts) are operational — not part of the runtime architecture.

---

## `app/` layout

```
app/
├── Console/Commands/     Artisan commands
├── Contracts/            Interfaces (e.g. SearchableEntity)
├── Events/               Domain events
├── Exceptions/           Exception handler
├── Http/
│   ├── Controllers/
│   │   ├── Admin/        CMS controllers
│   │   ├── Frontend/     Storefront controllers
│   │   └── Webhooks/     Shopify, Square, Judge, etc.
│   ├── Middleware/       locale, admin.auth, Shopify HMAC, page views
│   └── Kernel.php
├── Jobs/                 Queued / sync jobs
├── Listeners/            Event listeners
├── Models/
│   ├── Catalog/          Non-Eloquent catalog adapters (education, blog search)
│   └── Supabase/         Phase 2 Eloquent models (connection: supabase)
├── Providers/            Service & event providers
├── Services/             Business logic (preferred place for features)
└── Support/              Small helpers (SupabaseDb, WebhookSignature, ContentCatalog)
```

### Controllers

Thin HTTP adapters. Prefer calling **Services** rather than putting business logic in controllers.

- `Frontend\*` — storefront pages, cart, checkout, search
- `Admin\*` — CMS CRUD
- `Webhooks\*` — inbound integrations

### Models

- **`App\Models\Supabase\*`** — Phase 2 CMS (always `protected $connection = 'supabase'`).
- **`App\Models\Catalog\*`** — lightweight catalog entities for search/related (not always full Eloquent tables).
- **`App\Models\*`** (root) — legacy/default-connection models (`Order`, `User`, older `Product`, blog tables). Prefer Supabase product models for Phase 2 catalog work.

### Services

Primary business layer. See [Services](Services.md). No formal Repository layer in this project — services talk to Eloquent / HTTP clients directly.

### Jobs / Events / Listeners

- Jobs: Shopify sync, search index, analytics export, webhook processing.
- Events: `AnalyticsEventOccurred`.
- Listeners: `DispatchAnalyticsExportJobs`.

### Middleware

Registered in `app/Http/Kernel.php`. Key aliases: `locale`, `admin.auth`, `webhook.shopify`.

### Traits / Policies / Observers / Repositories

| Pattern | Status |
|---------|--------|
| Repositories | **Not used** — services + models |
| Policies | **Not used** — admin is session gate only |
| Custom traits | `app/Traits/HasLocalizedContent.php` — `scopeLocalized` / `scopeForLocale` |
| Observers | Model `booted()` hooks on searchable Supabase models (search index queue) instead of dedicated Observer classes |

### Console commands

Under `app/Console/Commands/`. Schedule in `app/Console/Kernel.php`. See [Queues](Queues.md) and [Developer-Guide](Developer-Guide.md).

### Helpers / Support

- `SupabaseDb` — feature flag + ping / failure reason for Admin
- `WebhookSignature` — HMAC / bearer validation helpers
- `ContentCatalog` / `StaticCatalog` — education/blog IDs and DB-less product fallback
- `CheckoutCart` / `CheckoutTotals` — session cart v2 and money math
- `ProductSlugResolver`, `ProductRequiresSize`, `ProductLandingAssets`, `ProductLandingLang`, `PostalCode`

### Contracts

- `App\Contracts\SearchableEntity` — title/body/keywords for search indexing

---

## Other important paths

| Path | Notes |
|------|-------|
| `resources/views/` | Blade: `layouts/`, `pages/`, `admin/`, `components/blocks/` |
| `resources/js/` | Vite entry + Alpine |
| `resources/css/` | Tailwind entry |
| `config/shopify.php`, `supabase.php`, `analytics.php`, `judgeme.php`, `webhooks.php`, `square.php`, `geo.php`, `currency.php` | Domain config |
| `database/migrations/` | Mix of default-connection and `Schema::connection('supabase')` migrations |
