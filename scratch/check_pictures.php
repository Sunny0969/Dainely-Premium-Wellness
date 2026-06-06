<?php
$apiToken = 'EbnzT_8VOZelL8lRGcc6pD9fyNw';
$shopDomain = 'ididit555.myshopify.com';

// Let's search reviews that have pictures. Is there a filter or can we paginate?
// We will scan 5 pages (500 reviews) to see if we find any pictures.
$foundPics = 0;
for ($page = 1; $page <= 10; $page++) {
    $url = "https://judge.me/api/v1/reviews?" . http_build_query([
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
    $reviews = $data['reviews'] ?? [];

    foreach ($reviews as $r) {
        if (!empty($r['pictures'])) {
            $foundPics++;
            echo "Review ID: {$r['id']} | Author: {$r['reviewer']['name']} | Pictures Count: " . count($r['pictures']) . "\n";
            echo "Pictures JSON: " . json_encode($r['pictures'], JSON_PRETTY_PRINT) . "\n\n";
            if ($foundPics >= 3) break 2;
        }
    }
}

echo "Scan complete. Found $foundPics reviews with pictures.\n";
