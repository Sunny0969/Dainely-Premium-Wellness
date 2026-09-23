<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$lp = \Illuminate\Support\Facades\DB::table('landing_pages')->where('slug', 'a-built-for-the-moments')->first();
if ($lp) {
    print_r(array_keys((array)$lp));
    echo "\nContent:\n" . $lp->content;
}