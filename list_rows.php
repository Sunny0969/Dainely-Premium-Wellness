<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$rows = DB::connection('supabase')->table('pages')->get();
foreach($rows as $r) {
    echo json_encode($r) . "\n";
}