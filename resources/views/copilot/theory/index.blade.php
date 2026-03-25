@extends('layouts.copilot')

@section('title', $sectionTitle ?? __('public.theory.title'))

@section('breadcrumb')
    <nav class="flex items-center gap-1.5 text-xs text-[var(--cp-muted)]" aria-label="{{ __('public.common.breadcrumb') }}">
        <a href="{{ localized_route('copilot.index') }}" class="hover:text-pilot-600 transition-colors">{{ __('public.common.home') }}</a>
        <span>/</span>
        <span class="font-medium text-[var(--cp-fg)]">{{ $sectionTitle ?? __('public.theory.title') }}</span>
    </nav>
@endsection

@section('content')
    @php
        $categoryPages = $categoryPages ?? collect();
        $routePrefix = $routePrefix ?? 'copilot.theory';
        $categoryDescription = $categoryDescription ?? ['hasBlocks' => false];
        $totalPages = $categories->sum(fn($c) => $c->pages_count ?? 0);
    @endphp

    <div class="space-y-10">
        {{-- ── Hero ──────────────────────────────────────────────────────── --}}
        <header class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-pilot-600 via-pilot-700 to-pilot-900 text-white">
            {{-- Decorative dot pattern --}}
            <div class="absolute inset-0 opacity-20">
                <svg class="h-full w-full" viewBox="0 0 100 100" preserveAspectRatio="none">
                    <defs>
                        <pattern id="cp-theory-dots" width="20" height="20" patternUnits="userSpaceOnUse">
                            <circle cx="2" cy="2" r="1" fill="currentColor" opacity="0.5"/>
                            <circle cx="12" cy="12" r="1.5" fill="currentColor" opacity="0.3"/>
                        </pattern>
                    </defs>
                    <rect width="100%" height="100%" fill="url(#cp-theory-dots)"/>
                </svg>
            </div>
            <div class="absolute -top-20 -right-20 w-64 h-64 bg-white/10 rounded-full blur-3xl"></div>
            <div class="absolute -bottom-20 -left-20 w-48 h-48 bg-white/10 rounded-full blur-3xl"></div>

            <div class="relative px-8 py-12 md:px-12 md:py-16">
                <div class="max-w-3xl">
                    {{-- Badges --}}
                    <div class="flex flex-wrap items-center gap-3 mb-6">
                        <span class="inline-flex items-center gap-2 rounded-full bg-white/20 backdrop-blur-sm px-4 py-2 text-sm font-semibold">
                            <svg class="h-5 w-5 text-pilot-200" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838L7.667 9.088l1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3zM3.31 9.397L5 10.12v4.102a8.969 8.969 0 00-1.05-.174 1 1 0 01-.89-.89 11.115 11.115 0 01.25-3.762zM9.3 16.573A9.026 9.026 0 007 14.935v-3.957l1.818.78a3 3 0 002.364 0l5.508-2.361a11.026 11.026 0 01.25 3.762 1 1 0 01-.89.89 8.968 8.968 0 00-5.35 2.524 1 1 0 01-1.4 0zM6 18a1 1 0 001-1v-2.065a8.935 8.935 0 00-2-.712V17a1 1 0 001 1z"/>
                            </svg>
                            {{ __('public.theory.section_badge') }}
                        </span>
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-white/15 px-3 py-1.5 text-xs font-medium">
                            {{ $categories->count() }} {{ __('public.theory.categories_count') }}
                        </span>
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-white/15 px-3 py-1.5 text-xs font-medium">
                            {{ $totalPages }} {{ __('public.theory.lessons_count') }}
                        </span>
                    </div>

                    <h1 class="text-3xl md:text-4xl lg:text-5xl font-black tracking-tight leading-tight mb-6">
                        {{ __('public.theory.title') }}
                    </h1>
                    <p class="text-lg md:text-xl text-white/85 leading-relaxed mb-8 max-w-2xl">
                        {{ __('public.theory.description') }}
                    </p>

                    <div class="flex flex-wrap gap-3">
                        @if($categories->first())
                            <a
                                href="{{ localized_route($routePrefix . '.category', $categories->first()->slug) }}"
                                class="inline-flex items-center gap-2 rounded-xl bg-white px-6 py-3 text-sm font-bold text-pilot-700 shadow-lg transition hover:-translate-y-0.5 hover:shadow-xl"
                            >
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                {{ __('public.theory.start_learning') }}
                            </a>
                        @endif
                        <a
                            href="#categories-section"
                            class="inline-flex items-center gap-2 rounded-xl bg-white/20 backdrop-blur-sm px-6 py-3 text-sm font-bold text-white transition hover:bg-white/30"
                        >
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                            </svg>
                            {{ __('public.theory.all_categories') }}
                        </a>
                    </div>
                </div>
            </div>
        </header>

        {{-- ── Category grid ────────────────────────────────────────────── --}}
        <section id="categories-section" class="scroll-mt-24 space-y-6">
            <div class="text-center">
                <p class="text-xs uppercase font-semibold tracking-widest text-pilot-600 dark:text-pilot-400 mb-1">{{ __('public.theory.sections') }}</p>
                <h2 class="text-2xl md:text-3xl font-bold text-[var(--cp-fg)] mb-2">{{ __('public.theory.topics_to_learn') }}</h2>
                <p class="text-[var(--cp-muted)] max-w-xl mx-auto text-sm">{{ __('public.theory.topics_hint') }}</p>
            </div>

            @php
                $gradients = [
                    'from-pilot-500 to-pilot-700',
                    'from-teal-500 to-emerald-600',
                    'from-cyan-500 to-pilot-600',
                    'from-pilot-600 to-teal-700',
                    'from-emerald-500 to-teal-600',
                    'from-teal-600 to-cyan-700',
                ];
            @endphp

            <div class="grid gap-5 md:grid-cols-2 lg:grid-cols-3">
                @forelse($categories as $index => $category)
                    @php
                        $gradient = $gradients[$index % count($gradients)];
                        $hasChildren = $category->relationLoaded('children') && $category->children->isNotEmpty();
                        $hasPages = $category->relationLoaded('pages') && $category->pages->isNotEmpty();
                    @endphp
                    <div class="group relative overflow-hidden rounded-2xl border border-[var(--cp-border)] bg-[var(--cp-surface)] transition-all hover:border-pilot-400 hover:shadow-card">
                        {{-- Gradient header --}}
                        <a
                            href="{{ localized_route($routePrefix . '.category', $category->slug) }}"
                            class="block relative min-h-[7rem] bg-gradient-to-br {{ $gradient }} p-6 transition-opacity hover:opacity-90"
                        >
                            <div class="absolute top-0 right-0 w-20 h-20 bg-white/10 rounded-full -translate-y-1/2 translate-x-1/2"></div>
                            <div class="absolute bottom-0 left-0 w-16 h-16 bg-white/10 rounded-full translate-y-1/2 -translate-x-1/2"></div>
                            <div class="absolute top-4 right-4 flex h-8 w-8 items-center justify-center rounded-full bg-white/20 text-white text-sm font-bold">
                                {{ $index + 1 }}
                            </div>
                            <div class="flex items-start gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/20 backdrop-blur-sm text-white flex-shrink-0">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                    </svg>
                                </div>
                                <h3 class="text-base font-bold text-white pr-10 leading-snug">{{ $category->title }}</h3>
                            </div>
                        </a>

                        <div class="p-4">
                            <span class="text-xs text-[var(--cp-muted)]">
                                @if(isset($category->pages_count) && $category->pages_count > 0)
                                    {{ $category->pages_count }} {{ __('public.theory.lessons_count') }}
                                @else
                                    {{ __('public.common.no_lessons') }}
                                @endif
                            </span>

                            {{-- Subcategories --}}
                            @if($hasChildren)
                                <div class="mt-3 pt-3 border-t border-[var(--cp-border)]">
                                    <ul class="space-y-1.5">
                                        @foreach($category->children as $child)
                                            <li>
                                                <a
                                                    href="{{ localized_route($routePrefix . '.category', $child->slug) }}"
                                                    class="flex items-center justify-between text-sm text-[var(--cp-fg)] hover:text-pilot-600 transition-colors py-1 px-2 rounded-lg hover:bg-pilot-50/80 dark:hover:bg-pilot-900/30"
                                                >
                                                    <span class="flex items-center gap-2">
                                                        <svg class="h-3 w-3 text-[var(--cp-muted)]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                                                        </svg>
                                                        {{ $child->title }}
                                                    </span>
                                                    @if(isset($child->pages_count) && $child->pages_count > 0)
                                                        <span class="text-xs text-[var(--cp-muted)]">{{ $child->pages_count }}</span>
                                                    @endif
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            {{-- Direct pages --}}
                            @if($hasPages)
                                <div class="mt-3 pt-3 border-t border-[var(--cp-border)]">
                                    <ul class="space-y-1.5">
                                        @foreach($category->pages as $page)
                                            <li>
                                                <a
                                                    href="{{ localized_route($routePrefix . '.show', [$category->slug, $page->slug]) }}"
                                                    class="flex items-start gap-2 text-sm text-[var(--cp-fg)] hover:text-pilot-600 transition-colors py-1 px-2 rounded-lg hover:bg-pilot-50/80 dark:hover:bg-pilot-900/30"
                                                >
                                                    <svg class="h-3 w-3 text-[var(--cp-muted)] mt-1 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                    </svg>
                                                    <span class="line-clamp-2 break-words">{{ $page->title }}</span>
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            @if(!$hasChildren && !$hasPages)
                                <a href="{{ localized_route($routePrefix . '.category', $category->slug) }}" class="mt-3 flex justify-end">
                                    <span class="flex h-8 w-8 items-center justify-center rounded-full bg-pilot-50 dark:bg-pilot-900/30 text-pilot-600 group-hover:bg-pilot-100 transition-all">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                                        </svg>
                                    </span>
                                </a>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="col-span-full rounded-2xl border border-dashed border-[var(--cp-border)] p-12 text-center">
                        <div class="flex justify-center mb-4">
                            <div class="flex h-16 w-16 items-center justify-center rounded-full bg-pilot-50 dark:bg-pilot-900/30 text-pilot-400">
                                <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                </svg>
                            </div>
                        </div>
                        <h3 class="text-lg font-semibold text-[var(--cp-fg)] mb-2">{{ __('public.theory.no_categories_title') }}</h3>
                        <p class="text-[var(--cp-muted)]">{{ __('public.theory.no_categories_hint') }}</p>
                    </div>
                @endforelse
            </div>
        </section>

        {{-- ── Learning path ────────────────────────────────────────────── --}}
        <section class="rounded-3xl border border-[var(--cp-border)] bg-[var(--cp-surface)] p-8 md:p-10 shadow-panel">
            <div class="grid gap-8 lg:grid-cols-2 lg:items-center">
                <div>
                    <div class="inline-flex items-center gap-2 rounded-full bg-pilot-50 dark:bg-pilot-900/40 text-pilot-700 dark:text-pilot-300 px-4 py-2 text-sm font-semibold mb-4">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        {{ __('public.theory.recommended_path') }}
                    </div>
                    <h2 class="text-2xl md:text-3xl font-bold text-[var(--cp-fg)] mb-4">{{ __('public.theory.how_to_learn') }}</h2>
                    <p class="text-[var(--cp-muted)] leading-relaxed mb-5">{{ __('public.theory.how_to_learn_desc') }}</p>
                    <div class="space-y-3">
                        @foreach([
                            ['color' => 'bg-pilot-500/20 text-pilot-700 dark:text-pilot-300', 'title' => __('public.theory.step1'), 'desc' => __('public.theory.step1_desc')],
                            ['color' => 'bg-teal-500/20 text-teal-700 dark:text-teal-300', 'title' => __('public.theory.step2'), 'desc' => __('public.theory.step2_desc')],
                            ['color' => 'bg-emerald-500/20 text-emerald-700 dark:text-emerald-300', 'title' => __('public.theory.step3'), 'desc' => __('public.theory.step3_desc')],
                            ['color' => 'bg-cyan-500/20 text-cyan-700 dark:text-cyan-300', 'title' => __('public.theory.step4'), 'desc' => __('public.theory.step4_desc')],
                        ] as $i => $step)
                        <div class="flex items-start gap-3">
                            <div class="flex h-6 w-6 items-center justify-center rounded-full {{ $step['color'] }} text-xs font-bold flex-shrink-0 mt-0.5">{{ $i + 1 }}</div>
                            <p class="text-sm text-[var(--cp-fg)]"><strong>{{ $step['title'] }}</strong> — {{ $step['desc'] }}</p>
                        </div>
                        @endforeach
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="cp-card p-6 text-center">
                        <div class="text-4xl font-black text-pilot-600 dark:text-pilot-400 mb-2">{{ $categories->count() }}</div>
                        <p class="text-sm text-[var(--cp-muted)]">{{ __('public.theory.theory_categories') }}</p>
                    </div>
                    <div class="cp-card p-6 text-center">
                        <div class="text-4xl font-black text-pilot-700 dark:text-pilot-300 mb-2">{{ $totalPages }}</div>
                        <p class="text-sm text-[var(--cp-muted)]">{{ __('public.theory.lesson_pages') }}</p>
                    </div>
                    <div class="cp-card p-6 text-center">
                        <div class="text-4xl font-black text-teal-500 mb-2">A1-B2</div>
                        <p class="text-sm text-[var(--cp-muted)]">{{ __('public.theory.difficulty_levels') }}</p>
                    </div>
                    <div class="cp-card p-6 text-center">
                        <div class="text-4xl font-black text-amber-500 mb-2" role="img" aria-label="{{ __('public.theory.in_ukrainian') }}">🇺🇦</div>
                        <p class="text-sm text-[var(--cp-muted)]">{{ __('public.theory.in_ukrainian') }}</p>
                    </div>
                </div>
            </div>
        </section>

        {{-- Mobile floating menu --}}
        <div class="fixed bottom-6 left-1/2 -translate-x-1/2 z-50 lg:hidden" x-data="{ open: false }">
            <button
                @click="open = !open"
                class="flex items-center gap-2 rounded-full bg-pilot-600 px-6 py-3 text-sm font-semibold text-white shadow-lg transition-transform hover:scale-105"
            >
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
                {{ __('public.theory.mobile_categories') }}
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
                class="absolute bottom-full left-1/2 -translate-x-1/2 mb-3 w-80 max-h-[60vh] overflow-y-auto rounded-2xl border border-[var(--cp-border)] bg-[var(--cp-surface)] p-5 shadow-xl"
            >
                <div class="space-y-4">
                    <div class="flex items-center justify-between border-b border-[var(--cp-border)] pb-3">
                        <h4 class="font-bold text-[var(--cp-fg)]">{{ __('public.theory.theory_categories_mobile') }}</h4>
                        <span class="text-xs text-[var(--cp-muted)]">{{ $categories->count() }} {{ __('public.theory.categories_count') }}</span>
                    </div>
                    <nav class="space-y-2">
                        @forelse($categories as $category)
                            <div>
                                <a
                                    href="{{ localized_route($routePrefix . '.category', $category->slug) }}"
                                    class="flex items-center justify-between rounded-xl bg-pilot-50/80 dark:bg-pilot-900/30 px-4 py-2.5 text-sm font-medium text-[var(--cp-fg)] transition hover:bg-pilot-100 dark:hover:bg-pilot-900/50 hover:text-pilot-700 dark:hover:text-pilot-300"
                                >
                                    <span>{{ $category->title }}</span>
                                    @if(isset($category->pages_count) && $category->pages_count > 0)
                                        <span class="text-xs text-[var(--cp-muted)]">{{ $category->pages_count }}</span>
                                    @endif
                                </a>
                                @if($category->relationLoaded('children') && $category->children->isNotEmpty())
                                    <div class="ml-4 space-y-1 mt-1">
                                        @foreach($category->children as $child)
                                            <a
                                                href="{{ localized_route($routePrefix . '.category', $child->slug) }}"
                                                class="flex items-center justify-between rounded-lg px-3 py-1.5 text-xs text-[var(--cp-muted)] transition hover:bg-pilot-50/80 hover:text-pilot-600"
                                            >
                                                <span class="flex items-center gap-1.5">
                                                    <svg class="h-2.5 w-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                                                    </svg>
                                                    {{ $child->title }}
                                                </span>
                                                @if(isset($child->pages_count) && $child->pages_count > 0)
                                                    <span>{{ $child->pages_count }}</span>
                                                @endif
                                            </a>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @empty
                            <p class="text-sm text-[var(--cp-muted)] text-center py-4">{{ __('public.common.no_categories') }}</p>
                        @endforelse
                    </nav>
                </div>
            </div>
        </div>
    </div>
@endsection
