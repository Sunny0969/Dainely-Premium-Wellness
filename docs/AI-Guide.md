# AI Assistant Guide

For Cursor, Copilot, Claude, ChatGPT, Windsurf, etc. generating code in this repo.

## High-level architecture

- Laravel 10 multilingual storefront (EN/FR/DE).
- **Shopify** owns catalog commerce; **Supabase PostgreSQL** owns Phase 2 CMS.
- Business logic belongs in **`app/Services`**, not fat controllers.
- **No repository layer. No Admin Policies.** Admin = env credentials + `admin.auth` session.

Read [Architecture](Architecture.md) and [README](README.md) first.

---

## Project conventions

1. New CMS tables → `Schema::connection('supabase')` + model in `App\Models\Supabase`.
2. Product mirror table name is **`dainely_products`** (model `Supabase\Product`).
3. Register event listeners manually in `EventServiceProvider`.
4. Locale routes must stay above the landing catch-all `/{slug}`.
5. Guard Supabase with `SupabaseDb::available()` when code may run without PDO.
6. Update `docs/` when adding features.

---

## Folder responsibilities

| Path | Put here |
|------|----------|
| `app/Services` | Domain + integrations |
| `app/Http/Controllers/Frontend` | Storefront HTTP |
| `app/Http/Controllers/Admin` | CMS HTTP |
| `app/Http/Controllers/Webhooks` | Inbound webhooks |
| `app/Jobs` | Async work |
| `app/Support` | Tiny shared helpers |
| `resources/views/components/blocks` | New block UIs |
| `config/*.php` | All `env()` reads |

---

## Service / repository patterns

- **Do:** inject services; keep methods focused.
- **Don’t:** add repositories by default.
- **Don’t:** call `env()` outside config files.

---

## Naming

Follow [Coding-Standards](Coding-Standards.md). Artisan: `area:action`. Jobs: `*Job`.

---

## Business rules (do not violate casually)

- Shopify remains source of truth for price/inventory.
- Supabase overlays change marketing content, not stock.
- Native Shopify checkout is preferred when `SHOPIFY_NATIVE_CHECKOUT=true`.
- Square is fallback only.
- Reviews totals should use Judge.me count API behavior already in `ReviewService`.
- Webhook processors for video-ai/wallpass are stubs — extend carefully with logging + `webhook_logs`.

---

## Preferred coding style

- PHP 8.1+ features OK (match expressions already used).
- Thin controllers, typed where surrounding code is typed.
- Blade + Alpine only for UI (no Vue/React introduction).
- Minimal comments; mirror neighboring files.

---

## Common pitfalls

| Pitfall | Instead |
|---------|---------|
| Querying table `products` for Phase 2 | Use `dainely_products` / `Supabase\Product` |
| Assuming Admin uses Spatie roles | Session flag only |
| Adding routes after landing catch-all | Insert above catch-all |
| Forgetting `pdo_pgsql` on host | Document + degrade gracefully |
| Hardcoding secrets | Config + `.env` |
| Reindexing search only in Admin | Ensure model hooks / `SearchService` |

---

## Files that need review before changing

- `routes/web.php` (order-sensitive)
- `app/Services/ShopifyService.php` (large, checkout-critical)
- `app/Http/Middleware/VerifyShopifyWebhook.php`
- `config/database.php` supabase connection
- Checkout controllers/services
- Deploy scripts that touch live hosts
- Anything rewriting webhook HMAC validation

---

## How to implement a new feature

1. Identify: storefront only, CMS, integration, or all three.
2. Add migration on correct connection.
3. Add/extend Supabase model + service methods.
4. Wire controller + routes (correct group).
5. Blade / Alpine UI.
6. Tests or `*:verify` command.
7. Docs: Features, CodeMap, and any touched specialist doc.
8. If searchable: implement searchable helpers + index job path.

---

## Reusable components & helpers

- Block partials under `components/blocks/*`
- `BreadcrumbBuilder`, `JsonLdBuilder`, `SeoService`
- `RelatedContentResolver`, `BundleDisplayService`, `LandingOfferService`
- `App\Support\*`: `SupabaseDb`, `WebhookSignature`, `ContentCatalog`, `CheckoutCart`, `CheckoutTotals`, …
- Trait: `HasLocalizedContent`
- Contract: `App\Contracts\SearchableEntity`

---

## Base classes

- Controllers extend `App\Http\Controllers\Controller`.
- Jobs use standard Laravel queue traits.
- No custom heavy base Model beyond Eloquent.

When unsure, open an existing similar feature (e.g. landings + blocks) and copy structure rather than inventing a new pattern.
