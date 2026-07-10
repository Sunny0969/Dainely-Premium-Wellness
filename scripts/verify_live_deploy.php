<?php

/**
 * Post-deploy live checks — run: php scripts/verify_live_deploy.php
 */
declare(strict_types=1);

$base = getenv('LIVE_URL') ?: 'https://dev.dainelylab.com';
$errors = [];
$pass = [];

function fetch(string $url): string
{
    $ctx = stream_context_create(['http' => ['timeout' => 20, 'header' => "User-Agent: DainelyDeployVerify/1.0\r\n"]]);
    $body = @file_get_contents($url, false, $ctx);

    return is_string($body) ? $body : '';
}

function check(bool $ok, string $label, array &$pass, array &$errors): void
{
    if ($ok) {
        $pass[] = $label;
    } else {
        $errors[] = $label;
    }
}

echo "=== LIVE VERIFY: {$base} ===\n\n";

$frHome = fetch("{$base}/fr/");
check(
    str_contains($frHome, 'id="hero-cta-primary"')
    && preg_match('/id="hero-cta-primary"[^>]*>[\s\S]{0,200}href="/', $frHome)
        || (str_contains($frHome, 'id="hero-cta-primary"') && preg_match('/<a[\s\S]{0,120}id="hero-cta-primary"/', $frHome)),
    'FR hero CTA is a link (not addToCart button)',
    $pass,
    $errors,
);
check(
    ! preg_match('/id="hero-cta-primary"[\s\S]{0,80}@click="addToCart/', $frHome),
    'FR hero does not call addToCart',
    $pass,
    $errors,
);

$deHome = fetch("{$base}/de/");
check(
    ! preg_match('/id="hero-cta-primary"[\s\S]{0,80}@click="addToCart/', $deHome),
    'DE hero does not call addToCart',
    $pass,
    $errors,
);

$checkout = fetch("{$base}/en/checkout");
check(str_contains($checkout, 'removeItem(item.key)') || str_contains($checkout, 'remove_item'), 'Checkout HTML has Remove control', $pass, $errors);
check(str_contains($checkout, 'cart/update'), 'Checkout config has cart/update URL', $pass, $errors);
check(str_contains($checkout, 'data-cfasync="false"'), 'Checkout has Cloudflare script guard', $pass, $errors);
check(str_contains($checkout, 'cartItems') && str_contains($checkout, '__CHECKOUT__'), 'Checkout injects __CHECKOUT__ cartItems', $pass, $errors);

if (preg_match('/build\/assets\/(app-[A-Za-z0-9_-]+\.js)/', $checkout, $m)) {
    $liveJs = fetch("{$base}/build/assets/{$m[1]}");
    check(str_contains($liveJs, 'remove_key'), "Live JS bundle ({$m[1]}) includes remove_key", $pass, $errors);
    check(str_contains($liveJs, 'validateOption'), "Live JS bundle includes validateOption", $pass, $errors);
} else {
    $errors[] = 'Could not detect live app.js hash from checkout page';
}

foreach ($pass as $p) {
    echo "  PASS  {$p}\n";
}
if ($errors !== []) {
    echo "\n";
    foreach ($errors as $e) {
        echo "  FAIL  {$e}\n";
    }
    echo "\nIf blades were uploaded but checks fail, run on server:\n";
    echo "  php artisan view:clear && php artisan config:clear\n";
    echo "Then hard-refresh (Ctrl+Shift+R).\n";
    exit(1);
}

echo "\nAll " . count($pass) . " live checks passed.\n";
exit(0);
