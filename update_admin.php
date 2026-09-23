<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

$email = env('ADMIN_EMAIL');
$password = env('ADMIN_PASSWORD');

if ($email && $password) {
    $user = User::updateOrCreate(
        ['email' => $email],
        [
            'name' => 'Admin User',
            'password' => Hash::make($password),
            'email_verified_at' => now()
        ]
    );
    echo "Success! Email: {$user->email}, Hash: {$user->password}";
} else {
    echo "Error: ADMIN_EMAIL or ADMIN_PASSWORD not found in .env";
}
