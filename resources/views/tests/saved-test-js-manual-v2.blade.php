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
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                    </svg>
                    Manual Mode - All Questions
                </div>
                <h1 class="text-3xl sm:text-4xl lg:text-5xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">
                    {{ $test->name }}
                </h1>
                <p class="text-base sm:text-lg text-gray-600 max-w-2xl mx-auto">
                    Type your answers without hints for all questions
                </p>
            </div>
        </header>

        <!-- Navigation Tabs -->
        @include('components.test-mode-nav-v2')

        <!-- Sticky Controls: Search + Progress -->
        <div class="sticky top-0 z-30">
            <div class="space-y-4 rounded-2xl border border-indigo-100 bg-white/90 p-4 sm:p-6 shadow-md backdrop-blur-md">
                @include('components.word-search')
                <div class="rounded-2xl border border-indigo-100 bg-gradient-to-r from-indigo-50 via-white to-purple-50 p-4 shadow-inner">
                    @include('components.saved-test-progress')
                </div>
            </div>
        </div>

        <main class="mt-8 rounded-3xl bg-white shadow-xl border border-gray-100 p-6 sm:p-8 space-y-6">
            <!-- Restart Button -->
            @include('components.saved-test-js-restart-button')

            <!-- Questions Container -->
            <div id="questions" class="space-y-6"></div>

            <!-- Check All Button -->
            <div id="final-check" class="pt-4">
                <button id="check-all" type="button" class="w-full sm:w-auto px-12 py-5 rounded-2xl bg-gradient-to-r from-indigo-600 to-purple-600 text-white text-lg font-semibold shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200">
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
                <div class="bg-gradient-to-br from-emerald-50 to-teal-50 rounded-3xl shadow-xl border-2 border-emerald-200 p-8 text-center">
                    <div class="w-20 h-20 mx-auto mb-6 rounded-full bg-gradient-to-br from-emerald-400 to-teal-500 flex items-center justify-center">
                        <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h2 class="text-3xl font-bold text-gray-900 mb-3">Test Complete! 🎉</h2>
                    <p id="summary-text" class="text-xl text-gray-700 mb-8"></p>
                    <div class="flex flex-col sm:flex-row gap-3 justify-center">
                        <button id="retry" class="group px-8 py-4 rounded-2xl bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-semibold shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200">
                            <span class="flex items-center justify-center">
                                <svg class="w-5 h-5 mr-2 group-hover:rotate-180 transition-transform duration-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                </svg>
                                Try Again
                            </span>
                        </button>
                        <button id="show-wrong" class="px-8 py-4 rounded-2xl border-2 border-gray-200 bg-white text-gray-700 font-semibold hover:border-gray-300 hover:bg-gray-50 transition-all duration-200">Show Mistakes Only</button>
                    </div>
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
</script>
@include('components.saved-test-js-persistence', ['mode' => $jsStateMode, 'savedState' => $savedState])
@include('components.saved-test-js-helpers')
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
    }
  }

  if (!restored) {
    state.items = QUESTIONS.map((q) => ({
      ...q,
      chosen: Array(q.answers.length).fill(''),
      done: false,
      wrongAttempt: false,
      feedback: '',
    }));
    state.correct = 0;
    state.answered = 0;
  }

  renderQuestions();
  updateProgress();
  document.getElementById('final-check').classList.remove('hidden');
  document.getElementById('check-all').onclick = () => {
    state.items.forEach((_, i) => onCheck(i));
  };
  persistState(state, true);
}

