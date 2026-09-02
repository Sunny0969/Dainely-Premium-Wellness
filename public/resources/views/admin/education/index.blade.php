@extends('layouts.admin')
@section('admin_title', 'Education Pages Manager')

@section('admin_content')
<div class="mb-6 flex justify-between items-center">
    <p class="text-sm text-slate-600">Create, edit, and delete database-backed education pages for your storefront.</p>
    <a href="/dainely-admin-panel/education/create" class="bg-slate-800 hover:bg-slate-900 text-white font-bold px-4 py-2 rounded-lg text-sm flex items-center gap-1.5 shadow-sm">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Add New Page
    </a>
</div>

<div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 font-semibold text-xs uppercase tracking-wider">
                    <th class="px-6 py-4">Title</th>
                    <th class="px-6 py-4">Slug</th>
                    <th class="px-6 py-4">Category</th>
                    
                    <th class="px-6 py-4">Status</th>
                    <th class="px-6 py-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 text-sm text-slate-600">
                @forelse($pages as $page)
                <tr class="hover:bg-slate-50/80 transition duration-150">
                    <td class="px-6 py-4 font-semibold text-slate-800">
                        <a href="/dainely-admin-panel/education/{{ $page->id }}/edit" class="hover:underline hover:text-navy-700">{{ $page->title }}</a>
                    </td>
                    <td class="px-6 py-4 text-slate-500 font-mono text-xs">
                        {{ $page->slug }}
                    </td>
                    <td class="px-6 py-4">
                        @if($page->category)
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-blue-50 text-blue-700">{{ $page->category }}</span>
                        @else
                            <span class="text-slate-400 italic text-xs">Uncategorized</span>
                        @endif
                    </td>
                    
                    <td class="px-6 py-4">
                        @if($page->is_active)
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 text-xs font-semibold">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Active
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-slate-100 text-slate-600 text-xs font-semibold">
                                <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> Hidden
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-right text-xs font-semibold">
                        <div class="flex items-center justify-end gap-3">
                            <a href="/dainely-admin-panel/education/{{ $page->id }}/edit" class="text-navy-700 hover:text-navy-900">Edit</a>
                            <form action="/dainely-admin-panel/education/{{ $page->id }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this page?');" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-rose-600 hover:text-rose-800 font-semibold">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-12 text-center text-slate-400">
                        No education pages found. Click "Add New Page" to create one.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
