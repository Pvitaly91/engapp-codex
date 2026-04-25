@extends('layouts.catalog-public')

@section('title', $course['name'])

@section('content')
@php
    $firstLessonCompletion = is_array(data_get($firstLesson, 'completion')) ? data_get($firstLesson, 'completion') : [];
    $completionWindow = (int) ($firstLessonCompletion['rolling_window'] ?? 100);
    $completionRating = (float) ($firstLessonCompletion['min_rating'] ?? 4.5);
    $heroUrl = data_get($firstLesson, 'compose_url');
    $courseContentComplete = $implementedLessonsCount >= $plannedTotalLessons && $plannedLessonsCount === 0;
    $continueCourseMap = [
        'polyglot-english-a1' => [
            'slug' => 'polyglot-english-a2',
            'name' => 'Polyglot English A2',
            'data_attr' => 'data-course-continue-a2-link',
        ],
        'polyglot-english-a2' => [
            'slug' => 'polyglot-english-b1',
            'name' => 'Polyglot English B1',
            'data_attr' => 'data-course-continue-b1-link',
        ],
        'polyglot-english-b1' => [
            'slug' => 'polyglot-english-b2',
            'name' => 'Polyglot English B2',
            'data_attr' => 'data-course-continue-b2-link',
        ],
        'polyglot-english-b2' => [
            'slug' => 'polyglot-english-c1',
            'name' => 'Polyglot English C1',
            'data_attr' => 'data-course-continue-c1-link',
        ],
    ];
    $continueCourse = $continueCourseMap[$course['slug'] ?? ''] ?? null;
    $continueCourseUrl = is_array($continueCourse)
        ? localized_route('courses.show', $continueCourse['slug'])
        : null;
    $continueCourseLabel = is_array($continueCourse)
        ? __('frontend.tests.course.continue_with_next_course', ['course' => $continueCourse['name']])
        : null;
    $continueCourseDataAttr = $continueCourse['data_attr'] ?? null;
    $courseManifestPayload = [
        'course' => $course,
        'lessons' => $lessons,
        'first_lesson' => $firstLesson,
        'planned_total_lessons' => $plannedTotalLessons,
    ];
    $progressSyncPayload = [
        'progressUrl' => localized_route('courses.progress.show', $course['slug'], false),
        'importUrl' => localized_route('courses.progress.import', $course['slug'], false),
        'csrfToken' => csrf_token(),
    ];
    if ($isAdmin ?? false) {
        $progressSyncPayload['debugUrl'] = localized_route('courses.progress.debug', $course['slug'], false);
    }
@endphp

