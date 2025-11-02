@extends('layouts.engram')

@section('title', $test->name)

@section('content')
<div class="mx-auto w-full max-w-6xl px-4 py-8 text-slate-800" id="match-quiz">
    <header class="mb-6 space-y-2">
        <h1 class="text-2xl font-bold text-slate-900 sm:text-3xl">{{ $test->name }}</h1>
        <p class="text-sm text-slate-600">З'єднай речення з правильним поясненням. Для мобільних пристроїв можна натиснути на елемент зліва, а потім на відповідний праворуч.</p>
    </header>

    @include('components.test-mode-nav')
    @include('components.word-search')
    @include('components.saved-test-progress')

    <div class="mb-6 rounded-2xl border border-sky-200 bg-sky-50 p-4 text-sm text-slate-700 shadow-sm">
        <ul class="list-disc space-y-1 pl-4">
            <li>Зліва — речення (1…n), справа — пояснення (a…n).</li>
            <li>Тягни або натискай, щоб провести лінію між відповідними парами.</li>
            <li>Кожен елемент може мати лише один зв'язок.</li>
            <li><strong>Перевірити</strong> активується, коли всі пари з'єднані.</li>
            <li><strong>Скинути</strong> очищує з'єднання та перемішує елементи.</li>
        </ul>
    </div>

    <div class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-4 shadow-sm" id="match-board">
        <svg id="match-svg" class="pointer-events-none absolute inset-0 h-full w-full"></svg>
        <div class="grid gap-10 md:grid-cols-2" id="match-columns">
            <div class="space-y-3" id="match-left"></div>
            <div class="space-y-3" id="match-right"></div>
        </div>
        <div id="match-empty" class="hidden rounded-xl border border-amber-200 bg-amber-50 p-4 text-center text-sm text-amber-700">
            Для цього тесту поки немає питань, які можна зіставити у форматі «речення → пояснення».
        </div>
    </div>

    <div class="mt-6 flex flex-wrap items-center gap-3">
        <button id="match-check" type="button" class="rounded-xl bg-sky-500 px-5 py-2 text-sm font-semibold text-white shadow transition hover:bg-sky-600 disabled:cursor-not-allowed disabled:opacity-50" disabled>
            Перевірити
        </button>
        <button id="match-reset" type="button" class="rounded-xl border border-slate-300 bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-200">
            Скинути
        </button>
        <div id="match-result" class="text-lg font-semibold text-sky-700"></div>
    </div>

    @include('components.saved-test-js-restart-button')
</div>

<style>
    #match-board { min-height: 420px; }
    .match-card {
        position: relative;
        user-select: none;
        border-radius: 16px;
        border: 1px solid #dbeafe;
        background: #f8fafc;
        padding: 14px 16px;
        transition: background 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
        cursor: crosshair;
    }
    .match-card:hover {
        background: #e0f2fe;
        border-color: #38bdf8;
        box-shadow: 0 10px 24px rgba(14, 165, 233, 0.18);
    }
    .match-card.connected {
        border-color: #0ea5e9;
        box-shadow: 0 0 0 2px rgba(14, 165, 233, 0.25);
        background: #f0f9ff;
    }
    .match-card.correct {
        background-color: #bbf7d0 !important;
        border-color: #22c55e !important;
    }
    .match-card.incorrect {
        background-color: #fecaca !important;
        border-color: #ef4444 !important;
    }
    .match-card.disabled {
        opacity: 0.6;
        pointer-events: none;
    }
    .match-card-label {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 28px;
        height: 28px;
        border-radius: 999px;
        background: #0ea5e9;
        color: #fff;
        font-size: 0.875rem;
        font-weight: 700;
        margin-right: 10px;
    }
    .match-card-meta {
        margin-top: 6px;
        font-size: 0.75rem;
        color: #64748b;
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }
    .match-card-meta span {
        background: #e2e8f0;
        border-radius: 999px;
        padding: 2px 8px;
        font-weight: 600;
    }
    #match-svg line {
        stroke-width: 3;
        stroke: #94a3b8;
        stroke-linecap: round;
        transition: stroke 0.3s ease;
    }
</style>

