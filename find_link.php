<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$tables = DB::connection('supabase')->select("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public'");
foreach($tables as $t) {
    $table = $t->table_name;
    try {
        $cols = DB::connection('supabase')->getSchemaBuilder()->getColumnListing($table);
        foreach($cols as $col) {
            $count = DB::connection('supabase')->table($table)->where($col, 'like', '%root-cause-chronic-back-pain%')->count();
            if($count > 0) {
                echo "Found in $table.$col\n";
            }
        }
    } catch(\Exception $e) {}
}