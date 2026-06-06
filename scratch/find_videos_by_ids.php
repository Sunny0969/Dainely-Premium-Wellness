<?php
$apiToken = 'EbnzT_8VOZelL8lRGcc6pD9fyNw';
$shopDomain = 'ididit555.myshopify.com';

$beltProductIds = [
    1938085530, // dainely-belt
    1938085524, // back-belt
    1938085529, // back-belt-1
    1938085526, // belt
    1938085518, // belt-2
    424230411,  // dainely-premium-belt-relieve-back-pain-sciatica
    1938085551, // db
    1938085522, // dainely-belt-for-lower-back-hip-pelvic-pain-relief...
    1938085533, // dainely™-belt-funnelish
    1938085546  // dainely™-belt-test
];

$foundVideos = 0;
foreach ($beltProductIds as $id) {
    echo "Checking reviews for product ID: $id...\n";
    $url = "https://judge.me/api/v1/reviews?" . http_build_query([
        'api_token' => $apiToken,
        'shop_domain' => $shopDomain,
        'product_id' => $id,
        'per_page' => 100,
    ]);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    curl_close($ch);

    $data = json_decode($response, true);
    $reviews = $data['reviews'] ?? [];

    foreach ($reviews as $r) {
        if (!empty($r['videos'])) {
            $foundVideos++;
            echo "Review ID: {$r['id']} | Author: {$r['reviewer']['name']} | Product ID: $id\n";
            echo "Videos: " . json_encode($r['videos'], JSON_PRETTY_PRINT) . "\n\n";
        }
    }
}

echo "Found $foundVideos reviews with videos across the belt product IDs.\n";
