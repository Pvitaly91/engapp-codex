@extends('layouts.catalog-public')

@section('title', $lesson['title'] . ' - ' . __('frontend.theory_course.mixed_test'))

@section('content')
@php
    $counts = $pool['counts'] ?? [];
    $questionCount = count($questions);
    $manifestPayload = [
        'course' => $course,
        'lesson' => $lesson,
        'lessons' => $manifest['flat_lessons'] ?? [],
        'nextLesson' => $nextLesson,
        'questions' => $questions,
        'completion' => $completion,
        'i18n' => __('frontend.theory_course'),
        'courseI18n' => __('frontend.tests.course'),
    ];
@endphp

<div class="nd-page"
     data-theory-mixed-root
     data-course-slug="{{ $course['slug'] }}"
     data-lesson-slug="{{ $lesson['lesson_slug'] }}">
    <nav class="mb-8 flex flex-wrap items-center gap-2 text-xs font-semibold uppercase tracking-[0.18em]" style="color: var(--muted);" aria-label="{{ __('public.common.breadcrumb') }}">
        <a href="{{ localized_route('home') }}" class="transition hover:text-ocean">{{ __('public.common.home') }}</a>
        <span>/</span>
        <a href="{{ $course['url'] }}" class="transition hover:text-ocean">{{ $course['name'] }}</a>
        <span>/</span>
        <a href="{{ $lesson['lesson_url'] }}" class="transition hover:text-ocean">{{ $lesson['title'] }}</a>
        <span>/</span>
        <span style="color: var(--text);">{{ __('frontend.theory_course.mixed_test') }}</span>
    </nav>

    <section class="relative overflow-hidden rounded-[30px] border p-7 shadow-card surface-card-strong" style="border-color: var(--line);">
        <div class="absolute -right-10 top-0 hidden h-36 w-36 rounded-full border-[18px] border-ocean/30 lg:block"></div>
        <div class="absolute bottom-0 right-0 hidden h-44 w-14 rounded-tl-[2rem] bg-ocean lg:block"></div>
        <div class="relative grid gap-6 lg:grid-cols-[1.05fr_0.95fr] lg:items-end">
            <div class="max-w-3xl">
                <a href="{{ $lesson['lesson_url'] }}" class="inline-flex items-center gap-2 rounded-full border px-4 py-2 text-xs font-extrabold uppercase tracking-[0.18em] transition hover:opacity-90" style="border-color: var(--line); color: var(--accent);">
                    <span aria-hidden="true">&larr;</span>
                    <span>{{ __('frontend.theory_course.open_lesson') }}</span>
                </a>
                <span class="mt-4 inline-flex items-center rounded-full border px-4 py-2 text-xs font-extrabold uppercase tracking-[0.22em] soft-accent" style="border-color: var(--line); color: var(--accent);">
                    {{ __('frontend.theory_course.mixed_test') }}
                </span>
                <h1 class="mt-4 font-display text-3xl font-extrabold leading-[1.04] sm:text-4xl">{{ $lesson['title'] }}</h1>
                <p class="mt-4 max-w-2xl text-sm leading-7 sm:text-base" style="color: var(--muted);">
                    {{ __('frontend.theory_course.questions_from_topic_tests') }}
                </p>
            </div>

            <div class="grid gap-4 sm:grid-cols-3">
                <article class="rounded-[24px] border p-5 surface-card" style="border-color: var(--line);">
                    <p class="text-[11px] font-extrabold uppercase tracking-[0.22em]" style="color: var(--accent);">{{ __('frontend.tests.hero.questions') }}</p>
                    <p class="mt-2 font-display text-[2.1rem] font-extrabold leading-none">{{ $questionCount }}</p>
                    <p class="mt-2 text-sm leading-6" style="color: var(--muted);">{{ __('frontend.theory_course.mixed_pool') }}</p>
                </article>
                <article class="rounded-[24px] border p-5 surface-card" style="border-color: var(--line);">
                    <p class="text-[11px] font-extrabold uppercase tracking-[0.22em]" style="color: var(--accent);">V3</p>
                    <p class="mt-2 font-display text-[2.1rem] font-extrabold leading-none">{{ (int) ($counts['standard_questions'] ?? 0) }}</p>
                    <p class="mt-2 text-sm leading-6" style="color: var(--muted);">{{ __('frontend.theory_course.standard_questions') }}</p>
                </article>
                <article class="rounded-[24px] border p-5 surface-card" style="border-color: var(--line);">
                    <p class="text-[11px] font-extrabold uppercase tracking-[0.22em]" style="color: var(--accent);">Polyglot</p>
                    <p class="mt-2 font-display text-[2.1rem] font-extrabold leading-none">{{ (int) ($counts['compose_questions'] ?? 0) }}</p>
                    <p class="mt-2 text-sm leading-6" style="color: var(--muted);">{{ __('frontend.theory_course.compose_questions') }}</p>
                </article>
            </div>
        </div>
    </section>

    <section id="theory-mixed-lock" class="mt-8 hidden rounded-[28px] border border-dashed p-6 text-center surface-card">
        <h2 class="font-display text-2xl font-extrabold">{{ __('frontend.theory_course.lesson_locked') }}</h2>
        <p class="mt-3 text-sm leading-7" style="color: var(--muted);">{{ __('frontend.theory_course.complete_previous') }}</p>
        <a href="{{ $course['url'] }}" class="mt-5 inline-flex rounded-full bg-ocean px-5 py-3 text-sm font-extrabold text-white">{{ __('frontend.tests.course.back_to_course') }}</a>
    </section>

    @if($questionCount < 1)
        <section class="mt-8 rounded-[28px] border border-dashed p-8 text-center surface-card">
            <h2 class="font-display text-2xl font-extrabold">{{ __('frontend.theory_course.no_tests_for_lesson') }}</h2>
            <p class="mt-3 text-sm leading-7" style="color: var(--muted);">{{ __('frontend.theory_course.need_generate_tests') }}</p>
            <a href="{{ $lesson['lesson_url'] }}" class="mt-5 inline-flex rounded-full bg-ocean px-5 py-3 text-sm font-extrabold text-white">{{ __('frontend.theory_course.open_lesson') }}</a>
        </section>
    @endif

    <section id="theory-mixed-workspace" class="mt-8 {{ $questionCount < 1 ? 'hidden' : '' }}">
        <div class="sticky-test-header sticky top-0 z-30">
            <div class="sticky-inner rounded-[28px] border p-4 shadow-card surface-card-strong" style="border-color: var(--line);">
                <div class="grid gap-3 lg:grid-cols-[minmax(0,1fr)_auto] lg:items-center">
                    <div class="space-y-2">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <p class="text-[11px] font-extrabold uppercase tracking-[0.18em]" style="color: var(--muted);">{{ __('frontend.tests.progress.label') }}</p>
                                <p id="theory-mixed-progress-label" class="font-display text-xl font-extrabold">0 / {{ $questionCount }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-[11px] font-extrabold uppercase tracking-[0.18em]" style="color: var(--muted);">{{ __('frontend.tests.progress.accuracy') }}</p>
                                <p id="theory-mixed-score-label" class="font-display text-xl font-extrabold" style="color: var(--accent);">0%</p>
                            </div>
                        </div>
                        <div class="h-3 overflow-hidden rounded-full border" style="border-color: var(--line); background: color-mix(in srgb, var(--surface) 88%, white);">
                            <div id="theory-mixed-progress-bar" class="h-full rounded-full transition-all duration-300" style="width: 0%; background: linear-gradient(90deg, #2f67b1 0%, #74a9f0 55%, #f59b2f 100%);"></div>
                        </div>
                    </div>
                    <button type="button" id="theory-mixed-reset-lesson" class="inline-flex items-center justify-center rounded-full border px-5 py-3 text-sm font-bold" style="border-color: var(--line);">
                        {{ __('frontend.tests.compose.reset_progress') }}
                    </button>
                </div>
            </div>
        </div>

        <article class="mt-6 rounded-[30px] border p-5 shadow-card surface-card-strong sm:p-7" style="border-color: var(--line);">
            <div id="theory-mixed-question-card"></div>
            <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:justify-between">
                <button type="button" id="theory-mixed-prev" class="rounded-full border px-5 py-3 text-sm font-bold disabled:opacity-50" style="border-color: var(--line);">
                    {{ __('frontend.tests.actions.previous') }}
                </button>
                <button type="button" id="theory-mixed-next" class="rounded-full bg-ocean px-5 py-3 text-sm font-extrabold text-white disabled:opacity-50">
                    {{ __('frontend.tests.actions.next') }}
                </button>
            </div>
        </article>
    </section>

    <section id="theory-mixed-summary" class="mt-8 hidden rounded-[28px] border p-6 text-center shadow-card surface-card-strong" style="border-color: var(--line);">
        <h2 id="theory-mixed-summary-title" class="font-display text-2xl font-extrabold"></h2>
        <p id="theory-mixed-summary-text" class="mt-3 text-sm leading-7" style="color: var(--muted);"></p>
        <div class="mt-6 flex flex-wrap justify-center gap-3">
            <button type="button" id="theory-mixed-retry" class="inline-flex rounded-full border px-5 py-3 text-sm font-bold" style="border-color: var(--line);">
                {{ __('frontend.tests.actions.try_again') }}
            </button>
            @if($nextLesson)
                <a id="theory-mixed-next-lesson" href="{{ $nextLesson['lesson_url'] }}" class="hidden inline-flex rounded-full bg-ocean px-5 py-3 text-sm font-extrabold text-white">
                    {{ __('frontend.tests.course.next_lesson') }}
                </a>
            @endif
            <a href="{{ $lesson['lesson_url'] }}" class="inline-flex rounded-full border px-5 py-3 text-sm font-bold" style="border-color: var(--line);">
                {{ __('frontend.theory_course.open_lesson') }}
            </a>
        </div>
    </section>
</div>
@endsection

@section('scripts')
@php
    $theoryMixedVersion = is_file(public_path('js/theory-mixed-test.js'))
        ? filemtime(public_path('js/theory-mixed-test.js'))
        : null;
@endphp
<script>
window.__THEORY_MIXED_TEST__ = @json($manifestPayload);
</script>
<script type="module" src="{{ asset('js/theory-mixed-test.js') }}@if($theoryMixedVersion)?v={{ $theoryMixedVersion }}@endif"></script>
@endsection
