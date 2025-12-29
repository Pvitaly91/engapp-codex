@extends('layouts.engram')

@section('title', __('public.search.title'))

@section('content')
@php
    $pageResults = $results->where('type', 'page');
    $testResults = $results->where('type', 'test');
@endphp
<div class="space-y-10">
    <section class="rounded-3xl border border-[var(--border)] bg-[var(--card)]/90 p-6 shadow-card">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="text-sm text-[var(--muted)]">{{ __('public.search.found_for') }}</p>
                <h1 class="text-3xl font-bold">“{{ e($query) }}”</h1>
            </div>
            <form action="{{ localized_route('site.search') }}" method="GET" class="flex w-full max-w-lg items-center gap-3 rounded-full border border-[var(--border)] bg-white/70 px-4 py-2 shadow-sm">
                <svg class="h-5 w-5 text-brand-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="search" name="q" value="{{ $query }}" placeholder="{{ __('public.search.placeholder') }}" class="w-full bg-transparent text-sm focus:outline-none" />
                <button type="submit" class="rounded-full bg-brand-600 px-4 py-2 text-xs font-semibold text-white">{{ __('public.search.button') }}</button>
            </form>
        </div>
        <div class="mt-6 grid gap-4 text-sm text-[var(--muted)] sm:grid-cols-2">
            <div class="rounded-2xl bg-brand-50/80 p-4 text-brand-900 border border-brand-100">
                <p class="text-xs uppercase tracking-[0.2em] font-semibold text-brand-600">{{ __('public.search.pages') }}</p>
                <p class="text-2xl font-bold text-brand-900">{{ $pageResults->count() }}</p>
            </div>
            <div class="rounded-2xl bg-brand-50/60 p-4 text-brand-900 border border-brand-100">
                <p class="text-xs uppercase tracking-[0.2em] font-semibold text-brand-600">{{ __('public.search.tests') }}</p>
                <p class="text-2xl font-bold text-brand-900">{{ $testResults->count() }}</p>
            </div>
        </div>
    </section>

    <section class="grid gap-8 lg:grid-cols-2">
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold">{{ __('public.search.pages') }}</h2>
                <span class="text-sm text-[var(--muted)]">{{ __('public.search.results_count', ['count' => $pageResults->count()]) }}</span>
            </div>
            @if($pageResults->isEmpty())
                <div class="rounded-2xl border border-dashed border-[var(--border)] bg-[var(--card)] p-8 text-center shadow-sm">
                    <div class="flex justify-center mb-3">
                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-brand-50 text-brand-600">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                    </div>
                    <p class="text-[var(--muted)]">{{ __('public.search.nothing_found') }}</p>
                </div>
            @else
                <div class="divide-y divide-[var(--border)] overflow-hidden rounded-2xl border border-[var(--border)] bg-[var(--card)]">
                    @foreach($pageResults as $item)
                        <a href="{{ $item['url'] }}" class="block px-5 py-4 hover:bg-brand-50/70 dark:hover:bg-slate-800">
                            <p class="text-sm text-brand-600">{{ __('public.common.type_theory') }}</p>
                            <p class="text-lg font-semibold">{{ $item['title'] }}</p>
                            <p class="text-xs text-[var(--muted)]">{{ $item['url'] }}</p>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold">{{ __('public.search.tests') }}</h2>
                <span class="text-sm text-[var(--muted)]">{{ __('public.search.results_count', ['count' => $testResults->count()]) }}</span>
            </div>
            @if($testResults->isEmpty())
                <div class="rounded-2xl border border-dashed border-[var(--border)] bg-[var(--card)] p-8 text-center shadow-sm">
                    <div class="flex justify-center mb-3">
                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-indigo-50 text-indigo-600">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                            </svg>
                        </div>
                    </div>
                    <p class="text-[var(--muted)]">{{ __('public.search.nothing_found') }}</p>
                </div>
            @else
                <div class="grid gap-3">
                    @foreach($testResults as $item)
                        <a href="{{ $item['url'] }}" class="flex items-center justify-between rounded-2xl border border-[var(--border)] bg-[var(--card)] p-4 shadow-sm hover:border-brand-500">
                            <div>
                                <p class="text-sm text-brand-600">{{ __('public.common.type_test') }}</p>
                                <p class="text-lg font-semibold">{{ $item['title'] }}</p>
                            </div>
                            <svg class="h-5 w-5 text-brand-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
</div>
@endsection