<div class="nd-page"
     data-polyglot-course-root
     data-polyglot-course-slug="{{ $course['slug'] }}"
     data-polyglot-planned-lessons="{{ $plannedTotalLessons }}"
     data-polyglot-implemented-lessons="{{ $implementedLessonsCount }}"
     data-polyglot-course-content-complete="{{ $courseContentComplete ? '1' : '0' }}"
     data-polyglot-course-learner-complete="0">
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
                    <p id="course-progress-status" class="mt-1 text-xs font-semibold uppercase tracking-[0.18em]" style="color: var(--muted);">
                        {{ __('frontend.tests.course.in_progress_summary') }}
                    </p>
                </article>
                <article class="rounded-[24px] border p-5 surface-card" style="border-color: var(--line);">
                    <p class="text-[11px] font-extrabold uppercase tracking-[0.22em]" style="color: var(--accent);">{{ __('frontend.tests.course.available_lessons') }}</p>
                    <p class="mt-2 font-display text-[1.85rem] font-extrabold leading-tight">{{ $implementedLessonsCount }} / {{ $plannedTotalLessons }}</p>
                    <p class="mt-2 text-sm leading-6" style="color: var(--muted);">
                        {{ __('frontend.tests.course.implemented_out_of_total', ['implemented' => $implementedLessonsCount, 'total' => $plannedTotalLessons]) }}
                    </p>
                    <p id="course-availability-note" class="mt-1 text-xs font-semibold uppercase tracking-[0.18em]" style="color: var(--muted);">
                        {{ $courseContentComplete
                            ? __('frontend.tests.course.no_planned_lessons')
                            : __('frontend.tests.course.planned_remaining', ['count' => $plannedLessonsCount]) }}
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

    @includeWhen(($isAdmin ?? false), 'courses.partials.polyglot-admin-debug-course')

    @if($courseContentComplete)
        <section data-course-content-complete-banner class="mt-8 rounded-[28px] border p-6 shadow-card surface-card-strong" style="border-color: var(--line);">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
                <div class="max-w-3xl">
                    <p class="text-[11px] font-extrabold uppercase tracking-[0.22em]" style="color: var(--accent);">{{ __('frontend.tests.course.course_fully_available') }}</p>
                    <h2 class="mt-2 font-display text-2xl font-extrabold">{{ __('frontend.tests.course.course_fully_available_title', ['course' => $course['name'] ?? __('frontend.tests.course.course')]) }}</h2>
                    <p class="mt-3 text-sm leading-7" style="color: var(--muted);">
                        {{ __('frontend.tests.course.course_fully_available_note', ['count' => $plannedTotalLessons]) }}
                    </p>
                </div>
                <div class="flex flex-wrap gap-3">
                    @if($heroUrl)
                        <a href="{{ $heroUrl }}"
                           data-course-repeat-link
                           class="inline-flex items-center justify-center rounded-full bg-ocean px-5 py-3 text-sm font-extrabold text-white shadow-sm transition hover:opacity-95">
                            {{ __('frontend.tests.course.repeat_course') }}
                        </a>
                    @endif
                    @if($continueCourseUrl)
                        <a href="{{ $continueCourseUrl }}"
                           @if($continueCourseDataAttr) {{ $continueCourseDataAttr }} @endif
                           class="inline-flex items-center justify-center rounded-full border px-5 py-3 text-sm font-bold transition hover:opacity-95"
                           style="border-color: var(--line);">
                            {{ $continueCourseLabel }}
                        </a>
                    @endif
                    <a href="#polyglot-course-lessons"
                       class="inline-flex items-center justify-center rounded-full border px-5 py-3 text-sm font-bold transition hover:opacity-95"
                       style="border-color: var(--line);">
                        {{ __('frontend.tests.course.back_to_lessons') }}
                    </a>
                </div>
            </div>
        </section>
    @endif

    <section id="course-learner-complete-banner"
             data-course-learner-complete-banner
             class="mt-8 hidden rounded-[28px] border p-6 shadow-card"
             style="border-color: #b8e3c7; background: linear-gradient(180deg, #f0fbf4 0%, #e7f8ee 100%);">
        <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
            <div class="max-w-3xl">
                <p class="text-[11px] font-extrabold uppercase tracking-[0.22em]" style="color: #17603a;">{{ __('frontend.tests.course.course_completed') }}</p>
                <h2 class="mt-2 font-display text-2xl font-extrabold" style="color: #17603a;">{{ __('frontend.tests.course.course_completed_title') }}</h2>
                <p class="mt-3 text-sm leading-7" style="color: #17603a;">{{ __('frontend.tests.course.course_completed_note') }}</p>
            </div>
            <div class="flex flex-wrap gap-3">
                @if($heroUrl)
                    <a href="{{ $heroUrl }}"
                       data-course-repeat-link
                       class="inline-flex items-center justify-center rounded-full bg-ocean px-5 py-3 text-sm font-extrabold text-white shadow-sm transition hover:opacity-95">
                        {{ __('frontend.tests.course.repeat_course') }}
                    </a>
                @endif
                @if($continueCourseUrl)
                    <a href="{{ $continueCourseUrl }}"
                       @if($continueCourseDataAttr) {{ $continueCourseDataAttr }} @endif
                       class="inline-flex items-center justify-center rounded-full border px-5 py-3 text-sm font-bold transition hover:opacity-95"
                       style="border-color: #17603a; color: #17603a;">
                        {{ $continueCourseLabel }}
                    </a>
                @endif
                <button type="button"
                        data-course-reset-progress-secondary
                        class="inline-flex items-center justify-center rounded-full border px-5 py-3 text-sm font-bold transition hover:opacity-95"
                        style="border-color: #17603a; color: #17603a;">
                    {{ __('frontend.tests.course.restart_course') }}
                </button>
                <a href="#polyglot-course-lessons"
                   class="inline-flex items-center justify-center rounded-full border px-5 py-3 text-sm font-bold transition hover:opacity-95"
                   style="border-color: #17603a; color: #17603a;">
                    {{ __('frontend.tests.course.back_to_lessons') }}
                </a>
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
@if($isAdmin ?? false)
    <script type="module" src="{{ asset('js/polyglot/course-admin-debug.js') }}"></script>
