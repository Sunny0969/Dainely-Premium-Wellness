<?php
$ch = curl_init('https://dainely.com/en/a-built-for-the-moments');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)');
$html = curl_exec($ch);
curl_close($ch);
file_put_contents('live_site.html', $html);

