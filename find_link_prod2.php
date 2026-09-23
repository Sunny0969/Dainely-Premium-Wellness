<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$product = \App\Models\Supabase\Product::where('handle', 'dainely-comfort-belt')->first();
if($product) {
    // Check its blocks
    foreach($product->pageBlocks()->get() as $block) {
        if(strpos(json_encode($block), 'root-cause-chronic-back-pain') !== false) {
            echo "Found in block " . $block->id . "\n";
            echo $block->content . "\n";
        }
    }
}