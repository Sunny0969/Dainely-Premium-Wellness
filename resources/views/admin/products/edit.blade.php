@extends('layouts.admin')

@section('admin_title', 'Edit Overlays for ' . $product->title)

@section('admin_content')
<div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm" x-data="{ currentTab: 'en', overwriteTranslate: false, translating: false }">

    {{-- Tabs header --}}
    <div class="flex flex-col gap-4 mb-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex border-b border-slate-200 flex-1 min-w-0">
                @foreach(['en' => 'English', 'fr' => 'Français', 'de' => 'Deutsch (German)'] as $lang => $label)
                    <button type="button" @click="currentTab = '{{ $lang }}'; $nextTick(() => { if (window.tinymce) tinymce.editors.forEach(e => { try { e.fire('ResizeEditor'); } catch (_) {} }); })"
                        :class="currentTab === '{{ $lang }}' ? 'border-navy-600 text-navy-600' : 'border-transparent text-slate-500 hover:text-slate-700'"
                        class="px-5 py-3 border-b-2 font-bold text-sm transition whitespace-nowrap">
                        {{ $label }}
                    </button>
                @endforeach
            </div>
        </div>

        <div class="rounded-xl border border-navy-100 bg-navy-50/60 px-5 py-4 flex flex-col lg:flex-row lg:items-center gap-4 justify-between">
            <div class="min-w-0">
                <p class="text-sm font-bold text-navy-900">Auto-translate from English</p>
                <p class="text-xs text-navy-700/80 mt-1 leading-relaxed">
                    Write content in the <strong>English</strong> tab, save it, then generate <strong>French</strong> and <strong>German</strong> automatically.
                    Usually finishes in under 30 seconds (first run may take a bit longer). You can still edit each language afterward.
                </p>
            </div>
            <form
                action="/{{ $adminBase }}/products/{{ $product->id }}/translate-from-en"
                method="POST"
                class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 shrink-0"
                @submit="translating = true; if (window.tinymce) tinymce.triggerSave();"
            >
                @csrf
                <label class="inline-flex items-center gap-2 text-xs font-semibold text-slate-700 bg-white border border-slate-200 rounded-lg px-3 py-2 cursor-pointer">
                    <input type="checkbox" name="overwrite" value="1" class="rounded border-slate-300 text-navy-600 focus:ring-navy-500" x-model="overwriteTranslate">
                    Overwrite existing FR / DE
                </label>
                <button
                    type="submit"
                    class="inline-flex items-center justify-center gap-2 bg-navy-700 hover:bg-navy-800 disabled:opacity-60 disabled:cursor-wait text-white font-bold px-5 py-2.5 rounded-lg text-sm transition"
                    :disabled="translating"
                >
                    <svg x-show="!translating" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"/></svg>
                    <svg x-show="translating" x-cloak class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                    <span x-text="translating ? 'Translating… please wait' : 'Translate EN → FR & DE'"></span>
                </button>
            </form>
            <p x-show="translating" x-cloak class="text-xs text-navy-600 lg:basis-full order-last">
                Working… do not close this tab. Large English content can take 15–40 seconds.
            </p>
        </div>
    </div>

    {{-- Editor form --}}
    <form action="/{{ $adminBase }}/products/{{ $product->id }}/update" method="POST" class="space-y-6" id="product-overlay-form">
        @csrf

        @foreach(['en', 'fr', 'de'] as $locale)
            <div x-show="currentTab === '{{ $locale }}'" x-cloak class="space-y-6">
                <div class="flex items-center justify-between gap-3 border-b border-slate-100 pb-2">
                    <h3 class="text-base font-bold text-slate-800">
                        @if($locale === 'en') English content
                        @elseif($locale === 'fr') French content — editable separately
                        @else German content — editable separately
                        @endif
                    </h3>
                    <span class="text-[11px] font-bold uppercase tracking-wider px-2 py-1 rounded bg-slate-100 text-slate-600">{{ strtoupper($locale) }}</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">SEO Title</label>
                        <input type="text" name="contents[{{ $locale }}][seo_title]" value="{{ $contents[$locale]->seo_title }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Canonical URL</label>
                        <input type="text" name="contents[{{ $locale }}][canonical_url]" value="{{ $contents[$locale]->canonical_url }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-slate-700 mb-1">SEO Description</label>
                        <textarea name="contents[{{ $locale }}][seo_description]" rows="2" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">{{ $contents[$locale]->seo_description }}</textarea>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Overview Description</label>
                        <textarea name="contents[{{ $locale }}][overview]" rows="4" class="js-admin-richtext w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">{{ $contents[$locale]->overview }}</textarea>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Benefits</label>
                        <textarea name="contents[{{ $locale }}][benefits]" rows="4" class="js-admin-richtext w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">{{ $contents[$locale]->benefits }}</textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">How It Works</label>
                        <textarea name="contents[{{ $locale }}][how_it_works]" rows="4" class="js-admin-richtext w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">{{ $contents[$locale]->how_it_works }}</textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Who Is It For</label>
                        <textarea name="contents[{{ $locale }}][who_is_it_for]" rows="4" class="js-admin-richtext w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">{{ $contents[$locale]->who_is_it_for }}</textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Specifications</label>
                        <textarea name="contents[{{ $locale }}][specifications]" rows="4" class="js-admin-richtext w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">{{ $contents[$locale]->specifications }}</textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Care & Maintenance</label>
                        <textarea name="contents[{{ $locale }}][care]" rows="4" class="js-admin-richtext w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">{{ $contents[$locale]->care }}</textarea>
                    </div>
                </div>
            </div>
        @endforeach

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pt-6 border-t border-slate-100">
            <p class="text-xs text-slate-500">Tip: Save English first, then use <strong>Translate EN → FR &amp; DE</strong>. Switch tabs to fine-tune each language.</p>
            <button type="submit" class="bg-slate-800 hover:bg-slate-900 text-white font-bold px-8 py-3 rounded-lg text-sm transition">
                Save All Locales
            </button>
        </div>
    </form>

    {{-- Product Content Blocks manager --}}
    <div class="border-t border-slate-200 pt-12 mt-12 space-y-8">
        <div>
            <h2 class="text-xl font-bold text-slate-900">Product Page Layout Blocks</h2>
            <p class="text-sm text-slate-600 mt-1">Write once in English. On the storefront, French/German customers see an auto-translated version when they switch language.</p>
        </div>
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            {{-- Add block form --}}
            <div class="bg-white p-6 rounded-xl border border-slate-300 shadow-sm h-fit">
                <h3 class="text-base font-bold text-slate-900 mb-1">Add Content Block</h3>
                <p class="text-xs text-slate-600 mb-4 leading-relaxed">Write in English. Customers see French/German automatically when they change language in the header.</p>
                <form action="/{{ $adminBase }}/products/{{ $product->id }}/blocks" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-semibold text-slate-800 mb-1">Block Type</label>
                        <select name="block_type" class="w-full rounded-lg border border-slate-400 px-3 py-2 text-sm bg-white text-slate-900">
                            <option value="benefits">Benefits</option>
                            <option value="how-it-works">How It Works</option>
                            <option value="testimonials">Testimonials</option>
                            <option value="faqs">FAQs Accordion</option>
                            <option value="cta">Call to Action</option>
                            <option value="video">Video</option>
                            <option value="comparison">Comparison</option>
                            <option value="bundle">Bundle</option>
                            <option value="disclaimer">Disclaimer</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-800 mb-1">Display Position</label>
                        <select name="display_position" class="w-full rounded-lg border border-slate-400 px-3 py-2 text-sm bg-white text-slate-900">
                            <option value="default">Default (In-order)</option>
                            @foreach($pageHeadings as $key => $heading)
                                <option value="before_{{ $key }}">Before: "{{ \Illuminate\Support\Str::limit(strip_tags($heading), 50) }}"</option>
                                <option value="after_{{ $key }}">After: "{{ \Illuminate\Support\Str::limit(strip_tags($heading), 50) }}"</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex items-center gap-2 mt-2">
                        <input type="hidden" name="is_global" value="0">
                        <input type="checkbox" name="is_global" id="is_global" value="1" class="rounded border-slate-400 text-navy-600 focus:ring-navy-600">
                        <label for="is_global" class="text-sm font-semibold text-slate-800">Apply to all products (Global Block)</label>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-800 mb-1">Block Title</label>
                        <input type="text" name="title" class="w-full rounded-lg border border-slate-400 px-3 py-2 text-sm bg-white text-slate-900 placeholder:text-slate-400" placeholder="e.g. How it works">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-800 mb-1">Content (HTML/Markdown)</label>
                        <textarea name="content" rows="4" class="js-admin-richtext w-full rounded-lg border border-slate-400 px-3 py-2 text-sm bg-white text-slate-900 placeholder:text-slate-400" placeholder="Customer-facing section content…"></textarea>
                    </div>

                    <button type="submit" class="w-full bg-navy-800 hover:bg-navy-900 text-white font-bold py-2.5 rounded-lg text-sm transition shadow-sm" style="background-color:#1e3a5f;color:#fff;">
                        Add Block
                    </button>
                </form>
            </div>

            {{-- Existing Blocks list --}}
            <div class="lg:col-span-2 bg-white rounded-xl border border-slate-300 shadow-sm overflow-hidden">
                <div class="px-6 py-4 bg-slate-100 border-b border-slate-300">
                    <h3 class="text-base font-bold text-slate-900">Current Blocks</h3>
                    <p class="text-xs text-slate-600 mt-0.5">Shown on the product page from top to bottom in this order.</p>
                </div>

                <div class="divide-y divide-slate-200">
                    @php
                        $allBlocks = $product->pageBlocks->where('locale', 'en')->concat($globalBlocks ?? collect())->unique('id')->sortBy('sort_order')->values();
                    @endphp
                    @forelse($allBlocks as $block)
                        <div class="p-6 space-y-4" x-data="{ editing: false }">
                            <div class="flex justify-between items-start gap-4">
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="px-2 py-0.5 rounded bg-navy-800 text-white text-xs font-bold uppercase tracking-wider" style="background-color:#1e3a5f;">
                                            {{ $block->block_type }}
                                        </span>
                                        <span class="text-xs font-semibold text-slate-600">#{{ $loop->iteration }}</span>
                                        @if($block->is_global)
                                            <span class="px-2 py-0.5 rounded bg-amber-100 text-amber-800 border border-amber-300 text-xs font-bold uppercase tracking-wider">GLOBAL</span>
                                        @endif
                                        @if($block->display_position !== 'default' && $block->display_position !== null)
                                            @php
                                                $posText = str_replace('_', ' ', $block->display_position);
                                                if (preg_match('/^(before|after)_(.*)$/', $block->display_position, $matches)) {
                                                    $sectionKey = $matches[2];
                                                    if (isset($pageHeadings[$sectionKey])) {
                                                        $posText = ucfirst($matches[1]) . ' "' . \Illuminate\Support\Str::limit(strip_tags($pageHeadings[$sectionKey]), 30) . '"';
                                                    }
                                                }
                                            @endphp
                                            <span class="px-2 py-0.5 rounded bg-slate-200 text-slate-700 border border-slate-300 text-xs font-bold tracking-wider" title="{{ $block->display_position }}">{{ $posText }}</span>
                                        @endif
                                    </div>
                                    @if($block->title)
                                        <strong class="block text-slate-900 text-base mt-2">{{ $block->title }}</strong>
                                    @endif
                                    <div class="text-slate-600 text-xs mt-1 font-mono max-w-lg truncate">{{ $block->content }}</div>
                                    <span class="inline-block text-xs font-semibold mt-2 {{ $block->visible ? 'text-emerald-800' : 'text-rose-700' }}">
                                        {{ $block->visible ? 'Visible on site' : 'Hidden' }}
                                    </span>
                                </div>

                                <div class="flex gap-3 shrink-0">
                                    <button type="button" @click="editing = !editing; if (editing) setTimeout(() => { if(window.tinymce) tinymce.editors.forEach(e => { try { e.fire('ResizeEditor'); } catch (_) {} }) }, 50)" class="text-navy-800 hover:text-navy-950 text-sm font-bold underline underline-offset-2">
                                        Edit
                                    </button>
                                    <form action="/{{ $adminBase }}/products/{{ $product->id }}/blocks/{{ $block->id }}/delete" method="POST" onsubmit="return confirm('Are you sure you want to delete this block?');">
                                        @csrf
                                        <button type="submit" class="text-rose-700 hover:text-rose-900 text-sm font-bold underline underline-offset-2">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </div>

                            {{-- Edit block inline --}}
                            <div x-show="editing" x-cloak class="p-4 bg-slate-50 rounded-lg border border-slate-300">
                                <form action="/{{ $adminBase }}/products/{{ $product->id }}/blocks/{{ $block->id }}/update" method="POST" class="space-y-4">
                                    @csrf
                                    <div>
                                        <label class="block text-xs font-bold text-slate-800 mb-1">Block Title</label>
                                        <input type="text" name="title" value="{{ $block->title }}" class="w-full rounded-lg border border-slate-400 px-3 py-1.5 text-sm bg-white text-slate-900">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-800 mb-1">Block Content</label>
                                        <textarea name="content" class="js-admin-richtext w-full rounded-lg border border-slate-400 px-3 py-1.5 text-sm bg-white text-slate-900" rows="3">{{ $block->content }}</textarea>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-800 mb-1">Display Position</label>
                                        <select name="display_position" class="w-full rounded-lg border border-slate-400 px-3 py-1.5 text-sm bg-white text-slate-900">
                                            <option value="default" {{ $block->display_position == 'default' ? 'selected' : '' }}>Default (In-order)</option>
                                            @foreach($pageHeadings as $key => $heading)
                                                <option value="before_{{ $key }}" {{ $block->display_position === 'before_'.$key ? 'selected' : '' }}>Before: "{{ \Illuminate\Support\Str::limit(strip_tags($heading), 50) }}"</option>
                                                <option value="after_{{ $key }}" {{ $block->display_position === 'after_'.$key ? 'selected' : '' }}>After: "{{ \Illuminate\Support\Str::limit(strip_tags($heading), 50) }}"</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <input type="hidden" name="is_global" value="0">
                                        <input type="checkbox" name="is_global" id="edit_is_global_{{ $block->id }}" value="1" {{ $block->is_global ? 'checked' : '' }} class="rounded border-slate-400 text-navy-600 focus:ring-navy-600">
                                        <label for="edit_is_global_{{ $block->id }}" class="text-xs font-bold text-slate-800">Apply to all products (Global Block)</label>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-800 mb-1">Visible on customer page</label>
                                        <select name="visible" class="w-full rounded-lg border border-slate-400 px-3 py-1.5 text-sm bg-white text-slate-900">
                                            <option value="1" {{ $block->visible ? 'selected' : '' }}>Yes</option>
                                            <option value="0" {{ ! $block->visible ? 'selected' : '' }}>No</option>
                                        </select>
                                    </div>
                                    <div class="flex gap-2">
                                        <button type="submit" class="bg-navy-800 hover:bg-navy-900 text-white text-xs font-bold px-4 py-2 rounded-lg transition" style="background-color:#1e3a5f;color:#fff;">
                                            Save Block
                                        </button>
                                        <button type="button" @click="editing = false" class="bg-slate-200 hover:bg-slate-300 text-slate-800 text-xs font-bold px-4 py-2 rounded-lg transition">
                                            Cancel
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="px-6 py-8 text-center text-slate-600">No layout blocks added for this product yet.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('admin_scripts')
<script src="https://cdn.jsdelivr.net/npm/tinymce@7.6.1/tinymce.min.js" referrerpolicy="origin"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
  if (typeof tinymce === 'undefined') return;

  tinymce.init({
    selector: 'textarea.js-admin-richtext',
    license_key: 'gpl',
    base_url: 'https://cdn.jsdelivr.net/npm/tinymce@7.6.1',
    suffix: '.min',
    plugins: 'lists table link autoresize',
    toolbar: 'bold underline | bullist numlist | table tabledelete | link | removeformat',
    menubar: false,
    branding: false,
    promotion: false,
    statusbar: true,
    height: 220,
    min_height: 180,
    resize: true,
    convert_urls: false,
    entity_encoding: 'raw',
    verify_html: false,
    forced_root_block: 'p',
    // Explicitly allow the formatting buttons we expose in the toolbar.
    valid_elements: 'p,br,strong/b,em/i,u,ul,ol,li,a[href|target|rel|title],table,thead,tbody,tr,th,td,span[style]',
    // No image upload / insert
    paste_data_images: false,
    invalid_elements: 'img,picture,source,svg,video,audio,iframe,object,embed',
    table_toolbar: 'tableprops tabledelete | tableinsertrowbefore tableinsertrowafter tabledeleterow | tableinsertcolbefore tableinsertcolafter tabledeletecol',
    content_style: 'body { font-family: system-ui, -apple-system, Segoe UI, sans-serif; font-size: 14px; line-height: 1.7; } body p { margin: 0; } body p + p { margin-top: 0.9em; } body strong, body b { font-weight: 700; } body ul { list-style: disc; padding-left: 1.5rem; margin: 0.75em 0; } body ol { list-style: decimal; padding-left: 1.5rem; margin: 0.75em 0; } body li { margin: 0.35em 0; } table { border-collapse: collapse; width: 100%; } td, th { border: 1px solid #cbd5e1; padding: 6px 8px; }',
    setup: function (editor) {
      editor.on('change', function () {
        editor.save();
      });
      editor.on('blur', function () {
        editor.save();
      });
    },
  });

  // When Alpine switches locale tabs, hidden editors need a layout refresh
  document.querySelectorAll('button[type="button"]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      setTimeout(function () {
        tinymce.editors.forEach(function (ed) {
          try { ed.fire('ResizeEditor'); } catch (e) {}
        });
      }, 50);
    });
  });

  // Ensure HTML is written back to textareas before submit
  var overlayForm = document.getElementById('product-overlay-form');
  if (overlayForm) {
    overlayForm.addEventListener('submit', function () {
      if (window.tinymce) tinymce.triggerSave();
    });
  }
  document.querySelectorAll('form').forEach(function (form) {
    form.addEventListener('submit', function () {
      if (window.tinymce) tinymce.triggerSave();
    });
  });
});
</script>
@endpush
