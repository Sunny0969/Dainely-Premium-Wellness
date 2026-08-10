# Code Map

Quick answers to “where is X?”

| Question | Location |
|----------|----------|
| Where is **Admin authentication**? | `AdminAuthController`, middleware `AdminAuth`, session key `admin_authenticated` |
| Where is **storefront locale**? | `LocaleMiddleware`, `GeoLocaleService`, `routes/web.php` locale group |
| Where are **products displayed**? | `Frontend\ProductController`, `ShopifyService`, views `products/` + `partials/product-*` |
| Where is **product CMS overlay**? | `AdminProductController`, `Supabase\Product`, `Supabase\ProductContent` |
| Where is **cart**? | `CartController`, session cart, `partials/cart-drawer.blade.php` |
| Where is **checkout / billing**? | `CheckoutController`, `ShopifyCheckoutService`, optional `SquareService` |
| Where are **orders persisted**? | `OrderPersistenceService`, model `Order` / `OrderItem` (legacy connection) |
| Where is **Shopify sync**? | `ShopifyWebhookController`, `SyncProductJob`, `shopify:sync-catalog` |
| Where are **landings**? | `LandingPageController`, `Supabase\LandingPage`, `AdminLandingController` |
| Where are **bundles**? | `BundleController`, `BundleDisplayService`, `AdminBundleController`, bundle models |
| Where is **related content**? | `RelatedContentResolver`, `AdminRelatedController`, table `related_content` |
| Where is **education**? | `EducationController`, `Catalog\EducationPage`, `AdminEducationController` |
| Where is **blog**? | `BlogController`, views `blog/`, catalog `Catalog\BlogPost` |
| Where is **search**? | `SearchController`, `SearchService`, `UpdateSearchIndexJob`, table `search_index` |
| Where are **FAQs**? | `Supabase\Faq`, `AdminFaqController`, block `components/blocks/faqs` |
| Where are **reviews**? | `ReviewService`, `ReviewController`, Judge.me config, `reviews:warm-cache` |
| Where is **SEO / JSON-LD**? | `SeoService`, `JsonLdBuilder`, `BreadcrumbBuilder`, `ai_schema_cache` |
| Where is **analytics**? | `AnalyticsEventService`, `TrackPageViews`, GA4/Meta jobs, tables `analytics_events` |
| Where are **webhooks API**? | `routes/api.php`, `GeneralWebhookController`, `ShopifyWebhookController`, `ProcessWebhookJob` |
| Where are **webhook logs UI**? | `AdminWebhookController`, `Supabase\WebhookLog` |
| Where are **knowledge signals**? | `AdminSignalController`, `ProductKnowledgeSignal` |
| Where are **recommendations**? | `RecommendationService`, `recommendation_rules` |
| Where is **currency / FX**? | `CurrencyService`, `config/currency.php` |
| Where is **tax**? | `ShopifyTaxService` |
| Where is **email**? | `config/mail.php`, contact handling in `PageController` (no large notification subsystem) |
| Where are **notifications**? | No first-class Laravel Notification center for shoppers; analytics/webhooks cover integrations |
| Where are **reports**? | No dedicated BI reports module; Admin dashboard counts + analytics tables |
| Where are **policies**? | Not used for Admin; see [Permissions](Permissions.md) |
| Where are **scheduled jobs**? | `app/Console/Kernel.php` |
| Where are **exports / imports**? | No general CSV export module; catalog **import** ≈ Shopify sync commands/jobs |
| Where are **organizations / learning paths**? | **N/A** — not part of this product |
| Where is **Supabase health**? | `App\Support\SupabaseDb`, `supabase:diagnose` |
| Where is **frontend build**? | `vite.config.js`, `resources/js`, `resources/css` |
| Where are **tests**? | `tests/Feature`, `tests/Unit` |

---

## Controller index (by namespace)

- `App\Http\Controllers\Frontend\*` — storefront
- `App\Http\Controllers\Admin\*` — CMS
- `App\Http\Controllers\Webhooks\*` — Shopify, Square, general integrations

## Service index

See [Services](Services.md).
