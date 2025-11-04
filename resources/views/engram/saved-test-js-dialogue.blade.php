@extends('layouts.engram')

@section('title', $test->name)

@section('content')
<div class="mx-auto w-full max-w-3xl px-4 py-8 text-slate-800" id="dialogue-app">
    <header class="mb-6 space-y-2">
        <h1 class="text-2xl font-bold text-slate-900 sm:text-3xl">{{ $test->name }}</h1>
        <p class="text-sm text-slate-600">Пройди діалог, заповнюючи пропуски. Натискай <strong>Enter</strong> або кнопку «Перевірити», щоб перейти до наступної репліки.</p>
    </header>

    @include('components.test-mode-nav')
    @include('components.word-search')
    @include('components.saved-test-progress')

    <div id="dialogue-chat" class="flex min-h-[420px] flex-col rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
        <div id="dialogue-container" class="flex flex-col space-y-4"></div>
        <div id="dialogue-empty" class="hidden rounded-xl border border-amber-200 bg-amber-50 p-4 text-center text-sm text-amber-700">
            Для цього тесту поки немає діалогів з пропусками.
        </div>
        <div id="dialogue-complete" class="hidden text-center text-lg font-semibold text-emerald-600">
            ✅ Чудово! Ти завершив(-ла) діалог.
        </div>
    </div>

    <p id="dialogue-error" class="mt-3 text-sm font-medium text-rose-600"></p>

    <div class="mt-4 flex flex-wrap items-center gap-3">
        <button id="dialogue-check" type="button" class="rounded-xl bg-sky-500 px-5 py-2 text-sm font-semibold text-white shadow transition hover:bg-sky-600 disabled:cursor-not-allowed disabled:opacity-50">
            Перевірити
        </button>
    </div>

    @include('components.saved-test-js-restart-button')
</div>

<style>
@keyframes fadeInUp {
    0% { opacity: 0; transform: translateY(10px); }
    100% { opacity: 1; transform: translateY(0); }
}

#dialogue-chat {
    max-height: 70vh;
    overflow-y: auto;
    scroll-behavior: smooth;
}

.dialogue-row {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    animation: fadeInUp 0.4s ease forwards;
}

.dialogue-row.right {
    justify-content: flex-end;
}

.dialogue-bubble {
    max-width: 80%;
    border-radius: 20px;
    padding: 12px 16px;
    background: #eff6ff;
    color: #1d4ed8;
    border: 1px solid #bfdbfe;
    box-shadow: 0 1px 3px rgba(15, 23, 42, 0.1);
}

.dialogue-row.right .dialogue-bubble {
    background: #dcfce7;
    color: #047857;
    border-color: #bbf7d0;
}

.dialogue-speaker {
    font-weight: 700;
    margin-right: 6px;
}

.dialogue-text {
    display: inline;
    line-height: 1.6;
}

.dialogue-input {
    display: inline-block;
    width: auto;
    min-width: 96px;
    padding: 4px 8px;
    border-radius: 10px;
    border: 1px solid #cbd5f5;
    background: #fff;
    margin: 0 4px;
    font-size: 0.95rem;
    line-height: 1.4;
}

.dialogue-row.right .dialogue-input {
    border-color: #bbf7d0;
}

.dialogue-input:focus {
    outline: none;
    box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.35);
}

.dialogue-input.incorrect {
    border-color: #f87171 !important;
    background: #fee2e2 !important;
    box-shadow: 0 0 0 2px rgba(248, 113, 113, 0.35);
}

.dialogue-input.correct {
    border-color: #34d399 !important;
    background: #dcfce7 !important;
    box-shadow: none;
}

.dialogue-meta {
    margin-top: 8px;
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    font-size: 0.75rem;
    color: #64748b;
}

