@extends('layouts.admin')

@section('admin_title', 'Polymorphic FAQs Manager')

@section('admin_content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

    {{-- Create FAQ Column --}}
    <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm h-fit">
        <h2 class="text-lg font-bold text-slate-800 mb-6">Create New FAQ</h2>
        <form action="/admin/faqs" method="POST" class="space-y-4" x-data="{ faqableType: 'App\\Models\\Supabase\\Product' }">
            @csrf
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Target Type</label>
                <select name="faqable_type" x-model="faqableType" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="App\Models\Supabase\Product">Product</option>
                    <option value="App\Models\Supabase\LandingPage">Landing Page</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Target Resource</label>
                {{-- Product selection --}}
                <select name="faqable_id" x-show="faqableType === 'App\\Models\\Supabase\\Product'" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    @foreach($products as $product)
                        <option value="{{ $product->id }}">{{ $product->title }}</option>
                    @endforeach
                </select>
                {{-- Landing Page selection --}}
                <select name="faqable_id" x-show="faqableType === 'App\\Models\\Supabase\\LandingPage'" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" disabled x-bind:disabled="faqableType !== 'App\\Models\\Supabase\\LandingPage'">
                    @foreach($landingPages as $page)
                        <option value="{{ $page->id }}">{{ $page->title }} ({{ $page->locale }})</option>
                    @endforeach
                </select>
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
                <label class="block text-sm font-semibold text-slate-700 mb-1">Question</label>
                <input type="text" name="question" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Answer</label>
                <textarea name="answer" required rows="3" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></textarea>
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Sort Order</label>
                <input type="number" name="sort_order" value="0" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>

            <button type="submit" class="w-full bg-slate-800 hover:bg-slate-900 text-white font-bold py-2.5 rounded-lg text-sm transition">
                Create FAQ
            </button>
        </form>
    </div>

    {{-- List FAQs Column --}}
    <div class="lg:col-span-2 bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 bg-slate-50 border-b border-slate-200">
            <h2 class="text-lg font-bold text-slate-800">Existing FAQs</h2>
        </div>

        <div class="divide-y divide-slate-200">
            @forelse($faqs as $faq)
                <div class="p-6" x-data="{ editing: false }">
                    <div class="flex justify-between items-start gap-4">
                        <div class="space-y-1 flex-1">
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-600 text-xs font-bold uppercase">
                                    {{ $faq->locale }}
                                </span>
                                <span class="text-xs text-slate-400">
                                    Linked to: {{ class_basename($faq->faqable_type) }} #{{ $faq->faqable_id }} 
                                    ({{ $faq->faqable ? $faq->faqable->title : 'Deleted' }})
                                </span>
                            </div>
                            <strong class="block text-slate-900 text-base">{{ $faq->question }}</strong>
                            <p class="text-slate-500 text-sm leading-relaxed">{{ $faq->answer }}</p>
                            <span class="inline-block text-xs font-semibold text-slate-400">Order: {{ $faq->sort_order }} | Status: {{ $faq->approved ? 'Approved' : 'Unapproved' }}</span>
                        </div>

                        <div class="flex gap-2">
                            <button @click="editing = !editing" class="text-navy-600 hover:text-navy-800 text-sm font-bold">
                                Edit
                            </button>
                            <form action="/admin/faqs/{{ $faq->id }}/delete" method="POST" onsubmit="return confirm('Are you sure you want to delete this FAQ?');">
                                @csrf
                                <button type="submit" class="text-rose-600 hover:text-rose-800 text-sm font-bold">
                                    Delete
                                </button>
                            </form>
                        </div>
                    </div>

                    {{-- Inline editing form --}}
                    <div x-show="editing" class="mt-4 p-4 bg-slate-50 rounded-lg border border-slate-200">
                        <form action="/admin/faqs/{{ $faq->id }}/update" method="POST" class="space-y-4">
                            @csrf
                            <div>
                                <label class="block text-xs font-bold text-slate-500 mb-1">Question</label>
                                <input type="text" name="question" value="{{ $faq->question }}" class="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-500 mb-1">Answer</label>
                                <textarea name="answer" class="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-sm" rows="3">{{ $faq->answer }}</textarea>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-slate-500 mb-1">Sort Order</label>
                                    <input type="number" name="sort_order" value="{{ $faq->sort_order }}" class="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-500 mb-1">Approved</label>
                                    <select name="approved" class="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-sm">
                                        <option value="1" {{ $faq->approved ? 'selected' : '' }}>Yes</option>
                                        <option value="0" {{ !$faq->approved ? 'selected' : '' }}>No</option>
                                    </select>
                                </div>
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
                </div>
            @empty
                <div class="px-6 py-8 text-center text-slate-400">No FAQs created yet.</div>
            @endforelse
        </div>

        @if($faqs->hasPages())
            <div class="px-6 py-4 bg-slate-50 border-t border-slate-200">
                {{ $faqs->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
