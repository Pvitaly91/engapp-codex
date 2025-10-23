@extends('layouts.engram')

@section('title', $test->name)

@section('content')
@php
    $questionCollection = collect($questionData ?? []);
    $questionTotal = $questionCollection->count();
    $blankTotal = $questionCollection->sum(function ($question) {
        $answerMap = data_get($question, 'answer_map');
        if (is_array($answerMap) && ! empty($answerMap)) {
            return collect($answerMap)
                ->filter(fn ($value) => ($value ?? '') !== '')
                ->count();
        }

        $answers = data_get($question, 'answers');
        if (is_array($answers)) {
            return collect($answers)
                ->filter(fn ($value) => ($value ?? '') !== '')
                ->count();
        }

        return 0;
    });
    $scoreTotal = $blankTotal ?: $questionTotal;
@endphp
<div class="drag-quiz mx-auto w-full max-w-[1100px]" id="drag-quiz">
    <div class="drag-quiz__grid">
        <div class="drag-quiz__card drag-quiz__left">
            <header class="drag-quiz__header">
                <h1 class="drag-quiz__title">{{ $test->name }}</h1>
                <p class="drag-quiz__subtitle">Перетягни правильне <strong>question word</strong> у пропуск. Можна також натиснути на слово, а потім на пропуск — це зручно на мобільних пристроях.</p>
            </header>

            <div id="drag-quiz-tasks"></div>
        </div>

        <aside class="drag-quiz__card drag-quiz__right">
            <h3 class="drag-quiz__heading">Банк слів</h3>
            <div class="drag-quiz__legend">Перетягуй або натискай, щоб обрати слово.</div>
            <div id="drag-quiz-bank" class="drag-quiz__bank" aria-label="Word bank"></div>

            <div class="drag-quiz__controls">
                <button id="drag-quiz-check" class="drag-quiz__btn">Перевірити</button>
                <button id="drag-quiz-retry" class="drag-quiz__btn drag-quiz__btn--secondary">Спробувати ще</button>
                <button id="drag-quiz-show" class="drag-quiz__btn drag-quiz__btn--ghost">Показати відповіді</button>
                <div class="drag-quiz__score" id="drag-quiz-score">0 / {{ $scoreTotal }}</div>
            </div>
            <p class="drag-quiz__hint">Підсвічення: <span class="drag-quiz__hint--correct">зелений</span> — вірно, <span class="drag-quiz__hint--wrong">червоний</span> — помилка.</p>
        </aside>
    </div>
</div>

<style>
@media (max-width: 900px) {
    body.drag-quiz-no-sticky-header > header {
        position: static !important;
        top: auto !important;
    }
}

