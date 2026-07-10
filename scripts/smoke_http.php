<?php
/**
 * HTTP smoke test — verifies key pages return 200 without ViewException.
 * Run: php scripts/smoke_http.php
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$productHandles = [
    'dainely-comfort-belt',
    'dainely-ball-massager',
    'neck-pain',
    'back-pain-relief-patches-20-pcs',
    'dainely-unisex-heated-jacket',
    'dainely-foot-massager',
    'brace',
    'dainely-massager',
    'shoulder-brace',
    'stretcher',
    'dainely-orthopedic-back-stretcher',
    'relaxaleg-system',
    'dainely-tourmaline-belt',
    'daily-relief-system',
    'cushion',
    'functional-mushroom-coffee',
];

$paths = [
    'en/',
    'en/products',
    'en/about',
    'en/faq',
    'en/checkout',
    'fr/products/back-pain-relief-patches-20-pcs',
];

foreach ($productHandles as $handle) {
    $paths[] = "en/products/{$handle}";
}

$failures = 0;

foreach ($paths as $path) {
    $request = Illuminate\Http\Request::create('/' . $path, 'GET');
    $response = $kernel->handle($request);
    $status = $response->getStatusCode();
    $body = $response->getContent();
    $bad = $status !== 200
        || str_contains($body, 'ViewException')
        || str_contains($body, 'foreach() argument must be of type array');

    if ($bad) {
        $failures++;
        echo "FAIL [{$status}] /{$path}\n";
        if (preg_match('/<title>(.*?)<\/title>/s', $body, $m)) {
            echo '  title: ' . trim(strip_tags($m[1])) . "\n";
        }
    } else {
        echo "OK   /{$path}\n";
    }

    $kernel->terminate($request, $response);
}

echo $failures === 0
    ? "\nAll smoke checks passed.\n"
    : "\n{$failures} check(s) failed.\n";

exit($failures === 0 ? 0 : 1);