<script>
window.__INITIAL_JS_TEST_QUESTIONS__ = @json($questionData);
let QUESTIONS = Array.isArray(window.__INITIAL_JS_TEST_QUESTIONS__)
    ? window.__INITIAL_JS_TEST_QUESTIONS__
    : [];
</script>

@include('components.saved-test-js-persistence', ['mode' => $jsStateMode, 'savedState' => $savedState])
@include('components.saved-test-js-helpers')

<script>
const matchState = {
    items: [],
    leftOrder: [],
    rightOrder: [],
    connections: [],
    evaluated: false,
    correct: 0,
};

const matchBoard = document.getElementById('match-board');
const svg = document.getElementById('match-svg');
const leftCol = document.getElementById('match-left');
const rightCol = document.getElementById('match-right');
const emptyState = document.getElementById('match-empty');
const checkBtn = document.getElementById('match-check');
const resetBtn = document.getElementById('match-reset');
const resultEl = document.getElementById('match-result');
const restartBtn = document.getElementById('restart-test');

let activeStart = null;
let tempLine = null;

function shuffleCopy(list) {
    const arr = Array.from(list);
    shuffle(arr);
    return arr;
}

function formatQuestion(text) {
    return String(text ?? '')
        .replace(/\{a\d+\}/gi, '____')
        .replace(/\s+/g, ' ')
        .trim();
}

function collectAnswers(question) {
    const values = [];

    const rawAnswers = question?.answers;
    if (Array.isArray(rawAnswers)) {
        rawAnswers.forEach((ans) => {
            if (ans === null || ans === undefined) {
                return;
            }
            if (typeof ans === 'object') {
                if (ans.value !== undefined && ans.value !== null) {
                    values.push(ans.value);
                    return;
                }
                if (ans.answer !== undefined && ans.answer !== null) {
                    values.push(ans.answer);
                    return;
                }
            }
            values.push(ans);
        });
    }

    const answerMap = question?.answer_map;
    if (answerMap && typeof answerMap === 'object') {
        Object.values(answerMap).forEach((value) => {
            if (value !== undefined && value !== null) {
                values.push(value);
            }
        });
    }

    const answerList = question?.answer_list;
    if (Array.isArray(answerList)) {
        answerList.forEach((value) => {
            if (value !== undefined && value !== null) {
                values.push(value);
            }
        });
    }

    if (question?.answer !== undefined && question?.answer !== null) {
        values.push(question.answer);
    }

    const options = question?.options;
    if (Array.isArray(options)) {
        options.forEach((value) => {
            if (value !== undefined && value !== null) {
                values.push(value);
            }
        });
    }

    return values
        .map((value) => {
            if (typeof value === 'object' && value !== null) {
                if (value.value !== undefined && value.value !== null) {
                    return value.value;
                }
                if (value.answer !== undefined && value.answer !== null) {
                    return value.answer;
                }
            }
            return value;
        })
        .map((value) => String(value ?? '').trim())
        .filter(Boolean);
}

function buildItems(rawQuestions) {
    return rawQuestions
        .map((question, index) => {
            const key = question?.id ? `q-${question.id}` : `idx-${index}`;
            const formatted = formatQuestion(question?.question ?? '');
            const answers = collectAnswers(question);
            const explanation = answers[0] ?? '';

            return {
                key,
                question: formatted,
                explanation,
                level: question?.level ?? '',
                tense: question?.tense ?? '',
            };
        })
        .filter(item => item.question && item.explanation);
}

function getItemMap() {
    return new Map(matchState.items.map(item => [item.key, item]));
}

function setCheckDisabled(disabled) {
    checkBtn.disabled = disabled;
    if (disabled) {
        checkBtn.classList.add('opacity-50', 'cursor-not-allowed');
    } else {
        checkBtn.classList.remove('opacity-50', 'cursor-not-allowed');
    }
}

function updateButtonState() {
    const total = matchState.items.length;
    setCheckDisabled(!(total && matchState.connections.length === total));
}

function updateProgress() {
    const total = matchState.items.length;
    const answered = matchState.connections.length;
    const label = document.getElementById('progress-label');
    const scoreLabel = document.getElementById('score-label');
    const progressBar = document.getElementById('progress-bar');

    if (label) {
        label.textContent = `${answered} / ${total}`;
    }

    if (scoreLabel) {
        const percent = matchState.evaluated ? pct(matchState.correct, total) : 0;
        scoreLabel.textContent = `Точність: ${percent}%`;
    }

    if (progressBar) {
        const percent = total ? (answered / total) * 100 : 0;
        progressBar.style.width = `${percent}%`;
    }
}

