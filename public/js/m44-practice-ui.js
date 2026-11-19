/* M44: own answer selectors and finite reviewed aliases; shared mechanics stay unchanged. */
(function (root) {
    'use strict';
    root.m44PracticeUi = config => {
        const component = root.authoredPracticeUi(config, {
            answerAttribute: 'data-m44-answer',
            explicitManualVariants: true,
        });
        const edited = component.edited;
        component.edited = function (i) {
            this.cases[i].controls.forEach((control, p) => {
                if (control.kind === 'manual' && !String(this.answers[i][p]).trim()) this.history[i][p] = [];
            });
            edited.call(this, i);
        };
        return component;
    };
})(typeof globalThis !== 'undefined' ? globalThis : this);