.dialogue-meta span {
    background: #e2e8f0;
    border-radius: 999px;
    padding: 2px 8px;
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
const chatEl = document.getElementById('dialogue-chat');
const containerEl = document.getElementById('dialogue-container');
const emptyEl = document.getElementById('dialogue-empty');
const completeEl = document.getElementById('dialogue-complete');
const checkBtn = document.getElementById('dialogue-check');
const errorEl = document.getElementById('dialogue-error');
const progressLabel = document.getElementById('progress-label');
const scoreLabel = document.getElementById('score-label');
const progressBar = document.getElementById('progress-bar');
const restartBtn = document.getElementById('restart-test');

let baseItems = [];
let totalBlanks = 0;

const state = {
    items: [],
    visibleCount: 0,
    currentIndex: null,
    completed: false,
};

function normalizeAnswer(value) {
    return String(value || '')
        .trim()
        .replace(/\s+/g, ' ')
        .toLowerCase();
}

function splitSpeaker(raw) {
    if (typeof raw !== 'string') {
        return { speaker: '', message: '' };
    }

    const trimmed = raw.trim();
    if (!trimmed) {
        return { speaker: '', message: '' };
    }

    const colonIndex = trimmed.indexOf(':');
    if (colonIndex !== -1 && colonIndex <= 30) {
        const potentialSpeaker = trimmed.slice(0, colonIndex).trim();
        const rest = trimmed.slice(colonIndex + 1).trim();
        if (potentialSpeaker.length > 0 && /[A-Za-zА-Яа-яІіЇїЄє'\-\s]+/.test(potentialSpeaker)) {
            return { speaker: potentialSpeaker, message: rest };
        }
    }

    return { speaker: '', message: trimmed };
}

function buildBaseItems(questions) {
    const items = questions.map((q) => {
        const { speaker, message } = splitSpeaker(q?.question ?? '');
        const content = message;
        const regex = /\{(a\d+)\}/gi;
        let match;
        const markers = [];
        const parts = [];
        let lastIndex = 0;
        const answerMap = q?.answer_map ?? {};
        const answersList = Array.isArray(q?.answers) ? q.answers : [];
        const verbHints = q?.verb_hints ?? {};
        const fallbackHint = q?.verb_hint ?? '';

        while ((match = regex.exec(content)) !== null) {
            const marker = match[1].toLowerCase();
            markers.push(marker);
            parts.push(content.slice(lastIndex, match.index));
            lastIndex = match.index + match[0].length;
        }
        parts.push(content.slice(lastIndex));

        const answers = markers.map((marker, idx) => {
            if (answerMap && typeof answerMap === 'object') {
                const direct = answerMap[marker];
                if (direct !== undefined) {
                    return direct;
                }
                const upper = answerMap[marker.toUpperCase()];
                if (upper !== undefined) {
                    return upper;
                }
            }
            return answersList[idx] ?? answersList[0] ?? '';
        });

        const hints = markers.map((marker) => {
            if (verbHints && typeof verbHints === 'object') {
                const direct = verbHints[marker];
                if (direct) {
                    return direct;
                }
                const upper = verbHints[marker.toUpperCase()];
                if (upper) {
                    return upper;
                }
            }
            return fallbackHint || '';
        });

        return {
            id: q?.id ?? `q-${Math.random().toString(36).slice(2)}`,
            speaker: speaker || '–',
            contentParts: parts,
            markers,
            answers,
            hints,
            hasBlanks: markers.length > 0,
            meta: {
                level: q?.level ?? '',
                tense: q?.tense ?? '',
            },
        };
    });

    const speakerSides = {};
    let nextSide = 'left';
    items.forEach((item) => {
        const key = (item.speaker || '').toLowerCase();
        if (!speakerSides[key]) {
            speakerSides[key] = nextSide;
            nextSide = nextSide === 'left' ? 'right' : 'left';
        }
        item.side = speakerSides[key] || 'left';
    });

    return items;
}

function createInitialState() {
    state.items = baseItems.map((item) => ({
        id: item.id,
        inputs: Array(item.markers.length).fill(''),
        visible: false,
        done: !item.hasBlanks,
        status: item.hasBlanks ? 'pending' : 'auto',
    }));
    state.visibleCount = 0;
    state.currentIndex = null;
    state.completed = baseItems.every((item) => !item.hasBlanks);
}

function computeVisibleCount() {
    let count = 0;
    for (let i = 0; i < state.items.length; i++) {
        if (state.items[i].visible) {
            count = i + 1;
        } else {
            break;
        }
    }
    return count;
}

function computeCurrentIndex() {
    for (let i = 0; i < state.items.length; i++) {
        if (!state.items[i].visible) {
            break;
        }
        if (baseItems[i].hasBlanks && !state.items[i].done) {
            return i;
        }
    }
    return null;
}

function saveState(immediate = false) {
    persistState({
        items: state.items,
        visibleCount: state.visibleCount,
        currentIndex: state.currentIndex,
        completed: state.completed,
    }, immediate);
}

function updateProgress() {
    const answered = state.items.reduce((acc, item, idx) => {
        if (!baseItems[idx]?.hasBlanks) {
            return acc;
        }
        return acc + (item.done ? baseItems[idx].markers.length : 0);
    }, 0);

    const correct = state.items.reduce((acc, item, idx) => {
        if (!baseItems[idx]?.hasBlanks) {
            return acc;
        }
        return acc + (item.status === 'correct' ? baseItems[idx].markers.length : 0);
    }, 0);

    const total = totalBlanks;
    if (progressLabel) {
        progressLabel.textContent = `${answered} / ${total}`;
    }
    if (scoreLabel) {
        scoreLabel.textContent = `Точність: ${pct(correct, total)}%`;
    }
    if (progressBar) {
        progressBar.style.width = `${pct(answered, total)}%`;
    }
}

function focusCurrentInput() {
    if (state.currentIndex === null) {
        return;
    }
    const card = containerEl.querySelector(`[data-index="${state.currentIndex}"]`);
    if (!card) {
        return;
    }
    const input = card.querySelector('input[data-blank-index]:not([disabled])');
    if (input) {
        input.focus();
    }
}

function scrollChatToBottom() {
    if (!chatEl) {
        return;
    }
    chatEl.scrollTop = chatEl.scrollHeight;
}

function renderVisibleItems() {
    containerEl.innerHTML = '';
    baseItems.forEach((item, idx) => {
        if (!state.items[idx]?.visible) {
            return;
        }
        const row = document.createElement('div');
        row.className = `dialogue-row ${item.side === 'right' ? 'right' : 'left'}`;
        row.dataset.index = idx;

        const bubble = document.createElement('div');
        bubble.className = 'dialogue-bubble';

        const speakerEl = document.createElement('span');
        speakerEl.className = 'dialogue-speaker';
        speakerEl.textContent = `${item.speaker || '–'}:`;
        bubble.appendChild(speakerEl);

        const textWrap = document.createElement('span');
        textWrap.className = 'dialogue-text';

        item.contentParts.forEach((part, partIdx) => {
            if (part) {
                textWrap.appendChild(document.createTextNode(part));
            }
            if (partIdx < item.markers.length) {
                const input = document.createElement('input');
                input.type = 'text';
                input.className = 'dialogue-input';
                input.setAttribute('data-item-index', idx);
                input.setAttribute('data-blank-index', partIdx);
                input.value = state.items[idx].inputs[partIdx] ?? '';
                input.disabled = state.items[idx].done;
                if (state.items[idx].done) {
                    input.classList.add('correct');
                }
                const hint = item.hints[partIdx];
                if (hint) {
                    input.placeholder = hint;
                }
                input.addEventListener('input', () => {
                    const normalized = input.value.replace(/\s+/g, ' ').trim();
                    if (normalized !== input.value) {
                        input.value = normalized;
                    }
                    state.items[idx].inputs[partIdx] = input.value;
                    input.classList.remove('incorrect');
                    saveState();
                });
                textWrap.appendChild(input);
            }
        });

        bubble.appendChild(textWrap);

        if ((item.meta.level && item.meta.level !== '') || (item.meta.tense && item.meta.tense !== '')) {
            const metaEl = document.createElement('div');
            metaEl.className = 'dialogue-meta';
            if (item.meta.level) {
                const badge = document.createElement('span');
                badge.textContent = item.meta.level;
                metaEl.appendChild(badge);
            }
            if (item.meta.tense) {
                const badge = document.createElement('span');
                badge.textContent = item.meta.tense;
                metaEl.appendChild(badge);
            }
            bubble.appendChild(metaEl);
        }

        row.appendChild(bubble);
        containerEl.appendChild(row);
    });

    if (state.completed) {
        completeEl.classList.remove('hidden');
    } else {
        completeEl.classList.add('hidden');
    }

    updateControls();
}

function updateControls() {
    if (!checkBtn) {
        return;
    }
    checkBtn.disabled = state.currentIndex === null;
}

function revealNextSequence() {
    let progressed = false;
    while (state.visibleCount < state.items.length) {
        const idx = state.visibleCount;
        state.items[idx].visible = true;
        state.visibleCount++;
        progressed = true;
        if (baseItems[idx].hasBlanks) {
            if (!state.items[idx].done) {
                state.currentIndex = idx;
                break;
            }
        }
    }

    if (state.visibleCount >= state.items.length) {
        if (baseItems.every((item, idx) => !item.hasBlanks || state.items[idx].done)) {
            state.completed = true;
            state.currentIndex = null;
        }
    } else {
        if (state.currentIndex === null) {
            state.currentIndex = computeCurrentIndex();
        }
    }

    if (progressed) {
        if (errorEl) {
            errorEl.textContent = '';
        }
        renderVisibleItems();
        focusCurrentInput();
        scrollChatToBottom();
        updateProgress();
        saveState();
    } else {
        renderVisibleItems();
        if (state.completed && errorEl) {
            errorEl.textContent = '';
        }
        updateProgress();
        saveState();
        updateControls();
    }
}

function handleCheck() {
    if (state.currentIndex === null) {
        return;
    }
    const idx = state.currentIndex;
    const base = baseItems[idx];
    const dyn = state.items[idx];
    if (!base || !dyn) {
        return;
    }

    const card = containerEl.querySelector(`[data-index="${idx}"]`);
    const inputs = card ? card.querySelectorAll('input[data-blank-index]') : [];
    if (!inputs || inputs.length === 0) {
        return;
    }

    let allFilled = true;
    let allCorrect = true;

    inputs.forEach((input, blankIdx) => {
        const raw = input.value.replace(/\s+/g, ' ').trim();
        input.value = raw;
        dyn.inputs[blankIdx] = raw;
        input.classList.remove('incorrect');
        if (!raw) {
            allFilled = false;
        }
        const expected = normalizeAnswer(base.answers[blankIdx]);
        const valueNorm = normalizeAnswer(raw);
        if (!raw || valueNorm !== expected) {
            allCorrect = false;
            input.classList.add('incorrect');
        } else {
            input.classList.remove('incorrect');
        }
    });

    if (!allFilled) {
        if (errorEl) {
            errorEl.textContent = 'Заповни всі поля перед перевіркою.';
        }
        saveState();
        return;
    }

    if (!allCorrect) {
        if (errorEl) {
            errorEl.textContent = '❌ Невірно. Спробуй ще раз!';
        }
        saveState();
        return;
    }

    if (errorEl) {
        errorEl.textContent = '';
    }

    dyn.done = true;
    dyn.status = 'correct';
    inputs.forEach((input) => {
        input.disabled = true;
        input.classList.remove('incorrect');
        input.classList.add('correct');
    });

    saveState();
    updateProgress();

    state.currentIndex = null;

    setTimeout(() => {
        revealNextSequence();
        if (state.currentIndex === null && !state.completed) {
            state.currentIndex = computeCurrentIndex();
            updateControls();
        }
    }, 400);
}

async function init(forceFresh = false) {
    const baseQuestions = await loadQuestions(forceFresh);
    QUESTIONS = Array.isArray(baseQuestions) ? baseQuestions : [];

    baseItems = buildBaseItems(QUESTIONS);
    totalBlanks = baseItems.reduce((acc, item) => acc + item.markers.length, 0);

    let restored = false;
    if (!forceFresh) {
        const saved = getSavedState();
        if (saved && Array.isArray(saved.items)) {
            const savedMap = new Map();
            saved.items.forEach((savedItem) => {
                if (savedItem && savedItem.id !== undefined) {
                    savedMap.set(savedItem.id, savedItem);
                }
            });

            state.items = baseItems.map((item) => {
                const savedItem = savedMap.get(item.id);
                const inputs = Array.isArray(savedItem?.inputs)
                    ? savedItem.inputs.slice(0, item.markers.length).map((val) => (typeof val === 'string' ? val : ''))
                    : Array(item.markers.length).fill('');
                return {
                    id: item.id,
                    inputs,
                    visible: false,
                    done: item.hasBlanks ? Boolean(savedItem?.done) : true,
                    status: savedItem?.status === 'correct' ? 'correct' : (item.hasBlanks ? 'pending' : 'auto'),
                };
            });

            state.visibleCount = Number.isFinite(saved.visibleCount)
                ? Math.min(Math.max(saved.visibleCount, 0), baseItems.length)
                : computeVisibleCount();
            state.items.forEach((entry, idx) => {
                entry.visible = idx < state.visibleCount;
            });
            state.visibleCount = computeVisibleCount();
            state.currentIndex = computeCurrentIndex();
            state.completed = Boolean(saved.completed) && baseItems.every((item, idx) => !item.hasBlanks || state.items[idx].done);
            if (state.completed) {
                state.items.forEach((entry) => {
                    entry.visible = true;
                });
                state.visibleCount = state.items.length;
                state.currentIndex = null;
            }
            restored = savedMap.size > 0 || Boolean(saved.visibleCount) || Boolean(saved.completed);
        }
    }

    if (!restored) {
        createInitialState();
    }

    if (baseItems.length === 0 || totalBlanks === 0) {
        emptyEl.classList.remove('hidden');
        containerEl.innerHTML = '';
        completeEl.classList.add('hidden');
        if (checkBtn) {
            checkBtn.disabled = true;
        }
        updateProgress();
        saveState(true);
        return;
    }

    emptyEl.classList.add('hidden');

    if (!restored || state.visibleCount === 0) {
        revealNextSequence();
    } else {
        renderVisibleItems();
        focusCurrentInput();
        scrollChatToBottom();
        updateProgress();
        saveState(true);
    }
}

if (checkBtn) {
    checkBtn.addEventListener('click', handleCheck);
}

document.addEventListener('keydown', (event) => {
    if (event.key === 'Enter' && !event.shiftKey) {
        const active = document.activeElement;
        if (active && active.tagName === 'TEXTAREA') {
            return;
        }
        if (state.currentIndex !== null) {
            event.preventDefault();
            handleCheck();
        }
    }
});

if (restartBtn) {
    restartBtn.addEventListener('click', () => {
        restartJsTest(init, { button: restartBtn });
    });
}

init();
</script>
@endsection
