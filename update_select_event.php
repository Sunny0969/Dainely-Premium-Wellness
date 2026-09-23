<?php
$file = "d:/dainly Project/Dainely-Premium-Wellness/resources/views/partials/product-landing-premium.blade.php";
$content = file_get_contents($file);

$search = 'x-model="selectedOption"';
$replace = '@change="selectOption($event.target.value === \'\' ? null : (isNaN($event.target.value) ? $event.target.value : Number($event.target.value)))" :value="selectedOption"';

$content = str_replace($search, $replace, $content);
file_put_contents($file, $content);
echo "Replaced x-model";