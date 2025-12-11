@extends('layouts.engram')

@section('title', $test->name)

@section('content')
<div class="fixed inset-0 -z-10 bg-gradient-to-br from-indigo-50 via-white to-purple-50"></div>
<div class="min-h-screen" id="quiz-app">
    <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8 py-8 sm:py-12">
        <!-- Header Section with Modern Design -->
        <header class="mb-8 sm:mb-12">
            <div class="text-center space-y-4">
                <div class="inline-flex items-center px-4 py-2 rounded-full bg-gradient-to-r from-indigo-100 to-purple-100 text-indigo-700 text-sm font-semibold mb-4">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                    Step-by-Step Test
                </div>
                <h1 class="text-3xl sm:text-4xl lg:text-5xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">
                    {{ $test->name }}
                </h1>
                <p class="text-base sm:text-lg text-gray-600 max-w-2xl mx-auto">
                    Answer questions one at a time using keyboard shortcuts (1-4) or click on the options below
                </p>
            </div>
        </header>

        <!-- Navigation Tabs -->
        @include('components.test-mode-nav-v2')

        <!-- Sticky Controls: Search + Progress -->
        <div class="sticky top-0 z-30 max-h-[40vh] md:max-h-none">
            <div class="space-y-3 sm:space-y-4 rounded-2xl border border-indigo-100 bg-white/90 p-3 sm:p-6 shadow backdrop-blur-md">
                @include('components.word-search')
                <div class="bg-gradient-to-r from-indigo-50 via-white to-purple-50 rounded-2xl border border-indigo-100 p-3 sm:p-4 shadow-inner">
                    <div class="flex flex-wrap items-center justify-between gap-3 mb-2.5 sm:mb-3">
                        <div class="flex items-center space-x-2.5 sm:space-x-3">
                            <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-full bg-gradient-to-br from-indigo-500 to-purple-500 flex items-center justify-center">
                                <svg class="w-4 h-4 sm:w-5 sm:h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg>
                            </div>
                            <div>
                                <div class="text-[11px] sm:text-xs font-semibold uppercase tracking-wide text-gray-500">Progress</div>
                                <div id="progress-label" class="text-lg sm:text-xl font-bold text-gray-900">1 / 0</div>
                            </div>
                        </div>
                        <div class="text-right space-y-0.5">
                            <div class="text-[11px] sm:text-xs font-semibold uppercase tracking-wide text-gray-500">Accuracy</div>
                            <div id="score-label" class="text-lg sm:text-xl font-bold bg-gradient-to-r from-emerald-600 to-teal-600 bg-clip-text text-transparent">0%</div>
                        </div>
                    </div>
                    <div class="relative w-full h-2.5 sm:h-3 bg-white border border-indigo-100 rounded-full overflow-hidden shadow-sm">
                        <div id="progress-bar" class="absolute top-0 left-0 h-full bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 rounded-full transition-all duration-500 ease-out" style="width:0%"></div>
                    </div>
                </div>
            </div>
        </div>

        <main class="mt-8 rounded-3xl bg-white shadow-xl border border-gray-100 p-6 sm:p-8 space-y-6">
            <!-- Restart Button -->
            @include('components.saved-test-js-restart-button')

            <!-- Question Container -->
            <div id="question-wrap">
                <div id="question-card" class="mb-6"></div>
                <div class="flex flex-col sm:flex-row gap-3 justify-between">
                    <button id="prev" class="group px-8 py-4 rounded-2xl border-2 border-gray-300 bg-white text-gray-700 font-semibold hover:border-gray-400 hover:bg-gray-50 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                        <span class="flex items-center justify-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                            </svg>
                            Previous
                        </span>
                    </button>
                    <button id="next" class="group px-8 py-4 rounded-2xl bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-semibold shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                        <span class="flex items-center justify-center">
                            Next
                            <svg class="w-5 h-5 ml-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </span>
                    </button>
                </div>
            </div>

            <!-- Summary Section with Celebration Design -->
            <div id="summary" class="hidden">
                <div class="bg-gradient-to-br from-emerald-50 to-teal-50 rounded-3xl shadow-xl border-2 border-emerald-200 p-8 text-center">
                    <div class="w-20 h-20 mx-auto mb-6 rounded-full bg-gradient-to-br from-emerald-400 to-teal-500 flex items-center justify-center">
                        <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h2 class="text-3xl font-bold text-gray-900 mb-3">Test Complete! 🎉</h2>
                    <p id="summary-text" class="text-xl text-gray-700 mb-8"></p>
                    <button id="retry" class="group px-8 py-4 rounded-2xl bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-semibold shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200">
                        <span class="flex items-center justify-center">
                            <svg class="w-5 h-5 mr-2 group-hover:rotate-180 transition-transform duration-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                            Try Again
                        </span>
                    </button>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Loading Overlay -->
