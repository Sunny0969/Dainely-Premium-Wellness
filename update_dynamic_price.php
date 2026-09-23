<?php
$file = "d:/dainly Project/Dainely-Premium-Wellness/resources/views/partials/product-landing-premium.blade.php";
$content = file_get_contents($file);

$search1 = <<<EOT
  \$displayPrice = \$price ?? 0;
  \$displayCompare = \$compareAt ?? null;
EOT;

$replace1 = <<<EOT
  \$displayPrice = \$price ?? 0;
  \$displayCompare = \$compareAt ?? null;
  
  \$variantPricesMap = [];
  if ((\$purchaseOptions['optionType'] ?? 'shopify') === 'shopify') {
      foreach(\$purchaseOptions['options'] ?? [] as \$idx => \$var) {
          \$variantPricesMap[\$idx] = [
              'price' => \$fmt(\$var['price'] ?? 0),
              'compare' => !empty(\$var['compare_at_price']) ? \$fmt(\$var['compare_at_price']) : null,
          ];
      }
  }
EOT;

$content = str_replace($search1, $replace1, $content);

$search2 = '<span class="font-display font-bold text-3xl sm:text-4xl text-navy-900">{{ $fmt($displayPrice) }}</span>';
$replace2 = '<span class="font-display font-bold text-3xl sm:text-4xl text-navy-900" x-text="(selectedOption !== null && @js($variantPricesMap)[selectedOption]) ? @js($variantPricesMap)[selectedOption].price : \'{{ $fmt($displayPrice) }}\'">{{ $fmt($displayPrice) }}</span>';
$content = str_replace($search2, $replace2, $content);

$search3 = '<span class="text-slate-400 line-through text-base sm:text-lg ml-2">{{ $fmt($displayCompare) }}</span>';
$replace3 = '<span class="text-slate-400 line-through text-base sm:text-lg ml-2" x-text="(selectedOption !== null && @js($variantPricesMap)[selectedOption] && @js($variantPricesMap)[selectedOption].compare) ? @js($variantPricesMap)[selectedOption].compare : \'{{ $fmt($displayCompare) }}\'">{{ $fmt($displayCompare) }}</span>';
$content = str_replace($search3, $replace3, $content);

$search4 = '<span class="font-display font-bold text-4xl sm:text-5xl text-navy-900">{{ $fmt($displayPrice) }}</span>';
$replace4 = '<span class="font-display font-bold text-4xl sm:text-5xl text-navy-900" x-text="(selectedOption !== null && @js($variantPricesMap)[selectedOption]) ? @js($variantPricesMap)[selectedOption].price : \'{{ $fmt($displayPrice) }}\'">{{ $fmt($displayPrice) }}</span>';
$content = str_replace($search4, $replace4, $content);

$search5 = '<p class="text-navy-700 font-bold text-base">{{ $fmt($displayPrice) }}</p>';
$replace5 = '<p class="text-navy-700 font-bold text-base" x-text="(selectedOption !== null && @js($variantPricesMap)[selectedOption]) ? @js($variantPricesMap)[selectedOption].price : \'{{ $fmt($displayPrice) }}\'">{{ $fmt($displayPrice) }}</p>';
$content = str_replace($search5, $replace5, $content);

file_put_contents($file, $content);
echo "Replaced properly";