<script>
const SENTENCE_REORDER_PRESENTATION = 'sentence_reorder';

function isSentenceReorderQuestion(q) {
  return String(q?.presentation ?? '') === SENTENCE_REORDER_PRESENTATION
    && Array.isArray(q?.reorder_tokens)
    && String(q?.reorder_answer ?? '').trim() !== '';
}

function sentenceReorderCopy(key) {
  const language = String(TEST_LOCALE || 'uk').toLowerCase();
  const copy = {
    uk: {
      title: 'Побудуй речення',
      instruction: 'Постав слова у правильному порядку.',
      answer: 'Твоя відповідь',
      bank: 'Банк слів',
      check: 'Перевірити',
      clear: 'Очистити',
      empty: 'Обери слова з банку',
    },
    ua: {
      title: 'Побудуй речення',
      instruction: 'Постав слова у правильному порядку.',
      answer: 'Твоя відповідь',
      bank: 'Банк слів',
      check: 'Перевірити',
      clear: 'Очистити',
      empty: 'Обери слова з банку',
    },
    pl: {
      title: 'Ułóż zdanie',
      instruction: 'Ułóż słowa we właściwej kolejności.',
      answer: 'Twoja odpowiedź',
      bank: 'Bank słów',
      check: 'Sprawdź',
      clear: 'Wyczyść',
      empty: 'Wybierz słowa z banku',
    },
    en: {
      title: 'Build the sentence',
      instruction: 'Put the words in the correct order.',
      answer: 'Your answer',
      bank: 'Word bank',
      check: 'Check',
      clear: 'Clear',
      empty: 'Choose words from the bank',
    },
  };

  return (copy[language] || copy.uk)[key] || '';
}

function ensureSentenceReorderState(q) {
  if (!isSentenceReorderQuestion(q)) return;
  if (!Array.isArray(q.reorderChosen)) q.reorderChosen = [];
  if (!Array.isArray(q.reorderUsed)) q.reorderUsed = [];
  if (typeof q.reorderAttempted !== 'boolean') q.reorderAttempted = false;
}

function sentenceReorderAnswer(q) {
  return (q.reorderChosen || []).map((item) => String(item?.token || '')).filter(Boolean).join(' ');
}

function renderSentenceReorderQuestion(q) {
  ensureSentenceReorderState(q);
  const answer = sentenceReorderAnswer(q);
  const done = Boolean(q.done);
  const feedbackClass = q.feedback === 'correct'
    ? 'border-emerald-300 bg-emerald-50'
    : q.feedback
      ? 'border-red-300 bg-red-50'
      : 'border-indigo-200 bg-white';
  const answerTokens = (q.reorderChosen || []).map((item, position) => `
    <button type="button" class="inline-flex items-center gap-1 rounded-xl border border-indigo-200 bg-indigo-50 px-3 py-2 text-sm font-semibold text-indigo-800 transition hover:bg-indigo-100 disabled:cursor-not-allowed disabled:opacity-70" data-reorder-action="remove" data-reorder-position="${position}" ${done ? 'disabled' : ''}>
      ${html(item.token)}<span aria-hidden="true" class="text-indigo-400">×</span>
    </button>`).join('');
  const bankTokens = q.reorder_tokens.map((token, tokenIndex) => {
    const used = (q.reorderUsed || []).includes(tokenIndex);
    return `<button type="button" class="rounded-xl border px-3 py-2 text-sm font-semibold transition ${used ? 'cursor-not-allowed border-gray-200 bg-gray-100 text-gray-400 line-through' : 'border-emerald-200 bg-emerald-50 text-emerald-800 hover:border-emerald-400 hover:bg-emerald-100'}" data-reorder-action="add" data-reorder-token-index="${tokenIndex}" ${used || done ? 'disabled' : ''}>${html(token)}</button>`;
  }).join('');

  return `
    <div class="space-y-4" data-sentence-reorder="true">
      <div class="rounded-2xl border border-emerald-100 bg-emerald-50/70 px-4 py-3">
        <div class="font-semibold text-emerald-900">${html(sentenceReorderCopy('title'))}</div>
        <div class="mt-1 text-sm text-emerald-800">${html(sentenceReorderCopy('instruction'))}</div>
      </div>
      <div>
        <div class="mb-2 text-sm font-semibold text-gray-700">${html(sentenceReorderCopy('answer'))}</div>
        <div class="min-h-[3.3rem] rounded-2xl border-2 p-3 ${feedbackClass}">
          <div class="flex flex-wrap gap-2">${answerTokens || `<span class="py-1 text-sm text-gray-400">${html(sentenceReorderCopy('empty'))}</span>`}</div>
        </div>
      </div>
      <div>
        <div class="mb-2 text-sm font-semibold text-gray-700">${html(sentenceReorderCopy('bank'))}</div>
        <div class="flex flex-wrap gap-2">${bankTokens}</div>
      </div>
      <div class="flex flex-wrap gap-2">
        <button type="button" class="rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50" data-reorder-action="clear" ${done || !(q.reorderChosen || []).length ? 'disabled' : ''}>${html(sentenceReorderCopy('clear'))}</button>
        <button type="button" class="rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-50" data-reorder-action="check" ${done || !answer ? 'disabled' : ''}>${html(sentenceReorderCopy('check'))}</button>
      </div>
    </div>`;
}

function applySentenceReorderAction(q, button) {
  if (!isSentenceReorderQuestion(q) || q.done) return false;
  ensureSentenceReorderState(q);
  const action = String(button?.dataset?.reorderAction || '');

  if (action === 'add') {
    const tokenIndex = Number.parseInt(button.dataset.reorderTokenIndex || '', 10);
    if (!Number.isInteger(tokenIndex) || q.reorderUsed.includes(tokenIndex) || !q.reorder_tokens[tokenIndex]) return false;
    q.reorderChosen.push({ tokenIndex, token: q.reorder_tokens[tokenIndex] });
    q.reorderUsed.push(tokenIndex);
    q.feedback = '';
    q.feedbackMeta = null;
    return true;
  }

  if (action === 'remove') {
    const position = Number.parseInt(button.dataset.reorderPosition || '', 10);
    const removed = q.reorderChosen.splice(position, 1)[0];
    if (!removed) return false;
    q.reorderUsed = q.reorderUsed.filter((tokenIndex) => tokenIndex !== removed.tokenIndex);
    q.feedback = '';
    q.feedbackMeta = null;
    return true;
  }

  if (action === 'clear') {
    if (!q.reorderChosen.length) return false;
    q.reorderChosen = [];
    q.reorderUsed = [];
    q.feedback = '';
    q.feedbackMeta = null;
    return true;
  }

  if (action === 'check') {
    const submitted = sentenceReorderAnswer(q);
    if (!submitted) return false;

    q.reorderAttempted = true;
    q.done = true;
    q.feedback = canonicalTestAnswer(submitted) === canonicalTestAnswer(q.reorder_answer) ? 'correct' : testUi('status.incorrect');
    q.wrongAttempt = q.feedback !== 'correct';
    q.feedbackMeta = {
      submittedAnswer: submitted,
      displayedAnswer: q.reorder_answer,
      result: q.feedback === 'correct' ? 'correct' : 'incorrect',
    };
    return 'checked';
  }

  return false;
}
</script>
