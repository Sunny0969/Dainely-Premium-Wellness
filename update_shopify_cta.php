<?php
$file = "d:/dainly Project/Dainely-Premium-Wellness/app/Services/ShopifyService.php";
$content = file_get_contents($file);

$search = <<<EOT
    public function mapProductForCta(array \$product): object
    {
        \$variant = \$product['variants'][0] ?? [];
EOT;

$replace = <<<EOT
    public function mapProductForCta(array \$product): object
    {
        \$variants = \$product['variants'] ?? [];
        \$variant = collect(\$variants)->sortBy('price')->first() ?? [];
EOT;

$content = str_replace($search, $replace, $content);
file_put_contents($file, $content);
echo "Done mapProductForCta\n";