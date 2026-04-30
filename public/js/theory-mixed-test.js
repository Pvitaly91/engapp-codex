import TheoryCourseProgress from './theory-course-progress.js';

const config = window.__THEORY_MIXED_TEST__ || {};
const course = config.course || {};
const lesson = config.lesson || {};
const lessons = Array.isArray(config.lessons) ? config.lessons : [];
const questions = Array.isArray(config.questions) ? config.questions : [];
const completion = config.completion || {};
const i18n = config.i18n || {};
const courseI18n = config.courseI18n || {};
const store = TheoryCourseProgress.createStore(course.slug, lessons);

const root = document.querySelector('[data-theory-mixed-root]');
const lockPanel = document.getElementById('theory-mixed-lock');
const workspace = document.getElementById('theory-mixed-workspace');
const card = document.getElementById('theory-mixed-question-card');
const progressLabel = document.getElementById('theory-mixed-progress-label');
const scoreLabel = document.getElementById('theory-mixed-score-label');
const progressBar = document.getElementById('theory-mixed-progress-bar');
const prevButton = document.getElementById('theory-mixed-prev');
const nextButton = document.getElementById('theory-mixed-next');
const summary = document.getElementById('theory-mixed-summary');
const summaryTitle = document.getElementById('theory-mixed-summary-title');
const summaryText = document.getElementById('theory-mixed-summary-text');
const retryButton = document.getElementById('theory-mixed-retry');
const nextLessonLink = document.getElementById('theory-mixed-next-lesson');
const resetButton = document.getElementById('theory-mixed-reset-lesson');

function text(key, fallback = '') {
    return Object.prototype.hasOwnProperty.call(i18n, key) ? i18n[key] : fallback;
}

function courseText(key, fallback = '') {
    return Object.prototype.hasOwnProperty.call(courseI18n, key) ? courseI18n[key] : fallback;
}

function interpolate(template, replacements = {}) {
    return String(template ?? '').replace(/:([A-Za-z0-9_]+)/g, (match, key) => (
        Object.prototype.hasOwnProperty.call(replacements, key) ? replacements[key] : match
    ));
}

