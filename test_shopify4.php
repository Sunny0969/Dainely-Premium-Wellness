<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$shopify = app(\App\Services\ShopifyService::class);
$res = $shopify->getProducts();
$belt = collect($res)->firstWhere('handle', 'dainely-comfort-belt');
print_r($belt['variants'][0]['price']);
print_r("\n");
print_r($belt['variants'][0]['compare_at_price']);