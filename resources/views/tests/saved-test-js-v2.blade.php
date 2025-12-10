@extends('layouts.engram')

@section('title', $test->name)

@section('content')
<div class="min-h-screen bg-gradient-to-br from-indigo-50 via-white to-purple-50" id="quiz-app">
    <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8 py-8 sm:py-12">
        <!-- Header Section with Modern Design -->
        <header class="mb-8 sm:mb-12">
            <div class="text-center space-y-4">
                <div class="inline-flex items-center px-4 py-2 rounded-full bg-gradient-to-r from-indigo-100 to-purple-100 text-indigo-700 text-sm font-semibold mb-4">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Interactive Test
                </div>
                <h1 class="text-3xl sm:text-4xl lg:text-5xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">
                    {{ $test->name }}
                </h1>
                <p class="text-base sm:text-lg text-gray-600 max-w-2xl mx-auto">
                    Answer questions instantly using keyboard shortcuts (1-4) or click on the options below
                </p>
            </div>
        </header>

        <!-- Navigation Tabs -->
        @include('components.test-mode-nav')

        <!-- Word Search Component -->
        @include('components.word-search')

        <!-- Progress Tracker with Modern Design -->
        <div class="mb-8 bg-white rounded-3xl shadow-lg border border-gray-100 p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-indigo-500 to-purple-500 flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                        </svg>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500 font-medium">Progress</div>
                        <div id="progress-label" class="text-xl font-bold text-gray-900">0 / 0</div>
                    </div>
                </div>
                <div class="text-right">
                    <div class="text-sm text-gray-500 font-medium">Accuracy</div>
                    <div id="score-label" class="text-xl font-bold bg-gradient-to-r from-emerald-600 to-teal-600 bg-clip-text text-transparent">0%</div>
                </div>
            </div>
            <div class="relative w-full h-3 bg-gray-100 rounded-full overflow-hidden">
                <div id="progress-bar" class="absolute top-0 left-0 h-full bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 rounded-full transition-all duration-500 ease-out" style="width:0%"></div>
            </div>
        </div>

        <!-- Restart Button -->
        @include('components.saved-test-js-restart-button')

        <!-- Questions Container -->
        <div id="questions" class="space-y-6"></div>

        <!-- Summary Section with Celebration Design -->
        <div id="summary" class="mt-12 hidden">
            <div class="bg-gradient-to-br from-emerald-50 to-teal-50 rounded-3xl shadow-xl border-2 border-emerald-200 p-8 text-center">
                <div class="w-20 h-20 mx-auto mb-6 rounded-full bg-gradient-to-br from-emerald-400 to-teal-500 flex items-center justify-center">
                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h2 class="text-3xl font-bold text-gray-900 mb-3">Test Complete! 🎉</h2>
                <p id="summary-text" class="text-xl text-gray-700 mb-8"></p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <button id="retry" class="group px-8 py-4 rounded-2xl bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-semibold shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200">
                        <span class="flex items-center justify-center">
                            <svg class="w-5 h-5 mr-2 group-hover:rotate-180 transition-transform duration-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                            Try Again
                        </span>
                    </button>
                    <button id="show-wrong" class="px-8 py-4 rounded-2xl border-2 border-gray-300 bg-white text-gray-700 font-semibold hover:border-gray-400 hover:bg-gray-50 transition-all duration-200">
                        <span class="flex items-center justify-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                            Review Mistakes
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
window.__INITIAL_JS_TEST_QUESTIONS__ = @json($questionData);
let QUESTIONS = Array.isArray(window.__INITIAL_JS_TEST_QUESTIONS__)
    ? window.__INITIAL_JS_TEST_QUESTIONS__
    : [];
const CSRF_TOKEN = '{{ csrf_token() }}';
const EXPLAIN_URL = '{{ route('question.explain') }}';
const TEST_SLUG = @json($test->slug);
</script>
@include('components.saved-test-js-persistence', ['mode' => $jsStateMode, 'savedState' => $savedState])
@include('components.saved-test-js-helpers')
<script>
const state = {
  items: [],
  correct: 0,
  answered: 0,
  activeCardIdx: 0,
};

let globalEventsHooked = false;

function ensureGlobalEvents() {
  if (globalEventsHooked) return;
  hookGlobalEvents();
  globalEventsHooked = true;
}

