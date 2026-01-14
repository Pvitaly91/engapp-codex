@extends('layouts.engram')

@section('title', __('public.coming_soon.meta_title'))

@section('content')
<div class="mx-auto max-w-3xl">
    <div class="rounded-3xl border border-[var(--border)] bg-[var(--card)] p-8 shadow-card">
        <div class="flex flex-col gap-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start">
                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-brand-100 text-brand-600">
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l3 3" />
                        <circle cx="12" cy="12" r="9" />
                    </svg>
                </div>
                <div class="space-y-2">
                    <p class="text-xs font-semibold uppercase tracking-[0.25em] text-brand-600">{{ __('public.coming_soon.kicker') }}</p>
                    <h1 class="text-3xl font-semibold">{{ __('public.coming_soon.title') }}</h1>
                    <p class="text-[var(--muted)]">{{ __('public.coming_soon.text') }}</p>
                </div>
            </div>

            <div class="flex flex-wrap gap-3">
                <a href="{{ localized_route('home') }}" class="inline-flex items-center justify-center rounded-full bg-brand-600 px-6 py-3 text-sm font-semibold text-white shadow-card">
                    {{ __('public.coming_soon.home') }}
                </a>
                <button type="button" onclick="history.back()" class="inline-flex items-center justify-center rounded-full border border-[var(--border)] bg-[var(--card)] px-6 py-3 text-sm font-semibold">
                    {{ __('public.coming_soon.back') }}
                </button>
            </div>

            <p class="text-sm text-[var(--muted)]">{{ __('public.coming_soon.contact_hint') }}</p>
        </div>
    </div>
</div>
@endsection
