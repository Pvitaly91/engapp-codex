@php
    $__questionReportAdmin = (bool) ($isAdmin ?? false);
    $__questionReportIssueCatalog = $__questionReportAdmin ? ($questionReportIssueCatalog ?? []) : [];
    $__questionReportsByQuestion = $__questionReportAdmin ? ($questionReportsByQuestion ?? []) : [];
    $__questionReportUi = $__questionReportAdmin ? ($questionReportUi ?? []) : [];
@endphp

@include('components.test-suggestion-keyboard')
@include('components.english-answer-variants')

<style>
    [data-question-reported="1"] {
        border-color: #f59e0b !important;
        background: #fffbeb !important;
        box-shadow: 0 0 0 1px rgba(245, 158, 11, 0.24);
    }

    @media (prefers-color-scheme: dark) {
        [data-question-reported="1"] {
            background: rgba(69, 26, 3, 0.3) !important;
        }
    }
</style>

<script>
window.FRONTEND_TESTS_I18N = @json(__('frontend.tests'));
window.__IS_ADMIN__ = Boolean(@json($isAdmin ?? false));
window.__SHOW_TEST_TECH_INFO__ = Boolean(@json($showTechnicalInfo ?? false));
window.__QUESTION_REPORT_ENDPOINT__ = @json($__questionReportAdmin ? route('question-reports.store') : null);
window.__QUESTION_REPORT_TEST_SLUG__ = @json((string) data_get($test ?? null, 'slug', ''));
window.__QUESTION_REPORT_TEST_NAME__ = @json((string) data_get($test ?? null, 'name', ''));
window.__QUESTION_REPORT_MODE__ = @json((string) ($jsStateMode ?? ''));
window.__QUESTION_REPORT_ISSUE_CATALOG__ = @json($__questionReportIssueCatalog);
window.__QUESTION_REPORT_BY_QUESTION__ = @json($__questionReportsByQuestion);
window.__QUESTION_REPORT_UI__ = @json($__questionReportUi);

function getTestUiValue(path, fallback = '') {
    const source = window.FRONTEND_TESTS_I18N || {};
    const value = String(path || '')
        .split('.')
        .reduce((acc, segment) => {
            if (acc && typeof acc === 'object' && Object.prototype.hasOwnProperty.call(acc, segment)) {
                return acc[segment];
            }

            return undefined;
        }, source);

    return value === undefined ? fallback : value;
}

function interpolateTestUi(template, replacements = {}) {
    return String(template ?? '').replace(/:([a-zA-Z0-9_]+)/g, (match, key) => {
        if (Object.prototype.hasOwnProperty.call(replacements, key)) {
            return replacements[key];
        }

        return match;
    });
}

function testUi(path, replacements = {}, fallback = '') {
    return interpolateTestUi(getTestUiValue(path, fallback), replacements);
}

function shuffle(arr) {
    for (let i = arr.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));
        [arr[i], arr[j]] = [arr[j], arr[i]];
    }
}
function pct(a, b) {
    return Math.round((a / (b || 1)) * 100);
}

function canonicalTestAnswer(value) {
    return EnglishAnswerVariants.normalize(value)
        .replace(/\bwill\s+not\b/g, "won't")
        .replace(/\bhave\s+not\b/g, "haven't")
        .replace(/\bhas\s+not\b/g, "hasn't")
        .replace(/\bhad\s+not\b/g, "hadn't");
}

// Registered finite builders use word slots even when an authored synonym or
// a genuine contraction changes sentence length. Unknown banks, gap questions
// and word-order groups retain their own policy. The historic function names
// also support previously saved Present Perfect Passive payloads.
function presentPerfectPassiveWordPolicy(question) {
    const finite = question?.finite_word_slots;
    const policy = finite || question?.pvq_word_slots;
    if ((finite ? finite.schema !== 1 || finite.uuid !== question.uuid
            : !/^pvq-present-perfect-(?:a1|a2|b1|b2|c1|c2)-c0[1-3]$/.test(String(question?.uuid || '')))
        || String(question?.type) !== '4' || question?.presentation === 'sentence_reorder'
        || !/^[a-f0-9]{64}$/.test(String(question?.psp_content_revision || ''))
        || policy?.revision !== question.psp_content_revision
        || policy.question !== question.question
        || !Array.isArray(policy.answers) || !policy.answers.length
        || policy.answers.some(word => typeof word !== 'string' || !word.trim() || /\s/.test(word))
        || !Array.isArray(policy.sentences) || !policy.sentences.length
        || JSON.stringify(policy.sentences) !== JSON.stringify(question.compose_accepted_sentences)
        || EnglishAnswerVariants.normalize(policy.answers.join(' ')) !== EnglishAnswerVariants.normalize(policy.sentences[0])) return null;
    return policy;
}

function presentPerfectPassiveWordPaths(question) {
    const policy = presentPerfectPassiveWordPolicy(question);
    if (!policy) return null;
    const cache = presentPerfectPassiveWordPaths.cache ||= new WeakMap();
    const signature = JSON.stringify(policy.sentences);
    const cached = cache.get(policy);
    if (cached?.signature === signature) return cached.paths;
    const seen = new Set(), paths = [];
    policy.sentences.forEach(sentence => EnglishAnswerVariants.variants(sentence).forEach(variant => {
        const words = EnglishAnswerVariants.withoutSentencePunctuation(variant).split(/\s+/).filter(Boolean);
        const key = EnglishAnswerVariants.normalize(words.join(' '));
        if (words.length && !seen.has(key)) { seen.add(key); paths.push(words); }
    }));
    cache.set(policy, {signature, paths});
    return paths;
}

function presentPerfectPassiveNextWords(question, slotIndex) {
    const paths = presentPerfectPassiveWordPaths(question);
    if (paths === null) return null;
    if (!Number.isInteger(slotIndex) || slotIndex < 0) return [];
    const before = Array.from({length: slotIndex}, (_, index) => question?.chosen?.[index]);
    if (before.some(word => typeof word !== 'string' || !word.trim() || /\s/.test(word.trim()))) return [];
    const normalize = EnglishAnswerVariants.normalize;
    return [...new Set(paths.filter(words => words.length > slotIndex
        && before.every((word, index) => normalize(word) === normalize(words[index])))
        .map(words => words[slotIndex]))];
}

function syncPresentPerfectPassiveWordPath(question) {
    const policy = presentPerfectPassiveWordPolicy(question), paths = presentPerfectPassiveWordPaths(question);
    if (!policy || !paths) return false;
    const chosen = [];
    for (const word of question.chosen || []) {
        if (word == null) break;
        if (typeof word !== 'string' || !word.trim() || /\s/.test(word.trim())) return false;
        chosen.push(word);
    }
    const normalize = EnglishAnswerVariants.normalize;
    const candidates = paths.filter(words => chosen.length <= words.length
        && chosen.every((word, index) => normalize(word) === normalize(words[index])));
    const path = candidates.find(words => words.length === chosen.length) || candidates[0];
    if (!path) return false;
    // Keep authored punctuation/case for the canonical spelling; alternate
    // paths still contain one real word per input and never an empty marker.
    const answers = normalize(path.join(' ')) === normalize(policy.answers.join(' ')) ? [...policy.answers] : [...path];
    const count = answers.length;
    question.answers = answers;
    question.answer = answers[0];
    question.markers = answers.map((_, index) => `a${index + 1}`);
    question.markers_count = count;
    question.answer_map = Object.fromEntries(question.markers.map((marker, index) => [marker, answers[index]]));
    question.chosen = answers.map((_, index) => chosen[index] ?? null);
    for (const [key, fallback] of [['manualInputsBySlot', ''], ['manualWordIndexBySlot', 0], ['attemptsBySlot', 0],
        ['lastWrongBySlot', null], ['wordSearchRequestBySlot', 0]]) {
        const old = question[key] || [];
        question[key] = answers.map((_, index) => old[index] ?? fallback);
    }
    question.wordSuggestionsBySlot = answers.map((_, index) => question.wordSuggestionsBySlot?.[index] || []);
    question.activeSlot = Math.min(chosen.length, count - 1);
    question.slot = question.activeSlot;
    return true;
}

function presentPerfectPassiveWordChoices(question, slotIndex, existing) {
    const allowed = presentPerfectPassiveNextWords(question, slotIndex);
    if (allowed === null) return null;
    const normalize = EnglishAnswerVariants.normalize, choices = [], seen = new Set();
    const count = Math.max(4, new Set(allowed.map(normalize)).size);
    for (const word of [...allowed, ...(existing || [])]) {
        const clean = EnglishAnswerVariants.withoutSentencePunctuation(word);
        if (!clean || /\s/.test(clean) || seen.has(normalize(clean))) continue;
        seen.add(normalize(clean)); choices.push(clean);
        if (choices.length === count) break;
    }
    // Stable within this prefix: numbered keyboard choices must keep the same
    // order as the buttons, even when getActiveOptions is called again.
    const key = `${question.uuid}|${slotIndex}|${(question.chosen || []).slice(0, slotIndex).join(' ')}`;
    const rank = word => Array.from(`${key}|${word}`).reduce((hash, character) => Math.imul(hash ^ character.charCodeAt(0), 16777619) >>> 0, 2166136261);
    return choices.sort((left, right) => rank(left) - rank(right) || left.localeCompare(right));
}

function restorePresentPerfectPassiveWordProgress(saved, fresh) {
    const policy = presentPerfectPassiveWordPolicy(fresh);
    if (!policy || saved?.uuid !== fresh.uuid || String(saved.type) !== '4' || saved.presentation === 'sentence_reorder') return null;
    const same = (left, right) => JSON.stringify(left) === JSON.stringify(right);
    const previous = policy.previous_versions || (policy.previous ? [policy.previous] : []);
    const old = previous.find(version => saved.question === version.question
        && (!saved.psp_content_revision || saved.psp_content_revision === version.revision)
        && same(saved.answers, version.answers) && same(saved.markers, version.markers));
    const grouped = Boolean(old);
    const current = saved.question === fresh.question && saved.psp_content_revision === fresh.psp_content_revision
        && same(saved.finite_word_slots || saved.pvq_word_slots, policy);
    if (!grouped && !current) return null;
    // Already-current canonical snapshots need a content refresh, not a
    // progress migration: preserve sparse arrays, retries and unfinished text.
    if (current && same(saved.answers, fresh.answers) && same(saved.markers, fresh.markers)) return {};
    const next = {...fresh, chosen: [], manualInputsBySlot: [], manualWordIndexBySlot: [], attemptsBySlot: [],
        lastWrongBySlot: [], wordSuggestionsBySlot: [], wordSearchRequestBySlot: []};
    let offset = 0, open = false;
    for (let index = 0; index < saved.answers.length; index++) {
        const value = saved.chosen?.[index];
        const canonicalWords = String(saved.answers[index]).trim().split(/\s+/);
        const words = value == null ? canonicalWords : EnglishAnswerVariants.withoutSentencePunctuation(value).split(/\s+/).filter(Boolean);
        if (value != null && (open || !words.length)) return null;
        if (value != null) next.chosen.push(...words);
        else open = true;
        const draft = value == null ? String(saved.manualInputsBySlot?.[index] || '').trim() : words.join(' ');
        const drafts = draft ? draft.split(/\s+/) : [];
        if (drafts.length > words.length) return null;
        if (grouped && value == null && next.chosen.length === offset) {
            const confirmed = Number(saved.manualWordIndexBySlot?.[index] || 0);
            if (!Number.isInteger(confirmed) || confirmed < 0 || confirmed > drafts.length) return null;
            next.chosen.push(...drafts.slice(0, confirmed));
        }
        drafts.forEach((word, part) => { next.manualInputsBySlot[offset + part] = word; });
        next.attemptsBySlot[offset] = saved.attemptsBySlot?.[index] || 0;
        next.lastWrongBySlot[offset] = saved.lastWrongBySlot?.[index] ?? null;
        offset += words.length;
    }
    if (!syncPresentPerfectPassiveWordPath(next)) return null;
    if (saved.done && next.chosen.some(word => word == null)) return null;
    const fields = ['answers', 'answer', 'markers', 'markers_count', 'answer_map', 'chosen', 'manualInputsBySlot',
        'manualWordIndexBySlot', 'attemptsBySlot', 'lastWrongBySlot', 'wordSuggestionsBySlot', 'wordSearchRequestBySlot', 'activeSlot', 'slot'];
    const patch = Object.fromEntries(fields.map(key => [key, next[key]]));
    if (saved.feedbackMeta && Number.isInteger(saved.feedbackMeta.slotIndex)) {
        const before = saved.answers.slice(0, saved.feedbackMeta.slotIndex);
        patch.feedbackMeta = {...saved.feedbackMeta, slotIndex: before.reduce((sum, answer) => sum + String(answer).trim().split(/\s+/).length, 0)};
    }
    return patch;
}

function composeSentenceChoiceMatches(question, slotIndex, value) {
    const words = typeof presentPerfectPassiveNextWords === 'function' ? presentPerfectPassiveNextWords(question, slotIndex) : null;
    if (words !== null) return !/\s/.test(String(value).trim()) && words.some(word => EnglishAnswerVariants.normalize(word) === EnglishAnswerVariants.normalize(value));
    const sentences = question?.compose_accepted_sentences;
    if (!['4', 'compose_tokens'].includes(String(question?.type)) || !Array.isArray(sentences) || !sentences.length) return true;
    const before = Array.from({length: slotIndex}, (_, index) => question?.chosen?.[index]);
    if (before.some(part => part == null || String(part).trim() === '')) return false;
    const candidate = EnglishAnswerVariants.normalize([...before, value].join(' '));
    if (!candidate) return false;
    const last = slotIndex === (question?.answers?.length || 0) - 1;
    return sentences.some(sentence => EnglishAnswerVariants.variants(sentence).some(variant => {
        const expected = EnglishAnswerVariants.normalize(variant);
        return last ? expected === candidate : (expected === candidate || expected.startsWith(`${candidate} `));
    }));
}

// Exact finite opt-in for inverted question surfaces. Expanding an isolated
// "isn't" before a visible subject would otherwise manufacture "is not she".
// The server supplies reviewed complete marker spellings, not a grammar guess.
function reviewedQuestionSurfaceAnswers(question, slotIndex) {
    const policy = question?.qnq_strict_slot_surfaces;
    if (!['0', '4'].includes(String(question?.type))
        || !/^[a-f0-9]{64}$/.test(String(question?.psp_content_revision ?? ''))
        || policy?.revision !== question.psp_content_revision
        || !policy.answers_by_marker || typeof policy.answers_by_marker !== 'object'
        || Array.isArray(policy.answers_by_marker)) return null;

    const markers = Array.isArray(question.markers) ? question.markers : Object.keys(question.answer_map || {});
    const marker = markers[slotIndex] || `a${slotIndex + 1}`;
    const reviewed = policy.answers_by_marker[marker];
    const expected = String(question.answers?.[slotIndex] ?? '').trim();
    if (!Array.isArray(reviewed) || !reviewed.length || !expected
        || reviewed.some(value => typeof value !== 'string' || !value.trim())
        || !reviewed.some(value => EnglishAnswerVariants.normalize(value) === EnglishAnswerVariants.normalize(expected))) return null;

    return [...new Set(reviewed.map(value => value.trim()))];
}

function acceptedTestAnswers(question, slotIndex) {
    const words = typeof presentPerfectPassiveNextWords === 'function' ? presentPerfectPassiveNextWords(question, slotIndex) : null;
    if (words !== null) return words;
    const reviewed = typeof reviewedQuestionSurfaceAnswers === 'function'
        ? reviewedQuestionSurfaceAnswers(question, slotIndex) : null;
    if (reviewed !== null) {
        return reviewed.filter(value => composeSentenceChoiceMatches(question, slotIndex, value));
    }
    const markers = Array.isArray(question?.markers)
        ? question.markers
        : Object.keys(question?.answer_map || {});
    const marker = markers[slotIndex] || `a${slotIndex + 1}`;
    const byMarker = question?.accepted_answers_by_marker?.[marker];
    const bySlot = Array.isArray(question?.accepted_answers)
        ? question.accepted_answers[slotIndex]
        : null;
    const configured = Array.isArray(byMarker)
        ? byMarker
        : (Array.isArray(bySlot) ? bySlot : []);
    const expected = String(question?.answers?.[slotIndex] ?? '').trim();
    const context = EnglishAnswerVariants.contextFor(question, slotIndex);
    const withoutContext = EnglishAnswerVariants.variants(expected).map(EnglishAnswerVariants.normalize);
    const contextual = EnglishAnswerVariants.variants(expected, context).map(EnglishAnswerVariants.normalize);
    const aliases = configured.filter(variant => !withoutContext.includes(EnglishAnswerVariants.normalize(variant))
        || contextual.includes(EnglishAnswerVariants.normalize(variant)));
    const variants = [...aliases, expected].flatMap(variant => EnglishAnswerVariants.variants(variant, context));

    return variants
        .map((variant) => String(variant ?? '').trim())
        .filter((variant, index, all) => variant !== '' && all.indexOf(variant) === index
            && composeSentenceChoiceMatches(question, slotIndex, variant));
}

function genericReferenceAnswerCaptionsEnabled(question) {
    const revision = String(question?.psp_content_revision ?? '');
    return String(question?.type) === '0'
        && /^naqq-generic-reference-(?:a1|a2|b1|b2|c1|c2)-g[0-9]{2}$/.test(String(question?.uuid ?? ''))
        && /^[a-f0-9]{64}$/.test(revision)
        && question?.naqq_generic_reference_caption_revision === revision;
}

function genericReferenceNoArticleLabel() {
    return typeof testUi === 'function' ? testUi('question.no_article', {}, 'Без артикля') : 'Без артикля';
}

function testAnswerMatches(question, slotIndex, value) {
    if (typeof genericReferenceAnswerCaptionsEnabled === 'function' && genericReferenceAnswerCaptionsEnabled(question)
        && question.presentation !== 'sentence_reorder'
        && EnglishAnswerVariants.normalize(value) === EnglishAnswerVariants.normalize(genericReferenceNoArticleLabel())) {
        // Keep the canonical omission value for grading; never accept a blank.
        value = '-';
    }
    const normalized = canonicalTestAnswer(value);

    return normalized !== '' && acceptedTestAnswers(question, slotIndex)
        .some((accepted) => EnglishAnswerVariants.normalize(accepted) === EnglishAnswerVariants.normalize(value));
}

function isPassivePhraseGapQuestion(question, requireWordInputs = false) {
    return question?.passive_gap_phrase_input === true
        && /^[a-f0-9]{64}$/.test(String(question.psp_content_revision || ''))
        && String(question.type) === '0'
        && question.presentation !== 'sentence_reorder'
        // The same bank can appear in other tests. Only this mixed page opts
        // its reviewed gap rows into the existing word-by-word phrase UI.
        && (!requireWordInputs || question.passive_phrase_word_inputs === true
            || (typeof TEST_SLUG !== 'undefined'
                && TEST_SLUG === 'word-order/inversion-after-negative-adverbials'
                && /^woq-negative-adverbial-inve-[a-f0-9]{8}$/.test(String(question.uuid || ''))));
}

function isReviewedComposePhraseSlot(question, slotIndex = Number(question?.activeSlot ?? 0)) {
    if (String(question?.type) !== '4'
        || !/^[a-f0-9]{64}$/.test(String(question?.psp_content_revision ?? ''))
        || !/^[a-f0-9]{64}$/.test(String(question?.compose_content_revision ?? ''))
        || !Array.isArray(question?.compose_accepted_sentences) || !question.compose_accepted_sentences.length
        || !Array.isArray(question?.answers) || !Number.isInteger(slotIndex)
        || slotIndex < 0 || slotIndex >= question.answers.length) return false;

    return /\s/.test(String(question.answers[slotIndex]).trim())
        || acceptedTestAnswers(question, slotIndex).some(answer => /\s/.test(String(answer).trim()));
}

function normalizeManualGapInput(question, value, slotIndex = Number(question?.activeSlot ?? 0)) {
    const text = String(value ?? '');
    return isPassivePhraseGapQuestion(question) || isReviewedComposePhraseSlot(question, slotIndex)
        ? text.replace(/\s+/g, ' ') : text.replace(/\s+/g, '');
}

