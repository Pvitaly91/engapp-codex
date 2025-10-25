<?php $__env->startSection('title', $test->name); ?>

<?php $__env->startSection('content'); ?>
<div class="mx-auto max-w-3xl px-4 py-8 text-stone-800" id="quiz-app">
    <header class="mb-6">
        <h1 class="text-2xl sm:text-3xl font-bold text-stone-900"><?php echo e($test->name); ?></h1>
        <p class="text-sm text-stone-600 mt-1">Перевіряй відповіді одразу, клавіші 1–4 працюють для активного запитання.</p>
    </header>

    <?php echo $__env->make('components.test-mode-nav', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    <?php echo $__env->make('components.word-search', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <?php echo $__env->make('components.saved-test-progress', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <?php echo $__env->make('components.saved-test-js-restart-button', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

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
<style>
 
  #questions article{
        background-color: #fef6db;
  }
</style>
<script>
window.__INITIAL_JS_TEST_QUESTIONS__ = <?php echo json_encode($questionData, 15, 512) ?>;
let QUESTIONS = Array.isArray(window.__INITIAL_JS_TEST_QUESTIONS__)
    ? window.__INITIAL_JS_TEST_QUESTIONS__
    : [];
const CSRF_TOKEN = '<?php echo e(csrf_token()); ?>';
const EXPLAIN_URL = '<?php echo e(route('question.explain')); ?>';
const TEST_SLUG = <?php echo json_encode($test->slug, 15, 512) ?>;
</script>
<?php echo $__env->make('components.saved-test-js-persistence', ['mode' => $jsStateMode, 'savedState' => $savedState], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php echo $__env->make('components.saved-test-js-helpers', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<script>
const state = {
  items: [],
  correct: 0,
  answered: 0,
  activeCardIdx: 0,
};

let globalEventsHooked = false;

function ensureGlobalEvents() {
  if (globalEventsHooked) return;
  hookGlobalEvents();
  globalEventsHooked = true;
}

async function init(forceFresh = false) {
  const baseQuestions = await loadQuestions(forceFresh);
  QUESTIONS = Array.isArray(baseQuestions) ? baseQuestions : [];

  let restored = false;
  if (!forceFresh) {
    const saved = getSavedState();
    if (saved && Array.isArray(saved.items)) {
      state.items = saved.items;
      state.correct = Number.isFinite(saved.correct) ? saved.correct : 0;
      state.answered = Number.isFinite(saved.answered) ? saved.answered : 0;
      state.activeCardIdx = Number.isFinite(saved.activeCardIdx) ? saved.activeCardIdx : 0;
      restored = true;
      state.items.forEach((item) => {
        if (typeof item.explanation !== 'string') item.explanation = '';
        if (!item.explanationsCache || typeof item.explanationsCache !== 'object') item.explanationsCache = {};
        if (!('pendingExplanationKey' in item)) item.pendingExplanationKey = null;
      });
    }
  }

  if (!restored) {
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
        attempts: 0,
        explanation: '',
        explanationsCache: {},
        pendingExplanationKey: null,
      };
    });
    state.correct = 0;
    state.answered = 0;
    state.activeCardIdx = 0;
  }

  ensureGlobalEvents();
  renderQuestions();
  updateProgress();
  checkAllDone();
  persistState(state, true);
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

      <div class="mt-2" id="feedback-${idx}">${renderFeedback(q)}</div>
    `;

    card.addEventListener('click', (e) => {
      const btn = e.target.closest('button[data-opt]');
      if (!btn) return;
      onChoose(idx, btn.dataset.opt);
    });

    card.addEventListener('focusin', () => {
      if (state.activeCardIdx !== idx) {
        state.activeCardIdx = idx;
        persistState(state);
      }
    });

    wrap.appendChild(card);
  });

  const allDone = state.items.every(it => it.done);
  document.getElementById('summary').classList.toggle('hidden', !allDone);

  if (allDone) {
    const summaryText = document.getElementById('summary-text');
    summaryText.textContent = `Правильних відповідей: ${state.correct} із ${state.items.length} (${pct(state.correct, state.items.length)}%).`;
    const retryButton = document.getElementById('retry');
    if (retryButton) {
      retryButton.onclick = () => restartJsTest(init, { button: retryButton });
    }
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
  if (q.feedback === 'correct') {
    let htmlStr = '<div class="text-sm text-emerald-700">✅ Вірно!</div>';
    if (q.explanation) {
      htmlStr += `<div class="mt-2 whitespace-pre-line text-sm text-emerald-700 bg-emerald-50 border border-emerald-200 px-3 py-2 rounded-xl">${html(q.explanation)}</div>`;
    }
    return htmlStr;
  }
  if (q.feedback) {
    let htmlStr = `<div class="text-sm text-rose-700">${html(q.feedback)}</div>`;
    if (q.explanation) {
      htmlStr += `<div class="mt-2 whitespace-pre-line text-sm text-rose-700 bg-rose-50 border border-rose-200 px-3 py-2 rounded-xl">${html(q.explanation)}</div>`;
    }
    return htmlStr;
  }
  if (q.explanation) {
    return `<div class="mt-2 whitespace-pre-line text-sm text-stone-700 bg-stone-50 border border-stone-200 px-3 py-2 rounded-xl">${html(q.explanation)}</div>`;
  }
  return '';
}

function onChoose(idx, opt) {
  const item = state.items[idx];
  if (item.done) return;

  const slotIndex = item.slot;
  const expected = item.answers[slotIndex];
  if (expected === undefined) return;

  if (!item.explanationsCache) {
    item.explanationsCache = {};
  }

  const key = buildExplanationKey(opt, expected);
  item.pendingExplanationKey = key;
  if (Object.prototype.hasOwnProperty.call(item.explanationsCache, key)) {
    item.explanation = item.explanationsCache[key];
  } else {
    item.explanation = '';
  }

  const explanationPromise = ensureExplanation(item, idx, opt, expected, key, slotIndex);

  if (opt === expected) {
    item.chosen[slotIndex] = opt;
    item.slot += 1;
    item.lastWrong = null;
    item.feedback = 'correct';
    item.attempts = 0;
    if (item.slot === item.answers.length) {
      item.done = true;
      state.answered += 1;
      if (!item.wrongAttempt) state.correct += 1;
    }
  } else {
    item.wrongAttempt = true;
    item.lastWrong = opt;
    item.attempts += 1;
    if (item.attempts >= 2) {
      const correct = expected;
      item.chosen[slotIndex] = correct;
      item.slot += 1;
      item.feedback = `Правильна відповідь: ${correct}`;
      item.attempts = 0;
      if (item.slot === item.answers.length) {
        item.done = true;
        state.answered += 1;
      }
    } else {
      item.feedback = 'Невірно, спробуй ще раз';
    }
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
  persistState(state);

  explanationPromise
    .then((text) => {
      if (item.pendingExplanationKey !== key) {
        return;
      }
      item.explanation = text || '';
      const card = document.querySelector(`article[data-idx="${idx}"]`);
      if (card) {
        const feedbackEl = card.querySelector(`#feedback-${idx}`);
        if (feedbackEl) {
          feedbackEl.innerHTML = renderFeedback(item);
        }
      }
      persistState(state);
    })
    .catch((error) => {
      console.error(error);
    });
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

