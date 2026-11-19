/* Finite M40 wrapper; no owner or question data is inferred here. */
(function (root) {
    'use strict';
    root.m40PracticeUi = config => root.authoredPracticeUi(config, {
        answerAttribute: 'data-m40-answer',
        // This author prompt explicitly asks for the non-defining commas.
        punctuationSensitiveIds: ['m40-b5-relative'],
    });
})(typeof globalThis !== 'undefined' ? globalThis : this);