function updateResult() {
    if (!matchState.evaluated) {
        resultEl.textContent = '';
        return;
    }

    const total = matchState.items.length;
    resultEl.textContent = `✅ Правильних: ${matchState.correct} / ${total}`;
}

function updateEmptyState() {
    if (!emptyState) {
        return;
    }

    if (!matchState.items.length) {
        emptyState.classList.remove('hidden');
        matchBoard.classList.add('pointer-events-none');
    } else {
        emptyState.classList.add('hidden');
        matchBoard.classList.remove('pointer-events-none');
    }
}

function attachItemEvents(el) {
    if (!el) {
        return;
    }

    el.addEventListener('pointerdown', (event) => {
        if (!matchState.items.length) {
            return;
        }
        event.preventDefault();
        startConnection(el);
    });

    el.addEventListener('dblclick', () => {
        removeConnectionForElement(el);
    });
}

function startConnection(el) {
    if (!el) {
        return;
    }

    activeStart = el;
    const { x, y } = getCenter(el);
    tempLine = createLine(x, y, x, y, '#94a3b8');
    tempLine.dataset.temp = '1';
    svg.appendChild(tempLine);

    document.addEventListener('pointermove', handlePointerMove);
    document.addEventListener('pointerup', handlePointerUp);
}

function handlePointerMove(event) {
    if (!tempLine) {
        return;
    }

    const rect = svg.getBoundingClientRect();
    tempLine.setAttribute('x2', event.clientX - rect.left);
    tempLine.setAttribute('y2', event.clientY - rect.top);
}

function handlePointerUp(event) {
    document.removeEventListener('pointermove', handlePointerMove);
    document.removeEventListener('pointerup', handlePointerUp);

    if (tempLine) {
        tempLine.remove();
        tempLine = null;
    }

    if (!activeStart) {
        return;
    }

    const pointerTarget = document.elementFromPoint(event.clientX, event.clientY);
    const dropTarget = pointerTarget && typeof pointerTarget.closest === 'function'
        ? pointerTarget.closest('.match-card')
        : null;

    if (!dropTarget || dropTarget === activeStart) {
        activeStart = null;
        return;
    }

    const startIsLeft = activeStart.classList.contains('match-sentence');
    const targetIsLeft = dropTarget.classList.contains('match-sentence');

    if (startIsLeft === targetIsLeft) {
        activeStart = null;
        return;
    }

    const leftEl = startIsLeft ? activeStart : dropTarget;
    const rightEl = startIsLeft ? dropTarget : activeStart;

    activeStart = null;
    applyConnection(leftEl.dataset.key, rightEl.dataset.key);
}

function removeConnectionForElement(el) {
    if (!el) {
        return;
    }

    const key = el.dataset.key;
    if (!key) {
        return;
    }

    const type = el.classList.contains('match-sentence') ? 'leftKey' : 'rightKey';
    const idx = matchState.connections.findIndex(conn => conn[type] === key);

    if (idx !== -1) {
        matchState.connections.splice(idx, 1);
        clearEvaluation();
        renderConnections();
        updateButtonState();
        updateProgress();
        persistState(matchState);
    }
}

function clearEvaluation() {
    if (!matchState.evaluated) {
        return;
    }

    matchState.evaluated = false;
    matchState.correct = 0;
    matchState.connections = matchState.connections.map(conn => ({
        leftKey: conn.leftKey,
        rightKey: conn.rightKey,
        correct: false,
    }));
    resultEl.textContent = '';
}

function applyConnection(leftKey, rightKey) {
    if (!leftKey || !rightKey) {
        return;
    }

    const existingLeft = matchState.connections.findIndex(conn => conn.leftKey === leftKey);
    if (existingLeft !== -1) {
        matchState.connections.splice(existingLeft, 1);
    }

    const existingRight = matchState.connections.findIndex(conn => conn.rightKey === rightKey);
    if (existingRight !== -1) {
        matchState.connections.splice(existingRight, 1);
    }

    matchState.connections.push({ leftKey, rightKey, correct: false });
    clearEvaluation();
    renderConnections();
    updateButtonState();
    updateProgress();
    persistState(matchState);
}

