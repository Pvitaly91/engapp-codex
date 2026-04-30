@extends('layouts.catalog-public')

@section('title', $lesson['title'])

@section('content')
@php
    $counts = $poolSummary['counts'] ?? [];
    $hasTests = (bool) ($poolSummary['has_tests'] ?? false);
    $manifestPayload = [
        'course' => $course,
        'lessons' => $manifest['flat_lessons'] ?? [],
        'lesson' => $lesson,
        'next_lesson' => $nextLesson,
    ];
@endphp

<div class="nd-page"
     data-theory-lesson-root
     data-course-slug="{{ $course['slug'] }}"
     data-lesson-slug="{{ $lesson['lesson_slug'] }}">
    <nav class="mb-8 flex flex-wrap items-center gap-2 text-xs font-semibold uppercase tracking-[0.18em]" style="color: var(--muted);" aria-label="{{ __('public.common.breadcrumb') }}">
        <a href="{{ localized_route('home') }}" class="transition hover:text-ocean">{{ __('public.common.home') }}</a>
        <span>/</span>
        <a href="{{ localized_route('courses.index') }}" class="transition hover:text-ocean">{{ __('public.nav.courses') }}</a>
        <span>/</span>
        <a href="{{ $course['url'] }}" class="transition hover:text-ocean">{{ $course['name'] }}</a>
        <span>/</span>
        <span style="color: var(--text);">{{ $lesson['title'] }}</span>
    </nav>

    <section class="relative overflow-hidden rounded-[30px] border p-7 shadow-card surface-card-strong" style="border-color: var(--line);">
        <div class="absolute -right-10 top-0 hidden h-36 w-36 rounded-full border-[18px] border-ocean/30 lg:block"></div>
        <div class="absolute bottom-0 right-0 hidden h-44 w-14 rounded-tl-[2rem] bg-ocean lg:block"></div>
        <div class="relative grid gap-6 lg:grid-cols-[1.05fr_0.95fr] lg:items-end">
            <div class="max-w-3xl">
                <a href="{{ $course['url'] }}" class="inline-flex items-center gap-2 rounded-full border px-4 py-2 text-xs font-extrabold uppercase tracking-[0.18em] transition hover:opacity-90" style="border-color: var(--line); color: var(--accent);">
                    <span aria-hidden="true">&larr;</span>
                    <span>{{ __('frontend.tests.course.back_to_course') }}</span>
                </a>
                <div class="mt-4 flex flex-wrap items-center gap-2 text-xs font-semibold uppercase tracking-[0.18em]" style="color: var(--muted);">
                    <span>{{ __('frontend.tests.course.lesson_number', ['number' => $lesson['lesson_order']]) }}</span>
                    <span>&bull;</span>
                    <span>{{ $lesson['category_title'] }}</span>
                    <span>&bull;</span>
                    <span data-theory-lesson-status-label>{{ __('frontend.tests.course.current') }}</span>
                </div>
                <h1 class="mt-4 font-display text-3xl font-extrabold leading-[1.04] sm:text-4xl">{{ $lesson['title'] }}</h1>
                <p class="mt-4 max-w-2xl text-sm leading-7 sm:text-base" style="color: var(--muted);">
                    {{ __('frontend.theory_course.lesson_intro') }}
                </p>
                <div class="mt-6 flex flex-wrap gap-3">
                    @if($hasTests)
                        <a href="{{ $lesson['test_url'] }}"
                           data-theory-lesson-test-link
                           class="inline-flex items-center justify-center rounded-full bg-ocean px-6 py-3 text-sm font-extrabold text-white shadow-sm transition hover:opacity-95">
                            {{ __('frontend.theory_course.mixed_test') }}
                        </a>
                    @else
                        <span class="inline-flex items-center justify-center rounded-full border px-6 py-3 text-sm font-extrabold" style="border-color: #fecaca; color: #b42318;">
                            {{ __('frontend.theory_course.no_tests_for_lesson') }}
                        </span>
                    @endif
                    <a href="{{ $lesson['theory_url'] }}"
                       class="inline-flex items-center justify-center rounded-full border px-6 py-3 text-sm font-bold transition hover:opacity-95"
                       style="border-color: var(--line);">
                        {{ __('frontend.tests.question.theory') }}
                    </a>
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-3">
                <article class="rounded-[24px] border p-5 surface-card" style="border-color: var(--line);">
                    <p class="text-[11px] font-extrabold uppercase tracking-[0.22em]" style="color: var(--accent);">{{ __('public.common.related_tests') }}</p>
                    <p class="mt-2 font-display text-[2.1rem] font-extrabold leading-none">{{ (int) ($counts['tests_total'] ?? 0) }}</p>
                    <p class="mt-2 text-sm leading-6" style="color: var(--muted);">{{ __('frontend.theory_course.questions_from_topic_tests') }}</p>
                </article>
                <article class="rounded-[24px] border p-5 surface-card" style="border-color: var(--line);">
                    <p class="text-[11px] font-extrabold uppercase tracking-[0.22em]" style="color: var(--accent);">V3</p>
                    <p class="mt-2 font-display text-[2.1rem] font-extrabold leading-none">{{ (int) ($counts['standard_tests'] ?? 0) }}</p>
                    <p class="mt-2 text-sm leading-6" style="color: var(--muted);">{{ __('frontend.theory_course.standard_tests') }}</p>
                </article>
                <article class="rounded-[24px] border p-5 surface-card" style="border-color: var(--line);">
                    <p class="text-[11px] font-extrabold uppercase tracking-[0.22em]" style="color: var(--accent);">Polyglot</p>
                    <p class="mt-2 font-display text-[2.1rem] font-extrabold leading-none">{{ (int) ($counts['polyglot_tests'] ?? 0) }}</p>
                    <p class="mt-2 text-sm leading-6" style="color: var(--muted);">{{ __('frontend.theory_course.polyglot_tests') }}</p>
                </article>
            </div>
        </div>
    </section>

    <section data-theory-lesson-lock class="mt-8 hidden rounded-[28px] border border-dashed p-6 text-center surface-card">
        <h2 class="font-display text-2xl font-extrabold">{{ __('frontend.theory_course.lesson_locked') }}</h2>
        <p class="mt-3 text-sm leading-7" style="color: var(--muted);">{{ __('frontend.theory_course.complete_previous') }}</p>
        <a href="{{ $course['url'] }}" class="mt-5 inline-flex rounded-full bg-ocean px-5 py-3 text-sm font-extrabold text-white">{{ __('frontend.tests.course.back_to_course') }}</a>
    </section>

    <section data-theory-lesson-next class="mt-8 hidden rounded-[28px] border p-6 shadow-card" style="border-color: #b8e3c7; background: linear-gradient(180deg, #f0fbf4 0%, #e7f8ee 100%);">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-[11px] font-extrabold uppercase tracking-[0.22em]" style="color: #17603a;">{{ __('frontend.tests.course.lesson_unlocked') }}</p>
                <h2 class="mt-2 font-display text-2xl font-extrabold" style="color: #17603a;">{{ __('frontend.tests.course.next_lesson_available') }}</h2>
                @if($nextLesson)
                    <p class="mt-2 text-sm leading-6" style="color: #17603a;">{{ $nextLesson['title'] }}</p>
                @endif
            </div>
            @if($nextLesson)
                <a href="{{ $nextLesson['lesson_url'] }}" class="inline-flex items-center justify-center rounded-full bg-ocean px-5 py-3 text-sm font-extrabold text-white">
                    {{ __('frontend.tests.course.next_lesson') }}
                </a>
            @endif
        </div>
    </section>

    <div data-theory-lesson-content class="mt-8">
        @include('courses.partials.theory-page-content', ['page' => $page])
    </div>

    <section class="mt-8 rounded-[28px] border p-6 shadow-card surface-card" style="border-color: var(--line);">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <p class="text-[11px] font-extrabold uppercase tracking-[0.22em]" style="color: var(--accent);">{{ __('frontend.theory_course.mixed_test') }}</p>
                <h2 class="mt-2 font-display text-2xl font-extrabold">{{ __('frontend.theory_course.questions_from_topic_tests') }}</h2>
                <p class="mt-3 max-w-3xl text-sm leading-7" style="color: var(--muted);">
                    {{ $hasTests
                        ? __('frontend.theory_course.mixed_pool_summary', [
                            'tests' => (int) ($counts['tests_total'] ?? 0),
                            'standard' => (int) ($counts['standard_tests'] ?? 0),
                            'polyglot' => (int) ($counts['polyglot_tests'] ?? 0),
                            'questions' => (int) ($counts['questions_total'] ?? 0),
                        ])
                        : __('frontend.theory_course.need_generate_tests') }}
                </p>
            </div>
            <div class="flex flex-wrap gap-3">
                @if($hasTests)
                    <a href="{{ $lesson['test_url'] }}" data-theory-lesson-test-link class="inline-flex items-center justify-center rounded-full bg-ocean px-5 py-3 text-sm font-extrabold text-white">
                        {{ __('frontend.theory_course.start_mixed_test') }}
                    </a>
                @else
                    <span class="inline-flex items-center justify-center rounded-full border px-5 py-3 text-sm font-bold" style="border-color: #fecaca; color: #b42318;">
                        {{ __('frontend.theory_course.need_generate_tests') }}
                    </span>
                @endif
                <button type="button" data-theory-lesson-reset class="inline-flex items-center justify-center rounded-full border px-5 py-3 text-sm font-bold" style="border-color: var(--line);">
                    {{ __('frontend.tests.compose.reset_progress') }}
                </button>
            </div>
        </div>
    </section>
