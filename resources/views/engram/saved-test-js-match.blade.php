@extends('layouts.engram')

@section('title', $test->name)

@section('content')
<div class="relative mx-auto max-w-6xl" id="match-quiz" data-test-slug="{{ $test->slug }}">
    <div class="bg-white/95 shadow-xl rounded-3xl p-6 sm:p-8 relative overflow-hidden">
        <h1 class="text-2xl sm:text-3xl font-bold text-sky-700 text-center mb-6">
            🧩 Match the Sentences with Their Meanings
        </h1>

        <div class="bg-sky-100 border border-sky-200 rounded-2xl p-4 mb-6 text-sm text-slate-700">
            <ul class="list-disc list-inside space-y-1">
                <li>Зліва — речення, справа — пояснення.</li>
                <li>З’єднай кожну пару, перетягнувши лінію між відповідними блоками.</li>
                <li>Кожен елемент може бути з’єднаний лише один раз.</li>
                <li><strong>Check Result</strong> активується, коли всі пари з’єднані.</li>
                <li><strong>Reset</strong> очищує результат та перемішує пари.</li>
            </ul>
        </div>

        <div class="relative">
            <svg id="match-svg" class="absolute inset-0 w-full h-full pointer-events-none"></svg>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-16 relative z-10">
                <div class="space-y-3" id="match-left"></div>
                <div class="space-y-3" id="match-right"></div>
            </div>
        </div>

        <div class="flex flex-col sm:flex-row items-center justify-center gap-3 mt-8">
            <button id="match-check" type="button" class="bg-sky-500 text-white px-5 py-2 rounded-xl shadow disabled:opacity-60 disabled:cursor-not-allowed">
                Check Result
            </button>
            <button id="match-reset" type="button" class="bg-slate-200 hover:bg-slate-300 text-slate-800 px-4 py-2 rounded-xl shadow">
                Reset
            </button>
            <div id="match-result" class="sm:ml-4 font-semibold text-lg text-sky-700"></div>
        </div>
    </div>
</div>

<style>
    #match-quiz .match-item {
        user-select: none;
        transition: background-color 0.25s ease, box-shadow 0.25s ease;
        cursor: crosshair;
    }
    #match-quiz .match-item.correct {
        background-color: #bbf7d0 !important;
    }
    #match-quiz .match-item.incorrect {
        background-color: #fecaca !important;
    }
    #match-quiz .match-item.disabled {
        opacity: 0.6;
        pointer-events: none;
    }
    #match-quiz svg line {
        stroke-width: 2.5;
        stroke-linecap: round;
        transition: stroke 0.3s ease;
    }
</style>

<script>
    window.__INITIAL_JS_TEST_QUESTIONS__ = @json($questionData);
