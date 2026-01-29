<!doctype html>
<html lang="{{ app()->getLocale() }}" class="h-full" x-data="themeController()" x-init="init()" x-bind:class="{ 'dark': isDark }">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>{{ __('public.coming_soon.title') }} — Gramlyze</title>
    <meta name="description" content="{{ __('public.coming_soon.text') }}" />
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="//unpkg.com/alpinejs" defer></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'] },
                    colors: {
                        brand: {
                            50: '#eef2ff',
                            100: '#e0e7ff',
                            200: '#c7d2fe',
                            400: '#7c8aff',
                            500: '#5a6bff',
                            600: '#4350e6',
                            700: '#3730a3',
                            900: '#0b1140'
                        }
                    }
                }
            }
        }
    </script>
    <style>
        :root {
            --bg: #f7f8fb;
            --fg: #0f172a;
            --card: #ffffff;
            --muted: #475569;
            --border: #e2e8f0;
        }
        .dark {
            --bg: #0b1020;
            --fg: #e5e7eb;
            --card: #0f172a;
            --muted: #94a3b8;
            --border: #1f2937;
        }
        body { background: radial-gradient(circle at 10% 20%, rgba(90,107,255,0.12), transparent 22%), radial-gradient(circle at 80% 0%, rgba(91,206,250,0.14), transparent 18%), var(--bg); background-attachment: fixed; color: var(--fg); }
    </style>
</head>
<body class="min-h-full font-sans antialiased bg-[var(--bg)] text-[var(--fg)] flex items-center justify-center">
    <div class="text-center px-6 py-12 max-w-lg">
        <div class="mb-8">
            <svg class="mx-auto h-24 w-24 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>

        <h1 class="text-4xl font-bold mb-4 text-[var(--fg)]">{{ __('public.coming_soon.title') }}</h1>
        <p class="text-lg text-[var(--muted)] mb-8">{{ __('public.coming_soon.text') }}</p>

        <div class="flex flex-wrap justify-center gap-4 mb-8">
            <a href="{{ url('/') }}" class="inline-flex items-center gap-2 rounded-full bg-brand-600 px-6 py-3 text-sm font-semibold text-white shadow-lg hover:bg-brand-700 transition">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                {{ __('public.coming_soon.home') }}
            </a>
            <button onclick="history.back()" class="inline-flex items-center gap-2 rounded-full border border-[var(--border)] bg-[var(--card)] px-6 py-3 text-sm font-semibold shadow-sm hover:border-brand-500 transition">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                {{ __('public.coming_soon.back') }}
            </button>
        </div>

        <p class="text-sm text-[var(--muted)]">
            {{ __('public.coming_soon.bug_report') }}
        </p>
    </div>

    <script>
        function themeController() {
            return {
                isDark: false,
                init() {
                    const saved = localStorage.getItem('theme');
                    const prefers = window.matchMedia('(prefers-color-scheme: dark)').matches;
                    this.isDark = saved ? saved === 'dark' : prefers;
                }
            }
        }
    </script>
</body>
</html>
