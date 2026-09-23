<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$sqlFile = 'C:/Users/PC/.gemini/antigravity/brain/e6ccfae0-7415-4e8a-9fe5-346aa7a5dce1/safe_supabase_setup.sql';
$sql = file_get_contents($sqlFile);

try {
    \Illuminate\Support\Facades\DB::unprepared($sql);
    echo "SUCCESS";
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
