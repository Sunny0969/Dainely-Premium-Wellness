<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$columns = DB::connection('supabase')->select("SELECT column_name, data_type FROM information_schema.columns WHERE table_name = 'landing_pages'");
foreach($columns as $c) {
    echo $c->column_name . " - " . $c->data_type . "\n";
}