<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

$email = 'vijay@dainely.com';
$password = '!Ma}Fc;-DV6P^$c;=pPG';

$user = User::where('email', $email)->first();
if ($user) {
    $user->password = Hash::make($password);
    $user->save();
    echo "User updated successfully!";
} else {
    User::create([
        'name' => 'Vijay Admin',
        'email' => $email,
        'password' => Hash::make($password),
        'email_verified_at' => now(),
    ]);
    echo "User created successfully!";
}