async function init(forceFresh = false) {
  const baseQuestions = await loadQuestions(forceFresh);
  QUESTIONS = Array.isArray(baseQuestions) ? baseQuestions : [];

  let restored = false;
  if (!forceFresh) {
    const saved = getSavedState();
    if (saved && Array.isArray(saved.items)) {
      state.items = saved.items;
      state.correct = Number.isFinite(saved.correct) ? saved.correct : 0;
      state.answered = Number.isFinite(saved.answered) ? saved.answered : 0;
      state.activeCardIdx = Number.isFinite(saved.activeCardIdx) ? saved.activeCardIdx : 0;
      restored = true;
      state.items.forEach((item) => {
        if (typeof item.explanation !== 'string') item.explanation = '';
        if (!item.explanationsCache || typeof item.explanationsCache !== 'object') item.explanationsCache = {};
        if (!('pendingExplanationKey' in item)) item.pendingExplanationKey = null;
      });
    }
  }

  if (!restored) {
    state.items = QUESTIONS.map((q) => {
      const opts = [...q.options];
      shuffle(opts);
      return {
        ...q,
        options: opts,
        chosen: Array(q.answers.length).fill(null),
        slot: 0,
        done: false,
        wrongAttempt: false,
        lastWrong: null,
        feedback: '',
        attempts: 0,
        explanation: '',
        explanationsCache: {},
        pendingExplanationKey: null,
      };
    });
    state.correct = 0;
    state.answered = 0;
    state.activeCardIdx = 0;
  }

  ensureGlobalEvents();
  renderQuestions();
  updateProgress();
  checkAllDone();
  persistState(state, true);
}

function renderQuestions(showOnlyWrong = false) {
  const wrap = document.getElementById('questions');
  wrap.innerHTML = '';

  state.items.forEach((q, idx) => {
    if (showOnlyWrong && (!q.done || !q.wrongAttempt)) return;

    const card = document.createElement('article');
    card.className = 'group bg-white rounded-3xl shadow-md hover:shadow-xl border-2 border-gray-100 hover:border-indigo-200 p-6 sm:p-8 transition-all duration-300 focus-within:ring-4 ring-indigo-100 outline-none transform hover:-translate-y-1';
    card.tabIndex = 0;
    card.dataset.idx = idx;

    const sentence = renderSentence(q);

    card.innerHTML = `
      <div class="flex items-start justify-between gap-4 mb-6">
        <div class="flex-1">
          <div class="flex items-center gap-3 mb-3">
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-gradient-to-r from-blue-100 to-indigo-100 text-indigo-700">
              ${q.level || 'N/A'}
            </span>
            <span class="text-sm text-gray-500 font-medium">${q.tense || 'Grammar'}</span>
          </div>
          <div class="text-lg sm:text-xl leading-relaxed text-gray-900 font-medium">${sentence}</div>
        </div>
        <div class="flex flex-col items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-br from-indigo-50 to-purple-50 border border-indigo-100 shrink-0">
          <div class="text-xs text-gray-500 font-medium">Q</div>
          <div class="text-lg font-bold text-indigo-600">${idx + 1}</div>
        </div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3" role="group" aria-label="Answer options">
        ${q.options.map((opt, i) => renderOptionButton(q, idx, opt, i)).join('')}
      </div>

      <div class="mt-6" id="feedback-${idx}">${renderFeedback(q)}</div>
    `;

    card.addEventListener('click', (e) => {
      const btn = e.target.closest('button[data-opt]');
      if (!btn) return;
      onChoose(idx, btn.dataset.opt);
    });

    card.addEventListener('focusin', () => {
      if (state.activeCardIdx !== idx) {
        state.activeCardIdx = idx;
        persistState(state);
      }
    });

    wrap.appendChild(card);
  });

  const allDone = state.items.every(it => it.done);
  document.getElementById('summary').classList.toggle('hidden', !allDone);

  if (allDone) {
    const summaryText = document.getElementById('summary-text');
    summaryText.textContent = `You got ${state.correct} out of ${state.items.length} correct (${pct(state.correct, state.items.length)}%)!`;
    const retryButton = document.getElementById('retry');
    if (retryButton) {
      retryButton.onclick = () => restartJsTest(init, { button: retryButton });
    }
    document.getElementById('show-wrong').onclick = () => renderQuestions(true);
  }
}

