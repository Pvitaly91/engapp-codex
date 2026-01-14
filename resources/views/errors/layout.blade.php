@props([
    'code' => '500',
    'title' => null,
    'text' => null,
])

@php
    $translationKey = is_numeric($code) && strlen($code) === 3 ? $code : (str_starts_with((string)$code, '4') ? '4xx' : '5xx');
    $title = $title ?? __("public.errors.{$translationKey}.title");
    $text = $text ?? __("public.errors.{$translationKey}.text");
@endphp

<!doctype html>
<html lang="{{ app()->getLocale() }}" class="h-full" x-data="themeController()" x-init="init()" x-bind:class="{ 'dark': isDark }">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>{{ $code }} - {{ $title }} — Gramlyze</title>
    <meta name="description" content="{{ $text }}" />
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
        <div class="mb-6">
            <span class="text-8xl font-extrabold text-brand-500/30 dark:text-brand-400/20">{{ $code }}</span>
        </div>

        <div class="mb-6">
            @if(str_starts_with((string)$code, '4'))
                {{-- Client error icon --}}
                <svg class="mx-auto h-20 w-20 text-amber-500 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            @else
                {{-- Server error icon --}}
                <svg class="mx-auto h-20 w-20 text-red-500 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            @endif
        </div>

        <h1 class="text-3xl font-bold mb-3 text-[var(--fg)]">{{ $title }}</h1>
        <p class="text-lg text-[var(--muted)] mb-8">{{ $text }}</p>

        <div class="flex flex-wrap justify-center gap-4">
            <a href="{{ url('/') }}" class="inline-flex items-center gap-2 rounded-full bg-brand-600 px-6 py-3 text-sm font-semibold text-white shadow-lg hover:bg-brand-700 transition">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                {{ __('public.errors.home') }}
            </a>
            <button onclick="history.back()" class="inline-flex items-center gap-2 rounded-full border border-[var(--border)] bg-[var(--card)] px-6 py-3 text-sm font-semibold shadow-sm hover:border-brand-500 transition">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                {{ __('public.errors.back') }}
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
                }
            }
        }
    </script>
</body>
</html>
