@extends('layouts.engram')

@section('title', $sectionTitle ?? 'Теорія')

@section('content')
    @php($categoryPages = $categoryPages ?? collect())
    @php($routePrefix = $routePrefix ?? 'theory')
    @php($categoryDescription = $categoryDescription ?? ['hasBlocks' => false])
    @php($totalPages = $categories->sum(fn($c) => $c->pages_count ?? 0))

    <div class="min-h-screen">
        {{-- Hero Section with General Overview --}}
        <header class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-indigo-600 via-purple-600 to-pink-500 text-white mb-10">
            {{-- Animated Background Pattern --}}
            <div class="absolute inset-0 opacity-20">
                <svg class="h-full w-full" viewBox="0 0 100 100" preserveAspectRatio="none">
                    <defs>
                        <pattern id="theory-hero-pattern" width="20" height="20" patternUnits="userSpaceOnUse">
                            <circle cx="2" cy="2" r="1" fill="currentColor" opacity="0.5"/>
                            <circle cx="12" cy="12" r="1.5" fill="currentColor" opacity="0.3"/>
                        </pattern>
                    </defs>
                    <rect width="100%" height="100%" fill="url(#theory-hero-pattern)"/>
                </svg>
            </div>
            
            {{-- Decorative Shapes --}}
            <div class="absolute -top-20 -right-20 w-64 h-64 bg-white/10 rounded-full blur-3xl"></div>
            <div class="absolute -bottom-20 -left-20 w-48 h-48 bg-yellow-400/20 rounded-full blur-3xl"></div>
            
            <div class="relative px-8 py-12 md:px-12 md:py-16 lg:py-20">
                <div class="max-w-4xl">
                    {{-- Badge --}}
                    <div class="flex flex-wrap items-center gap-3 mb-6">
                        <span class="inline-flex items-center gap-2 rounded-full bg-white/20 backdrop-blur-sm px-4 py-2 text-sm font-semibold">
                            <svg class="h-5 w-5 text-yellow-300" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838L7.667 9.088l1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3zM3.31 9.397L5 10.12v4.102a8.969 8.969 0 00-1.05-.174 1 1 0 01-.89-.89 11.115 11.115 0 01.25-3.762zM9.3 16.573A9.026 9.026 0 007 14.935v-3.957l1.818.78a3 3 0 002.364 0l5.508-2.361a11.026 11.026 0 01.25 3.762 1 1 0 01-.89.89 8.968 8.968 0 00-5.35 2.524 1 1 0 01-1.4 0zM6 18a1 1 0 001-1v-2.065a8.935 8.935 0 00-2-.712V17a1 1 0 001 1z"/>
                            </svg>
                            Розділ теорії
                        </span>
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-white/15 px-3 py-1.5 text-xs font-medium">
                            {{ $categories->count() }} категорій
                        </span>
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-white/15 px-3 py-1.5 text-xs font-medium">
                            {{ $totalPages }} уроків
                        </span>
                    </div>

                    {{-- Title --}}
                    <h1 class="text-3xl md:text-4xl lg:text-5xl font-black tracking-tight leading-tight mb-6">
                        Граматика англійської мови
                    </h1>

                    {{-- Description --}}
                    <p class="text-lg md:text-xl text-white/90 leading-relaxed mb-8 max-w-2xl">
                        Вивчай граматику англійської мови від базових понять до просунутих тем. 
                        Структуровані уроки, зрозумілі пояснення та практичні приклади допоможуть тобі 
                        опанувати мову на рівні від A1 до B2.
                    </p>

                    {{-- Quick Action Buttons --}}
                    <div class="flex flex-wrap gap-3">
                        @if($categories->first())
                            <a 
                                href="{{ route($routePrefix . '.category', $categories->first()->slug) }}"
                                class="inline-flex items-center gap-2 rounded-xl bg-white px-6 py-3 text-sm font-bold text-indigo-600 shadow-lg transition hover:-translate-y-0.5 hover:shadow-xl"
                            >
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Почати навчання
                            </a>
                        @endif
                        <a 
                            href="#categories-section"
                            class="inline-flex items-center gap-2 rounded-xl bg-white/20 backdrop-blur-sm px-6 py-3 text-sm font-bold text-white transition hover:bg-white/30"
                        >
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                            </svg>
                            Усі категорії
                        </a>
                    </div>
                </div>
            </div>
        </header>

        {{-- What You'll Learn Section --}}
        <section class="mb-12">
            <div class="text-center mb-8">
                <h2 class="text-2xl md:text-3xl font-bold text-foreground mb-3">Що ти вивчиш</h2>
                <p class="text-muted-foreground max-w-2xl mx-auto">
                    Основні теми англійської граматики, структуровані для поступового та ефективного навчання
                </p>
            </div>

            {{-- Features Grid --}}
            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-4">
                {{-- Feature 1: Basic Grammar --}}
                <div class="group relative overflow-hidden rounded-2xl border border-border/60 bg-card p-6 transition-all hover:border-emerald-500/40 hover:shadow-lg">
                    <div class="absolute top-0 right-0 w-24 h-24 bg-gradient-to-bl from-emerald-500/10 to-transparent rounded-bl-full"></div>
                    <div class="relative">
                        <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-500 to-emerald-600 text-white mb-4 shadow-lg shadow-emerald-500/20">
                            <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-bold text-foreground mb-2">Базова граматика</h3>
                        <p class="text-sm text-muted-foreground leading-relaxed">
                            Частини мови, структура речення, порядок слів, типи речень та базові сполучники
                        </p>
                        <div class="mt-4 flex items-center gap-1.5 text-xs text-emerald-600 font-medium">
                            <span class="px-2 py-0.5 rounded-full bg-emerald-500/10">A1</span>
                            <span class="px-2 py-0.5 rounded-full bg-emerald-500/10">A2</span>
                        </div>
                    </div>
                </div>

                {{-- Feature 2: Nouns & Articles --}}
                <div class="group relative overflow-hidden rounded-2xl border border-border/60 bg-card p-6 transition-all hover:border-blue-500/40 hover:shadow-lg">
                    <div class="absolute top-0 right-0 w-24 h-24 bg-gradient-to-bl from-blue-500/10 to-transparent rounded-bl-full"></div>
                    <div class="relative">
                        <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-500 to-blue-600 text-white mb-4 shadow-lg shadow-blue-500/20">
                            <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-bold text-foreground mb-2">Іменники та артиклі</h3>
                        <p class="text-sm text-muted-foreground leading-relaxed">
                            Артиклі a/an/the, злічувані та незлічувані іменники, квантифікатори some/any/much/many
                        </p>
                        <div class="mt-4 flex items-center gap-1.5 text-xs text-blue-600 font-medium">
                            <span class="px-2 py-0.5 rounded-full bg-blue-500/10">A1</span>
                            <span class="px-2 py-0.5 rounded-full bg-blue-500/10">A2</span>
                            <span class="px-2 py-0.5 rounded-full bg-blue-500/10">B1</span>
                        </div>
                    </div>
                </div>

                {{-- Feature 3: Adjectives --}}
                <div class="group relative overflow-hidden rounded-2xl border border-border/60 bg-card p-6 transition-all hover:border-amber-500/40 hover:shadow-lg">
                    <div class="absolute top-0 right-0 w-24 h-24 bg-gradient-to-bl from-amber-500/10 to-transparent rounded-bl-full"></div>
                    <div class="relative">
                        <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-amber-500 to-orange-500 text-white mb-4 shadow-lg shadow-amber-500/20">
                            <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-bold text-foreground mb-2">Прикметники</h3>
                        <p class="text-sm text-muted-foreground leading-relaxed">
                            Ступені порівняння прикметників: comparative та superlative, правила утворення
                        </p>
                        <div class="mt-4 flex items-center gap-1.5 text-xs text-amber-600 font-medium">
                            <span class="px-2 py-0.5 rounded-full bg-amber-500/10">A2</span>
                            <span class="px-2 py-0.5 rounded-full bg-amber-500/10">B1</span>
                        </div>
                    </div>
                </div>

                {{-- Feature 4: Word Order --}}
                <div class="group relative overflow-hidden rounded-2xl border border-border/60 bg-card p-6 transition-all hover:border-purple-500/40 hover:shadow-lg">
                    <div class="absolute top-0 right-0 w-24 h-24 bg-gradient-to-bl from-purple-500/10 to-transparent rounded-bl-full"></div>
                    <div class="relative">
                        <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-purple-500 to-purple-600 text-white mb-4 shadow-lg shadow-purple-500/20">
                            <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-bold text-foreground mb-2">Порядок слів</h3>
                        <p class="text-sm text-muted-foreground leading-relaxed">
                            Структура S-V-O, питання та заперечення, прислівники, інверсія та cleft-речення
                        </p>
                        <div class="mt-4 flex items-center gap-1.5 text-xs text-purple-600 font-medium">
                            <span class="px-2 py-0.5 rounded-full bg-purple-500/10">A1</span>
                            <span class="px-2 py-0.5 rounded-full bg-purple-500/10">B2</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- Categories Section --}}
        <section id="categories-section" class="scroll-mt-24">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h2 class="text-2xl md:text-3xl font-bold text-foreground mb-2">Категорії теорії</h2>
                    <p class="text-muted-foreground">Обери категорію для перегляду детальних уроків</p>
                </div>
                <div class="hidden md:flex items-center gap-4 text-sm text-muted-foreground">
                    <div class="flex items-center gap-2">
                        <div class="h-3 w-3 rounded-full bg-primary"></div>
                        <span>{{ $categories->count() }} категорій</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="h-3 w-3 rounded-full bg-secondary"></div>
                        <span>{{ $totalPages }} уроків</span>
                    </div>
                </div>
            </div>

            {{-- Categories Grid --}}
            <div class="grid gap-5 md:grid-cols-2 lg:grid-cols-3">
                @forelse($categories as $index => $category)
                    @php
                        $gradients = [
                            'from-indigo-500 to-purple-600',
                            'from-emerald-500 to-teal-600',
                            'from-blue-500 to-cyan-600',
                            'from-amber-500 to-orange-600',
                            'from-rose-500 to-pink-600',
                            'from-violet-500 to-purple-600',
                        ];
                        $gradient = $gradients[$index % count($gradients)];
                    @endphp
                    <a 
                        href="{{ route($routePrefix . '.category', $category->slug) }}"
                        class="group relative overflow-hidden rounded-2xl border border-border/60 bg-card transition-all hover:border-primary/30 hover:shadow-xl hover:-translate-y-1"
                    >
                        {{-- Card Header with Gradient --}}
                        <div class="relative h-32 bg-gradient-to-br {{ $gradient }} p-6">
                            {{-- Decorative elements --}}
                            <div class="absolute top-0 right-0 w-20 h-20 bg-white/10 rounded-full -translate-y-1/2 translate-x-1/2"></div>
                            <div class="absolute bottom-0 left-0 w-16 h-16 bg-white/10 rounded-full translate-y-1/2 -translate-x-1/2"></div>
                            
                            {{-- Category number --}}
                            <div class="absolute top-4 right-4 flex h-8 w-8 items-center justify-center rounded-full bg-white/20 text-white text-sm font-bold">
                                {{ $index + 1 }}
                            </div>
                            
                            {{-- Icon --}}
                            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-white/20 backdrop-blur-sm text-white">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                </svg>
                            </div>
                        </div>
                        
                        {{-- Card Body --}}
                        <div class="p-5">
                            <h3 class="text-lg font-bold text-foreground mb-2 group-hover:text-primary transition-colors">
                                {{ $category->title }}
                            </h3>
                            
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-muted-foreground">
                                    @if(isset($category->pages_count) && $category->pages_count > 0)
                                        {{ $category->pages_count }} {{ trans_choice('уроків|урок|уроки', $category->pages_count) }}
                                    @else
                                        Немає уроків
                                    @endif
                                </span>
                                
                                {{-- Arrow indicator --}}
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-muted/50 text-muted-foreground group-hover:bg-primary group-hover:text-primary-foreground transition-all">
                                    <svg class="h-4 w-4 transform group-hover:translate-x-0.5 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="col-span-full rounded-2xl border border-dashed border-muted p-12 text-center">
                        <div class="flex justify-center mb-4">
                            <div class="flex h-16 w-16 items-center justify-center rounded-full bg-muted/50 text-muted-foreground">
                                <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                </svg>
                            </div>
                        </div>
                        <h3 class="text-lg font-semibold text-foreground mb-2">Категорії поки відсутні</h3>
                        <p class="text-muted-foreground">Матеріали теорії з'являться тут незабаром.</p>
                    </div>
                @endforelse
            </div>
        </section>

        {{-- Learning Path Section --}}
        <section class="mt-16 mb-12">
            <div class="rounded-3xl border border-border/60 bg-gradient-to-br from-muted/30 via-card to-muted/20 p-8 md:p-12">
                <div class="grid gap-8 lg:grid-cols-2 lg:items-center">
                    <div>
                        <div class="inline-flex items-center gap-2 rounded-full bg-primary/10 text-primary px-4 py-2 text-sm font-semibold mb-4">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Рекомендований шлях
                        </div>
                        <h2 class="text-2xl md:text-3xl font-bold text-foreground mb-4">
                            Як ефективно вивчати граматику?
                        </h2>
                        <p class="text-muted-foreground leading-relaxed mb-6">
                            Почни з базових тем і поступово переходь до складніших. 
                            Кожен урок включає теоретичний матеріал, приклади та практичні вправи 
                            для закріплення знань.
                        </p>
                        <div class="space-y-3">
                            <div class="flex items-start gap-3">
                                <div class="flex h-6 w-6 items-center justify-center rounded-full bg-emerald-500/20 text-emerald-600 text-xs font-bold flex-shrink-0 mt-0.5">1</div>
                                <p class="text-sm text-foreground"><strong>Почни з базової граматики</strong> — частини мови та структура речення</p>
                            </div>
                            <div class="flex items-start gap-3">
                                <div class="flex h-6 w-6 items-center justify-center rounded-full bg-blue-500/20 text-blue-600 text-xs font-bold flex-shrink-0 mt-0.5">2</div>
                                <p class="text-sm text-foreground"><strong>Вивчи артиклі та іменники</strong> — a/an/the, злічувані/незлічувані</p>
                            </div>
                            <div class="flex items-start gap-3">
                                <div class="flex h-6 w-6 items-center justify-center rounded-full bg-amber-500/20 text-amber-600 text-xs font-bold flex-shrink-0 mt-0.5">3</div>
                                <p class="text-sm text-foreground"><strong>Опануй порівняння</strong> — ступені порівняння прикметників</p>
                            </div>
                            <div class="flex items-start gap-3">
                                <div class="flex h-6 w-6 items-center justify-center rounded-full bg-purple-500/20 text-purple-600 text-xs font-bold flex-shrink-0 mt-0.5">4</div>
                                <p class="text-sm text-foreground"><strong>Поглиби знання</strong> — порядок слів, інверсія, складні структури</p>
                            </div>
                        </div>
                    </div>
                    
                    {{-- Stats Cards --}}
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="rounded-2xl border border-border/60 bg-card p-6 text-center">
                            <div class="text-4xl font-black text-primary mb-2">{{ $categories->count() }}</div>
                            <p class="text-sm text-muted-foreground">Категорій теорії</p>
                        </div>
                        <div class="rounded-2xl border border-border/60 bg-card p-6 text-center">
                            <div class="text-4xl font-black text-secondary mb-2">{{ $totalPages }}</div>
                            <p class="text-sm text-muted-foreground">Сторінок уроків</p>
                        </div>
                        <div class="rounded-2xl border border-border/60 bg-card p-6 text-center">
                            <div class="text-4xl font-black text-emerald-500 mb-2">A1-B2</div>
                            <p class="text-sm text-muted-foreground">Рівні складності</p>
                        </div>
                        <div class="rounded-2xl border border-border/60 bg-card p-6 text-center">
                            <div class="text-4xl font-black text-amber-500 mb-2" role="img" aria-label="Україна">🇺🇦</div>
                            <p class="text-sm text-muted-foreground">Українською мовою</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- Mobile Floating Menu --}}
        <div class="fixed bottom-6 left-1/2 -translate-x-1/2 z-50 lg:hidden" x-data="{ open: false }">
            <button 
                @click="open = !open"
                class="flex items-center gap-2 rounded-full bg-gradient-to-r from-indigo-600 to-purple-600 px-6 py-3.5 text-sm font-semibold text-white shadow-lg transition-transform hover:scale-105"
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
                class="absolute bottom-full left-1/2 -translate-x-1/2 mb-3 w-80 max-h-[60vh] overflow-y-auto rounded-2xl border border-border bg-card p-5 shadow-xl"
            >
                <div class="space-y-4">
                    <div class="flex items-center justify-between border-b border-border pb-3">
                        <h4 class="font-bold text-foreground">Категорії теорії</h4>
                        <span class="text-xs text-muted-foreground">{{ $categories->count() }} категорій</span>
                    </div>
                    <nav class="space-y-2">
                        @forelse($categories as $category)
                            <a 
                                href="{{ route($routePrefix . '.category', $category->slug) }}"
                                class="flex items-center justify-between rounded-xl bg-muted/30 px-4 py-3 text-sm font-medium text-foreground transition hover:bg-primary/10 hover:text-primary"
                            >
                                <span>{{ $category->title }}</span>
                                @if(isset($category->pages_count) && $category->pages_count > 0)
                                    <span class="text-xs text-muted-foreground">{{ $category->pages_count }}</span>
                                @endif
                            </a>
                        @empty
                            <p class="text-sm text-muted-foreground text-center py-4">Немає категорій</p>
                        @endforelse
                    </nav>
                </div>
            </div>
        </div>
    </div>
@endsection
