<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class AdminMediaController extends Controller
{
    public function uploadEditorImage(Request $request)
    {
        try {
            if (!$request->hasFile('image')) {
                return response()->json(['success' => 0, 'message' => 'No image file was received. Please ensure the file is under your server\'s upload size limit (usually 2MB).'], 400);
            }

            $request->validate([
                'image' => 'required|image|max:10240' // 10MB max
            ]);

            $file = $request->file('image');
            if (!$file->isValid()) {
                return response()->json(['success' => 0, 'message' => 'Uploaded file is invalid or corrupted.'], 400);
            }

            $filename = time() . '-' . Str::random(10) . '.' . $file->getClientOriginalExtension();
            
            $folderType = $request->input('folder_type', 'others');
            $folderMap = [
                'blogs' => 'blogs',
                'education' => 'education',
                'landing' => 'Landing Pages',
                'others' => 'others'
            ];
            $baseFolder = $folderMap[$folderType] ?? 'others';

            // 1. Save locally in public/images/{baseFolder}
            $localDir = public_path('images/' . $baseFolder);
            if (!file_exists($localDir)) {
                @mkdir($localDir, 0755, true);
            }
            $file->move($localDir, $filename);

            // If it's blogs, also copy to public/blogs/ and public/images/
            if ($baseFolder === 'blogs') {
                $publicBlogs = public_path('blogs');
                if (!file_exists($publicBlogs)) {
                    @mkdir($publicBlogs, 0755, true);
                }
                @copy($localDir . '/' . $filename, $publicBlogs . '/' . $filename);
                @copy($localDir . '/' . $filename, public_path('images/' . $filename));
            }

            // 2. Also upload to S3
            try {
                Storage::disk('s3')->putFileAs($baseFolder, new \Illuminate\Http\File($localDir . '/' . $filename), $filename);
            } catch (\Throwable $e) {
                Log::warning('S3 editor upload error: ' . $e->getMessage());
            }

            $url = asset('images/' . $baseFolder . '/' . $filename);

            return response()->json([
                'success' => 1,
                'url' => $url
            ]);
        } catch (\Exception $e) {
            Log::error('Editor Upload Error: ' . $e->getMessage());
            return response()->json(['success' => 0, 'message' => $e->getMessage()], 500);
        }
    }
}
