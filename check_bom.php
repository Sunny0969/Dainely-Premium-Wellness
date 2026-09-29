<?php
$hasBom = false;
$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(getcwd()));
foreach ($it as $file) {
    if ($file->getExtension() === "php") {
        $c = file_get_contents($file->getPathname());
        if (str_starts_with($c, "\xEF\xBB\xBF")) {
            echo "BOM found in: " . $file->getPathname() . "\n";
            $hasBom = true;
        } elseif (preg_match("/^[\r\n\s]+<\?php/", substr($c, 0, 10))) {
            echo "Whitespace found in: " . $file->getPathname() . "\n";
            $hasBom = true;
        }
    }
}
if (!$hasBom) echo "No BOMs or leading whitespace found in PHP files.\n";

