@extends('layouts.admin')

@section('admin_title', 'Landing Pages Manager')

@section('admin_content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

    {{-- Create Landing Page --}}
    <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm h-fit">
        <h2 class="text-lg font-bold text-slate-800 mb-6">Create Landing Page</h2>
        <form action="/admin/landings" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Title</label>
                <input type="text" name="title" required placeholder="e.g. Back Pain walking" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Slug</label>
                <input type="text" name="slug" required placeholder="e.g. back-pain-walking" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Locale</label>
                <select name="locale" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="en">English (en)</option>
                    <option value="fr">French (fr)</option>
                    <option value="de">German (de)</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">SEO Title</label>
                <input type="text" name="meta_title" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">SEO Description</label>
                <textarea name="meta_description" rows="2" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></textarea>
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Canonical URL</label>
                <input type="text" name="canonical_url" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Published</label>
                <select name="published" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="0">Draft</option>
                    <option value="1">Published</option>
                </select>
            </div>

            <button type="submit" class="w-full bg-slate-800 hover:bg-slate-900 text-white font-bold py-2.5 rounded-lg text-sm transition">
                Create Landing Page
            </button>
        </form>
    </div>

    {{-- List Landing Pages --}}
    <div class="lg:col-span-2 bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 bg-slate-50 border-b border-slate-200">
            <h2 class="text-lg font-bold text-slate-800">Existing Landing Pages</h2>
        </div>

        <div class="divide-y divide-slate-200">
            @forelse($landings as $landing)
                <div class="p-6 flex justify-between items-center gap-4">
                    <div class="space-y-1">
                        <div class="flex items-center gap-2">
                            <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-600 text-xs font-bold uppercase">
                                {{ $landing->locale }}
                            </span>
                            <span class="text-xs text-slate-400">
                                Path: /{{ $landing->locale }}/{{ $landing->slug }}
                            </span>
                        </div>
                        <strong class="block text-slate-900 text-lg">{{ $landing->title }}</strong>
                        <span class="inline-block text-xs font-semibold px-2 py-0.5 rounded {{ $landing->published ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                            {{ $landing->published ? 'Published' : 'Draft' }}
                        </span>
                    </div>

                    <div>
                        <a href="/admin/landings/{{ $landing->id }}/edit" class="bg-navy-600 hover:bg-navy-700 text-white text-xs font-bold px-3 py-2 rounded transition">
                            Edit Page & Blocks
                        </a>
                    </div>
                </div>
            @empty
                <div class="px-6 py-8 text-center text-slate-400">No landing pages created yet.</div>
            @endforelse
        </div>
    </div>
</div>
@endsection
