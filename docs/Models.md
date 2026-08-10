# Models

## Phase 2 — `App\Models\Supabase\*`

All use `protected $connection = 'supabase'`.

### `Product` (`dainely_products`)

- **Purpose:** Shopify product mirror for CMS/search.
- **Relationships:** `productContents`, `knowledgeSignals`, morph `faqs`, `pageBlocks`.
- **Business logic:** Locale helpers `getTranslatedTitle`, `getPlainTextContent`, `getSearchKeywords`; `booted()` queues search reindex.
- **Implements:** Searchable via contract helpers.

### `ProductContent` (`product_content`)

- **Purpose:** Per-locale overlay content.
- **Relationships:** `belongsTo` Product.
- **Hooks:** `booted()` may trigger search updates.

### `LandingPage` (`landing_pages`)

- **Purpose:** Published marketing pages.
- **Relationships:** `pageBlocks`, `faqs`, `parent`/`children`, `bundle`.
- **Scopes:** `published`.
- **Business logic:** Offer metadata, translated title/body for search; search hooks in `booted()`.

### `PageBlock` (`page_blocks`)

- **Purpose:** Typed content blocks (JSON `content`).
- **Relationships:** `morphTo` `blockable`.
- **Scopes:** `visible`.

### `Faq` (`faqs`)

- **Purpose:** Polymorphic Q&A.
- **Relationships:** `morphTo` `faqable`.
- **Scopes:** `approved`.

### `ProductKnowledgeSignal`

- **Purpose:** Knowledge/AI signals for products.
- **Relationships:** `belongsTo` Product.
- **Scopes:** `approved`.

### `RelatedContent`

- **Purpose:** Graph edges between entities (source → target).

### `ProductBundle` / `ProductBundleItem`

- **Purpose:** Bundle header + line items with quantities.
- **Relationships:** Bundle `hasMany` items; item → Product.

### `AiSchemaCache`

- **Purpose:** Cached schema documents.
- **Relationships:** `morphTo` `cacheable`.

### `AnalyticsEvent`

- **Purpose:** Persist tracked events (`event_name`, `event_data`, …).

### `UserActivityLog`

- **Purpose:** Activity rows with optional morph `item`.

### `SearchIndex`

- **Purpose:** FTS documents.
- **Relationships:** `morphTo` `searchable`.

### `WebhookLog`

- **Purpose:** Webhook audit + retry.
- **Scopes:** `retryable`, `dead`.
- **Methods:** `nextRetryDelay`, `markFailedWithRetry`, `markProcessed`.

### `RecommendationRule`

- **Purpose:** Upsell rules.
- **Relationships:** morph `sourceItem`, `recommendedItem`.

---

## Catalog adapters — `App\Models\Catalog\*`

Not always backed by a dedicated Supabase table; used for search/related resolution.

| Model | Purpose |
|-------|---------|
| `EducationPage` | Fixed education topics; loads blocks/FAQs by catalog id |
| `BlogPost` | Catalog view of blog for search keywords/title |

---

## Legacy / default models — `App\Models\*`

| Model | Purpose | Notes |
|-------|---------|-------|
| `User` | Sanctum user | Scaffold; not Admin CMS |
| `Product` | Older local products | SoftDeletes; prefer Supabase `Product` for Phase 2 |
| `ProductTranslation` | Local translations | Legacy |
| `ProductContent` (root namespace) | Older overlay model | Prefer `Supabase\ProductContent` |
| `Order` / `OrderItem` | Checkout persistence | Used by order persistence paths |
| `DiscountCode` | Local discounts | `isValid`, `calculateDiscount` |
| `BlogPost`, `BlogPostTranslation`, `BlogCategory` | Blog | May coexist with Shopify-less content |
| `Faq` / `FaqTranslation` | Older FAQ | Prefer Supabase `Faq` for CMS |
| `Language`, `Currency`, `Testimonial` | Supporting | Seeded historically |

---

## Observers / traits

- No dedicated Observer classes; searchable models use `booted()` static hooks.
- `User` uses Sanctum / Notifiable / HasFactory.
- SoftDeletes on legacy `Product` and `Order`.

---

## Guidance

When adding CMS entities: put Eloquent models under `App\Models\Supabase`, set `$connection = 'supabase'`, and document the table in [Database](Database.md).
