<?php
$file = "d:/dainly Project/Dainely-Premium-Wellness/app/Services/ShopifyService.php";
$content = file_get_contents($file);

$search = <<<EOT
        return array_values(array_map(function (array \$product) use (\$storeUrl) {
            \$variant = \$product['variants'][0] ?? [];
EOT;

$replace = <<<EOT
        return array_values(array_map(function (array \$product) use (\$storeUrl) {
            \$variants = \$product['variants'] ?? [];
            \$variant = collect(\$variants)->sortBy('price')->first() ?? [];
EOT;

$content = str_replace($search, $replace, $content);
file_put_contents($file, $content);
echo "Done mapProductsForDisplay\n";