const verbs = Array.isArray(window.__VERBS__) ? window.__VERBS__ : [];
const i18n = window.__VERBS_I18N__ || {};
const storageKey = 'verbs_test_state_v1';
const signature = createSignature(verbs);

const els = {
    startBtn: document.getElementById('startBtn'),
    restartBtn: document.getElementById('restartBtn'),
    checkBtn: document.getElementById('checkBtn'),
    revealBtn: document.getElementById('revealBtn'),
    nextBtn: document.getElementById('nextBtn'),
    mode: document.getElementById('mode'),
    askWhat: document.getElementById('askWhat'),
    count: document.getElementById('count'),
    showUk: document.getElementById('showUk'),
    baseVerb: document.getElementById('baseVerb'),
    ukVerb: document.getElementById('ukVerb'),
    askLabel: document.getElementById('askLabel'),
    hint: document.getElementById('hint'),
    typingBox: document.getElementById('typingBox'),
    choiceBox: document.getElementById('choiceBox'),
    answerInput: document.getElementById('answerInput'),
    progressText: document.getElementById('progressText'),
    progressBar: document.getElementById('progressBar'),
    progressPercent: document.getElementById('progressPercent'),
    correct: document.getElementById('correct'),
    wrong: document.getElementById('wrong'),
    feedback: document.getElementById('feedback'),
    doneBox: document.getElementById('doneBox'),
    doneText: document.getElementById('doneText'),
};

const defaultState = () => ({
    settings: {
        mode: 'typing',
        askWhat: 'random',
        count: 10,
        showTranslation: false,
    },
    queue: [],
    pos: 0,
    correct: 0,
    wrong: 0,
    current: null,
    signature,
});

let state = defaultState();

function createSignature(list) {
    return list
        .map((verb) => {
            const f2 = Array.isArray(verb.f2) ? verb.f2.join(',') : '';
            const f3 = Array.isArray(verb.f3) ? verb.f3.join(',') : '';
            return `${verb.base}|${verb.translation}|${verb.f1}|${verb.f4}|${f2}|${f3}`;
        })
        .join(';');
}

function normalize(str) {
    return (str || '').toString().trim().toLowerCase();
}

function shuffle(array) {
    const arr = [...array];
    for (let i = arr.length - 1; i > 0; i -= 1) {
        const j = Math.floor(Math.random() * (i + 1));
        [arr[i], arr[j]] = [arr[j], arr[i]];
    }
    return arr;
}

function saveState() {
    if (!window.localStorage) return;
    const payload = {
        ...state,
        current: state.current
            ? {
                ...state.current,
                answers: Array.isArray(state.current.answers) ? [...state.current.answers] : [],
                answersRaw: Array.isArray(state.current.answersRaw) ? [...state.current.answersRaw] : [],
                options: Array.isArray(state.current.options) ? [...state.current.options] : [],
            }
            : null,
    };
    window.localStorage.setItem(storageKey, JSON.stringify(payload));
}

function loadState() {
    if (!window.localStorage) return null;
    const raw = window.localStorage.getItem(storageKey);
    if (!raw) return null;
    try {
        const parsed = JSON.parse(raw);
        if (parsed.signature !== signature) return null;
        if (!Array.isArray(parsed.queue) || !parsed.queue.length) return null;
        const cleanedQueue = parsed.queue.filter((i) => Number.isInteger(i) && i >= 0 && i < verbs.length);
        if (!cleanedQueue.length) return null;
        const settings = {
            ...defaultState().settings,
            ...(parsed.settings || {}),
        };
        const current = parsed.current && typeof parsed.current === 'object'
            ? {
                verbIndex: parsed.current.verbIndex,
                askKey: parsed.current.askKey,
                answers: Array.isArray(parsed.current.answers) ? parsed.current.answers : [],
                answersRaw: Array.isArray(parsed.current.answersRaw) ? parsed.current.answersRaw : [],
                answered: Boolean(parsed.current.answered),
                wasCorrect: Boolean(parsed.current.wasCorrect),
                options: Array.isArray(parsed.current.options) ? parsed.current.options : [],
            }
            : null;

        return {
            ...defaultState(),
            ...parsed,
            settings,
            queue: cleanedQueue,
            pos: Math.min(Math.max(parsed.pos || 0, 0), cleanedQueue.length),
            current,
            signature,
        };
    } catch (e) {
        return null;
    }
}

