<?php
$apiToken = 'EbnzT_8VOZelL8lRGcc6pD9fyNw';
$shopDomain = 'ididit555.myshopify.com';

$url = "https://judge.me/api/v1/reviews?" . http_build_query([
    'api_token' => $apiToken,
    'shop_domain' => $shopDomain,
    'per_page' => 100,
]);

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);
$reviews = $data['reviews'] ?? [];

$withTitle = 0;
foreach ($reviews as $r) {
    if (!empty($r['title'])) {
        $withTitle++;
        echo "Title: {$r['title']} | Author: {$r['reviewer']['name']}\n";
    }
}
echo "Found $withTitle reviews with titles out of " . count($reviews) . "\n";
