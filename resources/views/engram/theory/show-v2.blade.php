@extends('layouts.engram')

@section('title', $page->title)

@section('content')
    @php($blocks = $page->textBlocks ?? collect())
    @php($breadcrumbs = $breadcrumbs ?? [])
    @php($routePrefix = $routePrefix ?? 'theory')
    @php($heroBlock = $blocks->firstWhere('type', 'hero-v2') ?? $blocks->firstWhere('type', 'hero'))
    @php($contentBlocks = $blocks->reject(fn($b) => in_array($b->type, ['hero', 'hero-v2'])))

    {{-- Full-width Hero Section --}}
    <section class="relative -mx-4 -mt-10 mb-8 overflow-hidden bg-gradient-to-br from-primary/5 via-background to-secondary/5">
        <div class="absolute inset-0 bg-[url('data:image/svg+xml,%3Csvg%20width%3D%2260%22%20height%3D%2260%22%20viewBox%3D%220%200%2060%2060%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%3E%3Cg%20fill%3D%22none%22%20fill-rule%3D%22evenodd%22%3E%3Cg%20fill%3D%22%239C92AC%22%20fill-opacity%3D%220.03%22%3E%3Cpath%20d%3D%22M36%2034v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6%2034v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6%204V0H4v4H0v2h4v4h2V6h4V4H6z%22%2F%3E%3C%2Fg%3E%3C%2Fg%3E%3C%2Fsvg%3E')] opacity-50"></div>
        
        <div class="relative page-shell mx-auto px-4 py-12 md:py-16">
            {{-- Breadcrumb Navigation --}}
            <nav class="mb-6 flex items-center gap-2 text-sm" aria-label="Breadcrumb">
                @foreach($breadcrumbs as $index => $crumb)
                    @if(!empty($crumb['url']))
                        <a href="{{ $crumb['url'] }}" class="text-muted-foreground hover:text-primary transition-colors">
                            {{ $crumb['label'] }}
                        </a>
                    @else
                        <span class="font-medium text-foreground">{{ $crumb['label'] }}</span>
                    @endif
                    @if($index < count($breadcrumbs) - 1)
                        <svg class="h-4 w-4 text-muted-foreground/50" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    @endif
                @endforeach
            </nav>

            {{-- Title & Meta --}}
            <div class="max-w-4xl">
                @if($heroBlock)
                    @php($heroData = json_decode($heroBlock->body ?? '[]', true) ?? [])
                    @if(!empty($heroData['level']))
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-primary/10 px-3 py-1 text-xs font-semibold text-primary mb-4">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                            Рівень {{ $heroData['level'] }}
                        </span>
                    @endif
                @endif

                <h1 class="text-3xl md:text-4xl lg:text-5xl font-bold tracking-tight text-foreground mb-4 leading-tight">
                    {{ $page->title }}
                </h1>

                @if($heroBlock)
                    @php($heroData = json_decode($heroBlock->body ?? '[]', true) ?? [])
                    @if(!empty($heroData['intro']))
                        <p class="text-lg md:text-xl text-muted-foreground leading-relaxed">
                            {!! $heroData['intro'] !!}
                        </p>
                    @endif
                @endif
            </div>

            {{-- Quick Rules Preview --}}
            @if($heroBlock)
                @php($heroData = json_decode($heroBlock->body ?? '[]', true) ?? [])
                @if(!empty($heroData['rules']))
                    <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach($heroData['rules'] as $rule)
                            @php($ruleColor = $rule['color'] ?? 'slate')
                            @php($colorClasses = match($ruleColor) {
                                'emerald' => 'from-emerald-500/10 to-emerald-500/5 border-emerald-200/50',
                                'blue' => 'from-blue-500/10 to-blue-500/5 border-blue-200/50',
                                'rose' => 'from-rose-500/10 to-rose-500/5 border-rose-200/50',
                                'amber' => 'from-amber-500/10 to-amber-500/5 border-amber-200/50',
                                'sky' => 'from-sky-500/10 to-sky-500/5 border-sky-200/50',
                                default => 'from-slate-500/10 to-slate-500/5 border-slate-200/50',
                            })
                            @php($labelColor = match($ruleColor) {
                                'emerald' => 'text-emerald-600',
                                'blue' => 'text-blue-600',
                                'rose' => 'text-rose-600',
                                'amber' => 'text-amber-600',
                                'sky' => 'text-sky-600',
                                default => 'text-slate-600',
                            })
                            <div class="group relative rounded-2xl border bg-gradient-to-br {{ $colorClasses }} p-5 backdrop-blur-sm transition-all hover:shadow-lg hover:-translate-y-0.5">
                                @if(!empty($rule['label']))
                                    <span class="text-xs font-bold uppercase tracking-wider {{ $labelColor }} mb-2 block">
                                        {{ $rule['label'] }}
                                    </span>
                                @endif
                                <p class="text-sm text-foreground/80 leading-relaxed">
                                    {!! $rule['text'] ?? '' !!}
                                </p>
                                @if(!empty($rule['example']))
                                    <code class="mt-3 block rounded-lg bg-background/60 px-3 py-2 font-mono text-xs text-foreground/70">
                                        {{ $rule['example'] }}
                                    </code>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            @endif
        </div>
    </section>

    {{-- Main Content Area --}}
    <div class="grid gap-8 lg:grid-cols-[280px_1fr]">
        {{-- Left Sidebar --}}
        <aside class="hidden lg:block">
            <div class="sticky top-24 space-y-6">
                {{-- Theory Categories List --}}
                @if(isset($categories) && $categories->isNotEmpty())
                    <nav class="rounded-2xl border border-border/50 bg-card/80 p-5 backdrop-blur-sm">
                        <h3 class="mb-4 text-sm font-bold uppercase tracking-wider text-muted-foreground">
                            Категорії теорії
                        </h3>
                        <ul class="space-y-1">
                            @foreach($categories as $category)
                                @php($isActiveCategory = isset($selectedCategory) && $selectedCategory->is($category))
                                <li>
                                    <a 
                                        href="{{ route($routePrefix . '.category', $category->slug) }}"
                                        class="block rounded-xl px-3 py-2 text-sm transition-all {{ $isActiveCategory ? 'bg-primary text-primary-foreground font-medium' : 'text-muted-foreground hover:bg-muted hover:text-foreground' }}"
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
                    <nav class="rounded-2xl border border-border/50 bg-card/80 p-5 backdrop-blur-sm">
                        <h3 class="mb-4 text-sm font-bold uppercase tracking-wider text-muted-foreground">
                            Зміст
                        </h3>
                        <ul class="space-y-2 text-sm">
                            @foreach($tocBlocks as $tocBlock)
                                @php($tocData = json_decode($tocBlock->body ?? '[]', true))
                                @if(!empty($tocData['title']))
                                    <li>
                                        <a 
                                            href="#block-{{ $tocBlock->id }}" 
                                            class="flex items-center gap-2 text-muted-foreground hover:text-primary transition-colors py-1"
                                        >
                                            <span class="h-1.5 w-1.5 rounded-full bg-border"></span>
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
                    <nav class="rounded-2xl border border-border/50 bg-card/80 p-5 backdrop-blur-sm">
                        <h3 class="mb-4 text-sm font-bold uppercase tracking-wider text-muted-foreground">
                            {{ $selectedCategory->title }}
                        </h3>
                        <ul class="space-y-1">
                            @foreach($categoryPages as $pageItem)
                                @php($isCurrentPage = $page->is($pageItem))
                                <li>
                                    <a 
                                        href="{{ route($routePrefix . '.show', [$selectedCategory->slug, $pageItem->slug]) }}"
                                        class="block rounded-xl px-3 py-2 text-sm transition-all {{ $isCurrentPage ? 'bg-primary text-primary-foreground font-medium' : 'text-muted-foreground hover:bg-muted hover:text-foreground' }}"
                                    >
                                        {{ $pageItem->title }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </nav>
                @endif

                {{-- Quick Actions --}}
                <div class="rounded-2xl border border-border/50 bg-gradient-to-br from-primary/5 to-secondary/5 p-5">
                    <h3 class="mb-4 text-sm font-bold uppercase tracking-wider text-muted-foreground">
                        Швидкі дії
                    </h3>
                    <div class="space-y-2">
                        <a 
                            href="{{ route($routePrefix . '.index') }}"
                            class="flex items-center gap-3 rounded-xl bg-background/60 px-3 py-2.5 text-sm font-medium text-foreground transition-all hover:bg-background hover:shadow-sm"
                        >
                            <svg class="h-4 w-4 text-muted-foreground" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                            </svg>
                            Усі категорії
                        </a>
                        @if(isset($selectedCategory))
                            <a 
                                href="{{ route($routePrefix . '.category', $selectedCategory->slug) }}"
                                class="flex items-center gap-3 rounded-xl bg-background/60 px-3 py-2.5 text-sm font-medium text-foreground transition-all hover:bg-background hover:shadow-sm"
                            >
                                <svg class="h-4 w-4 text-muted-foreground" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
                <section class="rounded-3xl border border-border/50 bg-gradient-to-br from-card to-card/50 p-6 md:p-8 shadow-sm">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary/10">
                            <svg class="h-5 w-5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                            </svg>
                        </div>
                        <h2 class="text-xl font-bold text-foreground">Тести по темі</h2>
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
                <section class="rounded-3xl border border-border/50 bg-card/50 p-6" x-data="{ expanded: false }">
                    <button 
                        @click="expanded = !expanded"
                        class="flex w-full items-center justify-between text-left"
                    >
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-secondary/50">
                                <svg class="h-5 w-5 text-secondary-foreground" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                </svg>
                            </div>
                            <span class="text-lg font-semibold text-foreground">Теги сторінки</span>
                        </div>
                        <svg 
                            class="h-5 w-5 text-muted-foreground transition-transform" 
                            :class="{ 'rotate-180': expanded }"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div x-show="expanded" x-collapse class="mt-4">
                        <div class="flex flex-wrap gap-2">
                            @foreach($page->tags as $tag)
                                <span class="inline-flex items-center rounded-full bg-muted px-3 py-1 text-xs font-medium text-muted-foreground">
                                    {{ $tag->name }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                </section>
            @endif

            {{-- Related Tests Section --}}
            @if(isset($relatedTests) && $relatedTests->isNotEmpty())
                <section class="rounded-3xl border border-border/50 bg-card/50 p-6" x-data="{ expanded: false }">
                    <button 
                        @click="expanded = !expanded"
                        class="flex w-full items-center justify-between text-left"
                    >
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-accent/20">
                                <svg class="h-5 w-5 text-accent-foreground" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                                </svg>
                            </div>
                            <span class="text-lg font-semibold text-foreground">Пов'язані тести</span>
                        </div>
                        <svg 
                            class="h-5 w-5 text-muted-foreground transition-transform" 
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
            class="flex w-full items-center justify-center gap-2 rounded-2xl bg-primary px-4 py-3 text-sm font-semibold text-primary-foreground shadow-lg transition-all hover:shadow-xl"
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
            class="absolute bottom-full left-0 right-0 mb-2 max-h-[60vh] overflow-y-auto rounded-2xl border border-border bg-card p-4 shadow-xl"
        >
            <div class="space-y-4">
                {{-- Categories --}}
                @if(isset($categories) && $categories->isNotEmpty())
                    <div>
                        <h4 class="mb-2 text-xs font-bold uppercase tracking-wider text-muted-foreground">Категорії</h4>
                        <ul class="space-y-1">
                            @foreach($categories as $category)
                                <li>
                                    <a 
                                        href="{{ route($routePrefix . '.category', $category->slug) }}"
                                        class="block rounded-xl px-3 py-2 text-sm {{ isset($selectedCategory) && $selectedCategory->is($category) ? 'bg-primary text-primary-foreground' : 'text-muted-foreground hover:bg-muted' }}"
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
                    <div class="border-t border-border pt-4">
                        <h4 class="mb-2 text-xs font-bold uppercase tracking-wider text-muted-foreground">{{ $selectedCategory->title }}</h4>
                        <ul class="space-y-1">
                            @foreach($categoryPages as $pageItem)
                                <li>
                                    <a 
                                        href="{{ route($routePrefix . '.show', [$selectedCategory->slug, $pageItem->slug]) }}"
                                        class="block rounded-xl px-3 py-2 text-sm {{ $page->is($pageItem) ? 'bg-secondary text-secondary-foreground font-medium' : 'text-muted-foreground hover:bg-muted' }}"
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
