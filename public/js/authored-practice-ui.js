/* Shared authored-practice mechanics; owner and semantic opt-ins live in finite wrappers. */
(function (root, factory) {
    if (typeof module === 'object' && module.exports) module.exports = factory(require('./english-answer-variants.js'));
    else root.authoredPracticeUi = factory(root.EnglishAnswerVariants);
})(typeof globalThis !== 'undefined' ? globalThis : this, function (variants) {
    'use strict';
    const typography = value => String(value ?? '').replace(/[‘’ʼ`]/gu, "'").trim().replace(/\s+/gu, ' ');
    // Comparison only; never mutate what the learner sees. Internal sentence punctuation matters.
    const normalise = value => typography(value).toLowerCase().replace(/[.!?…]+$/u, '')
        .replace(/\s*([,;:.!?])\s*/gu, '$1 ').trim();
    const word = value => typography(value).toLowerCase().replace(/^[.,;:!?]+|[.,;:!?]+$/gu, '');
    return function authoredPracticeUi(config, policy = {}) {
        const cases = config.cases || [];
        return {
            cases,
            answers: cases.map(task => task.controls.map(control => control.kind === 'multi' ? [] : '')),
            checked: cases.map(() => false),
            banks: cases.map((task, i) => task.controls.map((control, p) => {
                const tokens = (control.tokens || []).map((value, index) => ({value, index}));
                let seed = (i + 1) * 104729 + (p + 1) * 7919;
                for (let j = tokens.length - 1; j > 0; j--) {
                    seed = (seed * 1664525 + 1013904223) >>> 0;
                    const k = seed % (j + 1); [tokens[j], tokens[k]] = [tokens[k], tokens[j]];
                }
                if (tokens.length > 1 && tokens.every((token, j) => token.index === j)) tokens.push(tokens.shift());
                return tokens;
            })),
            history: cases.map(task => task.controls.map(() => [])),
            normalise,
            edited(i) { this.checked[i] = false; },
            setAnswer(i, p, value) {
                if (this.cases[i].controls[p].kind === 'multi') {
                    const current = this.answers[i][p];
                    this.answers[i][p] = current.includes(value) ? current.filter(v => v !== value) : [...current, value];
                } else this.answers[i][p] = value;
                this.edited(i);
            },
            selected(i, p, value) {
                const answer = this.answers[i][p];
                return Array.isArray(answer) ? answer.includes(value) : answer === value;
            },
            cycleAnswer(i, p, direction, event) {
                const options = this.cases[i].controls[p].options;
                let current = options.findIndex(option => option.value === this.answers[i][p]);
                if (current < 0) current = options.findIndex(option => option.value === event.target.getAttribute(policy.answerAttribute || 'data-authored-answer'));
                if (current < 0) current = 0;
                const next = (current + direction + options.length) % options.length;
                this.setAnswer(i, p, options[next].value);
                const buttons = event.target.closest('fieldset').querySelectorAll('[role="radio"]');
                buttons[next]?.focus();
            },
            partCorrect(i, p) {
                const control = this.cases[i].controls[p], answer = this.answers[i][p];
                if (control.kind === 'multi') {
                    return JSON.stringify([...answer].sort()) === JSON.stringify([...control.answer].sort());
                }
                if (!normalise(answer)) return false;
                const accepted = control.accepted || [control.answer];
                if (control.kind !== 'manual') return accepted.some(value => normalise(value) === normalise(answer));
                return accepted.some(value => (policy.explicitManualVariants || !variants ? [value] : variants.variants(value))
                    .filter(candidate => !(policy.keepFirstClauseIds || []).includes(control.id)
                        || normalise(candidate).split(',')[0] === normalise(control.answer).split(',')[0])
                    .some(candidate => normalise(candidate) === normalise(answer)));
            },
            isCorrect(i) { return this.cases[i].controls.every((_, p) => this.partCorrect(i, p)); },
            check(i) { this.checked[i] = true; },
            reset(i) {
                this.answers[i] = this.cases[i].controls.map(control => control.kind === 'multi' ? [] : '');
                this.history[i] = this.cases[i].controls.map(() => []); this.checked[i] = false;
            },
            get score() { return this.cases.filter((_, i) => this.checked[i] && this.isCorrect(i)).length; },
            usedTokens(i, p) {
                const words = typography(this.answers[i][p]).split(/\s+/u).map(word), used = new Set();
                const history = this.history[i][p], bank = this.banks[i][p];
                const order = [...bank].sort((a, b) => {
                    const length = b.value.split(/\s+/u).length - a.value.split(/\s+/u).length;
                    const rank = token => history.includes(token.index) ? history.indexOf(token.index) : history.length + bank.indexOf(token);
                    return length || rank(a) - rank(b);
                });
                for (let j = 0; j < words.length;) {
                    const token = order.find(candidate => {
                        const parts = candidate.value.split(/\s+/u).map(word);
                        return !used.has(candidate.index) && parts.every((part, offset) => part === words[j + offset]);
                    });
                    if (token) { used.add(token.index); j += token.value.split(/\s+/u).length; } else j++;
                }
                return used;
            },
            tokenUsed(i, p, index) { return this.usedTokens(i, p).has(index); },
            appendToken(i, p, index) {
                if (this.tokenUsed(i, p, index)) return;
                const token = this.banks[i][p].find(candidate => candidate.index === index);
                if (!token) return;
                this.history[i][p] = [...this.history[i][p].filter(v => v !== index), index];
                this.answers[i][p] = (String(this.answers[i][p]).trim() + ' ' + token.value).trim();
                this.edited(i);
            },
        };
    };
});
