#!/usr/bin/env bash
# Run on the production server after code + assets are deployed.
set -euo pipefail
cd "$(dirname "$0")/.."

echo "==> Composer (if needed)"
# composer install --no-dev --optimize-autoloader

echo "==> Vite production build (run on CI or before upload if Node unavailable on host)"
# npm ci && npm run build

echo "==> Laravel production caches"
php artisan optimize:production --force

echo "==> Done. Confirm OPcache in hosting panel / public/.user.ini"
echo "    Cloudflare: see docs/Cloudflare.md"
