@extends('layouts.engram')

@section('title', __('public.search.title'))

@section('content')
@php
    $pageResults = $results->where('type', 'page');
    $testResults = $results->where('type', 'test');
@endphp
<div class="space-y-10">
    {{-- Hero Section with Search --}}
    <section class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-brand-600 via-brand-500 to-brand-400 p-8 text-white shadow-card">
        {{-- Decorative background elements --}}
        <div class="absolute inset-0 opacity-20">
            <div class="absolute -top-20 -right-20 w-64 h-64 bg-white/20 rounded-full blur-3xl"></div>
            <div class="absolute -bottom-20 -left-20 w-48 h-48 bg-white/15 rounded-full blur-3xl"></div>
        </div>
        
        <div class="relative flex flex-col gap-6 md:flex-row md:items-center md:justify-between">
            <div>
                <span class="inline-flex items-center gap-2 rounded-full bg-white/20 backdrop-blur-sm px-4 py-2 text-sm font-semibold mb-4">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    {{ __('public.search.found_for') }}
                </span>
                <h1 class="text-3xl md:text-4xl font-bold">"{{ e($query) }}"</h1>
            </div>
            <form action="{{ localized_route('site.search') }}" method="GET" class="flex w-full max-w-lg items-center gap-3 rounded-full border border-white/30 bg-white/20 backdrop-blur-sm px-4 py-2 shadow-lg">
                <svg class="h-5 w-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="search" name="q" value="{{ $query }}" placeholder="{{ __('public.search.placeholder') }}" class="w-full bg-transparent text-sm text-white placeholder-white/70 focus:outline-none" />
                <button type="submit" class="rounded-full bg-white px-5 py-2 text-xs font-semibold text-brand-600 shadow-md transition hover:shadow-lg hover:-translate-y-0.5">{{ __('public.search.button') }}</button>
            </form>
        </div>
        
        {{-- Stats Cards --}}
        <div class="relative mt-8 grid gap-4 sm:grid-cols-2">
            <div class="rounded-2xl bg-white/20 backdrop-blur-sm p-5 border border-white/20">
                <div class="flex items-center gap-3">
                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-white/30 text-white" role="img" aria-label="{{ __('public.search.pages') }}">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                    </span>
                    <div>
                        <p class="text-xs uppercase tracking-[0.2em] font-semibold text-white/80">{{ __('public.search.pages') }}</p>
                        <p class="text-2xl font-bold">{{ $pageResults->count() }}</p>
                    </div>
                </div>
            </div>
            <div class="rounded-2xl bg-white/20 backdrop-blur-sm p-5 border border-white/20">
                <div class="flex items-center gap-3">
                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-white/30 text-white" role="img" aria-label="{{ __('public.search.tests') }}">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                    </span>
                    <div>
                        <p class="text-xs uppercase tracking-[0.2em] font-semibold text-white/80">{{ __('public.search.tests') }}</p>
                        <p class="text-2xl font-bold">{{ $testResults->count() }}</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Results Section --}}
    <section class="grid gap-8 lg:grid-cols-2">
        {{-- Pages Results --}}
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-brand-100 text-brand-600" role="img" aria-label="{{ __('public.search.pages') }}">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                    </span>
                    <h2 class="text-xl font-semibold">{{ __('public.search.pages') }}</h2>
                </div>
                <span class="rounded-full bg-brand-50 px-3 py-1 text-xs font-semibold text-brand-700">{{ __('public.search.results_count', ['count' => $pageResults->count()]) }}</span>
            </div>
            @if($pageResults->isEmpty())
                <div class="rounded-2xl border border-dashed border-[var(--border)] bg-[var(--card)] p-8 text-center text-[var(--muted)] shadow-sm">
                    <div class="flex justify-center mb-3">
                        <span class="inline-flex h-12 w-12 items-center justify-center rounded-full bg-brand-50 text-brand-600">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </span>
                    </div>
                    {{ __('public.search.nothing_found') }}
                </div>
            @else
                <div class="space-y-3">
                    @foreach($pageResults as $item)
                        <a href="{{ $item['url'] }}" class="group flex items-center justify-between rounded-2xl border border-[var(--border)] bg-[var(--card)] p-5 shadow-sm transition hover:border-brand-500 hover:shadow-md hover:-translate-y-0.5">
                            <div>
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-brand-50 px-2.5 py-1 text-xs font-semibold text-brand-700 mb-2">
                                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                                    {{ __('public.common.type_theory') }}
                                </span>
                                <p class="text-lg font-semibold group-hover:text-brand-600 transition">{{ $item['title'] }}</p>
                                <p class="text-xs text-[var(--muted)]">{{ $item['url'] }}</p>
                            </div>
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-brand-50 text-brand-600 transition group-hover:bg-brand-600 group-hover:text-white">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
        
        {{-- Tests Results --}}
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-brand-100 text-brand-600" role="img" aria-label="{{ __('public.search.tests') }}">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                    </span>
                    <h2 class="text-xl font-semibold">{{ __('public.search.tests') }}</h2>
                </div>
                <span class="rounded-full bg-brand-50 px-3 py-1 text-xs font-semibold text-brand-700">{{ __('public.search.results_count', ['count' => $testResults->count()]) }}</span>
            </div>
            @if($testResults->isEmpty())
                <div class="rounded-2xl border border-dashed border-[var(--border)] bg-[var(--card)] p-8 text-center text-[var(--muted)] shadow-sm">
                    <div class="flex justify-center mb-3">
                        <span class="inline-flex h-12 w-12 items-center justify-center rounded-full bg-brand-50 text-brand-600">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </span>
                    </div>
                    {{ __('public.search.nothing_found') }}
                </div>
            @else
                <div class="space-y-3">
                    @foreach($testResults as $item)
                        <a href="{{ $item['url'] }}" class="group flex items-center justify-between rounded-2xl border border-[var(--border)] bg-[var(--card)] p-5 shadow-sm transition hover:border-brand-500 hover:shadow-md hover:-translate-y-0.5">
                            <div>
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-brand-50 px-2.5 py-1 text-xs font-semibold text-brand-700 mb-2">
                                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                                    {{ __('public.common.type_test') }}
                                </span>
                                <p class="text-lg font-semibold group-hover:text-brand-600 transition">{{ $item['title'] }}</p>
                            </div>
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-brand-50 text-brand-600 transition group-hover:bg-brand-600 group-hover:text-white">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
</div>
@endsection
