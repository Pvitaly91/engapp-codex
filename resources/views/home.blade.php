@extends('layouts.catalog-public')

@section('title', __('public.home.title'))

@section('content')
@php
    $statsCards = [
        ['value' => '120+', 'label' => __('public.home.stat_categories')],
        ['value' => '7k+', 'label' => __('public.home.stat_tags')],
        ['value' => '2k+', 'label' => __('public.home.stat_ai_hints')],
        ['value' => '24/7', 'label' => __('public.home.stat_access')],
    ];

    $pathCards = [
        [
            'href' => localized_route('catalog.tests-cards'),
            'eyebrow' => __('public.nav.catalog'),
            'title' => __('public.nav.catalog'),
            'description' => __('public.home.catalog_desc'),
            'badge' => '01',
            'accent' => 'bg-ocean',
            'icon' => 'CT',
        ],
        [
            'href' => localized_route('theory.index'),
            'eyebrow' => __('public.nav.theory'),
            'title' => __('public.nav.theory'),
            'description' => __('public.home.theory_desc'),
            'badge' => '02',
            'accent' => 'bg-amber',
            'icon' => 'TH',
        ],
        [
            'href' => localized_route('words.test'),
            'eyebrow' => __('public.nav.words_test'),
            'title' => __('public.nav.words_test'),
            'description' => __('public.home.words_desc'),
            'badge' => '03',
            'accent' => 'bg-slate-800 dark:bg-slate-200',
            'icon' => 'WD',
        ],
        [
            'href' => localized_route('verbs.test'),
            'eyebrow' => __('public.nav.verbs_test'),
            'title' => __('public.nav.verbs_test'),
            'description' => __('public.home.verbs_desc'),
            'badge' => '04',
            'accent' => 'bg-emerald-500',
            'icon' => 'VB',
        ],
    ];

    $steps = [
        ['index' => '1', 'title' => __('public.home.step_find'), 'description' => __('public.home.step_find_desc')],
        ['index' => '2', 'title' => __('public.home.step_start'), 'description' => __('public.home.step_start_desc')],
        ['index' => '3', 'title' => __('public.home.step_share'), 'description' => __('public.home.step_share_desc')],
    ];
@endphp