.drag-quiz {
    --quiz-bg: #f8fafc;
    --quiz-card: #ffffff;
    --quiz-text: #0f172a;
    --quiz-muted: #64748b;
    --quiz-accent: #2563eb;
    --quiz-ok: #16a34a;
    --quiz-bad: #dc2626;
    --quiz-chip: #eef2ff;
    --quiz-chip-border: #c7d2fe;
    --drag-quiz-sticky-offset: 16px;
    color: var(--quiz-text);
}
.drag-quiz__header {
    margin: 0;
}
.drag-quiz__title {
    font-size: clamp(1.375rem, 3vw, 2rem);
    margin: 0 0 0.25rem;
}
.drag-quiz__subtitle {
    margin: 0;
    color: var(--quiz-muted);
}
.drag-quiz__grid {
    display: grid;
    grid-template-columns: 1fr 300px;
    gap: 20px;
}
@media (max-width: 900px) {
    .drag-quiz__grid {
        grid-template-columns: 1fr;
    }
    .drag-quiz__right {
        order: -1;
        position: sticky;
        top: var(--drag-quiz-sticky-offset);
        z-index: 20;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.12);
        margin-bottom: 12px;
        max-height: calc(100vh - var(--drag-quiz-sticky-offset) - 8px);
        overflow: hidden;
    }
    .drag-quiz__bank {
        max-height: min(240px, 33vh);
        overflow-y: auto;
        padding-right: 12px;
        align-content: flex-start;
    }
    .drag-quiz {
        --drag-quiz-sticky-offset: calc(var(--engram-header-height, 64px) + 8px);
    }
}
.drag-quiz__card {
    background: var(--quiz-card);
    border: 1px solid #e5e7eb;
    border-radius: 14px;
    box-shadow: 0 1px 0 rgba(0, 0, 0, 0.02);
}
.drag-quiz__left {
    padding: 14px;
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}
.drag-quiz__right {
    padding: 14px;
    position: sticky;
    top: var(--drag-quiz-sticky-offset);
    align-self: start;
    display: flex;
    flex-direction: column;
}
.drag-quiz__bank {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    min-height: 54px;
    padding: 8px;
    border: 1px dashed #d1d5db;
    border-radius: 10px;
    background: #fafafa;
    align-content: flex-start;
    -webkit-overflow-scrolling: touch;
}
.drag-quiz__token {
    user-select: none;
    cursor: grab;
    padding: 8px 10px;
    border-radius: 999px;
    font-weight: 600;
    background: var(--quiz-chip);
    border: 1px solid var(--quiz-chip-border);
    transition: transform 0.1s ease, box-shadow 0.1s ease, background 0.2s ease;
    touch-action: manipulation;
}
.drag-quiz__token[draggable="true"]:active {
    cursor: grabbing;
    transform: scale(0.96);
}
.drag-quiz__token.is-selected {
    outline: 2px solid var(--quiz-accent);
    box-shadow: 0 0 0 2px #dbeafe;
}
.drag-quiz__token.is-fixed {
    cursor: default;
}
.drag-quiz__controls {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    margin-top: 12px;
    align-items: center;
}
.drag-quiz__btn {
    appearance: none;
    border: 1px solid #e5e7eb;
    background: #111827;
    color: #fff;
    padding: 10px 14px;
    border-radius: 10px;
    font-weight: 700;
    cursor: pointer;
}
.drag-quiz__btn--secondary {
    background: #fff;
    color: #111827;
}
.drag-quiz__btn--ghost {
    background: #fff;
    color: #111827;
    border-style: dashed;
}
.drag-quiz__score {
    margin-left: auto;
    font-weight: 800;
}
.drag-quiz__row {
    display: grid;
    grid-template-columns: auto 1fr;
    gap: 10px;
    padding: 10px 8px;
    align-items: flex-start;
    border-bottom: 1px dashed #e5e7eb;
}
.drag-quiz__num {
    width: 28px;
    text-align: right;
    color: var(--quiz-muted);
}
.drag-quiz__sentence {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    align-items: center;
}
.drag-quiz__drop {
    min-width: 110px;
    min-height: 38px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 4px 10px;
    border: 2px dashed #d1d5db;
    border-radius: 10px;
    background: #fff;
    color: #64748b;
    transition: border-color 0.15s ease, background 0.15s ease;
}
.drag-quiz__drop.is-hover {
    border-color: #60a5fa;
    background: #eff6ff;
}
.drag-quiz__drop.is-filled {
    border-style: solid;
    color: var(--quiz-text);
}
.drag-quiz__drop.is-correct {
    border-color: var(--quiz-ok);
    background: #ecfdf5;
}
.drag-quiz__drop.is-wrong {
    border-color: var(--quiz-bad);
    background: #fef2f2;
}
.drag-quiz__remove {
    margin-left: 6px;
    font-weight: 900;
    color: #9ca3af;
    cursor: pointer;
    user-select: none;
}
.drag-quiz__tail {
    color: var(--quiz-muted);
}
.drag-quiz__hint {
    font-size: 12px;
    color: var(--quiz-muted);
    margin: 8px 0 0;
}
.drag-quiz__hint--correct {
    color: var(--quiz-ok);
    font-weight: 700;
}
.drag-quiz__hint--wrong {
    color: var(--quiz-bad);
    font-weight: 700;
}
.drag-quiz__heading {
    margin: 6px 0 10px;
}
.drag-quiz__legend {
    font-size: 12px;
    color: var(--quiz-muted);
    margin-bottom: 8px;
}
</style>