<div id="ajax-loader" class="hidden fixed inset-0 bg-gradient-to-br from-indigo-900/20 to-purple-900/20 backdrop-blur-sm flex items-center justify-center z-50">
    <div class="bg-white rounded-3xl shadow-2xl p-8 flex flex-col items-center">
        <svg class="h-12 w-12 animate-spin text-indigo-600 mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
        </svg>
        <p class="text-gray-700 font-medium">Loading...</p>
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
  current: 0,
  correct: 0,
};

let globalEventsHooked = false;

const loaderEl = document.getElementById('ajax-loader');
function showLoader(show) {
  if (!loaderEl) return;
  loaderEl.classList.toggle('hidden', !show);
}

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
      state.current = Number.isFinite(saved.current) ? saved.current : 0;
      state.correct = Number.isFinite(saved.correct) ? saved.correct : 0;
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
    state.current = 0;
    state.correct = 0;
  }

  if (state.current < 0) state.current = 0;
  if (state.current >= state.items.length) state.current = Math.max(0, state.items.length - 1);

  document.getElementById('summary').classList.add('hidden');
  document.getElementById('question-wrap').classList.remove('hidden');
  ensureGlobalEvents();
  render();
  updateProgress();
  persistState(state, true);
}

function render() {
  const wrap = document.getElementById('question-card');
  const q = state.items[state.current];
  const sentence = renderSentence(q);
  wrap.innerHTML = `
    <article class="group bg-white rounded-3xl shadow-md hover:shadow-xl border-2 border-gray-100 hover:border-indigo-200 p-6 sm:p-8 transition-all duration-300 focus-within:ring-4 ring-indigo-100 outline-none transform hover:-translate-y-1" data-idx="${state.current}">
      <div class="flex items-start justify-between gap-4 mb-6">
        <div class="flex-1">
          <div class="flex items-center gap-3 mb-3">
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-gradient-to-r from-blue-100 to-indigo-100 text-indigo-700">
              ${q.level || 'N/A'}
            </span>
            <span class="text-sm text-gray-500 font-medium">${q.tense || 'Grammar'}</span>
          </div>
          <div class="text-lg sm:text-xl leading-relaxed text-gray-900 font-medium mb-3">${sentence}</div>
          <button type="button" id="help" class="inline-flex items-center text-sm text-indigo-600 hover:text-indigo-700 font-medium transition-colors">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            Show Help
          </button>
          ${q.theory_block ? `<button type="button" id="theory-btn" class="ml-3 inline-flex items-center text-sm text-emerald-600 hover:text-emerald-700 font-medium transition-colors">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
            </svg>
            Show Theory
          </button>` : ''}
          <div id="hints" class="mt-3 space-y-2"></div>
          <div id="theory-panel" class="mt-3 hidden"></div>
        </div>
        <div class="flex flex-col items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-br from-indigo-50 to-purple-50 border border-indigo-100 shrink-0">
          <div class="text-xs text-gray-500 font-medium">Q</div>
          <div class="text-lg font-bold text-indigo-600">${state.current + 1}</div>
        </div>
      </div>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-6" role="group" aria-label="Answer options">
        ${q.options.map((opt, i) => renderOptionButton(q, opt, i)).join('')}
      </div>
      <div id="feedback">${renderFeedback(q)}</div>
    </article>
  `;

  document.getElementById('prev').disabled = state.current === 0;
  document.getElementById('next').disabled = !q.done;
  document.getElementById('help').addEventListener('click', () => fetchHints(q));
  renderHints(q);
  
  // Theory button handler
  const theoryBtn = document.getElementById('theory-btn');
  if (theoryBtn && q.theory_block) {
    theoryBtn.addEventListener('click', () => toggleTheoryPanel(q));
    renderTheoryPanel(q);
  }
}

