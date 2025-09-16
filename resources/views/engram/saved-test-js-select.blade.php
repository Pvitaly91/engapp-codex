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
    @include('components.saved-test-progress')

    <div id="questions" class="space-y-4"></div>

    <div id="final-check" class="mt-6"><button id="check-all" type="button" class="px-4 py-2 rounded-xl bg-stone-900 text-white">Перевірити всі</button></div>

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
<style>
  #questions select{
    min-width: 60px;
  }
</style>
<script>
const QUESTIONS = @json($questionData);
</script>
@include('components.saved-test-js-helpers')
<script>
const state = {
  items: [],
  correct: 0,
  answered: 0,
};

function init() {
  state.items = QUESTIONS.map(q => ({
    ...q,
    chosen: Array(q.answers.length).fill(''),
    isCorrect: null,
  }));
  state.correct = 0;
  state.answered = 0;
  renderQuestions();
  updateProgress();
  document.getElementById('final-check').classList.remove('hidden');
  document.getElementById('check-all').onclick = () => {
    state.items.forEach((_, i) => onCheck(i));
  };
}

function renderQuestions() {
  const wrap = document.getElementById('questions');
  wrap.innerHTML = '';
  state.items.forEach((_, i) => {
    const card = document.createElement('article');
    card.className = 'rounded-2xl border border-stone-200 bg-white p-4';
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
    <div class="flex items-start justify-between gap-3">
      <div>
        <div class="text-sm text-stone-500">${q.level} • ${q.tense}</div>
        <div class="mt-1 text-base leading-relaxed text-stone-900">${sentence}</div>
      </div>
      <div class="text-xs text-stone-500 shrink-0">[${idx + 1}/${state.items.length}]</div>
    </div>
    <div class="mt-2 h-5" id="feedback-${idx}">${renderFeedback(q)}</div>
  `;
  if (q.isCorrect === null) {
    card.querySelectorAll('select[data-idx]').forEach(sel => {
      const aIdx = parseInt(sel.dataset.idx);
      const update = () => resizeSelect(sel);
      sel.addEventListener('change', () => { q.chosen[aIdx] = sel.value; update(); });
      update();
    });
  }
}

function onCheck(idx) {
  const q = state.items[idx];
  if (q.isCorrect !== null) return;
  q.isCorrect = q.answers.every((ans, i) => (q.chosen[i] || '').toLowerCase() === (ans || '').toLowerCase());
  if (q.isCorrect) state.correct += 1;
  state.answered += 1;
  renderQuestion(idx);
  updateProgress();
}

function renderFeedback(q) {
  if (q.isCorrect === true) {
    return '<div class="text-sm text-emerald-700">✅ Вірно!</div>';
  }
  if (q.isCorrect === false) {
    return '<div class="text-sm text-rose-700">❌ Невірно. Правильна відповідь: <b>' + html(q.answers.join(' ')) + '</b></div>';
  }
  return '';
}

function renderSentence(q, qIdx) {
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

function updateProgress() {
  const label = document.getElementById('progress-label');
  label.textContent = `${state.answered} / ${state.items.length}`;
  const score = document.getElementById('score-label');
  const percent = state.answered ? pct(state.correct, state.items.length) : 0;
  score.textContent = `Точність: ${percent}%`;
  document.getElementById('progress-bar').style.width = `${(state.answered / state.items.length) * 100}%`;
  if (state.answered === state.items.length) {
    document.getElementById('summary').classList.remove('hidden');
    document.getElementById('summary-text').textContent = `Правильних відповідей: ${state.correct} із ${state.items.length} (${pct(state.correct, state.items.length)}%).`;
    document.getElementById('retry').onclick = init;
    document.getElementById('final-check').classList.add('hidden');
  }
}

init();
</script>
@endsection

