<?php
$file = 'c:/Users/IT_STORE/Desktop/dainely1/Dainely-Premium-Wellness/resources/views/products/show.blade.php';
$content = file_get_contents($file);
$lines = explode("\n", $content);

foreach ($lines as $i => $line) {
    if (strpos($line, 'isDainelyBelt') !== false) {
        echo ($i + 1) . ": " . trim($line) . "\n";
    }
}
