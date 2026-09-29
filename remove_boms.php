<?php
$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(getcwd()));
foreach ($it as $file) {
    if ($file->getExtension() === "php") {
        $path = $file->getPathname();
        if (str_contains($path, "vendor")) continue;
        $c = file_get_contents($path);
        if (str_starts_with($c, "\xEF\xBB\xBF")) {
            file_put_contents($path, substr($c, 3));
            echo "Removed BOM from " . $path . "\n";
        }
    }
}