function html(value) {
    return String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

function normalizeCompare(value) {
    return String(value ?? '')
        .trim()
        .toLowerCase()
        .replace(/\s+([?.!,;:])/g, '$1')
        .replace(/\s+/g, ' ');
}

function percent(value, total) {
    return Math.round((Number(value || 0) / Math.max(Number(total || 0), 1)) * 100);
}

function seededSort(values, seed) {
    return [...values].sort((left, right) => {
        const leftScore = hash(`${seed}|${left}`);
        const rightScore = hash(`${seed}|${right}`);

        return leftScore - rightScore;
    });
}

function hash(value) {
    let result = 0;
    const source = String(value);

    for (let index = 0; index < source.length; index += 1) {
        result = ((result << 5) - result) + source.charCodeAt(index);
        result |= 0;
    }

    return Math.abs(result);
}

function normalizeStandardQuestion(question) {
    const markers = Array.isArray(question.markers) && question.markers.length > 0
        ? question.markers
        : Object.keys(question.answer_map || {});

    return {
        ...question,
        renderer: 'standard',
        markers,
        chosen: Array(markers.length).fill(null),
        activeSlot: 0,
        wrong: false,
        done: markers.length === 0,
        feedback: '',
    };
}

function normalizeComposeQuestion(question) {
    const tokenIds = (Array.isArray(question.tokenBank) ? question.tokenBank : []).map((token) => token.id);

    return {
        ...question,
        renderer: 'compose',
        selectedTokenIds: [],
        bankOrder: seededSort(tokenIds, question.uuid || question.id || question.question),
        wrong: false,
        done: false,
        feedback: '',
    };
}

function normalizeQuestion(question) {
    return String(question?.renderer || '') === 'compose'
        ? normalizeComposeQuestion(question)
        : normalizeStandardQuestion(question);
}

const state = {
    index: 0,
    answered: 0,
    correct: 0,
    completed: false,
    items: questions.map(normalizeQuestion),
};

function currentItem() {
    return state.items[state.index] || null;
}

function sourceLabel(item) {
    const source = item?.source_test || {};

    if (!source.name) {
        return '';
    }

    return `${source.is_polyglot ? 'Polyglot' : 'V3'}: ${source.name}`;
}

function activeMarker(item) {
    return item.markers?.[item.activeSlot] || `a${item.activeSlot + 1}`;
}

function answerForMarker(item, marker) {
    return String(item.answer_map?.[marker] ?? item.answers?.[item.activeSlot] ?? '').trim();
}

function optionsForActiveSlot(item) {
    const raw = item.options_by_marker;
    const marker = activeMarker(item);
    const options = Array.isArray(raw?.[item.activeSlot])
        ? raw[item.activeSlot]
        : (Array.isArray(raw?.[marker]) ? raw[marker] : item.options);
    const expected = answerForMarker(item, marker);
    const clean = [];

    (Array.isArray(options) ? options : []).forEach((option) => {
        const value = String(option ?? '').trim();

        if (value !== '' && !clean.includes(value)) {
            clean.push(value);
        }
    });

    if (expected !== '' && !clean.includes(expected)) {
        clean.push(expected);
    }

    return seededSort(clean, `${item.uuid || item.id}|${item.activeSlot}`);
}

function renderStandardQuestion(item) {
    let sentence = String(item.question || '');

    item.markers.forEach((marker, index) => {
        const chosen = item.chosen[index];
        const active = index === item.activeSlot;
        const replacement = chosen
            ? `<mark class="rounded-lg px-2 py-1 font-semibold ${active ? 'ring-2 ring-indigo-300' : ''}" style="background: var(--accent-soft);">${html(chosen)}</mark>`
            : `<button type="button" data-gap-index="${index}" class="rounded-lg border px-2 py-1 font-semibold ${active ? 'ring-2 ring-indigo-300' : ''}" style="border-color: var(--line); background: var(--surface);">____</button>`;
        const hint = item.verb_hints?.[marker]
            ? ` <span class="text-sm font-bold" style="color:#b42318;">(${html(item.verb_hints[marker])})</span>`
            : '';

        sentence = sentence.replace(new RegExp(`\\{${marker}\\}`, 'g'), replacement + hint);
    });

    const options = optionsForActiveSlot(item);
    const buttons = options.map((option, index) => `
        <button type="button"
                data-standard-option="${html(option)}"
                class="rounded-[18px] border px-4 py-3 text-left text-sm font-bold transition hover:-translate-y-0.5"
                style="border-color: var(--line); background: var(--surface);">
            <span class="mr-2 inline-flex h-7 w-7 items-center justify-center rounded-xl border text-xs" style="border-color: var(--line); color: var(--muted);">${index + 1}</span>
            ${html(option)}
        </button>
    `).join('');

    return `
        <div class="space-y-5">
            <div>
                <p class="text-[11px] font-extrabold uppercase tracking-[0.22em]" style="color: var(--accent);">V3</p>
                <div class="mt-3 text-2xl font-extrabold leading-[1.45]">${sentence}</div>
                <p class="mt-2 text-xs font-semibold uppercase tracking-[0.16em]" style="color: var(--muted);">${html(sourceLabel(item))}</p>
            </div>
            <div class="rounded-[20px] border p-4 surface-card" style="border-color: var(--line);">
                <p class="text-[11px] font-extrabold uppercase tracking-[0.18em]" style="color: var(--muted);">
                    ${html(interpolate(text('active_gap', 'Gap :current / :total'), { current: item.activeSlot + 1, total: item.markers.length }))}
                </p>
                <div class="mt-4 grid gap-3 sm:grid-cols-2">${buttons}</div>
            </div>
            ${feedbackMarkup(item)}
        </div>
    `;
}

function renderComposeQuestion(item) {
    const tokenMap = Object.fromEntries((item.tokenBank || []).map((token) => [token.id, token]));
    const answerTokens = item.selectedTokenIds.map((tokenId) => tokenMap[tokenId]).filter(Boolean);
    const selected = new Set(item.selectedTokenIds);
    const bankTokens = item.bankOrder.map((tokenId) => tokenMap[tokenId]).filter((token) => token && !selected.has(token.id));
    const answerMarkup = answerTokens.length > 0
        ? answerTokens.map((token) => `<button type="button" data-compose-answer-token="${html(token.id)}" class="rounded-xl border px-3 py-2 text-sm font-bold" style="border-color: var(--line); background: var(--accent-soft);">${html(token.value)}</button>`).join('')
        : `<span class="rounded-xl border border-dashed px-3 py-3 text-sm" style="border-color: var(--line); color: var(--muted);">${html(text('compose_placeholder', 'Choose tokens to build the answer.'))}</span>`;
    const bankMarkup = bankTokens.map((token) => `
        <button type="button" data-compose-bank-token="${html(token.id)}" class="rounded-xl border px-3 py-2 text-sm font-bold transition hover:-translate-y-0.5" style="border-color: var(--line); background: var(--surface);">
            ${html(token.value)}
        </button>
    `).join('');

    return `
        <div class="space-y-5">
            <div>
                <p class="text-[11px] font-extrabold uppercase tracking-[0.22em]" style="color: var(--accent);">Polyglot</p>
                <div class="mt-3 text-2xl font-extrabold leading-[1.35]">${html(item.sourceTextUk || item.question)}</div>
                <p class="mt-2 text-xs font-semibold uppercase tracking-[0.16em]" style="color: var(--muted);">${html(sourceLabel(item))}</p>
            </div>
            <section class="rounded-[20px] border p-4 surface-card" style="border-color: var(--line);">
                <p class="text-[11px] font-extrabold uppercase tracking-[0.18em]" style="color: var(--muted);">${html(text('build_translation', 'Build the translation'))}</p>
                <div class="mt-4 flex min-h-[5rem] flex-wrap content-start gap-2 rounded-[18px] border border-dashed p-3" style="border-color: var(--line);">${answerMarkup}<span class="rounded-xl border px-3 py-2 text-sm font-bold" style="border-color: var(--line); color: var(--muted);">${html(item.punctuation || '.')}</span></div>
            </section>
            <section class="rounded-[20px] border p-4 surface-card" style="border-color: var(--line);">
                <p class="text-[11px] font-extrabold uppercase tracking-[0.18em]" style="color: var(--muted);">${html(text('token_bank', 'Token bank'))}</p>
                <div class="mt-4 flex flex-wrap gap-2">${bankMarkup || `<span class="text-sm" style="color: var(--muted);">${html(text('empty_pool', 'No tokens left.'))}</span>`}</div>
            </section>
            <div class="flex flex-wrap gap-3">
                <button type="button" data-compose-check class="rounded-full bg-ocean px-5 py-3 text-sm font-extrabold text-white">${html(text('check', 'Check'))}</button>
                <button type="button" data-compose-clear class="rounded-full border px-5 py-3 text-sm font-bold" style="border-color: var(--line);">${html(text('clear', 'Clear'))}</button>
            </div>
            ${feedbackMarkup(item)}
        </div>
    `;
}

function feedbackMarkup(item) {
    if (!item.feedback) {
        return '';
    }

    const ok = item.feedback === 'correct';
    const styles = ok
        ? 'border-color: #b8e3c7; background: linear-gradient(180deg, #f0fbf4 0%, #e7f8ee 100%); color: #17603a;'
        : 'border-color: #fecaca; background: linear-gradient(180deg, #fff5f5 0%, #ffefef 100%); color: #b42318;';

    return `<div class="rounded-[20px] border px-4 py-3 text-sm font-semibold" style="${styles}">${html(item.feedbackText || (ok ? text('correct', 'Correct') : text('incorrect', 'Incorrect')))}</div>`;
}

function markCurrentDone(wasCorrect) {
    const item = currentItem();

    if (!item || item.done) {
        return;
    }

    item.done = true;
    state.answered += 1;

    if (wasCorrect) {
        state.correct += 1;
    }

    if (state.answered >= state.items.length) {
        state.completed = true;
    }
}

function standardChoose(value) {
    const item = currentItem();

    if (!item || item.done || item.renderer !== 'standard') {
        return;
    }

    const marker = activeMarker(item);
    const expected = answerForMarker(item, marker);

    if (normalizeCompare(value) === normalizeCompare(expected)) {
        item.chosen[item.activeSlot] = value;
        item.feedback = 'correct';
        item.feedbackText = text('correct', 'Correct');

        const nextSlot = item.chosen.findIndex((chosen) => !chosen);
        if (nextSlot === -1) {
            markCurrentDone(!item.wrong);
        } else {
            item.activeSlot = nextSlot;
        }
    } else {
        item.wrong = true;
        item.feedback = 'incorrect';
        item.feedbackText = text('incorrect_try_again', 'Incorrect. Try again.');
    }

    render();
}

function composeSentence(item) {
    const tokenMap = Object.fromEntries((item.tokenBank || []).map((token) => [token.id, token]));
    const values = item.selectedTokenIds
        .map((tokenId) => tokenMap[tokenId]?.value || '')
        .filter(Boolean);

    return normalizeCompare(`${values.join(' ')}${item.punctuation || '.'}`);
}

function composeCheck() {
    const item = currentItem();

    if (!item || item.done || item.renderer !== 'compose') {
        return;
    }

    const expected = normalizeCompare(item.correctText);

    if (composeSentence(item) === expected) {
        item.feedback = 'correct';
        item.feedbackText = text('correct', 'Correct');
        markCurrentDone(!item.wrong);
    } else {
        item.wrong = true;
        item.feedback = 'incorrect';
        item.feedbackText = `${text('incorrect', 'Incorrect')} ${text('correct_answer', 'Correct answer')}: ${item.correctText}`;
    }

    render();
}

function move(delta) {
    state.index = Math.max(0, Math.min(state.items.length - 1, state.index + delta));
    render();
}

function nextUnansweredIndex() {
    const afterCurrent = state.items.findIndex((item, index) => index > state.index && !item.done);

    if (afterCurrent >= 0) {
        return afterCurrent;
    }

    return state.items.findIndex((item) => !item.done);
}

function renderProgress() {
    const scorePercent = percent(state.correct, state.items.length);

    if (progressLabel) {
        progressLabel.textContent = `${state.answered} / ${state.items.length}`;
    }

    if (scoreLabel) {
        scoreLabel.textContent = `${scorePercent}%`;
    }

    if (progressBar) {
        progressBar.style.width = `${percent(state.answered, state.items.length)}%`;
    }
}

function renderSummary() {
    const scorePercent = percent(state.correct, state.items.length);
    const minimumCorrect = Number(completion.minimum_correct || Math.ceil(state.items.length * 0.8));
    const passed = state.items.length > 0
        && state.answered >= state.items.length
        && state.correct >= minimumCorrect
        && scorePercent >= Number(completion.minimum_rating_percent || 80);

    summary?.classList.remove('hidden');
    workspace?.classList.add('hidden');

    if (summaryTitle) {
        summaryTitle.textContent = passed
            ? text('lesson_completed', 'Lesson completed')
            : text('test_not_passed', 'Try again');
    }

    if (summaryText) {
        summaryText.textContent = interpolate(
            passed ? text('summary_passed', 'Score: :correct / :total (:percent%).') : text('summary_failed', 'Score: :correct / :total (:percent%). Required: :minimum.'),
            {
                correct: state.correct,
                total: state.items.length,
                percent: scorePercent,
                minimum: minimumCorrect,
            }
        );
    }

    if (passed) {
        store.markLessonCompleted(lesson.lesson_slug, {
            scorePercent,
            correct: state.correct,
            total: state.items.length,
        });
        nextLessonLink?.classList.toggle('hidden', !config.nextLesson?.lesson_url);
    }
}

function render() {
    renderProgress();

    if (state.completed) {
        renderSummary();
        return;
    }

    const item = currentItem();

    if (!item || !card) {
        return;
    }

    summary?.classList.add('hidden');
    workspace?.classList.remove('hidden');
    card.innerHTML = item.renderer === 'compose'
        ? renderComposeQuestion(item)
        : renderStandardQuestion(item);

    if (prevButton) {
        prevButton.disabled = state.index <= 0;
    }

    if (nextButton) {
        const nextIndex = nextUnansweredIndex();
        nextButton.disabled = nextIndex < 0 && !item.done;
        nextButton.textContent = item.done && nextIndex >= 0
            ? text('next_unanswered', 'Next unanswered')
            : text('next_question', 'Next');
    }
}

function boot() {
    if (!root) {
        return;
    }

    const courseState = store.read();
    const status = store.getLessonStatus(lesson.lesson_slug, courseState);

    if (status === 'locked') {
        lockPanel?.classList.remove('hidden');
        workspace?.classList.add('hidden');
        return;
    }

    store.markLessonOpened(lesson.lesson_slug);

    if (questions.length === 0) {
        workspace?.classList.add('hidden');
        return;
    }

    render();
}

card?.addEventListener('click', (event) => {
    const standardOption = event.target.closest('[data-standard-option]');
    if (standardOption) {
        standardChoose(standardOption.getAttribute('data-standard-option') || '');
        return;
    }

    const gapButton = event.target.closest('[data-gap-index]');
    if (gapButton) {
        const item = currentItem();
        const index = Number.parseInt(gapButton.getAttribute('data-gap-index') || '', 10);
        if (item && Number.isInteger(index)) {
            item.activeSlot = index;
            item.feedback = '';
            render();
        }
        return;
    }

    const bankToken = event.target.closest('[data-compose-bank-token]');
    if (bankToken) {
        const item = currentItem();
        const tokenId = bankToken.getAttribute('data-compose-bank-token') || '';
        if (item && !item.selectedTokenIds.includes(tokenId)) {
            item.selectedTokenIds.push(tokenId);
            item.feedback = '';
            render();
        }
        return;
    }

    const answerToken = event.target.closest('[data-compose-answer-token]');
    if (answerToken) {
        const item = currentItem();
        const tokenId = answerToken.getAttribute('data-compose-answer-token') || '';
        if (item) {
            item.selectedTokenIds = item.selectedTokenIds.filter((selected) => selected !== tokenId);
            item.feedback = '';
            render();
        }
        return;
    }

    if (event.target.closest('[data-compose-check]')) {
        composeCheck();
        return;
    }

    if (event.target.closest('[data-compose-clear]')) {
        const item = currentItem();
        if (item) {
            item.selectedTokenIds = [];
            item.feedback = '';
            render();
        }
    }
});

prevButton?.addEventListener('click', () => move(-1));
nextButton?.addEventListener('click', () => {
    const nextIndex = nextUnansweredIndex();

    if (nextIndex >= 0) {
        state.index = nextIndex;
        render();
        return;
    }

    if (state.answered >= state.items.length) {
        state.completed = true;
        render();
    }
});

retryButton?.addEventListener('click', () => {
    const url = new URL(window.location.href);
    url.searchParams.set('attempt', String(Date.now()));
    window.location.assign(url.toString());
});

resetButton?.addEventListener('click', () => {
    store.resetLesson(lesson.lesson_slug);
    window.location.reload();
});

boot();
