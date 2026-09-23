<?php
$file = "d:/dainly Project/Dainely-Premium-Wellness/config/filesystems.php";
$content = file_get_contents($file);

$search = <<<EOT
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
            'throw' => false,
EOT;

$replace = <<<EOT
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
            'throw' => false,
            'http' => [
                'verify' => env('AWS_VERIFY_SSL', true)
            ],
EOT;

$content = str_replace($search, $replace, $content);
file_put_contents($file, $content);
echo "Replaced in filesystems.php\n";