/* M45 owns its selector and finite aliases; shared practice mechanics stay unchanged. */
(function (root) {
    'use strict';
    root.m45PracticeUi = function m45PracticeUi(config) {
        const component = root.authoredPracticeUi(config, {
            answerAttribute: 'data-m45-answer',
            explicitManualVariants: true,
        });
        const edited = component.edited;
        const usedTokens = component.usedTokens;
        component.usedTokens = function (i, p) {
            // An empty field contains no punctuation token; keep this M45-scoped.
            const answer = String(this.answers[i][p] ?? '');
            if (!answer.trim()) return new Set();
            const used = usedTokens.call(this, i, p);
            const bank = this.banks[i][p], history = this.history[i][p];
            const questions = bank.filter(token => token.value === '?');
            // Reconcile only M45's bare ? instances, not periods or word tokens.
            questions.forEach(token => used.delete(token.index));
            const rank = token => {
                const clicked = history.indexOf(token.index);
                return clicked < 0 ? history.length + bank.indexOf(token) : clicked;
            };
            questions.sort((a, b) => rank(a) - rank(b));
            const count = (answer.match(/\?/gu) || []).length;
            questions.slice(0, count).forEach(token => used.add(token.index));
            return used;
        };
        component.edited = function (i) {
            this.cases[i].controls.forEach((control, p) => {
                if (control.kind === 'manual' && !String(this.answers[i][p]).trim()) this.history[i][p] = [];
            });
            edited.call(this, i);
        };
        return component;
    };
})(typeof globalThis !== 'undefined' ? globalThis : this);
