<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$block = \App\Models\Supabase\PageBlock::where('title', 'How it works')->first();
if ($block) {
    echo "Type: " . $block->block_type . "\n";
    echo $block->content;
}