# API Documentation

This app is **not** a general public REST API. External surface area is mostly **webhooks** plus a few JSON helpers used by the storefront.

Base URL examples: `https://your-domain.test` or live host.

---

## Authentication

| Endpoint type | Auth |
|---------------|------|
| Shopify webhooks | HMAC header validated by `webhook.shopify` middleware (`SHOPIFY_WEBHOOK_SECRET`) |
| Judge / Video AI / Wallpass | Optional HMAC and/or bearer from `config/webhooks.php` when headers are present |
| Square webhook | Signature via `SquareService` |
| Storefront review JSON | Public (no user auth); rate-limit via `api` group throttle |
| Admin CMS | Session cookie after login — **not** token API |

There is no documented Sanctum personal-access-token API for CMS resources.

---

## Webhook endpoints

### Shopify product sync

- `POST /api/webhooks/shopify`
- Also: `POST /webhooks/shopify` (legacy registration)

**Headers:** Shopify HMAC (`X-Shopify-Hmac-Sha256` typical).

**Behavior:** Validate → log → dispatch `SyncProductJob` (and related handling in `ShopifyWebhookController`).

**Errors:** `401` on bad signature.

### Judge.me

- `POST /api/webhooks/judge`

**Behavior:** Validate optional signature → write `webhook_logs` when Supabase up → `ProcessWebhookJob` (warms review cache for handles when possible).

### Video AI / Wallpass

- `POST /api/webhooks/video-ai`
- `POST /api/webhooks/wallpass`

**Behavior:** Same intake pattern; processors are **stubs** (log + mark processed) until product requirements land.

### Square

- `POST /webhooks/square`

Payment lifecycle updates for Square fallback checkout.

---

## Storefront JSON

### Product reviews

`GET /{locale}/api/products/{handle}/reviews`

Returns cached Judge.me review payload for lazy UI.

**Example response shape (illustrative):**

```json
{
  "reviews": [],
  "stats": { "average": 4.8, "count": 11000 }
}
```

Exact keys follow `ReviewService` / `ReviewController` implementation.

---

## Checkout helpers (web, CSRF protected)

These are form/AJAX endpoints under the locale group, not a public API:

| Method | Path | Purpose |
|--------|------|---------|
| POST | `/{locale}/checkout/validate-discount` | Discount validation |
| POST | `/{locale}/checkout/tax-estimate` | Tax estimate |
| POST | `/{locale}/checkout/process` | Place order / redirect to Shopify |

Validation: Laravel `$request->validate(...)` in controllers. Failures typically redirect back with errors or return JSON validation errors for XHR.

---

## Error responses

| Situation | Typical result |
|-----------|----------------|
| Invalid Shopify HMAC | 401 |
| Missing webhook auth when required | 401/403 per controller |
| Validation failure (web forms) | 422 redirect/JSON |
| Supabase down | Admin shows warning; storefront may degrade gracefully |
| Throttle (`api` group) | 429 |

---

## Rate limiting

`api` middleware group includes `ThrottleRequests`. Web routes rely on CSRF + session, not API tokens.

---

## Request example (Shopify webhook)

```http
POST /api/webhooks/shopify HTTP/1.1
Host: example.com
Content-Type: application/json
X-Shopify-Hmac-Sha256: <base64-hmac>
X-Shopify-Topic: products/update

{"id":123,"handle":"example","title":"..."}
```

Do not commit real secrets or production payloads into docs/tests.
