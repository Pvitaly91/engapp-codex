@extends('layouts.catalog-public')

@section('title', __('public.search.title'))

@section('content')
@php
    $pageResults = $results->where('type', 'page');
    $testResults = $results->where('type', 'test');
    $hasQuery = filled(trim((string) $query));
    $hasEnoughChars = mb_strlen(trim((string) $query)) >= 2;
    $hasResults = $results->isNotEmpty();
    $summaryCards = [
        ['label' => __('public.search.pages'), 'value' => $pageResults->count(), 'tone' => 'bg-ocean'],
        ['label' => __('public.search.tests'), 'value' => $testResults->count(), 'tone' => 'bg-amber'],
        ['label' => __('public.search.button'), 'value' => $hasQuery ? __('frontend.search.live') : __('frontend.search.wait'), 'tone' => 'bg-emerald-500'],
        ['label' => __('public.home.search_block_hint'), 'value' => $hasEnoughChars ? '2+' : '0-1', 'tone' => 'bg-slate-800 dark:bg-slate-200'],
    ];
@endphp

<div class="nd-page">
    <section class="nd-section-tight relative border-b" style="border-color: var(--line);">
        <div class="absolute left-[12%] top-10 hidden h-24 w-24 rounded-full border-[16px] border-ocean/40 lg:block"></div>
        <div class="absolute right-[10%] top-10 hidden h-20 w-20 rounded-full bg-amber/80 lg:block"></div>
        <div class="absolute bottom-0 right-0 hidden h-56 w-16 rounded-tl-[2.5rem] bg-ocean lg:block"></div>

        <div class="relative grid gap-6 lg:grid-cols-[1.02fr_0.98fr] lg:items-center">
            <div class="max-w-3xl">
                <span class="inline-flex items-center rounded-full border px-4 py-2 text-xs font-extrabold uppercase tracking-[0.28em] soft-accent" style="border-color: var(--line); color: var(--accent);">
                    {{ __('public.search.title') }}
                </span>

                @if($hasQuery)
                    <h1 class="mt-6 font-display text-3xl font-extrabold leading-[1.04] sm:text-4xl">
                        {{ __('public.search.found_for') }}
                        <span class="block" style="color: var(--accent);">"{{ e($query) }}"</span>
                    </h1>
                    <p class="mt-5 max-w-3xl text-lg leading-8 sm:text-xl" style="color: var(--muted);">
                        {{ __('public.home.search_block_desc') }}
                    </p>
                @else
                    <h1 class="mt-6 font-display text-3xl font-extrabold leading-[1.04] sm:text-4xl">
                        {{ __('public.search.placeholder') }}
                    </h1>
                    <p class="mt-5 max-w-3xl text-lg leading-8 sm:text-xl" style="color: var(--muted);">
                        {{ __('public.home.search_block_hint') }}
                    </p>
                @endif

                <form action="{{ localized_route('site.search') }}" method="GET" class="mt-8 rounded-[24px] border p-3 shadow-sm surface-card-strong" style="border-color: var(--line);">
                    <div class="flex flex-col gap-3 sm:flex-row">
                        <input
                            type="search"
                            name="q"
                            value="{{ $query }}"
                            placeholder="{{ __('public.search.placeholder') }}"
                            class="w-full rounded-[18px] border px-4 py-3 text-sm font-medium outline-none transition focus:border-ocean focus:ring-2 focus:ring-blue-100 dark:bg-slate-900/50"
                            style="border-color: var(--line);"
                        >
                        <button type="submit" class="rounded-[18px] bg-ocean px-5 py-3 text-sm font-extrabold uppercase tracking-[0.18em] text-white transition hover:bg-[#245592]">
                            {{ __('public.search.button') }}
                        </button>
                    </div>
                </form>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                @foreach($summaryCards as $card)
                    <article class="rounded-[28px] border p-6 shadow-card surface-card-strong" style="border-color: var(--line);">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-[11px] font-extrabold uppercase tracking-[0.22em]" style="color: var(--accent);">{{ $card['label'] }}</p>
                                <p class="mt-3 font-display text-[2.25rem] font-extrabold leading-none">{{ $card['value'] }}</p>
                            </div>
                            <span class="inline-flex h-12 w-12 items-center justify-center rounded-[18px] {{ $card['tone'] }} text-sm font-extrabold text-white dark:text-slate-950">
                                {{ strtoupper(mb_substr((string) $card['value'], 0, 2)) }}
                            </span>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="nd-section">
        @if(!$hasQuery)
            <div class="rounded-[30px] border border-dashed p-10 text-center shadow-card surface-card-strong" style="border-color: var(--line);">
                <h2 class="font-display text-2xl font-extrabold">{{ __('public.search.placeholder') }}</h2>
                <p class="mt-4 max-w-2xl mx-auto text-sm leading-7 sm:text-base" style="color: var(--muted);">
                    {{ __('public.home.search_block_desc') }}
                </p>
            </div>
        @elseif(!$hasEnoughChars)
            <div class="rounded-[30px] border p-8 shadow-card surface-card-strong" style="border-color: var(--line);">
                <p class="text-[11px] font-extrabold uppercase tracking-[0.22em]" style="color: var(--accent);">{{ __('public.search.title') }}</p>
                <h2 class="mt-3 font-display text-2xl font-extrabold leading-tight">{{ __('public.home.search_block_hint') }}</h2>
                <p class="mt-4 text-sm leading-7 sm:text-base" style="color: var(--muted);">
                    {{ __('public.search.nothing_found') }}
                </p>
            </div>
        @elseif(!$hasResults)
            <div class="rounded-[30px] border border-dashed p-10 text-center shadow-card surface-card-strong" style="border-color: var(--line);">
                <h2 class="font-display text-2xl font-extrabold">"{{ e($query) }}"</h2>
                <p class="mt-4 text-sm leading-7 sm:text-base" style="color: var(--muted);">
                    {{ __('public.search.nothing_found') }}
                </p>
            </div>
        @else
            <div class="grid gap-6 xl:grid-cols-2">
                <section class="space-y-5">
                    <div class="flex items-end justify-between gap-4">
                        <div>
                            <p class="text-[11px] font-extrabold uppercase tracking-[0.22em]" style="color: var(--accent);">{{ __('public.common.type_theory') }}</p>
                            <h2 class="mt-2 font-display text-2xl font-extrabold leading-none">{{ __('public.search.pages') }}</h2>
                        </div>
                        <span class="text-sm font-semibold" style="color: var(--muted);">{{ __('public.search.results_count', ['count' => $pageResults->count()]) }}</span>
                    </div>

                    <div class="grid gap-4">
                        @foreach($pageResults as $index => $item)
                            @php
                                $accents = ['bg-ocean', 'bg-amber', 'bg-emerald-500', 'bg-slate-800 dark:bg-slate-200', 'bg-rose-500', 'bg-sky-500'];
                                $accent = $accents[$index % count($accents)];
                            @endphp
                            <a href="{{ $item['url'] }}" class="rounded-[26px] border p-5 shadow-card transition hover:-translate-y-1 surface-card-strong" style="border-color: var(--line);">
                                <div class="flex items-start justify-between gap-4">
                                    <span class="inline-flex h-12 w-12 items-center justify-center rounded-[18px] {{ $accent }} text-sm font-extrabold text-white dark:text-slate-950">
                                        {{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}
                                    </span>
                                    <span class="text-[11px] font-extrabold uppercase tracking-[0.22em]" style="color: var(--muted);">{{ __('public.common.type_theory') }}</span>
                                </div>
                                <h3 class="mt-5 font-display text-xl font-extrabold leading-tight">{{ $item['title'] }}</h3>
                                <p class="mt-3 break-all text-sm leading-6" style="color: var(--muted);">{{ $item['url'] }}</p>
                            </a>
                        @endforeach
                    </div>
                </section>

                <section class="space-y-5">
                    <div class="flex items-end justify-between gap-4">
                        <div>
                            <p class="text-[11px] font-extrabold uppercase tracking-[0.22em]" style="color: var(--accent);">{{ __('public.common.type_test') }}</p>
                            <h2 class="mt-2 font-display text-2xl font-extrabold leading-none">{{ __('public.search.tests') }}</h2>
                        </div>
                        <span class="text-sm font-semibold" style="color: var(--muted);">{{ __('public.search.results_count', ['count' => $testResults->count()]) }}</span>
                    </div>

                    <div class="grid gap-4">
                        @foreach($testResults as $index => $item)
                            @php
                                $accents = ['bg-ocean', 'bg-amber', 'bg-emerald-500', 'bg-slate-800 dark:bg-slate-200', 'bg-rose-500', 'bg-sky-500'];
                                $accent = $accents[$index % count($accents)];
                            @endphp
                            <a href="{{ $item['url'] }}" class="rounded-[26px] border p-5 shadow-card transition hover:-translate-y-1 surface-card-strong" style="border-color: var(--line);">
                                <div class="flex items-start justify-between gap-4">
                                    <span class="inline-flex h-12 w-12 items-center justify-center rounded-[18px] {{ $accent }} text-sm font-extrabold text-white dark:text-slate-950">
                                        {{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}
                                    </span>
                                    <span class="text-[11px] font-extrabold uppercase tracking-[0.22em]" style="color: var(--muted);">{{ __('public.common.type_test') }}</span>
                                </div>
                                <h3 class="mt-5 font-display text-xl font-extrabold leading-tight">{{ $item['title'] }}</h3>
                                <div class="mt-4 flex items-center justify-between gap-3 text-sm font-extrabold uppercase tracking-[0.18em]" style="color: var(--muted);">
                                    <span>{{ __('public.search.button') }}</span>
                                    <span style="color: var(--accent);">→</span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </section>
            </div>
        @endif
    </section>
</div>
@endsection
