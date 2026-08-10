# Changelog

Project documentation changelog for **Dainely Premium Wellness**. Application version tags may differ; dates reflect major engineering milestones around Phase 2.

## Unreleased

- Technical documentation set added under `/docs` (developer + AI onboarding).

## 2026-07 — Phase 2 CMS & platform (approx. v2.x)

### Features

- Supabase-backed CMS: product overlays, FAQs, page blocks, landing pages, bundles, related content, education blocks.
- Shopify catalog mirror table `dainely_products` + sync webhook/job/commands.
- Site search via PostgreSQL FTS (`search_index` + `SearchService`).
- Analytics pipeline (`AnalyticsEventService`, GA4 MP + Meta CAPI jobs).
- Integration webhooks: Judge.me, Video AI, Wallpass (processors stubbed where noted).
- Admin session CMS at `/admin/*`.
- Landing offer meta, parent landings, discount codes.
- Webhook retry columns + scheduled `RetryFailedWebhooksJob`.
- Judge.me shop totals via count API; review cache warmer scheduled hourly.
- Supabase diagnose command and raised PDO timeout defaults for shared hosting.

### Architecture

- Dual DB connections: default + `supabase`.
- Service-layer commerce (Shopify/Square) with Blade/Alpine storefront.
- Event `AnalyticsEventOccurred` → export jobs.

### Schema

- Phase 2 migration series `2026_07_10` → `2026_07_14` (see [Database](Database.md)).
- Finalize migration drops legacy analytics/search columns; ensures GIN index on `tsv`.

### Integrations

- Shopify Admin/Storefront, Square fallback, Judge.me, Open Exchange Rates, geo IP, GA4, Meta, Supabase.

### Breaking / ops notes

- Live Admin requires **`pdo_pgsql`**. Without it, CMS fails while Shopify storefront may still work.
- Prefer Supabase Session pooler from shared hosts (direct `db.*.supabase.co` often problematic).
- Some schema cleanup migrations are intentionally one-way.

## Earlier — Phase 1 storefront foundations

- Multilingual Laravel storefront (EN/FR/DE).
- Shopify-centric product browsing and checkout paths.
- Square payment option, currency display, geo locale redirect.
- Blog/education/static pages, sitemaps, basic SEO helpers.

---

When releasing, append dated sections with: features, schema, integrations, breaking changes. Keep entries concise.