<script>
window.__INITIAL_JS_TEST_QUESTIONS__ = @json($questionData);
</script>
<script>
(function () {
    const rawQuestions = Array.isArray(window.__INITIAL_JS_TEST_QUESTIONS__)
        ? window.__INITIAL_JS_TEST_QUESTIONS__
        : [];

    function normalize(value) {
        return String(value || '')
            .replace(/\s+/g, ' ')
            .trim()
            .toLowerCase();
    }

    function escapeSelector(value) {
        if (typeof CSS !== 'undefined' && typeof CSS.escape === 'function') {
            return CSS.escape(value);
        }

        return value.replace(/"/g, '\\"');
    }

    function buildAnswerMap(item, answersArray) {
        const map = {};

        if (item && typeof item.answer_map === 'object' && item.answer_map !== null) {
            Object.entries(item.answer_map).forEach(([marker, value]) => {
                map[String(marker)] = String(value ?? '');
            });
        }

        answersArray.forEach((value, idx) => {
            const marker = `a${idx + 1}`;
            if (!(marker in map)) {
                map[marker] = String(value ?? '');
            }
        });

        if (!('a1' in map) && typeof item.answer === 'string') {
            map.a1 = String(item.answer);
        }

        return map;
    }

    function buildQuestion(item, index) {
        const rawText = typeof item.question === 'string' ? item.question : '';
        const [line, tail = ''] = rawText.split(/\r?\n/, 2);
        const sentence = line || '';
        const answersArray = Array.isArray(item.answers) ? item.answers : [];
        const answerMap = buildAnswerMap(item, answersArray);

        const placeholderRegex = /\{a(\d+)\}/g;
        const segments = [];
        const blanks = [];
        let match;
        let lastIndex = 0;

        while ((match = placeholderRegex.exec(sentence)) !== null) {
            const preceding = sentence.slice(lastIndex, match.index);
            if (preceding) {
                segments.push({ type: 'text', value: preceding });
            }

            const number = parseInt(match[1], 10);
            const marker = Number.isFinite(number) ? `a${number}` : `a${blanks.length + 1}`;
            const fallbackIndex = Number.isFinite(number) ? number - 1 : blanks.length;
            const answer = answerMap[marker] ?? answersArray[fallbackIndex] ?? '';

            const blankIndex = blanks.length;
            blanks.push({
                marker,
                answer,
                normalized: normalize(answer),
            });
            segments.push({ type: 'blank', blankIndex });

            lastIndex = match.index + match[0].length;
        }

        const trailing = sentence.slice(lastIndex);
        if (trailing) {
            segments.push({ type: 'text', value: trailing });
        }

        if (!blanks.length) {
            const parts = sentence.split('_____');
            if (parts.length > 1) {
                parts.forEach((part, partIndex) => {
                    if (part) {
                        segments.push({ type: 'text', value: part });
                    }

                    if (partIndex < parts.length - 1) {
                        const blankIndex = blanks.length;
                        const marker = `a${blankIndex + 1}`;
                        const answer = answerMap[marker] ?? answersArray[blankIndex] ?? '';
                        blanks.push({
                            marker,
                            answer,
                            normalized: normalize(answer),
                        });
                        segments.push({ type: 'blank', blankIndex });
                    }
                });
            } else if (sentence) {
                segments.push({ type: 'text', value: sentence });
            }
        }

        return {
            index,
            segments,
            blanks,
            tail,
        };
    }

    const quizRootEl = document.getElementById('drag-quiz');
    const headerEl = typeof document !== 'undefined' ? document.querySelector('body > header') : null;

    if (typeof document !== 'undefined' && document.body) {
        document.body.classList.add('drag-quiz-no-sticky-header');
        window.addEventListener('beforeunload', () => {
            document.body.classList.remove('drag-quiz-no-sticky-header');
        });
    }

    function setupStickyOffsetWatcher() {
        if (!quizRootEl || !headerEl || typeof window === 'undefined') {
            return;
        }

        let rafId = null;

        const applyOffset = () => {
            rafId = null;
            const rect = headerEl.getBoundingClientRect();
            const headerBottom = rect ? rect.bottom : 0;
            const headerHeight = rect ? rect.height : 0;
            const visibleHeader = Math.max(0, Math.min(headerBottom, headerHeight));
            const offset = Math.max(8, Math.round(visibleHeader + 8));
            quizRootEl.style.setProperty('--drag-quiz-sticky-offset', `${offset}px`);
        };

        const requestOffsetUpdate = () => {
            if (rafId !== null) {
                return;
            }

            rafId = window.requestAnimationFrame(applyOffset);
        };

        applyOffset();

        window.addEventListener('scroll', requestOffsetUpdate, { passive: true });
        window.addEventListener('resize', requestOffsetUpdate);

        if (typeof ResizeObserver === 'function') {
            const observer = new ResizeObserver(requestOffsetUpdate);
            observer.observe(headerEl);
        }
    }

    setupStickyOffsetWatcher();

    const questions = rawQuestions.map((item, index) => buildQuestion(item, index));

    const tasksEl = document.getElementById('drag-quiz-tasks');
    const bankEl = document.getElementById('drag-quiz-bank');
    const scoreEl = document.getElementById('drag-quiz-score');
    const checkBtn = document.getElementById('drag-quiz-check');
    const retryBtn = document.getElementById('drag-quiz-retry');
    const showBtn = document.getElementById('drag-quiz-show');

    let tokenSerial = 0;
    const baseTokens = [];
    questions.forEach((question) => {
        question.blanks.forEach((blank) => {
            const word = String(blank.answer ?? '').trim();
            if (!word) {
                return;
            }
            tokenSerial += 1;
            baseTokens.push({ id: `token-${tokenSerial}`, word });
        });
    });

    const totalTargets = baseTokens.length;
    const scoreTotal = totalTargets > 0 ? totalTargets : questions.length;
    let selectedTokenId = null;
    const nav = typeof navigator !== 'undefined' ? navigator : null;
    const supportsTouch =
        typeof window !== 'undefined' &&
        (('ontouchstart' in window) || (nav && ((nav.maxTouchPoints || 0) > 0 || (nav.msMaxTouchPoints || 0) > 0)));
    let activeTouchId = null;
    let activeTouchTokenId = null;
    let touchHoverDrop = null;
    let touchStartPoint = null;

    function clearTouchSelection() {
        if (touchHoverDrop) {
            touchHoverDrop.classList.remove('is-hover');
            touchHoverDrop = null;
        }

        if (supportsTouch && bankEl && activeTouchTokenId) {
            const selector = `.drag-quiz__token[data-id="${escapeSelector(activeTouchTokenId)}"]`;
            const activeToken = bankEl.querySelector(selector);
            if (activeToken) {
                activeToken.classList.remove('is-selected');
            }
        }

        activeTouchId = null;
        activeTouchTokenId = null;
        touchStartPoint = null;
        selectedTokenId = null;
    }

    function findTouchById(touchList, identifier) {
        if (!touchList || typeof touchList.length !== 'number') {
            return null;
        }

        for (let i = 0; i < touchList.length; i += 1) {
            const touch = touchList.item ? touchList.item(i) : touchList[i];
            if (touch && touch.identifier === identifier) {
                return touch;
            }
        }

        return null;
    }

    function handleTokenTouchStart(event, tokenEl) {
        if (!supportsTouch || !tokenEl) {
            return;
        }

        const touch = (event.changedTouches && event.changedTouches[0]) || (event.touches && event.touches[0]);
        const tokenId = tokenEl.dataset.id;

        if (!touch || !tokenId) {
            return;
        }

        activeTouchId = touch.identifier;
        activeTouchTokenId = tokenId;
        touchStartPoint = { x: touch.clientX, y: touch.clientY };

        bankEl?.querySelectorAll('.drag-quiz__token').forEach((node) => node.classList.remove('is-selected'));
        tokenEl.classList.add('is-selected');
        selectedTokenId = tokenId;

        if (touchHoverDrop) {
            touchHoverDrop.classList.remove('is-hover');
            touchHoverDrop = null;
        }
    }

    function handleTouchMove(event) {
        if (!supportsTouch || activeTouchId === null) {
            return;
        }

        const touch =
            findTouchById(event.changedTouches, activeTouchId) ||
            findTouchById(event.touches, activeTouchId);

        if (!touch) {
            return;
        }

        const element = document.elementFromPoint(touch.clientX, touch.clientY);
        const drop = element ? element.closest('.drag-quiz__drop') : null;

        const hasMovedEnough =
            touchStartPoint &&
            (Math.abs(touch.clientX - touchStartPoint.x) > 6 || Math.abs(touch.clientY - touchStartPoint.y) > 6);

        if (drop !== touchHoverDrop) {
            if (touchHoverDrop) {
                touchHoverDrop.classList.remove('is-hover');
            }
            if (drop) {
                drop.classList.add('is-hover');
            }
            touchHoverDrop = drop || null;
        }

        if (drop || hasMovedEnough) {
            event.preventDefault();
        }
    }

    function handleTouchEnd(event) {
        if (!supportsTouch || activeTouchId === null) {
            return;
        }

        const touch =
            findTouchById(event.changedTouches, activeTouchId) ||
            findTouchById(event.touches, activeTouchId);

        if (!touch) {
            clearTouchSelection();
            return;
        }

        const element = document.elementFromPoint(touch.clientX, touch.clientY);
        const drop = element ? element.closest('.drag-quiz__drop') : null;

        if (drop && activeTouchTokenId) {
            placeTokenInDrop(activeTouchTokenId, drop);
            event.preventDefault();
        }

        clearTouchSelection();
    }

    if (supportsTouch) {
        document.addEventListener('touchmove', handleTouchMove, { passive: false });
        document.addEventListener('touchend', handleTouchEnd, { passive: false });
        document.addEventListener('touchcancel', handleTouchEnd, { passive: false });
    }

    function updateScoreLabel(correctCount) {
        if (!scoreEl) {
            return;
        }
        scoreEl.textContent = `${correctCount} / ${scoreTotal}`;
    }

    function addTokenToBank(tokenData) {
        const token = document.createElement('div');
        token.className = 'drag-quiz__token';
        token.textContent = tokenData.word;
        token.dataset.id = tokenData.id;
        token.dataset.word = tokenData.word;
        token.setAttribute('draggable', 'true');
        token.addEventListener('dragstart', (event) => {
            event.dataTransfer.setData('text/plain', tokenData.id);
        });
        token.addEventListener('click', () => {
            const alreadySelected = token.classList.contains('is-selected');
            bankEl.querySelectorAll('.drag-quiz__token').forEach((node) => node.classList.remove('is-selected'));
            if (alreadySelected) {
                selectedTokenId = null;
            } else {
                token.classList.add('is-selected');
                selectedTokenId = tokenData.id;
            }
        });
        if (supportsTouch) {
            token.addEventListener('touchstart', (event) => handleTokenTouchStart(event, token), { passive: true });
        }
        bankEl.appendChild(token);
    }

    function shuffled(list) {
        const arr = list.slice();
        for (let i = arr.length - 1; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1));
            [arr[i], arr[j]] = [arr[j], arr[i]];
        }
        return arr;
    }

    function renderBank(useShuffle = true) {
        bankEl.innerHTML = '';
        const tokens = useShuffle ? shuffled(baseTokens) : baseTokens.slice();
        tokens.forEach((token) => addTokenToBank({ id: token.id, word: token.word }));
        selectedTokenId = null;
    }

    function createDropElement(questionIndex, blankIndex, totalBlanks) {
        const drop = document.createElement('span');
        drop.className = 'drag-quiz__drop';
        drop.dataset.questionIndex = String(questionIndex);
        drop.dataset.blankIndex = String(blankIndex);
        drop.textContent = '_____';
        drop.tabIndex = 0;
        const suffix = totalBlanks > 1 ? ` (${blankIndex + 1})` : '';
        drop.setAttribute('aria-label', `Drop zone ${questionIndex + 1}${suffix}`);
        drop.addEventListener('dragover', (event) => {
            event.preventDefault();
            drop.classList.add('is-hover');
        });
        drop.addEventListener('dragleave', () => {
            drop.classList.remove('is-hover');
        });
        drop.addEventListener('drop', (event) => {
            event.preventDefault();
            drop.classList.remove('is-hover');
            const id = event.dataTransfer.getData('text/plain');
            placeTokenInDrop(id, drop);
        });
        drop.addEventListener('click', () => {
            if (selectedTokenId) {
                placeTokenInDrop(selectedTokenId, drop);
            }
        });

        return drop;
    }

    function renderTasks() {
        tasksEl.innerHTML = '';

        questions.forEach((question, idx) => {
            const row = document.createElement('div');
            row.className = 'drag-quiz__row';

            const num = document.createElement('div');
            num.className = 'drag-quiz__num';
            num.textContent = `${idx + 1}.`;
            row.appendChild(num);

            const sentence = document.createElement('div');
            sentence.className = 'drag-quiz__sentence';

            question.segments.forEach((segment) => {
                if (segment.type === 'text') {
                    if (!segment.value) {
                        return;
                    }
                    const span = document.createElement('span');
                    span.textContent = segment.value;
                    sentence.appendChild(span);
                    return;
                }

                if (segment.type === 'blank') {
                    const drop = createDropElement(idx, segment.blankIndex, question.blanks.length);
                    sentence.appendChild(drop);
                }
            });

            if (question.tail) {
                const tailSpan = document.createElement('span');
                tailSpan.className = 'drag-quiz__tail';
                tailSpan.textContent = question.tail;
                sentence.appendChild(tailSpan);
            }

            row.appendChild(sentence);
            tasksEl.appendChild(row);
        });
    }

    function returnTokenToBank(tokenEl) {
        const drop = tokenEl.closest('.drag-quiz__drop');
        if (!drop) {
            return;
        }

        const id = tokenEl.dataset.id || tokenEl.getAttribute('data-id');
        const word = tokenEl.dataset.word || tokenEl.textContent.trim();

        drop.textContent = '_____';
        drop.classList.remove('is-filled', 'is-correct', 'is-wrong');
        drop.removeAttribute('data-token-id');

        if (id && word) {
            addTokenToBank({ id, word });
        }

        selectedTokenId = null;
    }

    function placeTokenInDrop(tokenId, drop) {
        if (!tokenId || !drop) {
            return false;
        }

        const selector = `.drag-quiz__token[data-id="${escapeSelector(tokenId)}"]`;
        const token = bankEl.querySelector(selector);
        if (!token) {
            return false;
        }

        const existing = drop.querySelector('.drag-quiz__token');
        if (existing) {
            returnTokenToBank(existing);
        }

        bankEl.querySelectorAll('.drag-quiz__token').forEach((node) => node.classList.remove('is-selected'));
        selectedTokenId = null;

        if (supportsTouch && touchHoverDrop) {
            touchHoverDrop.classList.remove('is-hover');
            touchHoverDrop = null;
            touchStartPoint = null;
        }

        const clone = token.cloneNode(true);
        clone.classList.remove('is-selected');
        clone.classList.add('is-fixed');
        clone.removeAttribute('draggable');
        clone.dataset.word = token.dataset.word;
        clone.dataset.id = token.dataset.id;

        const remove = document.createElement('span');
        remove.className = 'drag-quiz__remove';
        remove.setAttribute('role', 'button');
        remove.setAttribute('aria-label', 'Видалити слово');
        remove.textContent = '×';
        remove.addEventListener('click', (event) => {
            event.stopPropagation();
            returnTokenToBank(clone);
        });

        clone.addEventListener('click', (event) => {
            event.stopPropagation();
            returnTokenToBank(clone);
        });

        drop.innerHTML = '';
        drop.appendChild(clone);
        drop.appendChild(remove);
        drop.classList.add('is-filled');
        drop.classList.remove('is-correct', 'is-wrong');
        drop.dataset.tokenId = tokenId;

        token.remove();
        return true;
    }

    function checkAnswers() {
        let score = 0;
        const drops = tasksEl.querySelectorAll('.drag-quiz__drop');

        drops.forEach((drop) => {
            drop.classList.remove('is-correct', 'is-wrong');

            const questionIndex = Number.parseInt(drop.dataset.questionIndex ?? '-1', 10);
            const blankIndex = Number.parseInt(drop.dataset.blankIndex ?? '-1', 10);
            const question = Number.isInteger(questionIndex) ? questions[questionIndex] : null;
            const blank = question && Number.isInteger(blankIndex) ? question.blanks[blankIndex] : null;
            const token = drop.querySelector('.drag-quiz__token');

            if (!question || !blank || !token) {
                return;
            }

            const value = normalize(token.dataset.word || token.textContent);
            if (value && value === blank.normalized) {
                drop.classList.add('is-correct');
                score += 1;
            } else {
                drop.classList.add('is-wrong');
            }
        });

        updateScoreLabel(score);
    }

    function showAnswers() {
        tasksEl
            .querySelectorAll('.drag-quiz__drop .drag-quiz__token')
            .forEach((token) => returnTokenToBank(token));

        tasksEl.querySelectorAll('.drag-quiz__drop').forEach((drop) => {
            drop.classList.remove('is-correct', 'is-wrong');
        });

        questions.forEach((question, questionIndex) => {
            question.blanks.forEach((blank, blankIndex) => {
                const dropSelector = `.drag-quiz__drop[data-question-index="${questionIndex}"][data-blank-index="${blankIndex}"]`;
                const drop = tasksEl.querySelector(dropSelector);
                if (!drop) {
                    return;
                }

                let token = Array.from(bankEl.querySelectorAll('.drag-quiz__token')).find((node) => {
                    const value = normalize(node.dataset.word || node.textContent);
                    return value === blank.normalized;
                });

                if (!token) {
                    const fallbackId = `auto-${questionIndex}-${blankIndex}`;
                    addTokenToBank({ id: fallbackId, word: blank.answer });
                    token = bankEl.querySelector(`.drag-quiz__token[data-id="${escapeSelector(fallbackId)}"]`);
                }

                if (token) {
                    placeTokenInDrop(token.dataset.id, drop);
                }
            });
        });

        checkAnswers();
    }

    function retry() {
        tasksEl.querySelectorAll('.drag-quiz__drop').forEach((drop) => {
            drop.textContent = '_____';
            drop.classList.remove('is-filled', 'is-correct', 'is-wrong');
            drop.removeAttribute('data-token-id');
        });

        renderBank(true);
        updateScoreLabel(0);
    }

    renderTasks();
    renderBank(true);
    updateScoreLabel(0);

    checkBtn?.addEventListener('click', checkAnswers);
    retryBtn?.addEventListener('click', retry);
    showBtn?.addEventListener('click', showAnswers);

    tasksEl.addEventListener('keydown', (event) => {
        if (event.key === 'Enter') {
            const drop = event.target.closest('.drag-quiz__drop');
            if (drop && selectedTokenId) {
                event.preventDefault();
                placeTokenInDrop(selectedTokenId, drop);
            }
        }
    });
})();
</script>
@endsection
