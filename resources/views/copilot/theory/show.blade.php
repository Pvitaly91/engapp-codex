@extends('layouts.copilot')

@section('title', $page->title)

@section('breadcrumb')
    <nav class="flex items-center gap-1.5 text-xs text-[var(--cp-muted)]" aria-label="Breadcrumb">
        @foreach($breadcrumbs as $index => $crumb)
            @if(!empty($crumb['url']))
                <a href="{{ $crumb['url'] }}" class="hover:text-pilot-600 transition-colors truncate max-w-[120px]">{{ $crumb['label'] }}</a>
            @else
                <span class="font-medium text-[var(--cp-fg)] truncate max-w-[180px]">{{ $crumb['label'] }}</span>
            @endif
            @if($index < count($breadcrumbs) - 1)
                <span>/</span>
            @endif
        @endforeach
    </nav>
@endsection

@section('content')
    @php($blocks = $page->textBlocks ?? collect())
    @php($breadcrumbs = $breadcrumbs ?? [])
    @php($routePrefix = $routePrefix ?? 'copilot.theory')
    @php($heroBlock = $blocks->firstWhere('type', 'hero-v2') ?? $blocks->firstWhere('type', 'hero'))
    @php($heroData = $heroBlock ? (json_decode($heroBlock->body ?? '[]', true) ?? []) : [])
    @php($contentBlocks = $blocks->reject(fn($b) => in_array($b->type, ['hero', 'hero-v2', 'navigation-chips'])))
    @php($navBlock = $blocks->firstWhere('type', 'navigation-chips'))
    @php($categoryPages = $categoryPages ?? collect())

    <div class="space-y-8">
        @include('copilot.theory.partials.sidebar-navigation-mobile', [
            'categories' => $categories,
            'selectedCategory' => $selectedCategory ?? null,
            'categoryPages' => $categoryPages,
            'currentPage' => $page,
            'routePrefix' => $routePrefix,
        ])

        {{-- ── Main content grid ────────────────────────────────────────── --}}
        <div class="grid grid-cols-1 gap-8 lg:grid-cols-[280px_minmax(0,1fr)] xl:grid-cols-[300px_minmax(0,1fr)]">

            {{-- Left sidebar (desktop) --}}
            <aside class="hidden lg:block">
                <div id="cp-theory-sidebar" class="sticky top-24 space-y-4 max-h-[calc(100vh-7rem)] overflow-y-auto pr-1">

                    {{-- Categories list --}}
                    @if(isset($categories) && $categories->isNotEmpty())
                        <div class="cp-card p-5">
                            <h3 class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-[var(--cp-muted)] mb-4">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                </svg>
                                Категорії теорії
                            </h3>
                            <nav class="space-y-1">
                                @include('copilot.theory.partials.nested-category-nav', [
                                    'categories' => $categories,
                                    'selectedCategory' => $selectedCategory ?? null,
                                    'currentPage' => $page,
                                    'routePrefix' => $routePrefix,
                                ])
                            </nav>
                        </div>
                    @endif

                    {{-- Category pages --}}
                    @if(isset($selectedCategory) && $categoryPages->isNotEmpty())
                        <div class="cp-card p-5">
                            <h3 class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-[var(--cp-muted)] mb-4">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h7"/>
                                </svg>
                                {{ $selectedCategory->title }}
                            </h3>
                            <nav class="space-y-1">
                                @foreach($categoryPages as $pageItem)
                                    @php($isCurrentPage = $page->is($pageItem))
                                    <a
                                        href="{{ localized_route($routePrefix . '.show', [$selectedCategory->slug, $pageItem->slug]) }}"
                                        class="block rounded-lg px-3 py-2 text-sm transition-colors
                                            {{ $isCurrentPage
                                                ? 'bg-pilot-100 dark:bg-pilot-900/50 text-pilot-700 dark:text-pilot-300 font-semibold'
                                                : 'text-[var(--cp-muted)] hover:bg-pilot-50/80 dark:hover:bg-pilot-900/30 hover:text-[var(--cp-fg)]' }}"
                                        @if($isCurrentPage) aria-current="page" @endif
                                    >
                                        {{ $pageItem->title }}
                                    </a>
                                @endforeach
                            </nav>
                        </div>
                    @endif

                    {{-- Table of contents --}}
                    @php($tocBlocks = $contentBlocks->filter(fn($b) => !empty(json_decode($b->body ?? '[]', true)['title'] ?? '')))
                    @if($tocBlocks->isNotEmpty())
                        <div class="cp-card p-5">
                            <h3 class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-[var(--cp-muted)] mb-4">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h7"/>
                                </svg>
                                Зміст
                            </h3>
                            <nav class="space-y-1">
                                @foreach($tocBlocks as $tocBlock)
                                    @php($tocData = json_decode($tocBlock->body ?? '[]', true))
                                    @if(!empty($tocData['title']))
                                        <a
                                            href="#block-{{ $tocBlock->id }}"
                                            class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm text-[var(--cp-muted)] hover:text-[var(--cp-fg)] hover:bg-pilot-50/80 dark:hover:bg-pilot-900/30 transition-colors"
                                        >
                                            <span class="h-1 w-1 rounded-full bg-[var(--cp-border)]"></span>
                                            <span class="truncate">{{ preg_replace('/^\d+\.\s*/', '', $tocData['title']) }}</span>
                                        </a>
                                    @endif
                                @endforeach
                            </nav>
                        </div>
                    @endif

                    {{-- Quick actions --}}
                    <div class="cp-card p-5">
                        <h3 class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-[var(--cp-muted)] mb-4">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                            Швидкі дії
                        </h3>
                        <div class="space-y-2">
                            <a
                                href="{{ localized_route($routePrefix . '.index') }}"
                                class="flex items-center gap-3 rounded-xl border border-[var(--cp-border)] bg-[var(--cp-surface)] px-4 py-3 text-sm font-medium text-[var(--cp-fg)] transition-all hover:border-pilot-400 hover:shadow-sm"
                            >
                                <svg class="h-5 w-5 text-[var(--cp-muted)]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                                </svg>
                                Усі категорії
                            </a>
                            @if(isset($selectedCategory))
                                <a
                                    href="{{ localized_route($routePrefix . '.category', $selectedCategory->slug) }}"
                                    class="flex items-center gap-3 rounded-xl border border-[var(--cp-border)] bg-[var(--cp-surface)] px-4 py-3 text-sm font-medium text-[var(--cp-fg)] transition-all hover:border-pilot-400 hover:shadow-sm"
                                >
                                    <svg class="h-5 w-5 text-[var(--cp-muted)]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                                    </svg>
                                    {{ $selectedCategory->title }}
                                </a>
                            @endif
                        </div>
                    </div>

                    {{-- Tags --}}
                    @if($page->tags->isNotEmpty())
                        <div class="cp-card p-5" x-data="{ show: false }">
                            <button @click="show = !show" class="flex w-full items-center justify-between text-left">
                                <h3 class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-[var(--cp-muted)]">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                    </svg>
                                    Теги ({{ $page->tags->count() }})
                                </h3>
                                <svg class="h-4 w-4 text-[var(--cp-muted)] transition-transform" :class="{ 'rotate-180': show }" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                            <div x-show="show" x-collapse class="mt-4">
                                <div class="flex flex-wrap gap-1.5">
                                    @foreach($page->tags as $tag)
                                        <span class="inline-flex items-center rounded-md bg-pilot-50 dark:bg-pilot-900/30 px-2 py-1 text-xs text-[var(--cp-muted)]">
                                            {{ $tag->name }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </aside>

            {{-- Primary content area --}}
            <div class="min-w-0 space-y-6 rounded-3xl border border-[var(--cp-border)] bg-[var(--cp-surface)] p-6 shadow-panel">

                {{-- Hero title card --}}
                <header class="relative overflow-hidden rounded-2xl border border-[var(--cp-border)] bg-gradient-to-br from-pilot-50 via-[var(--cp-surface)] to-teal-50/40 dark:from-pilot-900/30 dark:via-[var(--cp-surface)] dark:to-teal-900/10">
                    <div class="absolute inset-0 opacity-40">
                        <svg class="h-full w-full text-pilot-100 dark:text-pilot-900" viewBox="0 0 100 100" preserveAspectRatio="none">
                            <defs>
                                <pattern id="cp-page-grid" width="10" height="10" patternUnits="userSpaceOnUse">
                                    <path d="M 10 0 L 0 0 0 10" fill="none" stroke="currentColor" stroke-width="0.5"/>
                                </pattern>
                            </defs>
                            <rect width="100%" height="100%" fill="url(#cp-page-grid)"/>
                        </svg>
                    </div>

                    <div class="relative px-6 py-8 md:px-8 md:py-10">
                        @if(!empty($heroData['level']))
                            <div class="mb-4 flex items-center gap-3">
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-pilot-100 dark:bg-pilot-900/50 text-pilot-700 dark:text-pilot-300 px-3 py-1.5 text-xs font-bold tracking-wide shadow-sm">
                                    <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                    Рівень {{ $heroData['level'] }}
                                </span>
                                <span class="h-1 w-1 rounded-full bg-pilot-200 dark:bg-pilot-700"></span>
                                <span class="text-xs text-[var(--cp-muted)]">Теорія</span>
                            </div>
                        @endif

                        <h1 class="text-2xl md:text-3xl lg:text-4xl font-extrabold tracking-tight leading-tight mb-4 text-[var(--cp-fg)]">
                            {{ $page->title }}
                        </h1>

                        @if(!empty($heroData['intro']))
                            <p class="text-base md:text-lg text-[var(--cp-muted)] leading-relaxed max-w-3xl">
                                {!! $heroData['intro'] !!}
                            </p>
                        @endif

                        {{-- Quick rules as pills --}}
                        @if(!empty($heroData['rules']))
                            <div class="mt-6 flex flex-wrap gap-2">
                                @foreach($heroData['rules'] as $rule)
                                    @php($ruleColor = $rule['color'] ?? 'slate')
                                    @php($pillBg = match($ruleColor) {
                                        'emerald' => 'bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300',
                                        'blue'    => 'bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300',
                                        'rose'    => 'bg-rose-50 dark:bg-rose-900/30 text-rose-700 dark:text-rose-300',
                                        'amber'   => 'bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300',
                                        'sky'     => 'bg-sky-50 dark:bg-sky-900/30 text-sky-700 dark:text-sky-300',
                                        default   => 'bg-pilot-50 dark:bg-pilot-900/30 text-pilot-700 dark:text-pilot-300',
                                    })
                                    <span class="inline-flex items-center gap-1.5 rounded-full {{ $pillBg }} px-3 py-1 text-xs font-medium">
                                        <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
                                        {{ $rule['label'] ?? '' }}
                                    </span>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </header>

                {{-- Quick rules expanded cards --}}
                @if(!empty($heroData['rules']))
                    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach($heroData['rules'] as $index => $rule)
                            @php($ruleColor = $rule['color'] ?? 'slate')
                            @php($borderColor = match($ruleColor) {
                                'emerald' => 'border-l-emerald-500',
                                'blue'    => 'border-l-blue-500',
                                'rose'    => 'border-l-rose-500',
                                'amber'   => 'border-l-amber-500',
                                'sky'     => 'border-l-sky-500',
                                default   => 'border-l-pilot-500',
                            })
                            @php($iconBg = match($ruleColor) {
                                'emerald' => 'bg-emerald-100 dark:bg-emerald-900/50 text-emerald-600 dark:text-emerald-400',
                                'blue'    => 'bg-blue-100 dark:bg-blue-900/50 text-blue-600 dark:text-blue-400',
                                'rose'    => 'bg-rose-100 dark:bg-rose-900/50 text-rose-600 dark:text-rose-400',
                                'amber'   => 'bg-amber-100 dark:bg-amber-900/50 text-amber-600 dark:text-amber-400',
                                'sky'     => 'bg-sky-100 dark:bg-sky-900/50 text-sky-600 dark:text-sky-400',
                                default   => 'bg-pilot-100 dark:bg-pilot-900/50 text-pilot-600 dark:text-pilot-400',
                            })
                            <article class="group rounded-xl border border-[var(--cp-border)] {{ $borderColor }} border-l-4 bg-[var(--cp-surface)] p-4 transition-all hover:shadow-card hover:-translate-y-0.5">
                                <div class="flex items-start gap-3">
                                    <span class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-lg {{ $iconBg }} text-sm font-bold">
                                        {{ $index + 1 }}
                                    </span>
                                    <div class="min-w-0 flex-1">
                                        @if(!empty($rule['label']))
                                            <span class="text-xs font-bold uppercase tracking-wider text-[var(--cp-muted)] block mb-1">
                                                {{ $rule['label'] }}
                                            </span>
                                        @endif
                                        <p class="text-sm text-[var(--cp-fg)] leading-relaxed opacity-80">
                                            {!! $rule['text'] ?? '' !!}
                                        </p>
                                        @if(!empty($rule['example']))
                                            <code class="mt-2 block rounded-lg bg-pilot-50 dark:bg-pilot-900/30 px-3 py-2 font-mono text-xs text-[var(--cp-muted)]">
                                                {{ $rule['example'] }}
                                            </code>
                                        @endif
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @endif

                {{-- Content blocks (reuse engram blocks-v3 – CSS var aliases make them render correctly) --}}
                <div class="space-y-6">
                    @php($practiceQuestionsByBlock = $practiceQuestionsByBlock ?? [])
                    @foreach($contentBlocks as $block)
                        @includeIf('engram.theory.blocks-v3.' . $block->type, [
                            'page' => $page,
                            'block' => $block,
                            'data' => json_decode($block->body ?? '[]', true),
                            'practiceQuestions' => $practiceQuestionsByBlock[$block->uuid] ?? collect(),
                        ])
                    @endforeach
                </div>

                {{-- Navigation footer --}}
                @if($navBlock)
                    @php($navData = json_decode($navBlock->body ?? '[]', true) ?? [])
                    @if(!empty($navData['items']))
                        <nav class="mt-8 rounded-2xl border border-dashed border-[var(--cp-border)] bg-pilot-50/30 dark:bg-pilot-900/10 p-6">
                            @if(!empty($navData['title']))
                                <h3 class="text-sm font-bold text-[var(--cp-muted)] mb-4">{{ $navData['title'] }}</h3>
                            @endif
                            <div class="flex flex-wrap gap-2">
                                @foreach($navData['items'] as $item)
                                    @if(!empty($item['current']))
                                        <span class="inline-flex items-center gap-2 rounded-lg bg-pilot-50 dark:bg-pilot-900/30 border border-pilot-200 dark:border-pilot-700 px-4 py-2 text-sm font-medium text-pilot-700 dark:text-pilot-300">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                            </svg>
                                            {{ $item['label'] ?? '' }}
                                        </span>
                                    @else
                                        <a
                                            href="{{ $item['url'] ?? '#' }}"
                                            class="inline-flex items-center gap-2 rounded-lg border border-[var(--cp-border)] bg-[var(--cp-surface)] px-4 py-2 text-sm font-medium text-[var(--cp-muted)] transition-all hover:border-pilot-400 hover:text-pilot-600 hover:bg-pilot-50/50"
                                        >
                                            {{ $item['label'] ?? '' }}
                                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                                            </svg>
                                        </a>
                                    @endif
                                @endforeach
                            </div>
                        </nav>
                    @endif
                @endif

                {{-- Auto-generated tests --}}
                @if(isset($autoGeneratedTests) && $autoGeneratedTests->isNotEmpty())
                    <section class="rounded-2xl border border-[var(--cp-border)] bg-[var(--cp-surface)] p-6">
                        <div class="flex items-center gap-3 mb-5">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-pilot-500 to-pilot-600 text-white shadow-sm">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-lg font-bold text-[var(--cp-fg)]">Практичні тести</h2>
                                <p class="text-xs text-[var(--cp-muted)]">Перевір свої знання з цієї теми</p>
                            </div>
                        </div>
                        <div class="grid gap-3 sm:grid-cols-2">
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
                class="absolute bottom-full left-1/2 -translate-x-1/2 mb-3 w-72 max-h-[60vh] overflow-y-auto rounded-2xl border border-[var(--cp-border)] bg-[var(--cp-surface)] p-4 shadow-xl"
            >
                <div class="space-y-4">
                    @if(isset($categories) && $categories->isNotEmpty())
                        <div>
                            <h4 class="text-xs font-bold uppercase tracking-wider text-[var(--cp-muted)] mb-2">Категорії теорії</h4>
                            <nav class="space-y-1">
                                @include('copilot.theory.partials.nested-category-nav-mobile', [
                                    'categories' => $categories,
                                    'selectedCategory' => $selectedCategory ?? null,
                                    'currentPage' => $page,
                                    'routePrefix' => $routePrefix,
                                ])
                            </nav>
                        </div>
                    @endif

                    @if(isset($selectedCategory) && $categoryPages->isNotEmpty())
                        <div class="border-t border-[var(--cp-border)] pt-4">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-[var(--cp-muted)] mb-2">{{ $selectedCategory->title }}</h4>
                            <nav class="space-y-1">
                                @foreach($categoryPages as $pageItem)
                                    @php($isCurrentPage = $page->is($pageItem))
                                    <a
                                        href="{{ localized_route($routePrefix . '.show', [$selectedCategory->slug, $pageItem->slug]) }}"
                                        class="block rounded-lg px-3 py-2 text-sm
                                            {{ $isCurrentPage
                                                ? 'bg-pilot-100 dark:bg-pilot-900/50 text-pilot-700 dark:text-pilot-300 font-medium'
                                                : 'text-[var(--cp-muted)] hover:bg-pilot-50/80 hover:text-[var(--cp-fg)]' }}"
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
                            Усі категорії
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
