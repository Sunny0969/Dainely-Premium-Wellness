<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$shopify = app(\App\Services\ShopifyService::class);
$res = $shopify->fetchProductByHandle('dainely-comfort-belt');
echo json_encode($res['product']['variants'] ?? null, JSON_PRETTY_PRINT);