function readSettingsFromControls() {
    const countValue = parseInt(els.count?.value ?? '10', 10);
    return {
        mode: els.mode?.value || 'typing',
        askWhat: els.askWhat?.value || 'random',
        count: Number.isNaN(countValue) || countValue < 1 ? verbs.length || 10 : countValue,
        showTranslation: Boolean(els.showUk?.checked),
    };
}

function applySettingsToControls(settings) {
    if (els.mode) els.mode.value = settings.mode;
    if (els.askWhat) els.askWhat.value = settings.askWhat;
    if (els.count) els.count.value = settings.count;
    if (els.showUk) els.showUk.checked = settings.showTranslation;
}

function toggleModeVisibility(mode) {
    if (!els.typingBox || !els.choiceBox) return;
    if (mode === 'choice') {
        els.typingBox.classList.add('hidden');
        els.choiceBox.classList.remove('hidden');
    } else {
        els.typingBox.classList.remove('hidden');
        els.choiceBox.classList.add('hidden');
    }
}

function setFeedback(message, isPositive = false) {
    if (!els.feedback) return;
    els.feedback.textContent = message || '';
    els.feedback.classList.remove('text-success', 'text-destructive');
    if (message) {
        els.feedback.classList.add(isPositive ? 'text-success' : 'text-destructive');
    }
}

function updateProgress() {
    if (!els.progressText || !els.progressBar || !els.progressPercent) return;
    const total = state.queue.length;
    const currentIndex = Math.min(state.pos + 1, total);
    els.progressText.textContent = `${total ? currentIndex : 0} / ${total}`;
    const percent = total ? Math.round((state.pos / total) * 100) : 0;
    els.progressBar.style.width = `${percent}%`;
    els.progressPercent.textContent = `${percent}%`;
}

function updateStats() {
    if (els.correct) els.correct.textContent = state.correct;
    if (els.wrong) els.wrong.textContent = state.wrong;
}

function hintForVerb(verb) {
    if (!verb) return '';
    const base = Array.isArray(verb.base_forms) && verb.base_forms.length ? verb.base_forms.join(' / ') : verb.base;
    const f2 = Array.isArray(verb.f2) ? verb.f2.join(' / ') : verb.f2;
    const f3 = Array.isArray(verb.f3) ? verb.f3.join(' / ') : verb.f3;
    return `Base: ${base} • f1: ${verb.f1} • f2: ${f2} • f3: ${f3} • f4: ${verb.f4}`;
}

function labelForAskKey(key) {
    return {
        f1: i18n.form1 || 'Form 1',
        f2: i18n.form2 || 'Form 2',
        f3: i18n.form3 || 'Form 3',
        f4: i18n.form4 || 'Form 4',
    }[key] || key;
}

function resolveAskKey() {
    if (state.settings.askWhat === 'random') {
        const options = ['f1', 'f2', 'f3', 'f4'];
        return options[Math.floor(Math.random() * options.length)];
    }
    return state.settings.askWhat;
}

function getAnswersForVerb(verb, askKey) {
    if (!verb) return [];
    switch (askKey) {
        case 'f1':
            return verb.f1 ? [verb.f1] : [];
        case 'f2':
            return Array.isArray(verb.f2) ? verb.f2 : [];
        case 'f3':
            return Array.isArray(verb.f3) ? verb.f3 : [];
        case 'f4':
            return verb.f4 ? [verb.f4] : [];
        default:
            return [];
    }
}

function createQueue(count) {
    const baseIndexes = verbs.map((_, idx) => idx);
    let queue = [];
    while (queue.length < count) {
        queue = queue.concat(shuffle(baseIndexes));
    }
    return queue.slice(0, count);
}

function setTranslationVisibility() {
    if (!els.ukVerb) return;
    els.ukVerb.classList.toggle('hidden', !state.settings.showTranslation);
}

function formatAskLabel(askKey) {
    const formLabel = labelForAskKey(askKey);
    return (i18n.askLabel || ':form').replace(':form', formLabel);
}

function setDone(message) {
    state.current = null;
    if (els.doneBox) els.doneBox.classList.remove('hidden');
    if (els.doneText) els.doneText.textContent = message;
    setFeedback('', true);
    updateProgress();
    saveState();
}