@endif
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
window.__POLYGLOT_PROGRESS_SYNC__ = @json($progressSyncPayload);

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
        const pageRoot = document.querySelector('[data-polyglot-course-root]');

        if (!root || !course.slug || !window.PolyglotCourseProgress) {
            return;
        }

        booted = true;

        const store = window.PolyglotCourseProgress.createStore(course.slug, lessons);
        const cards = Array.from(root.querySelectorAll('[data-course-lesson-card][data-course-lesson-runtime="1"]'));
        const resetButtons = Array.from(document.querySelectorAll('[data-course-reset-progress], [data-course-reset-progress-secondary]'));
        const heroButton = document.getElementById('course-hero-cta');
        const repeatLinks = Array.from(document.querySelectorAll('[data-course-repeat-link]'));
        const progressCount = document.getElementById('course-progress-count');
        const progressStatus = document.getElementById('course-progress-status');
        const currentLesson = document.getElementById('course-current-lesson');
        const lastOpenedLesson = document.getElementById('course-last-opened-lesson');
        const learnerCompleteBanner = document.getElementById('course-learner-complete-banner');
        const progressSync = window.__POLYGLOT_PROGRESS_SYNC__ || {};
        let serverState = null;
        let serverAuthenticated = false;

        function cookieValue(name) {
            const prefix = `${name}=`;
            const item = String(document.cookie || '')
                .split(';')
                .map((cookie) => cookie.trim())
                .find((cookie) => cookie.startsWith(prefix));

            if (!item) {
                return '';
            }

            try {
                return decodeURIComponent(item.slice(prefix.length));
            } catch (error) {
                return item.slice(prefix.length);
            }
        }

        function csrfHeaders() {
            const headers = {
                'X-Requested-With': 'XMLHttpRequest',
            };
            const xsrfToken = cookieValue('XSRF-TOKEN');
            const embeddedToken = String(
                progressSync.csrfToken
                || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                || ''
            ).trim();

            if (xsrfToken !== '') {
                headers['X-XSRF-TOKEN'] = xsrfToken;
            } else if (embeddedToken !== '') {
                headers['X-CSRF-TOKEN'] = embeddedToken;
            }

            return headers;
        }

        function serverHeaders() {
            return {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                ...csrfHeaders(),
            };
        }

        function lessonBySlug(slug) {
            return lessons.find((lesson) => lesson.slug === slug) || null;
        }

        function activeCourseState() {
            return serverState || store.read();
        }

        function localProgressPayload() {
            const lessonProgress = {};

            lessons.forEach((lesson) => {
                const lessonStore = window.PolyglotCourseProgress.createLessonStore({
                    lessonSlug: lesson.slug,
                    courseSlug: course.slug,
                    courseStore: store,
                });
                const progress = lessonStore.read();

                if (progress.total_attempts > 0 || progress.rolling_results.length > 0 || progress.lesson_completed) {
                    lessonProgress[lesson.slug] = progress;
                }
            });

            return {
                course: store.read(),
                lesson_progress: lessonProgress,
            };
        }

        function localLessonAnswered(progress) {
            if (!progress || typeof progress !== 'object') {
                return 0;
            }

            const totalAttempts = Number(progress.total_attempts || 0);
            const rollingCount = Array.isArray(progress.rolling_results) ? progress.rolling_results.length : 0;

            return Math.max(Number.isFinite(totalAttempts) ? totalAttempts : 0, rollingCount);
        }

        function serverLessonAnswered(progress, slug) {
            const lessonEntry = progress?.lessons?.[slug] || null;
            const lessonProgress = progress?.lesson_progress?.[slug] || null;
            const answeredCount = Number(lessonEntry?.answered_count || 0);
            const progressTotal = Number(lessonProgress?.total_attempts || 0);
            const rollingCount = Array.isArray(lessonProgress?.rolling_results) ? lessonProgress.rolling_results.length : 0;

            return Math.max(
                Number.isFinite(answeredCount) ? answeredCount : 0,
                Number.isFinite(progressTotal) ? progressTotal : 0,
                rollingCount
            );
        }

        function hasImportableLocalProgress(localProgress, serverProgress) {
            return Object.entries(localProgress.lesson_progress || {}).some(([slug, lessonProgress]) => {
                return localLessonAnswered(lessonProgress) > serverLessonAnswered(serverProgress, slug);
            });
        }

        async function maybeImportLocalProgress(progress) {
            if (!progressSync.importUrl) {
                return null;
            }

            const localProgress = localProgressPayload();
            if (
                Object.keys(localProgress.lesson_progress).length === 0
                || !hasImportableLocalProgress(localProgress, progress)
            ) {
                return null;
            }

            const response = await fetch(progressSync.importUrl, {
                method: 'POST',
                credentials: 'same-origin',
                headers: serverHeaders(),
                body: JSON.stringify({
                    local_progress: localProgress,
                }),
            });

            if (!response.ok) {
                return null;
            }

            const payload = await response.json();

            return payload?.authenticated && payload.progress ? payload.progress : null;
        }

        async function hydrateServerProgress() {
            if (!progressSync.progressUrl) {
                return;
            }

            try {
                const response = await fetch(progressSync.progressUrl, {
                    credentials: 'same-origin',
                    headers: {
                        'Accept': 'application/json',
                    },
                });

                if (!response.ok) {
                    return;
                }

                const payload = await response.json();
                if (!payload?.authenticated || !payload.progress) {
                    serverAuthenticated = false;
                    serverState = null;
                    render();
                    return;
                }

                serverAuthenticated = true;
                serverState = await maybeImportLocalProgress(payload.progress) || payload.progress;
                render();
            } catch (error) {
                serverAuthenticated = false;
                serverState = null;
                render();
            }
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

        function updateRepeatLinks(url) {
            repeatLinks.forEach((link) => {
                if (!url) {
                    return;
                }

                link.href = url;
            });
        }

        function updateHeroCta(state, summary) {
            if (!heroButton) {
                return;
            }

            const current = lessonBySlug(state.current_lesson_slug) || lessons[0] || null;
            const firstLesson = lessons[0] || null;
            const targetLesson = summary.completed_all_lessons ? (firstLesson || current) : current;

            if (!targetLesson) {
                return;
            }

            heroButton.href = targetLesson.compose_url;
            heroButton.textContent = summary.completed_all_lessons
                ? testUi('course.repeat_course')
                : (summary.completed_lessons > 0 ? testUi('course.continue') : testUi('course.start_course'));
        }

        function renderSummary(state) {
            const summary = store.getSummary(state);
            const current = lessonBySlug(summary.current_lesson_slug);
            const lastOpened = lessonBySlug(summary.last_opened_lesson_slug);
            const firstLesson = lessons[0] || null;

            if (progressCount) {
                progressCount.textContent = `${summary.completed_lessons} / ${summary.total_lessons}`;
            }

            if (progressStatus) {
                progressStatus.textContent = summary.completed_all_lessons
                    ? testUi('course.all_lessons_completed')
                    : (serverAuthenticated
                        ? testUi('course.account_progress_summary')
                        : testUi('course.in_progress_summary'));
            }

            if (currentLesson) {
                currentLesson.textContent = summary.completed_all_lessons
                    ? testUi('course.course_completed')
                    : (current ? current.name : testUi('course.course_completed'));
            }

            if (lastOpenedLesson) {
                lastOpenedLesson.textContent = `${testUi('course.last_opened_lesson')}: ${lastOpened ? lastOpened.name : testUi('course.not_started_yet')}`;
            }

            if (learnerCompleteBanner) {
                learnerCompleteBanner.classList.toggle('hidden', !summary.completed_all_lessons);
            }

            if (pageRoot) {
                pageRoot.dataset.polyglotCourseLearnerComplete = summary.completed_all_lessons ? '1' : '0';
            }

            updateRepeatLinks(firstLesson?.compose_url || current?.compose_url || null);
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
            const state = activeCourseState();
            renderSummary(state);
            renderCards(state);

            resetButtons.forEach((button) => {
                button.disabled = serverAuthenticated;
                button.classList.toggle('opacity-50', serverAuthenticated);
                button.classList.toggle('cursor-not-allowed', serverAuthenticated);
            });
        }

        window.PolyglotCourseProgress.subscribeToSync({
            courseSlug: course.slug,
            ignoreSourceId: store.sourceId,
            onSync: (sync = {}) => {
                const detail = sync.detail || {};

                if (detail.serverCourseProgress) {
                    serverAuthenticated = true;
                    serverState = detail.serverCourseProgress;
                }

                render();
            },
        });

        resetButtons.forEach((button) => {
            button.addEventListener('click', () => {
                if (serverAuthenticated) {
                    return;
                }

                const confirmed = window.confirm(testUi('course.reset_course_progress_confirm'));
                if (!confirmed) {
                    return;
                }

                store.reset();
                render();
            });
        });

        store.sync('course-hydrate');
        render();
        hydrateServerProgress();
    }

    if (window.PolyglotCourseProgress) {
        boot();

        return;
    }

    window.addEventListener('polyglot:progress-ready', boot, { once: true });
})();
</script>
@endsection