<div class="nd-page">
    <section class="nd-section-tight relative border-b" style="border-color: var(--line);">
        <div class="absolute left-[52%] top-0 hidden h-44 w-44 -translate-x-1/2 rounded-full border-[22px] border-ocean/70 lg:block"></div>
        <div class="absolute right-5 top-10 hidden h-20 w-20 rounded-full bg-slate-200/70 lg:block dark:bg-slate-700/60"></div>
        <div class="absolute -bottom-12 left-[44%] hidden h-28 w-28 rounded-full border-[18px] border-slate-200/90 lg:block dark:border-slate-700/70"></div>
        <div class="absolute bottom-0 right-0 hidden h-56 w-16 rounded-tl-[2.5rem] bg-amber lg:block"></div>

        <div class="relative grid gap-10 lg:grid-cols-[1.05fr_0.95fr] lg:items-center">
            <div class="max-w-2xl py-3">
                <span class="inline-flex items-center rounded-full border px-4 py-2 text-xs font-extrabold uppercase tracking-[0.28em] soft-accent" style="border-color: var(--line); color: var(--accent);">
                    {{ __('public.home.badge') }}
                </span>
                <h1 class="mt-6 max-w-3xl font-display text-3xl font-extrabold leading-[1.05] sm:text-4xl">
                    {{ __('public.home.hero_title') }}
                    <span class="block" style="color: var(--accent);">{{ __('public.home.hero_title_accent') }}</span>
                </h1>
                <p class="mt-5 max-w-2xl text-lg leading-8 sm:text-xl" style="color: var(--muted);">
                    {{ __('public.home.hero_description') }}
                </p>

                <div class="mt-8 flex flex-col gap-4 sm:flex-row">
                    <a href="{{ localized_route('catalog.tests-cards') }}" class="rounded-2xl bg-ocean px-6 py-4 text-center text-base font-extrabold text-white shadow-card transition hover:bg-[#245592]">
                        {{ __('public.home.to_catalog') }}
                    </a>
                    <a href="{{ localized_route('theory.index') }}" class="rounded-2xl bg-amber px-6 py-4 text-center text-base font-extrabold text-white shadow-card transition hover:bg-[#df8a24]">
                        {{ __('public.home.explore_theory') }}
                    </a>
                </div>

                <div class="mt-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    @foreach($statsCards as $card)
                        <article class="rounded-[24px] border p-4 shadow-card surface-card-strong">
                            <p class="font-display text-2xl font-extrabold leading-none" style="color: var(--accent);">{{ $card['value'] }}</p>
                            <p class="mt-3 text-sm font-semibold leading-6" style="color: var(--muted);">{{ $card['label'] }}</p>
                        </article>
                    @endforeach
                </div>
            </div>

            <div class="grid gap-6">
                <article class="rounded-[28px] border p-6 shadow-card surface-card-strong">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-xs font-extrabold uppercase tracking-[0.28em]" style="color: var(--accent);">{{ __('public.home.quick_search') }}</p>
                            <h2 class="mt-3 font-display text-2xl font-extrabold leading-tight">{{ __('public.home.quick_search_hint') }}</h2>
                        </div>
                        <span class="inline-flex h-14 w-14 items-center justify-center rounded-[20px] soft-accent text-lg font-extrabold" style="color: var(--accent);">A/Z</span>
                    </div>

                    <div class="mt-6">
                        @include('components.word-search')
                    </div>

                    <div class="mt-5 rounded-[22px] border px-4 py-4 text-sm font-semibold soft-accent" style="border-color: var(--line); color: var(--text);">
                        {{ __('public.home.dictionary_cta') }}
                    </div>
                </article>

                <div class="grid gap-4 sm:grid-cols-2">
                    @foreach(array_slice($pathCards, 0, 2) as $card)
                        <a href="{{ $card['href'] }}" class="rounded-[24px] border p-5 shadow-card transition hover:-translate-y-1 surface-card">
                            <div class="flex items-center justify-between gap-4">
                                <span class="inline-flex h-12 w-12 items-center justify-center rounded-[18px] {{ $card['accent'] }} text-sm font-extrabold text-white dark:text-slate-950">{{ $card['icon'] }}</span>
                                <span class="text-xs font-extrabold uppercase tracking-[0.24em]" style="color: var(--muted);">{{ $card['badge'] }}</span>
                            </div>
                            <p class="mt-5 text-sm font-bold uppercase tracking-[0.24em]" style="color: var(--accent);">{{ $card['eyebrow'] }}</p>
                            <h3 class="mt-3 font-display text-xl font-extrabold leading-tight">{{ $card['title'] }}</h3>
                            <p class="mt-2 text-sm leading-6" style="color: var(--muted);">{{ $card['description'] }}</p>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    @if(config('app.is_beta'))
        <section class="nd-section-tight border-b" style="border-color: var(--line);">
            <div class="rounded-[26px] border border-amber-300/70 bg-amber-50/90 px-5 py-5 shadow-card dark:border-amber-500/30 dark:bg-amber-500/10">
                <div class="flex items-start gap-4">
                    <span class="inline-flex h-12 w-12 items-center justify-center rounded-[18px] bg-amber text-lg font-extrabold text-white">!</span>
                    <div>
                        <p class="font-display text-lg font-extrabold text-slate-900 dark:text-amber-100">{{ __('public.beta.banner_title') }}</p>
                        <p class="mt-2 text-sm font-medium leading-6 text-slate-700 dark:text-amber-50/80">{{ __('public.beta.banner_text') }}</p>
                    </div>
                </div>
            </div>
        </section>
    @endif

    <section class="nd-section border-b" style="border-color: var(--line);">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-extrabold uppercase tracking-[0.28em]" style="color: var(--accent);">{{ __('public.home.sections') }}</p>
                <h2 class="mt-2 font-display text-2xl font-extrabold leading-none">{{ __('public.home.pick_path') }}</h2>
            </div>
            <p class="max-w-2xl text-sm leading-6 sm:text-right" style="color: var(--muted);">
                {{ __('public.home.steps_intro') }}
            </p>
        </div>

        <div class="mt-8 grid gap-5 xl:grid-cols-4">
            @foreach($pathCards as $card)
                <a href="{{ $card['href'] }}" class="group overflow-hidden rounded-[24px] border shadow-card transition hover:-translate-y-1 surface-card-strong">
                    <div class="relative border-b p-5" style="border-color: var(--line);">
                        <span class="absolute right-5 top-5 text-xs font-extrabold uppercase tracking-[0.24em]" style="color: var(--muted);">{{ $card['badge'] }}</span>
                        <span class="inline-flex h-16 w-16 items-center justify-center rounded-[22px] {{ $card['accent'] }} font-display text-lg font-extrabold text-white dark:text-slate-950">{{ $card['icon'] }}</span>
                        <p class="mt-6 text-sm font-bold uppercase tracking-[0.24em]" style="color: var(--accent);">{{ $card['eyebrow'] }}</p>
                        <h3 class="mt-2 font-display text-xl font-extrabold leading-tight">{{ $card['title'] }}</h3>
                        <p class="mt-3 text-sm leading-6" style="color: var(--muted);">{{ $card['description'] }}</p>
                    </div>
                    <div class="flex items-center justify-between px-5 py-4 text-sm font-extrabold uppercase tracking-[0.2em]" style="color: var(--text);">
                        <span>{{ __('public.common.go_to') }}</span>
                        <span class="transition group-hover:translate-x-1" style="color: var(--accent);">→</span>
                    </div>
                </a>
            @endforeach
        </div>
    </section>

    <section class="nd-section">
        <div class="grid gap-8 xl:grid-cols-[1.08fr_0.92fr]">
            <article class="rounded-[28px] border p-6 shadow-card surface-card-strong">
                <p class="text-xs font-extrabold uppercase tracking-[0.28em]" style="color: var(--accent);">{{ __('public.home.how_it_works') }}</p>
                <h2 class="mt-3 font-display text-2xl font-extrabold leading-tight">{{ __('public.home.steps_title') }}</h2>
                <p class="mt-3 max-w-2xl text-sm leading-7 sm:text-base" style="color: var(--muted);">{{ __('public.home.steps_intro') }}</p>

                <div class="mt-8 grid gap-4">
                    @foreach($steps as $step)
                        <div class="flex gap-4 rounded-[24px] border p-4 surface-card" style="border-color: var(--line);">
                            <span class="inline-flex h-14 w-14 shrink-0 items-center justify-center rounded-[18px] bg-ocean text-lg font-extrabold text-white">
                                {{ $step['index'] }}
                            </span>
                            <div>
                                <h3 class="font-display text-lg font-extrabold leading-tight">{{ $step['title'] }}</h3>
                                <p class="mt-2 text-sm leading-6" style="color: var(--muted);">{{ $step['description'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </article>

            <article class="rounded-[28px] border p-6 shadow-card surface-card">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-extrabold uppercase tracking-[0.28em]" style="color: var(--accent);">{{ __('public.home.search_block_title') }}</p>
                        <h2 class="mt-3 font-display text-2xl font-extrabold leading-tight">{{ __('public.search.placeholder') }}</h2>
                    </div>
                    <span class="inline-flex h-14 w-14 items-center justify-center rounded-[20px] bg-amber text-lg font-extrabold text-white">GO</span>
                </div>

                <p class="mt-4 text-sm leading-7 sm:text-base" style="color: var(--muted);">{{ __('public.home.search_block_desc') }}</p>

                <form action="{{ localized_route('site.search') }}" method="GET" class="mt-6 rounded-[24px] border p-3 shadow-sm surface-card-strong" style="border-color: var(--line);">
                    <div class="flex flex-col gap-3 sm:flex-row">
                        <input type="search" name="q" placeholder="{{ __('public.search.placeholder') }}" class="w-full rounded-[18px] border px-4 py-3 text-sm font-medium outline-none transition focus:border-ocean focus:ring-2 focus:ring-blue-100 dark:bg-slate-900/50" style="border-color: var(--line);">
                        <button type="submit" class="rounded-[18px] bg-ocean px-5 py-3 text-sm font-extrabold uppercase tracking-[0.18em] text-white transition hover:bg-[#245592]">
                            {{ __('public.search.button') }}
                        </button>
                    </div>
                </form>

                <p class="mt-4 text-xs font-semibold uppercase tracking-[0.22em]" style="color: var(--muted);">{{ __('public.home.search_block_hint') }}</p>

                <div class="mt-8 grid gap-4 sm:grid-cols-2">
                    @foreach(array_slice($pathCards, 2, 2) as $card)
                        <a href="{{ $card['href'] }}" class="rounded-[22px] border p-4 transition hover:-translate-y-1 surface-card-strong" style="border-color: var(--line);">
                            <div class="flex items-center justify-between gap-3">
                                <span class="inline-flex h-11 w-11 items-center justify-center rounded-[16px] {{ $card['accent'] }} text-sm font-extrabold text-white dark:text-slate-950">{{ $card['icon'] }}</span>
                                <span class="text-xs font-extrabold uppercase tracking-[0.24em]" style="color: var(--muted);">{{ $card['badge'] }}</span>
                            </div>
                            <h3 class="mt-4 font-display text-lg font-extrabold leading-tight">{{ $card['title'] }}</h3>
                            <p class="mt-2 text-sm leading-6" style="color: var(--muted);">{{ $card['description'] }}</p>
                        </a>
                    @endforeach
                </div>
            </article>
        </div>
    </section>
</div>
@endsection
