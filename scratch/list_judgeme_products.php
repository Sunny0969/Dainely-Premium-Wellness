<?php
$apiToken = 'EbnzT_8VOZelL8lRGcc6pD9fyNw';
$shopDomain = 'ididit555.myshopify.com';

$url = "https://judge.me/api/v1/products?" . http_build_query([
    'api_token' => $apiToken,
    'shop_domain' => $shopDomain,
    'per_page' => 10,
]);

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP $httpCode:\n";
$data = json_decode($response, true);
if (isset($data['products'])) {
    echo "Found " . count($data['products']) . " products.\n";
    foreach ($data['products'] as $p) {
        echo "ID: {$p['id']} | Title: {$p['title']} | Handle: {$p['handle']} | External ID: {$p['external_id']}\n";
    }
} else {
    echo "Response: " . substr($response, 0, 500) . "\n";
}
