<?php

/**
 * Pre/post deploy verification — run: php scripts/verify_deploy_readiness.php
 */
declare(strict_types=1);

$root = dirname(__DIR__);
$errors = [];
$pass = [];

function check(bool $ok, string $label, array &$pass, array &$errors): void
{
    if ($ok) {
        $pass[] = $label;
    } else {
        $errors[] = $label;
    }
}

// 1. Build manifest
$manifestPath = $root . '/public/build/manifest.json';
if (! is_file($manifestPath)) {
    $errors[] = 'Run npm run build — manifest.json missing';
} else {
    $manifest = json_decode((string) file_get_contents($manifestPath), true);
    $jsFile = $manifest['resources/js/app.js']['file'] ?? '';
    $jsPath = $root . '/public/build/' . $jsFile;
    check(is_file($jsPath), "Build JS exists: {$jsFile}", $pass, $errors);

    $jsContent = is_file($jsPath) ? (string) file_get_contents($jsPath) : '';
    check(str_contains($jsContent, 'remove_key'), 'Bundle contains cart remove (remove_key)', $pass, $errors);
    check(str_contains($jsContent, 'validateOption'), 'Bundle contains size validation (validateOption)', $pass, $errors);
}

// 2. Blade sources
$home = (string) file_get_contents($root . '/resources/views/pages/home.blade.php');
check(! str_contains($home, '@click="addToCart($event)"'), 'home.blade.php has no hero addToCart', $pass, $errors);
check(str_contains($home, 'id="hero-cta-primary"') && str_contains($home, 'href="{{ $beltUrl }}"'), 'home hero links to product page', $pass, $errors);

$checkout = (string) file_get_contents($root . '/resources/views/checkout/index.blade.php');
check(str_contains($checkout, 'removeItem(item.key)'), 'checkout has Remove button', $pass, $errors);
check(str_contains($checkout, 'cart.update'), 'checkout config has cartUpdate URL', $pass, $errors);
check(str_contains($checkout, 'data-cfasync="false"'), 'checkout has Cloudflare script fix', $pass, $errors);

// 3. Routes
$web = (string) file_get_contents($root . '/routes/web.php');
check(str_contains($web, "cart.update"), 'routes/web.php defines cart.update', $pass, $errors);

// 4. PHP services
require $root . '/vendor/autoload.php';
$app = require $root . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Support\ProductRequiresSize;

check(ProductRequiresSize::check('1', 'Ceinture Dainely'), 'FR belt title requires size', $pass, $errors);
check(ProductRequiresSize::check('1', 'Dainely Gürtel'), 'DE belt title requires size', $pass, $errors);
check(! ProductRequiresSize::missingSelection('1', 'Ceinture Dainely', 'L/XL', 'L/XL'), 'FR belt with size allowed', $pass, $errors);

// 5. FTP upload list
$ftp = (string) file_get_contents($root . '/scripts/ftp_upload.py');
foreach (['routes/web.php', 'home.blade.php', 'shopify-products-slider.blade.php', 'checkout/index.blade.php'] as $needle) {
    check(str_contains($ftp, $needle), "ftp_upload.py includes {$needle}", $pass, $errors);
}

echo "=== DEPLOY READINESS ===\n\n";
foreach ($pass as $p) {
    echo "  PASS  {$p}\n";
}
if ($errors !== []) {
    echo "\n";
    foreach ($errors as $e) {
        echo "  FAIL  {$e}\n";
    }
    echo "\nFix failures before deploying.\n";
    exit(1);
}

echo "\nAll " . count($pass) . " checks passed. Safe to deploy.\n";
echo "After FTP upload run on server: php artisan view:clear && php artisan config:clear\n";
echo "Latest JS: " . ($jsFile ?? 'n/a') . "\n";
exit(0);
