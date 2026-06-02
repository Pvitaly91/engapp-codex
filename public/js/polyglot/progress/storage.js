export const LESSON_PROGRESS_PREFIX = 'polyglot_progress:';
export const COURSE_PROGRESS_PREFIX = 'polyglot_course_progress:';
export const LEGACY_COURSE_PROGRESS_PREFIX = 'polyglot_course_state:';

export function buildLessonProgressKey(lessonSlug) {
    return `${LESSON_PROGRESS_PREFIX}${normalizeStorageSegment(lessonSlug)}`;
}

export function buildCourseProgressKey(courseSlug) {
    return `${COURSE_PROGRESS_PREFIX}${normalizeStorageSegment(courseSlug)}`;
}

export function buildLegacyCourseProgressKey(courseSlug) {
    return `${LEGACY_COURSE_PROGRESS_PREFIX}${normalizeStorageSegment(courseSlug)}`;
}

export function normalizeStorageSegment(value) {
    return String(value ?? '').trim();
}

export function safeJsonParse(raw) {
    if (typeof raw !== 'string' || raw.trim() === '') {
        return null;
    }

    try {
        return JSON.parse(raw);
    } catch (error) {
        return null;
    }
}

export function resolveStorage(storageOverride = null) {
    if (storageOverride && typeof storageOverride.getItem === 'function') {
        return storageOverride;
    }

    if (typeof window !== 'undefined' && window.localStorage) {
        return window.localStorage;
    }

    if (typeof globalThis !== 'undefined' && globalThis.localStorage) {
        return globalThis.localStorage;
    }

    return null;
}

export function safeStorageGet(storage, key) {
    if (!storage || typeof storage.getItem !== 'function') {
        return null;
    }

    try {
        return storage.getItem(key);
    } catch (error) {
        return null;
    }
}

export function safeStorageSet(storage, key, value) {
    if (!storage || typeof storage.setItem !== 'function') {
        return false;
    }

    try {
        storage.setItem(key, value);

        return true;
    } catch (error) {
        return false;
    }
}

export function safeStorageRemove(storage, key) {
    if (!storage || typeof storage.removeItem !== 'function') {
        return false;
    }

    try {
        storage.removeItem(key);

        return true;
    } catch (error) {
        return false;
    }
}
