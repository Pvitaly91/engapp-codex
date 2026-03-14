<!doctype html>
<html lang="{{ app()->getLocale() }}" class="h-full" x-data="themeController()" x-init="init()" x-bind:class="{ 'night-mode': isDark }">
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
    <script src="https://unpkg.com/alpinejs" defer></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        steel: "#54677a",
                        night: "#14233b",
                        ocean: "#2f67b1",
                        amber: "#f59b2f",
                        mist:  "#f5fbff",
                        shell: "#fffefd",
                        line:  "#d8e2ee",
                        /* brand maps to ocean so reused engram blocks render correctly */
                        brand: {
                            50:  "#eef4ff",
                            100: "#d9e8ff",
                            200: "#bed3fe",
                            400: "#6b99e0",
                            500: "#4d7fd0",
                            600: "#2f67b1",
                            700: "#1e4d8c",
                            900: "#0c2247"
                        }
                    },
                    fontFamily: {
                        display: ["Archivo", "sans-serif"],
                        body:    ["Manrope", "sans-serif"]
                    },
                    boxShadow: {
                        panel: "0 24px 60px rgba(17,38,63,0.18)",
                        card:  "0 12px 28px rgba(17,38,63,0.10)"
                    },
                    backgroundImage: {
                        glow: "radial-gradient(circle at top left,rgba(255,255,255,0.18),transparent 30%),radial-gradient(circle at bottom right,rgba(16,44,82,0.12),transparent 26%),linear-gradient(180deg,#5b7083 0%,#526678 100%)",
                        hero: "radial-gradient(circle at left,rgba(47,103,177,0.12),transparent 30%),linear-gradient(180deg,#f7fcff 0%,#f4faff 100%)"
                    }
                }
            }
        };
    </script>
    <style>
        :root {
            --nd-bg:      #f5fbff;
            --nd-fg:      #0f172a;
            --nd-card:    #fffefd;
            --nd-muted:   #54677a;
            --nd-border:  #d8e2ee;
            --nd-ocean:   #2f67b1;
            --nd-amber:   #f59b2f;
            --nd-night:   #14233b;

            /* Aliases so engram blocks-v3 partials render correctly */
            --bg:     #f5fbff;
            --fg:     #14233b;
            --card:   #fffefd;
            --muted:  #54677a;
            --border: #d8e2ee;

            /* Tailwind v4 semantic color tokens */
            --color-background:           #f5fbff;
            --color-foreground:           #14233b;
            --color-card:                 #fffefd;
            --color-card-foreground:      #14233b;
            --color-border:               #d8e2ee;
            --color-muted:                rgba(47,103,177,0.07);
            --color-muted-foreground:     #54677a;
            --color-primary:              #2f67b1;
            --color-primary-foreground:   #ffffff;
            --color-secondary:            rgba(47,103,177,0.10);
            --color-secondary-foreground: #14233b;
            --color-accent:               #2f67b1;
            --color-accent-foreground:    #ffffff;
        }
        .night-mode {
            --nd-bg:      #0b1828;
            --nd-fg:      #e5ecf5;
            --nd-card:    #0f1e30;
            --nd-muted:   #8ca5bf;
            --nd-border:  #1e3048;
            --nd-ocean:   #4d8fd4;
            --nd-amber:   #f7ae52;
            --nd-night:   #d0e0f0;

            /* Aliases for dark mode */
            --bg:     #0b1828;
            --fg:     #e5ecf5;
            --card:   #0f1e30;
            --muted:  #8ca5bf;
            --border: #1e3048;

            /* Tailwind v4 semantic color tokens – dark */
            --color-background:           #0b1828;
            --color-foreground:           #e5ecf5;
            --color-card:                 #0f1e30;
            --color-card-foreground:      #e5ecf5;
            --color-border:               #1e3048;
            --color-muted:                rgba(77,143,212,0.08);
            --color-muted-foreground:     #8ca5bf;
            --color-primary:              #4d8fd4;
            --color-primary-foreground:   #ffffff;
            --color-secondary:            rgba(77,143,212,0.12);
            --color-secondary-foreground: #b8d4f0;
            --color-accent:               #4d8fd4;
            --color-accent-foreground:    #0b1828;
        }
        /* Utility card class for new-design */
        .nd-card {
            border-radius: 1rem;
            border: 1px solid var(--nd-border);
            background: var(--nd-card);
            box-shadow: 0 2px 8px -2px rgba(17,38,63,0.06);
        }
        body {
            background: radial-gradient(circle at top left,rgba(255,255,255,0.18),transparent 30%),
                        radial-gradient(circle at bottom right,rgba(16,44,82,0.12),transparent 26%),
                        linear-gradient(180deg,#5b7083 0%,#526678 100%);
            background-attachment: fixed;
            color: var(--nd-fg);
        }
    </style>
    @livewireStyles
</head>
<body class="min-h-screen font-body text-[var(--nd-fg)] antialiased bg-glow night-mode:[background:radial-gradient(circle_at_top_left,rgba(30,60,100,0.3),transparent_30%),radial-gradient(circle_at_bottom_right,rgba(8,20,40,0.3),transparent_26%),linear-gradient(180deg,#0d1e33_0%,#0b1828_100%)]">
<div class="mx-auto max-w-[1400px] px-4 py-6 sm:px-6 lg:px-8">
    <main class="overflow-hidden rounded-[28px] border border-white/60 bg-[var(--nd-card)] shadow-panel">

        {{-- Header --}}
        <header class="border-b border-[var(--nd-border)]/80 bg-[var(--nd-card)]/95 px-5 py-4 backdrop-blur sm:px-8">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">

                {{-- Logo --}}
                <div class="flex items-center gap-3">
                    <a href="{{ localized_route('home') }}" class="flex items-center gap-3" aria-label="{{ config('app.name', 'Gramlyze') }}">
                        <x-gramlyze-logo variant="compact" class="h-11 w-11" />
                        <span class="font-display text-[1.4rem] font-extrabold tracking-tight text-[var(--nd-night)]">{{ strtoupper(config('app.name', 'Gramlyze')) }}</span>
                    </a>
                    @if(config('app.is_beta'))
                        <span class="inline-flex items-center rounded-full bg-amber/20 px-2.5 py-0.5 text-xs font-semibold text-amber-800 ring-1 ring-inset ring-amber/30">{{ __('public.beta.badge') }}</span>
                    @endif
                </div>

                {{-- Desktop Nav --}}
                <nav class="hidden lg:flex flex-wrap items-center gap-x-6 gap-y-3 text-[15px] font-semibold text-[var(--nd-muted)]">
                    <a href="{{ localized_route('catalog.tests-cards') }}" class="transition hover:text-[var(--nd-ocean)]">{{ __('public.nav.catalog') }}</a>
                    <a href="{{ localized_route('theory.index') }}" class="transition hover:text-[var(--nd-ocean)]">{{ __('public.nav.theory') }}</a>
                    <a href="{{ localized_route('words.test') }}" class="transition hover:text-[var(--nd-ocean)]">{{ __('public.nav.words_test') }}</a>
                    <a href="{{ localized_route('verbs.test') }}" class="transition hover:text-[var(--nd-ocean)]">{{ __('public.nav.verbs_test') }}</a>
                </nav>

                {{-- Right Controls --}}
                <div class="flex items-center gap-3">
                    {{-- Desktop search --}}
                    <div x-data="searchBox()" class="hidden md:flex items-center">
                        <form @submit.prevent="go" class="relative">
                            <input x-model="query" @input="autocomplete" @keydown.escape="open=false"
                                   type="search" name="q"
                                   aria-label="{{ __('public.search.placeholder') }}"
                                   placeholder="{{ __('public.search.placeholder') }}"
                                   class="w-56 rounded-xl border border-[var(--nd-border)] bg-[var(--nd-card)]/90 px-4 py-2 text-sm shadow-sm focus:border-[var(--nd-ocean)] focus:outline-none focus:ring-2 focus:ring-ocean/20">
                            <div x-show="open" x-transition
                                 class="absolute left-0 right-0 mt-2 overflow-hidden rounded-2xl border border-[var(--nd-border)] bg-[var(--nd-card)] shadow-card"
                                 role="listbox">
                                <template x-if="!results.length">
                                    <div class="px-4 py-3 text-xs text-[var(--nd-muted)]">{{ __('public.search.nothing_found') }}</div>
                                </template>
                                <template x-for="item in results" :key="item.url">
                                    <a :href="item.url"
                                       class="flex items-start gap-3 px-4 py-3 text-sm hover:bg-mist"
                                       role="option">
                                        <span class="mt-1 inline-flex h-6 w-6 items-center justify-center rounded-full bg-ocean/10 text-[var(--nd-ocean)] text-xs" x-text="item.type === 'test' ? 'T' : 'P'"></span>
                                        <div>
                                            <p class="font-semibold" x-html="highlight(item.title)"></p>
                                            <p class="text-xs text-[var(--nd-muted)]" x-text="item.url"></p>
                                        </div>
                                    </a>
                                </template>
                            </div>
                        </form>
                    </div>

                    {{-- Language switcher --}}
                    <div x-data="languageSwitcher()" class="relative">
                        <button @click="toggle" :aria-expanded="open"
                                class="flex items-center gap-2 rounded-xl border border-[var(--nd-border)] bg-[var(--nd-card)] px-3 py-2 text-sm font-semibold shadow-sm hover:border-[var(--nd-ocean)] transition"
                                aria-haspopup="listbox">
                            <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-ocean/10 text-[var(--nd-ocean)] text-xs"
                                  x-text="active.code.toUpperCase()"></span>
                            <span class="hidden sm:inline" x-text="active.native_name || active.name"></span>
                            <svg class="h-4 w-4 text-[var(--nd-muted)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div x-show="open" @click.outside="open=false" x-transition
                             class="absolute right-0 mt-2 w-64 overflow-hidden rounded-2xl border border-[var(--nd-border)] bg-[var(--nd-card)] shadow-card">
                            <div class="p-3 border-b border-[var(--nd-border)]">
                                <input x-model="filter" type="search"
                                       placeholder="{{ __('public.language.search') }}"
                                       class="w-full rounded-xl border border-[var(--nd-border)] bg-mist/80 px-3 py-2 text-sm focus:border-[var(--nd-ocean)] focus:outline-none">
                            </div>
                            <div class="max-h-64 overflow-y-auto" role="listbox">
                                <template x-for="lang in filtered" :key="lang.code">
                                    <a :href="lang.url" @click="open=false"
                                       class="flex items-center justify-between px-4 py-3 text-sm hover:bg-mist"
                                       role="option">
                                        <div class="flex items-center gap-3">
                                            <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-ocean/10 text-[var(--nd-ocean)] text-xs"
                                                  x-text="lang.code.toUpperCase()"></span>
                                            <div>
                                                <p class="font-semibold" x-text="lang.native_name || lang.name"></p>
                                                <p class="text-xs text-[var(--nd-muted)]" x-text="lang.name"></p>
                                            </div>
                                        </div>
                                        <span x-show="lang.is_current" class="text-xs text-[var(--nd-ocean)]">{{ __('public.language.current') }}</span>
                                    </a>
                                </template>
                            </div>
                        </div>
                    </div>

                    {{-- Theme toggle --}}
                    <button @click="toggleTheme"
                            class="flex h-10 w-10 items-center justify-center rounded-xl border border-[var(--nd-border)] bg-[var(--nd-card)] shadow-sm hover:border-[var(--nd-ocean)] transition"
                            :aria-label="isDark ? '{{ __('public.theme.light') }}' : '{{ __('public.theme.dark') }}'">
                        <template x-if="isDark">
                            <svg class="h-5 w-5 text-[var(--nd-muted)]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/>
                            </svg>
                        </template>
                        <template x-if="!isDark">
                            <svg class="h-5 w-5 text-[var(--nd-muted)]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="5"/>
                                <path d="M12 1v2m0 18v2m11-11h-2M3 12H1m17.657-7.657l-1.414 1.414M6.757 17.243l-1.414 1.414m0-13.414l1.414 1.414M17.243 17.243l1.414 1.414"/>
                            </svg>
                        </template>
                    </button>

                    {{-- Mobile menu toggle --}}
                    <button @click="mobile = true"
                            class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-[var(--nd-border)] bg-[var(--nd-card)] shadow-sm lg:hidden"
                            aria-label="Open menu">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                </div>
            </div>

            {{-- Mobile menu --}}
            <div x-show="mobile" x-transition class="lg:hidden border-t border-[var(--nd-border)] mt-4 pt-4 space-y-4">
                <div class="flex flex-col gap-2 text-sm font-semibold text-[var(--nd-muted)]">
                    <a class="rounded-xl px-4 py-3 hover:bg-mist hover:text-[var(--nd-ocean)] transition" href="{{ localized_route('catalog.tests-cards') }}">{{ __('public.nav.catalog') }}</a>
                    <a class="rounded-xl px-4 py-3 hover:bg-mist hover:text-[var(--nd-ocean)] transition" href="{{ localized_route('theory.index') }}">{{ __('public.nav.theory') }}</a>
                    <a class="rounded-xl px-4 py-3 hover:bg-mist hover:text-[var(--nd-ocean)] transition" href="{{ localized_route('words.test') }}">{{ __('public.nav.words_test') }}</a>
                    <a class="rounded-xl px-4 py-3 hover:bg-mist hover:text-[var(--nd-ocean)] transition" href="{{ localized_route('verbs.test') }}">{{ __('public.nav.verbs_test') }}</a>
                </div>
                <div class="border-t border-[var(--nd-border)] pt-4 space-y-3">
                    <div x-data="searchBox()" class="w-full">
                        <form @submit.prevent="go" class="relative">
                            <input x-model="query" @input="autocomplete"
                                   type="search"
                                   placeholder="{{ __('public.search.placeholder') }}"
                                   class="w-full rounded-xl border border-[var(--nd-border)] bg-[var(--nd-card)] px-4 py-2 text-sm focus:border-[var(--nd-ocean)] focus:outline-none">
                            <div x-show="open" x-transition
                                 class="absolute left-0 right-0 mt-2 rounded-2xl border border-[var(--nd-border)] bg-[var(--nd-card)] shadow-card max-h-64 overflow-y-auto">
                                <template x-for="item in results" :key="item.url">
                                    <a :href="item.url" class="block px-4 py-3 text-sm hover:bg-mist">
                                        <p class="font-semibold" x-html="highlight(item.title)"></p>
                                        <p class="text-xs text-[var(--nd-muted)]" x-text="item.url"></p>
                                    </a>
                                </template>
                            </div>
                        </form>
                    </div>
                    <div class="flex items-center gap-3">
                        <button @click="toggleTheme"
                                class="h-10 w-10 rounded-xl border border-[var(--nd-border)] bg-[var(--nd-card)] flex items-center justify-center">
                            <span x-show="isDark">🌙</span>
                            <span x-show="!isDark">☀️</span>
                        </button>
                        <button @click="mobile=false"
                                class="h-10 w-10 rounded-xl border border-[var(--nd-border)] bg-[var(--nd-card)] flex items-center justify-center"
                                aria-label="Close menu">✕</button>
                    </div>
                </div>
            </div>
        </header>

        {{-- Main content --}}
        <main class="px-5 py-8 sm:px-8 lg:px-10">
            @yield('content')
        </main>

        {{-- Footer --}}
        <footer class="border-t border-[var(--nd-border)]/80 bg-[linear-gradient(180deg,#13233b_0%,#0f1b31_100%)] px-5 py-10 sm:px-8 lg:px-10">
            <div class="grid gap-8 md:grid-cols-3">
                <div class="space-y-3">
                    <div class="flex items-center gap-3">
                        <x-gramlyze-logo variant="compact" class="h-9 w-9" />
                        <span class="font-display text-lg font-extrabold tracking-tight text-white">{{ strtoupper(config('app.name', 'Gramlyze')) }}</span>
                    </div>
                    <p class="text-sm text-slate-400">{{ __('public.footer.description') }}</p>
                    <p class="text-xs text-slate-500">© <span x-text="new Date().getFullYear()"></span> {{ config('app.name', 'Gramlyze') }}</p>
                </div>
                <div class="space-y-2 text-sm">
                    <h3 class="font-semibold text-white">{{ __('public.footer.links') }}</h3>
                    <div class="flex flex-col gap-2 text-slate-400">
                        <a class="hover:text-[var(--nd-amber)] transition" href="{{ localized_route('catalog.tests-cards') }}">{{ __('public.nav.catalog') }}</a>
                        <a class="hover:text-[var(--nd-amber)] transition" href="{{ localized_route('theory.index') }}">{{ __('public.nav.theory') }}</a>
                        <a class="hover:text-[var(--nd-amber)] transition" href="{{ localized_route('words.test') }}">{{ __('public.nav.words_test') }}</a>
                        <a class="hover:text-[var(--nd-amber)] transition" href="{{ localized_route('verbs.test') }}">{{ __('public.nav.verbs_test') }}</a>
                    </div>
                </div>
                <div class="space-y-3 text-sm">
                    <h3 class="font-semibold text-white">{{ __('public.footer.contact') }}</h3>
                    <p class="text-slate-400">{{ __('public.footer.support') }}</p>
                    <button @click="toggleTheme"
                            class="inline-flex items-center gap-2 rounded-xl border border-slate-600 px-4 py-2 text-sm font-semibold text-slate-300 shadow-sm hover:border-[var(--nd-ocean)] transition">
                        <span x-show="!isDark">🌙</span><span x-show="isDark">☀️</span>
                        <span>{{ __('public.footer.theme') }}</span>
                    </button>
                </div>
            </div>
        </footer>

    </main>
</div>

<script>
    function themeController() {
        return {
            isDark: false,
            mobile: false,
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
                const safe = (text || '').replace(/[&<>"']/g, (c) => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
                if (!this.query) return safe;
                const regex = new RegExp('(' + this.query.replace(/[-/\\^$*+?.()|[\]{}]/g, '\\$&') + ')', 'ig');
                return safe.replace(regex, '<mark class="bg-amber/40 text-night">$1</mark>');
            },
            async autocomplete() {
                if (this.query.trim().length < 2) { this.results = []; this.open = false; return; }
                const res = await fetch('{{ localized_route('site.search') }}?q=' + encodeURIComponent(this.query.trim()), { headers: { 'Accept': 'application/json' }});
                this.results = await res.json();
                this.open = true;
            }
        }
    }
    function languageSwitcher() {
        return {
            open: false,
            filter: '',
            languages: @json($__languageSwitcher ?? []),
            get active() { return this.languages.find(l => l.is_current) || this.languages[0] || { code: '{{ app()->getLocale() }}', name: '{{ app()->getLocale() }}' }; },
            toggle() { this.open = !this.open; },
            get filtered() {
                const term = this.filter.toLowerCase();
                return this.languages.filter(l => !term || (l.name && l.name.toLowerCase().includes(term)) || (l.native_name && l.native_name.toLowerCase().includes(term)) || l.code.toLowerCase().includes(term));
            }
        }
    }
</script>
@livewireScripts
@yield('scripts')
</body>
</html>
