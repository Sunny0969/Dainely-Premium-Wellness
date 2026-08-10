# Troubleshooting

## Admin dashboard shows zeros / DB warning

**Likely cause:** PHP missing `pdo_pgsql` / `pgsql`, wrong pooler credentials, or timeout too low.

**Debug:**

```bash
php artisan supabase:diagnose
php -m | findstr pgsql
```

**Fix:** Enable `pgsql` + `pdo_pgsql` for the site’s PHP version; use Session pooler host; set `DB_SUPABASE_TIMEOUT=15`; `config:clear`.

---

## Storefront works but Admin does not

Expected when Shopify APIs work but Supabase PDO does not. Commerce can continue; CMS cannot.

---

## Shopify products empty

- Check `SHOPIFY_*` tokens and `shopify:health`.
- Confirm Storefront vs Admin catalog flags in `config/shopify.php`.
- Clear cache: `php artisan cache:clear`.

---

## Webhooks not processing

1. Confirm URL registered in Shopify / Judge (HTTPS).
2. Verify secrets match `.env`.
3. Check `storage/logs/laravel.log`.
4. Inspect `webhook_logs` in Supabase.
5. Ensure queue worker running if not `sync`.
6. Confirm scheduler for `RetryFailedWebhooksJob`.

```bash
php artisan webhooks:verify
```

---

## Search returns nothing

```bash
php artisan search:verify --reindex
```

Confirm GIN index on `search_index.tsv` and Supabase connectivity.

---

## Reviews count looks too low

Judge.me page samples ≠ shop totals. Ensure `JUDGEME_USE_COUNT_API=true` and run `reviews:warm-cache`.

---

## Cache / config “stuck” after env change

```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
```

On production, rebuild caches after fixing env.

---

## Queue issues

- Jobs never async → still on `sync`.
- Jobs failing → `failed_jobs` table / logs.
- Restart Supervisor workers after code deploy.

---

## Permission / Admin login fails

- Confirm `ADMIN_EMAIL` / `ADMIN_PASSWORD` (quote special characters in `.env`).
- Session driver writable (`storage/framework/sessions`).
- Not mixing HTTP/HTTPS cookie issues behind proxy — check `TrustProxies`.

---

## Migration issues

- Wrong connection / missing `pdo_pgsql`.
- Table already exists — many migrations no-op if table present.
- One-way finalize migrations may not fully reverse.

---

## Deployment issues

- Docroot not `public/`.
- Missing `npm run build` → broken CSS/JS.
- Old config cache with stale secrets.
- Cron missing → reviews/webhooks stale.

---

## How to debug generally

1. Reproduce with `APP_DEBUG=true` only on non-prod.
2. Read `storage/logs/laravel.log`.
3. Run the matching `*:verify` or `*:diagnose` command.
4. Confirm hosting extensions and outbound HTTPS to Shopify/Supabase.
