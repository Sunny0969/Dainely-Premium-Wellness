<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';
$app = require dirname(__DIR__) . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$cases = [
    'NL' => 'EUR',
    'ES' => 'EUR',
    'GR' => 'EUR',
    'BE' => 'EUR',
    'CH' => 'EUR',
    'GB' => 'GBP',
    'US' => 'USD',
    'CA' => 'CAD',
    'AU' => 'AUD',
    'SE' => 'SEK',
    'NO' => 'NOK',
    'DK' => 'DKK',
    'PL' => 'PLN',
    'NZ' => 'NZD',
    'ZA' => 'ZAR',
];

$localePath = [
    'BE' => '/fr',
    'CH' => '/de',
    'FR' => '/fr',
    'DE' => '/de',
    'AT' => '/de',
];

$failed = 0;
foreach ($cases as $country => $expected) {
    $path = $localePath[$country] ?? '/en';
    $request = Illuminate\Http\Request::create($path, 'GET');
    $request->headers->set('CF-IPCountry', $country);
    $response = $kernel->handle($request);
    $body = (string) $response->getContent();
    preg_match('/code:\s*"([A-Z]+)"/', $body, $matches);
    $got = $matches[1] ?? '?';
    $ok = $got === $expected;
    echo ($ok ? 'PASS' : 'FAIL') . " {$country} => {$got} (expected {$expected})\n";
    if (! $ok) {
        $failed++;
    }
    $kernel->terminate($request, $response);
}

exit($failed > 0 ? 1 : 0);