function renderOptionButton(q, idx, opt, i) {
  const base = 'group relative w-full text-left px-5 py-4 rounded-2xl border-2 transition-all duration-200 font-medium text-base';
  let cls = 'border-gray-200 bg-white hover:border-indigo-300 hover:bg-indigo-50 hover:shadow-md transform hover:-translate-y-0.5';
  let iconColor = 'text-gray-400 group-hover:text-indigo-500';
  
  if (q.done) {
    cls = 'border-gray-200 bg-gray-50 text-gray-500 cursor-not-allowed';
    iconColor = 'text-gray-300';
  } else if (q.lastWrong === opt) {
    cls = 'border-red-300 bg-red-50 text-red-700 shadow-md';
    iconColor = 'text-red-500';
  }
  
  const hotkey = i + 1;
  return `
    <button type="button" class="${base} ${cls}" data-opt="${html(opt)}" title="Press ${hotkey}" ${q.done ? 'disabled' : ''}>
      <div class="flex items-center gap-3">
        <span class="flex-shrink-0 flex items-center justify-center w-8 h-8 rounded-xl border-2 font-bold text-sm ${iconColor} transition-colors">
          ${hotkey}
        </span>
        <span class="flex-1">${opt}</span>
      </div>
    </button>
  `;
}

function renderFeedback(q) {
  if (q.feedback === 'correct') {
    let htmlStr = '<div class="flex items-start gap-3 p-4 rounded-2xl bg-gradient-to-r from-emerald-50 to-teal-50 border-2 border-emerald-200"><div class="flex-shrink-0 w-6 h-6 rounded-full bg-emerald-500 flex items-center justify-center"><svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg></div><div class="flex-1"><div class="font-semibold text-emerald-800">Correct!</div></div></div>';
    if (q.explanation) {
      htmlStr += `<div class="mt-3 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-sm text-emerald-800 whitespace-pre-line leading-relaxed">${html(q.explanation)}</div>`;
    }
    return htmlStr;
  }
  if (q.feedback) {
    let htmlStr = `<div class="flex items-start gap-3 p-4 rounded-2xl bg-gradient-to-r from-red-50 to-rose-50 border-2 border-red-200"><div class="flex-shrink-0 w-6 h-6 rounded-full bg-red-500 flex items-center justify-center"><svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></div><div class="flex-1"><div class="font-semibold text-red-800">${html(q.feedback)}</div></div></div>`;
    if (q.explanation) {
      htmlStr += `<div class="mt-3 p-4 rounded-2xl bg-red-50 border border-red-200 text-sm text-red-800 whitespace-pre-line leading-relaxed">${html(q.explanation)}</div>`;
    }
    return htmlStr;
  }
  if (q.explanation) {
    return `<div class="p-4 rounded-2xl bg-gray-50 border border-gray-200 text-sm text-gray-700 whitespace-pre-line leading-relaxed">${html(q.explanation)}</div>`;
  }
  return '';
}

function onChoose(idx, opt) {
  const item = state.items[idx];
  if (item.done) return;

  const slotIndex = item.slot;
  const expected = item.answers[slotIndex];
  if (expected === undefined) return;

  if (!item.explanationsCache) {
    item.explanationsCache = {};
  }

  const key = buildExplanationKey(opt, expected);
  item.pendingExplanationKey = key;
  if (Object.prototype.hasOwnProperty.call(item.explanationsCache, key)) {
    item.explanation = item.explanationsCache[key];
  } else {
    item.explanation = '';
  }

  const explanationPromise = ensureExplanation(item, idx, opt, expected, key, slotIndex);

  if (opt === expected) {
    item.chosen[slotIndex] = opt;
    item.slot += 1;
    item.lastWrong = null;
    item.feedback = 'correct';
    item.attempts = 0;
    if (item.slot === item.answers.length) {
      item.done = true;
      state.answered += 1;
      if (!item.wrongAttempt) state.correct += 1;
    }
  } else {
    item.wrongAttempt = true;
    item.lastWrong = opt;
    item.attempts += 1;
    if (item.attempts >= 2) {
      const correct = expected;
      item.chosen[slotIndex] = correct;
      item.slot += 1;
      item.feedback = `Correct answer: ${correct}`;
      item.attempts = 0;
      if (item.slot === item.answers.length) {
        item.done = true;
        state.answered += 1;
      }
    } else {
      item.feedback = 'Incorrect, try again';
    }
  }

  const container = document.querySelector(`article[data-idx="${idx}"]`);
  if (container) {
    container.querySelector('.leading-relaxed').innerHTML = renderSentence(item);
    const group = container.querySelector('[role="group"]');
    group.innerHTML = item.options.map((optText, i) => renderOptionButton(item, idx, optText, i)).join('');
    container.querySelector(`#feedback-${idx}`).innerHTML = renderFeedback(item);
  }

  updateProgress();
  checkAllDone();
  persistState(state);

  explanationPromise
    .then((text) => {
      if (item.pendingExplanationKey !== key) {
        return;
      }
      item.explanation = text || '';
      const card = document.querySelector(`article[data-idx="${idx}"]`);
      if (card) {
        const feedbackEl = card.querySelector(`#feedback-${idx}`);
        if (feedbackEl) {
          feedbackEl.innerHTML = renderFeedback(item);
        }
      }
      persistState(state);
    })
    .catch((error) => {
      console.error(error);
    });
}

