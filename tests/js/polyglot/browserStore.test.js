import { afterEach, beforeEach, describe, expect, test, vi } from 'vitest';
import {
    POLYGLOT_COURSE_PROGRESS_UPDATED_EVENT,
    POLYGLOT_LESSON_COMPLETED_EVENT,
    subscribeToProgressSync,
} from '../../../public/js/polyglot/progress/events.js';
import { createCourseStore, createLessonStore } from '../../../public/js/polyglot/progress/browserStore.js';
import {
    buildCourseProgressKey,
    buildLessonProgressKey,
} from '../../../public/js/polyglot/progress/storage.js';

const manifest = [
    {
        slug: 'polyglot-to-be-a1',
        lesson_order: 1,
        previous_lesson_slug: null,
        next_lesson_slug: 'polyglot-there-is-there-are-a1',
    },
    {
        slug: 'polyglot-there-is-there-are-a1',
        lesson_order: 2,
        previous_lesson_slug: 'polyglot-to-be-a1',
        next_lesson_slug: null,
    },
];

const a2Manifest = [
    ['polyglot-present-perfect-basic-a2', null, 'polyglot-present-perfect-vs-past-simple-a2'],
    ['polyglot-present-perfect-vs-past-simple-a2', 'polyglot-present-perfect-basic-a2', 'polyglot-first-conditional-a2'],
    ['polyglot-first-conditional-a2', 'polyglot-present-perfect-vs-past-simple-a2', 'polyglot-be-going-to-a2'],
    ['polyglot-be-going-to-a2', 'polyglot-first-conditional-a2', 'polyglot-should-ought-to-a2'],
    ['polyglot-should-ought-to-a2', 'polyglot-be-going-to-a2', 'polyglot-must-have-to-a2'],
    ['polyglot-must-have-to-a2', 'polyglot-should-ought-to-a2', 'polyglot-gerund-vs-infinitive-a2'],
    ['polyglot-gerund-vs-infinitive-a2', 'polyglot-must-have-to-a2', 'polyglot-past-continuous-a2'],
    ['polyglot-past-continuous-a2', 'polyglot-gerund-vs-infinitive-a2', 'polyglot-present-perfect-time-expressions-a2'],
    ['polyglot-present-perfect-time-expressions-a2', 'polyglot-past-continuous-a2', 'polyglot-relative-clauses-a2'],
    ['polyglot-relative-clauses-a2', 'polyglot-present-perfect-time-expressions-a2', 'polyglot-passive-voice-basics-a2'],
    ['polyglot-passive-voice-basics-a2', 'polyglot-relative-clauses-a2', 'polyglot-reported-speech-basics-a2'],
    ['polyglot-reported-speech-basics-a2', 'polyglot-passive-voice-basics-a2', 'polyglot-used-to-a2'],
    ['polyglot-used-to-a2', 'polyglot-reported-speech-basics-a2', 'polyglot-question-tags-basics-a2'],
    ['polyglot-question-tags-basics-a2', 'polyglot-used-to-a2', 'polyglot-second-conditional-basics-a2'],
    ['polyglot-second-conditional-basics-a2', 'polyglot-question-tags-basics-a2', 'polyglot-final-drill-a2'],
    ['polyglot-final-drill-a2', 'polyglot-second-conditional-basics-a2', null],
].map(([slug, previous_lesson_slug, next_lesson_slug], index) => ({
    slug,
    lesson_order: index + 1,
    previous_lesson_slug,
    next_lesson_slug,
}));

function completeLesson(store, lessonSlug, courseSlug) {
    let progress = store.write({
        lesson_slug: lessonSlug,
        course_slug: courseSlug,
        rolling_results: Array.from({ length: 99 }, () => 5),
        total_attempts: 99,
        correct_attempts: 99,
    }, 'prefill');

    progress = store.markAttempt(progress, true);

    expect(progress.lesson_completed).toBe(true);

    return progress;
}

