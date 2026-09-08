<!doctype html>
<html lang="{{ app()->getLocale() }}" class="h-full" x-data="themeController()" x-bind:class="{ 'dark': isDark }" x-bind:data-background-mode="backgroundMode" x-bind:style="customBackgroundStyle()">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>@yield('title', __('public.meta.title'))</title>
    <meta name="description" content="@yield('meta_description', __('public.meta.description'))" />
    @include('layouts.partials.social-meta')
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Archivo:wght@600;700;800&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/catalog-public.css', 'resources/js/catalog-public.js'])
    <script>
        (() => {
            try {
                const theme = localStorage.getItem('theme');
                document.documentElement.classList.toggle('dark', theme ? theme === 'dark' : window.matchMedia('(prefers-color-scheme: dark)').matches);
                const mode = localStorage.getItem('backgroundMode');
                if (['cards', 'custom'].includes(mode)) {
                    document.documentElement.dataset.backgroundMode = mode;
                }

                const defaults = {
                    ct: '#2f67b1',
                    cr: '#4b55e8',
                    th: '#f99a24',
                    wd: '#172033',
                    vb: '#12b982',
                };
                const savedColors = JSON.parse(localStorage.getItem('backgroundColors') || '{}');
                Object.entries(defaults).forEach(([key, fallback]) => {
                    const value = /^#[0-9a-f]{6}$/i.test(savedColors[key] || '') ? savedColors[key] : fallback;
                    document.documentElement.style.setProperty(`--app-custom-${key}`, value);
                });
            } catch (error) {
                document.documentElement.dataset.backgroundMode = 'blue';
            }
        })();
    </script>
    <style>
        :root {
            --app-background-blue: url('{{ asset('gramlyze-background.svg') }}');
            --app-background-cards: url('{{ asset('gramlyze-background-cards.svg') }}');
        }
    </style>
    @livewireStyles
    @yield('head')
