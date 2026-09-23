<?php
$file = "d:/dainly Project/Dainely-Premium-Wellness/app/Http/Controllers/Frontend/ProductController.php";
$content = file_get_contents($file);

$search = <<<EOT
            \$dbProduct = new SupabaseProduct([
                'title'  => \$product['title'] ?? '',
                'handle' => \$productHandle,
                'price'  => \$product['price'] ?? (\$product['variants'][0]['price'] ?? null),
EOT;

$replace = <<<EOT
            \$dbProduct = new SupabaseProduct([
                'title'  => \$product['title'] ?? '',
                'handle' => \$productHandle,
                'price'  => \$product['price'] ?? collect(\$product['variants'] ?? [])->min('price'),
EOT;

$content = str_replace($search, $replace, $content);
file_put_contents($file, $content);
echo "Done SupabaseProduct\n";