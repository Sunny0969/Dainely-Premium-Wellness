<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class UploadAllMediaToS3 extends Command
{
    protected $signature = 'dainely:upload-all-media';
    protected $description = 'Uploads all local images and videos to S3/E2 cloud';

    public function handle()
    {
        $directories = ['images', 'videos'];
        
        foreach ($directories as $dir) {
            $localDirPath = public_path($dir);
            if (!File::exists($localDirPath)) {
                $this->warn("Directory {$dir} does not exist locally. Skipping.");
                continue;
            }

            $files = File::allFiles($localDirPath);
            $this->info("Found " . count($files) . " files in public/{$dir}. Uploading to E2...");
            
            $bar = $this->output->createProgressBar(count($files));
            $bar->start();

            foreach ($files as $file) {
                $relativePath = $dir . '/' . $file->getRelativePathname();
                // Windows slashes to forward slashes
                $relativePath = str_replace('\\', '/', $relativePath); 
                
                try {
                    Storage::disk('s3')->put($relativePath, file_get_contents($file->getRealPath()), 'public');
                } catch (\Exception $e) {
                    $this->error("\nFailed to upload {$relativePath}: " . $e->getMessage());
                }
                
                $bar->advance();
            }
            
            $bar->finish();
            $this->newLine();
            $this->info("Successfully uploaded all files from {$dir} to E2!");
        }
        
        $this->newLine();
        $this->info("=========================================");
        $this->info("ALL UPLOADS COMPLETE!");
        $this->info("To make your website use these E2 images, add this to your live .env file:");
        $this->info("ASSET_URL=" . env('AWS_URL', 'https://your-e2-bucket-url.com'));
        $this->info("=========================================");
    }
}