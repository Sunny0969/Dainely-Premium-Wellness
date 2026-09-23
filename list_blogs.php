<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$blogs = \App\Models\BlogPost::all();
foreach($blogs as $blog) {
    echo $blog->id . " - " . $blog->translation('en')->slug . "\n";
}