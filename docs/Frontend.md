# Frontend

## Stack

| Tech | Used? |
|------|-------|
| Blade | **Yes** — primary templating |
| Alpine.js | **Yes** — interactivity |
| Tailwind CSS 3 | **Yes** |
| Vite 5 | **Yes** — asset bundling |
| Vue / React / Livewire | **No** |

---

## Blade structure

```
resources/views/
├── layouts/app.blade.php      Storefront layout
├── layouts/admin.blade.php    Admin layout
├── pages/                     Home, about, contact, FAQ, products index, …
├── products/show.blade.php
├── blog/
├── education/
├── landing/
├── checkout/
├── admin/                     CMS screens
├── components/
│   ├── blocks/                Landing/product block partials
│   ├── breadcrumbs.blade.php
│   └── related-content.blade.php
├── partials/                  Header, footer, cart, reviews, product hero
└── sitemap/
```

Block types (examples): `benefits`, `faqs`, `cta`, `video`, `comparison`, `bundle`, `testimonials`, `how-it-works`.

---

## JavaScript

- Entry via Vite (`resources/js/app.js` pattern).
- Alpine + `@alpinejs/collapse` for UI disclosure.
- Axios available for XHR (reviews, tax estimate, etc.).
- Prefer progressive enhancement; keep critical commerce paths working without SPA routing.

---

## CSS

- Tailwind via `resources/css/app.css` + `tailwind.config.js`.
- Plugins: forms, typography.
- Avoid introducing a second CSS framework.

---

## Assets & build

```bash
npm install
npm run dev      # Vite HMR
npm run build    # production → public/build
```

Blade should use `@vite([...])` for CSS/JS.

Static SEO files may live in `public/` (e.g. `llms_en.txt`).

---

## Localization in UI

- URLs are prefixed with locale (`en` / `fr` / `de`).
- Translation files live under `resources/lang/{locale}/` (e.g. `header`, `footer`, `checkout`, `products`, `product_landing`, `bundles`, …).
- Copy may also come from Blade, Shopify fields, or Supabase overlays — check the feature before hardcoding strings.

---

## Admin UI

Server-rendered Blade forms under `/admin`. Not a SPA. Keep forms simple and CSRF-protected (`web` middleware).
