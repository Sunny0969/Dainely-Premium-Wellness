@extends('layouts.admin')

@section('admin_title', 'Edit Overlays for ' . $product->title)

@section('admin_content')
<div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm" x-data="{ currentTab: 'en' }">

    {{-- Tabs header --}}
    <div class="flex border-b border-slate-200 mb-8">
        @foreach(['en' => 'English', 'fr' => 'Français', 'de' => 'Deutsch'] as $lang => $label)
            <button type="button" @click="currentTab = '{{ $lang }}'" 
                :class="currentTab === '{{ $lang }}' ? 'border-navy-600 text-navy-600' : 'border-transparent text-slate-500 hover:text-slate-700'"
                class="px-6 py-3 border-b-2 font-bold text-sm transition">
                {{ $label }} ({{ $lang }})
            </button>
        @endforeach
    </div>

    {{-- Editor form --}}
    <form action="/admin/products/{{ $product->id }}/update" method="POST" class="space-y-6">
        @csrf

        @foreach(['en', 'fr', 'de'] as $locale)
            <div x-show="currentTab === '{{ $locale }}'" class="space-y-6">
                <h3 class="text-base font-bold text-slate-800 border-b border-slate-100 pb-2">Local Settings ({{ strtoupper($locale) }})</h3>

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
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Overview Description (Markdown or HTML)</label>
                        <textarea name="contents[{{ $locale }}][overview]" rows="3" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">{{ $contents[$locale]->overview }}</textarea>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Benefits</label>
                        <textarea name="contents[{{ $locale }}][benefits]" rows="3" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">{{ $contents[$locale]->benefits }}</textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">How It Works</label>
                        <textarea name="contents[{{ $locale }}][how_it_works]" rows="3" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">{{ $contents[$locale]->how_it_works }}</textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Who Is It For</label>
                        <textarea name="contents[{{ $locale }}][who_is_it_for]" rows="3" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">{{ $contents[$locale]->who_is_it_for }}</textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Specifications</label>
                        <textarea name="contents[{{ $locale }}][specifications]" rows="3" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">{{ $contents[$locale]->specifications }}</textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Care & Maintenance</label>
                        <textarea name="contents[{{ $locale }}][care]" rows="3" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">{{ $contents[$locale]->care }}</textarea>
                    </div>
                </div>
            </div>
        @endforeach

        <div class="flex justify-end pt-6 border-t border-slate-100">
            <button type="submit" class="bg-slate-800 hover:bg-slate-900 text-white font-bold px-8 py-3 rounded-lg text-sm transition">
                Save All Locales
            </button>
        </div>
    </form>

    {{-- Product Content Blocks manager --}}
    <div class="border-t border-slate-200 pt-12 mt-12 space-y-8">
        <h2 class="text-xl font-bold text-slate-800">Product Page Layout Blocks</h2>
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            {{-- Add block form --}}
            <div class="bg-slate-50 p-6 rounded-xl border border-slate-200 h-fit">
                <h3 class="text-base font-bold text-slate-800 mb-4">Add Content Block</h3>
                <form action="/admin/products/{{ $product->id }}/blocks" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Locale</label>
                        <select name="locale" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm bg-white">
                            <option value="en">English (en)</option>
                            <option value="fr">French (fr)</option>
                            <option value="de">German (de)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Block Type</label>
                        <select name="block_type" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm bg-white">
                            <option value="benefits">Benefits (benefits)</option>
                            <option value="how-it-works">How It Works (how-it-works)</option>
                            <option value="testimonials">Testimonials (testimonials)</option>
                            <option value="faqs">FAQs Accordion (faqs)</option>
                            <option value="cta">Call to Action (cta)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Block Title</label>
                        <input type="text" name="title" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm bg-white">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Content (HTML/Markdown)</label>
                        <textarea name="content" rows="3" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm bg-white"></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Sort Order</label>
                        <input type="number" name="sort_order" value="0" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm bg-white">
                    </div>

                    <button type="submit" class="w-full bg-slate-800 hover:bg-slate-900 text-white font-bold py-2 rounded-lg text-sm transition">
                        Add Block
                    </button>
                </form>
            </div>

            {{-- Existing Blocks list --}}
            <div class="lg:col-span-2 bg-white rounded-xl border border-slate-200 overflow-hidden">
                <div class="px-6 py-4 bg-slate-50 border-b border-slate-200">
                    <h3 class="text-base font-bold text-slate-800">Current Blocks</h3>
                </div>

                <div class="divide-y divide-slate-200">
                    @forelse($product->pageBlocks as $block)
                        <div class="p-6 space-y-4" x-data="{ editing: false }">
                            <div class="flex justify-between items-start gap-4">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-600 text-xs font-bold uppercase">
                                            {{ $block->locale }}
                                        </span>
                                        <span class="px-2 py-0.5 rounded bg-navy-50 text-navy-700 text-xs font-bold uppercase tracking-wider">
                                            {{ $block->block_type }}
                                        </span>
                                    </div>
                                    @if($block->title)
                                        <strong class="block text-slate-800 text-base mt-2">{{ $block->title }}</strong>
                                    @endif
                                    <div class="text-slate-500 text-xs mt-1 font-mono max-w-lg truncate">{{ $block->content }}</div>
                                    <span class="inline-block text-xs font-semibold text-slate-400 mt-2">Order: {{ $block->sort_order }} | Visible: {{ $block->visible ? 'Yes' : 'No' }}</span>
                                </div>

                                <div class="flex gap-2">
                                    <button @click="editing = !editing" class="text-navy-600 hover:text-navy-800 text-sm font-bold">
                                        Edit
                                    </button>
                                    <form action="/admin/products/{{ $product->id }}/blocks/{{ $block->id }}/delete" method="POST" onsubmit="return confirm('Are you sure you want to delete this block?');">
                                        @csrf
                                        <button type="submit" class="text-rose-600 hover:text-rose-800 text-sm font-bold">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </div>

                            {{-- Edit block inline --}}
                            <div x-show="editing" class="p-4 bg-slate-50 rounded-lg border border-slate-200">
                                <form action="/admin/products/{{ $product->id }}/blocks/{{ $block->id }}/update" method="POST" class="space-y-4">
                                    @csrf
                                    <div>
                                        <label class="block text-xs font-bold text-slate-500 mb-1">Block Title</label>
                                        <input type="text" name="title" value="{{ $block->title }}" class="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-sm bg-white">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-500 mb-1">Block Content</label>
                                        <textarea name="content" class="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-sm bg-white" rows="3">{{ $block->content }}</textarea>
                                    </div>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-xs font-bold text-slate-500 mb-1">Sort Order</label>
                                            <input type="number" name="sort_order" value="{{ $block->sort_order }}" class="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-sm bg-white">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-bold text-slate-500 mb-1">Visible</label>
                                            <select name="visible" class="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-sm bg-white">
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
                        <div class="px-6 py-8 text-center text-slate-400">No layout blocks added for this product yet.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
