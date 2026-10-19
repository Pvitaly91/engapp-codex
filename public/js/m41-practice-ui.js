/* Finite M41 author variants only; shared mechanics retain their legacy defaults. */
(function (root) {
    'use strict';
    root.m41PracticeUi = config => root.authoredPracticeUi(config, {
        answerAttribute: 'data-m41-answer',
        explicitManualVariants: true,
    });
})(typeof globalThis !== 'undefined' ? globalThis : this);
