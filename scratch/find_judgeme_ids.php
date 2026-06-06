<?php
$apiToken = 'EbnzT_8VOZelL8lRGcc6pD9fyNw';
$shopDomain = 'ididit555.myshopify.com';

$map = [];
for ($page = 1; $page <= 5; $page++) {
    $url = "https://judge.me/api/v1/products?" . http_build_query([
        'api_token' => $apiToken,
        'shop_domain' => $shopDomain,
        'per_page' => 100,
        'page' => $page,
    ]);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    curl_close($ch);

    $data = json_decode($response, true);
    $products = $data['products'] ?? [];
    if (empty($products)) break;

    foreach ($products as $p) {
        $map[$p['handle']] = $p['id'];
    }
}

echo "Handle to ID Map (total " . count($map) . "):\n";
echo json_encode($map, JSON_PRETTY_PRINT) . "\n";
