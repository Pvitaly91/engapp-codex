import { describe, expect, test } from 'vitest';
import {
    buildDefaultCourseProgress,
    computeCourseSummary,
    normalizeCourseProgress,
    resetCourseProgress,
} from '../../../public/js/polyglot/progress/courseProgressState.js';

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
        next_lesson_slug: 'polyglot-have-got-has-got-a1',
    },
    {
        slug: 'polyglot-have-got-has-got-a1',
        lesson_order: 3,
        previous_lesson_slug: 'polyglot-there-is-there-are-a1',
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

describe('courseProgressState', () => {
    test('lesson 1 is unlocked by default', () => {
        const snapshot = normalizeCourseProgress(null, 'polyglot-english-a1', manifest);

        expect(snapshot.unlocked_lessons).toEqual(['polyglot-to-be-a1']);
        expect(snapshot.current_lesson_slug).toBe('polyglot-to-be-a1');
        expect(snapshot.lessons['polyglot-there-is-there-are-a1'].unlocked).toBe(false);
    });

    test('completed previous lesson unlocks the next lesson', () => {
        const snapshot = normalizeCourseProgress({
            completed_lessons: ['polyglot-to-be-a1'],
            lessons: {
                'polyglot-to-be-a1': {
                    completed: true,
                    unlocked: true,
                    has_progress: true,
                },
            },
        }, 'polyglot-english-a1', manifest);

        expect(snapshot.lessons['polyglot-to-be-a1'].completed).toBe(true);
        expect(snapshot.lessons['polyglot-there-is-there-are-a1'].unlocked).toBe(true);
    });

    test('broken course state is rebuilt from lesson states', () => {
        const snapshot = normalizeCourseProgress('broken', 'polyglot-english-a1', manifest, {
            lessonStates: {
                'polyglot-to-be-a1': {
                    lesson_slug: 'polyglot-to-be-a1',
                    course_slug: 'polyglot-english-a1',
                    rolling_results: Array.from({ length: 100 }, () => 5),
                    total_attempts: 100,
                    correct_attempts: 100,
                    lesson_completed: true,
                },
                'polyglot-there-is-there-are-a1': {
                    lesson_slug: 'polyglot-there-is-there-are-a1',
                    course_slug: 'polyglot-english-a1',
                    rolling_results: [5],
                    total_attempts: 1,
                    correct_attempts: 1,
                    lesson_completed: false,
                },
            },
        });

        expect(snapshot.completed_lessons).toEqual(['polyglot-to-be-a1']);
        expect(snapshot.unlocked_lessons).toEqual([
            'polyglot-to-be-a1',
            'polyglot-there-is-there-are-a1',
        ]);
        expect(snapshot.lessons['polyglot-there-is-there-are-a1'].has_progress).toBe(true);
    });

    test('course summary and reset stay consistent', () => {
        const completed = normalizeCourseProgress({
            completed_lessons: ['polyglot-to-be-a1'],
            current_lesson_slug: 'polyglot-there-is-there-are-a1',
        }, 'polyglot-english-a1', manifest);
        const summary = computeCourseSummary(completed, manifest);
        const reset = resetCourseProgress('polyglot-english-a1', manifest);

        expect(summary.completed_lessons).toBe(1);
        expect(summary.total_lessons).toBe(3);
        expect(reset).toEqual(buildDefaultCourseProgress('polyglot-english-a1', manifest));
    });

    test('all lessons completed summary flips the complete-course flag', () => {
        const completed = normalizeCourseProgress({
            completed_lessons: manifest.map((lesson) => lesson.slug),
            current_lesson_slug: 'polyglot-have-got-has-got-a1',
            last_opened_lesson_slug: 'polyglot-have-got-has-got-a1',
        }, 'polyglot-english-a1', manifest);
        const summary = computeCourseSummary(completed, manifest);

        expect(summary.completed_lessons).toBe(3);
        expect(summary.total_lessons).toBe(3);
        expect(summary.completed_all_lessons).toBe(true);
    });

    test('reset after full completion restores the initial lesson-unlock state', () => {
        const reset = resetCourseProgress('polyglot-english-a1', manifest);
        const summary = computeCourseSummary(reset, manifest);

        expect(reset.unlocked_lessons).toEqual(['polyglot-to-be-a1']);
        expect(reset.completed_lessons).toEqual([]);
        expect(summary.completed_all_lessons).toBe(false);
        expect(summary.current_lesson_slug).toBe('polyglot-to-be-a1');
    });

    test('fully completed A2 manifest reports a complete 16 lesson summary', () => {
        const completed = normalizeCourseProgress({
            completed_lessons: a2Manifest.map((lesson) => lesson.slug),
            current_lesson_slug: 'polyglot-final-drill-a2',
            last_opened_lesson_slug: 'polyglot-final-drill-a2',
        }, 'polyglot-english-a2', a2Manifest);
        const summary = computeCourseSummary(completed, a2Manifest);

        expect(summary.completed_lessons).toBe(16);
        expect(summary.total_lessons).toBe(16);
        expect(summary.completed_all_lessons).toBe(true);
        expect(summary.current_lesson_slug).toBe('polyglot-final-drill-a2');
        expect(summary.last_opened_lesson_slug).toBe('polyglot-final-drill-a2');
    });

    test('resetting a fully complete A2 course reopens only lesson one', () => {
        const reset = resetCourseProgress('polyglot-english-a2', a2Manifest);
        const summary = computeCourseSummary(reset, a2Manifest);

        expect(reset.unlocked_lessons).toEqual(['polyglot-present-perfect-basic-a2']);
        expect(reset.completed_lessons).toEqual([]);
        expect(summary.completed_all_lessons).toBe(false);
        expect(summary.current_lesson_slug).toBe('polyglot-present-perfect-basic-a2');
    });
});
