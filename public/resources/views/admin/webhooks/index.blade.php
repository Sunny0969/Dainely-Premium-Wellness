@extends('layouts.admin')

@section('admin_title', 'Webhook Logs')

@section('admin_content')
<div class="space-y-6" x-data="{ openId: null }">

    {{-- Purpose --}}
    <div class="rounded-xl border border-slate-200 bg-white p-5">
        <h2 class="text-base font-bold text-slate-900 mb-2">What this panel maintains</h2>
        <p class="text-sm text-slate-600 mb-3">
            Incoming integration events are stored here, then applied to the catalog / orders / review cache.
            Failed items retry automatically every 5 minutes (scheduler) or manually via <strong>Retry</strong>.
        </p>
        <ul class="grid sm:grid-cols-2 lg:grid-cols-4 gap-3 text-xs text-slate-600">
            <li class="rounded-lg bg-slate-50 border border-slate-100 p-3"><strong class="text-slate-800">shopify</strong> — product create/update/delete syncs Supabase catalog; order fulfill/cancel/refund updates local orders</li>
            <li class="rounded-lg bg-slate-50 border border-slate-100 p-3"><strong class="text-slate-800">judge</strong> — clears &amp; warms Judge.me review caches so PDP ratings stay current</li>
            <li class="rounded-lg bg-slate-50 border border-slate-100 p-3"><strong class="text-slate-800">square</strong> — payment/refund status updates local orders (fallback checkout)</li>
            <li class="rounded-lg bg-slate-50 border border-slate-100 p-3"><strong class="text-slate-800">video-ai / wallpass</strong> — accepted &amp; logged for Phase 2 integrations</li>
        </ul>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
        @foreach([
            ['label' => 'Total', 'key' => 'total', 'class' => 'bg-slate-800 text-white'],
            ['label' => 'Pending', 'key' => 'pending', 'class' => 'bg-amber-50 text-amber-800 border border-amber-100'],
            ['label' => 'Processed', 'key' => 'processed', 'class' => 'bg-emerald-50 text-emerald-800 border border-emerald-100'],
            ['label' => 'Failed', 'key' => 'failed', 'class' => 'bg-rose-50 text-rose-800 border border-rose-100'],
            ['label' => 'Dead', 'key' => 'dead', 'class' => 'bg-slate-100 text-slate-700 border border-slate-200'],
        ] as $card)
            <a href="?status={{ $card['key'] === 'total' ? '' : $card['key'] }}{{ $source ? '&source='.$source : '' }}{{ $q ? '&q='.urlencode($q) : '' }}"
               class="rounded-xl px-4 py-3 {{ $card['class'] }} hover:opacity-90 transition">
                <p class="text-[11px] font-bold uppercase tracking-wider opacity-80">{{ $card['label'] }}</p>
                <p class="text-2xl font-bold mt-1">{{ number_format($stats[$card['key']] ?? 0) }}</p>
            </a>
        @endforeach
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-xl border border-slate-200 p-4 flex flex-col gap-3">
        <form method="GET" action="/{{ $adminBase }}/webhooks" class="flex flex-col lg:flex-row gap-3 lg:items-end">
            <div class="flex-1">
                <label class="block text-xs font-semibold text-slate-600 mb-1">Search event / error / ID</label>
                <input type="text" name="q" value="{{ $q }}" placeholder="e.g. products/update" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Status</label>
                <select name="status" class="rounded-lg border border-slate-300 px-3 py-2 text-sm min-w-[140px]">
                    <option value="">All</option>
                    @foreach(['pending','processed','failed','dead'] as $st)
                        <option value="{{ $st }}" @selected($status === $st)>{{ ucfirst($st) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Source</label>
                <select name="source" class="rounded-lg border border-slate-300 px-3 py-2 text-sm min-w-[140px]">
                    <option value="">All</option>
                    @foreach($sources as $src)
                        <option value="{{ $src }}" @selected($source === $src)>{{ $src }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="bg-slate-800 hover:bg-slate-900 text-white font-bold px-5 py-2 rounded-lg text-sm">Filter</button>
            <a href="/{{ $adminBase }}/webhooks" class="text-sm font-semibold text-slate-500 hover:text-slate-800 px-2 py-2">Reset</a>
        </form>
        <form action="/{{ $adminBase }}/webhooks/process-pending" method="POST" onsubmit="return confirm('Process stuck pending webhooks now?');">
            @csrf
            <button type="submit" class="bg-amber-600 hover:bg-amber-700 text-white font-bold px-4 py-2 rounded-lg text-sm">
                Process stuck pending
            </button>
        </form>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 bg-slate-50 border-b border-slate-200 flex justify-between items-center gap-3">
            <h2 class="text-lg font-bold text-slate-800">Incoming webhook transactions</h2>
            <span class="text-xs text-slate-500">Showing page {{ $logs->currentPage() }} / {{ max(1, $logs->lastPage()) }}</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-100 text-slate-600 text-sm font-semibold border-b border-slate-200">
                        <th class="px-4 py-3">ID</th>
                        <th class="px-4 py-3">When</th>
                        <th class="px-4 py-3">Source</th>
                        <th class="px-4 py-3">Event</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Attempts</th>
                        <th class="px-4 py-3">Error</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 text-slate-700 text-sm">
                    @forelse($logs as $log)
                        <tr class="hover:bg-slate-50 align-top">
                            <td class="px-4 py-3 font-mono font-bold">{{ $log->id }}</td>
                            <td class="px-4 py-3 text-xs whitespace-nowrap">
                                {{ optional($log->created_at)->format('Y-m-d H:i') ?: '—' }}
                                @if($log->processed_at)
                                    <div class="text-emerald-600 mt-0.5">✓ {{ $log->processed_at->format('H:i') }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3 capitalize font-semibold">{{ $log->source }}</td>
                            <td class="px-4 py-3 font-mono text-xs break-all max-w-[180px]">{{ $log->event_type }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 rounded-full text-xs font-semibold
                                    @if($log->status === 'processed') bg-emerald-50 text-emerald-700
                                    @elseif($log->status === 'pending') bg-amber-50 text-amber-700
                                    @elseif($log->status === 'failed') bg-rose-50 text-rose-700
                                    @else bg-slate-100 text-slate-700
                                    @endif">
                                    {{ $log->status }}
                                </span>
                            </td>
                            <td class="px-4 py-3 font-semibold">{{ $log->attempts ?? 0 }}/{{ \App\Models\Supabase\WebhookLog::MAX_ATTEMPTS }}</td>
                            <td class="px-4 py-3 text-xs text-rose-600 font-mono max-w-[220px] truncate" title="{{ $log->error }}">
                                {{ $log->error ?: '—' }}
                            </td>
                            <td class="px-4 py-3 text-right space-x-2 whitespace-nowrap">
                                <button type="button" @click="openId = openId === {{ $log->id }} ? null : {{ $log->id }}"
                                        class="text-xs font-bold text-navy-700 hover:underline">
                                    Payload
                                </button>
                                @if(in_array($log->status, ['failed', 'dead', 'pending'], true))
                                    <form action="/{{ $adminBase }}/webhooks/{{ $log->id }}/retry" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="bg-navy-600 hover:bg-navy-700 text-white text-xs font-bold px-3 py-1.5 rounded transition">
                                            Retry
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                        <tr x-show="openId === {{ $log->id }}" x-cloak class="bg-slate-50">
                            <td colspan="8" class="px-4 py-3">
                                <pre class="text-[11px] leading-relaxed overflow-x-auto max-h-64 bg-white border border-slate-200 rounded-lg p-3 text-slate-700">{{ json_encode($log->payload, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) }}</pre>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-10 text-center text-slate-400">
                                No webhook logs yet. When Shopify / Judge.me / Square send events, they appear here and update the site data automatically.
                            </td>
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
</div>
@endsection
