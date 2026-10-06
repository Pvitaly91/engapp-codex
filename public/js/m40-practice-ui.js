/* Finite M40 wrapper; no owner or question data is inferred here. */
(function (root) {
    'use strict';
    root.m40PracticeUi = config => root.authoredPracticeUi(config, {"answerAttribute":"data-m40-answer"});
})(typeof globalThis !== 'undefined' ? globalThis : this);
