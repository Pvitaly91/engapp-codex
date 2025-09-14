@extends('layouts.engram')

@section('title', $test->name)

@section('content')
<div class="mx-auto max-w-3xl px-4 py-8 text-stone-800" id="quiz-app">
    <header class="mb-6">
        <h1 class="text-2xl sm:text-3xl font-bold text-stone-900">{{ $test->name }}</h1>
        <p class="text-sm text-stone-600 mt-1">Введи відповідь без підказок.</p>
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
            <div class="mt-3 flex gap-3">
                <button id="retry" class="px-4 py-2 rounded-xl bg-stone-900 text-white">Пройти ще раз</button>
                <button id="show-wrong" class="px-4 py-2 rounded-xl border border-stone-300">Показати тільки помилки</button>
            </div>
        </div>
    </div>
</div>

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
  state.items = QUESTIONS.map((q) => ({
    ...q,
    chosen: Array(q.answers.length).fill(''),
    done: false,
    wrongAttempt: false,
    feedback: '',
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

function renderQuestions(showOnlyWrong = false) {
  const wrap = document.getElementById('questions');
  wrap.innerHTML = '';

  state.items.forEach((q, idx) => {
    if (showOnlyWrong && (!q.done || !q.wrongAttempt)) return;

    const card = document.createElement('article');
    card.className = 'rounded-2xl border border-stone-200 bg-white p-4';
    card.dataset.idx = idx;

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

    wrap.appendChild(card);
    card.querySelectorAll('input').forEach(inp => {
      if (!inp.dataset.minWidth) inp.dataset.minWidth = inp.offsetWidth;
      inp.addEventListener('input', () => autoResize(inp));
      autoResize(inp);
    });

  });

  const allDone = state.items.every(it => it.done);
  document.getElementById('summary').classList.toggle('hidden', !allDone);
  document.getElementById('final-check').classList.toggle('hidden', allDone);
  if (allDone) {
    document.getElementById('summary-text').textContent = `Правильних відповідей: ${state.correct} із ${state.items.length} (${pct(state.correct, state.items.length)}%).`;
    document.getElementById('retry').onclick = init;
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
      el.classList.add('border-rose-400');
    } else {
      el.classList.remove('border-rose-400');
    }
  });
  if (allCorrect) {
    item.done = true;
    item.feedback = 'correct';
    state.answered += 1;
    if (!item.wrongAttempt) state.correct += 1;
  } else {
    item.wrongAttempt = true;
    item.feedback = 'Невірно, спробуй ще раз';
  }
  renderQuestions();
  updateProgress();
}

function renderFeedback(q) {
  if (q.done || q.feedback === 'correct') {
    return '<div class="text-sm text-emerald-700">✅ Вірно!</div>';
  }
  return q.feedback ? `<div class="text-sm text-rose-700">${html(q.feedback)}</div>` : '';
}

function renderSentence(q, idx) {
  let text = q.question;
  q.answers.forEach((ans, i) => {
    let replacement;
    if (q.done) {
      replacement = `<mark class=\"px-1 py-0.5 rounded bg-amber-100\">${html(q.chosen[i])}</mark>`;
    } else {
      const val = q.chosen[i] || '';
      replacement = `<input id=\"input-${idx}-${i}\" class=\"text-center bg-transparent border-0 border-b border-stone-400 focus:outline-none\" style=\"width:auto;min-width:6rem\" placeholder=\"____\" autocomplete=\"off\" value=\"${html(val)}\" />`;
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
  score.textContent = `Точність: ${percent}%`;
  const bar = document.getElementById('progress-bar');
  bar.style.width = `${(state.answered / state.items.length) * 100}%`;
}

init();
</script>
@endsection
