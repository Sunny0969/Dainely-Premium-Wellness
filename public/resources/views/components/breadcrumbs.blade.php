{{-- Phase 2 §7.3 — accessible breadcrumb trail --}}
@props(['items' => []])
@if(count($items))
<nav class="bg-slate-50 border-b border-slate-100" aria-label="Breadcrumb">
  <div class="container-site py-3">
    <ol class="flex flex-wrap items-center gap-2 text-sm text-slate-500">
      @foreach($items as $i => $item)
        <li class="flex items-center gap-2">
          @if($i > 0)
            <svg class="w-3 h-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
          @endif
          @if(!empty($item['url']) && !$loop->last)
            <a href="{{ $item['url'] }}" class="hover:text-navy-700 transition-colors">{{ $item['name'] }}</a>
          @else
            <span class="{{ $loop->last ? 'text-navy-800 font-medium' : '' }}">{{ $item['name'] }}</span>
          @endif
        </li>
      @endforeach
    </ol>
  </div>
</nav>
@endif
