import {
    buildCourseProgressKey,
    buildLegacyCourseProgressKey,
    buildLessonProgressKey,
    normalizeStorageSegment,
} from './storage.js';

export const POLYGLOT_PROGRESS_READY_EVENT = 'polyglot:progress-ready';
export const POLYGLOT_LESSON_PROGRESS_UPDATED_EVENT = 'polyglot:lesson-progress-updated';
export const POLYGLOT_COURSE_PROGRESS_UPDATED_EVENT = 'polyglot:course-progress-updated';
export const POLYGLOT_LESSON_COMPLETED_EVENT = 'polyglot:lesson-completed';
export const POLYGLOT_COURSE_RESET_EVENT = 'polyglot:course-reset';

export const POLYGLOT_SYNC_EVENT_NAMES = [
    POLYGLOT_LESSON_PROGRESS_UPDATED_EVENT,
    POLYGLOT_COURSE_PROGRESS_UPDATED_EVENT,
    POLYGLOT_LESSON_COMPLETED_EVENT,
    POLYGLOT_COURSE_RESET_EVENT,
];

export function defaultEventTarget() {
    if (typeof window !== 'undefined' && typeof window.addEventListener === 'function') {
        return window;
    }

    return null;
}

export function emitProgressEvent(name, detail = {}, target = defaultEventTarget()) {
    if (!target || typeof target.dispatchEvent !== 'function' || typeof CustomEvent === 'undefined') {
        return null;
    }

    const event = new CustomEvent(name, {
        detail,
    });

    target.dispatchEvent(event);

    return event;
}

export function isRelevantProgressEvent(event, options = {}) {
    const detail = event?.detail ?? {};
    const courseSlug = normalizeStorageSegment(options.courseSlug);
    const lessonSlug = normalizeStorageSegment(options.lessonSlug);
    const ignoreSourceId = normalizeStorageSegment(options.ignoreSourceId);

    if (ignoreSourceId !== '' && normalizeStorageSegment(detail.sourceId) === ignoreSourceId) {
        return false;
    }

    if (courseSlug !== '' && normalizeStorageSegment(detail.courseSlug) !== '' && normalizeStorageSegment(detail.courseSlug) !== courseSlug) {
        return false;
    }

    if (lessonSlug !== '' && normalizeStorageSegment(detail.lessonSlug) !== '' && normalizeStorageSegment(detail.lessonSlug) !== lessonSlug) {
        return false;
    }

    return true;
}

export function isRelevantStorageEvent(event, options = {}) {
    const key = normalizeStorageSegment(event?.key);
    const courseSlug = normalizeStorageSegment(options.courseSlug);
    const lessonSlug = normalizeStorageSegment(options.lessonSlug);

    if (key === '') {
        return false;
    }

    if (courseSlug !== '' && [
        buildCourseProgressKey(courseSlug),
        buildLegacyCourseProgressKey(courseSlug),
    ].includes(key)) {
        return true;
    }

    if (lessonSlug !== '' && key === buildLessonProgressKey(lessonSlug)) {
        return true;
    }

    return false;
}

export function subscribeToProgressSync(options = {}) {
    const target = options.target ?? defaultEventTarget();
    const onSync = typeof options.onSync === 'function' ? options.onSync : () => {};
    const listeners = [];

    if (!target || typeof target.addEventListener !== 'function') {
        return () => {};
    }

    const customHandler = (event) => {
        if (!isRelevantProgressEvent(event, options)) {
            return;
        }

        onSync({
            type: 'custom',
            originalEvent: event,
            detail: event.detail ?? {},
        });
    };

    POLYGLOT_SYNC_EVENT_NAMES.forEach((name) => {
        target.addEventListener(name, customHandler);
        listeners.push([name, customHandler]);
    });

    const storageHandler = (event) => {
        if (!isRelevantStorageEvent(event, options)) {
            return;
        }

        onSync({
            type: 'storage',
            originalEvent: event,
            detail: {
                key: event.key ?? null,
            },
        });
    };

    target.addEventListener('storage', storageHandler);
    listeners.push(['storage', storageHandler]);

    return () => {
        listeners.forEach(([name, handler]) => {
            target.removeEventListener(name, handler);
        });
    };
}
