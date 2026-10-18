@extends('layouts.app')

@section('title', $page->meta_title ?: $page->title)
@section('meta_description', $page->meta_description)

@section('meta_canonical')
    <link rel="canonical" href="{{ $canonicalUrl ?? request()->url() }}">
@endsection

@push('json-ld')
    @if(isset($schemaJson))
        <script type="application/ld+json">
            {!! $schemaJson !!}
        </script>
    @endif
@endpush

@push('page_styles')
<style>
    /*
     * Storefront rendering for the `rich` landing block.
     * Loads after the Vite bundle so these rules decide type scale, table shape and
     * link colour — every pairing below is AA (4.5:1) or better on its own background.
     */
    .landing-richtext {
        color: #0f172a;               /* 17.1:1 on white */
        font-size: 1.0625rem;
        line-height: 1.75;
        overflow-wrap: break-word;
    }

    .landing-richtext p {
        margin: 0 0 1rem;
    }

    .landing-richtext > p:last-child {
        margin-bottom: 0;
    }

    .landing-richtext h1,
    .landing-richtext h2,
    .landing-richtext h3,
    .landing-richtext h4,
    .landing-richtext h5,
    .landing-richtext h6 {
        color: inherit;
        font-weight: 800;
        line-height: 1.25;
        margin-top: 1.9rem;
        margin-bottom: 0.7rem;
        text-wrap: balance;
    }

    .landing-richtext h1 { font-size: 2.15rem; }
    .landing-richtext h2 { font-size: 1.75rem; }
    .landing-richtext h3 { font-size: 1.4rem; }
    .landing-richtext h4 { font-size: 1.175rem; }
    .landing-richtext h5 { font-size: 1.05rem; }
    .landing-richtext h6 { font-size: 1rem; text-transform: uppercase; letter-spacing: .06em; }

    .landing-richtext > h1:first-child,
    .landing-richtext > h2:first-child,
    .landing-richtext > h3:first-child,
    .landing-richtext > h4:first-child,
    .landing-richtext > h5:first-child,
    .landing-richtext > h6:first-child {
        margin-top: 0;
    }

    .landing-richtext strong,
    .landing-richtext b {
        color: inherit;
        font-weight: 800;
    }

    .landing-richtext a {
        color: #1d4ed8;               /* 6.3:1 on white */
        text-decoration: underline;
        text-decoration-thickness: 1.5px;
        text-underline-offset: 2px;
    }

    .landing-richtext a:hover {
        color: #1e3a8a;
    }

    .landing-richtext ul,
    .landing-richtext ol {
        margin: 0 0 1rem;
        padding-left: 1.6rem;
    }

    .landing-richtext ul {
        list-style-type: disc;
    }

    .landing-richtext ol {
        list-style-type: decimal;
    }

    /* Support Quill 2 semantic markup where both bullets and numbers use <ol><li data-list="..."> */
    .landing-richtext ol:has(li[data-list="bullet"]),
    .landing-richtext ol:has(li[data-list="ordered"]) {
        list-style-type: none !important;
        padding-left: 0 !important;
    }

    .landing-richtext li[data-list] {
        list-style-type: none !important;
        padding-left: 1.5rem !important;
        position: relative !important;
    }

    .landing-richtext li[data-list] > .ql-ui {
        display: none !important;
    }

    .landing-richtext li[data-list="bullet"]::before {
        content: "•";
        position: absolute;
        left: 0.25rem;
        color: #475569;
        font-weight: 700;
        font-size: 1.2em;
        line-height: 1;
    }

    .landing-richtext ol {
        counter-reset: landing-ol-counter;
    }

    .landing-richtext li[data-list="ordered"] {
        counter-increment: landing-ol-counter;
    }

    .landing-richtext li[data-list="ordered"]::before {
        content: counter(landing-ol-counter) ".";
        position: absolute;
        left: 0;
        color: #475569;
        font-weight: 700;
    }

    .landing-richtext li {
        margin: 0.35rem 0;
    }

    .landing-richtext li::marker {
        color: #475569;
        font-weight: 700;
    }

    .landing-richtext blockquote {
        background: #f8fafc;
        border-left: 4px solid #cbd5e1;
        border-radius: 0 8px 8px 0;
        color: #334155;               /* 9.1:1 on #f8fafc */
        font-style: italic;
        margin: 1.4rem 0;
        padding: 0.75rem 1rem;
    }

    .landing-richtext pre,
    .landing-richtext .ql-code-block-container {
        background: #1e293b;
        border-radius: 8px;
        color: #f1f5f9;
        font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
        font-size: 0.9rem;
        line-height: 1.6;
        margin: 1.4rem 0;
        overflow-x: auto;
        padding: 1rem 1.25rem;
        border: 1px solid #334155;
    }

    .landing-richtext .ql-code-block {
        white-space: pre-wrap;
        word-break: break-word;
        font-family: inherit;
        color: inherit;
        margin: 0;
        padding: 0;
    }

    .landing-richtext code {
        background: #f1f5f9;
        border-radius: 4px;
        color: #0f172a;
        font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
        font-size: 0.92em;
        padding: 0.1em 0.35em;
    }

    .landing-richtext pre code {
        background: transparent;
        border-radius: 0;
        color: inherit;
        font-size: inherit;
        padding: 0;
    }

    .landing-richtext hr {
        border: 0;
        border-top: 1px solid #e2e8f0;
        margin: 1.75rem 0;
    }

    .landing-richtext img {
        display: block;
        height: auto !important;
        margin: 1.35rem auto;
        max-width: 100% !important;   /* wins over a width left behind by Word */
        border-radius: 10px;
    }

    .landing-richtext table {
        border: 1px solid #cbd5e1 !important; /* wins over the black rule Quill bakes into pasted tables */
        border-collapse: collapse;
        font-size: 0.95rem;
        margin: 1.4rem 0;
        table-layout: auto;
        width: 100%;
    }

    .landing-richtext th,
    .landing-richtext td {
        border: 1px solid #cbd5e1;
        color: #0f172a;
        overflow-wrap: anywhere;
        padding: 0.6rem 0.75rem;
        text-align: left;
        vertical-align: top;
        word-break: break-word;
    }

    .landing-richtext th {
        background: #f1f5f9;
        color: #0f172a;
        font-weight: 700;
    }

    /* Dark band variant — every pairing below is AA against #0f172a / #1e293b. */
    .landing-richtext.landing-richtext--dark th,
    .landing-richtext.landing-richtext--dark td {
        border-color: #475569;
        color: #e2e8f0;
    }

    .landing-richtext.landing-richtext--dark th {
        background: #1e293b;
        color: #f1f5f9;
    }

    .landing-richtext.landing-richtext--dark td:first-child,
    .landing-richtext.landing-richtext--dark th:first-child {
        color: #f1f5f9;
        font-weight: 600;
    }

    .landing-richtext.landing-richtext--dark a {
        color: #93c5fd;
    }

    .landing-richtext.landing-richtext--dark a:hover {
        color: #bfdbfe;
    }

    .landing-richtext.landing-richtext--dark blockquote {
        background: #1e293b;
        border-left-color: #64748b;
        color: #cbd5e1;
    }

    .landing-richtext.landing-richtext--dark code {
        background: #1e293b;
        color: #e2e8f0;
    }

    .landing-richtext.landing-richtext--dark pre {
        background: #020617;
        color: #e2e8f0;
    }

    .landing-richtext.landing-richtext--dark li::marker {
        color: #93c5fd;
    }

    .landing-richtext.landing-richtext--dark li[data-list="bullet"]::before,
    .landing-richtext.landing-richtext--dark li[data-list="ordered"]::before {
        color: #93c5fd;
    }

    .landing-richtext.landing-richtext--dark hr {
        border-top-color: #334155;
    }

    @media (max-width: 768px) {
        .landing-richtext {
            font-size: 1rem;
        }

        .landing-richtext h1 { font-size: 1.7rem; }
        .landing-richtext h2 { font-size: 1.45rem; }
        .landing-richtext h3 { font-size: 1.24rem; }
        .landing-richtext h4 { font-size: 1.1rem; }

        /* Wide Word tables scroll instead of pushing the page sideways. */
        .landing-richtext table {
            display: block;
            overflow-x: auto;
        }

        .landing-richtext th,
        .landing-richtext td {
            padding: 0.45rem 0.5rem;
        }
    }
