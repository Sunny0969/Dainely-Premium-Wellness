<?php
$dir = new RecursiveDirectoryIterator('resources/views');
$ite = new RecursiveIteratorIterator($dir);
foreach($ite as $file) {
    if ($file->getExtension() == 'php') {
        $path = $file->getPathname();
        $content = file_get_contents($path);
        // We look for: asset('images/' . $var) and change it to Str::startsWith($var, 'http') ? $var : asset('images/' . $var)
        $newContent = preg_replace_callback("/asset\('images\/'\s*\.\s*(\\$[a-zA-Z0-9_\->]+)\)/", function($matches) {
            $var = $matches[1];
            return "\Str::startsWith($var, 'http') ? $var : asset('images/' . $var)";
        }, $content);
        if ($content !== $newContent) {
            file_put_contents($path, $newContent);
            echo "Updated $path\n";
        }
    }
}
