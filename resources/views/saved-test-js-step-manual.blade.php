@extends('layouts.engram')

@section('title', $test->name)

@section('content')
<div class="mx-auto max-w-3xl px-4 py-8 text-stone-800" id="quiz-app">
    <header class="mb-6">
        <h1 class="text-2xl sm:text-3xl font-bold text-stone-900">{{ $test->name }}</h1>
        <p class="text-sm text-stone-600 mt-1">Введи відповідь без підказок.</p>
    </header>

    <nav class="mb-6 flex gap-2 text-sm">
        <a href="{{ route('saved-test.js', $test->slug) }}" class="px-3 py-1 rounded-lg border border-stone-300 {{ request()->routeIs('saved-test.js') ? 'bg-stone-900 text-white' : '' }}">Карти</a>
        <a href="{{ route('saved-test.js.step', $test->slug) }}" class="px-3 py-1 rounded-lg border border-stone-300 {{ request()->routeIs('saved-test.js.step') ? 'bg-stone-900 text-white' : '' }}">Step</a>
        <a href="{{ route('saved-test.js.manual', $test->slug) }}" class="px-3 py-1 rounded-lg border border-stone-300 {{ request()->routeIs('saved-test.js.manual') ? 'bg-stone-900 text-white' : '' }}">Manual</a>
        <a href="{{ route('saved-test.js.step-manual', $test->slug) }}" class="px-3 py-1 rounded-lg border border-stone-300 {{ request()->routeIs('saved-test.js.step-manual') ? 'bg-stone-900 text-white' : '' }}">Step Manual</a>
        <a href="{{ route('saved-test.js.step-input', $test->slug) }}" class="px-3 py-1 rounded-lg border border-stone-300 {{ request()->routeIs('saved-test.js.step-input') ? 'bg-stone-900 text-white' : '' }}">Step Input</a>
        <a href="{{ route('saved-test.js.input', $test->slug) }}" class="px-3 py-1 rounded-lg border border-stone-300 {{ request()->routeIs('saved-test.js.input') ? 'bg-stone-900 text-white' : '' }}">Input All</a>
    </nav>

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
    chosen: Array(q.answers.length).fill(''),
    done: false,
    feedback: '',
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
    <article class="rounded-2xl border border-stone-200 bg-white p-4" data-idx="${state.current}">
      <div class="flex items-start justify-between gap-3">
        <div>
          <div class="text-sm text-stone-500">${q.level} • ${q.tense}</div>
          <div class="mt-1 text-base leading-relaxed text-stone-900">${sentence}</div>
        </div>
        <div class="text-xs text-stone-500 shrink-0">[${state.current + 1}/${state.items.length}]</div>
      </div>
      <div class="mt-3 flex gap-2">
        <button id="check" class="px-4 py-2 rounded-xl bg-stone-900 text-white" ${q.done ? 'disabled' : ''}>Перевірити</button>
      </div>
      <div class="mt-2 h-5" id="feedback">${renderFeedback(q)}</div>
    </article>
  `;
  document.getElementById('prev').disabled = state.current === 0;
  document.getElementById('next').disabled = !q.done;
  document.getElementById('check').addEventListener('click', onCheck);
  wrap.querySelectorAll('input').forEach(inp => {
    inp.addEventListener('keydown', (e) => { if (e.key === 'Enter') onCheck(); });
  });
  wrap.querySelector('input')?.focus();
}

function onCheck() {
  const q = state.items[state.current];
  if (q.done) return;
  let allCorrect = true;
  q.answers.forEach((ans, i) => {
    const el = document.getElementById(`input-${i}`);
    const val = (el.value || '').trim();
    q.chosen[i] = val;
    if (val.toLowerCase() !== ans.toLowerCase()) {
      allCorrect = false;
      el.classList.add('border-rose-400');
    } else {
      el.classList.remove('border-rose-400');
    }
  });
  if (allCorrect) {
    q.done = true;
    q.feedback = 'correct';
    state.correct += 1;
  } else {
    q.feedback = 'Невірно, спробуй ще раз';
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
  const answered = state.items.filter(it => it.done).length;
  document.getElementById('progress-label').textContent = `${state.current + 1} / ${state.items.length}`;
  document.getElementById('score-label').textContent = `Точність: ${pct(state.correct, state.items.length)}%`;
  document.getElementById('progress-bar').style.width = `${(answered / state.items.length) * 100}%`;
}

function renderSentence(q) {
  let text = q.question;
  q.answers.forEach((ans, i) => {
    let replacement;
    if (q.done) {
      replacement = `<mark class=\"px-1 py-0.5 rounded bg-amber-100\">${html(q.chosen[i])}</mark>`;
    } else {
      const val = q.chosen[i] || '';
      replacement = `<input id=\"input-${i}\" class=\"w-24 text-center bg-transparent border-0 border-b border-stone-400 focus:outline-none\" placeholder=\"____\" autocomplete=\"off\" value=\"${html(val)}\" />`;
    }
    const regex = new RegExp(`\\{a${i + 1}\\}`);
    const marker = `a${i + 1}`;
    const hint = q.verb_hints && q.verb_hints[marker]
      ? ` <span class=\\"verb-hint text-red-700 text-xs font-bold\\"><strong style='color:red'>(${html(q.verb_hints[marker])})</strong></span>`
      : '';
    text = text.replace(regex, replacement + hint);
  });
  return text;
}

function renderFeedback(q) {
  if (q.done || q.feedback === 'correct') {
    return '<div class="text-sm text-emerald-700 ">✅ Вірно!</div>';
  }
  return q.feedback ? `<div class="text-sm text-rose-700">${html(q.feedback)}</div>` : '';
}

function showSummary() {
  document.getElementById('question-wrap').classList.add('hidden');
  const summary = document.getElementById('summary');
  summary.classList.remove('hidden');
  document.getElementById('summary-text').textContent = `Правильних відповідей: ${state.correct} із ${state.items.length} (${pct(state.correct, state.items.length)}%).`;
  document.getElementById('retry').onclick = init;
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