function composeManualAnswerWords(question, slotIndex) {
    const expected = String(question?.answers?.[slotIndex] ?? '').trim();
    const phraseWords = isPassivePhraseGapQuestion(question, true);
    const wordIndex = Number(question?.manualWordIndexBySlot?.[slotIndex] || 0);
    if (!Number.isInteger(wordIndex) || wordIndex <= 0) {
        return phraseWords ? expected.split(/\s+/).filter(Boolean) : [expected];
    }

    const normalize = EnglishAnswerVariants.normalize;
    const confirmedWords = normalize(question?.manualInputsBySlot?.[slotIndex])
        .split(' ')
        .slice(0, wordIndex)
        .join(' ');

    // One logical answer may take several words (haven't / have not). Follow
    // the spelling the learner started, adding the next field only after a
    // correct prefix. An incorrect current word must not change that spelling.
    const variants = acceptedTestAnswers(question, slotIndex);
    const matching = variants.find((variant) => normalize(variant).startsWith(`${confirmedWords} `));
    const words = String(matching ?? expected).split(/\s+/).filter(Boolean);

    // Keep markers/options unchanged: extra inputs belong to the same answer.
    return words.length ? (phraseWords ? words : words.slice(0, wordIndex + 1)) : [''];
}

// Only exact server-decorated finite rows hide sentence punctuation in button captions.
// Raw options remain untouched for click handling, aliases, grading and saved progress.
function finitePracticeOptionCaption(question, value) {
    const raw = String(value ?? '');
    if (typeof genericReferenceAnswerCaptionsEnabled === 'function' && genericReferenceAnswerCaptionsEnabled(question)) {
        if (raw.trim() === '-') return genericReferenceNoArticleLabel();
        return EnglishAnswerVariants.withoutSentencePunctuation(raw);
    }
    const revision = String(question?.psp_content_revision ?? '');
    const uuid = String(question?.uuid ?? '');
    const ownsAdjectives = /^aaq-(?:adverbs|degrees|comparative|equality|order|eding|intensifiers)-(?:a1|a2|b1|b2|c1|c2)-(?:g|c)[0-9]{2}$/.test(uuid)
        && question?.aaq_option_caption_revision === revision;
    const ownsSomeAny = /^saq-(?:people|places|things)-(?:a1|a2|b1|b2|c1|c2)-(?:g|c)[0-9]{2}$/.test(uuid)
        && question?.saq_option_caption_revision === revision;
    const ownsPassiveVoice = /^pvq-(?:formation|causative|mistakes|style|get|impersonal|modals|negatives|phrasal|restrictions|two-object|gerund|infinitive|agent|future-continuous|future-perfect|future-simple|past-continuous|past-perfect|past-simple|present-continuous|present-perfect|present-simple|advanced|reporting|complex-causative|complex-impersonal)-(?:a1|a2|b1|b2|c1|c2)-(?:g|c)[0-9]{2}$/.test(uuid)
        && question?.pvq_option_caption_revision === revision;
    const ownsModalVerbs = /^mvq-(?:cc|mm|mht|sot|need|ded|past|requests|perfect|subtle)-(?:a1|a2|b1|b2|c1|c2)-(?:g|c)[0-9]{2}$/.test(uuid)
        && question?.mvq_option_caption_revision === revision;
    const ownsConditionals = /^cqq-(?:zero|first|second|third|mixed|unless|case|wish|rather|better|conditions|advanced|alternatives)-(?:a1|a2|b1|b2|c1|c2)-(?:g|c)[0-9]{2}$/.test(uuid)
        && question?.cqq_option_caption_revision === revision;
    const ownsReportedSpeech = /^rsq-(?:statements|questions|commands|backshift|timeplace|saytell|advanced)-(?:a1|a2|b1|b2|c1|c2)-(?:g|c)[0-9]{2}$/.test(uuid)
        && question?.rsq_option_caption_revision === revision;
    const ownsClausesLinking = /^clq-(?:relative|defining|nondefining|omitting|whosewhich|reason|result|contrast|purpose|linking|reasonresult|advanced|concessive|partbasics|participle|absolute|discourse)-(?:a1|a2|b1|b2|c1|c2)-(?:g|c)[0-9]{2}$/.test(uuid)
        && question?.clq_option_caption_revision === revision;
    const ownsPrepositionsPhrasal = /^ppq-(?:time|place|movement|dependent|basics|separable|common)-(?:a1|a2|b1|b2|c1|c2)-(?:g|c)[0-9]{2}$/.test(uuid)
        && question?.ppq_option_caption_revision === revision;
    const ownsCommonMistakes = /^cmq-(?:articles|perfect|countable|prepositions|traps)-(?:a1|a2|b1|b2|c1|c2)-(?:g|c)[0-9]{2}$/.test(uuid)
        && question?.cmq_option_caption_revision === revision;
    const ownsSentenceTransformations = /^stq-(?:tenses|modals|passive|reported|conditionals|clauses|articles)-(?:a1|a2|b1|b2|c1|c2)-(?:g|c)[0-9]{2}$/.test(uuid)
        && question?.stq_option_caption_revision === revision;
    const ownsVerbPatterns = /^vpq-(?:gerund|to|bare|contrast|verbs-gerund|verbs-infinitive|meaning|used|advanced)-(?:a1|a2|b1|b2|c1|c2)-(?:g|c)[0-9]{2}$/.test(uuid)
        && question?.vpq_option_caption_revision === revision;
    const ownsMixedRevision = /^mrq-(?:a1|a2|b1|b2|c1|c2)-(?:a1|a2|b1|b2|c1|c2)-(?:g|c)[0-9]{2}$/.test(uuid)
        && question?.mrq_option_caption_revision === revision;
    const ownsAcademicEnglish = /^aeq-(?:hcb|hed|sta|arg)-(?:a1|a2|b1|b2|c1|c2)-(?:g|c)[0-9]{2}$/.test(uuid)
        && question?.aeq_option_caption_revision === revision;
    const ownsArticlesQuantifiers = /^aqv-(?:aqn|pad)-(?:a1|a2|b1|b2|c1|c2)-(?:g|c)[0-9]{2}$/.test(uuid)
        && question?.aqv_option_caption_revision === revision;
    const ownsFormalEnglish = /^feq-(?:bas|nom|ton|par|sub|sty)-(?:a1|a2|b1|b2|c1|c2)-(?:g|c)[0-9]{2}$/.test(uuid)
        && question?.feq_option_caption_revision === revision;
    const ownsRelativeClauses = /^rcq-(?:crc)-(?:a1|a2|b1|b2|c1|c2)-(?:g|c)[0-9]{2}$/.test(uuid)
        && question?.rcq_option_caption_revision === revision;
    const ownsSentenceStructure = /^ssq-(?:csb|cse|esr|cnp)-(?:a1|a2|b1|b2|c1|c2)-(?:g|c)[0-9]{2}$/.test(uuid)
        && question?.ssq_option_caption_revision === revision;
    const ownsVocabCollocations = /^vcq-(?:aclc)-(?:a1|a2|b1|b2|c1|c2)-(?:g|c)[0-9]{2}$/.test(uuid)
        && question?.vcq_option_caption_revision === revision;
    const supported = (String(question?.type) === '0' && question?.presentation === 'sentence_reorder')
        || (String(question?.type) === '4' && question?.compose_content_revision === revision);
    if (!(ownsAdjectives || ownsSomeAny || ownsPassiveVoice || ownsModalVerbs || ownsConditionals || ownsReportedSpeech || ownsClausesLinking || ownsPrepositionsPhrasal || ownsCommonMistakes || ownsSentenceTransformations || ownsVerbPatterns || ownsMixedRevision || ownsAcademicEnglish || ownsArticlesQuantifiers || ownsFormalEnglish || ownsRelativeClauses || ownsSentenceStructure || ownsVocabCollocations) || !supported || !/^[a-f0-9]{64}$/.test(revision) || !raw.trim()
        || typeof EnglishAnswerVariants?.withoutSentencePunctuation !== 'function') return raw;
    return EnglishAnswerVariants.withoutSentencePunctuation(raw);
}

function formatAnswerOptionLabel(question, slotIndex, option) {
    if (typeof presentPerfectPassiveWordPolicy === 'function' && presentPerfectPassiveWordPolicy(question)) {
        return EnglishAnswerVariants.withoutSentencePunctuation(option);
    }
    const caption = value => typeof finitePracticeOptionCaption === 'function'
        ? finitePracticeOptionCaption(question, value) : String(value ?? '');
    const markers = Array.isArray(question?.markers)
        ? question.markers
        : Object.keys(question?.answer_map || {});
    const marker = markers[slotIndex] || `a${slotIndex + 1}`;
    const expected = String(question?.answers?.[slotIndex] ?? '').trim();

    if (canonicalTestAnswer(option) !== canonicalTestAnswer(expected)) {
        return caption(option);
    }

    const configuredTokens = question?.answer_synonym_tokens_by_marker?.[marker];

    if (configuredTokens && typeof configuredTokens === 'object' && !Array.isArray(configuredTokens)) {
        return String(option ?? '')
            .split(/(\s+)/)
            .map((token) => {
                const entry = Object.entries(configuredTokens)
                    .find(([canonical]) => canonicalTestAnswer(canonical) === canonicalTestAnswer(token));

                if (!entry || !Array.isArray(entry[1])) {
                    return caption(token);
                }

                const aliases = entry[1]
                    .map((synonym) => String(synonym ?? '').trim())
                    .filter((synonym, index, all) => synonym !== ''
                        && canonicalTestAnswer(synonym) !== canonicalTestAnswer(token)
                        && all.findIndex((candidate) => canonicalTestAnswer(candidate) === canonicalTestAnswer(synonym)) === index);

                return aliases.length > 0 ? `${caption(token)} (${aliases.map(caption).join(', ')})` : caption(token);
            })
            .join('');
    }

    const configured = question?.answer_synonyms_by_marker?.[marker];
    if (!Array.isArray(configured)) {
        return caption(option);
    }

    const synonyms = configured
        .map((synonym) => String(synonym ?? '').trim())
        .filter((synonym, index, all) => synonym !== ''
            && canonicalTestAnswer(synonym) !== canonicalTestAnswer(option)
            && all.findIndex((candidate) => canonicalTestAnswer(candidate) === canonicalTestAnswer(synonym)) === index);

    return synonyms.length > 0
        ? `${caption(option)} (${synonyms.map(caption).join(', ')})`
        : caption(option);
}

