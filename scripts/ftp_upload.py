#!/usr/bin/env python3
"""FTP deploy for Vite build assets."""

import os
import sys
from ftplib import FTP, error_perm
from pathlib import Path

HOST = os.environ.get("FTP_HOST", "49.12.85.100")
USER = os.environ.get("FTP_USER", "dainelylab-dev")
PASSWORD = os.environ.get("FTP_PASSWORD", "")
ROOT = Path(__file__).resolve().parents[1]
HTDOCS = "htdocs"
_HOME = ""


def goto_htdocs(ftp: FTP) -> None:
    ftp.cwd(_HOME)
    ftp.cwd(HTDOCS)


def ensure_remote_dir(ftp: FTP, remote_dir: str) -> None:
    goto_htdocs(ftp)
    for part in [p for p in remote_dir.split("/") if p]:
        try:
            ftp.cwd(part)
        except error_perm:
            ftp.mkd(part)
            ftp.cwd(part)


def upload_file(ftp: FTP, local: Path, remote_rel: str) -> None:
    remote_dir, filename = remote_rel.rsplit("/", 1)
    ensure_remote_dir(ftp, remote_dir)
    with local.open("rb") as fh:
        ftp.storbinary(f"STOR {filename}", fh)
    print(f"  uploaded {remote_rel}")


def upload_dir(ftp: FTP, local_dir: Path, remote_base: str) -> int:
    count = 0
    for item in sorted(local_dir.rglob("*")):
        if item.is_dir():
            continue
        rel = item.relative_to(local_dir).as_posix()
        remote = f"{remote_base.rstrip('/')}/{rel}"
        upload_file(ftp, item, remote)
        count += 1
    return count


def delete_if_exists(ftp: FTP, remote_rel: str) -> None:
    try:
        goto_htdocs(ftp)
        ftp.delete(remote_rel)
        print(f"  deleted {remote_rel}")
    except error_perm:
        pass


