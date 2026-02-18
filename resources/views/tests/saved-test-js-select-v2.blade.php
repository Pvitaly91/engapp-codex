@extends('layouts.engram')

@section('title', $test->name)

@section('content')
<div class="min-h-screen bg-gradient-to-br from-indigo-50 via-white to-purple-50" id="quiz-app">
    <div class="max-w-5xl -mx-3 sm:mx-auto px-0 sm:px-5 md:px-6 lg:px-8 py-6 sm:py-10">
        <!-- Header Section with Modern Design -->
        <header class="mb-6 sm:mb-12">
            <div class="text-center space-y-3 sm:space-y-4">
                <div class="inline-flex items-center px-3.5 py-1.5 sm:px-4 sm:py-2 rounded-full bg-gradient-to-r from-indigo-100 to-purple-100 text-indigo-700 text-sm font-semibold mb-3 sm:mb-4">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                    </svg>
                    Select Mode - All Questions
                </div>
                <h1 class="text-[28px] sm:text-4xl lg:text-5xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">
                    {{ $test->name }}
                </h1>
                <p class="text-[15px] sm:text-lg text-gray-600 max-w-2xl mx-auto">
                    Choose the correct answers from dropdown lists for all questions
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

            <!-- Questions Container -->
            <div id="questions" class="space-y-5 sm:space-y-6"></div>

            <!-- Check All Button -->
            <div id="final-check" class="pt-3 sm:pt-4">
                <button id="check-all" type="button" class="w-full sm:w-auto px-8 py-4 sm:px-12 sm:py-5 rounded-2xl bg-gradient-to-r from-indigo-600 to-purple-600 text-white text-[15px] sm:text-lg font-semibold shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200">
                    <span class="flex items-center justify-center">
                        <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Check All Answers
                    </span>
                </button>
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

<script>
window.__INITIAL_JS_TEST_QUESTIONS__ = @json($questionData);
let QUESTIONS = Array.isArray(window.__INITIAL_JS_TEST_QUESTIONS__)
    ? window.__INITIAL_JS_TEST_QUESTIONS__
    : [];
const CSRF_TOKEN = '{{ csrf_token() }}';
window.__IS_ADMIN__ = Boolean(@json(auth()->user()?->is_admin ?? session('admin_authenticated', false)));
const MARKER_THEORY_URL = '{{ localized_route('question.marker-theory') }}';
const TEST_SLUG = @json($test->slug);
</script>
@include('components.saved-test-js-persistence', ['mode' => $jsStateMode, 'savedState' => $savedState])
@include('components.saved-test-js-helpers')
@include('components.marker-theory-js')
<script>
const state = {
  items: [],
  correct: 0,
  answered: 0,
};

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
      restored = true;
      state.items.forEach((item) => {
        if (!item.markerTheoryCache || typeof item.markerTheoryCache !== 'object') item.markerTheoryCache = {};
        if (!item.markerTheoryMatch || typeof item.markerTheoryMatch !== 'object') item.markerTheoryMatch = {};
      });
    }
  }

  if (!restored) {
    state.items = QUESTIONS.map(q => {
      const shuffledOptions = Array.isArray(q.options) ? [...q.options] : [];
      shuffle(shuffledOptions);

      return {
        ...q,
        options: shuffledOptions,
        optionsShuffled: true,
        chosen: Array(q.answers.length).fill(''),
        isCorrect: null,
        markerTheoryCache: {},
        markerTheoryMatch: {},
      };
    });
    state.correct = 0;
    state.answered = 0;
  }
  // Ensure restored items get shuffled options at least once
  state.items.forEach((item) => {
    if (!item.optionsShuffled && Array.isArray(item.options)) {
      const shuffled = [...item.options];
      shuffle(shuffled);
      item.options = shuffled;
      item.optionsShuffled = true;
    }
  });

  renderQuestions();
  updateProgress();
  document.getElementById('final-check').classList.remove('hidden');
  document.getElementById('check-all').onclick = () => {
    state.items.forEach((_, i) => onCheck(i));
  };
  persistState(state, true);
}

function renderQuestions() {
  const wrap = document.getElementById('questions');
  wrap.innerHTML = '';
  state.items.forEach((_, i) => {
    const card = document.createElement('article');
    card.className = 'group bg-white rounded-3xl shadow-md hover:shadow-xl border-2 border-gray-100 hover:border-indigo-200 p-4 sm:p-6 lg:p-8 transition-all duration-300 transform hover:-translate-y-1';
    card.dataset.idx = i;
    wrap.appendChild(card);
    renderQuestion(i);
  });
}