function html(str) {
    return String(str)
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

// Use the exact category or finite builder authorship, never the URL or lesson text.
function isPastPerfectContinuousQuestion(q) {
    if (typeof q?.is_past_perfect_continuous === 'boolean') return q.is_past_perfect_continuous;
    if (String(q?.tense ?? '').split('/').pop().trim().toLowerCase() === 'past perfect continuous') return true;
    const seeder = String(q?.tech_info?.seeder?.class ?? q?.seeder ?? '');
    return [
        'Database\\Seeders\\V3\\Polyglot\\PolyglotPastPerfectContinuousFormsAllLevelsLessonSeeder',
        'Database\\Seeders\\V3\\Polyglot\\PolyglotPastPerfectContinuousNegativesAllLevelsLessonSeeder',
        'Database\\Seeders\\V3\\Polyglot\\PolyglotPastPerfectContinuousQuestionsAllLevelsLessonSeeder',
        'Database\\Seeders\\V3\\Polyglot\\PolyglotPastPerfectContinuousTimeExpressionsAllLevelsLessonSeeder',
        'Database\\Seeders\\V3\\Polyglot\\PolyglotPastPerfectContinuousBasicsB2LessonSeeder',
    ].includes(seeder);
}

function clearManualAnswerFeedbackOnEdit(q, slotIndex, wordIndex) {
    const meta = q?.feedbackMeta;
    if ((!isPastPerfectContinuousQuestion(q) && !isPassivePhraseGapQuestion(q)) || q.done || meta?.result !== 'incorrect'
        || meta.slotIndex !== slotIndex
        || (Number.isInteger(meta.wordIndex) && meta.wordIndex !== wordIndex)) return false;

    q.feedback = '';
    q.feedbackMeta = null;
    q.explanation = '';
    q.pendingExplanationKey = null;
    if (Array.isArray(q.lastWrongBySlot)) q.lastWrongBySlot[slotIndex] = null;
    // Keep attempts and wrongAttempt: editing is not a new first attempt.
    return true;
}

// Older snapshots can contain a canonical answer silently revealed after two
// mistakes. Reopen only a slot for which the recorded submission proves this.
function restoreSavedPpcRetryState(q) {
    const meta = q?.feedbackMeta;
    const slot = meta?.slotIndex;
    if (!isPastPerfectContinuousQuestion(q) || meta?.result !== 'incorrect'
        || !Array.isArray(q.answers) || !Array.isArray(q.chosen)
        || !Number.isInteger(slot) || slot < 0 || slot >= (q.answers?.length ?? 0)
        || meta.wordIndex !== null || typeof q.done !== 'boolean' || !q.wrongAttempt || !q.chosen?.[slot]
        || !testAnswerMatches(q, slot, q.chosen[slot])
        || !String(meta.submittedAnswer ?? '').trim()
        || testAnswerMatches(q, slot, meta.submittedAnswer)) return false;

    q.chosen[slot] = null;
    q.activeSlot = slot;
    q.done = false;
    q.wrongAttempt = true;
    q.manualInputsBySlot[slot] = '';
    q.manualWordIndexBySlot[slot] = 0;
    q.attemptsBySlot[slot] = Math.max(2, Number(q.attemptsBySlot[slot]) || 0);
    q.lastWrongBySlot[slot] = meta.submittedAnswer;
    q.feedback = '';
    q.feedbackMeta = null;
    q.explanation = '';
    q.pendingExplanationKey = null;
    return true;
}

// Authored PPC help is a disclosure, never part of the translation heading.
function getAuthoredComposeHelpText(q) {
    return String(q?.type ?? '') === '4'
        && /^[a-f0-9]{64}$/.test(String(q?.compose_content_revision ?? ''))
        && typeof q?.hint === 'string'
        ? q.hint.trim()
        : '';
}

function renderAuthoredComposeHelp(q) {
    const hint = getAuthoredComposeHelpText(q);
    return hint
        ? `<p data-authored-compose-hint class="text-sm text-gray-600 whitespace-pre-line leading-normal font-normal">${html(hint)}</p>`
        : '';
}

function toggleAuthoredComposeHelp(q, panelId, button) {
    if (!getAuthoredComposeHelpText(q)) return false;
    const panel = document.getElementById(panelId);
    if (!panel) return true;
    const opened = !panel.hasAttribute('data-authored-compose-help-open');
    panel.innerHTML = opened ? renderAuthoredComposeHelp(q) : '';
    panel.toggleAttribute('data-authored-compose-help-open', opened);
    if (button) {
        button.setAttribute('aria-expanded', String(opened));
        button.setAttribute('aria-controls', panelId);
        const label = button.querySelector('[data-authored-compose-help-label]');
        if (label) label.textContent = testUi(opened ? 'question.hide_help' : 'question.show_help');
    }
    return true;
}

function techInfoUi(key, fallback = '') {
    return testUi(`tech_info.${key}`, {}, fallback);
}

function hasTechnicalValue(value) {
    if (Array.isArray(value)) {
        return value.length > 0;
    }

    return value !== null && value !== undefined && String(value).trim() !== '';
}

function technicalValue(value) {
    return hasTechnicalValue(value) ? String(value) : techInfoUi('not_available', 'N/A');
}

function renderTechnicalInfoField(label, value, options = {}) {
    const classes = ['rounded-xl border px-3 py-2.5'];
    if (options.colSpan) {
        classes.push('sm:col-span-2');
    }

    const primaryTag = options.code ? 'code' : 'div';
    const primaryClass = options.code
        ? 'mt-1 block break-all font-mono text-[12px] leading-5'
        : 'mt-1 text-[13px] leading-5';
    const secondary = hasTechnicalValue(options.secondary)
        ? `<div class="mt-1 break-all font-mono text-[11px] leading-5" style="color:#64748b;">${html(options.secondary)}</div>`
        : '';

    return `
        <div class="${classes.join(' ')}" style="border-color:#dbe4f0;background:#f8fbff;">
            <div class="text-[10px] font-extrabold uppercase tracking-[0.14em]" style="color:#64748b;">${html(label)}</div>
            <${primaryTag} class="${primaryClass}" style="color:#0f172a;">${html(technicalValue(value))}</${primaryTag}>
            ${secondary}
        </div>
    `;
}

function getTechnicalQuestions() {
    return Array.isArray(window.__INITIAL_JS_TEST_QUESTIONS__)
        ? window.__INITIAL_JS_TEST_QUESTIONS__.map(EnglishAnswerVariants.prepareQuestion)
        : [];
}

function getMatchQuestionByKey(key) {
    if (!key) {
        return null;
    }

    const questions = getTechnicalQuestions();
    if (key.startsWith('q-')) {
        const id = key.slice(2);
        return questions.find((question) => String(question?.id ?? '') === id) || null;
    }

    if (key.startsWith('idx-')) {
        const index = Number.parseInt(key.slice(4), 10);
        return Number.isInteger(index) ? (questions[index] || null) : null;
    }

    return null;
}

function resolveTechnicalQuestion(container) {
    const questions = getTechnicalQuestions();

    if (container.matches('article[data-idx]')) {
        const index = Number.parseInt(container.dataset.idx || '', 10);
        return Number.isInteger(index) ? (questions[index] || null) : null;
    }

    if (container.classList.contains('drag-quiz__row')) {
        const rows = Array.from(container.parentElement?.querySelectorAll('.drag-quiz__row') || []);
        const index = rows.indexOf(container);
        return index >= 0 ? (questions[index] || null) : null;
    }

    if (container.classList.contains('match-card') && container.classList.contains('match-sentence')) {
        return getMatchQuestionByKey(container.dataset.key || '');
    }

    return null;
}

function renderTechnicalInfoBlock(question, context = 'default') {
    if (!window.__SHOW_TEST_TECH_INFO__ || !question || !question.tech_info) {
        return '';
    }

    const info = question.tech_info || {};
    const source = info.source || {};
    const theoryPage = info.theory_page || {};
    const seeder = info.seeder || {};
    const type = info.type || {};
    const markers = Array.isArray(info.markers) ? info.markers.join(', ') : '';
    const tags = Array.isArray(info.tags) ? info.tags.join(', ') : '';
    const typeValue = hasTechnicalValue(type.label)
        ? `${type.label}${hasTechnicalValue(type.value) ? ` (${type.value})` : ''}`
        : type.value;
    const wrapperStyles = context === 'drag'
        ? 'grid-column: 2 / -1; margin-top: 10px;'
        : 'margin-top: 14px;';

    const fields = [
        renderTechnicalInfoField(techInfoUi('question_id', 'Question ID'), info.question_id),
        renderTechnicalInfoField(techInfoUi('question_uuid', 'Question UUID'), info.question_uuid, { code: true }),
        renderTechnicalInfoField(techInfoUi('difficulty', 'Difficulty'), info.difficulty),
        renderTechnicalInfoField(techInfoUi('level', 'Level'), info.level),
        renderTechnicalInfoField(techInfoUi('category', 'Category'), info.category),
        renderTechnicalInfoField(techInfoUi('source', 'Source'), source.name, {
            secondary: hasTechnicalValue(source.id)
                ? `${techInfoUi('source_id', 'Source ID')}: ${source.id}`
                : '',
            colSpan: true,
        }),
        (hasTechnicalValue(theoryPage.title) || hasTechnicalValue(theoryPage.id) || hasTechnicalValue(theoryPage.slug))
            ? renderTechnicalInfoField(techInfoUi('theory_page', 'Theory page'), theoryPage.title || theoryPage.slug || theoryPage.id, {
                secondary: [
                    hasTechnicalValue(theoryPage.id) ? `${techInfoUi('theory_page_id', 'Theory page ID')}: ${theoryPage.id}` : '',
                    hasTechnicalValue(theoryPage.slug) ? `${techInfoUi('theory_page_slug', 'Theory page slug')}: ${theoryPage.slug}` : '',
                ].filter(Boolean).join(' · '),
                colSpan: true,
            })
            : '',
        renderTechnicalInfoField(techInfoUi('seeder', 'Seeder'), seeder.name || seeder.class, {
            secondary: seeder.class && seeder.class !== seeder.name ? seeder.class : '',
            code: true,
            colSpan: true,
        }),
        renderTechnicalInfoField(techInfoUi('seeder_run', 'Latest seeder run'), seeder.last_ran_at),
        renderTechnicalInfoField(techInfoUi('type', 'Type'), typeValue),
        renderTechnicalInfoField(techInfoUi('markers', 'Markers'), markers || null),
        renderTechnicalInfoField(techInfoUi('markers_count', 'Markers count'), info.markers_count),
        renderTechnicalInfoField(techInfoUi('answers_count', 'Answers count'), info.answers_count),
        renderTechnicalInfoField(techInfoUi('options_count', 'Options count'), info.options_count),
        renderTechnicalInfoField(techInfoUi('variants_count', 'Variants count'), info.variants_count),
        renderTechnicalInfoField(techInfoUi('theory_block', 'Theory block UUID'), info.theory_text_block_uuid, {
            code: true,
        }),
        renderTechnicalInfoField(techInfoUi('tags', 'Tags'), tags || null, { colSpan: true }),
        renderTechnicalInfoField(techInfoUi('created_at', 'Created at'), info.created_at),
        renderTechnicalInfoField(techInfoUi('updated_at', 'Updated at'), info.updated_at),
    ].join('');

    const v3NamespaceBlock = seeder.is_v3 && hasTechnicalValue(seeder.v3_path_after_ai)
        ? `
            <div class="rounded-2xl border px-3.5 py-3" style="border-color:#bfdbfe;background:linear-gradient(180deg,#eff6ff 0%,#f8fbff 100%);">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <div class="text-[10px] font-extrabold uppercase tracking-[0.16em]" style="color:#1d4ed8;">${html(techInfoUi('v3_namespace', 'V3 namespace'))}</div>
                    <div class="rounded-full px-2.5 py-1 text-[10px] font-extrabold uppercase tracking-[0.14em]" style="background:#dbeafe;color:#1e40af;">
                        ${html(techInfoUi('llm', 'LLM'))}: ${html(technicalValue(seeder.llm_display_name || seeder.llm))}
                    </div>
                </div>
                <code class="mt-2 block break-all font-mono text-[12px] leading-5" style="color:#0f172a;">${html(seeder.v3_path_after_ai)}</code>
            </div>
        `
        : '';

    return `
        <details class="js-tech-info-block rounded-2xl border" style="${wrapperStyles} border-color:#cbd5e1;background:#ffffff;">
            <summary class="cursor-pointer list-none px-4 py-3 text-[11px] font-extrabold uppercase tracking-[0.18em]" style="color:#334155;">
                ${html(techInfoUi('summary', 'Technical info'))}
            </summary>
            <div class="border-t px-4 pb-4 pt-3" style="border-color:#e2e8f0;">
                <div class="space-y-3">
                    ${v3NamespaceBlock}
                    <div class="grid gap-2 sm:grid-cols-2">
                        ${fields}
                    </div>
                </div>
            </div>
        </details>
    `;
}

function getQuestionReportObjectValue(path) {
    const source = window.__QUESTION_REPORT_UI__ || {};
    return String(path || '')
        .split('.')
        .reduce((acc, segment) => {
            if (acc && typeof acc === 'object' && Object.prototype.hasOwnProperty.call(acc, segment)) {
                return acc[segment];
            }

            return undefined;
        }, source);
}

function questionReportUi(key, fallback = '') {
    const adminValue = getQuestionReportObjectValue(key);

    if (adminValue !== undefined) {
        return adminValue;
    }

    return testUi(`question_report.${key}`, {}, fallback);
}

function reportQuestionId(question) {
    const id = question?.id ?? question?.question_id ?? question?.tech_info?.question_id ?? '';

    return String(id ?? '').trim();
}

function reportQuestionUuid(question) {
    const uuid = question?.uuid ?? question?.question_uuid ?? question?.tech_info?.question_uuid ?? '';

    return String(uuid ?? '').trim();
}

function reportQuestionText(question) {
    return String(question?.question ?? question?.sourceTextUk ?? question?.text ?? '').trim();
}

function questionReportIssueCatalog() {
    const catalog = window.__QUESTION_REPORT_ISSUE_CATALOG__;

    return Array.isArray(catalog) ? catalog : [];
}

function questionReportIssueCheckboxes() {
    const items = questionReportIssueCatalog();
    if (items.length === 0) {
        return '';
    }

    const title = html(questionReportUi('title', 'Що не так із питанням?'));
    const note = html(questionReportUi('comment_optional', 'Коментар необов’язковий, якщо вибрано тип помилки'));
    const otherHint = html(questionReportUi('other_requires_comment_hint', 'Опишіть проблему в коментарі'));

    const rows = items.map((issue) => {
        const key = String(issue?.key ?? issue?.slug ?? '');
        const label = String(issue?.label ?? key);
        const description = String(issue?.description ?? '');
        if (key === '') {
            return '';
        }

        const labelText = html(label);
        const descriptionMarkup = description
            ? `<span class="mt-0.5 block text-xs leading-5" style="color:#9a3412;">${html(description)}</span>`
            : '';

        return `
            <label class="flex items-start gap-2 rounded-xl border px-3 py-2 transition hover:bg-orange-50"
                   style="border-color:#fed7aa;background:#fffaf2;">
                <input type="checkbox"
                       name="issue_types[]"
                       value="${html(key)}"
                       class="mt-1 h-4 w-4 rounded border-orange-300 text-orange-600 focus:ring-orange-400"
                       data-question-report-issue>
                <span class="block">
                    <span class="block text-sm font-semibold" style="color:#7c2d12;">${labelText}</span>
                    ${descriptionMarkup}
                </span>
            </label>
        `;
    }).filter(Boolean).join('');

    return `
        <div class="mb-3">
            <div class="text-sm font-bold" style="color:#7c2d12;">${title}</div>
            <p class="mt-1 text-xs" style="color:#b45309;">${note}</p>
            <div class="mt-2 grid gap-2 sm:grid-cols-2">${rows}</div>
            <p class="mt-2 hidden text-xs font-semibold" style="color:#9a3412;" data-question-report-other-hint>${otherHint}</p>
        </div>
    `;
}

function renderQuestionReportBlock(question, context = 'default') {
    if (!window.__IS_ADMIN__ || !question) {
        return '';
    }

    const questionId = reportQuestionId(question);
    const questionUuid = reportQuestionUuid(question);

    if (!questionId && !questionUuid) {
        return '';
    }

    const questionText = reportQuestionText(question);
    const wrapperStyles = context === 'drag'
        ? 'grid-column: 2 / -1; margin-top: 10px;'
        : 'margin-top: 14px;';
    const checkboxes = questionReportIssueCheckboxes();

    return `
        <div class="js-question-report-block rounded-2xl border" style="${wrapperStyles} border-color:#fed7aa;background:#fff7ed;"
             data-question-id="${html(questionId)}"
             data-question-uuid="${html(questionUuid)}"
             data-question-text="${html(questionText)}">
            <div class="flex flex-wrap items-center justify-between gap-2 px-4 py-3">
                <div>
                    <div class="text-[10px] font-extrabold uppercase tracking-[0.16em]" style="color:#c2410c;">${html(questionReportUi('admin_only', 'Admin only'))}</div>
                    <div class="mt-1 flex flex-wrap items-center gap-2 text-sm font-semibold" style="color:#7c2d12;">
                        <span>${html(questionReportUi('admin_panel_title', 'Question report'))}</span>
                        <span class="hidden rounded-full bg-amber-200 px-2 py-0.5 text-[11px] font-extrabold uppercase tracking-[0.12em]" style="color:#7c2d12;" data-question-report-badge></span>
                    </div>
                </div>
                <button type="button"
                    class="rounded-xl border px-3 py-2 text-xs font-extrabold uppercase tracking-[0.12em] transition hover:bg-orange-100"
                    style="border-color:#fdba74;color:#9a3412;"
                    data-question-report-toggle>
                    ${html(questionReportUi('toggle', 'Report'))}
                </button>
            </div>
            <div class="border-t" style="border-color:#fed7aa;" data-question-report-existing></div>
            <form class="hidden border-t px-4 pb-4 pt-3" style="border-color:#fed7aa;" data-question-report-form>
                ${checkboxes}
                <label class="block text-xs font-bold uppercase tracking-[0.14em]" style="color:#9a3412;">
                    ${html(questionReportUi('comment_label', 'Comment'))}
                </label>
                <p class="mt-1 text-xs" style="color:#b45309;">${html(questionReportUi('comment_optional', 'Comment is optional when an issue type is selected'))}</p>
                <textarea
                    class="mt-2 min-h-[84px] w-full rounded-xl border px-3 py-2 text-sm leading-6 focus:outline-none focus:ring-2"
                    style="border-color:#fdba74;--tw-ring-color:#fb923c;"
                    name="comment"
                    maxlength="4000"
                    placeholder="${html(questionReportUi('comment_placeholder', 'Describe what is wrong in the question or answer.'))}"></textarea>
                <div class="mt-3 flex flex-wrap items-center gap-3">
                    <button type="submit"
                        class="rounded-xl bg-orange-600 px-4 py-2 text-sm font-bold text-white transition hover:bg-orange-700"
                        data-question-report-submit>
                        ${html(questionReportUi('submit', 'Send report'))}
                    </button>
                    <span class="text-sm" style="color:#9a3412;" data-question-report-status></span>
                </div>
            </form>
        </div>
    `;
}

function questionReportLookupKeys(block) {
    const keys = [];
    const id = (block?.dataset?.questionId || '').trim();
    const uuid = (block?.dataset?.questionUuid || '').trim();
    if (id) keys.push(id);
    if (uuid && uuid !== id) keys.push(uuid);

    return keys;
}

function questionReportFindExisting(block) {
    const map = window.__QUESTION_REPORT_BY_QUESTION__;
    if (!map || typeof map !== 'object') {
        return [];
    }

    const seen = new Set();
    const collected = [];
    for (const key of questionReportLookupKeys(block)) {
        const list = map[key];
        if (!Array.isArray(list)) continue;
        for (const card of list) {
            if (!card || typeof card !== 'object') continue;
            const id = String(card.id || '');
            if (id === '' || seen.has(id)) continue;
            seen.add(id);
            collected.push(card);
        }
    }

    return collected;
}

function questionReportIssueLabel(slug) {
    const entry = questionReportIssueCatalog().find((item) => item?.key === slug || item?.slug === slug);

    return entry ? String(entry.label || slug) : String(slug);
}

function questionReportFormatDate(iso) {
    if (!iso) return '';
    const d = new Date(iso);
    if (Number.isNaN(d.getTime())) return String(iso);
    const pad = (n) => String(n).padStart(2, '0');

    return `${pad(d.getDate())}.${pad(d.getMonth() + 1)}.${d.getFullYear()} ${pad(d.getHours())}:${pad(d.getMinutes())}`;
}

function createQuestionReportExistingPanel(reports) {
    if (!Array.isArray(reports) || reports.length === 0) {
        return null;
    }

    const root = document.createElement('div');
    root.className = 'px-4 py-3';
    root.dataset.questionReportExistingInner = '1';

    const heading = document.createElement('div');
    heading.className = 'text-[10px] font-extrabold uppercase tracking-[0.16em]';
    heading.style.color = '#9a3412';
    heading.textContent = questionReportUi('admin_panel_title', 'Question report');
    root.appendChild(heading);

    const list = document.createElement('ul');
    list.className = 'mt-2 space-y-2';

    reports.forEach((card) => {
        const item = document.createElement('li');
        item.className = 'rounded-xl border px-3 py-2';
        item.style.borderColor = '#fdba74';
        item.style.background = '#ffedd5';

        const issueTypes = Array.isArray(card.issue_types)
            ? card.issue_types
            : (Array.isArray(card.issues) ? card.issues : []);
        const issueLabels = Array.isArray(card.issue_labels) ? card.issue_labels : [];
        const chips = document.createElement('div');
        chips.className = 'flex flex-wrap items-center gap-1.5';

        if (issueTypes.length > 0 || issueLabels.length > 0) {
            const labels = issueLabels.length > 0
                ? issueLabels
                : issueTypes.map((slug) => questionReportIssueLabel(slug));
            labels.forEach((label) => {
                const chip = document.createElement('span');
                chip.className = 'inline-flex items-center rounded-full bg-orange-200 px-2 py-0.5 text-[11px] font-bold';
                chip.style.color = '#7c2d12';
                chip.textContent = String(label || '');
                chips.appendChild(chip);
            });
        } else {
            const chip = document.createElement('span');
            chip.className = 'inline-flex items-center rounded-full bg-orange-100 px-2 py-0.5 text-[11px] font-bold';
            chip.style.color = '#9a3412';
            chip.textContent = questionReportUi('no_issue_type', 'Issue type not specified');
            chips.appendChild(chip);
        }

        item.appendChild(chips);

        const comment = String(card.comment || '').trim();
        const commentBlock = document.createElement('div');
        commentBlock.className = 'mt-1 whitespace-pre-line text-sm';
        commentBlock.style.color = comment ? '#7c2d12' : '#9a3412';
        commentBlock.textContent = comment || questionReportUi('no_comment', 'No comment');
        item.appendChild(commentBlock);

        const meta = [
            questionReportFormatDate(card.reported_at),
            card?.seeder?.class ? String(card.seeder.class) : '',
        ].filter(Boolean).join(' · ');

        if (meta) {
            const metaNode = document.createElement('div');
            metaNode.className = 'mt-1 text-[11px] uppercase tracking-[0.14em]';
            metaNode.style.color = '#9a3412';
            metaNode.textContent = meta;
            item.appendChild(metaNode);
        }

        list.appendChild(item);
    });

    root.appendChild(list);

    return root;
}

function renderQuestionReportExistingForBlock(block) {
    if (!block) return;
    const slot = block.querySelector('[data-question-report-existing]');
    if (!slot) return;

    const reports = questionReportFindExisting(block);
    slot.replaceChildren();

    const panel = createQuestionReportExistingPanel(reports);
    if (panel) {
        slot.appendChild(panel);
    }

    const badge = block.querySelector('[data-question-report-badge]');
    if (badge) {
        badge.textContent = questionReportUi('admin_badge', 'Has report');
        badge.classList.toggle('hidden', reports.length === 0);
    }

    const wrapper = block.closest('article[data-idx]')
        || block.closest('.drag-quiz__row')
        || block.closest('.match-card');
    if (wrapper) {
        if (reports.length > 0) {
            wrapper.dataset.questionReported = '1';
        } else {
            delete wrapper.dataset.questionReported;
        }
    }
}

function questionReportRecordLocally(report) {
    if (!report || typeof report !== 'object') return;
    const map = window.__QUESTION_REPORT_BY_QUESTION__ = window.__QUESTION_REPORT_BY_QUESTION__ || {};
    const card = {
        id: report.id || null,
        status: report.status || 'open',
        reported_at: report.reported_at || new Date().toISOString(),
        comment: report.comment || '',
        issue_types: Array.isArray(report.issue_types) ? report.issue_types : (Array.isArray(report.issues) ? report.issues : []),
        issue_labels: Array.isArray(report.issue_labels) ? report.issue_labels : [],
        seeder: report.seeder || {},
        test: report.test || {},
    };
    const keys = [String(report.question_id || ''), String(report.question_uuid || '')]
        .filter((key) => key !== '');
    for (const key of keys) {
        const list = map[key] = map[key] || [];
        if (!list.some((existing) => existing.id === card.id)) {
            list.push(card);
        }
    }
}

function resolveReportQuestion(container) {
    return resolveTechnicalQuestion(container);
}

function injectQuestionReportBlock(container) {
    if (!window.__IS_ADMIN__ || !container) {
        return;
    }

    const hasExisting = Array.from(container.children || []).some((child) => child.classList?.contains('js-question-report-block'));
    if (hasExisting) {
        // Refresh the inline existing-reports panel + container highlight even
        // if the block itself was already injected on a prior pass.
        const existingBlock = Array.from(container.children).find((child) => child.classList?.contains('js-question-report-block'));
        renderQuestionReportExistingForBlock(existingBlock);
        return;
    }

    const question = resolveReportQuestion(container);
    const context = container.classList.contains('drag-quiz__row')
        ? 'drag'
        : (container.classList.contains('match-card') ? 'match' : 'default');
    const blockHtml = renderQuestionReportBlock(question, context);

    if (!blockHtml) {
        return;
    }

    const wrapper = document.createElement('div');
    wrapper.innerHTML = blockHtml.trim();
    const block = wrapper.firstElementChild;

    if (block) {
        container.appendChild(block);
        renderQuestionReportExistingForBlock(block);
    }
}

function enhanceQuestionReportBlocks() {
    if (!window.__IS_ADMIN__) {
        return;
    }

    document.querySelectorAll('article[data-idx]').forEach(injectQuestionReportBlock);
    document.querySelectorAll('.drag-quiz__row').forEach(injectQuestionReportBlock);
    document.querySelectorAll('.match-card.match-sentence').forEach(injectQuestionReportBlock);
}

let questionReportFrameId = null;

function scheduleQuestionReportEnhancement() {
    if (!window.__IS_ADMIN__) {
        return;
    }

    if (questionReportFrameId !== null) {
        return;
    }

    questionReportFrameId = window.requestAnimationFrame(() => {
        questionReportFrameId = null;
        enhanceQuestionReportBlocks();
    });
}

function questionReportPayload(block, form) {
    const formData = new FormData(form);
    const issueTypes = Array.from(form.querySelectorAll('[data-question-report-issue]:checked'))
        .map((input) => String(input.value || '').trim())
        .filter(Boolean);

    return {
        question_id: block.dataset.questionId || null,
        question_uuid: block.dataset.questionUuid || null,
        issue_types: issueTypes,
        comment: String(formData.get('comment') || '').trim(),
        test_slug: window.__QUESTION_REPORT_TEST_SLUG__ || null,
        test_name: window.__QUESTION_REPORT_TEST_NAME__ || null,
        mode: window.__QUESTION_REPORT_MODE__ || null,
        url: questionReportCurrentUrl(),
    };
}

function questionReportCurrentUrl() {
    try {
        const url = new URL(window.location.href);
        url.searchParams.delete('filters');

        return url.toString();
    } catch (error) {
        return window.location.href;
    }
}

function updateQuestionReportOtherHint(form) {
    if (!form) return;
    const hint = form.querySelector('[data-question-report-other-hint]');
    if (!hint) return;

    const otherSelected = Boolean(form.querySelector('[data-question-report-issue][value="other"]:checked'));
    hint.classList.toggle('hidden', !otherSelected);
}

async function submitQuestionReport(form) {
    const block = form.closest('.js-question-report-block');
    const status = block?.querySelector('[data-question-report-status]');
    const submitButton = form.querySelector('[data-question-report-submit]');
    const payload = questionReportPayload(block, form);

    if (payload.issue_types.length === 0 && !payload.comment) {
        if (status) {
            status.textContent = questionReportUi('validation.issue_or_comment_required', 'Choose at least one issue type or write a comment');
        }
        return;
    }

    if (!window.__QUESTION_REPORT_ENDPOINT__) {
        if (status) {
            status.textContent = questionReportUi('error', 'Unable to save report.');
        }
        return;
    }

    if (submitButton) {
        submitButton.disabled = true;
        submitButton.classList.add('opacity-60');
    }
    if (status) {
        status.textContent = questionReportUi('saving', 'Saving...');
    }

    try {
        const response = await fetch(window.__QUESTION_REPORT_ENDPOINT__, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}',
            },
            credentials: 'same-origin',
            body: JSON.stringify(payload),
        });

        if (!response.ok) {
            throw new Error('Failed to save question report');
        }

        const data = await response.json();
        form.reset();
        updateQuestionReportOtherHint(form);
        if (status) {
            status.textContent = `${questionReportUi('success', questionReportUi('saved', 'Saved'))}: ${data?.report?.file || ''}`;
        }
        if (block) {
            block.dataset.reported = '1';
            questionReportRecordLocally(data?.report);
            renderQuestionReportExistingForBlock(block);
        }
    } catch (error) {
        console.error(error);
        if (status) {
            status.textContent = questionReportUi('error', 'Unable to save report.');
        }
    } finally {
        if (submitButton) {
            submitButton.disabled = false;
            submitButton.classList.remove('opacity-60');
        }
    }
}

function setupQuestionReportControls() {
    if (!window.__IS_ADMIN__) {
        return;
    }

    scheduleQuestionReportEnhancement();

    document.addEventListener('click', (event) => {
        const toggle = event.target.closest('[data-question-report-toggle]');
        if (!toggle) {
            return;
        }

        event.preventDefault();
        const block = toggle.closest('.js-question-report-block');
        const form = block?.querySelector('[data-question-report-form]');
        if (!form) {
            return;
        }

        form.classList.toggle('hidden');
        if (!form.classList.contains('hidden')) {
            form.querySelector('textarea')?.focus();
        }
    });

    document.addEventListener('submit', (event) => {
        const form = event.target.closest('[data-question-report-form]');
        if (!form) {
            return;
        }

        event.preventDefault();
        submitQuestionReport(form);
    });

    document.addEventListener('change', (event) => {
        const issueInput = event.target.closest('[data-question-report-issue]');
        if (!issueInput) {
            return;
        }

        updateQuestionReportOtherHint(issueInput.closest('[data-question-report-form]'));
    });

    if (typeof MutationObserver === 'undefined' || !document.body) {
        return;
    }

    const observer = new MutationObserver(() => {
        scheduleQuestionReportEnhancement();
    });

    observer.observe(document.body, {
        childList: true,
        subtree: true,
    });
}

