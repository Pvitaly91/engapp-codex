<!doctype html>
<html lang="{{ app()->getLocale() }}" class="h-full" x-data="themeController()" x-init="init()" :class="{ 'dark': isDark }">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>@yield('title', __('public.meta.title'))</title>
    <meta name="description" content="{{ __('public.meta.description') }}" />
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">

    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="//unpkg.com/alpinejs" defer></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: { sans: ['Manrope', 'ui-sans-serif', 'system-ui', 'sans-serif'] },
                    colors: {
                        brand: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            200: '#bfdbfe',
                            400: '#60a5fa',
                            500: '#3b82f6',
                            600: '#2563eb',
                            800: '#1e3a8a',
                            900: '#0f172a',
                        }
                    },
                    boxShadow: {
                        glow: '0 20px 80px -30px rgba(37, 99, 235, 0.55)',
                        soft: '0 10px 30px -16px rgba(15, 23, 42, 0.35)',
                    }
                }
            }
        }
    </script>
    <style>
        :root {
            --bg: #f5f7ff;
            --surface: #ffffff;
            --surface-2: #eef2ff;
            --text: #0f172a;
            --muted: #475569;
            --line: #dbe3f3;
        }

        .dark {
            --bg: #020617;
            --surface: #0f172a;
            --surface-2: #111c34;
            --text: #e2e8f0;
            --muted: #94a3b8;
            --line: #23314f;
        }

        body {
            background:
                radial-gradient(circle at 0% 10%, rgba(59,130,246,0.22), transparent 30%),
                radial-gradient(circle at 100% 0%, rgba(14,165,233,0.16), transparent 24%),
                var(--bg);
            color: var(--text);
        }
    </style>
    @livewireStyles
