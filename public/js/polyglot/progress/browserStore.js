import {
    POLYGLOT_COURSE_PROGRESS_UPDATED_EVENT,
    POLYGLOT_COURSE_RESET_EVENT,
    POLYGLOT_LESSON_COMPLETED_EVENT,
    POLYGLOT_LESSON_PROGRESS_UPDATED_EVENT,
    emitProgressEvent,
} from './events.js';
import {
    buildManifestHash,
    computeCourseSummary,
    computeLessonStatus,
    normalizeCourseProgress,
    normalizeManifestLessons,
    resetCourseProgress,
} from './courseProgressState.js';
import {
    buildDefaultLessonProgress,
    markLessonAttempt,
    markLessonCompleted,
    normalizeLessonProgress,
    resetLessonProgress,
} from './lessonProgressState.js';
import {
    buildCourseProgressKey,
    buildLegacyCourseProgressKey,
    buildLessonProgressKey,
    resolveStorage,
    safeJsonParse,
    safeStorageGet,
    safeStorageRemove,
    safeStorageSet,
} from './storage.js';

let polyglotSourceSequence = 0;

export function createCourseStore(courseSlug, rawLessons, options = {}) {
    const normalizedCourseSlug = String(courseSlug ?? '').trim();
    const lessons = normalizeManifestLessons(rawLessons);
    const storage = resolveStorage(options.storage);
    const courseKey = buildCourseProgressKey(normalizedCourseSlug);
    const legacyCourseKey = buildLegacyCourseProgressKey(normalizedCourseSlug);
    const sourceId = options.sourceId || createSourceId('course');
    const warnedKeys = new Set();

    function warnOnce(key, message, meta = {}) {
        if (warnedKeys.has(key) || typeof console === 'undefined' || typeof console.warn !== 'function') {
            return;
        }

        warnedKeys.add(key);
        console.warn(`[polyglot-progress] ${message}`, meta);
    }

    function readJsonKey(key) {
        const rawValue = safeStorageGet(storage, key);
        const parsed = safeJsonParse(rawValue);

        if (rawValue !== null && parsed === null) {
            warnOnce(key, 'Corrupted progress state was ignored and rebuilt.', {
                key,
            });
        }

        return parsed;
    }

    function readLessonStates() {
        return lessons.reduce((carry, lesson) => {
            carry[lesson.slug] = normalizeLessonProgress(
                readJsonKey(buildLessonProgressKey(lesson.slug)),
                lesson.slug,
                {
                    courseSlug: normalizedCourseSlug,
                }
            );

            return carry;
        }, {});
    }

    function readCourseRaw() {
        return readJsonKey(courseKey) ?? readJsonKey(legacyCourseKey);
    }

    function read() {
        return normalizeCourseProgress(readCourseRaw(), normalizedCourseSlug, lessons, {
            lessonStates: readLessonStates(),
        });
    }

    function emitCourseUpdated(state, reason) {
        emitProgressEvent(POLYGLOT_COURSE_PROGRESS_UPDATED_EVENT, {
            sourceId,
            courseSlug: normalizedCourseSlug,
            state,
            reason,
        });
    }

    function write(state, reason = 'sync') {
        const snapshot = normalizeCourseProgress(state, normalizedCourseSlug, lessons, {
            lessonStates: readLessonStates(),
        });

        snapshot.updated_at = nowIso();
        safeStorageSet(storage, courseKey, JSON.stringify(snapshot));
        safeStorageRemove(storage, legacyCourseKey);
        emitCourseUpdated(snapshot, reason);

        return snapshot;
    }

    function sync(reason = 'sync') {
        return write(read(), reason);
    }

    function findLesson(slug) {
        const normalizedSlug = String(slug ?? '').trim();

        return lessons.find((lesson) => lesson.slug === normalizedSlug) || null;
    }

    function touchLessonState(lessonSlug, options = {}) {
        const normalizedLessonSlug = String(lessonSlug ?? '').trim();
        if (normalizedLessonSlug === '') {
            return null;
        }

        const key = buildLessonProgressKey(normalizedLessonSlug);
        const current = normalizeLessonProgress(readJsonKey(key), normalizedLessonSlug, {
            courseSlug: normalizedCourseSlug,
            questionCount: options.questionCount,
            rollingWindow: options.rollingWindow,
            minRating: options.minRating,
        });
        const snapshot = normalizeLessonProgress({
            ...current,
            last_seen_at: nowIso(),
        }, normalizedLessonSlug, {
            courseSlug: normalizedCourseSlug,
            questionCount: options.questionCount,
            rollingWindow: options.rollingWindow,
            minRating: options.minRating,
            now: nowIso(),
        });

        safeStorageSet(storage, key, JSON.stringify(snapshot));
        emitProgressEvent(POLYGLOT_LESSON_PROGRESS_UPDATED_EVENT, {
            sourceId,
            courseSlug: normalizedCourseSlug,
            lessonSlug: normalizedLessonSlug,
            state: snapshot,
            reason: 'touch',
        });

        return snapshot;
    }

    function markLessonOpened(slug) {
        const lesson = findLesson(slug);
        const state = read();

        if (!lesson || !state.lessons[lesson.slug] || !state.lessons[lesson.slug].unlocked) {
            return state;
        }

        touchLessonState(lesson.slug);

        state.lessons[lesson.slug].has_progress = true;
        state.lessons[lesson.slug].last_opened_at = nowIso();
        state.lessons[lesson.slug].updated_at = nowIso();
        state.last_opened_lesson_slug = lesson.slug;

        if (!state.lessons[lesson.slug].completed) {
            state.current_lesson_slug = lesson.slug;
        }

        return write(state, 'lesson-opened');
    }

    function markLessonCompletedInCourse(slug) {
        const lesson = findLesson(slug);
        const state = read();

        if (!lesson || !state.lessons[lesson.slug]) {
            return state;
        }

        state.lessons[lesson.slug].completed = true;
        state.lessons[lesson.slug].unlocked = true;
        state.lessons[lesson.slug].has_progress = true;
        state.lessons[lesson.slug].updated_at = nowIso();

        return write(state, 'lesson-completed');
    }

    function markLessonIncomplete(slug) {
        const normalizedLessonSlug = String(slug ?? '').trim();
        if (normalizedLessonSlug === '') {
            return read();
        }

        const snapshot = buildDefaultLessonProgress({
            lessonSlug: normalizedLessonSlug,
            courseSlug: normalizedCourseSlug,
        });

        safeStorageSet(storage, buildLessonProgressKey(normalizedLessonSlug), JSON.stringify(snapshot));
        emitProgressEvent(POLYGLOT_LESSON_PROGRESS_UPDATED_EVENT, {
            sourceId,
            courseSlug: normalizedCourseSlug,
            lessonSlug: normalizedLessonSlug,
            state: snapshot,
            reason: 'lesson-reset',
        });

        return sync('lesson-reset');
    }

    function reset() {
        lessons.forEach((lesson) => {
            safeStorageRemove(storage, buildLessonProgressKey(lesson.slug));
        });

        safeStorageRemove(storage, courseKey);
        safeStorageRemove(storage, legacyCourseKey);

        const snapshot = resetCourseProgress(normalizedCourseSlug, lessons, {
            now: nowIso(),
        });

        safeStorageSet(storage, courseKey, JSON.stringify(snapshot));
        emitProgressEvent(POLYGLOT_COURSE_RESET_EVENT, {
            sourceId,
            courseSlug: normalizedCourseSlug,
            state: snapshot,
        });
        emitCourseUpdated(snapshot, 'course-reset');

        return snapshot;
    }

    return {
        sourceId,
        courseSlug: normalizedCourseSlug,
        lessons,
        key: courseKey,
        manifestHash: buildManifestHash(normalizedCourseSlug, lessons),
        lessonProgressKey: buildLessonProgressKey,
        read,
        sync,
        write,
        findLesson,
        markLessonOpened,
        markLessonCompleted: markLessonCompletedInCourse,
        markLessonIncomplete,
        reset,
        getLessonStatus(slug, state = null) {
            const snapshot = state ?? read();

            return computeLessonStatus(snapshot, slug);
        },
        getSummary(state = null) {
            const snapshot = state ?? read();

            return computeCourseSummary(snapshot, lessons);
        },
    };
}

