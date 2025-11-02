@extends('layouts.engram')

@section('title', $test->name)

@section('content')
<div class="mx-auto max-w-6xl px-4 py-8 text-slate-800" id="match-app">
    <header class="mb-6">
        <h1 class="text-2xl sm:text-3xl font-bold text-slate-900">{{ $test->name }}</h1>
        <p class="mt-1 text-sm text-slate-600">З’єднай речення з відповідним поясненням.</p>
    </header>

    @include('components.test-mode-nav')
    @include('components.word-search')
    @include('components.saved-test-progress')
    @include('components.saved-test-js-restart-button')

    <div id="match-loader" class="mb-6 hidden rounded-2xl border border-slate-200 bg-white p-4 text-center text-sm font-medium text-slate-600 shadow-sm">
        Завантаження завдань…
    </div>

    <div id="match-empty" class="mb-6 hidden rounded-2xl border border-slate-200 bg-white p-6 text-center text-sm text-slate-600 shadow-sm">
        Немає доступних питань для цього режиму. Спробуй оновити тест або обрати інший.
    </div>

    <section id="match-board" class="relative hidden rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="match-instructions mb-6 rounded-2xl border border-sky-200 bg-sky-50 p-4 text-sm text-slate-700">
            <ul class="list-disc space-y-1 pl-5">
                <li>Ліворуч — речення, праворуч — пояснення.</li>
                <li>Почни з будь-якого боку й проведи лінію до відповідної пари.</li>
                <li>Кожен елемент можна з’єднати лише один раз. Нове з’єднання перезапише старе.</li>
                <li><strong>Перевірити результат</strong> активується, коли з’єднані всі пари.</li>
                <li><strong>Скинути</strong> очистить лінії та перемішає елементи.</li>
            </ul>
        </div>

        <div class="match-stage">
            <svg id="match-canvas" class="match-canvas" aria-hidden="true"></svg>
            <div class="match-grid">
                <div id="match-left" class="space-y-3"></div>
                <div id="match-right" class="space-y-3"></div>
            </div>
        </div>

        <div class="mt-6 flex flex-wrap items-center gap-3">
            <button id="match-check" type="button" disabled class="rounded-xl bg-sky-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-sky-700 disabled:cursor-not-allowed disabled:bg-sky-300">Перевірити результат</button>
            <button id="match-reset" type="button" class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-100">Скинути</button>
            <span id="match-feedback" class="match-feedback text-slate-700"></span>
        </div>
    </section>

    <div id="summary" class="mt-8 hidden">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="text-lg font-semibold text-slate-900">Підсумок</div>
            <p id="summary-text" class="mt-2 text-sm text-slate-600"></p>
            <div class="mt-4 flex flex-wrap gap-2">
                <button id="match-summary-retry" type="button" class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-100">Спробувати ще раз</button>
            </div>
        </div>
    </div>
</div>

