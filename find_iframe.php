<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$page = \App\Models\Catalog\EducationPage::find(60);
foreach($page->root_causes as $idx => $cause) {
    if(strpos(json_encode($cause), 'iframe') !== false || strpos(json_encode($cause), 'youtube') !== false || strpos(json_encode($cause), 'video') !== false) {
        echo "Cause $idx:\n";
        echo $cause['description'] . "\n\n";
    }
}