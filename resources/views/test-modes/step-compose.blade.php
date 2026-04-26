@extends('layouts.engram')

@section('title', $test->name)

@section('content')
@php
    $courseContext = is_array($courseContext ?? null) ? $courseContext : [];
    $rawFilters = $test->filters ?? [];
    if (is_string($rawFilters)) {
        $decodedFilters = json_decode($rawFilters, true);
        $rawFilters = is_array($decodedFilters) ? $decodedFilters : [];
    }
    $rawFilters = is_array($rawFilters) ? $rawFilters : [];
    $completionWindow = (int) data_get($rawFilters, 'completion.rolling_window', 100);
    $completionRating = (float) data_get($rawFilters, 'completion.min_rating', 4.5);
    $courseSlug = data_get($courseContext, 'course_slug', data_get($rawFilters, 'course_slug'));
    $courseUrl = data_get($courseContext, 'course_url', filled($courseSlug) ? localized_route('courses.show', $courseSlug) : null);
    $previousLessonSlug = data_get($courseContext, 'previous_lesson_slug', data_get($rawFilters, 'previous_lesson_slug'));
    $nextLessonSlug = data_get($courseContext, 'next_lesson_slug', data_get($rawFilters, 'next_lesson_slug'));
    $firstLessonSlug = data_get($courseContext, 'first_lesson_slug');
    $firstLessonUrl = data_get($courseContext, 'first_lesson_url');
    $isFinalLesson = ! filled($nextLessonSlug);
    $startsLockedPending = filled($courseSlug) && filled($previousLessonSlug);
    $continueCourseMap = [
        'polyglot-english-a1' => [
            'slug' => 'polyglot-english-a2',
            'name' => 'Polyglot English A2',
        ],
        'polyglot-english-a2' => [
            'slug' => 'polyglot-english-b1',
            'name' => 'Polyglot English B1',
        ],
        'polyglot-english-b1' => [
            'slug' => 'polyglot-english-b2',
            'name' => 'Polyglot English B2',
        ],
        'polyglot-english-b2' => [
            'slug' => 'polyglot-english-c1',
            'name' => 'Polyglot English C1',
        ],
    ];
    $continueCourse = $isFinalLesson ? ($continueCourseMap[$courseSlug ?? ''] ?? null) : null;
    $continueCourseUrl = is_array($continueCourse)
        ? localized_route('courses.show', $continueCourse['slug'])
        : null;
    $continueCourseLabel = is_array($continueCourse)
        ? __('frontend.tests.course.continue_with_next_course', ['course' => $continueCourse['name']])
        : null;
@endphp

<style>
    #new-design-test-shell #polyglot-compose-root,
    #polyglot-compose-root {
        --polyglot-chip-min-height: 1.45rem;
        --polyglot-chip-padding: 0.16rem 0.9rem;
        --polyglot-chip-radius: 10px;
        --polyglot-chip-font-size: 1.38rem;
    }

    @media (max-width: 639px) {
        #new-design-test-shell #polyglot-compose-root,
        #polyglot-compose-root {
            margin-left: -0.75rem !important;
            margin-right: -0.75rem !important;
            width: calc(100% + 1.5rem) !important;
            max-width: calc(100% + 1.5rem) !important;
            --polyglot-chip-min-height: 2rem;
            --polyglot-chip-padding: 0.28rem 0.65rem;
            --polyglot-chip-radius: 0.65rem;
            --polyglot-chip-font-size: 1rem;
        }
    }
</style>

