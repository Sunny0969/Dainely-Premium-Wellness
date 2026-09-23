@extends('layouts.admin')

@section('admin_title', 'Bundles & Offers')

@section('admin_content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

    {{-- Create Bundle --}}
    <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm h-fit">
        <h2 class="text-lg font-bold text-slate-800 mb-6">Create Product Bundle</h2>
        <form id="bundle-create-form" action="/{{ $adminBase }}/bundles" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Bundle Title</label>
                <input type="text" name="title" required placeholder="e.g. Daily Relief System" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Bundle Image</label>
                <input type="file" name="image" accept="image/*" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm bg-white file:mr-4 file:py-1 file:px-3 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-navy-50 file:text-navy-700 hover:file:bg-navy-100">
            </div>

            <input type="hidden" name="locale" value="en">

            <div>
                <div class="flex justify-between items-center mb-1">
                    <label class="block text-sm font-semibold text-slate-700">Bundle Components</label>
                    <button type="button" id="add-component-btn" class="text-xs text-blue-600 font-bold hover:text-blue-800">+ Add Product</button>
                </div>
                <div id="components-container" class="space-y-4">
                    <div class="component-row space-y-2 p-3 bg-slate-50 border border-slate-200 rounded-lg">
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Product</label>
                            <select name="components[0][product_id]" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" required>
                                <option value="">Select Component Product</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}">{{ $product->title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Quantity</label>
                            <input type="number" name="components[0][quantity]" value="1" min="1"  required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="Qty">
                        </div>
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Price ($)</label>
                <input type="number" name="price" step="0.01" min="0" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="e.g. 49.99">
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Description</label>
                <input type="hidden" name="description" id="hidden-description">
                <div id="editor-description" class="bg-white rounded-b-lg border-x border-b border-slate-300" style="min-height: 120px;"></div>
            </div>

            <button type="submit" class="w-full bg-slate-800 hover:bg-slate-900 text-white font-bold py-2.5 rounded-lg text-sm transition">
                Create Bundle
            </button>
        </form>
    </div>

    {{-- List Bundles --}}
    <div class="lg:col-span-2 bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 bg-slate-50 border-b border-slate-200 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <h2 class="text-lg font-bold text-slate-800">Existing Product Bundles</h2>
            <div class="flex flex-col sm:flex-row items-center gap-3 w-full md:w-auto">
                <div class="relative w-full sm:w-64">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-4 w-4 text-slate-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" /></svg>
                    </div>
                    <input type="text" id="bundle-search" placeholder="Search bundles..." class="w-full pl-9 pr-3 py-1.5 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-navy-500 focus:border-navy-500">
                </div>
                <select id="bundle-locale-filter" class="w-full sm:w-auto border border-slate-300 rounded-lg text-sm py-1.5 pl-3 pr-8 focus:outline-none focus:ring-1 focus:ring-navy-500 focus:border-navy-500">
                    <option value="">All Locales</option>
                    @foreach($bundles->pluck('locale')->unique() as $loc)
                        <option value="{{ $loc }}">{{ strtoupper($loc) }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="divide-y divide-slate-200" id="bundles-list-container">
            @forelse($bundles as $bundle)
                <div class="bundle-item p-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4" data-title="{{ strtolower($bundle->title) }}" data-locale="{{ strtolower($bundle->locale) }}">
                    <div class="flex items-start md:items-center gap-4">
                        @php
                            $imgUrl = null;
                            if (is_array($bundle->images) && isset($bundle->images['url'])) {
                                $imgUrl = $bundle->images['url'];
                            } elseif (is_string($bundle->images)) {
                                $decoded = json_decode($bundle->images, true);
                                if (is_array($decoded) && isset($decoded['url'])) $imgUrl = $decoded['url'];
                            }
                        @endphp
                        @if($imgUrl)
                            <img src="{{ $imgUrl }}" alt="Bundle Image" class="w-16 h-16 object-cover rounded-lg shadow-sm border border-slate-200">
                        @else
                            <div class="w-16 h-16 bg-slate-100 rounded-lg flex items-center justify-center text-slate-400">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            </div>
                        @endif
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <span class="bg-slate-100 text-slate-600 px-2 py-0.5 rounded text-xs font-bold uppercase">{{ $bundle->locale }}</span>
                                <span class="text-xs text-slate-400 font-mono" title="{{ $bundle->bundle_shopify_product_id }}">GID: {{ Str::limit($bundle->bundle_shopify_product_id, 30) }}</span>
                                <span class="text-xs font-bold text-emerald-600">${{ number_format($bundle->price, 2) }}</span>
                            </div>
                            <h3 class="text-base font-bold text-slate-900 leading-tight">
                                {{ $bundle->title }}
                            </h3>
                            @if($bundle->items && $bundle->items->count() > 0)
                                <div class="text-sm text-slate-500 mt-2">
                                    <ul class="list-disc list-inside">
                                        @foreach($bundle->items as $item)
                                            <li>{{ $item->quantity }}x {{ $item->product->title ?? 'Unknown Product' }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @elseif($bundle->description)
                                <div class="text-sm text-slate-500 mt-1 line-clamp-1 prose prose-sm max-w-none">
                                    {!! strip_tags($bundle->description) !!}
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="flex items-center gap-2 mt-4 md:mt-0">
                        <a href="/{{ $adminBase }}/bundles/{{ $bundle->id }}/edit" class="bg-navy-600 hover:bg-navy-700 text-white font-bold py-2 px-4 rounded shadow-sm text-sm transition">
                            Edit Components
                        </a>
                        <form action="/{{ $adminBase }}/bundles/{{ $bundle->id }}/delete" method="POST" onsubmit="return confirm('Are you sure you want to delete this bundle?');" class="m-0">
                            @csrf
                            <button type="submit" class="bg-rose-100 hover:bg-rose-200 text-rose-700 font-bold py-2 px-4 rounded shadow-sm text-sm transition">
                                Delete
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="px-6 py-8 text-center text-slate-400">No bundles created yet.</div>
            @endforelse
        </div>
    </div>
</div>

@push('admin_scripts')
<link href="https://cdn.jsdelivr.net/npm/quill@2/dist/quill.snow.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/quill@2/dist/quill.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const editorElement = document.getElementById('editor-description');
        if (editorElement && typeof Quill !== 'undefined') {
            const quill = new Quill('#editor-description', {
                theme: 'snow',
                modules: {
                    table: true,
                    toolbar: [
                        [{ 'header': [1, 2, 3, 4, false] }],
                        ['bold', 'italic', 'underline', 'strike', 'blockquote'],
                        [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                        ['link', 'image', 'video'],
                        ['table'],
                        ['clean']
                    ]
                }
            });

            const form = document.getElementById('bundle-create-form');
            if (form) {
                form.addEventListener('submit', function () {
                    const hiddenDesc = document.getElementById('hidden-description');
                    if (hiddenDesc) {
                        hiddenDesc.value = quill.root.innerHTML;
                    }
                });
            }
        }

        // Dynamic components script
        const addBtn = document.getElementById('add-component-btn');
        const container = document.getElementById('components-container');
        if(addBtn && container) {
            let rowCount = 1;
            addBtn.addEventListener('click', function() {
                const row = document.createElement('div');
                row.className = 'component-row space-y-2 p-3 bg-slate-50 border border-slate-200 rounded-lg mt-4 relative';
                row.innerHTML = `
                    <button type="button" class="remove-row-btn absolute top-2 right-2 px-2 py-1 bg-rose-100 text-rose-600 rounded text-xs hover:bg-rose-200">Remove</button>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Product</label>
                        <select name="components[${rowCount}][product_id]" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" required>
                            <option value="">Select Component Product</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}">{{ str_replace("'", "\\'", $product->title) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Quantity</label>
                        <input type="number" name="components[${rowCount}][quantity]" value="1" min="1"  required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="Qty">
                    </div>
                `;
                container.appendChild(row);
                rowCount++;
            });

            container.addEventListener('click', function(e) {
                if(e.target.classList.contains('remove-row-btn')) {
                    e.target.closest('.component-row').remove();
                }
            });
        }

        // Search and Filter Logic
        const searchInput = document.getElementById('bundle-search');
        const localeFilter = document.getElementById('bundle-locale-filter');
        const bundleItems = document.querySelectorAll('.bundle-item');

        function filterBundles() {
            const searchTerm = searchInput ? searchInput.value.toLowerCase() : '';
            const selectedLocale = localeFilter ? localeFilter.value.toLowerCase() : '';

            bundleItems.forEach(item => {
                const title = item.getAttribute('data-title') || '';
                const locale = item.getAttribute('data-locale') || '';
                
                const matchesSearch = title.includes(searchTerm);
                const matchesLocale = selectedLocale === '' || locale === selectedLocale;

                if (matchesSearch && matchesLocale) {
                    item.style.display = 'flex';
                } else {
                    item.style.display = 'none';
                }
            });
        }

        if (searchInput) searchInput.addEventListener('input', filterBundles);
        if (localeFilter) localeFilter.addEventListener('change', filterBundles);
    });
</script>
@endpush
@endsection