function renderOptionButton(q, opt, i) {
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

document.getElementById('question-card').addEventListener('click', (e) => {
  const btn = e.target.closest('button[data-opt]');
  if (!btn) return;
  onChoose(btn.dataset.opt);
});

function onChoose(opt) {
  const q = state.items[state.current];
  if (!q || q.done) return;

  const slotIndex = q.slot;
  const expected = q.answers[slotIndex];
  if (expected === undefined) return;

  if (!q.explanationsCache) {
    q.explanationsCache = {};
  }

  const key = buildExplanationKey(opt, expected);
  q.pendingExplanationKey = key;
  if (Object.prototype.hasOwnProperty.call(q.explanationsCache, key)) {
    q.explanation = q.explanationsCache[key];
  } else {
    q.explanation = '';
  }

  const explanationPromise = ensureExplanation(q, opt, expected, key, slotIndex);

  if (opt === expected) {
    q.chosen[slotIndex] = opt;
    q.slot += 1;
    q.lastWrong = null;
    q.feedback = 'correct';
    q.attempts = 0;
    if (q.slot === q.answers.length) {
      q.done = true;
      if (!q.wrongAttempt) state.correct += 1;
    }
  } else {
    q.wrongAttempt = true;
    q.lastWrong = opt;
    q.attempts += 1;
    if (q.attempts >= 2) {
      const correct = expected;
      q.chosen[slotIndex] = correct;
      q.slot += 1;
      q.feedback = `Correct answer: ${correct}`;
      q.attempts = 0;
      if (q.slot === q.answers.length) {
        q.done = true;
      }
    } else {
      q.feedback = 'Incorrect, try again';
    }
  }
  render();
  updateProgress();
  persistState(state);

  explanationPromise
    .then((text) => {
      if (q.pendingExplanationKey !== key) {
        return;
      }
      q.explanation = text || '';
      if (state.items[state.current] === q) {
        const feedbackEl = document.getElementById('feedback');
        if (feedbackEl) {
          feedbackEl.innerHTML = renderFeedback(q);
        }
      }
      persistState(state);
    })
    .catch((err) => {
      console.error(err);
    });
}

document.getElementById('prev').addEventListener('click', () => {
  if (state.current > 0) {
    state.current -= 1;
    render();
    updateProgress();
    persistState(state);
  }
});

document.getElementById('next').addEventListener('click', () => {
  if (state.current < state.items.length - 1) {
    state.current += 1;
    render();
    updateProgress();
    persistState(state);
  } else {
    showSummary();
  }
});

function updateProgress() {
  const answered = state.items.filter(it => it.done).length;
  document.getElementById('progress-label').textContent = `${state.current + 1} / ${state.items.length}`;
  const percent = state.items.length ? pct(state.correct, state.items.length) : 0;
  document.getElementById('score-label').textContent = `${percent}%`;
  document.getElementById('progress-bar').style.width = `${(answered / state.items.length) * 100}%`;
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

function ensureExplanation(q, selected, expected, key, slotIndex) {
  if (!expected && !selected) {
    return Promise.resolve('');
  }

  if (!q.explanationsCache) {
    q.explanationsCache = {};
  }

  if (Object.prototype.hasOwnProperty.call(q.explanationsCache, key)) {
    return Promise.resolve(q.explanationsCache[key] || '');
  }

  const payload = {
    question_id: q.id,
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
      q.explanationsCache[key] = text;

      return text;
    })
    .catch((error) => {
      console.error(error);
      q.explanationsCache[key] = '';

      return '';
    });
}

function showSummary() {
  document.getElementById('question-wrap').classList.add('hidden');
  const summary = document.getElementById('summary');
  summary.classList.remove('hidden');
  document.getElementById('summary-text').textContent = `You got ${state.correct} out of ${state.items.length} correct (${pct(state.correct, state.items.length)}%)!`;
  const retryButton = document.getElementById('retry');
  if (retryButton) {
    retryButton.onclick = () => restartJsTest(init, { showLoaderFn: showLoader, button: retryButton });
  }
}

function fetchHints(q, refresh = false) {
  const payload = q.id ? { question_id: q.id } : { question: q.question };
  if (refresh) payload.refresh = true;
  showLoader(true);
  fetch('{{ route('question.hint') }}', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': CSRF_TOKEN,
    },
    body: JSON.stringify(payload),
  })
    .then((r) => r.json())
    .then((d) => {
      if (d.chatgpt) d.chatgpt = d.chatgpt.replace(/\{a\d+\}/g, '\n$&');
      if (d.gemini) d.gemini = d.gemini.replace(/\{a\d+\}/g, '\n$&');
      q.hints = d;
      renderHints(q);
      persistState(state);
    })
    .catch((e) => console.error(e))
    .finally(() => showLoader(false));
}

