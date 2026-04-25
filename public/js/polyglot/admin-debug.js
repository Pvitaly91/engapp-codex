(function () {
    const root = document.querySelector('[data-polyglot-admin-debug="1"]');
    const payload = window.__POLYGLOT_ADMIN_DEBUG__;

    if (!root || !payload || typeof payload !== 'object') {
        return;
    }

    const i18n = window.__POLYGLOT_ADMIN_DEBUG_I18N__ || {};
    const lesson = payload.lesson || {};
    const course = payload.course || {};
    const completion = payload.completion || {};
    const storageKeys = payload.storage_keys || {};
    const lessons = Array.isArray(course.lessons) ? course.lessons : [];
    const lessonSlug = String(lesson.slug || root.dataset.polyglotDebugLessonSlug || '').trim();
    const courseSlug = String(course.slug || root.dataset.polyglotDebugCourseSlug || '').trim();
    const nextLessonSlug = String(lesson.next_lesson_slug || root.dataset.polyglotDebugNextLessonSlug || '').trim();
    const totalQuestions = Math.max(0, toInt(lesson.total_questions, Array.isArray(payload.questions) ? payload.questions.length : 0));
    const rollingWindow = Math.max(1, toInt(completion.rolling_window, 100));
    const minRating = Number.isFinite(Number(completion.min_rating)) ? Number(completion.min_rating) : 4.5;
    const lessonProgressKey = storageKeys.lesson_progress || `polyglot_progress:${lessonSlug}`;
    const courseProgressKey = storageKeys.course_progress || `polyglot_course_progress:${courseSlug}`;
    const legacyCourseProgressKey = storageKeys.legacy_course_progress || `polyglot_course_state:${courseSlug}`;
    const lessonPolicyKey = storageKeys.lesson_debug_policy || `polyglot_debug_unlock_policy:${courseSlug}:${lessonSlug}`;
    const coursePolicyKey = storageKeys.course_debug_policy || `polyglot_debug_unlock_policy:${courseSlug}:__course__`;
    const coursePolicyPrefix = storageKeys.course_debug_policy_prefix || `polyglot_debug_unlock_policy:${courseSlug}:`;
    const stateNode = root.querySelector('[data-polyglot-debug-state]');
    const statusNode = root.querySelector('[data-polyglot-debug-status]');

    let progressApi = null;
    let courseStore = null;
    let lessonStore = null;

    function t(key, fallback = '') {
        return String(i18n[key] || fallback || key);
    }

    function toInt(value, fallback = 0) {
        const parsed = Number.parseInt(String(value ?? ''), 10);

        return Number.isInteger(parsed) ? parsed : fallback;
    }

    function clamp(value, min, max) {
        return Math.min(max, Math.max(min, value));
    }

    function input(name) {
        return root.querySelector(`[data-polyglot-debug-input="${name}"]`);
    }

    function inputNumber(name, fallback, min = 0, max = Number.MAX_SAFE_INTEGER) {
        const field = input(name);
        const parsed = toInt(field?.value, fallback);

        return clamp(parsed, min, max);
    }

    function inputChecked(name) {
        const field = input(name);

        return Boolean(field && field.checked);
    }

    function nowIso() {
        return new Date().toISOString();
    }

    function readJson(key) {
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

    function writeJson(key, value) {
        if (!key) {
            return false;
        }

        try {
            window.localStorage.setItem(key, JSON.stringify(value));

            return true;
        } catch (error) {
            return false;
        }
    }

    function removeKey(key) {
        if (!key) {
            return false;
        }

        try {
            window.localStorage.removeItem(key);

            return true;
        } catch (error) {
            return false;
        }
    }

    function keysMatching(predicate) {
        const keys = [];

        try {
            for (let index = 0; index < window.localStorage.length; index += 1) {
                const key = window.localStorage.key(index);

                if (key && predicate(key)) {
                    keys.push(key);
                }
            }
        } catch (error) {
            return [];
        }

        return keys;
    }

    function showStatus(message, tone = 'info') {
        if (!statusNode) {
            return;
        }

        const styles = {
            ok: 'border-color: #b8e3c7; background: #f0fbf4; color: #17603a;',
            warn: 'border-color: #f59b2f; background: #fff8ec; color: #7c3f00;',
            error: 'border-color: #fecaca; background: #fff5f5; color: #b42318;',
            info: 'border-color: var(--line); background: var(--surface); color: var(--text);',
        };

        statusNode.setAttribute('style', styles[tone] || styles.info);
        statusNode.textContent = message;
        statusNode.classList.remove('hidden');
        renderState();
    }

    function emitProgressEvent(kind, detail = {}) {
        if (!progressApi?.events || typeof window.CustomEvent === 'undefined') {
            return;
        }

        const name = progressApi.events[kind];
        if (!name) {
            return;
        }

        window.dispatchEvent(new CustomEvent(name, {
            detail: {
                sourceId: 'polyglot-admin-debug',
                courseSlug,
                lessonSlug,
                ...detail,
            },
        }));
    }

    function makeRollingResults(length, correctCount) {
        const normalizedLength = clamp(toInt(length, 0), 0, 100);
        const normalizedCorrect = clamp(toInt(correctCount, 0), 0, normalizedLength);
        const wrongCount = normalizedLength - normalizedCorrect;

        return [
            ...Array.from({ length: wrongCount }, () => 0),
            ...Array.from({ length: normalizedCorrect }, () => 5),
        ];
    }

    function buildLessonProgress(completed = false) {
        const answeredInput = inputNumber('answered', totalQuestions || rollingWindow, 0, 10000);
        const ratingPercentInput = inputNumber('ratingPercent', 100, 0, 100);
        const completionPercent = Math.ceil(minRating * 20);
        const effectiveRatingPercent = completed ? Math.max(ratingPercentInput, completionPercent) : ratingPercentInput;
        const rollingLength = completed
            ? Math.max(rollingWindow, answeredInput, 1)
            : Math.max(answeredInput, 0);
        const rollingCorrect = Math.round(rollingLength * effectiveRatingPercent / 100);
        const rollingResults = makeRollingResults(rollingLength, rollingCorrect);
        const totalAttempts = Math.max(answeredInput, rollingResults.length);
        const correctAttempts = completed
            ? Math.max(Math.round(totalAttempts * effectiveRatingPercent / 100), rollingCorrect)
            : Math.round(totalAttempts * ratingPercentInput / 100);

        return {
            version: 3,
            lesson_slug: lessonSlug,
            course_slug: courseSlug || null,
            current_queue_index: totalQuestions > 0 ? totalAttempts % totalQuestions : 0,
            rolling_results: rollingResults,
            total_attempts: totalAttempts,
            correct_attempts: clamp(correctAttempts, 0, totalAttempts),
            lesson_completed: completed,
            completed_at: completed ? nowIso() : null,
            last_seen_at: nowIso(),
            admin_debug: {
                rating_percent: ratingPercentInput,
                generated_at: nowIso(),
            },
        };
    }

    function writeLessonProgress(progress, reason = 'admin-debug-lesson-write') {
        let snapshot = progress;

        if (lessonStore && typeof lessonStore.write === 'function') {
            snapshot = lessonStore.write(progress, reason);
        } else {
            writeJson(lessonProgressKey, snapshot);
            emitProgressEvent('lessonProgressUpdated', {
                state: snapshot,
                reason,
            });
        }

        return snapshot;
    }

    function readLessonProgress() {
        if (lessonStore && typeof lessonStore.read === 'function') {
            return lessonStore.read();
        }

        return readJson(lessonProgressKey);
    }

    function readCourseState() {
        if (courseStore && typeof courseStore.read === 'function') {
            return courseStore.read();
        }

        return readJson(courseProgressKey) || readJson(legacyCourseProgressKey) || {
            version: 3,
            course_slug: courseSlug,
            unlocked_lessons: [],
            completed_lessons: [],
            current_lesson_slug: lessonSlug || null,
            last_opened_lesson_slug: lessonSlug || null,
            lessons: {},
            updated_at: nowIso(),
        };
    }

    function writeCourseState(state, reason = 'admin-debug-course-write') {
        const snapshot = {
            ...state,
            updated_at: nowIso(),
        };

        if (courseStore && typeof courseStore.write === 'function') {
            return courseStore.write(snapshot, reason);
        }

        writeJson(courseProgressKey, snapshot);
        removeKey(legacyCourseProgressKey);
        emitProgressEvent('courseProgressUpdated', {
            state: snapshot,
            reason,
        });

        return snapshot;
    }

    function ensureCourseEntry(state, slug) {
        if (!state.lessons || typeof state.lessons !== 'object') {
            state.lessons = {};
        }

        if (!state.lessons[slug]) {
            state.lessons[slug] = {
                unlocked: false,
                completed: false,
                has_progress: false,
                last_opened_at: null,
                updated_at: null,
            };
        }

        return state.lessons[slug];
    }

    function uniquePush(values, value) {
        const list = Array.isArray(values) ? values.slice() : [];

        if (value && !list.includes(value)) {
            list.push(value);
        }

        return list;
    }

    function removeValue(values, value) {
        return (Array.isArray(values) ? values : []).filter((item) => item !== value);
    }

    function markCurrentComplete() {
        if (!lessonSlug) {
            showStatus(t('missing_lesson_slug', 'Missing lesson slug.'), 'error');
            return;
        }

        const progress = writeLessonProgress(buildLessonProgress(true), 'admin-debug-mark-complete');

        if (courseStore && typeof courseStore.markLessonCompleted === 'function') {
            courseStore.markLessonCompleted(lessonSlug);
        } else if (courseSlug) {
            const state = readCourseState();
            const entry = ensureCourseEntry(state, lessonSlug);
            entry.completed = true;
            entry.unlocked = true;
            entry.has_progress = true;
            entry.updated_at = nowIso();
            state.completed_lessons = uniquePush(state.completed_lessons, lessonSlug);
            state.unlocked_lessons = uniquePush(state.unlocked_lessons, lessonSlug);
            writeCourseState(state, 'admin-debug-mark-complete');
        }

        showStatus(t('status_marked_complete', 'Current lesson was marked complete.'), progress.lesson_completed ? 'ok' : 'warn');
    }

    function unlockNext(reason = 'admin-debug-unlock-next') {
        if (!nextLessonSlug) {
            showStatus(t('status_final_lesson', 'This is the final lesson.'), 'warn');
            return false;
        }

        if (!courseSlug) {
            showStatus(t('missing_course_slug', 'Missing course slug.'), 'error');
            return false;
        }

        const state = readCourseState();
        const nextEntry = ensureCourseEntry(state, nextLessonSlug);
        nextEntry.unlocked = true;
        nextEntry.updated_at = nowIso();
        state.unlocked_lessons = uniquePush(state.unlocked_lessons, nextLessonSlug);
        state.current_lesson_slug = state.current_lesson_slug || nextLessonSlug;
        writeCourseState(state, reason);
        showStatus(t('status_next_unlocked', 'Next lesson was unlocked in localStorage.'), 'ok');

        return true;
    }

    function currentPolicy() {
        return {
            enabled: true,
            requiredAnswered: inputNumber('answered', totalQuestions, 0, 10000),
            requiredCorrect: inputNumber('requiredCorrect', totalQuestions, 0, 10000),
            minimumRatingPercent: inputNumber('minimumRatingPercent', 90, 0, 100),
            forceUnlockNext: inputChecked('forceUnlockNext'),
            updatedAt: nowIso(),
        };
    }

    function saveLessonPolicy() {
        const policy = currentPolicy();
        writeJson(lessonPolicyKey, policy);

        return policy;
    }

    function saveCoursePolicy() {
        const policy = {
            ...currentPolicy(),
            scope: 'course',
            lessonSlug,
        };
        writeJson(coursePolicyKey, policy);
        showStatus(t('status_course_policy_saved', 'Course debug policy was saved locally.'), 'ok');

        return policy;
    }

    function ratingPercentFromProgress(progress) {
        const rolling = Array.isArray(progress?.rolling_results) ? progress.rolling_results : [];

        if (rolling.length === 0) {
            return 0;
        }

        const total = rolling.reduce((sum, value) => sum + Number(value || 0), 0);

        return Math.round((total / rolling.length) / 5 * 100);
    }

    function correctCountFromProgress(progress) {
        const rolling = Array.isArray(progress?.rolling_results) ? progress.rolling_results : [];
        const rollingCorrect = rolling.filter((value) => Number(value) >= 4.5).length;

        return Math.max(toInt(progress?.correct_attempts, 0), rollingCorrect);
    }

    function answeredCountFromProgress(progress) {
        const rolling = Array.isArray(progress?.rolling_results) ? progress.rolling_results : [];

        return Math.max(toInt(progress?.total_attempts, 0), rolling.length);
    }

    function applyLessonPolicy() {
        const policy = saveLessonPolicy();
        const progress = readLessonProgress() || {};
        const answered = answeredCountFromProgress(progress);
        const correct = correctCountFromProgress(progress);
        const ratingPercent = ratingPercentFromProgress(progress);
        const passes = policy.forceUnlockNext
            || (
                answered >= policy.requiredAnswered
                && correct >= policy.requiredCorrect
                && ratingPercent >= policy.minimumRatingPercent
            );

        if (!passes) {
            showStatus(t('status_policy_failed', 'Policy did not pass for current local progress.'), 'warn');
            return;
        }

        unlockNext('admin-debug-policy-unlock-next');
    }

    function simulateProgress() {
        writeLessonProgress(buildLessonProgress(false), 'admin-debug-simulate-progress');
        showStatus(t('status_progress_simulated', 'Lesson progress was simulated locally.'), 'ok');
    }

    function resetCurrentLesson() {
        removeKey(lessonProgressKey);

        const state = readCourseState();
        const entry = ensureCourseEntry(state, lessonSlug);
        entry.completed = false;
        entry.has_progress = false;
        entry.last_opened_at = null;
        entry.updated_at = nowIso();
        state.completed_lessons = removeValue(state.completed_lessons, lessonSlug);
        state.current_lesson_slug = lessonSlug || state.current_lesson_slug;
        writeCourseState(state, 'admin-debug-reset-current-lesson');

        if (inputChecked('clearPolicyOnReset')) {
            removeKey(lessonPolicyKey);
        }

        emitProgressEvent('lessonProgressUpdated', {
            state: null,
            reason: 'admin-debug-reset-current-lesson',
        });
        showStatus(t('status_current_lesson_reset', 'Current lesson progress was reset locally.'), 'ok');
    }

    function resetCurrentCompletion() {
        const progress = readLessonProgress() || buildLessonProgress(false);
        const rolling = Array.isArray(progress.rolling_results) ? progress.rolling_results : [];
        const trimmedRolling = rolling.length >= rollingWindow ? rolling.slice(-(rollingWindow - 1)) : rolling;
        const snapshot = {
            ...progress,
            rolling_results: trimmedRolling,
            total_attempts: Math.max(toInt(progress.total_attempts, 0), trimmedRolling.length),
            lesson_completed: false,
            completed_at: null,
            last_seen_at: nowIso(),
        };

        writeLessonProgress(snapshot, 'admin-debug-reset-current-completion');

        const state = readCourseState();
        const entry = ensureCourseEntry(state, lessonSlug);
        entry.completed = false;
        entry.updated_at = nowIso();
        state.completed_lessons = removeValue(state.completed_lessons, lessonSlug);
        writeCourseState(state, 'admin-debug-reset-current-completion');
        showStatus(t('status_current_completion_reset', 'Current lesson completion flag was reset locally.'), 'ok');
    }

    function resetNextUnlock() {
        if (!nextLessonSlug) {
            showStatus(t('status_final_lesson', 'This is the final lesson.'), 'warn');
            return;
        }

        const state = readCourseState();
        const nextEntry = ensureCourseEntry(state, nextLessonSlug);
        nextEntry.unlocked = false;
        nextEntry.updated_at = nowIso();
        state.unlocked_lessons = removeValue(state.unlocked_lessons, nextLessonSlug);

        if (state.current_lesson_slug === nextLessonSlug) {
            state.current_lesson_slug = lessonSlug || null;
        }

        if (inputChecked('removeNextProgress')) {
            removeKey(`polyglot_progress:${nextLessonSlug}`);
        }

        writeCourseState(state, 'admin-debug-reset-next-unlock');
        showStatus(t('status_next_unlock_reset', 'Next lesson unlock flag was reset locally.'), 'ok');
    }

    function resetCourseProgress() {
        const allCourses = inputChecked('allPolyglotCourses');
        const confirmMessage = allCourses
            ? t('confirm_all_progress_reset', 'Clear all Polyglot progress and debug keys in this browser?')
            : t('confirm_course_reset', 'Clear progress for this course in this browser?');

        if (!window.confirm(confirmMessage)) {
            return;
        }

        if (allCourses) {
            keysMatching((key) => (
                key.startsWith('polyglot_progress:')
                || key.startsWith('polyglot_course_progress:')
                || key.startsWith('polyglot_course_state:')
                || key.startsWith('polyglot_debug_')
            )).forEach(removeKey);
            showStatus(t('status_all_progress_reset', 'All Polyglot progress/debug keys were cleared locally.'), 'ok');
            return;
        }

        removeKey(courseProgressKey);
        removeKey(legacyCourseProgressKey);
        lessons.forEach((item) => {
            if (item?.slug) {
                removeKey(`polyglot_progress:${item.slug}`);
            }
        });
        keysMatching((key) => key.startsWith(coursePolicyPrefix)).forEach(removeKey);
        emitProgressEvent('courseReset', {
            state: null,
        });
        showStatus(t('status_course_progress_reset', 'Current course progress/debug keys were cleared locally.'), 'ok');
    }

    function clearDebugOverrides() {
        keysMatching((key) => key.startsWith(coursePolicyPrefix)).forEach(removeKey);
        showStatus(t('status_debug_overrides_cleared', 'Course debug unlock overrides were cleared locally.'), 'ok');
    }

    function clearDebugKeys() {
        keysMatching((key) => key.startsWith('polyglot_debug_')).forEach(removeKey);
        showStatus(t('status_debug_keys_cleared', 'All Polyglot debug keys were cleared locally.'), 'ok');
    }

    function renderState() {
        if (!stateNode) {
            return;
        }

        const snapshot = {
            lessonProgressKey,
            lessonProgress: readJson(lessonProgressKey),
            courseProgressKey,
            courseProgress: readJson(courseProgressKey),
            legacyCourseProgressKey,
            legacyCourseProgress: readJson(legacyCourseProgressKey),
            lessonPolicyKey,
            lessonPolicy: readJson(lessonPolicyKey),
            coursePolicyKey,
            coursePolicy: readJson(coursePolicyKey),
            nextLessonSlug: nextLessonSlug || null,
        };

        stateNode.textContent = JSON.stringify(snapshot, null, 2);
    }

    function handleAction(action) {
        if (action === 'simulate-progress') {
            simulateProgress();
        } else if (action === 'mark-complete') {
            markCurrentComplete();
        } else if (action === 'unlock-next') {
            unlockNext();
        } else if (action === 'apply-lesson-policy') {
            applyLessonPolicy();
        } else if (action === 'apply-course-policy') {
            saveCoursePolicy();
        } else if (action === 'clear-debug-overrides') {
            clearDebugOverrides();
        } else if (action === 'reset-current-lesson') {
            resetCurrentLesson();
        } else if (action === 'reset-current-completion') {
            resetCurrentCompletion();
        } else if (action === 'reset-next-unlock') {
            resetNextUnlock();
        } else if (action === 'reset-course-progress') {
            resetCourseProgress();
        } else if (action === 'clear-debug-keys') {
            clearDebugKeys();
        } else if (action === 'refresh-state') {
            renderState();
            showStatus(t('status_state_refreshed', 'Debug state was refreshed.'), 'info');
        }
    }

    function boot(api) {
        progressApi = api || window.PolyglotCourseProgress || null;

        if (progressApi && courseSlug && typeof progressApi.createCourseStore === 'function') {
            courseStore = progressApi.createCourseStore(courseSlug, lessons, {
                sourceId: 'polyglot-admin-debug-course',
            });
        }

        if (progressApi && lessonSlug && typeof progressApi.createLessonStore === 'function') {
            lessonStore = progressApi.createLessonStore({
                lessonSlug,
                courseSlug,
                courseStore,
                questionCount: totalQuestions,
                rollingWindow,
                minRating,
                sourceId: 'polyglot-admin-debug-lesson',
            });
        }

        renderState();
    }

    root.addEventListener('click', (event) => {
        const button = event.target.closest('[data-polyglot-debug-action]');

        if (!button || !root.contains(button)) {
            return;
        }

        handleAction(button.getAttribute('data-polyglot-debug-action'));
    });

    root.addEventListener('input', () => renderState());
    root.addEventListener('change', () => renderState());

    if (window.PolyglotCourseProgress) {
        boot(window.PolyglotCourseProgress);
    } else {
        window.addEventListener('polyglot:progress-ready', (event) => boot(event.detail?.api), { once: true });
    }
})();
