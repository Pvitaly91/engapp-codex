const COURSE_PROGRESS_PREFIX = 'theory_course_progress:';
const LESSON_PROGRESS_PREFIX = 'theory_lesson_progress:';
const READY_EVENT = 'theory-course:progress-ready';
const UPDATED_EVENT = 'theory-course:progress-updated';
const COMPLETED_EVENT = 'theory-course:lesson-completed';
const RESET_EVENT = 'theory-course:reset';

function normalizeSlug(value) {
    return String(value ?? '').trim();
}

function lessonSlug(lesson) {
    return normalizeSlug(lesson?.lesson_slug || lesson?.slug);
}

function normalizeLessons(rawLessons) {
    return (Array.isArray(rawLessons) ? rawLessons : [])
        .map((lesson, index) => {
            const slug = lessonSlug(lesson);

            if (slug === '') {
                return null;
            }

            return {
                ...lesson,
                slug,
                lesson_slug: slug,
                name: normalizeSlug(lesson.name || lesson.title || slug),
                lesson_order: Number.parseInt(String(lesson.lesson_order || lesson.order || index + 1), 10) || index + 1,
                previous_lesson_slug: normalizeSlug(lesson.previous_lesson_slug) || null,
                next_lesson_slug: normalizeSlug(lesson.next_lesson_slug) || null,
                lesson_url: normalizeSlug(lesson.lesson_url),
                test_url: normalizeSlug(lesson.test_url),
            };
        })
        .filter(Boolean)
        .sort((left, right) => {
            if (left.lesson_order === right.lesson_order) {
                return left.slug.localeCompare(right.slug);
            }

            return left.lesson_order - right.lesson_order;
        });
}

function courseKey(courseSlug) {
    return `${COURSE_PROGRESS_PREFIX}${normalizeSlug(courseSlug)}`;
}

function lessonKey(courseSlug, slug) {
    return `${LESSON_PROGRESS_PREFIX}${normalizeSlug(courseSlug)}:${normalizeSlug(slug)}`;
}

function storage() {
    try {
        return window.localStorage || null;
    } catch (error) {
        return null;
    }
}

function readJson(key) {
    const store = storage();

    if (!store) {
        return null;
    }

    try {
        const raw = store.getItem(key);

        return raw ? JSON.parse(raw) : null;
    } catch (error) {
        return null;
    }
}

function writeJson(key, value) {
    const store = storage();

    if (!store) {
        return false;
    }

    try {
        store.setItem(key, JSON.stringify(value));

        return true;
    } catch (error) {
        return false;
    }
}

function removeKey(key) {
    const store = storage();

    if (!store) {
        return false;
    }

    try {
        store.removeItem(key);

        return true;
    } catch (error) {
        return false;
    }
}

function uniqueOrdered(values, lessons) {
    const allowed = new Set(lessons.map((lesson) => lesson.slug));
    const seen = new Set();
    const selected = new Set((Array.isArray(values) ? values : []).map(normalizeSlug));

    return lessons
        .map((lesson) => lesson.slug)
        .filter((slug) => allowed.has(slug) && selected.has(slug) && !seen.has(slug) && seen.add(slug));
}

function defaultState(courseSlug, lessons) {
    const first = lessons[0] || null;

    return {
        version: 1,
        courseSlug: normalizeSlug(courseSlug),
        unlockedLessons: first ? [first.slug] : [],
        completedLessons: [],
        lessonScores: {},
        currentLessonSlug: first ? first.slug : null,
        lastOpenedLessonSlug: null,
        updatedAt: new Date().toISOString(),
    };
}

