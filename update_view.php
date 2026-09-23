<?php
$file = "d:\dainly Project\Dainely-Premium-Wellness\resources\views\products\show.blade.php";
$content = file_get_contents($file);

$search = <<<EOT
  \$variants  = \$product['variants'] ?? [];
  \$firstVar  = \$variants[0] ?? [];
  \$price     = \$firstVar['price'] ?? null;
  \$compareAt = \$firstVar['compare_at_price'] ?? null;
EOT;

$replace = <<<EOT
  \$variants  = \$product['variants'] ?? [];
  \$firstVar  = \$variants[0] ?? [];
  
  // Find the cheapest variant for base display (so bundles like 'Get 3 for 2' don't skew the hero price)
  \$cheapestVar = collect(\$variants)->sortBy('price')->first() ?? \$firstVar;
  
  \$price     = \$product['price'] ?? \$cheapestVar['price'] ?? null;
  \$compareAt = \$cheapestVar['compare_at_price'] ?? null;
EOT;

$content = str_replace($search, $replace, $content);
file_put_contents($file, $content);
echo "Replaced successfully\n";