<?php
require "vendor/autoload.php";
$app = require_once "bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$postTranslation = App\Models\BlogPostTranslation::where("slug", "why-mobility-matters-after-50-7-simple-ways-to-keep-moving-with-confidence")->first();
if ($postTranslation) {
    if (strpos($postTranslation->content, "media.dainely.com") !== false) {
        echo "YES! Still contains media.dainely.com!";
    } else {
        echo "NO! Successfully replaced in DB!";
    }
}

