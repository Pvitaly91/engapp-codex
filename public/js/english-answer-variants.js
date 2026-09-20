/* Shared by saved tests and course exercises. No answer data is rewritten. */
(function (root, factory) {
    if (typeof module === 'object' && module.exports) module.exports = factory(require('../data/english-contractions.json'));
    else root.EnglishAnswerVariants = factory(root.GRAMLYZE_CONTRACTION_RULES);
})(typeof globalThis !== 'undefined' ? globalThis : this, function (rules) {
    'use strict';
    const typography = value => String(value ?? '').replace(/[‘’ʼ`]/g, "'").trim().replace(/\s+/g, ' ');
    const normalize = value => typography(value).replace(/[.!?…]+$/u, '').trim().toLowerCase();
    const escape = value => value.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    const wordPattern = value => new RegExp(`(?<![a-z'])${escape(value)}(?![a-z'])`, 'gi');
    const participle = word => rules.participles.includes(word) || /(?:ed|en)$/.test(word);
    const nextWord = value => typography(value).toLowerCase().replace(/^(?:(?:already|just|never|always|still|really|also)\s+)+/, '').match(/^[a-z]+/)?.[0] || '';
    // Recognize the subject only, never move NOT across the whole question.
    // The predicate boundary also handles noun phrases used by question banks.
    function invertedSubject(tail) {
        const boundary = `(?=\\s+(?:${rules.question_predicates})\\b|\\s*$)`;
        return tail.match(new RegExp(`^\\s+((?:the|a|an|this|that|these|those|my|your|his|her|our|their) (?:[a-z'-]+ ){0,3}?[a-z'-]+)${boundary}`, 'i'))
            || tail.match(/^\s+(I|you|he|she|it|we|they|there|this|that|these|those)\b/i)
            || tail.match(new RegExp(`^\\s+([A-Z][a-z]+(?: [A-Z][a-z]+){0,2})${boundary}`));
    }

    function ambiguousAuxiliary(suffix, tail) {
        const next = nextWord(tail);
        if (!next) return null;
        if (suffix === 'd') {
            if (next === 'better' || participle(next)) return 'had';
            return 'would';
        }
        if (next === 'been' || next === 'got' || next === 'gotten') return 'has';
        // A bare participle can describe a passive/state or a perfect. Do not
        // guess its meaning; a full authored answer or explicit alias resolves it.
        if (participle(next)) return null;
        return 'is';
    }

    function variants(answer, context = {}) {
        const original = typography(answer);
        if (!original) return [];
        if (!/'|\b(?:am|is|are|have|has|had|not|cannot|will|would|shall|could|should|might|must|may|let)\b/i.test(original)) return [original];
        const seen = new Set([original]);
        const queue = [original];
        const pairs = [...Object.entries(rules.negative), ...Object.entries(rules.positive), ["can't", 'can not']];
        const add = value => {
            if (value && !seen.has(value) && seen.size < 128) { seen.add(value); queue.push(value); }
        };
        // Expand only the authored ambiguous contraction, never a contraction
        // generated from a full form (otherwise 'he is' could become 'he has').
        for (const suffix of ['s', 'd']) {
            const subjects = rules[`${suffix}_subjects`].join('|');
            original.replace(new RegExp(`\\b(${subjects})'${suffix}\\b`, 'gi'), (match, subject, offset) => {
                const aux = ambiguousAuxiliary(suffix, original.slice(offset + match.length) + ' ' + (context.after || ''));
                if (aux) add(original.slice(0, offset) + `${subject} ${aux}` + original.slice(offset + match.length));
                return match;
            });
        }
        for (let index = 0; index < queue.length; index++) {
            const value = queue[index];
            for (const [short, full] of pairs) {
                for (const [from, to] of [[short, full], [full, short]]) {
                    value.replace(wordPattern(from), (match, offset) => {
                        const tail = value.slice(offset + match.length);
                        const negative = Object.hasOwn(rules.negative, short);
                        // A positive auxiliary at the end of a short reply is
                        // stressed: 'Yes, I am', never 'Yes, I'm'.
                        if (!negative && from === full && /^\W*$/.test(tail + (context.after || ''))
                            && /\b(?:yes|no)[,\s]/i.test((context.before || '') + value.slice(0, offset))) return match;
                        const inverted = negative && from === short && invertedSubject(tail);
                        if (!inverted) add(value.slice(0, offset) + to + tail);
                        // In questions NOT follows the subject when expanded.
                        if (negative && from === short) {
                            const subject = inverted;
                            if (subject) {
                                const auxiliary = short === "aren't" && subject[1].toLowerCase() === 'i' ? 'am'
                                    : (full === 'cannot' ? 'can' : full.replace(/ not$/, ''));
                                add(value.slice(0, offset) + `${auxiliary} ${subject[1]} not` + tail.slice(subject[0].length));
                            }
                        }
                        return match;
                    });
                }
                if (Object.hasOwn(rules.negative, short)) {
                    const auxiliary = full === 'cannot' ? 'can' : full.replace(/ not$/, '');
                    value.replace(new RegExp(`\\b${auxiliary} ([a-z'-]+(?: [a-z'-]+){0,4}?) not\\b`, 'gi'), (match, subject, offset) => {
                        if (invertedSubject(` ${subject}`)?.[1] !== subject) return match;
                        add(value.slice(0, offset) + `${short} ${subject}` + value.slice(offset + match.length));
                        return match;
                    });
                }
            }
            value.replace(/\bam I not\b/gi, (match, offset) => {
                add(value.slice(0, offset) + "aren't I" + value.slice(offset + match.length));
                return match;
            });
            // Full forms may be contracted without choosing a second meaning.
            for (const [suffix, auxiliaries] of [['s', ['is', 'has']], ['d', ['had', 'would']]]) {
                const subjects = rules[`${suffix}_subjects`].join('|');
                value.replace(new RegExp(`\\b(${subjects}) (${auxiliaries.join('|')})\\b`, 'gi'), (match, subject, aux, offset) => {
                    const tail = value.slice(offset + match.length) + ' ' + (context.after || '');
                    if (/^\W*$/.test(tail) && /\b(?:yes|no)[,\s]/i.test((context.before || '') + value.slice(0, offset))) return match;
                    if (aux.toLowerCase() === 'has' && nextWord(tail) && !participle(nextWord(tail))) return match;
                    if (aux.toLowerCase() === 'had' && nextWord(tail) && nextWord(tail) !== 'better' && !participle(nextWord(tail))) return match;
                    add(value.slice(0, offset) + `${subject}'${suffix}` + value.slice(offset + match.length));
                    return match;
                });
            }
        }
        return [...seen].filter(value => !/\b(?:do|does|did|is|are|was|were|have|has|had|can|could|will|would|shall|should|must|might|need|dare|ought) not (?:i|you|he|she|it|we|they|there)\b|\bcannot (?:i|you|he|she|it|we|they|there)\b/i.test(value));
    }

    function matches(expected, answer, context = {}) {
        const value = normalize(answer);
        return value !== '' && variants(expected, context).some(variant => normalize(variant) === value);
    }

    function contextFor(question, index) {
        const answers = question.answers || [];
        const marker = question.markers?.[index] || `a${index + 1}`;
        const text = String(question.question || '');
        const position = text.indexOf(`{${marker}}`);
        if (position < 0) return { before: answers.slice(0, index).join(' '), after: answers.slice(index + 1).join(' ') };
        const fill = value => value.replace(/\{([^{}]+)\}/g, (match, key) => question.answer_map?.[key] ?? answers[question.markers?.indexOf(key)] ?? match);
        return { before: fill(text.slice(0, position)), after: fill(text.slice(position + marker.length + 2)) };
    }

    function prepareQuestion(question) {
        if (!question || question.contraction_slots_version === 1 || !Array.isArray(question.answers)) return question;
        const q = { ...question };
        const source = question.answers;
        const markers = question.markers || source.map((_, i) => `a${i + 1}`);
        const compose = ['4', 'compose_tokens'].includes(String(question.type));
        const groups = [];
        for (let i = 0; i < source.length;) {
            let size = 1;
            for (let count = Math.min(3, source.length - i); count >= 2; count--) {
                const adjacent = compose || String(q.question).includes(markers.slice(i, i + count).map(m => `{${m}}`).join(' '));
                const text = source.slice(i, i + count).join(' ');
                const after = source.slice(i + count).join(' ');
                const subjectAuxiliary = /^(?:i|you|he|she|it|we|they) (?:[a-z]+n't)\b/i.test(text);
                if (adjacent && (subjectAuxiliary || variants(text, { after }).some(v => typography(v).split(' ').length < typography(text).split(' ').length))) {
                    size = count;
                    break;
                }
                if (compose && count === 2 && Object.hasOwn(rules.negative, normalize(source[i])) && /^(?:i|you|he|she|it|we|they|there)$/i.test(source[i + 1])) size = 2;
            }
            groups.push(Array.from({ length: size }, (_, n) => i + n));
            i += size;
        }
        const newMarkers = groups.map(group => markers[group[0]]);
        const answers = groups.map(group => group.map(i => source[i]).join(' '));
        let text = String(q.question || '');
        const additions = groups.map(() => '');
        groups.forEach((group, index) => {
            if (group.length > 1 && !compose) text = text.replace(group.map(i => `{${markers[i]}}`).join(' '), `{${newMarkers[index]}}`);
            // A negative question must allow 'Do you not', not 'Do not you'.
            if (!compose && Object.hasOwn(rules.negative, normalize(answers[index]))) {
                const placeholder = `{${newMarkers[index]}}`;
                const position = text.indexOf(placeholder);
                const subject = position < 0 ? null : invertedSubject(text.slice(position + placeholder.length));
                if (subject) {
                    additions[index] = ` ${subject[1]}`;
                    answers[index] += additions[index];
                    text = text.slice(0, position + placeholder.length) + text.slice(position + placeholder.length + subject[0].length);
                }
            }
        });
        if (!groups.some(group => group.length > 1) && !additions.some(Boolean)) return question;
        const optionsFor = (index) => question.options_by_marker?.[markers[index]] ?? question.options_by_marker?.[index] ?? question.optionsBySlot?.[index] ?? question.options ?? [];
        const options = groups.map((group, index) => [...new Set([
            answers[index],
            ...optionsFor(group[0]).map(option => [option, ...group.slice(1).map(i => source[i])].join(' ') + additions[index]),
        ])]);
        const joinState = (key, group) => group.map(i => Array.isArray(question[key]?.[i]) ? question[key][i].join(' ') : question[key]?.[i] ?? '').join(' ').trim();
        if (groups.some(group => group.length > 1) || additions.some(Boolean)) {
            for (const key of ['chosen', 'inputs']) {
                if (Array.isArray(question[key])) q[key] = groups.map((group, index) => {
                    const value = group.every(i => question[key][i] != null && question[key][i] !== '') ? joinState(key, group) + additions[index] : (key === 'chosen' ? null : joinState(key, group));
                    return key === 'inputs' && Array.isArray(question.inputs[group[0]]) ? String(value || '').split(' ') : value;
                });
            }
            if (Array.isArray(question.manualInputsBySlot)) {
                q.manualInputsBySlot = groups.map((group, index) => {
                    if (q.chosen?.[index] != null) return q.chosen[index];
                    return group.map(i => question.chosen?.[i] ?? question.manualInputsBySlot[i] ?? '').filter(Boolean).join(' ');
                });
                q.manualWordIndexBySlot = groups.map((group, index) => {
                    if (q.chosen?.[index] != null) return 0;
                    const confirmed = group.reduce((sum, i) => sum + (question.chosen?.[i] != null ? typography(question.chosen[i]).split(' ').length : Number(question.manualWordIndexBySlot?.[i] || 0)), 0);
                    return confirmed;
                });
            }
            for (const key of ['attemptsBySlot', 'lastWrongBySlot', 'wordSuggestionsBySlot', 'wordSearchRequestBySlot']) {
                if (Array.isArray(question[key])) q[key] = groups.map(group => question[key][group[0]]);
            }
            if (Number.isInteger(question.activeSlot)) q.activeSlot = Math.max(0, groups.findIndex(group => group.includes(question.activeSlot)));
            if (Number.isInteger(question.slot)) q.slot = Math.max(0, groups.findIndex(group => group.includes(question.slot)));
        }
        q.question = text;
        q.answers = answers;
        q.answer = answers[0] || '';
        q.markers = newMarkers;
        q.markers_count = answers.length;
        q.answer_map = Object.fromEntries(newMarkers.map((m, i) => [m, answers[i]]));
        q.options_by_marker = options;
        if (question.optionsBySlot) q.optionsBySlot = options;
        const accepted = groups.map((group, index) => {
            let values = [''];
            for (const i of group) {
                const aliases = question.accepted_answers_by_marker?.[markers[i]] ?? question.accepted_answers?.[i] ?? [];
                values = values.flatMap(prefix => [...new Set([source[i], ...aliases])].map(value => `${prefix} ${value}`.trim())).slice(0, 128);
            }
            return values.map(value => {
                const full = value + additions[index];
                if (!additions[index]) return full;
                const subject = additions[index].trim();
                const expanded = full.replace(new RegExp(`\\bcannot ${escape(subject)}$`, 'i'), `can ${subject} not`)
                    .replace(new RegExp(`\\b(\\w+) not ${escape(subject)}$`, 'i'), `$1 ${subject} not`);
                return normalize(answers[index]) === "aren't i" ? expanded.replace(/^are I not$/i, 'am I not') : expanded;
            });
        });
        q.accepted_answers = accepted;
        q.accepted_answers_by_marker = Object.fromEntries(newMarkers.map((m, i) => [m, accepted[i]]));
        q.verb_hints = Object.fromEntries(groups.map((group, i) => [newMarkers[i], [...new Set(group.map(j => question.verb_hints?.[markers[j]]).filter(Boolean))].join(' ')]));
        q.contraction_slots_version = 1;
        return q;
    }

    return { typography, normalize, variants, matches, contextFor, prepareQuestion };
});