</style>
@endpush

@section('content')
@include('components.breadcrumbs', ['items' => $breadcrumbs ?? []])
<div class="landing-page-container py-12">

    @foreach($blocks as $index => $block)
        @php
            $customBg = $block->bg_color ?? null;
            $bgClass = '';
            $styleAttr = '';
            if (!empty($customBg)) {
                $styleAttr = 'background-color: ' . e($customBg) . ';';
            } else {
                $bgClass = ($index % 2 === 0) ? 'bg-white' : 'bg-slate-50 border-y border-slate-100';
            }
        @endphp
        <div class="page-block-wrapper py-6 {{ $bgClass }}" style="{{ $styleAttr }}" id="block-{{ $block->id }}">
            @includeIf('components.blocks.' . $block->block_type, [
                'title' => $block->title,
                'content' => $block->content,
                'ctaUrl' => $offer['checkout_url'] ?? null,
                'ctaLabel' => $offer['label'] ?? null,
                'bundleView' => ($bundleViews[$block->id] ?? null),
            ])
        </div>
    @endforeach

    {{-- Landing Page FAQs --}}
    @if(!empty($faqs) && (is_countable($faqs) ? count($faqs) : $faqs->count()) > 0)
        <div class="landing-faqs-section container-site max-w-4xl mx-auto px-4 mt-14 mb-8" id="faqs">
            <h2 class="text-3xl font-extrabold text-navy-900 mb-8 text-center">{{ __('Frequently Asked Questions') }}</h2>
            <div class="space-y-4 max-w-3xl mx-auto">
                @foreach($faqs as $faq)
                    <details class="group bg-white border border-slate-200 rounded-xl px-5 py-4 transition shadow-sm">
                        <summary class="font-semibold text-navy-900 cursor-pointer list-none flex justify-between gap-4">
                            <span>{{ $faq->question }}</span>
                            <span class="text-slate-400 group-open:rotate-180 transition">▾</span>
                        </summary>
                        <div class="mt-3 text-slate-600 text-sm leading-relaxed cms-richtext">
                            {!! nl2br(e($faq->answer)) !!}
                        </div>
                    </details>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Sticky / fallback checkout CTA when offer is linked --}}
    @if(!empty($offer['checkout_url']) && in_array($offer['type'] ?? null, ['product', 'bundle'], true))
        <div class="container-site max-w-4xl mx-auto px-4 mt-10 text-center">
            <a href="{{ $offer['checkout_url'] }}"
               class="inline-flex items-center justify-center bg-navy-800 hover:bg-navy-900 text-white font-bold px-8 py-3.5 rounded-xl shadow-md transition">
                {{ $offer['label'] ?? __('Shop Now') }}
            </a>
        </div>
    @endif
</div>

@include('components.related-content', [
    'title' => __('Related Resources'),
    'links' => $relatedLinks ?? [],
])
@endsection
