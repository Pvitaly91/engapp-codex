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
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    Input Test Mode
                </div>
                <h1 class="text-3xl sm:text-4xl lg:text-5xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">
                    {{ $test->name }}
                </h1>
                <p class="text-base sm:text-lg text-gray-600 max-w-2xl mx-auto">
                    Type your answers using hints and autocomplete suggestions
                </p>
            </div>
        </header>

        <!-- Navigation Tabs -->
        @include('components.test-mode-nav-v2')

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
                        <div id="progress-label" class="text-xl font-bold text-gray-900">1 / 0</div>
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
        <div id="summary" class="mt-12 hidden">
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
</script>
@include('components.saved-test-js-persistence', ['mode' => $jsStateMode, 'savedState' => $savedState])
@include('components.saved-test-js-helpers')
<script>
const state = {
  items: [],
  current: 0,
  correct: 0,
};

const loaderEl = document.getElementById('ajax-loader');
function showLoader(show) {
  if (!loaderEl) return;
  loaderEl.classList.toggle('hidden', !show);
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
    }
  }

  if (!restored) {
    state.items = QUESTIONS.map((q) => ({
      ...q,
      inputs: Array(q.answers.length)
        .fill(null)
        .map(() => ['']),
      isCorrect: null,
      explanation: '',
    }));
    state.current = 0;
    state.correct = 0;
  }

  if (state.current < 0) state.current = 0;
  if (state.current >= state.items.length) state.current = Math.max(0, state.items.length - 1);

  document.getElementById('summary').classList.add('hidden');
  document.getElementById('question-wrap').classList.remove('hidden');
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
          <div id="hints" class="mt-3 space-y-2"></div>
        </div>
        <div class="flex flex-col items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-br from-indigo-50 to-purple-50 border border-indigo-100 shrink-0">
          <div class="text-xs text-gray-500 font-medium">Q</div>
          <div class="text-lg font-bold text-indigo-600">${state.current + 1}</div>
        </div>
      </div>
      ${q.isCorrect === null ? '<div class="mb-4"><button id="check" class="px-8 py-4 rounded-2xl bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-semibold shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200">Check Answer</button></div>' : ''}
      <div class="min-h-8" id="feedback">${renderFeedback(q)}</div>
    </article>
  `;

  document.getElementById('prev').disabled = state.current === 0;
  document.getElementById('next').disabled = q.isCorrect === null;
  document.getElementById('help').addEventListener('click', () => fetchHints(q));
  renderHints(q);
  if (q.isCorrect === null) {
    document.getElementById('check').addEventListener('click', onCheck);
    document.querySelectorAll('input[data-idx][data-word]').forEach((inp) => {
      const idx = parseInt(inp.dataset.idx);
      const widx = parseInt(inp.dataset.word);
      inp.addEventListener('keydown', (e) => {
        if (e.key === ' ') e.preventDefault();
        if (e.key === 'Enter') onCheck();
      });
      const handle = () => {
        const val = inp.value.replace(/\s+/g, '');
        if (val !== inp.value) inp.value = val;
        q.inputs[idx][widx] = val;
        fetchSuggestions(inp, idx, widx);
        autoResize(inp);
        persistState(state);
      };
      inp.addEventListener('input', handle);
      inp.addEventListener('change', handle);
      autoResize(inp);
      fetchSuggestions(inp, idx, widx);
      const listId = inp.dataset.list;
      const getListEl = () => (listId ? document.querySelector(`#${listId}`) : null);
      const listEl = getListEl();
      if (listEl) {
        listEl.addEventListener('click', (event) => {
          const item = event.target.closest('li[data-value]');
          if (!item) return;
          const chosen = (item.dataset.value || item.textContent || '').trim();
          if (!chosen) return;
          const sanitized = chosen.replace(/\s+/g, '');
          const ignoreQuery = listEl.dataset.pendingQuery || inp.value.trim();
          listEl.dataset.ignoreQuery = ignoreQuery;
          inp.value = sanitized;
          q.inputs[idx][widx] = sanitized;
          autoResize(inp);
          listEl.innerHTML = '';
          listEl.dataset.hasOptions = '0';
          listEl.dataset.pendingQuery = '';
          listEl.classList.add('hidden');
          inp.focus();
          persistState(state);
        });
      }
      inp.addEventListener('blur', () => {
        const el = getListEl();
        if (!el) return;
        setTimeout(() => {
          el.classList.add('hidden');
        }, 150);
      });
      inp.addEventListener('focus', () => {
        const el = getListEl();
        if (!el || el.dataset.hasOptions !== '1') return;
        el.classList.remove('hidden');
      });
    });
    document.querySelectorAll('button[data-add]').forEach((btn) => {
      btn.addEventListener('click', () => {
        addWord(q, parseInt(btn.dataset.add));
      });
    });
    document.querySelectorAll('button[data-remove]').forEach((btn) => {
      btn.addEventListener('click', () => {
        removeWord(q, parseInt(btn.dataset.remove));
      });
    });
    const first = document.querySelector('input[data-idx][data-word]');
    if (first) first.focus();
  }
}

