(function () {
    const root = document.querySelector('[data-polyglot-course-admin-debug="1"]');
    const payload = window.__POLYGLOT_COURSE_ADMIN_DEBUG__;

    if (!root || !payload || typeof payload !== 'object') {
        return;
    }

    const i18n = window.__POLYGLOT_COURSE_ADMIN_DEBUG_I18N__ || {};
    const course = payload.course || {};
    const lessons = Array.isArray(payload.lessons) ? payload.lessons : [];
    const storageKeys = payload.storage_keys || {};
    const courseSlug = String(course.slug || root.dataset.polyglotDebugCourseSlug || '').trim();
    const firstLessonSlug = String(root.dataset.polyglotDebugFirstLessonSlug || lessons[0]?.slug || '').trim();
    const courseProgressKey = storageKeys.course_progress || `polyglot_course_progress:${courseSlug}`;
    const legacyCourseProgressKey = storageKeys.legacy_course_progress || `polyglot_course_state:${courseSlug}`;
    const coursePolicyKey = `${storageKeys.course_debug_policy_prefix || `polyglot_debug_unlock_policy:${courseSlug}:`}__course__`;
    const coursePolicyPrefix = storageKeys.course_debug_policy_prefix || `polyglot_debug_unlock_policy:${courseSlug}:`;
    const progressSync = window.__POLYGLOT_PROGRESS_SYNC__ || {};
    const progressUrl = String(progressSync.progressUrl || '').trim();
    const debugUrl = String(progressSync.debugUrl || payload.debug_endpoint || '').trim();
    const csrfToken = String(progressSync.csrfToken || '').trim();
    const stateNode = root.querySelector('[data-polyglot-course-debug-state]');
    const statusNode = root.querySelector('[data-polyglot-course-debug-status]');
    const defaultPolicy = {
        enabled: true,
        scope: 'course',
        required_answered_percent: toInt(payload.completion_defaults?.required_answered_percent, 100),
        required_correct_percent: toInt(payload.completion_defaults?.required_correct_percent, 90),
        minimum_rating_percent: toInt(payload.completion_defaults?.minimum_rating_percent, 90),
        force_unlock_next: false,
    };

    let progressApi = null;
    let courseStore = null;
    let lastServerResponse = null;
    let lastServerProgress = null;

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
        return root.querySelector(`[data-polyglot-course-debug-input="${name}"]`);
    }

    function inputNumber(name, fallback, min = 0, max = Number.MAX_SAFE_INTEGER) {
        const parsed = toInt(input(name)?.value, fallback);

        return clamp(parsed, min, max);
    }

    function lessonQuestionCount(lesson = null) {
        const normalizedLesson = lesson || lessons[0] || {};

        return Math.max(1, toInt(normalizedLesson.question_count, 100));
    }

    function answeredCountFromPercent(percent, lesson = null) {
        const questionCount = lessonQuestionCount(lesson);

        return percent <= 0 ? 0 : Math.ceil(questionCount * percent / 100);
    }

    function correctCountFromPercent(answeredCount, percent) {
        return percent <= 0 ? 0 : Math.ceil(Math.max(0, answeredCount) * percent / 100);
    }

    function inputChecked(name) {
        const field = input(name);

        return Boolean(field && field.checked);
    }

    function setInputValue(name, value) {
        const field = input(name);

        if (field) {
            field.value = String(value);
        }
    }

    function setInputChecked(name, value) {
        const field = input(name);

        if (field) {
            field.checked = Boolean(value);
        }
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

    function animateActionButton(button) {
        if (!button) {
            return;
        }

        button.classList.remove('polyglot-debug-action-pressed', 'polyglot-debug-action-working');
        void button.offsetWidth;
        button.classList.add('polyglot-debug-action-pressed', 'polyglot-debug-action-working');
        window.setTimeout(() => button.classList.remove('polyglot-debug-action-pressed'), 180);
        window.setTimeout(() => button.classList.remove('polyglot-debug-action-working'), 560);
    }

    function currentPolicy() {
        const requiredAnsweredPercent = inputNumber('requiredAnsweredPercent', defaultPolicy.required_answered_percent, 0, 100);
        const requiredCorrectPercent = inputNumber('requiredCorrectPercent', defaultPolicy.required_correct_percent, 0, 100);
        const requiredAnswered = answeredCountFromPercent(requiredAnsweredPercent);
        const minimumRatingPercent = inputNumber('minimumRatingPercent', defaultPolicy.minimum_rating_percent, 0, 100);

        return {
            enabled: true,
            scope: 'course',
            required_answered_percent: requiredAnsweredPercent,
            required_correct_percent: requiredCorrectPercent,
            required_answered: requiredAnswered,
            required_correct: correctCountFromPercent(requiredAnswered, requiredCorrectPercent),
            minimum_rating_percent: minimumRatingPercent,
            force_unlock_next: inputChecked('forceUnlockNext'),
            updated_at: nowIso(),
        };
    }

    function normalizePolicy(policy) {
        if (!policy || typeof policy !== 'object') {
            return null;
        }

        const fallbackQuestionCount = lessonQuestionCount();
        const requiredAnswered = clamp(toInt(policy.required_answered ?? policy.requiredAnswered, answeredCountFromPercent(defaultPolicy.required_answered_percent)), 0, 10000);
        const requiredAnsweredPercent = clamp(toInt(
            policy.required_answered_percent ?? policy.requiredAnsweredPercent,
            Math.round(requiredAnswered / fallbackQuestionCount * 100)
        ), 0, 100);
        const requiredCorrect = clamp(toInt(policy.required_correct ?? policy.requiredCorrect, correctCountFromPercent(requiredAnswered, defaultPolicy.required_correct_percent)), 0, 10000);
        const requiredCorrectPercent = clamp(toInt(
            policy.required_correct_percent ?? policy.requiredCorrectPercent,
            requiredAnswered > 0 ? Math.round(requiredCorrect / requiredAnswered * 100) : defaultPolicy.required_correct_percent
        ), 0, 100);
        const minimumRatingPercent = clamp(toInt(policy.minimum_rating_percent ?? policy.minimumRatingPercent, defaultPolicy.minimum_rating_percent), 0, 100);
        const normalizedAnswered = answeredCountFromPercent(requiredAnsweredPercent);

        return {
            enabled: true,
            scope: 'course',
            required_answered_percent: requiredAnsweredPercent,
            required_correct_percent: requiredCorrectPercent,
            required_answered: normalizedAnswered,
            required_correct: correctCountFromPercent(normalizedAnswered, requiredCorrectPercent),
            minimum_rating_percent: minimumRatingPercent,
            force_unlock_next: Boolean(policy.force_unlock_next ?? policy.forceUnlockNext),
            updated_at: String(policy.updated_at || nowIso()),
        };
    }

    function applyPolicyToInputs(policy) {
        const normalized = normalizePolicy(policy);

        if (!normalized) {
            return null;
        }

        setInputValue('requiredAnsweredPercent', normalized.required_answered_percent);
        setInputValue('requiredCorrectPercent', normalized.required_correct_percent);
        setInputValue('minimumRatingPercent', normalized.minimum_rating_percent);
        setInputChecked('forceUnlockNext', normalized.force_unlock_next);

        return normalized;
    }

    function resetPolicyInputs() {
        applyPolicyToInputs(defaultPolicy);
    }

    function restoreLocalCoursePolicy() {
        return applyPolicyToInputs(readJson(coursePolicyKey));
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

    function serverPayload(action) {
        const policy = currentPolicy();

        return {
            action,
            lesson_slug: firstLessonSlug || null,
            required_answered_percent: policy.required_answered_percent,
            required_correct_percent: policy.required_correct_percent,
            required_answered: policy.required_answered,
            required_correct: policy.required_correct,
            minimum_rating_percent: policy.minimum_rating_percent,
            force_unlock_next: policy.force_unlock_next,
            all_courses: false,
        };
    }

    function emitCourseProgress(state, reason) {
        const name = progressApi?.events?.courseProgressUpdated;

        if (!name || typeof window.CustomEvent === 'undefined') {
            return;
        }

        window.dispatchEvent(new CustomEvent(name, {
            detail: {
                sourceId: 'polyglot-course-admin-debug',
                courseSlug,
                state,
                serverCourseProgress: state,
                reason,
            },
        }));
    }

    function saveLocalCoursePolicy(policy = null) {
        const normalized = normalizePolicy(policy || currentPolicy());

        if (!normalized) {
            return null;
        }

        writeJson(coursePolicyKey, normalized);

        return normalized;
    }

    function clearLocalCourseKeys() {
        removeKey(courseProgressKey);
        removeKey(legacyCourseProgressKey);
        lessons.forEach((lesson) => {
            if (lesson?.slug) {
                removeKey(`polyglot_progress:${lesson.slug}`);
            }
        });
        keysMatching((key) => key.startsWith(coursePolicyPrefix)).forEach(removeKey);
    }

    function clearLocalDebugPolicies() {
        keysMatching((key) => key.startsWith(coursePolicyPrefix)).forEach(removeKey);
    }

    function applyServerResponse(responsePayload, reason) {
        if (!responsePayload || typeof responsePayload !== 'object') {
            return;
        }

        lastServerResponse = responsePayload;

        if (responsePayload.course_progress) {
            lastServerProgress = responsePayload.course_progress;
            emitCourseProgress(responsePayload.course_progress, reason);
        }

        renderState();
    }

    function serverStatus(responsePayload, fallback) {
        const key = responsePayload?.message_key ? `status_${responsePayload.message_key}` : '';

        return t(key, fallback);
    }

    async function fetchServerProgress() {
        if (!progressUrl) {
            renderState();
            return null;
        }

        const response = await fetch(progressUrl, {
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json',
            },
        });
        const responsePayload = await response.json().catch(() => null);

        if (!response.ok || !responsePayload?.authenticated) {
            lastServerProgress = null;
            renderState();
            return null;
        }

        lastServerProgress = responsePayload.progress || null;

        if (lastServerProgress) {
            emitCourseProgress(lastServerProgress, 'course-admin-debug-refresh');
        }

        renderState();

        return lastServerProgress;
    }

    async function postServerAction(action) {
        if (!debugUrl) {
            throw new Error(t('status_server_action_failed', 'Server debug action failed.'));
        }

        const response = await fetch(debugUrl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: serverHeaders(),
            body: JSON.stringify(serverPayload(action)),
        });
        const responsePayload = await response.json().catch(() => null);

        if (!response.ok || !responsePayload?.authenticated) {
            const message = responsePayload?.message
                || responsePayload?.errors?.debug?.[0]
                || t('status_server_action_failed', 'Server debug action failed.');
            throw new Error(message);
        }

        applyServerResponse(responsePayload, `server-${action}`);

        return responsePayload;
    }

    async function handleServerAction(action) {
        if (action === 'reset-course-progress') {
            const confirmed = window.confirm(t('confirm_course_reset', 'Clear progress for this course?'));

            if (!confirmed) {
                return;
            }
        }

        try {
            const responsePayload = await postServerAction(action);

            if (action === 'apply-course-policy') {
                const appliedPolicy = saveLocalCoursePolicy(responsePayload.policy || currentPolicy());
                applyPolicyToInputs(appliedPolicy);
            } else if (action === 'clear-debug-overrides') {
                clearLocalDebugPolicies();
                resetPolicyInputs();
            } else if (action === 'reset-course-progress') {
                clearLocalCourseKeys();
                resetPolicyInputs();
            }

            showStatus(
                serverStatus(responsePayload, responsePayload.applied ? 'Server debug action applied.' : 'Server debug action did not apply.'),
                responsePayload.applied ? 'ok' : 'warn'
            );
        } catch (error) {
            showStatus(error?.message || t('status_server_action_failed', 'Server debug action failed.'), 'error');
        }
    }

    function renderState() {
        if (!stateNode) {
            return;
        }

        const snapshot = {
            courseSlug,
            firstLessonSlug: firstLessonSlug || null,
            serverDebugUrl: debugUrl || null,
            serverProgressUrl: progressUrl || null,
            localCourseProgressKey: courseProgressKey,
            localCourseProgress: readJson(courseProgressKey),
            localLegacyCourseProgressKey: legacyCourseProgressKey,
            localLegacyCourseProgress: readJson(legacyCourseProgressKey),
            localCoursePolicyKey: coursePolicyKey,
            localCoursePolicy: readJson(coursePolicyKey),
            currentPolicy: currentPolicy(),
            lessonProgressKeys: lessons.map((lesson) => `polyglot_progress:${lesson.slug}`),
            serverCourseProgress: lastServerProgress,
            lastServerResponse,
        };

        stateNode.textContent = JSON.stringify(snapshot, null, 2);
    }

    async function handleAction(action) {
        if (action === 'apply-course-policy' || action === 'clear-debug-overrides' || action === 'reset-course-progress') {
            await handleServerAction(action);
        } else if (action === 'refresh-state') {
            await fetchServerProgress();
            showStatus(t('status_state_refreshed', 'Debug state was refreshed.'), 'info');
        }
    }

    function boot(api) {
        progressApi = api || window.PolyglotCourseProgress || null;

        if (progressApi && courseSlug && typeof progressApi.createCourseStore === 'function') {
            courseStore = progressApi.createCourseStore(courseSlug, lessons, {
                sourceId: 'polyglot-course-admin-debug-store',
            });
        }

        restoreLocalCoursePolicy();
        renderState();
        fetchServerProgress().catch(() => renderState());
    }

    root.addEventListener('click', (event) => {
        const button = event.target.closest('[data-polyglot-course-debug-action]');

        if (!button || !root.contains(button)) {
            return;
        }

        animateActionButton(button);
        handleAction(button.getAttribute('data-polyglot-course-debug-action'));
    });

    root.addEventListener('input', () => renderState());
    root.addEventListener('change', () => renderState());

    if (window.PolyglotCourseProgress) {
        boot(window.PolyglotCourseProgress);
    } else {
        window.addEventListener('polyglot:progress-ready', (event) => boot(event.detail?.api), { once: true });
    }
})();
