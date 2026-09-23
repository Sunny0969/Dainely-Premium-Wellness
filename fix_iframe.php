<?php
$path = 'resources/views/education/show.blade.php';
$content = file_get_contents($path);
$content = str_replace('{!! $block[''content''] ?? '' !!}', '{!! str_replace(''<iframe '', ''<iframe referrerpolicy="strict-origin-when-cross-origin" '', $block[''content''] ?? '''') !!}', $content);
file_put_contents($path, $content);
echo "Replaced in education/show.blade.php\n";
