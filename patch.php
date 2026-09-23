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
                headers: { 'X-CSRF-TOKEN': token },
                body: fd
            }).then(r => r.json()).then(result => {
                if (result.success) {
                    const range = quill.getSelection(true) || {index: quill.getLength()};
                    quill.insertEmbed(range.index, 'image', result.url);
                    quill.setSelection(range.index + 1);
                } else { alert('Upload failed'); }
            }).catch(e => alert('Upload failed'));
        }
    };
}
JS;

foreach ($files as $file) {
    $content = file_get_contents($file);
    if (strpos($content, 'selectLocalImage(quill)') !== false) {
        continue;
    }
    
    // Inject JS
    $content = preg_replace('/<script>/i', "<script>\n" . $jsHandler, $content, 1);
    
    // Inject Toolbar
    if (strpos($file, 'blogs') !== false) {
        $toolbar = <<<JS
                    toolbar: {
                        container: [
                            [{ 'header': [1, 2, 3, 4, false] }],
                            ['bold', 'italic', 'underline', 'strike', 'blockquote'],
                            [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                            ['link', 'image', 'video'],
                            ['table'],
                            ['clean']
                        ],
                        handlers: {
                            image: function() { selectLocalImage(this.quill); }
                        }
                    }
JS;
        $content = preg_replace('/toolbar:\s*\[[\s\S]*?\[\'clean\'\]\s*\]/m', $toolbar, $content);
    } else {
        $toolbar = <<<JS
                    toolbar: {
                        container: [
                            ['bold', 'italic', 'underline', 'strike'],
                            ['blockquote', 'code-block'],
                            [{ 'header': 1 }, { 'header': 2 }],
                            [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                            [{ 'script': 'sub'}, { 'script': 'super' }],
                            [{ 'indent': '-1'}, { 'indent': '+1' }],
                            [{ 'direction': 'rtl' }],
                            [{ 'size': ['small', false, 'large', 'huge'] }],
                            [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                            [{ 'color': [] }, { 'background': [] }],
                            [{ 'font': [] }],
                            [{ 'align': [] }],
                            ['clean'],
                            ['link', 'image', 'video']
                        ],
                        handlers: {
                            image: function() { selectLocalImage(this.quill); }
                        }
                    }
JS;
        $content = preg_replace('/toolbar:\s*\[[\s\S]*?\[\'link\', \'image\', \'video\'\]\s*\]/m', $toolbar, $content);
    }
    file_put_contents($file, $content);
}
echo "Done";