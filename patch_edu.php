<?php
$file = 'app/Http/Controllers/Admin/AdminEducationController.php';
$content = file_get_contents($file);

$replacement = <<<PHP
    private function handleUploads(Request \$request, array \$validated): array
    {
        // PROCESS ALL BASE64 IMAGES FROM QUILL EDITOR FIRST
        if (!empty(\$validated['hero_description'])) {
            \$validated['hero_description'] = \App\Services\HtmlImageProcessor::processBase64Images(\$validated['hero_description']);
        }
        if (!empty(\$validated['treatments_description'])) {
            \$validated['treatments_description'] = \App\Services\HtmlImageProcessor::processBase64Images(\$validated['treatments_description']);
        }
        if (isset(\$validated['figures']) && is_array(\$validated['figures'])) {
            foreach (\$validated['figures'] as &\$item) {
                if (!empty(\$item['label'])) \$item['label'] = \App\Services\HtmlImageProcessor::processBase64Images(\$item['label']);
            }
        }
        if (isset(\$validated['root_causes']) && is_array(\$validated['root_causes'])) {
            foreach (\$validated['root_causes'] as &\$item) {
                if (!empty(\$item['description'])) \$item['description'] = \App\Services\HtmlImageProcessor::processBase64Images(\$item['description']);
            }
        }
        if (isset(\$validated['content_blocks']) && is_array(\$validated['content_blocks'])) {
            foreach (\$validated['content_blocks'] as &\$item) {
                if (!empty(\$item['content'])) \$item['content'] = \App\Services\HtmlImageProcessor::processBase64Images(\$item['content']);
            }
        }
        
        if (\$request->hasFile('hero_image_file')) {
PHP;

$content = str_replace(
    "    private function handleUploads(Request \$request, array \$validated): array\n    {\n        if (\$request->hasFile('hero_image_file')) {",
    $replacement,
    $content
);

file_put_contents($file, $content);
echo "Done";