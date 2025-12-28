<!doctype html>
<html lang="{{ app()->getLocale() }}" class="h-full">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>@yield('title', __('public.meta.title'))</title>
    <meta name="description" content="{{ __('public.meta.description') }}" />

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'ui-sans-serif', 'system-ui'] },
                    colors: {
                        night: '#0f172a',
                        ink: '#111827',
                        cloud: '#f8fafc',
                        stone: '#94a3b8',
                        mint: '#14b8a6',
                        lilac: '#6366f1',
                        amber: '#f59e0b',
                        background: '#0b1221',
                        foreground: '#e2e8f0',
                        border: '#1e293b',
                        input: '#1e293b',
                        ring: '#6366f1',
                        primary: { DEFAULT: '#6366f1', foreground: '#0b1221' },
                        secondary: { DEFAULT: '#14b8a6', foreground: '#0b1221' },
                        muted: { DEFAULT: '#111827', foreground: '#cbd5e1' },
                        accent: { DEFAULT: '#f59e0b', foreground: '#0b1221' },
                        destructive: { DEFAULT: '#ef4444', foreground: '#0b1221' },
                        card: { DEFAULT: '#0f172a', foreground: '#e2e8f0' },
                        popover: { DEFAULT: '#0f172a', foreground: '#e2e8f0' },
                        success: '#22c55e',
                        warning: '#f59e0b',
                        info: '#38bdf8',
                    },
                    boxShadow: {
                        lifted: '0 10px 40px -16px rgba(15,23,42,0.35)',
                        soft: '0 12px 30px -18px rgba(15,23,42,0.25)',
                    }
                }
            }
        }
    </script>
    <style>
        :root {
            --bg: #0b1221;
            --panel: #0f172a;
            --muted: #1e293b;
            --stroke: rgba(255,255,255,0.08);
            --card: #0b1221;
        }
        body { background: radial-gradient(circle at 10% 20%, rgba(99,102,241,0.12), transparent 30%), radial-gradient(circle at 85% 10%, rgba(20,184,166,0.12), transparent 25%), var(--bg); color: #e2e8f0; min-height: 100%; }
        .frosted { background-color: rgba(15, 23, 42, 0.7); backdrop-filter: blur(12px); border: 1px solid var(--stroke); }
        .glass-card { background: linear-gradient(145deg, rgba(15,23,42,0.78), rgba(15,23,42,0.92)); border: 1px solid var(--stroke); }
        .pill { border: 1px solid var(--stroke); background: rgba(255,255,255,0.03); }
        .input-base { border: 1px solid var(--stroke); background: rgba(255,255,255,0.04); color: #e2e8f0; }
        .input-base:focus-visible { outline: 2px solid rgba(99,102,241,0.5); border-color: rgba(99,102,241,0.6); }
    </style>
    @livewireStyles
</head>
<body class="font-sans antialiased min-h-full">
    <div class="absolute inset-0 pointer-events-none" aria-hidden="true">
        <div class="absolute inset-0" style="background: radial-gradient(650px circle at 20% 20%, rgba(99,102,241,0.07), transparent 35%), radial-gradient(700px circle at 80% 10%, rgba(20,184,166,0.08), transparent 40%), radial-gradient(550px circle at 50% 90%, rgba(245,158,11,0.07), transparent 30%);"></div>
    </div>

    <div x-data="publicShell()" class="relative flex min-h-screen flex-col">
        <header class="sticky top-0 z-40 border-b border-white/5 bg-gradient-to-r from-[#0c1122]/95 via-[#0f162c]/95 to-[#0c1122]/95 backdrop-blur">
            <div class="mx-auto flex w-full max-w-6xl items-center justify-between gap-3 px-4 py-4 md:py-5">
                <div class="flex items-center gap-3">
                    <a href="{{ route('home') }}" class="flex items-center gap-2 rounded-xl px-2 text-lg font-semibold tracking-tight text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-lilac">
                        <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-gradient-to-br from-lilac to-mint text-base font-bold text-white shadow-soft">G</span>
                        <span class="hidden sm:inline">Gramlyze</span>
                    </a>
                    <div class="hidden items-center gap-2 rounded-full bg-white/5 px-3 py-1 text-xs font-medium text-slate-300 sm:flex">
                        <span class="h-2 w-2 rounded-full bg-mint"></span>
                        <span>{{ __('public.nav.catalog') }}</span>
                    </div>
                </div>

                <div class="hidden items-center gap-2 lg:flex" role="navigation">
                    <a href="{{ route('catalog.tests-cards') }}" class="rounded-full px-3 py-2 text-sm font-semibold text-slate-200 transition hover:bg-white/5 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-lilac">{{ __('public.nav.catalog') }}</a>
                    <a href="{{ route('theory.index') }}" class="rounded-full px-3 py-2 text-sm font-semibold text-slate-200 transition hover:bg-white/5 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-lilac">{{ __('public.nav.theory') }}</a>
                    <a href="{{ route('words.test') }}" class="rounded-full px-3 py-2 text-sm font-semibold text-slate-200 transition hover:bg-white/5 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-lilac">{{ __('words_test.title') }}</a>
                    <a href="{{ route('verbs.test') }}" class="rounded-full px-3 py-2 text-sm font-semibold text-slate-200 transition hover:bg-white/5 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-lilac">{{ __('verbs_test.title') }}</a>
                </div>

                <div class="flex items-center gap-2">
                    <div class="hidden md:flex">
                        <form @submit="closeSuggestion" action="{{ route('site.search') }}" method="GET" class="relative">
                            <label for="header-search" class="sr-only">{{ __('public.search.placeholder') }}</label>
                            <div class="relative">
                                <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">🔎</span>
                                <input x-model="pageQuery" @input.debounce.250="fetchSuggestions" @focus="openSuggestion" @keydown.down.prevent="highlight(1)" @keydown.up.prevent="highlight(-1)" @keydown.enter.prevent="maybeSubmit($event)" id="header-search" type="search" name="q" autocomplete="off" class="input-base w-60 rounded-2xl pl-9 pr-3 py-2 text-sm placeholder:text-slate-400" placeholder="{{ __('public.search.placeholder') }}" />
                            </div>
                            <div x-show="suggestionOpen" @mouseleave="activeIndex=-1" class="absolute left-0 right-0 mt-2 max-h-72 overflow-auto rounded-2xl bg-[#0b1221] shadow-lifted border border-white/5" x-cloak>
                                <template x-if="suggestions.length === 0">
                                    <div class="px-4 py-3 text-sm text-slate-400">{{ __('public.search.nothing_found') }}</div>
                                </template>
                                <template x-for="(item, idx) in suggestions" :key="idx">
                                    <a :href="item.url" :class="{'bg-white/5 text-white': activeIndex === idx}" class="flex items-center justify-between px-4 py-3 text-sm text-slate-200 transition hover:bg-white/5" role="option" tabindex="0" @mouseenter="activeIndex=idx" @click="suggestionOpen=false">
                                        <div class="flex flex-col">
                                            <span x-text="item.title"></span>
                                            <span class="text-xs text-slate-400" x-text="item.type === 'page' ? '{{ __('public.common.type_theory') }}' : '{{ __('public.common.type_test') }}'"></span>
                                        </div>
                                        <span class="rounded-full bg-white/5 px-2.5 py-1 text-[11px] uppercase tracking-wide" x-text="item.type"></span>
                                    </a>
                                </template>
                            </div>
                        </form>
                    </div>

                    <div class="relative">
                        <button @click="toggleDictionary" :aria-expanded="dictOpen.toString()" class="pill inline-flex items-center gap-2 rounded-full px-3 py-2 text-sm font-semibold text-white transition hover:bg-white/10 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-mint">
                            📖 <span class="hidden sm:inline">{{ __('public.search.dictionary') }}</span>
                        </button>
                        <div x-show="dictOpen" x-transition.origin.top.right @click.away="dictOpen=false" class="absolute right-0 mt-3 w-80 rounded-2xl bg-[#0b1221] p-4 shadow-lifted border border-white/5" role="dialog" aria-label="Dictionary search" x-cloak>
                            <div class="flex items-center justify-between gap-2">
                                <p class="text-sm font-semibold text-white">{{ __('public.search.dictionary_title') }}</p>
                                <span class="rounded-full bg-white/5 px-2 py-1 text-[11px] uppercase text-slate-300">{{ strtoupper($__currentLocale ?? app()->getLocale()) }}</span>
                            </div>
                            <div class="mt-3">
                                <label for="dict-input" class="sr-only">{{ __('public.search.dictionary_placeholder') }}</label>
                                <input id="dict-input" type="search" x-model="dictQuery" @input.debounce.300="fetchDictionary" class="input-base w-full rounded-xl px-3 py-2 text-sm" placeholder="{{ __('public.search.dictionary_placeholder') }}" />
                            </div>
                            <div class="mt-3 max-h-60 overflow-auto rounded-xl border border-white/5">
                                <template x-if="dictLoading">
                                    <div class="px-4 py-3 text-sm text-slate-300">{{ __('public.common.loading') ?? 'Loading…' }}</div>
                                </template>
                                <template x-if="!dictLoading && dictResults.length === 0 && dictQuery.length">
                                    <div class="px-4 py-3 text-sm text-slate-400">{{ __('public.search.no_translation') }}</div>
                                </template>
                                <template x-for="item in dictResults" :key="item.en">
                                    <div class="flex items-start justify-between gap-3 border-b border-white/5 px-4 py-3 last:border-b-0">
                                        <div>
                                            <p class="font-semibold text-white" x-text="item.en"></p>
                                            <p class="text-sm text-slate-300" x-text="item.translation || '{{ __('public.search.no_translation') }}'"></p>
                                        </div>
                                        <span class="rounded-full bg-white/5 px-2 py-1 text-[11px] uppercase text-slate-400">{{ __('public.search.word') }}</span>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    <div class="relative">
                        <button @click="toggleLang" :aria-expanded="langOpen.toString()" class="pill flex items-center gap-2 rounded-full px-3 py-2 text-sm font-semibold text-white transition hover:bg-white/10 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-lilac">
                            🌐 <span class="hidden sm:inline" x-text="currentLocaleLabel"></span>
                        </button>
                        <div x-show="langOpen" x-transition.origin.top.right @click.away="langOpen=false" class="absolute right-0 mt-3 w-72 rounded-2xl bg-[#0b1221] p-4 shadow-lifted border border-white/5" role="listbox" aria-label="Language switcher" x-cloak>
                            <div class="flex items-center gap-2 rounded-xl bg-white/5 px-3 py-2 text-sm">
                                <span class="text-slate-300">🔍</span>
                                <input type="search" x-model="langFilter" class="bg-transparent text-slate-100 placeholder:text-slate-400 focus:outline-none w-full" placeholder="{{ __('public.search.filter_lang') }}" />
                            </div>
                            <div class="mt-3 max-h-64 overflow-auto rounded-xl border border-white/5">
                                <template x-for="lang in filteredLanguages" :key="lang.code">
                                    <a :href="lang.url" class="flex items-center justify-between px-4 py-3 text-sm text-slate-200 transition hover:bg-white/5 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-lilac" role="option" :aria-selected="lang.is_current">
                                        <div class="flex flex-col">
                                            <span class="font-semibold" x-text="lang.name"></span>
                                            <span class="text-xs text-slate-400" x-text="lang.code.toUpperCase()"></span>
                                        </div>
                                        <span class="rounded-full px-2 py-1 text-[11px] uppercase" :class="lang.is_current ? 'bg-lilac/20 text-lilac' : 'bg-white/5 text-slate-300'">{{ __('public.search.current') }}</span>
                                    </a>
                                </template>
                            </div>
                        </div>
                    </div>

                    <button @click="mobileOpen = !mobileOpen" class="lg:hidden inline-flex h-10 w-10 items-center justify-center rounded-xl border border-white/10 text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-lilac" :aria-expanded="mobileOpen.toString()" aria-controls="mobile-nav">
                        <span class="sr-only">{{ __('public.nav.menu') }}</span>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                        </svg>
                    </button>
                </div>
            </div>

            <div x-show="mobileOpen" x-transition class="lg:hidden border-t border-white/5 bg-[#0b1221] px-4 py-4" id="mobile-nav" x-cloak>
                <div class="space-y-3">
                    <form action="{{ route('site.search') }}" method="GET" class="relative">
                        <label for="mobile-search" class="sr-only">{{ __('public.search.placeholder') }}</label>
                        <input id="mobile-search" type="search" name="q" class="input-base w-full rounded-2xl px-3 py-2 text-sm" placeholder="{{ __('public.search.placeholder') }}">
                    </form>
                    <div class="grid grid-cols-2 gap-2 text-sm font-semibold text-slate-200">
                        <a href="{{ route('catalog.tests-cards') }}" class="pill rounded-xl px-3 py-2 text-center transition hover:bg-white/10">{{ __('public.nav.catalog') }}</a>
                        <a href="{{ route('theory.index') }}" class="pill rounded-xl px-3 py-2 text-center transition hover:bg-white/10">{{ __('public.nav.theory') }}</a>
                        <a href="{{ route('words.test') }}" class="pill rounded-xl px-3 py-2 text-center transition hover:bg-white/10">{{ __('words_test.title') }}</a>
                        <a href="{{ route('verbs.test') }}" class="pill rounded-xl px-3 py-2 text-center transition hover:bg-white/10">{{ __('verbs_test.title') }}</a>
                    </div>
                </div>
            </div>
        </header>

        <main class="mx-auto flex w-full max-w-6xl flex-1 flex-col gap-8 px-4 py-8 md:py-10">
            @yield('content')
        </main>

        <footer class="mt-auto border-t border-white/5 bg-[#0b1221]/90 backdrop-blur">
            <div class="mx-auto grid w-full max-w-6xl gap-8 px-4 py-10 md:grid-cols-[1.3fr_1fr] md:py-12">
                <div class="space-y-3">
                    <div class="flex items-center gap-2 text-white">
                        <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-gradient-to-br from-lilac to-mint text-base font-bold shadow-soft">G</span>
                        <div>
                            <p class="text-lg font-semibold">Gramlyze</p>
                            <p class="text-sm text-slate-400">{{ __('public.footer.description') }}</p>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-2 text-xs text-slate-300">
                        <span class="pill rounded-full px-3 py-1">{{ __('public.footer.data_security') }}</span>
                        <span class="pill rounded-full px-3 py-1">{{ __('public.footer.team_support') }}</span>
                        <span class="pill rounded-full px-3 py-1">{{ __('public.footer.quick_start') }}</span>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4 text-sm text-slate-200">
                    <div class="space-y-2">
                        <p class="text-xs uppercase tracking-[0.08em] text-slate-400">{{ __('public.nav.catalog') }}</p>
                        <a href="{{ route('catalog.tests-cards') }}" class="block rounded-lg px-2 py-1 transition hover:bg-white/5">{{ __('public.search.to_catalog') }}</a>
                        <a href="{{ route('theory.index') }}" class="block rounded-lg px-2 py-1 transition hover:bg-white/5">{{ __('public.nav.theory') }}</a>
                        <a href="{{ route('words.test') }}" class="block rounded-lg px-2 py-1 transition hover:bg-white/5">{{ __('words_test.title') }}</a>
                        <a href="{{ route('verbs.test') }}" class="block rounded-lg px-2 py-1 transition hover:bg-white/5">{{ __('verbs_test.title') }}</a>
                    </div>
                    <div class="space-y-2">
                        <p class="text-xs uppercase tracking-[0.08em] text-slate-400">{{ __('public.footer.support') }}</p>
                        <p class="text-slate-300">{{ __('public.footer.data_security') }}</p>
                        <p class="text-slate-300">{{ __('public.footer.team_support') }}</p>
                        <p class="text-slate-300">{{ __('public.footer.quick_start') }}</p>
                    </div>
                </div>
            </div>
            <div class="border-t border-white/5 bg-[#0b1221]/95 py-4 text-center text-xs text-slate-500">© {{ now()->year }} Gramlyze — {{ __('public.nav.catalog') }}</div>
        </footer>
    </div>

    @livewireScripts
    <script>
        function publicShell() {
            return {
                mobileOpen: false,
                dictOpen: false,
                langOpen: false,
                pageQuery: '',
                suggestions: [],
                suggestionOpen: false,
                activeIndex: -1,
                dictQuery: '',
                dictResults: [],
                dictLoading: false,
                langFilter: '',
                languages: @json($__languageSwitcher ?? []),
                get currentLocaleLabel() {
                    const active = this.languages.find(l => l.is_current);
                    if (!active) return (this.languages[0]?.code || '{{ app()->getLocale() }}').toUpperCase();
                    return `${active.code.toUpperCase()} · ${active.name}`;
                },
                get filteredLanguages() {
                    const filter = this.langFilter.toLowerCase();
                    return this.languages.filter(l => !filter || l.name.toLowerCase().includes(filter) || l.code.toLowerCase().includes(filter));
                },
                toggleLang() { this.langOpen = !this.langOpen; },
                toggleDictionary() { this.dictOpen = !this.dictOpen; },
                openSuggestion() { this.suggestionOpen = true; },
                closeSuggestion() { this.suggestionOpen = false; this.activeIndex = -1; },
                highlight(delta) {
                    if (!this.suggestionOpen || this.suggestions.length === 0) return;
                    this.activeIndex = (this.activeIndex + delta + this.suggestions.length) % this.suggestions.length;
                },
                maybeSubmit(event) {
                    if (!this.suggestionOpen || this.activeIndex === -1) return event.target.form.submit();
                    event.preventDefault();
                    const target = this.suggestions[this.activeIndex];
                    if (target) window.location.href = target.url;
                },
                async fetchSuggestions() {
                    this.suggestionOpen = true;
                    const q = this.pageQuery.trim();
                    if (!q) { this.suggestions = []; return; }
                    try {
                        const response = await fetch(`{{ route('site.search') }}?q=${encodeURIComponent(q)}`, { headers: { 'Accept': 'application/json' } });
                        if (response.ok) {
                            this.suggestions = await response.json();
                        }
                    } catch (e) { console.warn(e); }
                },
                async fetchDictionary() {
                    const q = this.dictQuery.trim();
                    if (!q) { this.dictResults = []; return; }
                    this.dictLoading = true;
                    try {
                        const url = `/api/search?lang={{ $__currentLocale ?? app()->getLocale() }}&q=${encodeURIComponent(q)}`;
                        const res = await fetch(url);
                        if (res.ok) {
                            const data = await res.json();
                            this.dictResults = data;
                        }
                    } catch (e) { console.warn(e); }
                    this.dictLoading = false;
                },
            };
        }
    </script>
</body>
</html>