function injectTechnicalInfoBlock(container) {
    if (!window.__SHOW_TEST_TECH_INFO__ || !container) {
        return;
    }

    const question = resolveTechnicalQuestion(container);
    if (!question || !question.tech_info) {
        return;
    }

    const hasExisting = Array.from(container.children || []).some((child) => child.classList?.contains('js-tech-info-block'));
    if (hasExisting) {
        return;
    }

    const context = container.classList.contains('drag-quiz__row')
        ? 'drag'
        : (container.classList.contains('match-card') ? 'match' : 'default');
    const blockHtml = renderTechnicalInfoBlock(question, context);

    if (!blockHtml) {
        return;
    }

    const wrapper = document.createElement('div');
    wrapper.innerHTML = blockHtml.trim();
    const block = wrapper.firstElementChild;

    if (block) {
        container.appendChild(block);
    }
}

let technicalInfoFrameId = null;

function enhanceTechnicalInfoBlocks() {
    if (!window.__SHOW_TEST_TECH_INFO__) {
        return;
    }

    document.querySelectorAll('article[data-idx]').forEach(injectTechnicalInfoBlock);
    document.querySelectorAll('.drag-quiz__row').forEach(injectTechnicalInfoBlock);
    document.querySelectorAll('.match-card.match-sentence').forEach(injectTechnicalInfoBlock);
}

function scheduleTechnicalInfoEnhancement() {
    if (!window.__SHOW_TEST_TECH_INFO__) {
        return;
    }

    if (technicalInfoFrameId !== null) {
        return;
    }

    technicalInfoFrameId = window.requestAnimationFrame(() => {
        technicalInfoFrameId = null;
        enhanceTechnicalInfoBlocks();
    });
}

