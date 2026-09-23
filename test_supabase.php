<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$products = \App\Models\Supabase\Product::all();
foreach($products as $p) {
    echo $p->handle . " -> price: " . $p->price . "\n";
}