function onCheck() {
  const q = state.items[state.current];
  if (q.isCorrect !== null) return;
  const valParts = q.inputs.map((words) => words.join(' ').trim());
  const val = valParts.join(' ');
  q.input = val;
  q.isCorrect = q.answers.every((ans, i) =>
    valParts[i].toLowerCase() === (ans || '').toLowerCase()
  );
  if (q.isCorrect) {
    state.correct += 1;
  } else {
    fetchExplanation(q, val);
  }
  render();
  updateProgress();
  persistState(state);
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
  const answered = state.items.filter(it => it.isCorrect !== null).length;
  document.getElementById('progress-label').textContent = `${state.current + 1} / ${state.items.length}`;
  const percent = state.items.length ? pct(state.correct, state.items.length) : 0;
  document.getElementById('score-label').textContent = `${percent}%`;
  document.getElementById('progress-bar').style.width = `${(answered / state.items.length) * 100}%`;
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
  persistState(state);
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

function fetchExplanation(q, given) {
  showLoader(true);
  fetch(EXPLAIN_URL, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': CSRF_TOKEN,
    },
    body: JSON.stringify({ question_id: q.id, answer: given }),
  })
    .then((r) => r.json())
    .then((d) => {
      q.explanation = d.explanation || '';
      render();
      persistState(state);
    })
    .catch((e) => console.error(e))
    .finally(() => showLoader(false));
}

function addWord(q, idx) {
  q.inputs[idx].push('');
  render();
  persistState(state);
}

function removeWord(q, idx) {
  if (q.inputs[idx].length > 1) {
    q.inputs[idx].pop();
    render();
    persistState(state);
  }
}

function fetchSuggestions(input, idx, widx) {
  const val = input.value.trim();
  const listId = `opts-${state.current}-${idx}-${widx}`;
  const listEl = document.getElementById(listId);
  if (!listEl) return;
  const hideList = () => {
    listEl.innerHTML = '';
    listEl.classList.add('hidden');
    listEl.dataset.hasOptions = '0';
    listEl.dataset.pendingQuery = '';
    listEl.dataset.ignoreQuery = '';
  };
  if (!val) {
    hideList();
    return;
  }
  const query = val;
  listEl.dataset.pendingQuery = query;
  fetch('/api/search?lang=en&q=' + encodeURIComponent(query))
    .then(res => res.json())
    .then(data => {
      if (input.value.trim() !== query) return;
      if (listEl.dataset.ignoreQuery === query) {
        listEl.dataset.ignoreQuery = '';
        listEl.dataset.pendingQuery = '';
        return;
      }
      if (!Array.isArray(data) || data.length === 0) {
        hideList();
        return;
      }
      const options = [];
      data.forEach(it => {
        const value = html(it.en);
        options.push(`<li data-value="${value}" class="suggestion-item cursor-pointer px-3 py-2 hover:bg-indigo-50 rounded-lg transition-colors text-sm">${value}</li>`);
      });
      listEl.innerHTML = options.join('');
      listEl.dataset.hasOptions = '1';
      listEl.dataset.pendingQuery = '';
      listEl.scrollTop = 0;
      if (document.activeElement === input) {
        listEl.classList.remove('hidden');
      }
      if (input.dataset.list) {
        const width = input.style.width || input.getBoundingClientRect().width + 'px';
        listEl.style.width = width;
      }
    })
    .catch(() => {
      hideList();
    });
}

