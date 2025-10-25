@extends('layouts.engram')

@section('title', $test->name)

@section('content')
<div class="mx-auto max-w-3xl px-4 py-8 text-stone-800" id="quiz-app">
    <header class="mb-6">
        <h1 class="text-2xl sm:text-3xl font-bold text-stone-900">{{ $test->name }}</h1>
        <p class="text-sm text-stone-600 mt-1">Перевіряй відповіді одразу, клавіші 1–4 працюють для активного запитання.</p>
    </header>

    @include('components.test-mode-nav')

    @include('components.word-search')
    @include('components.saved-test-progress', ['progress' => '1 / 0'])
    @include('components.saved-test-js-restart-button')

    <div id="question-wrap">
        <div id="question-card"></div>
        <div class="mt-4 flex justify-between">
            <button id="prev" class="px-4 py-2 rounded-xl border border-stone-300" disabled>Назад</button>
            <button id="next" class="px-4 py-2 rounded-xl bg-stone-900 text-white" disabled>Далі</button>
        </div>
    </div>

    <div id="summary" class="mt-8 hidden">
        <div class="rounded-2xl border border-stone-200 bg-white p-4">
            <div class="text-lg font-semibold">Підсумок</div>
            <p class="text-sm text-stone-600 mt-1" id="summary-text"></p>
            <div class="mt-3">
                <button id="retry" class="px-4 py-2 rounded-xl bg-stone-900 text-white">Пройти ще раз</button>
            </div>
        </div>
    </div>
</div>
<div id="ajax-loader" class="hidden fixed inset-0 bg-white/70 flex items-center justify-center z-50">
    <svg class="h-8 w-8 animate-spin text-stone-900" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
    </svg>
</div>
<style>
 
  #question-card article{
        background-color: #fef6db;
  }
</style>
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
    <article class="rounded-2xl border border-stone-200 bg-white p-4 focus-within:ring-2 ring-stone-900/20 outline-none" data-idx="${state.current}">
      <div class="flex items-start justify-between gap-3">
        <div>
          <div class="text-sm text-stone-500">${q.level} • ${q.tense}</div>
          <div class="mt-1 text-base leading-relaxed text-stone-900">${sentence}</div>
          <button type="button" id="help" class="text-xs text-blue-600 underline mt-1">Help</button>
          <div id="hints" class="mt-2 text-base text-gray-800 space-y-2"></div>
        </div>
        <div class="text-xs text-stone-500 shrink-0">[${state.current + 1}/${state.items.length}]</div>
      </div>
      <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-2" role="group" aria-label="Варіанти відповіді">
        ${q.options.map((opt, i) => renderOptionButton(q, opt, i)).join('')}
      </div>
      <div class="mt-2" id="feedback">${renderFeedback(q)}</div>
    </article>
  `;

  document.getElementById('prev').disabled = state.current === 0;
  document.getElementById('next').disabled = !q.done;
  document.getElementById('help').addEventListener('click', () => fetchHints(q));
  renderHints(q);
}

function renderOptionButton(q, opt, i) {
  const base = 'w-full text-left px-3 py-2 rounded-xl border transition';
  let cls = 'border-stone-300 hover:border-stone-400 bg-white';
  if (q.done) {
    cls = 'border-stone-300 bg-stone-100';
  } else if (q.lastWrong === opt) {
    cls = 'border-rose-300 bg-rose-50';
  }
  const hotkey = i + 1;
  return `
    <button type="button" class="${base} ${cls}" data-opt="${html(opt)}" title="Натисни ${hotkey}" ${q.done ? 'disabled' : ''}>
      <span class="mr-2 inline-flex h-5 w-5 items-center justify-center rounded-md border text-xs">${hotkey}</span>
      ${opt}
    </button>
  `;
}

function renderFeedback(q) {
  if (q.feedback === 'correct') {
    let htmlStr = '<div class="text-sm text-emerald-700">✅ Вірно!</div>';
    if (q.explanation) {
      htmlStr += `<div class="mt-2 whitespace-pre-line text-sm text-emerald-700 bg-emerald-50 border border-emerald-200 px-3 py-2 rounded-xl">${html(q.explanation)}</div>`;
    }
    return htmlStr;
  }
  if (q.feedback) {
    let htmlStr = `<div class="text-sm text-rose-700">${html(q.feedback)}</div>`;
    if (q.explanation) {
      htmlStr += `<div class="mt-2 whitespace-pre-line text-sm text-rose-700 bg-rose-50 border border-rose-200 px-3 py-2 rounded-xl">${html(q.explanation)}</div>`;
    }
    return htmlStr;
  }
  if (q.explanation) {
    return `<div class="mt-2 whitespace-pre-line text-sm text-stone-700 bg-stone-50 border border-stone-200 px-3 py-2 rounded-xl">${html(q.explanation)}</div>`;
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
      q.feedback = `Правильна відповідь: ${correct}`;
      q.attempts = 0;
      if (q.slot === q.answers.length) {
        q.done = true;
      }
    } else {
      q.feedback = 'Невірно, спробуй ще раз';
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
  document.getElementById('score-label').textContent = `Точність: ${pct(state.correct, state.items.length)}%`;
  document.getElementById('progress-bar').style.width = `${(answered / state.items.length) * 100}%`;
}

function renderSentence(q) {
  let text = q.question;
  q.answers.forEach((ans, i) => {
    const replacement = q.chosen[i]
      ? `<mark class=\"px-1 py-0.5 rounded bg-amber-100\">${html(q.chosen[i])}</mark>`
      : (i === q.slot
        ? `<mark class=\"px-1 py-0.5 rounded bg-amber-200\">____</mark>`
        : '____');
    const regex = new RegExp(`\\{a${i + 1}\\}`);
    const marker = `a${i + 1}`;
    const hint = q.verb_hints && q.verb_hints[marker]
      ? ` <span class=\"verb-hint text-red-700 text-xs font-bold\">(${html(q.verb_hints[marker])})</span>`
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
  document.getElementById('summary-text').textContent = `Правильних відповідей: ${state.correct} із ${state.items.length} (${pct(state.correct, state.items.length)}%).`;
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
    htmlStr += `<p class="p-2 bg-stone-50 rounded"><strong>ChatGPT:</strong> <span class="whitespace-pre-line">${html(q.hints.chatgpt)}</span></p>`;
  }
  if (q.hints.gemini) {
    htmlStr += `<p class="p-2 bg-stone-50 rounded"><strong>Gemini:</strong> <span class="whitespace-pre-line">${html(q.hints.gemini)}</span></p>`;
  }
  htmlStr += `<button type="button" id="refresh-hint" class="mt-2 text-xs text-blue-600 underline block w-fit">Refresh</button>`;
  el.innerHTML = htmlStr;
  document.getElementById('refresh-hint').addEventListener('click', () => fetchHints(q, true));
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
