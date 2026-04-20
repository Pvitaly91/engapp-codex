@extends('layouts.catalog-public')

@section('title', $course['name'])

@section('content')
@php
    $firstLessonCompletion = is_array(data_get($firstLesson, 'completion')) ? data_get($firstLesson, 'completion') : [];
    $completionWindow = (int) ($firstLessonCompletion['rolling_window'] ?? 100);
    $completionRating = (float) ($firstLessonCompletion['min_rating'] ?? 4.5);
    $heroUrl = data_get($firstLesson, 'compose_url');
    $courseManifestPayload = [
        'course' => $course,
        'lessons' => $lessons,
        'first_lesson' => $firstLesson,
        'planned_total_lessons' => $plannedTotalLessons,
    ];
@endphp

<div class="nd-page"
     data-polyglot-course-root
     data-polyglot-course-slug="{{ $course['slug'] }}"
     data-polyglot-planned-lessons="{{ $plannedTotalLessons }}"
     data-polyglot-implemented-lessons="{{ $implementedLessonsCount }}">
    <nav class="mb-8 flex flex-wrap items-center gap-2 text-xs font-semibold uppercase tracking-[0.18em]" style="color: var(--muted);" aria-label="{{ __('public.common.breadcrumb') }}">
        <a href="{{ localized_route('home') }}" class="transition hover:text-ocean">{{ __('public.common.home') }}</a>
        <span>/</span>
        <a href="{{ localized_route('catalog.tests-cards') }}" class="transition hover:text-ocean">{{ __('public.nav.catalog') }}</a>
        <span>/</span>
        <span style="color: var(--text);">{{ $course['name'] }}</span>
    </nav>

    <section class="relative overflow-hidden rounded-[30px] border p-7 shadow-card surface-card-strong" style="border-color: var(--line);">
        <div class="absolute -right-10 top-0 hidden h-36 w-36 rounded-full border-[18px] border-ocean/30 lg:block"></div>
        <div class="absolute bottom-0 right-0 hidden h-44 w-14 rounded-tl-[2rem] bg-ocean lg:block"></div>
        <div class="relative grid gap-6 lg:grid-cols-[1.05fr_0.95fr] lg:items-end">
            <div class="max-w-3xl">
                <span class="inline-flex items-center rounded-full border px-4 py-2 text-xs font-extrabold uppercase tracking-[0.22em] soft-accent" style="border-color: var(--line); color: var(--accent);">
                    {{ __('frontend.tests.course.course') }}
                </span>
                <h1 class="mt-4 font-display text-3xl font-extrabold leading-[1.04] sm:text-4xl">{{ $course['name'] }}</h1>
                <p class="mt-4 max-w-2xl text-sm leading-7 sm:text-base" style="color: var(--muted);">
                    {{ __('frontend.tests.course.hero_description', ['count' => $completionWindow, 'rating' => number_format($completionRating, 1)]) }}
                </p>
                @if($heroUrl)
                    <div class="mt-6 flex flex-wrap gap-3">
                        <a id="course-hero-cta"
                           href="{{ $heroUrl }}"
                           class="inline-flex items-center justify-center rounded-full bg-ocean px-6 py-3 text-sm font-extrabold text-white shadow-sm transition hover:opacity-95">
                            {{ __('frontend.tests.course.start_course') }}
                        </a>
                    </div>
                @endif
            </div>

            <div class="grid gap-4 sm:grid-cols-3">
                <article class="rounded-[24px] border p-5 surface-card" style="border-color: var(--line);">
                    <p class="text-[11px] font-extrabold uppercase tracking-[0.22em]" style="color: var(--accent);">{{ __('frontend.tests.course.course_progress') }}</p>
                    <p id="course-progress-count" class="mt-2 font-display text-[2.25rem] font-extrabold leading-none">0 / {{ $implementedLessonsCount }}</p>
                    <p class="mt-2 text-sm leading-6" style="color: var(--muted);">{{ __('frontend.tests.course.lessons_completed') }}</p>
                </article>
                <article class="rounded-[24px] border p-5 surface-card" style="border-color: var(--line);">
                    <p class="text-[11px] font-extrabold uppercase tracking-[0.22em]" style="color: var(--accent);">{{ __('frontend.tests.course.available_lessons') }}</p>
                    <p class="mt-2 font-display text-[1.85rem] font-extrabold leading-tight">{{ $implementedLessonsCount }} / {{ $plannedTotalLessons }}</p>
                    <p class="mt-2 text-sm leading-6" style="color: var(--muted);">
                        {{ __('frontend.tests.course.implemented_out_of_total', ['implemented' => $implementedLessonsCount, 'total' => $plannedTotalLessons]) }}
                    </p>
                    <p class="mt-1 text-xs font-semibold uppercase tracking-[0.18em]" style="color: var(--muted);">
                        {{ __('frontend.tests.course.planned_remaining', ['count' => $plannedLessonsCount]) }}
                    </p>
                </article>
                <article class="rounded-[24px] border p-5 surface-card" style="border-color: var(--line);">
                    <p class="text-[11px] font-extrabold uppercase tracking-[0.22em]" style="color: var(--accent);">{{ __('frontend.tests.course.current_lesson') }}</p>
                    <p id="course-current-lesson" class="mt-2 font-display text-[1.55rem] font-extrabold leading-tight">{{ data_get($firstLesson, 'name') }}</p>
                    <p id="course-last-opened-lesson" class="mt-2 text-sm leading-6" style="color: var(--muted);">{{ __('frontend.tests.course.last_opened_lesson') }}: {{ __('frontend.tests.course.not_started_yet') }}</p>
                </article>
            </div>
        </div>
    </section>

    <section class="mt-8 rounded-[28px] border p-6 shadow-card surface-card-strong" style="border-color: var(--line);">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-[11px] font-extrabold uppercase tracking-[0.22em]" style="color: var(--accent);">{{ __('frontend.tests.course.course') }}</p>
                <h2 class="mt-2 font-display text-2xl font-extrabold">{{ __('frontend.tests.course.lesson_list_title', ['count' => $plannedTotalLessons]) }}</h2>
                <p class="mt-2 text-sm leading-6" style="color: var(--muted);">
                    {{ __('frontend.tests.course.implemented_out_of_total', ['implemented' => $implementedLessonsCount, 'total' => $plannedTotalLessons]) }}
                </p>
            </div>
            <button type="button"
                    data-course-reset-progress
                    class="inline-flex items-center justify-center rounded-full border px-5 py-3 text-sm font-bold transition hover:opacity-95"
                    style="border-color: var(--line);">
                {{ __('frontend.tests.course.reset_course_progress') }}
            </button>
        </div>

        <div id="polyglot-course-lessons"
             data-polyglot-course-slug="{{ $course['slug'] }}"
             data-polyglot-total-lessons="{{ $implementedLessonsCount }}"
             class="mt-6 grid gap-4">
            @foreach($displayLessons as $lesson)
                @php
                    $isImplemented = (bool) ($lesson['is_implemented'] ?? false);
                    $serverStatus = $isImplemented
                        ? (($lesson['slug'] ?? null) === data_get($firstLesson, 'slug') ? 'current' : 'locked')
                        : 'planned';
                    $isLocked = $serverStatus === 'locked' || ! $isImplemented;
                @endphp
                <article data-course-lesson-card
                         data-course-lesson-runtime="{{ $isImplemented ? '1' : '0' }}"
                         data-lesson-slug="{{ $lesson['slug'] }}"
                         data-lesson-order="{{ $lesson['lesson_order'] }}"
                         data-course-lesson-status="{{ $serverStatus }}"
                         class="rounded-[24px] border p-5 transition"
                         style="border-color: var(--line); background: {{ $isLocked ? 'color-mix(in srgb, var(--surface) 84%, transparent)' : 'var(--surface)' }};">
                    <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
                        <div class="max-w-3xl">
                            <div class="flex flex-wrap items-center gap-2 text-xs font-semibold uppercase tracking-[0.18em]" style="color: var(--muted);">
                                <span>{{ __('frontend.tests.course.lesson_number', ['number' => $lesson['lesson_order']]) }}</span>
                                @if(!empty($lesson['topic']))
                                    <span>&bull;</span>
                                    <span>{{ $lesson['topic'] }}</span>
                                @endif
                                @if(!empty($lesson['level']))
                                    <span>&bull;</span>
                                    <span>{{ $lesson['level'] }}</span>
                                @endif
                            </div>
                            <div class="mt-3 flex flex-wrap items-center gap-3">
                                <h3 class="font-display text-2xl font-extrabold">{{ $lesson['name'] }}</h3>
                                <span data-course-status-badge
                                      class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-extrabold uppercase tracking-[0.18em]"
                                      style="border-color: var(--line); color: var(--accent);">
                                    {{ __('frontend.tests.course.' . $serverStatus) }}
                                </span>
                            </div>
                            <p class="mt-3 text-sm leading-7" style="color: var(--muted);">
                                {{ $lesson['description'] ?: __('frontend.tests.course.lesson_topic_fallback', ['topic' => $lesson['topic'] ?? __('frontend.tests.course.course')]) }}
                            </p>
                        </div>

                        <div class="min-w-[15rem] space-y-3">
                            @if($isImplemented)
                                <a href="{{ $lesson['compose_url'] }}"
                                   data-course-lesson-action
                                   class="inline-flex w-full items-center justify-center rounded-full border px-5 py-3 text-sm font-extrabold transition hover:opacity-95"
                                   style="border-color: {{ $isLocked ? 'var(--line)' : 'var(--accent)' }}; {{ $isLocked ? 'color: var(--muted); pointer-events: none;' : 'background: var(--accent); color: white;' }}"
                                   @if($isLocked) aria-disabled="true" @endif>
                                    {{ $isLocked ? __('frontend.tests.course.locked') : __('frontend.tests.course.start') }}
                                </a>
                            @else
                                <button type="button"
                                        data-course-lesson-action-disabled
                                        class="inline-flex w-full items-center justify-center rounded-full border px-5 py-3 text-sm font-extrabold"
                                        style="border-color: var(--line); color: var(--muted);"
                                        disabled>
                                    {{ __('frontend.tests.course.coming_soon') }}
                                </button>
                            @endif
                            <p data-course-lesson-note class="text-sm leading-6" style="color: var(--muted);">
                                @if($isImplemented)
                                    {{ $isLocked ? __('frontend.tests.course.complete_previous_lesson_first') : __('frontend.tests.course.available') }}
                                @else
                                    {{ __('frontend.tests.course.planned_lesson_note') }}
                                @endif
                            </p>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    </section>