function renderHints(q) {
  const el = document.getElementById('hints');
  if (!el) return;
  if (!q.hints || (!q.hints.chatgpt && !q.hints.gemini)) {
    el.innerHTML = '';
    return;
  }
  let htmlStr = '';
  if (q.hints.chatgpt) {
    htmlStr += `<div class="p-4 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-2xl border border-blue-200"><p class="text-sm font-semibold text-blue-900 mb-2">💡 ChatGPT Hint:</p><p class="text-sm text-blue-800 whitespace-pre-line leading-relaxed">${html(q.hints.chatgpt)}</p></div>`;
  }
  if (q.hints.gemini) {
    htmlStr += `<div class="p-4 bg-gradient-to-r from-purple-50 to-pink-50 rounded-2xl border border-purple-200"><p class="text-sm font-semibold text-purple-900 mb-2">✨ Gemini Hint:</p><p class="text-sm text-purple-800 whitespace-pre-line leading-relaxed">${html(q.hints.gemini)}</p></div>`;
  }
  htmlStr += `<button type="button" id="refresh-hint" class="inline-flex items-center text-sm text-indigo-600 hover:text-indigo-700 font-medium transition-colors mt-2"><svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>Refresh Hint</button>`;
  el.innerHTML = htmlStr;
  document.getElementById('refresh-hint').addEventListener('click', () => fetchHints(q, true));
}

function toggleTheoryPanel(q) {
  const panel = document.getElementById('theory-panel');
  if (!panel) return;
  
  if (panel.classList.contains('hidden')) {
    renderTheoryPanel(q);
    panel.classList.remove('hidden');
  } else {
    panel.classList.add('hidden');
  }
}

function renderTheoryPanel(q) {
  const panel = document.getElementById('theory-panel');
  if (!panel || !q.theory_block) return;
  
  const block = q.theory_block;
  let content = '';
  
  try {
    const body = typeof block.body === 'string' ? JSON.parse(block.body) : block.body;
    
    if (body.title) {
      content += `<h4 class="font-semibold text-emerald-900 mb-2">${html(body.title)}</h4>`;
    }
    // Note: body.intro and section.description may contain intentional HTML formatting
    // (e.g., <strong> tags) from trusted server-side seeders, so we preserve it
    if (body.intro) {
      content += `<p class="text-sm text-emerald-800 mb-3">${body.intro}</p>`;
    }
    if (body.sections && Array.isArray(body.sections)) {
      body.sections.forEach(section => {
        content += `<div class="mb-3">`;
        if (section.label) {
          content += `<p class="text-sm font-semibold text-emerald-700">${html(section.label)}</p>`;
        }
        if (section.description) {
          content += `<p class="text-sm text-emerald-800">${section.description}</p>`;
        }
        if (section.examples && Array.isArray(section.examples)) {
          content += `<ul class="mt-1 space-y-1">`;
          section.examples.forEach(ex => {
            content += `<li class="text-sm"><span class="text-emerald-900 font-medium">${html(ex.en || '')}</span>`;
            if (ex.ua) content += ` — <span class="text-emerald-700">${html(ex.ua)}</span>`;
            content += `</li>`;
          });
          content += `</ul>`;
        }
        if (section.note) {
          content += `<p class="text-xs text-emerald-600 mt-1">${html(section.note)}</p>`;
        }
        content += `</div>`;
      });
    }
    if (body.items && Array.isArray(body.items)) {
      content += `<ul class="list-disc list-inside space-y-1">`;
      body.items.forEach(item => {
        if (typeof item === 'string') {
          content += `<li class="text-sm text-emerald-800">${html(item)}</li>`;
        } else if (item.title) {
          content += `<li class="text-sm"><span class="font-medium text-emerald-900">${html(item.title)}</span>`;
          if (item.subtitle) content += ` — <span class="text-emerald-700">${html(item.subtitle)}</span>`;
          content += `</li>`;
        }
      });
      content += `</ul>`;
    }
  } catch (e) {
    content = `<p class="text-sm text-emerald-800">${html(block.body || '')}</p>`;
  }
  
  panel.innerHTML = `
    <div class="p-4 bg-gradient-to-r from-emerald-50 to-teal-50 rounded-2xl border border-emerald-200">
      <div class="flex items-center gap-2 mb-2">
        <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
        </svg>
        <span class="text-sm font-semibold text-emerald-900">📚 Theory</span>
        ${block.level ? `<span class="ml-auto px-2 py-0.5 text-xs font-bold rounded-full bg-emerald-200 text-emerald-800">${html(block.level)}</span>` : ''}
      </div>
      ${content}
    </div>
  `;
}

function hookGlobalEvents() {
  document.addEventListener('keydown', (e) => {
    const n = Number(e.key);
    if (!Number.isInteger(n) || n < 1 || n > 4) return;
    const q = state.items[state.current];
    if (!q || q.done) return;
    const opt = q.options[n - 1];
    if (opt) onChoose(opt);
  });
}

const restartButton = document.getElementById('restart-test');
if (restartButton) {
  restartButton.addEventListener('click', () => restartJsTest(init, { showLoaderFn: showLoader, button: restartButton }));
}

init();
</script>
@endsection
