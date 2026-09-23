<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

// 1. Create table
if (!Schema::connection('supabase')->hasTable('settings')) {
    Schema::connection('supabase')->create('settings', function (Blueprint $table) {
        $table->id();
        $table->string('key')->unique();
        $table->text('value')->nullable();
        $table->timestamps();
    });
    echo "Created settings table.\n";
}

// 2. Upload video to S3
$localPath = public_path('videos/day-in-motion.mp4');
if (file_exists($localPath)) {
    echo "Uploading video...\n";
    $content = file_get_contents($localPath);
    Storage::disk('s3')->put('videos/day-in-motion.mp4', $content, 'public');
    $url = Storage::disk('s3')->url('videos/day-in-motion.mp4');
    echo "Uploaded to: $url\n";
    
    // 3. Save to Supabase
    DB::connection('supabase')->table('settings')->updateOrInsert(
        ['key' => 'hero_video_url'],
        ['value' => $url, 'updated_at' => now()]
    );
    echo "Saved to Supabase.\n";
} else {
    echo "Video file not found locally!\n";
}