<div class="-mx-3 w-auto max-w-5xl sm:mx-auto sm:w-full"
     id="polyglot-compose-root"
     data-polyglot-lesson-root
     data-polyglot-lesson-slug="{{ $test->slug }}"
     data-polyglot-course-slug="{{ $courseSlug }}"
     data-polyglot-previous-lesson-slug="{{ $previousLessonSlug }}"
     data-polyglot-next-lesson-slug="{{ $nextLessonSlug }}"
     data-polyglot-first-lesson-url="{{ $firstLessonUrl }}"
     data-polyglot-is-final-lesson="{{ $isFinalLesson ? '1' : '0' }}"
     data-polyglot-lock-state="{{ $startsLockedPending ? 'pending' : 'ready' }}">
    @if(filled($courseSlug))
        <section class="mb-4 rounded-[20px] p-3 shadow-none surface-card-strong sm:mb-6 sm:rounded-[28px] sm:border sm:p-5 sm:shadow-card" style="border-color: var(--line);">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="space-y-3">
                    <a href="{{ $courseUrl }}"
                       class="inline-flex items-center gap-2 rounded-full border px-4 py-2 text-xs font-extrabold uppercase tracking-[0.18em] transition hover:opacity-90"
                       style="border-color: var(--line); color: var(--accent);">
                        <span aria-hidden="true">&larr;</span>
                        <span>{{ __('frontend.tests.course.back_to_course') }}</span>
                    </a>
                    <div class="flex flex-wrap items-center gap-2 text-xs font-semibold uppercase tracking-[0.18em]" style="color: var(--muted);">
                        <span>{{ __('frontend.tests.course.course') }}: {{ data_get($courseContext, 'course_name') }}</span>
                        @if(filled(data_get($courseContext, 'lesson_order')))
                            <span>&bull;</span>
                            <span>{{ __('frontend.tests.course.lesson_number', ['number' => data_get($courseContext, 'lesson_order')]) }}</span>
                        @endif
                        @if(filled(data_get($courseContext, 'total_lessons')))
                            <span>/ {{ data_get($courseContext, 'total_lessons') }}</span>
                        @endif
                        @if(filled(data_get($courseContext, 'topic')))
                            <span>&bull;</span>
                            <span>{{ data_get($courseContext, 'topic') }}</span>
                        @endif
                    </div>
                </div>

                <div class="flex flex-wrap gap-2">
                    @if(filled(data_get($courseContext, 'previous_lesson_url')))
                        <a href="{{ data_get($courseContext, 'previous_lesson_url') }}"
                           class="inline-flex items-center justify-center rounded-full border px-4 py-2 text-sm font-semibold transition hover:opacity-95"
                           style="border-color: var(--line);">
                            {{ __('frontend.tests.course.previous_lesson') }}
                        </a>
                    @endif
                </div>
            </div>
        </section>
    @endif

    <div id="polyglot-compose-app" class="space-y-4 sm:space-y-6">
        <section class="rounded-[20px] p-3 shadow-none surface-card-strong sm:rounded-[28px] sm:border sm:p-8 sm:shadow-card" style="border-color: var(--line);">
            <div class="grid gap-4 xl:grid-cols-[1.05fr_0.95fr] xl:items-start">
                <div class="space-y-3">
                    <span class="inline-flex items-center rounded-full border px-3 py-1.5 text-[10px] font-extrabold uppercase tracking-[0.16em] soft-accent sm:px-4 sm:py-2 sm:text-[11px] sm:tracking-[0.22em]" style="border-color: var(--line); color: var(--accent);">
                        {{ __('frontend.tests.templates.compose.badge') }}
                    </span>
                    <div>
                        <h2 class="font-display text-xl font-extrabold leading-tight sm:text-3xl" id="compose-test-name">{{ $test->name }}</h2>
                        <p class="mt-1.5 text-xs leading-5 sm:mt-2 sm:text-sm sm:leading-6" id="compose-question-index" style="color: var(--muted);">
                            {{ __('frontend.tests.compose.current_sentence', ['current' => 1, 'total' => max(count($questionData ?? []), 1)]) }}
                        </p>
                    </div>
                </div>

                <div class="grid gap-3 sm:grid-cols-2">
                    <article class="rounded-[18px] p-3 surface-card sm:rounded-[24px] sm:border sm:p-4" style="border-color: var(--line);">
                        <p class="text-[10px] font-extrabold uppercase tracking-[0.16em] sm:text-[11px] sm:tracking-[0.22em]" id="compose-rating-title" style="color: var(--accent);">{{ __('frontend.tests.compose.last_100_answers') }}</p>
                        <p class="mt-1.5 font-display text-[1.45rem] font-extrabold leading-none sm:mt-2 sm:text-[2rem]" id="compose-rating">0.0 / 5</p>
                        <p class="mt-1.5 text-xs leading-5 sm:mt-2" id="compose-rating-meta" style="color: var(--muted);">0 / {{ $completionWindow }}</p>
                    </article>
                    <article class="rounded-[18px] p-3 surface-card sm:rounded-[24px] sm:border sm:p-4" style="border-color: var(--line);">
                        <p class="text-[10px] font-extrabold uppercase tracking-[0.16em] sm:text-[11px] sm:tracking-[0.22em]" style="color: var(--accent);">{{ __('frontend.tests.compose.lesson_status') }}</p>
                        <p class="mt-1.5 font-display text-[1.2rem] font-extrabold leading-tight sm:mt-2 sm:text-[1.55rem]" id="compose-status">{{ __('frontend.tests.compose.in_progress') }}</p>
                        <p class="mt-1.5 text-xs leading-5 sm:mt-2" id="compose-status-note" style="color: var(--muted);">
                            {{ __('frontend.tests.compose.goal_note', ['rating' => number_format($completionRating, 1), 'count' => $completionWindow]) }}
                        </p>
                    </article>
                </div>
            </div>
        </section>

        <article class="rounded-[20px] p-3 shadow-none surface-card-strong sm:rounded-[28px] sm:border sm:p-8 sm:shadow-card" style="border-color: var(--line);">
            <div id="compose-empty-state" class="{{ count($questionData ?? []) > 0 ? 'hidden' : '' }}">
                <div class="rounded-[18px] border border-dashed px-4 py-7 text-center sm:rounded-[24px] sm:px-6 sm:py-10" style="border-color: var(--line);">
                    <p class="text-base font-semibold sm:text-lg">{{ __('frontend.tests.compose.empty') }}</p>
                </div>
            </div>

            <div id="compose-course-pending" class="{{ count($questionData ?? []) > 0 && $startsLockedPending ? '' : 'hidden' }}">
                <div class="rounded-[18px] border border-dashed px-4 py-7 text-center sm:rounded-[24px] sm:px-6 sm:py-10" style="border-color: var(--line);">
                    <p class="text-base font-semibold sm:text-lg">{{ __('frontend.tests.course.checking_access') }}</p>
                </div>
            </div>

            <div id="compose-course-lock" class="hidden"></div>

            <div id="compose-workspace" class="{{ count($questionData ?? []) > 0 && ! $startsLockedPending ? '' : 'hidden' }}">
                <div id="compose-feedback" class="hidden mb-3 sm:mb-5" aria-live="polite"></div>

                <div class="mb-3 rounded-[18px] p-3 surface-card sm:mb-6 sm:rounded-[24px] sm:border sm:p-5" style="border-color: var(--line);">
                    <p class="text-[10px] font-extrabold uppercase tracking-[0.16em] sm:text-[11px] sm:tracking-[0.22em]" style="color: var(--accent);">{{ __('frontend.tests.compose.source_sentence') }}</p>
                    <div id="compose-source-text" class="mt-2 text-[1.55rem] font-extrabold leading-[1.25] sm:mt-4 sm:text-[2.55rem]"></div>
                </div>

                <div class="grid gap-3 sm:gap-5 xl:grid-cols-[1.08fr_0.92fr]">
                    <section class="order-1 rounded-[18px] p-3 surface-card sm:rounded-[24px] sm:border sm:p-5 xl:order-1" style="border-color: var(--line);">
                        <div class="flex flex-wrap items-center justify-between gap-2 sm:gap-3">
                            <p class="text-[10px] font-extrabold uppercase tracking-[0.16em] sm:text-[11px] sm:tracking-[0.22em]" style="color: var(--accent);">{{ __('frontend.tests.compose.build_translation') }}</p>
                            <span class="rounded-[10px] border px-2 py-1.5 text-xs font-semibold leading-none sm:rounded-[12px] sm:px-3 sm:py-2 sm:text-sm" id="compose-punctuation" style="border-color: var(--line); color: var(--muted);"></span>
                        </div>
                        <div id="compose-answer-zone" class="mt-3 min-h-[5rem] rounded-[16px] border border-dashed p-2.5 sm:mt-4 sm:min-h-[8rem] sm:rounded-[22px] sm:p-5" style="border-color: var(--line); background: color-mix(in srgb, var(--surface) 88%, white);"></div>
                    </section>

                    <section class="order-2 rounded-[18px] p-3 surface-card sm:rounded-[24px] sm:border sm:p-5 xl:order-2" aria-label="{{ __('frontend.tests.compose.token_bank') }}" style="border-color: var(--line);">
                        <p class="hidden text-[10px] font-extrabold uppercase tracking-[0.16em] sm:block sm:text-[11px] sm:tracking-[0.22em]" style="color: var(--accent);">{{ __('frontend.tests.compose.token_bank') }}</p>
                        <div id="compose-bank" class="flex min-h-[4rem] flex-wrap content-start items-start gap-2 sm:mt-4 sm:min-h-[8rem] sm:gap-3"></div>
                    </section>

                    <div id="compose-controls" class="order-3 rounded-[18px] px-3 pb-3 sm:rounded-[24px] sm:px-5 sm:pb-5 xl:col-start-1 xl:row-start-2">
                        <div class="flex flex-wrap gap-2.5 sm:gap-3">
                            <button type="button" data-action="check" class="inline-flex items-center justify-center rounded-full bg-ocean px-4 py-2.5 text-[13px] font-extrabold text-white shadow-sm transition hover:opacity-95 sm:px-5 sm:py-3 sm:text-sm">
                                {{ __('frontend.tests.compose.check') }}
                            </button>
                            <button type="button" data-action="clear" class="inline-flex items-center justify-center rounded-full border px-4 py-2.5 text-[13px] font-bold transition sm:px-5 sm:py-3 sm:text-sm" style="border-color: var(--line);">
                                {{ __('frontend.tests.compose.clear') }}
                            </button>
                            <button type="button" data-action="undo" class="inline-flex items-center justify-center rounded-full border px-4 py-2.5 text-[13px] font-bold transition sm:px-5 sm:py-3 sm:text-sm" style="border-color: var(--line);">
                                {{ __('frontend.tests.compose.remove_last') }}
                            </button>
                            <button type="button" data-action="reset-progress" class="inline-flex items-center justify-center rounded-full border px-4 py-2.5 text-[13px] font-bold transition sm:px-5 sm:py-3 sm:text-sm" style="border-color: var(--line);">
                                {{ __('frontend.tests.compose.reset_progress') }}
                            </button>
                        </div>
                        <p class="mt-3 text-[11px] leading-5 sm:mt-4 sm:text-xs" style="color: var(--muted);">{{ __('frontend.tests.compose.keyboard_hint') }}</p>
                    </div>
                </div>
            </div>

            <div id="compose-course-completion"
                 data-polyglot-course-completion-kind="{{ $isFinalLesson ? 'course' : 'lesson' }}"
                 class="mt-5 hidden"></div>
        </article>
    </div>
</div>

@includeWhen(($isAdmin ?? false) && is_array($polyglotAdminDebugPayload ?? null), 'test-modes.partials.polyglot-admin-debug', [
    'debugPayload' => $polyglotAdminDebugPayload ?? null,
])

