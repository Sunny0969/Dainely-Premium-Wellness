<?php
$file = 'app/Http/Controllers/Admin/AdminBlogController.php';
$content = file_get_contents($file);

$replacement = <<<PHP
\$submittedTranslations = \$request->input('translations', []);
        foreach (\$submittedTranslations as &\$t) {
            if (isset(\$t['content'])) {
                \$t['content'] = \App\Services\HtmlImageProcessor::processBase64Images(\$t['content']);
            }
        }
        unset(\$t);
PHP;

$content = str_replace("\$submittedTranslations = \$request->input('translations', []);", $replacement, $content);
file_put_contents($file, $content);
echo "Done";