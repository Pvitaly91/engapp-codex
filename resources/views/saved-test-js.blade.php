@extends('layouts.engram')

@section('title', $test->name)

@section('content')
<div class="mx-auto max-w-3xl px-4 py-8 text-stone-800" id="quiz-app">
    <header class="mb-6">
        <h1 class="text-2xl sm:text-3xl font-bold text-stone-900">{{ $test->name }}</h1>
        <p class="text-sm text-stone-600 mt-1">Перевіряй відповіді одразу, клавіші 1–4 працюють для активного запитання.</p>
    </header>

    <nav class="mb-6 flex gap-2 text-sm">
        <a href="{{ route('saved-test.js', $test->slug) }}" class="px-3 py-1 rounded-lg border border-stone-300 {{ request()->routeIs('saved-test.js') ? 'bg-stone-900 text-white' : '' }}">Карти</a>
        <a href="{{ route('saved-test.js.step', $test->slug) }}" class="px-3 py-1 rounded-lg border border-stone-300 {{ request()->routeIs('saved-test.js.step') ? 'bg-stone-900 text-white' : '' }}">Step</a>
        <a href="{{ route('saved-test.js.step-input', $test->slug) }}" class="px-3 py-1 rounded-lg border border-stone-300 {{ request()->routeIs('saved-test.js.step-input') ? 'bg-stone-900 text-white' : '' }}">Input</a>
    </nav>

    <div class="mb-6">
        <div class="flex items-center justify-between text-sm">
            <span id="progress-label" class="text-stone-600">0 / 0</span>
            <span id="score-label" class="text-stone-600">Точність: 0%</span>
        </div>
        <div class="w-full h-2 bg-stone-200 rounded-full mt-2">
            <div id="progress-bar" class="h-2 bg-stone-900 rounded-full" style="width:0%"></div>
        </div>
    </div>

    <div id="questions" class="space-y-4"></div>

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
<script>
const state = {
  items: [],
  correct: 0,
  answered: 0,
  activeCardIdx: 0,
};

function init() {
  state.items = QUESTIONS.map((q) => {
    const opts = [...q.options];
    shuffle(opts);
    return {
      ...q,
      options: opts,
      chosen: Array(q.answers.length).fill(null),
      slot: 0,
      done: false,
      wrongAttempt: false,
      lastWrong: null,
      feedback: '',
    };
  });
  state.correct = 0;
  state.answered = 0;

  renderQuestions();
  updateProgress();
  hookGlobalEvents();
}

function renderQuestions(showOnlyWrong = false) {
  const wrap = document.getElementById('questions');
  wrap.innerHTML = '';

  state.items.forEach((q, idx) => {
    if (showOnlyWrong && (!q.done || !q.wrongAttempt)) return;

    const card = document.createElement('article');
    card.className = 'rounded-2xl border border-stone-200 bg-white p-4 focus-within:ring-2 ring-stone-900/20 outline-none';
    card.tabIndex = 0;
    card.dataset.idx = idx;

    const sentence = renderSentence(q);

    card.innerHTML = `
      <div class="flex items-start justify-between gap-3">
        <div>
          <div class="text-sm text-stone-500">${q.level} • ${q.tense}</div>
          <div class="mt-1 text-base leading-relaxed text-stone-900">${sentence}</div>
        </div>
        <div class="text-xs text-stone-500 shrink-0">[${idx + 1}/${state.items.length}]</div>
      </div>

      <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-2" role="group" aria-label="Варіанти відповіді">
        ${q.options.map((opt, i) => renderOptionButton(q, idx, opt, i)).join('')}
      </div>

      <div class="mt-2 h-5" id="feedback-${idx}">${renderFeedback(q)}</div>
    `;

    card.addEventListener('click', (e) => {
      const btn = e.target.closest('button[data-opt]');
      if (!btn) return;
      onChoose(idx, btn.dataset.opt);
    });

    card.addEventListener('focusin', () => {
      state.activeCardIdx = idx;
    });

    wrap.appendChild(card);
  });

  const allDone = state.items.every(it => it.done);
  document.getElementById('summary').classList.toggle('hidden', !allDone);

  if (allDone) {
    const summaryText = document.getElementById('summary-text');
    summaryText.textContent = `Правильних відповідей: ${state.correct} із ${state.items.length} (${pct(state.correct, state.items.length)}%).`;
    document.getElementById('retry').onclick = init;
    document.getElementById('show-wrong').onclick = () => renderQuestions(true);
  }
}

