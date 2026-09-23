<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$shopify = app(\App\Services\ShopifyService::class);
$res = $shopify->fetchProductByHandle('dainely-belt-2-0');
if (empty($res['product'])) {
    $res = $shopify->fetchProductByHandle('dainely-comfort-belt');
}
print_r($res['product']['variants']);