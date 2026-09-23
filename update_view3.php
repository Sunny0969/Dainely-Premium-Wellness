<?php
$file = "d:/dainly Project/Dainely-Premium-Wellness/resources/views/products/show.blade.php";
$content = file_get_contents($file);

$search = "  \$variants  = \$product['variants'] ?? [];\r\n  \$firstVar  = \$variants[0] ?? [];\r\n  \$price     = \$firstVar['price'] ?? null;\r\n  \$compareAt = \$firstVar['compare_at_price'] ?? null;";

$replace = "  \$variants  = \$product['variants'] ?? [];\n  \$firstVar  = \$variants[0] ?? [];\n  \n  // Find the cheapest variant for base display (so bundles like 'Get 3 for 2' don't skew the hero price)\n  \$cheapestVar = collect(\$variants)->sortBy('price')->first() ?? \$firstVar;\n  \n  \$price     = \$product['price'] ?? \$cheapestVar['price'] ?? null;\n  \$compareAt = \$cheapestVar['compare_at_price'] ?? null;";

$content = str_replace($search, $replace, $content);

$search2 = "  \$variants  = \$product['variants'] ?? [];\n  \$firstVar  = \$variants[0] ?? [];\n  \$price     = \$firstVar['price'] ?? null;\n  \$compareAt = \$firstVar['compare_at_price'] ?? null;";
$content = str_replace($search2, $replace, $content);

file_put_contents($file, $content);
echo "Done";