</head>
<body class="min-h-full font-sans antialiased">
<div class="relative min-h-screen">
    <div class="pointer-events-none absolute -top-10 left-1/2 h-72 w-72 -translate-x-1/2 rounded-full bg-brand-400/25 blur-3xl"></div>

    <header class="sticky top-0 z-40 border-b border-[var(--line)]/80 bg-[color-mix(in_srgb,var(--surface)_86%,transparent)] backdrop-blur-xl">
        <div class="mx-auto max-w-7xl px-4 py-4 lg:px-8" x-data="{ mobile: false }">
            <div class="flex items-center justify-between gap-3">
                <a href="{{ localized_route('home') }}" class="flex items-center gap-3" aria-label="Gramlyze">
                    <x-gramlyze-logo variant="horizontal" class="h-10 w-auto" />
                    <span class="hidden rounded-full bg-brand-100 px-3 py-1 text-xs font-bold text-brand-700 lg:inline">GPT</span>
                </a>

                <nav class="hidden items-center gap-6 text-sm font-semibold lg:flex">
                    <a class="hover:text-brand-600" href="{{ localized_route('catalog.tests-cards') }}">{{ __('public.nav.catalog') }}</a>
                    <a class="hover:text-brand-600" href="{{ localized_route('theory.index') }}">{{ __('public.nav.theory') }}</a>
                    <a class="hover:text-brand-600" href="{{ localized_route('words.test') }}">{{ __('public.nav.words_test') }}</a>
                    <a class="hover:text-brand-600" href="{{ localized_route('verbs.test') }}">{{ __('public.nav.verbs_test') }}</a>
                </nav>

                <div class="hidden items-center gap-2 lg:flex">
                    <div x-data="searchBox()" class="relative">
                        <form @submit.prevent="go">
                            <input x-model="query" @input="autocomplete" type="search" placeholder="{{ __('public.search.placeholder') }}" class="w-64 rounded-2xl border border-[var(--line)] bg-[var(--surface)] px-4 py-2 text-sm focus:border-brand-500 focus:outline-none">
                        </form>
                        <div x-show="open" x-transition class="absolute left-0 right-0 z-20 mt-2 overflow-hidden rounded-2xl border border-[var(--line)] bg-[var(--surface)] shadow-soft">
                            <template x-for="item in results" :key="item.url">
                                <a :href="item.url" class="block px-4 py-3 text-sm hover:bg-brand-50/60 dark:hover:bg-slate-800/80">
                                    <p class="font-semibold" x-html="highlight(item.title)"></p>
                                    <p class="text-xs text-[var(--muted)]" x-text="item.url"></p>
                                </a>
                            </template>
                        </div>
                    </div>

                    <div x-data="languageSwitcher()" class="relative">
                        <button @click="toggle" class="rounded-xl border border-[var(--line)] bg-[var(--surface)] px-3 py-2 text-sm font-semibold">
                            <span x-text="active.code.toUpperCase()"></span>
                        </button>
                        <div x-show="open" @click.outside="open = false" class="absolute right-0 z-20 mt-2 w-44 overflow-hidden rounded-2xl border border-[var(--line)] bg-[var(--surface)] shadow-soft">
                            <template x-for="lang in filtered" :key="lang.code">
                                <a :href="lang.url" class="block px-3 py-2 text-sm hover:bg-brand-50/80 dark:hover:bg-slate-800" x-text="lang.native_name || lang.name"></a>
                            </template>
                        </div>
                    </div>

                    <button @click="toggleTheme" class="h-10 w-10 rounded-xl border border-[var(--line)] bg-[var(--surface)] text-lg">
                        <span x-show="!isDark">☀️</span><span x-show="isDark">🌙</span>
                    </button>
                </div>

                <button @click="mobile = !mobile" class="lg:hidden rounded-xl border border-[var(--line)] bg-[var(--surface)] px-3 py-2 text-sm">☰</button>
            </div>

            <div x-show="mobile" x-transition class="mt-4 space-y-3 rounded-2xl border border-[var(--line)] bg-[var(--surface)] p-4 lg:hidden">
                <a class="block rounded-lg px-2 py-2 hover:bg-[var(--surface-2)]" href="{{ localized_route('catalog.tests-cards') }}">{{ __('public.nav.catalog') }}</a>
                <a class="block rounded-lg px-2 py-2 hover:bg-[var(--surface-2)]" href="{{ localized_route('theory.index') }}">{{ __('public.nav.theory') }}</a>
                <a class="block rounded-lg px-2 py-2 hover:bg-[var(--surface-2)]" href="{{ localized_route('words.test') }}">{{ __('public.nav.words_test') }}</a>
                <a class="block rounded-lg px-2 py-2 hover:bg-[var(--surface-2)]" href="{{ localized_route('verbs.test') }}">{{ __('public.nav.verbs_test') }}</a>
            </div>
        </div>
    </header>

    <main class="relative mx-auto max-w-7xl px-4 py-10 lg:px-8 lg:py-12">
        <section class="mb-8 rounded-3xl border border-[var(--line)] bg-[color-mix(in_srgb,var(--surface)_92%,transparent)] p-6 shadow-glow">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-brand-600">New Public Template</p>
            <h1 class="mt-2 text-2xl font-extrabold md:text-3xl">GPT Layout</h1>
            <p class="mt-2 max-w-2xl text-sm text-[var(--muted)]">Свіжий публічний шаблон із новою структурою шапки, акцентним hero-блоком і оновленими картками контенту.</p>
        </section>
        @yield('content')
    </main>

    <footer class="mt-10 border-t border-[var(--line)] bg-[color-mix(in_srgb,var(--surface)_94%,transparent)]">
        <div class="mx-auto grid max-w-7xl gap-8 px-4 py-10 md:grid-cols-3 lg:px-8">
            <div>
                <x-gramlyze-logo variant="horizontal" class="h-8 w-auto" />
                <p class="mt-3 text-sm text-[var(--muted)]">{{ __('public.footer.description') }}</p>
            </div>
            <div class="text-sm">
                <h3 class="font-bold">{{ __('public.footer.links') }}</h3>
                <div class="mt-3 space-y-2 text-[var(--muted)]">
                    <a href="{{ localized_route('catalog.tests-cards') }}" class="block hover:text-brand-600">{{ __('public.nav.catalog') }}</a>
                    <a href="{{ localized_route('theory.index') }}" class="block hover:text-brand-600">{{ __('public.nav.theory') }}</a>
                    <a href="{{ localized_route('site.search') }}" class="block hover:text-brand-600">{{ __('public.nav.search') }}</a>
                </div>
            </div>
            <div class="text-sm text-[var(--muted)]">
                <h3 class="font-bold text-[var(--text)]">{{ __('public.footer.contact') }}</h3>
                <p class="mt-3">{{ __('public.footer.support') }}</p>
                <p class="mt-4 text-xs">© <span x-text="new Date().getFullYear()"></span> Gramlyze GPT</p>
            </div>
        </div>
    </footer>
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
                if (this.query.trim().length < 2) {
                    this.results = [];
                    this.open = false;
                    return;
                }

                const res = await fetch('{{ localized_route('site.search') }}?q=' + encodeURIComponent(this.query.trim()), {
                    headers: { 'Accept': 'application/json' }
                });
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
            get active() {
                return this.languages.find((l) => l.is_current) || this.languages[0] || {
                    code: '{{ app()->getLocale() }}',
                    name: '{{ app()->getLocale() }}'
                };
            },
            toggle() {
                this.open = !this.open;
            },
            get filtered() {
                const term = this.filter.toLowerCase();
                return this.languages.filter((l) => !term || (l.name && l.name.toLowerCase().includes(term)) || (l.native_name && l.native_name.toLowerCase().includes(term)) || l.code.toLowerCase().includes(term));
            }
        }
    }
</script>

@livewireScripts
@yield('scripts')
</body>
</html>
