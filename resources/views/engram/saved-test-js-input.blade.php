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
 
  #questions article{
        background-color: #fef6db;
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
      inp.addEventListener('keydown', e => {
        if (e.key === ' ') e.preventDefault();
      });
      const handle = () => {
        const val = inp.value.replace(/\s+/g, '');
        if (val !== inp.value) inp.value = val;
        q.inputs[aIdx][wIdx] = val;
        fetchSuggestions(inp, idx, aIdx, wIdx);
        autoResize(inp);
      };
      inp.addEventListener('input', handle);
      inp.addEventListener('change', handle);
      autoResize(inp);
      fetchSuggestions(inp, idx, aIdx, wIdx);
      const selectId = inp.dataset.select;
      if (selectId) {
        const selectEl = card.querySelector(`#${selectId}`);
        if (selectEl) {
          selectEl.addEventListener('change', () => {
            const chosen = selectEl.value.trim();
            if (!chosen) return;
            const sanitized = chosen.replace(/\s+/g, '');
            inp.value = sanitized;
            q.inputs[aIdx][wIdx] = sanitized;
            autoResize(inp);
            selectEl.value = '';
          });
        }
      }
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
  const selectEl = document.getElementById(listId);
  if (!selectEl) return;
  if (!val) {
    selectEl.innerHTML = '';
    selectEl.classList.add('hidden');
    return;
  }
  fetch('/api/search?lang=en&q=' + encodeURIComponent(val))
    .then(res => res.json())
    .then(data => {
      if (!Array.isArray(data) || data.length === 0) {
        selectEl.innerHTML = '';
        selectEl.classList.add('hidden');
        return;
      }
      const options = ['<option value="">Обери підказку</option>'];
      data.forEach(it => {
        const value = html(it.en);
        options.push(`<option value="${value}">${value}</option>`);
      });
      selectEl.innerHTML = options.join('');
      selectEl.value = '';
      selectEl.classList.remove('hidden');
      if (input.dataset.select) {
        selectEl.style.width = input.style.width;
      }
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
        .map((w, j) => {
          const inputId = `input-${qIdx}-${i}-${j}`;
          const selectId = `opts-${qIdx}-${i}-${j}`;
          return `<span class=\"inline-flex flex-col items-start gap-1\"><input id=\"${inputId}\" type=\"text\" data-idx=\"${i}\" data-word=\"${j}\" class=\"px-1 py-0.5 text-center border-b border-stone-400 focus:outline-none\" style=\"width:auto\" value=\"${html(w)}\" data-select=\"${selectId}\"><select id=\"${selectId}\" data-input=\"${inputId}\" class=\"suggestion-select hidden rounded border border-stone-300 bg-white px-2 py-1 text-sm text-stone-700 focus:outline-none\"></select></span>`;
        })
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
  const selectId = el.dataset.select;
  if (selectId) {
    const selectEl = document.getElementById(selectId);
    if (selectEl) {
      selectEl.style.width = width + 'px';
    }
  }
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
