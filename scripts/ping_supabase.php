<?php

require dirname(__DIR__).'/vendor/autoload.php';
$app = require dirname(__DIR__).'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$t = microtime(true);
$ok = App\Support\SupabaseDb::ping();
$ms = round((microtime(true) - $t) * 1000);

echo 'ping='.($ok ? 'OK' : 'FAIL')." in {$ms}ms\n";
echo 'err='.(App\Support\SupabaseDb::lastError() ?? '-')."\n";
echo 'connect_timeout='.config('database.connections.supabase.connect_timeout')."\n";
