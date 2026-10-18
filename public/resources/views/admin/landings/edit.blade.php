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
        transition: background-color 0.25s ease, border-color 0.25s ease;
    }
    .landing-editor .ql-editor {
        min-height: 150px;
        padding: 12px 14px;
        line-height: 1.6;
        transition: background-color 0.25s ease, color 0.25s ease;
    }
    .landing-editor--tall .ql-editor { min-height: 340px; }
    .landing-editor .ql-editor h1 { font-size: 1.9em; font-weight: 800; line-height: 1.25; margin-top: 1.2rem; margin-bottom: 0.5rem; }
    .landing-editor .ql-editor h2 { font-size: 1.55em; font-weight: 800; line-height: 1.25; margin-top: 1.1rem; margin-bottom: 0.5rem; }
    .landing-editor .ql-editor h3 { font-size: 1.3em; font-weight: 800; line-height: 1.25; margin-top: 1rem; margin-bottom: 0.4rem; }
    .landing-editor .ql-editor h4 { font-size: 1.12em; font-weight: 700; line-height: 1.25; }
    .landing-editor .ql-editor h5 { font-size: 1.02em; font-weight: 700; }
    .landing-editor .ql-editor h6 { font-size: .95em; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; }

    /* Light Theme (Default / White / Light Slate / Soft Gray) */
    .landing-editor:not(.landing-editor--dark) .ql-editor {
        color: #0f172a;
    }
    .landing-editor:not(.landing-editor--dark) .ql-editor h1,
    .landing-editor:not(.landing-editor--dark) .ql-editor h2,
    .landing-editor:not(.landing-editor--dark) .ql-editor h3,
    .landing-editor:not(.landing-editor--dark) .ql-editor h4,
    .landing-editor:not(.landing-editor--dark) .ql-editor h5,
    .landing-editor:not(.landing-editor--dark) .ql-editor h6 {
        color: #0f172a;
    }
    .landing-editor:not(.landing-editor--dark) .ql-editor strong,
    .landing-editor:not(.landing-editor--dark) .ql-editor b {
        color: #0f172a;
        font-weight: 800;
    }
    .landing-editor:not(.landing-editor--dark) .ql-editor a {
        color: #1d4ed8;
        text-decoration: underline;
    }
    .landing-editor:not(.landing-editor--dark) .ql-editor blockquote {
        background: #f8fafc;
        border-left: 4px solid #cbd5e1;
        border-radius: 0 6px 6px 0;
        color: #334155;
        padding: .5rem .85rem;
    }
    .landing-editor:not(.landing-editor--dark) .ql-editor pre.ql-syntax { background: #0f172a; color: #e2e8f0; }
    .landing-editor:not(.landing-editor--dark) .ql-editor code { background: #f1f5f9; color: #0f172a; }
    .landing-editor:not(.landing-editor--dark) .ql-editor table {
        border-collapse: collapse;
        margin: 10px 0;
        table-layout: auto;
        width: 100%;
        border: 1px solid #cbd5e1 !important;
    }
    .landing-editor:not(.landing-editor--dark) .ql-editor th,
    .landing-editor:not(.landing-editor--dark) .ql-editor td {
        border: 1px solid #cbd5e1;
        padding: 6px 8px;
        text-align: left;
        vertical-align: top;
        word-break: break-word;
        color: #0f172a;
    }
    .landing-editor:not(.landing-editor--dark) .ql-editor th { background: #f1f5f9; font-weight: 700; color: #0f172a; }
    .landing-editor:not(.landing-editor--dark) .ql-editor.ql-blank::before {
        font-style: normal;
        color: #94a3b8;
    }

    /* Dark Theme (matches customer storefront when Dark Navy #0f172a or Slate Navy #1e293b is chosen) */
    .landing-editor.landing-editor--dark .ql-container.ql-snow {
        border-color: #334155 !important;
    }
    .landing-editor.landing-editor--dark .ql-editor {
        color: #f8fafc !important;
    }
    .landing-editor.landing-editor--dark .ql-editor h1,
    .landing-editor.landing-editor--dark .ql-editor h2,
    .landing-editor.landing-editor--dark .ql-editor h3,
    .landing-editor.landing-editor--dark .ql-editor h4,
    .landing-editor.landing-editor--dark .ql-editor h5,
    .landing-editor.landing-editor--dark .ql-editor h6 {
        color: #f8fafc !important;
    }
    .landing-editor.landing-editor--dark .ql-editor p,
    .landing-editor.landing-editor--dark .ql-editor div,
    .landing-editor.landing-editor--dark .ql-editor li {
        color: #f8fafc;
    }
    .landing-editor.landing-editor--dark .ql-editor strong,
    .landing-editor.landing-editor--dark .ql-editor b {
        color: #ffffff !important;
        font-weight: 800;
    }
    .landing-editor.landing-editor--dark .ql-editor a {
        color: #93c5fd !important;
        text-decoration: underline;
    }
    .landing-editor.landing-editor--dark .ql-editor blockquote {
        background: #1e293b !important;
        border-left: 4px solid #64748b !important;
        color: #cbd5e1 !important;
        border-radius: 0 6px 6px 0;
        padding: .5rem .85rem;
    }
    .landing-editor.landing-editor--dark .ql-editor pre.ql-syntax { background: #020617 !important; color: #e2e8f0 !important; }
    .landing-editor.landing-editor--dark .ql-editor code { background: #1e293b !important; color: #e2e8f0 !important; }
    .landing-editor.landing-editor--dark .ql-editor table {
        border-collapse: collapse;
        margin: 10px 0;
        table-layout: auto;
        width: 100%;
        border: 1px solid #475569 !important;
    }
    .landing-editor.landing-editor--dark .ql-editor th,
    .landing-editor.landing-editor--dark .ql-editor td {
        border: 1px solid #475569 !important;
        padding: 6px 8px;
        text-align: left;
        vertical-align: top;
        word-break: break-word;
        color: #e2e8f0 !important;
    }
    .landing-editor.landing-editor--dark .ql-editor th { background: #1e293b !important; font-weight: 700; color: #f1f5f9 !important; }
    .landing-editor.landing-editor--dark .ql-editor.ql-blank::before {
        font-style: normal;
        color: #94a3b8 !important;
    }
    .landing-editor.landing-editor--dark .ql-editor li::marker,
    .landing-editor.landing-editor--dark .ql-editor li > .ql-ui:before {
        color: #93c5fd !important;
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

<form action="/{{ $adminBase }}/landings/{{ $page->id }}/update" method="POST" id="landing-main-form" class="space-y-8">
    @csrf
    <input type="hidden" name="faqs_synced" value="1">
    <input type="hidden" name="blocks_synced" value="1">

    {{-- Page Meta details --}}
    <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-4 mb-6 pb-4 border-b border-slate-100">
            <div>
                <h2 class="text-lg font-bold text-slate-800">Page Settings</h2>
                <p class="text-xs text-slate-500 mt-0.5">Manage URL, SEO metadata, offer integration, and landing page FAQs.</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="/en/{{ $page->slug }}" target="_blank" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-bold transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    <span>View Live Page</span>
                </a>
                <button type="submit" class="inline-flex items-center gap-2 bg-slate-800 hover:bg-slate-900 text-white font-bold px-5 py-2 rounded-lg text-xs transition shadow-sm">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>Save Settings</span>
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Title</label>
                <input type="text" name="title" value="{{ $page->title }}" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm bg-white">
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Slug</label>
                <input type="text" name="slug" value="{{ $page->slug }}" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm bg-white">
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">SEO Title</label>
                <input type="text" name="meta_title" value="{{ $page->meta_title }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm bg-white">
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">SEO Description</label>
                <textarea name="meta_description" rows="2" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm bg-white">{{ $page->meta_description }}</textarea>
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Published Status</label>
                <select name="published" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm bg-white">
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
                    <span class="text-xs text-slate-400" x-show="activeSectionTab === 'faq'">Page FAQs (Saved automatically with Save Settings)</span>
                </div>

                {{-- 1. Product Content (Shown when Product option active) --}}
                <div x-show="activeSectionTab === 'product'">
                    <p class="text-xs text-slate-500 mb-4">Select the product that this landing page's CTA button will navigate to.</p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Shopify Product</label>
                            <select name="shopify_product_id" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm bg-white">
                                <option value="">— none —</option>
                                @foreach($productChoices as $value => $title)
                                    <option value="{{ $value }}" @selected($storedProduct === $value)>{{ $title }}</option>
                                @endforeach
                            </select>
                            <p class="text-xs text-slate-400 mt-1">Visitors clicking the CTA button will be taken directly to this product's details page.</p>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">CTA Label</label>
                            <input type="text" name="cta_label" value="{{ $page->cta_label }}" placeholder="Shop Now" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm bg-white">
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
        </div>
    </div>

    {{-- Content Blocks section --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {{-- Section Overview & Unified Save (Left Column) --}}
        <div class="space-y-6">
            {{-- Unified Save Card --}}
            <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm space-y-4">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider">Unified Single-Click Save</h3>
                </div>
                <p class="text-xs text-slate-500 leading-relaxed">
                    All page settings, FAQs, and layout block updates are kept in memory while editing. Click below to save everything to the database at once.
                </p>
                <button type="submit" class="w-full bg-slate-800 hover:bg-slate-900 text-white font-bold py-3 rounded-lg text-sm transition shadow-sm flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>Save Settings</span>
                </button>
            </div>

            {{-- Page Status & Live Link --}}
            <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm space-y-3">
                <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider">Page Information</h3>
                <div class="text-xs space-y-2 text-slate-600">
                    <div class="flex justify-between items-center py-1 border-b border-slate-100">
                        <span class="text-slate-400">Status</span>
                        <span class="font-bold {{ $page->published ? 'text-emerald-600' : 'text-amber-600' }}">
                            {{ $page->published ? '● Published' : '○ Draft' }}
                        </span>
                    </div>
                    <div class="flex justify-between items-center py-1 border-b border-slate-100">
                        <span class="text-slate-400">Total Blocks</span>
                        <span class="font-bold text-slate-800">{{ $page->pageBlocks->count() }} blocks</span>
                    </div>
                    <div class="flex justify-between items-center py-1">
                        <span class="text-slate-400">URL Slug</span>
                        <span class="font-mono text-slate-700 text-[11px]">/en/{{ $page->slug }}</span>
                    </div>
                </div>
                <a href="/en/{{ $page->slug }}" target="_blank" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-bold transition mt-2">
                    <span>Preview Storefront</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                </a>
            </div>
        </div>

        {{-- Layout Blocks List (Right Column) --}}
        <div class="lg:col-span-2 bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden"
             x-data="{
                showAddBlock: false,
                blockType: 'rich',
                newAlign: 'content_only',
                newImageUrl: ''
             }">
            <div class="px-6 py-4 bg-slate-50 border-b border-slate-200 flex justify-between items-center gap-4">
                <div>
                    <h2 class="text-lg font-bold text-slate-800">Page Content Layout Blocks</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Manage and arrange sections. Individual save buttons are removed; click <strong>Save Settings</strong> to persist all blocks.</p>
                </div>
                <button type="button"
                        @click="showAddBlock = !showAddBlock; if (showAddBlock) { $nextTick(() => { if (!plainIs(blockType)) { landingOpenEditor('new'); } }); }"
                        class="inline-flex items-center gap-1.5 bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold px-3.5 py-2 rounded-lg transition shadow-sm flex-shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span x-text="showAddBlock ? 'Close Add Form' : '+ Add Block'">+ Add Block</span>
                </button>
            </div>

            {{-- New Block Creation Form (Part of the unified form) --}}
            <div x-show="showAddBlock" x-cloak class="p-6 bg-slate-50 border-b border-slate-200 space-y-4">
                <div class="flex justify-between items-center pb-2 border-b border-slate-200">
                    <span class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        New Content Block (Will be created on Save Settings)
                    </span>
                    <button type="button" @click="showAddBlock = false" class="text-xs text-slate-400 hover:text-slate-600 font-bold">&times; Close</button>
                </div>

                <input type="hidden" name="blocks[new_0][id]" value="">
                <input type="hidden" name="blocks[new_0][sort_order]" value="{{ $nextSortOrder }}">
                <input type="hidden" name="blocks[new_0][visible]" value="1">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1">Block Type</label>
                        <select name="blocks[new_0][block_type]" x-model="blockType" @change="plainIs(blockType) ? landingToPlain('new') : landingToRich('new')" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm bg-white">
                            <option value="rich">Rich Text / Custom Content</option>
                            <option value="benefits">Benefits</option>
                            <option value="how-it-works">How It Works</option>
                            <option value="testimonials">Testimonials</option>
                            <option value="faqs">FAQs Accordion</option>
                            <option value="cta">Call to Action (uses page offer)</option>
                            <option value="video">Video (YouTube/Vimeo URL)</option>
                            <option value="comparison">Comparison (HTML table)</option>
                            <option value="bundle">Bundle (components + total)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1">Block Title</label>
                        <input type="text" name="blocks[new_0][title]" placeholder="e.g. Why Choose Our Product" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm bg-white">
                    </div>
                </div>

                {{-- New Block Image Alignment Options --}}
                <div class="bg-white p-4 rounded-xl border border-slate-200 space-y-3">
                    <div class="flex items-center justify-between">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                            Image Alignment & Layout
                        </label>
                        <span class="text-xs font-bold text-navy-700 bg-navy-50 px-2.5 py-0.5 rounded-full"
                              x-text="newAlign === 'image_left' ? 'Image Left + Content Right' : (newAlign === 'image_right' ? 'Content Left + Image Right' : 'Content Only (Full Width)')">
                        </span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                        <label class="flex items-center gap-2.5 p-3 rounded-lg border cursor-pointer transition text-xs"
                               :class="newAlign === 'content_only' ? 'border-navy-600 bg-navy-50/60 text-navy-900 font-bold shadow-sm' : 'border-slate-200 bg-white hover:border-slate-300 text-slate-700'">
                            <input type="radio" name="blocks[new_0][display_position]" value="content_only" x-model="newAlign" class="text-navy-600 focus:ring-navy-500">
                            <div>
                                <span class="block font-bold">Content Only</span>
                                <span class="text-slate-400 font-normal text-[11px]">No side image</span>
                            </div>
                        </label>

                        <label class="flex items-center gap-2.5 p-3 rounded-lg border cursor-pointer transition text-xs"
                               :class="newAlign === 'image_left' ? 'border-navy-600 bg-navy-50/60 text-navy-900 font-bold shadow-sm' : 'border-slate-200 bg-white hover:border-slate-300 text-slate-700'">
                            <input type="radio" name="blocks[new_0][display_position]" value="image_left" x-model="newAlign" class="text-navy-600 focus:ring-navy-500">
                            <div>
                                <span class="block font-bold">Image Left</span>
                                <span class="text-slate-400 font-normal text-[11px]">Image left + Content right</span>
                            </div>
                        </label>

                        <label class="flex items-center gap-2.5 p-3 rounded-lg border cursor-pointer transition text-xs"
                               :class="newAlign === 'image_right' ? 'border-navy-600 bg-navy-50/60 text-navy-900 font-bold shadow-sm' : 'border-slate-200 bg-white hover:border-slate-300 text-slate-700'">
                            <input type="radio" name="blocks[new_0][display_position]" value="image_right" x-model="newAlign" class="text-navy-600 focus:ring-navy-500">
                            <div>
                                <span class="block font-bold">Image Right</span>
                                <span class="text-slate-400 font-normal text-[11px]">Content left + Image right</span>
                            </div>
                        </label>
                    </div>

                    {{-- Side Image URL and Upload --}}
                    <div x-show="newAlign === 'image_left' || newAlign === 'image_right'" x-cloak class="mt-3 pt-3 border-t border-slate-100 space-y-2">
                        <label class="block text-xs font-bold text-slate-600">
                            Side Image <span class="text-rose-500">*</span>
                        </label>
                        <div class="flex items-center gap-2">
                            <input type="text"
                                   id="image-url-new"
                                   name="blocks[new_0][image_url]"
                                   x-model="newImageUrl"
                                   placeholder="https://... or click Upload Picture"
                                   class="flex-1 rounded-lg border border-slate-300 px-3 py-2 text-sm bg-white focus:border-navy-500 focus:ring-1 focus:ring-navy-500">
                            <button type="button"
                                    onclick="uploadSideImage('new')"
                                    class="inline-flex items-center gap-1.5 bg-navy-600 hover:bg-navy-700 text-white text-xs font-bold px-3.5 py-2.5 rounded-lg transition shadow-sm flex-shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                <span>Upload Picture</span>
                            </button>
                        </div>

                        <template x-if="newImageUrl && newImageUrl.trim() !== ''">
                            <div class="flex items-center gap-3 pt-2">
                                <div class="relative w-36 h-24 rounded-lg overflow-hidden border border-slate-300 shadow-sm bg-slate-100 flex-shrink-0">
                                    <img :src="newImageUrl" alt="Preview" class="w-full h-full object-cover">
                                    <button type="button"
                                            @click="newImageUrl = ''"
                                            class="absolute top-1 right-1 bg-rose-600 text-white rounded-full p-1 opacity-80 hover:opacity-100 transition shadow"
                                            title="Remove picture">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>
                                <div class="text-xs text-slate-500">
                                    <span class="font-bold text-slate-700 block">Image Preview</span>
                                    <span x-text="newAlign === 'image_left' ? 'This image will be placed on the LEFT side of the text.' : 'This image will be placed on the RIGHT side of the text.'"></span>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1">
                        Block Content
                        <span class="text-slate-400 font-normal" x-show="!plainIs(blockType)">— rich text (formatting, images, tables)</span>
                        <span class="text-slate-400 font-normal" x-show="plainIs(blockType)" x-cloak x-text="blockType === 'video' ? '— video URL only' : '— raw HTML table'"></span>
                    </label>

                    <!-- Plain mode (video URL / comparison HTML) -->
                    <div x-show="plainIs(blockType)" x-cloak>
                        <textarea name="blocks[new_0][content]" id="content-new-plain" x-bind:disabled="!plainIs(blockType)" rows="4" placeholder="https://www.youtube.com/watch?v=… or paste an HTML table" class="landing-preview w-full rounded-lg border border-slate-300 px-3 py-2 text-sm bg-white"></textarea>
                        <p class="text-xs text-slate-400 mt-1">Plain value — no formatting toolbar for this block type.</p>
                    </div>

                    <!-- Rich text mode -->
                    <div x-show="!plainIs(blockType)" x-cloak>
                        <div class="landing-editor landing-editor--tall" id="qc-new"></div>
                        <textarea name="blocks[new_0][content]" id="content-new" rows="4" class="hidden" x-bind:disabled="plainIs(blockType)"></textarea>
                        <div class="flex items-center justify-between gap-3 mt-2">
                            <button type="button" onclick="landingUploadImage('new')" class="inline-flex items-center gap-1.5 bg-navy-600 hover:bg-navy-700 text-white text-xs font-bold px-3 py-1.5 rounded-lg transition">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                Upload Picture
                            </button>
                            <span class="text-xs text-slate-400">JPG / PNG / WEBP &middot; max 10 MB</span>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1">Background Style</label>
                        <select name="blocks[new_0][bg_color]" onchange="landingApplyBg('new')" class="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-xs bg-white">
                            <option value="">Default (Alternating)</option>
                            <option value="#ffffff">White (#ffffff)</option>
                            <option value="#f8fafc">Light Slate (#f8fafc)</option>
                            <option value="#f1f5f9">Soft Gray (#f1f5f9)</option>
                            <option value="#0f172a">Dark Navy (#0f172a)</option>
                            <option value="#1e293b">Slate Navy (#1e293b)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1">Sort Order</label>
                        <input type="number" name="blocks[new_0][sort_order]" value="{{ $nextSortOrder }}" class="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-sm bg-white">
                    </div>
                </div>

                <div class="flex items-center justify-between pt-3 border-t border-slate-200">
                    <span class="text-xs text-slate-500">This block will be added and saved when you click <strong>Save Settings</strong>.</span>
                    <button type="button" @click="showAddBlock = false" class="bg-slate-200 hover:bg-slate-300 text-slate-700 text-xs font-bold px-4 py-2 rounded-lg transition">
                        Done
                    </button>
                </div>
            </div>

            {{-- Existing Blocks List --}}
            <div class="divide-y divide-slate-200">
                @forelse($page->pageBlocks as $index => $block)
                    @php
                        $isPlainBlock = in_array($block->block_type, $plainBlockTypes, true);
                        $previewText = trim(strip_tags((string) $block->content));
                        $previewText = $previewText === '' ? '—' : \Illuminate\Support\Str::limit($previewText, 120);
                        $blockAlign = $block->display_position ?: 'content_only';
                    @endphp
                    <div class="p-6 space-y-4"
                         x-data="{
                            editing: false,
                            align: '{{ $blockAlign }}',
                            imageUrl: '{{ $block->image_url ?: '' }}',
                            removed: false
                         }"
                         x-show="!removed">

                        {{-- Hidden block inputs --}}
                        <input type="hidden" name="deleted_blocks[]" :value="removed ? '{{ $block->id }}' : ''" :disabled="!removed">
                        <input type="hidden" name="blocks[{{ $index }}][id]" value="{{ $block->id }}" :disabled="removed">
                        <input type="hidden" name="blocks[{{ $index }}][sort_order]" value="{{ $block->sort_order ?? ($index + 1) }}" :disabled="removed">
                        <input type="hidden" name="blocks[{{ $index }}][block_type]" value="{{ $block->block_type }}" :disabled="removed">

                        <div class="flex justify-between items-start gap-4">
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="px-2.5 py-0.5 rounded bg-navy-50 text-navy-700 text-xs font-bold uppercase tracking-wider">
                                        {{ $block->block_type }}
                                    </span>
                                    <span class="px-2 py-0.5 rounded text-xs font-semibold"
                                          :class="align === 'content_only' ? 'bg-slate-100 text-slate-600' : 'bg-emerald-50 text-emerald-800 font-bold border border-emerald-200'"
                                          x-text="align === 'image_left' ? '◧ Image Left' : (align === 'image_right' ? '◨ Image Right' : '☰ Content Only')">
                                    </span>
                                    @if(!empty($block->bg_color))
                                        <span class="px-2 py-0.5 rounded text-xs font-mono border"
                                              style="background-color: {{ $block->bg_color }}; color: {{ in_array($block->bg_color, ['#0f172a', '#1e293b']) ? '#f8fafc' : '#0f172a' }};">
                                            {{ $block->bg_color }}
                                        </span>
                                    @endif
                                </div>

                                @if($block->title)
                                    <strong class="block text-slate-800 text-base mt-2">{{ $block->title }}</strong>
                                @endif
                                <div class="text-slate-500 text-xs mt-1 font-mono max-w-lg truncate">{{ $previewText }}</div>
                                <span class="inline-block text-xs font-semibold text-slate-400 mt-2">Visible: {{ $block->visible ? 'Yes' : 'No' }}</span>
                            </div>

                            <div class="flex items-center gap-3">
                                <button type="button"
                                        @click="editing = !editing; if (editing) { landingOpenEditor('b{{ $block->id }}'); }"
                                        class="text-navy-600 hover:text-navy-800 text-sm font-bold flex items-center gap-1">
                                    <span x-text="editing ? '▲ Close' : '▼ Edit Block'">▼ Edit Block</span>
                                </button>
                                <button type="button"
                                        @click="if (confirm('Delete this block? Changes will take effect when you click Save Settings.')) { removed = true; }"
                                        class="text-rose-600 hover:text-rose-800 text-sm font-bold">
                                    Delete
                                </button>
                            </div>
                        </div>

                        {{-- Edit Block Body --}}
                        <div x-show="editing" x-cloak class="p-5 bg-slate-50 rounded-xl border border-slate-200 space-y-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-600 mb-1">Block Title</label>
                                <input type="text"
                                       name="blocks[{{ $index }}][title]"
                                       value="{{ $block->title }}"
                                       :disabled="removed"
                                       class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm bg-white focus:border-navy-500 focus:ring-1 focus:ring-navy-500">
                            </div>

                            {{-- Image Alignment & Media Option --}}
                            <div class="bg-white p-4 rounded-xl border border-slate-200 space-y-3">
                                <div class="flex items-center justify-between">
                                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                                        Image Alignment & Layout
                                    </label>
                                    <span class="text-xs font-bold text-navy-700 bg-navy-50 px-2.5 py-0.5 rounded-full"
                                          x-text="align === 'image_left' ? 'Image Left + Content Right' : (align === 'image_right' ? 'Content Left + Image Right' : 'Content Only (Full Width)')">
                                    </span>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                                    <label class="flex items-center gap-2.5 p-3 rounded-lg border cursor-pointer transition text-xs"
                                           :class="align === 'content_only' ? 'border-navy-600 bg-navy-50/60 text-navy-900 font-bold shadow-sm' : 'border-slate-200 bg-white hover:border-slate-300 text-slate-700'">
                                        <input type="radio"
                                               name="blocks[{{ $index }}][display_position]"
                                               value="content_only"
                                               x-model="align"
                                               :disabled="removed"
                                               class="text-navy-600 focus:ring-navy-500">
                                        <div>
                                            <span class="block font-bold">Content Only</span>
                                            <span class="text-slate-400 font-normal text-[11px]">No side image</span>
                                        </div>
                                    </label>

                                    <label class="flex items-center gap-2.5 p-3 rounded-lg border cursor-pointer transition text-xs"
                                           :class="align === 'image_left' ? 'border-navy-600 bg-navy-50/60 text-navy-900 font-bold shadow-sm' : 'border-slate-200 bg-white hover:border-slate-300 text-slate-700'">
                                        <input type="radio"
                                               name="blocks[{{ $index }}][display_position]"
                                               value="image_left"
                                               x-model="align"
                                               :disabled="removed"
                                               class="text-navy-600 focus:ring-navy-500">
                                        <div>
                                            <span class="block font-bold">Image Left</span>
                                            <span class="text-slate-400 font-normal text-[11px]">Image left + Content right</span>
                                        </div>
                                    </label>

                                    <label class="flex items-center gap-2.5 p-3 rounded-lg border cursor-pointer transition text-xs"
                                           :class="align === 'image_right' ? 'border-navy-600 bg-navy-50/60 text-navy-900 font-bold shadow-sm' : 'border-slate-200 bg-white hover:border-slate-300 text-slate-700'">
                                        <input type="radio"
                                               name="blocks[{{ $index }}][display_position]"
                                               value="image_right"
                                               x-model="align"
                                               :disabled="removed"
                                               class="text-navy-600 focus:ring-navy-500">
                                        <div>
                                            <span class="block font-bold">Image Right</span>
                                            <span class="text-slate-400 font-normal text-[11px]">Content left + Image right</span>
                                        </div>
                                    </label>
                                </div>

                                {{-- Side Image URL and Upload (shown when Image Left or Image Right) --}}
                                <div x-show="align === 'image_left' || align === 'image_right'" x-cloak class="mt-3 pt-3 border-t border-slate-100 space-y-2">
                                    <label class="block text-xs font-bold text-slate-600">
                                        Side Image <span class="text-rose-500">*</span>
                                    </label>
                                    <div class="flex items-center gap-2">
                                        <input type="text"
                                               id="image-url-{{ $index }}"
                                               name="blocks[{{ $index }}][image_url]"
                                               x-model="imageUrl"
                                               :disabled="removed"
                                               placeholder="https://... or click Upload Picture"
                                               class="flex-1 rounded-lg border border-slate-300 px-3 py-2 text-sm bg-white focus:border-navy-500 focus:ring-1 focus:ring-navy-500">
                                        <button type="button"
                                                onclick="uploadSideImage('{{ $index }}')"
                                                class="inline-flex items-center gap-1.5 bg-navy-600 hover:bg-navy-700 text-white text-xs font-bold px-3.5 py-2.5 rounded-lg transition shadow-sm flex-shrink-0">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                            <span>Upload Picture</span>
                                        </button>
                                    </div>

                                    <template x-if="imageUrl && imageUrl.trim() !== ''">
                                        <div class="flex items-center gap-3 pt-2">
                                            <div class="relative w-36 h-24 rounded-lg overflow-hidden border border-slate-300 shadow-sm bg-slate-100 flex-shrink-0">
                                                <img :src="imageUrl" alt="Preview" class="w-full h-full object-cover">
                                                <button type="button"
                                                        @click="imageUrl = ''"
                                                        class="absolute top-1 right-1 bg-rose-600 text-white rounded-full p-1 opacity-80 hover:opacity-100 transition shadow"
                                                        title="Remove picture">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                </button>
                                            </div>
                                            <div class="text-xs text-slate-500">
                                                <span class="font-bold text-slate-700 block">Image Preview</span>
                                                <span x-text="align === 'image_left' ? 'This image will be placed on the LEFT side of the text.' : 'This image will be placed on the RIGHT side of the text.'"></span>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-500 mb-1">
                                    Block Content
                                    <span class="text-slate-400 font-normal">{{ $isPlainBlock ? '— plain value' : '— rich text' }}</span>
                                </label>

                                @if($isPlainBlock)
                                    <textarea name="blocks[{{ $index }}][content]"
                                              id="content-b{{ $block->id }}"
                                              rows="3"
                                              :disabled="removed"
                                              class="landing-preview w-full rounded-lg border border-slate-300 px-3 py-1.5 text-sm"
                                              placeholder="{{ $block->block_type === 'video' ? 'https://www.youtube.com/watch?v=…' : '<table>…</table>' }}">{{ \App\Support\CmsHtml::cleanMediaUrls($block->content) }}</textarea>
                                @else
                                    <div class="landing-editor" id="qc-b{{ $block->id }}"></div>
                                    <textarea name="blocks[{{ $index }}][content]"
                                              id="content-b{{ $block->id }}"
                                              rows="3"
                                              :disabled="removed"
                                              class="hidden">{{ \App\Support\CmsHtml::cleanMediaUrls($block->content) }}</textarea>
                                    <div class="flex items-center justify-between gap-3 mt-2">
                                        <button type="button" onclick="landingUploadImage('b{{ $block->id }}')" class="inline-flex items-center gap-1.5 bg-navy-600 hover:bg-navy-700 text-white text-xs font-bold px-3 py-1.5 rounded-lg transition">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                            Upload Picture
                                        </button>
                                        <span class="text-xs text-slate-400">JPG / PNG / WEBP &middot; max 10 MB</span>
                                    </div>
                                @endif
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-slate-500 mb-1">Background Style</label>
                                    <select name="blocks[{{ $index }}][bg_color]"
                                            onchange="landingApplyBg('b{{ $block->id }}')"
                                            :disabled="removed"
                                            class="w-full rounded-lg border border-slate-300 px-2 py-1.5 text-xs bg-white">
                                        <option value="" {{ empty($block->bg_color) ? 'selected' : '' }}>Default (Alternating)</option>
                                        <option value="#ffffff" {{ $block->bg_color === '#ffffff' ? 'selected' : '' }}>White (#ffffff)</option>
                                        <option value="#f8fafc" {{ $block->bg_color === '#f8fafc' ? 'selected' : '' }}>Light Slate (#f8fafc)</option>
                                        <option value="#f1f5f9" {{ $block->bg_color === '#f1f5f9' ? 'selected' : '' }}>Soft Gray (#f1f5f9)</option>
                                        <option value="#0f172a" {{ $block->bg_color === '#0f172a' ? 'selected' : '' }}>Dark Navy (#0f172a)</option>
                                        <option value="#1e293b" {{ $block->bg_color === '#1e293b' ? 'selected' : '' }}>Slate Navy (#1e293b)</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-500 mb-1">Visible</label>
                                    <select name="blocks[{{ $index }}][visible]"
                                            :disabled="removed"
                                            class="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-sm bg-white">
                                        <option value="1" {{ $block->visible ? 'selected' : '' }}>Yes</option>
                                        <option value="0" {{ !$block->visible ? 'selected' : '' }}>No</option>
                                    </select>
                                </div>
                            </div>

                            {{-- Notice: Changes are held in memory until Save Settings --}}
                            <div class="flex items-center justify-between pt-3 border-t border-slate-200">
                                <span class="text-xs text-slate-500 flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                    <span>Changes auto-held. Click <strong>Save Settings</strong> to save to database.</span>
                                </span>
                                <button type="button" @click="editing = false" class="bg-slate-200 hover:bg-slate-300 text-slate-700 text-xs font-bold px-4 py-1.5 rounded-lg transition">
                                    Done
                                </button>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="px-6 py-8 text-center text-slate-400">No layout blocks added yet. Click &ldquo;+ Add Block&rdquo; above to create one.</div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Bottom Sticky Save Settings Bar --}}
    <div class="sticky bottom-4 z-30 bg-slate-900/95 backdrop-blur text-white px-6 py-4 rounded-2xl shadow-2xl flex flex-wrap items-center justify-between gap-4 border border-slate-700">
        <div class="flex items-center gap-3">
            <span class="w-3 h-3 rounded-full bg-emerald-400 animate-pulse"></span>
            <div>
                <div class="text-sm font-bold text-white">Ready to save your landing page?</div>
                <div class="text-xs text-slate-400">Click <strong>Save Settings</strong> to persist all page settings, FAQs, and layout blocks together.</div>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <a href="/en/{{ $page->slug }}" target="_blank" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-bold rounded-lg border border-slate-600 transition">
                View Live Page ↗
            </a>
            <button type="submit" class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-500 text-white font-bold px-6 py-2.5 rounded-lg text-sm transition shadow-lg">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>Save Settings</span>
            </button>
        </div>
    </div>
</form>
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
        return '#ffffff'; // Default is clean white
    }

    function applyEditorTheme(key) {
        var el = container(key);
        if (!el) { return; }
        var bg = backdropFor(key);
        var parsed = parseCssColor(bg) || { r: 255, g: 255, b: 255 };
        var lum = (0.2126 * parsed.r + 0.7152 * parsed.g + 0.0722 * parsed.b) / 255;
        var isDark = lum < 0.45; // Dark Navy (#0f172a), Slate Navy (#1e293b)

        var qlContainer = el.querySelector('.ql-container');
        var qlEditor = el.querySelector('.ql-editor');

        if (qlContainer) {
            qlContainer.style.setProperty('background-color', bg, 'important');
        }
        if (qlEditor) {
            qlEditor.style.setProperty('background-color', bg, 'important');
            qlEditor.style.setProperty('color', isDark ? '#f8fafc' : '#0f172a', 'important');
        }

        if (isDark) {
            el.classList.add('landing-editor--dark');
        } else {
            el.classList.remove('landing-editor--dark');
        }

        var quill = CACHE[key];
        if (quill) {
            try { enforceContrast(quill, bg); }
            catch (e) { console.warn('Contrast pass failed', e); }
        }
    }

    function enforceContrast(quill, backdropHex) {
        var root = quill.root;
        var base = parseCssColor(backdropHex) || { r: 255, g: 255, b: 255 };
        var lum = (0.2126 * base.r + 0.7152 * base.g + 0.0722 * base.b) / 255;
        var isDark = lum < 0.45;

        Array.prototype.forEach.call(root.querySelectorAll('*'), function (node) {
            var tag = node.tagName;
            if (tag === 'IMG' || tag === 'VIDEO' || tag === 'SVG' || tag === 'BR' || tag === 'IFRAME') { return; }
            if (node.classList.contains('ql-ui') || node.classList.contains('ql-clipboard')) { return; }

            var ownColor = node.style && node.style.color;
            if (ownColor) {
                var own = parseCssColor(ownColor);
                if (own && contrastRatio(own, base) < 3.0) {
                    node.style.color = isDark ? '#f8fafc' : '#0f172a';
                }
            }
        });
    }

    function refreshContrast(key) {
        applyEditorTheme(key);
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

        // Keep textarea constantly in sync as the user types
        quill.on('text-change', function () {
            if (ta) {
                ta.value = editorHtml(quill);
            }
        });

        var form = el.closest ? el.closest('form') : null;
        var bgSelect = form ? form.querySelector('select[name="bg_color"]') : null;
        if (bgSelect) {
            bgSelect.addEventListener('change', function () { applyEditorTheme(key); });
            bgSelect.addEventListener('input', function () { applyEditorTheme(key); });
        }

        requestAnimationFrame(function () { applyEditorTheme(key); });
        setTimeout(function () { applyEditorTheme(key); }, 80);

        return quill;
    }

    function sync(key) {
        var quill = CACHE[key];
        var ta = field(key);
        if (quill && ta) {
            applyEditorTheme(key);
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

    window.landingOpenEditor = function (key) {
        var q = create(key);
        requestAnimationFrame(function () { applyEditorTheme(key); });
        setTimeout(function () { applyEditorTheme(key); }, 60);
        setTimeout(function () { applyEditorTheme(key); }, 200);
        return q;
    };

    window.landingApplyBg = function (key) {
        applyEditorTheme(key);
    };

    window.landingUploadImage = function (key) {
        withEditor(key, function (quill) {
            uploadForEditor(quill);
        });
    };

    // Upload picture for side-by-side block image
    window.uploadSideImage = function (key) {
        pickImage().then(function (file) {
            return uploadFile(file);
        }).then(function (res) {
            var input = document.getElementById('image-url-' + key);
            if (input) {
                input.value = res.url;
                input.dispatchEvent(new Event('input', { bubbles: true }));
                input.dispatchEvent(new Event('change', { bubbles: true }));
            }
        }).catch(function (e) {
            console.error(e);
            alert('Upload failed: ' + e.message);
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

    // Persist every editor into its <textarea> before any form submits (including closed/hidden ones).
    document.addEventListener('submit', function () {
        Object.keys(CACHE).forEach(function (key) {
            sync(key);
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
