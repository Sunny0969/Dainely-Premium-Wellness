<?php
$filePath = 'c:/Users/IT_STORE/Desktop/dainely1/Dainely-Premium-Wellness/resources/views/products/show.blade.php';
$content = file_get_contents($filePath);

// 1. Replace the hardcoded reviews section for Dainely Belt (lines 859-922)
// Let's find: {{-- ── 6. TESTIMONIALS & REVIEWS ─────────────────────────────── --}}
// and the matching </section> after it.
$targetStart = '{{-- ── 6. TESTIMONIALS & REVIEWS ─────────────────────────────── --}}';
$pos = strpos($content, $targetStart);
if ($pos !== false) {
    // Find the next </section> after it
    $endPos = strpos($content, '</section>', $pos);
    if ($endPos !== false) {
        $endPos += strlen('</section>');
        $reviewsBlock = substr($content, $pos, $endPos - $pos);
        $replacement = "{{-- ── 6. TESTIMONIALS & REVIEWS ─────────────────────────────── --}}\n"
            . "@include('partials.reviews', ['reviews' => \$reviews, 'reviewStats' => \$reviewStats])";
        $content = substr_replace($content, $replacement, $pos, $endPos - $pos);
        echo "Replaced Dainely Belt hardcoded reviews section.\n";
    } else {
        echo "Error: Could not find end of reviews section.\n";
    }
} else {
    echo "Error: Could not find start of reviews section.\n";
}

// 2. Add reviews partial before FAQ section for OTHER products (from line 1489 onwards)
// Let's find all occurrences of: <section class="section bg-stone-50" aria-label="FAQ" x-data="faqAccordion()">
// We want to skip the first one (which is Dainely Belt at line 1023)
$faqTag = '<section class="section bg-stone-50" aria-label="FAQ" x-data="faqAccordion()">';
$offset = 0;
$count = 0;
while (($pos = strpos($content, $faqTag, $offset)) !== false) {
    $count++;
    if ($count > 1) { // Skip the first one
        $replacement = "@include('partials.reviews', ['reviews' => \$reviews, 'reviewStats' => \$reviewStats])\n\n" . $faqTag;
        $content = substr_replace($content, $replacement, $pos, strlen($faqTag));
        // Advance offset past the inserted replacement to avoid infinite loop
        $offset = $pos + strlen($replacement);
    } else {
        $offset = $pos + strlen($faqTag);
    }
}
echo "Added reviews partial before FAQ sections of " . ($count - 1) . " products.\n";

// 3. For the generic product template (under @else), insert before:
// <section class="py-8 bg-white border-t border-slate-100">
$genericTag = '<section class="py-8 bg-white border-t border-slate-100">';
$pos = strpos($content, $genericTag);
if ($pos !== false) {
    $replacement = "@include('partials.reviews', ['reviews' => \$reviews, 'reviewStats' => \$reviewStats])\n\n" . $genericTag;
    // We only want the last occurrence or the one near the end. Let's make sure it's the right one.
    // Let's do strrpos (last occurrence) to be safe.
    $lastPos = strrpos($content, $genericTag);
    if ($lastPos !== false) {
        $content = substr_replace($content, $replacement, $lastPos, strlen($genericTag));
        echo "Added reviews partial to generic template.\n";
    }
}

// 4. Update the hero rating rows for all products
$regex = '/<span class="text-navy-800 font-bold text-sm">(\d\.\d)<\/span>\s*(?:<span class="text-slate-500 text-sm">|<a href="#reviews" class="text-slate-500 text-sm hover:text-navy-700 underline underline-offset-2">)([\d,]+) verified reviews(?:<\/span>|<\/a>)/';
$replacedCount = 0;
$newContent = preg_replace_callback($regex, function($matches) use (&$replacedCount) {
    $replacedCount++;
    return '<span class="text-navy-800 font-bold text-sm">{{ $reviewStats[\'average_rating\'] ?? \'' . $matches[1] . '\' }}</span>
          <a href="#reviews" class="text-slate-500 text-sm hover:text-navy-700 underline underline-offset-2">{{ number_format($reviewStats[\'total_reviews\'] ?? 0) }} verified reviews</a>';
}, $content);

echo "Replaced $replacedCount hero rating rows with dynamic code.\n";

// 5. Update the generic product stars to include rating value and reviews link
$genericStarsTag = '@for($i=0;$i<5;$i++)<svg class="w-5 h-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>@endfor
          </div>
          <span class="text-emerald-600 text-sm font-semibold">✓ In Stock</span>';

$genericStarsReplacement = '@for($i=0;$i<5;$i++)<svg class="w-5 h-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>@endfor
          </div>
          <span class="text-navy-800 font-bold text-sm">{{ $reviewStats[\'average_rating\'] ?? \'4.8\' }}</span>
          <a href="#reviews" class="text-slate-500 text-sm hover:text-navy-700 underline underline-offset-2">{{ number_format($reviewStats[\'total_reviews\'] ?? 0) }} verified reviews</a>
          <span class="text-slate-300">|</span>
          <span class="text-emerald-600 text-sm font-semibold">✓ In Stock</span>';

$pos = strpos($newContent, $genericStarsTag);
if ($pos !== false) {
    $newContent = str_replace($genericStarsTag, $genericStarsReplacement, $newContent);
    echo "Updated generic product template stars rating row.\n";
}

// Write the file back
file_put_contents($filePath, $newContent);
echo "Successfully updated show.blade.php!\n";
