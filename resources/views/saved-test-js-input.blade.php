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

<script>
const QUESTIONS = @json($questionData);
</script>
<script>
const state = {
  items: [],
  correct: 0,
  answered: 0,
};

function init() {
  state.items = QUESTIONS.map(q => ({
    ...q,
    inputs: Array(q.answers.length).fill(null).map(() => ['']),
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
    card.querySelectorAll('input[data-idx][data-word]').forEach(inp => {
      const aIdx = parseInt(inp.dataset.idx);
      const wIdx = parseInt(inp.dataset.word);
      inp.addEventListener('input', () => {
        q.inputs[aIdx][wIdx] = inp.value;
        fetchSuggestions(inp, idx, aIdx, wIdx);
      });
      fetchSuggestions(inp, idx, aIdx, wIdx);
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
}

function addWord(q, idx) {
  q.inputs[idx].push('');
}

function removeWord(q, idx) {
  if (q.inputs[idx].length > 1) q.inputs[idx].pop();
}

function fetchSuggestions(input, qIdx, idx, widx) {
  const val = input.value.trim();
  const listId = `opts-${qIdx}-${idx}-${widx}`;
  const dl = document.getElementById(listId);
  if (!val) { dl.innerHTML = ''; return; }
  fetch('/api/search?lang=en&q=' + encodeURIComponent(val))
    .then(res => res.json())
    .then(data => {
      dl.innerHTML = data.map(it => `<option value="${html(it.en)}"></option>`).join('');
    });
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
      const words = q.inputs[i];
      const inputs = words
        .map((w, j) => `<span class=\"inline-block\"><input type=\"text\" data-idx=\"${i}\" data-word=\"${j}\" class=\"w-20 px-1 py-0.5 text-center border-b border-stone-400 focus:outline-none\" list=\"opts-${qIdx}-${i}-${j}\" value=\"${html(w)}\"><datalist id=\"opts-${qIdx}-${i}-${j}\"></datalist></span>`)
        .join(' ');
      const addBtn = `<button type=\"button\" data-add=\"${i}\" class=\"ml-1 px-2 py-0.5 rounded bg-stone-200\">+</button>`;
      const removeBtn = words.length > 1 ? `<button type=\"button\" data-remove=\"${i}\" class=\"ml-1 px-2 py-0.5 rounded bg-stone-200\">-</button>` : '';
      replacement = `<span class=\"inline-flex items-center gap-1\">${inputs}${addBtn}${removeBtn}</span>`;
    } else {
      replacement = `<mark class=\"px-1 py-0.5 rounded bg-amber-100\">${html(q.inputs[i].join(' '))}</mark>`;
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
