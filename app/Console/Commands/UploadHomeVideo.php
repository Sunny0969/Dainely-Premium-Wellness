<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use App\Models\Supabase\Setting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class UploadHomeVideo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dainely:upload-home-video';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Uploads the local home video to S3/IDrive E2 and saves URL in Supabase settings table';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Ensuring settings table exists in Supabase...");
        if (!Schema::connection('supabase')->hasTable('settings')) {
            Schema::connection('supabase')->create('settings', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique();
                $table->text('value')->nullable();
                $table->timestamps();
            });
            $this->info("Settings table created.");
        }

        $localPath = public_path('videos/day-in-motion.mp4');
        if (!file_exists($localPath)) {
            $this->error("Video file not found at: {$localPath}");
            $this->info("If you have it uploaded manually to E2, you can just insert the URL directly into the settings table.");
            return;
        }

        $this->info("Uploading day-in-motion.mp4 to S3...");
        try {
            $fileContents = file_get_contents($localPath);
            // using original name or a new name
            $filename = 'videos/day-in-motion-' . time() . '.mp4';
            
            Storage::disk('s3')->put($filename, $fileContents, 'public');
            
            $url = Storage::disk('s3')->url($filename);
            $this->info("Successfully uploaded to: {$url}");
            
            Setting::updateOrCreate(
                ['key' => 'home_video_url'],
                ['value' => $url]
            );
            
            $this->info("Supabase settings updated with the new video URL!");
            $this->info("Please clear your caches (php artisan optimize:clear) to apply changes.");
            
        } catch (\Exception $e) {
            $this->error("Upload failed: " . $e->getMessage());
        }
    }
}