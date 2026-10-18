@extends('layouts.admin')

@section('admin_title', 'Edit Landing Page: ' . $page->title)

@push('admin_head')
<link href="https://cdn.jsdelivr.net/npm/quill@2/dist/quill.snow.css" rel="stylesheet">
<style>
    .landing-editor .ql-toolbar.ql-snow {
        border-top-left-radius: 8px;
        border-top-right-radius: 8px;
        background: #f8fafc;
        position: sticky;
        top: 0;
        z-index: 5;
    }
    .landing-editor .ql-container.ql-snow {
        border-bottom-left-radius: 8px;
        border-bottom-right-radius: 8px;
        font-family: inherit;
        font-size: 15px;
    }
    .landing-editor .ql-editor {
        min-height: 150px;
        padding: 12px 14px;
        line-height: 1.6;
        color: #1e293b;
    }
    .landing-editor--tall .ql-editor { min-height: 340px; }
    .landing-editor .ql-editor h1 { font-size: 1.9em; }
    .landing-editor .ql-editor h2 { font-size: 1.55em; }
    .landing-editor .ql-editor h3 { font-size: 1.3em; }
    .landing-editor .ql-editor h4 { font-size: 1.12em; }
    .landing-editor .ql-editor h5 { font-size: 1.02em; }
    .landing-editor .ql-editor h6 { font-size: .95em; text-transform: uppercase; letter-spacing: .05em; }
    .landing-editor .ql-editor blockquote {
        background: #f8fafc;
        border-left: 4px solid #cbd5e1;
        border-radius: 0 6px 6px 0;
        color: #334155;
        padding: .5rem .85rem;
    }
    .landing-editor .ql-editor pre.ql-syntax { background: #0f172a; color: #e2e8f0; }
    .landing-editor .ql-editor code { background: #f1f5f9; color: #0f172a; }
    /* Pasted tables need to look like they will ship. */
    .landing-editor .ql-editor table {
        border-collapse: collapse;
        margin: 10px 0;
        table-layout: auto;
        width: 100%;
        border: 1px solid #cbd5e1 !important;
    }
    .landing-editor .ql-editor th,
    .landing-editor .ql-editor td {
        border: 1px solid #cbd5e1;
        padding: 6px 8px;
        text-align: left;
        vertical-align: top;
        word-break: break-word;
    }
    .landing-editor .ql-editor th { background: #f1f5f9; font-weight: 700; }
    .landing-editor .ql-editor.ql-blank::before {
        font-style: normal;
        color: #94a3b8;
    }
    .landing-editor .ql-editor img {
        max-width: 100%;
        height: auto;
        border-radius: 8px;
        margin: 8px 0;
        display: block;
    }
    .landing-editor .ql-editor img.ql-image-uploading {
        opacity: .5;
    }
    .landing-preview img { max-width: 100%; height: auto; border-radius: 6px; }

    /* Quill list formatting fixes to prevent Tailwind / cms-richtext conflict */
    .landing-editor .ql-editor ol,
    .landing-editor .ql-editor ul {
        padding-left: 0 !important;
        margin: 0.5em 0 !important;
        list-style-type: none !important;
    }
    .landing-editor .ql-editor li {
        list-style-type: none !important;
        padding-left: 1.5em !important;
        position: relative !important;
        display: block !important;
    }
    .landing-editor .ql-editor li > .ql-ui:before {
        display: inline-block !important;
        margin-left: -1.5em !important;
        margin-right: 0.3em !important;
        text-align: right !important;
        white-space: nowrap !important;
        width: 1.2em !important;
    }
    .landing-editor .ql-editor li[data-list="bullet"] > .ql-ui:before {
        content: '\2022' !important;
    }
    .landing-editor .ql-editor li[data-list="ordered"] {
        counter-increment: list-0;
    }
    .landing-editor .ql-editor li[data-list="ordered"] > .ql-ui:before {
        content: counter(list-0, decimal) '. ' !important;
    }
</style>
@endpush

@section('admin_content')
@php
    // Block types that expect a plain value (URL / raw HTML source) instead of rich text
    $plainBlockTypes = ['video', 'comparison'];
    $nextSortOrder = (int) ($page->pageBlocks->max('sort_order') ?? 0) + 1;

    // Product picker: label is the plain-English title only — no GIDs, no handles.
    $storedProduct = trim((string) ($page->shopify_product_id ?? ''));
    $productChoices = [];
    foreach ($products ?? [] as $prod) {
        $value = trim((string) ($prod->shopify_product_id ?? ''));
        if ($value === '') {
            $value = trim((string) ($prod->handle ?? ''));
        }
        $title = trim((string) ($prod->title ?? ''));
        if ($value !== '' && $title !== '') {
            $productChoices[$value] = $title;
        }
    }
    // A legacy value stored as a handle (or anything not in the list) still needs
    // its own option so saving never silently swaps the selection.
    if ($storedProduct !== '' && ! array_key_exists($storedProduct, $productChoices)) {
        $label = '';
        foreach ($products ?? [] as $prod) {
            if (trim((string) ($prod->handle ?? '')) === $storedProduct) {
                $label = trim((string) ($prod->title ?? ''));
                break;
            }
        }
        $productChoices[$storedProduct] = $label !== '' ? $label : $storedProduct;
    }
@endphp

<div class="space-y-8">
    {{-- Page Meta details --}}
    <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
        <h2 class="text-lg font-bold text-slate-800 mb-6">Page Settings</h2>
        <form action="/{{ $adminBase }}/landings/{{ $page->id }}/update" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @csrf
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Title</label>
                <input type="text" name="title" value="{{ $page->title }}" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Slug</label>
                <input type="text" name="slug" value="{{ $page->slug }}" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">SEO Title</label>
                <input type="text" name="meta_title" value="{{ $page->meta_title }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">SEO Description</label>
                <textarea name="meta_description" rows="2" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">{{ $page->meta_description }}</textarea>
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Published Status</label>
                <select name="published" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="0" {{ !$page->published ? 'selected' : '' }}>Draft</option>
                    <option value="1" {{ $page->published ? 'selected' : '' }}>Published</option>
                </select>
            </div>

            <div class="md:col-span-2 border-t border-slate-100 pt-6" x-data="{
                activeSectionTab: 'product',
                faqs: {{ Js::from(($page->faqs ?? collect())->map(fn($f) => [
                    'id' => $f->id,
                    'question' => $f->question,
                    'answer' => $f->answer,
                ])->values()) }},
                addFaq() {
                    this.faqs.push({ id: null, question: '', answer: '' });
                },
                removeFaq(idx) {
                    this.faqs.splice(idx, 1);
                }
            }">
                <input type="hidden" name="faqs_synced" value="1">

                {{-- Option switcher: Product vs FAQ --}}
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 pb-3 mb-5">
                    <div class="flex items-center gap-2">
                        <button type="button"
                                @click="activeSectionTab = 'product'"
                                :class="activeSectionTab === 'product' ? 'bg-slate-800 text-white shadow-sm' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                                class="px-4 py-2 rounded-lg text-sm font-bold transition flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                            <span>Product</span>
                        </button>
                        <button type="button"
                                @click="activeSectionTab = 'faq'"
                                :class="activeSectionTab === 'faq' ? 'bg-slate-800 text-white shadow-sm' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                                class="px-4 py-2 rounded-lg text-sm font-bold transition flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <span>FAQ</span>
                            <span x-show="faqs.length > 0" x-text="faqs.length" class="text-xs px-2 py-0.5 rounded-full font-bold" :class="activeSectionTab === 'faq' ? 'bg-slate-700 text-white' : 'bg-slate-200 text-slate-700'"></span>
                        </button>
                    </div>
                    <span class="text-xs text-slate-400" x-show="activeSectionTab === 'product'">&sect;8.4 Offer / Checkout CTA</span>
                    <span class="text-xs text-slate-400" x-show="activeSectionTab === 'faq'">Page FAQs (Saved when clicking Save Settings)</span>
                </div>

                {{-- 1. Product Content (Shown when Product option active) --}}
                <div x-show="activeSectionTab === 'product'">
                    <p class="text-xs text-slate-500 mb-4">Select the product that this landing page's CTA button will navigate to.</p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Shopify Product</label>
                            <select name="shopify_product_id" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                                <option value="">— none —</option>
                                @foreach($productChoices as $value => $title)
                                    <option value="{{ $value }}" @selected($storedProduct === $value)>{{ $title }}</option>
                                @endforeach
                            </select>
                            <p class="text-xs text-slate-400 mt-1">Visitors clicking the CTA button will be taken directly to this product's details page.</p>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">CTA Label</label>
                            <input type="text" name="cta_label" value="{{ $page->cta_label }}" placeholder="Shop Now" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            <p class="text-xs text-slate-400 mt-1">Label shown on the call-to-action button (e.g. Shop Now).</p>
                        </div>
                    </div>
                </div>

                {{-- 2. FAQ Content (Shown when FAQ option active) --}}
                <div x-show="activeSectionTab === 'faq'" x-cloak class="space-y-4">
                    <p class="text-xs text-slate-500 mb-3">Add frequently asked questions for this landing page. Questions & answers will be saved to the database when you click <strong>Save Settings</strong>.</p>

                    {{-- Empty state --}}
                    <div x-show="faqs.length === 0" class="text-center py-8 border-2 border-dashed border-slate-200 rounded-xl bg-slate-50/70">
                        <svg class="w-9 h-9 text-slate-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <p class="text-sm text-slate-600 font-medium mb-3">No FAQs added yet.</p>
                        <button type="button"
                                @click="addFaq()"
                                class="inline-flex items-center gap-1.5 px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold rounded-lg transition shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            <span>Add First FAQ</span>
                        </button>
                    </div>

                    {{-- FAQs List --}}
                    <template x-for="(faq, index) in faqs" :key="index">
                        <div class="p-4 bg-slate-50 rounded-xl border border-slate-200 space-y-3 transition hover:border-slate-300">
                            <input type="hidden" :name="'faqs[' + index + '][id]'" :value="faq.id || ''">

                            <div class="flex items-start gap-4">
                                {{-- Index badge --}}
                                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-slate-200 text-slate-700 text-xs font-bold flex-shrink-0 mt-1" x-text="index + 1"></span>

                                {{-- Question & Answer inputs --}}
                                <div class="flex-1 space-y-3">
                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                                            Question <span class="text-rose-500">*</span>
                                        </label>
                                        <input type="text"
                                               :name="'faqs[' + index + '][question]'"
                                               x-model="faq.question"
                                               required
                                               placeholder="e.g. How does the Dainely belt relieve back pain?"
                                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm bg-white focus:border-slate-500 focus:ring-1 focus:ring-slate-500 outline-none">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                                            Answer <span class="text-rose-500">*</span>
                                        </label>
                                        <textarea :name="'faqs[' + index + '][answer]'"
                                                  x-model="faq.answer"
                                                  required
                                                  rows="3"
                                                  placeholder="Provide the detailed answer here..."
                                                  class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm bg-white focus:border-slate-500 focus:ring-1 focus:ring-slate-500 outline-none"></textarea>
                                    </div>
                                </div>

                                {{-- Action buttons: Delete on each, Delete and Add on the last FAQ --}}
                                <div class="flex flex-col items-end gap-2 flex-shrink-0 pt-6">
                                    <button type="button"
                                            @click="removeFaq(index)"
                                            title="Delete FAQ"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-rose-200 bg-rose-50 hover:bg-rose-100 text-rose-700 text-xs font-semibold transition">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        <span>Delete</span>
                                    </button>

                                    <template x-if="index === faqs.length - 1">
                                        <button type="button"
                                                @click="addFaq()"
                                                title="Add New FAQ"
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-emerald-300 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 text-xs font-bold transition shadow-sm">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                            <span>+ Add</span>
                                        </button>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <div class="md:col-span-2 flex justify-end">
                <button type="submit" class="bg-slate-800 hover:bg-slate-900 text-white font-bold px-6 py-2.5 rounded-lg text-sm transition">
                    Save Settings
                </button>
            </div>
        </form>
    </div>

    {{-- Content Blocks section --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {{-- Add Content Block
             Retained for reference only — the panel below replaces it with a single
             full-featured editor so Word copy can be pasted in one go.
        --}}
        {{--
        <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm h-fit"
             x-data="{ blockType: 'benefits' }"
             x-init="plainIs(blockType) || landingOpenEditor('new'), $watch('blockType', v => { plainIs(v) ? landingToPlain('new') : landingToRich('new'); })">
            <h2 class="text-lg font-bold text-slate-800 mb-6">Add Content Block</h2>
            <form action="/{{ $adminBase }}/landings/{{ $page->id }}/blocks" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Block Type</label>
                    <select name="block_type" x-model="blockType" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <option value="benefits">Benefits</option>
                        <option value="how-it-works">How It Works</option>
                        <option value="testimonials">Testimonials</option>
                        <option value="faqs">FAQs Accordion</option>
                        <option value="cta">Call to Action (uses page offer)</option>
                        <option value="video">Video (YouTube/Vimeo URL)</option>
                        <option value="comparison">Comparison (HTML table)</option>
                        <option value="bundle">Bundle (components + total — put bundle ID in content)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Block Title</label>
                    <input type="text" name="title" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">
                        Block Content
                        <span class="text-slate-400 font-normal" x-show="!plainIs(blockType)">— rich text (bold, headings, lists, links, images)</span>
                        <span class="text-slate-400 font-normal" x-show="plainIs(blockType)" x-cloak x-text="blockType === 'video' ? '— video URL only' : '— raw HTML (tables)'"></span>
                    </label>

                    <!-- Plain mode (video URL / comparison HTML) -->
                    <div x-show="plainIs(blockType)" x-cloak>
                        <textarea name="content" id="content-new" rows="4" placeholder="https://www.youtube.com/watch?v=… or paste an HTML table" class="landing-preview w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></textarea>
                        <p class="text-xs text-slate-400 mt-1">Plain value — no formatting toolbar for this block type.</p>
                    </div>

                    <!-- Rich text mode -->
                    <div x-show="!plainIs(blockType)" x-cloak>
                        <div class="landing-editor" id="qc-new"></div>
                        <div class="flex items-center justify-between gap-3 mt-2">
                            <button type="button" onclick="landingUploadImage('new')" class="inline-flex items-center gap-1.5 bg-navy-600 hover:bg-navy-700 text-white text-xs font-bold px-3 py-2 rounded-lg transition">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                Upload Picture
                            </button>
                            <span class="text-xs text-slate-400">JPG / PNG / WEBP &middot; max 10 MB</span>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Background Style</label>
                        <select name="bg_color" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            <option value="">Default (Alternating)</option>
                            <option value="#ffffff">White (#ffffff)</option>
                            <option value="#f8fafc">Light Slate (#f8fafc)</option>
                            <option value="#f1f5f9">Soft Gray (#f1f5f9)</option>
                            <option value="#0f172a">Dark Navy (#0f172a)</option>
                            <option value="#1e293b">Slate Navy (#1e293b)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Sort Order</label>
                        <input type="number" name="sort_order" value="0" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </div>
                </div>

                <button type="submit" class="w-full bg-slate-800 hover:bg-slate-900 text-white font-bold py-2.5 rounded-lg text-sm transition">
                    Add Block
                </button>
            </form>
        </div>
        --}}

        {{-- Section Title (Single block setup) --}}
        @php
            $singleBlock = $page->pageBlocks->first();
        @endphp
        <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm h-fit">
            <h2 class="text-lg font-bold text-slate-800">Section Title</h2>
            <p class="text-xs text-slate-500 mt-1.5 mb-5">
                Set or update the main section heading for your landing page content.
            </p>

            @if($singleBlock)
                <form action="/{{ $adminBase }}/landings/{{ $page->id }}/blocks/{{ $singleBlock->id }}/update" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-500 mb-1">
                            Section Title
                        </label>
                        <input type="text" name="title" value="{{ old('title', $singleBlock->title) }}" placeholder="e.g. Why Choose Our Product" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-navy-500 focus:ring-1 focus:ring-navy-500" required>
                    </div>

                    <button type="submit" class="w-full bg-slate-800 hover:bg-slate-900 text-white font-bold py-2.5 rounded-lg text-sm transition shadow-sm">
                        Update Section Title
                    </button>
                </form>
            @else
                <form action="/{{ $adminBase }}/landings/{{ $page->id }}/blocks" method="POST" class="space-y-4">
                    @csrf
                    <input type="hidden" name="block_type" value="rich">
                    <input type="hidden" name="sort_order" value="1">

                    <div>
                        <label class="block text-xs font-bold text-slate-500 mb-1">
                            Section Title
                        </label>
                        <input type="text" name="title" value="{{ old('title') }}" placeholder="e.g. Why Choose Our Product" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-navy-500 focus:ring-1 focus:ring-navy-500" required>
                    </div>

                    <button type="submit" class="w-full bg-slate-800 hover:bg-slate-900 text-white font-bold py-2.5 rounded-lg text-sm transition shadow-sm">
                        Add Section Title
                    </button>
                </form>
            @endif
        </div>

        {{-- Existing Content Blocks --}}
        <div class="lg:col-span-2 bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-6 py-4 bg-slate-50 border-b border-slate-200">
                <h2 class="text-lg font-bold text-slate-800">Page Content Layout Blocks</h2>
            </div>

            <div class="divide-y divide-slate-200">
                @forelse($page->pageBlocks as $block)
                    @php
                        $isPlainBlock = in_array($block->block_type, $plainBlockTypes, true);
                        $previewText = trim(strip_tags((string) $block->content));
                        $previewText = $previewText === '' ? '—' : \Illuminate\Support\Str::limit($previewText, 120);
                    @endphp
                    <div class="p-6 space-y-4" x-data="{ editing: false }">
                        <div class="flex justify-between items-start gap-4">
                            <div>
                                <span class="px-2.5 py-0.5 rounded bg-navy-50 text-navy-700 text-xs font-bold uppercase tracking-wider">
                                    {{ $block->block_type }}
                                </span>
                                @if($block->title)
                                    <strong class="block text-slate-800 text-base mt-2">{{ $block->title }}</strong>
                                @endif
                                <div class="text-slate-500 text-xs mt-1 font-mono max-w-lg truncate">{{ $previewText }}</div>
                                <span class="inline-block text-xs font-semibold text-slate-400 mt-2">Visible: {{ $block->visible ? 'Yes' : 'No' }}</span>
                            </div>

                            <div class="flex gap-2">
                                <button type="button"
                                        @click="editing = !editing; landingOpenEditor('b{{ $block->id }}')"
                                        class="text-navy-600 hover:text-navy-800 text-sm font-bold">
                                    <span x-text="editing ? 'Close' : 'Edit Block'">Edit Block</span>
                                </button>
                                <form action="/{{ $adminBase }}/landings/{{ $page->id }}/blocks/{{ $block->id }}/delete" method="POST" onsubmit="return confirm('Are you sure you want to delete this block?');">
                                    @csrf
                                    <button type="submit" class="text-rose-600 hover:text-rose-800 text-sm font-bold">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </div>

                        {{-- Edit Block Form --}}
                        <div x-show="editing" x-cloak class="p-4 bg-slate-50 rounded-lg border border-slate-200">
                            <form action="/{{ $adminBase }}/landings/{{ $page->id }}/blocks/{{ $block->id }}/update" method="POST" class="space-y-4">
                                @csrf
                                <div>
                                    <label class="block text-xs font-bold text-slate-500 mb-1">Block Title</label>
                                    <input type="text" name="title" value="{{ $block->title }}" class="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-500 mb-1">
                                        Block Content
                                        <span class="text-slate-400 font-normal">{{ $isPlainBlock ? '— plain value' : '— rich text' }}</span>
                                    </label>

                                    @if($isPlainBlock)
                                        <textarea name="content" id="content-b{{ $block->id }}" rows="3" class="landing-preview w-full rounded-lg border border-slate-300 px-3 py-1.5 text-sm" placeholder="{{ $block->block_type === 'video' ? 'https://www.youtube.com/watch?v=…' : '<table>…</table>' }}">{{ $block->content }}</textarea>
                                    @else
                                        <div class="landing-editor" id="qc-b{{ $block->id }}"></div>
                                        <textarea name="content" id="content-b{{ $block->id }}" rows="3" class="hidden">{{ $block->content }}</textarea>
                                    @endif
                                </div>
                                <input type="hidden" name="sort_order" value="{{ $block->sort_order }}">
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-bold text-slate-500 mb-1">Background Style</label>
                                        <select name="bg_color" class="w-full rounded-lg border border-slate-300 px-2 py-1.5 text-xs">
                                            <option value="" {{ empty($block->bg_color) ? 'selected' : '' }}>Default (Alternating)</option>
                                            <option value="#ffffff" {{ $block->bg_color === '#ffffff' ? 'selected' : '' }}>White</option>
                                            <option value="#f8fafc" {{ $block->bg_color === '#f8fafc' ? 'selected' : '' }}>Light Slate</option>
                                            <option value="#f1f5f9" {{ $block->bg_color === '#f1f5f9' ? 'selected' : '' }}>Soft Gray</option>
                                            <option value="#0f172a" {{ $block->bg_color === '#0f172a' ? 'selected' : '' }}>Dark Navy</option>
                                            <option value="#1e293b" {{ $block->bg_color === '#1e293b' ? 'selected' : '' }}>Slate Navy</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-500 mb-1">Visible</label>
                                        <select name="visible" class="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-sm">
                                            <option value="1" {{ $block->visible ? 'selected' : '' }}>Yes</option>
                                            <option value="0" {{ !$block->visible ? 'selected' : '' }}>No</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="flex gap-2">
                                    <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold px-3 py-1.5 rounded transition">
                                        Save Block
                                    </button>
                                    <button type="button" @click="editing = false" class="bg-slate-200 hover:bg-slate-300 text-slate-700 text-xs font-bold px-3 py-1.5 rounded transition">
                                        Cancel
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="px-6 py-8 text-center text-slate-400">No layout blocks added yet.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

@push('admin_scripts')
<script src="https://cdn.jsdelivr.net/npm/quill@2/dist/quill.js"></script>
<script src="https://unpkg.com/quill-html-edit-button@2.2.7/dist/quill.htmlEditButton.min.js"></script>
<script>
(function () {
    'use strict';

    var UPLOAD_ENDPOINT = '/dainely-admin-panel/editor-upload';
    var CACHE = {};
    var HTML_EDIT_OK = false;

    // Block types that use a plain textarea instead of the rich editor.
    window.plainIs = function (type) { return type === 'video' || type === 'comparison'; };

    if (typeof Quill !== 'undefined' && typeof htmlEditButton !== 'undefined') {
        try {
            Quill.register('modules/htmlEditButton', htmlEditButton);
            HTML_EDIT_OK = !(Quill.imports && !Quill.imports['modules/htmlEditButton']);
        } catch (e) {
            HTML_EDIT_OK = false;
        }
    }

    function csrfToken() {
        var meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    function uploadFile(file) {
        var fd = new FormData();
        fd.append('image', file);
        fd.append('folder_type', 'landing');
        return fetch(UPLOAD_ENDPOINT, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken(), 'Accept': 'application/json' },
            body: fd
        }).then(function (r) {
            return r.json().catch(function () { return {}; }).then(function (data) {
                if (!r.ok || !data.success) {
                    throw new Error((data && data.message) || ('Server error ' + r.status));
                }
                return data;
            });
        });
    }

    function pickImage() {
        return new Promise(function (resolve) {
            var input = document.createElement('input');
            input.setAttribute('type', 'file');
            input.setAttribute('accept', 'image/*');
            input.onchange = function () {
                var file = input.files && input.files[0];
                if (!file) { return; }
                if (!/^image\//.test(file.type)) { alert('Please choose an image file (JPG, PNG, WEBP…).'); return; }
                resolve(file);
            };
            input.click();
        });
    }

    function insertImage(quill, url) {
        var range = quill.getSelection(true) || { index: quill.getLength() };
        quill.insertEmbed(range.index, 'image', url);
        quill.setSelection(range.index + 1);
    }

    /* ------------------------------------------------------------------ *
     * Full word-processor toolbar                                        *
     * ------------------------------------------------------------------ */

    var TEXT_COLORS = [
        '#ffffff', '#f8fafc', '#e2e8f0', '#cbd5e1',
        '#0f172a', '#1e293b', '#334155', '#475569', '#64748b',
        '#991b1b', '#b45309', '#166534', '#075985', '#1e40af',
        '#6b21a8', '#831843', '#0f766e'
    ];

    var BG_COLORS = [
        '#ffffff', '#f8fafc', '#f1f5f9', '#e2e8f0',
        '#fef3c7', '#dcfce7', '#dbeafe', '#fce7f3', '#ede9fe',
        '#ffedd5', '#fee2e2', '#ccfbf1',
        '#0f172a', '#1e293b'
    ];

    function svgIcon(body) {
        return '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" ' +
            'stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">' + body + '</svg>';
    }

    // Buttons Quill ships without an icon (or with a blank one).
    var EXTRA_ICONS = {
        undo: svgIcon('<polyline points="9 14 4 9 9 4"/><path d="M20 20v-7a4 4 0 0 0-4-4H4"/>'),
        redo: svgIcon('<polyline points="15 14 20 9 15 4"/><path d="M4 20v-7a4 4 0 0 1 4-4h12"/>'),
        'code-block': svgIcon('<polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/>'),
        'table-insert-row': svgIcon('<rect x="3" y="4" width="18" height="16" rx="1.5"/><path d="M3 9.3h18M3 14.7h18M12 4v5.3M12 14.7V20"/><path d="M9 12h6M12 9.5v5"/>'),
        'table-insert-column': svgIcon('<rect x="3" y="4" width="18" height="16" rx="1.5"/><path d="M9.3 4v16M14.7 4v16M3 12h6.3M14.7 12H21"/><path d="M9.5 12h5M12 9.5v5"/>'),
        'table-delete-row': svgIcon('<rect x="3" y="4" width="18" height="16" rx="1.5"/><path d="M3 9.3h18M3 14.7h18M12 4v5.3M12 14.7V20"/><path d="M10 10.4l4 3.2M14 10.4l-4 3.2"/>'),
        'table-delete-column': svgIcon('<rect x="3" y="4" width="18" height="16" rx="1.5"/><path d="M9.3 4v16M14.7 4v16M3 12h6.3M14.7 12H21"/><path d="M10.2 10.2l3.6 3.6M13.8 10.2l-3.6 3.6"/>'),
        'table-delete': svgIcon('<path d="M4 7h16"/><path d="M19 7v12a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V7"/><path d="M9 7V5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2"/><path d="M10 11v6M14 11v6"/>')
    };

    // Quill builds <option> elements without any text, so the pickers render
    // blank until we fill the labels in ourselves.
    var PICKER_LABELS = {
        header: { '': 'Paragraph', 1: 'Heading 1', 2: 'Heading 2', 3: 'Heading 3', 4: 'Heading 4', 5: 'Heading 5', 6: 'Heading 6' },
        font: { '': 'Default font', serif: 'Serif', monospace: 'Monospace' },
        size: { '': 'Default size', small: 'Small', large: 'Large', huge: 'Huge' }
    };

    var TITLES = {
        bold: 'Bold', italic: 'Italic', underline: 'Underline', strike: 'Strikethrough',
        blockquote: 'Quote', 'code-block': 'Code block', link: 'Insert link', image: 'Upload picture',
        clean: 'Clear formatting', sub: 'Subscript', super: 'Superscript',
        header: 'Text style', font: 'Font', size: 'Size', align: 'Alignment', list: 'List', indent: 'Indent',
        color: 'Text colour', background: 'Highlight colour',
        table: 'Insert 3 x 3 table', 'table-insert-row': 'Insert row below',
        'table-insert-column': 'Insert column right', 'table-delete-row': 'Delete row',
        'table-delete-column': 'Delete column', 'table-delete': 'Delete table',
        undo: 'Undo', redo: 'Redo'
    };

    function installIcons() {
        if (typeof Quill === 'undefined') { return; }
        try {
            var icons = Quill.import('ui/icons');
            Object.keys(EXTRA_ICONS).forEach(function (name) { icons[name] = EXTRA_ICONS[name]; });
        } catch (e) { console.warn('Could not register toolbar icons', e); }
    }
    installIcons();

    function firstQlClass(el) {
        var found = '';
        Array.prototype.forEach.call(el.classList, function (cls) {
            if (found === '' && cls.indexOf('ql-') === 0) { found = cls.slice(3); }
        });
        return found;
    }

    // Quill 2 ships pickers with empty option text; give them readable labels,
    // add tooltips to every control and tidy up the HTML-source button.
    function decorate(el) {
        var toolbar = el.querySelector('.ql-toolbar');
        if (!toolbar) { return; }

        Object.keys(PICKER_LABELS).forEach(function (name) {
            var map = PICKER_LABELS[name];
            var select = toolbar.querySelector('select.ql-' + name);
            if (!select) { return; }
            Array.prototype.forEach.call(select.options, function (opt) {
                var text = Object.prototype.hasOwnProperty.call(map, opt.value) ? map[opt.value] : opt.value;
                opt.textContent = text || '';
            });

            var picker = toolbar.querySelector('.ql-picker.ql-' + name);
            if (!picker) { return; }
            Array.prototype.forEach.call(picker.querySelectorAll('.ql-picker-item'), function (item, i) {
                var opt = select.options[i];
                if (!opt) { return; }
                item.setAttribute('data-value', opt.value || '');
                if (opt.textContent) { item.setAttribute('data-label', opt.textContent); }
                else { item.removeAttribute('data-label'); }
            });
            var label = picker.querySelector('.ql-picker-label');
            var selected = select.options[select.selectedIndex];
            if (label) {
                if (selected && selected.textContent) { label.setAttribute('data-label', selected.textContent); }
                else { label.removeAttribute('data-label'); }
            }
        });

        Array.prototype.forEach.call(toolbar.querySelectorAll('button'), function (btn) {
            var name = firstQlClass(btn);
            if (!name) { return; }
            if (!btn.innerHTML.trim() && EXTRA_ICONS[name]) { btn.innerHTML = EXTRA_ICONS[name]; }
            if (TITLES[name]) {
                btn.setAttribute('title', TITLES[name]);
                btn.setAttribute('aria-label', TITLES[name]);
            }
        });

        Array.prototype.forEach.call(toolbar.querySelectorAll('select'), function (sel) {
            var name = firstQlClass(sel);
            if (name && TITLES[name]) { sel.setAttribute('title', TITLES[name]); }
        });

        // The HTML-source button is appended last — move it next to Link / Image.
        var htmlBtn = toolbar.querySelector('button[title="Edit HTML source"], button[title="Show HTML source"]');
        if (htmlBtn) {
            var imgBtn = toolbar.querySelector('.ql-image');
            var wrap = htmlBtn.parentElement;
            if (imgBtn && imgBtn.parentElement && imgBtn.parentElement !== htmlBtn.parentElement) {
                imgBtn.parentElement.appendChild(htmlBtn);
                if (wrap && wrap !== htmlBtn.parentElement && wrap.children.length === 0) {
                    wrap.parentNode.removeChild(wrap);
                }
            }
        }
    }

    function tableModule(quill) {
        try { return quill.getModule('table'); } catch (e) { return null; }
    }

    function inTable(quill) {
        var m = tableModule(quill);
        if (!m) { return false; }
        try { var t = m.getTable(); return !!(t && t[3] >= 0); } catch (e) { return false; }
    }

    function quillOptions() {
        var modules = {
            toolbar: {
                container: [
                    [{ header: [1, 2, 3, 4, 5, 6, false] }],
                    [{ font: [false, 'serif', 'monospace'] }],
                    [{ size: [false, 'small', 'large', 'huge'] }],
                    ['bold', 'italic', 'underline', 'strike'],
                    [{ color: TEXT_COLORS }, { background: BG_COLORS }],
                    ['sub', 'super'],
                    [{ list: 'ordered' }, { list: 'bullet' }, { list: 'check' }],
                    [{ indent: '-1' }, { indent: '+1' }],
                    [{ align: [false, 'center', 'right', 'justify'] }],
                    ['blockquote', 'code-block'],
                    ['link', 'image'],
                    ['table', 'table-insert-row', 'table-insert-column', 'table-delete-row', 'table-delete-column', 'table-delete'],
                    ['clean'],
                    ['undo', 'redo']
                ],
                handlers: {
                    image: function () { uploadForEditor(this.quill); },
                    table: function () {
                        var q = this.quill, m = tableModule(q);
                        q.getSelection(true);
                        if (m) { m.insertTable(3, 3); }
                    },
                    'table-insert-row': function () {
                        var q = this.quill, m = tableModule(q);
                        if (!q.getSelection(true) || !inTable(q)) { return; }
                        if (m) { m.insertRowBelow(); }
                    },
                    'table-insert-column': function () {
                        var q = this.quill, m = tableModule(q);
                        if (!q.getSelection(true) || !inTable(q)) { return; }
                        if (m) { m.insertColumnRight(); }
                    },
                    'table-delete-row': function () {
                        var q = this.quill, m = tableModule(q);
                        if (!q.getSelection(true) || !inTable(q)) { return; }
                        if (m) { m.deleteRow(); }
                    },
                    'table-delete-column': function () {
                        var q = this.quill, m = tableModule(q);
                        if (!q.getSelection(true) || !inTable(q)) { return; }
                        if (m) { m.deleteColumn(); }
                    },
                    'table-delete': function () {
                        var q = this.quill, m = tableModule(q);
                        if (!q.getSelection(true) || !inTable(q)) { return; }
                        if (m) { m.deleteTable(); }
                    },
                    undo: function () {
                        var h = this.quill.history || this.quill.getModule('history');
                        if (h) { h.undo(); }
                    },
                    redo: function () {
                        var h = this.quill.history || this.quill.getModule('history');
                        if (h) { h.redo(); }
                    },
                    'code-block': function () {
                        var q = this.quill;
                        var range = q.getSelection(true);
                        if (!range) { return; }
                        var current = q.getFormat(range);
                        q.format('code-block', current['code-block'] ? false : true, 'user');
                    }
                }
            },
            table: {},
            history: { delay: 800, maxStack: 200, userOnly: true }
        };
        if (HTML_EDIT_OK) {
            modules.htmlEditButton = {
                msg: 'Edit the HTML source of this content, then press OK to put it back into the editor.',
                buttonTitle: 'Edit HTML source',
                closeOnClickOverlay: true
            };
        }
        return { theme: 'snow', modules: modules };
    }

    function uploadForEditor(quill) {
        pickImage().then(function (file) {
            return uploadFile(file);
        }).then(function (res) {
            insertImage(quill, res.url);
        }).catch(function (e) {
            console.error(e);
            alert('Upload failed: ' + e.message);
        });
    }

    function toQuillHtml(raw) {
        raw = (raw || '').trim();
        if (raw === '') { return ''; }
        if (/<[a-z][^>]*>/i.test(raw)) { return raw; }
        return raw.split(/\n{2,}/).map(function (p) {
            return '<p>' + p.replace(/\n/g, '<br>') + '</p>';
        }).join('');
    }

    // Load stored HTML through the clipboard matchers so Word markup is
    // cleaned, normalised and rebuilt as real Quill content.
    function loadHtml(quill, raw) {
        var html = toQuillHtml(raw || '');
        if (html === '') { return; }
        try {
            var ops = quill.clipboard.convert({ html: html });
            if (ops && ops.ops && ops.ops.length) {
                quill.setContents(ops, 'silent');
                return;
            }
        } catch (e) { console.warn('Could not import HTML, falling back', e); }
        quill.root.innerHTML = html;
    }

    /* ------------------------------------------------------------------ *
     * WCAG contrast guard — mirrors CmsHtml::ensureContrast() in the      *
     * browser so the admin sees the shipped colours while editing.        *
     * ------------------------------------------------------------------ */

    function parseCssColor(str) {
        if (!str) { return null; }
        str = String(str).trim().toLowerCase();
        if (str === 'transparent') { return null; }

        var m = /^#([0-9a-f]{3})$/.exec(str);
        if (m) {
            return { r: parseInt(m[1][0] + m[1][0], 16), g: parseInt(m[1][1] + m[1][1], 16), b: parseInt(m[1][2] + m[1][2], 16) };
        }
        m = /^#([0-9a-f]{6})([0-9a-f]{2})?$/.exec(str);
        if (m) {
            if (m[2] && parseInt(m[2], 16) < 128) { return null; }
            return { r: parseInt(m[1].slice(0, 2), 16), g: parseInt(m[1].slice(2, 4), 16), b: parseInt(m[1].slice(4, 6), 16) };
        }
        m = /^rgba?\(\s*([\d.]+)[\s,]+([\d.]+)[\s,]+([\d.]+)\s*(?:,\s*([\d.]+)\s*)?\)$/.exec(str);
        if (m) {
            if (m[4] !== undefined && parseFloat(m[4]) < 0.5) { return null; }
            return { r: Math.round(parseFloat(m[1])), g: Math.round(parseFloat(m[2])), b: Math.round(parseFloat(m[3])) };
        }
        if (str === 'white') { return { r: 255, g: 255, b: 255 }; }
        if (str === 'black') { return { r: 0, g: 0, b: 0 }; }
        return null;
    }

    function relativeLuminance(c) {
        function channel(v) {
            v /= 255;
            return v <= 0.03928 ? v / 12.92 : Math.pow((v + 0.055) / 1.055, 2.4);
        }
        return 0.2126 * channel(c.r) + 0.7152 * channel(c.g) + 0.0722 * channel(c.b);
    }

    function contrastRatio(a, b) {
        var l1 = relativeLuminance(a), l2 = relativeLuminance(b);
        var hi = Math.max(l1, l2), lo = Math.min(l1, l2);
        return (hi + 0.05) / (lo + 0.05);
    }

    var INKS = ['#0f172a', '#f8fafc', '#000000', '#ffffff'].map(function (hex) {
        return { hex: hex, rgb: parseCssColor(hex) };
    });

    // Preference order: site ink first, then the raw pair as a last resort.
    // Anything under 4.5:1 skips to the next candidate.
    function bestInk(bg) {
        var fallback = INKS[INKS.length - 1].hex;
        for (var i = 0; i < INKS.length; i++) {
            if (contrastRatio(INKS[i].rgb, bg) >= 4.5) { return INKS[i].hex; }
        }
        return fallback;
    }

    function backdropFor(key) {
        var el = container(key);
        var form = el && el.closest ? el.closest('form') : null;
        var sel = form ? form.querySelector('select[name="bg_color"]') : null;
        var value = sel ? String(sel.value || '').trim() : '';
        if (value && parseCssColor(value)) { return value; }
        return '#f8fafc'; // storefront bands alternate white / slate-50
    }

    function enforceContrast(quill, backdropHex) {
        var root = quill.root;
        var base = parseCssColor(backdropHex) || { r: 255, g: 255, b: 255 };

        function bgOf(node) {
            var current = node;
            while (current && current.nodeType === 1) {
                var color = parseCssColor(window.getComputedStyle(current).backgroundColor);
                if (color) { return color; }
                if (current === root) { break; }
                current = current.parentElement;
            }
            return base;
        }

        Array.prototype.forEach.call(root.querySelectorAll('*'), function (node) {
            var tag = node.tagName;
            if (tag === 'IMG' || tag === 'VIDEO' || tag === 'SVG' || tag === 'BR' || tag === 'IFRAME') { return; }
            if (node.classList.contains('ql-ui') || node.classList.contains('ql-clipboard')) { return; }

            var bg = bgOf(node);
            var fg = parseCssColor(window.getComputedStyle(node).color);
            if (fg && contrastRatio(fg, bg) >= 4.5) { return; }

            var ink = bestInk(bg);
            var own = parseCssColor(node.style.color);
            if (own && contrastRatio(own, bg) >= 4.5) { return; }
            node.style.color = ink;
        });
    }

    function refreshContrast(key) {
        var quill = CACHE[key];
        if (!quill) { return; }
        try { enforceContrast(quill, backdropFor(key)); }
        catch (e) { console.warn('Contrast pass failed', e); }
    }

    function editorHtml(quill) {
        var hasMedia = quill.root.querySelectorAll('img, video, iframe, table, ul, ol, blockquote').length > 0;
        if (quill.getText().trim() === '' && !hasMedia) { return ''; }
        return quill.root.innerHTML;
    }

    function container(key) { return document.getElementById('qc-' + key); }
    function field(key) { return document.getElementById('content-' + key); }

    function create(key) {
        if (CACHE[key]) { return CACHE[key]; }
        if (typeof Quill === 'undefined') { return null; }
        var el = container(key);
        if (!el) { return null; }

        // Wait until the panel is actually visible (Alpine x-show toggles it).
        if (el.offsetWidth === 0 && el.offsetHeight === 0) {
            if (!create.retries) { create.retries = {}; }
            create.retries[key] = (create.retries[key] || 0) + 1;
            if (create.retries[key] < 90) { requestAnimationFrame(function () { create(key); }); }
            return null;
        }
        delete create.retries[key];

        var quill = new Quill(el, quillOptions());
        CACHE[key] = quill;

        decorate(el);

        var ta = field(key);
        if (ta && ta.value.trim() !== '') {
            loadHtml(quill, ta.value);
        }

        var form = el.closest ? el.closest('form') : null;
        var bgSelect = form ? form.querySelector('select[name="bg_color"]') : null;
        if (bgSelect) {
            bgSelect.addEventListener('change', function () { refreshContrast(key); });
        }

        requestAnimationFrame(function () { refreshContrast(key); });

        return quill;
    }

    function sync(key) {
        var quill = CACHE[key];
        var ta = field(key);
        if (quill && ta) {
            refreshContrast(key);
            ta.value = editorHtml(quill);
        }
    }

    function isHidden(key) {
        var el = container(key);
        return !el || el.offsetParent === null;
    }

    // Runs cb(quill) as soon as the editor exists (it may be created a frame later,
    // because Alpine's x-show has to reveal the panel first).
    function withEditor(key, cb, attempt) {
        attempt = attempt || 0;
        var quill = CACHE[key] || create(key);
        if (quill) { cb(quill); return; }
        if (attempt < 90 && container(key)) {
            setTimeout(function () { withEditor(key, cb, attempt + 1); }, 50);
        }
    }

    window.landingOpenEditor = function (key) { create(key); };

    window.landingUploadImage = function (key) {
        withEditor(key, function (quill) {
            uploadForEditor(quill);
        });
    };

    // Switch an Add-form field from rich mode to the plain textarea.
    window.landingToPlain = function (key) {
        if (CACHE[key]) { sync(key); }
    };

    // Switch an Add-form field from the plain textarea back to rich mode.
    window.landingToRich = function (key) {
        withEditor(key, function (quill) {
            var ta = field(key);
            if (ta && ta.value.trim() !== '') {
                loadHtml(quill, ta.value);
                refreshContrast(key);
            }
        });
    };

    // Persist every visible editor into its <textarea> before any form submits.
    document.addEventListener('submit', function () {
        Object.keys(CACHE).forEach(function (key) {
            if (!isHidden(key)) { sync(key); }
        });
    }, true);

    var bootTries = 0;
    function boot() {
        if (typeof Quill === 'undefined') {
            if (++bootTries < 25) { setTimeout(boot, 120); }
            return;
        }
        if (container('new')) { create('new'); }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
})();
</script>
@endpush
