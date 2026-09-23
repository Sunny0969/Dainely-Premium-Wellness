<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$page = \App\Models\Catalog\EducationPage::where('slug', 'pickleball-injury-prevention')->first();
if($page && is_array($page->content_blocks)) {
    foreach($page->content_blocks as $idx => $block) {
        if(strpos($block['content'], '<iframe') !== false || strpos($block['content'], 'youtube') !== false) {
            echo "Block $idx has video:\n";
            echo $block['content'] . "\n\n";
        }
    }
}