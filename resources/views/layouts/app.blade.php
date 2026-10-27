<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-950 text-slate-100 antialiased">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Evidentia' }} - Academic Evidence Integrity Assessment</title>

    <!-- Google Fonts: Plus Jakarta Sans & JetBrains Mono -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Styles & Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
        }
        .font-mono {
            font-family: 'JetBrains Mono', monospace;
        }
        /* Custom scrollbar for inspectable panes */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: rgba(15, 23, 42, 0.6);
        }
        ::-webkit-scrollbar-thumb {
            background: rgba(71, 85, 105, 0.5);
            border-radius: 9999px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: rgba(100, 116, 139, 0.8);
        }
    </style>
</head>
<body class="min-h-full flex flex-col bg-slate-950 text-slate-100 selection:bg-indigo-500 selection:text-white">
    <!-- Top Navigation Bar -->
    <header class="sticky top-0 z-50 border-b border-slate-800/80 bg-slate-950/80 backdrop-blur-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <!-- Brand Logo -->
            <a href="{{ route('papers.create') }}" class="flex items-center gap-3 group">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-indigo-600 via-indigo-500 to-cyan-400 p-0.5 shadow-lg shadow-indigo-500/20 group-hover:shadow-indigo-500/40 transition-all duration-300">
                    <div class="w-full h-full bg-slate-950 rounded-[10px] flex items-center justify-center">
                        <svg class="w-5 h-5 text-indigo-400 group-hover:scale-110 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-xl font-bold tracking-tight text-white group-hover:text-indigo-300 transition-colors">EVIDENTIA</span>
                        <span class="px-2 py-0.5 text-[10px] font-semibold tracking-wider uppercase rounded-full bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">Integrity AI</span>
                    </div>
                    <p class="text-[11px] text-slate-400 font-medium">Academic Evidence Verification System</p>
                </div>
            </a>

            <!-- Navigation Links & Actions -->
            <nav class="flex items-center gap-3">
                <a href="{{ route('papers.create') }}" class="px-3.5 py-1.5 text-sm font-medium rounded-lg text-slate-300 hover:text-white hover:bg-slate-800/60 transition-colors {{ request()->routeIs('papers.create') ? 'bg-slate-800/80 text-white font-semibold' : '' }}">
                    New Scan
                </a>
                <a href="{{ route('papers.index') }}" class="px-3.5 py-1.5 text-sm font-medium rounded-lg text-slate-300 hover:text-white hover:bg-slate-800/60 transition-colors {{ request()->routeIs('papers.index') ? 'bg-slate-800/80 text-white font-semibold' : '' }}">
                    Submissions History
                </a>
                
                <div class="h-4 w-px bg-slate-800 my-auto mx-1"></div>

                <!-- OpenAlex / OA Badge indicator -->
                <div class="hidden sm:flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs font-medium">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                    OpenAlex & Unpaywall Active
                </div>
            </nav>
        </div>
    </header>

    <!-- Main Content Slot -->
    <main class="flex-1 flex flex-col">
        @if (session('status'))
            <div class="bg-indigo-950/80 border-b border-indigo-500/30 text-indigo-200 px-4 py-2.5 text-sm text-center font-medium">
                {{ session('status') }}
            </div>
        @endif

        {{ $slot ?? '' }}
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="border-t border-slate-900 bg-slate-950/90 py-6 text-center text-xs text-slate-400">
        <div class="max-w-7xl mx-auto px-4 flex flex-col sm:flex-row items-center justify-between gap-4">
            <p>Evidentia &copy; {{ date('Y') }} — Grounded in the Toulmin Model of Argumentation. Transparent & Inspectable.</p>
            <div class="flex items-center gap-4 text-slate-400">
                <span>Evaluates Claim-to-Ground Evidence</span>
                <span>&bull;</span>
                <span>OpenAlex & Crossref OA Retrieval</span>
            </div>
        </div>
    </footer>
</body>
</html>
