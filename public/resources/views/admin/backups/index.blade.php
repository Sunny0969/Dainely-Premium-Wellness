@extends('layouts.admin')

@section('admin_title', 'CMS Backups')

@section('admin_content')
<div class="max-w-5xl mx-auto space-y-6">

    @if(session('success'))
        <div class="bg-emerald-50 text-emerald-700 p-4 rounded-lg border border-emerald-200 font-medium">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="bg-red-50 text-red-700 p-4 rounded-lg border border-red-200 font-medium">
            {{ session('error') }}
        </div>
    @endif

    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 py-5 bg-slate-50 border-b border-slate-200 flex justify-between items-center">
            <div>
                <h2 class="text-lg font-bold text-slate-800">Daily CMS Backups</h2>
                <p class="text-sm text-slate-500 mt-1">Automatic JSON backups run daily at 12:00 AM UK time.</p>
            </div>
            
            <form action="{{ url('dainely-admin-panel/backups/create') }}" method="POST">
                @csrf
                <button type="submit" class="bg-slate-800 hover:bg-slate-900 text-white font-bold py-2.5 px-5 rounded-lg text-sm transition inline-flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                    Run Manual Backup
                </button>
            </form>
        </div>

        <div class="divide-y divide-slate-100">
            @forelse($backups as $backup)
                <div class="px-6 py-4 flex items-center justify-between hover:bg-slate-50 transition">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 bg-slate-100 rounded-lg flex items-center justify-center text-slate-400">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </div>
                        <div>
                            <strong class="text-slate-900 block font-mono text-sm">{{ $backup['name'] }}</strong>
                            <span class="text-xs text-slate-500">{{ $backup['date'] }} &bull; {{ $backup['size'] }}</span>
                        </div>
                    </div>
                    
                    <a href="{{ url('dainely-admin-panel/backups/download/' . $backup['name']) }}" target="_blank" class="text-navy-600 hover:text-navy-800 font-bold text-sm bg-navy-50 hover:bg-navy-100 px-3 py-1.5 rounded transition">
                        Download
                    </a>
                </div>
            @empty
                <div class="p-8 text-center text-slate-500">
                    No backups found yet. Click the button above to run your first backup!
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
