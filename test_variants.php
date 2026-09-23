<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$s = app(\App\Services\ShopifyService::class);
$p = $s->fetchProducts(100)['products'] ?? [];
foreach($p as $prod) {
    echo $prod['title'] . " (" . $prod['handle'] . ") - Variants: " . count($prod['variants']) . "\n";
}
