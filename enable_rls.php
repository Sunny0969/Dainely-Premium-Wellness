<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$tables = \Illuminate\Support\Facades\DB::connection('pgsql')->select("SELECT tablename FROM pg_catalog.pg_tables WHERE schemaname = 'public'");
foreach ($tables as $table) {
    $tableName = $table->tablename;
    try {
        \Illuminate\Support\Facades\DB::connection('pgsql')->statement("ALTER TABLE \"$tableName\" ENABLE ROW LEVEL SECURITY;");
        echo "Enabled RLS on $tableName\n";
    } catch (\Exception $e) {
        echo "Error on $tableName: " . $e->getMessage() . "\n";
    }
}
