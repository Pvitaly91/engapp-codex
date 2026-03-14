<!doctype html>
<html lang="{{ app()->getLocale() }}" class="h-full" x-data="themeController()" x-init="init()" x-bind:class="{ 'dark': isDark }">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
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
        }

        .catalog-shell {
            background:
                radial-gradient(circle at top, rgba(104, 165, 231, 0.10), transparent 24%),
                linear-gradient(180deg, var(--shell-bg) 0%, color-mix(in srgb, var(--shell-bg) 92%, white) 100%);
            border-color: var(--shell-border);
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
        }
    </style>
    @livewireStyles
    @yield('head')
</head>
<body class="min-h-full font-body antialiased">
    <div class="app-fixed-background" aria-hidden="true"></div>
    <div class="relative mx-auto max-w-[1440px] px-4 py-5 sm:px-6 lg:px-8 lg:py-6">
        <div class="catalog-shell overflow-hidden rounded-[30px] border shadow-panel">
            <header class="border-b px-5 py-4 backdrop-blur sm:px-8 surface-card" style="border-color: var(--line);">
                <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
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
                        <a href="{{ localized_route('words.test') }}" class="transition hover:text-ocean">{{ __('public.nav.words_test') }}</a>
                        <a href="{{ localized_route('verbs.test') }}" class="transition hover:text-ocean">{{ __('public.nav.verbs_test') }}</a>
                    </nav>

                    <div class="hidden items-center gap-3 xl:flex">
                        <div x-data="searchBox()" class="relative">
                            <form @submit.prevent="go">
                                <input x-model="query" @input="autocomplete" @keydown.escape="open = false" type="search" placeholder="{{ __('public.search.placeholder') }}" class="w-72 rounded-2xl border px-4 py-3 text-sm font-medium shadow-sm outline-none transition focus:border-ocean focus:ring-2 focus:ring-blue-100 surface-card-strong">
                            </form>
                            <div x-show="open" x-cloak x-transition class="absolute left-0 right-0 top-full z-30 mt-2 overflow-hidden rounded-[22px] border shadow-card surface-card-strong">
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
                                <span class="max-w-24 truncate" x-text="active.native_name || active.name"></span>
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
                                                    <p class="text-sm font-semibold" x-text="lang.native_name || lang.name"></p>
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

                <div x-show="mobile" x-cloak x-transition class="mt-4 space-y-4 border-t pt-4 xl:hidden" style="border-color: var(--line);">
                    <nav class="grid gap-2 text-sm font-semibold">
                        <a href="{{ localized_route('catalog.tests-cards') }}" class="rounded-2xl border px-4 py-3 transition hover:border-ocean surface-card-strong">{{ __('public.nav.catalog') }}</a>
                        <a href="{{ localized_route('theory.index') }}" class="rounded-2xl border px-4 py-3 transition hover:border-ocean surface-card-strong">{{ __('public.nav.theory') }}</a>
                        <a href="{{ localized_route('words.test') }}" class="rounded-2xl border px-4 py-3 transition hover:border-ocean surface-card-strong">{{ __('public.nav.words_test') }}</a>
                        <a href="{{ localized_route('verbs.test') }}" class="rounded-2xl border px-4 py-3 transition hover:border-ocean surface-card-strong">{{ __('public.nav.verbs_test') }}</a>
                    </nav>

                    <div class="grid gap-3 md:grid-cols-[1fr_auto_auto]">
                        <div x-data="searchBox()" class="relative">
                            <form @submit.prevent="go">
                                <input x-model="query" @input="autocomplete" @keydown.escape="open = false" type="search" placeholder="{{ __('public.search.placeholder') }}" class="w-full rounded-2xl border px-4 py-3 text-sm font-medium outline-none transition focus:border-ocean focus:ring-2 focus:ring-blue-100 surface-card-strong">
                            </form>
                            <div x-show="open" x-cloak x-transition class="absolute left-0 right-0 top-full z-20 mt-2 overflow-hidden rounded-[22px] border shadow-card surface-card-strong">
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
                                    <a :href="lang.url" class="block border-b px-4 py-3 text-sm last:border-b-0 hover:bg-blue-50/80 dark:hover:bg-slate-800/70" style="border-color: var(--line);" x-text="lang.native_name || lang.name"></a>
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

            <main>
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
                        || (lang.name && lang.name.toLowerCase().includes(term))
                        || (lang.native_name && lang.native_name.toLowerCase().includes(term))
                        || lang.code.toLowerCase().includes(term)
                    ));
                }
            }
        }
    </script>
    @livewireScripts
    @yield('scripts')
</body>
</html>
