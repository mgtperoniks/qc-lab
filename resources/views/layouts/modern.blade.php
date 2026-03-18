<!DOCTYPE html>
<html class="light" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'QC Lab System' }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        @font-face {
            font-family: 'Inter';
            src: url("{{ asset('fonts/inter.woff2') }}") format('woff2');
            font-weight: 100 900;
            font-display: swap;
            font-style: normal;
        }
        @font-face {
            font-family: 'Material Symbols Outlined';
            src: url("{{ asset('fonts/material-symbols.woff2') }}") format('woff2');
            font-weight: normal;
            font-style: normal;
            display: inline-block;
        }

        body { font-family: 'Inter', sans-serif; }
        .material-symbols-outlined {
            font-family: 'Material Symbols Outlined';
            font-weight: normal;
            font-style: normal;
            font-size: 24px;
            line-height: 1;
            letter-spacing: normal;
            text-transform: none;
            display: inline-block;
            white-space: nowrap;
            word-wrap: normal;
            direction: ltr;
            -webkit-font-feature-settings: 'liga';
            -webkit-font-smoothing: antialiased;
        }
        [v-cloak] { display: none; }

        /* ===================================================
           LEGACY BROWSER FALLBACK (Windows 7 / Chrome < 49)
           Tailwind v4 uses oklch() colors + CSS variables
           which are unsupported in old Chrome.
           This block overrides with plain HEX for old browsers.
           @supports not (color: oklch(0 0 0)) targets ONLY
           browsers that don't support oklch.
           =================================================== */
        @supports not (color: oklch(0 0 0)) {
            /* Primary Brand Color */
            .bg-primary        { background-color: #0f74bd !important; }
            .text-primary      { color: #0f74bd !important; }
            .border-primary    { border-color: #0f74bd !important; }
            .hover\:text-primary:hover { color: #0f74bd !important; }
            .focus\:ring-primary:focus { outline-color: #0f74bd !important; }

            /* Primary tints */
            .bg-primary\/5    { background-color: rgba(15,116,189,0.05) !important; }
            .bg-primary\/10   { background-color: rgba(15,116,189,0.10) !important; }
            .bg-primary\/20   { background-color: rgba(15,116,189,0.20) !important; }
            .bg-primary\/90   { background-color: rgba(15,116,189,0.90) !important; }
            .hover\:bg-primary\/5:hover   { background-color: rgba(15,116,189,0.05) !important; }
            .hover\:bg-primary\/10:hover  { background-color: rgba(15,116,189,0.10) !important; }
            .hover\:bg-primary\/90:hover  { background-color: rgba(15,116,189,0.90) !important; }
            .border-primary\/10 { border-color: rgba(15,116,189,0.10) !important; }
            .border-primary\/30 { border-color: rgba(15,116,189,0.30) !important; }

            /* Background */
            .bg-background-light  { background-color: #f6f7f8 !important; }
            .bg-background-dark   { background-color: #101a22 !important; }

            /* Slate */
            .bg-slate-50    { background-color: #f8fafc !important; }
            .bg-slate-100   { background-color: #f1f5f9 !important; }
            .bg-slate-200   { background-color: #e2e8f0 !important; }
            .bg-slate-400   { background-color: #94a3b8 !important; }
            .bg-slate-700   { background-color: #334155 !important; }
            .bg-slate-800   { background-color: #1e293b !important; }
            .bg-slate-900   { background-color: #0f172a !important; }
            .text-slate-400 { color: #94a3b8 !important; }
            .text-slate-500 { color: #64748b !important; }
            .text-slate-600 { color: #475569 !important; }
            .text-slate-700 { color: #334155 !important; }
            .text-slate-900 { color: #0f172a !important; }
            .border-slate-100 { border-color: #f1f5f9 !important; }
            .border-slate-200 { border-color: #e2e8f0 !important; }
            .hover\:bg-slate-50:hover  { background-color: #f8fafc !important; }
            .hover\:bg-slate-200:hover { background-color: #e2e8f0 !important; }

            /* Green */
            .bg-green-50     { background-color: #f0fdf4 !important; }
            .bg-green-500    { background-color: #22c55e !important; }
            .bg-green-600    { background-color: #16a34a !important; }
            .text-green-600  { color: #16a34a !important; }
            .text-green-700  { color: #15803d !important; }
            .border-green-200 { border-color: #bbf7d0 !important; }
            .hover\:bg-green-700:hover { background-color: #15803d !important; }

            /* Red */
            .bg-red-50       { background-color: #fef2f2 !important; }
            .bg-red-500      { background-color: #ef4444 !important; }
            .bg-red-600      { background-color: #dc2626 !important; }
            .text-red-500    { color: #ef4444 !important; }
            .text-red-600    { color: #dc2626 !important; }
            .text-red-700    { color: #b91c1c !important; }
            .border-red-200  { border-color: #fecaca !important; }
            .hover\:bg-red-50:hover { background-color: #fef2f2 !important; }
            .bg-red-500\/20  { background-color: rgba(239,68,68,0.2) !important; }

            /* Amber / Orange */
            .bg-amber-50     { background-color: #fffbeb !important; }
            .text-amber-600  { color: #d97706 !important; }
            .bg-amber-50\/30 { background-color: rgba(255,251,235,0.3) !important; }

            /* Blue */
            .bg-blue-50      { background-color: #eff6ff !important; }
            .bg-blue-600     { background-color: #2563eb !important; }
            .text-blue-500   { color: #3b82f6 !important; }
            .text-blue-600   { color: #2563eb !important; }
            .text-blue-700   { color: #1d4ed8 !important; }

            /* Gray */
            .bg-gray-100     { background-color: #f3f4f6 !important; }
            .bg-gray-200     { background-color: #e5e7eb !important; }
            .text-gray-400   { color: #9ca3af !important; }
            .text-gray-500   { color: #6b7280 !important; }
            .text-gray-600   { color: #4b5563 !important; }
            .text-gray-700   { color: #374151 !important; }
            .text-gray-900   { color: #111827 !important; }
            .hover\:bg-gray-100:hover { background-color: #f3f4f6 !important; }

            /* White */
            .bg-white        { background-color: #ffffff !important; }
            .text-white      { color: #ffffff !important; }

            /* Black */
            .text-black      { color: #000000 !important; }
        }
    </style>
    @stack('head')
</head>
<body class="bg-background-light dark:bg-background-dark text-slate-900 dark:text-slate-100 font-display">
    <div class="flex h-screen flex-col">
        <!-- Top Navigation Bar -->
        <header class="flex h-14 w-full items-center justify-between border-b border-slate-200 dark:border-slate-800 bg-white dark:bg-background-dark px-6 z-10">
            <div class="flex items-center gap-4 shrink-0">
                <div class="flex items-center justify-center rounded-lg bg-primary p-1.5 text-white">
                    <span class="material-symbols-outlined">precision_manufacturing</span>
                </div>
                <h2 class="text-lg font-bold tracking-tight text-primary">QC Lab System</h2>
            </div>
            <div class="flex flex-1 items-center justify-end gap-3 md:gap-6">
                <div class="flex items-center gap-2">
                    <button class="flex h-10 w-10 items-center justify-center rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200">
                        <span class="material-symbols-outlined">notifications</span>
                    </button>
                    <button class="flex h-10 w-10 items-center justify-center rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200">
                        <span class="material-symbols-outlined">settings</span>
                    </button>
                    <div class="h-8 w-[1px] bg-slate-200 dark:bg-slate-700 mx-2"></div>
                    <div class="flex items-center gap-3 pl-2">
                        <div class="text-right hidden sm:block">
                            <p class="text-sm font-semibold leading-none">{{ auth()->user()->name }}</p>
                            <p class="text-xs text-slate-500 uppercase">{{ auth()->user()->roles->first()?->name ?? 'User' }}</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="h-10 w-10 rounded-full bg-primary/20 flex items-center justify-center border border-primary/30 overflow-hidden">
                                <span class="material-symbols-outlined text-primary">person</span>
                            </div>
                            <form method="POST" action="{{ route('logout') }}" class="m-0">
                                @csrf
                                <button type="submit" class="text-slate-400 hover:text-red-500 transition-colors">
                                    <span class="material-symbols-outlined">logout</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <div class="flex flex-1 overflow-hidden">
            <!-- Sidebar Navigation -->
            <aside class="w-60 border-r border-slate-200 dark:border-slate-800 bg-white dark:bg-background-dark hidden md:flex flex-col py-6">
                <div class="px-4 mb-6">
                    <p class="text-xs font-bold uppercase tracking-widest text-slate-400">Main Menu</p>
                </div>
                <nav class="flex-1 px-2 space-y-1">
                    <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('dashboard') ? 'bg-primary text-white font-medium' : 'text-slate-600 dark:text-slate-400 hover:bg-primary/10 hover:text-primary transition-colors' }}" href="{{ route('dashboard') }}">
                        <span class="material-symbols-outlined">dashboard</span>
                        <span>Dashboard</span>
                    </a>
                    <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('samples.*') ? 'bg-primary text-white font-medium' : 'text-slate-600 dark:text-slate-400 hover:bg-primary/10 hover:text-primary transition-colors' }}" href="{{ route('samples.index') }}">
                        <span class="material-symbols-outlined">biotech</span>
                        <span>Chemical Testing</span>
                    </a>
                    <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ (request()->routeIs('mechanical.*')) ? 'bg-primary text-white font-medium' : 'text-slate-600 dark:text-slate-400 hover:bg-primary/10 hover:text-primary transition-colors' }}" href="{{ route('mechanical.index') }}">
                        <span class="material-symbols-outlined">engineering</span>
                        <span>Mechanical Testing</span>
                    </a>
                    <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-slate-600 dark:text-slate-400 hover:bg-primary/10 hover:text-primary transition-colors" href="#">
                        <span class="material-symbols-outlined">science</span>
                        <span>Supplementary Tests</span>
                    </a>
                    <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('approvals.*') ? 'bg-primary text-white font-medium' : 'text-slate-600 dark:text-slate-400 hover:bg-primary/10 hover:text-primary transition-colors' }}" href="{{ route('approvals.index') }}">
                        <span class="material-symbols-outlined">rule</span>
                        <span>Approvals</span>
                    </a>
                    <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->is('reports/daily*') ? 'bg-primary text-white font-medium' : 'text-slate-600 dark:text-slate-400 hover:bg-primary/10 hover:text-primary transition-colors' }}" href="{{ route('reports.daily') }}">
                        <span class="material-symbols-outlined">description</span>
                        <span>Reports</span>
                    </a>
                    <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('mill-certificate.*') ? 'bg-primary text-white font-medium' : 'text-slate-600 dark:text-slate-400 hover:bg-primary/10 hover:text-primary transition-colors' }}" href="{{ route('mill-certificate.index') }}">
                        <span class="material-symbols-outlined">analytics</span>
                        <span>Mill Certificate</span>
                    </a>
                    <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('checker.*') ? 'bg-primary text-white font-medium' : 'text-slate-600 dark:text-slate-400 hover:bg-primary/10 hover:text-primary transition-colors' }}" href="{{ route('checker.index') }}">
                        <span class="material-symbols-outlined">task_alt</span>
                        <span>Heat Numbers Checkers</span>
                    </a>
                </nav>
                <div class="px-4 mt-auto">
                    <div class="rounded-xl bg-slate-50 dark:bg-slate-800 p-4 border border-slate-100 dark:border-slate-700">
                        <p class="text-xs font-semibold text-slate-500">System Status</p>
                        <div class="mt-2 flex items-center gap-2">
                            <div class="h-2 w-2 rounded-full bg-green-500"></div>
                            <p class="text-sm font-medium">Lab Nodes Online</p>
                        </div>
                    </div>
                </div>
            </aside>

            <!-- Main Content Area -->
            <main class="flex-1 overflow-y-auto p-4 md:p-6">
                @if(session('ok'))
                    <div class="mb-4 rounded-lg bg-green-50 p-4 text-sm text-green-700 border border-green-200" role="alert">
                        {{ session('ok') }}
                    </div>
                @endif
                @if(session('err'))
                    <div class="mb-4 rounded-lg bg-red-50 p-4 text-sm text-red-700 border border-red-200" role="alert">
                        {{ session('err') }}
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>
    @stack('scripts')
</body>
</html>
