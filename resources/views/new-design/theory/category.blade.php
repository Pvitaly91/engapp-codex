@extends('layouts.new-design')

@section('title', ($selectedCategory->title ?? 'Категорія') . ' — ' . ($sectionTitle ?? 'Теорія'))

@section('content')
@php
    $categoryPages       = $categoryPages ?? collect();
    $routePrefix         = $routePrefix ?? 'new-design.theory';
    $categoryDescription = $categoryDescription ?? ['hasBlocks' => false];
@endphp

<div class="space-y-8">

    {{-- ─────────────── CATEGORY HERO ─────────────── --}}
    <header class="relative overflow-hidden rounded-[1.75rem] border border-line bg-[radial-gradient(circle_at_top_left,rgba(47,103,177,0.10),transparent_40%),#f5fbff] shadow-panel">
        {{-- Grid pattern --}}
        <div class="pointer-events-none absolute inset-0 opacity-30">
            <svg class="h-full w-full text-ocean/20" viewBox="0 0 100 100" preserveAspectRatio="none">
                <defs>
                    <pattern id="nd-cat-grid" width="10" height="10" patternUnits="userSpaceOnUse">
                        <path d="M 10 0 L 0 0 0 10" fill="none" stroke="currentColor" stroke-width="0.5"/>
                    </pattern>
                </defs>
                <rect width="100%" height="100%" fill="url(#nd-cat-grid)"/>
            </svg>
        </div>
        {{-- Amber accent blob --}}
        <div class="pointer-events-none absolute -right-8 -top-8 h-32 w-32 rounded-full bg-amber/15 blur-2xl"></div>

        <div class="relative px-6 py-8 md:px-8 md:py-10">
            <div class="mb-4 flex items-center gap-3">
                <span class="inline-flex items-center gap-1.5 rounded-full bg-ocean/10 px-3 py-1.5 text-xs font-bold tracking-wide text-ocean shadow-sm">
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"/>
                    </svg>
                    Категорія
                </span>
                <span class="h-1 w-1 rounded-full bg-line"></span>
                <span class="inline-flex items-center gap-1.5 rounded-full bg-ocean/8 px-3 py-1.5 text-xs font-bold tracking-wide text-ocean">
                    {{ $categoryPages->count() }} {{ trans_choice('сторінок|сторінка|сторінки', $categoryPages->count()) }}
                </span>
            </div>

            <h1 class="font-display mb-4 text-2xl font-extrabold leading-tight tracking-tight text-night md:text-3xl lg:text-4xl">
                {{ $selectedCategory->title }}
            </h1>
            <p class="max-w-3xl text-base leading-relaxed text-steel md:text-lg">
                Матеріали з категорії «{{ $selectedCategory->title }}». Обери сторінку, щоб почати вивчення.
            </p>

            <div class="mt-6">
                <a
                    href="{{ localized_route($routePrefix . '.index') }}"
                    class="inline-flex items-center gap-1.5 rounded-xl bg-ocean px-4 py-2 text-sm font-semibold text-white shadow-card transition hover:bg-[#245592]"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Усі категорії
                </a>
            </div>
        </div>
    </header>

    @include('new-design.theory.partials.sidebar-navigation-mobile', [
        'categories'       => $categories,
        'selectedCategory' => $selectedCategory,
        'categoryPages'    => $categoryPages,
        'currentPage'      => null,
        'routePrefix'      => $routePrefix,
    ])

    {{-- ─────────────── MAIN GRID ─────────────── --}}
    <div class="grid grid-cols-1 gap-8 lg:grid-cols-[280px_minmax(0,1fr)] xl:grid-cols-[300px_minmax(0,1fr)]">

        {{-- ── Desktop sidebar ── --}}
        <aside class="hidden lg:block">
            <div class="sticky top-24 max-h-[calc(100vh-7rem)] space-y-4 overflow-y-auto pr-1">

                {{-- Categories nav --}}
                <div class="nd-card p-5">
                    <h3 class="mb-4 flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-steel">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                        Категорії
                    </h3>
                    <nav class="space-y-1 overflow-y-auto pr-1">
                        @if($categories->isNotEmpty())
                            @include('new-design.theory.partials.nested-category-nav', [
                                'categories'       => $categories,
                                'selectedCategory' => $selectedCategory,
                                'routePrefix'      => $routePrefix,
                            ])
                        @else
                            <p class="text-sm text-steel">Немає категорій.</p>
                        @endif
                    </nav>
                </div>

                {{-- Tags --}}
                @if(isset($selectedCategory) && $selectedCategory->tags->isNotEmpty())
                    <div class="nd-card p-5" x-data="{ show: false }">
                        <button @click="show = !show" class="flex w-full items-center justify-between text-left">
                            <h3 class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-steel">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                </svg>
                                Теги ({{ $selectedCategory->tags->count() }})
                            </h3>
                            <svg class="h-4 w-4 text-steel transition-transform" :class="{ 'rotate-180': show }" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div x-show="show" x-collapse class="mt-4">
                            <div class="flex flex-wrap gap-1.5">
                                @foreach($selectedCategory->tags as $tag)
                                    <span class="inline-flex items-center rounded-lg bg-ocean/8 px-2 py-1 text-xs text-steel">
                                        {{ $tag->name }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Quick actions --}}
                <div class="nd-card p-5">
                    <h3 class="mb-4 flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-steel">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        Швидкі дії
                    </h3>
                    <a
                        href="{{ localized_route($routePrefix . '.index') }}"
                        class="flex items-center gap-3 rounded-xl border border-line bg-mist px-4 py-3 text-sm font-medium text-night transition hover:border-ocean hover:shadow-sm"
                    >
                        <svg class="h-5 w-5 text-steel" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                        </svg>
                        Усі категорії
                    </a>
                </div>
            </div>
        </aside>

        {{-- ── Primary content ── --}}
        <div class="min-w-0 space-y-6">

            {{-- Category description blocks --}}
            @if($categoryDescription['hasBlocks'] ?? false)
                @include('engram.theory.partials.category-description-v3', [
                    'page'                => $selectedCategory,
                    'categoryDescription' => $categoryDescription,
                ])
            @endif

            {{-- Pages grid --}}
            @if($categoryPages->isNotEmpty())
                <section class="rounded-[1.75rem] border border-line bg-shell p-6 shadow-card">
                    <div class="mb-8 text-center">
                        <h2 class="font-display mb-2 text-xl font-extrabold text-night md:text-2xl">Матеріали для вивчення</h2>
                        <p class="mx-auto max-w-xl text-sm text-steel">
                            Обери тему та почни вивчати матеріали з категорії «{{ $selectedCategory->title }}»
                        </p>
                    </div>

                    @php
                        $palette = [
                            ['icon' => 'bg-ocean/10 text-ocean', 'pill' => 'bg-ocean/8 text-ocean'],
                            ['icon' => 'bg-amber/15 text-amber-700', 'pill' => 'bg-amber/10 text-amber-700'],
                            ['icon' => 'bg-night/8 text-night', 'pill' => 'bg-night/6 text-night'],
                            ['icon' => 'bg-ocean/15 text-ocean', 'pill' => 'bg-ocean/10 text-ocean'],
                        ];
                    @endphp

                    <div class="grid gap-4 md:grid-cols-2">
                        @foreach($categoryPages as $index => $page)
                            @php($colors = $palette[$index % count($palette)])
                            <a
                                href="{{ localized_route($routePrefix . '.show', [$selectedCategory->slug, $page->slug]) }}"
                                class="group relative block overflow-hidden rounded-2xl border border-line bg-mist p-5 shadow-sm transition-all hover:border-ocean hover:shadow-card"
                            >
                                <div class="pointer-events-none absolute inset-0 bg-gradient-to-br from-ocean/5 to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100"></div>
                                <div class="relative flex items-start gap-4">
                                    <div class="flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-xl {{ $colors['icon'] }}">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                    </div>
                                    <div class="flex-1 space-y-1.5">
                                        <div class="flex items-center gap-2">
                                            <span class="inline-flex items-center rounded-full {{ $colors['pill'] }} px-2.5 py-0.5 text-[11px] font-semibold">
                                                Сторінка {{ $index + 1 }}
                                            </span>
                                            <span class="text-[11px] text-steel">{{ $categoryPages->count() }} у категорії</span>
                                        </div>
                                        <h3 class="text-base font-semibold leading-snug text-night">{{ $page->title }}</h3>
                                        @if(!empty($page->text))
                                            <p class="line-clamp-2 text-sm text-steel">{{ $page->text }}</p>
                                        @endif
                                    </div>
                                </div>
                                <div class="relative mt-4 flex items-center justify-between text-sm font-semibold text-ocean">
                                    <span class="inline-flex items-center gap-1">
                                        Переглянути матеріал
                                        <svg class="h-4 w-4 transition-transform group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                                        </svg>
                                    </span>
                                    <span class="text-[11px] text-steel">#{{ $index + 1 }}</span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </section>
            @else
                <div class="rounded-2xl border border-dashed border-line p-8 text-center text-steel">
                    Поки що в цій категорії немає сторінок теорії.
                </div>
            @endif

            {{-- Auto-generated tests --}}
            @if(isset($autoGeneratedTests) && $autoGeneratedTests->isNotEmpty())
                <section class="nd-card p-6">
                    <div class="mb-5 flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-ocean text-white shadow-card">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="font-display text-lg font-bold text-night">Практичні тести</h2>
                            <p class="text-xs text-steel">Перевір свої знання з цієї категорії</p>
                        </div>
                    </div>
                    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach($autoGeneratedTests as $test)
                            <x-auto-generated-test-card :test="$test" />
                        @endforeach
                    </div>
                </section>
            @endif

            {{-- Related tests --}}
            @if(isset($relatedTests) && $relatedTests->isNotEmpty())
                <section class="nd-card p-6" x-data="{ show: false }">
                    <button @click="show = !show" class="flex w-full items-center justify-between text-left">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-ocean/10 text-ocean">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                                </svg>
                            </div>
                            <div>
                                <h2 class="font-display text-lg font-bold text-night">Пов'язані тести</h2>
                                <p class="text-xs text-steel">{{ $relatedTests->count() }} тестів за тегами</p>
                            </div>
                        </div>
                        <svg class="h-5 w-5 text-steel transition-transform" :class="{ 'rotate-180': show }" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div x-show="show" x-collapse class="mt-5">
                        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach($relatedTests as $test)
                                <x-related-test-card :test="$test" />
                            @endforeach
                        </div>
                    </div>
                </section>
            @endif
        </div>
    </div>

    {{-- ─────────────── MOBILE FLOATING MENU ─────────────── --}}
    <div class="fixed bottom-6 left-1/2 z-50 -translate-x-1/2 lg:hidden" x-data="{ open: false }">
        <button
            @click="open = !open"
            class="flex items-center gap-2 rounded-full bg-ocean px-5 py-3 text-sm font-bold text-white shadow-card transition-transform hover:scale-105"
        >
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
            Меню
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
            class="absolute bottom-full left-1/2 mb-3 w-72 max-h-[60vh] -translate-x-1/2 overflow-y-auto rounded-2xl border border-line bg-shell p-4 shadow-panel"
        >
            <div class="space-y-4">
                <div>
                    <h4 class="mb-2 text-xs font-bold uppercase tracking-wider text-steel">Категорії теорії</h4>
                    <nav class="space-y-1">
                        @if($categories->isNotEmpty())
                            @include('new-design.theory.partials.nested-category-nav-mobile', [
                                'categories'       => $categories,
                                'selectedCategory' => $selectedCategory,
                                'routePrefix'      => $routePrefix,
                            ])
                        @endif
                    </nav>
                </div>
                @if($categoryPages->isNotEmpty())
                    <div class="border-t border-line pt-4">
                        <h4 class="mb-2 text-xs font-bold uppercase tracking-wider text-steel">{{ $selectedCategory->title }}</h4>
                        <nav class="space-y-1">
                            @foreach($categoryPages as $pageItem)
                                <a
                                    href="{{ localized_route($routePrefix . '.show', [$selectedCategory->slug, $pageItem->slug]) }}"
                                    class="block rounded-xl px-3 py-2 text-sm text-steel hover:bg-ocean/8 hover:text-ocean"
                                >
                                    {{ $pageItem->title }}
                                </a>
                            @endforeach
                        </nav>
                    </div>
                @endif
                <div class="border-t border-line pt-4">
                    <a
                        href="{{ localized_route($routePrefix . '.index') }}"
                        class="flex items-center gap-2 rounded-xl bg-ocean/8 px-3 py-2 text-sm font-medium text-night"
                    >
                        <svg class="h-4 w-4 text-steel" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                        </svg>
                        Усі категорії
                    </a>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