function renderQuestions(showOnlyWrong = false) {
  const wrap = document.getElementById('questions');
  wrap.innerHTML = '';

  state.items.forEach((q, idx) => {
    if (showOnlyWrong && (!q.done || !q.wrongAttempt)) return;

    const card = document.createElement('article');
    card.className = 'group bg-white rounded-3xl shadow-md hover:shadow-xl border-2 border-gray-100 hover:border-indigo-200 p-6 sm:p-8 transition-all duration-300 transform hover:-translate-y-1';
    card.dataset.idx = idx;

    const sentence = renderSentence(q, idx);

    card.innerHTML = `
      <div class="flex items-start justify-between gap-4 mb-4">
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
      <div class="min-h-8" id="feedback-${idx}">${renderFeedback(q)}</div>
    `;

    wrap.appendChild(card);
    card.querySelectorAll('input[data-question][data-slot]').forEach(inp => {
      if (!inp.dataset.minWidth) inp.dataset.minWidth = inp.offsetWidth;
      const handle = () => {
        autoResize(inp);
        const qIdx = Number(inp.dataset.question);
        const slot = Number(inp.dataset.slot);
        if (Number.isInteger(qIdx) && Number.isInteger(slot) && state.items[qIdx]) {
          state.items[qIdx].chosen[slot] = inp.value;
          persistState(state);
        }
      };
      inp.addEventListener('input', handle);
      inp.addEventListener('change', handle);
      autoResize(inp);
    });

  });

  const allDone = state.items.every(it => it.done);
  document.getElementById('summary').classList.toggle('hidden', !allDone);
  document.getElementById('final-check').classList.toggle('hidden', allDone);
  if (allDone) {
    document.getElementById('summary-text').textContent = `You got ${state.correct} out of ${state.items.length} correct (${pct(state.correct, state.items.length)}%)!`;
    const retryButton = document.getElementById('retry');
    if (retryButton) {
      retryButton.onclick = () => restartJsTest(init, { button: retryButton });
    }
    document.getElementById('show-wrong').onclick = () => renderQuestions(true);
  }
}

function onCheck(idx) {
  const item = state.items[idx];
  if (item.done) return;
  let allCorrect = true;
  item.answers.forEach((ans, i) => {
    const el = document.getElementById(`input-${idx}-${i}`);
    const val = (el.value || '').trim();
    item.chosen[i] = val;
    if (val.toLowerCase() !== ans.toLowerCase()) {
      allCorrect = false;
      el.classList.add('border-red-400');
      el.classList.remove('border-indigo-200');
    } else {
      el.classList.remove('border-red-400');
      el.classList.add('border-indigo-200');
    }
  });
  if (allCorrect) {
    item.done = true;
    item.feedback = 'correct';
    state.answered += 1;
    if (!item.wrongAttempt) state.correct += 1;
  } else {
    item.wrongAttempt = true;
    item.feedback = 'Incorrect, try again';
  }
  renderQuestions();
  updateProgress();
  persistState(state);
}

function renderFeedback(q) {
  if (q.done || q.feedback === 'correct') {
    return '<div class="flex items-start gap-3 p-4 rounded-2xl bg-gradient-to-r from-emerald-50 to-teal-50 border-2 border-emerald-200"><div class="flex-shrink-0 w-6 h-6 rounded-full bg-emerald-500 flex items-center justify-center"><svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg></div><div class="flex-1"><div class="font-semibold text-emerald-800">Correct!</div></div></div>';
  }
  return q.feedback ? `<div class="flex items-start gap-3 p-4 rounded-2xl bg-gradient-to-r from-red-50 to-rose-50 border-2 border-red-200"><div class="flex-shrink-0 w-6 h-6 rounded-full bg-red-500 flex items-center justify-center"><svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></div><div class="flex-1"><div class="font-semibold text-red-800">${html(q.feedback)}</div></div></div>` : '';
}

function renderSentence(q, idx) {
  let text = q.question;
  q.answers.forEach((ans, i) => {
    let replacement;
    if (q.done) {
      replacement = `<mark class="px-3 py-1 rounded-lg bg-gradient-to-r from-amber-100 to-yellow-100 font-semibold">${html(q.chosen[i])}</mark>`;
    } else {
      const val = q.chosen[i] || '';
      replacement = `<input id="input-${idx}-${i}" data-question="${idx}" data-slot="${i}" class="px-3 py-2 text-center border-2 border-indigo-200 rounded-xl focus:outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all font-medium" style="width:auto;min-width:8rem" placeholder="____" autocomplete="off" value="${html(val)}" />`;
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
  const min = parseFloat(el.dataset.minWidth || el.offsetWidth)*0.2;
  const span = document.createElement('span');
  span.style.visibility = 'hidden';
  span.style.position = 'absolute';
  span.style.whiteSpace = 'pre';
  span.style.font = getComputedStyle(el).font;
  span.textContent = el.value || '';
  document.body.appendChild(span);
  const width = span.offsetWidth + 8;
  document.body.removeChild(span);
  el.style.width = Math.max(min, width) + 'px';
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

const restartButton = document.getElementById('restart-test');
if (restartButton) {
  restartButton.addEventListener('click', () => restartJsTest(init, { button: restartButton }));
}

init();
</script>
@endsection
