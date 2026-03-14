@extends('layouts.new-design')

@section('title', __('public.home.title'))

@section('content')
{{-- ─────────────────────── HERO ─────────────────────── --}}
<section class="relative overflow-hidden border-b border-line/80 bg-hero -mx-5 -mt-8 sm:-mx-8 lg:-mx-10 px-5 py-10 sm:px-8 lg:px-10 mb-10">
    {{-- Decorative shapes from catalog.html --}}
    <div class="pointer-events-none absolute left-[48%] top-0 h-44 w-44 -translate-x-1/2 rounded-full border-[22px] border-ocean/30"></div>
    <div class="pointer-events-none absolute right-5 top-10 h-16 w-16 rounded-full bg-slate-200/60"></div>
    <div class="pointer-events-none absolute -bottom-8 left-1/2 h-28 w-28 rounded-full border-[18px] border-slate-200/70"></div>
    <div class="pointer-events-none absolute right-0 top-16 h-48 w-14 rounded-l-[2rem] bg-amber/60"></div>

    <div class="relative grid items-center gap-8 lg:grid-cols-[1fr_1fr]">
        {{-- Left: copy --}}
        <div class="max-w-xl space-y-6 py-4">
            <span class="inline-flex items-center gap-2 rounded-full bg-ocean/10 px-4 py-2 text-xs font-semibold text-ocean">
                {{ __('public.home.badge') }}
            </span>
            <h1 class="font-display text-4xl font-extrabold leading-[1.08] text-night sm:text-5xl">
                {{ __('public.home.hero_title') }}
                <span class="text-ocean"> {{ __('public.home.hero_title_accent') }}</span>
            </h1>
            <p class="max-w-lg text-xl leading-8 text-steel">{{ __('public.home.hero_description') }}</p>
            <div class="flex flex-col gap-4 sm:flex-row">
                <a href="{{ localized_route('catalog.tests-cards') }}"
                   class="rounded-xl bg-ocean px-6 py-4 text-center text-lg font-semibold text-white shadow-card transition hover:bg-[#245592]">
                    {{ __('public.home.to_catalog') }}
                </a>
                <a href="{{ localized_route('theory.index') }}"
                   class="rounded-xl bg-amber px-6 py-4 text-center text-lg font-semibold text-white shadow-card transition hover:bg-[#e38c24]">
                    {{ __('public.home.explore_theory') }}
                </a>
            </div>

            {{-- Stats --}}
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                @php
                    $stats = [
                        ['value' => '120+', 'label' => __('public.home.stat_categories')],
                        ['value' => '7k+',  'label' => __('public.home.stat_tags')],
                        ['value' => '2k+',  'label' => __('public.home.stat_ai_hints')],
                        ['value' => '24/7', 'label' => __('public.home.stat_access')],
                    ];
                @endphp
                @foreach($stats as $stat)
                    <div class="rounded-2xl border border-line bg-shell p-4 shadow-sm">
                        <p class="font-display text-2xl font-extrabold text-ocean">{{ $stat['value'] }}</p>
                        <p class="text-sm text-steel">{{ $stat['label'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Right: quick search card --}}
        <div class="rounded-[2rem] border border-line bg-shell p-6 shadow-card space-y-4">
            <p class="text-sm font-semibold text-ocean">{{ __('public.home.quick_search') }}</p>
            <p class="text-steel">{{ __('public.home.quick_search_hint') }}</p>
            @include('components.word-search')
            <div class="rounded-2xl bg-ocean/8 border border-ocean/20 p-4 text-sm text-night">
                {{ __('public.home.dictionary_cta') }}
            </div>
        </div>
    </div>
</section>

{{-- ─────────────────────── BETA BANNER ─────────────────────── --}}
@if(config('app.is_beta'))
<section class="mb-10 rounded-2xl border border-amber/40 bg-amber/10 p-6">
    <div class="flex items-start gap-4">
        <svg class="h-6 w-6 shrink-0 text-amber" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <div>
            <h3 class="text-sm font-semibold text-night">{{ __('public.beta.banner_title') }}</h3>
            <p class="mt-1 text-sm text-steel">{{ __('public.beta.banner_text') }}</p>
        </div>
    </div>
</section>
@endif

{{-- ─────────────────────── HOW IT WORKS ─────────────────────── --}}
<section class="mb-10 border-b border-line/80 pb-10">
    <h2 class="mb-8 text-center font-display text-[2rem] font-extrabold text-night">
        {{ strtoupper(__('public.home.steps_title')) }}
    </h2>
    <div class="grid gap-8 sm:grid-cols-3">
        @php
            $steps = [
                [
                    'number' => '1',
                    'title'  => __('public.home.step_find'),
                    'desc'   => __('public.home.step_find_desc'),
                    'svg'    => '<svg class="h-10 w-10" viewBox="0 0 48 48" fill="none"><path d="M14 11H29C31.2 11 33 12.8 33 15V31C33 33.2 31.2 35 29 35H14C11.8 35 10 33.2 10 31V15C10 12.8 11.8 11 14 11Z" stroke="#173152" stroke-width="2.6"/><path d="M16 18H27" stroke="#173152" stroke-width="2.6" stroke-linecap="round"/><path d="M16 24H24" stroke="#173152" stroke-width="2.6" stroke-linecap="round"/><circle cx="34.5" cy="33.5" r="4.5" stroke="#F59B2F" stroke-width="2.6"/><path d="M37.5 36.5L41 40" stroke="#F59B2F" stroke-width="2.6" stroke-linecap="round"/></svg>',
                ],
                [
                    'number' => '2',
                    'title'  => __('public.home.step_start'),
                    'desc'   => __('public.home.step_start_desc'),
                    'svg'    => '<svg class="h-10 w-10" viewBox="0 0 48 48" fill="none"><rect x="9" y="9" width="30" height="30" rx="6" stroke="#173152" stroke-width="2.6"/><path d="M20 17L30 24L20 31V17Z" fill="#2F67B1"/></svg>',
                ],
                [
                    'number' => '3',
                    'title'  => __('public.home.step_share'),
                    'desc'   => __('public.home.step_share_desc'),
                    'svg'    => '<svg class="h-10 w-10" viewBox="0 0 48 48" fill="none"><circle cx="24" cy="24" r="14" stroke="#173152" stroke-width="2.6"/><path d="M17 24L22 29L31 19" stroke="#2F67B1" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round"/></svg>',
                ],
            ];
        @endphp
        @foreach($steps as $step)
            <article class="text-center">
                <div class="mx-auto mb-4 flex h-20 w-20 items-center justify-center rounded-full bg-shell shadow-card">
                    {!! $step['svg'] !!}
                </div>
                <div class="mx-auto mb-2 inline-flex h-8 w-8 items-center justify-center rounded-full bg-ocean font-display text-sm font-extrabold text-white">
                    {{ $step['number'] }}
                </div>
                <h3 class="mt-2 font-display text-lg font-extrabold text-night">{{ $step['title'] }}</h3>
                <p class="mt-2 text-sm leading-6 text-steel">{{ $step['desc'] }}</p>
            </article>
        @endforeach
    </div>
</section>

{{-- ─────────────────────── PICK YOUR PATH ─────────────────────── --}}
<section class="mb-10 space-y-6">
    <div>
        <p class="text-xs font-semibold uppercase tracking-[0.25em] text-ocean">{{ __('public.home.sections') }}</p>
        <h2 class="font-display text-2xl font-extrabold text-night">{{ __('public.home.pick_path') }}</h2>
    </div>
    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
        @php
            $paths = [
                [
                    'href'  => localized_route('catalog.tests-cards'),
                    'icon'  => '📚',
                    'title' => __('public.nav.catalog'),
                    'desc'  => __('public.home.catalog_desc'),
                ],
                [
                    'href'  => localized_route('theory.index'),
                    'icon'  => '🧠',
                    'title' => __('public.nav.theory'),
                    'desc'  => __('public.home.theory_desc'),
                ],
                [
                    'href'  => localized_route('words.test'),
                    'icon'  => '📝',
                    'title' => __('public.nav.words_test'),
                    'desc'  => __('public.home.words_desc'),
                ],
                [
                    'href'  => localized_route('verbs.test'),
                    'icon'  => '⚡',
                    'title' => __('public.nav.verbs_test'),
                    'desc'  => __('public.home.verbs_desc'),
                ],
            ];
        @endphp
        @foreach($paths as $path)
            <a href="{{ $path['href'] }}"
               class="group flex h-full flex-col justify-between rounded-2xl border border-line bg-shell p-5 shadow-sm transition hover:border-ocean hover:shadow-card">
                <div class="space-y-3">
                    <span class="inline-flex h-12 w-12 items-center justify-center rounded-xl bg-ocean/10 text-2xl">{{ $path['icon'] }}</span>
                    <h3 class="font-display text-xl font-extrabold text-night">{{ $path['title'] }}</h3>
                    <p class="text-sm leading-6 text-steel">{{ $path['desc'] }}</p>
                </div>
                <span class="mt-4 inline-flex items-center gap-1 text-sm font-semibold text-ocean group-hover:underline">
                    {{ __('public.common.go_to') }}
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </span>
            </a>
        @endforeach
    </div>
</section>

{{-- ─────────────────────── STUDY FLOW ─────────────────────── --}}
<section class="grid gap-6 lg:grid-cols-[1.1fr_1fr] items-start rounded-[1.5rem] border border-line bg-[radial-gradient(circle_at_top_left,_rgba(47,103,177,0.07),_transparent_40%),#f7fbff] p-6 shadow-card">
    <div class="space-y-4">
        <p class="text-xs font-semibold uppercase tracking-[0.25em] text-ocean">{{ __('public.home.how_it_works') }}</p>
        <h2 class="font-display text-2xl font-extrabold text-night">{{ __('public.home.steps_title') }}</h2>
        <p class="text-steel">{{ __('public.home.steps_intro') }}</p>
        <div class="space-y-3">
            @php
                $flowSteps = [
                    ['num' => '1', 'title' => __('public.home.step_find'),  'desc' => __('public.home.step_find_desc')],
                    ['num' => '2', 'title' => __('public.home.step_start'), 'desc' => __('public.home.step_start_desc')],
                    ['num' => '3', 'title' => __('public.home.step_share'), 'desc' => __('public.home.step_share_desc')],
                ];
            @endphp
            @foreach($flowSteps as $fs)
                <div class="flex gap-3 rounded-2xl border border-line bg-shell p-4 shadow-sm">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-ocean font-display text-sm font-extrabold text-white">
                        {{ $fs['num'] }}
                    </div>
                    <div>
                        <p class="font-semibold text-night">{{ $fs['title'] }}</p>
                        <p class="text-sm text-steel">{{ $fs['desc'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    <div class="space-y-4 rounded-2xl border border-line bg-shell p-6 text-sm text-steel shadow-sm">
        <h3 class="font-display text-xl font-extrabold text-night">{{ __('public.home.search_block_title') }}</h3>
        <p>{{ __('public.home.search_block_desc') }}</p>
        <form action="{{ localized_route('site.search') }}" method="GET"
              class="flex items-center gap-3 rounded-xl border border-line bg-white px-4 py-2 shadow-sm">
            <input type="search" name="q"
                   placeholder="{{ __('public.search.placeholder') }}"
                   class="w-full bg-transparent text-sm text-night focus:outline-none placeholder:text-steel/60">
            <button type="submit"
                    class="rounded-xl bg-ocean px-4 py-2 text-xs font-semibold text-white transition hover:bg-[#245592]">
                {{ __('public.search.button') }}
            </button>
        </form>
        <p class="text-xs text-steel/70">{{ __('public.home.search_block_hint') }}</p>
    </div>
</section>
@endsection
