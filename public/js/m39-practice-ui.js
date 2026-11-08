/* Finite M39 wrapper; no owner or question data is inferred here. */
(function (root) {
    'use strict';
    root.m39PracticeUi = config => root.authoredPracticeUi(config, {
        answerAttribute: 'data-m39-answer',
        keepFirstClauseIds: ['c2-1-answer'],
        // Restrictive relative clause: commas would change the chosen group.
        punctuationSensitiveIds: ['c1-4-answer'],
    });
})(typeof globalThis !== 'undefined' ? globalThis : this);
