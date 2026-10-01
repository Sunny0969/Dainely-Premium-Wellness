<?php
$file = "routes/web.php";
$content = file_get_contents($file);
$route = "
Route::get(\"/migrate-old-images\", function () {
    \$log = [];
    try {
        // 1. Migrate Blogs
        \$blogs = App\Models\BlogPostTranslation::all();
        foreach (\$blogs as \$blog) {
            \$changed = false;
            \$html = \$blog->content;
            
            // Find all optimized/editor images in blog content
            if (preg_match_all(\"/\/optimized\/editor\/([a-zA-Z0-9_.-]+)/\", \$html, \$matches)) {
                foreach (\$matches[1] as \$filename) {
                    \$oldPath = \"optimized/editor/\" . \$filename;
                    \$newPath = \"dainely/media/blogs/\" . \$filename;
                    if (Storage::disk(\"s3\")->exists(\$oldPath)) {
                        Storage::disk(\"s3\")->copy(\$oldPath, \$newPath);
                        \$html = str_replace(\"optimized/editor/\" . \$filename, \"dainely/media/blogs/\" . \$filename, \$html);
                        \$changed = true;
                        \$log[] = \"Copied \$oldPath to \$newPath\";
                    }
                }
            }
            // Find all images/ in blog content
            if (preg_match_all(\"/\/images\/([a-zA-Z0-9_.-]+)/\", \$html, \$matches)) {
                foreach (\$matches[1] as \$filename) {
                    // Avoid matching /images/ (local) vs S3
                    // The S3 ones have dainely-media/images/
                    if (strpos(\$html, \"dainely-media/images/\" . \$filename) !== false) {
                        \$oldPath = \"images/\" . \$filename;
                        \$newPath = \"dainely/media/blogs/\" . \$filename;
                        if (Storage::disk(\"s3\")->exists(\$oldPath)) {
                            Storage::disk(\"s3\")->copy(\$oldPath, \$newPath);
                            \$html = str_replace(\"images/\" . \$filename, \"dainely/media/blogs/\" . \$filename, \$html);
                            \$changed = true;
                            \$log[] = \"Copied \$oldPath to \$newPath\";
                        }
                    }
                }
            }
            
            if (\$changed) {
                \$blog->content = \$html;
                \$blog->save();
            }
        }
        
        return \"Migration complete! Log: <br>\" . implode(\"<br>\", \$log);
    } catch (\Exception \$e) {
        return \"Error: \" . \$e->getMessage();
    }
});
";
if (strpos($content, "/migrate-old-images") === false) {
    file_put_contents($file, $content . $route);
    echo "Route added!";
} else {
    echo "Route already exists.";
}