function renderChoices(options) {
    if (!els.choiceBox) return;
    els.choiceBox.innerHTML = '';
    options.forEach((option, idx) => {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'w-full rounded-xl border border-border/70 bg-background px-4 py-3 text-sm font-semibold text-foreground shadow-sm transition hover:-translate-y-0.5 hover:shadow';
        btn.textContent = option.label;
        btn.dataset.index = idx.toString();
        btn.dataset.value = option.normalized;
        btn.addEventListener('click', () => {
            if (state.current?.answered) return;
            evaluateAnswer(option.normalized);
        });
        els.choiceBox.appendChild(btn);
    });
}

function markChoiceButtons() {
    if (!els.choiceBox || state.settings.mode !== 'choice') return;
    const answers = state.current?.answers || [];
    Array.from(els.choiceBox.children).forEach((button) => {
        const btn = button;
        const normalized = btn.dataset.value || normalize(btn.textContent || '');
        btn.classList.remove('border-primary', 'bg-primary/10', 'border-destructive', 'bg-destructive/10');
        if (answers.includes(normalized)) {
            btn.classList.add('border-primary', 'bg-primary/10');
        } else if (state.current?.answered && !answers.includes(normalized)) {
            btn.classList.add('border-destructive/60', 'bg-destructive/10');
        }
    });
}

function buildChoiceOptions(verb, askKey, answers) {
    const correctRaw = getAnswersForVerb(verb, askKey)[0] || answers[0];
    const pool = verbs
        .map((item) => {
            const value = getAnswersForVerb(item, askKey)[0];
            return value || null;
        })
        .filter(Boolean)
        .map((value) => value);

    const uniquePool = Array.from(new Set(pool.filter((value) => !answers.includes(normalize(value)))));
    const distractors = shuffle(uniquePool).slice(0, 3);
    const optionValues = shuffle([correctRaw, ...distractors].filter(Boolean));

    return optionValues.map((value) => ({
        label: value,
        normalized: normalize(value),
        correct: answers.includes(normalize(value)),
    }));
}

function renderQuestion() {
    if (!verbs.length) {
        setFeedback(i18n.noVerbs || '');
        if (els.baseVerb) els.baseVerb.textContent = '—';
        if (els.ukVerb) els.ukVerb.textContent = '';
        return;
    }

    if (state.pos >= state.queue.length) {
        const total = state.queue.length || 0;
        const resultText = `${i18n.completed || ''}. ${i18n.result || 'Result'}: ${state.correct}/${total}`;
        setDone(resultText);
        return;
    }

    const verbIndex = state.queue[state.pos];
    const verb = verbs[verbIndex];
    const existingCurrent = state.current && state.current.verbIndex === verbIndex ? state.current : null;
    const askKey = existingCurrent?.askKey || resolveAskKey();
    const answersRaw = (existingCurrent?.answersRaw && existingCurrent.answersRaw.length
        ? existingCurrent.answersRaw
        : getAnswersForVerb(verb, askKey).map((a) => a || '').filter(Boolean)
    );
    const answersNormalized = answersRaw.map((a) => normalize(a));

    state.current = {
        verbIndex,
        askKey,
        answers: answersNormalized,
        answersRaw,
        answered: existingCurrent?.answered || false,
        wasCorrect: existingCurrent?.wasCorrect || false,
        options: [],
    };

    if (els.baseVerb) els.baseVerb.textContent = verb?.base || '—';
    if (els.ukVerb) els.ukVerb.textContent = verb?.translation || '';
    setTranslationVisibility();

    if (els.askLabel) els.askLabel.textContent = formatAskLabel(askKey);
    if (els.hint) els.hint.textContent = hintForVerb(verb);

    if (state.settings.mode === 'typing' && els.answerInput) {
        els.answerInput.value = '';
        els.answerInput.focus();
    }

    toggleModeVisibility(state.settings.mode);
    setFeedback('');
    if (els.doneBox) els.doneBox.classList.add('hidden');

    if (state.settings.mode === 'choice') {
        const options = (existingCurrent?.options && existingCurrent.options.length)
            ? existingCurrent.options
            : buildChoiceOptions(verb, askKey, answersNormalized);
        state.current.options = options;
        renderChoices(options);
    } else if (els.choiceBox) {
        els.choiceBox.innerHTML = '';
    }

    if (state.current.answered) {
        const reveal = (i18n.revealed || 'Correct answer: :answer').replace(
            ':answer',
            state.current.answersRaw?.[0] || state.current.answers?.[0] || ''
        );
        setFeedback(
            state.current.wasCorrect ? (i18n.correctAnswer || 'Correct!') : `${i18n.wrongAnswer || 'Wrong'}. ${reveal}`,
            state.current.wasCorrect
        );
        if (state.settings.mode === 'choice') {
            markChoiceButtons();
        }
    }

    updateProgress();
    updateStats();
    saveState();
}

