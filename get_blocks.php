<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$page = \App\Models\Catalog\EducationPage::where('slug', 'pickleball-injury-prevention')->first();
if($page) {
    $blocks = $page->pageBlocks()->get();
    echo json_encode($blocks);
}