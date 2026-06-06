<?php
$apiToken = 'EbnzT_8VOZelL8lRGcc6pD9fyNw';
$shopDomain = 'ididit555.myshopify.com';
$productId = '1938085518'; // belt-2

$url = "https://judge.me/api/v1/reviews?" . http_build_query([
    'api_token' => $apiToken,
    'shop_domain' => $shopDomain,
    'product_id' => $productId,
    'per_page' => 5,
]);

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);
$reviews = $data['reviews'] ?? [];

echo "Found " . count($reviews) . " reviews for product_id $productId.\n";
foreach ($reviews as $i => $r) {
    echo "Review #$i | Rating: {$r['rating']} | Title: {$r['title']} | Author: {$r['reviewer']['name']} | Handle: {$r['product_handle']}\n";
}
