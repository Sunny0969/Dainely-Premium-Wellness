<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$blogs = \App\Models\BlogPost::with('translations')->get();
foreach($blogs as $blog) {
    echo $blog->id . "\n";
    foreach($blog->translations as $t) {
        echo "  " . $t->locale . " - " . $t->slug . "\n";
    }
}