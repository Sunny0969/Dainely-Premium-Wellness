<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$shopify = app(\App\Services\ShopifyService::class);
$res = $shopify->fetchProducts();
$mapped = array_map(function($p) {
    return [
        'title' => $p['title'],
        'handle' => $p['handle'],
        'variants' => array_map(function($v) {
            return [
                'title' => $v['title'],
                'price' => $v['price']
            ];
        }, $p['variants'] ?? [])
    ];
}, $res);
echo json_encode($mapped, JSON_PRETTY_PRINT);