function renderFeedback(q) {
  if (q.isCorrect === true) {
    return '<div class="flex items-start gap-3 p-4 rounded-2xl bg-gradient-to-r from-emerald-50 to-teal-50 border-2 border-emerald-200"><div class="flex-shrink-0 w-6 h-6 rounded-full bg-emerald-500 flex items-center justify-center"><svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg></div><div class="flex-1"><div class="font-semibold text-emerald-800">Correct!</div></div></div>';
  }
  if (q.isCorrect === false) {
    let htmlStr = '<div class="flex items-start gap-3 p-4 rounded-2xl bg-gradient-to-r from-red-50 to-rose-50 border-2 border-red-200"><div class="flex-shrink-0 w-6 h-6 rounded-full bg-red-500 flex items-center justify-center"><svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></div><div class="flex-1"><div class="font-semibold text-red-800">Incorrect. Correct answer: <span class="font-bold">' + html(q.answers.join(' ')) + '</span></div></div></div>';
    if (q.explanation) {
      htmlStr += '<div class="mt-3 p-4 rounded-2xl bg-blue-50 border border-blue-200 text-sm text-blue-800 leading-relaxed">' + html(q.explanation) + '</div>';
    }
    return htmlStr;
  }
  return '';
}

function renderSentence(q) {
  let text = q.question;
  q.answers.forEach((ans, i) => {
    let replacement = '';
    if (q.isCorrect === null) {
      const words = q.inputs[i];
      const inputs = words
        .map((w, j) => {
          const inputId = `input-${state.current}-${i}-${j}`;
          const listId = `opts-${state.current}-${i}-${j}`;
          return `<span class="relative inline-flex flex-col items-start gap-1"><input id="${inputId}" autocomplete="off" type="text" data-idx="${i}" data-word="${j}" class="px-3 py-2 text-center border-2 border-indigo-200 rounded-xl focus:outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all font-medium" style="width:auto; min-width:80px" value="${html(w)}" data-list="${listId}"><ul id="${listId}" data-input="${inputId}" data-has-options="0" role="listbox" class="suggestion-list hidden absolute left-0 top-full mt-2 z-10 w-full max-h-60 overflow-auto rounded-2xl border-2 border-indigo-200 bg-white shadow-xl list-none p-2"></ul></span>`;
        })
        .join(' ');
      const addBtn = `<button type="button" data-add="${i}" class="ml-2 px-3 py-2 rounded-xl bg-gradient-to-r from-indigo-100 to-purple-100 hover:from-indigo-200 hover:to-purple-200 text-indigo-700 font-semibold transition-all flex-shrink-0">+</button>`;
      const removeBtn = words.length > 1
        ? `<button type="button" data-remove="${i}" class="ml-2 px-3 py-2 rounded-xl bg-gradient-to-r from-red-100 to-rose-100 hover:from-red-200 hover:to-rose-200 text-red-700 font-semibold transition-all flex-shrink-0">−</button>`
        : '';
      replacement = `<span class="inline-flex items-center gap-2 flex-wrap">${inputs}${addBtn}${removeBtn}</span>`;
    } else {
      replacement = `<mark class="px-3 py-1 rounded-lg bg-gradient-to-r from-amber-100 to-yellow-100 font-semibold">${html(q.inputs[i].join(' '))}</mark>`;
    }
    const regex = new RegExp(`\\{a${i + 1}\\}`);
    const marker = `a${i + 1}`;
    const hint = q.verb_hints && q.verb_hints[marker]
      ? ` <span class="verb-hint text-red-600 text-sm font-bold">( ${html(q.verb_hints[marker])} )</span>`
      : '';
    text = text.replace(regex, replacement + hint);
  });
  return text;
}

function autoResize(el) {
  const span = document.createElement('span');
  span.style.visibility = 'hidden';
  span.style.position = 'absolute';
  span.style.whiteSpace = 'pre';
  span.style.font = getComputedStyle(el).font;
  span.textContent = el.value || '';
  document.body.appendChild(span);
  const width = Math.max(80, span.offsetWidth + 40);
  document.body.removeChild(span);
  el.style.width = width + 'px';
  const listId = el.dataset.list;
  if (listId) {
    const listEl = document.getElementById(listId);
    if (listEl) {
      listEl.style.width = width + 'px';
    }
  }
}

const restartButton = document.getElementById('restart-test');
if (restartButton) {
  restartButton.addEventListener('click', () => restartJsTest(init, { showLoaderFn: showLoader, button: restartButton }));
}

init();
</script>
@endsection
