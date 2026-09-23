<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

function getTableDef($table) {
    $cols = \Illuminate\Support\Facades\DB::select("SELECT column_name, data_type FROM information_schema.columns WHERE table_schema = 'public' AND table_name = ?", [$table]);
    echo "TABLE $table:\n";
    foreach($cols as $c) { echo " - {$c->column_name}: {$c->data_type}\n"; }
}

getTableDef('users');
getTableDef('products');
