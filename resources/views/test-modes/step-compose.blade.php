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
    $startsLockedPending = filled($courseSlug) && filled($previousLessonSlug);
@endphp

<div class="mx-auto w-full max-w-5xl"
     id="polyglot-compose-root"
     data-polyglot-lesson-root
     data-polyglot-lesson-slug="{{ $test->slug }}"
     data-polyglot-course-slug="{{ $courseSlug }}"
     data-polyglot-previous-lesson-slug="{{ $previousLessonSlug }}"
     data-polyglot-next-lesson-slug="{{ $nextLessonSlug }}"
     data-polyglot-lock-state="{{ $startsLockedPending ? 'pending' : 'ready' }}">
    @if(filled($courseSlug))
        <section class="mb-6 rounded-[28px] border p-5 shadow-card surface-card-strong" style="border-color: var(--line);">
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

    <div id="polyglot-compose-app" class="space-y-6">
        <section class="rounded-[28px] border p-5 shadow-card surface-card-strong sm:p-8" style="border-color: var(--line);">
            <div class="grid gap-4 xl:grid-cols-[1.05fr_0.95fr] xl:items-start">
                <div class="space-y-3">
                    <span class="inline-flex items-center rounded-full border px-4 py-2 text-[11px] font-extrabold uppercase tracking-[0.22em] soft-accent" style="border-color: var(--line); color: var(--accent);">
                        {{ __('frontend.tests.templates.compose.badge') }}
                    </span>
                    <div>
                        <h2 class="font-display text-2xl font-extrabold leading-tight sm:text-3xl" id="compose-test-name">{{ $test->name }}</h2>
                        <p class="mt-2 text-sm leading-6" id="compose-question-index" style="color: var(--muted);">
                            {{ __('frontend.tests.compose.current_sentence', ['current' => 1, 'total' => max(count($questionData ?? []), 1)]) }}
                        </p>
                    </div>
                </div>

                <div class="grid gap-3 sm:grid-cols-2">
                    <article class="rounded-[24px] border p-4 surface-card" style="border-color: var(--line);">
                        <p class="text-[11px] font-extrabold uppercase tracking-[0.22em]" style="color: var(--accent);">{{ __('frontend.tests.compose.last_100_answers') }}</p>
                        <p class="mt-2 font-display text-[2rem] font-extrabold leading-none" id="compose-rating">0.0 / 5</p>
                        <p class="mt-2 text-xs leading-5" id="compose-rating-meta" style="color: var(--muted);">0 / {{ $completionWindow }}</p>
                    </article>
                    <article class="rounded-[24px] border p-4 surface-card" style="border-color: var(--line);">
                        <p class="text-[11px] font-extrabold uppercase tracking-[0.22em]" style="color: var(--accent);">{{ __('frontend.tests.compose.lesson_status') }}</p>
                        <p class="mt-2 font-display text-[1.55rem] font-extrabold leading-tight" id="compose-status">{{ __('frontend.tests.compose.in_progress') }}</p>
                        <p class="mt-2 text-xs leading-5" id="compose-status-note" style="color: var(--muted);">
                            {{ __('frontend.tests.compose.goal_note', ['rating' => number_format($completionRating, 1), 'count' => $completionWindow]) }}
                        </p>
                    </article>
                </div>
            </div>
        </section>

        <article class="rounded-[28px] border p-5 shadow-card surface-card-strong sm:p-8" style="border-color: var(--line);">
            <div id="compose-empty-state" class="{{ count($questionData ?? []) > 0 ? 'hidden' : '' }}">
                <div class="rounded-[24px] border border-dashed px-6 py-10 text-center" style="border-color: var(--line);">
                    <p class="text-lg font-semibold">{{ __('frontend.tests.compose.empty') }}</p>
                </div>
            </div>

            <div id="compose-course-pending" class="{{ count($questionData ?? []) > 0 && $startsLockedPending ? '' : 'hidden' }}">
                <div class="rounded-[24px] border border-dashed px-6 py-10 text-center" style="border-color: var(--line);">
                    <p class="text-lg font-semibold">{{ __('frontend.tests.course.checking_access') }}</p>
                </div>
            </div>

            <div id="compose-course-lock" class="hidden"></div>

            <div id="compose-workspace" class="{{ count($questionData ?? []) > 0 && ! $startsLockedPending ? '' : 'hidden' }}">
                <div class="mb-6 rounded-[24px] border p-5 surface-card" style="border-color: var(--line);">
                    <p class="text-[11px] font-extrabold uppercase tracking-[0.22em]" style="color: var(--accent);">{{ __('frontend.tests.compose.source_sentence') }}</p>
                    <div id="compose-source-text" class="mt-4 text-2xl font-extrabold leading-[1.35] sm:text-[2rem]"></div>
                </div>

                <div class="grid gap-5 xl:grid-cols-[1.08fr_0.92fr]">
                    <section class="rounded-[24px] border p-5 surface-card" style="border-color: var(--line);">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <p class="text-[11px] font-extrabold uppercase tracking-[0.22em]" style="color: var(--accent);">{{ __('frontend.tests.compose.build_translation') }}</p>
                            <span class="rounded-full border px-3 py-1 text-xs font-semibold" id="compose-punctuation" style="border-color: var(--line); color: var(--muted);"></span>
                        </div>
                        <div id="compose-answer-zone" class="mt-4 min-h-[8rem] rounded-[22px] border border-dashed p-4 sm:p-5" style="border-color: var(--line); background: color-mix(in srgb, var(--surface) 88%, white);"></div>
                        <div class="mt-5 flex flex-wrap gap-3">
                            <button type="button" data-action="check" class="inline-flex items-center justify-center rounded-full bg-ocean px-5 py-3 text-sm font-extrabold text-white shadow-sm transition hover:opacity-95">
                                {{ __('frontend.tests.compose.check') }}
                            </button>
                            <button type="button" data-action="clear" class="inline-flex items-center justify-center rounded-full border px-5 py-3 text-sm font-bold transition" style="border-color: var(--line);">
                                {{ __('frontend.tests.compose.clear') }}
                            </button>
                            <button type="button" data-action="undo" class="inline-flex items-center justify-center rounded-full border px-5 py-3 text-sm font-bold transition" style="border-color: var(--line);">
                                {{ __('frontend.tests.compose.remove_last') }}
                            </button>
                            <button type="button" data-action="reset-progress" class="inline-flex items-center justify-center rounded-full border px-5 py-3 text-sm font-bold transition" style="border-color: var(--line);">
                                {{ __('frontend.tests.compose.reset_progress') }}
                            </button>
                        </div>
                        <p class="mt-4 text-xs leading-5" style="color: var(--muted);">{{ __('frontend.tests.compose.keyboard_hint') }}</p>
                    </section>

                    <section class="rounded-[24px] border p-5 surface-card" style="border-color: var(--line);">
                        <p class="text-[11px] font-extrabold uppercase tracking-[0.22em]" style="color: var(--accent);">{{ __('frontend.tests.compose.token_bank') }}</p>
                        <div id="compose-bank" class="mt-4 flex min-h-[8rem] flex-wrap gap-3"></div>
                    </section>
                </div>

                <div id="compose-feedback" class="mt-5" aria-live="polite"></div>
            </div>

            <div id="compose-course-completion" class="mt-5 hidden"></div>
        </article>
    </div>
