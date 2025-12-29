@extends('layouts.engram')

@section('title', __('public.search.title'))

@section('content')
@php
    $pageResults = $results->where('type', 'page');
    $testResults = $results->where('type', 'test');
@endphp
<div class="space-y-12">
    <section class="overflow-hidden rounded-3xl border border-[var(--border)] bg-gradient-to-br from-brand-50/80 via-white to-white p-8 shadow-card">
        <div class="flex flex-col gap-6 md:flex-row md:items-center md:justify-between">
            <div class="space-y-2">
                <span class="inline-flex items-center gap-2 rounded-full bg-brand-100 px-4 py-2 text-xs font-semibold text-brand-700">
                    {{ __('public.search.title') }}
                </span>
                <p class="text-sm text-[var(--muted)]">{{ __('public.search.found_for') }}</p>
                <h1 class="text-3xl font-bold text-[var(--fg)] md:text-4xl">“{{ e($query) }}”</h1>
                <p class="text-sm text-[var(--muted)] max-w-2xl">{{ __('public.home.search_block_hint') }}</p>
            </div>
            <form action="{{ localized_route('site.search') }}" method="GET" class="flex w-full max-w-xl items-center gap-3 rounded-full border border-brand-100 bg-white px-4 py-3 shadow-lg shadow-brand-100/60">
                <svg class="h-5 w-5 text-brand-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="search" name="q" value="{{ $query }}" placeholder="{{ __('public.search.placeholder') }}" class="w-full bg-transparent text-sm font-medium focus:outline-none" />
                <button type="submit" class="inline-flex items-center gap-2 rounded-full bg-brand-600 px-4 py-2 text-xs font-semibold text-white shadow-card transition hover:-translate-y-0.5">
                    {{ __('public.search.button') }}
                </button>
            </form>
        </div>
        <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4 text-sm text-[var(--muted)]">
            <div class="rounded-2xl border border-brand-100 bg-white/80 p-4 shadow-sm">
                <p class="text-xs uppercase tracking-[0.2em] font-semibold text-brand-600">{{ __('public.search.pages') }}</p>
                <p class="text-2xl font-bold text-brand-700">{{ $pageResults->count() }}</p>
            </div>
            <div class="rounded-2xl border border-brand-100 bg-brand-50/80 p-4 text-brand-900 shadow-sm">
                <p class="text-xs uppercase tracking-[0.2em] font-semibold">{{ __('public.search.tests') }}</p>
                <p class="text-2xl font-bold">{{ $testResults->count() }}</p>
            </div>
            <div class="rounded-2xl border border-[var(--border)] bg-[var(--card)]/90 p-4 shadow-sm">
                <p class="text-xs uppercase tracking-[0.2em] font-semibold">{{ __('public.home.stat_categories') }}</p>
                <p class="text-2xl font-bold text-[var(--fg)]">120+</p>
            </div>
            <div class="rounded-2xl border border-[var(--border)] bg-[var(--card)]/90 p-4 shadow-sm">
                <p class="text-xs uppercase tracking-[0.2em] font-semibold">{{ __('public.home.stat_tags') }}</p>
                <p class="text-2xl font-bold text-[var(--fg)]">7k+</p>
            </div>
        </div>
    </section>

    <section class="grid gap-8 lg:grid-cols-2">
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs uppercase tracking-[0.2em] text-brand-600">{{ __('public.search.pages') }}</p>
                    <h2 class="text-xl font-semibold">{{ __('public.search.results_count', ['count' => $pageResults->count()]) }}</h2>
                </div>
                <span class="inline-flex items-center gap-2 rounded-full bg-brand-50 px-3 py-1 text-xs font-semibold text-brand-700">{{ __('public.common.type_theory') }}</span>
            </div>
            @if($pageResults->isEmpty())
                <div class="rounded-2xl border border-dashed border-[var(--border)] bg-[var(--card)]/70 p-6 text-[var(--muted)]">{{ __('public.search.nothing_found') }}</div>
            @else
                <div class="divide-y divide-[var(--border)] overflow-hidden rounded-2xl border border-[var(--border)] bg-[var(--card)] shadow-card">
                    @foreach($pageResults as $item)
                        <a href="{{ $item['url'] }}" class="block px-5 py-4 transition hover:bg-brand-50/70">
                            <p class="text-sm font-semibold text-brand-600">{{ __('public.common.type_theory') }}</p>
                            <p class="text-lg font-semibold text-[var(--fg)]">{{ $item['title'] }}</p>
                            <p class="text-xs text-[var(--muted)]">{{ $item['url'] }}</p>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs uppercase tracking-[0.2em] text-brand-600">{{ __('public.search.tests') }}</p>
                    <h2 class="text-xl font-semibold">{{ __('public.search.results_count', ['count' => $testResults->count()]) }}</h2>
                </div>
                <span class="inline-flex items-center gap-2 rounded-full bg-brand-600/10 px-3 py-1 text-xs font-semibold text-brand-700">{{ __('public.common.type_test') }}</span>
            </div>
            @if($testResults->isEmpty())
                <div class="rounded-2xl border border-dashed border-[var(--border)] bg-[var(--card)]/70 p-6 text-[var(--muted)]">{{ __('public.search.nothing_found') }}</div>
            @else
                <div class="grid gap-3">
                    @foreach($testResults as $item)
                        <a href="{{ $item['url'] }}" class="group flex items-center justify-between rounded-2xl border border-[var(--border)] bg-[var(--card)] p-4 shadow-card transition hover:-translate-y-0.5 hover:border-brand-500 hover:shadow-lg">
                            <div class="space-y-1">
                                <p class="text-sm font-semibold text-brand-600">{{ __('public.common.type_test') }}</p>
                                <p class="text-lg font-semibold text-[var(--fg)]">{{ $item['title'] }}</p>
                            </div>
                            <span class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-brand-50 text-brand-600 shadow-sm transition group-hover:bg-brand-600 group-hover:text-white">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                            </span>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
</div>
@endsection
