<!doctype html>
<html lang="{{ app()->getLocale() }}" class="h-full" x-data="copilotTheme()" x-init="init()" x-bind:class="{ 'dark': isDark }">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>@yield('title', __('public.meta.title'))</title>
    <meta name="description" content="{{ __('public.meta.description') }}" />
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    {{-- Tailwind CDN serves dynamically-generated content; SRI cannot be applied to it --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs@3.14.3/dist/cdn.min.js"
            integrity="sha384-OLBgp1GsljhM2TJ+sbHjaiH9txEUvgdDTAzHv2P24donTt6/529l+9Ua0vFImLlb"
            crossorigin="anonymous"
            defer></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'] },
                    colors: {
                        pilot: {
                            50:  '#f0fdf9',
                            100: '#ccfbef',
                            200: '#99f6e0',
                            300: '#5eead4',
                            400: '#2dd4bf',
                            500: '#14b8a6',
                            600: '#0d9488',
                            700: '#0f766e',
                            800: '#115e59',
                            900: '#134e4a',
                        },
                        ink: {
                            50:  '#f8fafc',
                            100: '#f1f5f9',
                            200: '#e2e8f0',
                            300: '#cbd5e1',
                            400: '#94a3b8',
                            500: '#64748b',
                            600: '#475569',
                            700: '#334155',
                            800: '#1e293b',
                            900: '#0f172a',
                        }
                    },
                    colors: {
                        /* brand maps to pilot teal so reused engram blocks render correctly */
                        brand: {
                            50:  '#f0fdf9',
                            100: '#ccfbef',
                            200: '#99f6e0',
                            400: '#2dd4bf',
                            500: '#14b8a6',
                            600: '#0d9488',
                            700: '#0f766e',
                            900: '#134e4a',
                        },
                        pilot: {
                            50:  '#f0fdf9',
                            100: '#ccfbef',
                            200: '#99f6e0',
                            300: '#5eead4',
                            400: '#2dd4bf',
                            500: '#14b8a6',
                            600: '#0d9488',
                            700: '#0f766e',
                            800: '#115e59',
                            900: '#134e4a',
                        },
                        ink: {
                            50:  '#f8fafc',
                            100: '#f1f5f9',
                            200: '#e2e8f0',
                            300: '#cbd5e1',
                            400: '#94a3b8',
                            500: '#64748b',
                            600: '#475569',
                            700: '#334155',
                            800: '#1e293b',
                            900: '#0f172a',
                        }
                    },
                    boxShadow: {
                        glow:  '0 0 0 3px rgba(20,184,166,0.25)',
                        panel: '0 4px 24px -8px rgba(15,23,42,0.18)',
                        card:  '0 20px 50px -22px rgba(0,0,0,0.25)',
                        soft:  '0 2px 12px -4px rgba(15,23,42,0.10)',
                    },
                    borderRadius: {
                        '4xl': '2rem',
                    }
                }
            }
        }
    </script>
    <style>
        :root {
            --cp-bg:     #f0fdf9;
            --cp-surface:#ffffff;
            --cp-fg:     #0f172a;
            --cp-muted:  #475569;
            --cp-border: #ccfbef;
            --cp-accent: #0d9488;

            /* Aliases so engram blocks-v3 partials render correctly in copilot */
            --bg:     #f0fdf9;
            --fg:     #0f172a;
            --card:   #ffffff;
            --muted:  #475569;
            --border: #ccfbef;

            /* Tailwind v4 semantic color tokens */
            --color-background:           #f0fdf9;
            --color-foreground:           #0f172a;
            --color-card:                 #ffffff;
            --color-card-foreground:      #0f172a;
            --color-border:               #ccfbef;
            --color-muted:                rgba(20,184,166,0.08);
            --color-muted-foreground:     #475569;
            --color-primary:              #0d9488;
            --color-primary-foreground:   #ffffff;
            --color-secondary:            rgba(20,184,166,0.12);
            --color-secondary-foreground: #0f766e;
            --color-accent:               #0d9488;
            --color-accent-foreground:    #ffffff;
        }
        .dark {
            --cp-bg:     #0a1a18;
            --cp-surface:#0f2420;
            --cp-fg:     #e2e8f0;
            --cp-muted:  #94a3b8;
            --cp-border: #164e47;
            --cp-accent: #2dd4bf;

            /* Aliases for dark mode */
            --bg:     #0a1a18;
            --fg:     #e2e8f0;
            --card:   #0f2420;
            --muted:  #94a3b8;
            --border: #164e47;

            /* Tailwind v4 semantic color tokens – dark */
            --color-background:           #0a1a18;
            --color-foreground:           #e2e8f0;
            --color-card:                 #0f2420;
            --color-card-foreground:      #e2e8f0;
            --color-border:               #164e47;
            --color-muted:                rgba(20,184,166,0.08);
            --color-muted-foreground:     #94a3b8;
            --color-primary:              #14b8a6;
            --color-primary-foreground:   #ffffff;
            --color-secondary:            rgba(20,184,166,0.12);
            --color-secondary-foreground: #5eead4;
            --color-accent:               #2dd4bf;
            --color-accent-foreground:    #0a1a18;
        }
        html { scroll-behavior: smooth; }
        body {
            background: var(--cp-bg);
            color: var(--cp-fg);
            min-height: 100%;
        }
        /* Gradient mesh background */
        .cp-mesh {
            background:
                radial-gradient(ellipse 70% 50% at 0% 0%,   rgba(20,184,166,0.15), transparent),
                radial-gradient(ellipse 50% 50% at 100% 20%, rgba(99,102,241,0.08), transparent),
                var(--cp-bg);
            background-attachment: fixed;
        }
        /* Sidebar nav items */
        .cp-nav-item {
            display: flex;
            align-items: center;
            gap: 0.625rem;
            padding: 0.5rem 0.875rem;
            border-radius: 0.75rem;
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--cp-muted);
            transition: background 0.15s, color 0.15s;
        }
        .cp-nav-item:hover,
        .cp-nav-item.active {
            background: rgba(20,184,166,0.12);
            color: var(--cp-accent);
        }
        /* Pill badge */
        .cp-badge {
            display: inline-flex;
            align-items: center;
            border-radius: 9999px;
            padding: 0.15rem 0.65rem;
            font-size: 0.65rem;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            background: rgba(20,184,166,0.15);
            color: var(--cp-accent);
        }
        /* Card */
        .cp-card {
            border-radius: 1.25rem;
            border: 1px solid var(--cp-border);
            background: var(--cp-surface);
            box-shadow: 0 2px 16px -4px rgba(15,23,42,0.10);
        }
        /* Scrollbar */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: var(--cp-border); border-radius: 9999px; }
        ::-webkit-scrollbar-thumb:hover { background: var(--cp-accent); }
    </style>
    @livewireStyles
