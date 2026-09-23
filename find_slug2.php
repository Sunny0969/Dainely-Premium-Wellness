<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$tables = \Illuminate\Support\Facades\DB::select("SELECT table_name FROM information_schema.tables WHERE table_schema='public'");
foreach ($tables as $t) {
    $table = $t->table_name;
    try {
        $hasSlug = \Illuminate\Support\Facades\Schema::hasColumn($table, 'slug');
        if ($hasSlug) {
            $count = \Illuminate\Support\Facades\DB::table($table)->where('slug', 'a-built-for-the-moments')->count();
            if ($count > 0) {
                echo "Found in $table\n";
            }
        }
    } catch (\Exception $e) {}
}