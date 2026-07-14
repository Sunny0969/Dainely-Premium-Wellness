@extends('layouts.admin')

@section('admin_title', 'Webhook Logs')

@section('admin_content')
<div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="px-6 py-4 bg-slate-50 border-b border-slate-200 flex justify-between items-center">
        <h2 class="text-lg font-bold text-slate-800">Incoming Webhook Transactions</h2>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-100 text-slate-600 text-sm font-semibold border-b border-slate-200">
                    <th class="px-6 py-3">ID</th>
                    <th class="px-6 py-3">Source</th>
                    <th class="px-6 py-3">Event Type</th>
                    <th class="px-6 py-3">Status</th>
                    <th class="px-6 py-3">Attempts</th>
                    <th class="px-6 py-3">Error</th>
                    <th class="px-6 py-3">Processed At</th>
                    <th class="px-6 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 text-slate-700 text-sm">
                @forelse($logs as $log)
                    <tr class="hover:bg-slate-50">
                        <td class="px-6 py-4 font-mono font-bold">{{ $log->id }}</td>
                        <td class="px-6 py-4 capitalize font-semibold">{{ $log->source }}</td>
                        <td class="px-6 py-4 font-mono text-xs">{{ $log->event_type }}</td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 rounded-full text-xs font-semibold 
                                @if($log->status === 'processed') bg-emerald-50 text-emerald-700
                                @elseif($log->status === 'pending') bg-amber-50 text-amber-700
                                @elseif($log->status === 'failed') bg-rose-50 text-rose-700
                                @else bg-slate-100 text-slate-700
                                @endif">
                                {{ $log->status }}
                            </span>
                        </td>
                        <td class="px-6 py-4 font-semibold">{{ $log->attempts ?? 0 }}</td>
                        <td class="px-6 py-4 text-xs text-rose-600 font-mono max-w-xs truncate" title="{{ $log->error }}">
                            {{ $log->error ?: '—' }}
                        </td>
                        <td class="px-6 py-4 text-xs">
                            {{ $log->processed_at ? $log->processed_at->format('Y-m-d H:i') : '—' }}
                        </td>
                        <td class="px-6 py-4 text-right">
                            @if(in_array($log->status, ['failed', 'dead']))
                                <form action="/admin/webhooks/{{ $log->id }}/retry" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="bg-navy-600 hover:bg-navy-700 text-white text-xs font-bold px-3 py-1.5 rounded transition">
                                        Retry
                                    </button>
                                </form>
                            @else
                                <span class="text-slate-400 text-xs">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-8 text-center text-slate-400">No webhook logs available.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($logs->hasPages())
        <div class="px-6 py-4 bg-slate-50 border-t border-slate-200">
            {{ $logs->links() }}
        </div>
    @endif
</div>
@endsection
