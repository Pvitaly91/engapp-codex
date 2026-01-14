<!doctype html>
<html lang="{{ app()->getLocale() }}" class="h-full" x-data="themeController()" x-init="init()" x-bind:class="{ 'dark': isDark }">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>@yield('title', 'Error')</title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs" defer></script>
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
                    },
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
<body class="flex min-h-full items-center justify-center font-sans antialiased bg-[var(--bg)] text-[var(--fg)] px-4 py-10">
    <div class="mx-auto max-w-2xl space-y-8 text-center">
        @yield('error-icon')

        <div class="space-y-3">
            <h1 class="text-6xl font-bold text-brand-600">@yield('code')</h1>
            <h2 class="text-3xl font-bold">@yield('error-title')</h2>
            <p class="text-lg text-[var(--muted)]">@yield('error-message')</p>
        </div>

        <div class="flex flex-wrap items-center justify-center gap-3 pt-4">
            <a href="{{ url('/') }}" class="inline-flex items-center gap-2 rounded-full bg-brand-600 px-6 py-3 text-sm font-semibold text-white hover:bg-brand-700 transition">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                {{ __('public.errors.home') }}
            </a>
            <button onclick="window.history.back()" class="inline-flex items-center gap-2 rounded-full border border-[var(--border)] bg-[var(--card)] px-6 py-3 text-sm font-semibold hover:border-brand-500 transition">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                {{ __('public.errors.back') }}
            </button>
        </div>

        <div class="flex items-center justify-center gap-2 pt-6">
            <button @click="toggleTheme" class="flex h-10 w-10 items-center justify-center rounded-full border border-[var(--border)] bg-[var(--card)]" :aria-label="isDark ? '{{ __('public.theme.light') }}' : '{{ __('public.theme.dark') }}'">
                <template x-if="isDark">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/></svg>
                </template>
                <template x-if="!isDark">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"/><path d="M12 1v2m0 18v2m11-11h-2M3 12H1m17.657-7.657l-1.414 1.414M6.757 17.243l-1.414 1.414m0-13.414l1.414 1.414M17.243 17.243l1.414 1.414"/></svg>
                </template>
            </button>
        </div>
    </div>

    <script>
        function themeController() {
            return {
                isDark: false,
                init() {
                    const saved = localStorage.getItem('theme');
                    const prefers = window.matchMedia('(prefers-color-scheme: dark)').matches;
                    this.isDark = saved ? saved === 'dark' : prefers;
                },
                toggleTheme() {
                    this.isDark = !this.isDark;
                    localStorage.setItem('theme', this.isDark ? 'dark' : 'light');
                }
            }
        }
    </script>
</body>
</html>
