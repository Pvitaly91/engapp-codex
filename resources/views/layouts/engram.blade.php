<!doctype html>
<html lang="{{ app()->getLocale() }}" class="h-full" x-data="themeController()" x-init="init()" x-bind:class="{ 'dark': isDark }">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>@yield('title', __('public.meta.title'))</title>
    <meta name="description" content="{{ __('public.meta.description') }}" />
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
                    },
                    boxShadow: {
                        card: '0 20px 50px -22px rgba(0,0,0,0.35)',
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
    @livewireStyles
</head>
<body class="min-h-full font-sans antialiased bg-[var(--bg)] text-[var(--fg)]">
    <div class="absolute inset-0 pointer-events-none bg-[radial-gradient(circle_at_20%_0%,rgba(255,255,255,0.6),transparent_20%),radial-gradient(circle_at_90%_10%,rgba(90,107,255,0.12),transparent_20%)]"></div>
    <div class="relative">
        <header class="sticky top-0 z-40 border-b border-[var(--border)]/80 backdrop-blur bg-[color-mix(in_srgb,var(--card)_90%,transparent)]">
            <div class="mx-auto max-w-6xl px-4">
                <div class="flex items-center justify-between gap-4 py-4">
                    <a href="{{ localized_route('home') }}" class="flex items-center gap-3" aria-label="Gramlyze">
                        <x-gramlyze-logo variant="horizontal" class="h-10 w-auto" />
                    </a>
                    <nav class="hidden lg:flex items-center gap-8 text-sm font-semibold">
                        <a class="hover:text-brand-600 transition" href="{{ localized_route('catalog.tests-cards') }}">{{ __('public.nav.catalog') }}</a>
                        <a class="hover:text-brand-600 transition" href="{{ localized_route('theory.index') }}">{{ __('public.nav.theory') }}</a>
                        <a class="hover:text-brand-600 transition" href="{{ localized_route('words.test') }}">{{ __('public.nav.words_test') }}</a>
                        <a class="hover:text-brand-600 transition" href="{{ localized_route('verbs.test') }}">{{ __('public.nav.verbs_test') }}</a>
                    </nav>
                    <div class="flex items-center gap-3">
                        <div x-data="searchBox()" class="hidden md:flex items-center gap-2">
                            <form @submit.prevent="go" class="relative">
                                <input x-model="query" @input="autocomplete" @keydown.escape="open=false" type="search" name="q" aria-label="{{ __('public.search.placeholder') }}" placeholder="{{ __('public.search.placeholder') }}" class="w-60 rounded-full border border-[var(--border)] bg-[var(--card)]/90 px-4 py-2 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100">
                                <div x-show="open" x-transition class="absolute left-0 right-0 mt-2 overflow-hidden rounded-2xl border border-[var(--border)] bg-[var(--card)] shadow-card" role="listbox">
                                    <template x-if="!results.length">
                                        <div class="px-4 py-3 text-xs text-[var(--muted)]">{{ __('public.search.nothing_found') }}</div>
                                    </template>
                                    <template x-for="item in results" :key="item.url">
                                        <a :href="item.url" class="flex items-start gap-3 px-4 py-3 text-sm hover:bg-brand-50/70 dark:hover:bg-slate-800" role="option">
                                            <span class="mt-1 inline-flex h-6 w-6 items-center justify-center rounded-full bg-brand-100 text-brand-600" x-text="item.type === 'test' ? 'T' : 'P'"></span>
                                            <div>
                                                <p class="font-semibold" x-html="highlight(item.title)"></p>
                                                <p class="text-xs text-[var(--muted)]" x-text="item.url"></p>
                                            </div>
                                        </a>
                                    </template>
                                </div>
                            </form>
                        </div>
                        <div x-data="languageSwitcher()" class="relative">
                            <button @click="toggle" :aria-expanded="open" class="flex items-center gap-2 rounded-full border border-[var(--border)] bg-[var(--card)] px-3 py-2 text-sm font-semibold shadow-sm hover:border-brand-500" aria-haspopup="listbox">
                                <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-brand-100 text-brand-600 text-xs" x-text="active.code.toUpperCase()"></span>
                                <span class="hidden sm:inline" x-text="active.native_name || active.name"></span>
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                            <div x-show="open" @click.outside="open=false" x-transition class="absolute right-0 mt-2 w-72 overflow-hidden rounded-2xl border border-[var(--border)] bg-[var(--card)] shadow-card">
                                <div class="p-3 border-b border-[var(--border)]">
                                    <label class="sr-only" for="lang-search">{{ __('public.language.search') }}</label>
                                    <input id="lang-search" x-model="filter" type="search" placeholder="{{ __('public.language.search') }}" class="w-full rounded-xl border border-[var(--border)] bg-white/70 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-100">
                                </div>
                                <div class="max-h-64 overflow-y-auto" role="listbox">
                                    <template x-for="lang in filtered" :key="lang.code">
                                        <a :href="lang.url" @click="open=false" class="flex items-center justify-between px-4 py-3 text-sm hover:bg-brand-50/80 dark:hover:bg-slate-800" role="option">
                                            <div class="flex items-center gap-3">
                                                <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-brand-100 text-brand-600 text-xs" x-text="lang.code.toUpperCase()"></span>
                                                <div>
                                                    <p class="font-semibold" x-text="lang.native_name || lang.name"></p>
                                                    <p class="text-xs text-[var(--muted)]" x-text="lang.name"></p>
                                                </div>
                                            </div>
                                            <span x-show="lang.is_current" class="text-xs text-brand-600">{{ __('public.language.current') }}</span>
                                        </a>
                                    </template>
                                </div>
                            </div>
                        </div>
                        <button @click="toggleTheme" class="flex h-10 w-10 items-center justify-center rounded-full border border-[var(--border)] bg-[var(--card)] shadow-sm" :aria-label="isDark ? '{{ __('public.theme.light') }}' : '{{ __('public.theme.dark') }}'">
                            <template x-if="isDark">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/></svg>
                            </template>
                            <template x-if="!isDark">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"/><path d="M12 1v2m0 18v2m11-11h-2M3 12H1m17.657-7.657l-1.414 1.414M6.757 17.243l-1.414 1.414m0-13.414l1.414 1.414M17.243 17.243l1.414 1.414"/></svg>
                            </template>
                        </button>
                        <button @click="mobile = true" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-[var(--border)] bg-[var(--card)] shadow-sm lg:hidden" aria-label="Open menu">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                        </button>
                    </div>
                </div>
            </div>
            <div x-show="mobile" x-transition class="lg:hidden border-t border-[var(--border)] bg-[var(--card)]/95 shadow-lg">
                <div class="mx-auto max-w-6xl px-4 py-4 space-y-4">
                    <div class="flex flex-col gap-3 text-sm font-semibold">
                        <a class="rounded-xl px-4 py-3 hover:bg-brand-50/80" href="{{ localized_route('catalog.tests-cards') }}">{{ __('public.nav.catalog') }}</a>
                        <a class="rounded-xl px-4 py-3 hover:bg-brand-50/80" href="{{ localized_route('theory.index') }}">{{ __('public.nav.theory') }}</a>
                        <a class="rounded-xl px-4 py-3 hover:bg-brand-50/80" href="{{ localized_route('words.test') }}">{{ __('public.nav.words_test') }}</a>
                        <a class="rounded-xl px-4 py-3 hover:bg-brand-50/80" href="{{ localized_route('verbs.test') }}">{{ __('public.nav.verbs_test') }}</a>
                    </div>
                    <div class="border-t border-[var(--border)] pt-4 space-y-3">
                        <div x-data="searchBox()" class="w-full">
                            <form @submit.prevent="go" class="relative">
                                <input x-model="query" @input="autocomplete" type="search" placeholder="{{ __('public.search.placeholder') }}" class="w-full rounded-xl border border-[var(--border)] bg-[var(--card)] px-4 py-2 text-sm focus:border-brand-500 focus:outline-none">
                                <div x-show="open" x-transition class="absolute left-0 right-0 mt-2 rounded-2xl border border-[var(--border)] bg-[var(--card)] shadow-card max-h-64 overflow-y-auto">
                                    <template x-for="item in results" :key="item.url">
                                        <a :href="item.url" class="block px-4 py-3 text-sm hover:bg-brand-50/80">
                                            <p class="font-semibold" x-html="highlight(item.title)"></p>
                                            <p class="text-xs text-[var(--muted)]" x-text="item.url"></p>
                                        </a>
                                    </template>
                                </div>
                            </form>
                        </div>
                        <div class="flex items-center gap-3">
                            <div x-data="languageSwitcher()" class="flex-1 relative">
                                <button @click="toggle" class="w-full rounded-xl border border-[var(--border)] bg-[var(--card)] px-4 py-2 text-sm font-semibold flex items-center justify-between">
                                    <span x-text="active.native_name || active.name"></span>
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </button>
                                <div x-show="open" @click.outside="open=false" class="absolute left-0 right-0 mt-2 rounded-2xl border border-[var(--border)] bg-[var(--card)] shadow-card max-h-56 overflow-y-auto">
                                    <template x-for="lang in filtered" :key="lang.code">
                                        <a :href="lang.url" class="block px-4 py-3 text-sm hover:bg-brand-50/80" x-text="lang.native_name || lang.name"></a>
                                    </template>
                                </div>
                            </div>
                            <button @click="toggleTheme" class="h-10 w-10 rounded-xl border border-[var(--border)] bg-[var(--card)] flex items-center justify-center">
                                <span x-show="isDark">🌙</span>
                                <span x-show="!isDark">☀️</span>
                            </button>
                            <button @click="mobile=false" class="h-10 w-10 rounded-xl border border-[var(--border)] bg-[var(--card)] flex items-center justify-center" aria-label="Close menu">✕</button>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <main class="mx-auto max-w-6xl px-4 py-10 md:py-14">
            @yield('content')
        </main>

        <footer class="border-t border-[var(--border)]/80 bg-[color-mix(in_srgb,var(--card)_95%,transparent)]">
            <div class="mx-auto max-w-6xl px-4 py-10 grid gap-8 md:grid-cols-3">
                <div class="space-y-3">
                    <x-gramlyze-logo variant="horizontal" class="h-8 w-auto" />
                    <p class="text-sm text-[var(--muted)]">{{ __('public.footer.description') }}</p>
                    <p class="text-xs text-[var(--muted)]">© <span x-text="new Date().getFullYear()"></span> Gramlyze</p>
                </div>
                <div class="space-y-2 text-sm">
                    <h3 class="font-semibold">{{ __('public.footer.links') }}</h3>
                    <div class="flex flex-col gap-2 text-[var(--muted)]">
                        <a class="hover:text-brand-600" href="{{ localized_route('catalog.tests-cards') }}">{{ __('public.nav.catalog') }}</a>
                        <a class="hover:text-brand-600" href="{{ localized_route('theory.index') }}">{{ __('public.nav.theory') }}</a>
                        <a class="hover:text-brand-600" href="{{ localized_route('words.test') }}">{{ __('public.nav.words_test') }}</a>
                        <a class="hover:text-brand-600" href="{{ localized_route('verbs.test') }}">{{ __('public.nav.verbs_test') }}</a>
                    </div>
                </div>
                <div class="space-y-3 text-sm">
                    <h3 class="font-semibold">{{ __('public.footer.contact') }}</h3>
                    <p class="text-[var(--muted)]">{{ __('public.footer.support') }}</p>
                    <div class="flex items-center gap-3">
                        <button @click="toggleTheme" class="inline-flex items-center gap-2 rounded-full border border-[var(--border)] px-4 py-2 text-sm font-semibold shadow-sm">
                            <span x-show="!isDark">🌙</span><span x-show="isDark">☀️</span>
                            <span>{{ __('public.footer.theme') }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </footer>
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
                    const safe = (text || '').replace(/[&<>"]/g, (c) => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c]));
                    if (!this.query) return safe;
                    const regex = new RegExp('(' + this.query.replace(/[-/\\^$*+?.()|[\]{}]/g, '\\$&') + ')', 'ig');
                    return safe.replace(regex, '<mark class="bg-yellow-200 text-black">$1</mark>');
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
