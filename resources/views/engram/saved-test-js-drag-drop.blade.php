@extends('layouts.engram')

@section('title', $test->name)

@section('content')
@php
    $questionTotal = count($questionData ?? []);
@endphp
<div class="drag-quiz mx-auto w-full max-w-[1100px]" id="drag-quiz">
    <header class="drag-quiz__header">
        <h1 class="drag-quiz__title">{{ $test->name }}</h1>
        <p class="drag-quiz__subtitle">Перетягни правильне <strong>question word</strong> у пропуск. Можна також натиснути на слово, а потім на пропуск — це зручно на мобільних пристроях.</p>
    </header>

    <div class="drag-quiz__grid">
        <div class="drag-quiz__card drag-quiz__left" id="drag-quiz-tasks"></div>

        <aside class="drag-quiz__card drag-quiz__right">
            <h3 class="drag-quiz__heading">Банк слів</h3>
            <div class="drag-quiz__legend">Перетягуй або натискай, щоб обрати слово.</div>
            <div id="drag-quiz-bank" class="drag-quiz__bank" aria-label="Word bank"></div>

            <div class="drag-quiz__controls">
                <button id="drag-quiz-check" class="drag-quiz__btn">Перевірити</button>
                <button id="drag-quiz-retry" class="drag-quiz__btn drag-quiz__btn--secondary">Спробувати ще</button>
                <button id="drag-quiz-show" class="drag-quiz__btn drag-quiz__btn--ghost">Показати відповіді</button>
                <div class="drag-quiz__score" id="drag-quiz-score">0 / {{ $questionTotal }}</div>
            </div>
            <p class="drag-quiz__hint">Підсвічення: <span class="drag-quiz__hint--correct">зелений</span> — вірно, <span class="drag-quiz__hint--wrong">червоний</span> — помилка.</p>
        </aside>
    </div>
</div>

<style>
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
    color: var(--quiz-text);
}
.drag-quiz__header {
    margin-bottom: 1.25rem;
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
        position: static;
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
}
.drag-quiz__right {
    padding: 14px;
    position: sticky;
    top: 16px;
    align-self: start;
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

    const questions = rawQuestions.map((item, index) => {
        const rawText = typeof item.question === 'string' ? item.question : '';
        const [line, tail = ''] = rawText.split(/\r?\n/, 2);
        const sentence = line || '';
        const parts = sentence.includes('_____') ? sentence.split('_____') : ['', sentence];
        const before = parts.shift() ?? '';
        const after = parts.length ? parts.join('_____') : '';
        const answers = Array.isArray(item.answers) ? item.answers : [];
        const answer = answers.length ? String(answers[0]) : String(item.answer || '');

        return {
            index,
            before,
            after,
            tail,
            answer,
            normalized: normalize(answer),
        };
    });

    const tasksEl = document.getElementById('drag-quiz-tasks');
    const bankEl = document.getElementById('drag-quiz-bank');
    const scoreEl = document.getElementById('drag-quiz-score');
    const checkBtn = document.getElementById('drag-quiz-check');
    const retryBtn = document.getElementById('drag-quiz-retry');
    const showBtn = document.getElementById('drag-quiz-show');

    const tokenCounters = {};
    const baseTokens = questions.flatMap((question, idx) => {
        const word = (question.answer || '').trim();
        if (!word) {
            return [];
        }
        tokenCounters[word] = (tokenCounters[word] || 0) + 1;
        return [{ word, id: `token-${idx}-${tokenCounters[word]}` }];
    });

    let selectedTokenId = null;

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

            if (question.before) {
                const beforeSpan = document.createElement('span');
                beforeSpan.textContent = question.before;
                sentence.appendChild(beforeSpan);
            }

            const drop = document.createElement('span');
            drop.className = 'drag-quiz__drop';
            drop.dataset.index = String(idx);
            drop.textContent = '_____';
            drop.tabIndex = 0;
            drop.setAttribute('aria-label', `Drop zone ${idx + 1}`);
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
            sentence.appendChild(drop);

            if (question.after) {
                const afterSpan = document.createElement('span');
                afterSpan.textContent = question.after;
                sentence.appendChild(afterSpan);
            }

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
        tokens.forEach(addTokenToBank);
        selectedTokenId = null;
    }

    function returnTokenToBank(tokenEl) {
        const drop = tokenEl.closest('.drag-quiz__drop');
        if (!drop) {
            return;
        }
        const id = tokenEl.dataset.id || tokenEl.getAttribute('data-id');
        const word = tokenEl.dataset.word || tokenEl.textContent.trim();
        if (id && word) {
            addTokenToBank({ id, word });
        }
        drop.textContent = '_____';
        drop.classList.remove('is-filled', 'is-correct', 'is-wrong');
        drop.removeAttribute('data-token-id');
        selectedTokenId = null;
    }

    function placeTokenInDrop(tokenId, drop) {
        if (!tokenId || !drop) {
            return;
        }
        const token = bankEl.querySelector(`.drag-quiz__token[data-id="${tokenId}"]`);
        if (!token) {
            return;
        }

        const existing = drop.querySelector('.drag-quiz__token');
        if (existing) {
            returnTokenToBank(existing);
        }

        bankEl.querySelectorAll('.drag-quiz__token').forEach((node) => node.classList.remove('is-selected'));
        selectedTokenId = null;

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
    }

    function checkAnswers() {
        let score = 0;
        const drops = tasksEl.querySelectorAll('.drag-quiz__drop');
        drops.forEach((drop) => {
            drop.classList.remove('is-correct', 'is-wrong');
            const idx = Number.parseInt(drop.dataset.index ?? '-1', 10);
            const question = Number.isInteger(idx) ? questions[idx] : null;
            const token = drop.querySelector('.drag-quiz__token');
            if (!question || !token) {
                return;
            }
            const value = normalize(token.dataset.word || token.textContent);
            if (value && value === question.normalized) {
                drop.classList.add('is-correct');
                score += 1;
            } else {
                drop.classList.add('is-wrong');
            }
        });
        if (scoreEl) {
            scoreEl.textContent = `${score} / ${questions.length}`;
        }
    }

    function showAnswers() {
        tasksEl.querySelectorAll('.drag-quiz__drop .drag-quiz__token').forEach((token) => returnTokenToBank(token));

        const drops = tasksEl.querySelectorAll('.drag-quiz__drop');
        drops.forEach((drop) => {
            drop.classList.remove('is-correct', 'is-wrong');
        });

        questions.forEach((question, idx) => {
            const drop = drops[idx];
            if (!drop) {
                return;
            }
            const token = Array.from(bankEl.querySelectorAll('.drag-quiz__token')).find((node) => {
                const value = node.dataset.word || node.textContent.trim();
                return value === question.answer;
            });
            if (token) {
                placeTokenInDrop(token.dataset.id, drop);
                drop.classList.add('is-correct');
            }
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
        if (scoreEl) {
            scoreEl.textContent = `0 / ${questions.length}`;
        }
    }

    renderTasks();
    renderBank(true);
    if (scoreEl) {
        scoreEl.textContent = `0 / ${questions.length}`;
    }

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
