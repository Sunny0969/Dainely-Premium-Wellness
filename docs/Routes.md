# Route Documentation

Routes live in `routes/web.php` and `routes/api.php`. This page **summarizes by module** — not every named route.

---

## Locale storefront (`web.php`)

Prefix: `/{locale}` where `locale` ∈ `en|fr|de`. Middleware: `web`, `locale`.

| Module | Paths (relative to locale) | Controllers |
|--------|----------------------------|-------------|
| Home | `/` | `HomeController` |
| Products | `/products`, `/products/{slug}` | `ProductController` |
| Product reviews JSON | `/api/products/{handle}/reviews` | `ReviewController` |
| Blog | `/blog`, `/blog/{slug}` | `BlogController` |
| Education | `/education/{topic}` (fixed topics) | `EducationController` |
| Static | `/about`, `/contact`, `/faq`, newsletter POST | `PageController` |
| Legal | privacy, terms, shipping, refund | `PageController` |
| Cart | POST `/cart/add`, `/cart/update` | `CartController` |
| Checkout | `/checkout`, process, confirmation, discount, tax | `CheckoutController` |
| Search | `/search` | `SearchController` |
| Bundles | POST `/bundle/{bundleId}/add` | `BundleController` |
| Landing offer | `/landing/{id}/checkout` | `LandingPageController` |
| AI discovery | `/llms.txt` | closure → `public/llms_{locale}.txt` |
| Catch-all landing | `/{slug}` (**must stay last**) | `LandingPageController@show` |

Root `/` redirects to a locale using cookie or `GeoLocaleService`.

---

## Sitemaps (`web.php`)

| Path | Name |
|------|------|
| `/sitemap.xml` | Master index |
| `/{locale}/sitemap.xml` | Per-locale |

---

## Webhooks on web stack (`web.php`)

| Path | Middleware | Purpose |
|------|------------|---------|
| `POST /webhooks/shopify` | `api`, `webhook.shopify` | Shopify product sync (legacy URL still registered) |
| `POST /webhooks/square` | `api` | Square payment events |

---

## API routes (`api.php`)

Prefix: `/api` (Laravel default).

| Path | Name | Purpose |
|------|------|---------|
| `POST /api/webhooks/shopify` | `api.webhooks.shopify` | Shopify webhook (HMAC) |
| `POST /api/webhooks/judge` | `api.webhooks.judge` | Judge.me |
| `POST /api/webhooks/video-ai` | `api.webhooks.video-ai` | Video AI (stub processor) |
| `POST /api/webhooks/wallpass` | `api.webhooks.wallpass` | Wallpass (stub processor) |

There is **no** broad public REST resource API for products/orders. Integrations are webhook-centric. See [API](API.md).

---

## Admin CMS (`web.php`)

Prefix: `/admin`.

| Area | Auth | Notes |
|------|------|-------|
| Login / logout | Public | `AdminAuthController` |
| Dashboard | `admin.auth` | Counts / health |
| Webhooks | `admin.auth` | List + retry |
| Signals | `admin.auth` | Approve/update |
| FAQs | `admin.auth` | CRUD |
| Landings + blocks | `admin.auth` | CRUD |
| Products + blocks | `admin.auth` | Overlay edit |
| Bundles + items | `admin.auth` | CRUD |
| Related content | `admin.auth` | CRUD |
| Education blocks | `admin.auth` | Block CRUD for catalog pages |

---

## Route ordering caveat

The landing catch-all `/{slug}` is registered **after** all concrete storefront routes. Adding a new top-level page under the locale group must be declared **above** that catch-all.
