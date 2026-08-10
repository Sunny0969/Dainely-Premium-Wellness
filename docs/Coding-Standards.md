# Coding Standards

## Naming

| Kind | Convention |
|------|------------|
| Classes | PascalCase |
| Methods / vars | camelCase |
| Config keys | snake_case |
| DB tables | snake_case plural |
| Route names | dot.nested (`products.show`) |
| Jobs | Verb + noun + `Job` |
| Artisan | `domain:action` (`shopify:sync-catalog`) |

Supabase product table is intentionally `dainely_products`.

---

## Folder conventions

- Controllers thin; logic in `app/Services`.
- Phase 2 Eloquent models in `app/Models/Supabase` with `$connection = 'supabase'`.
- Webhooks in `Http/Controllers/Webhooks`.
- Shared non-service utilities in `app/Support`.
- Blade blocks in `resources/views/components/blocks`.

---

## Dependency injection

Prefer constructor injection of services. Avoid `new ShopifyService` in random places when the container can resolve it.

---

## Service pattern

Yes — primary pattern. One service per integration or domain capability.

## Repository pattern

**Not used.** Do not add repositories “for cleanliness” without a second data source needing abstraction.

---

## Validation

- Form requests or `$request->validate([...])` in controllers.
- Keep validation messages user-safe; never echo SQL/API secrets.

---

## Error handling

- Catch integration failures at service/job boundaries; log context without PII/secrets.
- Soft-degrade when Supabase unavailable (`SupabaseDb::available()`).
- Webhooks: record failure + retry rather than silent drop.

---

## Logging

Use Laravel `Log` facade. Prefer structured context arrays. Never log access tokens, card data, or raw passwords.

---

## Comments

Explain **why**, not what. Prefer clear method names over essay comments. Match existing file style.

---

## Best practices (project-specific)

1. Do not change Shopify pricing logic to “fix” CMS bugs — overlays are content, not inventory.
2. Keep landing catch-all route last under the locale group.
3. Register new event listeners in `EventServiceProvider` (discovery off).
4. Update `docs/` when adding modules.
5. Use Pint (`vendor/bin/pint`) for PHP style when touching many files.
6. Quote `.env` values that contain `#`, spaces, or special characters.
7. Prefer Session pooler for Supabase from shared hosting.

---

## Deprecated / avoid

- Prefer `App\Models\Supabase\Product` over legacy `App\Models\Product` for Phase 2 catalog work.
- Prefer Supabase `Faq` over legacy FAQ models for CMS.
- Do not invent a second frontend framework (Vue/React) for small widgets — Alpine fits the stack.
