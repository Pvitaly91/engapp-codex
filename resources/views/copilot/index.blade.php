@extends('layouts.copilot')

@section('title', __('public.home.title'))

@section('breadcrumb')
    <span class="text-[var(--cp-muted)]">{{ __('public.home.title') }}</span>
@endsection

@section('content')
<div class="space-y-12">

    {{-- ── Hero ──────────────────────────────────────────────────────────────── --}}
    <section class="space-y-6">
        <div class="inline-flex items-center gap-2 rounded-full bg-pilot-100 dark:bg-pilot-900/60 border border-pilot-200 dark:border-pilot-700/60 px-4 py-1.5 text-xs font-semibold text-pilot-700 dark:text-pilot-300">
            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            {{ __('public.home.badge') }}
        </div>
        <h1 class="text-4xl md:text-5xl font-extrabold leading-tight tracking-tight text-[var(--cp-fg)]">
            {{ __('public.home.hero_title') }}
            <span class="bg-gradient-to-r from-pilot-500 to-teal-400 bg-clip-text text-transparent">{{ __('public.home.hero_title_accent') }}</span>
        </h1>
        <p class="max-w-2xl text-lg leading-relaxed text-[var(--cp-muted)]">{{ __('public.home.hero_description') }}</p>
        <div class="flex flex-wrap gap-3">
            <a href="{{ localized_route('catalog.tests-cards') }}"
               class="inline-flex items-center gap-2 rounded-xl bg-pilot-600 hover:bg-pilot-700 text-white px-6 py-3 text-sm font-semibold shadow-glow transition">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h10"/></svg>
                {{ __('public.home.to_catalog') }}
            </a>
            <a href="{{ localized_route('theory.index') }}"
               class="inline-flex items-center gap-2 rounded-xl border border-[var(--cp-border)] bg-[var(--cp-surface)] hover:border-pilot-500 px-6 py-3 text-sm font-semibold transition">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                {{ __('public.home.explore_theory') }}
            </a>
        </div>
    </section>

    {{-- ── Stats strip ───────────────────────────────────────────────────────── --}}
    <section>
        @php
            $stats = [
                ['icon' => '📚', 'value' => '120+', 'label' => __('public.home.stat_categories')],
                ['icon' => '🏷️', 'value' => '7k+',  'label' => __('public.home.stat_tags')],
                ['icon' => '🤖', 'value' => '2k+',  'label' => __('public.home.stat_ai_hints')],
                ['icon' => '⏱️', 'value' => '24/7', 'label' => __('public.home.stat_access')],
            ];
        @endphp
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @foreach($stats as $s)
                <div class="cp-card flex flex-col items-center gap-1 py-5 px-4 text-center">
                    <span class="text-2xl leading-none">{{ $s['icon'] }}</span>
                    <p class="text-2xl font-extrabold text-pilot-600 dark:text-pilot-400">{{ $s['value'] }}</p>
                    <p class="text-xs text-[var(--cp-muted)]">{{ $s['label'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- ── Quick search card ─────────────────────────────────────────────────── --}}
    <section class="cp-card p-6 md:p-8 space-y-4">
        <div class="flex items-center gap-3">
            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-pilot-100 dark:bg-pilot-900/60 text-pilot-600 dark:text-pilot-300">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
            </span>
            <div>
                <p class="font-semibold text-[var(--cp-fg)]">{{ __('public.home.quick_search') }}</p>
                <p class="text-sm text-[var(--cp-muted)]">{{ __('public.home.quick_search_hint') }}</p>
            </div>
        </div>
        @include('components.word-search')
        <div class="rounded-xl bg-pilot-50 dark:bg-pilot-900/30 border border-pilot-200 dark:border-pilot-800/60 p-4 text-sm text-pilot-800 dark:text-pilot-200">
            {{ __('public.home.dictionary_cta') }}
        </div>
    </section>

    {{-- ── Section cards ─────────────────────────────────────────────────────── --}}
    <section class="space-y-5">
        <div>
            <p class="text-xs uppercase font-semibold tracking-widest text-pilot-600 dark:text-pilot-400 mb-1">{{ __('public.home.sections') }}</p>
            <h2 class="text-2xl font-bold text-[var(--cp-fg)]">{{ __('public.home.pick_path') }}</h2>
        </div>
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @php
                $sections = [
                    ['href' => localized_route('catalog.tests-cards'), 'icon' => '📚', 'title' => __('public.nav.catalog'),     'desc' => __('public.home.catalog_desc')],
                    ['href' => localized_route('theory.index'),        'icon' => '🧠', 'title' => __('public.nav.theory'),      'desc' => __('public.home.theory_desc')],
                    ['href' => localized_route('words.test'),          'icon' => '📝', 'title' => __('public.nav.words_test'),  'desc' => __('public.home.words_desc')],
                    ['href' => localized_route('verbs.test'),          'icon' => '⚡', 'title' => __('public.nav.verbs_test'), 'desc' => __('public.home.verbs_desc')],
                ];
            @endphp
            @foreach($sections as $sec)
                <a href="{{ $sec['href'] }}"
                   class="cp-card group flex flex-col justify-between p-5 gap-4 hover:border-pilot-400 hover:shadow-glow transition-all">
                    <div class="space-y-2">
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-pilot-100 dark:bg-pilot-900/60 text-xl">{{ $sec['icon'] }}</span>
                        <h3 class="font-semibold text-[var(--cp-fg)]">{{ $sec['title'] }}</h3>
                        <p class="text-sm text-[var(--cp-muted)]">{{ $sec['desc'] }}</p>
                    </div>
                    <span class="text-sm font-semibold text-pilot-600 dark:text-pilot-400 group-hover:underline">{{ __('public.common.go_to') }} →</span>
                </a>
            @endforeach
        </div>
    </section>

    {{-- ── How it works ──────────────────────────────────────────────────────── --}}
    <section class="cp-card p-6 md:p-8 grid gap-8 lg:grid-cols-2 items-start">
        <div class="space-y-5">
            <p class="text-xs uppercase font-semibold tracking-widest text-pilot-600 dark:text-pilot-400">{{ __('public.home.how_it_works') }}</p>
            <h2 class="text-2xl font-bold text-[var(--cp-fg)]">{{ __('public.home.steps_title') }}</h2>
            <p class="text-[var(--cp-muted)]">{{ __('public.home.steps_intro') }}</p>
            <div class="space-y-3">
                @foreach([
                    [__('public.home.step_find'),  __('public.home.step_find_desc')],
                    [__('public.home.step_start'), __('public.home.step_start_desc')],
                    [__('public.home.step_share'), __('public.home.step_share_desc')],
                ] as $i => [$title, $desc])
                <div class="flex gap-4 rounded-xl border border-[var(--cp-border)] p-4">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-pilot-100 dark:bg-pilot-900/60 text-pilot-700 dark:text-pilot-300 font-bold">{{ $i + 1 }}</div>
                    <div>
                        <p class="font-semibold text-[var(--cp-fg)]">{{ $title }}</p>
                        <p class="text-sm text-[var(--cp-muted)]">{{ $desc }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <div class="rounded-2xl border border-[var(--cp-border)] bg-gradient-to-br from-pilot-50 dark:from-pilot-900/30 to-transparent p-6 space-y-4">
            <h3 class="text-lg font-semibold text-[var(--cp-fg)]">{{ __('public.home.search_block_title') }}</h3>
            <p class="text-sm text-[var(--cp-muted)]">{{ __('public.home.search_block_desc') }}</p>
            <form action="{{ localized_route('site.search') }}" method="GET"
                  class="flex items-center gap-2 rounded-xl border border-[var(--cp-border)] bg-[var(--cp-surface)] px-4 py-2.5 shadow-sm focus-within:border-pilot-500 focus-within:ring-2 focus-within:ring-pilot-200 dark:focus-within:ring-pilot-800 transition">
                <svg class="h-4 w-4 text-[var(--cp-muted)] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                <input type="search" name="q" placeholder="{{ __('public.search.placeholder') }}" class="flex-1 bg-transparent text-sm focus:outline-none">
                <button type="submit" class="rounded-lg bg-pilot-600 hover:bg-pilot-700 px-4 py-1.5 text-xs font-semibold text-white transition">{{ __('public.search.button') }}</button>
            </form>
            <p class="text-xs text-[var(--cp-muted)]">{{ __('public.home.search_block_hint') }}</p>
        </div>
    </section>

    @if(config('app.is_beta'))
    <section class="rounded-2xl border border-amber-200 dark:border-amber-700/50 bg-amber-50/80 dark:bg-amber-900/20 p-5">
        <div class="flex items-start gap-4">
            <svg class="h-5 w-5 text-amber-600 dark:text-amber-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <div>
                <h3 class="text-sm font-semibold text-amber-800 dark:text-amber-200">{{ __('public.beta.banner_title') }}</h3>
                <p class="mt-1 text-sm text-amber-700 dark:text-amber-300">{{ __('public.beta.banner_text') }}</p>
            </div>
        </div>
    </section>
    @endif

</div>
@endsection