describe('browserStore', () => {
    beforeEach(() => {
        window.localStorage.clear();
    });

    afterEach(() => {
        vi.restoreAllMocks();
        window.localStorage.clear();
    });

    test('course state is synchronized from lesson attempts and unlocks the next lesson', () => {
        const courseStore = createCourseStore('polyglot-english-a1', manifest);
        const lessonStore = createLessonStore({
            lessonSlug: 'polyglot-to-be-a1',
            courseSlug: 'polyglot-english-a1',
            questionCount: 24,
            courseStore,
            sourceId: courseStore.sourceId,
        });

        let progress = lessonStore.write({
            lesson_slug: 'polyglot-to-be-a1',
            course_slug: 'polyglot-english-a1',
            rolling_results: Array.from({ length: 99 }, () => 5),
            total_attempts: 99,
            correct_attempts: 99,
            current_queue_index: 3,
        }, 'prefill');

        progress = lessonStore.markAttempt(progress, true);

        const courseState = courseStore.read();

        expect(progress.lesson_completed).toBe(true);
        expect(courseState.completed_lessons).toContain('polyglot-to-be-a1');
        expect(courseState.lessons['polyglot-there-is-there-are-a1'].unlocked).toBe(true);
    });

    test('course reset clears course and lesson keys', () => {
        const courseStore = createCourseStore('polyglot-english-a1', manifest);
        const lessonStore = createLessonStore({
            lessonSlug: 'polyglot-to-be-a1',
            courseSlug: 'polyglot-english-a1',
            questionCount: 24,
            courseStore,
            sourceId: courseStore.sourceId,
        });

        lessonStore.markAttempt(lessonStore.read(), true);
        courseStore.reset();

        expect(window.localStorage.getItem(buildLessonProgressKey('polyglot-to-be-a1'))).toBe(null);
        expect(JSON.parse(window.localStorage.getItem(buildCourseProgressKey('polyglot-english-a1'))).unlocked_lessons).toEqual([
            'polyglot-to-be-a1',
        ]);
    });

    test('completing every lesson computes a complete-course summary and reset rehydrates predictably', () => {
        const courseStore = createCourseStore('polyglot-english-a1', manifest);
        const lessonOneStore = createLessonStore({
            lessonSlug: 'polyglot-to-be-a1',
            courseSlug: 'polyglot-english-a1',
            questionCount: 24,
            courseStore,
            sourceId: courseStore.sourceId,
        });
        const lessonTwoStore = createLessonStore({
            lessonSlug: 'polyglot-there-is-there-are-a1',
            courseSlug: 'polyglot-english-a1',
            questionCount: 24,
            courseStore,
            sourceId: courseStore.sourceId,
        });

        let lessonOneProgress = lessonOneStore.write({
            lesson_slug: 'polyglot-to-be-a1',
            course_slug: 'polyglot-english-a1',
            rolling_results: Array.from({ length: 99 }, () => 5),
            total_attempts: 99,
            correct_attempts: 99,
        }, 'prefill');
        lessonOneProgress = lessonOneStore.markAttempt(lessonOneProgress, true);

        let lessonTwoProgress = lessonTwoStore.write({
            lesson_slug: 'polyglot-there-is-there-are-a1',
            course_slug: 'polyglot-english-a1',
            rolling_results: Array.from({ length: 99 }, () => 5),
            total_attempts: 99,
            correct_attempts: 99,
        }, 'prefill');
        lessonTwoProgress = lessonTwoStore.markAttempt(lessonTwoProgress, true);

        const completeSummary = courseStore.getSummary();

        expect(lessonOneProgress.lesson_completed).toBe(true);
        expect(lessonTwoProgress.lesson_completed).toBe(true);
        expect(completeSummary.completed_all_lessons).toBe(true);
        expect(completeSummary.completed_lessons).toBe(2);

        const resetState = courseStore.reset();
        const resetSummary = courseStore.getSummary(resetState);

        expect(resetSummary.completed_all_lessons).toBe(false);
        expect(resetSummary.completed_lessons).toBe(0);
        expect(resetState.unlocked_lessons).toEqual(['polyglot-to-be-a1']);
    });

    test('custom events and storage sync hooks trigger rehydration callbacks', () => {
        const onSync = vi.fn();
        const unsubscribe = subscribeToProgressSync({
            courseSlug: 'polyglot-english-a1',
            lessonSlug: 'polyglot-to-be-a1',
            ignoreSourceId: 'local-tab',
            onSync,
        });

        window.dispatchEvent(new CustomEvent(POLYGLOT_COURSE_PROGRESS_UPDATED_EVENT, {
            detail: {
                sourceId: 'other-tab',
                courseSlug: 'polyglot-english-a1',
            },
        }));

        const storageEvent = new Event('storage');
        Object.defineProperty(storageEvent, 'key', {
            value: buildCourseProgressKey('polyglot-english-a1'),
        });
        window.dispatchEvent(storageEvent);

        window.dispatchEvent(new CustomEvent(POLYGLOT_LESSON_COMPLETED_EVENT, {
            detail: {
                sourceId: 'local-tab',
                courseSlug: 'polyglot-english-a1',
                lessonSlug: 'polyglot-to-be-a1',
            },
        }));

        unsubscribe();

        expect(onSync).toHaveBeenCalledTimes(2);
        expect(onSync.mock.calls[0][0].type).toBe('custom');
        expect(onSync.mock.calls[1][0].type).toBe('storage');
    });

    test('lesson sixteen completion rehydrates A2 into a fully complete course and reset restarts from lesson one', () => {
        const courseStore = createCourseStore('polyglot-english-a2', a2Manifest);
        const courseSlug = 'polyglot-english-a2';

        a2Manifest.slice(0, -1).forEach((lesson) => {
            const lessonStore = createLessonStore({
                lessonSlug: lesson.slug,
                courseSlug,
                questionCount: 24,
                courseStore,
                sourceId: courseStore.sourceId,
            });

            completeLesson(lessonStore, lesson.slug, courseSlug);
        });

        const finalLessonStore = createLessonStore({
            lessonSlug: 'polyglot-final-drill-a2',
            courseSlug,
            questionCount: 24,
            courseStore,
            sourceId: courseStore.sourceId,
        });

        completeLesson(finalLessonStore, 'polyglot-final-drill-a2', courseSlug);

        const completeSummary = courseStore.getSummary();
        const rehydratedCourseStore = createCourseStore('polyglot-english-a2', a2Manifest);
        const rehydratedSummary = rehydratedCourseStore.getSummary();
        const resetState = rehydratedCourseStore.reset();
        const resetSummary = rehydratedCourseStore.getSummary(resetState);

        expect(completeSummary.completed_lessons).toBe(16);
        expect(completeSummary.completed_all_lessons).toBe(true);
        expect(completeSummary.current_lesson_slug).toBe('polyglot-final-drill-a2');
        expect(rehydratedSummary.completed_lessons).toBe(16);
        expect(rehydratedSummary.completed_all_lessons).toBe(true);
        expect(resetSummary.completed_all_lessons).toBe(false);
        expect(resetSummary.completed_lessons).toBe(0);
        expect(resetState.unlocked_lessons).toEqual(['polyglot-present-perfect-basic-a2']);
    });
});
