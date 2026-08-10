# IT / Hosting — enable PostgreSQL for PHP 8.3

## Problem
Dainely Admin CMS (Supabase) shows:

> PHP extension pdo_pgsql is not installed/enabled on this server.
> Ask hosting to enable pgsql for PHP 8.3.

Internal Links / Knowledge Graph then shows “Supabase database connection failed”.

This is **not** an application code bug. The live PHP runtime is missing the PostgreSQL driver.

## Required action (hosting panel / server)
For the **same PHP version the website uses** (PHP **8.3**):

1. Enable extensions:
   - `pgsql`
   - `pdo_pgsql`
2. Restart PHP-FPM / Apache / LiteSpeed as applicable.
3. Confirm with either:
   - `php -m | findstr pgsql` on the server, or
   - Upload `public/ext-check.php` and open:
     `https://dev.dainelylab.com/ext-check.php`
     Expect: `"pdo_pgsql": true`
   - **Delete `ext-check.php` after checking.**

4. On the app server, clear Laravel caches:
   ```bash
   php artisan config:clear
   php artisan cache:clear
   ```

5. Reload Admin → Dashboard. The red `pdo_pgsql` banner should be gone, and metrics / Internal Links should load.

## Outbound network
If the extension is enabled but Admin still fails, allow outbound TCP from the web host to Supabase pooler:
- Host: `*.pooler.supabase.com`
- Ports: `5432` (session) / `6543` (transaction)

## Not fixed by
- Redeploying Laravel code alone
- Changing Shopify keys
- Turning `FEATURES_SUPABASE` off (that only hides CMS; it does not replace Postgres)
