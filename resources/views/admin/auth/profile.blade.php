@extends('layouts.admin')
@section('admin_title', 'Admin Settings')
@section('admin_content')
<p class="mb-4 text-slate-500">If you change your password here, make sure to remember it for your next login.</p>
<div class="max-w-2xl bg-white p-8 rounded-xl shadow-sm border border-slate-200">
    <h1 class="text-2xl font-bold mb-6">Admin Settings</h1>
    
    @If(session('success'))
        <div class="bg-emerald-100 text-emerald-800 p-4 rounded mb-6 font-medium">
            {{ session('success') }}
        </div>
    @endif
    @If($errors->any())
        <div class="bg-rose-100 text-rose-800 p-4 rounded mb-6">
            <ul class="list-disc pl-5">
                @foreach($errors->all() as $error) <li>{{ $error }}</li> @endforeach
            </ul>
        </div>
    @endif

    <form action="/dainely-admin-panel/profile" method="POST">
        @csrf
        <div class="space-y-6">
            <div>
                <label class="block font-bold mb-2 text-slate-700">Admin Email</label>
                <input type="email" name="email" value="{{ old('email', config('app.admin_email', env('ADMIN_EMAIL', 'admin@dainelylab.com'))) }}" class="w-full border p-3 rounded-lg" required>
            </div>
            <div>
                <label class="block font-bold mb-2 text-slate-700">New Password</label>
                <input type="text" name="password" value="{{ old('password', config('app.admin_password', env('ADMIN_PASSWORD', 'admin12345'))) }}" class="w-full border p-3 rounded-lg" required>
                <p class="text-sm text-slate-500 mt-2">Update this to change your login password.</p>
            </div>
            <div class="pt-4 border-t border-slate-200">
                <button type="submit" class="bg-navy-900 hover:bg-navy-950 text-white font-bold py-3 px-6 rounded-lg shadow-sm transition-colors">
                    Update Admin Credentials
                </button>
            </div>
        </div>
    </form>
</div>
@endsection