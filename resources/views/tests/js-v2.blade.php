@extends('layouts.engram')

@section('title', $test->name)

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50">
    <!-- Header Section -->
    <div class="bg-white border-b border-slate-200 shadow-sm">
        <div class="mx-auto max-w-5xl px-6 py-8">
            <div class="flex items-start justify-between gap-6 flex-wrap">
                <div class="flex-1 min-w-0">
                    <h1 class="text-3xl md:text-4xl font-bold text-slate-900 mb-2 tracking-tight">{{ $test->name }}</h1>
                    <p class="text-base text-slate-600 leading-relaxed">
                        Check your answers instantly. Press keys 1–4 to select options for the active question.
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    @include('components.saved-test-js-restart-button')
                    @include('components.word-search')
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="mx-auto max-w-5xl px-6 py-8" id="quiz-app">
        <!-- Test Mode Navigation -->
        @include('components.test-mode-nav')

        <!-- Progress Section -->
        <div class="mb-8">
            @include('components.saved-test-progress')
        </div>

        <!-- Questions Container -->
        <div id="questions" class="space-y-6"></div>

        <!-- Summary Section -->
        <div id="summary" class="mt-10 hidden">
            <div class="rounded-3xl border-2 border-slate-200 bg-white shadow-lg overflow-hidden">
                <div class="bg-gradient-to-r from-emerald-500 to-teal-500 px-8 py-6">
                    <div class="flex items-center gap-3">
                        <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <div>
                            <h3 class="text-2xl font-bold text-white">Test Complete!</h3>
                            <p class="text-emerald-50 text-sm mt-1">Great job on finishing the test</p>
                        </div>
                    </div>
                </div>
                <div class="px-8 py-6">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <p class="text-sm text-slate-600 uppercase tracking-wide font-semibold mb-1">Your Score</p>
                            <p class="text-4xl font-bold text-slate-900" id="summary-text"></p>
                        </div>
                        <div class="h-20 w-20 rounded-full bg-gradient-to-br from-emerald-100 to-teal-100 flex items-center justify-center">
                            <svg class="w-10 h-10 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="flex gap-3 flex-wrap">
                        <button id="retry" class="flex-1 min-w-[200px] px-6 py-3.5 rounded-xl bg-gradient-to-r from-slate-900 to-slate-700 text-white font-semibold shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200">
                            Try Again
                        </button>
                        <button id="show-wrong" class="flex-1 min-w-[200px] px-6 py-3.5 rounded-xl border-2 border-slate-300 text-slate-700 font-semibold hover:bg-slate-50 hover:border-slate-400 transition-all duration-200">
                            Review Mistakes
                        </button>
                    </div>
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
<script>
window.JS_TEST_PERSISTENCE = {
    endpoint: '{{ route('saved-test-v2.js.state', $test->slug) }}',
    mode: '{{ $jsStateMode }}',
    token: '{{ csrf_token() }}',
    questionsEndpoint: '{{ route('saved-test-v2.js.questions', $test->slug) }}',
    saved: @json($savedState),
};
</script>
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
    card.className = 'group rounded-2xl border-2 border-slate-200 bg-white shadow-md hover:shadow-xl transition-all duration-300 overflow-hidden focus-within:ring-4 ring-blue-500/20 outline-none';
    card.tabIndex = 0;
    card.dataset.idx = idx;

    const sentence = renderSentence(q);

    card.innerHTML = `
      <div class="bg-gradient-to-r from-slate-50 to-blue-50 px-6 py-4 border-b border-slate-200">
        <div class="flex items-center justify-between gap-4">
          <div class="flex items-center gap-3">
            <div class="flex items-center gap-2">
              <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-700 border border-blue-200">
                ${q.level || 'N/A'}
              </span>
              <span class="text-sm font-medium text-slate-600">${q.tense}</span>
            </div>
          </div>
          <div class="flex items-center gap-2">
            <span class="text-xs font-semibold text-slate-500 bg-slate-100 px-3 py-1.5 rounded-full">
              Question ${idx + 1} of ${state.items.length}
            </span>
          </div>
        </div>
      </div>

      <div class="px-6 py-5">
        <div class="text-lg leading-relaxed text-slate-900 mb-5 font-medium">${sentence}</div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3" role="group" aria-label="Answer options">
          ${q.options.map((opt, i) => renderOptionButton(q, idx, opt, i)).join('')}
        </div>

        <div class="mt-4" id="feedback-${idx}">${renderFeedback(q)}</div>
      </div>
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
    summaryText.textContent = `${state.correct} out of ${state.items.length} correct (${pct(state.correct, state.items.length)}%)`;
    const retryButton = document.getElementById('retry');
    if (retryButton) {
      retryButton.onclick = () => restartJsTest(init, { button: retryButton });
    }
    document.getElementById('show-wrong').onclick = () => renderQuestions(true);
  }
}

function renderOptionButton(q, idx, opt, i) {
  const base = 'w-full text-left px-4 py-3.5 rounded-xl border-2 font-medium transition-all duration-200 transform';
  let cls = 'border-slate-200 bg-white hover:border-blue-400 hover:bg-blue-50 hover:shadow-md active:scale-[0.98]';
  
  if (q.done) {
    cls = 'border-slate-200 bg-slate-50 text-slate-500 cursor-not-allowed';
  } else if (q.lastWrong === opt) {
    cls = 'border-rose-400 bg-rose-50 text-rose-700 shadow-sm';
  }
  
  const hotkey = i + 1;
  return `
    <button type="button" class="${base} ${cls}" data-opt="${html(opt)}" title="Press ${hotkey}" ${q.done ? 'disabled' : ''}>
      <div class="flex items-center gap-3">
        <span class="flex-shrink-0 inline-flex h-7 w-7 items-center justify-center rounded-lg border-2 ${q.done ? 'border-slate-300 text-slate-400' : 'border-slate-400 text-slate-700'} font-bold text-sm bg-white shadow-sm">
          ${hotkey}
        </span>
        <span class="flex-1">${opt}</span>
      </div>
    </button>
  `;
}

function renderFeedback(q) {
  if (q.feedback === 'correct') {
    let htmlStr = '<div class="flex items-center gap-2 text-sm font-semibold text-emerald-700"><svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg> Correct!</div>';
    if (q.explanation) {
      htmlStr += `<div class="mt-3 whitespace-pre-line text-sm text-emerald-800 bg-emerald-50 border-l-4 border-emerald-500 px-4 py-3 rounded-r-lg shadow-sm">${html(q.explanation)}</div>`;
    }
    return htmlStr;
  }
  if (q.feedback) {
    let htmlStr = `<div class="flex items-center gap-2 text-sm font-semibold text-rose-700"><svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg> ${html(q.feedback)}</div>`;
    if (q.explanation) {
      htmlStr += `<div class="mt-3 whitespace-pre-line text-sm text-rose-800 bg-rose-50 border-l-4 border-rose-500 px-4 py-3 rounded-r-lg shadow-sm">${html(q.explanation)}</div>`;
    }
    return htmlStr;
  }
  if (q.explanation) {
    return `<div class="mt-3 whitespace-pre-line text-sm text-slate-700 bg-slate-50 border-l-4 border-slate-400 px-4 py-3 rounded-r-lg shadow-sm">${html(q.explanation)}</div>`;
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
  score.textContent = `Accuracy: ${percent}%`;

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
      ? `<mark class=\"px-2 py-1 rounded-lg bg-amber-100 text-amber-900 font-semibold border border-amber-200\">${html(q.chosen[i])}</mark>`
      : (i === q.slot
        ? `<mark class=\"px-2 py-1 rounded-lg bg-blue-100 text-blue-900 font-semibold border-2 border-blue-400 animate-pulse\">____</mark>`
        : '<span class="text-slate-400">____</span>');
    const regex = new RegExp(`\\{a${i + 1}\\}`);
    const marker = `a${i + 1}`;
    const hint = q.verb_hints && q.verb_hints[marker]
      ? ` <span class=\"verb-hint text-rose-600 text-sm font-bold\">(${html(q.verb_hints[marker])})</span>`
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
