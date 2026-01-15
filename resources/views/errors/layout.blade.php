@php
    $code = $code ?? 500;
    $title = $title ?? __('public.errors.5xx.title');
    $message = $message ?? __('public.errors.5xx.text');
    $showSearch = $showSearch ?? false;
@endphp

@extends('layouts.engram')

@section('title', __('public.errors.meta_title', ['code' => $code]))

@section('content')
<div class="mx-auto max-w-3xl">
    <div class="rounded-3xl border border-[var(--border)] bg-[var(--card)] p-8 shadow-card">
        <div class="flex flex-col gap-8">
            <div class="flex flex-col gap-6 sm:flex-row sm:items-start">
                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-brand-100 text-brand-600">
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.29 3.86l-7.2 12.5A2 2 0 004.82 19h14.36a2 2 0 001.73-2.64l-7.2-12.5a2 2 0 00-3.42 0z" />
                    </svg>
                </div>
                <div class="space-y-2">
                    <p class="text-xs font-semibold uppercase tracking-[0.25em] text-brand-600">{{ __('public.errors.kicker') }}</p>
                    <div class="text-5xl font-bold text-brand-600">{{ $code }}</div>
                    <h1 class="text-2xl font-semibold">{{ $title }}</h1>
                    <p class="text-[var(--muted)]">{{ $message }}</p>
                </div>
            </div>

            <div class="flex flex-wrap gap-3">
                <a href="{{ localized_route('home') }}" class="inline-flex items-center justify-center rounded-full bg-brand-600 px-6 py-3 text-sm font-semibold text-white shadow-card">
                    {{ __('public.errors.home') }}
                </a>
                <button type="button" onclick="history.back()" class="inline-flex items-center justify-center rounded-full border border-[var(--border)] bg-[var(--card)] px-6 py-3 text-sm font-semibold">
                    {{ __('public.errors.back') }}
                </button>
            </div>

            @if ($showSearch)
                <form action="{{ localized_route('site.search') }}" method="GET" class="flex flex-col gap-3 rounded-2xl border border-[var(--border)] bg-[var(--card)] p-4 sm:flex-row sm:items-center">
                    <label class="text-sm font-semibold" for="error-search">{{ __('public.errors.search_label') }}</label>
                    <input id="error-search" type="search" name="q" placeholder="{{ __('public.search.placeholder') }}" class="flex-1 rounded-full border border-[var(--border)] bg-white/80 px-4 py-2 text-sm focus:border-brand-500 focus:outline-none">
                    <button type="submit" class="rounded-full bg-brand-600 px-4 py-2 text-xs font-semibold text-white">{{ __('public.search.button') }}</button>
                </form>
            @endif
        </div>
    </div>
</div>
@endsection
