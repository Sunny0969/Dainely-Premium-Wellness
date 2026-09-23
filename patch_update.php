<?php

$files = [
    'resources/views/admin/blogs/create.blade.php',
    'resources/views/admin/blogs/edit.blade.php',
    'resources/views/admin/education/edit.blade.php'
];

$jsHandler = <<<JS
function selectLocalImage(quill) {
    const input = document.createElement('input');
    input.setAttribute('type', 'file');
    input.setAttribute('accept', 'image/*');
    input.click();
    input.onchange = () => {
        const file = input.files[0];
        if (/^image\//.test(file.type)) {
            const fd = new FormData();
            fd.append('image', file);
            const csrfToken = document.querySelector('meta[name="csrf-token"]');
            const token = csrfToken ? csrfToken.getAttribute('content') : '';
            
            fetch('/dainely-admin-panel/editor-upload', {
                method: 'POST',
                headers: { 
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json'
                },
                body: fd
            })
            .then(async r => {
                if (!r.ok) {
                    let err = await r.json().catch(() => ({}));
                    throw new Error(err.message || 'Server error: ' + r.status);
                }
                return r.json();
            })
            .then(result => {
                if (result.success) {
                    const range = quill.getSelection(true) || {index: quill.getLength()};
                    quill.insertEmbed(range.index, 'image', result.url);
                    quill.setSelection(range.index + 1);
                } else { 
                    alert('Upload failed: ' + (result.message || 'Unknown error')); 
                }
            })
            .catch(e => {
                console.error(e);
                alert('Upload failed: ' + e.message);
            });
        }
    };
}
JS;

foreach ($files as $file) {
    $content = file_get_contents($file);
    
    // Completely replace the old selectLocalImage function
    $content = preg_replace('/function selectLocalImage\(quill\) \{[\s\S]*?\}\n/m', $jsHandler . "\n", $content);
    
    file_put_contents($file, $content);
}
echo "Done";