export function createLessonStore(options = {}) {
    const lessonSlug = String(options.lessonSlug ?? '').trim();
    const courseSlug = String(options.courseSlug ?? '').trim();
    const storage = resolveStorage(options.storage);
    const sourceId = options.sourceId || createSourceId('lesson');
    const courseStore = options.courseStore ?? null;
    const key = buildLessonProgressKey(lessonSlug);
    const baseOptions = {
        lessonSlug,
        courseSlug: courseSlug === '' ? null : courseSlug,
        questionCount: options.questionCount,
        rollingWindow: options.rollingWindow,
        minRating: options.minRating,
    };

    function read() {
        return normalizeLessonProgress(
            safeJsonParse(safeStorageGet(storage, key)),
            lessonSlug,
            baseOptions
        );
    }

    function syncCourse(reason) {
        if (!courseStore || typeof courseStore.sync !== 'function') {
            return null;
        }

        return courseStore.sync(reason);
    }

    function emitLessonUpdated(state, reason) {
        emitProgressEvent(POLYGLOT_LESSON_PROGRESS_UPDATED_EVENT, {
            sourceId,
            lessonSlug,
            courseSlug: courseSlug === '' ? null : courseSlug,
            state,
            reason,
        });
    }

    function write(progress, reason = 'lesson-write') {
        const snapshot = normalizeLessonProgress({
            ...progress,
            last_seen_at: nowIso(),
        }, lessonSlug, {
            ...baseOptions,
            now: nowIso(),
        });

        safeStorageSet(storage, key, JSON.stringify(snapshot));
        emitLessonUpdated(snapshot, reason);

        const courseState = syncCourse(reason);

        if (snapshot.lesson_completed) {
            emitProgressEvent(POLYGLOT_LESSON_COMPLETED_EVENT, {
                sourceId,
                lessonSlug,
                courseSlug: courseSlug === '' ? null : courseSlug,
                state: snapshot,
                courseState,
            });
        }

        return snapshot;
    }

    function reset() {
        return write(resetLessonProgress(lessonSlug, {
            courseSlug: courseSlug === '' ? null : courseSlug,
        }), 'lesson-reset');
    }

    return {
        sourceId,
        key,
        read,
        write,
        reset,
        normalize(progress) {
            return normalizeLessonProgress(progress, lessonSlug, baseOptions);
        },
        markAttempt(progress, wasCorrect) {
            return write(markLessonAttempt(progress, wasCorrect, {
                ...baseOptions,
                now: nowIso(),
            }), wasCorrect ? 'lesson-attempt-correct' : 'lesson-attempt-wrong');
        },
        markCompleted(progress) {
            return write(markLessonCompleted(progress, {
                ...baseOptions,
                now: nowIso(),
            }), 'lesson-completed');
        },
    };
}

function createSourceId(prefix) {
    polyglotSourceSequence += 1;

    return `${prefix}-${Date.now().toString(36)}-${polyglotSourceSequence.toString(36)}`;
}

function nowIso() {
    return new Date().toISOString();
}
