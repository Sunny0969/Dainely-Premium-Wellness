<?php
require 'c:/Users/IT_STORE/Desktop/dainely1/Dainely-Premium-Wellness/vendor/autoload.php';
$app = require_once 'c:/Users/IT_STORE/Desktop/dainely1/Dainely-Premium-Wellness/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\ReviewService;
$service = new ReviewService();
$stats = $service->getProductStats('dainely-comfort-belt');

echo "Stats:\n";
echo "Average Rating: {$stats['average_rating']}\n";
echo "Total Reviews: {$stats['total_reviews']}\n";
echo "Breakdown:\n";
var_dump($stats['rating_breakdown']);