function buildExplanationKey(selected, expected) {
  const normSelected = (selected ?? '').toString().trim().toLowerCase();
  const normExpected = (expected ?? '').toString().trim().toLowerCase();

  return `${normSelected}|||${normExpected}`;
}

function ensureExplanation(item, idx, selected, expected, key, slotIndex) {
  if (!expected && !selected) {
    return Promise.resolve('');
  }

  if (!item.explanationsCache) {
    item.explanationsCache = {};
  }

  if (Object.prototype.hasOwnProperty.call(item.explanationsCache, key)) {
    return Promise.resolve(item.explanationsCache[key] || '');
  }

  const payload = {
    question_id: item.id,
    answer: selected,
    correct_answer: expected,
  };

  if (typeof slotIndex === 'number') {
    payload.marker = `a${slotIndex + 1}`;
  }

  if (TEST_SLUG) {
    payload.test_slug = TEST_SLUG;
  }

  return fetch(EXPLAIN_URL, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'X-CSRF-TOKEN': CSRF_TOKEN,
    },
    body: JSON.stringify(payload),
  })
    .then((response) => {
      if (!response.ok) {
        throw new Error('Failed to load explanation');
      }
      return response.json();
    })
    .then((data) => {
      const text = data && typeof data.explanation === 'string' ? data.explanation : '';
      item.explanationsCache[key] = text;

      return text;
    })
    .catch((error) => {
      console.error(error);
      item.explanationsCache[key] = '';

      return '';
    });
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

const restartButton = document.getElementById('restart-test');
if (restartButton) {
  restartButton.addEventListener('click', () => restartJsTest(init, { button: restartButton }));
}

init();
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.engram', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/pvital01/data/www/gramlyze.com/resources/views/engram/saved-test-js.blade.php ENDPATH**/ ?>