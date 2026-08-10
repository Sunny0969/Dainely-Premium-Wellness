@extends('layouts.admin')

@section('admin_title', 'Edit Landing Page: ' . $page->title)

@section('admin_content')
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
                <label class="block text-sm font-semibold text-slate-700 mb-1">Canonical URL</label>
                <input type="text" name="canonical_url" value="{{ $page->canonical_url }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>

            <div class="md:col-span-2">
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

            <div class="md:col-span-2 border-t border-slate-100 pt-6">
                <h3 class="text-sm font-bold text-slate-800 mb-3">§8.4 Offer / Checkout CTA</h3>
                <p class="text-xs text-slate-500 mb-4">Link a Shopify product <em>or</em> a bundle. Bundle wins if both set. CTA buttons add items to cart then go to checkout.</p>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Shopify Product ID / handle</label>
                        <input type="text" name="shopify_product_id" value="{{ $page->shopify_product_id }}"
                               list="product-gids"
                               placeholder="gid://shopify/Product/… or handle"
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <datalist id="product-gids">
                            @foreach($products ?? [] as $prod)
                                <option value="{{ $prod->shopify_product_id }}">{{ $prod->title }}</option>
                                <option value="{{ $prod->handle }}">{{ $prod->title }} (handle)</option>
                            @endforeach
                        </datalist>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Bundle</label>
                        <select name="bundle_id" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            <option value="">— none —</option>
                            @foreach($bundles ?? [] as $bundle)
                                <option value="{{ $bundle->id }}" @selected((int)$page->bundle_id === (int)$bundle->id)>
                                    {{ $bundle->title }} ({{ $bundle->locale }}) #{{ $bundle->id }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">CTA Label</label>
                        <input type="text" name="cta_label" value="{{ $page->cta_label }}" placeholder="Shop Now" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Discount code</label>
                        <input type="text" name="discount_code" value="{{ $page->discount_code }}" placeholder="e.g. RELIEF10" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm uppercase">
                        <p class="text-xs text-slate-500 mt-1">Applied when visitor uses this landing’s CTA checkout.</p>
                    </div>
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
        {{-- Add Content Block --}}
        <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm h-fit">
            <h2 class="text-lg font-bold text-slate-800 mb-6">Add Content Block</h2>
            <form action="/{{ $adminBase }}/landings/{{ $page->id }}/blocks" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Block Type</label>
                    <select name="block_type" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
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
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Block Content (Markdown or HTML)</label>
                    <textarea name="content" rows="4" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></textarea>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Sort Order</label>
                    <input type="number" name="sort_order" value="0" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>

                <button type="submit" class="w-full bg-slate-800 hover:bg-slate-900 text-white font-bold py-2.5 rounded-lg text-sm transition">
                    Add Block
                </button>
            </form>
        </div>

        {{-- Existing Content Blocks --}}
        <div class="lg:col-span-2 bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-6 py-4 bg-slate-50 border-b border-slate-200">
                <h2 class="text-lg font-bold text-slate-800">Page Content Layout Blocks</h2>
            </div>

            <div class="divide-y divide-slate-200">
                @forelse($page->pageBlocks as $block)
                    <div class="p-6 space-y-4" x-data="{ editing: false }">
                        <div class="flex justify-between items-start gap-4">
                            <div>
                                <span class="px-2.5 py-0.5 rounded bg-navy-50 text-navy-700 text-xs font-bold uppercase tracking-wider">
                                    {{ $block->block_type }}
                                </span>
                                @if($block->title)
                                    <strong class="block text-slate-800 text-base mt-2">{{ $block->title }}</strong>
                                @endif
                                <div class="text-slate-500 text-xs mt-1 font-mono max-w-lg truncate">{{ $block->content }}</div>
                                <span class="inline-block text-xs font-semibold text-slate-400 mt-2">Order: {{ $block->sort_order }} | Visible: {{ $block->visible ? 'Yes' : 'No' }}</span>
                            </div>

                            <div class="flex gap-2">
                                <button @click="editing = !editing" class="text-navy-600 hover:text-navy-800 text-sm font-bold">
                                    Edit Block
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
                        <div x-show="editing" class="p-4 bg-slate-50 rounded-lg border border-slate-200">
                            <form action="/{{ $adminBase }}/landings/{{ $page->id }}/blocks/{{ $block->id }}/update" method="POST" class="space-y-4">
                                @csrf
                                <div>
                                    <label class="block text-xs font-bold text-slate-500 mb-1">Block Title</label>
                                    <input type="text" name="title" value="{{ $block->title }}" class="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-500 mb-1">Block Content</label>
                                    <textarea name="content" class="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-sm" rows="3">{{ $block->content }}</textarea>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-bold text-slate-500 mb-1">Sort Order</label>
                                        <input type="number" name="sort_order" value="{{ $block->sort_order }}" class="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-sm">
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
