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
      manualInstruction: 'Натискай на групи слів або введи речення вручну.',
      answer: 'Твоя відповідь',
      bank: 'Банк слів',
      check: 'Перевірити',
      clear: 'Очистити',
      empty: 'Обери слова з банку',
    },
    ua: {
      title: 'Побудуй речення',
      instruction: 'Постав слова у правильному порядку.',
      manualInstruction: 'Натискай на групи слів або введи речення вручну.',
      answer: 'Твоя відповідь',
      bank: 'Банк слів',
      check: 'Перевірити',
      clear: 'Очистити',
      empty: 'Обери слова з банку',
    },
    pl: {
      title: 'Ułóż zdanie',
      instruction: 'Ułóż słowa we właściwej kolejności.',
      manualInstruction: 'Klikaj grupy słów lub wpisz zdanie samodzielnie.',
      answer: 'Twoja odpowiedź',
      bank: 'Bank słów',
      check: 'Sprawdź',
      clear: 'Wyczyść',
      empty: 'Wybierz słowa z banku',
    },
    en: {
      title: 'Build the sentence',
      instruction: 'Put the words in the correct order.',
      manualInstruction: 'Click the word groups or type the sentence yourself.',
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
  if (q.reorder_manual_input && typeof q.reorderManualText !== 'string') {
    q.reorderManualText = q.reorderChosen.map(item => String(item?.token || '')).filter(Boolean).join(' ');
  }
}

function sentenceReorderAnswer(q) {
  if (q.reorder_manual_input) return String(q.reorderManualText || '').trim();
  return (q.reorderChosen || []).map((item) => String(item?.token || '')).filter(Boolean).join(' ');
}

function sentenceReorderWords(text) {
  return String(text || '').trim().toLowerCase().replace(/[’‘]/g, "'").split(/\s+/)
    .map(word => word.replace(/^[.,!?;:"“”]+|[.,!?;:"“”]+$/g, '')).filter(Boolean);
}

