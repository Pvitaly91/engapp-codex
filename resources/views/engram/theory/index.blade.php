@extends('layouts.engram')

@section('title', $sectionTitle ?? 'Теорія')

@section('content')
    @php($categoryPages = $categoryPages ?? collect())
    @php($routePrefix = $routePrefix ?? 'theory')

    <div class="min-h-screen">
        {{-- Hero Section --}}
        <header class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 text-white mb-8">
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
                <div class="flex items-center gap-3 mb-4">
                    <span class="inline-flex items-center gap-1.5 rounded-lg bg-white/10 backdrop-blur-sm px-3 py-1.5 text-xs font-bold tracking-wide">
                        <svg class="h-4 w-4 text-amber-400" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838L7.667 9.088l1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3zM3.31 9.397L5 10.12v4.102a8.969 8.969 0 00-1.05-.174 1 1 0 01-.89-.89 11.115 11.115 0 01.25-3.762zM9.3 16.573A9.026 9.026 0 007 14.935v-3.957l1.818.78a3 3 0 002.364 0l5.508-2.361a11.026 11.026 0 01.25 3.762 1 1 0 01-.89.89 8.968 8.968 0 00-5.35 2.524 1 1 0 01-1.4 0zM6 18a1 1 0 001-1v-2.065a8.935 8.935 0 00-2-.712V17a1 1 0 001 1z"/>
                        </svg>
                        {{ $categories->count() }} {{ trans_choice('категорій|категорія|категорії', $categories->count()) }}
                    </span>
                </div>

                <h1 class="text-2xl md:text-3xl lg:text-4xl font-extrabold tracking-tight leading-tight mb-4">
                    {{ $sectionTitle ?? 'Теорія' }}
                </h1>

                <p class="text-base md:text-lg text-white/80 leading-relaxed max-w-3xl">
                    Вивчай граматику англійської мови разом з Gramlyze. Обери категорію та почни опановувати нові теми.
                </p>
            </div>
        </header>

        {{-- Main Content Grid --}}
        <div class="grid gap-8 lg:grid-cols-[280px_1fr] xl:grid-cols-[320px_1fr]">
            {{-- Left Sidebar --}}
            <aside class="hidden lg:block">
                <div class="sticky top-24 space-y-5">
                    {{-- Categories Navigation --}}
                    <div class="rounded-2xl border border-border/60 bg-card p-5">
                        <h3 class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-muted-foreground mb-4">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                            </svg>
                            Категорії
                        </h3>
                        <nav class="space-y-1">
                            @forelse($categories as $category)
                                @php($isActive = isset($selectedCategory) && $selectedCategory && $selectedCategory->is($category))
                                <a 
                                    href="{{ route($routePrefix . '.category', $category->slug) }}"
                                    class="flex items-center justify-between gap-2 rounded-lg px-3 py-2 text-sm transition-all {{ $isActive ? 'bg-primary text-primary-foreground font-medium' : 'text-muted-foreground hover:text-foreground hover:bg-muted/50' }}"
                                >
                                    <span class="truncate">{{ $category->title }}</span>
                                    @if(isset($category->pages_count))
                                        <span class="text-xs opacity-60">{{ $category->pages_count }}</span>
                                    @endif
                                </a>
                            @empty
                                <p class="text-sm text-muted-foreground">Немає категорій.</p>
                            @endforelse
                        </nav>
                    </div>

                    {{-- Quick Stats --}}
                    <div class="rounded-2xl border border-border/60 bg-gradient-to-br from-muted/30 to-muted/10 p-5">
                        <h3 class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-muted-foreground mb-4">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                            Статистика
                        </h3>
                        <div class="grid gap-3">
                            <div class="flex items-center gap-3 rounded-xl bg-card px-4 py-3 border border-transparent">
                                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary/10 text-primary">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                    </svg>
                                </div>
                                <div>
                                    <div class="text-lg font-bold text-foreground">{{ $categories->count() }}</div>
                                    <div class="text-xs text-muted-foreground">Категорій</div>
                                </div>
                            </div>
                            <div class="flex items-center gap-3 rounded-xl bg-card px-4 py-3 border border-transparent">
                                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-secondary/10 text-secondary">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                </div>
                                <div>
                                    <div class="text-lg font-bold text-foreground">{{ $categories->sum('pages_count') }}</div>
                                    <div class="text-xs text-muted-foreground">Сторінок теорії</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </aside>

            {{-- Primary Content Area --}}
            <div class="min-w-0 space-y-6">
                {{-- Selected Category Header (if selected) --}}
                @if(isset($selectedCategory) && $selectedCategory)
                    @php($categoryDescription = $categoryDescription ?? ['hasBlocks' => false])
                    
                    @if($categoryDescription['hasBlocks'] ?? false)
                        @include('engram.pages.partials.grammar-card', [
                            'page' => $selectedCategory,
                            'subtitleBlock' => $categoryDescription['subtitleBlock'] ?? null,
                            'columns' => $categoryDescription['columns'] ?? [],
                            'locale' => $categoryDescription['locale'] ?? app()->getLocale(),
                        ])
                    @else
                        <div class="rounded-2xl border border-border/60 bg-card p-6">
                            <div class="flex items-center gap-4">
                                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-primary to-primary/70 text-white">
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                    </svg>
                                </div>
                                <div>
                                    <h2 class="text-xl font-bold text-foreground">{{ $selectedCategory->title }}</h2>
                                    <p class="text-sm text-muted-foreground">
                                        {{ $categoryPages->count() }} {{ trans_choice('сторінок|сторінка|сторінки', $categoryPages->count()) }} у цій категорії
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif
                @else
                    {{-- All Categories View --}}
                    <div class="rounded-2xl border border-border/60 bg-card p-6">
                        <div class="flex items-center gap-4">
                            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-primary to-primary/70 text-white">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-xl font-bold text-foreground">Усі категорії теорії</h2>
                                <p class="text-sm text-muted-foreground">
                                    Обери категорію, щоб переглянути доступні сторінки теорії
                                </p>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Categories Grid --}}
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @forelse($categories as $category)
                        @php($isActive = isset($selectedCategory) && $selectedCategory && $selectedCategory->is($category))
                        <a 
                            href="{{ route($routePrefix . '.category', $category->slug) }}"
                            class="group relative overflow-hidden rounded-xl border {{ $isActive ? 'border-primary/50 bg-primary/5' : 'border-border/60 bg-card' }} p-5 transition-all hover:border-primary/40 hover:shadow-md"
                        >
                            {{-- Category Icon --}}
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg {{ $isActive ? 'bg-primary text-primary-foreground' : 'bg-muted text-muted-foreground group-hover:bg-primary/10 group-hover:text-primary' }} transition-colors mb-4">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                </svg>
                            </div>

                            <h3 class="text-base font-bold {{ $isActive ? 'text-primary' : 'text-foreground' }} mb-1">
                                {{ $category->title }}
                            </h3>

                            @if(isset($category->pages_count) && $category->pages_count > 0)
                                <p class="text-sm text-muted-foreground">
                                    {{ $category->pages_count }} {{ trans_choice('сторінок|сторінка|сторінки', $category->pages_count) }}
                                </p>
                            @endif

                            {{-- Hover Arrow --}}
                            <div class="absolute right-4 top-1/2 -translate-y-1/2 opacity-0 group-hover:opacity-100 transition-opacity">
                                <svg class="h-5 w-5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                                </svg>
                            </div>

                            {{-- Active Indicator --}}
                            @if($isActive)
                                <div class="absolute top-0 right-0 m-3">
                                    <span class="flex h-2 w-2 rounded-full bg-primary"></span>
                                </div>
                            @endif
                        </a>
                    @empty
                        <div class="col-span-full rounded-2xl border border-dashed border-muted p-8 text-center text-muted-foreground">
                            Поки що немає категорій теорії.
                        </div>
                    @endforelse
                </div>

                {{-- Category Pages Grid (if category selected) --}}
                @if(isset($selectedCategory) && $selectedCategory && $categoryPages->isNotEmpty())
                    <div class="rounded-2xl border border-border/60 bg-card overflow-hidden">
                        <div class="border-b border-border/40 bg-muted/30 px-5 py-4">
                            <h2 class="flex items-center gap-3 text-lg font-bold text-foreground">
                                <svg class="h-5 w-5 text-muted-foreground" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                Сторінки теорії у категорії «{{ $selectedCategory->title }}»
                            </h2>
                        </div>
                        <div class="p-5">
                            <div class="grid gap-3 sm:grid-cols-2">
                                @foreach($categoryPages as $page)
                                    <a 
                                        href="{{ route($routePrefix . '.show', [$selectedCategory->slug, $page->slug]) }}"
                                        class="group relative rounded-xl border border-border/50 bg-gradient-to-br from-muted/20 to-transparent p-4 transition-all hover:border-primary/30 hover:shadow-sm"
                                    >
                                        <h3 class="text-base font-semibold text-foreground mb-1 group-hover:text-primary transition-colors">
                                            {{ $page->title }}
                                        </h3>
                                        
                                        @if(!empty($page->text))
                                            <p class="text-sm text-muted-foreground line-clamp-2">
                                                {{ $page->text }}
                                            </p>
                                        @endif

                                        {{-- Hover indicator --}}
                                        <div class="absolute right-3 top-1/2 -translate-y-1/2 opacity-0 group-hover:opacity-100 transition-opacity">
                                            <svg class="h-4 w-4 text-primary/50" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                                            </svg>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @elseif(isset($selectedCategory) && $selectedCategory)
                    <div class="rounded-2xl border border-dashed border-muted p-8 text-center text-muted-foreground">
                        Поки що в цій категорії немає сторінок. Спробуйте іншу категорію.
                    </div>
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
                                <h2 class="text-lg font-bold text-foreground">Тести по темі</h2>
                                <p class="text-xs text-muted-foreground">Перевір свої знання з цієї категорії</p>
                            </div>
                        </div>
                        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach($autoGeneratedTests as $test)
                                <x-auto-generated-test-card :test="$test" />
                            @endforeach
                        </div>
                    </section>
                @endif

                {{-- Category Tags Section --}}
                @if(isset($selectedCategory) && $selectedCategory && $selectedCategory->tags->isNotEmpty())
                    <section class="rounded-2xl border border-border/60 bg-card p-6" x-data="{ show: false }">
                        <button 
                            @click="show = !show"
                            class="flex w-full items-center justify-between text-left"
                        >
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-secondary/10 text-secondary">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                    </svg>
                                </div>
                                <div>
                                    <h2 class="text-lg font-bold text-foreground">Теги категорії</h2>
                                    <p class="text-xs text-muted-foreground">{{ $selectedCategory->tags->count() }} тегів пов'язано з цією категорією</p>
                                </div>
                            </div>
                            <svg 
                                class="h-5 w-5 text-muted-foreground transition-transform" 
                                :class="{ 'rotate-180': show }"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div x-show="show" x-collapse class="mt-5">
                            <div class="flex flex-wrap gap-1.5">
                                @foreach($selectedCategory->tags as $tag)
                                    <span class="inline-flex items-center rounded-md bg-muted/60 px-2.5 py-1 text-xs text-muted-foreground">
                                        {{ $tag->name }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    </section>
                @endif

                {{-- Related Tests Section --}}
                @if(isset($relatedTests) && $relatedTests->isNotEmpty())
                    <section class="rounded-2xl border border-border/60 bg-card p-6" x-data="{ show: false }">
                        <button 
                            @click="show = !show"
                            class="flex w-full items-center justify-between text-left"
                        >
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-accent/10 text-accent">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                                    </svg>
                                </div>
                                <div>
                                    <h2 class="text-lg font-bold text-foreground">Пов'язані тести</h2>
                                    <p class="text-xs text-muted-foreground">{{ $relatedTests->count() }} тестів за тегами</p>
                                </div>
                            </div>
                            <svg 
                                class="h-5 w-5 text-muted-foreground transition-transform" 
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
                class="flex items-center gap-2 rounded-full bg-foreground px-5 py-3 text-sm font-semibold text-background shadow-lg transition-transform hover:scale-105"
            >
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
                Категорії
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
                    {{-- Categories --}}
                    <div>
                        <h4 class="text-xs font-bold uppercase tracking-wider text-muted-foreground mb-2">Категорії теорії</h4>
                        <nav class="space-y-1">
                            @foreach($categories as $category)
                                @php($isActive = isset($selectedCategory) && $selectedCategory && $selectedCategory->is($category))
                                <a 
                                    href="{{ route($routePrefix . '.category', $category->slug) }}"
                                    class="flex items-center justify-between rounded-lg px-3 py-2 text-sm {{ $isActive ? 'bg-primary text-primary-foreground font-medium' : 'text-muted-foreground hover:bg-muted' }}"
                                >
                                    <span>{{ $category->title }}</span>
                                    @if(isset($category->pages_count))
                                        <span class="text-xs opacity-60">{{ $category->pages_count }}</span>
                                    @endif
                                </a>
                            @endforeach
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