<script type="module" src="{{ asset('js/polyglot-course-progress.js') }}"></script>
<script>
@php
    $composeConfig = [
        'slug' => $test->slug,
        'rollingWindow' => $completionWindow,
        'minRating' => $completionRating,
        'courseSlug' => $courseSlug,
        'courseUrl' => $courseUrl,
        'courseName' => data_get($courseContext, 'course_name'),
        'lessonOrder' => data_get($courseContext, 'lesson_order'),
        'totalLessons' => data_get($courseContext, 'total_lessons'),
        'topic' => data_get($courseContext, 'topic'),
        'firstLessonSlug' => $firstLessonSlug,
        'firstLessonUrl' => $firstLessonUrl,
        'previousLessonSlug' => $previousLessonSlug,
        'previousLessonUrl' => data_get($courseContext, 'previous_lesson_url'),
        'nextLessonSlug' => $nextLessonSlug,
        'nextLessonUrl' => data_get($courseContext, 'next_lesson_url', filled($nextLessonSlug) ? localized_route('test.step-compose', $nextLessonSlug) : null),
        'isFinalLesson' => $isFinalLesson,
        'continueCourseUrl' => $continueCourseUrl,
        'continueCourseLabel' => $continueCourseLabel,
        'interfaceLocale' => data_get($rawFilters, 'interface_locale', app()->getLocale()),
        'courseLessons' => data_get($courseContext, 'lessons', []),
    ];
    $progressSyncPayload = [
        'progressUrl' => filled($courseSlug) ? localized_route('courses.progress.show', $courseSlug, false) : null,
        'attemptUrl' => filled($courseSlug) ? localized_route('courses.progress.attempt', $courseSlug, false) : null,
        'importUrl' => filled($courseSlug) ? localized_route('courses.progress.import', $courseSlug, false) : null,
        'csrfToken' => csrf_token(),
    ];
    if (($isAdmin ?? false) && filled($courseSlug)) {
        $progressSyncPayload['debugUrl'] = localized_route('courses.progress.debug', $courseSlug, false);
    }