// Match each token instance to unclaimed contiguous words in the current text.
// Editing or deleting text therefore returns that exact instance to the bank.
function syncSentenceReorderManualText(q, value, clearFeedback = true) {
  if (!isSentenceReorderQuestion(q) || !q.reorder_manual_input || q.done) return false;
  ensureSentenceReorderState(q);
  q.reorderManualText = String(value || '');
  const words = sentenceReorderWords(value), claimed = new Set();
  q.reorderChosen = [];
  q.reorderUsed = [];
  const tokens = q.reorder_tokens.map((token, tokenIndex) => ({token, tokenIndex, chunk: sentenceReorderWords(token)}));
  // The reviewed Passive Voice groups may contain both "the" and "The regulator".
  // Claim the complete group before its shorter prefix without changing bank indices.
  // Historical/course payloads keep their existing matching order.
  const revision = String(q.psp_content_revision || '');
  const ownsPassiveVoice = /^pvq-(?:formation|causative|mistakes|style|get|impersonal|modals|negatives|phrasal|restrictions|two-object|gerund|infinitive|agent|future-continuous|future-perfect|future-simple|past-continuous|past-perfect|past-simple|present-continuous|present-perfect|present-simple|advanced|reporting|complex-causative|complex-impersonal)-(?:a1|a2|b1|b2|c1|c2)-(?:g|c)[0-9]{2}$/.test(String(q.uuid || ''))
    && /^[a-f0-9]{64}$/.test(revision) && q.pvq_option_caption_revision === revision;
  const ownsModalVerbs = /^mvq-(?:cc|mm|mht|sot|need|ded|past|requests|perfect|subtle)-(?:a1|a2|b1|b2|c1|c2)-(?:g|c)[0-9]{2}$/.test(String(q.uuid || ''))
    && /^[a-f0-9]{64}$/.test(revision) && q.mvq_option_caption_revision === revision;
  const ownsConditionals = /^cqq-(?:zero|first|second|third|mixed|unless|case|wish|rather|better|conditions|advanced|alternatives)-(?:a1|a2|b1|b2|c1|c2)-(?:g|c)[0-9]{2}$/.test(String(q.uuid || ''))
    && /^[a-f0-9]{64}$/.test(revision) && q.cqq_option_caption_revision === revision;
  const ownsReportedSpeech = /^rsq-(?:statements|questions|commands|backshift|timeplace|saytell|advanced)-(?:a1|a2|b1|b2|c1|c2)-(?:g|c)[0-9]{2}$/.test(String(q.uuid || ''))
    && /^[a-f0-9]{64}$/.test(revision) && q.rsq_option_caption_revision === revision;
  const ownsClausesLinking = /^clq-(?:relative|defining|nondefining|omitting|whosewhich|reason|result|contrast|purpose|linking|reasonresult|advanced|concessive|partbasics|participle|absolute|discourse)-(?:a1|a2|b1|b2|c1|c2)-(?:g|c)[0-9]{2}$/.test(String(q.uuid || ''))
    && /^[a-f0-9]{64}$/.test(revision) && q.clq_option_caption_revision === revision;
  const ownsPrepositionsPhrasal = /^ppq-(?:time|place|movement|dependent|basics|separable|common)-(?:a1|a2|b1|b2|c1|c2)-(?:g|c)[0-9]{2}$/.test(String(q.uuid || ''))
    && /^[a-f0-9]{64}$/.test(revision) && q.ppq_option_caption_revision === revision;
  const ownsCommonMistakes = /^cmq-(?:articles|perfect|countable|prepositions|traps)-(?:a1|a2|b1|b2|c1|c2)-(?:g|c)[0-9]{2}$/.test(String(q.uuid || ''))
    && /^[a-f0-9]{64}$/.test(revision) && q.cmq_option_caption_revision === revision;
  const ownsSentenceTransformations = /^stq-(?:tenses|modals|passive|reported|conditionals|clauses|articles)-(?:a1|a2|b1|b2|c1|c2)-(?:g|c)[0-9]{2}$/.test(String(q.uuid || ''))
    && /^[a-f0-9]{64}$/.test(revision) && q.stq_option_caption_revision === revision;
  const ownsVerbPatterns = /^vpq-(?:gerund|to|bare|contrast|verbs-gerund|verbs-infinitive|meaning|used|advanced)-(?:a1|a2|b1|b2|c1|c2)-(?:g|c)[0-9]{2}$/.test(String(q.uuid || ''))
    && /^[a-f0-9]{64}$/.test(revision) && q.vpq_option_caption_revision === revision;
  const ownsMixedRevision = /^mrq-(?:a1|a2|b1|b2|c1|c2)-(?:a1|a2|b1|b2|c1|c2)-(?:g|c)[0-9]{2}$/.test(String(q.uuid || ''))
    && /^[a-f0-9]{64}$/.test(revision) && q.mrq_option_caption_revision === revision;
  const ownsAcademicEnglish = /^aeq-(?:hcb|hed|sta|arg)-(?:a1|a2|b1|b2|c1|c2)-(?:g|c)[0-9]{2}$/.test(String(q.uuid || ''))
    && /^[a-f0-9]{64}$/.test(revision) && q.aeq_option_caption_revision === revision;
  const ownsArticlesQuantifiers = /^aqv-(?:aqn|pad)-(?:a1|a2|b1|b2|c1|c2)-(?:g|c)[0-9]{2}$/.test(String(q.uuid || ''))
    && /^[a-f0-9]{64}$/.test(revision) && q.aqv_option_caption_revision === revision;
  const ownsFormalEnglish = /^feq-(?:bas|nom|ton|par|sub|sty)-(?:a1|a2|b1|b2|c1|c2)-(?:g|c)[0-9]{2}$/.test(String(q.uuid || ''))
    && /^[a-f0-9]{64}$/.test(revision) && q.feq_option_caption_revision === revision;
  const ownsRelativeClauses = /^rcq-(?:crc)-(?:a1|a2|b1|b2|c1|c2)-(?:g|c)[0-9]{2}$/.test(String(q.uuid || ''))
    && /^[a-f0-9]{64}$/.test(revision) && q.rcq_option_caption_revision === revision;
  const ownsSentenceStructure = /^ssq-(?:csb|cse|esr|cnp)-(?:a1|a2|b1|b2|c1|c2)-(?:g|c)[0-9]{2}$/.test(String(q.uuid || ''))
    && /^[a-f0-9]{64}$/.test(revision) && q.ssq_option_caption_revision === revision;
  const ownsVocabCollocations = /^vcq-(?:aclc)-(?:a1|a2|b1|b2|c1|c2)-(?:g|c)[0-9]{2}$/.test(String(q.uuid || ''))
    && /^[a-f0-9]{64}$/.test(revision) && q.vcq_option_caption_revision === revision;
  if (ownsPassiveVoice || ownsModalVerbs || ownsConditionals || ownsReportedSpeech || ownsClausesLinking || ownsPrepositionsPhrasal || ownsCommonMistakes || ownsSentenceTransformations || ownsVerbPatterns || ownsMixedRevision || ownsAcademicEnglish || ownsArticlesQuantifiers || ownsFormalEnglish || ownsRelativeClauses || ownsSentenceStructure || ownsVocabCollocations) tokens.sort((left, right) => right.chunk.length - left.chunk.length || left.tokenIndex - right.tokenIndex);
  tokens.forEach(({token, tokenIndex, chunk}) => {
    for (let start = 0; chunk.length && start <= words.length - chunk.length; start++) {
      if (chunk.every((word, offset) => words[start + offset] === word && !claimed.has(start + offset))) {
        chunk.forEach((_, offset) => claimed.add(start + offset));
        q.reorderUsed.push(tokenIndex);
        q.reorderChosen.push({tokenIndex, token, start});
        break;
      }
    }
  });
  q.reorderChosen.sort((left, right) => left.start - right.start);
  if (clearFeedback) {
    q.feedback = '';
    q.feedbackMeta = null;
  }
  return true;
}

