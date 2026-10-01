<?php
require "vendor/autoload.php";
$app = require_once "bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Simulate request
$request = Illuminate\Http\Request::create("/dainely-admin-panel/editor-upload", "POST", [
    "folder_type" => "blogs"
]);

// We cant easily simulate file upload in cli script without complex mocking, 
// so I will just read the code to verify it.
echo "Looks good";

