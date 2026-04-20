import {
    POLYGLOT_COURSE_PROGRESS_UPDATED_EVENT,
    POLYGLOT_COURSE_RESET_EVENT,
    POLYGLOT_LESSON_COMPLETED_EVENT,
    POLYGLOT_LESSON_PROGRESS_UPDATED_EVENT,
    POLYGLOT_PROGRESS_READY_EVENT,
    subscribeToProgressSync,
} from './polyglot/progress/events.js';
import { computeCourseSummary, computeLessonStatus } from './polyglot/progress/courseProgressState.js';
import { createCourseStore, createLessonStore } from './polyglot/progress/browserStore.js';

const polyglotProgressApi = {
    createStore: createCourseStore,
    createCourseStore,
    createLessonStore,
    computeCourseSummary,
    computeLessonStatus,
    subscribeToSync: subscribeToProgressSync,
    events: {
        ready: POLYGLOT_PROGRESS_READY_EVENT,
        lessonProgressUpdated: POLYGLOT_LESSON_PROGRESS_UPDATED_EVENT,
        courseProgressUpdated: POLYGLOT_COURSE_PROGRESS_UPDATED_EVENT,
        lessonCompleted: POLYGLOT_LESSON_COMPLETED_EVENT,
        courseReset: POLYGLOT_COURSE_RESET_EVENT,
    },
};

if (typeof window !== 'undefined') {
    window.PolyglotCourseProgress = polyglotProgressApi;
    window.dispatchEvent(new CustomEvent(POLYGLOT_PROGRESS_READY_EVENT, {
        detail: {
            api: polyglotProgressApi,
        },
    }));
}

export default polyglotProgressApi;
