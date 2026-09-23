<?php
$views = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('resources/views'));
foreach ($views as $view) {
    if ($view->isFile() && $view->getExtension() === 'php') {
        $content = file_get_contents($view->getPathname());
        $newContent = preg_replace_callback('/asset\(''images\/'' \. (\$[a-zA-Z0-9_\->\[\]'']+) ?\)/', function($matches) {
            $var = $matches[1];
            return "str_starts_with($var, 'http') ? $var : asset('images/' . $var)";
        }, $content);
        if ($newContent !== $content) {
            file_put_contents($view->getPathname(), $newContent);
            echo "Updated " . $view->getPathname() . "\n";
        }
    }
}