@endphp
window.__INITIAL_JS_TEST_QUESTIONS__ = @json($questionData);
window.__POLYGLOT_COMPOSE_CONFIG__ = @json($composeConfig);
window.__POLYGLOT_PROGRESS_SYNC__ = @json($progressSyncPayload);
</script>
@include('components.saved-test-js-helpers')
<script>
(function () {
    let booted = false;

    function boot() {
        if (booted) {
            return;
        }

        if (!window.PolyglotCourseProgress) {
            return;
        }

        booted = true;

    const questions = Array.isArray(window.__INITIAL_JS_TEST_QUESTIONS__)
        ? window.__INITIAL_JS_TEST_QUESTIONS__
        : [];
    const config = window.__POLYGLOT_COMPOSE_CONFIG__ || {};
    const rollingWindow = Number.isFinite(Number(config.rollingWindow)) ? Number(config.rollingWindow) : 100;
    const minRating = Number.isFinite(Number(config.minRating)) ? Number(config.minRating) : 4.5;
    const progressSync = window.__POLYGLOT_PROGRESS_SYNC__ || {};
    const adminDebugQuestionStatsKey = `polyglot_debug_question_stats:${config.courseSlug || 'course'}:${config.slug || 'lesson'}`;
    let serverCourseState = null;
    let serverAuthenticated = false;

    const pageRoot = document.getElementById('polyglot-compose-root');
    const root = document.getElementById('polyglot-compose-app');
    const workspace = document.getElementById('compose-workspace');
    const emptyState = document.getElementById('compose-empty-state');
    const pendingState = document.getElementById('compose-course-pending');
    const lockState = document.getElementById('compose-course-lock');
    const completionState = document.getElementById('compose-course-completion');

    if (!root) {
        return;
    }

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

    function generateClientAttemptUuid() {
        if (window.crypto && typeof window.crypto.randomUUID === 'function') {
            return window.crypto.randomUUID();
        }

        return `attempt-${Date.now().toString(36)}-${Math.random().toString(36).slice(2)}`;
    }

    function normalizeText(value) {
        return String(value ?? '')
            .replace(/\s+/g, ' ')
            .replace(/\s+([?.!,;:])/g, '$1')
            .trim();
    }

    function normalizeCompare(value) {
        return normalizeText(value).toLowerCase();
    }

    function sanitizeInteger(value, fallback = 0) {
        const normalized = Number.parseInt(String(value ?? ''), 10);

        return Number.isInteger(normalized) ? normalized : fallback;
    }

    function sanitizeCount(value) {
        return Math.max(0, sanitizeInteger(value, 0));
    }

    function sanitizeRollingResults(values) {
        if (!Array.isArray(values)) {
            return [];
        }

        return values
            .map((value) => Number(value))
            .filter((value) => value === 0 || value === 5)
            .slice(-rollingWindow);
    }

    function readJsonStorage(key) {
        if (!key) {
            return null;
        }

        try {
            const raw = window.localStorage.getItem(key);

            return raw ? JSON.parse(raw) : null;
        } catch (error) {
            return null;
        }
    }

    function normalizeDebugPolicy(policy) {
        if (!policy || typeof policy !== 'object' || policy.enabled === false) {
            return null;
        }

        const rawRequiredAnswered = sanitizeCount(policy.required_answered ?? policy.requiredAnswered);
        const hasAnsweredPercent = policy.required_answered_percent !== undefined || policy.requiredAnsweredPercent !== undefined;
        const requiredAnsweredPercent = Math.min(
            100,
            Math.max(0, sanitizeInteger(
                policy.required_answered_percent ?? policy.requiredAnsweredPercent,
                hasAnsweredPercent ? 0 : Math.round(rawRequiredAnswered / Math.max(1, normalizedQuestions.length) * 100)
            ))
        );
        const requiredAnswered = requiredAnsweredPercent > 0
            ? Math.ceil(normalizedQuestions.length * requiredAnsweredPercent / 100)
            : rawRequiredAnswered;
        const rawRequiredCorrect = sanitizeCount(policy.required_correct ?? policy.requiredCorrect);
        const hasCorrectPercent = policy.required_correct_percent !== undefined || policy.requiredCorrectPercent !== undefined;
        const requiredCorrectPercent = Math.min(
            100,
            Math.max(0, sanitizeInteger(
                policy.required_correct_percent ?? policy.requiredCorrectPercent,
                hasCorrectPercent ? 0 : (requiredAnswered > 0 ? Math.round(rawRequiredCorrect / requiredAnswered * 100) : 0)
            ))
        );
        const requiredCorrect = requiredCorrectPercent > 0
            ? Math.ceil(requiredAnswered * requiredCorrectPercent / 100)
            : rawRequiredCorrect;
        const minimumRatingPercent = Math.min(
            100,
            Math.max(0, sanitizeInteger(policy.minimum_rating_percent ?? policy.minimumRatingPercent, Math.round(minRating * 20)))
        );

        return {
            required_answered: requiredAnswered,
            required_correct: requiredCorrect,
            required_answered_percent: requiredAnsweredPercent,
            required_correct_percent: requiredCorrectPercent,
            minimum_rating_percent: minimumRatingPercent,
            force_unlock_next: Boolean(policy.force_unlock_next ?? policy.forceUnlockNext),
        };
    }

    function activeDebugPolicy() {
        const adminDebugEnabled = Boolean(progressSync.debugUrl || document.querySelector('[data-polyglot-admin-debug="1"]'));
        if (!adminDebugEnabled) {
            return null;
        }

        const courseSlug = String(config.courseSlug || '').trim();
        const lessonSlug = String(config.slug || '').trim();

        if (!courseSlug || !lessonSlug) {
            return null;
        }

        const lessonPolicy = normalizeDebugPolicy(
            readJsonStorage(`polyglot_debug_unlock_policy:${courseSlug}:${lessonSlug}`)
        );

        if (lessonPolicy) {
            return lessonPolicy;
        }

        return normalizeDebugPolicy(
            readJsonStorage(`polyglot_debug_unlock_policy:${courseSlug}:__course__`)
        );
    }

    function statusGoalNote() {
        if (state.progress.lesson_completed) {
            return testUi('compose.completed_banner');
        }

        const policy = activeDebugPolicy();
        if (!policy) {
            return testUi('compose.goal_note', { rating: minRating.toFixed(1), count: rollingWindow });
        }

        return testUi('compose.debug_goal_note', {
            count: policy.required_answered,
            correct: policy.required_correct,
            answered_percent: policy.required_answered_percent,
            correct_percent: policy.required_correct_percent,
            percent: policy.minimum_rating_percent,
            rating: (policy.minimum_rating_percent / 20).toFixed(1),
        });
    }

    function ratingCardSummary(rating) {
        const policy = activeDebugPolicy();

        if (!policy || state.progress.lesson_completed) {
            return {
                title: testUi('compose.last_100_answers'),
                value: `${rating.toFixed(1)} / 5`,
                meta: `${state.progress.rolling_results.length} / ${rollingWindow}`,
            };
        }

        return {
            title: testUi('compose.debug_policy_metric_title'),
            value: `${(policy.minimum_rating_percent / 20).toFixed(1)} / 5`,
            meta: testUi('compose.debug_policy_rating_meta', {
                current: state.progress.rolling_results.length,
                required: policy.required_answered,
                correct: policy.required_correct,
                answered_percent: policy.required_answered_percent,
                correct_percent: policy.required_correct_percent,
            }),
        };
    }

    function sanitizeTokenValue(value) {
        return String(value ?? '').trim();
    }

    function createTokenInstance(id, value, isCorrect = false, correctIndex = null) {
        const sanitizedValue = sanitizeTokenValue(value);
        if (sanitizedValue === '') {
            return null;
        }

        return {
            id: String(id),
            value: sanitizedValue,
            isCorrect: Boolean(isCorrect),
            correctIndex: Number.isInteger(correctIndex) ? correctIndex : null,
        };
    }

    function buildFallbackTokenBank(correctTokenValues, tokensPool) {
        const instances = [];
        const correctLookup = {};

        correctTokenValues.forEach((token, index) => {
            const instance = createTokenInstance(`c${index + 1}`, token, true, index);
            if (!instance) {
                return;
            }

            correctLookup[instance.value] = true;
            instances.push(instance);
        });

        let distractorIndex = 1;
        (Array.isArray(tokensPool) ? tokensPool : []).forEach((token) => {
            const value = sanitizeTokenValue(token);
            if (value === '' || correctLookup[value]) {
                return;
            }

            const instance = createTokenInstance(`d${distractorIndex}`, value, false, null);
            if (!instance) {
                return;
            }

            distractorIndex += 1;
            instances.push(instance);
        });

        return instances;
    }

    function sanitizeTokenBank(rawBank, fallbackCorrectValues, fallbackPool) {
        const tokenBank = Array.isArray(rawBank)
            ? rawBank
                .map((token, index) => {
                    const fallbackId = `t${index + 1}`;
                    const isCorrect = Boolean(token?.isCorrect ?? token?.is_correct);
                    const correctIndex = Number.isInteger(token?.correctIndex)
                        ? token.correctIndex
                        : sanitizeInteger(token?.correct_index, -1);

                    return createTokenInstance(
                        token?.id ?? fallbackId,
                        token?.value,
                        isCorrect,
                        correctIndex >= 0 ? correctIndex : null
                    );
                })
                .filter(Boolean)
            : [];

        return tokenBank.length > 0
            ? tokenBank
            : buildFallbackTokenBank(fallbackCorrectValues, fallbackPool);
    }

    function sanitizeQuestion(item, index = 0) {
        const punctuation = item?.punctuation === '?' ? '?' : '.';
        const fallbackCorrectValues = Array.isArray(item?.correctTokenValues)
            ? item.correctTokenValues.map(sanitizeTokenValue).filter(Boolean)
            : (Array.isArray(item?.correctTokens)
                ? item.correctTokens.map(sanitizeTokenValue).filter(Boolean)
                : []);
        const fallbackPool = Array.isArray(item?.tokensPool)
            ? item.tokensPool.map(sanitizeTokenValue).filter(Boolean)
            : [];
        const tokenBank = sanitizeTokenBank(item?.tokenBank, fallbackCorrectValues, fallbackPool);
        const tokenMap = tokenBank.reduce((accumulator, token) => {
            accumulator[token.id] = token;

            return accumulator;
        }, {});
        const derivedCorrectInstances = tokenBank
            .filter((token) => token.isCorrect)
            .sort((left, right) => {
                const leftIndex = Number.isInteger(left.correctIndex) ? left.correctIndex : Number.MAX_SAFE_INTEGER;
                const rightIndex = Number.isInteger(right.correctIndex) ? right.correctIndex : Number.MAX_SAFE_INTEGER;

                return leftIndex - rightIndex;
            });
        const providedCorrectTokenIds = Array.isArray(item?.correctTokenIds)
            ? item.correctTokenIds
                .map((id) => String(id ?? ''))
                .filter((id) => id !== '' && Object.prototype.hasOwnProperty.call(tokenMap, id))
            : [];
        const correctTokenIds = providedCorrectTokenIds.length > 0
            ? providedCorrectTokenIds
            : derivedCorrectInstances.map((token) => token.id);
        const correctTokenValues = correctTokenIds
            .map((id) => tokenMap[id]?.value ?? '')
            .filter(Boolean);
        const sourceTextUk = String(item?.sourceTextUk ?? item?.question ?? '').trim();

        if (sourceTextUk === '' || correctTokenValues.length === 0 || tokenBank.length === 0) {
            return null;
        }

        return {
            id: String(item?.id ?? ''),
            uuid: String(item?.uuid ?? ''),
            position: index + 1,
            level: item?.level ? String(item.level) : '',
            sourceTextUk,
            correctTokens: correctTokenValues,
            correctTokenValues,
            correctTokenIds,
            tokenBank,
            tokenMap,
            correctText: normalizeText(item?.correctText || `${correctTokenValues.join(' ')}${punctuation}`),
            hintUk: item?.hintUk ? String(item.hintUk).trim() : '',
            explanations: item?.explanations && typeof item.explanations === 'object' ? item.explanations : {},
            punctuation,
        };
    }

    const normalizedQuestions = questions
        .map(sanitizeQuestion)
        .filter(Boolean);

    if (normalizedQuestions.length === 0) {
        workspace?.classList.add('hidden');
        pendingState?.classList.add('hidden');
        emptyState?.classList.remove('hidden');
        return;
    }

    const courseStore = config.courseSlug && window.PolyglotCourseProgress
        ? window.PolyglotCourseProgress.createStore(
            config.courseSlug,
            Array.isArray(config.courseLessons) ? config.courseLessons : []
        )
        : null;
    const progressStore = window.PolyglotCourseProgress.createLessonStore({
        lessonSlug: config.slug,
        courseSlug: config.courseSlug,
        questionCount: normalizedQuestions.length,
        rollingWindow,
        minRating,
        courseStore,
        sourceId: courseStore?.sourceId,
    });

    const state = {
        progress: progressStore.read(),
        selectedTokenIds: [],
        bankOrder: [],
        feedback: null,
        autoAdvanceTimer: null,
        lastTrackedQuestionKey: null,
        nextLessonPromptShown: false,
    };

    function currentCourseState() {
        return serverCourseState || (courseStore ? courseStore.read() : null);
    }

    function currentCourseLesson(courseState) {
        if (!courseStore || !courseState || !courseState.lessons) {
            return null;
        }

        return courseState.lessons[config.slug] || null;
    }

    function lessonIsLocked(courseState) {
        const lessonState = currentCourseLesson(courseState);

        return Boolean(courseStore && (!lessonState || !lessonState.unlocked));
    }

    function lessonNameBySlug(slug) {
        if (!courseStore || !slug) {
            return '';
        }

        return courseStore.findLesson(slug)?.name || '';
    }

    function localLessonAnswered(progress) {
        if (!progress || typeof progress !== 'object') {
            return 0;
        }

        const totalAttempts = Number(progress.total_attempts || 0);
        const rollingCount = Array.isArray(progress.rolling_results) ? progress.rolling_results.length : 0;

        return Math.max(Number.isFinite(totalAttempts) ? totalAttempts : 0, rollingCount);
    }

    function serverLessonAnswered(progress) {
        const lessonEntry = progress?.lessons?.[config.slug] || null;
        const lessonProgress = progress?.lesson_progress?.[config.slug] || null;
        const answeredCount = Number(lessonEntry?.answered_count || 0);
        const progressTotal = Number(lessonProgress?.total_attempts || 0);
        const rollingCount = Array.isArray(lessonProgress?.rolling_results) ? lessonProgress.rolling_results.length : 0;

        return Math.max(
            Number.isFinite(answeredCount) ? answeredCount : 0,
            Number.isFinite(progressTotal) ? progressTotal : 0,
            rollingCount
        );
    }

    function localProgressPayload() {
        const lessonProgress = progressStore.read();

        if (localLessonAnswered(lessonProgress) <= 0 && !lessonProgress.lesson_completed) {
            return null;
        }

        return {
            course: courseStore ? courseStore.read() : null,
            lesson_progress: {
                [config.slug]: lessonProgress,
            },
        };
    }

    async function maybeImportLocalProgress(progress) {
        if (!progressSync.importUrl) {
            return null;
        }

        const localProgress = localProgressPayload();
        const lessonProgress = localProgress?.lesson_progress?.[config.slug] || null;

        if (!lessonProgress || localLessonAnswered(lessonProgress) <= serverLessonAnswered(progress)) {
            return null;
        }

        try {
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
        } catch (error) {
            return null;
        }
    }

    function applyServerProgress(progress) {
        if (!progress || typeof progress !== 'object') {
            return;
        }

        serverCourseState = progress;
        serverAuthenticated = true;

        const lessonProgress = progress.lesson_progress?.[config.slug];
        if (lessonProgress) {
            state.progress = progressStore.normalize(lessonProgress);
        }
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
                serverCourseState = null;
                return;
            }

            applyServerProgress(await maybeImportLocalProgress(payload.progress) || payload.progress);
        } catch (error) {
            serverAuthenticated = false;
            serverCourseState = null;
        }
    }

    async function postServerAttempt(result, question, submitted, progressSnapshot) {
        if (!serverAuthenticated || !progressSync.attemptUrl) {
            return;
        }

        try {
            const response = await fetch(progressSync.attemptUrl, {
                method: 'POST',
                credentials: 'same-origin',
                headers: serverHeaders(),
                body: JSON.stringify({
                    lesson_slug: config.slug,
                    question_uuid: question?.uuid || null,
                    rating: result,
                    is_correct: result >= minRating,
                    client_attempt_uuid: generateClientAttemptUuid(),
                    answer_payload: {
                        source: 'compose_tokens',
                        current_queue_index: progressSnapshot.current_queue_index,
                        selected_token_ids: state.selectedTokenIds,
                        submitted_answer: submitted,
                        correct_answer: question?.correctText || null,
                    },
                }),
            });

            if (!response.ok) {
                return;
            }

            const payload = await response.json();
            if (!payload?.authenticated) {
                serverAuthenticated = false;
                serverCourseState = null;
                return;
            }

            if (payload.course_progress) {
                serverCourseState = payload.course_progress;
            }

            if (payload.lesson_progress) {
                const previousProgress = { ...state.progress };
                const localQueueIndex = state.progress.current_queue_index;
                const serverLessonProgress = progressStore.normalize(payload.lesson_progress);

                state.progress = {
                    ...serverLessonProgress,
                    current_queue_index: state.autoAdvanceTimer !== null
                        ? localQueueIndex
                        : serverLessonProgress.current_queue_index,
                };

                render();
                offerNextLessonIfJustUnlocked(previousProgress, state.progress);
            }
        } catch (error) {
            serverAuthenticated = false;
            serverCourseState = null;
        }
    }

    function actionLinkMarkup(url, label, variant = 'solid') {
        if (!url || !label) {
            return '';
        }

        const style = variant === 'solid'
            ? 'background: var(--accent); color: white; border-color: var(--accent);'
            : 'border-color: var(--line); color: var(--text);';

        return `
            <a href="${html(url)}"
               class="inline-flex items-center justify-center rounded-full border px-5 py-3 text-sm font-extrabold transition hover:opacity-95"
               style="${style}">
                ${html(label)}
            </a>
        `;
    }

    function actionButtonMarkup(action, label, variant = 'soft') {
        if (!action || !label) {
            return '';
        }

        const style = variant === 'solid'
            ? 'background: var(--accent); color: white; border-color: var(--accent);'
            : 'border-color: var(--line); color: var(--text);';

        return `
            <button type="button"
               data-action="${html(action)}"
               class="inline-flex items-center justify-center rounded-full border px-5 py-3 text-sm font-extrabold transition hover:opacity-95"
               style="${style}">
                ${html(label)}
            </button>
        `;
    }

    function lockMarkup() {
        const previousLessonName = lessonNameBySlug(config.previousLessonSlug);
        const previousLabel = previousLessonName || testUi('course.previous_lesson');
        const previousLink = config.previousLessonUrl
            ? actionLinkMarkup(config.previousLessonUrl, previousLabel, 'soft')
            : '';

        return `
            <div class="rounded-[24px] border px-6 py-8 text-center surface-card" style="border-color: var(--line);">
                <span class="inline-flex items-center rounded-full border px-4 py-2 text-[11px] font-extrabold uppercase tracking-[0.22em] soft-accent" style="border-color: var(--line); color: var(--accent);">
                    ${html(testUi('course.lesson_locked'))}
                </span>
                <h3 class="mt-4 font-display text-2xl font-extrabold">${html(testUi('course.complete_previous_lesson_first'))}</h3>
                <p class="mx-auto mt-3 max-w-2xl text-sm leading-7" style="color: var(--muted);">
                    ${html(testUi('course.locked_lesson_note'))}
                </p>
                <div class="mt-6 flex flex-wrap justify-center gap-3">
                    ${actionLinkMarkup(config.courseUrl, testUi('course.back_to_course'), 'soft')}
                    ${previousLink}
                </div>
            </div>
        `;
    }

    function completionMarkup() {
        if (!state.progress.lesson_completed) {
            return '';
        }

        if (config.nextLessonUrl && config.nextLessonSlug) {
            return `
                <div class="rounded-[24px] border px-5 py-5" style="border-color: #b8e3c7; background: linear-gradient(180deg, #f0fbf4 0%, #e7f8ee 100%);">
                    <p class="text-[11px] font-extrabold uppercase tracking-[0.22em]" style="color: #17603a;">${html(testUi('course.lesson_unlocked'))}</p>
                    <h3 class="mt-2 font-display text-2xl font-extrabold" style="color: #17603a;">${html(testUi('course.next_lesson_available'))}</h3>
                    <p class="mt-2 text-sm leading-6" style="color: #17603a;">${html(testUi('compose.completed_banner'))}</p>
                    <div class="mt-4 flex flex-wrap gap-3">
                        ${actionLinkMarkup(config.nextLessonUrl, testUi('course.next_lesson'), 'solid')}
                        ${actionLinkMarkup(config.courseUrl, testUi('course.back_to_course'), 'soft')}
                    </div>
                </div>
            `;
        }

        return `
            <div class="rounded-[24px] border px-5 py-5" style="border-color: #b8e3c7; background: linear-gradient(180deg, #f0fbf4 0%, #e7f8ee 100%);">
                <p class="text-[11px] font-extrabold uppercase tracking-[0.22em]" style="color: #17603a;">${html(testUi('course.course_completed'))}</p>
                <h3 class="mt-2 font-display text-2xl font-extrabold" style="color: #17603a;">${html(testUi('course.course_completed_title'))}</h3>
                <p class="mt-2 text-sm leading-6" style="color: #17603a;">${html(testUi('course.course_completed_note'))}</p>
                <div class="mt-4 flex flex-wrap gap-3">
                    ${actionLinkMarkup(config.firstLessonUrl || config.courseUrl, testUi('course.repeat_course'), 'solid')}
                    ${actionLinkMarkup(config.courseUrl, testUi('course.back_to_course'), 'soft')}
                    ${config.continueCourseUrl ? actionLinkMarkup(config.continueCourseUrl, config.continueCourseLabel || '', 'soft') : ''}
                    ${actionButtonMarkup('restart-course', testUi('course.restart_course'), 'soft')}
                </div>
            </div>
        `;
    }

    function offerNextLessonIfJustUnlocked(previousProgress, nextProgress) {
        if (state.nextLessonPromptShown || !config.nextLessonUrl || !config.nextLessonSlug) {
            return;
        }

        if (previousProgress?.lesson_completed || !nextProgress?.lesson_completed) {
            return;
        }

        state.nextLessonPromptShown = true;

        window.setTimeout(() => {
            const shouldGo = window.confirm(testUi('course.next_lesson_unlocked_prompt'));
            if (shouldGo) {
                window.location.assign(config.nextLessonUrl);
            }
        }, 120);
    }

    function refreshCourseUi() {
        const courseState = currentCourseState();

        if (lessonIsLocked(courseState)) {
            if (pageRoot) {
                pageRoot.dataset.polyglotLockState = 'locked';
            }

            workspace?.classList.add('hidden');
            pendingState?.classList.add('hidden');
            completionState?.classList.add('hidden');
            lockState.innerHTML = lockMarkup();
            lockState.classList.remove('hidden');

            return {
                courseState,
                locked: true,
            };
        }

        lockState.innerHTML = '';
        lockState.classList.add('hidden');
        if (pageRoot) {
            pageRoot.dataset.polyglotLockState = 'ready';
        }
        pendingState?.classList.add('hidden');
        workspace?.classList.remove('hidden');

        if (state.progress.lesson_completed) {
            completionState.innerHTML = completionMarkup();
            completionState.classList.remove('hidden');
        } else {
            completionState.innerHTML = '';
            completionState.classList.add('hidden');
        }

        return {
            courseState,
            locked: false,
        };
    }

    function clampQueueIndex() {
        state.progress = progressStore.normalize(state.progress);
    }

    function currentQuestion() {
        clampQueueIndex();

        return normalizedQuestions[state.progress.current_queue_index] || normalizedQuestions[0];
    }

    function adminDebugEnabled() {
        return Boolean(progressSync.debugUrl || document.querySelector('[data-polyglot-admin-debug="1"]'));
    }

    function readQuestionStats() {
        if (!adminDebugEnabled()) {
            return {};
        }

        return readJsonStorage(adminDebugQuestionStatsKey) || {};
    }

    function writeQuestionStats(stats) {
        if (!adminDebugEnabled()) {
            return;
        }

        try {
            window.localStorage.setItem(adminDebugQuestionStatsKey, JSON.stringify(stats));
            window.dispatchEvent(new CustomEvent('polyglot:admin-debug-question-stats-updated', {
                detail: {
                    key: adminDebugQuestionStatsKey,
                    stats,
                },
            }));
        } catch (error) {
            // localStorage can be unavailable in private contexts.
        }
    }

    function questionStatsEntry(stats, question) {
        const position = sanitizeCount(question?.position);
        const uuid = String(question?.uuid || '').trim();
        const id = String(question?.id || '').trim();
        const key = position > 0
            ? `position:${position}`
            : (uuid !== '' ? uuid : (id !== '' ? `id:${id}` : ''));

        if (key === '') {
            return null;
        }

        const aliasKeys = [
            key,
            position > 0 ? `position:${position}` : '',
            uuid,
            id !== '' ? `id:${id}` : '',
        ].filter((value, index, values) => value !== '' && values.indexOf(value) === index);
        const existingKey = aliasKeys.find((alias) => stats[alias]);
        const existing = existingKey ? stats[existingKey] : {};

        stats[key] = {
            uuid: uuid || existing.uuid || '',
            id: id || existing.id || '',
            position: position > 0 ? position : (sanitizeCount(existing.position) || null),
            shown: sanitizeCount(existing.shown),
            correct: sanitizeCount(existing.correct),
            incorrect: sanitizeCount(existing.incorrect),
            last_seen_at: existing.last_seen_at || null,
            last_answered_at: existing.last_answered_at || null,
        };

        aliasKeys
            .filter((alias) => alias !== key)
            .forEach((alias) => {
                delete stats[alias];
            });

        return stats[key];
    }

    function questionStatsKey(question) {
        const position = sanitizeCount(question?.position);
        const uuid = String(question?.uuid || '').trim();
        const id = String(question?.id || '').trim();

        if (position > 0) {
            return `position:${position}`;
        }

        if (uuid !== '') {
            return uuid;
        }

        return id !== '' ? `id:${id}` : '';
    }

    function trackQuestionShown(question) {
        const key = questionStatsKey(question);

        if (!adminDebugEnabled() || key === '' || state.lastTrackedQuestionKey === key) {
            return;
        }

        const stats = readQuestionStats();
        const entry = questionStatsEntry(stats, question);
        if (!entry) {
            return;
        }

        entry.shown = sanitizeCount(entry.shown) + 1;
        entry.last_seen_at = new Date().toISOString();
        state.lastTrackedQuestionKey = key;
        writeQuestionStats(stats);
    }

    function trackQuestionAttempt(question, wasCorrect) {
        if (!adminDebugEnabled()) {
            return;
        }

        const stats = readQuestionStats();
        const entry = questionStatsEntry(stats, question);
        if (!entry) {
            return;
        }

        if (sanitizeCount(entry.shown) <= 0) {
            entry.shown = 1;
            entry.last_seen_at = new Date().toISOString();
        }

        if (wasCorrect) {
            entry.correct = sanitizeCount(entry.correct) + 1;
        } else {
            entry.incorrect = sanitizeCount(entry.incorrect) + 1;
        }

        entry.last_answered_at = new Date().toISOString();
        writeQuestionStats(stats);
    }

    function persistProgress() {
        state.progress = progressStore.write(state.progress);
    }

    function sentenceFromTokenIds(question, tokenIds) {
        const values = tokenIds
            .map((tokenId) => question.tokenMap[tokenId]?.value ?? '')
            .filter(Boolean);

        return normalizeText(`${values.join(' ')}${question.punctuation || '.'}`);
    }

    function ratingValue() {
        if (state.progress.rolling_results.length === 0) {
            return 0;
        }

        const total = state.progress.rolling_results.reduce((sum, value) => sum + Number(value || 0), 0);

        return total / state.progress.rolling_results.length;
    }

    function shuffleBank(question) {
        state.bankOrder = question.tokenBank.map((token) => token.id);
        shuffle(state.bankOrder);
    }

    function availableBankTokenIds(question) {
        const selectedIds = new Set(state.selectedTokenIds);

        return state.bankOrder.filter((tokenId) => !selectedIds.has(tokenId) && question.tokenMap[tokenId]);
    }

    function relevantExplanation(question) {
        if (!question.explanations || typeof question.explanations !== 'object') {
            return '';
        }

        const selectedValues = state.selectedTokenIds
            .map((tokenId) => question.tokenMap[tokenId]?.value ?? '')
            .filter(Boolean);

        const mismatchedToken = selectedValues.find((token, index) => {
            const expected = question.correctTokenValues[index];
            if (expected && normalizeCompare(expected) === normalizeCompare(token)) {
                return false;
            }

            return Object.prototype.hasOwnProperty.call(question.explanations, token);
        });

        if (mismatchedToken) {
            return String(question.explanations[mismatchedToken] || '').trim();
        }

        const explainedToken = selectedValues.find((token) => Object.prototype.hasOwnProperty.call(question.explanations, token));

        return explainedToken
            ? String(question.explanations[explainedToken] || '').trim()
            : '';
    }

    function clearAutoAdvance() {
        if (state.autoAdvanceTimer !== null) {
            window.clearTimeout(state.autoAdvanceTimer);
            state.autoAdvanceTimer = null;
        }
    }

    function resetCurrentAnswer(reshuffle = false) {
        state.selectedTokenIds = [];
        state.feedback = null;

        if (reshuffle || state.bankOrder.length === 0) {
            shuffleBank(currentQuestion());
        }
    }

    function advanceQuestion() {
        state.progress.current_queue_index = (state.progress.current_queue_index + 1) % normalizedQuestions.length;
        persistProgress();
        resetCurrentAnswer(true);
        render();
    }

    function markAttempt(result, question, submitted) {
        const previousProgress = { ...state.progress };
        trackQuestionAttempt(question, result === 5);
        state.progress = progressStore.markAttempt(state.progress, result === 5);
        offerNextLessonIfJustUnlocked(previousProgress, state.progress);
        postServerAttempt(result, question, submitted, state.progress);
    }

    function resetLessonProgress() {
        if (serverAuthenticated) {
            return;
        }

        clearAutoAdvance();
        state.progress = progressStore.reset();
        resetCurrentAnswer(true);
        render();
    }

    function restartCourse() {
        if (serverAuthenticated) {
            if (config.courseUrl) {
                window.location.assign(config.courseUrl);
            }

            return;
        }

        clearAutoAdvance();

        if (courseStore && typeof courseStore.reset === 'function') {
            courseStore.reset();
        } else {
            progressStore.reset();
        }

        if (config.courseUrl) {
            window.location.assign(config.courseUrl);

            return;
        }

        if (config.firstLessonUrl) {
            window.location.assign(config.firstLessonUrl);

            return;
        }

        rehydrateFromSharedState();
    }

    function rehydrateFromSharedState(sync = null) {
        const detail = sync?.detail || {};

        if (serverAuthenticated) {
            const serverProgress = detail.serverCourseProgress || detail.course_progress || detail.progress || null;
            if (serverProgress) {
                applyServerProgress(serverProgress);
            } else if (detail.serverLessonProgress && typeof detail.serverLessonProgress === 'object') {
                state.progress = progressStore.normalize(detail.serverLessonProgress);
            }

            render();
            return;
        }

        const previousQueueIndex = state.progress.current_queue_index;

        clearAutoAdvance();
        state.progress = progressStore.read();
        clampQueueIndex();
        state.feedback = null;

        if (previousQueueIndex !== state.progress.current_queue_index) {
            resetCurrentAnswer(true);
        } else if (state.selectedTokenIds.some((tokenId) => !currentQuestion().tokenMap[tokenId])) {
            resetCurrentAnswer(true);
        }

        render();
    }

    function feedbackMarkup(question) {
        if (!state.feedback) {
            if (state.progress.lesson_completed) {
                return `
                    <div class="rounded-[22px] border px-5 py-4" style="border-color: #b8e3c7; background: linear-gradient(180deg, #f0fbf4 0%, #e7f8ee 100%);">
                        <p class="font-semibold" style="color: #17603a;">${html(testUi('compose.lesson_completed'))}</p>
                        <p class="mt-2 text-sm leading-6" style="color: #17603a;">${html(testUi('compose.completed_banner'))}</p>
                    </div>
                `;
            }

            return '';
        }

        const baseClass = state.feedback.type === 'correct'
            ? 'border-color: #b8e3c7; background: linear-gradient(180deg, #f0fbf4 0%, #e7f8ee 100%); color: #17603a;'
            : 'border-color: #fecaca; background: linear-gradient(180deg, #fff5f5 0%, #ffefef 100%); color: #b42318;';
        const statusLabel = state.feedback.type === 'correct'
            ? testUi('compose.correct')
            : testUi('compose.incorrect');
        const hintBlock = state.feedback.hint
            ? `
                <div class="mt-3 rounded-[18px] border px-4 py-3 text-sm leading-6" style="border-color: rgba(148, 163, 184, 0.25); background: rgba(255, 255, 255, 0.72);">
                    <div class="text-xs font-extrabold uppercase tracking-[0.18em]" style="color: var(--muted);">${html(testUi('compose.hint'))}</div>
                    <div class="mt-2 whitespace-pre-line">${html(state.feedback.hint)}</div>
                </div>
            `
            : '';
        const explanationBlock = state.feedback.explanation
            ? `
                <div class="mt-3 rounded-[18px] border px-4 py-3 text-sm leading-6" style="border-color: rgba(148, 163, 184, 0.25); background: rgba(255, 255, 255, 0.72);">
                    <div class="text-xs font-extrabold uppercase tracking-[0.18em]" style="color: var(--muted);">${html(testUi('compose.explanation'))}</div>
                    <div class="mt-2 whitespace-pre-line">${html(state.feedback.explanation)}</div>
                </div>
            `
            : '';
        const answerBlock = state.feedback.type === 'incorrect'
            ? `
                <div class="mt-3 rounded-[18px] border px-4 py-3 text-sm leading-6" style="border-color: rgba(148, 163, 184, 0.25); background: rgba(255, 255, 255, 0.72);">
                    <div class="text-xs font-extrabold uppercase tracking-[0.18em]" style="color: var(--muted);">${html(testUi('compose.correct_answer'))}</div>
                    <div class="mt-2 font-semibold">${html(question.correctText)}</div>
                </div>
            `
            : '';

        return `
            <div class="rounded-[22px] border px-5 py-4" style="${baseClass}">
                <p class="font-semibold">${html(statusLabel)}</p>
                ${answerBlock}
                ${hintBlock}
                ${explanationBlock}
            </div>
        `;
    }

    const chipBaseClass = 'inline-flex items-center border font-bold leading-none transition';
    const chipLayoutStyle = 'min-height: var(--polyglot-chip-min-height); padding: var(--polyglot-chip-padding); border-radius: var(--polyglot-chip-radius); font-size: var(--polyglot-chip-font-size);';
    const answerChipStyle = `${chipLayoutStyle} border-color: var(--line); background: color-mix(in srgb, var(--accent-soft) 88%, white); color: var(--text);`;
    const bankChipStyle = `${chipLayoutStyle} border-color: var(--line); background: var(--surface); color: var(--text);`;
    const punctuationChipStyle = `${chipLayoutStyle} border-color: var(--line); background: color-mix(in srgb, var(--surface) 90%, white); color: var(--muted);`;

    function answerZoneMarkup(question) {
        const chips = state.selectedTokenIds.map((tokenId) => {
            const token = question.tokenMap[tokenId];
            if (!token) {
                return '';
            }

            return `
                <button type="button"
                    data-answer-token-id="${html(token.id)}"
                    class="${chipBaseClass} gap-1.5 hover:opacity-90 sm:gap-2.5"
                    style="${answerChipStyle}">
                    <span>${html(token.value)}</span>
                    <span class="text-xs leading-none sm:text-sm" style="color: var(--muted);">&times;</span>
                </button>
            `;
        }).join('');

        const placeholder = chips === ''
            ? `<div class="rounded-[14px] border border-dashed px-3 py-3 text-[13px] leading-5 sm:rounded-[18px] sm:px-4 sm:py-5 sm:text-sm sm:leading-6" style="border-color: var(--line); color: var(--muted);">${html(testUi('compose.answer_placeholder'))}</div>`
            : chips;

        return `
            <div class="flex min-h-[3.25rem] flex-wrap content-start items-start gap-2 sm:min-h-[5rem] sm:gap-3">
                ${placeholder}
                <span class="${chipBaseClass}" style="${punctuationChipStyle}">${html(question.punctuation)}</span>
            </div>
        `;
    }

    function bankMarkup(question) {
        const tokenIds = availableBankTokenIds(question);

        if (tokenIds.length === 0) {
            return `<div class="rounded-[14px] border border-dashed px-3 py-3 text-[13px] leading-5 sm:rounded-[18px] sm:px-4 sm:py-5 sm:text-sm sm:leading-6" style="border-color: var(--line); color: var(--muted);">${html(testUi('compose.empty_pool'))}</div>`;
        }

        return tokenIds.map((tokenId) => {
            const token = question.tokenMap[tokenId];
            if (!token) {
                return '';
            }

            return `
                <button type="button"
                    data-bank-token-id="${html(token.id)}"
                    class="${chipBaseClass} hover:-translate-y-0.5 hover:shadow-sm"
                    style="${bankChipStyle}">
                    ${html(token.value)}
                </button>
            `;
        }).join('');
    }

    function render() {
        const courseUi = refreshCourseUi();
        if (courseUi.locked) {
            return;
        }

        clampQueueIndex();
        const question = currentQuestion();
        trackQuestionShown(question);
        const rating = ratingValue();
        const ratingSummary = ratingCardSummary(rating);
        const isLocked = state.autoAdvanceTimer !== null;
        const statusLabel = state.progress.lesson_completed
            ? testUi('compose.lesson_completed')
            : testUi('compose.in_progress');

        document.getElementById('compose-question-index').textContent = testUi('compose.current_sentence', {
            current: state.progress.current_queue_index + 1,
            total: normalizedQuestions.length,
        });
        document.getElementById('compose-rating-title').textContent = ratingSummary.title;
        document.getElementById('compose-rating').textContent = ratingSummary.value;
        document.getElementById('compose-rating-meta').textContent = ratingSummary.meta;
        document.getElementById('compose-status').textContent = statusLabel;
        document.getElementById('compose-status-note').textContent = statusGoalNote();
        document.getElementById('compose-source-text').textContent = question.sourceTextUk;
        document.getElementById('compose-punctuation').textContent = testUi('compose.ends_with', { punctuation: question.punctuation });
        document.getElementById('compose-answer-zone').innerHTML = answerZoneMarkup(question);
        document.getElementById('compose-bank').innerHTML = bankMarkup(question);
        const feedbackElement = document.getElementById('compose-feedback');
        const feedbackHtml = feedbackMarkup(question);
        feedbackElement.innerHTML = feedbackHtml;
        feedbackElement.classList.toggle('hidden', feedbackHtml.trim() === '');

        root.querySelector('[data-action="check"]').disabled = isLocked;
        root.querySelector('[data-action="clear"]').disabled = isLocked || state.selectedTokenIds.length === 0;
        root.querySelector('[data-action="undo"]').disabled = isLocked || state.selectedTokenIds.length === 0;
        root.querySelector('[data-action="reset-progress"]').disabled = isLocked || serverAuthenticated;
    }

    function addTokenById(tokenId) {
        const question = currentQuestion();
        if (!question.tokenMap[tokenId] || state.selectedTokenIds.includes(tokenId)) {
            return;
        }

        state.selectedTokenIds.push(String(tokenId));
        state.feedback = null;
        render();
    }

    function removeTokenById(tokenId) {
        const index = state.selectedTokenIds.findIndex((selectedTokenId) => selectedTokenId === tokenId);
        if (index === -1) {
            return;
        }

        state.selectedTokenIds.splice(index, 1);
        state.feedback = null;
        render();
    }

    function removeLastToken() {
        if (state.selectedTokenIds.length === 0) {
            return;
        }

        removeTokenById(state.selectedTokenIds[state.selectedTokenIds.length - 1]);
    }

    function checkAnswer() {
        if (state.autoAdvanceTimer !== null) {
            return;
        }

        const question = currentQuestion();
        const submitted = sentenceFromTokenIds(question, state.selectedTokenIds);
        const isCorrect = normalizeCompare(submitted) === normalizeCompare(question.correctText);

        if (isCorrect) {
            markAttempt(5, question, submitted);
            state.feedback = {
                type: 'correct',
                hint: '',
                explanation: '',
            };
            render();
            state.autoAdvanceTimer = window.setTimeout(() => {
                state.autoAdvanceTimer = null;
                advanceQuestion();
            }, 800);

            return;
        }

        markAttempt(0, question, submitted);
        state.feedback = {
            type: 'incorrect',
            hint: question.hintUk || '',
            explanation: relevantExplanation(question),
        };
        render();
    }

    function hasEditableFocus(target) {
        if (!(target instanceof HTMLElement)) {
            return false;
        }

        if (target.isContentEditable) {
            return true;
        }

        return ['INPUT', 'TEXTAREA', 'SELECT'].includes(target.tagName);
    }

    root.addEventListener('click', (event) => {
        if (state.autoAdvanceTimer !== null) {
            const actionButton = event.target.closest('[data-action="reset-progress"], [data-action="restart-course"]');
            if (!actionButton) {
                return;
            }
        }

        const bankToken = event.target.closest('[data-bank-token-id]');
        if (bankToken) {
            addTokenById(bankToken.getAttribute('data-bank-token-id') || '');
            return;
        }

        const answerToken = event.target.closest('[data-answer-token-id]');
        if (answerToken) {
            removeTokenById(answerToken.getAttribute('data-answer-token-id') || '');
            return;
        }

        const actionButton = event.target.closest('[data-action]');
        if (!actionButton) {
            return;
        }

        const action = actionButton.getAttribute('data-action');

        if (action === 'check') {
            checkAnswer();
        } else if (action === 'clear') {
            resetCurrentAnswer(false);
            render();
        } else if (action === 'undo') {
            removeLastToken();
        } else if (action === 'reset-progress') {
            resetLessonProgress();
        } else if (action === 'restart-course') {
            const confirmed = window.confirm(testUi('course.reset_course_progress_confirm'));
            if (!confirmed) {
                return;
            }

            restartCourse();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (hasEditableFocus(event.target)) {
            return;
        }

        if (event.key === 'Enter') {
            event.preventDefault();
            checkAnswer();
            return;
        }

        if (event.key === 'Backspace' && state.selectedTokenIds.length > 0) {
            event.preventDefault();
            removeLastToken();
        }
    });

    window.addEventListener('storage', (event) => {
        const key = String(event.key || '');
        const courseSlug = String(config.courseSlug || '').trim();

        if (courseSlug && key.startsWith(`polyglot_debug_unlock_policy:${courseSlug}:`)) {
            render();
        }
    });

    window.PolyglotCourseProgress.subscribeToSync({
        courseSlug: config.courseSlug,
        lessonSlug: config.slug,
        ignoreSourceId: progressStore.sourceId,
        onSync: rehydrateFromSharedState,
    });

    hydrateServerProgress().finally(() => {
        const initialCourseUi = refreshCourseUi();
        if (initialCourseUi.locked) {
            return;
        }

        if (courseStore && !serverAuthenticated) {
            courseStore.markLessonOpened(config.slug);
        }

        clampQueueIndex();
        resetCurrentAnswer(true);

        if (!serverAuthenticated) {
            persistProgress();
        }

        render();
    });
    }

    if (window.PolyglotCourseProgress) {
        boot();

        return;
    }

    window.addEventListener('polyglot:progress-ready', boot, { once: true });
})();
</script>
@endsection