function normalizeState(raw, courseSlug, lessons) {
    const fallback = defaultState(courseSlug, lessons);
    const state = raw && typeof raw === 'object' && !Array.isArray(raw) ? raw : {};
    const completedLessons = uniqueOrdered(state.completedLessons || state.completed_lessons, lessons);
    const unlockedFromState = uniqueOrdered(state.unlockedLessons || state.unlocked_lessons, lessons);
    const unlocked = new Set(unlockedFromState);

    if (lessons[0]) {
        unlocked.add(lessons[0].slug);
    }

    completedLessons.forEach((slug) => {
        unlocked.add(slug);
        const lesson = lessons.find((item) => item.slug === slug);
        if (lesson?.next_lesson_slug) {
            unlocked.add(lesson.next_lesson_slug);
        }
    });

    lessons.forEach((lesson) => {
        if (lesson.previous_lesson_slug && completedLessons.includes(lesson.previous_lesson_slug)) {
            unlocked.add(lesson.slug);
        }
    });

    const orderedUnlocked = lessons
        .filter((lesson) => unlocked.has(lesson.slug))
        .map((lesson) => lesson.slug);
    const lessonScores = state.lessonScores && typeof state.lessonScores === 'object'
        ? state.lessonScores
        : {};
    const requestedCurrent = normalizeSlug(state.currentLessonSlug || state.current_lesson_slug);
    const current = lessons.find((lesson) => (
        lesson.slug === requestedCurrent
        && orderedUnlocked.includes(lesson.slug)
        && !completedLessons.includes(lesson.slug)
    )) || lessons.find((lesson) => (
        orderedUnlocked.includes(lesson.slug)
        && !completedLessons.includes(lesson.slug)
    )) || lessons[lessons.length - 1] || null;
    const requestedLast = normalizeSlug(state.lastOpenedLessonSlug || state.last_opened_lesson_slug);
    const lastOpened = lessons.find((lesson) => lesson.slug === requestedLast) || null;

    return {
        ...fallback,
        courseSlug: normalizeSlug(courseSlug),
        unlockedLessons: orderedUnlocked,
        completedLessons,
        lessonScores,
        currentLessonSlug: current ? current.slug : null,
        lastOpenedLessonSlug: lastOpened ? lastOpened.slug : null,
        updatedAt: normalizeSlug(state.updatedAt || state.updated_at) || fallback.updatedAt,
    };
}

function emit(name, detail = {}) {
    if (typeof window === 'undefined' || typeof window.dispatchEvent !== 'function') {
        return;
    }

    window.dispatchEvent(new CustomEvent(name, { detail }));
}

