with open('resources/views/products/show.blade.php', 'r', encoding='utf-8') as f:
    content = f.read()

php_code = """@php
function getCustomAltText($url, $default) {
    if (!is_string($url)) return $default;
    if (strpos($url, 'Whisk_62d7eaf0776e71dab844b2f07ef3b0a5dr.png') !== false) return 'Dainely back support belt';
    if (strpos($url, '1.jpg') !== false) return 'Dainely lumbar support belt';
    if (strpos($url, '7.jpg') !== false) return 'Belt for pickleball support';
    if (strpos($url, '5.5.jpg') !== false) return 'Dainely belt support features';
    if (strpos($url, '4.jpg') !== false) return 'Adjustable Dainely support belt';
    if (strpos($url, '8.jpg') !== false) return 'Dainely belt for men';
    if (strpos($url, '3.jpg') !== false) return 'back support belt for women';
    return $default;
}

$galleryAlts = [];
foreach ($galleryUrls as $gUrl) {
    $galleryAlts[] = getCustomAltText($gUrl, $title);
}
@endphp
"""

# 1. Insert PHP block and wrapper div
search_grid = '''      <div class="grid lg:grid-cols-2 gap-8 lg:gap-20 items-start">
  
        {{-- Left: Image --}}
      <div x-data="productGallery(@js($galleryUrls))" class="min-w-0 lg:sticky lg:top-24">'''
      
replace_grid = php_code + '''      <div class="grid lg:grid-cols-2 gap-8 lg:gap-20 items-start">
  
        {{-- Left: Image --}}
      <div x-data="{ alts: @js($galleryAlts) }" class="contents">
      <div x-data="productGallery(@js($galleryUrls))" class="min-w-0 lg:sticky lg:top-24">'''

content = content.replace(search_grid, replace_grid, 1)

# 2. Modify Main Image
search_main_img = '''<img
            src="{{ \App\Support\ProductLandingAssets::cdnSized($galleryUrls[0], 800) }}" alt="{{ $title }}" loading="eager" fetchpriority="high" width="800" height="800" class="w-full aspect-square object-contain transition-all duration-500"
            x-bind:src="images.length ? images[active] : @js($galleryUrls[0])"
          >'''
replace_main_img = '''<img
            src="{{ \App\Support\ProductLandingAssets::cdnSized($galleryUrls[0], 800) }}" alt="{{ getCustomAltText($galleryUrls[0] ?? '', $title) }}" loading="eager" fetchpriority="high" width="800" height="800" class="w-full aspect-square object-contain transition-all duration-500"
            x-bind:src="images.length ? images[active] : @js($galleryUrls[0])"
            x-bind:alt="alts[active] || '{{ $title }}'"
          >'''
content = content.replace(search_main_img, replace_main_img, 1)

# 3. Modify Thumbs
search_thumb = '''<img :src="img" :alt="'View ' + (i+1)" loading="lazy" width="100" height="100" class="w-full h-full object-contain">'''
replace_thumb = '''<img :src="img" :alt="alts[i] || 'View ' + (i+1)" loading="lazy" width="100" height="100" class="w-full h-full object-contain">'''
content = content.replace(search_thumb, replace_thumb)

# 4. Close the wrapper div
search_close = '''        {{-- Right: Product Details --}}'''
replace_close = '''      </div>
        {{-- Right: Product Details --}}'''
content = content.replace(search_close, replace_close, 1)

with open('resources/views/products/show.blade.php', 'w', encoding='utf-8') as f:
    f.write(content)

print("Done")
