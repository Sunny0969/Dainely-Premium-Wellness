@extends('layouts.admin')

@section('admin_title', 'Education Pages (CMS blocks)')

@section('admin_content')
<div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="px-6 py-4 bg-slate-50 border-b border-slate-200">
        <h2 class="text-lg font-bold text-slate-800">Education Catalog</h2>
        <p class="text-xs text-slate-500 mt-1">Static route pages — attach <code>page_blocks</code> and manage FAQs via FAQs Manager (type: Education).</p>
    </div>
    <div class="divide-y divide-slate-200">
        @foreach($pages as $edu)
            <div class="p-6 flex justify-between items-center gap-4">
                <div>
                    <strong class="block text-slate-900">{{ $edu['title'] }}</strong>
                    <span class="text-xs text-slate-500">id={{ $edu['id'] }} · slug={{ $edu['slug'] }} · route={{ $edu['route'] }}</span>
                </div>
                <a href="/{{ $adminBase }}/education/{{ $edu['id'] }}/edit" class="text-sm font-bold text-navy-700 hover:text-navy-900">
                    Manage blocks →
                </a>
            </div>
        @endforeach
    </div>
</div>
@endsection