</div>
@endsection

@section('scripts')
@php
    $theoryCourseProgressVersion = is_file(public_path('js/theory-course-progress.js'))
        ? filemtime(public_path('js/theory-course-progress.js'))
        : null;
@endphp
<script type="module" src="{{ asset('js/theory-course-progress.js') }}@if($theoryCourseProgressVersion)?v={{ $theoryCourseProgressVersion }}@endif"></script>
<script>
window.__THEORY_COURSE_MANIFEST__ = @json($manifestPayload);
window.__FRONTEND_COURSE_I18N__ = @json(__('frontend.tests.course'));

(function () {
    let booted = false;

    function boot() {
        if (booted || !window.TheoryCourseProgress) return;

        const manifest = window.__THEORY_COURSE_MANIFEST__ || {};
        const course = manifest.course || {};
        const lesson = manifest.lesson || {};
        const lessons = Array.isArray(manifest.lessons) ? manifest.lessons : [];
        const root = document.querySelector('[data-theory-lesson-root]');
        const content = document.querySelector('[data-theory-lesson-content]');
        const lock = document.querySelector('[data-theory-lesson-lock]');
        const next = document.querySelector('[data-theory-lesson-next]');
        const statusLabel = document.querySelector('[data-theory-lesson-status-label]');
        const reset = document.querySelector('[data-theory-lesson-reset]');
        const i18n = window.__FRONTEND_COURSE_I18N__ || {};

        if (!root || !course.slug || !lesson.lesson_slug) return;

        booted = true;

        const store = window.TheoryCourseProgress.createStore(course.slug, lessons);

        function label(status) {
            if (status === 'completed') return i18n.completed || 'Completed';
            if (status === 'locked') return i18n.locked || 'Locked';
            if (status === 'current') return i18n.current || 'Current';
            return i18n.available || 'Available';
        }

        function render() {
            const state = store.read();
            const status = store.getLessonStatus(lesson.lesson_slug, state);
            const locked = status === 'locked';
            const completed = status === 'completed';

            if (statusLabel) statusLabel.textContent = label(status);
            lock?.classList.toggle('hidden', !locked);
            content?.classList.toggle('hidden', locked);
            next?.classList.toggle('hidden', !completed);

            document.querySelectorAll('[data-theory-lesson-test-link]').forEach((link) => {
                link.setAttribute('aria-disabled', locked ? 'true' : 'false');
                link.style.pointerEvents = locked ? 'none' : 'auto';
                link.style.opacity = locked ? '0.6' : '1';
            });
        }

        reset?.addEventListener('click', () => {
            store.resetLesson(lesson.lesson_slug);
            render();
        });

        store.markLessonOpened(lesson.lesson_slug);
        render();
        window.addEventListener(window.TheoryCourseProgress.events.updated, render);
    }

    if (window.TheoryCourseProgress) boot();
    else window.addEventListener('theory-course:progress-ready', boot, { once: true });
})();
</script>
@endsection
