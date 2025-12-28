@extends('layouts.public')

@section('title', __('public.search.title'))

@section('content')
<div class="space-y-6 max-w-4xl mx-auto">
    <header class="space-y-2">
        <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-foreground">{{ __('public.search.results_title') }}</h1>
        <p class="text-sm text-muted-foreground">
            {{ __('public.search.found_for') }} <span class="font-semibold text-foreground">"{{ e($query) }}"</span>
        </p>
    </header>

    @if($results->isEmpty())
        <div class="rounded-2xl border-2 border-dashed border-border bg-muted/20 p-8 sm:p-12 text-center">
            <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-muted">
                <svg class="h-7 w-7 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
            <p class="text-lg font-semibold text-foreground">{{ __('public.search.nothing_found') }}</p>
            <p class="mt-2 text-sm text-muted-foreground">{{ __('public.search.try_another') }}</p>
            <a href="{{ route('catalog.tests-cards') }}" class="mt-5 inline-flex items-center gap-2 rounded-lg bg-primary px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-primary/90">
                {{ __('public.search.to_catalog') }}
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                </svg>
            </a>
        </div>
    @else
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($results as $item)
                <a href="{{ $item['url'] }}" class="group flex flex-col gap-3 rounded-xl border border-border bg-card p-5 shadow-sm transition-all hover:-translate-y-0.5 hover:border-primary/40 hover:shadow-md">
                    <div class="flex items-start justify-between gap-2">
                        <h3 class="text-base font-semibold text-foreground group-hover:text-primary transition-colors">{{ $item['title'] }}</h3>
                        <span class="shrink-0 rounded-full bg-muted px-2.5 py-0.5 text-xs font-medium text-muted-foreground">
                            {{ $item['type'] === 'page' ? __('public.common.type_theory') : __('public.common.type_test') }}
                        </span>
                    </div>
                    <div class="flex items-center gap-2 text-sm text-primary font-medium">
                        <span>{{ __('public.common.go_to') }}</span>
                        <svg class="h-4 w-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</div>
@endsection
