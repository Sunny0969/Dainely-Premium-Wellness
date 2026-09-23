<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$shopify = app(\App\Services\ShopifyService::class);
$all = $shopify->getProducts();
$belt = collect($all)->firstWhere('handle', 'dainely-comfort-belt');
print_r("From getProducts: " . $belt['variants'][0]['price'] . "\n");

$res = $shopify->fetchProductByHandle('dainely-comfort-belt');
print_r("From fetchProductByHandle: " . $res['product']['variants'][0]['price'] . "\n");