function renderOptionButton(q, idx, opt, i) {
  const base = 'w-full text-left px-3 py-2 rounded-xl border transition';
  let cls = 'border-stone-300 hover:border-stone-400 bg-white';
  if (q.done) {
    cls = 'border-stone-300 bg-stone-100';
  } else if (q.lastWrong === opt) {
    cls = 'border-rose-300 bg-rose-50';
  }
  const hotkey = i + 1;
  return `
    <button type="button" class="${base} ${cls}" data-opt="${html(opt)}" title="Натисни ${hotkey}" ${q.done ? 'disabled' : ''}>
      <span class="mr-2 inline-flex h-5 w-5 items-center justify-center rounded-md border text-xs">${hotkey}</span>
      ${opt}
    </button>
  `;
}

function renderFeedback(q) {
  if (q.done || q.feedback === 'correct') {
    return '<div class="text-sm text-emerald-700">✅ Вірно!</div>';
  }
  return q.feedback
    ? `<div class="text-sm text-rose-700">${html(q.feedback)}</div>`
    : '';
}

function onChoose(idx, opt) {
  const item = state.items[idx];
  if (item.done) return;

  if (opt === item.answers[item.slot]) {
    item.chosen[item.slot] = opt;
    item.slot += 1;
    item.lastWrong = null;
    item.feedback = 'correct';
    if (item.slot === item.answers.length) {
      item.done = true;
      state.answered += 1;
      if (!item.wrongAttempt) state.correct += 1;
    }
  } else {
    item.wrongAttempt = true;
    item.lastWrong = opt;
    item.feedback = 'Невірно, спробуй ще раз';
  }

  const container = document.querySelector(`article[data-idx="${idx}"]`);
  if (container) {
    container.querySelector('.leading-relaxed').innerHTML = renderSentence(item);
    const group = container.querySelector('[role="group"]');
    group.innerHTML = item.options.map((optText, i) => renderOptionButton(item, idx, optText, i)).join('');
    container.querySelector(`#feedback-${idx}`).innerHTML = renderFeedback(item);
  }

  updateProgress();
  checkAllDone();
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

function checkAllDone() {
  const allDone = state.items.every(it => it.done);
  if (allDone) {
    renderQuestions();
  }
}

function renderSentence(q) {
  let text = q.question;
  q.answers.forEach((ans, i) => {
    const replacement = q.chosen[i]
      ? `<mark class=\"px-1 py-0.5 rounded bg-amber-100\">${html(q.chosen[i])}</mark>`
      : (i === q.slot
        ? `<mark class=\"px-1 py-0.5 rounded bg-amber-200\">____</mark>`
        : '____');
    const regex = new RegExp(`\\{a${i + 1}\\}`);
    const marker = `a${i + 1}`;
    const hint = q.verb_hints && q.verb_hints[marker]
      ? ` <span class=\"verb-hint text-red-700 text-xs font-bold\">(${html(q.verb_hints[marker])})</span>`
      : '';
    text = text.replace(regex, replacement + hint);
  });
  return text;
}

function hookGlobalEvents() {
  document.addEventListener('keydown', (e) => {
    const n = Number(e.key);
    if (!Number.isInteger(n) || n < 1 || n > 4) return;

    const idx = state.activeCardIdx ?? 0;
    const item = state.items[idx];
    if (!item || item.done) return;

    const opt = item.options[n - 1];
    if (!opt) return;

    onChoose(idx, opt);
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
