@extends('layouts.admin')

@section('admin_title', 'AI Knowledge Signals')

@section('admin_content')
<div class="space-y-6">
    <div class="flex justify-end">
        <a href="/{{ $adminBase }}/signals/json-ld" class="bg-navy-600 hover:bg-navy-700 text-white px-4 py-2 rounded-lg text-sm font-semibold shadow-sm">
            Manage JSON-LD Product Schemas
        </a>
    </div>

    {{-- Add New Hidden FAQ Form --}}
    <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
        <div class="mb-4">
            <h3 class="text-lg font-bold text-slate-900">Add Hidden FAQ (JSON-LD Only)</h3>
            <p class="text-xs text-slate-500">These FAQs will NOT be visible to customers on the website. They are injected directly into the page source (JSON-LD) specifically for Google and AI bots. (Note: You add in English, it will automatically translate to FR, DE, and NL).</p>
        </div>
        
        <form action="/{{ $adminBase }}/signals" method="POST" class="space-y-4">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="md:col-span-1">
                    <label class="block text-xs font-bold text-slate-700 mb-1">Product</label>
                    <select name="product_id" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm bg-white">
                        <option value="">Select a Product</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}">{{ $product->title }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 mb-1">Question (in English)</label>
                    <input type="text" name="question" required placeholder="e.g. Does this help with lower back pain?" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                
                <div class="md:col-span-3">
                    <label class="block text-xs font-bold text-slate-700 mb-1">Answer (in English)</label>
                    <textarea name="answer" required rows="2" placeholder="Provide the answer here..." class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></textarea>
                </div>
            </div>

            <div class="flex justify-end mt-4">
                <button type="submit" class="bg-navy-600 hover:bg-navy-700 text-white px-6 py-2 rounded-lg text-sm font-bold shadow-sm">
                    + Add FAQ
                </button>
            </div>
        </form>
    </div>

    {{-- Filters --}}
    <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex flex-wrap gap-4 items-center justify-between">
        <form action="/{{ $adminBase }}/signals" method="GET" class="flex flex-wrap gap-4 items-center w-full">
            <select name="product_id" class="rounded-lg border border-slate-300 px-3 py-2 text-sm max-w-xs">
                <option value="">All Products</option>
                @foreach($products as $prod)
                    <option value="{{ $prod->id }}" {{ request('product_id') == $prod->id ? 'selected' : '' }}>
                        {{ $prod->title }}
                    </option>
                @endforeach
            </select>

            <select name="approved" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="">All Statuses</option>
                <option value="1" {{ request('approved') === '1' ? 'selected' : '' }}>Active</option>
                <option value="0" {{ request('approved') === '0' ? 'selected' : '' }}>Inactive</option>
            </select>

            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search questions/answers..." class="rounded-lg border border-slate-300 px-3 py-2 text-sm flex-1 min-w-[200px]">

            <button type="submit" class="bg-slate-800 hover:bg-slate-900 text-white px-4 py-2 rounded-lg text-sm font-semibold">
                Filter
            </button>
            @if(request()->anyFilled(['product_id', 'approved', 'search']))
                <a href="/{{ $adminBase }}/signals" class="text-sm text-slate-500 hover:text-slate-800 underline">Clear</a>
            @endif
        </form>
    </div>

    {{-- Signals Table --}}
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-100 text-slate-600 text-sm font-semibold border-b border-slate-200">
                        <th class="px-6 py-3">Product</th>
                        <th class="px-6 py-3">Question / Answer</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 text-slate-700 text-sm">
                    @forelse($signals as $signal)
                        <tr class="hover:bg-slate-50" x-data="{ editing: false }">
                            <td class="px-6 py-4 font-semibold">
                                {{ $signal->product ? $signal->product->title : 'Unknown Product' }}
                            </td>
                            
                            {{-- Read / Edit View --}}
                            <td class="px-6 py-4 max-w-lg">
                                <div x-show="!editing" class="space-y-1">
                                    <strong class="block text-slate-900">{{ $signal->question }}</strong>
                                    <p class="text-slate-500 text-xs">{{ $signal->answer }}</p>
                                </div>

                                <div x-show="editing" class="mt-2">
                                    <form action="/{{ $adminBase }}/signals/{{ $signal->id }}/update" method="POST" class="space-y-3">
                                        @csrf
                                        <div>
                                            <label class="block text-xs font-bold text-slate-500 mb-1">Question</label>
                                            <input type="text" name="question" value="{{ $signal->question }}" class="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-sm">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-bold text-slate-500 mb-1">Answer</label>
                                            <textarea name="answer" class="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-sm" rows="2">{{ $signal->answer }}</textarea>
                                        </div>
                                        <div class="flex gap-2">
                                            <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold px-3 py-1.5 rounded transition">
                                                Save
                                            </button>
                                            <button type="button" @click="editing = false" class="bg-slate-200 hover:bg-slate-300 text-slate-700 text-xs font-bold px-3 py-1.5 rounded transition">
                                                Cancel
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </td>

                            {{-- Approval Status --}}
                            <td class="px-6 py-4">
                                <span class="px-3 py-1 rounded-full text-xs font-bold transition
                                    @if($signal->approved) bg-emerald-100 text-emerald-700
                                    @else bg-rose-100 text-rose-700
                                    @endif">
                                    {{ $signal->approved ? 'Active' : 'Inactive' }}
                                </span>
                            </td>

                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-3">
                                    <button type="button" @click="editing = !editing" class="text-slate-600 hover:text-slate-800 text-xs font-bold">
                                        Edit
                                    </button>
                                    <form action="/{{ $adminBase }}/signals/{{ $signal->id }}/toggle-approval" method="POST">
                                        @csrf
                                        <button type="submit" class="text-xs font-bold transition
                                            @if($signal->approved) text-rose-600 hover:text-rose-800
                                            @else text-emerald-600 hover:text-emerald-800
                                            @endif">
                                            {{ $signal->approved ? 'Deactivate' : 'Activate' }}
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-slate-400">No knowledge signals found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($signals->hasPages())
            <div class="px-6 py-4 bg-slate-50 border-t border-slate-200">
                {{ $signals->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
