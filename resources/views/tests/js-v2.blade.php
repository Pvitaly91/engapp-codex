@extends('layouts.engram')

@section('title', $test->name)

@section('content')
<div class="min-h-screen bg-gradient-to-br from-indigo-50 via-purple-50 to-pink-50">
    <!-- Hero Section -->
    <div class="bg-white shadow-sm border-b border-gray-200">
        <div class="mx-auto max-w-5xl px-6 py-8">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div class="flex-1 min-w-0">
                    <h1 class="text-3xl sm:text-4xl font-extrabold text-gray-900 tracking-tight">
                        {{ $test->name }}
                    </h1>
                    <p class="mt-2 text-sm text-gray-600 max-w-2xl">
                        Complete the test by selecting the correct answers. Use keyboard shortcuts 1-4 for quick navigation.
                    </p>
                </div>
                @include('components.saved-test-js-restart-button')
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="mx-auto max-w-5xl px-6 py-8" id="quiz-app">
        <!-- Progress Card -->
        <div class="mb-8 bg-white rounded-2xl shadow-lg p-6 border border-gray-200">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-lg font-bold text-gray-900">Your Progress</h2>
                    <p class="text-sm text-gray-600 mt-1" id="progress-label">0 / 0</p>
                </div>
                <div class="text-right">
                    <div class="text-2xl font-bold text-indigo-600" id="score-label">0%</div>
                    <p class="text-xs text-gray-500 mt-1">Accuracy</p>
                </div>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
                <div id="progress-bar" class="h-full bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 transition-all duration-500 ease-out" style="width: 0%"></div>
            </div>
        </div>

        @include('components.test-mode-nav')
        @include('components.word-search')

        <!-- Questions Container -->
        <div id="questions" class="space-y-6"></div>

        <!-- Summary Section -->
        <div id="summary" class="mt-8 hidden">
            <div class="bg-white rounded-2xl shadow-xl p-8 border border-gray-200">
                <div class="text-center mb-6">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gradient-to-br from-green-400 to-emerald-500 mb-4">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 mb-2">Test Complete!</h2>
                    <p class="text-lg text-gray-600" id="summary-text"></p>
                </div>
                <div class="flex gap-4 justify-center flex-wrap">
                    <button id="retry" class="px-6 py-3 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-semibold hover:from-indigo-700 hover:to-purple-700 transition-all transform hover:scale-105 shadow-lg">
                        Try Again
                    </button>
                    <button id="show-wrong" class="px-6 py-3 rounded-xl border-2 border-gray-300 text-gray-700 font-semibold hover:border-indigo-500 hover:text-indigo-600 transition-all">
                        Show Only Errors
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
const STATE_URL = '{{ route('saved-test.js.v2.state', $test->slug) }}';
const QUESTIONS_URL = '{{ route('saved-test.js.v2.questions', $test->slug) }}';
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
    card.className = 'bg-white rounded-2xl shadow-lg border border-gray-200 p-6 hover:shadow-xl transition-all duration-200 focus-within:ring-2 ring-indigo-500 outline-none';
    card.tabIndex = 0;
    card.dataset.idx = idx;

    const sentence = renderSentence(q);

    card.innerHTML = `
      <div class="flex items-start justify-between gap-4 mb-4">
        <div class="flex-1">
          <div class="flex items-center gap-2 mb-2">
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-700">
              ${q.level}
            </span>
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-purple-100 text-purple-700">
              ${q.tense}
            </span>
          </div>
          <div class="text-lg leading-relaxed text-gray-800 font-medium">${sentence}</div>
        </div>
        <div class="text-sm text-gray-400 font-bold shrink-0">#${idx + 1}</div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-3" role="group" aria-label="Answer options">
        ${q.options.map((opt, i) => renderOptionButton(q, idx, opt, i)).join('')}
      </div>

      <div id="feedback-${idx}">${renderFeedback(q)}</div>
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
    summaryText.textContent = `You got ${state.correct} out of ${state.items.length} correct (${pct(state.correct, state.items.length)}%).`;
    const retryButton = document.getElementById('retry');
    if (retryButton) {
      retryButton.onclick = () => restartJsTest(init, { button: retryButton });
    }
    document.getElementById('show-wrong').onclick = () => renderQuestions(true);
  }
}

function renderOptionButton(q, idx, opt, i) {
  const base = 'w-full text-left px-4 py-3 rounded-xl border-2 transition-all duration-200 font-medium';
  let cls = 'border-gray-300 hover:border-indigo-400 hover:bg-indigo-50 bg-white';
  
  if (q.done) {
    cls = 'border-gray-200 bg-gray-50 text-gray-500 cursor-not-allowed';
  } else if (q.lastWrong === opt) {
    cls = 'border-red-400 bg-red-50 text-red-700';
  }
  
  const hotkey = i + 1;
  return `
    <button type="button" class="${base} ${cls}" data-opt="${html(opt)}" title="Press ${hotkey}" ${q.done ? 'disabled' : ''}>
      <span class="inline-flex items-center justify-center w-6 h-6 rounded-md border-2 border-current text-sm font-bold mr-3">${hotkey}</span>
      ${opt}
    </button>
  `;
}

function renderFeedback(q) {
  if (q.feedback === 'correct') {
    let htmlStr = '<div class="flex items-center gap-2 text-sm font-semibold text-green-700 bg-green-50 border-2 border-green-200 px-4 py-3 rounded-xl"><svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg><span>Correct!</span></div>';
    if (q.explanation) {
      htmlStr += `<div class="mt-3 whitespace-pre-line text-sm text-green-800 bg-green-50 border border-green-200 px-4 py-3 rounded-xl">${html(q.explanation)}</div>`;
    }
    return htmlStr;
  }
  if (q.feedback) {
    let htmlStr = `<div class="flex items-center gap-2 text-sm font-semibold text-red-700 bg-red-50 border-2 border-red-200 px-4 py-3 rounded-xl"><svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg><span>${html(q.feedback)}</span></div>`;
    if (q.explanation) {
      htmlStr += `<div class="mt-3 whitespace-pre-line text-sm text-red-800 bg-red-50 border border-red-200 px-4 py-3 rounded-xl">${html(q.explanation)}</div>`;
    }
    return htmlStr;
  }
  if (q.explanation) {
    return `<div class="mt-3 whitespace-pre-line text-sm text-gray-700 bg-gray-50 border border-gray-200 px-4 py-3 rounded-xl">${html(q.explanation)}</div>`;
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
      ? `<mark class=\"px-2 py-1 rounded-lg bg-gradient-to-r from-amber-200 to-yellow-200 font-semibold\">${html(q.chosen[i])}</mark>`
      : (i === q.slot
        ? `<mark class=\"px-2 py-1 rounded-lg bg-gradient-to-r from-indigo-200 to-purple-200 font-semibold\">____</mark>`
        : '____');
    const regex = new RegExp(`\\{a${i + 1}\\}`);
    const marker = `a${i + 1}`;
    const hint = q.verb_hints && q.verb_hints[marker]
      ? ` <span class=\"verb-hint text-red-600 text-sm font-bold italic\">(${html(q.verb_hints[marker])})</span>`
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
