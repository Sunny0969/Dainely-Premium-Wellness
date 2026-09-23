@extends('layouts.admin')

@section('admin_title', 'Create Blog Post')

@section('admin_content')
@push('admin_head')
<link href="https://cdn.jsdelivr.net/npm/quill@2/dist/quill.snow.css" rel="stylesheet">
@endpush

<div class="mb-4">
    <a href="/dainely-admin-panel/blogs" class="text-sm text-slate-500 hover:text-navy-700">â† Back to blogs list</a>
</div>

<form id="blog-post-form" action="/dainely-admin-panel/blogs" method="POST" enctype="multipart/form-data" class="space-y-6" x-data="{ activeLocale: 'en' }">
    @csrf

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {{-- Left Form Fields --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Tabs selection --}}
            <div class="flex border-b border-slate-200">
                <button type="button" @click="activeLocale = 'en'" :class="activeLocale === 'en' ? 'border-navy-500 text-navy-600 font-bold border-b-2' : 'text-slate-500'" class="px-4 py-2.5 text-sm transition">English (en)</button>
                <button type="button" @click="activeLocale = 'fr'" :class="activeLocale === 'fr' ? 'border-navy-500 text-navy-600 font-bold border-b-2' : 'text-slate-500'" class="px-4 py-2.5 text-sm transition">French (fr)</button>
                <button type="button" @click="activeLocale = 'de'" :class="activeLocale === 'de' ? 'border-navy-500 text-navy-600 font-bold border-b-2' : 'text-slate-500'" class="px-4 py-2.5 text-sm transition">German (de)</button>
            </div>

            {{-- Multilingual Inputs --}}
            @foreach(['en', 'fr', 'de'] as $loc)
            <div x-show="activeLocale === '{{ $loc }}'" class="space-y-6">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Title ({{ strtoupper($loc) }})</label>
                    <input type="text" name="translations[{{ $loc }}][title]" class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm focus:ring-navy-500 focus:border-navy-500">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Excerpt ({{ strtoupper($loc) }})</label>
                    <textarea name="translations[{{ $loc }}][excerpt]" rows="3" class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm focus:ring-navy-500 focus:border-navy-500" placeholder="Brief summary of the article..."></textarea>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Content ({{ strtoupper($loc) }})</label>
                    <div id="editor-{{ $loc }}" class="bg-white h-72 rounded-b-lg border border-slate-300"></div>
                    <input type="hidden" name="translations[{{ $loc }}][content]" id="content-{{ $loc }}">
                </div>



                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Tags / Keywords ({{ strtoupper($loc) }})</label>
                    <input type="text" name="translations[{{ $loc }}][tags]" class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm focus:ring-navy-500 focus:border-navy-500" placeholder="sciatica, back pain, posture (comma separated)">
                </div>

                {{-- FAQs Section --}}
                <div class="bg-slate-50 p-6 rounded-xl border border-slate-200" x-data="{ faqs: [] }">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-sm font-bold text-slate-800">FAQs ({{ strtoupper($loc) }})</h3>
                        <button type="button" @click="faqs.push({question: '', answer: ''})" class="text-xs bg-slate-800 hover:bg-slate-900 text-white font-semibold px-3 py-1.5 rounded-lg">Add FAQ</button>
                    </div>
                    <div class="space-y-4">
                        <template x-for="(faq, index) in faqs" :key="index">
                            <div class="p-5 bg-white rounded-xl border border-slate-200 shadow-sm space-y-4">
                                <div class="flex justify-between items-center pb-2 border-b border-slate-100">
                                    <span class="text-xs font-bold text-slate-400">FAQ #<span x-text="index + 1"></span></span>
                                    <button type="button" @click="faqs.splice(index, 1)" class="text-rose-600 hover:text-rose-800 text-xs font-bold transition">âœ• Remove</button>
                                </div>
                                <div class="space-y-3">
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-500 mb-1">Question</label>
                                        <input type="text" :name="`translations[{{ $loc }}][faqs][${index}][question]`" x-model="faq.question" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:ring-navy-500 focus:border-navy-500">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-500 mb-1">Answer</label>
                                        <textarea :name="`translations[{{ $loc }}][faqs][${index}][answer]`" x-model="faq.answer" rows="3" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:ring-navy-500 focus:border-navy-500"></textarea>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- SEO Fields --}}
                <div class="bg-slate-50 p-6 rounded-xl border border-slate-200 space-y-4">
                    <h3 class="text-sm font-bold text-slate-800">SEO Settings ({{ strtoupper($loc) }})</h3>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Meta Title</label>
                        <input type="text" name="translations[{{ $loc }}][meta_title]" class="w-full rounded-lg border border-slate-300 px-4 py-2 text-xs">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Meta Description</label>
                        <textarea name="translations[{{ $loc }}][meta_description]" rows="2" class="w-full rounded-lg border border-slate-300 px-4 py-2 text-xs"></textarea>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Right Column (Sidebar Settings) --}}
        <div class="space-y-6">
            <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm space-y-6">
                <h3 class="text-base font-bold text-slate-800">Post Settings</h3>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Category</label>
                    <select name="blog_category_id" class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm">
                        @foreach($categories as $category)
                            @php 
                                $catName = is_array($category->name) ? ($category->name['en'] ?? '') : $category->name;
                            @endphp
                            <option value="{{ $category->id }}">{{ $catName }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Cover Image</label>
                    <input type="file" name="featured_image" class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-slate-800 file:text-white hover:file:bg-slate-900">
                    <p class="text-xs text-rose-500 mt-2 font-medium">âš ï¸ Max file size: 2MB. Please compress larger images before uploading.</p>
                </div>

                <div class="pt-2">
                    <label class="block text-xs font-bold text-slate-700 mb-1">Cover Image Alt Tag</label>
                    <input type="text" name="translations[en][featured_image_alt]" class="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-xs" placeholder="e.g. Back support belt">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Author Name</label>
                    <input type="text" name="author_name" value="Dainely Editorial" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>

                <div class="pt-4 border-t border-slate-200">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="hidden" name="is_published" value="0">
                        <input type="checkbox" name="is_published" value="1" class="rounded border-slate-300 text-navy-600 focus:ring-navy-500">
                        <span class="text-sm font-semibold text-slate-700">Publish Post Immediately</span>
                    </label>
                </div>

                <button type="submit" class="w-full bg-slate-800 hover:bg-slate-900 text-white font-bold py-3 rounded-lg text-sm transition">Create Post</button>
            </div>
        </div>
    </div>
</form>

@push('admin_scripts')
<script src="https://cdn.jsdelivr.net/npm/quill@2/dist/quill.js"></script>
<script src="https://unpkg.com/quill-html-edit-button@2.2.7/dist/quill.htmlEditButton.min.js"></script>
<script>
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
            }).catch(e => alert('Upload failed'));
        }
    };
}Quill.register("modules/htmlEditButton", htmlEditButton);</script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const locales = ['en', 'fr', 'de'];
        const editors = {};

        locales.forEach(loc => {
            editors[loc] = new Quill('#editor-' + loc, {
                theme: 'snow',
                modules: {
                    htmlEditButton: { msg: 'Edit HTML Code' },
                    table: true,
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
                }
            });
        });

        const form = document.getElementById('blog-post-form');
        form.addEventListener('submit', function () {
            locales.forEach(loc => {
                const contentInput = document.getElementById('content-' + loc);
                contentInput.value = editors[loc].root.innerHTML;
            });
        });
    });
</script>
@endpush
@endsection
