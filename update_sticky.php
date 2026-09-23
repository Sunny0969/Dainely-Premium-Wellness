<?php
$file = "d:/dainly Project/Dainely-Premium-Wellness/resources/views/partials/product-landing-premium.blade.php";
$content = file_get_contents($file);

$search = <<<EOT
{{-- Mobile sticky Order Now — fixed at bottom while scrolling --}}
<div
  id="sticky-order-bar"
  class="lg:hidden fixed bottom-0 left-0 right-0 z-40 bg-white/95 backdrop-blur-sm border-t border-slate-200 shadow-[0_-4px_24px_rgba(0,0,0,0.12)]"
  style="padding-bottom: max(0.75rem, env(safe-area-inset-bottom));"
  aria-label="Quick order bar"
>
  <div class="container-site pt-3">
    <div class="flex items-center gap-3">
      <div class="min-w-0 flex-1">
        <p class="font-bold text-navy-900 text-sm truncate">{{ \$t('product_name') }}</p>
        <p class="text-navy-700 font-bold text-base">{{ \$fmt(\$displayPrice) }}</p>
      </div>
EOT;

$replace = <<<EOT
{{-- Sticky Order Now — fixed at bottom while scrolling --}}
<div
  id="sticky-order-bar"
  x-data="{ showSticky: false }"
  @scroll.window="showSticky = window.scrollY > 600"
  x-show="showSticky"
  x-transition:enter="transition ease-out duration-300"
  x-transition:enter-start="transform translate-y-full"
  x-transition:enter-end="transform translate-y-0"
  x-transition:leave="transition ease-in duration-200"
  x-transition:leave-start="transform translate-y-0"
  x-transition:leave-end="transform translate-y-full"
  class="fixed bottom-0 left-0 right-0 z-40 bg-white/95 backdrop-blur-sm border-t border-slate-200 shadow-[0_-4px_24px_rgba(0,0,0,0.12)]"
  style="padding-bottom: max(0.75rem, env(safe-area-inset-bottom)); display: none;"
  aria-label="Quick order bar"
>
  <div class="container-site pt-2 pb-2">
    <div class="flex flex-wrap items-center justify-between gap-3">
      <div class="hidden sm:block min-w-0 flex-1">
        <p class="font-bold text-navy-900 text-sm truncate">{{ \$t('product_name') }}</p>
        <p class="text-navy-700 font-bold text-base">{{ \$fmt(\$displayPrice) }}</p>
      </div>
      
      @if(\$purchaseOptions['requiresOption'] ?? \$requiresOption)
      <div class="flex-1 min-w-[140px] sm:flex-none">
        <select x-model="selectedOption" class="w-full bg-slate-50 border border-slate-200 text-navy-900 text-sm rounded-lg focus:ring-gold-400 focus:border-gold-400 block p-2.5 font-semibold appearance-none">
          <option value="" disabled>{{ \$purchaseOptions['optionLabel'] ?? \$t('select_option') }}</option>
          @if((\$purchaseOptions['optionType'] ?? 'shopify') === 'shopify')
            @foreach(\$purchaseOptions['options'] ?? [] as \$variant)
              <option value="{{ \$loop->index }}">{{ \$variant['title'] ?? 'Option' }}</option>
            @endforeach
          @else
            @foreach(\$purchaseOptions['options'] ?? [] as \$option)
              @php \$optVal = is_array(\$option) ? (\$option['value'] ?? \$option['label']) : \$option; @endphp
              <option value="{{ \$optVal }}">{{ is_array(\$option) ? (\$option['label'] ?? \$optVal) : \$option }}</option>
            @endforeach
          @endif
        </select>
      </div>
      @endif

      <div class="flex items-center gap-3">
        <div class="sm:hidden min-w-0 flex-1 text-right">
          <p class="text-navy-700 font-bold text-base">{{ \$fmt(\$displayPrice) }}</p>
        </div>
EOT;

// Try replacing with em dash
$content = str_replace($search, $replace, $content);

// Try replacing with normal dash if em dash fails
$search2 = str_replace("—", "-", $search);
$replace2 = str_replace("—", "-", $replace);
$content = str_replace($search2, $replace2, $content);

// Try replacing without the first comment line
$search3 = <<<EOT
<div
  id="sticky-order-bar"
  class="lg:hidden fixed bottom-0 left-0 right-0 z-40 bg-white/95 backdrop-blur-sm border-t border-slate-200 shadow-[0_-4px_24px_rgba(0,0,0,0.12)]"
  style="padding-bottom: max(0.75rem, env(safe-area-inset-bottom));"
  aria-label="Quick order bar"
>
  <div class="container-site pt-3">
    <div class="flex items-center gap-3">
      <div class="min-w-0 flex-1">
        <p class="font-bold text-navy-900 text-sm truncate">{{ \$t('product_name') }}</p>
        <p class="text-navy-700 font-bold text-base">{{ \$fmt(\$displayPrice) }}</p>
      </div>
EOT;
$content = str_replace($search3, $replace, $content);

file_put_contents($file, $content);
echo "Done";