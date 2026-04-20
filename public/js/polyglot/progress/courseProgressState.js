import {
    hasLessonProgress,
    normalizeLessonProgress,
} from './lessonProgressState.js';

export const COURSE_PROGRESS_VERSION = 3;

export function normalizeManifestLessons(rawLessons) {
    return (Array.isArray(rawLessons) ? rawLessons : [])
        .map((lesson, index) => normalizeManifestLesson(lesson, index))
        .filter(Boolean)
        .sort((left, right) => {
            if (left.lesson_order === right.lesson_order) {
                return left.slug.localeCompare(right.slug);
            }

            return left.lesson_order - right.lesson_order;
        });
}

export function buildDefaultCourseProgress(courseSlug, manifest, options = {}) {
    const normalizedLessons = normalizeManifestLessons(manifest);
    const firstLesson = normalizedLessons[0] ?? null;
    const lessonMap = normalizedLessons.reduce((carry, lesson, index) => {
        carry[lesson.slug] = {
            unlocked: index === 0,
            completed: false,
            has_progress: false,
            last_opened_at: null,
            updated_at: null,
        };

        return carry;
    }, {});

    return {
        version: COURSE_PROGRESS_VERSION,
        course_slug: normalizeSlug(courseSlug),
        manifest_hash: buildManifestHash(courseSlug, normalizedLessons),
        unlocked_lessons: firstLesson ? [firstLesson.slug] : [],
        completed_lessons: [],
        current_lesson_slug: firstLesson ? firstLesson.slug : null,
        last_opened_lesson_slug: null,
        lessons: lessonMap,
        updated_at: normalizeIsoString(options.now),
    };
}

export function normalizeCourseProgress(raw, courseSlug, manifest, options = {}) {
    const normalizedCourseSlug = normalizeSlug(courseSlug);
    const lessons = normalizeManifestLessons(manifest);
    const fallback = buildDefaultCourseProgress(normalizedCourseSlug, lessons);
    const baseState = raw && typeof raw === 'object' && !Array.isArray(raw)
        ? raw
        : {};
    const rawLessonMap = baseState.lessons && typeof baseState.lessons === 'object'
        ? baseState.lessons
        : {};
    const unlockedFlags = collectFlags(baseState.unlocked_lessons);
    const completedFlags = collectFlags(baseState.completed_lessons);
    const lessonStates = normalizeLessonStateMap(options.lessonStates, lessons, normalizedCourseSlug);
    const normalizedLessonMap = {};

    lessons.forEach((lesson, index) => {
        const lessonState = lessonStates[lesson.slug] ?? null;
        const entry = normalizeCourseLessonEntry(rawLessonMap[lesson.slug]);

        if (index === 0) {
            entry.unlocked = true;
        }

        if (unlockedFlags.includes(lesson.slug)) {
            entry.unlocked = true;
        }

        if (completedFlags.includes(lesson.slug)) {
            entry.completed = true;
            entry.has_progress = true;
        }

        if (lessonState) {
            entry.completed = entry.completed || lessonState.lesson_completed;
            entry.has_progress = entry.has_progress || hasLessonProgress(lessonState) || lessonState.lesson_completed;
        }

        if (entry.completed) {
            entry.unlocked = true;
        }

        normalizedLessonMap[lesson.slug] = entry;
    });

    lessons.forEach((lesson, index) => {
        const entry = normalizedLessonMap[lesson.slug];

        if (index === 0) {
            entry.unlocked = true;
        }

        if (lesson.previous_lesson_slug && normalizedLessonMap[lesson.previous_lesson_slug]?.completed) {
            entry.unlocked = true;
        }

        if (entry.completed && lesson.next_lesson_slug && normalizedLessonMap[lesson.next_lesson_slug]) {
            normalizedLessonMap[lesson.next_lesson_slug].unlocked = true;
        }
    });

    const orderedUnlocked = lessons
        .filter((lesson) => normalizedLessonMap[lesson.slug]?.unlocked)
        .map((lesson) => lesson.slug);
    const orderedCompleted = lessons
        .filter((lesson) => normalizedLessonMap[lesson.slug]?.completed)
        .map((lesson) => lesson.slug);
    const requestedCurrent = normalizeNullableString(baseState.current_lesson_slug);
    const requestedLastOpened = normalizeNullableString(baseState.last_opened_lesson_slug);
    const currentLesson = lessons.find((lesson) => (
        lesson.slug === requestedCurrent
        && normalizedLessonMap[lesson.slug]?.unlocked
        && !normalizedLessonMap[lesson.slug]?.completed
    )) || lessons.find((lesson) => (
        normalizedLessonMap[lesson.slug]?.unlocked
        && !normalizedLessonMap[lesson.slug]?.completed
    )) || lessons[lessons.length - 1] || null;
    const lastOpenedLesson = lessons.find((lesson) => lesson.slug === requestedLastOpened)
        || currentLesson
        || lessons[0]
        || null;

    return {
        ...fallback,
        course_slug: normalizedCourseSlug,
        manifest_hash: buildManifestHash(normalizedCourseSlug, lessons),
        unlocked_lessons: orderedUnlocked,
        completed_lessons: orderedCompleted,
        current_lesson_slug: currentLesson ? currentLesson.slug : null,
        last_opened_lesson_slug: lastOpenedLesson ? lastOpenedLesson.slug : null,
        lessons: normalizedLessonMap,
        updated_at: normalizeIsoString(baseState.updated_at),
    };
}

export function mergeCourseStateFromLessonStates(courseState, lessonStates, manifest, options = {}) {
    const resolvedCourseSlug = normalizeSlug(courseState?.course_slug ?? options.courseSlug);

    return normalizeCourseProgress(courseState, resolvedCourseSlug, manifest, {
        ...options,
        lessonStates,
    });
}