function updateProgress() {
  const label = document.getElementById('progress-label');
  label.textContent = `${state.answered} / ${state.items.length}`;

  const score = document.getElementById('score-label');
  const percent = state.answered ? pct(state.correct, state.items.length) : 0;
  score.textContent = `${percent}%`;

  const bar = document.getElementById('progress-bar');
  bar.style.width = `${(state.answered / state.items.length) * 100}%`;
}

function checkAllDone() {
  const allDone = state.items.every(it => it.done);
  if (allDone) {
    renderQuestions();
  }
}

function renderSentence(q) {
  let text = q.question;
  q.answers.forEach((ans, i) => {
    const replacement = q.chosen[i]
      ? `<mark class="px-2 py-1 rounded-lg bg-gradient-to-r from-amber-100 to-yellow-100 font-semibold">${html(q.chosen[i])}</mark>`
      : (i === q.slot
        ? `<mark class="px-2 py-1 rounded-lg bg-gradient-to-r from-amber-200 to-yellow-200 font-semibold">____</mark>`
        : '____');
    const regex = new RegExp(`\\{a${i + 1}\\}`);
    const marker = `a${i + 1}`;
    const hint = q.verb_hints && q.verb_hints[marker]
      ? ` <span class="verb-hint text-red-600 text-sm font-bold">( ${html(q.verb_hints[marker])} )</span>`
      : '';
    text = text.replace(regex, replacement + hint);
  });
  return text;
}

function buildExplanationKey(selected, expected) {
  const normSelected = (selected ?? '').toString().trim().toLowerCase();
  const normExpected = (expected ?? '').toString().trim().toLowerCase();

  return `${normSelected}|||${normExpected}`;
}

function ensureExplanation(item, idx, selected, expected, key, slotIndex) {
  if (!expected && !selected) {
    return Promise.resolve('');
  }

  if (!item.explanationsCache) {
    item.explanationsCache = {};
  }

  if (Object.prototype.hasOwnProperty.call(item.explanationsCache, key)) {
    return Promise.resolve(item.explanationsCache[key] || '');
  }

  const payload = {
    question_id: item.id,
    answer: selected,
    correct_answer: expected,
  };

  if (typeof slotIndex === 'number') {
    payload.marker = `a${slotIndex + 1}`;
  }

  if (TEST_SLUG) {
    payload.test_slug = TEST_SLUG;
  }

  return fetch(EXPLAIN_URL, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'X-CSRF-TOKEN': CSRF_TOKEN,
    },
    body: JSON.stringify(payload),
  })
    .then((response) => {
      if (!response.ok) {
        throw new Error('Failed to load explanation');
      }
      return response.json();
    })
    .then((data) => {
      const text = data && typeof data.explanation === 'string' ? data.explanation : '';
      item.explanationsCache[key] = text;

      return text;
    })
    .catch((error) => {
      console.error(error);
      item.explanationsCache[key] = '';

      return '';
    });
}

function hookGlobalEvents() {
  document.addEventListener('keydown', (e) => {
    const n = Number(e.key);
    if (!Number.isInteger(n) || n < 1 || n > 4) return;

    const idx = state.activeCardIdx ?? 0;
    const item = state.items[idx];
    if (!item || item.done) return;

    const opt = item.options[n - 1];
    if (!opt) return;

    onChoose(idx, opt);
  });
}

const restartButton = document.getElementById('restart-test');
if (restartButton) {
  restartButton.addEventListener('click', () => restartJsTest(init, { button: restartButton }));
}

init();
</script>
@endsection