function renderColumns() {
    const itemMap = getItemMap();

    leftCol.innerHTML = '';
    rightCol.innerHTML = '';

    const letters = 'abcdefghijklmnopqrstuvwxyz';

    matchState.leftOrder.forEach((key, index) => {
        const item = itemMap.get(key);
        if (!item) {
            return;
        }
        const card = document.createElement('div');
        card.className = 'match-card match-sentence flex items-start gap-3';
        card.dataset.key = key;
        card.innerHTML = `
            <span class="match-card-label">${index + 1}</span>
            <div class="flex-1">
                <div class="text-sm leading-relaxed">${html(item.question)}</div>
                ${renderMeta(item)}
            </div>
        `;
        attachItemEvents(card);
        leftCol.appendChild(card);
    });

    matchState.rightOrder.forEach((key, index) => {
        const item = itemMap.get(key);
        if (!item) {
            return;
        }
        const label = letters[index] ? letters[index] : String(index + 1);
        const card = document.createElement('div');
        card.className = 'match-card match-explanation flex items-start gap-3';
        card.dataset.key = key;
        card.innerHTML = `
            <span class="match-card-label">${label}</span>
            <div class="flex-1 text-sm leading-relaxed">${html(item.explanation)}</div>
        `;
        attachItemEvents(card);
        rightCol.appendChild(card);
    });

    updateEmptyState();
}

function renderMeta(item) {
    const meta = [];
    if (item.level) {
        meta.push(`<span>${html(item.level)}</span>`);
    }
    if (item.tense) {
        meta.push(`<span>${html(item.tense)}</span>`);
    }

    if (!meta.length) {
        return '';
    }

    return `<div class="match-card-meta">${meta.join('')}</div>`;
}

function renderConnections() {
    svg.querySelectorAll('line[data-conn]').forEach(line => line.remove());

    const itemMap = getItemMap();

    const leftCards = new Map(Array.from(leftCol.querySelectorAll('.match-card')).map(el => [el.dataset.key, el]));
    const rightCards = new Map(Array.from(rightCol.querySelectorAll('.match-card')).map(el => [el.dataset.key, el]));

    leftCards.forEach(card => card.classList.remove('connected', 'correct', 'incorrect'));
    rightCards.forEach(card => card.classList.remove('connected', 'correct', 'incorrect'));

    matchState.connections.forEach(conn => {
        const leftEl = leftCards.get(conn.leftKey);
        const rightEl = rightCards.get(conn.rightKey);

        if (!leftEl || !rightEl) {
            return;
        }

        leftEl.classList.add('connected');
        rightEl.classList.add('connected');

        if (matchState.evaluated) {
            if (conn.correct) {
                leftEl.classList.add('correct');
                rightEl.classList.add('correct');
            } else {
                leftEl.classList.add('incorrect');
                rightEl.classList.add('incorrect');
            }
        }

        const { x: x1, y: y1 } = getCenter(leftEl);
        const { x: x2, y: y2 } = getCenter(rightEl);
        const stroke = matchState.evaluated
            ? (conn.correct ? '#22c55e' : '#ef4444')
            : '#0f172a';
        const line = createLine(x1, y1, x2, y2, stroke);
        line.dataset.conn = `${conn.leftKey}|${conn.rightKey}`;
        svg.appendChild(line);
    });
}

function getCenter(el) {
    const rect = el.getBoundingClientRect();
    const svgRect = svg.getBoundingClientRect();
    const isSentence = el.classList.contains('match-sentence');
    const x = isSentence ? rect.right - svgRect.left : rect.left - svgRect.left;
    const y = rect.top - svgRect.top + rect.height / 2;

    return { x, y };
}

function createLine(x1, y1, x2, y2, stroke) {
    const line = document.createElementNS('http://www.w3.org/2000/svg', 'line');
    line.setAttribute('x1', x1);
    line.setAttribute('y1', y1);
    line.setAttribute('x2', x2);
    line.setAttribute('y2', y2);
    line.setAttribute('stroke', stroke);
    line.setAttribute('stroke-width', '3');
    line.setAttribute('stroke-linecap', 'round');
    return line;
}

