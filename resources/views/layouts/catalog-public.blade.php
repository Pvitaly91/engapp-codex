<!doctype html>
<html lang="{{ app()->getLocale() }}" class="h-full" x-data="themeController()" x-init="init()" x-bind:class="{ 'dark': isDark }">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>@yield('title', __('public.meta.title'))</title>
    <meta name="description" content="{{ __('public.meta.description') }}" />
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Archivo:wght@600;700;800&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="//unpkg.com/alpinejs" defer></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#eef2ff',
                            100: '#e0e7ff',
                            600: '#4350e6',
                            700: '#3730a3'
                        },
                        steel: '#5d7185',
                        night: '#13233b',
                        ocean: '#2f67b1',
                        amber: '#f59b2f',
                        mist: '#f5fbff',
                        shell: '#fffefd',
                        line: '#d8e2ee'
                    },
                    fontFamily: {
                        display: ['Archivo', 'sans-serif'],
                        body: ['Manrope', 'sans-serif']
                    },
                    boxShadow: {
                        panel: '0 24px 60px rgba(17, 38, 63, 0.18)',
                        card: '0 12px 28px rgba(17, 38, 63, 0.10)'
                    }
                }
            }
        };
    </script>
    <style>
        :root {
            --app-bg: linear-gradient(180deg, #607487 0%, #526678 100%);
            --shell-bg: #f5fbff;
            --shell-border: rgba(255, 255, 255, 0.72);
            --shell-radius: 30px;
            --surface: rgba(255, 254, 253, 0.94);
            --surface-strong: #ffffff;
            --line: #d8e2ee;
            --text: #14233b;
            --muted: #5b6c80;
            --accent: #2f67b1;
            --accent-soft: rgba(47, 103, 177, 0.10);
            --footer-bg: linear-gradient(180deg, #13233b 0%, #0f1b31 100%);
        }

        .dark {
            --app-bg: linear-gradient(180deg, #0b1730 0%, #14233b 100%);
            --shell-bg: #0f1c30;
            --shell-border: rgba(111, 140, 172, 0.34);
            --surface: rgba(15, 29, 49, 0.90);
            --surface-strong: #16253d;
            --line: rgba(127, 153, 182, 0.26);
            --text: #edf5ff;
            --muted: #b5c5d6;
            --accent: #74a9f0;
            --accent-soft: rgba(116, 169, 240, 0.16);
            --footer-bg: linear-gradient(180deg, #091425 0%, #0b1528 100%);
        }

        [x-cloak] { display: none !important; }

        body {
            margin: 0;
            background: var(--app-bg);
            color: var(--text);
        }

        .app-fixed-background {
            position: fixed;
            inset: 0;
            pointer-events: none;
            background:
                radial-gradient(circle at top left, rgba(255,255,255,0.14), transparent 28%),
                radial-gradient(circle at bottom right, rgba(20,35,59,0.18), transparent 22%),
                var(--app-bg);
            transform: translateZ(0);
            overflow: hidden;
        }

        .app-fixed-background::before {
            content: "";
            position: absolute;
            inset: -6%;
            background-image: url('{{ asset('engram-language-pattern.svg') }}');
            background-position: center top;
            background-repeat: repeat;
            background-size: 720px 720px;
            opacity: 0.22;
            filter: saturate(1.05) contrast(1.08);
        }

        .app-fixed-background::after {
            content: "";
            position: absolute;
            inset: 0;
            background:
                radial-gradient(circle at 14% 18%, rgba(255, 255, 255, 0.22), transparent 20%),
                radial-gradient(circle at 78% 10%, rgba(245, 155, 47, 0.10), transparent 18%),
                linear-gradient(180deg, rgba(255, 255, 255, 0.03), rgba(19, 35, 59, 0.08));
        }

        .dark .app-fixed-background::before {
            opacity: 0.16;
            filter: brightness(1.22) saturate(0.92) contrast(1.12);
        }

        .dark .app-fixed-background::after {
            background:
                radial-gradient(circle at 18% 18%, rgba(116, 169, 240, 0.10), transparent 22%),
                radial-gradient(circle at 76% 10%, rgba(245, 155, 47, 0.06), transparent 18%),
                linear-gradient(180deg, rgba(5, 11, 23, 0.04), rgba(5, 11, 23, 0.18));
        }

        .catalog-shell {
            position: relative;
            background:
                radial-gradient(circle at top, rgba(104, 165, 231, 0.10), transparent 24%),
                linear-gradient(180deg, var(--shell-bg) 0%, color-mix(in srgb, var(--shell-bg) 92%, white) 100%);
            border-color: var(--shell-border);
            border-radius: var(--shell-radius);
        }

        .site-header {
            position: sticky;
            top: 0;
            z-index: 40;
            isolation: isolate;
            background: transparent !important;
            border-top-left-radius: var(--shell-radius);
            border-top-right-radius: var(--shell-radius);
            contain: none;
            overflow: visible;
            transform: translateZ(0);
            backface-visibility: hidden;
            transition: border-top-left-radius 160ms ease, border-top-right-radius 160ms ease, box-shadow 160ms ease, border-color 160ms ease;
        }

        .site-header > div {
            overflow: visible;
        }

        .site-header::before {
            content: "";
            position: absolute;
            inset: 0;
            z-index: -1;
            pointer-events: none;
            background: color-mix(in srgb, var(--surface-strong) 94%, var(--surface));
            border-top-left-radius: inherit;
            border-top-right-radius: inherit;
            transition: opacity 160ms ease, background-color 160ms ease;
            will-change: opacity;
        }

        .catalog-shell.is-header-stuck .site-header {
            border-top-left-radius: 0 !important;
            border-top-right-radius: 0 !important;
            box-shadow: 0 16px 36px rgba(17, 38, 63, 0.12);
        }

        .catalog-shell.is-header-stuck .site-header::before {
            background: color-mix(in srgb, var(--surface-strong) 98%, var(--surface));
        }

        .surface-card {
            background: var(--surface);
            border-color: var(--line);
        }

        .surface-card-strong {
            background: var(--surface-strong);
            border-color: var(--line);
        }

        .soft-accent {
            background: var(--accent-soft);
        }

        .footer-shell {
            background: var(--footer-bg);
            border-bottom-left-radius: var(--shell-radius);
            border-bottom-right-radius: var(--shell-radius);
            overflow: hidden;
        }

        .catalog-shell > main,
        .catalog-shell > footer {
            position: relative;
            z-index: 1;
        }

        #shell-random-shapes {
            position: absolute;
            inset: 0;
            z-index: 0;
        }

        #shell-random-shapes > span {
            will-change: transform, opacity;
        }

        .decorated-content .surface-card {
            background: color-mix(in srgb, var(--surface) 92%, var(--surface-strong));
        }

        .decorated-content .surface-card-strong {
            background: color-mix(in srgb, var(--surface-strong) 96%, var(--surface));
        }

        .decorated-content .soft-accent {
            background: color-mix(in srgb, var(--accent-soft) 84%, var(--surface-strong));
        }

        body.scroll-optimized .theory-lazy-section {
            content-visibility: auto;
            contain-intrinsic-size: auto 720px;
        }

        body.scroll-optimized .decorated-content .surface-card {
            background: color-mix(in srgb, var(--surface) 96%, var(--surface-strong));
        }

        body.scroll-optimized .decorated-content .surface-card-strong {
            background: color-mix(in srgb, var(--surface-strong) 98%, var(--surface));
        }

        body.scroll-optimized .decorated-content .soft-accent {
            background: color-mix(in srgb, var(--accent-soft) 90%, var(--surface-strong));
        }

        .nd-page {
            overflow: hidden;
            padding-inline: 0.75rem;
            padding-block: 2rem;
        }

        .nd-section {
            padding-inline: 0;
            padding-block: 2.5rem;
        }

        .nd-section-tight {
            padding-inline: 0;
            padding-block: 2rem;
        }

        .catalog-frame {
            width: 100%;
        }

        @media (max-width: 1023px) {
            body {
                background: var(--shell-bg);
                overflow-x: hidden;
            }

            .app-fixed-background {
                display: none;
            }

            .catalog-frame {
                width: 100vw !important;
                max-width: 100vw !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            .catalog-shell {
                width: 100vw !important;
                min-height: 100dvh;
                margin: 0 !important;
                border: 0 !important;
                border-radius: 0 !important;
                box-shadow: none !important;
            }

            .site-header,
            .site-header::before,
            .footer-shell {
                border-radius: 0 !important;
            }

            .site-header {
                width: 100% !important;
                padding-inline: 0 !important;
            }

            .site-header > div {
                padding-inline: 1rem;
            }

            .nd-page {
                width: 100% !important;
                overflow: visible !important;
                padding-inline: 0 !important;
                padding-block: 1rem 1.25rem !important;
            }

            main {
                overflow: visible;
            }
        }

        @media (min-width: 640px) {
            .nd-page {
                padding-inline: 1.25rem;
            }

            .nd-page {
                padding-block: 2.5rem;
            }

            .nd-section {
                padding-block: 2.85rem;
            }

            .nd-section-tight {
                padding-block: 2.35rem;
            }
        }

        @media (min-width: 1024px) {
            .nd-page {
                padding-inline: 1.5rem;
            }

            .nd-page {
                padding-block: 2.75rem;
            }

            .nd-section {
                padding-block: 3.25rem;
            }

            .nd-section-tight {
                padding-block: 2.6rem;
            }
        }
    </style>
    @livewireStyles
    @yield('head')
</head>
<body class="min-h-full font-body antialiased @yield('body_class')">
    <div class="app-fixed-background" aria-hidden="true"></div>
    <div class="catalog-frame relative mx-auto max-w-[1440px] px-0 py-0 lg:px-8 lg:py-6">
        <div id="catalog-shell" class="catalog-shell rounded-none border-0 shadow-none lg:rounded-[30px] lg:border lg:shadow-panel">
            <div id="shell-random-shapes" class="pointer-events-none" aria-hidden="true"></div>
            @yield('shell_background')
            <div id="site-header-sentinel" class="pointer-events-none absolute inset-x-0 top-0 h-px" aria-hidden="true"></div>
            <header id="site-header" class="site-header border-b px-0 py-4 lg:px-8 surface-card" style="border-color: var(--line);">
                <div class="flex flex-col gap-4 px-4 xl:flex-row xl:items-center xl:justify-between lg:px-0">
                    <div class="flex items-center justify-between gap-4">
                        <a href="{{ localized_route('home') }}" class="flex items-center gap-3" aria-label="Gramlyze">
                            <x-gramlyze-logo variant="compact" class="h-12 w-12 rounded-2xl bg-[linear-gradient(135deg,#f7b34c_0%,#fff0d7_50%,#2f67b1_100%)] shadow-card" />
                            <div>
                                <p class="font-display text-xl font-extrabold leading-none tracking-tight">GRAMLYZE</p>
                                <p class="mt-1 text-xs font-semibold uppercase tracking-[0.28em]" style="color: var(--muted);">{{ __('public.home.badge') }}</p>
                            </div>
                        </a>

                        <button @click="mobile = !mobile" class="inline-flex h-11 w-11 items-center justify-center rounded-2xl border xl:hidden surface-card-strong" aria-label="{{ __('public.nav.menu') }}">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                    </div>

                    <nav class="hidden items-center gap-6 text-[15px] font-semibold xl:flex">
                        <a href="{{ localized_route('catalog.tests-cards') }}" class="transition hover:text-ocean">{{ __('public.nav.catalog') }}</a>
                        <a href="{{ localized_route('theory.index') }}" class="transition hover:text-ocean">{{ __('public.nav.theory') }}</a>
                        <a href="{{ localized_route('courses.show', 'polyglot-english-a1') }}" class="transition hover:text-ocean">{{ __('public.nav.polyglot_course') }}</a>
                        <a href="{{ localized_route('words.test') }}" class="transition hover:text-ocean">{{ __('public.nav.words_test') }}</a>
                        <a href="{{ localized_route('verbs.test') }}" class="transition hover:text-ocean">{{ __('public.nav.verbs_test') }}</a>
                    </nav>

                    <div class="hidden items-center gap-3 xl:flex">
                        <div x-data="searchBox()" class="relative">
                            <form @submit.prevent="go">
                                <input x-model="query" @input="autocomplete" @keydown.escape="open = false" type="search" placeholder="{{ __('public.search.placeholder') }}" class="w-72 rounded-2xl border px-4 py-3 text-sm font-medium shadow-sm outline-none transition focus:border-ocean focus:ring-2 focus:ring-blue-100 surface-card-strong">
                            </form>
                            <div x-show="open" x-cloak x-transition class="absolute left-0 right-0 top-full z-30 mt-2 max-h-[min(28rem,calc(100vh-8rem))] overflow-y-auto overscroll-contain rounded-[22px] border shadow-card surface-card-strong">
                                <template x-if="!results.length">
                                    <div class="px-4 py-3 text-sm" style="color: var(--muted);">{{ __('public.search.nothing_found') }}</div>
                                </template>
                                <template x-for="item in results" :key="item.url">
                                    <a :href="item.url" class="block border-b px-4 py-3 last:border-b-0 hover:bg-blue-50/80 dark:hover:bg-slate-800/70" style="border-color: var(--line);">
                                        <p class="font-semibold" x-html="highlight(item.title)"></p>
                                        <p class="text-xs" style="color: var(--muted);" x-text="item.url"></p>
                                    </a>
                                </template>
                            </div>
                        </div>

                        <div x-data="languageSwitcher()" class="relative">
                            <button @click="toggle" :aria-expanded="open" class="flex items-center gap-3 rounded-2xl border px-3 py-2.5 text-sm font-semibold shadow-sm transition hover:border-ocean surface-card-strong">
                                <span class="inline-flex h-8 w-8 items-center justify-center rounded-xl soft-accent text-xs font-bold" style="color: var(--accent);" x-text="active.code.toUpperCase()"></span>
                                <span class="max-w-24 truncate" x-text="active.localized_name || active.native_name || active.name"></span>
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <div x-show="open" x-cloak @click.outside="open = false" x-transition class="absolute right-0 z-30 mt-2 w-72 overflow-hidden rounded-[22px] border shadow-card surface-card-strong">
                                <div class="border-b p-3" style="border-color: var(--line);">
                                    <input x-model="filter" type="search" placeholder="{{ __('public.language.search') }}" class="w-full rounded-2xl border px-3 py-2 text-sm outline-none transition focus:border-ocean focus:ring-2 focus:ring-blue-100 surface-card">
                                </div>
                                <div class="max-h-64 overflow-y-auto">
                                    <template x-for="lang in filtered" :key="lang.code">
                                        <a :href="lang.url" @click="open = false" class="flex items-center justify-between gap-3 border-b px-4 py-3 last:border-b-0 hover:bg-blue-50/80 dark:hover:bg-slate-800/70" style="border-color: var(--line);">
                                            <div class="flex items-center gap-3">
                                                <span class="inline-flex h-8 w-8 items-center justify-center rounded-xl soft-accent text-xs font-bold" style="color: var(--accent);" x-text="lang.code.toUpperCase()"></span>
                                                <div>
                                                    <p class="text-sm font-semibold" x-text="lang.localized_name || lang.native_name || lang.name"></p>
                                                    <p class="text-xs" style="color: var(--muted);" x-text="lang.name"></p>
                                                </div>
                                            </div>
                                            <span x-show="lang.is_current" x-cloak class="text-xs font-semibold" style="color: var(--accent);">{{ __('public.language.current') }}</span>
                                        </a>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <button @click="toggleTheme" class="inline-flex h-11 w-11 items-center justify-center rounded-2xl border shadow-sm transition hover:border-ocean surface-card-strong" :aria-label="isDark ? '{{ __('public.theme.light') }}' : '{{ __('public.theme.dark') }}'">
                            <template x-if="isDark">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z" />
                                </svg>
                            </template>
                            <template x-if="!isDark">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="5" />
                                    <path d="M12 1v2m0 18v2m11-11h-2M3 12H1m17.657-7.657l-1.414 1.414M6.757 17.243l-1.414 1.414m0-13.414l1.414 1.414M17.243 17.243l1.414 1.414" />
                                </svg>
                            </template>
                        </button>
                    </div>
                </div>

                <div id="site-header-test-controls" class="px-4 lg:px-0"></div>

                <div x-show="mobile" x-cloak x-transition class="mt-4 space-y-4 border-t pt-4 xl:hidden" style="border-color: var(--line);">
                    <nav class="grid gap-2 text-sm font-semibold">
                        <a href="{{ localized_route('catalog.tests-cards') }}" class="rounded-2xl border px-4 py-3 transition hover:border-ocean surface-card-strong">{{ __('public.nav.catalog') }}</a>
                        <a href="{{ localized_route('theory.index') }}" class="rounded-2xl border px-4 py-3 transition hover:border-ocean surface-card-strong">{{ __('public.nav.theory') }}</a>
                        <a href="{{ localized_route('courses.show', 'polyglot-english-a1') }}" class="rounded-2xl border px-4 py-3 transition hover:border-ocean surface-card-strong">{{ __('public.nav.polyglot_course') }}</a>
                        <a href="{{ localized_route('words.test') }}" class="rounded-2xl border px-4 py-3 transition hover:border-ocean surface-card-strong">{{ __('public.nav.words_test') }}</a>
                        <a href="{{ localized_route('verbs.test') }}" class="rounded-2xl border px-4 py-3 transition hover:border-ocean surface-card-strong">{{ __('public.nav.verbs_test') }}</a>
                    </nav>

                    <div class="grid gap-3 md:grid-cols-[1fr_auto_auto]">
                        <div x-data="searchBox()" class="relative">
                            <form @submit.prevent="go">
                                <input x-model="query" @input="autocomplete" @keydown.escape="open = false" type="search" placeholder="{{ __('public.search.placeholder') }}" class="w-full rounded-2xl border px-4 py-3 text-sm font-medium outline-none transition focus:border-ocean focus:ring-2 focus:ring-blue-100 surface-card-strong">
                            </form>
                            <div x-show="open" x-cloak x-transition class="absolute left-0 right-0 top-full z-20 mt-2 max-h-[min(24rem,calc(100vh-10rem))] overflow-y-auto overscroll-contain rounded-[22px] border shadow-card surface-card-strong">
                                <template x-for="item in results" :key="item.url">
                                    <a :href="item.url" class="block border-b px-4 py-3 last:border-b-0 hover:bg-blue-50/80 dark:hover:bg-slate-800/70" style="border-color: var(--line);">
                                        <p class="font-semibold" x-html="highlight(item.title)"></p>
                                        <p class="text-xs" style="color: var(--muted);" x-text="item.url"></p>
                                    </a>
                                </template>
                            </div>
                        </div>

                        <div x-data="languageSwitcher()" class="relative">
                            <button @click="toggle" class="flex h-full items-center gap-2 rounded-2xl border px-4 py-3 text-sm font-semibold surface-card-strong">
                                <span x-text="active.code.toUpperCase()"></span>
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <div x-show="open" x-cloak @click.outside="open = false" class="absolute left-0 right-0 z-20 mt-2 overflow-hidden rounded-[22px] border shadow-card surface-card-strong">
                                <template x-for="lang in filtered" :key="lang.code">
                                    <a :href="lang.url" class="block border-b px-4 py-3 text-sm last:border-b-0 hover:bg-blue-50/80 dark:hover:bg-slate-800/70" style="border-color: var(--line);" x-text="lang.localized_name || lang.native_name || lang.name"></a>
                                </template>
                            </div>
                        </div>

                        <button @click="toggleTheme" class="inline-flex h-full items-center justify-center rounded-2xl border px-4 py-3 surface-card-strong" :aria-label="isDark ? '{{ __('public.theme.light') }}' : '{{ __('public.theme.dark') }}'">
                            <span x-show="!isDark" x-cloak>☾</span>
                            <span x-show="isDark" x-cloak>☼</span>
                        </button>
                    </div>
                </div>
            </header>

            <main class="decorated-content px-4 lg:px-0">
                @yield('content')
            </main>

            <footer class="footer-shell relative overflow-hidden px-5 py-8 text-white sm:px-8 lg:px-10">
                <div class="grid gap-8 lg:grid-cols-[1.1fr_0.9fr_0.9fr]">
                    <div class="space-y-4">
                        <div class="flex items-center gap-3">
                            <x-gramlyze-logo variant="compact" class="h-12 w-12 rounded-2xl bg-[linear-gradient(135deg,#f7b34c_0%,#fff0d7_50%,#2f67b1_100%)] shadow-card" />
                            <div>
                                <p class="font-display text-xl font-extrabold leading-none tracking-tight">GRAMLYZE</p>
                                <p class="mt-1 text-sm text-white/70">{{ __('public.footer.description') }}</p>
                            </div>
                        </div>
                        <p class="text-sm text-white/70">© <span x-text="new Date().getFullYear()"></span> Gramlyze</p>
                    </div>

                    <div>
                        <h3 class="font-display text-lg font-extrabold">{{ __('public.footer.links') }}</h3>
                        <div class="mt-4 grid gap-3 text-sm text-white/80">
                            <a href="{{ localized_route('catalog.tests-cards') }}" class="transition hover:text-white">{{ __('public.nav.catalog') }}</a>
                            <a href="{{ localized_route('theory.index') }}" class="transition hover:text-white">{{ __('public.nav.theory') }}</a>
                            <a href="{{ localized_route('courses.show', 'polyglot-english-a1') }}" class="transition hover:text-white">{{ __('public.nav.polyglot_course') }}</a>
                            <a href="{{ localized_route('words.test') }}" class="transition hover:text-white">{{ __('public.nav.words_test') }}</a>
                            <a href="{{ localized_route('verbs.test') }}" class="transition hover:text-white">{{ __('public.nav.verbs_test') }}</a>
                        </div>
                    </div>

                    <div>
                        <h3 class="font-display text-lg font-extrabold">{{ __('public.footer.contact') }}</h3>
                        <p class="mt-4 text-sm text-white/80">{{ __('public.footer.support') }}</p>
                        <div class="mt-5 flex flex-wrap gap-3">
                            <a href="{{ localized_route('site.search') }}" class="rounded-2xl bg-white/10 px-4 py-3 text-sm font-semibold transition hover:bg-white/20">{{ __('public.search.button') }}</a>
                            <button @click="toggleTheme" class="rounded-2xl bg-white/10 px-4 py-3 text-sm font-semibold transition hover:bg-white/20">{{ __('public.footer.theme') }}</button>
                        </div>
                    </div>
                </div>

            </footer>
        </div>
    </div>
 
    <script>
        function themeController() {
            return {
                isDark: false,
                mobile: false,
                init() {
                    const saved = localStorage.getItem('theme');
                    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                    this.isDark = saved ? saved === 'dark' : prefersDark;
                },
                toggleTheme() {
                    this.isDark = !this.isDark;
                    localStorage.setItem('theme', this.isDark ? 'dark' : 'light');
                }
            }
        }

        function searchBox() {
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
                    const safe = (text || '').replace(/[&<>"]/g, (char) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[char]));
                    if (!this.query) {
                        return safe;
                    }

                    const regex = new RegExp('(' + this.query.replace(/[-/\\^$*+?.()|[\]{}]/g, '\\$&') + ')', 'ig');

                    return safe.replace(regex, '<mark class="rounded bg-amber-200/80 px-1 text-slate-900">$1</mark>');
                },
                async autocomplete() {
                    if (this.query.trim().length < 2) {
                        this.results = [];
                        this.open = false;
                        return;
                    }

                    const response = await fetch('{{ localized_route('site.search') }}?q=' + encodeURIComponent(this.query.trim()), {
                        headers: { 'Accept': 'application/json' }
                    });

                    this.results = await response.json();
                    this.open = true;
                }
            }
        }

        function languageSwitcher() {
            return {
                open: false,
                filter: '',
                languages: @json($__languageSwitcher ?? []),
                get active() {
                    return this.languages.find((lang) => lang.is_current)
                        || this.languages[0]
                        || { code: '{{ app()->getLocale() }}', name: '{{ app()->getLocale() }}' };
                },
                toggle() {
                    this.open = !this.open;
                },
                get filtered() {
                    const term = this.filter.toLowerCase();

                    return this.languages.filter((lang) => (
                        !term
                        || (lang.localized_name && lang.localized_name.toLowerCase().includes(term))
                        || (lang.name && lang.name.toLowerCase().includes(term))
                        || (lang.native_name && lang.native_name.toLowerCase().includes(term))
                        || lang.code.toLowerCase().includes(term)
                    ));
                }
            }
        }

        function initStickyShellHeader() {
            const shell = document.getElementById('catalog-shell');
            const sentinel = document.getElementById('site-header-sentinel');
            const desktopQuery = window.matchMedia('(min-width: 1024px)');

            if (!shell || !sentinel) {
                return;
            }

            let observer = null;
            let fallbackBound = false;
            let lastState = null;

            const applyStickyState = (isStuck) => {
                const nextState = desktopQuery.matches && Boolean(isStuck);

                if (lastState === nextState) {
                    return;
                }

                lastState = nextState;
                shell.classList.toggle('is-header-stuck', nextState);
            };

            const syncStickyFallback = () => {
                applyStickyState(Math.round(sentinel.getBoundingClientRect().top) < 0);
            };

            const bindFallback = () => {
                if (fallbackBound) {
                    return;
                }

                fallbackBound = true;

                const requestSync = () => window.requestAnimationFrame(syncStickyFallback);
                requestSync();
                window.addEventListener('scroll', requestSync, { passive: true });
                window.addEventListener('resize', requestSync, { passive: true });
            };

            const connectObserver = () => {
                if (observer) {
                    observer.disconnect();
                    observer = null;
                }

                applyStickyState(false);

                if (!desktopQuery.matches) {
                    return;
                }

                if (typeof IntersectionObserver !== 'function') {
                    bindFallback();
                    return;
                }

                observer = new IntersectionObserver(([entry]) => {
                    applyStickyState(!entry.isIntersecting && entry.boundingClientRect.top < 0);
                }, {
                    threshold: [0, 1],
                });

                observer.observe(sentinel);
                window.requestAnimationFrame(syncStickyFallback);
            };

            connectObserver();

            if (typeof desktopQuery.addEventListener === 'function') {
                desktopQuery.addEventListener('change', connectObserver);
            } else if (typeof desktopQuery.addListener === 'function') {
                desktopQuery.addListener(connectObserver);
            }
        }

        function buildShellRandomShapes() {
            const layer = document.getElementById('shell-random-shapes');

            if (!layer) {
                return;
            }

            const width = layer.offsetWidth;
            const height = layer.offsetHeight;

            if (!width || !height) {
                return;
            }

            const randomBetween = (min, max) => min + (Math.random() * (max - min));
            const pick = (items) => items[Math.floor(Math.random() * items.length)];
            const shuffle = (items) => {
                const copy = [...items];

                for (let index = copy.length - 1; index > 0; index -= 1) {
                    const swapIndex = Math.floor(Math.random() * (index + 1));
                    [copy[index], copy[swapIndex]] = [copy[swapIndex], copy[index]];
                }

                return copy;
            };
            const clamp = (value, min, max) => Math.min(Math.max(value, min), max);

            const compact = width < 640;
            const medium = width >= 640 && width < 1024;
            const sizeScale = compact ? 0.68 : (medium ? 0.82 : 1);
            const columns = compact ? 3 : (medium ? 4 : (width >= 1280 ? 6 : 5));
            const rows = compact
                ? Math.max(7, Math.min(13, Math.round(height / 150)))
                : Math.max(6, Math.min(10, Math.round(height / 175)));
            const cellWidth = width / columns;
            const cellHeight = height / rows;
            const shapes = [];
            const scaledBetween = (min, max) => randomBetween(min * sizeScale, max * sizeScale);
            const accentBetween = (min, max) => randomBetween(min * sizeScale * 0.58, max * sizeScale * 0.58);

            const shapeFactories = [
                () => {
                    const size = scaledBetween(58, 124);
                    const stroke = scaledBetween(8, 18);
                    return {
                        width: size,
                        height: size,
                        borderRadius: '999px',
                        border: `${stroke}px solid ${pick([
                            'rgba(47, 103, 177, 0.24)',
                            'rgba(116, 169, 240, 0.30)',
                            'rgba(156, 163, 175, 0.24)',
                            'rgba(125, 211, 252, 0.28)',
                        ])}`,
                        background: 'transparent',
                    };
                },
                () => {
                    const width = scaledBetween(72, 152);
                    const height = scaledBetween(14, 34);
                    return {
                        width,
                        height,
                        borderRadius: '999px',
                        background: pick([
                            'rgba(47, 103, 177, 0.18)',
                            'rgba(245, 155, 47, 0.18)',
                            'rgba(16, 185, 129, 0.18)',
                            'rgba(244, 114, 182, 0.14)',
                            'rgba(56, 189, 248, 0.16)',
                        ]),
                    };
                },
                () => {
                    const width = scaledBetween(46, 92);
                    const height = scaledBetween(46, 92);
                    return {
                        width,
                        height,
                        borderRadius: `${scaledBetween(18, 34)}px`,
                        border: `${scaledBetween(6, 12)}px solid ${pick([
                            'rgba(16, 185, 129, 0.24)',
                            'rgba(125, 211, 252, 0.24)',
                            'rgba(217, 70, 239, 0.18)',
                            'rgba(251, 191, 36, 0.22)',
                        ])}`,
                        background: 'transparent',
                    };
                },
                () => {
                    const width = scaledBetween(38, 84);
                    const height = scaledBetween(38, 84);
                    return {
                        width,
                        height,
                        borderRadius: `${scaledBetween(16, 28)}px`,
                        background: pick([
                            'rgba(245, 155, 47, 0.12)',
                            'rgba(16, 185, 129, 0.12)',
                            'rgba(47, 103, 177, 0.10)',
                            'rgba(251, 113, 133, 0.10)',
                        ]),
                    };
                },
                () => {
                    const width = scaledBetween(18, 30);
                    const height = scaledBetween(110, 190);
                    return {
                        width,
                        height,
                        borderRadius: '999px',
                        background: pick([
                            'rgba(245, 155, 47, 0.14)',
                            'rgba(47, 103, 177, 0.14)',
                            'rgba(16, 185, 129, 0.12)',
                        ]),
                    };
                },
                () => {
                    const width = scaledBetween(86, 156);
                    const height = scaledBetween(42, 74);
                    return {
                        width,
                        height,
                        borderRadius: `${scaledBetween(20, 30)}px`,
                        border: `${scaledBetween(8, 12)}px solid ${pick([
                            'rgba(167, 139, 250, 0.18)',
                            'rgba(125, 211, 252, 0.18)',
                            'rgba(47, 103, 177, 0.16)',
                        ])}`,
                        background: 'transparent',
                    };
                },
                () => {
                    const size = scaledBetween(72, 150);
                    return {
                        width: size,
                        height: size,
                        borderRadius: '999px',
                        background: pick([
                            'rgba(47, 103, 177, 0.08)',
                            'rgba(245, 155, 47, 0.08)',
                            'rgba(16, 185, 129, 0.08)',
                        ]),
                        filter: `blur(${scaledBetween(1, 3)}px)`,
                    };
                },
                () => {
                    const width = scaledBetween(62, 118);
                    const height = scaledBetween(54, 104);
                    return {
                        width,
                        height,
                        background: pick([
                            'rgba(245, 155, 47, 0.14)',
                            'rgba(47, 103, 177, 0.14)',
                            'rgba(16, 185, 129, 0.14)',
                            'rgba(244, 114, 182, 0.12)',
                        ]),
                        clipPath: 'polygon(50% 0%, 0% 100%, 100% 100%)',
                    };
                },
                () => {
                    const size = scaledBetween(54, 96);
                    return {
                        width: size,
                        height: size,
                        background: pick([
                            'rgba(125, 211, 252, 0.16)',
                            'rgba(167, 139, 250, 0.14)',
                            'rgba(251, 191, 36, 0.14)',
                            'rgba(16, 185, 129, 0.14)',
                        ]),
                        clipPath: 'polygon(50% 0%, 100% 50%, 50% 100%, 0% 50%)',
                    };
                },
                () => {
                    const width = scaledBetween(72, 124);
                    const height = scaledBetween(62, 108);
                    return {
                        width,
                        height,
                        background: pick([
                            'rgba(47, 103, 177, 0.12)',
                            'rgba(245, 155, 47, 0.12)',
                            'rgba(16, 185, 129, 0.12)',
                            'rgba(217, 70, 239, 0.10)',
                        ]),
                        clipPath: 'polygon(25% 0%, 75% 0%, 100% 50%, 75% 100%, 25% 100%, 0% 50%)',
                    };
                },
                () => {
                    const size = scaledBetween(52, 92);
                    return {
                        width: size,
                        height: size,
                        background: pick([
                            'rgba(245, 155, 47, 0.16)',
                            'rgba(125, 211, 252, 0.16)',
                            'rgba(16, 185, 129, 0.14)',
                            'rgba(217, 70, 239, 0.12)',
                        ]),
                        clipPath: 'polygon(35% 0%, 65% 0%, 65% 35%, 100% 35%, 100% 65%, 65% 65%, 65% 100%, 35% 100%, 35% 65%, 0% 65%, 0% 35%, 35% 35%)',
                    };
                },
                () => {
                    const width = scaledBetween(64, 120);
                    const height = scaledBetween(58, 110);
                    return {
                        width,
                        height,
                        background: pick([
                            'rgba(47, 103, 177, 0.12)',
                            'rgba(245, 155, 47, 0.12)',
                            'rgba(16, 185, 129, 0.12)',
                            'rgba(251, 113, 133, 0.10)',
                        ]),
                        clipPath: 'polygon(50% 0%, 100% 38%, 82% 100%, 18% 100%, 0% 38%)',
                    };
                },
                () => {
                    const size = scaledBetween(44, 82);
                    return {
                        width: size,
                        height: size,
                        borderRadius: `${scaledBetween(14, 22)}px`,
                        border: `${scaledBetween(5, 9)}px solid ${pick([
                            'rgba(125, 211, 252, 0.24)',
                            'rgba(251, 191, 36, 0.24)',
                            'rgba(16, 185, 129, 0.22)',
                            'rgba(217, 70, 239, 0.18)',
                        ])}`,
                        background: 'transparent',
                        transformOverride: `rotate(${randomBetween(38, 52)}deg)`,
                    };
                }
            ];

            const accentFactories = [
                () => {
                    const size = accentBetween(18, 42);
                    return {
                        width: size,
                        height: size,
                        borderRadius: '999px',
                        background: pick([
                            'rgba(47, 103, 177, 0.14)',
                            'rgba(245, 155, 47, 0.14)',
                            'rgba(16, 185, 129, 0.12)',
                            'rgba(217, 70, 239, 0.10)',
                        ]),
                    };
                },
                () => {
                    const width = accentBetween(28, 72);
                    const height = accentBetween(8, 20);
                    return {
                        width,
                        height,
                        borderRadius: '999px',
                        background: pick([
                            'rgba(47, 103, 177, 0.16)',
                            'rgba(245, 155, 47, 0.16)',
                            'rgba(16, 185, 129, 0.14)',
                            'rgba(56, 189, 248, 0.14)',
                        ]),
                    };
                },
                () => {
                    const size = accentBetween(24, 58);
                    const stroke = accentBetween(4, 9);
                    return {
                        width: size,
                        height: size,
                        borderRadius: '999px',
                        border: `${stroke}px solid ${pick([
                            'rgba(125, 211, 252, 0.22)',
                            'rgba(251, 191, 36, 0.22)',
                            'rgba(16, 185, 129, 0.18)',
                            'rgba(217, 70, 239, 0.16)',
                        ])}`,
                        background: 'transparent',
                    };
                },
                () => {
                    const size = accentBetween(20, 46);
                    return {
                        width: size,
                        height: size,
                        background: pick([
                            'rgba(245, 155, 47, 0.12)',
                            'rgba(125, 211, 252, 0.12)',
                            'rgba(16, 185, 129, 0.10)',
                        ]),
                        clipPath: 'polygon(50% 0%, 100% 50%, 50% 100%, 0% 50%)',
                    };
                }
            ];

            for (let row = 0; row < rows; row += 1) {
                for (let col = 0; col < columns; col += 1) {
                    const shape = shapeFactories[Math.floor(Math.random() * shapeFactories.length)]();
                    const offsetX = randomBetween(cellWidth * 0.16, cellWidth * 0.84);
                    const offsetY = randomBetween(cellHeight * 0.14, cellHeight * 0.86);
                    const left = clamp((col * cellWidth) + offsetX - (shape.width / 2), 0, width - shape.width);
                    const top = clamp((row * cellHeight) + offsetY - (shape.height / 2), 0, height - shape.height);

                    shapes.push({
                        left,
                        top,
                        rotate: randomBetween(-32, 32),
                        opacity: randomBetween(0.72, 1),
                        ...shape,
                    });
                }
            }

            const accentShapesCount = compact ? 18 : (medium ? 28 : 42);

            for (let index = 0; index < accentShapesCount; index += 1) {
                const shape = accentFactories[Math.floor(Math.random() * accentFactories.length)]();
                const left = clamp(randomBetween(width * 0.03, width * 0.97) - (shape.width / 2), 0, width - shape.width);
                const top = clamp(randomBetween(height * 0.03, height * 0.97) - (shape.height / 2), 0, height - shape.height);

                shapes.push({
                    left,
                    top,
                    rotate: randomBetween(-40, 40),
                    opacity: randomBetween(0.5, 0.88),
                    ...shape,
                });
            }

            layer.replaceChildren();

            for (const shape of shuffle(shapes)) {
                const node = document.createElement('span');
                node.style.position = 'absolute';
                node.style.left = `${shape.left}px`;
                node.style.top = `${shape.top}px`;
                node.style.width = `${shape.width}px`;
                node.style.height = `${shape.height}px`;
                node.style.transform = `rotate(${shape.rotate}deg)`;
                node.style.borderRadius = shape.borderRadius;
                node.style.opacity = shape.opacity;
                node.style.background = shape.background || 'transparent';
                node.style.border = shape.border || 'none';
                node.style.filter = shape.filter || 'none';
                node.style.clipPath = shape.clipPath || 'none';
                if (shape.transformOverride) {
                    node.style.transform = shape.transformOverride;
                }
                layer.appendChild(node);
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            initStickyShellHeader();
            window.requestAnimationFrame(buildShellRandomShapes);

            let shellShapesResizeTimeout = null;
            const scheduleShellShapes = () => {
                window.clearTimeout(shellShapesResizeTimeout);
                shellShapesResizeTimeout = window.setTimeout(buildShellRandomShapes, 180);
            };

            window.addEventListener('load', scheduleShellShapes, { once: true });
            window.addEventListener('resize', scheduleShellShapes);
        });
    </script>
    @livewireScripts
    @yield('scripts')
</body>
</html>
