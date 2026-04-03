@extends('layouts.copilot')

@section('title', ($selectedCategory->title ?? __('frontend.copilot_theory.category')) . ' — ' . ($sectionTitle ?? __('frontend.copilot_theory.theory')))

@section('breadcrumb')
    <nav class="flex items-center gap-1.5 text-xs text-[var(--cp-muted)]" aria-label="{{ __('public.common.breadcrumb') }}">
        <a href="{{ localized_route('copilot.index') }}" class="hover:text-pilot-600 transition-colors">{{ __('public.common.home') }}</a>
        <span>/</span>
        <a href="{{ localized_route($routePrefix . '.index') }}" class="hover:text-pilot-600 transition-colors">{{ $sectionTitle ?? __('frontend.copilot_theory.theory') }}</a>
        <span>/</span>
        <span class="font-medium text-[var(--cp-fg)] truncate max-w-[180px]">{{ $selectedCategory->title }}</span>
    </nav>
@endsection

@section('content')
    @php
        $categoryPages = $categoryPages ?? collect();
        $routePrefix = $routePrefix ?? 'copilot.theory';
        $categoryDescription = $categoryDescription ?? ['hasBlocks' => false];
    @endphp

    <div class="space-y-8">
        {{-- ── Category Hero ────────────────────────────────────────────── --}}
        <header class="relative overflow-hidden rounded-3xl border border-[var(--cp-border)] bg-gradient-to-br from-pilot-50 via-[var(--cp-surface)] to-teal-50/50 dark:from-pilot-900/30 dark:via-[var(--cp-surface)] dark:to-teal-900/20 shadow-panel">
            <div class="absolute inset-0 opacity-40">
                <svg class="h-full w-full text-pilot-100 dark:text-pilot-900" viewBox="0 0 100 100" preserveAspectRatio="none">
                    <defs>
                        <pattern id="cp-cat-grid" width="10" height="10" patternUnits="userSpaceOnUse">
                            <path d="M 10 0 L 0 0 0 10" fill="none" stroke="currentColor" stroke-width="0.5"/>
                        </pattern>
                    </defs>
                    <rect width="100%" height="100%" fill="url(#cp-cat-grid)"/>
                </svg>
            </div>

            <div class="relative px-6 py-8 md:px-8 md:py-10">
                <div class="flex items-center gap-3 mb-4">
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-pilot-100 dark:bg-pilot-900/50 text-pilot-700 dark:text-pilot-300 px-3 py-1.5 text-xs font-bold tracking-wide shadow-sm">
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"/>
                        </svg>
                        {{ __('frontend.copilot_theory.category') }}
                    </span>
                    <span class="h-1 w-1 rounded-full bg-pilot-200 dark:bg-pilot-700"></span>
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-pilot-50 dark:bg-pilot-900/30 text-pilot-700 dark:text-pilot-300 px-3 py-1.5 text-xs font-bold tracking-wide">
                        {{ __('frontend.copilot_theory.pages_count', ['count' => $categoryPages->count()]) }}
                    </span>
                </div>

                <h1 class="text-2xl md:text-3xl lg:text-4xl font-extrabold tracking-tight leading-tight mb-4 text-[var(--cp-fg)]">
                    {{ $selectedCategory->title }}
                </h1>
                <p class="text-base md:text-lg text-[var(--cp-muted)] leading-relaxed max-w-3xl">
                    {{ __('frontend.copilot_theory.materials_in_category', ['category' => $selectedCategory->title]) }}
                </p>

                <div class="mt-6">
                    <a
                        href="{{ localized_route($routePrefix . '.index') }}"
                        class="inline-flex items-center gap-1.5 rounded-xl bg-pilot-600 text-white px-4 py-2 text-sm font-semibold shadow-glow hover:bg-pilot-700 transition"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        {{ __('frontend.copilot_theory.all_categories') }}
                    </a>
                </div>
            </div>
        </header>

        @include('copilot.theory.partials.sidebar-navigation-mobile', [
            'categories' => $categories,
            'selectedCategory' => $selectedCategory,
            'categoryPages' => $categoryPages,
            'currentPage' => null,
            'routePrefix' => $routePrefix,
        ])

        {{-- ── Main grid ───────────────────────────────────────────────── --}}
        <div class="grid grid-cols-1 gap-8 lg:grid-cols-[280px_minmax(0,1fr)] xl:grid-cols-[300px_minmax(0,1fr)]">

            {{-- Left sidebar (desktop) --}}
            <aside class="hidden lg:block">
                <div class="sticky top-24 space-y-4 max-h-[calc(100vh-7rem)] overflow-y-auto pr-1">
                    {{-- Categories nav --}}
                    <div class="cp-card p-5">
                        <h3 class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-[var(--cp-muted)] mb-4">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                            </svg>
                            {{ __('frontend.copilot_theory.categories') }}
                        </h3>
                        <nav class="space-y-1 overflow-y-auto pr-1">
                            @if($categories->isNotEmpty())
                                @include('copilot.theory.partials.nested-category-nav', [
                                    'categories' => $categories,
                                    'selectedCategory' => $selectedCategory,
                                    'routePrefix' => $routePrefix,
                                ])
                            @else
                                <p class="text-sm text-[var(--cp-muted)]">{{ __('public.common.no_categories') }}</p>
                            @endif
                        </nav>
                    </div>

                    {{-- Tags --}}
                    @if(isset($selectedCategory) && $selectedCategory->tags->isNotEmpty())
                        <div class="cp-card p-5" x-data="{ show: false }">
                            <button @click="show = !show" class="flex w-full items-center justify-between text-left">
                                <h3 class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-[var(--cp-muted)]">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                    </svg>
                                    {{ __('frontend.copilot_theory.tags', ['count' => $selectedCategory->tags->count()]) }}
                                </h3>
                                <svg class="h-4 w-4 text-[var(--cp-muted)] transition-transform" :class="{ 'rotate-180': show }" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                            <div x-show="show" x-collapse class="mt-4">
                                <div class="flex flex-wrap gap-1.5">
                                    @foreach($selectedCategory->tags as $tag)
                                        <span class="inline-flex items-center rounded-md bg-pilot-50 dark:bg-pilot-900/30 px-2 py-1 text-xs text-[var(--cp-muted)]">
                                            {{ $tag->name }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Quick actions --}}
                    <div class="cp-card p-5">
                        <h3 class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-[var(--cp-muted)] mb-4">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                            {{ __('frontend.copilot_theory.quick_actions') }}
                        </h3>
                        <div class="space-y-2">
                            <a
                                href="{{ localized_route($routePrefix . '.index') }}"
                                class="flex items-center gap-3 rounded-xl border border-[var(--cp-border)] bg-[var(--cp-surface)] px-4 py-3 text-sm font-medium text-[var(--cp-fg)] transition-all hover:border-pilot-400 hover:shadow-sm"
                            >
                                <svg class="h-5 w-5 text-[var(--cp-muted)]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                                </svg>
                                {{ __('frontend.copilot_theory.all_categories') }}
                            </a>
                        </div>
                    </div>
                </div>
            </aside>

            {{-- Primary content --}}
            <div class="min-w-0 space-y-6">
                {{-- Category description (if has blocks) --}}
                @if($categoryDescription['hasBlocks'] ?? false)
                    @include('engram.theory.partials.category-description-v3', [
                        'page' => $selectedCategory,
                        'categoryDescription' => $categoryDescription,
                    ])
                @endif

                {{-- Pages grid --}}
                @if($categoryPages->isNotEmpty())
                    <section class="rounded-3xl border border-[var(--cp-border)] bg-[var(--cp-surface)] p-6 shadow-panel">
                        <div class="text-center mb-8">
                            <h2 class="text-xl md:text-2xl font-bold text-[var(--cp-fg)] mb-2">{{ __('frontend.copilot_theory.materials_for_learning') }}</h2>
                            <p class="text-[var(--cp-muted)] max-w-xl mx-auto text-sm">
                                {{ __('frontend.copilot_theory.materials_for_learning_hint', ['category' => $selectedCategory->title]) }}
                            </p>
                        </div>

                        @php
                            $palette = [
                                ['icon' => 'bg-pilot-100 dark:bg-pilot-900/50 text-pilot-700 dark:text-pilot-300', 'pill' => 'bg-pilot-50 dark:bg-pilot-900/30 text-pilot-700 dark:text-pilot-300', 'glow' => 'from-pilot-50/80 dark:from-pilot-900/20'],
                                ['icon' => 'bg-teal-100 dark:bg-teal-900/50 text-teal-700 dark:text-teal-300', 'pill' => 'bg-teal-50 dark:bg-teal-900/30 text-teal-700 dark:text-teal-300', 'glow' => 'from-teal-50/80 dark:from-teal-900/20'],
                                ['icon' => 'bg-cyan-100 dark:bg-cyan-900/50 text-cyan-700 dark:text-cyan-300', 'pill' => 'bg-cyan-50 dark:bg-cyan-900/30 text-cyan-700 dark:text-cyan-300', 'glow' => 'from-cyan-50/80 dark:from-cyan-900/20'],
                                ['icon' => 'bg-emerald-100 dark:bg-emerald-900/50 text-emerald-700 dark:text-emerald-300', 'pill' => 'bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300', 'glow' => 'from-emerald-50/80 dark:from-emerald-900/20'],
                                ['icon' => 'bg-sky-100 dark:bg-sky-900/50 text-sky-700 dark:text-sky-300', 'pill' => 'bg-sky-50 dark:bg-sky-900/30 text-sky-700 dark:text-sky-300', 'glow' => 'from-sky-50/80 dark:from-sky-900/20'],
                                ['icon' => 'bg-amber-100 dark:bg-amber-900/50 text-amber-700 dark:text-amber-300', 'pill' => 'bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300', 'glow' => 'from-amber-50/80 dark:from-amber-900/20'],
                            ];
                        @endphp

                        <div class="grid gap-4 md:grid-cols-2">
                            @foreach($categoryPages as $index => $page)
                                @php($colors = $palette[$index % count($palette)])
                                <a
                                    href="{{ localized_route($routePrefix . '.show', [$selectedCategory->slug, $page->slug]) }}"
                                    class="group relative block overflow-hidden rounded-2xl border border-[var(--cp-border)] bg-[var(--cp-surface)] p-5 shadow-sm transition-all hover:border-pilot-400 hover:shadow-card"
                                >
                                    <div class="absolute inset-0 bg-gradient-to-br {{ $colors['glow'] }} to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                                    <div class="relative flex items-start gap-4">
                                        <div class="flex h-11 w-11 items-center justify-center rounded-xl {{ $colors['icon'] }} flex-shrink-0">
                                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                            </svg>
                                        </div>
                                        <div class="flex-1 space-y-1.5">
                                            <div class="flex items-center gap-2">
                                                <span class="inline-flex items-center rounded-full {{ $colors['pill'] }} px-2.5 py-0.5 text-[11px] font-semibold">
                                                    {{ __('frontend.copilot_theory.page_number', ['number' => $index + 1]) }}
                                                </span>
                                                <span class="text-[11px] text-[var(--cp-muted)]">{{ __('frontend.copilot_theory.items_in_category', ['count' => $categoryPages->count()]) }}</span>
                                            </div>
                                            <h3 class="text-base font-semibold text-[var(--cp-fg)] leading-snug">
                                                {{ $page->title }}
                                            </h3>
                                            @if(!empty($page->text))
                                                <p class="text-sm text-[var(--cp-muted)] line-clamp-2">{{ $page->text }}</p>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="relative mt-4 flex items-center justify-between text-sm font-semibold text-pilot-600 dark:text-pilot-400">
                                        <span class="inline-flex items-center gap-1">
                                            {{ __('frontend.copilot_theory.view_material') }}
                                            <svg class="h-4 w-4 transition-transform group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                                            </svg>
                                        </span>
                                        <span class="text-[11px] text-[var(--cp-muted)]">#{{ $index + 1 }}</span>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </section>
                @else
                    <div class="rounded-2xl border border-dashed border-[var(--cp-border)] p-8 text-center text-[var(--cp-muted)]">
                        {{ __('frontend.copilot_theory.empty_category') }}
                    </div>
                @endif

                {{-- Auto-generated tests --}}
                @if(isset($autoGeneratedTests) && $autoGeneratedTests->isNotEmpty())
                    <section class="cp-card p-6">
                        <div class="flex items-center gap-3 mb-5">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-pilot-500 to-pilot-600 text-white shadow-sm">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-lg font-bold text-[var(--cp-fg)]">{{ __('frontend.copilot_theory.practice_tests') }}</h2>
                                <p class="text-xs text-[var(--cp-muted)]">{{ __('frontend.copilot_theory.check_category_knowledge') }}</p>
                            </div>
                        </div>
                        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach($autoGeneratedTests as $test)
                                <x-auto-generated-test-card :test="$test" />
                            @endforeach
                        </div>
                    </section>
                @endif

            </div>
        </div>

        {{-- Mobile floating menu --}}
        <div class="fixed bottom-6 left-1/2 -translate-x-1/2 z-50 lg:hidden" x-data="{ open: false }">
            <button
                @click="open = !open"
                class="flex items-center gap-2 rounded-full bg-pilot-600 px-5 py-3 text-sm font-semibold text-white shadow-lg transition-transform hover:scale-105"
            >
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
                {{ __('frontend.copilot_theory.menu') }}
            </button>
            <div
                x-show="open"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-4"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 translate-y-4"
                @click.away="open = false"
                class="absolute bottom-full left-1/2 -translate-x-1/2 mb-3 w-72 max-h-[60vh] overflow-y-auto rounded-2xl border border-[var(--cp-border)] bg-[var(--cp-surface)] p-4 shadow-xl"
            >
                <div class="space-y-4">
                    <div>
                        <h4 class="text-xs font-bold uppercase tracking-wider text-[var(--cp-muted)] mb-2">{{ __('frontend.copilot_theory.theory_categories') }}</h4>
                        <nav class="space-y-1">
                            @if($categories->isNotEmpty())
                                @include('copilot.theory.partials.nested-category-nav-mobile', [
                                    'categories' => $categories,
                                    'selectedCategory' => $selectedCategory,
                                    'routePrefix' => $routePrefix,
                                ])
                            @endif
                        </nav>
                    </div>
                    @if($categoryPages->isNotEmpty())
                        <div class="border-t border-[var(--cp-border)] pt-4">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-[var(--cp-muted)] mb-2">{{ $selectedCategory->title }}</h4>
                            <nav class="space-y-1">
                                @foreach($categoryPages as $pageItem)
                                    <a
                                        href="{{ localized_route($routePrefix . '.show', [$selectedCategory->slug, $pageItem->slug]) }}"
                                        class="block rounded-lg px-3 py-2 text-sm text-[var(--cp-muted)] hover:bg-pilot-50/80 hover:text-[var(--cp-fg)]"
                                    >
                                        {{ $pageItem->title }}
                                    </a>
                                @endforeach
                            </nav>
                        </div>
                    @endif
                    <div class="border-t border-[var(--cp-border)] pt-4">
                        <a
                            href="{{ localized_route($routePrefix . '.index') }}"
                            class="flex items-center gap-2 rounded-lg bg-pilot-50/80 dark:bg-pilot-900/30 px-3 py-2 text-sm font-medium text-[var(--cp-fg)]"
                        >
                            <svg class="h-4 w-4 text-[var(--cp-muted)]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                            </svg>
                            {{ __('frontend.copilot_theory.all_categories') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