</head>
<body class="min-h-full font-body antialiased @yield('body_class')">
    @include('components.admin-domain-switcher')
    @php
        $backgroundWidgetVisible = (bool) session('admin_authenticated', false)
            || (bool) session('admin_user_id', false)
            || (bool) data_get(auth()->user(), 'is_admin', false);
    @endphp
    <div class="app-fixed-background" aria-hidden="true">
        <svg class="app-bg-vector" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 900" preserveAspectRatio="xMidYMid slice">
            <defs>
                <linearGradient id="appCustomTopCards" x1="0" y1="0" x2="1" y2="1">
                    <stop stop-color="var(--app-custom-cr, #4b55e8)"/>
                    <stop offset=".34" stop-color="var(--app-custom-ct, #2f67b1)"/>
                    <stop offset=".68" stop-color="var(--app-custom-vb, #12b982)"/>
                    <stop offset="1" stop-color="var(--app-custom-th, #f99a24)"/>
                </linearGradient>
                <linearGradient id="appCustomBottomCards" x1="0" y1="0" x2="1" y2="1">
                    <stop stop-color="var(--app-custom-th, #f99a24)"/>
                    <stop offset=".45" stop-color="var(--app-custom-ct, #2f67b1)"/>
                    <stop offset="1" stop-color="var(--app-custom-wd, #172033)"/>
                </linearGradient>
                <linearGradient id="appCustomBottomSoftCards" x1="0" y1="0" x2="1" y2="1">
                    <stop stop-color="color-mix(in srgb, var(--app-custom-th, #f99a24) 28%, #ffffff)"/>
                    <stop offset=".45" stop-color="color-mix(in srgb, var(--app-custom-ct, #2f67b1) 24%, #ffffff)"/>
                    <stop offset="1" stop-color="color-mix(in srgb, var(--app-custom-vb, #12b982) 24%, #ffffff)"/>
                </linearGradient>
            </defs>

            <path d="M1010 0h430v360c-42-38-79-82-122-119-58-50-116-69-186-88-82-22-128-65-146-153z" fill="url(#appCustomTopCards)" opacity=".92"/>
            <path d="M1124 114c78 18 145 43 204 96 36 32 72 66 112 100v50c-42-38-79-82-122-119-58-50-116-69-186-88-82-22-128-65-146-153h86c14 51 30 88 52 114z" fill="#ffffff" opacity=".20"/>
            <path d="M1184 78c82 16 168 55 256 128v68c-74-70-152-112-234-130-60-13-104-36-132-66z" fill="var(--app-custom-wd, #172033)" opacity=".10"/>

            <path d="M0 668c90 12 156 44 214 84 52 36 120 63 166 63 45 1 84-21 120-53v138H0z" fill="url(#appCustomBottomSoftCards)" opacity=".84"/>
            <path d="M0 711c84 10 145 45 205 83 61 38 118 66 182 58 43-5 80-27 113-54v102H0z" fill="url(#appCustomBottomCards)" opacity=".94"/>

            <g fill="none" stroke-linecap="round" stroke-dasharray="9 14" opacity=".34">
                <path d="M180 165c95-25 155 45 230 15" stroke="var(--app-custom-ct, #2f67b1)" stroke-width="3"/>
                <path d="M1060 155c120-60 238-15 352 65" stroke="var(--app-custom-cr, #4b55e8)" stroke-width="3"/>
                <path d="M165 840c120-50 215 70 335 45" stroke="var(--app-custom-th, #f99a24)" stroke-width="3"/>
                <path d="M1040 815c120-50 220 40 380 25" stroke="var(--app-custom-vb, #12b982)" stroke-width="3"/>
            </g>

            <g opacity=".20">
                <circle cx="122" cy="122" r="46" fill="var(--app-custom-ct, #2f67b1)"/>
                <circle cx="306" cy="92" r="34" fill="var(--app-custom-cr, #4b55e8)"/>
                <circle cx="540" cy="142" r="42" fill="var(--app-custom-th, #f99a24)"/>
                <circle cx="910" cy="124" r="38" fill="var(--app-custom-wd, #172033)"/>
                <circle cx="1260" cy="604" r="48" fill="var(--app-custom-vb, #12b982)"/>
            </g>
        </svg>

        <div class="app-bg-icon app-bg-icon--chat app-bg-icon--shadow" data-app-bg-icon="chat" data-app-bg-side="left">
            <div class="app-bg-icon-inner">
                <svg viewBox="0 0 220 170" xmlns="http://www.w3.org/2000/svg">
                    <defs>
                        <linearGradient id="appChatGradient" x1="0" y1="0" x2="1" y2="1">
                            <stop offset="0%" stop-color="var(--app-bg-chat-from, #dff7ff)"/>
                            <stop offset="100%" stop-color="var(--app-bg-chat-to, #dbeafe)"/>
                        </linearGradient>
                    </defs>
                    <path d="M0 37C0 15 15 0 37 0h145c22 0 37 15 37 37v65c0 22-15 37-37 37H78l-39 30c-8 6-18 1-17-9l3-22C10 130 0 118 0 102V37Z" fill="url(#appChatGradient)" opacity=".92"/>
                    <circle cx="73" cy="69" r="10.5" fill="var(--app-bg-icon-strong, #1d4ed8)" opacity=".85"/>
                    <circle cx="109" cy="69" r="10.5" fill="var(--app-bg-icon-strong, #1d4ed8)" opacity=".85"/>
                    <circle cx="146" cy="69" r="10.5" fill="var(--app-bg-icon-strong, #1d4ed8)" opacity=".85"/>
                </svg>
            </div>
        </div>

        <div class="app-bg-icon app-bg-icon--aa app-bg-icon--slow" data-app-bg-icon="aa" data-app-bg-side="left">
            <div class="app-bg-icon-inner">
                <svg viewBox="0 0 125 112" xmlns="http://www.w3.org/2000/svg">
                    <text x="0" y="74" font-family="Georgia, serif" font-size="82" fill="var(--app-bg-icon-primary, #2563eb)">Aa</text>
                    <line x1="0" y1="95" x2="96" y2="95" stroke="var(--app-bg-icon-soft, #93c5fd)" stroke-width="5" stroke-linecap="round"/>
                </svg>
            </div>
        </div>

        <div class="app-bg-icon app-bg-icon--comma app-bg-icon--fast" data-app-bg-icon="comma" data-app-bg-side="left">
            <div class="app-bg-icon-inner">
                <svg viewBox="0 0 90 120" xmlns="http://www.w3.org/2000/svg">
                    <text x="0" y="95" font-family="Georgia, serif" font-size="120" fill="var(--app-bg-icon-primary, #2563eb)">,</text>
                </svg>
            </div>
        </div>

        <div class="app-bg-icon app-bg-icon--dots-left" data-app-bg-icon="dots" data-app-bg-side="left">
            <div class="app-bg-icon-inner">
                <svg viewBox="0 0 150 90" xmlns="http://www.w3.org/2000/svg" fill="var(--app-bg-icon-primary, #3b82f6)">
                    <circle cx="0" cy="0" r="4"/><circle cx="30" cy="0" r="4"/><circle cx="60" cy="0" r="4"/><circle cx="90" cy="0" r="4"/><circle cx="120" cy="0" r="4"/>
                    <circle cx="0" cy="30" r="4"/><circle cx="30" cy="30" r="4"/><circle cx="60" cy="30" r="4"/><circle cx="90" cy="30" r="4"/><circle cx="120" cy="30" r="4"/>
                    <circle cx="0" cy="60" r="4"/><circle cx="30" cy="60" r="4"/><circle cx="60" cy="60" r="4"/><circle cx="90" cy="60" r="4"/><circle cx="120" cy="60" r="4"/>
                </svg>
            </div>
        </div>

        <div class="app-bg-icon app-bg-icon--ring-left app-bg-icon--slow" data-app-bg-icon="ring" data-app-bg-side="left">
            <div class="app-bg-icon-inner">
                <svg viewBox="0 0 50 50" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="25" cy="25" r="18" fill="none" stroke="var(--app-bg-icon-primary, #60a5fa)" stroke-width="5"/>
                </svg>
            </div>
        </div>

        <div class="app-bg-icon app-bg-icon--quote app-bg-icon--fast" data-app-bg-icon="quote" data-app-bg-side="right">
            <div class="app-bg-icon-inner">
                <svg viewBox="0 0 110 100" xmlns="http://www.w3.org/2000/svg">
                    <text x="0" y="82" font-family="Georgia, serif" font-size="104" font-weight="700" fill="var(--app-bg-icon-primary, #2563eb)">“</text>
                </svg>
            </div>
        </div>

        <div class="app-bg-icon app-bg-icon--dots-right" data-app-bg-icon="dots" data-app-bg-side="right">
            <div class="app-bg-icon-inner">
                <svg viewBox="0 0 150 90" xmlns="http://www.w3.org/2000/svg" fill="var(--app-bg-icon-primary, #3b82f6)">
                    <circle cx="0" cy="0" r="4"/><circle cx="30" cy="0" r="4"/><circle cx="60" cy="0" r="4"/><circle cx="90" cy="0" r="4"/><circle cx="120" cy="0" r="4"/>
                    <circle cx="0" cy="30" r="4"/><circle cx="30" cy="30" r="4"/><circle cx="60" cy="30" r="4"/><circle cx="90" cy="30" r="4"/><circle cx="120" cy="30" r="4"/>
                    <circle cx="0" cy="60" r="4"/><circle cx="30" cy="60" r="4"/><circle cx="60" cy="60" r="4"/><circle cx="90" cy="60" r="4"/><circle cx="120" cy="60" r="4"/>
                </svg>
            </div>
        </div>

        <div class="app-bg-icon app-bg-icon--ring-right app-bg-icon--fast" data-app-bg-icon="ring" data-app-bg-side="right">
            <div class="app-bg-icon-inner">
                <svg viewBox="0 0 50 50" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="25" cy="25" r="18" fill="none" stroke="var(--app-bg-icon-primary, #60a5fa)" stroke-width="5"/>
                </svg>
            </div>
        </div>

        <div class="app-bg-icon app-bg-icon--document app-bg-icon--shadow" data-app-bg-icon="document" data-app-bg-side="right">
            <div class="app-bg-icon-inner">
                <svg viewBox="0 0 132 140" xmlns="http://www.w3.org/2000/svg">
                    <defs>
                        <linearGradient id="appDocGradient" x1="0" y1="0" x2="1" y2="1">
                            <stop offset="0%" stop-color="var(--app-bg-doc-from, #dfe7ff)"/>
                            <stop offset="100%" stop-color="var(--app-bg-doc-to, #cfdcff)"/>
                        </linearGradient>
                    </defs>
                    <rect width="132" height="140" rx="22" fill="url(#appDocGradient)"/>
                    <path d="M30 48h78M30 70h78M30 92h58" stroke="var(--app-bg-icon-primary, #2563eb)" stroke-width="6" stroke-linecap="round"/>
                </svg>
            </div>
        </div>

        <div class="app-bg-icon app-bg-icon--braces app-bg-icon--slow" data-app-bg-icon="braces" data-app-bg-side="right">
            <div class="app-bg-icon-inner">
                <svg viewBox="0 0 130 100" xmlns="http://www.w3.org/2000/svg">
                    <text x="0" y="77" font-family="ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace" font-size="80" fill="var(--app-bg-icon-primary, #2563eb)">{ }</text>
                </svg>
            </div>
        </div>

        <div class="app-bg-icon app-bg-icon--dots-low app-bg-icon--fast" data-app-bg-icon="dots-low" data-app-bg-side="right">
            <div class="app-bg-icon-inner">
                <svg viewBox="0 0 120 90" xmlns="http://www.w3.org/2000/svg" fill="var(--app-bg-icon-primary, #3b82f6)">
                    <circle cx="0" cy="0" r="4"/><circle cx="30" cy="0" r="4"/><circle cx="60" cy="0" r="4"/><circle cx="90" cy="0" r="4"/>
                    <circle cx="0" cy="30" r="4"/><circle cx="30" cy="30" r="4"/><circle cx="60" cy="30" r="4"/><circle cx="90" cy="30" r="4"/>
                    <circle cx="0" cy="60" r="4"/><circle cx="30" cy="60" r="4"/><circle cx="60" cy="60" r="4"/><circle cx="90" cy="60" r="4"/>
                </svg>
            </div>
        </div>

        <div class="app-bg-icon app-bg-icon--check app-bg-icon--shadow" data-app-bg-icon="check" data-app-bg-side="right">
            <div class="app-bg-icon-inner">
                <svg viewBox="0 0 150 150" xmlns="http://www.w3.org/2000/svg">
                    <defs>
                        <linearGradient id="appCheckGradient" x1="0" y1="0" x2="1" y2="1">
                            <stop offset="0%" stop-color="var(--app-bg-check-from, #38d5ff)"/>
                            <stop offset="100%" stop-color="var(--app-bg-check-to, #2563eb)"/>
                        </linearGradient>
                    </defs>
                    <circle cx="75" cy="75" r="70" fill="url(#appCheckGradient)" opacity=".88"/>
                    <path d="M42 75l24 25 46-55" fill="none" stroke="#fff" stroke-width="12" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
        </div>
    </div>
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
                        @devMode
                            <a href="{{ localized_route('catalog.tests-cards') }}" class="transition hover:text-ocean">{{ __('public.nav.catalog') }}</a>
                        @enddevMode
                        <a href="{{ localized_route('theory.index') }}" class="transition hover:text-ocean">{{ __('public.nav.theory') }}</a>
                        <a href="{{ localized_route('courses.index') }}" class="transition hover:text-ocean">{{ __('public.nav.courses') }}</a>
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

                        @if($backgroundWidgetVisible)
                            <div class="relative" @click.outside="backgroundPanelOpen = false">
                                <button data-testid="background-mode-toggle-desktop" @click="toggleBackgroundPanel" :aria-expanded="backgroundPanelOpen" class="inline-flex h-11 w-11 items-center justify-center rounded-2xl border shadow-sm transition hover:border-ocean surface-card-strong" :aria-label="'{{ __('public.background.widget') }}'">
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" aria-hidden="true">
                                        <circle cx="7" cy="8" r="3.3" fill="var(--app-custom-ct, #2f67b1)" />
                                        <circle cx="13.6" cy="6.4" r="3.3" fill="var(--app-custom-cr, #4b55e8)" />
                                        <circle cx="17" cy="12.4" r="3.3" fill="var(--app-custom-th, #f99a24)" />
                                        <circle cx="11.1" cy="17.2" r="3.3" fill="var(--app-custom-vb, #12b982)" />
                                        <circle cx="6.6" cy="14.2" r="3.3" fill="var(--app-custom-wd, #172033)" />
                                    </svg>
                                </button>

                                <div x-show="backgroundPanelOpen" x-cloak x-transition class="absolute right-0 top-full z-50 mt-2 w-80 rounded-[24px] border p-4 shadow-card surface-card-strong">
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <p class="text-sm font-extrabold">{{ __('public.background.title') }}</p>
                                            <p class="mt-1 text-xs" style="color: var(--muted);">{{ __('public.background.description') }}</p>
                                        </div>
                                        <button type="button" @click="backgroundPanelOpen = false" class="rounded-xl px-2 py-1 text-xs font-bold soft-accent" style="color: var(--accent);">{{ __('public.background.close') }}</button>
                                    </div>

                                    <div class="mt-4 grid grid-cols-3 gap-2">
                                        <button data-testid="background-mode-blue-desktop" type="button" @click="setBackgroundMode('blue')" class="rounded-2xl border px-3 py-2 text-left text-xs font-bold transition surface-card" :style="backgroundMode === 'blue' ? 'border-color: var(--accent); box-shadow: 0 0 0 2px rgba(47,103,177,.14)' : ''">
                                            <span class="block h-4 rounded-full bg-gradient-to-r from-sky-300 to-blue-600"></span>
                                            <span class="mt-2 block">{{ __('public.background.blue_short') }}</span>
                                        </button>
                                        <button data-testid="background-mode-cards-desktop" type="button" @click="setBackgroundMode('cards')" class="rounded-2xl border px-3 py-2 text-left text-xs font-bold transition surface-card" :style="backgroundMode === 'cards' ? 'border-color: var(--accent); box-shadow: 0 0 0 2px rgba(47,103,177,.14)' : ''">
                                            <span class="block h-4 rounded-full" style="background: linear-gradient(90deg, #2f67b1, #4b55e8, #f99a24, #172033, #12b982);"></span>
                                            <span class="mt-2 block">{{ __('public.background.cards_short') }}</span>
                                        </button>
                                        <button data-testid="background-mode-custom-desktop" type="button" @click="setBackgroundMode('custom')" class="rounded-2xl border px-3 py-2 text-left text-xs font-bold transition surface-card" :style="backgroundMode === 'custom' ? 'border-color: var(--accent); box-shadow: 0 0 0 2px rgba(47,103,177,.14)' : ''">
                                            <span class="block h-4 rounded-full" :style="customPreviewGradient()"></span>
                                            <span class="mt-2 block">{{ __('public.background.custom_short') }}</span>
                                        </button>
                                    </div>

                                    <div x-show="backgroundMode === 'custom'" x-cloak class="mt-4 space-y-3">
                                        <template x-for="control in backgroundColorControls" :key="control.key">
                                            <label class="grid grid-cols-[auto_1fr_auto] items-center gap-3 rounded-2xl border p-2 surface-card" style="border-color: var(--line);">
                                                <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl text-xs font-extrabold text-white" :style="`background: ${safeBackgroundColor(control.key)}`" x-text="control.short"></span>
                                                <span>
                                                    <span class="block text-xs font-bold" x-text="control.label"></span>
                                                    <input :data-testid="`background-hex-${control.key}`" x-model="backgroundColors[control.key]" @input="saveBackgroundColors" class="mt-1 w-full rounded-xl border px-2 py-1 text-xs font-semibold uppercase outline-none surface-card-strong" style="border-color: var(--line);" maxlength="7">
                                                </span>
                                                <input :data-testid="`background-color-${control.key}`" type="color" x-model="backgroundColors[control.key]" @input="saveBackgroundColors" class="h-9 w-11 cursor-pointer rounded-xl border bg-transparent p-1" style="border-color: var(--line);">
                                            </label>
                                        </template>
                                        <button type="button" @click="resetBackgroundColors" class="w-full rounded-2xl border px-3 py-2 text-xs font-bold transition hover:border-ocean surface-card">
                                            {{ __('public.background.reset') }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endif

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
                        @devMode
                            <a href="{{ localized_route('catalog.tests-cards') }}" class="rounded-2xl border px-4 py-3 transition hover:border-ocean surface-card-strong">{{ __('public.nav.catalog') }}</a>
                        @enddevMode
                        <a href="{{ localized_route('theory.index') }}" class="rounded-2xl border px-4 py-3 transition hover:border-ocean surface-card-strong">{{ __('public.nav.theory') }}</a>
                        <a href="{{ localized_route('courses.index') }}" class="rounded-2xl border px-4 py-3 transition hover:border-ocean surface-card-strong">{{ __('public.nav.courses') }}</a>
                        <a href="{{ localized_route('words.test') }}" class="rounded-2xl border px-4 py-3 transition hover:border-ocean surface-card-strong">{{ __('public.nav.words_test') }}</a>
                        <a href="{{ localized_route('verbs.test') }}" class="rounded-2xl border px-4 py-3 transition hover:border-ocean surface-card-strong">{{ __('public.nav.verbs_test') }}</a>
                    </nav>

                    <div class="grid gap-3 md:grid-cols-[1fr_auto_auto_auto]">
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

                        @if($backgroundWidgetVisible)
                            <button data-testid="background-mode-toggle-mobile" @click="toggleBackgroundPanel" class="inline-flex h-full items-center justify-center rounded-2xl border px-4 py-3 surface-card-strong" :aria-label="'{{ __('public.background.widget') }}'">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" aria-hidden="true">
                                    <circle cx="7" cy="8" r="3.3" fill="var(--app-custom-ct, #2f67b1)" />
                                    <circle cx="13.6" cy="6.4" r="3.3" fill="var(--app-custom-cr, #4b55e8)" />
                                    <circle cx="17" cy="12.4" r="3.3" fill="var(--app-custom-th, #f99a24)" />
                                    <circle cx="11.1" cy="17.2" r="3.3" fill="var(--app-custom-vb, #12b982)" />
                                    <circle cx="6.6" cy="14.2" r="3.3" fill="var(--app-custom-wd, #172033)" />
                                </svg>
                            </button>
                        @endif
                    </div>

                    @if($backgroundWidgetVisible)
                        <div x-show="backgroundPanelOpen" x-cloak x-transition class="rounded-[24px] border p-4 shadow-card surface-card-strong">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-sm font-extrabold">{{ __('public.background.title') }}</p>
                                    <p class="mt-1 text-xs" style="color: var(--muted);">{{ __('public.background.description') }}</p>
                                </div>
                                <button type="button" @click="backgroundPanelOpen = false" class="rounded-xl px-2 py-1 text-xs font-bold soft-accent" style="color: var(--accent);">{{ __('public.background.close') }}</button>
                            </div>

                            <div class="mt-4 grid grid-cols-3 gap-2">
                                <button data-testid="background-mode-blue-mobile" type="button" @click="setBackgroundMode('blue')" class="rounded-2xl border px-3 py-2 text-left text-xs font-bold transition surface-card" :style="backgroundMode === 'blue' ? 'border-color: var(--accent); box-shadow: 0 0 0 2px rgba(47,103,177,.14)' : ''">
                                    <span class="block h-4 rounded-full bg-gradient-to-r from-sky-300 to-blue-600"></span>
                                    <span class="mt-2 block">{{ __('public.background.blue_short') }}</span>
                                </button>
                                <button data-testid="background-mode-cards-mobile" type="button" @click="setBackgroundMode('cards')" class="rounded-2xl border px-3 py-2 text-left text-xs font-bold transition surface-card" :style="backgroundMode === 'cards' ? 'border-color: var(--accent); box-shadow: 0 0 0 2px rgba(47,103,177,.14)' : ''">
                                    <span class="block h-4 rounded-full" style="background: linear-gradient(90deg, #2f67b1, #4b55e8, #f99a24, #172033, #12b982);"></span>
                                    <span class="mt-2 block">{{ __('public.background.cards_short') }}</span>
                                </button>
                                <button data-testid="background-mode-custom-mobile" type="button" @click="setBackgroundMode('custom')" class="rounded-2xl border px-3 py-2 text-left text-xs font-bold transition surface-card" :style="backgroundMode === 'custom' ? 'border-color: var(--accent); box-shadow: 0 0 0 2px rgba(47,103,177,.14)' : ''">
                                    <span class="block h-4 rounded-full" :style="customPreviewGradient()"></span>
                                    <span class="mt-2 block">{{ __('public.background.custom_short') }}</span>
                                </button>
                            </div>

                            <div x-show="backgroundMode === 'custom'" x-cloak class="mt-4 space-y-3">
                                <template x-for="control in backgroundColorControls" :key="control.key">
                                    <label class="grid grid-cols-[auto_1fr_auto] items-center gap-3 rounded-2xl border p-2 surface-card" style="border-color: var(--line);">
                                        <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl text-xs font-extrabold text-white" :style="`background: ${safeBackgroundColor(control.key)}`" x-text="control.short"></span>
                                        <span>
                                            <span class="block text-xs font-bold" x-text="control.label"></span>
                                            <input :data-testid="`background-mobile-hex-${control.key}`" x-model="backgroundColors[control.key]" @input="saveBackgroundColors" class="mt-1 w-full rounded-xl border px-2 py-1 text-xs font-semibold uppercase outline-none surface-card-strong" style="border-color: var(--line);" maxlength="7">
                                        </span>
                                        <input :data-testid="`background-mobile-color-${control.key}`" type="color" x-model="backgroundColors[control.key]" @input="saveBackgroundColors" class="h-9 w-11 cursor-pointer rounded-xl border bg-transparent p-1" style="border-color: var(--line);">
                                    </label>
                                </template>
                                <button type="button" @click="resetBackgroundColors" class="w-full rounded-2xl border px-3 py-2 text-xs font-bold transition hover:border-ocean surface-card">
                                    {{ __('public.background.reset') }}
                                </button>
                            </div>
                        </div>
                    @endif
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
                            @devMode
                                <a href="{{ localized_route('catalog.tests-cards') }}" class="transition hover:text-white">{{ __('public.nav.catalog') }}</a>
                            @enddevMode
                            <a href="{{ localized_route('theory.index') }}" class="transition hover:text-white">{{ __('public.nav.theory') }}</a>
                            <a href="{{ localized_route('courses.index') }}" class="transition hover:text-white">{{ __('public.nav.courses') }}</a>
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
                backgroundPanelOpen: false,
                backgroundMode: 'blue',
                backgroundColors: {
                    ct: '#2f67b1',
                    cr: '#4b55e8',
                    th: '#f99a24',
                    wd: '#172033',
                    vb: '#12b982',
                },
                backgroundColorControls: [
                    { key: 'ct', short: 'CT', label: @js(__('public.background.ct')) },
                    { key: 'cr', short: 'CR', label: @js(__('public.background.cr')) },
                    { key: 'th', short: 'TH', label: @js(__('public.background.th')) },
                    { key: 'wd', short: 'WD', label: @js(__('public.background.wd')) },
                    { key: 'vb', short: 'VB', label: @js(__('public.background.vb')) },
                ],
                init() {
                    const saved = localStorage.getItem('theme');
                    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                    this.isDark = saved ? saved === 'dark' : prefersDark;
                    const savedBackgroundMode = localStorage.getItem('backgroundMode');
                    this.backgroundMode = ['blue', 'cards', 'custom'].includes(savedBackgroundMode) ? savedBackgroundMode : 'blue';
                    this.backgroundColors = this.loadBackgroundColors();
                },
                toggleTheme() {
                    this.isDark = !this.isDark;
                    localStorage.setItem('theme', this.isDark ? 'dark' : 'light');
                },
                defaultBackgroundColors() {
                    return {
                        ct: '#2f67b1',
                        cr: '#4b55e8',
                        th: '#f99a24',
                        wd: '#172033',
                        vb: '#12b982',
                    };
                },
                isHexColor(value) {
                    return /^#[0-9a-f]{6}$/i.test(value || '');
                },
                loadBackgroundColors() {
                    const defaults = this.defaultBackgroundColors();

                    try {
                        const saved = JSON.parse(localStorage.getItem('backgroundColors') || '{}');

                        return Object.fromEntries(Object.entries(defaults).map(([key, fallback]) => [
                            key,
                            this.isHexColor(saved[key]) ? saved[key] : fallback,
                        ]));
                    } catch (error) {
                        return defaults;
                    }
                },
                safeBackgroundColor(key) {
                    const defaults = this.defaultBackgroundColors();
                    const value = this.backgroundColors[key];

                    return this.isHexColor(value) ? value : defaults[key];
                },
                customBackgroundStyle() {
                    return Object.keys(this.defaultBackgroundColors())
                        .map((key) => `--app-custom-${key}: ${this.safeBackgroundColor(key)}`)
                        .join('; ');
                },
                customPreviewGradient() {
                    return `background: linear-gradient(90deg, ${this.safeBackgroundColor('ct')}, ${this.safeBackgroundColor('cr')}, ${this.safeBackgroundColor('th')}, ${this.safeBackgroundColor('wd')}, ${this.safeBackgroundColor('vb')});`;
                },
                saveBackgroundColors() {
                    const colors = Object.fromEntries(Object.keys(this.defaultBackgroundColors()).map((key) => [
                        key,
                        this.safeBackgroundColor(key),
                    ]));

                    localStorage.setItem('backgroundColors', JSON.stringify(colors));
                },
                resetBackgroundColors() {
                    this.backgroundColors = this.defaultBackgroundColors();
                    this.saveBackgroundColors();
                },
                toggleBackgroundPanel() {
                    this.backgroundPanelOpen = !this.backgroundPanelOpen;
                },
                setBackgroundMode(mode) {
                    if (!['blue', 'cards', 'custom'].includes(mode)) {
                        return;
                    }

                    this.backgroundMode = mode;
                    localStorage.setItem('backgroundMode', mode);
                    this.refreshBackgroundDecorations();
                },
                toggleBackground() {
                    const nextMode = this.backgroundMode === 'blue'
                        ? 'cards'
                        : (this.backgroundMode === 'cards' ? 'custom' : 'blue');

                    this.setBackgroundMode(nextMode);
                },
                refreshBackgroundDecorations() {
                    window.requestAnimationFrame(() => {
                        if (typeof randomizeAppBackgroundIcons === 'function') {
                            randomizeAppBackgroundIcons();
                        }
                    });
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

    </script>
    @livewireScripts
    @yield('scripts')
</body>
</html>
