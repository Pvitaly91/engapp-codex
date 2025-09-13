@extends('layouts.app')

@section('title', $test->name)

@section('content')
<div class="mx-auto max-w-3xl px-4 py-8 text-stone-800" id="quiz-app">
    <header class="mb-6">
        <h1 class="text-2xl sm:text-3xl font-bold text-stone-900">{{ $test->name }}</h1>
        <p class="text-sm text-stone-600 mt-1">Перевіряй відповіді одразу, клавіші 1–4 працюють для активного запитання.</p>
    </header>

    @include('components.word-search')

    <div class="mb-6">
        <div class="flex items-center justify-between text-sm">
            <span id="progress-label" class="text-stone-600">1 / 0</span>
            <span id="score-label" class="text-stone-600">Точність: 0%</span>
        </div>
        <div class="w-full h-2 bg-stone-200 rounded-full mt-2">
            <div id="progress-bar" class="h-2 bg-stone-900 rounded-full" style="width:0%"></div>
        </div>
    </div>

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

<script>
const QUESTIONS = @json($questionData);
const CSRF_TOKEN = '{{ csrf_token() }}';
const EXPLAIN_URL = '{{ route('question.explain') }}';
</script>
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

function init() {
  state.items = QUESTIONS.map((q) => {
    const opts = [...q.options];
    shuffle(opts);
    return {
      ...q,
      options: opts,
      chosen: null,
      isCorrect: null,
      explanation: '',
    };
  });
  state.current = 0;
  state.correct = 0;
  document.getElementById('summary').classList.add('hidden');
  document.getElementById('question-wrap').classList.remove('hidden');
  render();
  updateProgress();
  hookGlobalEvents();
}

function render() {
  const wrap = document.getElementById('question-card');
  const q = state.items[state.current];
  const sentence = q.question.replace(/\{a\d+\}/, `<mark class="px-1 py-0.5 rounded bg-amber-100">${q.chosen ?? '____'}</mark>`);
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
      <div class="mt-2 h-5" id="feedback">${renderFeedback(q)}</div>
    </article>
  `;

  document.getElementById('prev').disabled = state.current === 0;
  document.getElementById('next').disabled = q.isCorrect === null;
  document.getElementById('help').addEventListener('click', () => fetchHints(q));
  renderHints(q);
}

function renderOptionButton(q, opt, i) {
  const chosen = q.chosen === opt;
  const answered = q.isCorrect !== null;
  const base = 'w-full text-left px-3 py-2 rounded-xl border transition';
  let cls = 'border-stone-300 hover:border-stone-400 bg-white';
  if (answered) {
    if (opt === q.answer) cls = 'border-emerald-300 bg-emerald-50';
    if (chosen && opt !== q.answer) cls = 'border-rose-300 bg-rose-50';
  } else if (chosen) {
    cls = 'border-stone-900';
  }
  const hotkey = i + 1;
  return `
    <button type="button" class="${base} ${cls}" data-opt="${html(opt)}" title="Натисни ${hotkey}" ${answered ? 'disabled' : ''}>
      <span class="mr-2 inline-flex h-5 w-5 items-center justify-center rounded-md border text-xs">${hotkey}</span>
      ${opt}
    </button>
  `;
}

function renderFeedback(q) {
  if (q.isCorrect === true) {
    return '<div class="text-sm text-emerald-700">✅ Вірно!</div>';
  }
  if (q.isCorrect === false) {
    let htmlStr = '<div class="text-sm text-rose-700">❌ Невірно. Правильна відповідь: <b>' + html(q.answer) + '</b></div>';
    if (q.explanation) {
      htmlStr += '<div class="mt-1 text-xs bg-blue-50 text-blue-800 rounded px-2 py-1">' + html(q.explanation) + '</div>';
    }
    return htmlStr;
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
  if (q.isCorrect !== null) return;
  q.chosen = opt;
  q.isCorrect = (opt === q.answer);
  if (q.isCorrect) {
    state.correct += 1;
  } else {
    fetchExplanation(q, opt);
  }
  render();
  updateProgress();
}

document.getElementById('prev').addEventListener('click', () => {
  if (state.current > 0) {
    state.current -= 1;
    render();
    updateProgress();
  }
});

document.getElementById('next').addEventListener('click', () => {
  if (state.current < state.items.length - 1) {
    state.current += 1;
    render();
    updateProgress();
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
  document.getElementById('retry').onclick = init;
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
    })
    .catch((e) => console.error(e))
    .finally(() => showLoader(false));
}

function hookGlobalEvents() {
  document.addEventListener('keydown', (e) => {
    const n = Number(e.key);
    if (!Number.isInteger(n) || n < 1 || n > 4) return;
    const q = state.items[state.current];
    if (!q || q.isCorrect !== null) return;
    const opt = q.options[n - 1];
    if (opt) onChoose(opt);
  });
}

function shuffle(arr) {
  for (let i = arr.length - 1; i > 0; i--) {
    const j = (Math.random() * (i + 1)) | 0;
    [arr[i], arr[j]] = [arr[j], arr[i]];
  }
}
function pct(a, b) { return Math.round((a / (b || 1)) * 100); }
function html(str) {
  return String(str)
    .replaceAll('&', '&amp;')
    .replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;')
    .replaceAll('"', '&quot;')
    .replaceAll("'", '&#039;');
}

init();
</script>
@endsection
