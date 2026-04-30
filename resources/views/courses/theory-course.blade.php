@extends('layouts.catalog-public')

@section('title', $course['name'])

@section('content')
@php
    $courseSlug = $course['slug'];
    $totalLessons = count($lessons);
    $lessonsWithTests = collect($lessons)->where('has_tests', true)->count();
    $manifestPayload = [
        'course' => $course,
        'lessons' => $lessons,
        'first_lesson' => $firstLesson,
    ];
@endphp

<div class="nd-page"
     data-theory-course-root
     data-course-slug="{{ $courseSlug }}">
    <nav class="mb-8 flex flex-wrap items-center gap-2 text-xs font-semibold uppercase tracking-[0.18em]" style="color: var(--muted);" aria-label="{{ __('public.common.breadcrumb') }}">
        <a href="{{ localized_route('home') }}" class="transition hover:text-ocean">{{ __('public.common.home') }}</a>
        <span>/</span>
        <a href="{{ localized_route('courses.index') }}" class="transition hover:text-ocean">{{ __('public.nav.courses') }}</a>
        <span>/</span>
        <span style="color: var(--text);">{{ $course['name'] }}</span>
    </nav>

    <section class="relative overflow-hidden rounded-[30px] border p-7 shadow-card surface-card-strong" style="border-color: var(--line);">
        <div class="absolute -right-10 top-0 hidden h-36 w-36 rounded-full border-[18px] border-ocean/30 lg:block"></div>
        <div class="absolute bottom-0 right-0 hidden h-44 w-14 rounded-tl-[2rem] bg-amber lg:block"></div>
        <div class="relative grid gap-6 lg:grid-cols-[1.05fr_0.95fr] lg:items-end">
            <div class="max-w-3xl">
                <span class="inline-flex items-center rounded-full border px-4 py-2 text-xs font-extrabold uppercase tracking-[0.22em] soft-accent" style="border-color: var(--line); color: var(--accent);">
                    {{ __('frontend.theory_course.grammar_course') }}
                </span>
                <h1 class="mt-4 font-display text-3xl font-extrabold leading-[1.04] sm:text-4xl">{{ $course['name'] }}</h1>
                <p class="mt-4 max-w-2xl text-sm leading-7 sm:text-base" style="color: var(--muted);">
                    {{ __('public.courses.theory_course_description') }}
                </p>
                @if($firstLesson)
                    <div class="mt-6 flex flex-wrap gap-3">
                        <a id="theory-course-hero-cta"
                           href="{{ $firstLesson['lesson_url'] }}"
                           class="inline-flex items-center justify-center rounded-full bg-ocean px-6 py-3 text-sm font-extrabold text-white shadow-sm transition hover:opacity-95">
                            {{ __('frontend.theory_course.start_course') }}
                        </a>
                    </div>
                @endif
            </div>

            <div class="grid gap-4 sm:grid-cols-3">
                <article class="rounded-[24px] border p-5 surface-card" style="border-color: var(--line);">
                    <p class="text-[11px] font-extrabold uppercase tracking-[0.22em]" style="color: var(--accent);">{{ __('frontend.tests.course.course_progress') }}</p>
                    <p id="theory-course-progress-count" class="mt-2 font-display text-[2.1rem] font-extrabold leading-none">0 / {{ $totalLessons }}</p>
                    <p class="mt-2 text-sm leading-6" style="color: var(--muted);">{{ __('frontend.tests.course.lessons_completed') }}</p>
                </article>
                <article class="rounded-[24px] border p-5 surface-card" style="border-color: var(--line);">
                    <p class="text-[11px] font-extrabold uppercase tracking-[0.22em]" style="color: var(--accent);">{{ __('frontend.theory_course.lessons_from_theory') }}</p>
                    <p class="mt-2 font-display text-[2.1rem] font-extrabold leading-none">{{ $totalLessons }}</p>
                    <p class="mt-2 text-sm leading-6" style="color: var(--muted);">{{ __('frontend.copilot_theory.materials_for_learning') }}</p>
                </article>
                <article class="rounded-[24px] border p-5 surface-card" style="border-color: var(--line);">
                    <p class="text-[11px] font-extrabold uppercase tracking-[0.22em]" style="color: var(--accent);">{{ __('frontend.theory_course.mixed_test') }}</p>
                    <p class="mt-2 font-display text-[2.1rem] font-extrabold leading-none">{{ $lessonsWithTests }}</p>
                    <p class="mt-2 text-sm leading-6" style="color: var(--muted);">{{ __('frontend.theory_course.questions_from_topic_tests') }}</p>
                </article>
            </div>
        </div>
    </section>

    <section class="mt-8 rounded-[28px] border p-6 shadow-card surface-card-strong" style="border-color: var(--line);">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-[11px] font-extrabold uppercase tracking-[0.22em]" style="color: var(--accent);">{{ __('frontend.theory_course.english_grammar_theory') }}</p>
                <h2 class="mt-2 font-display text-2xl font-extrabold">{{ __('frontend.tests.course.lesson_list_title', ['count' => $totalLessons]) }}</h2>
                <p id="theory-course-current-label" class="mt-2 text-sm leading-6" style="color: var(--muted);">{{ __('frontend.tests.course.not_started_yet') }}</p>
            </div>
            <button type="button"
                    data-theory-course-reset
                    class="inline-flex items-center justify-center rounded-full border px-5 py-3 text-sm font-bold transition hover:opacity-95"
                    style="border-color: var(--line);">
                {{ __('frontend.theory_course.reset_course_progress') }}
            </button>
        </div>

        <div class="mt-7 space-y-7" id="theory-course-lessons">
            @foreach($sections as $section)
                <section class="rounded-[26px] border p-5 surface-card" style="border-color: var(--line);">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <p class="text-[11px] font-extrabold uppercase tracking-[0.22em]" style="color: var(--accent);">
                                {{ __('public.common.categories') }}
                            </p>
                            <h3 class="mt-2 font-display text-xl font-extrabold">{{ $section['category_title'] }}</h3>
                        </div>
                        <span class="text-xs font-extrabold uppercase tracking-[0.18em]" style="color: var(--muted);">
                            {{ count($section['lessons']) }} {{ __('public.theory.lessons_count') }}
                        </span>
                    </div>

                    <div class="mt-5 grid gap-3">
                        @foreach($section['lessons'] as $lesson)
                            @php
                                $isFirst = ($lesson['lesson_slug'] ?? null) === ($firstLesson['lesson_slug'] ?? null);
                                $serverStatus = $isFirst ? 'current' : 'locked';
                                $counts = $lesson['test_counts'] ?? [];
                            @endphp
                            <article data-theory-course-card
                                     data-lesson-slug="{{ $lesson['lesson_slug'] }}"
                                     data-lesson-order="{{ $lesson['lesson_order'] }}"
                                     data-lesson-url="{{ $lesson['lesson_url'] }}"
                                     data-test-url="{{ $lesson['test_url'] }}"
                                     data-has-tests="{{ $lesson['has_tests'] ? '1' : '0' }}"
                                     class="rounded-[22px] border p-4 transition"
                                     style="border-color: var(--line); opacity: {{ $isFirst ? '1' : '0.72' }};">
                                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                    <div class="min-w-0">
                                        <div class="flex flex-wrap items-center gap-2 text-xs font-semibold uppercase tracking-[0.18em]" style="color: var(--muted);">
                                            <span>{{ __('frontend.tests.course.lesson_number', ['number' => $lesson['lesson_order']]) }}</span>
                                            <span>&bull;</span>
                                            <span>{{ $lesson['category_title'] }}</span>
                                        </div>
                                        <div class="mt-2 flex flex-wrap items-center gap-2">
                                            <h4 class="font-display text-lg font-extrabold leading-tight">{{ $lesson['title'] }}</h4>
                                            <span data-theory-course-status
                                                  class="inline-flex items-center rounded-full border px-3 py-1 text-[11px] font-extrabold uppercase tracking-[0.16em]"
                                                  style="border-color: var(--line); color: var(--accent);">
                                                {{ __('frontend.tests.course.' . $serverStatus) }}
                                            </span>
                                            @if($lesson['has_tests'])
                                                <span class="inline-flex items-center rounded-full px-3 py-1 text-[11px] font-extrabold uppercase tracking-[0.16em]" style="background: var(--accent-soft); color: var(--text);">
                                                    {{ (int) ($counts['tests_total'] ?? 0) }} {{ __('public.common.related_tests') }}
                                                </span>
                                            @else
                                                <span class="inline-flex items-center rounded-full border px-3 py-1 text-[11px] font-extrabold uppercase tracking-[0.16em]" style="border-color: #fecaca; color: #b42318;">
                                                    {{ __('frontend.theory_course.needs_tests') }}
                                                </span>
                                            @endif
                                        </div>
                                        <p class="mt-2 text-sm leading-6" style="color: var(--muted);">
                                            {{ __('frontend.theory_course.mixed_pool_summary', [
                                                'tests' => (int) ($counts['tests_total'] ?? 0),
                                                'standard' => (int) ($counts['standard_tests'] ?? 0),
                                                'polyglot' => (int) ($counts['polyglot_tests'] ?? 0),
                                                'questions' => (int) ($counts['questions_total'] ?? 0),
                                            ]) }}
                                        </p>
                                    </div>
                                    <div class="flex min-w-[14rem] flex-col gap-2">
                                        <a href="{{ $lesson['lesson_url'] }}"
                                           data-theory-course-action
                                           class="inline-flex items-center justify-center rounded-full border px-5 py-3 text-sm font-extrabold transition hover:opacity-95"
                                           style="border-color: {{ $isFirst ? 'var(--accent)' : 'var(--line)' }}; {{ $isFirst ? 'background: var(--accent); color: white;' : 'color: var(--muted); pointer-events: none;' }}"
                                           @if(!$isFirst) aria-disabled="true" @endif>
                                            {{ $isFirst ? __('frontend.theory_course.open_lesson') : __('frontend.theory_course.lesson_locked') }}
                                        </a>
                                        <a href="{{ $lesson['theory_url'] }}" class="text-center text-xs font-bold uppercase tracking-[0.16em]" style="color: var(--muted);">
                                            {{ __('frontend.tests.question.theory') }}
                                        </a>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endforeach
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
window.__THEORY_COURSE_I18N__ = @json(__('frontend.theory_course'));
window.__FRONTEND_COURSE_I18N__ = @json(__('frontend.tests.course'));

