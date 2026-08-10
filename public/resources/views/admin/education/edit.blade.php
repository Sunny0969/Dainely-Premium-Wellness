@extends('layouts.admin')

@section('admin_title', 'Education blocks: ' . $page->title)

@section('admin_content')
<div class="mb-4">
    <a href="/{{ $adminBase }}/education" class="text-sm text-slate-500 hover:text-navy-700">← Education pages</a>
    <p class="text-sm text-slate-600 mt-2">CMS overlays for <strong>{{ $page->slug }}</strong> (catalog id {{ $page->id }}). FAQs: use <a class="underline" href="/{{ $adminBase }}/faqs">FAQs Manager</a> → Education.</p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm h-fit">
        <h2 class="text-lg font-bold text-slate-800 mb-6">Add Block</h2>
        <form action="/{{ $adminBase }}/education/{{ $page->id }}/blocks" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Locale</label>
                <select name="locale" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="en">en</option>
                    <option value="fr">fr</option>
                    <option value="de">de</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Block type</label>
                <select name="block_type" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    @foreach($blockTypes as $type)
                        <option value="{{ $type }}">{{ $type }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Title</label>
                <input type="text" name="title" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Content</label>
                <textarea name="content" rows="4" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></textarea>
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Sort order</label>
                <input type="number" name="sort_order" value="0" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>
            <button type="submit" class="w-full bg-slate-800 hover:bg-slate-900 text-white font-bold py-2.5 rounded-lg text-sm">Add block</button>
        </form>
    </div>

    <div class="lg:col-span-2 bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 bg-slate-50 border-b border-slate-200">
            <h2 class="text-lg font-bold text-slate-800">Existing blocks</h2>
        </div>
        <div class="divide-y divide-slate-200">
            @forelse($blocks as $block)
                <div class="p-6" x-data="{ editing: false }">
                    <div x-show="!editing">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="px-2 py-0.5 rounded bg-navy-50 text-navy-700 text-xs font-bold uppercase">{{ $block->block_type }}</span>
                            <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-600 text-xs font-bold uppercase">{{ $block->locale }}</span>
                            <span class="text-xs text-slate-400">order {{ $block->sort_order }} · {{ $block->visible ? 'visible' : 'hidden' }}</span>
                        </div>
                        <strong class="block text-slate-800">{{ $block->title ?: '(no title)' }}</strong>
                        <div class="text-slate-500 text-xs mt-1 font-mono max-w-lg truncate">{{ $block->content }}</div>
                        <div class="mt-3 flex gap-3">
                            <button type="button" @click="editing = true" class="text-xs font-bold text-navy-700">Edit</button>
                            <form action="/{{ $adminBase }}/education/{{ $page->id }}/blocks/{{ $block->id }}/delete" method="POST" onsubmit="return confirm('Delete block?');">
                                @csrf
                                <button type="submit" class="text-xs font-bold text-rose-600">Delete</button>
                            </form>
                        </div>
                    </div>
                    <form x-show="editing" action="/{{ $adminBase }}/education/{{ $page->id }}/blocks/{{ $block->id }}/update" method="POST" class="space-y-3">
                        @csrf
                        <input type="text" name="title" value="{{ $block->title }}" class="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-sm">
                        <textarea name="content" rows="3" class="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-sm">{{ $block->content }}</textarea>
                        <div class="grid grid-cols-2 gap-3">
                            <input type="number" name="sort_order" value="{{ $block->sort_order }}" class="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-sm">
                            <select name="visible" class="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-sm">
                                <option value="1" @selected($block->visible)>Visible</option>
                                <option value="0" @selected(!$block->visible)>Hidden</option>
                            </select>
                        </div>
                        <div class="flex gap-2">
                            <button type="submit" class="bg-emerald-600 text-white text-xs font-bold px-3 py-1.5 rounded">Save</button>
                            <button type="button" @click="editing = false" class="bg-slate-200 text-xs font-bold px-3 py-1.5 rounded">Cancel</button>
                        </div>
                    </form>
                </div>
            @empty
                <div class="px-6 py-8 text-center text-slate-400">No blocks yet.</div>
            @endforelse
        </div>
    </div>
</div>
@endsection
