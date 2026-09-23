<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

\Illuminate\Support\Facades\DB::statement('ALTER TABLE education_pages ADD COLUMN IF NOT EXISTS hero_media_id BIGINT');
\Illuminate\Support\Facades\DB::table('education_pages')->where('id', 60)->update(['hero_media_id' => 1]);
echo "Attached successfully!\n";
