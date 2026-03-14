@extends('layouts.catalog-public')

@section('title', ($selectedCategory->title ?? 'Категорія') . ' - ' . ($sectionTitle ?? 'Теорія'))

@section('content')
@php
    $categoryPages = $categoryPages ?? collect();
    $routePrefix = $routePrefix ?? 'theory';
    $categoryDescription = $categoryDescription ?? ['hasBlocks' => false];
@endphp

<div class="nd-page">
    <nav class="mb-8 flex flex-wrap items-center gap-2 text-xs font-semibold uppercase tracking-[0.18em]" style="color: var(--muted);" aria-label="Breadcrumb">
        <a href="{{ localized_route('home') }}" class="transition hover:text-ocean">Home</a>
        <span>/</span>
        <a href="{{ localized_route($routePrefix . '.index') }}" class="transition hover:text-ocean">{{ $sectionTitle ?? 'Теорія' }}</a>
        <span>/</span>
        <span style="color: var(--text);">{{ $selectedCategory->title }}</span>
    </nav>

    <section class="relative overflow-hidden rounded-[30px] border p-7 shadow-card surface-card-strong" style="border-color: var(--line);">
        <div class="absolute -right-8 top-0 hidden h-32 w-32 rounded-full border-[18px] border-ocean/30 lg:block"></div>
        <div class="absolute bottom-0 right-0 hidden h-40 w-12 rounded-tl-[2rem] bg-amber lg:block"></div>
        <div class="relative flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
            <div class="max-w-3xl">
                <p class="text-[11px] font-extrabold uppercase tracking-[0.22em]" style="color: var(--accent);">Category</p>
                <h1 class="mt-3 font-display text-3xl font-extrabold leading-[1.04] sm:text-4xl">{{ $selectedCategory->title }}</h1>
                <p class="mt-4 max-w-2xl text-sm leading-7 sm:text-base" style="color: var(--muted);">
                    Матеріали з категорії «{{ $selectedCategory->title }}». Обери сторінку або переходь по навігації всередині розділу.
                </p>
            </div>
            <div class="grid gap-3 sm:grid-cols-2">
                <div class="rounded-[22px] border px-5 py-4 surface-card" style="border-color: var(--line);">
                    <p class="text-[11px] font-extrabold uppercase tracking-[0.22em]" style="color: var(--accent);">{{ __('public.theory.lessons_count') }}</p>
                    <p class="mt-2 font-display text-2xl font-extrabold leading-none">{{ $categoryPages->count() }}</p>
                </div>
                <a href="{{ localized_route($routePrefix . '.index') }}" class="rounded-[22px] bg-ocean px-5 py-4 text-sm font-extrabold uppercase tracking-[0.18em] text-white shadow-card transition hover:bg-[#245592]">
                    {{ __('public.theory.all_categories') }}
                </a>
            </div>
        </div>
    </section>

    @include('theory.partials.mobile-navigation', [
        'categories' => $categories,
        'selectedCategory' => $selectedCategory,
        'categoryPages' => $categoryPages,
        'currentPage' => null,
        'routePrefix' => $routePrefix,
    ])

    <div class="mt-8 grid gap-8 lg:grid-cols-[320px_minmax(0,1fr)]">
        <aside class="hidden lg:block">
            <div class="sticky top-24 space-y-6">
                <section class="rounded-[28px] border p-5 shadow-card surface-card-strong" style="border-color: var(--line);">
                    <div class="flex items-end justify-between gap-3">
                        <div>
                            <p class="text-[11px] font-extrabold uppercase tracking-[0.22em]" style="color: var(--accent);">Map</p>
                            <h2 class="mt-2 font-display text-xl font-extrabold leading-none">{{ __('public.common.categories') }}</h2>
                        </div>
                        <span class="text-xs font-bold uppercase tracking-[0.18em]" style="color: var(--muted);">{{ $categories->count() }}</span>
                    </div>
                    <div class="mt-5 space-y-3">
                        @include('theory.partials.tree-nav', [
                            'categories' => $categories,
                            'selectedCategory' => $selectedCategory,
                            'currentPage' => null,
                            'routePrefix' => $routePrefix,
                        ])
                    </div>
                </section>

                @if($selectedCategory->tags->isNotEmpty())
                    <section class="rounded-[28px] border p-5 shadow-card surface-card" style="border-color: var(--line);">
                        <p class="text-[11px] font-extrabold uppercase tracking-[0.22em]" style="color: var(--accent);">{{ __('public.common.category_tags') }}</p>
                        <div class="mt-4 flex flex-wrap gap-2">
                            @foreach($selectedCategory->tags as $tag)
                                <span class="rounded-full px-3 py-1.5 text-xs font-bold" style="background: var(--accent-soft); color: var(--text);">{{ $tag->name }}</span>
                            @endforeach
                        </div>
                    </section>
                @endif
            </div>
        </aside>

        <div class="min-w-0 space-y-8">
            @if($categoryDescription['hasBlocks'] ?? false)
                @include('theory.partials.category-description', [
                    'page' => $selectedCategory,
                    'categoryDescription' => $categoryDescription,
                ])
            @endif

            <section class="rounded-[30px] border p-6 shadow-card surface-card-strong" style="border-color: var(--line);">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="text-[11px] font-extrabold uppercase tracking-[0.22em]" style="color: var(--accent);">{{ __('public.common.section_pages') }}</p>
                        <h2 class="mt-2 font-display text-2xl font-extrabold leading-none">{{ $selectedCategory->title }}</h2>
                    </div>
                    <p class="text-sm leading-6" style="color: var(--muted);">{{ $categoryPages->count() }} {{ __('public.theory.lessons_count') }}</p>
                </div>

                @if($categoryPages->isNotEmpty())
                    <div class="mt-8 grid gap-5 md:grid-cols-2">
                        @foreach($categoryPages as $page)
                            <a href="{{ localized_route($routePrefix . '.show', [$selectedCategory->slug, $page->slug]) }}" class="rounded-[24px] border p-5 shadow-card transition hover:-translate-y-1 surface-card" style="border-color: var(--line);">
                                <div class="flex items-center justify-between gap-4">
                                    <span class="inline-flex h-12 w-12 items-center justify-center rounded-[18px] bg-ocean text-sm font-extrabold text-white">
                                        {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}
                                    </span>
                                    <span class="text-[11px] font-extrabold uppercase tracking-[0.22em]" style="color: var(--muted);">Page</span>
                                </div>
                                <h3 class="mt-5 font-display text-xl font-extrabold leading-tight">{{ $page->title }}</h3>
                                @if(!empty($page->text))
                                    <p class="mt-3 text-sm leading-6" style="color: var(--muted);">{{ $page->text }}</p>
                                @endif
                                <div class="mt-5 inline-flex items-center gap-2 text-sm font-extrabold uppercase tracking-[0.18em]" style="color: var(--accent);">
                                    {{ __('public.common.go_to') }}
                                    <span>+</span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @else
                    <div class="mt-8 rounded-[24px] border border-dashed p-8 text-center surface-card" style="border-color: var(--line);">
                        <p class="text-sm leading-6" style="color: var(--muted);">{{ __('public.pages.no_pages_in_category') }}</p>
                    </div>
                @endif
            </section>

            @if(isset($autoGeneratedTests) && $autoGeneratedTests->isNotEmpty())
                <section class="rounded-[30px] border p-6 shadow-card surface-card-strong" style="border-color: var(--line);">
                    <p class="text-[11px] font-extrabold uppercase tracking-[0.22em]" style="color: var(--accent);">{{ __('public.common.tests_on_topic') }}</p>
                    <h2 class="mt-2 font-display text-2xl font-extrabold">{{ __('public.common.tests_on_topic') }}</h2>
                    <div class="mt-6 grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                        @foreach($autoGeneratedTests as $test)
                            <x-auto-generated-test-card :test="$test" />
                        @endforeach
                    </div>
                </section>
            @endif

            @if(isset($relatedTests) && $relatedTests->isNotEmpty())
                <section class="rounded-[30px] border p-6 shadow-card surface-card" style="border-color: var(--line);">
                    <p class="text-[11px] font-extrabold uppercase tracking-[0.22em]" style="color: var(--accent);">{{ __('public.common.related_tests') }}</p>
                    <h2 class="mt-2 font-display text-2xl font-extrabold">{{ __('public.common.related_tests') }}</h2>
                    <div class="mt-6 grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                        @foreach($relatedTests as $test)
                            <x-related-test-card :test="$test" />
                        @endforeach
                    </div>
                </section>
            @endif
        </div>
    </div>
</div>
@endsection
