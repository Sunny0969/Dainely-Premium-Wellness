<?php
$apiToken = 'EbnzT_8VOZelL8lRGcc6pD9fyNw';
$shopDomain = 'ididit555.myshopify.com';

$allProductIds = [
    1938085535, 1938085537, 1938085538, 1938085524, 1938085529, 1938085548, 
    1938085526, 1938085518, 1938085519, 1938085549, 429083455, 352033720, 
    379053216, 391965730, 1938085530, 1938085547, 1938085522, 1938085533, 
    1938085546, 1938085541, 1938085540, 376610616, 424230411, 389679824, 
    1938085554, 1938085551, 1938085534, 1938085520, 1938085515, 1938085511, 
    435482806, 1938085512, 1938085531, 368723407, 404102286, 377405246, 
    1938085528, 1938085550, 1938085552
];

$foundVideos = 0;
foreach ($allProductIds as $id) {
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
            echo "Review ID: {$r['id']} | Author: {$r['reviewer']['name']} | Product ID: $id | Handle: {$r['product_handle']}\n";
            echo "Videos: " . json_encode($r['videos'], JSON_PRETTY_PRINT) . "\n\n";
        }
    }
}

echo "Scan complete. Found $foundVideos reviews with videos across all product IDs.\n";
