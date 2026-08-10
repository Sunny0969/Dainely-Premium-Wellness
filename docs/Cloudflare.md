# Cloudflare (CDN & Edge Caching)

Dainely already sits behind Cloudflare on live hosts. Use this checklist so **static assets** and **selected HTML** are served from the edge.

## 1. DNS / proxy

- Domain proxied through Cloudflare (orange cloud).
- SSL/TLS mode: **Full (strict)** when the origin has a valid cert.

## 2. Static assets (always cache)

Create a **Cache Rule** (Caching → Cache Rules):

| Field | Value |
|--------|--------|
| If | URI Path starts with `/build` **OR** `/images` **OR** `/fonts` **OR** matches `*.webp` / `*.css` / `*.js` (as needed) |
| Then | Eligible for cache |
| Edge TTL | 1 month (or “Ignore cache-control header / Override” → 1 year for `/build/*` — Vite hashes filenames) |
| Browser TTL | Respect origin / 1 day |

Vite production builds emit hashed files under `public/build/` (`npm run build`). Safe to cache aggressively.

**Rocket Loader:** keep **off** for checkout pages, or keep `data-cfasync="false"` on critical inline scripts (already used on checkout).

## 3. Full-page HTML (CMS / blog / education)

The app sends edge-friendly headers via middleware `cf.cache` on:

- `/{locale}/blog`, `/{locale}/blog/{slug}`
- `/{locale}/education/*`
- About, FAQ, legal pages
- CMS landing pages `/{locale}/{slug}`
- Sitemaps

Headers (when `CLOUDFLARE_EDGE_CACHE=true`):

- `Cache-Control: public, max-age=0, s-maxage=300, …`
- `CDN-Cache-Control` / `Cloudflare-CDN-Cache-Control: public, max-age=300`

### Cache Rule for HTML

| Field | Value |
|--------|--------|
| If | URI Path matches `/*/blog*` **OR** `/*/education/*` **OR** `/*/about` **OR** `/*/faq` **OR** `/*/privacy-policy` **OR** `/*/terms` **OR** `/*/shipping-policy` **OR** `/*/refund-policy` (adjust for landings) |
| Then | **Eligible for cache** |
| Edge TTL | Use cache-control header / respect origin |
| **Cookie** | **Ignore presence of cookies** (required — Laravel sets a session cookie; without this, HTML rarely caches) |

### Never full-page cache

- `/dainely-admin-panel/*`
- `/*/checkout*`, cart POSTs
- `/*/products*`, home (personalized / inventory-sensitive)
- `/*/contact` (forms / CSRF)
- `/webhooks/*`, `/api/webhooks/*`

Cart badge in the header can be briefly stale on edge-cached HTML (TTL default **5 minutes**). Tune with `CLOUDFLARE_HTML_EDGE_TTL`.

## 4. Env

```env
CLOUDFLARE_EDGE_CACHE=true
CLOUDFLARE_HTML_EDGE_TTL=300
CLOUDFLARE_HTML_BROWSER_TTL=0
```

Disable edge HTML headers locally if needed: `CLOUDFLARE_EDGE_CACHE=false`.

## 5. After CMS publish

Purge Cloudflare cache for that URL (or “Custom purge” of `/en/blog/...`) so visitors see updates before TTL expires. Admin saves already invalidate Laravel app cache; edge HTML is separate.

## 6. Verify

1. Open a blog URL in a private window.
2. DevTools → Network → Response headers: look for `cf-cache-status: HIT` (after second load) and `CDN-Cache-Control`.
3. Confirm checkout / product pages stay `DYNAMIC` or `BYPASS`.
