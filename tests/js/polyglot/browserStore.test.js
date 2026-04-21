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
});
