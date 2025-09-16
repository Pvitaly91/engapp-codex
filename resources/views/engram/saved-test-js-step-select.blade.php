@extends('layouts.engram')

@section('title', $test->name)

@section('content')
<div class="mx-auto max-w-3xl px-4 py-8 text-stone-800" id="quiz-app">
    <header class="mb-6">
        <h1 class="text-2xl sm:text-3xl font-bold text-stone-900">{{ $test->name }}</h1>
        <p class="text-sm text-stone-600 mt-1">Вибери правильну відповідь зі списку.</p>
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
  #question-card select{
    min-width: 60px;
  }
  #question-card article{
        background-color: #fef6db;
  }
</style>
<script>
const QUESTIONS = @json($questionData);
const CSRF_TOKEN = '{{ csrf_token() }}';
const EXPLAIN_URL = '{{ route('question.explain') }}';
</script>
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

function init() {
  state.items = QUESTIONS.map((q) => ({
    ...q,
    chosen: Array(q.answers.length).fill(''),
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
      ${renderFeedback(q)}
    </article>
  `;

  document.getElementById('prev').disabled = state.current === 0;
  document.getElementById('next').disabled = q.isCorrect === null;
  document.getElementById('help').addEventListener('click', () => fetchHints(q));
  renderHints(q);
  if (q.isCorrect === null) {
    document.getElementById('check').addEventListener('click', onCheck);
    document.querySelectorAll('select[data-idx]').forEach(sel => {
      const idx = parseInt(sel.dataset.idx);
      const update = () => resizeSelect(sel);
      sel.addEventListener('change', () => {
        q.chosen[idx] = sel.value;
        update();
      });
      update();
    });
    const first = document.querySelector('select[data-idx]');
    if (first) first.focus();
  }
}

function onCheck() {
  const q = state.items[state.current];
  if (q.isCorrect !== null) return;
  q.isCorrect = q.answers.every((ans, i) => (q.chosen[i] || '').toLowerCase() === (ans || '').toLowerCase());
  if (q.isCorrect) {
    state.correct += 1;
  } else {
    fetchExplanation(q, q.chosen.join(' '));
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

function renderFeedback(q) {
  if (q.isCorrect === true) {
    return '<div class="text-sm text-emerald-700">✅ Вірно!</div>';
  }
  if (q.isCorrect === false) {
    let htmlStr = '<div class="text-sm text-rose-700">❌ Невірно. Правильна відповідь: <b>' + html(q.answers.join(' ')) + '</b></div>';
    if (q.explanation) {
      htmlStr += '<div class="whitespace-pre-line" style="background-color:#ffcccc; padding:10px; border-radius:5px; matgin-top:20px;">' + html(q.explanation) + '</div>';
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
      const opts = q.options.map(o => `<option value=\"${html(o)}\">${html(o)}</option>`).join('');
      replacement = `<select data-idx=\"${i}\" class=\"px-1 py-0.5 border-b border-stone-400\"><option value=\"\"></option>${opts}</select>`;
    } else {
      replacement = `<mark class=\"px-1 py-0.5 rounded bg-amber-100\">${html(q.chosen[i])}</mark>`;
    }
    const regex = new RegExp(`\\{a${i + 1}\\}`);
    const marker = `a${i + 1}`;
    const hint = q.verb_hints && q.verb_hints[marker]
      ? ` <span class=\"verb-hint text-red-700 text-xs font-bold\">(${html(q.verb_hints[marker])})</span>`
      : '';
    text = text.replace(regex, replacement + hint);
  });
  return text;
}

init();
</script>
@endsection

