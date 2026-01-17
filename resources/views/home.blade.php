@extends('layouts.engram')

@section('title', __('public.home.title'))

@section('content')
<div class="space-y-16">
    <section class="grid gap-10 lg:grid-cols-[1.2fr_1fr] items-center">
        <div class="space-y-6">
            <span class="inline-flex items-center gap-2 rounded-full bg-brand-50 px-4 py-2 text-xs font-semibold text-brand-700">{{ __('public.home.badge') }}</span>
            <h1 class="text-4xl font-bold leading-tight md:text-5xl">
                {{ __('public.home.hero_title') }}
                <span class="text-brand-600">{{ __('public.home.hero_title_accent') }}</span>
            </h1>
            <p class="max-w-2xl text-lg text-[var(--muted)]">{{ __('public.home.hero_description') }}</p>
            <div class="flex flex-wrap gap-3">
                <a href="{{ localized_route('catalog.tests-cards') }}" class="inline-flex items-center gap-2 rounded-full bg-brand-600 px-6 py-3 text-sm font-semibold text-white shadow-card">
                    {{ __('public.home.to_catalog') }}
                </a>
                <a href="{{ localized_route('theory.index') }}" class="inline-flex items-center gap-2 rounded-full border border-[var(--border)] bg-[var(--card)] px-6 py-3 text-sm font-semibold">
                    {{ __('public.home.explore_theory') }}
                </a>
            </div>
            <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
                @php
                    $stats = [
                        ['value' => '120+', 'label' => __('public.home.stat_categories')],
                        ['value' => '7k+', 'label' => __('public.home.stat_tags')],
                        ['value' => '2k+', 'label' => __('public.home.stat_ai_hints')],
                        ['value' => '24/7', 'label' => __('public.home.stat_access')],
                    ];
                @endphp
                @foreach($stats as $stat)
                    <div class="rounded-2xl border border-[var(--border)] bg-[var(--card)] p-4 shadow-sm">
                        <p class="text-2xl font-bold text-brand-600">{{ $stat['value'] }}</p>
                        <p class="text-sm text-[var(--muted)]">{{ $stat['label'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
        <div class="rounded-3xl border border-[var(--border)] bg-[var(--card)] p-6 shadow-card space-y-4">
            <p class="text-sm font-semibold text-brand-600">{{ __('public.home.quick_search') }}</p>
            <p class="text-[var(--muted)]">{{ __('public.home.quick_search_hint') }}</p>
            @include('components.word-search')
            <div class="rounded-2xl bg-brand-50/80 p-4 text-sm text-brand-900">
                {{ __('public.home.dictionary_cta') }}
            </div>
        </div>
    </section>

    @if(config('app.is_beta'))
    <section class="rounded-2xl border border-amber-200 dark:border-amber-700/50 bg-amber-50/80 dark:bg-amber-900/20 p-6">
        <div class="flex items-start gap-4">
            <div class="flex-shrink-0">
                <svg class="h-6 w-6 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <h3 class="text-sm font-semibold text-amber-800 dark:text-amber-200">{{ __('public.beta.banner_title') }}</h3>
                <p class="mt-1 text-sm text-amber-700 dark:text-amber-300">{{ __('public.beta.banner_text') }}</p>
            </div>
        </div>
    </section>
    @endif

    <section class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs uppercase tracking-[0.25em] text-brand-600">{{ __('public.home.sections') }}</p>
                <h2 class="text-2xl font-semibold">{{ __('public.home.pick_path') }}</h2>
            </div>
        </div>
        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
            <a href="{{ localized_route('catalog.tests-cards') }}" class="group flex h-full flex-col justify-between rounded-2xl border border-[var(--border)] bg-[var(--card)] p-5 shadow-sm hover:border-brand-500">
                <div class="space-y-3">
                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-brand-100 text-brand-600">📚</span>
                    <h3 class="text-xl font-semibold">{{ __('public.nav.catalog') }}</h3>
                    <p class="text-sm text-[var(--muted)]">{{ __('public.home.catalog_desc') }}</p>
                </div>
                <span class="text-sm font-semibold text-brand-600">{{ __('public.common.go_to') }}</span>
            </a>
            <a href="{{ localized_route('theory.index') }}" class="group flex h-full flex-col justify-between rounded-2xl border border-[var(--border)] bg-[var(--card)] p-5 shadow-sm hover:border-brand-500">
                <div class="space-y-3">
                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-brand-100 text-brand-600">🧠</span>
                    <h3 class="text-xl font-semibold">{{ __('public.nav.theory') }}</h3>
                    <p class="text-sm text-[var(--muted)]">{{ __('public.home.theory_desc') }}</p>
                </div>
                <span class="text-sm font-semibold text-brand-600">{{ __('public.common.go_to') }}</span>
            </a>
            <a href="{{ localized_route('words.test') }}" class="group flex h-full flex-col justify-between rounded-2xl border border-[var(--border)] bg-[var(--card)] p-5 shadow-sm hover:border-brand-500">
                <div class="space-y-3">
                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-brand-100 text-brand-600">📝</span>
                    <h3 class="text-xl font-semibold">{{ __('public.nav.words_test') }}</h3>
                    <p class="text-sm text-[var(--muted)]">{{ __('public.home.words_desc') }}</p>
                </div>
                <span class="text-sm font-semibold text-brand-600">{{ __('public.common.go_to') }}</span>
            </a>
            <a href="{{ localized_route('verbs.test') }}" class="group flex h-full flex-col justify-between rounded-2xl border border-[var(--border)] bg-[var(--card)] p-5 shadow-sm hover:border-brand-500">
                <div class="space-y-3">
                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-brand-100 text-brand-600">⚡</span>
                    <h3 class="text-xl font-semibold">{{ __('public.nav.verbs_test') }}</h3>
                    <p class="text-sm text-[var(--muted)]">{{ __('public.home.verbs_desc') }}</p>
                </div>
                <span class="text-sm font-semibold text-brand-600">{{ __('public.common.go_to') }}</span>
            </a>
        </div>
    </section>

    <section class="grid gap-6 lg:grid-cols-[1.1fr_1fr] items-center rounded-3xl border border-[var(--border)] bg-[var(--card)] p-6 shadow-card">
        <div class="space-y-4">
            <p class="text-xs uppercase tracking-[0.25em] text-brand-600">{{ __('public.home.how_it_works') }}</p>
            <h2 class="text-3xl font-semibold">{{ __('public.home.steps_title') }}</h2>
            <p class="text-[var(--muted)]">{{ __('public.home.steps_intro') }}</p>
            <div class="space-y-3">
                <div class="flex gap-3 rounded-2xl border border-[var(--border)] p-4">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-brand-100 text-brand-700 font-semibold">1</div>
                    <div>
                        <p class="font-semibold">{{ __('public.home.step_find') }}</p>
                        <p class="text-sm text-[var(--muted)]">{{ __('public.home.step_find_desc') }}</p>
                    </div>
                </div>
                <div class="flex gap-3 rounded-2xl border border-[var(--border)] p-4">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-brand-100 text-brand-700 font-semibold">2</div>
                    <div>
                        <p class="font-semibold">{{ __('public.home.step_start') }}</p>
                        <p class="text-sm text-[var(--muted)]">{{ __('public.home.step_start_desc') }}</p>
                    </div>
                </div>
                <div class="flex gap-3 rounded-2xl border border-[var(--border)] p-4">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-brand-100 text-brand-700 font-semibold">3</div>
                    <div>
                        <p class="font-semibold">{{ __('public.home.step_share') }}</p>
                        <p class="text-sm text-[var(--muted)]">{{ __('public.home.step_share_desc') }}</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="space-y-4 rounded-2xl border border-[var(--border)] bg-gradient-to-br from-brand-50 to-white p-6 text-sm text-[var(--muted)]">
            <h3 class="text-xl font-semibold text-[var(--fg)]">{{ __('public.home.search_block_title') }}</h3>
            <p>{{ __('public.home.search_block_desc') }}</p>
            <form action="{{ localized_route('site.search') }}" method="GET" class="flex items-center gap-3 rounded-full border border-[var(--border)] bg-white px-4 py-2 shadow-sm">
                <input type="search" name="q" placeholder="{{ __('public.search.placeholder') }}" class="w-full bg-transparent text-sm focus:outline-none">
                <button type="submit" class="rounded-full bg-brand-600 px-4 py-2 text-xs font-semibold text-white">{{ __('public.search.button') }}</button>
            </form>
            <p class="text-xs text-[var(--muted)]">{{ __('public.home.search_block_hint') }}</p>
        </div>
    </section>
</div>
@endsection
