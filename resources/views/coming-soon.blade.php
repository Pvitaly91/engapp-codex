@extends('layouts.engram')

@section('title', __('public.coming_soon.title'))

@section('content')
<div class="flex min-h-[60vh] items-center justify-center py-10">
    <div class="mx-auto max-w-2xl space-y-8 text-center">
        <div class="inline-flex h-24 w-24 items-center justify-center rounded-full bg-brand-100 text-brand-600">
            <svg class="h-12 w-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>

        <div class="space-y-3">
            <h1 class="text-4xl font-bold">{{ __('public.coming_soon.title') }}</h1>
            <p class="text-lg text-[var(--muted)]">{{ __('public.coming_soon.text') }}</p>
        </div>

        <div class="flex flex-wrap items-center justify-center gap-3 pt-4">
            <a href="{{ localized_route('home') }}" class="inline-flex items-center gap-2 rounded-full bg-brand-600 px-6 py-3 text-sm font-semibold text-white shadow-card hover:bg-brand-700 transition">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                {{ __('public.coming_soon.home') }}
            </a>
            <button onclick="window.history.back()" class="inline-flex items-center gap-2 rounded-full border border-[var(--border)] bg-[var(--card)] px-6 py-3 text-sm font-semibold hover:border-brand-500 transition">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                {{ __('public.coming_soon.back') }}
            </button>
        </div>

        <div class="border-t border-[var(--border)] pt-6">
            <p class="text-xs text-[var(--muted)]">{{ __('public.coming_soon.bug_notice') }}</p>
        </div>
    </div>
</div>
@endsection
