@extends('layouts.admin')

@section('admin_title', 'Change Password - Dainely Admin')

@section('admin_content')
<div class="max-w-2xl mx-auto py-8">
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 bg-slate-50 border-b border-slate-200">
            <h2 class="text-lg font-bold text-slate-800">Change Password</h2>
        </div>

        <div class="p-6">
            @if(session('error'))
                <div class="mb-4 bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 rounded-lg text-sm font-medium">
                    {{ session('error') }}
                </div>
            @endif

            <form action="{{ route('admin.password') }}" method="POST" class="space-y-5">
                @csrf
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Current Password</label>
                    <input type="password" name="current_password" required class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm outline-none transition">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">New Password</label>
                    <input type="password" name="new_password" required minlength="8" class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm outline-none transition">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Confirm New Password</label>
                    <input type="password" name="new_password_confirmation" required minlength="8" class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm outline-none transition">
                </div>

                <div class="pt-2 flex items-center justify-end">
                    <button type="submit" class="bg-navy-700 hover:bg-navy-800 text-white font-bold py-2.5 px-6 rounded-lg text-sm transition">
                        Save New Password
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
