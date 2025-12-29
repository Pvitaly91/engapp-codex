@extends('layouts.engram')

@section('title', ($selectedCategory->title ?? 'Категорія') . ' — ' . ($sectionTitle ?? 'Теорія'))

@section('content')
    @php($categoryPages = $categoryPages ?? collect())
    @php($routePrefix = $routePrefix ?? 'theory')
    @php($categoryDescription = $categoryDescription ?? ['hasBlocks' => false])

    <div class="min-h-screen">
        {{-- Breadcrumb Navigation --}}
        <nav class="mb-6 flex items-center gap-1.5 text-xs text-muted-foreground" aria-label="Breadcrumb">
            <a href="{{ localized_route('home') }}" class="hover:text-brand-600 transition-colors">
                Головна
            </a>
            <span class="text-border">/</span>
            <a href="{{ localized_route($routePrefix . '.index') }}" class="hover:text-brand-600 transition-colors">
                {{ $sectionTitle ?? 'Теорія' }}
            </a>
            <span class="text-border">/</span>
            <span class="font-medium text-foreground truncate max-w-[180px]">{{ $selectedCategory->title }}</span>
        </nav>

        {{-- Hero Section for Category --}}
        <header class="mb-8 space-y-4">
            <span class="inline-flex items-center gap-2 rounded-full bg-brand-50 px-4 py-2 text-xs font-semibold text-brand-700">
                {{ $sectionTitle ?? 'Теорія' }} / {{ $selectedCategory->title }}
            </span>

            <h1 class="text-2xl md:text-3xl lg:text-4xl font-bold tracking-tight leading-tight">
                {{ $selectedCategory->title }}
            </h1>

            <p class="text-base md:text-lg text-[var(--muted)] leading-relaxed max-w-3xl">
                Матеріали з категорії «{{ $selectedCategory->title }}». Обери сторінку, щоб почати вивчення.
            </p>

            {{-- Quick navigation back --}}
            <div class="flex flex-wrap gap-2">
                <a 
                    href="{{ localized_route($routePrefix . '.index') }}"
                    class="inline-flex items-center gap-1.5 rounded-full border border-[var(--border)] bg-[var(--card)] px-4 py-2 text-sm font-medium hover:border-brand-500"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Усі категорії
                </a>
            </div>
        </header>

        @include('engram.theory.partials.sidebar-navigation-mobile', [
            'categories' => $categories,
            'selectedCategory' => $selectedCategory,
            'categoryPages' => $categoryPages,
            'currentPage' => null,
            'routePrefix' => $routePrefix,
        ])

        {{-- Main Content Grid --}}
        <div class="grid grid-cols-1 gap-8 lg:grid-cols-[280px_minmax(0,1fr)] xl:grid-cols-[320px_minmax(0,1fr)]">
            {{-- Left Sidebar --}}
            <aside class="hidden lg:block">
                <div id="theory-sidebar" class="sticky top-24 space-y-5 transition-[top] duration-200 max-h-[calc(100vh-7rem)] overflow-y-auto pr-1">
                    {{-- Categories Navigation --}}
                    <div class="rounded-2xl border border-[var(--border)] bg-[var(--card)] p-5 shadow-sm">
                        <h3 class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-[var(--muted)] mb-4">
                            Категорії
                        </h3>
                        <nav id="category-nav-scroll" class="space-y-1 overflow-y-auto pr-1">
                            @if($categories->isNotEmpty())
                                @include('engram.theory.partials.nested-category-nav', [
                                    'categories' => $categories,
                                    'selectedCategory' => $selectedCategory,
                                    'routePrefix' => $routePrefix,
                                ])
                            @else
                                <p class="text-sm text-[var(--muted)]">Немає категорій.</p>
                            @endif
                        </nav>
                    </div>

                    {{-- Tags Section --}}
                    @if($selectedCategory->tags->isNotEmpty())
                        <div class="rounded-2xl border border-[var(--border)] bg-[var(--card)] p-5 shadow-sm" x-data="{ show: false }">
                            <button 
                                @click="show = !show"
                                class="flex w-full items-center justify-between text-left"
                            >
                                <h3 class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-[var(--muted)]">
                                    Теги ({{ $selectedCategory->tags->count() }})
                                </h3>
                                <svg 
                                    class="h-4 w-4 text-[var(--muted)] transition-transform" 
                                    :class="{ 'rotate-180': show }"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                            <div x-show="show" x-collapse class="mt-4">
                                <div class="flex flex-wrap gap-1.5">
                                    @foreach($selectedCategory->tags as $tag)
                                        <span class="inline-flex items-center rounded-md bg-brand-50 px-2 py-1 text-xs text-brand-700">
                                            {{ $tag->name }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Quick Actions --}}
                    <div class="rounded-2xl border border-[var(--border)] bg-[var(--card)] p-5 shadow-sm">
                        <h3 class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-[var(--muted)] mb-4">
                            Швидкі дії
                        </h3>
                        <div class="space-y-2">
                            <a 
                                href="{{ localized_route($routePrefix . '.index') }}"
                                class="flex items-center gap-3 rounded-xl bg-brand-50 px-4 py-3 text-sm font-medium hover:bg-brand-100 transition-all"
                            >
                                <svg class="h-5 w-5 text-brand-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                                </svg>
                                Усі категорії
                            </a>
                        </div>
                    </div>
                </div>
            </aside>

            {{-- Primary Content Area --}}
            <div class="min-w-0 space-y-6">
                {{-- Category Description (if has blocks) - Using V3 style --}}
                @if($categoryDescription['hasBlocks'] ?? false)
                    @include('engram.theory.partials.category-description-v3', [
                        'page' => $selectedCategory,
                        'categoryDescription' => $categoryDescription,
                    ])
                @endif

                {{-- Pages Grid --}}
                @if($categoryPages->isNotEmpty())
                    <section class="scroll-mt-24">
                        <div class="text-center mb-8">
                            <p class="text-xs uppercase tracking-[0.25em] text-brand-600">{{ __('public.home.sections') }}</p>
                            <h2 class="text-2xl md:text-3xl font-semibold">Матеріали для вивчення</h2>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            @foreach($categoryPages as $index => $page)
                                <a 
                                    href="{{ localized_route($routePrefix . '.show', [$selectedCategory->slug, $page->slug]) }}"
                                    class="group flex h-full flex-col justify-between rounded-2xl border border-[var(--border)] bg-[var(--card)] p-5 shadow-sm hover:border-brand-500"
                                >
                                    <div class="space-y-3">
                                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-brand-100 text-brand-600">
                                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                            </svg>
                                        </span>
                                        <h3 class="text-xl font-semibold">{{ $page->title }}</h3>
                                        @if(!empty($page->text))
                                            <p class="text-sm text-[var(--muted)] line-clamp-2">
                                                {{ $page->text }}
                                            </p>
                                        @endif
                                    </div>
                                    <span class="text-sm font-semibold text-brand-600 mt-3">{{ __('public.common.go_to') }}</span>
                                </a>
                            @endforeach
                        </div>
                    </section>
                @else
                    <div class="rounded-2xl border border-dashed border-[var(--border)] p-8 text-center">
                        <p class="text-[var(--muted)]">Поки що в цій категорії немає сторінок теорії.</p>
                    </div>
                @endif

                {{-- Auto Generated Tests Section --}}
                @if(isset($autoGeneratedTests) && $autoGeneratedTests->isNotEmpty())
                    <section class="rounded-2xl border border-[var(--border)] bg-[var(--card)] p-6 shadow-sm">
                        <div class="flex items-center gap-3 mb-5">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-brand-100 text-brand-600">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-lg font-bold">Практичні тести</h2>
                                <p class="text-xs text-[var(--muted)]">Перевір свої знання з цієї категорії</p>
                            </div>
                        </div>
                        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach($autoGeneratedTests as $test)
                                <x-auto-generated-test-card :test="$test" />
                            @endforeach
                        </div>
                    </section>
                @endif

                {{-- Related Tests Section --}}
                @if(isset($relatedTests) && $relatedTests->isNotEmpty())
                    <section class="rounded-2xl border border-[var(--border)] bg-[var(--card)] p-6 shadow-sm" x-data="{ show: false }">
                        <button 
                            @click="show = !show"
                            class="flex w-full items-center justify-between text-left"
                        >
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-brand-100 text-brand-600">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                                    </svg>
                                </div>
                                <div>
                                    <h2 class="text-lg font-bold">Пов'язані тести</h2>
                                    <p class="text-xs text-[var(--muted)]">{{ $relatedTests->count() }} тестів за тегами</p>
                                </div>
                            </div>
                            <svg 
                                class="h-5 w-5 text-[var(--muted)] transition-transform" 
                                :class="{ 'rotate-180': show }"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"
                            >
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

        {{-- Mobile Floating Menu --}}
        <div class="fixed bottom-6 left-1/2 -translate-x-1/2 z-50 lg:hidden" x-data="{ open: false }">
            <button 
                @click="open = !open"
                class="flex items-center gap-2 rounded-full bg-brand-600 px-5 py-3 text-sm font-semibold text-white shadow-lg"
            >
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
                Меню
            </button>

            {{-- Mobile Menu Content --}}
            <div 
                x-show="open" 
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-4"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 translate-y-4"
                @click.away="open = false"
                class="absolute bottom-full left-1/2 -translate-x-1/2 mb-3 w-72 max-h-[60vh] overflow-y-auto rounded-2xl border border-[var(--border)] bg-[var(--card)] p-4 shadow-xl"
            >
                <div class="space-y-4">
                    {{-- Categories --}}
                    <div>
                        <h4 class="text-xs font-bold uppercase tracking-wider text-[var(--muted)] mb-2">Категорії теорії</h4>
                        <nav class="space-y-1">
                            @if($categories->isNotEmpty())
                                @include('engram.theory.partials.nested-category-nav-mobile', [
                                    'categories' => $categories,
                                    'selectedCategory' => $selectedCategory,
                                    'routePrefix' => $routePrefix,
                                ])
                            @endif
                        </nav>
                    </div>

                    {{-- Pages in current category --}}
                    @if($categoryPages->isNotEmpty())
                        <div class="border-t border-[var(--border)] pt-4">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-[var(--muted)] mb-2">{{ $selectedCategory->title }}</h4>
                            <nav class="space-y-1">
                                @foreach($categoryPages as $pageItem)
                                    <a 
                                        href="{{ localized_route($routePrefix . '.show', [$selectedCategory->slug, $pageItem->slug]) }}"
                                        class="block rounded-lg px-3 py-2 text-sm hover:bg-brand-50"
                                    >
                                        {{ $pageItem->title }}
                                    </a>
                                @endforeach
                            </nav>
                        </div>
                    @endif

                    {{-- Quick Actions --}}
                    <div class="border-t border-[var(--border)] pt-4">
                        <div class="space-y-2">
                            <a 
                                href="{{ localized_route($routePrefix . '.index') }}"
                                class="flex items-center gap-2 rounded-lg bg-brand-50 px-3 py-2 text-sm font-medium"
                            >
                                <svg class="h-4 w-4 text-brand-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                                </svg>
                                Усі категорії
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
