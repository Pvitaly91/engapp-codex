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
    const questionStatsKey = `polyglot_debug_question_stats:${courseSlug}:${lessonSlug}`;
    const stateNode = root.querySelector('[data-polyglot-debug-state]');
    const statusNode = root.querySelector('[data-polyglot-debug-status]');
    const progressSync = window.__POLYGLOT_PROGRESS_SYNC__ || {};
    const debugUrl = String(progressSync.debugUrl || '').trim();
    const csrfToken = String(progressSync.csrfToken || '').trim();

    let progressApi = null;
    let courseStore = null;
    let lessonStore = null;
    let lastServerResponse = null;

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

    function answeredCountFromPercent(percent) {
        const questionCount = Math.max(1, totalQuestions || rollingWindow);

        return percent <= 0 ? 0 : Math.ceil(questionCount * percent / 100);
    }

    function correctCountFromPercent(answeredCount, percent) {
        return percent <= 0 ? 0 : Math.ceil(Math.max(0, answeredCount) * percent / 100);
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

    function formatPreview(template, count, total) {
        return String(template || '')
            .replace(':count', String(count))
            .replace(':total', String(total));
    }

    function renderPreviews() {
        const answeredPercent = inputNumber('answeredPercent', 100, 0, 100);
        const requiredCorrectPercent = inputNumber('requiredCorrectPercent', 100, 0, 100);
        const answered = answeredCountFromPercent(answeredPercent);
        const correct = correctCountFromPercent(answered, requiredCorrectPercent);
        const total = totalQuestions > 0 ? totalQuestions : 0;
        const answeredPreview = root.querySelector('[data-polyglot-debug-preview="answeredPercent"]');
        const correctPreview = root.querySelector('[data-polyglot-debug-preview="requiredCorrectPercent"]');

        if (answeredPreview) {
            answeredPreview.textContent = total > 0
                ? formatPreview(t('percent_count_preview', '≈ :count of :total'), answered, total)
                : formatPreview(t('percent_count_preview_unknown', '≈ :count questions'), answered);
        }

        if (correctPreview) {
            correctPreview.textContent = answered > 0
                ? formatPreview(t('percent_count_preview', '≈ :count of :total'), correct, answered)
                : formatPreview(t('percent_count_preview_unknown', '≈ :count questions'), correct);
        }
    }

    function animateActionButton(button) {
        if (!button) {
            return;
        }

        button.classList.remove('polyglot-debug-action-pressed', 'polyglot-debug-action-working');

        // Force a reflow so repeated fast clicks replay the press feedback.
        void button.offsetWidth;

        button.classList.add('polyglot-debug-action-pressed', 'polyglot-debug-action-working');
        window.setTimeout(() => button.classList.remove('polyglot-debug-action-pressed'), 180);
        window.setTimeout(() => button.classList.remove('polyglot-debug-action-working'), 560);
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

    function cookieValue(name) {
        if (typeof document === 'undefined' || typeof document.cookie !== 'string') {
            return '';
        }

        const prefix = `${name}=`;
        const item = document.cookie
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

    function metaCsrfToken() {
        return String(document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '').trim();
    }

    function serverHeaders() {
        const headers = {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        };
        const xsrfToken = cookieValue('XSRF-TOKEN');
        const embeddedToken = String(csrfToken || metaCsrfToken()).trim();

        if (xsrfToken !== '') {
            headers['X-XSRF-TOKEN'] = xsrfToken;
        } else if (embeddedToken !== '') {
            headers['X-CSRF-TOKEN'] = embeddedToken;
        }

        return headers;
    }

    function serverPayload(action, extra = {}) {
        const answeredPercent = inputNumber('answeredPercent', 100, 0, 100);
        const requiredCorrectPercent = inputNumber('requiredCorrectPercent', 100, 0, 100);
        const answered = answeredCountFromPercent(answeredPercent);
        const requiredCorrect = correctCountFromPercent(answered, requiredCorrectPercent);

        return {
            action,
            lesson_slug: lessonSlug || null,
            answered,
            rating_percent: inputNumber('ratingPercent', 100, 0, 100),
            required_answered_percent: answeredPercent,
            required_correct_percent: requiredCorrectPercent,
            required_answered: answered,
            required_correct: requiredCorrect,
            minimum_rating_percent: inputNumber('minimumRatingPercent', 90, 0, 100),
            force_unlock_next: inputChecked('forceUnlockNext'),
            clear_policy: inputChecked('clearPolicyOnReset'),
            remove_next_progress: inputChecked('removeNextProgress'),
            all_courses: inputChecked('allPolyglotCourses'),
            ...extra,
        };
    }

    function applyServerResponse(payload, reason) {
        if (!payload || typeof payload !== 'object') {
            return;
        }

        lastServerResponse = payload;

        if (payload.course_progress) {
            emitProgressEvent('courseProgressUpdated', {
                state: payload.course_progress,
                serverCourseProgress: payload.course_progress,
                serverLessonProgress: payload.lesson_progress || null,
                reason,
            });
        }

        if (payload.lesson_progress) {
            emitProgressEvent('lessonProgressUpdated', {
                state: payload.lesson_progress,
                serverCourseProgress: payload.course_progress || null,
                serverLessonProgress: payload.lesson_progress,
                reason,
            });
        }

        renderState();
    }

    function serverStatus(payload, fallback) {
        const key = payload?.message_key ? `status_${payload.message_key}` : '';

        return t(key, fallback);
    }

    async function postServerAction(action, extra = {}) {
        if (!debugUrl) {
            return null;
        }

        const response = await fetch(debugUrl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: serverHeaders(),
            body: JSON.stringify(serverPayload(action, extra)),
        });
        const payload = await response.json().catch(() => null);

        if (!response.ok || !payload?.authenticated) {
            const message = payload?.message || payload?.errors?.debug?.[0] || t('status_server_action_failed', 'Server debug action failed.');
            throw new Error(message);
        }

        applyServerResponse(payload, `server-${action}`);
        showStatus(
            serverStatus(payload, payload.applied ? 'Server debug action applied.' : 'Server debug action did not apply.'),
            payload.applied ? 'ok' : 'warn'
        );

        return payload;
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
        const answeredInput = answeredCountFromPercent(inputNumber('answeredPercent', 100, 0, 100));
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

    function nextLessonUrl() {
        if (!nextLessonSlug) {
            return '';
        }

        const currentUrl = String(window.location.href || '');
        const currentSegment = `/test/${lessonSlug}/`;
        const nextSegment = `/test/${nextLessonSlug}/`;

        if (lessonSlug && currentUrl.includes(currentSegment)) {
            return currentUrl.replace(currentSegment, nextSegment);
        }

        return `/test/${encodeURIComponent(nextLessonSlug)}/step/compose`;
    }

    function offerNextLesson() {
        const url = nextLessonUrl();

        if (!url) {
            return;
        }

        if (window.confirm(t('confirm_go_next_lesson', 'Next lesson was unlocked. Go to it now?'))) {
            window.location.assign(url);
        }
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
        if (progress.lesson_completed) {
            offerNextLesson();
        }
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
        offerNextLesson();

        return true;
    }

    function currentPolicy() {
        const requiredAnsweredPercent = inputNumber('answeredPercent', 100, 0, 100);
        const requiredCorrectPercent = inputNumber('requiredCorrectPercent', 100, 0, 100);
        const requiredAnswered = answeredCountFromPercent(requiredAnsweredPercent);

        return {
            enabled: true,
            requiredAnswered,
            requiredCorrect: correctCountFromPercent(requiredAnswered, requiredCorrectPercent),
            requiredAnsweredPercent: requiredAnsweredPercent,
            requiredCorrectPercent: requiredCorrectPercent,
            required_answered: requiredAnswered,
            required_correct: correctCountFromPercent(requiredAnswered, requiredCorrectPercent),
            required_answered_percent: requiredAnsweredPercent,
            required_correct_percent: requiredCorrectPercent,
            minimumRatingPercent: inputNumber('minimumRatingPercent', 90, 0, 100),
            minimum_rating_percent: inputNumber('minimumRatingPercent', 90, 0, 100),
            forceUnlockNext: inputChecked('forceUnlockNext'),
            force_unlock_next: inputChecked('forceUnlockNext'),
            updatedAt: nowIso(),
        };
    }

    function saveLessonPolicy() {
        const policy = currentPolicy();
        writeJson(lessonPolicyKey, policy);

        return policy;
    }

    function saveCoursePolicy(showMessage = true) {
        const policy = {
            ...currentPolicy(),
            scope: 'course',
            lessonSlug,
        };
        writeJson(coursePolicyKey, policy);
        if (showMessage) {
            showStatus(t('status_course_policy_saved', 'Course debug policy was saved.'), 'ok');
        }

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
        removeKey(questionStatsKey);

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

    function buildIncompleteCurrentProgress(progress) {
        const rolling = Array.isArray(progress.rolling_results) ? progress.rolling_results : [];
        const keepCount = Math.max(0, rollingWindow - 1);
        const trimmedRolling = rolling.length >= rollingWindow
            ? (keepCount > 0 ? rolling.slice(-keepCount) : [])
            : rolling;
        const totalAttempts = Math.max(toInt(progress.total_attempts, 0), trimmedRolling.length);

        return {
            ...progress,
            rolling_results: trimmedRolling,
            total_attempts: totalAttempts,
            correct_attempts: clamp(toInt(progress.correct_attempts, 0), 0, totalAttempts),
            lesson_completed: false,
            completed_at: null,
            last_seen_at: nowIso(),
        };
    }

    function buildZeroCurrentProgress() {
        return {
            version: 3,
            lesson_slug: lessonSlug,
            course_slug: courseSlug || null,
            current_queue_index: 0,
            rolling_results: [],
            total_attempts: 0,
            correct_attempts: 0,
            lesson_completed: false,
            completed_at: null,
            last_seen_at: nowIso(),
        };
    }

    function clearLessonCompletionOnly(targetLessonSlug) {
        if (!targetLessonSlug) {
            return null;
        }

        const key = `polyglot_progress:${targetLessonSlug}`;
        const raw = readJson(key);

        if (!raw || typeof raw !== 'object') {
            return null;
        }

        const rolling = Array.isArray(raw.rolling_results) ? raw.rolling_results : [];
        const keepCount = Math.max(0, rollingWindow - 1);
        const trimmedRolling = rolling.length >= rollingWindow
            ? (keepCount > 0 ? rolling.slice(-keepCount) : [])
            : rolling;
        const totalAttempts = Math.max(toInt(raw.total_attempts, 0), trimmedRolling.length);
        const snapshot = {
            ...raw,
            rolling_results: trimmedRolling,
            total_attempts: totalAttempts,
            correct_attempts: clamp(toInt(raw.correct_attempts, 0), 0, totalAttempts),
            lesson_completed: false,
            completed_at: null,
            last_seen_at: nowIso(),
        };

        writeJson(key, snapshot);
        emitProgressEvent('lessonProgressUpdated', {
            lessonSlug: targetLessonSlug,
            state: snapshot,
            reason: 'admin-debug-force-incomplete-next',
        });

        return snapshot;
    }

    function clearCurrentCompletion(reason = 'admin-debug-reset-current-completion') {
        const currentProgress = readLessonProgress();
        const snapshot = currentProgress
            ? buildIncompleteCurrentProgress(currentProgress)
            : buildZeroCurrentProgress();

        writeLessonProgress(snapshot, reason);

        const state = readCourseState();
        const entry = ensureCourseEntry(state, lessonSlug);
        entry.completed = false;
        entry.updated_at = nowIso();
        state.completed_lessons = removeValue(state.completed_lessons, lessonSlug);
        writeCourseState(state, reason);

        return snapshot;
    }

    function resetCurrentCompletion() {
        clearCurrentCompletion('admin-debug-reset-current-completion');
        showStatus(t('status_current_completion_reset', 'Current lesson completion flag was reset locally.'), 'ok');
    }

    function resetNextUnlock() {
        if (!nextLessonSlug) {
            showStatus(t('status_final_lesson', 'This is the final lesson.'), 'warn');
            return;
        }

        clearCurrentCompletion('admin-debug-reset-next-unlock-current-completion');

        const state = readCourseState();
        const currentEntry = ensureCourseEntry(state, lessonSlug);
        const nextEntry = ensureCourseEntry(state, nextLessonSlug);
        currentEntry.completed = false;
        currentEntry.updated_at = nowIso();
        nextEntry.unlocked = false;
        nextEntry.completed = false;
        nextEntry.updated_at = nowIso();
        state.completed_lessons = removeValue(state.completed_lessons, lessonSlug);
        state.completed_lessons = removeValue(state.completed_lessons, nextLessonSlug);
        state.unlocked_lessons = removeValue(state.unlocked_lessons, nextLessonSlug);

        if (state.current_lesson_slug === nextLessonSlug) {
            state.current_lesson_slug = lessonSlug || null;
        }

        if (inputChecked('removeNextProgress')) {
            removeKey(`polyglot_progress:${nextLessonSlug}`);
            nextEntry.has_progress = false;
            nextEntry.last_opened_at = null;
        } else {
            clearLessonCompletionOnly(nextLessonSlug);
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
                removeKey(`polyglot_debug_question_stats:${courseSlug}:${item.slug}`);
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

    function clearCourseLocalKeys(allCourses = false) {
        if (allCourses) {
            keysMatching((key) => (
                key.startsWith('polyglot_progress:')
                || key.startsWith('polyglot_course_progress:')
                || key.startsWith('polyglot_course_state:')
                || key.startsWith('polyglot_debug_')
            )).forEach(removeKey);
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
    }

    function cleanupLocalAfterServerAction(action) {
        if (action === 'reset-current-lesson') {
            removeKey(lessonProgressKey);
            removeKey(questionStatsKey);
            const state = readCourseState();
            const entry = ensureCourseEntry(state, lessonSlug);
            entry.completed = false;
            entry.has_progress = false;
            entry.last_opened_at = null;
            state.completed_lessons = removeValue(state.completed_lessons, lessonSlug);
            state.current_lesson_slug = lessonSlug || state.current_lesson_slug;
            writeCourseState(state, 'server-debug-reset-current-lesson');
            if (inputChecked('clearPolicyOnReset')) {
                removeKey(lessonPolicyKey);
            }
        } else if (action === 'reset-next-unlock') {
            clearCurrentCompletion('server-debug-reset-next-unlock-current-completion');
            const state = readCourseState();
            const currentEntry = ensureCourseEntry(state, lessonSlug);
            const nextEntry = ensureCourseEntry(state, nextLessonSlug);
            currentEntry.completed = false;
            nextEntry.unlocked = false;
            nextEntry.completed = false;
            state.completed_lessons = removeValue(removeValue(state.completed_lessons, lessonSlug), nextLessonSlug);
            state.unlocked_lessons = removeValue(state.unlocked_lessons, nextLessonSlug);
            if (state.current_lesson_slug === nextLessonSlug) {
                state.current_lesson_slug = lessonSlug || null;
            }
            if (inputChecked('removeNextProgress')) {
                removeKey(`polyglot_progress:${nextLessonSlug}`);
                nextEntry.has_progress = false;
                nextEntry.last_opened_at = null;
            } else {
                clearLessonCompletionOnly(nextLessonSlug);
            }
            writeCourseState(state, 'server-debug-reset-next-unlock');
        } else if (action === 'reset-course-progress') {
            clearCourseLocalKeys(inputChecked('allPolyglotCourses'));
            emitProgressEvent('courseReset', {
                state: null,
                reason: 'server-debug-reset-course-progress',
            });
        } else if (action === 'clear-debug-overrides') {
            keysMatching((key) => key.startsWith(coursePolicyPrefix)).forEach(removeKey);
        } else if (action === 'apply-lesson-policy') {
            saveLessonPolicy();
        } else if (action === 'apply-course-policy') {
            saveCoursePolicy(false);
        }
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
            questionStatsKey,
            questionStats: readJson(questionStatsKey),
            serverDebugUrl: debugUrl || null,
            lastServerResponse,
        };

        stateNode.textContent = JSON.stringify(snapshot, null, 2);
        renderQuestionStats();
        renderPreviews();
    }

    function findQuestionStats(stats, row) {
        const uuid = String(row.getAttribute('data-polyglot-debug-question-uuid') || '').trim();
        const id = String(row.getAttribute('data-polyglot-debug-question-id') || '').trim();
        const position = String(row.getAttribute('data-polyglot-debug-question-position') || '').trim();
        const keys = [
            position !== '' ? `position:${position}` : '',
            uuid,
            id !== '' ? `id:${id}` : '',
        ].filter(Boolean);

        for (const key of keys) {
            if (stats[key]) {
                return stats[key];
            }
        }

        return Object.values(stats).find((item) => {
            const itemPosition = String(item?.position ?? '').trim();
            const itemUuid = String(item?.uuid ?? '').trim();
            const itemId = String(item?.id ?? '').trim();

            return (position !== '' && itemPosition === position)
                || (uuid !== '' && itemUuid === uuid)
                || (id !== '' && itemId === id);
        }) || null;
    }

    function renderQuestionStats() {
        const stats = readJson(questionStatsKey) || {};

        root.querySelectorAll('[data-polyglot-debug-question-row]').forEach((row) => {
            const node = row.querySelector('[data-polyglot-debug-question-stats]');
            const item = findQuestionStats(stats, row);

            if (!node) {
                return;
            }

            if (!item || toInt(item.shown, 0) <= 0) {
                node.textContent = t('question_not_seen', 'Not shown yet');
                node.setAttribute('style', 'border-color: var(--line); color: var(--muted);');
                return;
            }

            node.textContent = t('question_stats_template', 'Shown: :shown, correct: :correct, wrong: :incorrect')
                .replace(':shown', String(toInt(item.shown, 0)))
                .replace(':correct', String(toInt(item.correct, 0)))
                .replace(':incorrect', String(toInt(item.incorrect, 0)));
            node.setAttribute('style', 'border-color: #b8e3c7; background: #f0fbf4; color: #17603a;');
        });
    }

    async function handleAction(action) {
        const serverActions = new Set([
            'simulate-progress',
            'mark-complete',
            'unlock-next',
            'apply-lesson-policy',
            'apply-course-policy',
            'clear-debug-overrides',
            'reset-current-lesson',
            'reset-current-completion',
            'reset-next-unlock',
            'reset-course-progress',
        ]);

        if (debugUrl && serverActions.has(action)) {
            if (action === 'reset-course-progress') {
                const allCourses = inputChecked('allPolyglotCourses');
                const confirmMessage = allCourses
                    ? t('confirm_all_progress_reset', 'Clear all Polyglot progress/debug keys?')
                    : t('confirm_course_reset', 'Clear progress for this course?');

                if (!window.confirm(confirmMessage)) {
                    return;
                }
            }

            try {
                const responsePayload = await postServerAction(action);
                cleanupLocalAfterServerAction(action);
                if (responsePayload?.applied && ['mark-complete', 'unlock-next', 'apply-lesson-policy', 'apply-course-policy'].includes(action)) {
                    offerNextLesson();
                }
                renderState();
            } catch (error) {
                showStatus(error?.message || t('status_server_action_failed', 'Server debug action failed.'), 'error');
            }

            return;
        }

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

        animateActionButton(button);
        handleAction(button.getAttribute('data-polyglot-debug-action'));
    });

    root.addEventListener('input', () => renderState());
    root.addEventListener('change', () => renderState());

    window.addEventListener('polyglot:admin-debug-question-stats-updated', (event) => {
        if (event.detail?.key === questionStatsKey) {
            renderQuestionStats();
            renderState();
        }
    });

    if (window.PolyglotCourseProgress) {
        boot(window.PolyglotCourseProgress);
    } else {
        window.addEventListener('polyglot:progress-ready', (event) => boot(event.detail?.api), { once: true });
    }
})();