<style>
    #match-app .match-stage {
        position: relative;
        min-height: 320px;
    }
    #match-app .match-canvas {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        pointer-events: none;
    }
    #match-app .match-grid {
        position: relative;
        z-index: 10;
        display: grid;
        gap: 2.5rem;
    }
    @media (min-width: 768px) {
        #match-app .match-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 4rem;
        }
    }
    #match-app .match-item {
        user-select: none;
        -webkit-user-select: none;
        touch-action: none;
        cursor: crosshair;
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
        padding: 0.85rem 1rem;
        border-radius: 0.9rem;
        border: 1px solid #e2e8f0;
        background: #f1f5f9;
        transition: background 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease;
    }
    #match-app .match-item:hover {
        background: #e2e8f0;
        border-color: #cbd5f5;
        box-shadow: 0 8px 16px rgba(15, 23, 42, 0.08);
    }
    #match-app .match-item .match-label {
        font-weight: 700;
        font-size: 0.95rem;
        color: #0f172a;
        margin-top: 0.15rem;
        min-width: 1.5rem;
    }
    #match-app .match-item .match-text {
        flex: 1;
        font-size: 0.95rem;
        line-height: 1.5;
    }
    #match-app .match-item.is-linked {
        background: #dbeafe;
        border-color: #93c5fd;
    }
    #match-app .match-item.correct {
        background: #bbf7d0;
        border-color: #22c55e;
    }
    #match-app .match-item.incorrect {
        background: #fecaca;
        border-color: #ef4444;
    }
    #match-app svg line {
        stroke-width: 3;
        stroke-linecap: round;
        transition: stroke 0.3s ease;
    }
    #match-app svg line[data-temp="true"] {
        stroke-dasharray: 6 6;
    }
    #match-app .match-feedback {
        min-height: 1.5rem;
        font-weight: 600;
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
(() => {
    const boardEl = document.getElementById('match-board');
    const leftCol = document.getElementById('match-left');
    const rightCol = document.getElementById('match-right');
    const svg = document.getElementById('match-canvas');
    const checkBtn = document.getElementById('match-check');
    const resetBtn = document.getElementById('match-reset');
    const feedbackEl = document.getElementById('match-feedback');
    const loaderEl = document.getElementById('match-loader');
    const emptyEl = document.getElementById('match-empty');
    const summaryEl = document.getElementById('summary');
    const summaryTextEl = document.getElementById('summary-text');
    const summaryRetryBtn = document.getElementById('match-summary-retry');
    const restartButton = document.getElementById('restart-test');

    if (!boardEl || !leftCol || !rightCol || !svg) {
        return;
    }

    let state = {
        items: [],
        orderLeft: [],
        orderRight: [],
        connections: [],
        answered: 0,
        correct: 0,
        completed: false,
    };

    let activePointerId = null;
    let dragStart = null;
    let tempLine = null;

    const updateLinesOnResize = () => requestAnimationFrame(renderConnections);
    window.addEventListener('resize', updateLinesOnResize);
    if (window.ResizeObserver) {
        const observer = new ResizeObserver(updateLinesOnResize);
        observer.observe(boardEl);
    }
    window.addEventListener('pointermove', handlePointerMove, { passive: true });
    window.addEventListener('pointerup', handlePointerUp, { passive: true });
    window.addEventListener('pointercancel', handlePointerCancel, { passive: true });

    if (checkBtn) {
        checkBtn.addEventListener('click', checkAnswers);
    }
    if (resetBtn) {
        resetBtn.addEventListener('click', () => resetMatches({ shuffle: true }));
    }
    if (summaryRetryBtn) {
        summaryRetryBtn.addEventListener('click', () => resetMatches({ shuffle: true }));
    }
    if (restartButton) {
        restartButton.addEventListener('click', () => restartJsTest(init, {
            button: restartButton,
            showLoaderFn: toggleLoader,
        }));
    }

    async function init(forceFresh = false) {
        toggleLoader(true);
        const baseQuestions = await loadQuestions(forceFresh);
        QUESTIONS = Array.isArray(baseQuestions) ? baseQuestions : [];
        const pairs = buildPairs(QUESTIONS);
        toggleLoader(false);

        if (!pairs.length) {
            showBoard(false);
            showEmpty(true);
            state = {
                items: [],
                orderLeft: [],
                orderRight: [],
                connections: [],
                answered: 0,
                correct: 0,
                completed: false,
            };
            renderAll();
            persistState(state, true);
            return;
        }

        showEmpty(false);
        showBoard(true);

        let restored = null;
        if (!forceFresh) {
            const saved = getSavedState();
            restored = restoreState(saved, pairs);
        }

        state = restored ?? createNewState(pairs);
        renderAll();
        persistState(state, true);
    }

    function buildPairs(data) {
        if (!Array.isArray(data)) {
            return [];
        }
        const seen = new Set();
        return data
            .map((entry, index) => {
                const question = String(entry?.question ?? '').trim();
                const answers = Array.isArray(entry?.answers) ? entry.answers : [];
                const fallbackAnswer = entry?.answer ?? '';
                const rawAnswer = answers.find((value) => value && String(value).trim() !== '') ?? fallbackAnswer;
                const answer = String(rawAnswer ?? '').trim();
                if (!question || !answer) {
                    return null;
                }
                let id = entry?.uuid ?? entry?.id ?? `q${index}`;
                id = String(id);
                while (seen.has(id)) {
                    id = `${id}-${index}`;
                }
                seen.add(id);
                return {
                    id,
                    question,
                    answer,
                };
            })
            .filter(Boolean);
    }

    function createNewState(pairs) {
        const ids = pairs.map((item) => item.id);
        const leftOrder = ids.slice();
        const rightOrder = ids.slice();
        shuffle(leftOrder);
        shuffle(rightOrder);
        return {
            items: pairs,
            orderLeft: leftOrder,
            orderRight: rightOrder,
            connections: [],
            answered: 0,
            correct: 0,
            completed: false,
        };
    }

    function restoreState(saved, pairs) {
        if (!saved || !Array.isArray(saved.orderLeft) || !Array.isArray(saved.orderRight)) {
            return null;
        }
        if (saved.orderLeft.length !== pairs.length || saved.orderRight.length !== pairs.length) {
            return null;
        }
        const validIds = new Set(pairs.map((item) => item.id));
        if (!saved.orderLeft.every((id) => validIds.has(id)) || !saved.orderRight.every((id) => validIds.has(id))) {
            return null;
        }
        const connections = Array.isArray(saved.connections)
            ? saved.connections
                .filter((conn) => validIds.has(conn.leftId) && validIds.has(conn.rightId))
                .map((conn) => ({
                    leftId: conn.leftId,
                    rightId: conn.rightId,
                    status: conn.status === true ? true : conn.status === false ? false : null,
                }))
            : [];
        const answered = Number.isFinite(saved.answered) ? Math.max(0, Math.min(saved.answered, pairs.length)) : connections.length;
        const correct = Number.isFinite(saved.correct) ? Math.max(0, Math.min(saved.correct, pairs.length)) : connections.filter((conn) => conn.status === true).length;
        return {
            items: pairs,
            orderLeft: saved.orderLeft.slice(),
            orderRight: saved.orderRight.slice(),
            connections,
            answered,
            correct,
            completed: Boolean(saved.completed),
        };
    }

    function renderAll() {
        renderColumns();
        requestAnimationFrame(() => {
            renderConnections();
            applyItemStatuses();
        });
        updateButtonState();
        updateProgress();
        updateSummary();
        if (state.completed && state.items.length) {
            const success = state.correct === state.items.length;
            showFeedback(`${success ? '✅' : '❌'} Правильних пар: ${state.correct} / ${state.items.length}`, success);
        } else {
            clearFeedback();
        }
    }

    function renderColumns() {
        leftCol.innerHTML = '';
        rightCol.innerHTML = '';
        state.orderLeft.forEach((id, index) => {
            const item = state.items.find((entry) => entry.id === id);
            if (!item) {
                return;
            }
            const node = document.createElement('div');
            node.className = 'match-item match-item-left';
            node.dataset.id = id;
            node.dataset.type = 'left';
            node.innerHTML = `<span class="match-label">${index + 1}.</span><span class="match-text">${html(item.question)}</span>`;
            node.addEventListener('pointerdown', handlePointerDown);
            leftCol.appendChild(node);
        });
        state.orderRight.forEach((id, index) => {
            const item = state.items.find((entry) => entry.id === id);
            if (!item) {
                return;
            }
            const label = String.fromCharCode(97 + index);
            const node = document.createElement('div');
            node.className = 'match-item match-item-right';
            node.dataset.id = id;
            node.dataset.type = 'right';
            node.innerHTML = `<span class="match-label">${label})</span><span class="match-text">${html(item.answer)}</span>`;
            node.addEventListener('pointerdown', handlePointerDown);
            rightCol.appendChild(node);
        });
    }

    function renderConnections() {
        if (!svg) {
            return;
        }
        svg.querySelectorAll('line[data-connection]').forEach((line) => line.remove());
        state.connections.forEach((conn) => {
            const leftNode = leftCol.querySelector(`[data-id="${conn.leftId}"]`);
            const rightNode = rightCol.querySelector(`[data-id="${conn.rightId}"]`);
            if (!leftNode || !rightNode) {
                return;
            }
            const start = getCenter(leftNode);
            const end = getCenter(rightNode);
            const line = createLine(start.x, start.y, end.x, end.y, getStroke(conn));
            line.dataset.connection = 'true';
            svg.appendChild(line);
        });
    }

    function applyItemStatuses() {
        boardEl.querySelectorAll('.match-item').forEach((node) => {
            node.classList.remove('is-linked', 'correct', 'incorrect');
        });
        state.connections.forEach((conn) => {
            const leftNode = leftCol.querySelector(`[data-id="${conn.leftId}"]`);
            const rightNode = rightCol.querySelector(`[data-id="${conn.rightId}"]`);
            if (leftNode) {
                leftNode.classList.add('is-linked');
            }
            if (rightNode) {
                rightNode.classList.add('is-linked');
            }
            if (state.completed) {
                const className = conn.status === true ? 'correct' : conn.status === false ? 'incorrect' : null;
                if (className) {
                    leftNode?.classList.add(className);
                    rightNode?.classList.add(className);
                }
            }
        });
    }

    function getStroke(conn) {
        if (state.completed) {
            if (conn.status === true) {
                return '#16a34a';
            }
            if (conn.status === false) {
                return '#dc2626';
            }
        }
        return '#0284c7';
    }

    function updateButtonState() {
        if (!checkBtn) {
            return;
        }
        const canCheck = state.items.length > 0 && state.connections.length === state.items.length;
        checkBtn.disabled = !canCheck;
    }

    function updateProgress() {
        const total = state.items.length;
        const answered = Math.min(state.connections.length, total);
        const label = document.getElementById('progress-label');
        if (label) {
            label.textContent = `${answered} / ${total}`;
        }
        const scoreLabel = document.getElementById('score-label');
        const percent = state.completed && total ? pct(state.correct, total) : 0;
        if (scoreLabel) {
            scoreLabel.textContent = `Точність: ${percent}%`;
        }
        const bar = document.getElementById('progress-bar');
        if (bar) {
            bar.style.width = `${total ? (answered / total) * 100 : 0}%`;
        }
    }

    function updateSummary() {
        if (!summaryEl) {
            return;
        }
        if (state.completed && state.items.length) {
            summaryEl.classList.remove('hidden');
            if (summaryTextEl) {
                summaryTextEl.textContent = `Правильних пар: ${state.correct} із ${state.items.length} (${pct(state.correct, state.items.length)}%).`;
            }
        } else {
            summaryEl.classList.add('hidden');
        }
    }

    function clearFeedback() {
        if (!feedbackEl) {
            return;
        }
        feedbackEl.textContent = '';
        feedbackEl.classList.remove('text-emerald-600', 'text-rose-600');
    }

    function showFeedback(message, isSuccess) {
        if (!feedbackEl) {
            return;
        }
        feedbackEl.textContent = message;
        feedbackEl.classList.remove('text-emerald-600', 'text-rose-600');
        feedbackEl.classList.add(isSuccess ? 'text-emerald-600' : 'text-rose-600');
    }

    function connectPair(leftId, rightId) {
        const remaining = state.connections
            .filter((conn) => conn.leftId !== leftId && conn.rightId !== rightId)
            .map((conn) => ({ ...conn, status: null }));
        remaining.push({ leftId, rightId, status: null });
        state.connections = remaining;
        state.completed = false;
        state.correct = 0;
        state.answered = state.connections.length;
        renderAll();
        persistState(state);
    }

    function resetMatches(options = {}) {
        const shouldShuffle = options.shuffle === true;
        const ids = state.items.map((item) => item.id);
        state.orderLeft = shouldShuffle ? shuffleCopy(ids) : state.orderLeft.slice();
        state.orderRight = shouldShuffle ? shuffleCopy(ids) : state.orderRight.slice();
        state.connections = [];
        state.completed = false;
        state.correct = 0;
        state.answered = 0;
        renderAll();
        persistState(state);
    }

    function shuffleCopy(ids) {
        const copy = ids.slice();
        shuffle(copy);
        return copy;
    }

    function checkAnswers() {
        if (checkBtn?.disabled || state.connections.length !== state.items.length) {
            return;
        }
        let correct = 0;
        state.connections = state.connections.map((conn) => {
            const isCorrect = conn.leftId === conn.rightId;
            if (isCorrect) {
                correct += 1;
            }
            return { ...conn, status: isCorrect };
        });
        state.correct = correct;
        state.answered = state.connections.length;
        state.completed = true;
        renderConnections();
        applyItemStatuses();
        updateProgress();
        updateSummary();
        const success = correct === state.items.length;
        showFeedback(`${success ? '✅' : '❌'} Правильних пар: ${correct} / ${state.items.length}`, success);
        persistState(state);
    }

    function getCenter(element) {
        const elementRect = element.getBoundingClientRect();
        const svgRect = svg.getBoundingClientRect();
        const isLeft = element.dataset.type === 'left';
        const x = isLeft ? elementRect.right - svgRect.left : elementRect.left - svgRect.left;
        const y = elementRect.top - svgRect.top + elementRect.height / 2;
        return { x, y };
    }

    function createLine(x1, y1, x2, y2, stroke) {
        const line = document.createElementNS('http://www.w3.org/2000/svg', 'line');
        line.setAttribute('x1', x1);
        line.setAttribute('y1', y1);
        line.setAttribute('x2', x2);
        line.setAttribute('y2', y2);
        line.setAttribute('stroke', stroke);
        line.setAttribute('stroke-linecap', 'round');
        line.setAttribute('stroke-width', '3');
        return line;
    }

    function handlePointerDown(event) {
        const node = event.currentTarget;
        const type = node.dataset.type;
        if (!type) {
            return;
        }
        dragStart = {
            element: node,
            type,
            id: node.dataset.id,
        };
        activePointerId = event.pointerId;
        const start = getCenter(node);
        tempLine = createLine(start.x, start.y, start.x, start.y, '#0ea5e9');
        tempLine.dataset.temp = 'true';
        svg.appendChild(tempLine);
        node.setPointerCapture?.(event.pointerId);
        event.preventDefault();
    }

    function handlePointerMove(event) {
        if (!tempLine || activePointerId !== event.pointerId) {
            return;
        }
        const svgRect = svg.getBoundingClientRect();
        tempLine.setAttribute('x2', event.clientX - svgRect.left);
        tempLine.setAttribute('y2', event.clientY - svgRect.top);
    }

    function handlePointerUp(event) {
        if (!dragStart || activePointerId !== event.pointerId) {
            return;
        }
        const target = event.target.closest('.match-item');
        if (target && target.dataset.type && target.dataset.type !== dragStart.type) {
            const leftId = dragStart.type === 'left' ? dragStart.id : target.dataset.id;
            const rightId = dragStart.type === 'right' ? dragStart.id : target.dataset.id;
            connectPair(leftId, rightId);
        }
        cleanupPointer(event.pointerId);
    }

    function handlePointerCancel(event) {
        if (activePointerId !== event.pointerId) {
            return;
        }
        cleanupPointer(event.pointerId);
    }

    function cleanupPointer(pointerId) {
        if (dragStart?.element?.releasePointerCapture) {
            try {
                dragStart.element.releasePointerCapture(pointerId);
            } catch (error) {
                // ignore
            }
        }
        if (tempLine) {
            tempLine.remove();
            tempLine = null;
        }
        dragStart = null;
        activePointerId = null;
    }

    function toggleLoader(show) {
        if (!loaderEl) {
            return;
        }
        loaderEl.classList.toggle('hidden', !show);
        if (boardEl) {
            boardEl.classList.toggle('opacity-50', show);
            boardEl.classList.toggle('pointer-events-none', show);
        }
    }

    function showBoard(visible) {
        if (!boardEl) {
            return;
        }
        boardEl.classList.toggle('hidden', !visible);
    }

    function showEmpty(visible) {
        if (!emptyEl) {
            return;
        }
        emptyEl.classList.toggle('hidden', !visible);
    }

    init();
})();
</script>
@endsection
