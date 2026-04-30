@extends('layouts.catalog-public')

@section('title', __('public.courses.title'))

@section('content')
@php
    $coursesCount = count($courses);
    $levelsCount = collect($courses)->sum(fn ($course) => count($course['levels'] ?? []));
@endphp

<div class="nd-page relative isolate">
    <nav class="mb-8 flex flex-wrap items-center gap-2 text-xs font-semibold uppercase tracking-[0.18em]" style="color: var(--muted);" aria-label="{{ __('public.common.breadcrumb') }}">
        <a href="{{ localized_route('home') }}" class="transition hover:text-ocean">{{ __('public.common.home') }}</a>
        <span>/</span>
        <span style="color: var(--text);">{{ __('public.courses.breadcrumb') }}</span>
    </nav>

    <section class="relative overflow-hidden rounded-[30px] border p-7 shadow-card surface-card-strong" style="border-color: var(--line);">
        <div class="absolute -right-10 top-0 hidden h-36 w-36 rounded-full border-[18px] border-ocean/30 lg:block"></div>
        <div class="absolute bottom-0 right-0 hidden h-44 w-14 rounded-tl-[2rem] bg-amber lg:block"></div>
        <div class="relative grid gap-8 lg:grid-cols-[1.05fr_0.95fr] lg:items-end">
            <div class="max-w-3xl">
                <span class="inline-flex items-center rounded-full border px-4 py-2 text-xs font-extrabold uppercase tracking-[0.22em] soft-accent" style="border-color: var(--line); color: var(--accent);">
                    {{ __('public.courses.badge') }}
                </span>
                <h1 class="mt-4 font-display text-3xl font-extrabold leading-[1.04] sm:text-4xl">
                    {{ __('public.courses.heading') }}
                </h1>
                <p class="mt-4 max-w-2xl text-sm leading-7 sm:text-base" style="color: var(--muted);">
                    {{ __('public.courses.description') }}
                </p>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <article class="rounded-[24px] border p-5 surface-card" style="border-color: var(--line);">
                    <p class="text-[11px] font-extrabold uppercase tracking-[0.22em]" style="color: var(--accent);">{{ __('public.courses.available_courses') }}</p>
                    <p class="mt-2 font-display text-[2.25rem] font-extrabold leading-none">{{ $coursesCount }}</p>
                    <p class="mt-2 text-sm leading-6" style="color: var(--muted);">{{ trans_choice('public.courses.course_count', $coursesCount, ['count' => $coursesCount]) }}</p>
                </article>
                <article class="rounded-[24px] border p-5 surface-card" style="border-color: var(--line);">
                    <p class="text-[11px] font-extrabold uppercase tracking-[0.22em]" style="color: var(--accent);">{{ __('public.courses.available_levels') }}</p>
                    <p class="mt-2 font-display text-[2.25rem] font-extrabold leading-none">{{ $levelsCount }}</p>
                    <p class="mt-2 text-sm leading-6" style="color: var(--muted);">{{ trans_choice('public.courses.level_count', $levelsCount, ['count' => $levelsCount]) }}</p>
                </article>
            </div>
        </div>
    </section>

    <section class="mt-8 rounded-[28px] border p-6 shadow-card surface-card-strong" style="border-color: var(--line);">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-[11px] font-extrabold uppercase tracking-[0.22em]" style="color: var(--accent);">{{ __('public.courses.catalog_label') }}</p>
                <h2 class="mt-2 font-display text-2xl font-extrabold">{{ __('public.courses.catalog_heading') }}</h2>
            </div>
            <p class="max-w-2xl text-sm leading-6 sm:text-right" style="color: var(--muted);">
                {{ __('public.courses.catalog_hint') }}
            </p>
        </div>

        <div class="mt-6 grid gap-5">
            @foreach($courses as $course)
                <article class="overflow-hidden rounded-[24px] border shadow-card surface-card" style="border-color: var(--line);">
                    <div class="grid gap-0 lg:grid-cols-[0.92fr_1.08fr]">
                        <div class="border-b p-6 lg:border-b-0 lg:border-r" style="border-color: var(--line);">
                            <div class="flex items-start justify-between gap-4">
                                <span class="inline-flex h-16 w-16 items-center justify-center rounded-[22px] bg-ocean font-display text-lg font-extrabold text-white">PG</span>
                                <span class="rounded-full border px-3 py-1 text-xs font-extrabold uppercase tracking-[0.18em] soft-accent" style="border-color: var(--line); color: var(--accent);">
                                    {{ __('public.courses.course_card_badge') }}
                                </span>
                            </div>
                            <h3 class="mt-6 font-display text-2xl font-extrabold leading-tight">{{ $course['title'] }}</h3>
                            <p class="mt-3 text-sm leading-7" style="color: var(--muted);">{{ $course['description'] }}</p>
                        </div>

                        <div class="p-6">
                            <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                                <div>
                                    <p class="text-[11px] font-extrabold uppercase tracking-[0.22em]" style="color: var(--accent);">{{ __('public.courses.levels_title') }}</p>
                                    <h4 class="mt-2 font-display text-xl font-extrabold">{{ __('public.courses.choose_level') }}</h4>
                                </div>
                                <p class="text-sm leading-6 sm:text-right" style="color: var(--muted);">{{ __('public.courses.levels_hint') }}</p>
                            </div>

                            <div class="mt-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                                @foreach($course['levels'] as $level)
                                    <a href="{{ $level['url'] }}"
                                       class="group rounded-[22px] border p-4 transition hover:-translate-y-1 hover:border-ocean surface-card-strong"
                                       style="border-color: var(--line);"
                                       data-course-slug="{{ $level['course_slug'] }}">
                                        <div class="flex items-center justify-between gap-3">
                                            <span class="inline-flex h-12 w-12 items-center justify-center rounded-[18px] bg-amber font-display text-base font-extrabold text-white">{{ $level['level'] }}</span>
                                            <span class="text-xs font-extrabold uppercase tracking-[0.18em]" style="color: var(--muted);">{{ __('public.common.level') }}</span>
                                        </div>
                                        <p class="mt-4 font-display text-lg font-extrabold">{{ $course['title'] }} {{ $level['level'] }}</p>
                                        <p class="mt-2 text-sm font-semibold transition group-hover:translate-x-1" style="color: var(--accent);">
                                            {{ __('public.courses.open_level') }}
                                        </p>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    </section>
</div>
@endsection