function checkAnswers() {
    if (checkBtn.disabled || !matchState.items.length) {
        return;
    }

    const itemMap = getItemMap();
    let correct = 0;

    matchState.connections = matchState.connections.map(conn => {
        const left = itemMap.get(conn.leftKey);
        const right = itemMap.get(conn.rightKey);
        const isCorrect = Boolean(left && right && left.key === right.key);
        if (isCorrect) {
            correct += 1;
        }

        return {
            leftKey: conn.leftKey,
            rightKey: conn.rightKey,
            correct: isCorrect,
        };
    });

    matchState.correct = correct;
    matchState.evaluated = true;
    renderConnections();
    updateProgress();
    updateResult();
    persistState(matchState);
}

function resetBoard(shuffleColumns = true) {
    if (!matchState.items.length) {
        return;
    }

    matchState.connections = [];
    matchState.correct = 0;
    matchState.evaluated = false;

    if (shuffleColumns) {
        const keys = matchState.items.map(item => item.key);
        matchState.leftOrder = shuffleCopy(keys);
        matchState.rightOrder = shuffleCopy(keys);
    }

    renderColumns();
    renderConnections();
    updateButtonState();
    updateProgress();
    updateResult();
    persistState(matchState);
}

async function initMatch(forceFresh = false) {
    const baseQuestions = await loadQuestions(forceFresh);
    QUESTIONS = Array.isArray(baseQuestions) ? baseQuestions : [];
    const builtItems = buildItems(QUESTIONS);

    matchState.items = builtItems;
    matchState.connections = [];
    matchState.correct = 0;
    matchState.evaluated = false;

    const keys = builtItems.map(item => item.key);
    matchState.leftOrder = shuffleCopy(keys);
    matchState.rightOrder = shuffleCopy(keys);

    if (!forceFresh) {
        const saved = getSavedState();
        if (saved && Array.isArray(saved.items) && saved.items.length) {
            const savedKeys = new Set(saved.items.map(item => item.key));
            const sameSize = savedKeys.size === keys.length;
            const allExist = keys.every(key => savedKeys.has(key));

            if (sameSize && allExist) {
                matchState.items = builtItems.map(item => {
                    const savedItem = saved.items.find(savedEntry => savedEntry.key === item.key);
                    return {
                        ...item,
                        ...(savedItem ? {
                            explanation: savedItem.explanation ?? item.explanation,
                            question: savedItem.question ?? item.question,
                            level: savedItem.level ?? item.level,
                            tense: savedItem.tense ?? item.tense,
                        } : {}),
                    };
                });

                const validLeftOrder = Array.isArray(saved.leftOrder)
                    ? saved.leftOrder.filter(key => keys.includes(key))
                    : [];
                const validRightOrder = Array.isArray(saved.rightOrder)
                    ? saved.rightOrder.filter(key => keys.includes(key))
                    : [];

                if (validLeftOrder.length === keys.length) {
                    matchState.leftOrder = validLeftOrder;
                }
                if (validRightOrder.length === keys.length) {
                    matchState.rightOrder = validRightOrder;
                }

                matchState.connections = Array.isArray(saved.connections)
                    ? saved.connections
                        .filter(conn => conn && keys.includes(conn.leftKey) && keys.includes(conn.rightKey))
                        .map(conn => ({
                            leftKey: conn.leftKey,
                            rightKey: conn.rightKey,
                            correct: Boolean(conn.correct),
                        }))
                    : [];

                matchState.evaluated = Boolean(saved.evaluated);
                matchState.correct = Number.isFinite(saved.correct) ? saved.correct : 0;
            }
        }
    }

    renderColumns();
    renderConnections();
    updateButtonState();
    updateProgress();
    updateResult();
    persistState(matchState, true);
}

checkBtn.addEventListener('click', checkAnswers);
resetBtn.addEventListener('click', () => resetBoard(true));
if (restartBtn) {
    restartBtn.addEventListener('click', () => restartJsTest(initMatch, { button: restartBtn }));
}

window.addEventListener('resize', () => {
    renderConnections();
});

initMatch();
</script>
@endsection
