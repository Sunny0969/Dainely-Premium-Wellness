<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

$log = [];
$blocks = \App\Models\Supabase\landingPage::all();
echo "Landing Pages: " . $blocks->count() . "\n";
$blocks = \tApp\Models\Supabase\PageBlock::all();
echo "PageBlocks: " . $blocks->count() . "\n";
foreach ($blocks as $block) {
    $val = $block->content;
    if ($html = $val) {
        preg_match_all("/media\.dainely\.com\/([^\\"\'\s]+)/", $html, $matches);
        if (count($matches[1]) > 0) {
            echo "Found URL in block " . $block->id . ": " . implode(", ", $matches[1]) . "\n";
        }
    }
}
