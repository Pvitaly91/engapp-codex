@extends('layouts.engram')

@section('title', $sectionTitle ?? __('public.theory.title'))

@section('content')
    @php
        $categoryPages = $categoryPages ?? collect();
        $routePrefix = $routePrefix ?? 'theory';
        $categoryDescription = $categoryDescription ?? ['hasBlocks' => false];
        $totalPages = $categories->sum(fn($c) => $c->pages_count ?? 0);
    @endphp

    <div class="min-h-screen">
        {{-- Hero Section with General Overview --}}
        <header class="space-y-6 mb-10">
            <div class="relative px-4 md:px-0">
                {{-- Badge --}}
                <span class="inline-flex items-center gap-2 rounded-full bg-brand-50 px-4 py-2 text-xs font-semibold text-brand-700">
                    {{ __('public.theory.section_badge') }}
                </span>

                {{-- Title --}}
                <h1 class="text-4xl font-bold leading-tight md:text-5xl">
                    {{ __('public.theory.title') }}
                </h1>

                {{-- Description --}}
                <p class="max-w-2xl text-lg text-[var(--muted)]">
                    {{ __('public.theory.description') }}
                </p>

                {{-- Quick Action Buttons --}}
                <div class="flex flex-wrap gap-3">
                    @if($categories->first())
                        <a 
                            href="{{ localized_route($routePrefix . '.category', $categories->first()->slug) }}"
                            class="inline-flex items-center gap-2 rounded-full bg-brand-600 px-6 py-3 text-sm font-semibold text-white shadow-card"
                        >
                            {{ __('public.theory.start_learning') }}
                        </a>
                    @endif
                    <a 
                        href="#categories-section"
                        class="inline-flex items-center gap-2 rounded-full border border-[var(--border)] bg-[var(--card)] px-6 py-3 text-sm font-semibold"
                    >
                        {{ __('public.theory.all_categories') }}
                    </a>
                </div>

                {{-- Stats --}}
                <div class="grid grid-cols-2 gap-4 md:grid-cols-3 max-w-2xl">
                    <div class="rounded-2xl border border-[var(--border)] bg-[var(--card)] p-4 shadow-sm">
                        <p class="text-2xl font-bold text-brand-600">{{ $categories->count() }}</p>
                        <p class="text-sm text-[var(--muted)]">{{ __('public.theory.categories_count') }}</p>
                    </div>
                    <div class="rounded-2xl border border-[var(--border)] bg-[var(--card)] p-4 shadow-sm">
                        <p class="text-2xl font-bold text-brand-600">{{ $totalPages }}</p>
                        <p class="text-sm text-[var(--muted)]">{{ __('public.theory.lessons_count') }}</p>
                    </div>
                    <div class="rounded-2xl border border-[var(--border)] bg-[var(--card)] p-4 shadow-sm col-span-2 md:col-span-1">
                        <p class="text-2xl font-bold text-brand-600">24/7</p>
                        <p class="text-sm text-[var(--muted)]">{{ __('public.home.stat_access') }}</p>
                    </div>
                </div>
            </div>
        </header>

        {{-- Categories Section --}}
        <section id="categories-section" class="scroll-mt-24">
            <div class="text-center mb-8">
                <p class="text-xs uppercase tracking-[0.25em] text-brand-600">{{ __('public.home.sections') }}</p>
                <h2 class="text-2xl font-semibold">{{ __('public.theory.topics_to_learn') }}</h2>
            </div>

            {{-- Categories Grid --}}
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                @forelse($categories as $index => $category)
                    @php
                        $hasChildren = $category->relationLoaded('children') && $category->children->isNotEmpty();
                        $hasPages = $category->relationLoaded('pages') && $category->pages->isNotEmpty();
                    @endphp
                    <a href="{{ localized_route($routePrefix . '.category', $category->slug) }}" class="group flex h-full flex-col justify-between rounded-2xl border border-[var(--border)] bg-[var(--card)] p-5 shadow-sm hover:border-brand-500">
                        <div class="space-y-3">
                            <span class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-brand-100 text-brand-600">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                </svg>
                            </span>
                            <h3 class="text-xl font-semibold">{{ $category->title }}</h3>
                            <p class="text-sm text-[var(--muted)]">
                                @if(isset($category->pages_count) && $category->pages_count > 0)
                                    {{ $category->pages_count }} {{ __('public.theory.lessons_count') }}
                                @else
                                    {{ __('public.common.no_lessons') }}
                                @endif
                            </p>
                            
                            {{-- Subcategories/Pages preview --}}
                            @if($hasChildren || $hasPages)
                                <div class="space-y-1 pt-2 border-t border-[var(--border)]">
                                    @if($hasChildren)
                                        @foreach($category->children->take(3) as $child)
                                            <div class="text-sm text-[var(--muted)] flex items-center gap-2">
                                                <svg class="h-3 w-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                                                </svg>
                                                <span class="truncate">{{ $child->title }}</span>
                                            </div>
                                        @endforeach
                                    @elseif($hasPages)
                                        @foreach($category->pages->take(3) as $page)
                                            <div class="text-sm text-[var(--muted)] flex items-center gap-2">
                                                <svg class="h-3 w-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                </svg>
                                                <span class="truncate">{{ $page->title }}</span>
                                            </div>
                                        @endforeach
                                    @endif
                                    @if(($hasChildren && $category->children->count() > 3) || ($hasPages && $category->pages->count() > 3))
                                        <div class="text-xs text-brand-600 mt-1">
                                            +{{ ($hasChildren ? $category->children->count() : $category->pages->count()) - 3 }} {{ __('public.common.more') }}
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>
                        <span class="text-sm font-semibold text-brand-600 mt-3">{{ __('public.common.go_to') }}</span>
                    </a>
                @empty
                    <div class="col-span-full rounded-2xl border border-dashed border-[var(--border)] p-12 text-center">
                        <h3 class="text-lg font-semibold mb-2">{{ __('public.theory.no_categories_title') }}</h3>
                        <p class="text-[var(--muted)]">{{ __('public.theory.no_categories_hint') }}</p>
                    </div>
                @endforelse
            </div>
        </section>

        {{-- Learning Path Section --}}
        <section class="mt-16 mb-12 rounded-3xl border border-[var(--border)] bg-[var(--card)] p-6 shadow-card">
            <div class="grid gap-6 lg:grid-cols-[1.1fr_1fr] items-center">
                <div class="space-y-4">
                    <p class="text-xs uppercase tracking-[0.25em] text-brand-600">{{ __('public.home.how_it_works') }}</p>
                    <h2 class="text-3xl font-semibold">{{ __('public.theory.how_to_learn') }}</h2>
                    <p class="text-[var(--muted)]">{{ __('public.theory.how_to_learn_desc') }}</p>
                    <div class="space-y-3">
                        <div class="flex gap-3 rounded-2xl border border-[var(--border)] p-4">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-brand-100 text-brand-700 font-semibold">1</div>
                            <div>
                                <p class="font-semibold">{{ __('public.theory.step1') }}</p>
                                <p class="text-sm text-[var(--muted)]">{{ __('public.theory.step1_desc') }}</p>
                            </div>
                        </div>
                        <div class="flex gap-3 rounded-2xl border border-[var(--border)] p-4">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-brand-100 text-brand-700 font-semibold">2</div>
                            <div>
                                <p class="font-semibold">{{ __('public.theory.step2') }}</p>
                                <p class="text-sm text-[var(--muted)]">{{ __('public.theory.step2_desc') }}</p>
                            </div>
                        </div>
                        <div class="flex gap-3 rounded-2xl border border-[var(--border)] p-4">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-brand-100 text-brand-700 font-semibold">3</div>
                            <div>
                                <p class="font-semibold">{{ __('public.theory.step3') }}</p>
                                <p class="text-sm text-[var(--muted)]">{{ __('public.theory.step3_desc') }}</p>
                            </div>
                        </div>
                        <div class="flex gap-3 rounded-2xl border border-[var(--border)] p-4">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-brand-100 text-brand-700 font-semibold">4</div>
                            <div>
                                <p class="font-semibold">{{ __('public.theory.step4') }}</p>
                                <p class="text-sm text-[var(--muted)]">{{ __('public.theory.step4_desc') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                {{-- Stats Cards --}}
                <div class="space-y-4 rounded-2xl border border-[var(--border)] bg-gradient-to-br from-brand-50 to-white p-6">
                    <h3 class="text-xl font-semibold">{{ __('public.home.search_block_title') }}</h3>
                    <p class="text-sm text-[var(--muted)]">{{ __('public.home.search_block_desc') }}</p>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="rounded-2xl border border-[var(--border)] bg-white p-4 text-center shadow-sm">
                            <div class="text-2xl font-bold text-brand-600">{{ $categories->count() }}</div>
                            <p class="text-xs text-[var(--muted)]">{{ __('public.theory.theory_categories') }}</p>
                        </div>
                        <div class="rounded-2xl border border-[var(--border)] bg-white p-4 text-center shadow-sm">
                            <div class="text-2xl font-bold text-brand-600">{{ $totalPages }}</div>
                            <p class="text-xs text-[var(--muted)]">{{ __('public.theory.lesson_pages') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- Mobile Floating Menu --}}
        <div class="fixed bottom-6 left-1/2 -translate-x-1/2 z-50 lg:hidden" x-data="{ open: false }">
            <button 
                @click="open = !open"
                class="flex items-center gap-2 rounded-full bg-brand-600 px-6 py-3.5 text-sm font-semibold text-white shadow-lg"
            >
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
                {{ __('public.theory.mobile_categories') }}
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
                class="absolute bottom-full left-1/2 -translate-x-1/2 mb-3 w-80 max-h-[60vh] overflow-y-auto rounded-2xl border border-[var(--border)] bg-[var(--card)] p-5 shadow-xl"
            >
                <div class="space-y-4">
                    <div class="flex items-center justify-between border-b border-[var(--border)] pb-3">
                        <h4 class="font-bold">{{ __('public.theory.theory_categories_mobile') }}</h4>
                        <span class="text-xs text-[var(--muted)]">{{ $categories->count() }} {{ __('public.theory.categories_count') }}</span>
                    </div>
                    <nav class="space-y-2">
                        @forelse($categories as $category)
                            @php
                                $hasChildrenMobile = $category->relationLoaded('children') && $category->children->isNotEmpty();
                            @endphp
                            <div class="space-y-1">
                                <a 
                                    href="{{ localized_route($routePrefix . '.category', $category->slug) }}"
                                    class="flex items-center justify-between rounded-xl bg-brand-50 px-4 py-3 text-sm font-medium hover:bg-brand-100"
                                >
                                    <span>{{ $category->title }}</span>
                                    @if(isset($category->pages_count) && $category->pages_count > 0)
                                        <span class="text-xs text-[var(--muted)]">{{ $category->pages_count }}</span>
                                    @endif
                                </a>
                                @if($hasChildrenMobile)
                                    <div class="ml-4 space-y-1">
                                        @foreach($category->children as $child)
                                            <a 
                                                href="{{ localized_route($routePrefix . '.category', $child->slug) }}"
                                                class="flex items-center justify-between rounded-lg px-3 py-2 text-xs text-[var(--muted)] hover:bg-brand-50"
                                            >
                                                <span class="flex items-center gap-1.5">
                                                    <svg class="h-2.5 w-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                                                    </svg>
                                                    {{ $child->title }}
                                                </span>
                                                @if(isset($child->pages_count) && $child->pages_count > 0)
                                                    <span class="text-xs">{{ $child->pages_count }}</span>
                                                @endif
                                            </a>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @empty
                            <p class="text-sm text-[var(--muted)] text-center py-4">{{ __('public.common.no_categories') }}</p>
                        @endforelse
                    </nav>
                </div>
            </div>
        </div>
    </div>
@endsection