function setupTechnicalInfoObserver() {
    if (!window.__SHOW_TEST_TECH_INFO__) {
        return;
    }

    scheduleTechnicalInfoEnhancement();

    if (typeof MutationObserver === 'undefined' || !document.body) {
        return;
    }

    const observer = new MutationObserver(() => {
        scheduleTechnicalInfoEnhancement();
    });

    observer.observe(document.body, {
        childList: true,
        subtree: true,
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', setupTechnicalInfoObserver, { once: true });
    document.addEventListener('DOMContentLoaded', setupQuestionReportControls, { once: true });
} else {
    setupTechnicalInfoObserver();
    setupQuestionReportControls();
}

function resizeSelect(el) {
    const span = document.createElement('span');
    span.style.visibility = 'hidden';
    span.style.position = 'absolute';
    span.style.whiteSpace = 'pre';
    span.style.font = getComputedStyle(el).font;
    span.textContent = el.options[el.selectedIndex]?.text || '';
    document.body.appendChild(span);
    const width = span.offsetWidth + 32;
    document.body.removeChild(span);
    el.style.width = width + 'px';
}

function cloneState(data) {
    if (data === null || data === undefined) {
        return null;
    }

    return JSON.parse(JSON.stringify(data));
}

function isFilledAnswerScalar(value) {
    if (Array.isArray(value)) {
        return value.some(isFilledAnswerScalar);
    }

    if (typeof value === 'string') {
        return value.trim() !== '';
    }

    if (typeof value === 'number') {
        return Number.isFinite(value);
    }

    return value !== null && value !== undefined && value !== false;
}

function isStartedState(state) {
    if (!state || typeof state !== 'object') {
        return false;
    }

    if (state.completed === true || state.evaluated === true) {
        return true;
    }

    if (Number(state.answered) > 0 || Number(state.current) > 0 || Number(state.activeCardIdx) > 0) {
        return true;
    }

    if (Array.isArray(state.connections) && state.connections.length > 0) {
        return true;
    }

    if (Array.isArray(state.placements) && state.placements.length > 0) {
        return true;
    }

    if (!Array.isArray(state.items)) {
        return false;
    }

    return state.items.some((item) => {
        if (!item || typeof item !== 'object') {
            return false;
        }

        if (isFilledAnswerScalar(item.chosen)
            || isFilledAnswerScalar(item.inputs)
            || isFilledAnswerScalar(item.manualInputsBySlot)) {
            return true;
        }

        if (item.done === true && item.status !== 'auto') {
            return true;
        }

        if (item.isCorrect !== null && item.isCorrect !== undefined) {
            return true;
        }

        if (item.wasCorrect !== null && item.wasCorrect !== undefined) {
            return true;
        }

        if (item.wrongAttempt === true || Number(item.attempts) > 0) {
            return true;
        }

        return Array.isArray(item.manualWordIndexBySlot)
            && item.manualWordIndexBySlot.some((index) => Number(index) > 0);
    });
}

function prepareStateForPersistence(state) {
    const snapshot = cloneState(state);

    if (!snapshot || typeof snapshot !== 'object') {
        return snapshot;
    }

    const meta = snapshot.__meta && typeof snapshot.__meta === 'object'
        ? snapshot.__meta
        : {};
    const started = isStartedState(snapshot);

    meta.started = started;
    meta.saved_at = new Date().toISOString();

    if (started) {
        const questionData = cloneState(window.__INITIAL_JS_TEST_QUESTIONS__);
        meta.question_data = Array.isArray(questionData) ? questionData : [];
    } else {
        delete meta.question_data;
    }

    snapshot.__meta = meta;

    return snapshot;
}

const JS_TEST_PERSISTENCE = window.JS_TEST_PERSISTENCE || null;
let JS_TEST_SAVE_TIMER = null;
let JS_TEST_SAVE_QUEUE = Promise.resolve();
let JS_TEST_LAST_SNAPSHOT = null;
let JS_TEST_RESTARTING = false;
let JS_TEST_CONFIRMED_MODAL_SNAPSHOT = null;
let JS_TEST_MODAL_RECOVERY_WARNED = false;
let JS_TEST_CONFIRMED_CONDITIONALS_SNAPSHOT = null;
let JS_TEST_CONDITIONALS_RECOVERY_WARNED = false;
let JS_TEST_CONFIRMED_REPORTED_SPEECH_SNAPSHOT = null;
let JS_TEST_REPORTED_SPEECH_RECOVERY_WARNED = false;
let JS_TEST_CONFIRMED_CLAUSES_LINKING_SNAPSHOT = null;
let JS_TEST_CLAUSES_LINKING_RECOVERY_WARNED = false;
let JS_TEST_CONFIRMED_PREPOSITIONS_PHRASAL_SNAPSHOT = null;
let JS_TEST_PREPOSITIONS_PHRASAL_RECOVERY_WARNED = false;
let JS_TEST_CONFIRMED_COMMON_MISTAKES_SNAPSHOT = null;
let JS_TEST_COMMON_MISTAKES_RECOVERY_WARNED = false;
let JS_TEST_CONFIRMED_SENTENCE_TRANSFORMATIONS_SNAPSHOT = null;
let JS_TEST_SENTENCE_TRANSFORMATIONS_RECOVERY_WARNED = false;
let JS_TEST_CONFIRMED_VERB_PATTERNS_SNAPSHOT = null;
let JS_TEST_VERB_PATTERNS_RECOVERY_WARNED = false;
let JS_TEST_CONFIRMED_MIXED_REVISION_SNAPSHOT = null;
let JS_TEST_MIXED_REVISION_RECOVERY_WARNED = false;
let JS_TEST_CONFIRMED_ACADEMIC_ENGLISH_SNAPSHOT = null;
let JS_TEST_ACADEMIC_ENGLISH_RECOVERY_WARNED = false;
let JS_TEST_CONFIRMED_ARTICLES_QUANTIFIERS_SNAPSHOT = null;
let JS_TEST_ARTICLES_QUANTIFIERS_RECOVERY_WARNED = false;
let JS_TEST_CONFIRMED_FORMAL_ENGLISH_SNAPSHOT = null;
let JS_TEST_FORMAL_ENGLISH_RECOVERY_WARNED = false;
let JS_TEST_CONFIRMED_RELATIVE_CLAUSES_SNAPSHOT = null;
let JS_TEST_RELATIVE_CLAUSES_RECOVERY_WARNED = false;
let JS_TEST_CONFIRMED_SENTENCE_STRUCTURE_SNAPSHOT = null;
let JS_TEST_SENTENCE_STRUCTURE_RECOVERY_WARNED = false;
let JS_TEST_CONFIRMED_VOCAB_COLLOCATIONS_SNAPSHOT = null;
let JS_TEST_VOCAB_COLLOCATIONS_RECOVERY_WARNED = false;

if (JS_TEST_PERSISTENCE && JS_TEST_PERSISTENCE.saved) {
    JS_TEST_PERSISTENCE.saved = cloneState(JS_TEST_PERSISTENCE.saved);
    JS_TEST_LAST_SNAPSHOT = cloneState(JS_TEST_PERSISTENCE.saved);
}

function readLocalJsTestState() {
    if (!JS_TEST_PERSISTENCE || !JS_TEST_PERSISTENCE.storageKey) {
        return null;
    }

    const storageKeys = Array.isArray(JS_TEST_PERSISTENCE.storageKeys) && JS_TEST_PERSISTENCE.storageKeys.length > 0
        ? JS_TEST_PERSISTENCE.storageKeys
        : [JS_TEST_PERSISTENCE.storageKey];
    const savedAt = (state, fallback = '') => {
        const timestamp = Date.parse(String(state?.__meta?.saved_at ?? fallback));
        return Number.isFinite(timestamp) ? timestamp : 0;
    };
    let selected = null;
    let selectedSavedAt = 0;

    [window.localStorage, window.sessionStorage].forEach((storage) => {
        if (!storage) {
            return;
        }

        storageKeys.forEach((key) => {
            try {
                const raw = storage.getItem(key);
                if (!raw) {
                    return;
                }

                const parsed = JSON.parse(raw);
                if (!parsed || typeof parsed !== 'object' || !parsed.state) {
                    return;
                }

                if (!isStartedState(parsed.state)) {
                    return;
                }

                const timestamp = savedAt(parsed.state, parsed.saved_at);
                if (!selected || timestamp > selectedSavedAt) {
                    selected = parsed.state;
                    selectedSavedAt = timestamp;
                }
            } catch (error) {
                console.error(error);
            }
        });
    });

    return selected ? cloneState(selected) : null;
}

function isModalLocalJsStorageScope(storageKey, mode) {
    return ['saved-test-js-v2', 'saved-test-js-step-v2'].includes(String(mode || ''))
        && /^gramlyze:js-test-state:v2:[a-f0-9]{64}:mvq-[a-f0-9]{20}$/.test(String(storageKey || ''));
}

function warnModalLocalRecovery(destination) {
    if (JS_TEST_MODAL_RECOVERY_WARNED) return;
    JS_TEST_MODAL_RECOVERY_WARNED = true;
    console.warn(`Browser test storage is unavailable; current Modal Verbs progress was confirmed in ${destination}.`);
}

function isConditionalsLocalJsStorageScope(storageKey, mode) {
    return ['saved-test-js-v2', 'saved-test-js-step-v2'].includes(String(mode || ''))
        && /^gramlyze:js-test-state:v2:[a-f0-9]{64}:cqq-[a-f0-9]{20}$/.test(String(storageKey || ''));
}

function warnConditionalsLocalRecovery(destination) {
    if (JS_TEST_CONDITIONALS_RECOVERY_WARNED) return;
    JS_TEST_CONDITIONALS_RECOVERY_WARNED = true;
    console.warn(`Browser test storage is unavailable; current Conditionals progress was confirmed in ${destination}.`);
}

// Canonical content is restored by mergeFreshQuestionContentIntoSavedState().
// Only exact current MVQ rows may omit it locally; every learner/unknown field stays intact.
function compactModalLocalJsState(state, storageKey, mode) {
    const snapshot = cloneState(state);
    if (!isModalLocalJsStorageScope(storageKey, mode) || !snapshot || !Array.isArray(snapshot.items)) return snapshot;
    const fresh = new Map(), duplicate = new Set();
    getTechnicalQuestions().forEach(question => {
        const uuid = String(question?.uuid || '');
        if (fresh.has(uuid)) duplicate.add(uuid);
        fresh.set(uuid, question);
    });
    const fields = [
        'type', 'question', 'answer', 'answers', 'answer_map', 'accepted_answers', 'accepted_answers_by_marker',
        'answer_synonyms_by_marker', 'answer_synonym_tokens_by_marker', 'compose_accepted_sentences',
        'markers', 'markers_count', 'contraction_slots_version', 'options_by_marker', 'verb_hint', 'verb_hints',
        'options', 'tense', 'is_past_perfect_continuous', 'level', 'theory_block', 'theory_blocks', 'marker_tags', 'tech_info',
        'compose_source_text', 'compose_source_text_uk', 'compose_hint', 'hint', 'compose_content_revision',
        'presentation', 'reorder_prompt', 'reorder_answer', 'reorder_tokens', 'reorder_source_question',
        'reorder_template_constraint', 'reorder_manual_input', 'reorder_groups', 'reorder_accepted_answers',
        'passive_gap_phrase_input', 'passive_phrase_word_inputs', 'gap_compactness_revision', 'gap_previous_question',
        'gap_previous_answer', 'gap_previous_accepted_answers', 'fsp_compose_options_revision', 'qnq_strict_slot_surfaces',
        'aaq_option_caption_revision', 'saq_option_caption_revision', 'pvq_option_caption_revision',
    ];
    const same = (left, right) => JSON.stringify(left) === JSON.stringify(right);
    snapshot.items = snapshot.items.map(item => {
        const uuid = String(item?.uuid || ''), current = fresh.get(uuid), revision = String(item?.psp_content_revision || '');
        if (!item || typeof item !== 'object' || duplicate.has(uuid)
            || !/^mvq-(?:cc|mm|mht|sot|need|ded|past|requests|perfect|subtle)-(?:a1|a2|b1|b2|c1|c2)-(?:g|c)[0-9]{2}$/.test(uuid)
            || !current || !/^[a-f0-9]{64}$/.test(revision) || current.psp_content_revision !== revision
            || current.mvq_option_caption_revision !== revision || item.mvq_option_caption_revision !== revision
            || item.type !== current.type || item.question !== current.question || !same(item.answer_map, current.answer_map)
            || (item.contraction_slots_version === 1 && current.contraction_slots_version !== 1)) return item;
        fields.forEach(field => {
            if (Object.prototype.hasOwnProperty.call(current, field) && Object.prototype.hasOwnProperty.call(item, field)
                && same(item[field], current[field])) delete item[field];
        });
        return item;
    });
    return snapshot;
}

// Separate finite Conditionals binding; the accepted MVQ compactor stays unchanged.
function compactConditionalsLocalJsState(state, storageKey, mode) {
    const snapshot = cloneState(state);
    if (!isConditionalsLocalJsStorageScope(storageKey, mode) || !snapshot || !Array.isArray(snapshot.items)) return snapshot;
    const fresh = new Map(), duplicate = new Set();
    getTechnicalQuestions().forEach(question => {
        const uuid = String(question?.uuid || '');
        if (fresh.has(uuid)) duplicate.add(uuid);
        fresh.set(uuid, question);
    });
    const fields = [
        'type', 'question', 'answer', 'answers', 'answer_map', 'accepted_answers', 'accepted_answers_by_marker',
        'answer_synonyms_by_marker', 'answer_synonym_tokens_by_marker', 'compose_accepted_sentences',
        'markers', 'markers_count', 'contraction_slots_version', 'options_by_marker', 'verb_hint', 'verb_hints',
        'options', 'tense', 'is_past_perfect_continuous', 'level', 'theory_block', 'theory_blocks', 'marker_tags', 'tech_info',
        'compose_source_text', 'compose_source_text_uk', 'compose_hint', 'hint', 'compose_content_revision',
        'presentation', 'reorder_prompt', 'reorder_answer', 'reorder_tokens', 'reorder_source_question',
        'reorder_template_constraint', 'reorder_manual_input', 'reorder_groups', 'reorder_accepted_answers',
        'passive_gap_phrase_input', 'passive_phrase_word_inputs', 'gap_compactness_revision', 'gap_previous_question',
        'gap_previous_answer', 'gap_previous_accepted_answers', 'fsp_compose_options_revision', 'qnq_strict_slot_surfaces',
        'aaq_option_caption_revision', 'saq_option_caption_revision', 'pvq_option_caption_revision',
    ];
    const same = (left, right) => JSON.stringify(left) === JSON.stringify(right);
    snapshot.items = snapshot.items.map(item => {
        const uuid = String(item?.uuid || ''), current = fresh.get(uuid), revision = String(item?.psp_content_revision || '');
        if (!item || typeof item !== 'object' || duplicate.has(uuid)
            || !/^cqq-(?:zero|first|second|third|mixed|unless|case|wish|rather|better|conditions|advanced|alternatives)-(?:a1|a2|b1|b2|c1|c2)-(?:g|c)[0-9]{2}$/.test(uuid)
            || !current || !/^[a-f0-9]{64}$/.test(revision) || current.psp_content_revision !== revision
            || current.cqq_option_caption_revision !== revision || item.cqq_option_caption_revision !== revision
            || item.type !== current.type || item.question !== current.question || !same(item.answer_map, current.answer_map)
            || (item.contraction_slots_version === 1 && current.contraction_slots_version !== 1)) return item;
        fields.forEach(field => {
            if (Object.prototype.hasOwnProperty.call(current, field) && Object.prototype.hasOwnProperty.call(item, field)
                && same(item[field], current[field])) delete item[field];
        });
        return item;
    });
    return snapshot;
}

function compactReportedSpeechLocalJsState(state, storageKey, mode) {
    const snapshot = cloneState(state);
    if (!isReportedSpeechLocalJsStorageScope(storageKey, mode) || !snapshot || !Array.isArray(snapshot.items)) return snapshot;
    const fresh = new Map(), duplicate = new Set();
    getTechnicalQuestions().forEach(question => {
        const uuid = String(question?.uuid || '');
        if (fresh.has(uuid)) duplicate.add(uuid);
        fresh.set(uuid, question);
    });
    const fields = [
        'type', 'question', 'answer', 'answers', 'answer_map', 'accepted_answers', 'accepted_answers_by_marker',
        'answer_synonyms_by_marker', 'answer_synonym_tokens_by_marker', 'compose_accepted_sentences',
        'markers', 'markers_count', 'contraction_slots_version', 'options_by_marker', 'verb_hint', 'verb_hints',
        'options', 'tense', 'is_past_perfect_continuous', 'level', 'theory_block', 'theory_blocks', 'marker_tags', 'tech_info',
        'compose_source_text', 'compose_source_text_uk', 'compose_hint', 'hint', 'compose_content_revision',
        'presentation', 'reorder_prompt', 'reorder_answer', 'reorder_tokens', 'reorder_source_question',
        'reorder_template_constraint', 'reorder_manual_input', 'reorder_groups', 'reorder_accepted_answers',
        'passive_gap_phrase_input', 'passive_phrase_word_inputs', 'gap_compactness_revision', 'gap_previous_question',
        'gap_previous_answer', 'gap_previous_accepted_answers', 'fsp_compose_options_revision', 'qnq_strict_slot_surfaces',
        'aaq_option_caption_revision', 'saq_option_caption_revision', 'pvq_option_caption_revision',
    ];
    const same = (left, right) => JSON.stringify(left) === JSON.stringify(right);
    snapshot.items = snapshot.items.map(item => {
        const uuid = String(item?.uuid || ''), current = fresh.get(uuid), revision = String(item?.psp_content_revision || '');
        if (!item || typeof item !== 'object' || duplicate.has(uuid)
            || !/^rsq-(?:statements|questions|commands|backshift|timeplace|saytell|advanced)-(?:a1|a2|b1|b2|c1|c2)-(?:g|c)[0-9]{2}$/.test(uuid)
            || !current || !/^[a-f0-9]{64}$/.test(revision) || current.psp_content_revision !== revision
            || current.rsq_option_caption_revision !== revision || item.rsq_option_caption_revision !== revision
            || item.type !== current.type || item.question !== current.question || !same(item.answer_map, current.answer_map)
            || (item.contraction_slots_version === 1 && current.contraction_slots_version !== 1)) return item;
        fields.forEach(field => {
            if (Object.prototype.hasOwnProperty.call(current, field) && Object.prototype.hasOwnProperty.call(item, field)
                && same(item[field], current[field])) delete item[field];
        });
        return item;
    });
    return snapshot;
}

function isReportedSpeechLocalJsStorageScope(storageKey, mode) {
    return ['saved-test-js-v2', 'saved-test-js-step-v2'].includes(String(mode || ''))
        && /^gramlyze:js-test-state:v2:[a-f0-9]{64}:rsq-[a-f0-9]{20}$/.test(String(storageKey || ''));
}

function warnReportedSpeechLocalRecovery(destination) {
    if (JS_TEST_REPORTED_SPEECH_RECOVERY_WARNED) return;
    JS_TEST_REPORTED_SPEECH_RECOVERY_WARNED = true;
    console.warn(`Browser test storage is unavailable; current Reported Speech progress was confirmed in ${destination}.`);
}

function compactClausesLinkingLocalJsState(state, storageKey, mode) {
    const snapshot = cloneState(state);
    if (!isClausesLinkingLocalJsStorageScope(storageKey, mode) || !snapshot || !Array.isArray(snapshot.items)) return snapshot;
    const fresh = new Map(), duplicate = new Set();
    getTechnicalQuestions().forEach(question => {
        const uuid = String(question?.uuid || '');
        if (fresh.has(uuid)) duplicate.add(uuid);
        fresh.set(uuid, question);
    });
    const fields = [
        'type', 'question', 'answer', 'answers', 'answer_map', 'accepted_answers', 'accepted_answers_by_marker',
        'answer_synonyms_by_marker', 'answer_synonym_tokens_by_marker', 'compose_accepted_sentences',
        'markers', 'markers_count', 'contraction_slots_version', 'options_by_marker', 'verb_hint', 'verb_hints',
        'options', 'tense', 'is_past_perfect_continuous', 'level', 'theory_block', 'theory_blocks', 'marker_tags', 'tech_info',
        'compose_source_text', 'compose_source_text_uk', 'compose_hint', 'hint', 'compose_content_revision',
        'presentation', 'reorder_prompt', 'reorder_answer', 'reorder_tokens', 'reorder_source_question',
        'reorder_template_constraint', 'reorder_manual_input', 'reorder_groups', 'reorder_accepted_answers',
        'passive_gap_phrase_input', 'passive_phrase_word_inputs', 'gap_compactness_revision', 'gap_previous_question',
        'gap_previous_answer', 'gap_previous_accepted_answers', 'fsp_compose_options_revision', 'qnq_strict_slot_surfaces',
        'aaq_option_caption_revision', 'saq_option_caption_revision', 'pvq_option_caption_revision',
    ];
    const same = (left, right) => JSON.stringify(left) === JSON.stringify(right);
    snapshot.items = snapshot.items.map(item => {
        const uuid = String(item?.uuid || ''), current = fresh.get(uuid), revision = String(item?.psp_content_revision || '');
        if (!item || typeof item !== 'object' || duplicate.has(uuid)
            || !/^clq-(?:relative|defining|nondefining|omitting|whosewhich|reason|result|contrast|purpose|linking|reasonresult|advanced|concessive|partbasics|participle|absolute|discourse)-(?:a1|a2|b1|b2|c1|c2)-(?:g|c)[0-9]{2}$/.test(uuid)
            || !current || !/^[a-f0-9]{64}$/.test(revision) || current.psp_content_revision !== revision
            || current.clq_option_caption_revision !== revision || item.clq_option_caption_revision !== revision
            || item.type !== current.type || item.question !== current.question || !same(item.answer_map, current.answer_map)
            || (item.contraction_slots_version === 1 && current.contraction_slots_version !== 1)) return item;
        fields.forEach(field => {
            if (Object.prototype.hasOwnProperty.call(current, field) && Object.prototype.hasOwnProperty.call(item, field)
                && same(item[field], current[field])) delete item[field];
        });
        return item;
    });
    return snapshot;
}

function isClausesLinkingLocalJsStorageScope(storageKey, mode) {
    return ['saved-test-js-v2', 'saved-test-js-step-v2'].includes(String(mode || ''))
        && /^gramlyze:js-test-state:v2:[a-f0-9]{64}:clq-[a-f0-9]{20}$/.test(String(storageKey || ''));
}

function warnClausesLinkingLocalRecovery(destination) {
    if (JS_TEST_CLAUSES_LINKING_RECOVERY_WARNED) return;
    JS_TEST_CLAUSES_LINKING_RECOVERY_WARNED = true;
    console.warn(`Browser test storage is unavailable; current Clauses and Linking Words progress was confirmed in ${destination}.`);
}

function compactPrepositionsPhrasalLocalJsState(state, storageKey, mode) {
    const snapshot = cloneState(state);
    if (!isPrepositionsPhrasalLocalJsStorageScope(storageKey, mode) || !snapshot || !Array.isArray(snapshot.items)) return snapshot;
    const fresh = new Map(), duplicate = new Set();
    getTechnicalQuestions().forEach(question => {
        const uuid = String(question?.uuid || '');
        if (fresh.has(uuid)) duplicate.add(uuid);
        fresh.set(uuid, question);
    });
    const fields = [
        'type', 'question', 'answer', 'answers', 'answer_map', 'accepted_answers', 'accepted_answers_by_marker',
        'answer_synonyms_by_marker', 'answer_synonym_tokens_by_marker', 'compose_accepted_sentences',
        'markers', 'markers_count', 'contraction_slots_version', 'options_by_marker', 'verb_hint', 'verb_hints',
        'options', 'tense', 'is_past_perfect_continuous', 'level', 'theory_block', 'theory_blocks', 'marker_tags', 'tech_info',
        'compose_source_text', 'compose_source_text_uk', 'compose_hint', 'hint', 'compose_content_revision',
        'presentation', 'reorder_prompt', 'reorder_answer', 'reorder_tokens', 'reorder_source_question',
        'reorder_template_constraint', 'reorder_manual_input', 'reorder_groups', 'reorder_accepted_answers',
        'passive_gap_phrase_input', 'passive_phrase_word_inputs', 'gap_compactness_revision', 'gap_previous_question',
        'gap_previous_answer', 'gap_previous_accepted_answers', 'fsp_compose_options_revision', 'qnq_strict_slot_surfaces',
        'aaq_option_caption_revision', 'saq_option_caption_revision', 'pvq_option_caption_revision',
    ];
    const same = (left, right) => JSON.stringify(left) === JSON.stringify(right);
    snapshot.items = snapshot.items.map(item => {
        const uuid = String(item?.uuid || ''), current = fresh.get(uuid), revision = String(item?.psp_content_revision || '');
        if (!item || typeof item !== 'object' || duplicate.has(uuid)
            || !/^ppq-(?:time|place|movement|dependent|basics|separable|common)-(?:a1|a2|b1|b2|c1|c2)-(?:g|c)[0-9]{2}$/.test(uuid)
            || !current || !/^[a-f0-9]{64}$/.test(revision) || current.psp_content_revision !== revision
            || current.ppq_option_caption_revision !== revision || item.ppq_option_caption_revision !== revision
            || item.type !== current.type || item.question !== current.question || !same(item.answer_map, current.answer_map)
            || (item.contraction_slots_version === 1 && current.contraction_slots_version !== 1)) return item;
        fields.forEach(field => {
            if (Object.prototype.hasOwnProperty.call(current, field) && Object.prototype.hasOwnProperty.call(item, field)
                && same(item[field], current[field])) delete item[field];
        });
        return item;
    });
    return snapshot;
}

function isPrepositionsPhrasalLocalJsStorageScope(storageKey, mode) {
    return ['saved-test-js-v2', 'saved-test-js-step-v2'].includes(String(mode || ''))
        && /^gramlyze:js-test-state:v2:[a-f0-9]{64}:ppq-[a-f0-9]{20}$/.test(String(storageKey || ''));
}

function warnPrepositionsPhrasalLocalRecovery(destination) {
    if (JS_TEST_PREPOSITIONS_PHRASAL_RECOVERY_WARNED) return;
    JS_TEST_PREPOSITIONS_PHRASAL_RECOVERY_WARNED = true;
    console.warn(`Browser test storage is unavailable; current Prepositions and Phrasal Verbs progress was confirmed in ${destination}.`);
}

function compactAcademicEnglishLocalJsState(state, storageKey, mode) {
    const snapshot = cloneState(state);
    if (!isAcademicEnglishLocalJsStorageScope(storageKey, mode) || !snapshot || !Array.isArray(snapshot.items)) return snapshot;
    const fresh = new Map(), duplicate = new Set();
    getTechnicalQuestions().forEach(question => {
        const uuid = String(question?.uuid || '');
        if (fresh.has(uuid)) duplicate.add(uuid);
        fresh.set(uuid, question);
    });
    const fields = [
        'type', 'question', 'answer', 'answers', 'answer_map', 'accepted_answers', 'accepted_answers_by_marker',
        'answer_synonyms_by_marker', 'answer_synonym_tokens_by_marker', 'compose_accepted_sentences',
        'markers', 'markers_count', 'contraction_slots_version', 'options_by_marker', 'verb_hint', 'verb_hints',
        'options', 'tense', 'is_past_perfect_continuous', 'level', 'theory_block', 'theory_blocks', 'marker_tags', 'tech_info',
        'compose_source_text', 'compose_source_text_uk', 'compose_hint', 'hint', 'compose_content_revision',
        'presentation', 'reorder_prompt', 'reorder_answer', 'reorder_tokens', 'reorder_source_question',
        'reorder_template_constraint', 'reorder_manual_input', 'reorder_groups', 'reorder_accepted_answers',
        'passive_gap_phrase_input', 'passive_phrase_word_inputs', 'gap_compactness_revision', 'gap_previous_question',
        'gap_previous_answer', 'gap_previous_accepted_answers', 'fsp_compose_options_revision', 'qnq_strict_slot_surfaces',
        'aeq_option_caption_revision',
    ];
    const same = (left, right) => JSON.stringify(left) === JSON.stringify(right);
    snapshot.items = snapshot.items.map(item => {
        const uuid = String(item?.uuid || ''), current = fresh.get(uuid), revision = String(item?.psp_content_revision || '');
        if (!item || typeof item !== 'object' || duplicate.has(uuid)
            || !/^aeq-(?:hcb|hed|sta|arg)-(?:a1|a2|b1|b2|c1|c2)-(?:g|c)[0-9]{2}$/.test(uuid)
            || !current || !/^[a-f0-9]{64}$/.test(revision) || current.psp_content_revision !== revision
            || current.aeq_option_caption_revision !== revision || item.aeq_option_caption_revision !== revision
            || item.type !== current.type || item.question !== current.question || !same(item.answer_map, current.answer_map)
            || (item.contraction_slots_version === 1 && current.contraction_slots_version !== 1)) return item;
        fields.forEach(field => {
            if (Object.prototype.hasOwnProperty.call(current, field) && Object.prototype.hasOwnProperty.call(item, field)
                && same(item[field], current[field])) delete item[field];
        });
        return item;
    });
    return snapshot;
}

function isAcademicEnglishLocalJsStorageScope(storageKey, mode) {
    return ['saved-test-js-v2', 'saved-test-js-step-v2'].includes(String(mode || ''))
        && /^gramlyze:js-test-state:v2:[a-f0-9]{64}:aeq-[a-f0-9]{20}$/.test(String(storageKey || ''));
}

function warnAcademicEnglishLocalRecovery(destination) {
    if (JS_TEST_ACADEMIC_ENGLISH_RECOVERY_WARNED) return;
    JS_TEST_ACADEMIC_ENGLISH_RECOVERY_WARNED = true;
    console.warn(`Browser test storage is unavailable; current Academic English progress was confirmed in ${destination}.`);
}

function compactArticlesQuantifiersLocalJsState(state, storageKey, mode) {
    const snapshot = cloneState(state);
    if (!isArticlesQuantifiersLocalJsStorageScope(storageKey, mode) || !snapshot || !Array.isArray(snapshot.items)) return snapshot;
    const fresh = new Map(), duplicate = new Set();
    getTechnicalQuestions().forEach(question => {
        const uuid = String(question?.uuid || '');
        if (fresh.has(uuid)) duplicate.add(uuid);
        fresh.set(uuid, question);
    });
    const fields = [
        'type', 'question', 'answer', 'answers', 'answer_map', 'accepted_answers', 'accepted_answers_by_marker',
        'answer_synonyms_by_marker', 'answer_synonym_tokens_by_marker', 'compose_accepted_sentences',
        'markers', 'markers_count', 'contraction_slots_version', 'options_by_marker', 'verb_hint', 'verb_hints',
        'options', 'tense', 'is_past_perfect_continuous', 'level', 'theory_block', 'theory_blocks', 'marker_tags', 'tech_info',
        'compose_source_text', 'compose_source_text_uk', 'compose_hint', 'hint', 'compose_content_revision',
        'presentation', 'reorder_prompt', 'reorder_answer', 'reorder_tokens', 'reorder_source_question',
        'reorder_template_constraint', 'reorder_manual_input', 'reorder_groups', 'reorder_accepted_answers',
        'passive_gap_phrase_input', 'passive_phrase_word_inputs', 'gap_compactness_revision', 'gap_previous_question',
        'gap_previous_answer', 'gap_previous_accepted_answers', 'fsp_compose_options_revision', 'qnq_strict_slot_surfaces',
        'aqv_option_caption_revision',
    ];
    const same = (left, right) => JSON.stringify(left) === JSON.stringify(right);
    snapshot.items = snapshot.items.map(item => {
        const uuid = String(item?.uuid || ''), current = fresh.get(uuid), revision = String(item?.psp_content_revision || '');
        if (!item || typeof item !== 'object' || duplicate.has(uuid)
            || !/^aqv-(?:aqn|pad)-(?:a1|a2|b1|b2|c1|c2)-(?:g|c)[0-9]{2}$/.test(uuid)
            || !current || !/^[a-f0-9]{64}$/.test(revision) || current.psp_content_revision !== revision
            || current.aqv_option_caption_revision !== revision || item.aqv_option_caption_revision !== revision
            || item.type !== current.type || item.question !== current.question || !same(item.answer_map, current.answer_map)
            || (item.contraction_slots_version === 1 && current.contraction_slots_version !== 1)) return item;
        fields.forEach(field => {
            if (Object.prototype.hasOwnProperty.call(current, field) && Object.prototype.hasOwnProperty.call(item, field)
                && same(item[field], current[field])) delete item[field];
        });
        return item;
    });
    return snapshot;
}

function isArticlesQuantifiersLocalJsStorageScope(storageKey, mode) {
    return ['saved-test-js-v2', 'saved-test-js-step-v2'].includes(String(mode || ''))
        && /^gramlyze:js-test-state:v2:[a-f0-9]{64}:aqv-[a-f0-9]{20}$/.test(String(storageKey || ''));
}

function warnArticlesQuantifiersLocalRecovery(destination) {
    if (JS_TEST_ARTICLES_QUANTIFIERS_RECOVERY_WARNED) return;
    JS_TEST_ARTICLES_QUANTIFIERS_RECOVERY_WARNED = true;
    console.warn(`Browser test storage is unavailable; current Articles and Quantifiers progress was confirmed in ${destination}.`);
}

function compactFormalEnglishLocalJsState(state, storageKey, mode) {
    const snapshot = cloneState(state);
    if (!isFormalEnglishLocalJsStorageScope(storageKey, mode) || !snapshot || !Array.isArray(snapshot.items)) return snapshot;
    const fresh = new Map(), duplicate = new Set();
    getTechnicalQuestions().forEach(question => {
        const uuid = String(question?.uuid || '');
        if (fresh.has(uuid)) duplicate.add(uuid);
        fresh.set(uuid, question);
    });
    const fields = [
        'type', 'question', 'answer', 'answers', 'answer_map', 'accepted_answers', 'accepted_answers_by_marker',
        'answer_synonyms_by_marker', 'answer_synonym_tokens_by_marker', 'compose_accepted_sentences',
        'markers', 'markers_count', 'contraction_slots_version', 'options_by_marker', 'verb_hint', 'verb_hints',
        'options', 'tense', 'is_past_perfect_continuous', 'level', 'theory_block', 'theory_blocks', 'marker_tags', 'tech_info',
        'compose_source_text', 'compose_source_text_uk', 'compose_hint', 'hint', 'compose_content_revision',
        'presentation', 'reorder_prompt', 'reorder_answer', 'reorder_tokens', 'reorder_source_question',
        'reorder_template_constraint', 'reorder_manual_input', 'reorder_groups', 'reorder_accepted_answers',
        'passive_gap_phrase_input', 'passive_phrase_word_inputs', 'gap_compactness_revision', 'gap_previous_question',
        'gap_previous_answer', 'gap_previous_accepted_answers', 'fsp_compose_options_revision', 'qnq_strict_slot_surfaces',
        'feq_option_caption_revision',
    ];
    const same = (left, right) => JSON.stringify(left) === JSON.stringify(right);
    snapshot.items = snapshot.items.map(item => {
        const uuid = String(item?.uuid || ''), current = fresh.get(uuid), revision = String(item?.psp_content_revision || '');
        if (!item || typeof item !== 'object' || duplicate.has(uuid)
            || !/^feq-(?:bas|nom|ton|par|sub|sty)-(?:a1|a2|b1|b2|c1|c2)-(?:g|c)[0-9]{2}$/.test(uuid)
            || !current || !/^[a-f0-9]{64}$/.test(revision) || current.psp_content_revision !== revision
            || current.feq_option_caption_revision !== revision || item.feq_option_caption_revision !== revision
            || item.type !== current.type || item.question !== current.question || !same(item.answer_map, current.answer_map)
            || (item.contraction_slots_version === 1 && current.contraction_slots_version !== 1)) return item;
        fields.forEach(field => {
            if (Object.prototype.hasOwnProperty.call(current, field) && Object.prototype.hasOwnProperty.call(item, field)
                && same(item[field], current[field])) delete item[field];
        });
        return item;
    });
    return snapshot;
}

function isFormalEnglishLocalJsStorageScope(storageKey, mode) {
    return ['saved-test-js-v2', 'saved-test-js-step-v2'].includes(String(mode || ''))
        && /^gramlyze:js-test-state:v2:[a-f0-9]{64}:feq-[a-f0-9]{20}$/.test(String(storageKey || ''));
}

function warnFormalEnglishLocalRecovery(destination) {
    if (JS_TEST_FORMAL_ENGLISH_RECOVERY_WARNED) return;
    JS_TEST_FORMAL_ENGLISH_RECOVERY_WARNED = true;
    console.warn(`Browser test storage is unavailable; current Formal English progress was confirmed in ${destination}.`);
}

function compactRelativeClausesLocalJsState(state, storageKey, mode) {
    const snapshot = cloneState(state);
    if (!isRelativeClausesLocalJsStorageScope(storageKey, mode) || !snapshot || !Array.isArray(snapshot.items)) return snapshot;
    const fresh = new Map(), duplicate = new Set();
    getTechnicalQuestions().forEach(question => {
        const uuid = String(question?.uuid || '');
        if (fresh.has(uuid)) duplicate.add(uuid);
        fresh.set(uuid, question);
    });
    const fields = [
        'type', 'question', 'answer', 'answers', 'answer_map', 'accepted_answers', 'accepted_answers_by_marker',
        'answer_synonyms_by_marker', 'answer_synonym_tokens_by_marker', 'compose_accepted_sentences',
        'markers', 'markers_count', 'contraction_slots_version', 'options_by_marker', 'verb_hint', 'verb_hints',
        'options', 'tense', 'is_past_perfect_continuous', 'level', 'theory_block', 'theory_blocks', 'marker_tags', 'tech_info',
        'compose_source_text', 'compose_source_text_uk', 'compose_hint', 'hint', 'compose_content_revision',
        'presentation', 'reorder_prompt', 'reorder_answer', 'reorder_tokens', 'reorder_source_question',
        'reorder_template_constraint', 'reorder_manual_input', 'reorder_groups', 'reorder_accepted_answers',
        'passive_gap_phrase_input', 'passive_phrase_word_inputs', 'gap_compactness_revision', 'gap_previous_question',
        'gap_previous_answer', 'gap_previous_accepted_answers', 'fsp_compose_options_revision', 'qnq_strict_slot_surfaces',
        'rcq_option_caption_revision',
    ];
    const same = (left, right) => JSON.stringify(left) === JSON.stringify(right);
    snapshot.items = snapshot.items.map(item => {
        const uuid = String(item?.uuid || ''), current = fresh.get(uuid), revision = String(item?.psp_content_revision || '');
        if (!item || typeof item !== 'object' || duplicate.has(uuid)
            || !/^rcq-(?:crc)-(?:a1|a2|b1|b2|c1|c2)-(?:g|c)[0-9]{2}$/.test(uuid)
            || !current || !/^[a-f0-9]{64}$/.test(revision) || current.psp_content_revision !== revision
            || current.rcq_option_caption_revision !== revision || item.rcq_option_caption_revision !== revision
            || item.type !== current.type || item.question !== current.question || !same(item.answer_map, current.answer_map)
            || (item.contraction_slots_version === 1 && current.contraction_slots_version !== 1)) return item;
        fields.forEach(field => {
            if (Object.prototype.hasOwnProperty.call(current, field) && Object.prototype.hasOwnProperty.call(item, field)
                && same(item[field], current[field])) delete item[field];
        });
        return item;
    });
    return snapshot;
}

function isRelativeClausesLocalJsStorageScope(storageKey, mode) {
    return ['saved-test-js-v2', 'saved-test-js-step-v2'].includes(String(mode || ''))
        && /^gramlyze:js-test-state:v2:[a-f0-9]{64}:rcq-[a-f0-9]{20}$/.test(String(storageKey || ''));
}

function warnRelativeClausesLocalRecovery(destination) {
    if (JS_TEST_RELATIVE_CLAUSES_RECOVERY_WARNED) return;
    JS_TEST_RELATIVE_CLAUSES_RECOVERY_WARNED = true;
    console.warn(`Browser test storage is unavailable; current Relative Clauses progress was confirmed in ${destination}.`);
}

function compactSentenceStructureLocalJsState(state, storageKey, mode) {
    const snapshot = cloneState(state);
    if (!isSentenceStructureLocalJsStorageScope(storageKey, mode) || !snapshot || !Array.isArray(snapshot.items)) return snapshot;
    const fresh = new Map(), duplicate = new Set();
    getTechnicalQuestions().forEach(question => {
        const uuid = String(question?.uuid || '');
        if (fresh.has(uuid)) duplicate.add(uuid);
        fresh.set(uuid, question);
    });
    const fields = [
        'type', 'question', 'answer', 'answers', 'answer_map', 'accepted_answers', 'accepted_answers_by_marker',
        'answer_synonyms_by_marker', 'answer_synonym_tokens_by_marker', 'compose_accepted_sentences',
        'markers', 'markers_count', 'contraction_slots_version', 'options_by_marker', 'verb_hint', 'verb_hints',
        'options', 'tense', 'is_past_perfect_continuous', 'level', 'theory_block', 'theory_blocks', 'marker_tags', 'tech_info',
        'compose_source_text', 'compose_source_text_uk', 'compose_hint', 'hint', 'compose_content_revision',
        'presentation', 'reorder_prompt', 'reorder_answer', 'reorder_tokens', 'reorder_source_question',
        'reorder_template_constraint', 'reorder_manual_input', 'reorder_groups', 'reorder_accepted_answers',
        'passive_gap_phrase_input', 'passive_phrase_word_inputs', 'gap_compactness_revision', 'gap_previous_question',
        'gap_previous_answer', 'gap_previous_accepted_answers', 'fsp_compose_options_revision', 'qnq_strict_slot_surfaces',
        'ssq_option_caption_revision',
    ];
    const same = (left, right) => JSON.stringify(left) === JSON.stringify(right);
    snapshot.items = snapshot.items.map(item => {
        const uuid = String(item?.uuid || ''), current = fresh.get(uuid), revision = String(item?.psp_content_revision || '');
        if (!item || typeof item !== 'object' || duplicate.has(uuid)
            || !/^ssq-(?:csb|cse|esr|cnp)-(?:a1|a2|b1|b2|c1|c2)-(?:g|c)[0-9]{2}$/.test(uuid)
            || !current || !/^[a-f0-9]{64}$/.test(revision) || current.psp_content_revision !== revision
            || current.ssq_option_caption_revision !== revision || item.ssq_option_caption_revision !== revision
            || item.type !== current.type || item.question !== current.question || !same(item.answer_map, current.answer_map)
            || (item.contraction_slots_version === 1 && current.contraction_slots_version !== 1)) return item;
        fields.forEach(field => {
            if (Object.prototype.hasOwnProperty.call(current, field) && Object.prototype.hasOwnProperty.call(item, field)
                && same(item[field], current[field])) delete item[field];
        });
        return item;
    });
    return snapshot;
}

function isSentenceStructureLocalJsStorageScope(storageKey, mode) {
    return ['saved-test-js-v2', 'saved-test-js-step-v2'].includes(String(mode || ''))
        && /^gramlyze:js-test-state:v2:[a-f0-9]{64}:ssq-[a-f0-9]{20}$/.test(String(storageKey || ''));
}

function warnSentenceStructureLocalRecovery(destination) {
    if (JS_TEST_SENTENCE_STRUCTURE_RECOVERY_WARNED) return;
    JS_TEST_SENTENCE_STRUCTURE_RECOVERY_WARNED = true;
    console.warn(`Browser test storage is unavailable; current Sentence Structure progress was confirmed in ${destination}.`);
}

function compactVocabCollocationsLocalJsState(state, storageKey, mode) {
    const snapshot = cloneState(state);
    if (!isVocabCollocationsLocalJsStorageScope(storageKey, mode) || !snapshot || !Array.isArray(snapshot.items)) return snapshot;
    const fresh = new Map(), duplicate = new Set();
    getTechnicalQuestions().forEach(question => {
        const uuid = String(question?.uuid || '');
        if (fresh.has(uuid)) duplicate.add(uuid);
        fresh.set(uuid, question);
    });
    const fields = [
        'type', 'question', 'answer', 'answers', 'answer_map', 'accepted_answers', 'accepted_answers_by_marker',
        'answer_synonyms_by_marker', 'answer_synonym_tokens_by_marker', 'compose_accepted_sentences',
        'markers', 'markers_count', 'contraction_slots_version', 'options_by_marker', 'verb_hint', 'verb_hints',
        'options', 'tense', 'is_past_perfect_continuous', 'level', 'theory_block', 'theory_blocks', 'marker_tags', 'tech_info',
        'compose_source_text', 'compose_source_text_uk', 'compose_hint', 'hint', 'compose_content_revision',
        'presentation', 'reorder_prompt', 'reorder_answer', 'reorder_tokens', 'reorder_source_question',
        'reorder_template_constraint', 'reorder_manual_input', 'reorder_groups', 'reorder_accepted_answers',
        'passive_gap_phrase_input', 'passive_phrase_word_inputs', 'gap_compactness_revision', 'gap_previous_question',
        'gap_previous_answer', 'gap_previous_accepted_answers', 'fsp_compose_options_revision', 'qnq_strict_slot_surfaces',
        'vcq_option_caption_revision',
    ];
    const same = (left, right) => JSON.stringify(left) === JSON.stringify(right);
    snapshot.items = snapshot.items.map(item => {
        const uuid = String(item?.uuid || ''), current = fresh.get(uuid), revision = String(item?.psp_content_revision || '');
        if (!item || typeof item !== 'object' || duplicate.has(uuid)
            || !/^vcq-(?:aclc)-(?:a1|a2|b1|b2|c1|c2)-(?:g|c)[0-9]{2}$/.test(uuid)
            || !current || !/^[a-f0-9]{64}$/.test(revision) || current.psp_content_revision !== revision
            || current.vcq_option_caption_revision !== revision || item.vcq_option_caption_revision !== revision
            || item.type !== current.type || item.question !== current.question || !same(item.answer_map, current.answer_map)
            || (item.contraction_slots_version === 1 && current.contraction_slots_version !== 1)) return item;
        fields.forEach(field => {
            if (Object.prototype.hasOwnProperty.call(current, field) && Object.prototype.hasOwnProperty.call(item, field)
                && same(item[field], current[field])) delete item[field];
        });
        return item;
    });
    return snapshot;
}

function isVocabCollocationsLocalJsStorageScope(storageKey, mode) {
    return ['saved-test-js-v2', 'saved-test-js-step-v2'].includes(String(mode || ''))
        && /^gramlyze:js-test-state:v2:[a-f0-9]{64}:vcq-[a-f0-9]{20}$/.test(String(storageKey || ''));
}

function warnVocabCollocationsLocalRecovery(destination) {
    if (JS_TEST_VOCAB_COLLOCATIONS_RECOVERY_WARNED) return;
    JS_TEST_VOCAB_COLLOCATIONS_RECOVERY_WARNED = true;
    console.warn(`Browser test storage is unavailable; current Vocabulary and Collocations progress was confirmed in ${destination}.`);
}

function compactMixedRevisionLocalJsState(state, storageKey, mode) {
    const snapshot = cloneState(state);
    if (!isMixedRevisionLocalJsStorageScope(storageKey, mode) || !snapshot || !Array.isArray(snapshot.items)) return snapshot;
    const fresh = new Map(), duplicate = new Set();
    getTechnicalQuestions().forEach(question => {
        const uuid = String(question?.uuid || '');
        if (fresh.has(uuid)) duplicate.add(uuid);
        fresh.set(uuid, question);
    });
    const fields = [
        'type', 'question', 'answer', 'answers', 'answer_map', 'accepted_answers', 'accepted_answers_by_marker',
        'answer_synonyms_by_marker', 'answer_synonym_tokens_by_marker', 'compose_accepted_sentences',
        'markers', 'markers_count', 'contraction_slots_version', 'options_by_marker', 'verb_hint', 'verb_hints',
        'options', 'tense', 'is_past_perfect_continuous', 'level', 'theory_block', 'theory_blocks', 'marker_tags', 'tech_info',
        'compose_source_text', 'compose_source_text_uk', 'compose_hint', 'hint', 'compose_content_revision',
        'presentation', 'reorder_prompt', 'reorder_answer', 'reorder_tokens', 'reorder_source_question',
        'reorder_template_constraint', 'reorder_manual_input', 'reorder_groups', 'reorder_accepted_answers',
        'passive_gap_phrase_input', 'passive_phrase_word_inputs', 'gap_compactness_revision', 'gap_previous_question',
        'gap_previous_answer', 'gap_previous_accepted_answers', 'fsp_compose_options_revision', 'qnq_strict_slot_surfaces',
        'aaq_option_caption_revision', 'saq_option_caption_revision', 'pvq_option_caption_revision',
    ];
    const same = (left, right) => JSON.stringify(left) === JSON.stringify(right);
    snapshot.items = snapshot.items.map(item => {
        const uuid = String(item?.uuid || ''), current = fresh.get(uuid), revision = String(item?.psp_content_revision || '');
        if (!item || typeof item !== 'object' || duplicate.has(uuid)
            || !/^mrq-(?:a1|a2|b1|b2|c1|c2)-(?:a1|a2|b1|b2|c1|c2)-(?:g|c)[0-9]{2}$/.test(uuid)
            || !current || !/^[a-f0-9]{64}$/.test(revision) || current.psp_content_revision !== revision
            || current.mrq_option_caption_revision !== revision || item.mrq_option_caption_revision !== revision
            || item.type !== current.type || item.question !== current.question || !same(item.answer_map, current.answer_map)
            || (item.contraction_slots_version === 1 && current.contraction_slots_version !== 1)) return item;
        fields.forEach(field => {
            if (Object.prototype.hasOwnProperty.call(current, field) && Object.prototype.hasOwnProperty.call(item, field)
                && same(item[field], current[field])) delete item[field];
        });
        return item;
    });
    return snapshot;
}

function isMixedRevisionLocalJsStorageScope(storageKey, mode) {
    return ['saved-test-js-v2', 'saved-test-js-step-v2'].includes(String(mode || ''))
        && /^gramlyze:js-test-state:v2:[a-f0-9]{64}:mrq-[a-f0-9]{20}$/.test(String(storageKey || ''));
}

function warnMixedRevisionLocalRecovery(destination) {
    if (JS_TEST_MIXED_REVISION_RECOVERY_WARNED) return;
    JS_TEST_MIXED_REVISION_RECOVERY_WARNED = true;
    console.warn(`Browser test storage is unavailable; current Mixed Revision progress was confirmed in ${destination}.`);
}

function compactVerbPatternsLocalJsState(state, storageKey, mode) {
    const snapshot = cloneState(state);
    if (!isVerbPatternsLocalJsStorageScope(storageKey, mode) || !snapshot || !Array.isArray(snapshot.items)) return snapshot;
    const fresh = new Map(), duplicate = new Set();
    getTechnicalQuestions().forEach(question => {
        const uuid = String(question?.uuid || '');
        if (fresh.has(uuid)) duplicate.add(uuid);
        fresh.set(uuid, question);
    });
    const fields = [
        'type', 'question', 'answer', 'answers', 'answer_map', 'accepted_answers', 'accepted_answers_by_marker',
        'answer_synonyms_by_marker', 'answer_synonym_tokens_by_marker', 'compose_accepted_sentences',
        'markers', 'markers_count', 'contraction_slots_version', 'options_by_marker', 'verb_hint', 'verb_hints',
        'options', 'tense', 'is_past_perfect_continuous', 'level', 'theory_block', 'theory_blocks', 'marker_tags', 'tech_info',
        'compose_source_text', 'compose_source_text_uk', 'compose_hint', 'hint', 'compose_content_revision',
        'presentation', 'reorder_prompt', 'reorder_answer', 'reorder_tokens', 'reorder_source_question',
        'reorder_template_constraint', 'reorder_manual_input', 'reorder_groups', 'reorder_accepted_answers',
        'passive_gap_phrase_input', 'passive_phrase_word_inputs', 'gap_compactness_revision', 'gap_previous_question',
        'gap_previous_answer', 'gap_previous_accepted_answers', 'fsp_compose_options_revision', 'qnq_strict_slot_surfaces',
        'aaq_option_caption_revision', 'saq_option_caption_revision', 'pvq_option_caption_revision',
    ];
    const same = (left, right) => JSON.stringify(left) === JSON.stringify(right);
    snapshot.items = snapshot.items.map(item => {
        const uuid = String(item?.uuid || ''), current = fresh.get(uuid), revision = String(item?.psp_content_revision || '');
        if (!item || typeof item !== 'object' || duplicate.has(uuid)
            || !/^vpq-(?:gerund|to|bare|contrast|verbs-gerund|verbs-infinitive|meaning|used|advanced)-(?:a1|a2|b1|b2|c1|c2)-(?:g|c)[0-9]{2}$/.test(uuid)
            || !current || !/^[a-f0-9]{64}$/.test(revision) || current.psp_content_revision !== revision
            || current.vpq_option_caption_revision !== revision || item.vpq_option_caption_revision !== revision
            || item.type !== current.type || item.question !== current.question || !same(item.answer_map, current.answer_map)
            || (item.contraction_slots_version === 1 && current.contraction_slots_version !== 1)) return item;
        fields.forEach(field => {
            if (Object.prototype.hasOwnProperty.call(current, field) && Object.prototype.hasOwnProperty.call(item, field)
                && same(item[field], current[field])) delete item[field];
        });
        return item;
    });
    return snapshot;
}

function isVerbPatternsLocalJsStorageScope(storageKey, mode) {
    return ['saved-test-js-v2', 'saved-test-js-step-v2'].includes(String(mode || ''))
        && /^gramlyze:js-test-state:v2:[a-f0-9]{64}:vpq-[a-f0-9]{20}$/.test(String(storageKey || ''));
}

function warnVerbPatternsLocalRecovery(destination) {
    if (JS_TEST_VERB_PATTERNS_RECOVERY_WARNED) return;
    JS_TEST_VERB_PATTERNS_RECOVERY_WARNED = true;
    console.warn(`Browser test storage is unavailable; current Verb Patterns progress was confirmed in ${destination}.`);
}

function compactCommonMistakesLocalJsState(state, storageKey, mode) {
    const snapshot = cloneState(state);
    if (!isCommonMistakesLocalJsStorageScope(storageKey, mode) || !snapshot || !Array.isArray(snapshot.items)) return snapshot;
    const fresh = new Map(), duplicate = new Set();
    getTechnicalQuestions().forEach(question => {
        const uuid = String(question?.uuid || '');
        if (fresh.has(uuid)) duplicate.add(uuid);
        fresh.set(uuid, question);
    });
    const fields = [
        'type', 'question', 'answer', 'answers', 'answer_map', 'accepted_answers', 'accepted_answers_by_marker',
        'answer_synonyms_by_marker', 'answer_synonym_tokens_by_marker', 'compose_accepted_sentences',
        'markers', 'markers_count', 'contraction_slots_version', 'options_by_marker', 'verb_hint', 'verb_hints',
        'options', 'tense', 'is_past_perfect_continuous', 'level', 'theory_block', 'theory_blocks', 'marker_tags', 'tech_info',
        'compose_source_text', 'compose_source_text_uk', 'compose_hint', 'hint', 'compose_content_revision',
        'presentation', 'reorder_prompt', 'reorder_answer', 'reorder_tokens', 'reorder_source_question',
        'reorder_template_constraint', 'reorder_manual_input', 'reorder_groups', 'reorder_accepted_answers',
        'passive_gap_phrase_input', 'passive_phrase_word_inputs', 'gap_compactness_revision', 'gap_previous_question',
        'gap_previous_answer', 'gap_previous_accepted_answers', 'fsp_compose_options_revision', 'qnq_strict_slot_surfaces',
        'aaq_option_caption_revision', 'saq_option_caption_revision', 'pvq_option_caption_revision',
    ];
    const same = (left, right) => JSON.stringify(left) === JSON.stringify(right);
    snapshot.items = snapshot.items.map(item => {
        const uuid = String(item?.uuid || ''), current = fresh.get(uuid), revision = String(item?.psp_content_revision || '');
        if (!item || typeof item !== 'object' || duplicate.has(uuid)
            || !/^cmq-(?:articles|perfect|countable|prepositions|traps)-(?:a1|a2|b1|b2|c1|c2)-(?:g|c)[0-9]{2}$/.test(uuid)
            || !current || !/^[a-f0-9]{64}$/.test(revision) || current.psp_content_revision !== revision
            || current.cmq_option_caption_revision !== revision || item.cmq_option_caption_revision !== revision
            || item.type !== current.type || item.question !== current.question || !same(item.answer_map, current.answer_map)
            || (item.contraction_slots_version === 1 && current.contraction_slots_version !== 1)) return item;
        fields.forEach(field => {
            if (Object.prototype.hasOwnProperty.call(current, field) && Object.prototype.hasOwnProperty.call(item, field)
                && same(item[field], current[field])) delete item[field];
        });
        return item;
    });
    return snapshot;
}

function isCommonMistakesLocalJsStorageScope(storageKey, mode) {
    return ['saved-test-js-v2', 'saved-test-js-step-v2'].includes(String(mode || ''))
        && /^gramlyze:js-test-state:v2:[a-f0-9]{64}:cmq-(?:parent-)?[a-f0-9]{20}$/.test(String(storageKey || ''));
}

function warnCommonMistakesLocalRecovery(destination) {
    if (JS_TEST_COMMON_MISTAKES_RECOVERY_WARNED) return;
    JS_TEST_COMMON_MISTAKES_RECOVERY_WARNED = true;
    console.warn(`Browser test storage is unavailable; current Common Mistakes progress was confirmed in ${destination}.`);
}

function compactSentenceTransformationsLocalJsState(state, storageKey, mode) {
    const snapshot = cloneState(state);
    if (!isSentenceTransformationsLocalJsStorageScope(storageKey, mode) || !snapshot || !Array.isArray(snapshot.items)) return snapshot;
    const fresh = new Map(), duplicate = new Set();
    getTechnicalQuestions().forEach(question => {
        const uuid = String(question?.uuid || '');
        if (fresh.has(uuid)) duplicate.add(uuid);
        fresh.set(uuid, question);
    });
    const fields = [
        'type', 'question', 'answer', 'answers', 'answer_map', 'accepted_answers', 'accepted_answers_by_marker',
        'answer_synonyms_by_marker', 'answer_synonym_tokens_by_marker', 'compose_accepted_sentences',
        'markers', 'markers_count', 'contraction_slots_version', 'options_by_marker', 'verb_hint', 'verb_hints',
        'options', 'tense', 'is_past_perfect_continuous', 'level', 'theory_block', 'theory_blocks', 'marker_tags', 'tech_info',
        'compose_source_text', 'compose_source_text_uk', 'compose_hint', 'hint', 'compose_content_revision',
        'presentation', 'reorder_prompt', 'reorder_answer', 'reorder_tokens', 'reorder_source_question',
        'reorder_template_constraint', 'reorder_manual_input', 'reorder_groups', 'reorder_accepted_answers',
        'passive_gap_phrase_input', 'passive_phrase_word_inputs', 'gap_compactness_revision', 'gap_previous_question',
        'gap_previous_answer', 'gap_previous_accepted_answers', 'fsp_compose_options_revision', 'qnq_strict_slot_surfaces',
        'aaq_option_caption_revision', 'saq_option_caption_revision', 'pvq_option_caption_revision',
    ];
    const same = (left, right) => JSON.stringify(left) === JSON.stringify(right);
    snapshot.items = snapshot.items.map(item => {
        const uuid = String(item?.uuid || ''), current = fresh.get(uuid), revision = String(item?.psp_content_revision || '');
        if (!item || typeof item !== 'object' || duplicate.has(uuid)
            || !/^stq-(?:tenses|modals|passive|reported|conditionals|clauses|articles)-(?:a1|a2|b1|b2|c1|c2)-(?:g|c)[0-9]{2}$/.test(uuid)
            || !current || !/^[a-f0-9]{64}$/.test(revision) || current.psp_content_revision !== revision
            || current.stq_option_caption_revision !== revision || item.stq_option_caption_revision !== revision
            || item.type !== current.type || item.question !== current.question || !same(item.answer_map, current.answer_map)
            || (item.contraction_slots_version === 1 && current.contraction_slots_version !== 1)) return item;
        fields.forEach(field => {
            if (Object.prototype.hasOwnProperty.call(current, field) && Object.prototype.hasOwnProperty.call(item, field)
                && same(item[field], current[field])) delete item[field];
        });
        return item;
    });
    return snapshot;
}

function isSentenceTransformationsLocalJsStorageScope(storageKey, mode) {
    return ['saved-test-js-v2', 'saved-test-js-step-v2'].includes(String(mode || ''))
        && /^gramlyze:js-test-state:v2:[a-f0-9]{64}:stq-(?:parent-)?[a-f0-9]{20}$/.test(String(storageKey || ''));
}

function warnSentenceTransformationsLocalRecovery(destination) {
    if (JS_TEST_SENTENCE_TRANSFORMATIONS_RECOVERY_WARNED) return;
    JS_TEST_SENTENCE_TRANSFORMATIONS_RECOVERY_WARNED = true;
    console.warn(`Browser test storage is unavailable; current Sentence Transformations progress was confirmed in ${destination}.`);
}

function writeLocalJsTestState(state, deferModalErrors = false) {
    if (!JS_TEST_PERSISTENCE || !JS_TEST_PERSISTENCE.storageKey || !isStartedState(state)) {
        return;
    }

    const primaryKey = JS_TEST_PERSISTENCE.storageKey;
    const storageKeys = Array.isArray(JS_TEST_PERSISTENCE.storageKeys) && JS_TEST_PERSISTENCE.storageKeys.length > 0
        ? JS_TEST_PERSISTENCE.storageKeys
        : [primaryKey];
    const legacyKeys = storageKeys.filter((key) => key !== primaryKey);
    const modalScope = isModalLocalJsStorageScope(primaryKey, JS_TEST_PERSISTENCE.mode);
    const conditionalsScope = typeof isConditionalsLocalJsStorageScope === 'function'
        && isConditionalsLocalJsStorageScope(primaryKey, JS_TEST_PERSISTENCE.mode);
    const reportedSpeechScope = typeof isReportedSpeechLocalJsStorageScope === 'function'
        && isReportedSpeechLocalJsStorageScope(primaryKey, JS_TEST_PERSISTENCE.mode);
    const clausesLinkingScope = typeof isClausesLinkingLocalJsStorageScope === 'function'
        && isClausesLinkingLocalJsStorageScope(primaryKey, JS_TEST_PERSISTENCE.mode);
    const prepositionsPhrasalScope = typeof isPrepositionsPhrasalLocalJsStorageScope === 'function'
        && isPrepositionsPhrasalLocalJsStorageScope(primaryKey, JS_TEST_PERSISTENCE.mode);
    const commonMistakesScope = typeof isCommonMistakesLocalJsStorageScope === 'function'
        && isCommonMistakesLocalJsStorageScope(primaryKey, JS_TEST_PERSISTENCE.mode);
    const sentenceTransformationsScope = typeof isSentenceTransformationsLocalJsStorageScope === 'function'
        && isSentenceTransformationsLocalJsStorageScope(primaryKey, JS_TEST_PERSISTENCE.mode);
    const verbPatternsScope = typeof isVerbPatternsLocalJsStorageScope === 'function'
        && isVerbPatternsLocalJsStorageScope(primaryKey, JS_TEST_PERSISTENCE.mode);
    const mixedRevisionScope = typeof isMixedRevisionLocalJsStorageScope === 'function'
        && isMixedRevisionLocalJsStorageScope(primaryKey, JS_TEST_PERSISTENCE.mode);
    const academicEnglishScope = typeof isAcademicEnglishLocalJsStorageScope === 'function'
        && isAcademicEnglishLocalJsStorageScope(primaryKey, JS_TEST_PERSISTENCE.mode);
    const articlesQuantifiersScope = typeof isArticlesQuantifiersLocalJsStorageScope === 'function'
        && isArticlesQuantifiersLocalJsStorageScope(primaryKey, JS_TEST_PERSISTENCE.mode);
    const formalEnglishScope = typeof isFormalEnglishLocalJsStorageScope === 'function'
        && isFormalEnglishLocalJsStorageScope(primaryKey, JS_TEST_PERSISTENCE.mode);
    const relativeClausesScope = typeof isRelativeClausesLocalJsStorageScope === 'function'
        && isRelativeClausesLocalJsStorageScope(primaryKey, JS_TEST_PERSISTENCE.mode);
    const sentenceStructureScope = typeof isSentenceStructureLocalJsStorageScope === 'function'
        && isSentenceStructureLocalJsStorageScope(primaryKey, JS_TEST_PERSISTENCE.mode);
    const vocabCollocationsScope = typeof isVocabCollocationsLocalJsStorageScope === 'function'
        && isVocabCollocationsLocalJsStorageScope(primaryKey, JS_TEST_PERSISTENCE.mode);
    const localState = vocabCollocationsScope ? compactVocabCollocationsLocalJsState(state, primaryKey, JS_TEST_PERSISTENCE.mode)
        : sentenceStructureScope ? compactSentenceStructureLocalJsState(state, primaryKey, JS_TEST_PERSISTENCE.mode)
        : relativeClausesScope ? compactRelativeClausesLocalJsState(state, primaryKey, JS_TEST_PERSISTENCE.mode)
        : formalEnglishScope ? compactFormalEnglishLocalJsState(state, primaryKey, JS_TEST_PERSISTENCE.mode)
        : articlesQuantifiersScope ? compactArticlesQuantifiersLocalJsState(state, primaryKey, JS_TEST_PERSISTENCE.mode)
        : academicEnglishScope ? compactAcademicEnglishLocalJsState(state, primaryKey, JS_TEST_PERSISTENCE.mode)
        : mixedRevisionScope ? compactMixedRevisionLocalJsState(state, primaryKey, JS_TEST_PERSISTENCE.mode)
        : verbPatternsScope ? compactVerbPatternsLocalJsState(state, primaryKey, JS_TEST_PERSISTENCE.mode)
        : modalScope ? compactModalLocalJsState(state, primaryKey, JS_TEST_PERSISTENCE.mode)
        : (conditionalsScope ? compactConditionalsLocalJsState(state, primaryKey, JS_TEST_PERSISTENCE.mode)
        : (reportedSpeechScope ? compactReportedSpeechLocalJsState(state, primaryKey, JS_TEST_PERSISTENCE.mode)
        : (clausesLinkingScope ? compactClausesLinkingLocalJsState(state, primaryKey, JS_TEST_PERSISTENCE.mode)
        : (prepositionsPhrasalScope ? compactPrepositionsPhrasalLocalJsState(state, primaryKey, JS_TEST_PERSISTENCE.mode)
        : (commonMistakesScope ? compactCommonMistakesLocalJsState(state, primaryKey, JS_TEST_PERSISTENCE.mode)
        : (sentenceTransformationsScope ? compactSentenceTransformationsLocalJsState(state, primaryKey, JS_TEST_PERSISTENCE.mode) : cloneState(state)))))));

    if (localState?.__meta && typeof localState.__meta === 'object') {
        delete localState.__meta.question_data;
    }

    const payload = JSON.stringify({
        saved_at: new Date().toISOString(),
        state: localState,
    });
    if (modalScope || conditionalsScope || reportedSpeechScope || clausesLinkingScope || prepositionsPhrasalScope || commonMistakesScope || sentenceTransformationsScope || verbPatternsScope || mixedRevisionScope || academicEnglishScope || articlesQuantifiersScope || formalEnglishScope || relativeClausesScope || sentenceStructureScope || vocabCollocationsScope) {
        const failures = [];
        try {
            window.localStorage.setItem(primaryKey, payload);
            if (window.localStorage.getItem(primaryKey) !== payload) throw new Error('Modal local progress write was not confirmed');
            return { modalStored: 'local' };
        } catch (error) { failures.push(error); }
        try {
            window.sessionStorage.setItem(primaryKey, payload);
            if (window.sessionStorage.getItem(primaryKey) !== payload) throw new Error('Modal session progress write was not confirmed');
            if (conditionalsScope) warnConditionalsLocalRecovery('session storage');
            else if (reportedSpeechScope) warnReportedSpeechLocalRecovery('session storage');
            else if (clausesLinkingScope) warnClausesLinkingLocalRecovery('session storage');
            else if (prepositionsPhrasalScope) warnPrepositionsPhrasalLocalRecovery('session storage');
            else if (commonMistakesScope) warnCommonMistakesLocalRecovery('session storage');
            else if (sentenceTransformationsScope) warnSentenceTransformationsLocalRecovery('session storage');
            else if (verbPatternsScope) warnVerbPatternsLocalRecovery('session storage');
            else if (mixedRevisionScope) warnMixedRevisionLocalRecovery('session storage');
            else if (academicEnglishScope) warnAcademicEnglishLocalRecovery('session storage');
            else if (articlesQuantifiersScope) warnArticlesQuantifiersLocalRecovery('session storage');
            else if (formalEnglishScope) warnFormalEnglishLocalRecovery('session storage');
            else if (relativeClausesScope) warnRelativeClausesLocalRecovery('session storage');
            else if (sentenceStructureScope) warnSentenceStructureLocalRecovery('session storage');
            else if (vocabCollocationsScope) warnVocabCollocationsLocalRecovery('session storage');
            else warnModalLocalRecovery('session storage');
            return { modalStored: 'session' };
        } catch (error) { failures.push(error); }
        // Do not remove any current, legacy, course, preference or other-topic keys.
        // A server recovery may downgrade the warning only after a successful response.
        const confirmedSnapshot = vocabCollocationsScope ? JS_TEST_CONFIRMED_VOCAB_COLLOCATIONS_SNAPSHOT
            : sentenceStructureScope ? JS_TEST_CONFIRMED_SENTENCE_STRUCTURE_SNAPSHOT
            : relativeClausesScope ? JS_TEST_CONFIRMED_RELATIVE_CLAUSES_SNAPSHOT
            : formalEnglishScope ? JS_TEST_CONFIRMED_FORMAL_ENGLISH_SNAPSHOT
            : articlesQuantifiersScope ? JS_TEST_CONFIRMED_ARTICLES_QUANTIFIERS_SNAPSHOT
            : academicEnglishScope ? JS_TEST_CONFIRMED_ACADEMIC_ENGLISH_SNAPSHOT
            : mixedRevisionScope ? JS_TEST_CONFIRMED_MIXED_REVISION_SNAPSHOT
            : verbPatternsScope ? JS_TEST_CONFIRMED_VERB_PATTERNS_SNAPSHOT
            : conditionalsScope ? JS_TEST_CONFIRMED_CONDITIONALS_SNAPSHOT
            : (reportedSpeechScope ? JS_TEST_CONFIRMED_REPORTED_SPEECH_SNAPSHOT
            : (clausesLinkingScope ? JS_TEST_CONFIRMED_CLAUSES_LINKING_SNAPSHOT
            : (prepositionsPhrasalScope ? JS_TEST_CONFIRMED_PREPOSITIONS_PHRASAL_SNAPSHOT
            : (commonMistakesScope ? JS_TEST_CONFIRMED_COMMON_MISTAKES_SNAPSHOT
            : (sentenceTransformationsScope ? JS_TEST_CONFIRMED_SENTENCE_TRANSFORMATIONS_SNAPSHOT : JS_TEST_CONFIRMED_MODAL_SNAPSHOT)))));
        if (confirmedSnapshot !== null && JSON.stringify(state) === confirmedSnapshot) {
            if (conditionalsScope) warnConditionalsLocalRecovery('server storage');
            else if (reportedSpeechScope) warnReportedSpeechLocalRecovery('server storage');
            else if (clausesLinkingScope) warnClausesLinkingLocalRecovery('server storage');
            else if (prepositionsPhrasalScope) warnPrepositionsPhrasalLocalRecovery('server storage');
            else if (commonMistakesScope) warnCommonMistakesLocalRecovery('server storage');
            else if (sentenceTransformationsScope) warnSentenceTransformationsLocalRecovery('server storage');
            else if (verbPatternsScope) warnVerbPatternsLocalRecovery('server storage');
            else if (mixedRevisionScope) warnMixedRevisionLocalRecovery('server storage');
            else if (academicEnglishScope) warnAcademicEnglishLocalRecovery('server storage');
            else if (articlesQuantifiersScope) warnArticlesQuantifiersLocalRecovery('server storage');
            else if (formalEnglishScope) warnFormalEnglishLocalRecovery('server storage');
            else if (relativeClausesScope) warnRelativeClausesLocalRecovery('server storage');
            else if (sentenceStructureScope) warnSentenceStructureLocalRecovery('server storage');
            else if (vocabCollocationsScope) warnVocabCollocationsLocalRecovery('server storage');
            else warnModalLocalRecovery('server storage');
            return { modalStored: 'server' };
        }
        if (deferModalErrors && JS_TEST_PERSISTENCE.endpoint) return { modalRecoveryPending: true, failures };
        failures.forEach(error => console.error(error));
        return { modalStored: false };
    }
    const removeLegacyKeys = (storage) => {
        legacyKeys.forEach((key) => storage.removeItem(key));
    };

    try {
        window.localStorage.setItem(primaryKey, payload);
        removeLegacyKeys(window.localStorage);
        window.sessionStorage.removeItem(primaryKey);
        removeLegacyKeys(window.sessionStorage);
        return;
    } catch (error) {
        console.error(error);
    }

    try {
        removeLegacyKeys(window.localStorage);
        window.localStorage.setItem(primaryKey, payload);
        window.sessionStorage.removeItem(primaryKey);
        removeLegacyKeys(window.sessionStorage);
        return;
    } catch (error) {
        console.error(error);
    }

    try {
        window.sessionStorage.setItem(primaryKey, payload);
        removeLegacyKeys(window.sessionStorage);
    } catch (error) {
        console.error(error);
    }
}

function clearLocalJsTestState() {
    if (!JS_TEST_PERSISTENCE || !JS_TEST_PERSISTENCE.storageKey) {
        return;
    }

    const storageKeys = Array.isArray(JS_TEST_PERSISTENCE.storageKeys) && JS_TEST_PERSISTENCE.storageKeys.length > 0
        ? JS_TEST_PERSISTENCE.storageKeys
        : [JS_TEST_PERSISTENCE.storageKey];

    [window.localStorage, window.sessionStorage].forEach((storage) => {
        if (!storage) {
            return;
        }

        storageKeys.forEach((key) => {
            try {
                storage.removeItem(key);
            } catch (error) {
                console.error(error);
            }
        });
    });
}

// Undo only a provable legacy contraction grouping of this same PSP source.
// Unknown/changed source progress is not reinterpreted or silently discarded.
function restorePspCanonicalSlotProgress(saved, fresh) {
    if (!Array.isArray(saved.answers) || !Array.isArray(saved.markers) || !Array.isArray(fresh.answers)
        || !Array.isArray(fresh.markers) || fresh.answers.some(answer => String(answer).trim().split(/\s+/).length !== 1)) return null;
    const normalize = EnglishAnswerVariants.normalize;
    const starts = saved.markers.map(marker => fresh.markers.indexOf(marker));
    if (starts[0] !== 0 || starts.some((start, index) => start < 0 || (index && start <= starts[index - 1]))) return null;
    const ends = starts.slice(1).concat(fresh.answers.length);
    if (saved.answers.length !== starts.length || saved.answers.some((answer, index) =>
        normalize(answer) !== normalize(fresh.answers.slice(starts[index], ends[index]).join(' ')))) return null;
    const count = fresh.answers.length;
    const patch = {chosen: Array(count).fill(null), manualInputsBySlot: Array(count).fill(''),
        manualWordIndexBySlot: Array(count).fill(0), attemptsBySlot: Array(count).fill(0),
        lastWrongBySlot: Array(count).fill(null), wordSuggestionsBySlot: Array.from({length: count}, () => []),
        wordSearchRequestBySlot: Array(count).fill(0)};
    for (let index = 0; index < starts.length; index++) {
        const start = starts[index], size = ends[index] - start;
        const confirmed = saved.chosen?.[index];
        if (confirmed != null) {
            const words = EnglishAnswerVariants.variants(confirmed).map(value => String(value).trim().split(/\s+/))
                .find(parts => parts.length === size && parts.every((word, offset) => {
                    const marker = fresh.markers[start + offset];
                    const candidates = [fresh.answers[start + offset], ...(fresh.accepted_answers_by_marker?.[marker] || [])];
                    return candidates.some(value => normalize(value) === normalize(word));
                }));
            if (!words) return null;
            words.forEach((word, offset) => { patch.chosen[start + offset] = word; patch.manualInputsBySlot[start + offset] = word; });
        } else {
            const words = String(saved.manualInputsBySlot?.[index] || '').trim().split(/\s+/).filter(Boolean);
            if (words.length > size) return null;
            const confirmedCount = Math.max(0, Math.min(size, Number(saved.manualWordIndexBySlot?.[index] || 0)));
            words.forEach((word, offset) => {
                patch.manualInputsBySlot[start + offset] = word;
                if (offset < confirmedCount) patch.chosen[start + offset] = word;
            });
        }
        patch.attemptsBySlot[start] = saved.attemptsBySlot?.[index] || 0;
        patch.lastWrongBySlot[start] = saved.lastWrongBySlot?.[index] ?? null;
    }
    const active = Number(saved.activeSlot || 0);
    patch.activeSlot = Math.min(count - 1, (starts[active] || 0) + Number(saved.manualWordIndexBySlot?.[active] || 0));
    patch.slot = patch.activeSlot;
    if (saved.feedbackMeta && Number.isInteger(saved.feedbackMeta.slotIndex)) {
        patch.feedbackMeta = {...saved.feedbackMeta, slotIndex: starts[saved.feedbackMeta.slotIndex] ?? saved.feedbackMeta.slotIndex};
    }
    return patch;
}

function mergeFreshQuestionContentIntoSavedState(state) {
    const restored = cloneState(state);
    const freshQuestions = cloneState(getTechnicalQuestions());
    const canonicalQuestionFields = [
        'id',
        'uuid',
        'type',
        'question',
        'answer',
        'answers',
        'answer_map',
        'accepted_answers',
        'accepted_answers_by_marker',
        'answer_synonyms_by_marker',
        'answer_synonym_tokens_by_marker',
        'compose_accepted_sentences',
        'psp_content_revision',
        'markers',
        'markers_count',
        'contraction_slots_version',
        'options_by_marker',
        'verb_hint',
        'verb_hints',
        'options',
        'tense',
        'is_past_perfect_continuous',
        'level',
        'theory_block',
        'theory_blocks',
        'marker_tags',
        'tech_info',
    ];

    if (!restored || typeof restored !== 'object'
        || !Array.isArray(restored.items)
        || !Array.isArray(freshQuestions)
        || freshQuestions.length === 0) {
        return restored;
    }

    const freshByUuid = new Map();
    const freshById = new Map();
    freshQuestions.forEach((question) => {
        if (!question || typeof question !== 'object') {
            return;
        }

        const uuid = String(question.uuid ?? '').trim();
        const id = String(question.id ?? '').trim();
        if (uuid !== '') freshByUuid.set(uuid, question);
        if (id !== '') freshById.set(id, question);
    });

    restored.items = restored.items.map((savedItem) => {
        savedItem = EnglishAnswerVariants.prepareQuestion(savedItem);
        if (!savedItem || typeof savedItem !== 'object') {
            return savedItem;
        }

        const uuid = String(savedItem.uuid ?? '').trim();
        const id = String(savedItem.id ?? '').trim();
        const freshQuestion = uuid !== ''
            ? freshByUuid.get(uuid)
            : (id !== '' ? freshById.get(id) : null);

        if (!freshQuestion) {
            return savedItem;
        }

        let pspProgress = null;
        let wordProgress = null;
        if (typeof presentPerfectPassiveWordPolicy === 'function' && presentPerfectPassiveWordPolicy(freshQuestion)) {
            wordProgress = restorePresentPerfectPassiveWordProgress(savedItem, freshQuestion);
            if (wordProgress === null) return savedItem;
        }
        if (freshQuestion.psp_content_revision && String(freshQuestion.type) === '4'
            && savedItem.contraction_slots_version === 1) {
            pspProgress = restorePspCanonicalSlotProgress(savedItem, freshQuestion);
            if (pspProgress === null) return savedItem;
        }

        // A finite, source-bound Future Passive correction moves the already
        // visible auxiliary/subject out of 11 long gaps. Translate only proven
        // old answers; keep totals, retries and all unrelated saved progress.
        let gapProgress = null;
        if (freshQuestion.passive_phrase_word_inputs === true
            && freshQuestion.passive_gap_phrase_input === true
            && /^[a-f0-9]{64}$/.test(String(freshQuestion.psp_content_revision || ''))
            && String(freshQuestion.type) === '0'
            && savedItem.answers?.length === 1
            && ((savedItem.question === freshQuestion.gap_previous_question
                    && savedItem.answers[0] === freshQuestion.gap_previous_answer)
                || (savedItem.question === freshQuestion.question
                    && savedItem.answers[0] === freshQuestion.answers?.[0]))
            && freshQuestion.gap_previous_accepted_answers && typeof freshQuestion.gap_previous_accepted_answers === 'object'
            && !Array.isArray(freshQuestion.gap_previous_accepted_answers)) {
            const normalize = EnglishAnswerVariants.normalize;
            const mappings = Object.entries(freshQuestion.gap_previous_accepted_answers)
                .filter(([previous, current]) => typeof previous === 'string' && typeof current === 'string');
            const project = value => mappings.find(([previous]) => EnglishAnswerVariants.variants(previous)
                .some(variant => normalize(variant) === normalize(value)))?.[1] ?? null;
            const chosen = savedItem.chosen?.[0];
            const mappedChosen = chosen == null ? null : project(chosen);
            if (chosen == null || mappedChosen !== null) {
                const draft = String(savedItem.manualInputsBySlot?.[0] || '').trim();
                let mappedDraft = mappedChosen ?? project(draft);
                // A prior server refresh may already have copied the current
                // stem/answer while leaving a proven old full chosen phrase.
                // Recover that exact mapping only; do not reinterpret current
                // compact drafts or arbitrary incomplete text in this shape.
                if (mappedDraft === null && savedItem.question === freshQuestion.gap_previous_question
                    && savedItem.answers[0] === freshQuestion.gap_previous_answer) {
                    for (const [previous, current] of mappings) {
                        const oldText = normalize(previous), newText = normalize(current);
                        if (!oldText.endsWith(` ${newText}`)) continue;
                        const visible = oldText.slice(0, -newText.length).trim();
                        const typed = normalize(draft);
                        if (typed === '' || visible === typed || visible.startsWith(`${typed} `)) { mappedDraft = ''; break; }
                        if (typed.startsWith(`${visible} `) && oldText.startsWith(`${typed} `)) {
                            mappedDraft = typed.slice(visible.length).trim(); break;
                        }
                    }
                }
                if (mappedDraft !== null) {
                    gapProgress = {chosen: [mappedChosen], manualInputsBySlot: [mappedDraft],
                        manualWordIndexBySlot: [Math.max(0, mappedDraft.split(/\s+/).filter(Boolean).length - 1)]};
                    if (savedItem.feedbackMeta && mappedChosen !== null) {
                        gapProgress.feedbackMeta = {...savedItem.feedbackMeta,
                            submittedAnswer: mappedChosen, displayedAnswer: mappedChosen};
                    }
                }
            }
        }

        // Keep answer/progress-only fields from the snapshot, but always use
        // the current question, options, theory and verb hints from the page.
        const merged = { ...savedItem };
        const fields = freshQuestion.compose_content_revision || freshQuestion.psp_content_revision
            ? [...canonicalQuestionFields, 'compose_source_text', 'compose_source_text_uk', 'compose_hint', 'hint', 'compose_content_revision',
                'presentation', 'reorder_prompt', 'reorder_answer', 'reorder_tokens', 'reorder_source_question', 'reorder_template_constraint',
                'reorder_manual_input', 'reorder_groups', 'reorder_accepted_answers', 'passive_gap_phrase_input',
                'passive_phrase_word_inputs', 'gap_compactness_revision', 'gap_previous_question', 'gap_previous_answer', 'gap_previous_accepted_answers',
                'naqq_generic_reference_caption_revision', 'fsp_compose_options_revision', 'qnq_strict_slot_surfaces', 'aaq_option_caption_revision', 'saq_option_caption_revision', 'pvq_option_caption_revision', 'pvq_word_slots', 'finite_word_slots', 'mvq_option_caption_revision', 'cqq_option_caption_revision', 'rsq_option_caption_revision', 'clq_option_caption_revision', 'ppq_option_caption_revision', 'cmq_option_caption_revision', 'stq_option_caption_revision', 'stq_reused_hint_revision', 'vpq_option_caption_revision', 'mrq_option_caption_revision', 'aeq_option_caption_revision', 'aqv_option_caption_revision', 'feq_option_caption_revision', 'rcq_option_caption_revision', 'ssq_option_caption_revision', 'vcq_option_caption_revision']
            : canonicalQuestionFields;
        fields.forEach((field) => {
            if (Object.prototype.hasOwnProperty.call(freshQuestion, field)) {
                merged[field] = cloneState(freshQuestion[field]);
            } else if (freshQuestion.psp_content_revision && !canonicalQuestionFields.includes(field)) {
                delete merged[field];
            }
        });
        if (pspProgress !== null) {
            Object.assign(merged, pspProgress);
            delete merged.contraction_slots_version;
        }
        if (gapProgress !== null) Object.assign(merged, gapProgress);
        if (wordProgress !== null) Object.assign(merged, wordProgress);
        // Old snapshots may contain green prefix feedback despite having no
        // chosen answer. Refresh that presentation without erasing the draft.
        if (merged.passive_gap_phrase_input === true && String(merged.type) === '0'
            && /^[a-f0-9]{64}$/.test(String(merged.psp_content_revision || ''))
            && merged.feedback === 'correct' && !merged.done
            && Array.isArray(merged.chosen) && merged.chosen.some(value => value == null)) {
            merged.feedback = '';
        }

        return merged;
    });

    if (restored.__meta && typeof restored.__meta === 'object') {
        restored.__meta.question_data = cloneState(freshQuestions);
    }

    return restored;
}

function getSavedState() {
    if (!JS_TEST_PERSISTENCE) {
        return null;
    }

    const serverState = JS_TEST_PERSISTENCE.saved
        ? cloneState(JS_TEST_PERSISTENCE.saved)
        : null;
    const localState = readLocalJsTestState();
    const savedAt = (value) => {
        const timestamp = Date.parse(String(value?.__meta?.saved_at ?? ''));
        return Number.isFinite(timestamp) ? timestamp : 0;
    };
    const selected = localState && (!serverState || savedAt(localState) > savedAt(serverState))
        ? localState
        : (serverState || localState);

    const refreshed = selected ? mergeFreshQuestionContentIntoSavedState(selected) : null;

    if (refreshed) {
        JS_TEST_PERSISTENCE.saved = cloneState(refreshed);
        JS_TEST_LAST_SNAPSHOT = cloneState(refreshed);
    }

    return refreshed ? cloneState(refreshed) : null;
}

function persistState(state, immediate = false) {
    if (!JS_TEST_PERSISTENCE || JS_TEST_RESTARTING) {
        return;
    }

    const previousStarted = Boolean(JS_TEST_PERSISTENCE?.saved?.__meta?.started);
    const snapshot = prepareStateForPersistence(state);
    const started = Boolean(snapshot?.__meta?.started);
    JS_TEST_PERSISTENCE.saved = snapshot;
    JS_TEST_LAST_SNAPSHOT = snapshot;
    const localPersistence = writeLocalJsTestState(snapshot, Boolean(JS_TEST_PERSISTENCE.endpoint));

    if (!JS_TEST_PERSISTENCE.endpoint) {
        return;
    }

    const payload = {
        mode: JS_TEST_PERSISTENCE.mode,
        state: snapshot,
    };

    const modalScope = isModalLocalJsStorageScope(JS_TEST_PERSISTENCE.storageKey, JS_TEST_PERSISTENCE.mode);
    const conditionalsScope = typeof isConditionalsLocalJsStorageScope === 'function'
        && isConditionalsLocalJsStorageScope(JS_TEST_PERSISTENCE.storageKey, JS_TEST_PERSISTENCE.mode);
    const reportedSpeechScope = typeof isReportedSpeechLocalJsStorageScope === 'function'
        && isReportedSpeechLocalJsStorageScope(JS_TEST_PERSISTENCE.storageKey, JS_TEST_PERSISTENCE.mode);
    const clausesLinkingScope = typeof isClausesLinkingLocalJsStorageScope === 'function'
        && isClausesLinkingLocalJsStorageScope(JS_TEST_PERSISTENCE.storageKey, JS_TEST_PERSISTENCE.mode);
    const prepositionsPhrasalScope = typeof isPrepositionsPhrasalLocalJsStorageScope === 'function'
        && isPrepositionsPhrasalLocalJsStorageScope(JS_TEST_PERSISTENCE.storageKey, JS_TEST_PERSISTENCE.mode);
    const commonMistakesScope = typeof isCommonMistakesLocalJsStorageScope === 'function'
        && isCommonMistakesLocalJsStorageScope(JS_TEST_PERSISTENCE.storageKey, JS_TEST_PERSISTENCE.mode);
    const sentenceTransformationsScope = typeof isSentenceTransformationsLocalJsStorageScope === 'function'
        && isSentenceTransformationsLocalJsStorageScope(JS_TEST_PERSISTENCE.storageKey, JS_TEST_PERSISTENCE.mode);
    const verbPatternsScope = typeof isVerbPatternsLocalJsStorageScope === 'function'
        && isVerbPatternsLocalJsStorageScope(JS_TEST_PERSISTENCE.storageKey, JS_TEST_PERSISTENCE.mode);
    const mixedRevisionScope = typeof isMixedRevisionLocalJsStorageScope === 'function'
        && isMixedRevisionLocalJsStorageScope(JS_TEST_PERSISTENCE.storageKey, JS_TEST_PERSISTENCE.mode);
    const academicEnglishScope = typeof isAcademicEnglishLocalJsStorageScope === 'function'
        && isAcademicEnglishLocalJsStorageScope(JS_TEST_PERSISTENCE.storageKey, JS_TEST_PERSISTENCE.mode);
    const articlesQuantifiersScope = typeof isArticlesQuantifiersLocalJsStorageScope === 'function'
        && isArticlesQuantifiersLocalJsStorageScope(JS_TEST_PERSISTENCE.storageKey, JS_TEST_PERSISTENCE.mode);
    const formalEnglishScope = typeof isFormalEnglishLocalJsStorageScope === 'function'
        && isFormalEnglishLocalJsStorageScope(JS_TEST_PERSISTENCE.storageKey, JS_TEST_PERSISTENCE.mode);
    const relativeClausesScope = typeof isRelativeClausesLocalJsStorageScope === 'function'
        && isRelativeClausesLocalJsStorageScope(JS_TEST_PERSISTENCE.storageKey, JS_TEST_PERSISTENCE.mode);
    const sentenceStructureScope = typeof isSentenceStructureLocalJsStorageScope === 'function'
        && isSentenceStructureLocalJsStorageScope(JS_TEST_PERSISTENCE.storageKey, JS_TEST_PERSISTENCE.mode);
    const vocabCollocationsScope = typeof isVocabCollocationsLocalJsStorageScope === 'function'
        && isVocabCollocationsLocalJsStorageScope(JS_TEST_PERSISTENCE.storageKey, JS_TEST_PERSISTENCE.mode);
    const send = () => {
        let sentModalSnapshot = null;
        JS_TEST_SAVE_QUEUE = JS_TEST_SAVE_QUEUE
            .catch(() => {})
            .then(() => {
                const body = JSON.stringify(payload);
                if (modalScope || conditionalsScope || reportedSpeechScope || clausesLinkingScope || prepositionsPhrasalScope || commonMistakesScope || sentenceTransformationsScope || verbPatternsScope || mixedRevisionScope || academicEnglishScope || articlesQuantifiersScope || formalEnglishScope || relativeClausesScope || sentenceStructureScope || vocabCollocationsScope) sentModalSnapshot = JSON.stringify(snapshot);
                return fetch(JS_TEST_PERSISTENCE.endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': JS_TEST_PERSISTENCE.token,
                },
                credentials: 'same-origin',
                body,
                });
            })
            .then(response => {
                if (modalScope && response?.ok && !response.redirected) JS_TEST_CONFIRMED_MODAL_SNAPSHOT = sentModalSnapshot;
                if (conditionalsScope && response?.ok && !response.redirected) JS_TEST_CONFIRMED_CONDITIONALS_SNAPSHOT = sentModalSnapshot;
                if (reportedSpeechScope && response?.ok && !response.redirected) JS_TEST_CONFIRMED_REPORTED_SPEECH_SNAPSHOT = sentModalSnapshot;
                if (clausesLinkingScope && response?.ok && !response.redirected) JS_TEST_CONFIRMED_CLAUSES_LINKING_SNAPSHOT = sentModalSnapshot;
                if (prepositionsPhrasalScope && response?.ok && !response.redirected) JS_TEST_CONFIRMED_PREPOSITIONS_PHRASAL_SNAPSHOT = sentModalSnapshot;
                if (commonMistakesScope && response?.ok && !response.redirected) JS_TEST_CONFIRMED_COMMON_MISTAKES_SNAPSHOT = sentModalSnapshot;
                if (sentenceTransformationsScope && response?.ok && !response.redirected) JS_TEST_CONFIRMED_SENTENCE_TRANSFORMATIONS_SNAPSHOT = sentModalSnapshot;
                if (verbPatternsScope && response?.ok && !response.redirected) JS_TEST_CONFIRMED_VERB_PATTERNS_SNAPSHOT = sentModalSnapshot;
                if (mixedRevisionScope && response?.ok && !response.redirected) JS_TEST_CONFIRMED_MIXED_REVISION_SNAPSHOT = sentModalSnapshot;
                if (academicEnglishScope && response?.ok && !response.redirected) JS_TEST_CONFIRMED_ACADEMIC_ENGLISH_SNAPSHOT = sentModalSnapshot;
                if (articlesQuantifiersScope && response?.ok && !response.redirected) JS_TEST_CONFIRMED_ARTICLES_QUANTIFIERS_SNAPSHOT = sentModalSnapshot;
                if (formalEnglishScope && response?.ok && !response.redirected) JS_TEST_CONFIRMED_FORMAL_ENGLISH_SNAPSHOT = sentModalSnapshot;
                if (relativeClausesScope && response?.ok && !response.redirected) JS_TEST_CONFIRMED_RELATIVE_CLAUSES_SNAPSHOT = sentModalSnapshot;
                if (sentenceStructureScope && response?.ok && !response.redirected) JS_TEST_CONFIRMED_SENTENCE_STRUCTURE_SNAPSHOT = sentModalSnapshot;
                if (vocabCollocationsScope && response?.ok && !response.redirected) JS_TEST_CONFIRMED_VOCAB_COLLOCATIONS_SNAPSHOT = sentModalSnapshot;
                if (localPersistence?.modalRecoveryPending) {
                    if (response?.ok && !response.redirected) {
                        if (conditionalsScope) warnConditionalsLocalRecovery('server storage');
                        else if (reportedSpeechScope) warnReportedSpeechLocalRecovery('server storage');
                        else if (clausesLinkingScope) warnClausesLinkingLocalRecovery('server storage');
                        else if (prepositionsPhrasalScope) warnPrepositionsPhrasalLocalRecovery('server storage');
                        else if (commonMistakesScope) warnCommonMistakesLocalRecovery('server storage');
                        else if (sentenceTransformationsScope) warnSentenceTransformationsLocalRecovery('server storage');
                        else if (verbPatternsScope) warnVerbPatternsLocalRecovery('server storage');
                        else if (mixedRevisionScope) warnMixedRevisionLocalRecovery('server storage');
                        else if (academicEnglishScope) warnAcademicEnglishLocalRecovery('server storage');
                        else if (articlesQuantifiersScope) warnArticlesQuantifiersLocalRecovery('server storage');
                        else if (formalEnglishScope) warnFormalEnglishLocalRecovery('server storage');
            else if (relativeClausesScope) warnRelativeClausesLocalRecovery('server storage');
            else if (sentenceStructureScope) warnSentenceStructureLocalRecovery('server storage');
            else if (vocabCollocationsScope) warnVocabCollocationsLocalRecovery('server storage');
                        else warnModalLocalRecovery('server storage');
                    } else {
                        localPersistence.failures.forEach(error => console.error(error));
                        console.error(new Error('Modal progress server recovery was not confirmed'));
                    }
                }
                return response;
            })
            .catch(error => {
                if (localPersistence?.modalRecoveryPending) {
                    localPersistence.failures.forEach(failure => console.error(failure));
                    console.error(error);
                }
            });

        return JS_TEST_SAVE_QUEUE;
    };

    if (immediate || (started && !previousStarted)) {
        if (JS_TEST_SAVE_TIMER) {
            clearTimeout(JS_TEST_SAVE_TIMER);
        }
        send();
        return;
    }

    if (JS_TEST_SAVE_TIMER) {
        clearTimeout(JS_TEST_SAVE_TIMER);
    }
    JS_TEST_SAVE_TIMER = setTimeout(send, 250);
}