export function unlockNextLesson(courseState, lessonSlug, manifest, options = {}) {
    const lessons = normalizeManifestLessons(manifest);
    const resolvedCourseSlug = normalizeSlug(courseState?.course_slug ?? options.courseSlug);
    const snapshot = normalizeCourseProgress(courseState, resolvedCourseSlug, lessons, options);
    const lesson = lessons.find((candidate) => candidate.slug === normalizeSlug(lessonSlug));

    if (!lesson?.next_lesson_slug || !snapshot.lessons[lesson.next_lesson_slug]) {
        return snapshot;
    }

    snapshot.lessons[lesson.next_lesson_slug].unlocked = true;

    return normalizeCourseProgress(snapshot, resolvedCourseSlug, lessons, options);
}

export function computeLessonStatus(courseState, lessonSlug) {
    const snapshot = courseState && typeof courseState === 'object' && !Array.isArray(courseState)
        ? courseState
        : { lessons: {} };
    const normalizedLessonSlug = normalizeSlug(lessonSlug);
    const lesson = snapshot.lessons?.[normalizedLessonSlug];

    if (!lesson) {
        return 'locked';
    }

    if (lesson.completed) {
        return 'completed';
    }

    if (snapshot.current_lesson_slug === normalizedLessonSlug) {
        return 'current';
    }

    return lesson.unlocked ? 'available' : 'locked';
}

export function computeCourseSummary(courseState, manifest) {
    const snapshot = normalizeCourseProgress(
        courseState,
        courseState?.course_slug ?? '',
        manifest
    );

    return {
        completed_lessons: snapshot.completed_lessons.length,
        total_lessons: Object.keys(snapshot.lessons).length,
        current_lesson_slug: snapshot.current_lesson_slug,
        last_opened_lesson_slug: snapshot.last_opened_lesson_slug,
        completed_all_lessons: Object.keys(snapshot.lessons).length > 0
            && snapshot.completed_lessons.length >= Object.keys(snapshot.lessons).length,
    };
}

export function resetCourseProgress(courseSlug, manifest, options = {}) {
    return buildDefaultCourseProgress(courseSlug, manifest, options);
}

export function buildManifestHash(courseSlug, manifest) {
    const normalizedLessons = normalizeManifestLessons(manifest);
    const source = [
        normalizeSlug(courseSlug),
        ...normalizedLessons.map((lesson) => [
            lesson.slug,
            lesson.lesson_order,
            lesson.previous_lesson_slug ?? '',
            lesson.next_lesson_slug ?? '',
        ].join(':')),
    ].join('|');

    let hash = 0;

    for (let index = 0; index < source.length; index += 1) {
        hash = ((hash << 5) - hash) + source.charCodeAt(index);
        hash |= 0;
    }

    return `polyglot-${Math.abs(hash).toString(36)}`;
}

function normalizeManifestLesson(rawLesson, index) {
    if (!rawLesson || typeof rawLesson !== 'object') {
        return null;
    }

    const slug = normalizeSlug(rawLesson.slug);
    if (slug === '') {
        return null;
    }

    const lessonOrder = sanitizePositiveInteger(rawLesson.lesson_order, index + 1);

    return {
        slug,
        name: normalizeSlug(rawLesson.name || slug),
        topic: normalizeNullableString(rawLesson.topic),
        lesson_order: lessonOrder,
        previous_lesson_slug: normalizeNullableString(rawLesson.previous_lesson_slug),
        next_lesson_slug: normalizeNullableString(rawLesson.next_lesson_slug),
        compose_url: normalizeSlug(rawLesson.compose_url),
    };
}

function normalizeCourseLessonEntry(raw) {
    const entry = raw && typeof raw === 'object' && !Array.isArray(raw)
        ? raw
        : {};

    return {
        unlocked: toBoolean(entry.unlocked),
        completed: toBoolean(entry.completed),
        has_progress: toBoolean(entry.has_progress),
        last_opened_at: normalizeIsoString(entry.last_opened_at),
        updated_at: normalizeIsoString(entry.updated_at),
    };
}

function normalizeLessonStateMap(rawLessonStates, lessons, courseSlug) {
    return lessons.reduce((carry, lesson) => {
        carry[lesson.slug] = normalizeLessonProgress(
            rawLessonStates?.[lesson.slug] ?? null,
            lesson.slug,
            {
                courseSlug,
            }
        );

        return carry;
    }, {});
}

function collectFlags(rawValue) {
    if (Array.isArray(rawValue)) {
        return uniqueStrings(rawValue);
    }

    if (rawValue && typeof rawValue === 'object') {
        return uniqueStrings(Object.keys(rawValue).filter((slug) => rawValue[slug]));
    }

    return [];
}

function uniqueStrings(values) {
    const unique = [];

    (Array.isArray(values) ? values : []).forEach((value) => {
        const normalized = normalizeSlug(value);
        if (normalized === '' || unique.includes(normalized)) {
            return;
        }

        unique.push(normalized);
    });

    return unique;
}

function sanitizePositiveInteger(value, fallback) {
    const normalized = Number.parseInt(String(value ?? ''), 10);

    if (Number.isInteger(normalized) && normalized > 0) {
        return normalized;
    }

    return fallback;
}

function toBoolean(value) {
    return value === true || value === 1 || value === '1' || value === 'true';
}

function normalizeSlug(value) {
    return String(value ?? '').trim();
}

function normalizeNullableString(value) {
    const normalized = normalizeSlug(value);

    return normalized === '' ? null : normalized;
}

function normalizeIsoString(value) {
    const normalized = normalizeSlug(value);

    return normalized === '' ? null : normalized;
}