// No re-render of the focused input: preserve caret/selection and never invoke
// the gap-completion suggestion path for a full-sentence ordering exercise.
function refreshSentenceReorderManualControls(q, root) {
  const input = root.querySelector('textarea[data-reorder-manual-input]');
  if (input && !q.feedback) {
    input.classList.remove('border-red-300', 'bg-red-50', 'border-emerald-300', 'bg-emerald-50');
    input.classList.add('border-indigo-200', 'bg-white');
  }
  root.querySelectorAll('button[data-reorder-action="add"]').forEach(button => {
    const used = q.reorderUsed.includes(Number(button.dataset.reorderTokenIndex));
    button.disabled = used || Boolean(q.done);
    button.className = 'rounded-xl border px-3 py-2 text-sm font-semibold transition ' + (used
      ? 'cursor-not-allowed border-gray-200 bg-gray-100 text-gray-400 line-through'
      : 'border-emerald-200 bg-emerald-50 text-emerald-800 hover:border-emerald-400 hover:bg-emerald-100');
  });
  root.querySelectorAll('button[data-reorder-action="check"], button[data-reorder-action="clear"]').forEach(button => {
    button.disabled = Boolean(q.done) || !sentenceReorderAnswer(q);
  });
}

function renderSentenceReorderQuestion(q) {
  ensureSentenceReorderState(q);
  if (q.reorder_manual_input && !q.done) syncSentenceReorderManualText(q, q.reorderManualText, false);
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
    const caption = typeof finitePracticeOptionCaption === 'function' ? finitePracticeOptionCaption(q, token) : token;
    return `<button type="button" class="rounded-xl border px-3 py-2 text-sm font-semibold transition ${used ? 'cursor-not-allowed border-gray-200 bg-gray-100 text-gray-400 line-through' : 'border-emerald-200 bg-emerald-50 text-emerald-800 hover:border-emerald-400 hover:bg-emerald-100'}" data-reorder-action="add" data-reorder-token-index="${tokenIndex}" ${used || done ? 'disabled' : ''}>${html(caption)}</button>`;
  }).join('');

  const templateCopy = {uk: 'Збережи порядок наведених частин речення:', ua: 'Збережи порядок наведених частин речення:',
    en: 'Keep the supplied parts in this sentence order:', pl: 'Zachowaj podaną kolejność części zdania:'};
  const templateConstraint = q.reorder_template_constraint && !q.reorder_manual_input && !isPastPerfectContinuousQuestion(q)
    ? `<div class="mt-2 text-sm text-emerald-900" data-reorder-template-constraint>${html(templateCopy[String(TEST_LOCALE || 'uk').toLowerCase()] || templateCopy.en)} <span>${html(String(q.reorder_source_question || '').replace(/\{[^{}]+\}/g, '___'))}</span></div>` : '';

  return `
    <div class="space-y-4" data-sentence-reorder="true">
      <div class="rounded-2xl border border-emerald-100 bg-emerald-50/70 px-4 py-3">
        <div class="font-semibold text-emerald-900">${html(sentenceReorderCopy('title'))}</div>
        <div class="mt-1 text-sm text-emerald-800">${html(sentenceReorderCopy(q.reorder_manual_input ? 'manualInstruction' : 'instruction'))}</div>
        ${templateConstraint}
      </div>
      <div>
        <div class="mb-2 text-sm font-semibold text-gray-700">${html(sentenceReorderCopy('answer'))}</div>
        ${q.reorder_manual_input
          ? `<textarea rows="2" class="min-h-[3.3rem] w-full rounded-2xl border-2 p-3 text-base ${feedbackClass}" data-reorder-manual-input autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false" data-no-suggestions="true" aria-label="${html(sentenceReorderCopy('answer'))}" ${done ? 'disabled' : ''}>${html(q.reorderManualText || '')}</textarea>`
          : `<div class="min-h-[3.3rem] rounded-2xl border-2 p-3 ${feedbackClass}"><div class="flex flex-wrap gap-2">${answerTokens || `<span class="py-1 text-sm text-gray-400">${html(sentenceReorderCopy('empty'))}</span>`}</div></div>`}
      </div>
      <div>
        <div class="mb-2 text-sm font-semibold text-gray-700">${html(sentenceReorderCopy('bank'))}</div>
        <div class="flex flex-wrap gap-2">${bankTokens}</div>
      </div>
      <div class="flex flex-wrap gap-2">
        <button type="button" class="rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50" data-reorder-action="clear" ${done || !answer ? 'disabled' : ''}>${html(sentenceReorderCopy('clear'))}</button>
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
    if (q.reorder_manual_input) {
      return syncSentenceReorderManualText(q, [sentenceReorderAnswer(q), q.reorder_tokens[tokenIndex]].filter(Boolean).join(' '));
    }
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
    if (q.reorder_manual_input) return syncSentenceReorderManualText(q, '');
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
    const answers = [q.reorder_answer, ...(Array.isArray(q.reorder_accepted_answers) ? q.reorder_accepted_answers : [])];
    q.feedback = answers.some(answer => EnglishAnswerVariants.matches(answer, submitted)) ? 'correct' : testUi('status.incorrect');
    q.done = !q.reorder_manual_input || q.feedback === 'correct';
    q.wrongAttempt = q.reorder_manual_input
      ? Boolean(q.wrongAttempt) || q.feedback !== 'correct'
      : q.feedback !== 'correct';
    q.feedbackMeta = {
      submittedAnswer: submitted,
      displayedAnswer: q.reorder_answer,
      result: q.feedback === 'correct' ? 'correct' : 'incorrect',
    };
    return q.reorder_manual_input && !q.done ? 'retry' : 'checked';
  }

  return false;
}
</script>
