<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

$users = User::all();
echo "Total users in dainely_admin_users: " . $users->count() . "\n";
foreach ($users as $u) {
    echo "ID: " . $u->id . ", Email: " . $u->email . ", Password Hash: " . $u->password . "\n";
}
