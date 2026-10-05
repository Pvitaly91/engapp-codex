<script>
// Finite authored compose tasks carry localized conditions, not gap markers.
// Keep legacy manual cards and their matcher contract unchanged.
function isAuthoredComposeManualQuestion(q) {
  return String(q?.type ?? '') === '4'
    && /^[a-f0-9]{64}$/.test(String(q?.compose_content_revision ?? ''))
    && Array.isArray(q?.answers)
    && q.answers.length > 0;
}

function getMarkerLabel(q, idx) {
  const markers = Array.isArray(q.markers) ? q.markers : Object.keys(q.answer_map || {});
  return markers[idx] || `a${idx + 1}`;
}

function renderAuthoredComposeManualQuestion(q, questionIndex = null) {
  const total = q.answers.length;
  const slots = q.answers.map((_, slotIndex) => {
    const marker = getMarkerLabel(q, slotIndex);
    const label = html(testUi('question.gap', { label: marker, current: slotIndex + 1, total }));
    const value = html(q.chosen?.[slotIndex] || '');
    const id = questionIndex === null ? `input-${slotIndex}` : `input-${questionIndex}-${slotIndex}`;
    const questionAttribute = questionIndex === null ? '' : ` data-question="${questionIndex}"`;
    const input = q.done
      ? `<mark data-authored-compose-slot="${slotIndex}" aria-label="${label}" class="px-3 py-1 rounded-lg bg-gradient-to-r from-amber-100 to-yellow-100 font-semibold">${value}</mark>`
      : `<input id="${id}"${questionAttribute} data-slot="${slotIndex}" data-authored-compose-input aria-label="${label}" class="px-3 py-2 text-center border-2 border-indigo-200 rounded-xl focus:outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all font-medium max-w-full" style="width:8rem;min-width:8rem;max-width:100%" placeholder="____" autocomplete="off" value="${value}" />`;
    const hint = q.verb_hints && q.verb_hints[marker]
      ? `<span class="verb-hint text-red-600 text-sm font-bold">( ${html(q.verb_hints[marker])} )</span>`
      : '';
    return `<span class="inline-flex flex-wrap items-center gap-1.5 max-w-full">${input}${hint}</span>`;
  }).join('');
  const hint = typeof q.hint === 'string' && q.hint.trim()
    ? `<p data-authored-compose-hint class="mt-2.5 mb-3 text-[13px] sm:text-sm text-gray-600 whitespace-pre-line leading-relaxed">${html(q.hint)}</p>`
    : '';
  const completed = q.done
    ? `<div data-polyglot-translation-status="done" class="mt-2.5 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm font-semibold text-emerald-800">${html(testUi('question.translation_completed'))}</div>`
    : '';
  return `<div data-authored-compose-condition>${html(q.question)}</div>
    ${hint}
    <div data-authored-compose-manual data-polyglot-translation-preview="true" class="mt-3 sm:mt-4 space-y-2.5">
      <div class="text-[11px] font-semibold uppercase tracking-[0.18em] text-gray-500 sm:text-xs">${html(testUi('question.translation_preview'))}</div>
      <div class="flex flex-wrap gap-2.5">${slots}</div>
      ${completed}
    </div>`;
}
</script>
