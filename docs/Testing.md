# Testing

## Strategy

- **Feature tests** — HTTP flows (cart, checkout summary, geo redirect, smoke pages).
- **Unit tests** — pure helpers (currency mapping, postal, totals, Square service bits).
- Prefer not hitting live Shopify/Judge in CI; mock or use fixtures where possible.
- Artisan `*:verify` commands complement PHPUnit for manual/integration checks against real env.

---

## Layout

```
tests/
├── Feature/
│   ├── CartAddTest.php
│   ├── CheckoutOrderSummaryTest.php
│   ├── GeoLocaleRedirectTest.php
│   ├── HeaderCartIconTest.php
│   ├── ProductLandingPageTest.php
│   ├── SiteSmokeTest.php
│   └── ExampleTest.php
├── Unit/
│   ├── CheckoutCartTest.php
│   ├── CheckoutTotalsTest.php
│   ├── GeoCurrencyMappingTest.php
│   ├── PostalCodeTest.php
│   ├── ProductRequiresSizeTest.php
│   ├── ShopifyOrderPresentationTest.php
│   ├── SquareServiceTest.php
│   └── ExampleTest.php
├── TestCase.php
└── CreatesApplication.php
```

Config: `phpunit.xml`.

---

## Factories & seeders

- `database/factories/UserFactory.php`
- Seeders: `DatabaseSeeder`, products, FAQs, languages, currencies, discounts, testimonials

Seeders are useful for local demos; production CMS data usually comes from Shopify sync + Admin.

---

## How to run tests

```bash
php artisan test
# or
./vendor/bin/phpunit
```

Filter:

```bash
php artisan test --filter=CartAddTest
```

---

## Coverage

No enforced coverage gate in-repo by default. Add coverage reports in CI if the team wants them:

```bash
php artisan test --coverage
```

(Requires Xdebug/PCOV.)

---

## Manual verification commands

Use against a configured `.env`:

```bash
php artisan supabase:diagnose
php artisan search:verify
php artisan analytics:verify
php artisan webhooks:verify
php artisan admin:verify
php artisan bundles:verify
php artisan related:verify
php artisan landing:verify
php artisan shopify:health
```

---

## Writing new tests

1. Put HTTP assertions in `tests/Feature`.
2. Avoid depending on live secrets; use `Http::fake()` for outbound APIs when practical.
3. For Supabase-backed features, either skip when connection unavailable or use a test database.