</script>
@include('components.saved-test-js-persistence', ['mode' => $jsStateMode, 'savedState' => $savedState])
@include('components.saved-test-js-helpers')
<script>
(function () {
    const svg = document.getElementById('match-svg');
    const leftCol = document.getElementById('match-left');
    const rightCol = document.getElementById('match-right');
    const checkBtn = document.getElementById('match-check');
    const resetBtn = document.getElementById('match-reset');
    const resultEl = document.getElementById('match-result');

    if (!svg || !leftCol || !rightCol || !checkBtn || !resetBtn) {
        return;
    }

    const state = {
        pairs: [],
        leftOrder: [],
        rightOrder: [],
        connections: [],
        checked: false,
        lastScore: 0,
    };

    let basePairs = [];
    let dragStart = null;
    let tempLine = null;

    const LETTERS = 'abcdefghijklmnopqrstuvwxyz'.split('');

    function toHtml(str) {
        return html(str ?? '');
    }

    function shuffleCopy(values) {
        const clone = values.slice();
        shuffle(clone);

        return clone;
    }

    function tryParseJson(value) {
        if (typeof value !== 'string') {
            return null;
        }

        try {
            const parsed = JSON.parse(value);
            return parsed && typeof parsed === 'object' ? parsed : null;
        } catch (error) {
            return null;
        }
    }

    function extractPairs(questions) {
        const pairs = [];

        if (!Array.isArray(questions)) {
            return pairs;
        }

        questions.forEach((question, index) => {
            if (!question) {
                return;
            }

            const baseId = question.uuid ?? question.id ?? `q-${index}`;
            const parsed = tryParseJson(question.question);
            if (parsed && Array.isArray(parsed.pairs)) {
                parsed.pairs.forEach((pair, pairIndex) => {
                    const left = pair?.sentence ?? pair?.question ?? pair?.left ?? '';
                    const right = pair?.explanation ?? pair?.answer ?? pair?.right ?? '';
                    const id = pair?.id ?? `${baseId}-${pairIndex}`;

                    if (String(left).trim() && String(right).trim()) {
                        pairs.push({
                            id: String(id),
                            sentence: String(left).trim(),
                            explanation: String(right).trim(),
                        });
                    }
                });

                return;
            }

            const sentence = String(question.question ?? '').trim();
            const answerList = Array.isArray(question.answers) ? question.answers : [];
            const candidates = [];
            if (question.answer) {
                candidates.push(question.answer);
            }
            answerList.forEach(ans => {
                if (ans) {
                    candidates.push(ans);
                }
            });
            const explanation = candidates.find(ans => String(ans).trim()) ?? '';

            if (sentence && explanation) {
                pairs.push({
                    id: String(baseId),
                    sentence,
                    explanation: String(explanation).trim(),
                });
            }
        });

        return pairs;
    }

    function sanitizeConnections(savedConnections, validIds) {
        const leftUsed = new Set();
        const rightUsed = new Set();
        const normalized = [];

        if (!Array.isArray(savedConnections)) {
            return normalized;
        }

        savedConnections.forEach(conn => {
            if (!conn) {
                return;
            }
            const leftId = String(conn.leftId ?? conn.left ?? '');
            const rightId = String(conn.rightId ?? conn.right ?? '');

            if (!validIds.includes(leftId) || !validIds.includes(rightId)) {
                return;
            }
            if (leftUsed.has(leftId) || rightUsed.has(rightId)) {
                return;
            }

            normalized.push({ leftId, rightId });
            leftUsed.add(leftId);
            rightUsed.add(rightId);
        });

        return normalized;
    }

    function restoreState(saved) {
        if (!saved || typeof saved !== 'object') {
            return false;
        }

        const validIds = basePairs.map(pair => pair.id);
        const leftOrder = Array.isArray(saved.leftOrder) ? saved.leftOrder.map(String) : null;
        const rightOrder = Array.isArray(saved.rightOrder) ? saved.rightOrder.map(String) : null;

        const sameElements = (order) => {
            if (!Array.isArray(order) || order.length !== validIds.length) {
                return false;
            }
            const sortedA = order.slice().sort();
            const sortedB = validIds.slice().sort();

            return sortedA.every((value, idx) => value === sortedB[idx]);
        };

        if (!sameElements(leftOrder) || !sameElements(rightOrder)) {
            return false;
        }

        state.pairs = basePairs.slice();
        state.leftOrder = leftOrder.slice();
        state.rightOrder = rightOrder.slice();
        state.connections = sanitizeConnections(saved.connections, validIds);
        state.checked = Boolean(saved.checked);
        state.lastScore = Number.isFinite(saved.lastScore) ? saved.lastScore : 0;

        if (state.connections.length !== state.pairs.length) {
            state.checked = false;
            state.lastScore = 0;
        }

        return true;
    }

    function resetState(options = {}) {
        const { immediate = false } = options;
        const ids = basePairs.map(pair => pair.id);
        state.pairs = basePairs.slice();
        state.leftOrder = shuffleCopy(ids);
        state.rightOrder = shuffleCopy(ids);
        state.connections = [];
        state.checked = false;
        state.lastScore = 0;

        renderAll();
        persist(immediate);
    }

    function persist(immediate = false) {
        persistState({
            leftOrder: state.leftOrder,
            rightOrder: state.rightOrder,
            connections: state.connections,
            checked: state.checked,
            lastScore: state.lastScore,
        }, immediate);
    }

    function getPairMap() {
        const map = new Map();
        state.pairs.forEach(pair => {
            map.set(pair.id, pair);
        });

        return map;
    }

    function renderColumns() {
        const pairMap = getPairMap();
        leftCol.innerHTML = '';
        rightCol.innerHTML = '';

        state.leftOrder.forEach((id, index) => {
            const pair = pairMap.get(id);
            if (!pair) {
                return;
            }

            const wrapper = document.createElement('div');
            wrapper.className = 'match-item sentence bg-sky-100 hover:bg-sky-200 p-3 rounded-xl border border-transparent flex gap-3';
            wrapper.dataset.side = 'left';
            wrapper.dataset.pairId = id;

            const marker = document.createElement('span');
            marker.className = 'font-semibold text-sky-700';
            marker.textContent = `${index + 1}.`;

            const text = document.createElement('span');
            text.className = 'flex-1';
            text.innerHTML = toHtml(pair.sentence);

            wrapper.append(marker, text);
            leftCol.appendChild(wrapper);
        });

        state.rightOrder.forEach((id, index) => {
            const pair = pairMap.get(id);
            if (!pair) {
                return;
            }

            const wrapper = document.createElement('div');
            wrapper.className = 'match-item explanation bg-slate-100 hover:bg-slate-200 p-3 rounded-xl border border-transparent flex gap-3';
            wrapper.dataset.side = 'right';
            wrapper.dataset.pairId = id;

            const marker = document.createElement('span');
            marker.className = 'font-semibold text-slate-600 uppercase';
            marker.textContent = `${LETTERS[index] ?? '?'}.`;

            const text = document.createElement('span');
            text.className = 'flex-1';
            text.innerHTML = toHtml(pair.explanation);

            wrapper.append(marker, text);
            rightCol.appendChild(wrapper);
        });
    }

    function removeAllLines() {
        while (svg.firstChild) {
            svg.removeChild(svg.firstChild);
        }
    }

    function getCenter(el) {
        const rect = el.getBoundingClientRect();
        const svgRect = svg.getBoundingClientRect();
        const x = el.dataset.side === 'left' ? rect.right - svgRect.left : rect.left - svgRect.left;
        const y = rect.top - svgRect.top + rect.height / 2;

        return { x, y };
    }

    function createLine(x1, y1, x2, y2, stroke = '#94a3b8') {
        const line = document.createElementNS('http://www.w3.org/2000/svg', 'line');
        line.setAttribute('x1', x1);
        line.setAttribute('y1', y1);
        line.setAttribute('x2', x2);
        line.setAttribute('y2', y2);
        line.setAttribute('stroke', stroke);

        return line;
    }

    function refreshConnections() {
        removeAllLines();
        const pairMap = getPairMap();

        state.connections.forEach(conn => {
            const leftEl = leftCol.querySelector(`[data-pair-id="${CSS.escape(conn.leftId)}"]`);
            const rightEl = rightCol.querySelector(`[data-pair-id="${CSS.escape(conn.rightId)}"]`);

            if (!leftEl || !rightEl || !pairMap.has(conn.leftId) || !pairMap.has(conn.rightId)) {
                return;
            }

            const start = getCenter(leftEl);
            const end = getCenter(rightEl);
            const correct = conn.leftId === conn.rightId;
            const stroke = state.checked ? (correct ? '#16a34a' : '#dc2626') : '#94a3b8';
            const line = createLine(start.x, start.y, end.x, end.y, stroke);
            svg.appendChild(line);
        });
    }

    function applyCheckStyles() {
        const pairMap = getPairMap();
        leftCol.querySelectorAll('.match-item').forEach(el => {
            el.classList.remove('correct', 'incorrect');
        });
        rightCol.querySelectorAll('.match-item').forEach(el => {
            el.classList.remove('correct', 'incorrect');
        });

        if (!state.checked) {
            return;
        }

        state.connections.forEach(conn => {
            const leftEl = leftCol.querySelector(`[data-pair-id="${CSS.escape(conn.leftId)}"]`);
            const rightEl = rightCol.querySelector(`[data-pair-id="${CSS.escape(conn.rightId)}"]`);

            if (!leftEl || !rightEl || !pairMap.has(conn.leftId) || !pairMap.has(conn.rightId)) {
                return;
            }

            const isCorrect = conn.leftId === conn.rightId;
            leftEl.classList.add(isCorrect ? 'correct' : 'incorrect');
            rightEl.classList.add(isCorrect ? 'correct' : 'incorrect');
        });
    }

    function updateButtonState() {
        const total = state.pairs.length;
        const ready = state.connections.length === total && total > 0;
        checkBtn.disabled = !ready;
        if (ready) {
            checkBtn.classList.remove('disabled');
        } else {
            checkBtn.classList.add('disabled');
        }
    }

    function updateResult() {
        if (state.checked) {
            resultEl.textContent = `✅ Correct: ${state.lastScore} / ${state.pairs.length}`;
        } else {
            resultEl.textContent = '';
        }
    }

    function renderAll() {
        renderColumns();
        attachInteractions();
        refreshConnections();
        applyCheckStyles();
        updateButtonState();
        updateResult();
    }

    function detachConnectionBySide(id, side) {
        const predicate = side === 'left'
            ? (conn) => conn.leftId !== id
            : (conn) => conn.rightId !== id;

        state.connections = state.connections.filter(predicate);
    }

    function finalizeConnection(leftId, rightId) {
        if (!leftId || !rightId) {
            return;
        }

        if (leftId === rightId && state.connections.find(conn => conn.leftId === leftId)) {
            // replacing with the same connection is allowed but resets order
            state.connections = state.connections.filter(conn => conn.leftId !== leftId && conn.rightId !== rightId);
        }

        detachConnectionBySide(leftId, 'left');
        detachConnectionBySide(rightId, 'right');

        state.connections.push({ leftId, rightId });
        state.checked = false;
        state.lastScore = 0;

        renderAll();
        persist();
    }

    function handlePointerDown(event) {
        if (event.pointerType === 'mouse' && event.button !== 0) {
            return;
        }

        const element = event.currentTarget;
        dragStart = {
            element,
            side: element.dataset.side,
            pointerId: event.pointerId,
        };

        const { x, y } = getCenter(element);
        tempLine = createLine(x, y, x, y, '#94a3b8');
        svg.appendChild(tempLine);
        event.preventDefault();
    }

    function cleanupDrag() {
        dragStart = null;
        if (tempLine) {
            tempLine.remove();
            tempLine = null;
        }
    }

    function handlePointerMove(event) {
        if (!dragStart || !tempLine || dragStart.pointerId !== event.pointerId) {
            return;
        }

        const svgRect = svg.getBoundingClientRect();
        tempLine.setAttribute('x2', event.clientX - svgRect.left);
        tempLine.setAttribute('y2', event.clientY - svgRect.top);
    }

    function handlePointerUp(event) {
        if (!dragStart || (event.pointerType === 'mouse' && event.button !== 0)) {
            cleanupDrag();
            return;
        }

        const targetNode = document.elementFromPoint(event.clientX, event.clientY);
        const dropTarget = targetNode ? targetNode.closest('[data-side]') : null;

        if (!dropTarget || dropTarget === dragStart.element || dropTarget.dataset.side === dragStart.side) {
            cleanupDrag();
            return;
        }

        const leftEl = dragStart.side === 'left' ? dragStart.element : dropTarget;
        const rightEl = dragStart.side === 'right' ? dragStart.element : dropTarget;

        const leftId = leftEl.dataset.pairId;
        const rightId = rightEl.dataset.pairId;

        cleanupDrag();
        finalizeConnection(leftId, rightId);
    }

    function handlePointerCancel() {
        cleanupDrag();
    }

    function attachInteractions() {
        const items = document.querySelectorAll('#match-quiz [data-side]');
        items.forEach(item => {
            item.removeEventListener('pointerdown', handlePointerDown);
            item.addEventListener('pointerdown', handlePointerDown);
        });
    }

    document.addEventListener('pointermove', handlePointerMove);
    document.addEventListener('pointerup', handlePointerUp);
    document.addEventListener('pointercancel', handlePointerCancel);

    function onCheck() {
        if (state.connections.length !== state.pairs.length) {
            return;
        }

        const correct = state.connections.reduce((acc, conn) => {
            return acc + (conn.leftId === conn.rightId ? 1 : 0);
        }, 0);

        state.checked = true;
        state.lastScore = correct;

        refreshConnections();
        applyCheckStyles();
        updateResult();
        persist();
    }

    function onReset() {
        resetState();
    }

    checkBtn.addEventListener('click', onCheck);
    resetBtn.addEventListener('click', onReset);

    function handleResizeOrScroll() {
        refreshConnections();
    }

    window.addEventListener('resize', handleResizeOrScroll);
    window.addEventListener('scroll', handleResizeOrScroll, { passive: true });

    async function init(forceFresh = false) {
        const questions = await loadQuestions(forceFresh);
        basePairs = extractPairs(questions).map(pair => ({
            id: String(pair.id),
            sentence: pair.sentence,
            explanation: pair.explanation,
        }));

        if (!basePairs.length) {
            leftCol.innerHTML = '<div class="bg-amber-100 text-amber-800 p-4 rounded-xl">Немає доступних пар для цього тесту.</div>';
            rightCol.innerHTML = '';
            checkBtn.disabled = true;
            resetBtn.disabled = true;
            return;
        }

        if (!forceFresh) {
            const saved = getSavedState();
            if (restoreState(saved)) {
                renderAll();
                persist(true);
                return;
            }
        }

        resetState({ immediate: true });
    }

    const restartButton = document.getElementById('restart-test');
    if (restartButton) {
        restartButton.addEventListener('click', () => restartJsTest(init, { button: restartButton }));
    }

    init();
})();
</script>
@endsection
