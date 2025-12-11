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
                    Input Mode - All Questions
                </div>
                <h1 class="text-3xl sm:text-4xl lg:text-5xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">
                    {{ $test->name }}
                </h1>
                <p class="text-base sm:text-lg text-gray-600 max-w-2xl mx-auto">
                    Type your answers using autocomplete hints for all questions
                </p>
            </div>
        </header>

        <!-- Navigation Tabs -->
        @include('components.test-mode-nav-v2')

        <!-- Word Search Component -->
        @include('components.word-search')

        <!-- Progress Tracker with Modern Design -->
        @include('components.saved-test-progress')

        <!-- Restart Button -->
        @include('components.saved-test-js-restart-button')

        <!-- Questions Container -->
        <div id="questions" class="space-y-6"></div>

        <!-- Check All Button -->
        <div id="final-check" class="mt-8">
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
    state.items = QUESTIONS.map(q => ({
      ...q,
      inputs: Array(q.answers.length).fill(null).map(() => ['']),
      isCorrect: null,
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

function renderQuestions() {
  const wrap = document.getElementById('questions');
  wrap.innerHTML = '';
  state.items.forEach((_, i) => {
    const card = document.createElement('article');
    card.className = 'group bg-white rounded-3xl shadow-md hover:shadow-xl border-2 border-gray-100 hover:border-indigo-200 p-6 sm:p-8 transition-all duration-300 transform hover:-translate-y-1';
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
    <div class="flex items-start justify-between gap-4 mb-4">
      <div class="flex-1">
        <div class="flex items-center gap-3 mb-3">
          <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-gradient-to-r from-blue-100 to-indigo-100 text-indigo-700">
            ${q.level || 'N/A'}
          </span>
          <span class="text-sm text-gray-500 font-medium">${q.tense || 'Grammar'}</span>
        </div>
        <div class="text-lg sm:text-xl leading-relaxed text-gray-900 font-medium">${sentence}</div>
        ${q.theory_block ? `<div class="mt-3">
          <button type="button" class="theory-btn inline-flex items-center text-sm text-emerald-600 hover:text-emerald-700 font-medium transition-colors" data-theory-idx="${idx}">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
            </svg>
            Show Theory
          </button>
          <div id="theory-panel-${idx}" class="mt-3 hidden"></div>
        </div>` : ''}
      </div>
      <div class="flex flex-col items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-br from-indigo-50 to-purple-50 border border-indigo-100 shrink-0">
        <div class="text-xs text-gray-500 font-medium">Q</div>
        <div class="text-lg font-bold text-indigo-600">${idx + 1}</div>
      </div>
    </div>
    <div class="min-h-8" id="feedback-${idx}">${renderFeedback(q)}</div>
  `;
  
  // Theory button handler
  const theoryBtn = card.querySelector('.theory-btn');
  if (theoryBtn && q.theory_block) {
    theoryBtn.addEventListener('click', () => toggleTheoryPanel(q, idx));
  }
  
  if (q.isCorrect === null) {
    card.querySelectorAll('input[data-idx][data-word]').forEach(inp => {
      const aIdx = parseInt(inp.dataset.idx);
      const wIdx = parseInt(inp.dataset.word);
      inp.addEventListener('keydown', e => {
        if (e.key === ' ') e.preventDefault();
      });
      const handle = () => {
        const val = inp.value.replace(/\s+/g, '');
        if (val !== inp.value) inp.value = val;
        q.inputs[aIdx][wIdx] = val;
        fetchSuggestions(inp, idx, aIdx, wIdx);
        autoResize(inp);
        persistState(state);
      };
      inp.addEventListener('input', handle);
      inp.addEventListener('change', handle);
      autoResize(inp);
      fetchSuggestions(inp, idx, aIdx, wIdx);
      const listId = inp.dataset.list;
      const getListEl = () => (listId ? card.querySelector(`#${listId}`) : null);
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
          q.inputs[aIdx][wIdx] = sanitized;
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
    card.querySelectorAll('button[data-add]').forEach(btn => {
      btn.addEventListener('click', () => { addWord(q, parseInt(btn.dataset.add)); renderQuestion(idx); });
    });
    card.querySelectorAll('button[data-remove]').forEach(btn => {
      btn.addEventListener('click', () => { removeWord(q, parseInt(btn.dataset.remove)); renderQuestion(idx); });
    });
  }
}

function onCheck(idx) {
  const q = state.items[idx];
  if (q.isCorrect !== null) return;
  const valParts = q.inputs.map(words => words.join(' ').trim());
  q.isCorrect = q.answers.every((ans, i) => valParts[i].toLowerCase() === (ans || '').toLowerCase());
  if (q.isCorrect) state.correct += 1;
  state.answered += 1;
  renderQuestion(idx);
  updateProgress();
  persistState(state);
}

function addWord(q, idx) {
  q.inputs[idx].push('');
  persistState(state);
}

function removeWord(q, idx) {
  if (q.inputs[idx].length > 1) {
    q.inputs[idx].pop();
    persistState(state);
  }
}

function fetchSuggestions(input, qIdx, idx, widx) {
  const val = input.value.trim();
  const listId = `opts-${qIdx}-${idx}-${widx}`;
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
    return '<div class="flex items-start gap-3 p-4 rounded-2xl bg-gradient-to-r from-red-50 to-rose-50 border-2 border-red-200"><div class="flex-shrink-0 w-6 h-6 rounded-full bg-red-500 flex items-center justify-center"><svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></div><div class="flex-1"><div class="font-semibold text-red-800">Incorrect. Correct answer: <span class="font-bold">' + html(q.answers.join(' ')) + '</span></div></div></div>';
  }
  return '';
}

function renderSentence(q, qIdx) {
  let text = q.question;
  q.answers.forEach((ans, i) => {
    let replacement = '';
    if (q.isCorrect === null) {
      const words = q.inputs[i];
      const inputs = words.map((w, j) => {
          const inputId = `input-${qIdx}-${i}-${j}`;
          const listId = `opts-${qIdx}-${i}-${j}`;
          return `<span class="relative inline-flex flex-col items-start gap-1"><input id="${inputId}" autocomplete="off" type="text" data-idx="${i}" data-word="${j}" class="px-3 py-2 text-center border-2 border-indigo-200 rounded-xl focus:outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all font-medium" style="width:auto; min-width:80px" value="${html(w)}" data-list="${listId}"><ul id="${listId}" data-input="${inputId}" data-has-options="0" role="listbox" class="suggestion-list hidden absolute left-0 top-full mt-2 z-10 w-full max-h-60 overflow-auto rounded-2xl border-2 border-indigo-200 bg-white shadow-xl list-none p-2"></ul></span>`;
        }).join(' ');
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

function toggleTheoryPanel(q, idx) {
  const panel = document.getElementById(`theory-panel-${idx}`);
  if (!panel) return;
  
  if (panel.classList.contains('hidden')) {
    renderTheoryPanel(q, idx);
    panel.classList.remove('hidden');
  } else {
    panel.classList.add('hidden');
  }
}

function renderTheoryPanel(q, idx) {
  const panel = document.getElementById(`theory-panel-${idx}`);
  if (!panel || !q.theory_block) return;
  
  const block = q.theory_block;
  let content = '';
  
  try {
    const body = typeof block.body === 'string' ? JSON.parse(block.body) : block.body;
    
    if (body.title) {
      content += `<h4 class="font-semibold text-emerald-900 mb-2">${html(body.title)}</h4>`;
    }
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
          content += `<li class="text-sm text-emerald-800">${item}</li>`;
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

const restartButton = document.getElementById('restart-test');
if (restartButton) {
  restartButton.addEventListener('click', () => restartJsTest(init, { button: restartButton }));
}

init();
</script>
@endsection
