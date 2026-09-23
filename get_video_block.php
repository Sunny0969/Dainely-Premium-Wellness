<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$block = \App\Models\Supabase\PageBlock::where('title', 'Test Title')->first();
if ($block) {
    echo "Raw Content:\n";
    echo $block->content;
}