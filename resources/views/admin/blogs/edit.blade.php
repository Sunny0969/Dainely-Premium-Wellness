@extends('layouts.admin')

@section('admin_title', 'Edit Blog Post')

@push('admin_head')
<link href="https://cdn.jsdelivr.net/npm/quill@2/dist/quill.snow.css" rel="stylesheet">
<style>
    .ql-editor {
        min-height: 250px;
    }
</style>
@endpush

@section('admin_content')
<div class="mb-6 flex items-center justify-between">
    <a href="/dainely-admin-panel/blogs" class="text-sm font-semibold text-navy-700 hover:text-navy-900 flex items-center gap-1">
        â† Back to Blog List
    </a>
</div>

<form action="/dainely-admin-panel/blogs/{{ $post->id }}/update" method="POST" enctype="multipart/form-data" id="blog-post-form">
    @csrf

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {{-- Left Column (Locales tabs & Rich text content) --}}
        <div class="lg:col-span-2 space-y-6" x-data="{ currentTab: 'en' }">
            {{-- Tabs buttons --}}
            <div class="border-b border-slate-200">
                <nav class="flex gap-6" aria-label="Tabs">
                    <button type="button" @click="currentTab = 'en'" :class="currentTab === 'en' ? 'border-navy-600 text-navy-600' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'" class="shrink-0 border-b-2 py-4 px-1 text-sm font-bold transition">
                        English (EN)
                    </button>
                    <button type="button" @click="currentTab = 'fr'" :class="currentTab === 'fr' ? 'border-navy-600 text-navy-600' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'" class="shrink-0 border-b-2 py-4 px-1 text-sm font-bold transition">
                        French (FR)
                    </button>
                    <button type="button" @click="currentTab = 'de'" :class="currentTab === 'de' ? 'border-navy-600 text-navy-600' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'" class="shrink-0 border-b-2 py-4 px-1 text-sm font-bold transition">
                        German (DE)
                    </button>
                </nav>
            </div>

            @foreach(['en', 'fr', 'de'] as $loc)
                @php
                    $trans = $post->translations->firstWhere('locale', $loc);
                @endphp
                <div x-show="currentTab === '{{ $loc }}'" class="space-y-6" x-cloak>
                    {{-- Alert to notify auto-translate behavior --}}
                    @if($loc !== 'en')
                        <div class="p-3 bg-slate-50 border border-slate-200 rounded-lg text-xs text-slate-600">
                            ðŸ’¡ If Title is left empty, the English post will be automatically translated into {{ strtoupper($loc) }} using DeepL/MyMemory upon saving.
                        </div>
                    @endif

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Title ({{ strtoupper($loc) }})</label>
                        <input type="text" name="translations[{{ $loc }}][title]" value="{{ $trans ? $trans->title : '' }}" class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm" placeholder="Post Title">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Excerpt ({{ strtoupper($loc) }})</label>
                        <textarea name="translations[{{ $loc }}][excerpt]" rows="3" class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm" placeholder="Brief summary of the article">{{ $trans ? $trans->excerpt : '' }}</textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Content ({{ strtoupper($loc) }})</label>
                        <div class="bg-white rounded-lg border border-slate-300 overflow-hidden">
                            <div id="editor-{{ $loc }}">{!! $trans ? $trans->content : '' !!}</div>
                        </div>
                        <input type="hidden" name="translations[{{ $loc }}][content]" id="content-{{ $loc }}">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Tags ({{ strtoupper($loc) }}) <span class="text-xs text-slate-400 font-normal">(Comma-separated)</span></label>
                        @php
                            $tagsStr = $trans ? (is_array($trans->tags) ? implode(', ', $trans->tags) : $trans->tags) : '';
                        @endphp
                        <input type="text" name="translations[{{ $loc }}][tags]" value="{{ $tagsStr }}" class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm" placeholder="e.g. Back pain, posture, support">
                    </div>

                    <div class="bg-slate-50 p-6 rounded-xl border border-slate-200 space-y-4">
                        <h4 class="text-sm font-bold text-slate-800 border-b border-slate-200 pb-2">SEO Settings ({{ strtoupper($loc) }})</h4>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Meta Title</label>
                            <input type="text" name="translations[{{ $loc }}][meta_title]" value="{{ $trans ? $trans->meta_title : '' }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-xs" placeholder="Meta Title">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Meta Description</label>
                            <textarea name="translations[{{ $loc }}][meta_description]" rows="2" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-xs" placeholder="Meta Description">{{ $trans ? $trans->meta_description : '' }}</textarea>
                        </div>
                    </div>

                    {{-- FAQs accordion CRUD ($loc spec) --}}
                    <div class="bg-slate-50 p-6 rounded-xl border border-slate-200 space-y-4" x-data="{ faqs: {{ json_encode($trans && is_array($trans->faqs) ? $trans->faqs : ($trans && is_string($trans->faqs) ? json_decode($trans->faqs, true) ?? [] : [])) }} }">
                        <div class="flex justify-between items-center border-b border-slate-200 pb-2">
                            <h4 class="text-sm font-bold text-slate-800">Frequently Asked Questions ({{ strtoupper($loc) }})</h4>
                            <button type="button" @click="faqs.push({question: '', answer: ''})" class="text-xs font-bold text-navy-700 hover:text-navy-900 flex items-center gap-1">+ Add FAQ</button>
                        </div>
                        <template x-for="(faq, index) in faqs" :key="index">
                            <div class="p-4 bg-white border border-slate-200 rounded-lg space-y-2 relative">
                                <button type="button" @click="faqs.splice(index, 1)" class="absolute top-2 right-2 text-rose-500 hover:text-rose-700 text-xs font-semibold">Delete</button>
                                <div>
                                    <label class="block text-xs font-bold text-slate-600 mb-1">Question</label>
                                    <input type="text" :name="`translations[{{ $loc }}][faqs][${index}][question]`" x-model="faq.question" class="w-full rounded border-slate-300 px-2 py-1 text-xs">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-600 mb-1">Answer</label>
                                    <textarea :name="`translations[{{ $loc }}][faqs][${index}][answer]`" x-model="faq.answer" rows="2" class="w-full rounded border-slate-300 px-2 py-1 text-xs"></textarea>
                                </div>
                            </div>
                        </template>
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
                            <option value="{{ $category->id }}" {{ $post->blog_category_id == $category->id ? 'selected' : '' }}>{{ $catName }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Cover Image</label>
                    <div class="mb-3">
                        <img id="current-cover-preview" 
                             src="{{ $post->featured_image ? (\Illuminate\Support\Str::startsWith($post->featured_image, ['http://', 'https://']) ? $post->featured_image : asset('images/' . $post->featured_image)) : '' }}" 
                             alt="current cover" 
                             class="w-full h-32 object-cover rounded-lg border border-slate-200 mb-1 {{ !$post->featured_image ? 'hidden' : '' }}">
                        <span id="current-cover-filename" class="text-xs text-slate-400 block {{ !$post->featured_image ? 'hidden' : '' }}">
                            Current: {{ $post->featured_image }}
                        </span>
                    </div>
                    <input type="file" name="featured_image" id="featured-image-input" class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-slate-800 file:text-white hover:file:bg-slate-900">
                    <p class="text-xs text-rose-500 mt-2 font-medium">âš ï¸ Max file size: 2MB. Please compress larger images before uploading.</p>
                </div>

                @php
                    $enTrans = $post->translations->firstWhere('locale', 'en');
                @endphp
                <div class="pt-2">
                    <label class="block text-xs font-bold text-slate-700 mb-1">Cover Image Alt Tag</label>
                    <input type="text" name="translations[en][featured_image_alt]" value="{{ $enTrans ? $enTrans->featured_image_alt : '' }}" class="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-xs" placeholder="e.g. Back support belt">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Author Name</label>
                    <input type="text" name="author_name" value="{{ $post->author_name }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>

                <div class="pt-4 border-t border-slate-200">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="hidden" name="is_published" value="0">
                        <input type="checkbox" name="is_published" value="1" {{ $post->is_published ? 'checked' : '' }} class="rounded border-slate-300 text-navy-600 focus:ring-navy-500">
                        <span class="text-sm font-semibold text-slate-700">Publish Post Immediately</span>
                    </label>
                </div>

                <button type="submit" class="w-full bg-slate-800 hover:bg-slate-900 text-white font-bold py-3 rounded-lg text-sm transition">Save Changes</button>
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

        // Cover Image Live Preview Handler
        const imgInput = document.getElementById('featured-image-input');
        const imgPreview = document.getElementById('current-cover-preview');
        const imgFilename = document.getElementById('current-cover-filename');

        if (imgInput && imgPreview) {
            imgInput.addEventListener('change', function (e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function (event) {
                        imgPreview.src = event.target.result;
                        imgPreview.classList.remove('hidden');
                        if (imgFilename) {
                            imgFilename.textContent = 'Selected Preview: ' + file.name;
                            imgFilename.classList.remove('hidden');
                        }
                    };
                    reader.readAsDataURL(file);
                }
            });
        }
    });
</script>
@endpush
@endsection