async function loadQuestions(forceFresh = false) {
    const current = Array.isArray(window.__INITIAL_JS_TEST_QUESTIONS__)
        ? window.__INITIAL_JS_TEST_QUESTIONS__.map(EnglishAnswerVariants.prepareQuestion)
        : [];

    if (!forceFresh) {
        return current;
    }

    if (!JS_TEST_PERSISTENCE || !JS_TEST_PERSISTENCE.questionsEndpoint) {
        if (JS_TEST_PERSISTENCE) {
            JS_TEST_PERSISTENCE.saved = null;
        }
        clearLocalJsTestState();

        window.__INITIAL_JS_TEST_QUESTIONS__ = current;

        return current;
    }

    try {
        const url = new URL(JS_TEST_PERSISTENCE.questionsEndpoint, window.location.origin);
        if (JS_TEST_PERSISTENCE.mode) {
            url.searchParams.set('mode', JS_TEST_PERSISTENCE.mode);
        }

        const response = await fetch(url.toString(), {
            headers: {
                'Accept': 'application/json',
            },
            credentials: 'same-origin',
        });

        if (!response.ok) {
            throw new Error('Failed to load fresh questions');
        }

        const payload = await response.json();
        if (payload && Array.isArray(payload.questions)) {
            JS_TEST_PERSISTENCE.saved = null;
            clearLocalJsTestState();
            window.__INITIAL_JS_TEST_QUESTIONS__ = payload.questions.map(EnglishAnswerVariants.prepareQuestion);

            return window.__INITIAL_JS_TEST_QUESTIONS__;
        }
    } catch (error) {
        console.error(error);
    }

    if (JS_TEST_PERSISTENCE) {
        JS_TEST_PERSISTENCE.saved = null;
        clearLocalJsTestState();
    }

    return current;
}

