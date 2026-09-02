@extends('layouts.admin')

@section('admin_title', 'FAQs Manager')

@section('admin_content')
@php
  $productType = 'App\\Models\\Supabase\\Product';
  $landingType = 'App\\Models\\Supabase\\LandingPage';
  $educationType = 'App\\Models\\Catalog\\EducationPage';
@endphp

<div
  class="grid grid-cols-1 lg:grid-cols-3 gap-8"
  x-data="faqManager({
    faqableType: @js($faqableType),
    faqableId: @js((string) $faqableId),
    locale: @js($locale),
    productType: @js($productType),
    landingType: @js($landingType),
    educationType: @js($educationType),
    forTargetUrl: @js('/'.$adminBase.'/faqs/for-target'),
    reorderUrl: @js('/'.$adminBase.'/faqs/reorder'),
    indexBase: @js('/'.$adminBase.'/faqs'),
    csrf: @js(csrf_token()),
  })"
>
    {{-- Create FAQ --}}
    <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm h-fit">
        <h2 class="text-lg font-bold text-slate-800 mb-2">Create New FAQ</h2>
        <p class="text-xs text-slate-500 mb-6 leading-relaxed">
            Pick the product/page + language first. New FAQs appear on that page for customers after save.
        </p>

        <form action="/{{ $adminBase }}/faqs" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Target Type</label>
                <select name="faqable_type" x-model="faqableType" @change="onTargetChange()" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="{{ $productType }}">Product</option>
                    <option value="{{ $landingType }}">Landing Page</option>
                    <option value="{{ $educationType }}">Education</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Target Resource</label>
                <select
                    name="faqable_id"
                    x-show="faqableType === productType"
                    x-bind:disabled="faqableType !== productType"
                    x-model="faqableId"
                    @change="onTargetChange()"
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"
                >
                    @foreach($products as $product)
                        <option value="{{ $product->id }}">{{ $product->title }}</option>
                    @endforeach
                </select>
                <select
                    name="faqable_id"
                    x-show="faqableType === landingType"
                    x-bind:disabled="faqableType !== landingType"
                    x-model="faqableId"
                    @change="onTargetChange()"
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"
                >
                    @foreach($landingPages as $page)
                        <option value="{{ $page->id }}">{{ $page->title }} ({{ $page->locale }})</option>
                    @endforeach
                </select>
                <select
                    name="faqable_id"
                    x-show="faqableType === educationType"
                    x-bind:disabled="faqableType !== educationType"
                    x-model="faqableId"
                    @change="onTargetChange()"
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"
                >
                    @foreach($educationPages ?? [] as $edu)
                        <option value="{{ $edu['id'] }}">{{ $edu['title'] }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Locale</label>
                <select name="locale" x-model="locale" @change="onTargetChange()" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="en">English (en)</option>
                    <option value="fr">French (fr)</option>
                    <option value="de">German (de)</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Question</label>
                <input type="text" name="question" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="e.g. How do I apply the patch?">
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Answer</label>
                <p class="text-[11px] text-slate-500 mb-1.5">Use bold, italics, underline, and bullet / numbered lists.</p>
                <textarea name="answer" required rows="5" class="js-faq-richtext w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="Customer-facing answer…"></textarea>
            </div>

            <button type="submit" class="w-full bg-slate-800 hover:bg-slate-900 text-white font-bold py-2.5 rounded-lg text-sm transition" onclick="if (window.tinymce) tinymce.triggerSave();">
                Create FAQ
            </button>
        </form>
    </div>

    {{-- Existing FAQs for selected target --}}
    <div class="lg:col-span-2 bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 bg-slate-50 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="text-lg font-bold text-slate-800">Existing FAQs</h2>
                <p class="text-xs text-slate-500 mt-1">
                    Showing for <span class="font-semibold text-slate-700">{{ $selectedTitle }}</span>
                    · <span class="uppercase font-bold">{{ $locale }}</span>
                </p>
            </div>
            <div class="flex flex-col sm:items-end gap-2">
                <form action="/{{ $adminBase }}/faqs/publish" method="POST">
                    @csrf
                    <input type="hidden" name="faqable_type" value="{{ $faqableType }}">
                    <input type="hidden" name="faqable_id" value="{{ $faqableId }}">
                    <input type="hidden" name="locale" value="{{ $locale }}">
                    <button
                        type="submit"
                        class="inline-flex items-center justify-center gap-2 bg-slate-800 hover:bg-slate-900 text-white text-sm font-bold px-5 py-2.5 rounded-lg transition shadow-sm border border-slate-900"
                        style="background-color:#1e293b;color:#fff;"
                    >
                        Save &amp; Publish to site
                    </button>
                </form>
                <p class="text-xs text-slate-400" x-show="!loading">Drag handles to reorder, then publish if the live page looks old.</p>
                <p class="text-xs text-navy-600 font-semibold" x-show="loading" x-cloak>Loading…</p>
                <p class="text-xs text-emerald-600 font-semibold" x-show="reorderMsg" x-text="reorderMsg" x-cloak></p>
            </div>
        </div>

        @if(($autoSyncedCount ?? 0) > 0)
            <div class="px-6 py-3 bg-emerald-50 border-b border-emerald-100 text-sm text-emerald-800">
                Loaded <strong>{{ $autoSyncedCount }}</strong> live page FAQ(s) for this product automatically. You can edit or drag to reorder.
            </div>
        @endif

        @if($faqs->isEmpty() && ! empty($previewFaqs))
            <div class="px-6 py-3 bg-slate-50 border-b border-slate-100 text-sm text-slate-700">
                Showing <strong>{{ count($previewFaqs) }}</strong> live page FAQ(s).
                @if(empty($online))
                    Database is offline right now — reconnect to edit / reorder.
                @else
                    They will sync into CMS on the next successful save cycle.
                @endif
            </div>
            <div class="divide-y divide-slate-200">
                @foreach($previewFaqs as $i => $row)
                    <div class="p-5">
                        <p class="text-xs font-bold text-slate-400 mb-1">#{{ $i + 1 }}</p>
                        <strong class="block text-slate-900 text-base">{{ $row['question'] }}</strong>
                        <p class="text-slate-500 text-sm leading-relaxed mt-1">{{ $row['answer'] }}</p>
                    </div>
                @endforeach
            </div>
        @endif

        <div id="faq-sortable-list" class="divide-y divide-slate-200" x-ref="list">
            @forelse($faqs as $faq)
                <div
                    class="p-5 bg-white"
                    data-id="{{ $faq->id }}"
                    x-data="{ editing: false }"
                >
                    <div class="flex justify-between items-start gap-3">
                        <div class="flex items-start gap-3 flex-1 min-w-0">
                            <button
                                type="button"
                                class="drag-handle mt-1 cursor-grab active:cursor-grabbing text-slate-300 hover:text-slate-500 shrink-0"
                                title="Drag to reorder"
                                aria-label="Drag to reorder"
                            >
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M7 4a1 1 0 100 2 1 1 0 000-2zm6 0a1 1 0 100 2 1 1 0 000-2zM7 9a1 1 0 100 2 1 1 0 000-2zm6 0a1 1 0 100 2 1 1 0 000-2zM7 14a1 1 0 100 2 1 1 0 000-2zm6 0a1 1 0 100 2 1 1 0 000-2z"/></svg>
                            </button>
                            <div class="space-y-1 flex-1 min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-600 text-[10px] font-bold uppercase">{{ $faq->locale }}</span>
                                    <span class="text-[10px] font-semibold {{ $faq->approved ? 'text-emerald-600' : 'text-amber-600' }}">
                                        {{ $faq->approved ? 'Published' : 'Hidden' }}
                                    </span>
                                </div>
                                <strong class="block text-slate-900 text-base">{{ $faq->question }}</strong>
                                <div class="cms-richtext text-slate-500 text-sm leading-relaxed mt-1">{!! \App\Support\CmsHtml::normalize($faq->answer) !!}</div>
                            </div>
                        </div>

                        <div class="flex gap-2 shrink-0">
                            <button
                                type="button"
                                @click="editing = !editing; if (editing) { setTimeout(function () { if (window.initFaqRichtext) window.initFaqRichtext(); }, 50); }"
                                class="text-navy-600 hover:text-navy-800 text-sm font-bold"
                            >
                                Edit
                            </button>
                            <form action="/{{ $adminBase }}/faqs/{{ $faq->id }}/delete" method="POST" onsubmit="return confirm('Delete this FAQ?');">
                                @csrf
                                <button type="submit" class="text-rose-600 hover:text-rose-800 text-sm font-bold">Delete</button>
                            </form>
                        </div>
                    </div>

                    <div x-show="editing" x-cloak class="mt-4 p-4 bg-slate-50 rounded-lg border border-slate-200">
                        <form action="/{{ $adminBase }}/faqs/{{ $faq->id }}/update" method="POST" class="space-y-4" onsubmit="if (window.tinymce) tinymce.triggerSave();">
                            @csrf
                            <div>
                                <label class="block text-xs font-bold text-slate-500 mb-1">Question</label>
                                <input type="text" name="question" value="{{ $faq->question }}" required class="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-500 mb-1">Answer</label>
                                <p class="text-[11px] text-slate-500 mb-1.5">Bold, italics, underline, bullets, numbered lists.</p>
                                <textarea name="answer" required class="js-faq-richtext w-full rounded-lg border border-slate-300 px-3 py-1.5 text-sm" rows="5">{{ $faq->answer }}</textarea>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-500 mb-1">Published on site</label>
                                <select name="approved" class="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-sm max-w-xs">
                                    <option value="1" {{ $faq->approved ? 'selected' : '' }}>Yes</option>
                                    <option value="0" {{ ! $faq->approved ? 'selected' : '' }}>No (hidden)</option>
                                </select>
                            </div>
                            <div class="flex gap-2">
                                <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold px-3 py-1.5 rounded transition">Save</button>
                                <button type="button" @click="editing = false" class="bg-slate-200 hover:bg-slate-300 text-slate-700 text-xs font-bold px-3 py-1.5 rounded transition">Cancel</button>
                            </div>
                        </form>
                    </div>
                </div>
            @empty
                @if(empty($previewFaqs))
                    <div class="px-6 py-10 text-center text-slate-400">
                        No FAQs for this resource yet. Create one on the left.
                    </div>
                @endif
            @endforelse
        </div>
    </div>
</div>
@endsection

@push('admin_scripts')
<script src="https://cdn.jsdelivr.net/npm/tinymce@7.6.1/tinymce.min.js" referrerpolicy="origin"></script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script>
window.initFaqRichtext = function () {
  if (typeof tinymce === 'undefined') return;

  document.querySelectorAll('textarea.js-faq-richtext').forEach(function (el) {
    if (!el.id) {
      el.id = 'faq-rt-' + Math.random().toString(36).slice(2, 10);
    }
    if (tinymce.get(el.id)) {
      return;
    }
    // Hidden Alpine edit panels: only init when visible.
    if (el.offsetParent === null) {
      return;
    }

    tinymce.init({
      selector: '#' + el.id,
      license_key: 'gpl',
      base_url: 'https://cdn.jsdelivr.net/npm/tinymce@7.6.1',
      suffix: '.min',
      plugins: 'lists link autoresize',
      toolbar: 'bold italic underline | bullist numlist | link | removeformat',
      menubar: false,
      branding: false,
      promotion: false,
      statusbar: false,
      height: 200,
      min_height: 160,
      resize: true,
      convert_urls: false,
      entity_encoding: 'raw',
      verify_html: false,
      forced_root_block: 'p',
      valid_elements: 'p,br,strong/b,em/i,u,ul,ol,li,a[href|target|rel|title]',
      paste_data_images: false,
      invalid_elements: 'img,picture,source,svg,video,audio,iframe,object,embed,table,thead,tbody,tr,th,td',
      content_style: 'body { font-family: Inter, system-ui, sans-serif; font-size: 14px; line-height: 1.7; } body p { margin: 0; } body p + p { margin-top: 0.75em; } body strong, body b { font-weight: 700; } body em, body i { font-style: italic; } body ul { list-style: disc; padding-left: 1.5rem; margin: 0.6em 0; } body ol { list-style: decimal; padding-left: 1.5rem; margin: 0.6em 0; } body li { margin: 0.3em 0; }',
      setup: function (editor) {
        editor.on('change blur', function () { editor.save(); });
      },
    });
  });
};

document.addEventListener('DOMContentLoaded', function () {
  window.initFaqRichtext();
  document.querySelectorAll('form').forEach(function (form) {
    form.addEventListener('submit', function () {
      if (window.tinymce) tinymce.triggerSave();
    });
  });
});

function faqManager(cfg) {
  return {
    faqableType: cfg.faqableType,
    faqableId: String(cfg.faqableId || ''),
    locale: cfg.locale,
    productType: cfg.productType,
    landingType: cfg.landingType,
    educationType: cfg.educationType,
    loading: false,
    reorderMsg: '',
    sortable: null,

    init() {
      this.$nextTick(() => this.initSortable());
    },

    onTargetChange() {
      // Sync first option when switching type (disabled selects don't submit).
      const active = this.$root.querySelector('select[name="faqable_id"]:not([disabled])');
      if (active && active.value) {
        this.faqableId = String(active.value);
      }
      const url = new URL(cfg.indexBase, window.location.origin);
      url.searchParams.set('faqable_type', this.faqableType);
      url.searchParams.set('faqable_id', this.faqableId);
      url.searchParams.set('locale', this.locale);
      window.location.href = url.toString();
    },

    initSortable() {
      const el = this.$refs.list;
      if (!el || typeof Sortable === 'undefined') return;
      if (this.sortable) {
        this.sortable.destroy();
      }
      this.sortable = Sortable.create(el, {
        animation: 150,
        handle: '.drag-handle',
        draggable: '[data-id]',
        onEnd: () => this.saveOrder(),
      });
    },

    async saveOrder() {
      const el = this.$refs.list;
      if (!el) return;
      const ids = Array.from(el.querySelectorAll('[data-id]')).map((n) => Number(n.getAttribute('data-id')));
      if (ids.length < 2) return;

      this.reorderMsg = 'Saving order…';
      try {
        const res = await fetch(cfg.reorderUrl, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': cfg.csrf,
            'X-Requested-With': 'XMLHttpRequest',
          },
          body: JSON.stringify({ ids }),
        });
        const data = await res.json();
        this.reorderMsg = data.ok ? 'Order saved — live site updated.' : (data.error || 'Could not save order.');
      } catch (e) {
        this.reorderMsg = 'Could not save order. Try again.';
      }
      setTimeout(() => { this.reorderMsg = ''; }, 3500);
    },
  };
}
</script>
@endpush
