import { describe, expect, test } from 'vitest';
import {
    LESSON_PROGRESS_WINDOW,
    buildDefaultLessonProgress,
    hasLessonProgress,
    markLessonAttempt,
    normalizeLessonProgress,
    resetLessonProgress,
} from '../../../public/js/polyglot/progress/lessonProgressState.js';

describe('lessonProgressState', () => {
    test('bad input is reset to the default contract', () => {
        const snapshot = normalizeLessonProgress('broken', 'polyglot-to-be-a1', {
            courseSlug: 'polyglot-english-a1',
            questionCount: 24,
        });

        expect(snapshot).toEqual(buildDefaultLessonProgress({
            lessonSlug: 'polyglot-to-be-a1',
            courseSlug: 'polyglot-english-a1',
        }));
    });

    test('rolling results are capped at 100 and keep valid 0..5 rating values', () => {
        const snapshot = normalizeLessonProgress({
            lesson_slug: 'polyglot-to-be-a1',
            rolling_results: [
                ...Array.from({ length: 80 }, () => 5),
                ...Array.from({ length: 40 }, (_, index) => index % 2 === 0 ? 0 : 3),
            ],
            total_attempts: 120,
            correct_attempts: 90,
        }, 'polyglot-to-be-a1', {
            questionCount: 24,
        });

        expect(snapshot.rolling_results).toHaveLength(LESSON_PROGRESS_WINDOW);
        expect(snapshot.rolling_results.every((value) => value >= 0 && value <= 5)).toBe(true);
        expect(snapshot.rolling_results).toContain(3);
    });

    test('completion rule still requires 100 answers with average >= 4.5', () => {
        const passing = normalizeLessonProgress({
            lesson_slug: 'polyglot-to-be-a1',
            rolling_results: [
                ...Array.from({ length: 90 }, () => 5),
                ...Array.from({ length: 10 }, () => 0),
            ],
            total_attempts: 100,
            correct_attempts: 90,
        }, 'polyglot-to-be-a1');
        const failing = normalizeLessonProgress({
            lesson_slug: 'polyglot-to-be-a1',
            rolling_results: [
                ...Array.from({ length: 89 }, () => 5),
                ...Array.from({ length: 11 }, () => 0),
            ],
            total_attempts: 100,
            correct_attempts: 89,
        }, 'polyglot-to-be-a1');

        expect(passing.lesson_completed).toBe(true);
        expect(failing.lesson_completed).toBe(false);
    });

    test('mark attempt updates counts for correct and wrong answers', () => {
        const afterCorrect = markLessonAttempt(buildDefaultLessonProgress({
            lessonSlug: 'polyglot-to-be-a1',
        }), true, {
            lessonSlug: 'polyglot-to-be-a1',
            questionCount: 24,
        });
        const afterWrong = markLessonAttempt(afterCorrect, false, {
            lessonSlug: 'polyglot-to-be-a1',
            questionCount: 24,
        });

        expect(afterCorrect.total_attempts).toBe(1);
        expect(afterCorrect.correct_attempts).toBe(1);
        expect(afterCorrect.rolling_results).toEqual([5]);
        expect(afterWrong.total_attempts).toBe(2);
        expect(afterWrong.correct_attempts).toBe(1);
        expect(afterWrong.rolling_results).toEqual([5, 0]);
        expect(hasLessonProgress(afterWrong)).toBe(true);
    });

    test('old no-version state does not break normalization and reset is predictable', () => {
        const migrated = normalizeLessonProgress({
            slug: 'polyglot-to-be-a1',
            current_queue_index: 7,
            rolling_results: [5, 5, 0],
            total_attempts: 3,
            correct_attempts: 2,
            lesson_completed: false,
        }, 'polyglot-to-be-a1', {
            courseSlug: 'polyglot-english-a1',
            questionCount: 24,
        });
        const reset = resetLessonProgress('polyglot-to-be-a1', {
            courseSlug: 'polyglot-english-a1',
        });

        expect(migrated.lesson_slug).toBe('polyglot-to-be-a1');
        expect(migrated.course_slug).toBe('polyglot-english-a1');
        expect(migrated.current_queue_index).toBe(7);
        expect(reset.rolling_results).toEqual([]);
        expect(reset.total_attempts).toBe(0);
        expect(reset.lesson_completed).toBe(false);
    });
});
