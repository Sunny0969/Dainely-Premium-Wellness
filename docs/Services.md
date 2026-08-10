# Services

All under `app/Services/`. Controllers and jobs should depend on these classes.

| Service | Purpose | Used by | Dependencies | Major methods |
|---------|---------|---------|--------------|---------------|
| `ShopifyService` | Admin/Storefront GraphQL & REST: products, customers, orders, discounts, webhook HMAC | Controllers, tax, checkout, sync jobs/commands | Config `shopify`, Guzzle/HTTP | `graphql`, `fetchProducts`, `fetchProductByHandle`, `createOrder*`, `validateDiscountCode`, `validateWebhookSignature` |
| `ShopifyCheckoutService` | Build native Shopify checkout URLs / carts | `CheckoutController` | Shopify Storefront token | `createCheckoutUrl`, `createCheckout` |
| `ShopifyTaxService` | Tax estimates for checkout | Checkout | `ShopifyService` | `estimate`, `estimateInitialUsd` |
| `SquareService` | Square payments + webhook signature | Checkout, Square webhook | `config/square.php` | `createPayment`, `refundPayment`, `validateWebhookSignature` |
| `CurrencyService` | FX rates & formatting | Checkout, views | Open Exchange Rates | `convert`, `formatForLocale`, `refreshRates` |
| `GeoLocaleService` | IP → country → locale/currency | `/` redirect, analytics | `config/geo.php` | `detectLocaleFromRequest`, `resolveCountryCode` |
| `ProductTranslationService` | Apply locale overlays onto Shopify product arrays | Product views | Content overlays | `apply`, `applyMany`, `titleForHandle` |
| `ReviewService` | Judge.me fetch + cache + shop totals | PDP, warm-cache command, webhooks | `config/judgeme.php` | `getProductReviews`, `getCachedStats`, `warmCacheForHandle`, `fetchShopWideTotals` |
| `SearchService` | Query `search_index`, URL resolution, reindex helpers | `SearchController`, verify command | Supabase FTS | `search`, `queueIndex`, `indexNow`, `reindexAll`, `deindex` |
| `RelatedContentResolver` | Resolve related entities for a source | Product/landing/education views, admin verify | `related_content` | `for` |
| `LandingOfferService` | Resolve landing CTA offer; add to cart | Landing controller | Bundles / products | `resolve`, `addOfferToCartAndRedirect` |
| `BundleDisplayService` | Present bundles for blocks | Landing/product views | Bundle models | `present`, `mapForBlocks` |
| `RecommendationService` | Cart recommendations | Checkout/cart | `recommendation_rules` | `getRecommendationsForCart` |
| `AnalyticsEventService` | Central `track()` pipeline | Controllers, middleware | Geo, events | `track` |
| `AnalyticsService` | Thin wrapper for events + activity log | Various | `AnalyticsEventService` | `logEvent`, `logActivity` |
| `OrderPersistenceService` | Save/load paid order payloads for Shopify sync | Checkout / jobs | Orders storage | `savePaidOrder`, `markShopifySynced` |
| `SeoService` | Page meta helpers + some schema strings | Controllers/views | — | `set`, `hreflangLinks`, schema helpers |
| `JsonLdBuilder` | Structured data for product/landing/org/FAQ | PDP/landing | Models | `buildForProduct`, `buildForLandingPage`, … |
| `BreadcrumbBuilder` | Trail + BreadcrumbList schema | Views | Landing hierarchy | `forProduct`, `forLandingPage`, `toSchema` |

---

## Patterns

1. Inject services via constructor (Laravel container).
2. Keep HTTP credentials in `config/*` read from env — never hardcode secrets.
3. Guard Supabase usage with `App\Support\SupabaseDb::available()` when the host may lack `pdo_pgsql`.
4. Prefer returning plain arrays/DTOs for view data over leaking remote API shapes into Blade.

---

## Support collaborators (not Services)

| Class | Role |
|-------|------|
| `SupabaseDb` | Ping, availability, failure reason |
| `WebhookSignature` | Shared webhook auth helpers |
| `ContentCatalog` / `StaticCatalog` | Education/blog IDs; static product fallback |
| `CheckoutCart` / `CheckoutTotals` | Session cart + totals/currency helpers |
| `ProductSlugResolver` | Legacy slug → Shopify handle |
| `ProductRequiresSize` / `ProductLandingAssets` / `ProductLandingLang` | PDP size rules & landing copy helpers |
| `PostalCode` | Country postal validation patterns |
