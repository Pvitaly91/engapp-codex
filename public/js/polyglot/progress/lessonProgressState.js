export const LESSON_PROGRESS_VERSION = 3;
export const LESSON_PROGRESS_WINDOW = 100;
export const LESSON_PROGRESS_PASS_VALUE = 5;
export const LESSON_PROGRESS_FAIL_VALUE = 0;

export function buildDefaultLessonProgress(options = {}) {
    return {
        version: LESSON_PROGRESS_VERSION,
        lesson_slug: normalizeSlug(options.lessonSlug),
        course_slug: normalizeNullableString(options.courseSlug),
        current_queue_index: 0,
        rolling_results: [],
        total_attempts: 0,
        correct_attempts: 0,
        lesson_completed: false,
        completed_at: null,
        last_seen_at: normalizeIsoString(options.now),
    };
}

export function normalizeLessonProgress(raw, lessonSlug, options = {}) {
    const fallback = buildDefaultLessonProgress({
        lessonSlug,
        courseSlug: options.courseSlug,
    });

    if (!raw || typeof raw !== 'object' || Array.isArray(raw)) {
        return fallback;
    }

    const normalizedLessonSlug = normalizeSlug(raw.lesson_slug ?? raw.slug ?? fallback.lesson_slug);
    const normalizedCourseSlug = normalizeNullableString(raw.course_slug ?? options.courseSlug ?? fallback.course_slug);
    const rollingResults = sanitizeRollingResults(raw.rolling_results);
    const totalAttempts = Math.max(
        sanitizeCount(raw.total_attempts),
        rollingResults.length
    );
    const correctAttempts = Math.min(
        totalAttempts,
        Math.max(
            sanitizeCount(raw.correct_attempts),
            rollingResults.filter((value) => value >= 4.5).length
        )
    );
    const maxIndex = resolveMaxQueueIndex(options.questionCount);
    const currentQueueIndex = Math.min(
        Math.max(sanitizeInteger(raw.current_queue_index, 0), 0),
        maxIndex
    );
    const normalized = {
        version: LESSON_PROGRESS_VERSION,
        lesson_slug: normalizedLessonSlug === '' ? fallback.lesson_slug : normalizedLessonSlug,
        course_slug: normalizedCourseSlug,
        current_queue_index: currentQueueIndex,
        rolling_results: rollingResults,
        total_attempts: totalAttempts,
        correct_attempts: correctAttempts,
        lesson_completed: toBoolean(raw.lesson_completed),
        completed_at: normalizeIsoString(raw.completed_at),
        last_seen_at: normalizeIsoString(raw.last_seen_at),
    };

    if (meetsLessonCompletionRule(normalized, options)) {
        normalized.lesson_completed = true;
        normalized.completed_at = normalized.completed_at ?? normalizeIsoString(options.now);
    } else {
        normalized.lesson_completed = false;
        normalized.completed_at = null;
    }

    return normalized;
}

export function markLessonAttempt(progress, wasCorrect, options = {}) {
    const snapshot = normalizeLessonProgress(progress, options.lessonSlug ?? progress?.lesson_slug, options);
    const result = wasCorrect ? LESSON_PROGRESS_PASS_VALUE : LESSON_PROGRESS_FAIL_VALUE;

    return normalizeLessonProgress({
        ...snapshot,
        total_attempts: snapshot.total_attempts + 1,
        correct_attempts: snapshot.correct_attempts + (wasCorrect ? 1 : 0),
        rolling_results: [...snapshot.rolling_results, result],
        last_seen_at: normalizeIsoString(options.now),
    }, snapshot.lesson_slug, options);
}

export function markLessonCompleted(progress, options = {}) {
    const snapshot = normalizeLessonProgress(progress, options.lessonSlug ?? progress?.lesson_slug, options);

    return normalizeLessonProgress({
        ...snapshot,
        lesson_completed: true,
        completed_at: normalizeIsoString(options.now),
        last_seen_at: normalizeIsoString(options.now),
    }, snapshot.lesson_slug, options);
}

export function resetLessonProgress(lessonSlug, options = {}) {
    return buildDefaultLessonProgress({
        lessonSlug,
        courseSlug: options.courseSlug,
    });
}

export function computeLessonStatus(progress) {
    const snapshot = normalizeLessonProgress(progress, progress?.lesson_slug ?? progress?.slug ?? '');

    if (snapshot.lesson_completed) {
        return 'completed';
    }

    return hasLessonProgress(snapshot) ? 'in_progress' : 'not_started';
}

export function computeLessonAverage(progress) {
    const rollingResults = sanitizeRollingResults(progress?.rolling_results);

    if (rollingResults.length === 0) {
        return 0;
    }

    const total = rollingResults.reduce((sum, value) => sum + Number(value || 0), 0);

    return total / rollingResults.length;
}

export function hasLessonProgress(progress) {
    const snapshot = normalizeLessonProgress(progress, progress?.lesson_slug ?? progress?.slug ?? '');

    return snapshot.lesson_completed
        || snapshot.rolling_results.length > 0
        || snapshot.total_attempts > 0
        || snapshot.current_queue_index > 0;
}

export function meetsLessonCompletionRule(progress, options = {}) {
    const snapshot = {
        rolling_results: Array.isArray(progress?.rolling_results) ? progress.rolling_results : [],
    };
    const rawWindow = Math.max(1, sanitizeInteger(options.rollingWindow, LESSON_PROGRESS_WINDOW));
    const questionCount = sanitizeInteger(options.questionCount, 0);
    const windowSize = questionCount > 0
        ? Math.max(1, Math.min(rawWindow, questionCount))
        : rawWindow;
    const minRating = Number.isFinite(Number(options.minRating))
        ? Number(options.minRating)
        : 4.5;

    if (snapshot.rolling_results.length < windowSize) {
        return false;
    }

    return computeLessonAverage(progress) >= minRating;
}

function sanitizeRollingResults(values) {
    if (!Array.isArray(values)) {
        return [];
    }

    return values
        .map((value) => Number(value))
        .filter((value) => Number.isFinite(value) && value >= LESSON_PROGRESS_FAIL_VALUE && value <= LESSON_PROGRESS_PASS_VALUE)
        .map((value) => Math.round(value * 100) / 100)
        .slice(-LESSON_PROGRESS_WINDOW);
}

function sanitizeInteger(value, fallback = 0) {
    const normalized = Number.parseInt(String(value ?? ''), 10);

    return Number.isInteger(normalized) ? normalized : fallback;
}

function sanitizeCount(value) {
    return Math.max(0, sanitizeInteger(value, 0));
}

function resolveMaxQueueIndex(questionCount) {
    if (questionCount === null || questionCount === undefined || questionCount === '') {
        return Number.MAX_SAFE_INTEGER;
    }

    const count = sanitizeCount(questionCount);

    return count > 0 ? count - 1 : 0;
}

function toBoolean(value) {
    return value === true || value === 1 || value === '1' || value === 'true';
}

function normalizeSlug(value) {
    return String(value ?? '').trim();
}

function normalizeNullableString(value) {
    const normalized = String(value ?? '').trim();

    return normalized === '' ? null : normalized;
}

function normalizeIsoString(value) {
    const normalized = String(value ?? '').trim();

    return normalized === '' ? null : normalized;
}
