@extends('layouts.engram')

@section('title', $test->name)

@section('content')
<div class="mx-auto max-w-3xl px-4 py-8 text-stone-800" id="quiz-app">
    <header class="mb-6">
        <h1 class="text-2xl sm:text-3xl font-bold text-stone-900">{{ $test->name }}</h1>
        <p class="text-sm text-stone-600 mt-1">Введи відповідь, використовуючи підказки.</p>
    </header>

    @include('components.test-mode-nav')

    @include('components.word-search')
    @include('components.saved-test-progress', ['progress' => '1 / 0'])

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

  #question-card article ul{
    min-width: 100px;

  }
</style>
<script>
const QUESTIONS = @json($questionData);
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

function init(forceFresh = false) {
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
      ${q.isCorrect === null ? '<div class="mt-3"><button id="check" class="px-4 py-2 rounded-xl bg-stone-900 text-white">Перевірити</button></div>' : ''}
      <div class="mt-2 h-5" id="feedback">${renderFeedback(q)}</div>
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
  document.getElementById('score-label').textContent = `Точність: ${pct(state.correct, state.items.length)}%`;
  document.getElementById('progress-bar').style.width = `${(answered / state.items.length) * 100}%`;
}

function showSummary() {
  document.getElementById('question-wrap').classList.add('hidden');
  const summary = document.getElementById('summary');
  summary.classList.remove('hidden');
  document.getElementById('summary-text').textContent = `Правильних відповідей: ${state.correct} із ${state.items.length} (${pct(state.correct, state.items.length)}%).`;
  document.getElementById('retry').onclick = () => init(true);
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
    htmlStr += `<p class="p-2 bg-stone-50 rounded"><strong>ChatGPT:</strong> <span class="whitespace-pre-line">${html(q.hints.chatgpt)}</span></p>`;
  }
  if (q.hints.gemini) {
    htmlStr += `<p class="p-2 bg-stone-50 rounded"><strong>Gemini:</strong> <span class="whitespace-pre-line">${html(q.hints.gemini)}</span></p>`;
  }
  htmlStr += `<button type="button" id="refresh-hint" class="mt-2 text-xs text-blue-600 underline block w-fit">Refresh</button>`;
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
        options.push(`<li data-value="${value}" class="suggestion-item cursor-pointer px-2 py-1 hover:bg-stone-100">${value}</li>`);
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
    return '<div class="text-sm text-emerald-700">✅ Вірно!</div>';
  }
  if (q.isCorrect === false) {
    let htmlStr = '<div class="text-sm text-rose-700">❌ Невірно. Правильна відповідь: <b>' + html(q.answers.join(' ')) + '</b></div>';
    if (q.explanation) {
      htmlStr += '<div class="mt-1 text-xs bg-blue-50 text-blue-800 rounded px-2 py-1">' + html(q.explanation) + '</div>';
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
          return `<span class=\"relative inline-flex flex-col items-start gap-1\"><input id=\"${inputId}\"  autocomplete="off" type=\"text\" data-idx=\"${i}\" data-word=\"${j}\" class=\"px-1 py-0.5 text-center border-b border-stone-400 focus:outline-none\" style=\"width:auto\" value=\"${html(w)}\" data-list=\"${listId}\"><ul id=\"${listId}\" data-input=\"${inputId}\" data-has-options=\"0\" role=\"listbox\" class=\"suggestion-list hidden absolute left-0 top-full mt-1 z-10 w-full max-h-40 overflow-auto rounded border border-stone-300 bg-white text-sm text-stone-700 shadow list-none p-0\"></ul></span>`;
        })
        .join(' ');
      const addBtn = `<button type=\"button\" data-add=\"${i}\" class=\"ml-1 px-2 py-0.5 rounded bg-stone-200 self-start flex-shrink-0\">+</button>`;
      const removeBtn = words.length > 1
        ? `<button type=\"button\" data-remove=\"${i}\" class=\"ml-1 px-2 py-0.5 rounded bg-stone-200 self-start flex-shrink-0\">-</button>`
        : '';
      replacement = `<span class=\"inline-flex items-start gap-1\">${inputs}${addBtn}${removeBtn}</span>`;
    } else {
      replacement = `<mark class=\"px-1 py-0.5 rounded bg-amber-100\">${html(q.inputs[i].join(' '))}</mark>`;
    }
    const regex = new RegExp(`\\{a${i + 1}\\}`);
    const marker = `a${i + 1}`;
    const hint = q.verb_hints && q.verb_hints[marker]
      ? ` <span class="verb-hint text-red-700 text-xs font-bold">(${html(q.verb_hints[marker])})</span>`
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
  const width = span.offsetWidth + 35;
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

init();
</script>
@endsection
