<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Dainely Admin CMS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full flex flex-col md:flex-row" x-data="{ sidebarOpen: false }">

    {{-- Sidebar Navigation --}}
    <aside class="w-full md:w-64 bg-slate-900 text-slate-300 flex-shrink-0 flex flex-col">
        <div class="h-16 flex items-center justify-between px-6 bg-slate-950 text-white font-bold text-lg tracking-wider border-b border-slate-800">
            <span>DAINELY CMS</span>
            <button class="md:hidden text-slate-400 hover:text-white" @click="sidebarOpen = !sidebarOpen">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
        </div>

        <nav class="flex-1 px-4 py-6 space-y-1 overflow-y-auto" :class="sidebarOpen ? 'block' : 'hidden md:block'">
            <a href="/admin/dashboard" class="flex items-center px-4 py-2.5 rounded-lg font-medium hover:bg-slate-800 hover:text-white transition duration-150 {{ request()->is('admin/dashboard') ? 'bg-slate-800 text-white' : '' }}">
                Dashboard
            </a>
            <a href="/admin/products" class="flex items-center px-4 py-2.5 rounded-lg font-medium hover:bg-slate-800 hover:text-white transition duration-150 {{ request()->is('admin/products*') ? 'bg-slate-800 text-white' : '' }}">
                Products Overlay
            </a>
            <a href="/admin/landings" class="flex items-center px-4 py-2.5 rounded-lg font-medium hover:bg-slate-800 hover:text-white transition duration-150 {{ request()->is('admin/landings*') ? 'bg-slate-800 text-white' : '' }}">
                Landing Pages
            </a>
            <a href="/admin/bundles" class="flex items-center px-4 py-2.5 rounded-lg font-medium hover:bg-slate-800 hover:text-white transition duration-150 {{ request()->is('admin/bundles*') ? 'bg-slate-800 text-white' : '' }}">
                Bundles & Offers
            </a>
            <a href="/admin/faqs" class="flex items-center px-4 py-2.5 rounded-lg font-medium hover:bg-slate-800 hover:text-white transition duration-150 {{ request()->is('admin/faqs*') ? 'bg-slate-800 text-white' : '' }}">
                FAQs Manager
            </a>
            <a href="/admin/signals" class="flex items-center px-4 py-2.5 rounded-lg font-medium hover:bg-slate-800 hover:text-white transition duration-150 {{ request()->is('admin/signals*') ? 'bg-slate-800 text-white' : '' }}">
                Knowledge Signals
            </a>
            <a href="/admin/related" class="flex items-center px-4 py-2.5 rounded-lg font-medium hover:bg-slate-800 hover:text-white transition duration-150 {{ request()->is('admin/related*') ? 'bg-slate-800 text-white' : '' }}">
                Internal Links
            </a>
            <a href="/admin/webhooks" class="flex items-center px-4 py-2.5 rounded-lg font-medium hover:bg-slate-800 hover:text-white transition duration-150 {{ request()->is('admin/webhooks*') ? 'bg-slate-800 text-white' : '' }}">
                Webhook Logs
            </a>
            <div class="pt-6 border-t border-slate-800 mt-6 space-y-2">
                <a href="/" class="flex items-center px-4 py-2.5 rounded-lg font-medium text-slate-400 hover:text-white transition duration-150">
                    ← Back to Storefront
                </a>
                <form action="/admin/logout" method="POST">
                    @csrf
                    <button type="submit" class="w-full flex items-center px-4 py-2.5 rounded-lg font-medium text-rose-400 hover:bg-rose-950 hover:text-rose-200 transition duration-150 text-left">
                        🚪 Log Out
                    </button>
                </form>
            </div>
        </nav>
    </aside>

    {{-- Main Admin Layout Area --}}
    <div class="flex-1 flex flex-col overflow-hidden">
        {{-- Header bar --}}
        <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-8 z-10 flex-shrink-0">
            <h1 class="text-xl font-bold text-slate-800">@yield('admin_title', 'CMS Dashboard')</h1>
            <div class="flex items-center gap-4">
                <span class="text-sm font-medium text-slate-600">Admin User</span>
            </div>
        </header>

        {{-- Scrollable Container --}}
        <main class="flex-1 overflow-y-auto p-8">
            {{-- Flash Messages --}}
            @if(session('success'))
                <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 rounded-lg text-emerald-800 text-sm font-semibold flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 p-4 bg-rose-50 border border-rose-200 rounded-lg text-rose-800 text-sm font-semibold flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @yield('admin_content')
        </main>
    </div>
</body>
</html>
