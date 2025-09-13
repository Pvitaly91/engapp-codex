@extends('layouts.app')

@section('title', $test->name)

@section('content')
<div class="mx-auto max-w-3xl px-4 py-8 text-stone-800" id="quiz-app">
    <header class="mb-6">
        <h1 class="text-2xl sm:text-3xl font-bold text-stone-900">{{ $test->name }}</h1>
        <p class="text-sm text-stone-600 mt-1">Введи відповідь, використовуючи підказки.</p>
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

function init() {
  state.items = QUESTIONS.map((q) => ({
    ...q,
    words: [''],
    input: '',
    isCorrect: null,
    explanation: '',
  }));
  state.current = 0;
  state.correct = 0;
  document.getElementById('summary').classList.add('hidden');
  document.getElementById('question-wrap').classList.remove('hidden');
  render();
  updateProgress();
}

function buildInputs(q) {
  const fields = q.words
    .map((w, i) => `
      <span class="inline-flex items-center">
        <input type="text" data-idx="${i}" class="w-20 px-1 py-0.5 text-center border-b border-stone-400 focus:outline-none" list="opts-${state.current}-${i}" value="${html(w)}">
        <datalist id="opts-${state.current}-${i}"></datalist>
      </span>`)
    .join(' ');
  return `<span id="builder" class="inline-flex items-center gap-1">${fields}<button type="button" id="add-word" class="ml-1 px-2 py-1 rounded border border-stone-300">+</button><button type="button" id="remove-word" class="px-2 py-1 rounded border border-stone-300">-</button></span>`;
}

function render() {
  const wrap = document.getElementById('question-card');
  const q = state.items[state.current];
  const hint = q.verb_hint ? ` <span class="verb-hint text-red-700 text-xs font-bold">(${html(q.verb_hint)})</span>` : '';
  const blank = q.isCorrect === null
    ? buildInputs(q) + hint
    : `<mark class="px-1 py-0.5 rounded bg-amber-100">${html(q.words.join(' '))}</mark>` + hint;
  const sentence = q.question.replace(/\{a\d+\}/, blank);
  wrap.innerHTML = `
    <article class="rounded-2xl border border-stone-200 bg-white p-4 focus-within:ring-2 ring-stone-900/20 outline-none" data-idx="${state.current}">
      <div class="flex items-start justify-between gap-3">
        <div>
          <div class="text-sm text-stone-500">${q.level} • ${q.tense}</div>
          <div class="mt-1 text-base leading-relaxed text-stone-900">${sentence}</div>
          <button type="button" id="help" class="text-xs text-blue-600 underline mt-1">Help</button>
          <div id="hints" class="text-sm text-gray-600 mt-1"></div>
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
    document.querySelectorAll('#builder input').forEach((inp) => {
      const idx = parseInt(inp.dataset.idx);
      inp.addEventListener('input', () => {
        q.words[idx] = inp.value;
        fetchSuggestions(inp, idx);
      });
      inp.addEventListener('keydown', (e) => { if (e.key === 'Enter') onCheck(); });
      fetchSuggestions(inp, idx);
    });
    document.getElementById('add-word').addEventListener('click', () => { q.words.push(''); render(); });
    document.getElementById('remove-word').addEventListener('click', () => { if (q.words.length > 1) { q.words.pop(); render(); } });
    const first = document.querySelector('#builder input');
    if (first) first.focus();
  }
}

function onCheck() {
  const q = state.items[state.current];
  if (q.isCorrect !== null) return;
  const val = q.words.join(' ').trim();
  q.input = val;
  q.isCorrect = val.toLowerCase() === q.answer.toLowerCase();
  if (q.isCorrect) {
    state.correct += 1;
  } else {
    fetchExplanation(q, val);
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
    .catch((e) => console.error(e));
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
    htmlStr += `<p><strong>ChatGPT:</strong> <span class="whitespace-pre-line">${html(q.hints.chatgpt)}</span></p>`;
  }
  if (q.hints.gemini) {
    htmlStr += `<p><strong>Gemini:</strong> <span class="whitespace-pre-line">${html(q.hints.gemini)}</span></p>`;
  }
  htmlStr += `<button type="button" id="refresh-hint" class="text-xs text-blue-600 underline">Refresh</button>`;
  el.innerHTML = htmlStr;
  document.getElementById('refresh-hint').addEventListener('click', () => fetchHints(q, true));
}

function fetchExplanation(q, given) {
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
    .catch((e) => console.error(e));
}

function fetchSuggestions(input, idx) {
  const query = input.value.trim();
  const listId = `opts-${state.current}-${idx}`;
  const dl = document.getElementById(listId);
  if (!query) { dl.innerHTML = ''; return; }
  fetch('/api/search?lang=en&q=' + encodeURIComponent(query))
    .then(res => res.json())
    .then(data => { dl.innerHTML = data.map(it => `<option value="${html(it.en)}"></option>`).join(''); });
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
