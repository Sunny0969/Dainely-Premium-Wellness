<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$page = \App\Models\Catalog\EducationPage::find(60);
$array = $page->toArray();
foreach($array as $key => $val) {
    if(is_string($val) && strpos($val, 'Heel-to-toe') !== false) {
        echo "Key (string): $key\n";
    }
    if(is_array($val) && strpos(json_encode($val), 'Heel-to-toe') !== false) {
        echo "Key (array): $key\n";
    }
}