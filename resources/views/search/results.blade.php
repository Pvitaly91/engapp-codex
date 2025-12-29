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
                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-white/30 text-white">🧠</span>
                    <div>
                        <p class="text-xs uppercase tracking-[0.2em] font-semibold text-white/80">{{ __('public.search.pages') }}</p>
                        <p class="text-2xl font-bold">{{ $pageResults->count() }}</p>
                    </div>
                </div>
            </div>
            <div class="rounded-2xl bg-white/20 backdrop-blur-sm p-5 border border-white/20">
                <div class="flex items-center gap-3">
                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-white/30 text-white">📚</span>
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
                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-brand-100 text-brand-600">🧠</span>
                    <h2 class="text-xl font-semibold">{{ __('public.search.pages') }}</h2>
                </div>
                <span class="rounded-full bg-brand-50 px-3 py-1 text-xs font-semibold text-brand-700">{{ __('public.search.results_count', ['count' => $pageResults->count()]) }}</span>
            </div>
            @if($pageResults->isEmpty())
                <div class="rounded-2xl border border-dashed border-[var(--border)] bg-[var(--card)] p-8 text-center text-[var(--muted)] shadow-sm">
                    <div class="flex justify-center mb-3">
                        <span class="inline-flex h-12 w-12 items-center justify-center rounded-full bg-brand-50 text-brand-600">📄</span>
                    </div>
                    {{ __('public.search.nothing_found') }}
                </div>
            @else
                <div class="space-y-3">
                    @foreach($pageResults as $item)
                        <a href="{{ $item['url'] }}" class="group flex items-center justify-between rounded-2xl border border-[var(--border)] bg-[var(--card)] p-5 shadow-sm transition hover:border-brand-500 hover:shadow-md hover:-translate-y-0.5">
                            <div>
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-brand-50 px-2.5 py-1 text-xs font-semibold text-brand-700 mb-2">
                                    🧠 {{ __('public.common.type_theory') }}
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
                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-brand-100 text-brand-600">📚</span>
                    <h2 class="text-xl font-semibold">{{ __('public.search.tests') }}</h2>
                </div>
                <span class="rounded-full bg-brand-50 px-3 py-1 text-xs font-semibold text-brand-700">{{ __('public.search.results_count', ['count' => $testResults->count()]) }}</span>
            </div>
            @if($testResults->isEmpty())
                <div class="rounded-2xl border border-dashed border-[var(--border)] bg-[var(--card)] p-8 text-center text-[var(--muted)] shadow-sm">
                    <div class="flex justify-center mb-3">
                        <span class="inline-flex h-12 w-12 items-center justify-center rounded-full bg-brand-50 text-brand-600">📝</span>
                    </div>
                    {{ __('public.search.nothing_found') }}
                </div>
            @else
                <div class="space-y-3">
                    @foreach($testResults as $item)
                        <a href="{{ $item['url'] }}" class="group flex items-center justify-between rounded-2xl border border-[var(--border)] bg-[var(--card)] p-5 shadow-sm transition hover:border-brand-500 hover:shadow-md hover:-translate-y-0.5">
                            <div>
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-brand-50 px-2.5 py-1 text-xs font-semibold text-brand-700 mb-2">
                                    📚 {{ __('public.common.type_test') }}
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
