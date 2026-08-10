# Feature Catalog

Major features of Dainely Premium Wellness. Paths are relative to the project root.

| Feature | Description | Main files | Routes | Database tables |
|---------|-------------|------------|--------|-----------------|
| Multilingual storefront | EN/FR/DE locale prefix, cookie + geo redirect | `LocaleMiddleware`, `GeoLocaleService`, `routes/web.php` | `/{locale}/…`, `/` | — |
| Product catalog (Shopify) | Fetch/map Shopify products for PLP/PDP | `ShopifyService`, `ProductController` | `products.index`, `products.show` | Shopify remote; mirror `dainely_products` |
| Product content overlay | CMS titles/descriptions per locale | `AdminProductController`, `Supabase\ProductContent` | `/admin/products` | `product_content`, `dainely_products` |
| Page blocks | Reusable blocks (FAQ, CTA, video, bundle, …) | `PageBlock`, block Blade components | Admin landings/products/education | `page_blocks` |
| Shopify checkout | Native Storefront checkout URLs | `ShopifyCheckoutService`, `CheckoutController` | `checkout.*` | Session cart; optional `orders` |
| Square fallback | Optional card payment path | `SquareService`, `SquareWebhookController` | `webhooks.square`, checkout | `orders` when used |
| Cart | Session cart add/update + drawer UI | `CartController`, cart partials | `cart.store`, `cart.update` | Session |
| Tax & discounts | Shopify tax estimate + discount codes | `ShopifyTaxService`, `ShopifyService` | `checkout.tax-estimate`, `checkout.validate-discount` | Shopify |
| Reviews (Judge.me) | Cached review stats & lists | `ReviewService`, `ReviewController` | `products.reviews` | Cache; webhooks may warm |
| Currency / FX | USD base, locale display currencies | `CurrencyService` | Used in checkout/views | Optional `currencies` |
| Geo locale | IP → country → locale/currency | `GeoLocaleService` | `/` redirect | Cache |
| Landing pages | Catch-all CMS landings + offer CTA | `LandingPageController`, `LandingOfferService` | `landing.show`, `landing.checkout` | `landing_pages`, `page_blocks` |
| Bundles | Multi-product bundles → expand to cart | `BundleController`, `BundleDisplayService` | `bundle.add`, `/admin/bundles` | `product_bundles`, `product_bundle_items` |
| Related content | Knowledge graph links between entities | `RelatedContentResolver`, `AdminRelatedController` | `/admin/related` | `related_content` |
| Education pages | Fixed education routes + CMS blocks/FAQs | `EducationController`, `Catalog\EducationPage` | `education.*`, `/admin/education` | `page_blocks`, `faqs` |
| Blog | Blog index/show (legacy + catalog search) | `BlogController`, `Catalog\BlogPost` | `blog.*` | `blog_posts` (+ translations) if used |
| Site search | Postgres FTS over indexed entities | `SearchService`, `UpdateSearchIndexJob` | `search` | `search_index` |
| SEO / JSON-LD | Meta, hreflang, product/FAQ schema | `SeoService`, `JsonLdBuilder`, `BreadcrumbBuilder` | Views / layouts | `ai_schema_cache` (optional) |
| Knowledge signals | AI/discoverability signals approval | `AdminSignalController`, `ProductKnowledgeSignal` | `/admin/signals` | `product_knowledge_signals` |
| FAQs (CMS) | Polymorphic FAQs per entity/locale | `AdminFaqController`, `Supabase\Faq` | `/admin/faqs`, storefront FAQ views | `faqs` |
| Analytics | Track events + activity; GA4/Meta export | `AnalyticsEventService`, analytics jobs | Via middleware/controllers | `analytics_events`, `user_activity_log` |
| Shopify sync | Webhook + Artisan catalog sync | `ShopifyWebhookController`, `SyncProductJob`, `shopify:sync-catalog` | `/api/webhooks/shopify`, `/webhooks/shopify` | `dainely_products`, `webhook_logs` |
| Integration webhooks | Judge / Video AI / Wallpass listeners | `GeneralWebhookController`, `ProcessWebhookJob` | `/api/webhooks/judge|video-ai|wallpass` | `webhook_logs` |
| Admin CMS | Session-auth lightweight CRUD | `Admin*Controller`, `AdminAuth` | `/admin/*` | Supabase tables |
| Recommendations | Cart upsell rules | `RecommendationService` | Checkout/cart UI | `recommendation_rules` |
| Sitemaps / llms.txt | SEO discovery files | `PageController`, public `llms_*.txt` | `sitemap.*`, `/{locale}/llms.txt` | — |
| Contact / newsletter | Form endpoints | `PageController` | `contact`, `newsletter.subscribe` | Mail / logs |
| Static / legal pages | About, policies | `PageController` | `about`, `privacy`, … | — |

### Not in this product

These prompt examples **do not** apply: Learning Paths, Organizations, Assignments, Certificates, Marketplace marketplace modules, Surveys as a first-class module. Use this table as the source of truth.
