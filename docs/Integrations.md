# Integrations

## Shopify

| | |
|-|-|
| **Purpose** | Source of truth for products, preferred checkout, tax, discounts, order sync |
| **Auth** | Admin access token and/or client credentials; Storefront access token for checkout |
| **Config** | `config/shopify.php`, `config/shopify_tax_fallback.php` |
| **Important files** | `ShopifyService`, `ShopifyCheckoutService`, `ShopifyTaxService`, `ShopifyWebhookController`, `SyncProductJob`, `shopify:*` commands |
| **Env** | `SHOPIFY_SHOP_DOMAIN`, `SHOPIFY_*_TOKEN`, `SHOPIFY_CLIENT_*`, `SHOPIFY_API_VERSION`, `SHOPIFY_WEBHOOK_SECRET`, `SHOPIFY_NATIVE_CHECKOUT`, `FEATURES_SQUARE_FALLBACK`, … |
| **Failure handling** | Health command; webhook HMAC rejection; order sync failure markers via `OrderPersistenceService` |

---

## Supabase (PostgreSQL)

| | |
|-|-|
| **Purpose** | Phase 2 CMS database |
| **Auth** | DB user/password (prefer **Session pooler** from shared hosts); optional REST keys for API |
| **Config** | `config/database.php` connection `supabase`, `config/supabase.php` |
| **Important files** | `App\Models\Supabase\*`, `App\Support\SupabaseDb`, `supabase:diagnose` |
| **Env** | `DB_SUPABASE_*`, `FEATURES_SUPABASE`, `SUPABASE_URL`, `SUPABASE_*_KEY` |
| **Failure handling** | `SupabaseDb::available()` / `failureReason()`; Admin shows DB warning; set `FEATURES_SUPABASE=false` only if intentional |

**Hosting requirement:** PHP `pgsql` + `pdo_pgsql` enabled.

---

## Square (fallback payments)

| | |
|-|-|
| **Purpose** | Optional card payment when Shopify native checkout is not used |
| **Auth** | Application id + access token + location |
| **Config** | `config/square.php` |
| **Files** | `SquareService`, `SquareWebhookController`, `square:setup` |
| **Env** | `SQUARE_*`, `FEATURES_SQUARE_FALLBACK` |
| **Failure handling** | Payment API errors returned to checkout; webhook signature validation |

---

## Judge.me

| | |
|-|-|
| **Purpose** | Product reviews and shop-wide totals |
| **Auth** | API token; webhook secret optional |
| **Config** | `config/judgeme.php` |
| **Files** | `ReviewService`, review Blade partials, `reviews:warm-cache`, Judge webhook path |
| **Env** | `JUDGEME_API_TOKEN`, `JUDGEME_SHOP_DOMAIN`, `JUDGEME_CACHE_TTL`, `JUDGEME_USE_COUNT_API`, `JUDGE*_WEBHOOK_*` |
| **Failure handling** | Cached fallbacks; count API preferred for large totals |

---

## Google Analytics 4

| | |
|-|-|
| **Purpose** | Browser gtag + server Measurement Protocol |
| **Auth** | Measurement ID + API secret |
| **Config** | `config/analytics.php` |
| **Files** | `AnalyticsEventService`, `SendGa4MeasurementProtocolJob` |
| **Env** | `GA4_MEASUREMENT_ID`, `GA4_API_SECRET`, `ANALYTICS_ENABLED` |
| **Failure handling** | Jobs log errors; tracking skipped when disabled/missing creds |

---

## Meta Conversions API

| | |
|-|-|
| **Purpose** | Server-side ad conversion events |
| **Auth** | Pixel ID + access token |
| **Config** | `config/analytics.php` |
| **Files** | `SendMetaConversionJob`, analytics listener |
| **Env** | `META_PIXEL_ID`, `META_CAPI_ACCESS_TOKEN`, optional `META_TEST_EVENT_CODE` |
| **Failure handling** | Same as GA4 jobs |

---

## Open Exchange Rates

| | |
|-|-|
| **Purpose** | FX for non-USD display |
| **Auth** | App ID |
| **Config** | `config/currency.php` |
| **Files** | `CurrencyService` |
| **Env** | `OPEN_EXCHANGE_RATES_APP_ID`, `BASE_CURRENCY` |
| **Failure handling** | Cached rates; conversion falls back carefully when refresh fails |

---

## IP geolocation

| | |
|-|-|
| **Purpose** | First-visit locale redirect |
| **Config** | `config/geo.php` |
| **Files** | `GeoLocaleService` |
| **Env** | `GEO_LOCALE_ENABLED`, `GEO_LOCALE_CACHE_TTL`, `IPAPI_KEY`, optional `GEO_TEST_COUNTRY` |
| **Failure handling** | Default locale when lookup fails |

---

## Trustpilot

| | |
|-|-|
| **Purpose** | Business unit id for widgets/display |
| **Env** | `TRUSTPILOT_BUSINESS_UNIT_ID` |

---

## Mail

| | |
|-|-|
| **Purpose** | Contact / transactional email |
| **Config** | `config/mail.php` |
| **Env** | `MAIL_*` |
| **Note** | Local often uses Mailpit |

---

## Video AI & Wallpass

Webhook intake exists; processing is intentionally minimal (stub). Secrets in `config/webhooks.php`. Extend `ProcessWebhookJob` when requirements are ready.
