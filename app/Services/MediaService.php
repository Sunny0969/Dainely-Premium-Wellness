<?php

namespace App\Services;

use App\Models\Media;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Str;

class MediaService
{
    public static function upload(UploadedFile $file, string $context = 'general', ?string $altText = null): Media
    {
        $disk = 's3';
        $filename = Str::random(40) . '.' . $file->getClientOriginalExtension();
        
        $originalPath = "originals/{$context}/{$filename}";
        Storage::disk($disk)->put($originalPath, file_get_contents($file->getRealPath()));

        $manager = new ImageManager(new Driver());
        $image = $manager->decodePath($file->getRealPath());
        $width = $image->width();
        $height = $image->height();
        
        $basename = pathinfo($filename, PATHINFO_FILENAME);
        $optimizedPath = "optimized/{$context}/{$basename}.webp";
        
        $webpImage = (string) $image->encodeUsingFileExtension('webp', 80);
        Storage::disk($disk)->put($optimizedPath, $webpImage);

        $targetWidths = [400, 800, 1200, 1600];
        $generatedWidths = [];
        
        foreach ($targetWidths as $targetWidth) {
            if ($width >= $targetWidth) {
                $resized = $manager->decodePath($file->getRealPath())->scale(width: $targetWidth);
                $resizedPath = "optimized/{$context}/{$basename}-{$targetWidth}.webp";
                Storage::disk($disk)->put($resizedPath, (string) $resized->encodeUsingFileExtension('webp', 80));
                $generatedWidths[] = $targetWidth;
            }
        }

        return Media::create([
            'disk' => $disk,
            'storage_path' => $optimizedPath,
            'original_path' => $originalPath,
            'filename' => $filename,
            'mime_type' => 'image/webp',
            'width' => $width,
            'height' => $height,
            'alt_text' => $altText,
            'size_bytes' => strlen($webpImage),
            'responsive_widths' => $generatedWidths
        ]);
    }
}