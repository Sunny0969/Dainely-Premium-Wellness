@extends('layouts.admin')

@section('admin_title', 'CMS Dashboard Overview')

@section('admin_content')
<div class="space-y-8">
    {{-- Metric Cards Grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm flex flex-col justify-between">
            <span class="text-sm font-semibold text-slate-500 uppercase tracking-wider">Synced Products</span>
            <span class="text-3xl font-bold text-slate-800 mt-2">{{ $metrics['products_count'] }}</span>
        </div>

        <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm flex flex-col justify-between">
            <span class="text-sm font-semibold text-slate-500 uppercase tracking-wider">Landing Pages</span>
            <span class="text-3xl font-bold text-slate-800 mt-2">{{ $metrics['landings_count'] }}</span>
        </div>

        <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm flex flex-col justify-between">
            <span class="text-sm font-semibold text-slate-500 uppercase tracking-wider">Active Bundles</span>
            <span class="text-3xl font-bold text-slate-800 mt-2">{{ $metrics['bundles_count'] }}</span>
        </div>

        <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm flex flex-col justify-between">
            <span class="text-sm font-semibold text-slate-500 uppercase tracking-wider">Failed Webhooks</span>
            <span class="text-3xl font-bold text-rose-600 mt-2">
                {{ $metrics['webhooks_failed'] }} <span class="text-sm text-slate-500">/ {{ $metrics['webhooks_dead'] }} dead</span>
            </span>
        </div>
    </div>

    {{-- Recent user activity log table --}}
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 bg-slate-50 border-b border-slate-200">
            <h2 class="text-lg font-bold text-slate-800">Recent User Activities</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-100 text-slate-600 text-sm font-semibold border-b border-slate-200">
                        <th class="px-6 py-3">Visitor ID</th>
                        <th class="px-6 py-3">Event Type</th>
                        <th class="px-6 py-3">Item Type</th>
                        <th class="px-6 py-3">Occurred At</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 text-slate-700">
                    @forelse($metrics['recent_activities'] as $act)
                        <tr class="hover:bg-slate-50">
                            <td class="px-6 py-4 font-mono text-xs truncate max-w-xs">{{ $act->visitor_id }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $act->event_type === 'purchase' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-700' }}">
                                    {{ $act->event_type }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm">{{ class_basename($act->item_type) }} (ID: {{ $act->item_id }})</td>
                            <td class="px-6 py-4 text-sm">{{ $act->created_at->diffForHumans() }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-slate-400">No user activities logged yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
