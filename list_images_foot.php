<?php
require 'c:/Users/IT_STORE/Desktop/dainely1/Dainely-Premium-Wellness/vendor/autoload.php';
$app = require_once 'c:/Users/IT_STORE/Desktop/dainely1/Dainely-Premium-Wellness/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\ShopifyService;
$service = app(ShopifyService::class);
$result = $service->fetchProductByHandle('dainely™-foot-massager');
if ($result['success'] && !empty($result['product'])) {
    $product = $result['product'];
    $images = $product['images'] ?? [];
    echo "Total images: " . count($images) . PHP_EOL;
    foreach ($images as $index => $img) {
        echo "Image {$index}: " . ($img['src'] ?? 'NO SRC') . PHP_EOL;
    }
} else {
    echo "Error: " . ($result['error'] ?? 'Unknown error') . PHP_EOL;
}