function renderQuestion(idx) {
  const q = state.items[idx];
  const card = document.querySelector(`article[data-idx="${idx}"]`);
  const sentence = renderSentence(q, idx);
  card.innerHTML = `
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 mb-4">
      <div class="flex-1">
        <div class="flex items-center gap-2.5 sm:gap-3 mb-2.5 sm:mb-3">
          <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-gradient-to-r from-blue-100 to-indigo-100 text-indigo-700">
            ${q.level || 'N/A'}
          </span>
          <span class="text-xs sm:text-sm text-gray-500 font-medium">${q.tense || 'Grammar'}</span>
        </div>
        <div class="text-base sm:text-xl leading-relaxed text-gray-900 font-medium">${sentence}</div>
        <div id="theory-panel-${idx}" class="mt-2.5 sm:mt-3 hidden"></div>
      </div>
    <div class="flex flex-col items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-br from-indigo-50 to-purple-50 border border-indigo-100 shrink-0 sm:self-start">
        <div class="text-xs text-gray-500 font-medium">Q</div>
        <div class="text-lg font-bold text-indigo-600">${idx + 1}</div>
      </div>
    </div>
    <div class="min-h-8" id="feedback-${idx}">${renderFeedback(q)}</div>
  `;
  if (q.isCorrect === null) {
    card.querySelectorAll('select[data-idx]').forEach(sel => {
      const aIdx = parseInt(sel.dataset.idx);
      const update = () => resizeSelect(sel);
      sel.addEventListener('change', () => {
        q.chosen[aIdx] = sel.value;
        update();
        persistState(state);
      });
      update();
    });
  }
  // Add event delegation for marker theory button clicks
  card.addEventListener('click', (e) => {
    const markerTheoryBtn = e.target.closest('button.marker-theory-btn');
    if (markerTheoryBtn) {
      e.stopPropagation();
      const marker = markerTheoryBtn.dataset.marker;
      const btnIdx = parseInt(markerTheoryBtn.dataset.idx, 10);
      fetchMarkerTheory(btnIdx, marker);
    }
  });
}

function onCheck(idx) {
  const q = state.items[idx];
  if (q.isCorrect !== null) return;
  q.isCorrect = q.answers.every((ans, i) => (q.chosen[i] || '').toLowerCase() === (ans || '').toLowerCase());
  if (q.isCorrect) state.correct += 1;
  state.answered += 1;
  renderQuestion(idx);
  updateProgress();
  persistState(state);
}

function renderFeedback(q) {
  if (q.isCorrect === true) {
    return '<div class="flex items-start gap-3 p-3 sm:p-4 rounded-2xl bg-gradient-to-r from-emerald-50 to-teal-50 border-2 border-emerald-200"><div class="flex-shrink-0 w-6 h-6 rounded-full bg-emerald-500 flex items-center justify-center"><svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg></div><div class="flex-1"><div class="font-semibold text-emerald-800">Correct!</div></div></div>';
  }
  if (q.isCorrect === false) {
    return '<div class="flex items-start gap-3 p-3 sm:p-4 rounded-2xl bg-gradient-to-r from-red-50 to-rose-50 border-2 border-red-200"><div class="flex-shrink-0 w-6 h-6 rounded-full bg-red-500 flex items-center justify-center"><svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></div><div class="flex-1"><div class="font-semibold text-red-800">Incorrect. Correct answer: <span class="font-bold">' + html(q.answers.join(' ')) + '</span></div></div></div>';
  }
  return '';
}

function renderSentence(q, qIdx) {
  let text = q.question;
  q.answers.forEach((ans, i) => {
    let replacement = '';
    if (q.isCorrect === null) {
      const opts = q.options.map(o => `<option value="${html(o)}">${html(o)}</option>`).join('');
      replacement = `<select data-idx="${i}" class="px-3 py-2 border-2 border-indigo-200 rounded-xl focus:outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all font-medium bg-white min-w-20"><option value=""></option>${opts}</select>`;
    } else {
      replacement = `<mark class="px-3 py-1 rounded-lg bg-gradient-to-r from-amber-100 to-yellow-100 font-semibold">${html(q.chosen[i])}</mark>`;
    }
    const regex = new RegExp(`\\{a${i + 1}\\}`);
    const marker = `a${i + 1}`;
    const hint = q.verb_hints && q.verb_hints[marker]
      ? ` <span class="verb-hint text-red-600 text-sm font-bold">( ${html(q.verb_hints[marker])} )</span>`
      : '';
    // Add marker theory button if marker has tags
    const theoryBtn = renderMarkerTheoryButton(marker, qIdx, hasMarkerTags(q, marker));
    // Add marker tags debug display
    const tagsDebug = renderMarkerTagsDebug(q, marker, qIdx);
    text = text.replace(regex, replacement + hint + theoryBtn + tagsDebug);
  });
  return text;
}

function updateProgress() {
  document.getElementById('progress-label').textContent = `${state.answered} / ${state.items.length}`;
  document.getElementById('score-label').textContent = `Accuracy: ${pct(state.correct, state.items.length)}%`;
  document.getElementById('progress-bar').style.width = `${(state.answered / state.items.length) * 100}%`;
  
  const allDone = state.answered === state.items.length;
  document.getElementById('summary').classList.toggle('hidden', !allDone);
  document.getElementById('final-check').classList.toggle('hidden', allDone);
  if (allDone) {
    document.getElementById('summary-text').textContent = `You got ${state.correct} out of ${state.items.length} correct (${pct(state.correct, state.items.length)}%)!`;
    const retryButton = document.getElementById('retry');
    if (retryButton) {
      retryButton.onclick = () => restartJsTest(init, { button: retryButton });
    }
  }
}

function resizeSelect(sel) {
  const span = document.createElement('span');
  span.style.visibility = 'hidden';
  span.style.position = 'absolute';
  span.style.whiteSpace = 'pre';
  span.style.font = getComputedStyle(sel).font;
  span.textContent = sel.options[sel.selectedIndex]?.text || sel.options[0]?.text || '';
  document.body.appendChild(span);
  const width = Math.max(80, span.offsetWidth + 50);
  document.body.removeChild(span);
  sel.style.width = width + 'px';
}

const restartButton = document.getElementById('restart-test');
if (restartButton) {
  restartButton.addEventListener('click', () => restartJsTest(init, { button: restartButton }));
}

init();
</script>
@include('components.sticky-header-scroll')
@endsection
