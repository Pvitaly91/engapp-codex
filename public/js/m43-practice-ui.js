/* M43: explicit reviewed aliases only; no global contraction expansion. */
(function (root) {
    'use strict';
    root.m43PracticeUi = config => root.authoredPracticeUi(config, {
        answerAttribute: 'data-m43-answer',
        explicitManualVariants: true,
    });
})(typeof globalThis !== 'undefined' ? globalThis : this);
