<?php
$views = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('resources/views'));
foreach ($views as $view) {
    if ($view->isFile() && $view->getExtension() === 'php') {
        $content = file_get_contents($view->getPathname());
        $newContent = preg_replace(''~asset\(''images/'' \. (\$[a-zA-Z0-9_\->\[\]'']+) ?\)~'', ''\Illuminate\Support\Str::startsWith($1, ["http://", "https://"]) ? $1 : asset("images/" . $1)'', $content);
        if ($newContent !== $content) {
            file_put_contents($view->getPathname(), $newContent);
            echo "Updated " . $view->getPathname() . "\n";
        }
    }
}
