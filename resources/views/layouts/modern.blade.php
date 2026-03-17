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
