# Dainely Premium Wellness

Multilingual e-commerce storefront for Dainely wellness products. Built with Laravel, Tailwind CSS, and Alpine.js.

## Features

- **Product catalog** — Shopify Admin API + storefront fallback (`/en/products`)
- **Product detail pages** — Dynamic Shopify-driven PDPs with Judge.me reviews
- **Checkout** — Square payments (sandbox/production) with Shopify order sync
- **Reviews** — Judge.me integration with server-side caching
- **Localization** — English, French, German (`/en`, `/fr`, `/de`)
- **Legal & support pages** — Privacy, terms, shipping, refund, FAQ, contact

## Requirements

- PHP 8.2+
- Composer
- Node.js 18+ (for Vite asset build)
- Optional: MySQL (currently session-only checkout; DB migrations available for future use)

## Setup

```bash
cp .env.example .env
composer install
npm install
php artisan key:generate
npm run build
php artisan serve
```

Open `http://localhost:8000`.

### Required environment variables

| Variable | Purpose |
|----------|---------|
| `SHOPIFY_CLIENT_ID` / `SHOPIFY_CLIENT_SECRET` | Shopify Admin API (products + orders) |
| `SHOPIFY_ADMIN_ACCESS_TOKEN` | Optional; overrides client credentials if set |
| `SQUARE_*` | Payment processing |
| `JUDGEME_API_TOKEN` | Product reviews |

See `.env.example` for the full list.

### Useful commands

```bash
php artisan square:setup --payments  # Verify Square + list recent API payments
php artisan reviews:warm-cache   # Pre-load Judge.me review data
php artisan view:clear           # Clear compiled Blade cache
npm run dev                      # Vite dev server (local CSS/JS hot reload)
```

### Square payments (sandbox verification)

Checkout charges cards through the **Square Payments API** (`POST /v2/payments`) using the Web Payments SDK.

If the Sandbox **Home** dashboard shows $0, open **Payments & invoices → Transactions** instead — API web payments appear there.

Confirm credentials match the same Square application:

```bash
php artisan square:setup --payments
```

Required `.env` keys: `SQUARE_APPLICATION_ID`, `SQUARE_ACCESS_TOKEN`, `SQUARE_LOCATION_ID`, `SQUARE_ENVIRONMENT=sandbox`.

## Project structure

```
app/
  Http/Controllers/Frontend/   # Public pages, cart, checkout
  Services/                    # Shopify, Square, Review, Currency
  Support/                     # CheckoutCart, ProductSlugResolver
config/
  company.php                  # Contact info (footer, header, legal)
  shopify.php                  # Shopify integration
resources/views/
  pages/                       # Static & catalog pages
  products/show.blade.php      # Product detail template
  partials/                    # Header, footer, reviews
routes/web.php                 # Locale-prefixed routes + webhooks
```

## Deployment notes

- Run `npm run build` and deploy `public/build/` to the server.
- Do **not** deploy `public/hot` (Vite dev marker).
- Set `APP_ENV=production`, `APP_DEBUG=false`, and configure real API credentials.

## License

Proprietary — Dainely LLC.
