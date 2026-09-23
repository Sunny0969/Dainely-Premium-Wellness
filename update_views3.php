
<?php
$views = new RecursiveIteratorIterator(new RecursiveDirectoryIterator("resources/views"));
foreach ($views as $view) {
    if ($view->isFile() && $view->getExtension() === "php") {
        $path = $view->getPathname();
        $content = file_get_contents($path);
        
        // Match `asset('images/' . $variable)` and replace it.
        // We will just use preg_replace directly without callback.
        // Example: asset('images/' . $post->featured_image)
        $pattern = "/asset\('images\/' \. (\\$[a-zA-Z0-9_\->]+)\)/";
        $replacement = "(\\Illuminate\\Support\\Str::startsWith($1, ['http://', 'https://']) ? $1 : asset('images/' . $1))";
        
        $newContent = preg_replace($pattern, $replacement, $content);
        
        if ($newContent !== null && $newContent !== $content && !empty($newContent)) {
            file_put_contents($path, $newContent);
            echo "Updated $path\n";
        }
    }
}

