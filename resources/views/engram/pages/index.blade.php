@extends('layouts.engram')

@section('title', $sectionTitle ?? __('public.pages.title'))

@section('content')
    @php($categoryPages = $categoryPages ?? collect())

    <div class="space-y-8">
        {{-- Hero Section --}}
        <section class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-brand-600 via-brand-500 to-brand-400 p-8 text-white shadow-card">
            {{-- Decorative background elements --}}
            <div class="absolute inset-0 opacity-20">
                <div class="absolute -top-20 -right-20 w-64 h-64 bg-white/20 rounded-full blur-3xl"></div>
                <div class="absolute -bottom-20 -left-20 w-48 h-48 bg-white/15 rounded-full blur-3xl"></div>
            </div>
            
            <div class="relative">
                <span class="inline-flex items-center gap-2 rounded-full bg-white/20 backdrop-blur-sm px-4 py-2 text-sm font-semibold mb-4">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                    {{ __('public.nav.theory') }}
                </span>
                <h1 class="text-3xl md:text-4xl font-bold mb-3">{{ optional($selectedCategory)->title ?? __('public.pages.title') }}</h1>
                <p class="text-white/80 max-w-2xl text-lg">
                    @if ($selectedCategory)
                        {{ __('public.pages.materials_section') }} «{{ $selectedCategory->title }}».
                    @else
                        {{ __('public.pages.select_category_hint') }}
                    @endif
                </p>
                
                <div class="mt-6 flex flex-wrap gap-4">
                    <div class="rounded-2xl bg-white/20 backdrop-blur-sm px-5 py-3 border border-white/20">
                        <p class="text-2xl font-bold">{{ $categories->count() }}</p>
                        <p class="text-xs uppercase tracking-wider text-white/80">{{ __('public.pages.categories_count', ['default' => 'категорій']) }}</p>
                    </div>
                    @if($categoryPages->isNotEmpty())
                        <div class="rounded-2xl bg-white/20 backdrop-blur-sm px-5 py-3 border border-white/20">
                            <p class="text-2xl font-bold">{{ $categoryPages->count() }}</p>
                            <p class="text-xs uppercase tracking-wider text-white/80">{{ __('public.pages.pages_count', ['default' => 'сторінок']) }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </section>

        <div class="grid gap-8 lg:grid-cols-[260px_1fr]">
            @include('engram.pages.partials.sidebar', [
                'categories' => $categories,
                'selectedCategory' => $selectedCategory,
                'categoryPages' => $categoryPages,
                'showCategoryPagesNav' => false,
                'routePrefix' => $routePrefix ?? 'pages',
            ])

            @php($categoryDescription = $categoryDescription ?? ['hasBlocks' => false])

            <section class="space-y-6">
                @if ($selectedCategory && ($categoryDescription['hasBlocks'] ?? false))
                    @include('engram.pages.partials.grammar-card', [
                        'page' => $selectedCategory,
                        'subtitleBlock' => $categoryDescription['subtitleBlock'] ?? null,
                        'columns' => $categoryDescription['columns'] ?? [],
                        'locale' => $categoryDescription['locale'] ?? app()->getLocale(),
                    ])
                @endif

            @if ($categoryPages->isNotEmpty())
                @include('engram.pages.partials.page-grid', [
                    'pages' => $categoryPages,
                    'category' => $selectedCategory,
                    'routePrefix' => $routePrefix ?? 'pages',
                ])
            @else
                <div class="rounded-2xl border border-dashed border-[var(--border)] bg-[var(--card)] p-8 text-center text-[var(--muted)] shadow-sm">
                    <div class="flex justify-center mb-3">
                        <span class="inline-flex h-12 w-12 items-center justify-center rounded-full bg-brand-50 text-brand-600">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                        </span>
                    </div>
                    {{ __('public.pages.no_pages_in_category') }}
                </div>
            @endif

            @if(isset($autoGeneratedTests) && $autoGeneratedTests->isNotEmpty())
                <div class="rounded-2xl border border-[var(--border)] bg-[var(--card)] shadow-sm p-6" x-data="{ expanded: true }">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-brand-100 text-brand-600" role="img" aria-label="{{ __('public.common.tests_on_topic') }}">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                            </span>
                            <h2 class="text-lg font-bold">{{ __('public.common.tests_on_topic') }}</h2>
                        </div>
                        <button 
                            @click="expanded = !expanded"
                            class="text-sm text-brand-600 font-semibold hover:underline transition flex items-center gap-1"
                            :aria-expanded="expanded"
                        >
                            <span x-text="expanded ? '{{ __('public.common.collapse') }}' : '{{ __('public.common.expand') }}'"></span>
                            <svg 
                                class="w-4 h-4 transition-transform" 
                                :class="{ 'rotate-180': !expanded }"
                                fill="none" 
                                stroke="currentColor" 
                                viewBox="0 0 24 24"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                    </div>

                    <div x-show="expanded" x-collapse>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($autoGeneratedTests as $test)
                                <x-auto-generated-test-card :test="$test" />
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            @if($selectedCategory && $selectedCategory->tags->isNotEmpty())
                <div class="rounded-2xl border border-[var(--border)] bg-[var(--card)] shadow-sm p-6" x-data="{ expanded: false }">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-brand-100 text-brand-600" role="img" aria-label="{{ __('public.common.category_tags') }}">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z"/></svg>
                            </span>
                            <h2 class="text-lg font-bold">{{ __('public.common.category_tags') }}</h2>
                        </div>
                        <button 
                            @click="expanded = !expanded"
                            class="text-sm text-brand-600 font-semibold hover:underline transition flex items-center gap-1"
                            :aria-expanded="expanded"
                        >
                            <span x-text="expanded ? '{{ __('public.common.collapse') }}' : '{{ __('public.common.expand') }}'"></span>
                            <svg 
                                class="w-4 h-4 transition-transform" 
                                :class="{ 'rotate-180': !expanded }"
                                fill="none" 
                                stroke="currentColor" 
                                viewBox="0 0 24 24"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                    </div>

                    <div x-show="expanded" x-collapse>
                        <div class="flex flex-wrap gap-1.5">
                            @foreach($selectedCategory->tags as $tag)
                                <span class="inline-block bg-brand-50 text-brand-700 font-medium text-xs px-2.5 py-1 rounded-full">{{ $tag->name }}</span>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            @if(isset($relatedTests) && $relatedTests->isNotEmpty())
                <div class="rounded-2xl border border-[var(--border)] bg-[var(--card)] shadow-sm p-6" x-data="{ expanded: false }">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-brand-100 text-brand-600" role="img" aria-label="{{ __('public.common.related_tests') }}">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                            </span>
                            <h2 class="text-lg font-bold">{{ __('public.common.related_tests') }}</h2>
                        </div>
                        <button 
                            @click="expanded = !expanded"
                            class="text-sm text-brand-600 font-semibold hover:underline transition flex items-center gap-1"
                            :aria-expanded="expanded"
                        >
                            <span x-text="expanded ? '{{ __('public.common.collapse') }}' : '{{ __('public.common.expand') }}'"></span>
                            <svg 
                                class="w-4 h-4 transition-transform" 
                                :class="{ 'rotate-180': !expanded }"
                                fill="none" 
                                stroke="currentColor" 
                                viewBox="0 0 24 24"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                    </div>

                    <div x-show="expanded" x-collapse>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($relatedTests as $test)
                                <x-related-test-card :test="$test" />
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
            </section>
        </div>
    </div>
@endsection
