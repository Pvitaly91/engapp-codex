@extends('layouts.engram')

@section('title', $page->title)

@section('content')
    @php($blocks = $page->textBlocks ?? collect())
    @php($breadcrumbs = $breadcrumbs ?? [])
    @php($routePrefix = $routePrefix ?? 'theory')
    @php($heroBlock = $blocks->firstWhere('type', 'hero-v2') ?? $blocks->firstWhere('type', 'hero'))
    @php($heroData = $heroBlock ? (json_decode($heroBlock->body ?? '[]', true) ?? []) : [])
    @php($contentBlocks = $blocks->reject(fn($b) => in_array($b->type, ['hero', 'hero-v2', 'navigation-chips'])))
    @php($navBlock = $blocks->firstWhere('type', 'navigation-chips'))
    @php($categoryPages = $categoryPages ?? collect())

    <div class="min-h-screen">
        {{-- Compact Breadcrumb Strip --}}
        <nav class="mb-6 flex items-center gap-1.5 text-xs text-muted-foreground" aria-label="Breadcrumb">
            @foreach($breadcrumbs as $index => $crumb)
                @if(!empty($crumb['url']))
                    <a href="{{ $crumb['url'] }}" class="hover:text-primary transition-colors truncate max-w-[120px]">
                        {{ $crumb['label'] }}
                    </a>
                @else
                    <span class="font-medium text-foreground truncate max-w-[180px]">{{ $crumb['label'] }}</span>
                @endif
                @if($index < count($breadcrumbs) - 1)
                    <span class="text-border">/</span>
                @endif
            @endforeach
        </nav>

        @include('engram.theory.partials.sidebar-navigation-mobile', [
            'categories' => $categories,
            'selectedCategory' => $selectedCategory ?? null,
            'categoryPages' => $categoryPages ?? collect(),
            'currentPage' => $page,
            'routePrefix' => $routePrefix,
        ])

        {{-- Main Content Grid --}}
        <div class="grid grid-cols-1 gap-8 lg:grid-cols-[280px_minmax(0,1fr)] xl:grid-cols-[320px_minmax(0,1fr)]">
            {{-- Left Sidebar --}}
            <aside class="hidden lg:block">
                <div id="theory-sidebar" class="sticky top-24 space-y-5 transition-[top] duration-200 max-h-[calc(100vh-7rem)] overflow-y-auto pr-1">
                    {{-- Theory Categories List --}}
                    @if(isset($categories) && $categories->isNotEmpty())
                        <div class="rounded-2xl border border-border/60 bg-card p-5">
                            <h3 class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-muted-foreground mb-4">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                </svg>
                                Категорії теорії
                            </h3>
                            <nav id="category-nav-scroll" class="space-y-1">
                                @include('engram.theory.partials.nested-category-nav', [
                                    'categories' => $categories,
                                    'selectedCategory' => $selectedCategory ?? null,
                                    'currentPage' => $page,
                                    'routePrefix' => $routePrefix,
                                ])
                            </nav>
                        </div>
                    @endif

                    @if(isset($selectedCategory) && $categoryPages->isNotEmpty())
                        <div class="rounded-2xl border border-border/60 bg-card p-5">
                            <h3 class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-muted-foreground mb-4">
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
                                        class="block rounded-lg px-3 py-2 text-sm transition-colors hover:bg-muted/50 {{ $isCurrentPage ? 'bg-secondary text-secondary-foreground font-semibold' : 'text-muted-foreground hover:text-foreground' }}"
                                        @if($isCurrentPage) aria-current="page" @endif
                                    >
                                        {{ $pageItem->title }}
                                    </a>
                                @endforeach
                            </nav>
                        </div>
                    @endif

                    {{-- Table of Contents --}}
                    @php($tocBlocks = $contentBlocks->filter(fn($b) => !empty(json_decode($b->body ?? '[]', true)['title'] ?? '')))
                    @if($tocBlocks->isNotEmpty())
                        <div class="rounded-2xl border border-border/60 bg-card p-5">
                            <h3 class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-muted-foreground mb-4">
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
                                            class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm text-muted-foreground hover:text-foreground hover:bg-muted/50 transition-colors"
                                        >
                                            <span class="h-1 w-1 rounded-full bg-border"></span>
                                            <span class="truncate">{{ preg_replace('/^\d+\.\s*/', '', $tocData['title']) }}</span>
                                        </a>
                                    @endif
                                @endforeach
                            </nav>
                        </div>
                    @endif

                

                    {{-- Quick Actions --}}
                    <div class="rounded-2xl border border-border/60 bg-gradient-to-br from-muted/30 to-muted/10 p-5">
                        <h3 class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-muted-foreground mb-4">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                            Швидкі дії
                        </h3>
                        <div class="space-y-2">
                            <a 
                                href="{{ localized_route($routePrefix . '.index') }}"
                                class="flex items-center gap-3 rounded-xl bg-card px-4 py-3 text-sm font-medium text-foreground transition-all hover:shadow-sm hover:border-primary/20 border border-transparent"
                            >
                                <svg class="h-5 w-5 text-muted-foreground" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                                </svg>
                                Усі категорії
                            </a>
                            @if(isset($selectedCategory))
                                <a 
                                    href="{{ localized_route($routePrefix . '.category', $selectedCategory->slug) }}"
                                    class="flex items-center gap-3 rounded-xl bg-card px-4 py-3 text-sm font-medium text-foreground transition-all hover:shadow-sm hover:border-primary/20 border border-transparent"
                                >
                                    <svg class="h-5 w-5 text-muted-foreground" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                                    </svg>
                                    {{ $selectedCategory->title }}
                                </a>
                            @endif
                        </div>
                    </div>

                    {{-- Tags Section --}}
                    @if($page->tags->isNotEmpty())
                        <div class="rounded-2xl border border-border/60 bg-card p-5" x-data="{ show: false }">
                            <button 
                                @click="show = !show"
                                class="flex w-full items-center justify-between text-left"
                            >
                                <h3 class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-muted-foreground">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                    </svg>
                                    Теги ({{ $page->tags->count() }})
                                </h3>
                                <svg 
                                    class="h-4 w-4 text-muted-foreground transition-transform" 
                                    :class="{ 'rotate-180': show }"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                            <div x-show="show" x-collapse class="mt-4">
                                <div class="flex flex-wrap gap-1.5">
                                    @foreach($page->tags as $tag)
                                        <span class="inline-flex items-center rounded-md bg-muted/60 px-2 py-1 text-xs text-muted-foreground">
                                            {{ $tag->name }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </aside>

            {{-- Primary Content Area --}}
            <div class="min-w-0 space-y-6">
                {{-- Hero Title Card --}}
                <header class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 text-white">
                    {{-- Decorative Pattern --}}
                    <div class="absolute inset-0 opacity-10">
                        <svg class="h-full w-full" viewBox="0 0 100 100" preserveAspectRatio="none">
                            <defs>
                                <pattern id="grid-pattern" width="10" height="10" patternUnits="userSpaceOnUse">
                                    <path d="M 10 0 L 0 0 0 10" fill="none" stroke="currentColor" stroke-width="0.5"/>
                                </pattern>
                            </defs>
                            <rect width="100%" height="100%" fill="url(#grid-pattern)"/>
                        </svg>
                    </div>
                    
                    <div class="relative px-6 py-8 md:px-8 md:py-10">
                        @if(!empty($heroData['level']))
                            <div class="mb-4 flex items-center gap-3">
                                <span class="inline-flex items-center gap-1.5 rounded-lg bg-white/10 backdrop-blur-sm px-3 py-1.5 text-xs font-bold tracking-wide">
                                    <svg class="h-3.5 w-3.5 text-amber-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                    Рівень {{ $heroData['level'] }}
                                </span>
                                <span class="h-1 w-1 rounded-full bg-white/30"></span>
                                <span class="text-xs text-white/60">Теорія</span>
                            </div>
                        @endif

                        <h1 class="text-2xl md:text-3xl lg:text-4xl font-extrabold tracking-tight leading-tight mb-4">
                            {{ $page->title }}
                        </h1>

                        @if(!empty($heroData['intro']))
                            <p class="text-base md:text-lg text-white/80 leading-relaxed max-w-3xl">
                                {!! $heroData['intro'] !!}
                            </p>
                        @endif

                        {{-- Quick Rules as Pills --}}
                        @if(!empty($heroData['rules']))
                            <div class="mt-6 flex flex-wrap gap-2">
                                @foreach($heroData['rules'] as $rule)
                                    @php($ruleColor = $rule['color'] ?? 'slate')
                                    @php($pillBg = match($ruleColor) {
                                        'emerald' => 'bg-emerald-500/20 text-emerald-200',
                                        'blue' => 'bg-blue-500/20 text-blue-200',
                                        'rose' => 'bg-rose-500/20 text-rose-200',
                                        'amber' => 'bg-amber-500/20 text-amber-200',
                                        'sky' => 'bg-sky-500/20 text-sky-200',
                                        default => 'bg-slate-500/20 text-slate-200',
                                    })
                                    <span class="inline-flex items-center gap-1.5 rounded-full {{ $pillBg }} px-3 py-1 text-xs font-medium backdrop-blur-sm">
                                        <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
                                        {{ $rule['label'] ?? '' }}
                                    </span>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </header>

                {{-- Quick Rules Expanded Cards --}}
                @if(!empty($heroData['rules']))
                    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach($heroData['rules'] as $index => $rule)
                            @php($ruleColor = $rule['color'] ?? 'slate')
                            @php($borderColor = match($ruleColor) {
                                'emerald' => 'border-l-emerald-500',
                                'blue' => 'border-l-blue-500',
                                'rose' => 'border-l-rose-500',
                                'amber' => 'border-l-amber-500',
                                'sky' => 'border-l-sky-500',
                                default => 'border-l-slate-500',
                            })
                            @php($iconBg = match($ruleColor) {
                                'emerald' => 'bg-emerald-100 text-emerald-600',
                                'blue' => 'bg-blue-100 text-blue-600',
                                'rose' => 'bg-rose-100 text-rose-600',
                                'amber' => 'bg-amber-100 text-amber-600',
                                'sky' => 'bg-sky-100 text-sky-600',
                                default => 'bg-slate-100 text-slate-600',
                            })
                            <article class="group rounded-xl border border-border/60 {{ $borderColor }} border-l-4 bg-card p-4 transition-all hover:shadow-md hover:bg-card/90">
                                <div class="flex items-start gap-3">
                                    <span class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-lg {{ $iconBg }} text-sm font-bold">
                                        {{ $index + 1 }}
                                    </span>
                                    <div class="min-w-0 flex-1">
                                        @if(!empty($rule['label']))
                                            <span class="text-xs font-bold uppercase tracking-wider text-muted-foreground block mb-1">
                                                {{ $rule['label'] }}
                                            </span>
                                        @endif
                                        <p class="text-sm text-foreground/80 leading-relaxed">
                                            {!! $rule['text'] ?? '' !!}
                                        </p>
                                        @if(!empty($rule['example']))
                                            <code class="mt-2 block rounded-lg bg-muted/60 px-3 py-2 font-mono text-xs text-muted-foreground">
                                                {{ $rule['example'] }}
                                            </code>
                                        @endif
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @endif

                {{-- Content Blocks --}}
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

                {{-- Navigation Footer --}}
                @if($navBlock)
                    @php($navData = json_decode($navBlock->body ?? '[]', true) ?? [])
                    @if(!empty($navData['items']))
                        <nav class="mt-8 rounded-2xl border border-dashed border-border/60 bg-muted/20 p-6">
                            @if(!empty($navData['title']))
                                <h3 class="text-sm font-bold text-muted-foreground mb-4">
                                    {{ $navData['title'] }}
                                </h3>
                            @endif
                            <div class="flex flex-wrap gap-2">
                                @foreach($navData['items'] as $item)
                                    @if(!empty($item['current']))
                                        <span class="inline-flex items-center gap-2 rounded-lg bg-primary/10 border border-primary/20 px-4 py-2 text-sm font-medium text-primary">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                            </svg>
                                            {{ $item['label'] ?? '' }}
                                        </span>
                                    @else
                                        <a 
                                            href="{{ $item['url'] ?? '#' }}"
                                            class="inline-flex items-center gap-2 rounded-lg border border-border bg-card px-4 py-2 text-sm font-medium text-muted-foreground transition-all hover:border-primary/40 hover:text-primary hover:bg-primary/5"
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

                {{-- Auto Generated Tests Section --}}
                @if(isset($autoGeneratedTests) && $autoGeneratedTests->isNotEmpty())
                    <section class="rounded-2xl border border-border/60 bg-card p-6">
                        <div class="flex items-center gap-3 mb-5">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-primary to-primary/70 text-white">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-lg font-bold text-foreground">Практичні тести</h2>
                                <p class="text-xs text-muted-foreground">Перевір свої знання з цієї теми</p>
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

        {{-- Mobile Floating Menu --}}
        <div class="fixed bottom-6 left-1/2 -translate-x-1/2 z-50 lg:hidden" x-data="{ open: false }">
            <button 
                @click="open = !open"
                class="flex items-center gap-2 rounded-full bg-foreground px-5 py-3 text-sm font-semibold text-background shadow-lg transition-transform hover:scale-105"
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
                class="absolute bottom-full left-1/2 -translate-x-1/2 mb-3 w-72 max-h-[60vh] overflow-y-auto rounded-2xl border border-border bg-card p-4 shadow-xl"
            >
                <div class="space-y-4">
                    {{-- Theory Categories --}}
                    @if(isset($categories) && $categories->isNotEmpty())
                        <div>
                            <h4 class="text-xs font-bold uppercase tracking-wider text-muted-foreground mb-2">Категорії теорії</h4>
                            <nav class="space-y-1">
                                @include('engram.theory.partials.nested-category-nav-mobile', [
                                    'categories' => $categories,
                                    'selectedCategory' => $selectedCategory ?? null,
                                    'currentPage' => $page,
                                    'routePrefix' => $routePrefix,
                                ])
                            </nav>
                        </div>
                    @endif

                    {{-- Category Pages --}}
                    @if(isset($selectedCategory) && $categoryPages->isNotEmpty())
                        <div class="border-t border-border pt-4">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-muted-foreground mb-2">{{ $selectedCategory->title }}</h4>
                            <nav class="space-y-1">
                                @foreach($categoryPages as $pageItem)
                                    @php($isCurrentPage = $page->is($pageItem))
                                    <a 
                                        href="{{ localized_route($routePrefix . '.show', [$selectedCategory->slug, $pageItem->slug]) }}"
                                        class="block rounded-lg px-3 py-2 text-sm {{ $isCurrentPage ? 'bg-primary text-primary-foreground font-medium' : 'text-muted-foreground hover:bg-muted' }}"
                                    >
                                        {{ $pageItem->title }}
                                    </a>
                                @endforeach
                            </nav>
                        </div>
                    @endif

                    {{-- Quick Actions --}}
                    <div class="border-t border-border pt-4">
                        <div class="space-y-2">
                            <a 
                                href="{{ localized_route($routePrefix . '.index') }}"
                                class="flex items-center gap-2 rounded-lg bg-muted/50 px-3 py-2 text-sm font-medium text-foreground"
                            >
                                <svg class="h-4 w-4 text-muted-foreground" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
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
