<?php
require 'c:/Users/IT_STORE/Desktop/dainely1/Dainely-Premium-Wellness/vendor/autoload.php';
$app = require_once 'c:/Users/IT_STORE/Desktop/dainely1/Dainely-Premium-Wellness/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\ReviewService;
$service = new ReviewService();

// Clear the cache first to ensure fresh data
Illuminate\Support\Facades\Cache::forget("judgeme_reviews_dainely-comfort-belt");

$reviewData = $service->getProductReviews('dainely-comfort-belt', 100);
$reviews = $reviewData['reviews'] ?? [];

echo "Total reviews returned: " . count($reviews) . "\n";
$withPics = 0;
foreach ($reviews as $i => $r) {
    if (!empty($r['pictures'])) {
        $withPics++;
        echo "Review #$i | Author: {$r['reviewer_name']} | Pictures: " . json_encode($r['pictures']) . "\n";
    }
}

echo "Found $withPics reviews with pictures in the service output.\n";