function evaluateAnswer(rawAnswer) {
    if (!state.current || state.current.answered) return;
    const answer = normalize(rawAnswer);
    const isCorrect = state.current.answers.includes(answer);
    state.current.answered = true;
    state.current.wasCorrect = isCorrect;

    if (isCorrect) {
        state.correct += 1;
        setFeedback(i18n.correctAnswer || 'Correct!', true);
    } else {
        state.wrong += 1;
        const revealed = (i18n.revealed || 'Correct answer: :answer').replace(
            ':answer',
            state.current.answers[0] || ''
        );
        setFeedback(`${i18n.wrongAnswer || 'Wrong'}. ${revealed}`, false);
    }

    updateStats();
    if (state.settings.mode === 'choice') {
        markChoiceButtons();
    }
    saveState();
}

function nextQuestion() {
    if (!state.queue.length) return;
    if (state.pos < state.queue.length) {
        state.pos += 1;
    }
    renderQuestion();
}

function revealAnswer() {
    if (!state.current) return;
    const revealed = state.current.answers[0] || '';
    if (state.settings.mode === 'typing' && els.answerInput) {
        els.answerInput.value = revealed;
    }
    if (state.settings.mode === 'choice' && els.choiceBox) {
        Array.from(els.choiceBox.children).forEach((button) => {
            const btn = button;
            const normalized = normalize(btn.textContent || '');
            btn.classList.toggle('border-primary', state.current.answers.includes(normalized));
        });
    }
    const message = (i18n.revealed || 'Correct answer: :answer').replace(':answer', revealed);
    setFeedback(message, true);
    saveState();
}

function startTest() {
    state = defaultState();
    state.settings = readSettingsFromControls();
    applySettingsToControls(state.settings);
    state.queue = createQueue(state.settings.count);
    if (!state.queue.length) {
        setFeedback(i18n.noVerbs || '');
        return;
    }
    state.pos = 0;
    state.correct = 0;
    state.wrong = 0;
    renderQuestion();
}

function restoreState(saved) {
    state = {
        ...defaultState(),
        ...saved,
    };
    applySettingsToControls(state.settings);
    toggleModeVisibility(state.settings.mode);
    renderQuestion();
    if (i18n.progressRestored) {
        setFeedback(i18n.progressRestored, true);
    }
}

function bindEvents() {
    els.startBtn?.addEventListener('click', () => startTest());
    els.restartBtn?.addEventListener('click', () => startTest());
    els.checkBtn?.addEventListener('click', () => {
        if (state.settings.mode !== 'typing') return;
        evaluateAnswer(els.answerInput?.value || '');
    });
    els.nextBtn?.addEventListener('click', () => nextQuestion());
    els.revealBtn?.addEventListener('click', () => revealAnswer());
    els.mode?.addEventListener('change', () => {
        state.settings.mode = els.mode.value;
        toggleModeVisibility(state.settings.mode);
        saveState();
    });
    els.askWhat?.addEventListener('change', () => {
        state.settings.askWhat = els.askWhat.value;
        saveState();
    });
    els.count?.addEventListener('change', () => {
        const settings = readSettingsFromControls();
        state.settings.count = settings.count;
        saveState();
    });
    els.showUk?.addEventListener('change', () => {
        state.settings.showTranslation = Boolean(els.showUk.checked);
        setTranslationVisibility();
        saveState();
    });
    els.answerInput?.addEventListener('keydown', (event) => {
        if (event.key === 'Enter') {
            event.preventDefault();
            evaluateAnswer(els.answerInput.value);
        }
    });
}

function init() {
    if (!els.baseVerb) return;
    bindEvents();

    if (!verbs.length) {
        setFeedback(i18n.noVerbs || '');
        return;
    }

    const savedState = loadState();
    if (savedState) {
        restoreState(savedState);
    } else {
        toggleModeVisibility(state.settings.mode);
        setFeedback(i18n.startNeeded || '');
        updateProgress();
        updateStats();
    }
}

document.addEventListener('DOMContentLoaded', init);