(function () {
    let booted = false;

    function tr(source, key, fallback) {
        return (source && Object.prototype.hasOwnProperty.call(source, key)) ? source[key] : fallback;
    }

    function boot() {
        if (booted || !window.TheoryCourseProgress) {
            return;
        }

        const manifest = window.__THEORY_COURSE_MANIFEST__ || {};
        const course = manifest.course || {};
        const lessons = Array.isArray(manifest.lessons) ? manifest.lessons : [];
        const root = document.querySelector('[data-theory-course-root]');
        const cards = Array.from(document.querySelectorAll('[data-theory-course-card]'));
        const progressCount = document.getElementById('theory-course-progress-count');
        const currentLabel = document.getElementById('theory-course-current-label');
        const heroCta = document.getElementById('theory-course-hero-cta');
        const resetButton = document.querySelector('[data-theory-course-reset]');
        const theoryI18n = window.__THEORY_COURSE_I18N__ || {};
        const courseI18n = window.__FRONTEND_COURSE_I18N__ || {};

        if (!root || !course.slug) {
            return;
        }

        booted = true;

        const store = window.TheoryCourseProgress.createStore(course.slug, lessons);

        function lessonBySlug(slug) {
            return lessons.find((lesson) => lesson.lesson_slug === slug || lesson.slug === slug) || null;
        }

        function statusLabel(status) {
            if (status === 'completed') return tr(theoryI18n, 'completed', tr(courseI18n, 'completed', 'Completed'));
            if (status === 'locked') return tr(theoryI18n, 'lesson_locked', tr(courseI18n, 'locked', 'Locked'));
            if (status === 'current') return tr(courseI18n, 'current', 'Current');
            return tr(courseI18n, 'available', 'Available');
        }

        function actionLabel(status, hasTests) {
            if (status === 'locked') return tr(theoryI18n, 'lesson_locked', 'Locked');
            if (!hasTests) return tr(theoryI18n, 'open_lesson', 'Open lesson');
            if (status === 'completed') return tr(courseI18n, 'repeat', 'Repeat');
            return tr(theoryI18n, 'open_lesson', 'Open lesson');
        }

        function render() {
            const state = store.read();
            const summary = store.summary(state);
            const current = lessonBySlug(summary.currentLessonSlug);
            const target = summary.completedAllLessons
                ? (lessons[0] || current)
                : current;

            if (progressCount) {
                progressCount.textContent = `${summary.completedLessons} / ${summary.totalLessons}`;
            }

            if (currentLabel) {
                currentLabel.textContent = summary.completedAllLessons
                    ? tr(courseI18n, 'all_lessons_completed', 'All lessons completed')
                    : (current ? current.title || current.name : tr(courseI18n, 'not_started_yet', 'Not started'));
            }

            if (heroCta && target) {
                heroCta.href = target.lesson_url;
                heroCta.textContent = summary.completedLessons > 0
                    ? tr(theoryI18n, 'continue_course', tr(courseI18n, 'continue', 'Continue'))
                    : tr(theoryI18n, 'start_course', tr(courseI18n, 'start_course', 'Start course'));
            }

            cards.forEach((card) => {
                const slug = card.dataset.lessonSlug || '';
                const status = store.getLessonStatus(slug, state);
                const hasTests = card.dataset.hasTests === '1';
                const badge = card.querySelector('[data-theory-course-status]');
                const action = card.querySelector('[data-theory-course-action]');
                const locked = status === 'locked';

                card.dataset.status = status;
                card.style.opacity = locked ? '0.72' : '1';

                if (badge) {
                    badge.textContent = statusLabel(status);
                }

                if (action) {
                    action.textContent = actionLabel(status, hasTests);
                    action.setAttribute('aria-disabled', locked ? 'true' : 'false');
                    action.style.pointerEvents = locked ? 'none' : 'auto';
                    action.style.borderColor = locked ? 'var(--line)' : 'var(--accent)';
                    action.style.background = locked || status === 'completed' ? 'transparent' : 'var(--accent)';
                    action.style.color = locked ? 'var(--muted)' : (status === 'completed' ? 'var(--text)' : 'white');
                }
            });
        }

        resetButton?.addEventListener('click', () => {
            if (!window.confirm(tr(courseI18n, 'reset_course_progress_confirm', 'Reset course progress?'))) {
                return;
            }

            store.reset();
            render();
        });

        render();
        window.addEventListener(window.TheoryCourseProgress.events.updated, render);
    }

    if (window.TheoryCourseProgress) {
        boot();
    } else {
        window.addEventListener('theory-course:progress-ready', boot, { once: true });
    }
})();
</script>
@endsection
