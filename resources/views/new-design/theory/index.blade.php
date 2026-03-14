@extends('layouts.new-design')

@section('title', $sectionTitle ?? __('public.theory.title'))

@section('content')
@php
    $categoryPages = $categoryPages ?? collect();
    $routePrefix   = $routePrefix ?? 'new-design.theory';
    $totalPages    = $categories->sum(fn($c) => $c->pages_count ?? 0);
@endphp

<div class="space-y-10">

    {{-- ─────────────── HERO ─────────────── --}}
    <header class="relative overflow-hidden rounded-[1.75rem] bg-[linear-gradient(135deg,#1a3d6e_0%,#2f67b1_50%,#1a3d6e_100%)] text-white shadow-panel">
        {{-- Dot pattern --}}
        <div class="pointer-events-none absolute inset-0 opacity-20">
            <svg class="h-full w-full" viewBox="0 0 100 100" preserveAspectRatio="none">
                <defs>
                    <pattern id="nd-theory-dots" width="20" height="20" patternUnits="userSpaceOnUse">
                        <circle cx="2" cy="2" r="1" fill="currentColor" opacity="0.5"/>
                        <circle cx="12" cy="12" r="1.5" fill="currentColor" opacity="0.3"/>
                    </pattern>
                </defs>
                <rect width="100%" height="100%" fill="url(#nd-theory-dots)"/>
            </svg>
        </div>
        {{-- Glow blobs --}}
        <div class="pointer-events-none absolute -top-20 -right-20 h-64 w-64 rounded-full bg-white/10 blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-20 -left-20 h-48 w-48 rounded-full bg-amber/20 blur-3xl"></div>

        <div class="relative px-8 py-12 md:px-12 md:py-16">
            <div class="max-w-3xl">
                {{-- Badges --}}
                <div class="mb-6 flex flex-wrap items-center gap-3">
                    <span class="inline-flex items-center gap-2 rounded-full bg-white/20 px-4 py-2 text-sm font-semibold backdrop-blur-sm">
                        <svg class="h-5 w-5 text-white/80" viewBox="0 0 20 20" fill="currentColor">
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

                <h1 class="font-display mb-6 text-3xl font-extrabold leading-tight tracking-tight md:text-4xl lg:text-5xl">
                    {{ __('public.theory.title') }}
                </h1>
                <p class="mb-8 max-w-2xl text-lg leading-relaxed text-white/85 md:text-xl">
                    {{ __('public.theory.description') }}
                </p>

                <div class="flex flex-wrap gap-3">
                    @if($categories->first())
                        <a
                            href="{{ localized_route($routePrefix . '.category', $categories->first()->slug) }}"
                            class="inline-flex items-center gap-2 rounded-xl bg-white px-6 py-3 text-sm font-bold text-ocean shadow-card transition hover:-translate-y-0.5 hover:shadow-panel"
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
                        class="inline-flex items-center gap-2 rounded-xl bg-white/20 px-6 py-3 text-sm font-bold text-white backdrop-blur-sm transition hover:bg-white/30"
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

    {{-- ─────────────── CATEGORY GRID ─────────────── --}}
    <section id="categories-section" class="scroll-mt-24 space-y-6">
        <div class="text-center">
            <p class="mb-1 text-xs font-semibold uppercase tracking-widest text-ocean">
                {{ __('public.theory.theory_categories') ?? 'Розділи' }}
            </p>
            <h2 class="font-display mb-2 text-2xl font-extrabold text-night md:text-3xl">{{ __('public.theory.topics_to_learn') }}</h2>
            <p class="mx-auto max-w-xl text-sm text-steel">{{ __('public.theory.topics_hint') }}</p>
        </div>

        @php
            $gradients = [
                ['from' => '#2f67b1', 'to' => '#1a3d6e'],
                ['from' => '#1d5fa8', 'to' => '#2d4e7e'],
                ['from' => '#245592', 'to' => '#1e3a6e'],
                ['from' => '#1a4d91', 'to' => '#162e5a'],
                ['from' => '#3373b8', 'to' => '#23508f'],
                ['from' => '#1e5099', 'to' => '#132d63'],
            ];
        @endphp

        <div class="grid gap-5 md:grid-cols-2 lg:grid-cols-3">
            @forelse($categories as $index => $category)
                @php
                    $g         = $gradients[$index % count($gradients)];
                    $hasChildren = $category->relationLoaded('children') && $category->children->isNotEmpty();
                    $hasPages    = $category->relationLoaded('pages') && $category->pages->isNotEmpty();
                @endphp
                <div class="group relative overflow-hidden rounded-2xl border border-line bg-shell transition-all hover:border-ocean hover:shadow-card">
                    {{-- Gradient header --}}
                    <a
                        href="{{ localized_route($routePrefix . '.category', $category->slug) }}"
                        class="relative block min-h-[7rem] p-6 transition-opacity hover:opacity-90"
                        style="background: linear-gradient(135deg, {{ $g['from'] }} 0%, {{ $g['to'] }} 100%)"
                    >
                        <div class="pointer-events-none absolute right-0 top-0 h-20 w-20 translate-x-1/2 -translate-y-1/2 rounded-full bg-white/10"></div>
                        <div class="pointer-events-none absolute bottom-0 left-0 h-16 w-16 -translate-x-1/2 translate-y-1/2 rounded-full bg-white/10"></div>
                        <div class="absolute right-4 top-4 flex h-8 w-8 items-center justify-center rounded-full bg-white/20 text-sm font-bold text-white">
                            {{ $index + 1 }}
                        </div>
                        <div class="flex items-start gap-3">
                            <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-xl bg-white/20 text-white backdrop-blur-sm">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                </svg>
                            </div>
                            <h3 class="pr-10 text-base font-bold leading-snug text-white">{{ $category->title }}</h3>
                        </div>
                    </a>

                    <div class="p-4">
                        <span class="text-xs text-steel">
                            @if(isset($category->pages_count) && $category->pages_count > 0)
                                {{ $category->pages_count }} {{ __('public.theory.lessons_count') }}
                            @else
                                {{ __('public.common.no_lessons') }}
                            @endif
                        </span>

                        {{-- Subcategories --}}
                        @if($hasChildren)
                            <div class="mt-3 border-t border-line pt-3">
                                <ul class="space-y-1.5">
                                    @foreach($category->children as $child)
                                        <li>
                                            <a
                                                href="{{ localized_route($routePrefix . '.category', $child->slug) }}"
                                                class="flex items-center justify-between rounded-xl px-2 py-1 text-sm text-night transition hover:bg-ocean/8 hover:text-ocean"
                                            >
                                                <span class="flex items-center gap-2">
                                                    <svg class="h-3 w-3 text-steel" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                                                    </svg>
                                                    {{ $child->title }}
                                                </span>
                                                @if(isset($child->pages_count) && $child->pages_count > 0)
                                                    <span class="text-xs text-steel">{{ $child->pages_count }}</span>
                                                @endif
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        {{-- Direct pages --}}
                        @if($hasPages)
                            <div class="mt-3 border-t border-line pt-3">
                                <ul class="space-y-1.5">
                                    @foreach($category->pages as $page)
                                        <li>
                                            <a
                                                href="{{ localized_route($routePrefix . '.show', [$category->slug, $page->slug]) }}"
                                                class="flex items-start gap-2 rounded-xl px-2 py-1 text-sm text-night transition hover:bg-ocean/8 hover:text-ocean"
                                            >
                                                <svg class="mt-0.5 h-3 w-3 flex-shrink-0 text-steel" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
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
                                <span class="flex h-8 w-8 items-center justify-center rounded-full bg-ocean/10 text-ocean transition group-hover:bg-ocean group-hover:text-white">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </span>
                            </a>
                        @endif
                    </div>
                </div>
            @empty
                <div class="col-span-full rounded-2xl border border-dashed border-line p-12 text-center">
                    <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-ocean/10 text-ocean">
                        <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                    </div>
                    <h3 class="mb-2 font-display text-lg font-bold text-night">{{ __('public.theory.no_categories_title') }}</h3>
                    <p class="text-steel">{{ __('public.theory.no_categories_hint') }}</p>
                </div>
            @endforelse
        </div>
    </section>

    {{-- ─────────────── LEARNING PATH ─────────────── --}}
    <section class="rounded-[1.75rem] border border-line bg-[radial-gradient(circle_at_top_right,rgba(47,103,177,0.07),transparent_40%),#fffefd] p-8 shadow-card md:p-10">
        <div class="grid gap-8 lg:grid-cols-2 lg:items-center">
            <div>
                <div class="mb-4 inline-flex items-center gap-2 rounded-full bg-ocean/10 px-4 py-2 text-sm font-semibold text-ocean">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    {{ __('public.theory.recommended_path') }}
                </div>
                <h2 class="font-display mb-4 text-2xl font-extrabold text-night md:text-3xl">{{ __('public.theory.how_to_learn') }}</h2>
                <p class="mb-5 leading-relaxed text-steel">{{ __('public.theory.how_to_learn_desc') }}</p>
                <div class="space-y-3">
                    @foreach([
                        ['label' => __('public.theory.step1'), 'desc' => __('public.theory.step1_desc'), 'color' => 'bg-ocean/15 text-ocean'],
                        ['label' => __('public.theory.step2'), 'desc' => __('public.theory.step2_desc'), 'color' => 'bg-amber/20 text-amber-700'],
                        ['label' => __('public.theory.step3'), 'desc' => __('public.theory.step3_desc'), 'color' => 'bg-ocean/10 text-ocean'],
                        ['label' => __('public.theory.step4'), 'desc' => __('public.theory.step4_desc'), 'color' => 'bg-night/10 text-night'],
                    ] as $i => $step)
                        <div class="flex items-start gap-3">
                            <div class="mt-0.5 flex h-6 w-6 flex-shrink-0 items-center justify-center rounded-full {{ $step['color'] }} text-xs font-bold">{{ $i + 1 }}</div>
                            <p class="text-sm text-night"><strong>{{ $step['label'] }}</strong> — {{ $step['desc'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Stats grid --}}
            <div class="grid gap-4 sm:grid-cols-2">
                <div class="nd-card p-6 text-center">
                    <div class="font-display mb-2 text-4xl font-extrabold text-ocean">{{ $categories->count() }}</div>
                    <p class="text-sm text-steel">{{ __('public.theory.theory_categories') }}</p>
                </div>
                <div class="nd-card p-6 text-center">
                    <div class="font-display mb-2 text-4xl font-extrabold text-night">{{ $totalPages }}</div>
                    <p class="text-sm text-steel">{{ __('public.theory.lesson_pages') }}</p>
                </div>
                <div class="nd-card p-6 text-center">
                    <div class="font-display mb-2 text-4xl font-extrabold text-ocean">A1-B2</div>
                    <p class="text-sm text-steel">{{ __('public.theory.difficulty_levels') }}</p>
                </div>
                <div class="nd-card p-6 text-center">
                    <div class="mb-2 text-4xl" role="img" aria-label="{{ __('public.theory.in_ukrainian') }}">🇺🇦</div>
                    <p class="text-sm text-steel">{{ __('public.theory.in_ukrainian') }}</p>
                </div>
            </div>
        </div>
    </section>

    {{-- ─────────────── MOBILE FLOATING MENU ─────────────── --}}
    <div class="fixed bottom-6 left-1/2 z-50 -translate-x-1/2 lg:hidden" x-data="{ open: false }">
        <button
            @click="open = !open"
            class="flex items-center gap-2 rounded-full bg-ocean px-6 py-3 text-sm font-bold text-white shadow-card transition-transform hover:scale-105"
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
            class="absolute bottom-full left-1/2 mb-3 w-80 max-h-[60vh] -translate-x-1/2 overflow-y-auto rounded-2xl border border-line bg-shell p-5 shadow-panel"
        >
            <div class="space-y-4">
                <div class="flex items-center justify-between border-b border-line pb-3">
                    <h4 class="font-display font-bold text-night">{{ __('public.theory.theory_categories_mobile') }}</h4>
                    <span class="text-xs text-steel">{{ $categories->count() }} {{ __('public.theory.categories_count') }}</span>
                </div>
                <nav class="space-y-2">
                    @forelse($categories as $category)
                        <div>
                            <a
                                href="{{ localized_route($routePrefix . '.category', $category->slug) }}"
                                class="flex items-center justify-between rounded-xl bg-ocean/8 px-4 py-2.5 text-sm font-medium text-night transition hover:bg-ocean/15 hover:text-ocean"
                            >
                                <span>{{ $category->title }}</span>
                                @if(isset($category->pages_count) && $category->pages_count > 0)
                                    <span class="text-xs text-steel">{{ $category->pages_count }}</span>
                                @endif
                            </a>
                            @if($category->relationLoaded('children') && $category->children->isNotEmpty())
                                <div class="ml-4 mt-1 space-y-1">
                                    @foreach($category->children as $child)
                                        <a
                                            href="{{ localized_route($routePrefix . '.category', $child->slug) }}"
                                            class="flex items-center justify-between rounded-lg px-3 py-1.5 text-xs text-steel transition hover:bg-ocean/8 hover:text-ocean"
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
                        <p class="py-4 text-center text-sm text-steel">{{ __('public.common.no_categories') }}</p>
                    @endforelse
                </nav>
            </div>
        </div>
    </div>

</div>
@endsection
