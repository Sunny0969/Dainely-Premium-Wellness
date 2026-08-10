<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-900">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dainely Admin CMS Login</title>
    @vite(['resources/css/app.css'])
</head>
<body class="h-full flex items-center justify-center px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8 bg-slate-800 p-8 sm:p-10 rounded-2xl border border-slate-700 shadow-2xl">
        <div>
            <div class="flex justify-center">
                <img src="/images/Dainelycut.png" alt="Dainely" class="h-10 w-auto brightness-0 invert">
            </div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-white">
                Admin Portal
            </h2>
            <p class="mt-2 text-center text-sm text-slate-400">
                Log in to manage catalog overlays, block content, FAQs, and webhooks.
            </p>
        </div>

        @if(session('error'))
            <div class="bg-red-900/30 border border-red-500 text-red-200 text-sm p-4 rounded-lg">
                {{ session('error') }}
            </div>
        @endif

        @if(session('success'))
            <div class="bg-emerald-900/30 border border-emerald-500 text-emerald-200 text-sm p-4 rounded-lg">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="bg-red-900/30 border border-red-500 text-red-200 text-sm p-4 rounded-lg space-y-1">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form class="mt-8 space-y-6" action="/dainely-admin-panel/login" method="POST">
            @csrf
            <div class="rounded-md shadow-sm space-y-4">
                <div>
                    <label for="email-address" class="block text-sm font-semibold text-slate-300 mb-1">Email Address</label>
                    <input id="email-address" name="email" type="email" autocomplete="email" required value="{{ old('email') }}"
                        class="appearance-none rounded-lg relative block w-full px-3.5 py-2.5 border border-slate-300 bg-white placeholder-slate-400 text-slate-900 focus:outline-none focus:ring-2 focus:ring-navy-500 focus:border-navy-500 focus:z-10 sm:text-sm"
                        placeholder="admin@dainelylab.com">
                </div>
                <div>
                    <label for="password" class="block text-sm font-semibold text-slate-300 mb-1">Password</label>
                    <input id="password" name="password" type="password" autocomplete="current-password" required
                        class="appearance-none rounded-lg relative block w-full px-3.5 py-2.5 border border-slate-300 bg-white placeholder-slate-400 text-slate-900 focus:outline-none focus:ring-2 focus:ring-navy-500 focus:border-navy-500 focus:z-10 sm:text-sm"
                        placeholder="••••••••">
                </div>
            </div>

            <div>
                <button type="submit"
                    class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-bold rounded-lg text-white bg-navy-600 hover:bg-navy-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-slate-800 focus:ring-navy-500 transition-colors">
                    Sign In
                </button>
            </div>
        </form>
    </div>
</body>
</html>
