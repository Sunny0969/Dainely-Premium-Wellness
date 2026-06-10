<?php
$file = 'c:/Users/IT_STORE/Desktop/dainely1/Dainely-Premium-Wellness/resources/views/products/show.blade.php';
$content = file_get_contents($file);
$lines = explode("\n", $content);

$start = false;
foreach ($lines as $i => $line) {
    $lineNum = $i + 1;
    if ($lineNum >= 580 && $lineNum <= 1500) {
        if (strpos($line, '@if') !== false || strpos($line, '@elseif') !== false || strpos($line, '@else') !== false || strpos($line, '@endif') !== false) {
            echo $lineNum . ": " . trim($line) . "\n";
        }
    }
}
