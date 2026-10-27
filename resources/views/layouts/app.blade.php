<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full antialiased">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Evidentia' }} - Academic Evidence Integrity Assessment</title>

    <!-- Theme Initialization Script (Anti-FOUC) -->
    <script>
        (function() {
            try {
                const savedTheme = localStorage.getItem('evidentia_theme');
                const systemDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                if (savedTheme === 'dark' || (!savedTheme && systemDark)) {
                    document.documentElement.classList.add('dark');
                } else {
                    document.documentElement.classList.remove('dark');
                }
            } catch (e) {}
        })();
    </script>

    <!-- Google Fonts: Newsreader (Editorial Serif), Plus Jakarta Sans (UI Sans) & JetBrains Mono -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600&family=Newsreader:ital,opsz,wght@0,6..72,400;0,6..72,500;0,6..72,600;0,6..72,700;1,6..72,400;1,6..72,500&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Styles & Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
        }
        .font-serif {
            font-family: 'Newsreader', Georgia, 'Times New Roman', serif;
        }
        .font-mono {
            font-family: 'JetBrains Mono', monospace;
        }
        /* Custom scrollbar for readable panes */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: transparent;
        }
        ::-webkit-scrollbar-thumb {
            background: rgba(107, 103, 96, 0.25);
            border-radius: 9999px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: rgba(107, 103, 96, 0.45);
        }
        .dark ::-webkit-scrollbar-thumb {
            background: rgba(163, 158, 147, 0.25);
        }
        .dark ::-webkit-scrollbar-thumb:hover {
            background: rgba(163, 158, 147, 0.45);
        }
    </style>
</head>
<body class="min-h-full flex flex-col bg-[#FAF9F5] text-[#1F1E1B] dark:bg-[#181715] dark:text-[#ECEBE5] transition-colors duration-200 selection:bg-terracotta-500/20 selection:text-terracotta-900 dark:selection:text-terracotta-200">
    <!-- Top Navigation Bar -->
    <header class="sticky top-0 z-50 border-b border-[#E6E4DD] dark:border-[#383530] bg-[#FAF9F5]/90 dark:bg-[#181715]/90 backdrop-blur-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <!-- Brand Logo -->
            <a href="{{ route('papers.create') }}" class="flex items-center gap-3 group">
                <div class="w-9 h-9 rounded-lg bg-terracotta-500 text-white flex items-center justify-center shadow-xs transition-transform group-hover:scale-105">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-base font-bold tracking-tight text-[#1F1E1B] dark:text-[#ECEBE5]">EVIDENTIA</span>
                        <span class="px-2 py-0.5 text-[10px] font-medium tracking-wide rounded-md bg-[#EFECE6] dark:bg-[#2A2825] text-[#6B6760] dark:text-[#A39E93] border border-[#E6E4DD] dark:border-[#383530]">Evidence Integrity</span>
                    </div>
                    <p class="text-[11px] text-[#6B6760] dark:text-[#A39E93]">Toulmin Argumentation & Retrieval</p>
                </div>
            </a>

            <!-- Navigation Links & Actions -->
            <nav class="flex items-center gap-2 sm:gap-3">
                <a href="{{ route('papers.create') }}" class="px-3 py-1.5 text-xs sm:text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('papers.create') ? 'bg-[#EFECE6] dark:bg-[#2A2825] text-[#1F1E1B] dark:text-[#ECEBE5] font-semibold' : 'text-[#6B6760] dark:text-[#A39E93] hover:text-[#1F1E1B] dark:hover:text-[#ECEBE5] hover:bg-[#F3F1EC] dark:hover:bg-[#22201D]' }}">
                    New Scan
                </a>
                <a href="{{ route('papers.index') }}" class="px-3 py-1.5 text-xs sm:text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('papers.index') ? 'bg-[#EFECE6] dark:bg-[#2A2825] text-[#1F1E1B] dark:text-[#ECEBE5] font-semibold' : 'text-[#6B6760] dark:text-[#A39E93] hover:text-[#1F1E1B] dark:hover:text-[#ECEBE5] hover:bg-[#F3F1EC] dark:hover:bg-[#22201D]' }}">
                    Submissions History
                </a>
                
                <div class="h-4 w-px bg-[#E6E4DD] dark:bg-[#383530] my-auto mx-1"></div>

                <!-- OpenAlex / OA Status Badge -->
                <div class="hidden sm:flex items-center gap-2 px-2.5 py-1 rounded-full bg-emerald-500/10 text-emerald-800 dark:text-emerald-400 border border-emerald-500/20 text-xs font-medium">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                    OpenAlex Active
                </div>

                <!-- Theme Toggle Button -->
                <button type="button" id="themeToggleBtn" onclick="toggleTheme()" aria-label="Toggle light/dark theme"
                    class="p-2 rounded-lg text-[#6B6760] dark:text-[#A39E93] hover:text-[#1F1E1B] dark:hover:text-[#ECEBE5] hover:bg-[#F3F1EC] dark:hover:bg-[#22201D] border border-transparent hover:border-[#E6E4DD] dark:hover:border-[#383530] transition-colors"
                    title="Toggle light/dark mode">
                    <!-- Sun Icon (visible in dark mode) -->
                    <svg class="w-4 h-4 hidden dark:block" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <!-- Moon Icon (visible in light mode) -->
                    <svg class="w-4 h-4 block dark:hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                    </svg>
                </button>
            </nav>
        </div>
    </header>

    <!-- Main Content Slot -->
    <main class="flex-1 flex flex-col">
        @if (session('status'))
            <div class="bg-terracotta-500/10 border-b border-terracotta-500/20 text-terracotta-800 dark:text-terracotta-300 px-4 py-2.5 text-sm text-center font-medium">
                {{ session('status') }}
            </div>
        @endif

        {{ $slot ?? '' }}
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="border-t border-[#E6E4DD] dark:border-[#383530] bg-[#FAF9F5] dark:bg-[#181715] py-6 text-center text-xs text-[#6B6760] dark:text-[#A39E93]">
        <div class="max-w-7xl mx-auto px-4 flex flex-col sm:flex-row items-center justify-between gap-4">
            <p>Evidentia &copy; {{ date('Y') }} — Grounded in the Toulmin Model of Argumentation.</p>
            <div class="flex items-center gap-4">
                <span>Claim-to-Ground Evidence Auditing</span>
                <span>&bull;</span>
                <span>OpenAlex & Crossref OA</span>
            </div>
        </div>
    </footer>

    <!-- Theme Toggle Helper Script -->
    <script>
        function toggleTheme() {
            const isDark = document.documentElement.classList.toggle('dark');
            localStorage.setItem('evidentia_theme', isDark ? 'dark' : 'light');
        }
    </script>
</body>
</html>

