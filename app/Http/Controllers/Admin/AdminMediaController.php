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
            
            // Upload directly to S3 (E2)
            $path = $file->storeAs('optimized/editor', $filename, 's3');
            
            if (!$path) {
                return response()->json(['success' => 0, 'message' => 'Failed to store image on the remote E2 server.'], 500);
            }
            
            $url = Storage::disk('s3')->url($path);

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
