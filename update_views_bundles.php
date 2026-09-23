
<?php
$views = new RecursiveIteratorIterator(new RecursiveDirectoryIterator("resources/views"));
foreach ($views as $view) {
    if ($view->isFile() && $view->getExtension() === "php") {
        $path = $view->getPathname();
        $content = file_get_contents($path);
        
        $pattern = "/asset\('images\/bundles\/' \. (\\$[a-zA-Z0-9_\->\[\]']+) ?\)/";
        $replacement = "(\\Illuminate\\Support\\Str::startsWith($1, ['http://', 'https://']) ? $1 : asset('images/bundles/' . $1))";
        
        $newContent = preg_replace($pattern, $replacement, $content);
        
        if ($newContent !== null && $newContent !== $content && !empty($newContent)) {
            file_put_contents($path, $newContent);
            echo "Updated $path\n";
        }
    }
}

