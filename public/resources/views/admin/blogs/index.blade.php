@extends('layouts.admin')

@section('admin_title', 'Blogs Manager')

@section('admin_content')
<div class="mb-6 flex justify-between items-center flex-wrap gap-4">
    <p class="text-sm text-slate-600">Create, edit, and delete database-backed blog posts for your storefront.</p>
    <div class="flex items-center gap-4">
        <form action="/dainely-admin-panel/blogs" method="GET" class="flex items-center gap-2">
            <select name="category_id" class="rounded-lg border border-slate-300 px-3 py-2 text-sm" onchange="this.form.submit()">
                <option value="">All Categories</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
            <select name="status" class="rounded-lg border border-slate-300 px-3 py-2 text-sm" onchange="this.form.submit()">
                <option value="">All Statuses</option>
                <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Published</option>
                <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
            </select>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search title..." class="rounded-lg border border-slate-300 px-3 py-2 text-sm min-w-[200px]">
            <button type="submit" class="bg-slate-800 hover:bg-slate-900 text-white font-bold px-4 py-2 rounded-lg text-sm">Filter</button>
            @if(request()->filled('search') || request()->filled('category_id') || request()->filled('status'))
                <a href="/dainely-admin-panel/blogs" class="text-sm text-slate-500 hover:underline">Clear</a>
            @endif
        </form>
        <a href="/dainely-admin-panel/blogs/create" class="bg-slate-800 hover:bg-slate-900 text-white font-bold px-4 py-2 rounded-lg text-sm flex items-center gap-1.5 shadow-sm">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Create Blog Post
        </a>
    </div>
</div>

<div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 font-semibold text-xs uppercase tracking-wider">
                    <th class="px-6 py-4">Cover</th>
                    <th class="px-6 py-4">Title (EN)</th>
                    <th class="px-6 py-4">Category</th>
                    <th class="px-6 py-4">Status</th>
                    <th class="px-6 py-4">Author</th>
                    <th class="px-6 py-4">Date</th>
                    <th class="px-6 py-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 text-sm text-slate-600">
                @forelse($posts as $post)
                    @php 
                        $enTranslation = $post->translation('en'); 
                        $catName = $post->category ? (is_array($post->category->name) ? ($post->category->name[app()->getLocale()] ?? $post->category->name['en'] ?? '') : $post->category->name) : '';
                    @endphp
                    <tr class="hover:bg-slate-50/80 transition duration-150">
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($post->featured_image)
                                <img src="{{ asset('images/' . $post->featured_image) }}" alt="cover" class="w-12 h-12 object-cover rounded-lg border border-slate-200">
                            @else
                                <div class="w-12 h-12 bg-slate-100 rounded-lg flex items-center justify-center text-slate-400 text-xs border border-dashed border-slate-300">No img</div>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <span class="font-bold text-slate-800 block">{{ $enTranslation ? $enTranslation->title : 'Untitled' }}</span>
                            <span class="text-xs text-slate-400">ID: {{ $post->id }} · {{ $enTranslation ? $enTranslation->slug : '' }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2.5 py-1 rounded-full bg-slate-100 text-slate-700 text-xs font-semibold">{{ $catName }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($post->is_published)
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 text-xs font-semibold">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Live
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-slate-100 text-slate-600 text-xs font-semibold">
                                    <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> Draft
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-slate-500 text-xs">
                            {{ $post->author_name }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-xs text-slate-400">
                            {{ $post->published_at ? $post->published_at->format('M d, Y') : 'Draft' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-xs font-semibold">
                            <div class="flex items-center justify-end gap-3">
                                <a href="/dainely-admin-panel/blogs/{{ $post->id }}/edit" class="text-navy-700 hover:text-navy-900">Edit</a>
                                <form action="/dainely-admin-panel/blogs/{{ $post->id }}/delete" method="POST" onsubmit="return confirm('Are you sure you want to delete this blog post?');" class="inline">
                                    @csrf
                                    <button type="submit" class="text-rose-600 hover:text-rose-800 font-semibold">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-slate-400">
                            No blog posts found. Click "Create Blog Post" to add one.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if(method_exists($posts, 'links') && $posts->hasPages())
        <div class="px-6 py-4 border-t border-slate-200">
            {{ $posts->links() }}
        </div>
    @endif
</div>
@endsection
