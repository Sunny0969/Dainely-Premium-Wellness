@extends('layouts.admin')

@section('admin_title', 'AI Knowledge Signals')

@section('admin_content')
<div class="space-y-6">
    {{-- Filters --}}
    <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex flex-wrap gap-4 items-center justify-between">
        <form action="/admin/signals" method="GET" class="flex gap-4 items-center">
            <select name="locale" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="">All Locales</option>
                <option value="en" {{ request('locale') === 'en' ? 'selected' : '' }}>English (en)</option>
                <option value="fr" {{ request('locale') === 'fr' ? 'selected' : '' }}>French (fr)</option>
                <option value="de" {{ request('locale') === 'de' ? 'selected' : '' }}>German (de)</option>
            </select>

            <select name="approved" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="">All Approval Status</option>
                <option value="1" {{ request('approved') === '1' ? 'selected' : '' }}>Approved</option>
                <option value="0" {{ request('approved') === '0' ? 'selected' : '' }}>Unapproved</option>
            </select>

            <button type="submit" class="bg-slate-800 hover:bg-slate-900 text-white px-4 py-2 rounded-lg text-sm font-semibold">
                Filter
            </button>
        </form>
    </div>

    {{-- Signals Table --}}
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-100 text-slate-600 text-sm font-semibold border-b border-slate-200">
                        <th class="px-6 py-3">Product</th>
                        <th class="px-6 py-3">Locale</th>
                        <th class="px-6 py-3">Speaker</th>
                        <th class="px-6 py-3">Question / Answer</th>
                        <th class="px-6 py-3">Approved</th>
                        <th class="px-6 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 text-slate-700 text-sm">
                    @forelse($signals as $signal)
                        <tr class="hover:bg-slate-50" x-data="{ editing: false }">
                            <td class="px-6 py-4 font-semibold">
                                {{ $signal->product ? $signal->product->title : 'Unknown Product' }}
                            </td>
                            <td class="px-6 py-4 uppercase font-bold">{{ $signal->locale }}</td>
                            <td class="px-6 py-4 capitalize">{{ $signal->speaker_type }}</td>
                            
                            {{-- Read / Edit View --}}
                            <td class="px-6 py-4 max-w-lg">
                                <div x-show="!editing" class="space-y-1">
                                    <strong class="block text-slate-900">{{ $signal->question }}</strong>
                                    <p class="text-slate-500 text-xs">{{ $signal->answer }}</p>
                                </div>

                                <div x-show="editing" class="mt-2">
                                    <form action="/admin/signals/{{ $signal->id }}/update" method="POST" class="space-y-3">
                                        @csrf
                                        <div>
                                            <label class="block text-xs font-bold text-slate-500 mb-1">Question</label>
                                            <input type="text" name="question" value="{{ $signal->question }}" class="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-sm">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-bold text-slate-500 mb-1">Answer</label>
                                            <textarea name="answer" class="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-sm" rows="2">{{ $signal->answer }}</textarea>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-bold text-slate-500 mb-1">Speaker Type</label>
                                            <select name="speaker_type" class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm">
                                                <option value="expert" {{ $signal->speaker_type === 'expert' ? 'selected' : '' }}>Expert</option>
                                                <option value="customer" {{ $signal->speaker_type === 'customer' ? 'selected' : '' }}>Customer</option>
                                                <option value="ai" {{ $signal->speaker_type === 'ai' ? 'selected' : '' }}>AI</option>
                                            </select>
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

                            {{-- Approval Status toggle form --}}
                            <td class="px-6 py-4">
                                <form action="/admin/signals/{{ $signal->id }}/toggle-approval" method="POST">
                                    @csrf
                                    <button type="submit" class="px-3 py-1 rounded-full text-xs font-bold transition
                                        @if($signal->approved) bg-emerald-100 text-emerald-700 hover:bg-emerald-200
                                        @else bg-rose-100 text-rose-700 hover:bg-rose-200
                                        @endif">
                                        {{ $signal->approved ? 'Approved' : 'Unapproved' }}
                                    </button>
                                </form>
                            </td>

                            <td class="px-6 py-4 text-right">
                                <button type="button" @click="editing = !editing" class="text-navy-600 hover:text-navy-800 text-xs font-bold">
                                    Edit
                                </button>
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