export function createStore(courseSlug, rawLessons) {
    const normalizedCourseSlug = normalizeSlug(courseSlug);
    const lessons = normalizeLessons(rawLessons);
    const key = courseKey(normalizedCourseSlug);

    function read() {
        return normalizeState(readJson(key), normalizedCourseSlug, lessons);
    }

    function write(state, reason = 'write') {
        const snapshot = normalizeState({
            ...state,
            updatedAt: new Date().toISOString(),
        }, normalizedCourseSlug, lessons);

        writeJson(key, snapshot);
        emit(UPDATED_EVENT, {
            courseSlug: normalizedCourseSlug,
            state: snapshot,
            reason,
        });

        return snapshot;
    }

    function findLesson(slug) {
        const normalized = normalizeSlug(slug);

        return lessons.find((lesson) => lesson.slug === normalized) || null;
    }

    function getLessonStatus(slug, state = null) {
        const snapshot = state || read();
        const normalized = normalizeSlug(slug);

        if (snapshot.completedLessons.includes(normalized)) {
            return 'completed';
        }

        if (!snapshot.unlockedLessons.includes(normalized)) {
            return 'locked';
        }

        return snapshot.currentLessonSlug === normalized ? 'current' : 'available';
    }

    function markLessonOpened(slug) {
        const lesson = findLesson(slug);
        const state = read();

        if (!lesson || !state.unlockedLessons.includes(lesson.slug)) {
            return state;
        }

        return write({
            ...state,
            lastOpenedLessonSlug: lesson.slug,
            currentLessonSlug: state.completedLessons.includes(lesson.slug)
                ? state.currentLessonSlug
                : lesson.slug,
        }, 'lesson-opened');
    }

    function markLessonCompleted(slug, score = {}) {
        const lesson = findLesson(slug);
        const state = read();

        if (!lesson) {
            return state;
        }

        const completed = state.completedLessons.includes(lesson.slug)
            ? state.completedLessons
            : [...state.completedLessons, lesson.slug];
        const unlocked = new Set(state.unlockedLessons);
        unlocked.add(lesson.slug);

        if (lesson.next_lesson_slug) {
            unlocked.add(lesson.next_lesson_slug);
        }

        const completedAt = new Date().toISOString();
        const nextCurrent = lesson.next_lesson_slug && unlocked.has(lesson.next_lesson_slug)
            ? lesson.next_lesson_slug
            : state.currentLessonSlug;
        const snapshot = write({
            ...state,
            unlockedLessons: lessons.filter((item) => unlocked.has(item.slug)).map((item) => item.slug),
            completedLessons: completed,
            currentLessonSlug: nextCurrent,
            lastOpenedLessonSlug: lesson.slug,
            lessonScores: {
                ...state.lessonScores,
                [lesson.slug]: {
                    scorePercent: Number(score.scorePercent || 0),
                    correct: Number(score.correct || 0),
                    total: Number(score.total || 0),
                    completedAt,
                },
            },
        }, 'lesson-completed');

        writeJson(lessonKey(normalizedCourseSlug, lesson.slug), {
            lessonSlug: lesson.slug,
            courseSlug: normalizedCourseSlug,
            completed: true,
            ...snapshot.lessonScores[lesson.slug],
        });

        emit(COMPLETED_EVENT, {
            courseSlug: normalizedCourseSlug,
            lessonSlug: lesson.slug,
            state: snapshot,
            score,
        });

        return snapshot;
    }

    function resetLesson(slug) {
        const lesson = findLesson(slug);

        if (!lesson) {
            return read();
        }

        const index = lessons.findIndex((item) => item.slug === lesson.slug);
        const affected = new Set(lessons.slice(Math.max(index, 0)).map((item) => item.slug));
        const state = read();
        const lessonScores = { ...state.lessonScores };

        affected.forEach((affectedSlug) => {
            delete lessonScores[affectedSlug];
            removeKey(lessonKey(normalizedCourseSlug, affectedSlug));
        });

        return write({
            ...state,
            completedLessons: state.completedLessons.filter((completedSlug) => !affected.has(completedSlug)),
            unlockedLessons: state.unlockedLessons.filter((unlockedSlug) => !affected.has(unlockedSlug) || unlockedSlug === lesson.slug),
            currentLessonSlug: lesson.slug,
            lessonScores,
        }, 'lesson-reset');
    }

    function reset() {
        lessons.forEach((lesson) => removeKey(lessonKey(normalizedCourseSlug, lesson.slug)));
        removeKey(key);

        const snapshot = defaultState(normalizedCourseSlug, lessons);
        writeJson(key, snapshot);
        emit(RESET_EVENT, {
            courseSlug: normalizedCourseSlug,
            state: snapshot,
        });
        emit(UPDATED_EVENT, {
            courseSlug: normalizedCourseSlug,
            state: snapshot,
            reason: 'course-reset',
        });

        return snapshot;
    }

    function summary(state = null) {
        const snapshot = state || read();

        return {
            completedLessons: snapshot.completedLessons.length,
            totalLessons: lessons.length,
            currentLessonSlug: snapshot.currentLessonSlug,
            lastOpenedLessonSlug: snapshot.lastOpenedLessonSlug,
            completedAllLessons: lessons.length > 0 && snapshot.completedLessons.length >= lessons.length,
        };
    }

    return {
        courseSlug: normalizedCourseSlug,
        lessons,
        key,
        lessonKey: (slug) => lessonKey(normalizedCourseSlug, slug),
        read,
        write,
        findLesson,
        getLessonStatus,
        markLessonOpened,
        markLessonCompleted,
        resetLesson,
        reset,
        summary,
    };
}

const api = {
    createStore,
    events: {
        ready: READY_EVENT,
        updated: UPDATED_EVENT,
        completed: COMPLETED_EVENT,
        reset: RESET_EVENT,
    },
};

if (typeof window !== 'undefined') {
    window.TheoryCourseProgress = api;
    emit(READY_EVENT, { api });
}

export default api;
