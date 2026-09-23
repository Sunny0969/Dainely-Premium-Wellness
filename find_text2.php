<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$page = \App\Models\Catalog\EducationPage::where('slug', 'pickleball-injury-prevention')->first();
if($page) {
    if(strpos(json_encode($page), 'Heel-to-toe walking') !== false) {
        echo "Found in EducationPage model directly!\n";
        echo $page->id;
    } else {
        echo "Not found in model directly.\n";
    }
}