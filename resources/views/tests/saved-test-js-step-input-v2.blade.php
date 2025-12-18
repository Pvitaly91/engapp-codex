@extends('layouts.engram')

@section('title', $test->name)

@section('content')
<div class="fixed inset-0 -z-10 bg-gradient-to-br from-indigo-50 via-white to-purple-50"></div>
<div class="min-h-screen" id="quiz-app">
    <div class="max-w-5xl -mx-3 sm:mx-auto px-0 sm:px-5 md:px-6 lg:px-8 py-6 sm:py-10">
        <!-- Header Section with Modern Design -->
        <header class="mb-6 sm:mb-12">
            <div class="text-center space-y-3 sm:space-y-4">
                <div class="inline-flex items-center px-3.5 py-1.5 sm:px-4 sm:py-2 rounded-full bg-gradient-to-r from-indigo-100 to-purple-100 text-indigo-700 text-sm font-semibold mb-3 sm:mb-4">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    Input Test Mode
                </div>
                <h1 class="text-[28px] sm:text-4xl lg:text-5xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">
                    {{ $test->name }}
                </h1>
                <p class="text-[15px] sm:text-lg text-gray-600 max-w-2xl mx-auto">
                    Type your answers using hints and autocomplete suggestions
                </p>
            </div>
        </header>

        <!-- Navigation Tabs -->
        @include('components.test-mode-nav-v2')

        <!-- Sticky Controls: Search + Progress -->
        <div class="sticky-test-header sticky top-0 z-30 max-h-[40vh] md:max-h-none" id="sticky-header">
            <div class="sticky-inner space-y-2.5 sm:space-y-4 rounded-2xl border border-indigo-100 bg-white/90 p-2.5 sm:p-5 lg:p-6 shadow backdrop-blur-md transition-all duration-300">
                <div class="word-search-section transition-all duration-300">
                    @include('components.word-search')
                </div>
                <div class="progress-section bg-gradient-to-r from-indigo-50 via-white to-purple-50 rounded-2xl border border-indigo-100 p-2.5 sm:p-4 shadow-inner transition-all duration-300">
                    <div class="flex flex-wrap items-center justify-between gap-2.5 sm:gap-3">
                        <div class="flex items-center space-x-2.5 sm:space-x-3">
                            <!-- Search toggle button - only visible when stuck -->
                            <button type="button" id="sticky-search-toggle" class="sticky-search-btn hidden w-7 h-7 rounded-full bg-gradient-to-br from-blue-500 to-cyan-500 flex items-center justify-center transition-all duration-300 hover:scale-110 hover:shadow-md" title="Пошук слова">
                                <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </button>
                            <div class="progress-icon w-9 h-9 sm:w-10 sm:h-10 rounded-full bg-gradient-to-br from-indigo-500 to-purple-500 flex items-center justify-center transition-all duration-300">
                                <svg class="w-4 h-4 sm:w-5 sm:h-5 text-white transition-all duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg>
                            </div>
                            <div>
                                <div class="progress-label-text text-[11px] sm:text-xs font-semibold uppercase tracking-wide text-gray-500 transition-all duration-300">Progress</div>
                                <div id="progress-label" class="progress-value text-base sm:text-xl font-bold text-gray-900 transition-all duration-300">1 / 0</div>
                            </div>
                        </div>
                        <div class="text-right space-y-0.5">
                            <div class="progress-label-text text-[11px] sm:text-xs font-semibold uppercase tracking-wide text-gray-500 transition-all duration-300">Accuracy</div>
                            <div id="score-label" class="progress-value text-base sm:text-xl font-bold bg-gradient-to-r from-emerald-600 to-teal-600 bg-clip-text text-transparent transition-all duration-300">0%</div>
                        </div>
                    </div>
                    <div class="progress-bar-container relative w-full h-2.5 sm:h-3 bg-white border border-indigo-100 rounded-full overflow-hidden shadow-sm transition-all duration-300">
                        <div id="progress-bar" class="absolute top-0 left-0 h-full bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 rounded-full transition-all duration-500 ease-out" style="width:0%"></div>
                    </div>
                </div>
            </div>
        </div>

        <main class="mt-6 sm:mt-8 rounded-3xl bg-white shadow-xl border border-gray-100 p-4 sm:p-6 lg:p-8 space-y-5 sm:space-y-6">
            <!-- Restart Button -->
            @include('components.saved-test-js-restart-button')

            <!-- Question Container -->
            <div id="question-wrap">
                <div id="question-card" class="mb-5 sm:mb-6"></div>
                <div class="flex flex-col sm:flex-row gap-3 justify-between">
                    <button id="prev" class="group px-6 py-3.5 sm:px-8 sm:py-4 rounded-2xl border-2 border-gray-300 bg-white text-gray-700 font-semibold hover:border-gray-400 hover:bg-gray-50 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                        <span class="flex items-center justify-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                            </svg>
                            Previous
                        </span>
                    </button>
                    <button id="next" class="group px-6 py-3.5 sm:px-8 sm:py-4 rounded-2xl bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-semibold shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed" disabled>
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
                <div class="bg-gradient-to-br from-emerald-50 to-teal-50 rounded-3xl shadow-xl border-2 border-emerald-200 p-6 sm:p-8 text-center">
                    <div class="w-20 h-20 mx-auto mb-6 rounded-full bg-gradient-to-br from-emerald-400 to-teal-500 flex items-center justify-center">
                        <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h2 class="text-3xl font-bold text-gray-900 mb-3">Test Complete! 🎉</h2>
                    <p id="summary-text" class="text-xl text-gray-700 mb-8"></p>
                    <button id="retry" class="group px-6 py-3.5 sm:px-8 sm:py-4 rounded-2xl bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-semibold shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200">
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
      <article class="group bg-white rounded-3xl shadow-md hover:shadow-xl border-2 border-gray-100 hover:border-indigo-200 p-4 sm:p-6 lg:p-8 transition-all duration-300 focus-within:ring-4 ring-indigo-100 outline-none transform hover:-translate-y-1" data-idx="${state.current}">
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3.5 sm:gap-4 mb-5 sm:mb-6">
        <div class="flex-1">
          <div class="flex items-center gap-2.5 sm:gap-3 mb-2.5 sm:mb-3">
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-gradient-to-r from-blue-100 to-indigo-100 text-indigo-700">
              ${q.level || 'N/A'}
            </span>
            <span class="text-xs sm:text-sm text-gray-500 font-medium">${q.tense || 'Grammar'}</span>
          </div>
          <div class="text-base sm:text-xl leading-relaxed text-gray-900 font-medium mb-2.5 sm:mb-3">${sentence}</div>
          <button type="button" id="help" class="inline-flex items-center text-[13px] sm:text-sm text-indigo-600 hover:text-indigo-700 font-medium transition-colors">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            Show Help
          </button>
          ${q.theory_block ? `<button type="button" id="theory-btn" class="ml-2 sm:ml-3 inline-flex items-center text-[13px] sm:text-sm text-emerald-600 hover:text-emerald-700 font-medium transition-colors">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
            </svg>
            Show Theory
          </button>` : ''}
          <div id="hints" class="mt-2.5 sm:mt-3 space-y-2"></div>
          <div id="theory-panel" class="mt-2.5 sm:mt-3 hidden"></div>
        </div>
        <div class="flex flex-col items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-br from-indigo-50 to-purple-50 border border-indigo-100 shrink-0 sm:self-start">
          <div class="text-xs text-gray-500 font-medium">Q</div>
          <div class="text-lg font-bold text-indigo-600">${state.current + 1}</div>
        </div>
      </div>
      ${q.isCorrect === null ? '<div class="mb-4"><button id="check" class="px-6 py-3.5 sm:px-8 sm:py-4 rounded-2xl bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-semibold shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200">Check Answer</button></div>' : ''}
      <div class="min-h-8" id="feedback">${renderFeedback(q)}</div>
    </article>
  `;

  document.getElementById('prev').disabled = state.current === 0;
  document.getElementById('next').disabled = q.isCorrect === null;
  document.getElementById('help').addEventListener('click', () => fetchHints(q));
  renderHints(q);
  
  // Theory button handler
  const theoryBtn = document.getElementById('theory-btn');
  if (theoryBtn && q.theory_block) {
    theoryBtn.addEventListener('click', () => toggleTheoryPanel(q));
    renderTheoryPanel(q);
  }
  
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
  htmlStr += `<button type="button" id="refresh-hint" class="inline-flex items-center text-[13px] sm:text-sm text-indigo-600 hover:text-indigo-700 font-medium transition-colors mt-2"><svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>Refresh Hint</button>`;
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
    
    // Render based on block type
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
    // If body is not valid JSON, it may be raw HTML content from trusted server-side sources
    // Render it directly without escaping
    content = `<div class="text-sm text-emerald-800">${block.body || ''}</div>`;
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
    return '<div class="flex items-start gap-3 p-3 sm:p-4 rounded-2xl bg-gradient-to-r from-emerald-50 to-teal-50 border-2 border-emerald-200"><div class="flex-shrink-0 w-6 h-6 rounded-full bg-emerald-500 flex items-center justify-center"><svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg></div><div class="flex-1"><div class="font-semibold text-emerald-800">Correct!</div></div></div>';
  }
  if (q.isCorrect === false) {
    let htmlStr = '<div class="flex items-start gap-3 p-3 sm:p-4 rounded-2xl bg-gradient-to-r from-red-50 to-rose-50 border-2 border-red-200"><div class="flex-shrink-0 w-6 h-6 rounded-full bg-red-500 flex items-center justify-center"><svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></div><div class="flex-1"><div class="font-semibold text-red-800">Incorrect. Correct answer: <span class="font-bold">' + html(q.answers.join(' ')) + '</span></div></div></div>';
    if (q.explanation) {
      htmlStr += '<div class="mt-2.5 sm:mt-3 p-3 sm:p-4 rounded-2xl bg-blue-50 border border-blue-200 text-sm text-blue-800 leading-relaxed">' + html(q.explanation) + '</div>';
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
@include('components.sticky-header-scroll')
@endsection