def main() -> int:
    if not PASSWORD:
        print("ERROR: set FTP_PASSWORD", file=sys.stderr)
        return 1

    if not (ROOT / "public" / "build" / "manifest.json").is_file():
        print("ERROR: run npm run build first", file=sys.stderr)
        return 1

    ftp = FTP(HOST, timeout=60)
    ftp.login(USER, PASSWORD)
    global _HOME
    _HOME = ftp.pwd()

    total = 0
    uploads = [
        (ROOT / "public" / "build", "public/build"),
        (ROOT / "public" / "build", "dev.dainelylab.com/build"),
        (ROOT / "app" / "Providers" / "AppServiceProvider.php", "app/Providers/AppServiceProvider.php"),
        (ROOT / "resources" / "views" / "partials" / "header.blade.php", "resources/views/partials/header.blade.php"),
        (ROOT / "resources" / "views" / "partials" / "cart-nav-link.blade.php", "resources/views/partials/cart-nav-link.blade.php"),
        (ROOT / "resources" / "views" / "partials" / "footer.blade.php", "resources/views/partials/footer.blade.php"),
        (ROOT / "resources" / "lang" / "en" / "nav.php", "resources/lang/en/nav.php"),
        (ROOT / "resources" / "lang" / "fr" / "nav.php", "resources/lang/fr/nav.php"),
        (ROOT / "resources" / "lang" / "de" / "nav.php", "resources/lang/de/nav.php"),
        (ROOT / "app" / "Services" / "ShopifyTaxService.php", "app/Services/ShopifyTaxService.php"),
        (ROOT / "app" / "Services" / "SquareService.php", "app/Services/SquareService.php"),
        (ROOT / "app" / "Support" / "CheckoutTotals.php", "app/Support/CheckoutTotals.php"),
        (ROOT / "app" / "Http" / "Controllers" / "Frontend" / "CartController.php", "app/Http/Controllers/Frontend/CartController.php"),
        (ROOT / "app" / "Http" / "Controllers" / "Frontend" / "CheckoutController.php", "app/Http/Controllers/Frontend/CheckoutController.php"),
        (ROOT / "app" / "Http" / "Controllers" / "Frontend" / "ProductController.php", "app/Http/Controllers/Frontend/ProductController.php"),
        (ROOT / "app" / "Http" / "Controllers" / "Webhooks" / "ShopifyWebhookController.php", "app/Http/Controllers/Webhooks/ShopifyWebhookController.php"),
        (ROOT / "app" / "Http" / "Middleware" / "VerifyShopifyWebhook.php", "app/Http/Middleware/VerifyShopifyWebhook.php"),
        (ROOT / "app" / "Http" / "Kernel.php", "app/Http/Kernel.php"),
        (ROOT / "app" / "Jobs" / "SyncProductJob.php", "app/Jobs/SyncProductJob.php"),
        (ROOT / "routes" / "api.php", "routes/api.php"),
        (ROOT / "routes" / "web.php", "routes/web.php"),
        (ROOT / "app" / "Services" / "JsonLdBuilder.php", "app/Services/JsonLdBuilder.php"),
        (ROOT / "app" / "Support" / "SupabaseDb.php", "app/Support/SupabaseDb.php"),
        (ROOT / "config" / "supabase.php", "config/supabase.php"),
        (ROOT / "app" / "Services" / "OrderPersistenceService.php", "app/Services/OrderPersistenceService.php"),
        (ROOT / "app" / "Services" / "ShopifyService.php", "app/Services/ShopifyService.php"),
        (ROOT / "app" / "Support" / "ProductRequiresSize.php", "app/Support/ProductRequiresSize.php"),
        (ROOT / "resources" / "views" / "layouts" / "app.blade.php", "resources/views/layouts/app.blade.php"),
        (ROOT / "resources" / "views" / "partials" / "cart-drawer.blade.php", "resources/views/partials/cart-drawer.blade.php"),
        (ROOT / "resources" / "views" / "partials" / "product-purchase.blade.php", "resources/views/partials/product-purchase.blade.php"),
        (ROOT / "resources" / "lang" / "en" / "products.php", "resources/lang/en/products.php"),
        (ROOT / "resources" / "lang" / "fr" / "products.php", "resources/lang/fr/products.php"),
        (ROOT / "resources" / "lang" / "de" / "products.php", "resources/lang/de/products.php"),
        (ROOT / "config" / "square.php", "config/square.php"),
        (ROOT / "config" / "shopify.php", "config/shopify.php"),
        (ROOT / "config" / "shopify_tax_fallback.php", "config/shopify_tax_fallback.php"),
        (ROOT / "config" / "products.php", "config/products.php"),
        (ROOT / "config" / "geo.php", "config/geo.php"),
        (ROOT / "config" / "postal.php", "config/postal.php"),
        (ROOT / "app" / "Support" / "PostalCode.php", "app/Support/PostalCode.php"),
        (ROOT / "app" / "Services" / "GeoLocaleService.php", "app/Services/GeoLocaleService.php"),
        (ROOT / "app" / "Services" / "CurrencyService.php", "app/Services/CurrencyService.php"),
        (ROOT / "app" / "Http" / "Middleware" / "LocaleMiddleware.php", "app/Http/Middleware/LocaleMiddleware.php"),
        (ROOT / "resources" / "views" / "checkout" / "index.blade.php", "resources/views/checkout/index.blade.php"),
        (ROOT / "resources" / "views" / "checkout" / "confirmation.blade.php", "resources/views/checkout/confirmation.blade.php"),
        (ROOT / "resources" / "lang" / "en" / "checkout.php", "resources/lang/en/checkout.php"),
        (ROOT / "resources" / "lang" / "fr" / "checkout.php", "resources/lang/fr/checkout.php"),
        (ROOT / "resources" / "views" / "pages" / "home.blade.php", "resources/views/pages/home.blade.php"),
        (ROOT / "resources" / "lang" / "en" / "home.php", "resources/lang/en/home.php"),
        (ROOT / "resources" / "lang" / "fr" / "home.php", "resources/lang/fr/home.php"),
        (ROOT / "app" / "Support" / "ProductLandingLang.php", "app/Support/ProductLandingLang.php"),
        (ROOT / "resources" / "views" / "partials" / "product-landing-premium.blade.php", "resources/views/partials/product-landing-premium.blade.php"),
        (ROOT / "resources" / "lang" / "en" / "product_landing.php", "resources/lang/en/product_landing.php"),
        (ROOT / "public" / "images" / "hero-dainely-belt-lifestyle.png", "dev.dainelylab.com/images/hero-dainely-belt-lifestyle.png"),
        (ROOT / "public" / "images" / "hero-dainely-belt-lifestyle.png", "public/images/hero-dainely-belt-lifestyle.png"),
        (ROOT / "public" / "images" / "lifestyle-dainely-in-motion.png", "dev.dainelylab.com/images/lifestyle-dainely-in-motion.png"),
        (ROOT / "public" / "images" / "lifestyle-dainely-in-motion.png", "public/images/lifestyle-dainely-in-motion.png"),
        (ROOT / "resources" / "lang" / "de" / "home.php", "resources/lang/de/home.php"),
        (ROOT / "resources" / "lang" / "de" / "checkout.php", "resources/lang/de/checkout.php"),
        (ROOT / "routes" / "web.php", "routes/web.php"),
        (ROOT / "resources" / "views" / "partials" / "shopify-products-slider.blade.php", "resources/views/partials/shopify-products-slider.blade.php"),
        (ROOT / "resources" / "views" / "products" / "show.blade.php", "resources/views/products/show.blade.php"),
        (ROOT / "resources" / "js" / "app.js", "resources/js/app.js"),
        (ROOT / "resources" / "js" / "checkout.js", "resources/js/checkout.js"),
    ]

    for local, remote in uploads:
        print(f"\n=== {remote} ===")
        if local.is_dir():
            total += upload_dir(ftp, local, remote)
        else:
            upload_file(ftp, local, remote)
            total += 1

    print("\n=== Remove hot markers ===")
    delete_if_exists(ftp, "public/hot")
    delete_if_exists(ftp, "dev.dainelylab.com/hot")

    for cache_file in ("bootstrap/cache/config.php", "bootstrap/cache/services.php"):
        delete_if_exists(ftp, cache_file)

    ftp.quit()
    print(f"\nDone. {total} file(s) uploaded.")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
