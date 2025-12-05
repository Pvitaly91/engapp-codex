@extends('layouts.engram')

@section('title', ($selectedCategory->title ?? 'Категорія') . ' — ' . ($sectionTitle ?? 'Теорія'))

@section('content')
    @php($categoryPages = $categoryPages ?? collect())
    @php($routePrefix = $routePrefix ?? 'theory')
    @php($categoryDescription = $categoryDescription ?? ['hasBlocks' => false])

    <div class="min-h-screen">
        {{-- Breadcrumb Navigation --}}
        <nav class="mb-6 flex items-center gap-1.5 text-xs text-muted-foreground" aria-label="Breadcrumb">
            <a href="{{ route('home') }}" class="hover:text-primary transition-colors">
                Головна
            </a>
            <span class="text-border">/</span>
            <a href="{{ route($routePrefix . '.index') }}" class="hover:text-primary transition-colors">
                {{ $sectionTitle ?? 'Теорія' }}
            </a>
            <span class="text-border">/</span>
            <span class="font-medium text-foreground truncate max-w-[180px]">{{ $selectedCategory->title }}</span>
        </nav>

        {{-- Hero Section for Category --}}
        <header class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 text-white mb-8">
            {{-- Decorative Pattern --}}
            <div class="absolute inset-0 opacity-10">
                <svg class="h-full w-full" viewBox="0 0 100 100" preserveAspectRatio="none">
                    <defs>
                        <pattern id="grid-pattern-theory-category" width="10" height="10" patternUnits="userSpaceOnUse">
                            <path d="M 10 0 L 0 0 0 10" fill="none" stroke="currentColor" stroke-width="0.5"/>
                        </pattern>
                    </defs>
                    <rect width="100%" height="100%" fill="url(#grid-pattern-theory-category)"/>
                </svg>
            </div>
            
            <div class="relative px-6 py-8 md:px-8 md:py-10">
                <div class="flex items-center gap-3 mb-4">
                    <span class="inline-flex items-center gap-1.5 rounded-lg bg-white/10 backdrop-blur-sm px-3 py-1.5 text-xs font-bold tracking-wide">
                        <svg class="h-4 w-4 text-amber-400" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"/>
                        </svg>
                        Категорія
                    </span>
                    <span class="h-1 w-1 rounded-full bg-white/30"></span>
                    <span class="inline-flex items-center gap-1.5 rounded-lg bg-white/10 backdrop-blur-sm px-3 py-1.5 text-xs font-bold tracking-wide">
                        {{ $categoryPages->count() }} {{ trans_choice('сторінок|сторінка|сторінки', $categoryPages->count()) }}
                    </span>
                </div>

                <h1 class="text-2xl md:text-3xl lg:text-4xl font-extrabold tracking-tight leading-tight mb-4">
                    {{ $selectedCategory->title }}
                </h1>

                <p class="text-base md:text-lg text-white/80 leading-relaxed max-w-3xl">
                    Матеріали з категорії «{{ $selectedCategory->title }}». Обери сторінку, щоб почати вивчення.
                </p>

                {{-- Quick navigation back --}}
                <div class="mt-6 flex flex-wrap gap-2">
                    <a 
                        href="{{ route($routePrefix . '.index') }}"
                        class="inline-flex items-center gap-1.5 rounded-full bg-white/10 backdrop-blur-sm px-4 py-2 text-sm font-medium text-white hover:bg-white/20 transition"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        Усі категорії
                    </a>
                </div>
            </div>
        </header>

        {{-- Mobile Sidebar (visible only on mobile/tablet) --}}
        <div
            class="lg:hidden space-y-4 mb-8"
            x-data="{
                showCategories: false,
                showPages: {{ $categoryPages->isNotEmpty() ? 'true' : 'false' }}
            }"
        >
            {{-- Categories accordion --}}
            <div class="rounded-2xl border border-border/80 bg-card shadow-soft">
                <button
                    type="button"
                    class="flex w-full items-center justify-between gap-3 px-4 py-3 text-left text-sm font-semibold"
                    @click="showCategories = !showCategories"
                    :aria-expanded="showCategories"
                >
                    <span class="flex items-center gap-2">
                        <svg class="h-4 w-4 text-muted-foreground" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                        Категорії
                    </span>
                    <span class="flex items-center gap-2 text-xs font-medium text-muted-foreground">
                        <span>{{ $selectedCategory->title ?? 'Оберіть категорію' }}</span>
                        <svg
                            class="h-4 w-4 transition-transform"
                            :class="{ 'rotate-180': showCategories }"
                            viewBox="0 0 20 20"
                            fill="currentColor"
                            aria-hidden="true"
                        >
                            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 10.94l3.71-3.71a.75.75 0 0 1 1.08 1.04l-4.25 4.25a.75.75 0 0 1-1.08 0L5.21 8.27a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd" />
                        </svg>
                    </span>
                </button>
                <div x-show="showCategories" x-collapse class="border-t border-border/80">
                    <nav class="space-y-1 px-4 py-3 max-h-[50vh] overflow-y-auto">
                        @if($categories->isNotEmpty())
                            @include('engram.theory.partials.nested-category-nav-mobile', [
                                'categories' => $categories,
                                'selectedCategory' => $selectedCategory,
                                'routePrefix' => $routePrefix,
                                'showPagesInNav' => false,
                            ])
                        @else
                            <p class="px-3 py-2 text-sm text-muted-foreground">Немає категорій.</p>
                        @endif
                    </nav>
                </div>
            </div>

            {{-- Pages in category accordion --}}
            @if($categoryPages->isNotEmpty())
                <div class="rounded-2xl border border-border/80 bg-card shadow-soft">
                    <button
                        type="button"
                        class="flex w-full items-center justify-between gap-3 px-4 py-3 text-left text-sm font-semibold"
                        @click="showPages = !showPages"
                        :aria-expanded="showPages"
                    >
                        <span class="flex items-center gap-2">
                            <svg class="h-4 w-4 text-muted-foreground" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Сторінки розділу
                        </span>
                        <span class="flex items-center gap-2 text-xs font-medium text-muted-foreground">
                            <span>{{ $categoryPages->count() }} {{ trans_choice('сторінок|сторінка|сторінки', $categoryPages->count()) }}</span>
                            <svg
                                class="h-4 w-4 transition-transform"
                                :class="{ 'rotate-180': showPages }"
                                viewBox="0 0 20 20"
                                fill="currentColor"
                                aria-hidden="true"
                            >
                                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 10.94l3.71-3.71a.75.75 0 0 1 1.08 1.04l-4.25 4.25a.75.75 0 0 1-1.08 0L5.21 8.27a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd" />
                            </svg>
                        </span>
                    </button>
                    <div x-show="showPages" x-collapse class="border-t border-border/80">
                        <nav class="space-y-1 px-4 py-3 max-h-[50vh] overflow-y-auto">
                            @foreach($categoryPages as $pageItem)
                                <a
                                    href="{{ route($routePrefix . '.show', [$selectedCategory->slug, $pageItem->slug]) }}"
                                    class="block rounded-xl px-3 py-2 text-sm transition hover:bg-muted/80 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-secondary/40 text-muted-foreground"
                                >
                                    {{ $pageItem->title }}
                                </a>
                            @endforeach
                        </nav>
                    </div>
                </div>
            @endif
        </div>

        {{-- Main Content Grid --}}
        <div class="grid gap-8 lg:grid-cols-[280px_1fr] xl:grid-cols-[320px_1fr]">
            {{-- Left Sidebar (desktop only) --}}
            <aside class="hidden lg:block">
                <div id="theory-sidebar" class="sticky top-24 space-y-5 transition-[top] duration-200 max-h-[calc(100vh-7rem)] overflow-y-auto pr-1">
                    {{-- Categories Navigation --}}
                    <div class="rounded-2xl border border-border/60 bg-card p-5">
                        <h3 class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-muted-foreground mb-4">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                            </svg>
                            Категорії
                        </h3>
                        <nav id="category-nav-scroll" class="space-y-1 max-h-[40vh] overflow-y-auto pr-1">
                            @if($categories->isNotEmpty())
                                @include('engram.theory.partials.nested-category-nav', [
                                    'categories' => $categories,
                                    'selectedCategory' => $selectedCategory,
                                    'routePrefix' => $routePrefix,
                                    'showPagesInNav' => false,
                                ])
                            @else
                                <p class="text-sm text-muted-foreground">Немає категорій.</p>
                            @endif
                        </nav>
                    </div>

                    {{-- Pages in Category --}}
                    @if($categoryPages->isNotEmpty())
                        <div class="rounded-2xl border border-border/60 bg-card p-5">
                            <h3 class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-muted-foreground mb-4">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                {{ $selectedCategory->title }}
                            </h3>
                            <nav class="space-y-1">
                                @foreach($categoryPages as $pageItem)
                                    <a 
                                        href="{{ route($routePrefix . '.show', [$selectedCategory->slug, $pageItem->slug]) }}"
                                        class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm text-muted-foreground hover:text-foreground hover:bg-muted/50 transition-all"
                                    >
                                        <span class="h-1 w-1 rounded-full bg-border flex-shrink-0"></span>
                                        <span class="truncate">{{ $pageItem->title }}</span>
                                    </a>
                                @endforeach
                            </nav>
                        </div>
                    @endif

                    {{-- Tags Section --}}
                    @if($selectedCategory->tags->isNotEmpty())
                        <div class="rounded-2xl border border-border/60 bg-card p-5" x-data="{ show: false }">
                            <button 
                                @click="show = !show"
                                class="flex w-full items-center justify-between text-left"
                            >
                                <h3 class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-muted-foreground">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                    </svg>
                                    Теги ({{ $selectedCategory->tags->count() }})
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
                                    @foreach($selectedCategory->tags as $tag)
                                        <span class="inline-flex items-center rounded-md bg-muted/60 px-2 py-1 text-xs text-muted-foreground">
                                            {{ $tag->name }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
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
                                href="{{ route($routePrefix . '.index') }}"
                                class="flex items-center gap-3 rounded-xl bg-card px-4 py-3 text-sm font-medium text-foreground transition-all hover:shadow-sm hover:border-primary/20 border border-transparent"
                            >
                                <svg class="h-5 w-5 text-muted-foreground" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
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
                            <h2 class="text-2xl md:text-3xl font-bold text-foreground mb-3">Матеріали для вивчення</h2>
                            <p class="text-muted-foreground max-w-2xl mx-auto">
                                Обери тему та почни вивчати матеріали з категорії «{{ $selectedCategory->title }}»
                            </p>
                        </div>

                        <div class="grid gap-5 md:grid-cols-2 lg:grid-cols-2">
                            @foreach($categoryPages as $index => $page)
                                <a 
                                    href="{{ route($routePrefix . '.show', [$selectedCategory->slug, $page->slug]) }}"
                                    class="group relative overflow-hidden rounded-2xl border border-border/60 bg-card transition-all hover:border-primary/30 hover:shadow-xl block"
                                >
                                    {{-- Card Header with Gradient and Title --}}
                                    <div class="relative min-h-[8rem] bg-gradient-to-br {{ ['from-indigo-500 to-purple-600', 'from-emerald-500 to-teal-600', 'from-blue-500 to-cyan-600', 'from-amber-500 to-orange-600', 'from-rose-500 to-pink-600', 'from-violet-500 to-purple-600'][$index % 6] }} p-6 transition-opacity group-hover:opacity-90">
                                        {{-- Decorative elements --}}
                                        <div class="absolute top-0 right-0 w-20 h-20 bg-white/10 rounded-full -translate-y-1/2 translate-x-1/2"></div>
                                        <div class="absolute bottom-0 left-0 w-16 h-16 bg-white/10 rounded-full translate-y-1/2 -translate-x-1/2"></div>
                                        
                                        {{-- Page number --}}
                                        <div class="absolute top-4 right-4 flex h-8 w-8 items-center justify-center rounded-full bg-white/20 text-white text-sm font-bold">
                                            {{ $index + 1 }}
                                        </div>
                                        
                                        {{-- Icon and Title --}}
                                        <div class="flex items-start gap-4">
                                            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-white/20 backdrop-blur-sm text-white flex-shrink-0">
                                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                </svg>
                                            </div>
                                            <h3 class="text-lg font-bold text-white pr-10 leading-snug">
                                                {{ $page->title }}
                                            </h3>
                                        </div>
                                    </div>
                                    
                                    {{-- Card Body --}}
                                    <div class="p-5">
                                        @if(!empty($page->text))
                                            <p class="text-sm text-muted-foreground line-clamp-2">
                                                {{ $page->text }}
                                            </p>
                                        @endif
                                        
                                        {{-- Arrow indicator --}}
                                        <div class="flex items-center justify-end mt-3">
                                            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-muted/50 text-muted-foreground group-hover:bg-primary group-hover:text-primary-foreground transition-all">
                                                <svg class="h-4 w-4 transform group-hover:translate-x-0.5 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                                                </svg>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </section>
                @else
                    <div class="rounded-2xl border border-dashed border-muted p-8 text-center text-muted-foreground">
                        Поки що в цій категорії немає сторінок теорії.
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
                                <h2 class="text-lg font-bold text-foreground">Практичні тести</h2>
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


    </div>
@endsection
