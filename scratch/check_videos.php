<?php
$apiToken = 'EbnzT_8VOZelL8lRGcc6pD9fyNw';
$shopDomain = 'ididit555.myshopify.com';

$foundVideos = 0;
for ($page = 1; $page <= 20; $page++) {
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
    if (empty($reviews)) break;

    foreach ($reviews as $r) {
        if (!empty($r['videos']) || !empty($r['has_published_videos']) || (isset($r['has_published_videos']) && $r['has_published_videos'] === true)) {
            $foundVideos++;
            echo "Review ID: {$r['id']} | Author: {$r['reviewer']['name']} | has_published_videos: " . ($r['has_published_videos'] ? 'yes' : 'no') . "\n";
            echo "Keys in review: " . implode(', ', array_keys($r)) . "\n";
            if (isset($r['videos'])) {
                echo "Videos JSON: " . json_encode($r['videos'], JSON_PRETTY_PRINT) . "\n";
            }
            // Let's print the entire review JSON to find video URLs
            echo "Review JSON snippet: " . json_encode($r, JSON_PRETTY_PRINT) . "\n\n";
            if ($foundVideos >= 3) break 2;
        }
    }
}

echo "Scan complete. Found $foundVideos reviews with videos.\n";
