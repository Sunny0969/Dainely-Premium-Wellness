import re

with open('resources/views/admin/landings/edit.blade.php', 'r', encoding='utf-8') as f:
    content = f.read()

# For the Add Block form
add_block_textarea_regex = r'(<label class="block text-sm font-semibold text-slate-700 mb-1">Block Content.*?<textarea name="content" rows="4" class=")(w-full.*?></textarea>)'
add_block_replacement = r'''\1tinymce-editor \2
                  <input type="hidden" name="bg_color" class="bg-color-hidden" value="">
                  <input type="hidden" name="text_color" class="text-color-hidden" value="">'''
content = re.sub(add_block_textarea_regex, add_block_replacement, content, flags=re.DOTALL)

# For the Edit Block forms
edit_block_textarea_regex = r'(<label class="block text-xs font-bold text-slate-500 mb-1">Block Content</label>\s*<textarea name="content" class=")(w-full.*?>{{ \->content }}</textarea>)'
edit_block_replacement = r'''\1tinymce-editor \2
                                      <input type="hidden" name="bg_color" class="bg-color-hidden" value="{{ ->bg_color ?? '' }}">
                                      <input type="hidden" name="text_color" class="text-color-hidden" value="{{ ->text_color ?? '' }}">'''
content = re.sub(edit_block_textarea_regex, edit_block_replacement, content, flags=re.DOTALL)

# Add TinyMCE script at the end
tinymce_script = '''
@push('admin_head')
<style>
  .tox-toolbar__primary { display: flex; align-items: center; flex-wrap: wrap; }
  .custom-color-picker-container { display: flex; align-items: center; margin-left: 10px; gap: 8px; border-left: 1px solid #ccc; padding-left: 10px; }
  .custom-color-picker-container label { font-size: 11px; font-weight: bold; color: #555; text-transform: uppercase; cursor: pointer; }
  .custom-color-picker-container input[type="color"] { width: 24px; height: 24px; border: none; padding: 0; cursor: pointer; border-radius: 4px; overflow: hidden; }
  .tox-tinymce { border-radius: 0.5rem !important; border-color: #cbd5e1 !important; }
</style>
@endpush

@push('admin_scripts')
<script src="https://cdn.jsdelivr.net/npm/tinymce@7.6.1/tinymce.min.js" referrerpolicy="origin"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    tinymce.init({
        selector: '.tinymce-editor',
        license_key: 'gpl',
        plugins: 'lists link autoresize code',
        toolbar: 'bold italic underline | bullist numlist | link | removeformat | code',
        menubar: false,
        branding: false,
        promotion: false,
        statusbar: false,
        min_height: 150,
        setup: function(editor) {
            editor.on('init', function() {
                var form = editor.getElement().closest('form');
                if (!form) return;
                
                var bgColorHidden = form.querySelector('.bg-color-hidden');
                var textColorHidden = form.querySelector('.text-color-hidden');
                
                if (bgColorHidden && textColorHidden) {
                    var toolbar = editor.getContainer().querySelector('.tox-toolbar__primary');
                    if (toolbar) {
                        var container = document.createElement('div');
                        container.className = 'custom-color-picker-container';
                        
                        var bgLabel = document.createElement('label');
                        bgLabel.innerText = 'BG';
                        var bgInput = document.createElement('input');
                        bgInput.type = 'color';
                        bgInput.value = bgColorHidden.value || '#ffffff';
                        bgLabel.appendChild(bgInput);
                        
                        var textLabel = document.createElement('label');
                        textLabel.innerText = 'Font';
                        var textInput = document.createElement('input');
                        textInput.type = 'color';
                        textInput.value = textColorHidden.value || '#000000';
                        textLabel.appendChild(textInput);
                        
                        bgInput.addEventListener('input', function(e) { bgColorHidden.value = e.target.value; });
                        textInput.addEventListener('input', function(e) { textColorHidden.value = e.target.value; });
                        
                        container.appendChild(bgLabel);
                        container.appendChild(textLabel);
                        toolbar.appendChild(container);
                    }
                }
            });
            editor.on('change', function () {
                editor.save();
            });
        }
    });
});
</script>
@endpush
'''

content = content.replace('@endsection', tinymce_script + '\\n@endsection')

with open('resources/views/admin/landings/edit.blade.php', 'w', encoding='utf-8') as f:
    f.write(content)

print("Done")