</head>
<body class="cp-mesh min-h-full font-sans antialiased">

    {{-- ══════════════════════════════ LAYOUT SHELL ══════════════════════════════ --}}
    <div class="flex min-h-screen" x-data="{ sidebarOpen: false }">

        {{-- ─── LEFT SIDEBAR (desktop) ──────────────────────────────────────────── --}}
        <aside class="hidden lg:flex flex-col w-64 shrink-0 border-r border-[var(--cp-border)] bg-[var(--cp-surface)]/80 backdrop-blur sticky top-0 h-screen overflow-y-auto">
            {{-- Logo --}}
            <div class="flex items-center gap-3 px-5 py-5 border-b border-[var(--cp-border)]">
                <a href="{{ localized_route('home') }}" class="flex items-center gap-3" aria-label="Gramlyze">
                    <x-gramlyze-logo variant="horizontal" class="h-9 w-auto" />
                </a>
                @if(config('app.is_beta'))
                    <span class="cp-badge">Beta</span>
                @endif
            </div>

            {{-- Navigation --}}
            <nav class="flex-1 px-3 py-5 space-y-1">
                <p class="px-3 pb-2 text-[0.65rem] uppercase font-semibold tracking-widest text-[var(--cp-muted)]">{{ __('public.nav.explore') }}</p>
                <a href="{{ localized_route('catalog.tests-cards') }}" class="cp-nav-item">
                    <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h10"/></svg>
                    {{ __('public.nav.catalog') }}
                </a>
                <a href="{{ localized_route('copilot.theory.index') }}" class="cp-nav-item">
                    <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                    {{ __('public.nav.theory') }}
                </a>

                <p class="px-3 pt-4 pb-2 text-[0.65rem] uppercase font-semibold tracking-widest text-[var(--cp-muted)]">{{ __('public.nav.practice') }}</p>
                <a href="{{ localized_route('words.test') }}" class="cp-nav-item">
                    <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    {{ __('public.nav.words_test') }}
                </a>
                <a href="{{ localized_route('verbs.test') }}" class="cp-nav-item">
                    <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    {{ __('public.nav.verbs_test') }}
                </a>
            </nav>

            {{-- Bottom actions --}}
            <div class="px-4 py-4 border-t border-[var(--cp-border)] space-y-3">
                {{-- Search --}}
                <div x-data="cpSearch()" class="relative">
                    <form @submit.prevent="go">
                        <div class="flex items-center gap-2 rounded-xl border border-[var(--cp-border)] bg-[var(--cp-bg)] px-3 py-2 focus-within:border-pilot-500 focus-within:ring-2 focus-within:ring-pilot-200 dark:focus-within:ring-pilot-800 transition">
                            <svg class="h-4 w-4 text-[var(--cp-muted)] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                            <input x-model="query" @input="autocomplete" @keydown.escape="open=false"
                                   type="search" name="q"
                                   placeholder="{{ __('public.search.placeholder') }}"
                                   class="w-full bg-transparent text-sm focus:outline-none">
                        </div>
                        <div x-show="open" x-transition
                             class="absolute left-0 right-0 bottom-full mb-2 rounded-2xl border border-[var(--cp-border)] bg-[var(--cp-surface)] shadow-panel overflow-hidden z-50">
                            <template x-if="!results.length">
                                <div class="px-4 py-3 text-xs text-[var(--cp-muted)]">{{ __('public.search.nothing_found') }}</div>
                            </template>
                            <template x-for="item in results" :key="item.url">
                                <a :href="item.url" class="flex items-start gap-3 px-4 py-3 text-sm hover:bg-pilot-50 dark:hover:bg-pilot-900/30">
                                    <span class="mt-0.5 inline-flex h-5 w-5 items-center justify-center rounded-full bg-pilot-100 dark:bg-pilot-900 text-pilot-700 dark:text-pilot-300 text-[10px] font-bold" x-text="item.type === 'test' ? 'T' : 'P'"></span>
                                    <div>
                                        <p class="font-medium" x-html="highlight(item.title)"></p>
                                        <p class="text-xs text-[var(--cp-muted)]" x-text="item.url"></p>
                                    </div>
                                </a>
                            </template>
                        </div>
                    </form>
                </div>

                {{-- Theme & lang row --}}
                <div class="flex items-center gap-2">
                    <div x-data="cpLang()" class="relative flex-1">
                        <button @click="toggle" class="w-full flex items-center justify-between gap-2 rounded-xl border border-[var(--cp-border)] bg-[var(--cp-bg)] px-3 py-2 text-sm font-medium hover:border-pilot-400 transition" aria-haspopup="listbox">
                            <span class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-pilot-100 dark:bg-pilot-900 text-pilot-700 dark:text-pilot-300 text-[10px] font-bold" x-text="active.code.toUpperCase()"></span>
                            <span class="truncate" x-text="active.native_name || active.name"></span>
                            <svg class="h-3.5 w-3.5 text-[var(--cp-muted)] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="open" @click.outside="open=false" x-transition
                             class="absolute left-0 right-0 bottom-full mb-2 rounded-2xl border border-[var(--cp-border)] bg-[var(--cp-surface)] shadow-panel overflow-hidden z-50">
                            <div class="p-2 border-b border-[var(--cp-border)]">
                                <input x-model="filter" type="search" placeholder="{{ __('public.language.search') }}"
                                       class="w-full rounded-lg border border-[var(--cp-border)] bg-[var(--cp-bg)] px-3 py-1.5 text-xs focus:border-pilot-500 focus:outline-none">
                            </div>
                            <div class="max-h-52 overflow-y-auto" role="listbox">
                                <template x-for="lang in filtered" :key="lang.code">
                                    <a :href="lang.url" @click="open=false"
                                       class="flex items-center justify-between px-3 py-2 text-sm hover:bg-pilot-50 dark:hover:bg-pilot-900/30" role="option">
                                        <div class="flex items-center gap-2">
                                            <span class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-pilot-100 dark:bg-pilot-900 text-pilot-700 dark:text-pilot-300 text-[10px] font-bold" x-text="lang.code.toUpperCase()"></span>
                                            <span class="font-medium text-xs" x-text="lang.native_name || lang.name"></span>
                                        </div>
                                        <span x-show="lang.is_current" class="text-[10px] text-pilot-600 font-semibold">{{ __('public.language.current') }}</span>
                                    </a>
                                </template>
                            </div>
                        </div>
                    </div>
                    <button @click="toggleTheme"
                            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border border-[var(--cp-border)] bg-[var(--cp-bg)] hover:border-pilot-400 transition"
                            :aria-label="isDark ? '{{ __('public.theme.light') }}' : '{{ __('public.theme.dark') }}'">
                        <template x-if="isDark">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/></svg>
                        </template>
                        <template x-if="!isDark">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="5"/><path d="M12 1v2m0 18v2m11-11h-2M3 12H1m17.657-7.657-1.414 1.414M6.757 17.243l-1.414 1.414m0-13.414 1.414 1.414M17.243 17.243l1.414 1.414"/></svg>
                        </template>
                    </button>
                </div>
            </div>
        </aside>

        {{-- ─── MOBILE OVERLAY SIDEBAR ──────────────────────────────────────────── --}}
        <div x-show="sidebarOpen" x-transition.opacity class="fixed inset-0 z-40 bg-black/40 backdrop-blur-sm lg:hidden" @click="sidebarOpen=false"></div>
        <aside x-show="sidebarOpen" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full"
               class="fixed inset-y-0 left-0 z-50 flex w-72 flex-col border-r border-[var(--cp-border)] bg-[var(--cp-surface)] shadow-panel lg:hidden">
            <div class="flex items-center justify-between px-5 py-5 border-b border-[var(--cp-border)]">
                <x-gramlyze-logo variant="horizontal" class="h-8 w-auto" />
                <button @click="sidebarOpen=false" class="flex h-8 w-8 items-center justify-center rounded-lg hover:bg-pilot-50 dark:hover:bg-pilot-900/30 text-[var(--cp-muted)]" aria-label="Close menu">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <nav class="flex-1 overflow-y-auto px-3 py-5 space-y-1">
                <p class="px-3 pb-2 text-[0.65rem] uppercase font-semibold tracking-widest text-[var(--cp-muted)]">{{ __('public.nav.explore') }}</p>
                <a href="{{ localized_route('catalog.tests-cards') }}" @click="sidebarOpen=false" class="cp-nav-item">
                    <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h10"/></svg>
                    {{ __('public.nav.catalog') }}
                </a>
                <a href="{{ localized_route('copilot.theory.index') }}" @click="sidebarOpen=false" class="cp-nav-item">
                    <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                    {{ __('public.nav.theory') }}
                </a>
                <p class="px-3 pt-4 pb-2 text-[0.65rem] uppercase font-semibold tracking-widest text-[var(--cp-muted)]">{{ __('public.nav.practice') }}</p>
                <a href="{{ localized_route('words.test') }}" @click="sidebarOpen=false" class="cp-nav-item">
                    <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    {{ __('public.nav.words_test') }}
                </a>
                <a href="{{ localized_route('verbs.test') }}" @click="sidebarOpen=false" class="cp-nav-item">
                    <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    {{ __('public.nav.verbs_test') }}
                </a>
            </nav>
        </aside>

        {{-- ─── MAIN COLUMN ──────────────────────────────────────────────────────── --}}
        <div class="flex flex-1 flex-col min-w-0">

            {{-- Top bar (mobile + desktop) --}}
            <header class="sticky top-0 z-30 flex items-center justify-between gap-4 border-b border-[var(--cp-border)] bg-[var(--cp-surface)]/90 backdrop-blur px-4 py-3">
                {{-- Mobile hamburger --}}
                <button @click="sidebarOpen=true"
                        class="lg:hidden flex h-9 w-9 items-center justify-center rounded-xl border border-[var(--cp-border)] bg-[var(--cp-bg)] hover:border-pilot-400 transition"
                        aria-label="Open menu">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>

                {{-- Mobile logo --}}
                <a href="{{ localized_route('home') }}" class="lg:hidden flex items-center gap-2" aria-label="Gramlyze">
                    <x-gramlyze-logo variant="horizontal" class="h-8 w-auto" />
                </a>

                {{-- Desktop breadcrumb / page title --}}
                <div class="hidden lg:block text-sm text-[var(--cp-muted)] font-medium">
                    @yield('breadcrumb', '<span>Gramlyze</span>')
                </div>

                {{-- Right controls --}}
                <div class="flex items-center gap-2 ml-auto">
                    {{-- Mobile search --}}
                    <div x-data="cpSearch()" class="relative lg:hidden">
                        <form @submit.prevent="go">
                            <div class="flex items-center gap-2 rounded-xl border border-[var(--cp-border)] bg-[var(--cp-bg)] px-3 py-2 focus-within:border-pilot-500 focus-within:ring-2 focus-within:ring-pilot-200 dark:focus-within:ring-pilot-800 transition">
                                <svg class="h-4 w-4 text-[var(--cp-muted)] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                                <input x-model="query" @input="autocomplete" @keydown.escape="open=false"
                                       type="search" name="q"
                                       placeholder="{{ __('public.search.placeholder') }}"
                                       class="w-40 bg-transparent text-sm focus:outline-none">
                            </div>
                        </form>
                    </div>
                    {{-- Theme toggle (mobile) --}}
                    <button @click="toggleTheme"
                            class="lg:hidden flex h-9 w-9 items-center justify-center rounded-xl border border-[var(--cp-border)] bg-[var(--cp-bg)] hover:border-pilot-400 transition">
                        <template x-if="isDark"><svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/></svg></template>
                        <template x-if="!isDark"><svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="5"/><path d="M12 1v2m0 18v2m11-11h-2M3 12H1m17.657-7.657-1.414 1.414M6.757 17.243l-1.414 1.414m0-13.414 1.414 1.414M17.243 17.243l1.414 1.414"/></svg></template>
                    </button>
                </div>
            </header>

            {{-- Page content --}}
            <main class="flex-1 px-4 py-8 md:px-8 md:py-10 max-w-5xl w-full mx-auto">
                @yield('content')
            </main>

            {{-- Footer --}}
            <footer class="border-t border-[var(--cp-border)] bg-[var(--cp-surface)]/80 px-4 py-8 md:px-8">
                <div class="max-w-5xl mx-auto grid gap-8 md:grid-cols-3">
                    <div class="space-y-3">
                        <x-gramlyze-logo variant="horizontal" class="h-8 w-auto" />
                        <p class="text-sm text-[var(--cp-muted)]">{{ __('public.footer.description') }}</p>
                        <p class="text-xs text-[var(--cp-muted)]">© <span x-text="new Date().getFullYear()"></span> Gramlyze</p>
                    </div>
                    <div class="space-y-2 text-sm">
                        <h3 class="font-semibold text-[var(--cp-fg)]">{{ __('public.footer.links') }}</h3>
                        <div class="flex flex-col gap-1.5 text-[var(--cp-muted)]">
                            <a class="hover:text-pilot-600 transition-colors" href="{{ localized_route('catalog.tests-cards') }}">{{ __('public.nav.catalog') }}</a>
                            <a class="hover:text-pilot-600 transition-colors" href="{{ localized_route('copilot.theory.index') }}">{{ __('public.nav.theory') }}</a>
                            <a class="hover:text-pilot-600 transition-colors" href="{{ localized_route('words.test') }}">{{ __('public.nav.words_test') }}</a>
                            <a class="hover:text-pilot-600 transition-colors" href="{{ localized_route('verbs.test') }}">{{ __('public.nav.verbs_test') }}</a>
                        </div>
                    </div>
                    <div class="space-y-3 text-sm">
                        <h3 class="font-semibold text-[var(--cp-fg)]">{{ __('public.footer.contact') }}</h3>
                        <p class="text-[var(--cp-muted)]">{{ __('public.footer.support') }}</p>
                        <button @click="toggleTheme" class="inline-flex items-center gap-2 rounded-xl border border-[var(--cp-border)] px-4 py-2 text-sm font-medium hover:border-pilot-400 transition">
                            <span x-show="!isDark">🌙</span><span x-show="isDark">☀️</span>
                            <span>{{ __('public.footer.theme') }}</span>
                        </button>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <script>
        function copilotTheme() {
            return {
                isDark: false,
                init() {
                    const saved = localStorage.getItem('cp-theme');
                    const prefers = window.matchMedia('(prefers-color-scheme: dark)').matches;
                    this.isDark = saved ? saved === 'dark' : prefers;
                },
                toggleTheme() {
                    this.isDark = !this.isDark;
                    localStorage.setItem('cp-theme', this.isDark ? 'dark' : 'light');
                }
            };
        }
        function cpSearch() {
            return {
                query: '',
                results: [],
                open: false,
                go() {
                    if (this.query.trim()) {
                        window.location.href = '{{ localized_route('site.search') }}?q=' + encodeURIComponent(this.query.trim());
                    }
                },
                highlight(text) {
                    const safe = (text || '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
                    if (!this.query) return safe;
                    const re = new RegExp('(' + this.query.replace(/[-/\\^$*+?.()|[\]{}]/g, '\\$&') + ')', 'ig');
                    return safe.replace(re, '<mark class="bg-pilot-200 dark:bg-pilot-800 text-pilot-900 dark:text-pilot-100 rounded px-0.5">$1</mark>');
                },
                async autocomplete() {
                    if (this.query.trim().length < 2) { this.results = []; this.open = false; return; }
                    try {
                        const res = await fetch('{{ localized_route('site.search') }}?q=' + encodeURIComponent(this.query.trim()), { headers: { Accept: 'application/json' } });
                        if (!res.ok) { this.results = []; return; }
                        this.results = await res.json();
                        this.open = true;
                    } catch {
                        this.results = [];
                    }
                }
            };
        }
        function cpLang() {
            return {
                open: false,
                filter: '',
                languages: @json($__languageSwitcher ?? []),
                get active() { return this.languages.find(l => l.is_current) || this.languages[0] || { code: '{{ app()->getLocale() }}', name: '{{ app()->getLocale() }}' }; },
                toggle() { this.open = !this.open; },
                get filtered() {
                    const t = this.filter.toLowerCase();
                    return this.languages.filter(l => !t || (l.name && l.name.toLowerCase().includes(t)) || (l.native_name && l.native_name.toLowerCase().includes(t)) || l.code.toLowerCase().includes(t));
                }
            };
        }
    </script>
    @livewireScripts
    @yield('scripts')
</body>
</html>
