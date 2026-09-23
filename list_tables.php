<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$tables = DB::connection('supabase')->select("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public'");
foreach($tables as $t) {
    echo $t->table_name . "\n";
}