async function resetJsTestState() {
    if (JS_TEST_SAVE_TIMER) {
        clearTimeout(JS_TEST_SAVE_TIMER);
        JS_TEST_SAVE_TIMER = null;
    }

    // Finish already queued writes before clearing the server snapshot. Otherwise
    // a slow autosave could restore the previous attempt after the reset request.
    await JS_TEST_SAVE_QUEUE.catch(() => {});

    if (JS_TEST_PERSISTENCE?.endpoint) {
        const response = await fetch(JS_TEST_PERSISTENCE.endpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': JS_TEST_PERSISTENCE.token,
            },
            credentials: 'same-origin',
            body: JSON.stringify({
                mode: JS_TEST_PERSISTENCE.mode,
                state: null,
            }),
        });

        if (!response.ok || response.redirected) {
            throw new Error('Failed to reset test progress');
        }
    }

    // Keep the local recovery copy until the server confirms the reset.
    if (JS_TEST_PERSISTENCE) {
        JS_TEST_PERSISTENCE.saved = null;
    }
    JS_TEST_LAST_SNAPSHOT = null;
    clearLocalJsTestState();
}

function flushJsTestStateBeforeUnload() {
    if (JS_TEST_RESTARTING || !JS_TEST_PERSISTENCE || !JS_TEST_LAST_SNAPSHOT || !isStartedState(JS_TEST_LAST_SNAPSHOT)) {
        return;
    }

    writeLocalJsTestState(JS_TEST_LAST_SNAPSHOT);
}

window.addEventListener('pagehide', flushJsTestStateBeforeUnload);

// Keep the first argument for existing mode-specific callers; rebuilding in
// place is deliberately replaced with a full document reload.
async function restartJsTest(_initFn, options = {}) {
    if (JS_TEST_RESTARTING) {
        return;
    }

    JS_TEST_RESTARTING = true;
    const { showLoaderFn, button } = options;
    const toggleLoader = (value) => {
        if (typeof showLoaderFn === 'function') {
            try {
                showLoaderFn(Boolean(value));
            } catch (error) {
                console.error(error);
            }
        }
    };

    if (button) {
        button.disabled = true;
        button.classList.add('opacity-50');
    }

    try {
        toggleLoader(true);
        await resetJsTestState();
        // Reload the same mode/query with a clean server session and browser
        // snapshot, including when the controls have moved into the site header.
        window.history.scrollRestoration = 'manual';
        window.scrollTo({ top: 0, left: 0, behavior: 'instant' });
        window.location.reload();
    } catch (error) {
        JS_TEST_RESTARTING = false;
        console.error(error);
        toggleLoader(false);
        if (button) {
            button.disabled = false;
            button.classList.remove('opacity-50');
        }
        window.alert(testUi('status.restart_failed'));
    }
}
</script>
