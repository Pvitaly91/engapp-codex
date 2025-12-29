@extends('layouts.engram')

@section('title', $page->title)

@section('content')
    @php($blocks = $page->textBlocks ?? collect())
    @php($breadcrumbs = $breadcrumbs ?? [])
    @php($routePrefix = $routePrefix ?? 'theory')
    @php($heroBlock = $blocks->firstWhere('type', 'hero-v2') ?? $blocks->firstWhere('type', 'hero'))
    @php($contentBlocks = $blocks->reject(fn($b) => in_array($b->type, ['hero', 'hero-v2'])))

    {{-- Full-width Hero Section --}}
    <section class="space-y-6 mb-8">
        {{-- Breadcrumb Navigation --}}
        <nav class="flex items-center gap-2 text-sm" aria-label="Breadcrumb">
            @foreach($breadcrumbs as $index => $crumb)
                @if(!empty($crumb['url']))
                    <a href="{{ $crumb['url'] }}" class="text-[var(--muted)] hover:text-brand-600">
                        {{ $crumb['label'] }}
                    </a>
                @else
                    <span class="font-medium">{{ $crumb['label'] }}</span>
                @endif
                @if($index < count($breadcrumbs) - 1)
                    <svg class="h-4 w-4 text-[var(--muted)]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                @endif
            @endforeach
        </nav>

        {{-- Title & Meta --}}
        <div class="max-w-4xl space-y-4">
            @if($heroBlock)
                @php($heroData = json_decode($heroBlock->body ?? '[]', true) ?? [])
                @if(!empty($heroData['level']))
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-brand-50 px-3 py-1 text-xs font-semibold text-brand-700">
                        Рівень {{ $heroData['level'] }}
                    </span>
                @endif
            @endif

            <h1 class="text-3xl md:text-4xl lg:text-5xl font-bold tracking-tight leading-tight">
                {{ $page->title }}
            </h1>

            @if($heroBlock)
                @php($heroData = json_decode($heroBlock->body ?? '[]', true) ?? [])
                @if(!empty($heroData['intro']))
                    <p class="text-lg md:text-xl text-[var(--muted)] leading-relaxed">
                        {!! $heroData['intro'] !!}
                    </p>
                @endif
            @endif
        </div>

        {{-- Quick Rules Preview --}}
        @if($heroBlock)
            @php($heroData = json_decode($heroBlock->body ?? '[]', true) ?? [])
            @if(!empty($heroData['rules']))
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($heroData['rules'] as $rule)
                        <div class="rounded-2xl border border-[var(--border)] bg-[var(--card)] p-5 shadow-sm hover:shadow-md transition-all hover:border-brand-500">
                            @if(!empty($rule['label']))
                                <span class="text-xs font-bold uppercase tracking-wider text-brand-600 mb-2 block">
                                    {{ $rule['label'] }}
                                </span>
                            @endif
                            <p class="text-sm leading-relaxed">
                                {!! $rule['text'] ?? '' !!}
                            </p>
                            @if(!empty($rule['example']))
                                <code class="mt-3 block rounded-lg bg-brand-50 px-3 py-2 font-mono text-xs text-brand-700">
                                    {{ $rule['example'] }}
                                </code>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        @endif
    </section>

    {{-- Main Content Area --}}
    <div class="grid gap-8 lg:grid-cols-[280px_1fr]">
        {{-- Left Sidebar --}}
        <aside class="hidden lg:block">
            <div class="sticky top-24 space-y-6">
                {{-- Theory Categories List --}}
                @if(isset($categories) && $categories->isNotEmpty())
                    <nav class="rounded-2xl border border-[var(--border)] bg-[var(--card)] p-5 shadow-sm">
                        <h3 class="mb-4 text-sm font-bold uppercase tracking-wider text-[var(--muted)]">
                            Категорії теорії
                        </h3>
                        <ul class="space-y-1">
                            @foreach($categories as $category)
                                @php($isActiveCategory = isset($selectedCategory) && $selectedCategory->is($category))
                                <li>
                                    <a 
                                        href="{{ localized_route($routePrefix . '.category', $category->slug) }}"
                                        class="block rounded-xl px-3 py-2 text-sm transition-all {{ $isActiveCategory ? 'bg-brand-100 text-brand-700 font-medium' : 'hover:bg-brand-50' }}"
                                    >
                                        {{ $category->title }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </nav>
                @endif

                {{-- Table of Contents --}}
                @php($tocBlocks = $contentBlocks->filter(fn($b) => !empty(json_decode($b->body ?? '[]', true)['title'] ?? '')))
                @if($tocBlocks->isNotEmpty())
                    <nav class="rounded-2xl border border-[var(--border)] bg-[var(--card)] p-5 shadow-sm">
                        <h3 class="mb-4 text-sm font-bold uppercase tracking-wider text-[var(--muted)]">
                            Зміст
                        </h3>
                        <ul class="space-y-2 text-sm">
                            @foreach($tocBlocks as $tocBlock)
                                @php($tocData = json_decode($tocBlock->body ?? '[]', true))
                                @if(!empty($tocData['title']))
                                    <li>
                                        <a 
                                            href="#block-{{ $tocBlock->id }}" 
                                            class="flex items-center gap-2 text-[var(--muted)] hover:text-brand-600 transition-colors py-1"
                                        >
                                            <span class="h-1.5 w-1.5 rounded-full bg-brand-600"></span>
                                            {{ $tocData['title'] }}
                                        </a>
                                    </li>
                                @endif
                            @endforeach
                        </ul>
                    </nav>
                @endif

                {{-- Category Navigation --}}
                @if(isset($selectedCategory) && $categoryPages->isNotEmpty())
                    <nav class="rounded-2xl border border-[var(--border)] bg-[var(--card)] p-5 shadow-sm">
                        <h3 class="mb-4 text-sm font-bold uppercase tracking-wider text-[var(--muted)]">
                            {{ $selectedCategory->title }}
                        </h3>
                        <ul class="space-y-1">
                            @foreach($categoryPages as $pageItem)
                                @php($isCurrentPage = $page->is($pageItem))
                                <li>
                                    <a 
                                        href="{{ localized_route($routePrefix . '.show', [$selectedCategory->slug, $pageItem->slug]) }}"
                                        class="block rounded-xl px-3 py-2 text-sm transition-all {{ $isCurrentPage ? 'bg-brand-100 text-brand-700 font-medium' : 'hover:bg-brand-50' }}"
                                    >
                                        {{ $pageItem->title }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </nav>
                @endif

                {{-- Quick Actions --}}
                <div class="rounded-2xl border border-[var(--border)] bg-[var(--card)] p-5 shadow-sm">
                    <h3 class="mb-4 text-sm font-bold uppercase tracking-wider text-[var(--muted)]">
                        Швидкі дії
                    </h3>
                    <div class="space-y-2">
                        <a 
                            href="{{ localized_route($routePrefix . '.index') }}"
                            class="flex items-center gap-3 rounded-xl bg-brand-50 px-3 py-2.5 text-sm font-medium hover:bg-brand-100 transition-all"
                        >
                            <svg class="h-4 w-4 text-brand-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                            </svg>
                            Усі категорії
                        </a>
                        @if(isset($selectedCategory))
                            <a 
                                href="{{ localized_route($routePrefix . '.category', $selectedCategory->slug) }}"
                                class="flex items-center gap-3 rounded-xl bg-brand-50 px-3 py-2.5 text-sm font-medium hover:bg-brand-100 transition-all"
                            >
                                <svg class="h-4 w-4 text-brand-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                                </svg>
                                {{ $selectedCategory->title }}
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </aside>

        {{-- Content Column --}}
        <div class="min-w-0 space-y-8">
            @foreach($contentBlocks as $block)
                @includeIf('engram.theory.blocks-v2.' . $block->type, [
                    'page' => $page,
                    'block' => $block,
                    'data' => json_decode($block->body ?? '[]', true),
                ])
            @endforeach

            {{-- Auto Generated Tests Section --}}
            @if(isset($autoGeneratedTests) && $autoGeneratedTests->isNotEmpty())
                <section class="rounded-2xl border border-[var(--border)] bg-[var(--card)] p-6 md:p-8 shadow-sm">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-brand-100 text-brand-600">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                            </svg>
                        </div>
                        <h2 class="text-xl font-bold">Тести по темі</h2>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        @foreach($autoGeneratedTests as $test)
                            <x-auto-generated-test-card :test="$test" />
                        @endforeach
                    </div>
                </section>
            @endif

            {{-- Tags Section --}}
            @if($page->tags->isNotEmpty())
                <section class="rounded-2xl border border-[var(--border)] bg-[var(--card)] p-6 shadow-sm" x-data="{ expanded: false }">
                    <button 
                        @click="expanded = !expanded"
                        class="flex w-full items-center justify-between text-left"
                    >
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-brand-100 text-brand-600">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                </svg>
                            </div>
                            <span class="text-lg font-semibold">Теги сторінки</span>
                        </div>
                        <svg 
                            class="h-5 w-5 text-[var(--muted)] transition-transform" 
                            :class="{ 'rotate-180': expanded }"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div x-show="expanded" x-collapse class="mt-4">
                        <div class="flex flex-wrap gap-2">
                            @foreach($page->tags as $tag)
                                <span class="inline-flex items-center rounded-full bg-brand-50 px-3 py-1 text-xs font-medium text-brand-700">
                                    {{ $tag->name }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                </section>
            @endif

            {{-- Related Tests Section --}}
            @if(isset($relatedTests) && $relatedTests->isNotEmpty())
                <section class="rounded-2xl border border-[var(--border)] bg-[var(--card)] p-6 shadow-sm" x-data="{ expanded: false }">
                    <button 
                        @click="expanded = !expanded"
                        class="flex w-full items-center justify-between text-left"
                    >
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-brand-100 text-brand-600">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                                </svg>
                            </div>
                            <span class="text-lg font-semibold">Пов'язані тести</span>
                        </div>
                        <svg 
                            class="h-5 w-5 text-[var(--muted)] transition-transform" 
                            :class="{ 'rotate-180': expanded }"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div x-show="expanded" x-collapse class="mt-4">
                        <div class="grid gap-4 sm:grid-cols-2">
                            @foreach($relatedTests as $test)
                                <x-related-test-card :test="$test" />
                            @endforeach
                        </div>
                    </div>
                </section>
            @endif
        </div>
    </div>

    {{-- Mobile Navigation --}}
    <div class="fixed bottom-4 left-4 right-4 z-40 lg:hidden" x-data="{ open: false }">
        <button 
            @click="open = !open"
            class="flex w-full items-center justify-center gap-2 rounded-2xl bg-brand-600 px-4 py-3 text-sm font-semibold text-white shadow-lg"
        >
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
            Навігація
        </button>

        {{-- Mobile Menu Overlay --}}
        <div 
            x-show="open" 
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-4"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 translate-y-4"
            @click.away="open = false"
            class="absolute bottom-full left-0 right-0 mb-2 max-h-[60vh] overflow-y-auto rounded-2xl border border-[var(--border)] bg-[var(--card)] p-4 shadow-xl"
        >
            <div class="space-y-4">
                {{-- Categories --}}
                @if(isset($categories) && $categories->isNotEmpty())
                    <div>
                        <h4 class="mb-2 text-xs font-bold uppercase tracking-wider text-[var(--muted)]">Категорії</h4>
                        <ul class="space-y-1">
                            @foreach($categories as $category)
                                <li>
                                    <a 
                                        href="{{ localized_route($routePrefix . '.category', $category->slug) }}"
                                        class="block rounded-xl px-3 py-2 text-sm {{ isset($selectedCategory) && $selectedCategory->is($category) ? 'bg-brand-100 text-brand-700 font-medium' : 'hover:bg-brand-50' }}"
                                    >
                                        {{ $category->title }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Category Pages --}}
                @if(isset($selectedCategory) && $categoryPages->isNotEmpty())
                    <div class="border-t border-[var(--border)] pt-4">
                        <h4 class="mb-2 text-xs font-bold uppercase tracking-wider text-[var(--muted)]">{{ $selectedCategory->title }}</h4>
                        <ul class="space-y-1">
                            @foreach($categoryPages as $pageItem)
                                <li>
                                    <a 
                                        href="{{ localized_route($routePrefix . '.show', [$selectedCategory->slug, $pageItem->slug]) }}"
                                        class="block rounded-xl px-3 py-2 text-sm {{ $page->is($pageItem) ? 'bg-brand-100 text-brand-700 font-medium' : 'hover:bg-brand-50' }}"
                                    >
                                        {{ $pageItem->title }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