</div>

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
        'previousLessonSlug' => $previousLessonSlug,
        'previousLessonUrl' => data_get($courseContext, 'previous_lesson_url'),
        'nextLessonSlug' => $nextLessonSlug,
        'nextLessonUrl' => data_get($courseContext, 'next_lesson_url', filled($nextLessonSlug) ? localized_route('test.step-compose', $nextLessonSlug) : null),
        'interfaceLocale' => data_get($rawFilters, 'interface_locale', app()->getLocale()),
        'courseLessons' => data_get($courseContext, 'lessons', []),
    ];
@endphp
window.__INITIAL_JS_TEST_QUESTIONS__ = @json($questionData);
window.__POLYGLOT_COMPOSE_CONFIG__ = @json($composeConfig);
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

    function sanitizeQuestion(item) {
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
            uuid: String(item?.uuid ?? ''),
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
    };

    function currentCourseState() {
        return courseStore ? courseStore.read() : null;
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
                    ${actionLinkMarkup(config.courseUrl, testUi('course.back_to_course'), 'soft')}
                </div>
            </div>
        `;
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

    function markAttempt(result) {
        state.progress = progressStore.markAttempt(state.progress, result === 5);
    }

    function resetLessonProgress() {
        clearAutoAdvance();
        state.progress = progressStore.reset();
        resetCurrentAnswer(true);
        render();
    }

    function rehydrateFromSharedState() {
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

    function answerZoneMarkup(question) {
        const chips = state.selectedTokenIds.map((tokenId) => {
            const token = question.tokenMap[tokenId];
            if (!token) {
                return '';
            }

            return `
                <button type="button"
                    data-answer-token-id="${html(token.id)}"
                    class="inline-flex items-center gap-2 rounded-full border px-4 py-2.5 text-sm font-bold transition hover:opacity-90"
                    style="border-color: var(--line); background: var(--accent-soft); color: var(--text);">
                    <span>${html(token.value)}</span>
                    <span class="text-xs" style="color: var(--muted);">&times;</span>
                </button>
            `;
        }).join('');

        const placeholder = chips === ''
            ? `<div class="rounded-[18px] border border-dashed px-4 py-5 text-sm leading-6" style="border-color: var(--line); color: var(--muted);">${html(testUi('compose.answer_placeholder'))}</div>`
            : chips;

        return `
            <div class="flex min-h-[5rem] flex-wrap gap-3">
                ${placeholder}
                <span class="inline-flex items-center rounded-full border px-4 py-2.5 text-sm font-semibold" style="border-color: var(--line); color: var(--muted);">${html(question.punctuation)}</span>
            </div>
        `;
    }

    function bankMarkup(question) {
        const tokenIds = availableBankTokenIds(question);

        if (tokenIds.length === 0) {
            return `<div class="rounded-[18px] border border-dashed px-4 py-5 text-sm leading-6" style="border-color: var(--line); color: var(--muted);">${html(testUi('compose.empty_pool'))}</div>`;
        }

        return tokenIds.map((tokenId) => {
            const token = question.tokenMap[tokenId];
            if (!token) {
                return '';
            }

            return `
                <button type="button"
                    data-bank-token-id="${html(token.id)}"
                    class="inline-flex items-center rounded-full border px-4 py-2.5 text-sm font-bold transition hover:-translate-y-0.5 hover:shadow-sm"
                    style="border-color: var(--line); background: var(--surface); color: var(--text);">
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
        const rating = ratingValue();
        const ratingText = `${rating.toFixed(1)} / 5`;
        const isLocked = state.autoAdvanceTimer !== null;
        const statusLabel = state.progress.lesson_completed
            ? testUi('compose.lesson_completed')
            : testUi('compose.in_progress');

        document.getElementById('compose-question-index').textContent = testUi('compose.current_sentence', {
            current: state.progress.current_queue_index + 1,
            total: normalizedQuestions.length,
        });
        document.getElementById('compose-rating').textContent = ratingText;
        document.getElementById('compose-rating-meta').textContent = `${state.progress.rolling_results.length} / ${rollingWindow}`;
        document.getElementById('compose-status').textContent = statusLabel;
        document.getElementById('compose-status-note').textContent = state.progress.lesson_completed
            ? testUi('compose.completed_banner')
            : testUi('compose.goal_note', { rating: minRating.toFixed(1), count: rollingWindow });
        document.getElementById('compose-source-text').textContent = question.sourceTextUk;
        document.getElementById('compose-punctuation').textContent = testUi('compose.ends_with', { punctuation: question.punctuation });
        document.getElementById('compose-answer-zone').innerHTML = answerZoneMarkup(question);
        document.getElementById('compose-bank').innerHTML = bankMarkup(question);
        document.getElementById('compose-feedback').innerHTML = feedbackMarkup(question);

        root.querySelector('[data-action="check"]').disabled = isLocked;
        root.querySelector('[data-action="clear"]').disabled = isLocked || state.selectedTokenIds.length === 0;
        root.querySelector('[data-action="undo"]').disabled = isLocked || state.selectedTokenIds.length === 0;
        root.querySelector('[data-action="reset-progress"]').disabled = isLocked;
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
            markAttempt(5);
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

        markAttempt(0);
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
            const actionButton = event.target.closest('[data-action="reset-progress"]');
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

    window.PolyglotCourseProgress.subscribeToSync({
        courseSlug: config.courseSlug,
        lessonSlug: config.slug,
        ignoreSourceId: progressStore.sourceId,
        onSync: rehydrateFromSharedState,
    });

    const initialCourseUi = refreshCourseUi();
    if (initialCourseUi.locked) {
        return;
    }

    if (courseStore) {
        courseStore.markLessonOpened(config.slug);
    }

    clampQueueIndex();
    resetCurrentAnswer(true);
    persistProgress();
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