</div>
@endsection

@section('scripts')
<script type="module" src="{{ asset('js/polyglot-course-progress.js') }}"></script>
<script>
window.FRONTEND_TESTS_I18N = @json(__('frontend.tests'));

function getTestUiValue(path, fallback = '') {
    const source = window.FRONTEND_TESTS_I18N || {};
    const value = String(path || '')
        .split('.')
        .reduce((acc, segment) => {
            if (acc && typeof acc === 'object' && Object.prototype.hasOwnProperty.call(acc, segment)) {
                return acc[segment];
            }

            return undefined;
        }, source);

    return value === undefined ? fallback : value;
}

function interpolateTestUi(template, replacements = {}) {
    return String(template ?? '').replace(/:([a-zA-Z0-9_]+)/g, (match, key) => {
        if (Object.prototype.hasOwnProperty.call(replacements, key)) {
            return replacements[key];
        }

        return match;
    });
}

function testUi(path, replacements = {}, fallback = '') {
    return interpolateTestUi(getTestUiValue(path, fallback), replacements);
}

window.__POLYGLOT_COURSE_MANIFEST__ = @json($courseManifestPayload);

(function () {
    let booted = false;

    function boot() {
        if (booted) {
            return;
        }

        const manifest = window.__POLYGLOT_COURSE_MANIFEST__ || {};
        const course = manifest.course || {};
        const lessons = Array.isArray(manifest.lessons) ? manifest.lessons : [];
        const root = document.getElementById('polyglot-course-lessons');

        if (!root || !course.slug || !window.PolyglotCourseProgress) {
            return;
        }

        booted = true;

        const store = window.PolyglotCourseProgress.createStore(course.slug, lessons);
        const cards = Array.from(root.querySelectorAll('[data-course-lesson-card][data-course-lesson-runtime="1"]'));
        const resetButton = document.querySelector('[data-course-reset-progress]');
        const heroButton = document.getElementById('course-hero-cta');
        const progressCount = document.getElementById('course-progress-count');
        const currentLesson = document.getElementById('course-current-lesson');
        const lastOpenedLesson = document.getElementById('course-last-opened-lesson');

        function lessonBySlug(slug) {
            return lessons.find((lesson) => lesson.slug === slug) || null;
        }

        function actionLabel(status, entry) {
            if (status === 'completed') {
                return testUi('course.repeat');
            }

            if (status === 'locked') {
                return testUi('course.locked');
            }

            return entry?.has_progress ? testUi('course.continue') : testUi('course.start');
        }

        function noteLabel(status) {
            if (status === 'completed') {
                return testUi('course.completed');
            }

            if (status === 'locked') {
                return testUi('course.complete_previous_lesson_first');
            }

            if (status === 'current') {
                return testUi('course.current_lesson');
            }

            return testUi('course.available');
        }

        function badgeLabel(status) {
            if (status === 'completed') {
                return testUi('course.completed');
            }

            if (status === 'locked') {
                return testUi('course.locked');
            }

            if (status === 'current') {
                return testUi('course.current');
            }

            return testUi('course.available');
        }

        function updateHeroCta(state, summary) {
            if (!heroButton) {
                return;
            }

            const current = lessonBySlug(state.current_lesson_slug) || lessons[0] || null;
            if (!current) {
                return;
            }

            heroButton.href = current.compose_url;
            heroButton.textContent = summary.completed_all_lessons
                ? testUi('course.repeat')
                : (summary.completed_lessons > 0 ? testUi('course.continue') : testUi('course.start_course'));
        }

        function renderSummary(state) {
            const summary = store.getSummary(state);
            const current = lessonBySlug(summary.current_lesson_slug);
            const lastOpened = lessonBySlug(summary.last_opened_lesson_slug);

            if (progressCount) {
                progressCount.textContent = `${summary.completed_lessons} / ${summary.total_lessons}`;
            }

            if (currentLesson) {
                currentLesson.textContent = current
                    ? current.name
                    : testUi('course.course_completed');
            }

            if (lastOpenedLesson) {
                lastOpenedLesson.textContent = `${testUi('course.last_opened_lesson')}: ${lastOpened ? lastOpened.name : testUi('course.not_started_yet')}`;
            }

            updateHeroCta(state, summary);
        }

        function renderCards(state) {
            cards.forEach((card) => {
                const slug = String(card.dataset.lessonSlug || '').trim();
                const lesson = lessonBySlug(slug);
                const entry = state.lessons?.[slug] || {};
                const status = store.getLessonStatus(slug, state);
                const badge = card.querySelector('[data-course-status-badge]');
                const action = card.querySelector('[data-course-lesson-action]');
                const note = card.querySelector('[data-course-lesson-note]');
                const locked = status === 'locked';

                card.dataset.courseLessonStatus = status;
                card.style.background = locked
                    ? 'color-mix(in srgb, var(--surface) 84%, transparent)'
                    : 'var(--surface)';
                card.style.opacity = locked ? '0.72' : '1';

                if (badge) {
                    badge.textContent = badgeLabel(status);
                }

                if (action && lesson) {
                    action.textContent = actionLabel(status, entry);
                    action.href = lesson.compose_url;
                    action.setAttribute('aria-disabled', locked ? 'true' : 'false');
                    action.style.borderColor = locked ? 'var(--line)' : 'var(--accent)';
                    action.style.background = status === 'completed' || status === 'locked'
                        ? 'transparent'
                        : 'var(--accent)';
                    action.style.color = status === 'completed'
                        ? 'var(--text)'
                        : (locked ? 'var(--muted)' : 'white');
                    action.style.pointerEvents = locked ? 'none' : 'auto';
                }

                if (note) {
                    note.textContent = noteLabel(status);
                }
            });
        }

        function render() {
            const state = store.read();
            renderSummary(state);
            renderCards(state);
        }

        window.PolyglotCourseProgress.subscribeToSync({
            courseSlug: course.slug,
            ignoreSourceId: store.sourceId,
            onSync: render,
        });

        resetButton?.addEventListener('click', () => {
            const confirmed = window.confirm(testUi('course.reset_course_progress_confirm'));
            if (!confirmed) {
                return;
            }

            store.reset();
            render();
        });

        store.sync('course-hydrate');
        render();
    }

    if (window.PolyglotCourseProgress) {
        boot();

        return;
    }

    window.addEventListener('polyglot:progress-ready', boot, { once: true });
})();
</script>
@endsection
