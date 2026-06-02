<?php

namespace App\Services;

use Illuminate\Support\Str;

/**
 * Lightweight regex-based classifier that tags an English question text
 * (with `{aN}` markers for the gap) with every grammar topic it touches.
 * Used by V3 theory-link seeders to produce a "full grammar breakdown"
 * theory hint — not just the topic of the gap, but every grammar feature
 * present in the sentence.
 *
 * Returned slugs are stable identifiers consumed by TheoryFeatureBundleResolver.
 *
 * The classifier is intentionally generous (it will surface "articles_the"
 * for any sentence containing "the") because the goal is breadth — the
 * theory hint shows several topical anchors so the learner gets the full
 * context. False positives on rare patterns are acceptable.
 */
class EnglishSentenceGrammarClassifier
{
    /** Most-used irregular past participles — used to spot passive voice
     *  when the gap is a be-form (e.g. "The package {aN} delivered yesterday"). */
    private const IRREGULAR_PAST_PARTICIPLES = [
        'gone', 'taken', 'given', 'made', 'done', 'written', 'broken',
        'chosen', 'eaten', 'seen', 'known', 'thrown', 'found', 'told',
        'brought', 'caught', 'kept', 'left', 'met', 'sent', 'set',
        'let', 'put', 'cut', 'hit', 'cost', 'read', 'said', 'sold',
        'bought', 'built', 'spent', 'got', 'gotten', 'taught', 'paid',
        'laid', 'led', 'fed', 'fled', 'held', 'hung', 'lost', 'shut',
        'spread', 'burst', 'stuck', 'forgotten', 'frozen', 'driven',
        'risen', 'fallen', 'flown', 'grown', 'drawn', 'shown', 'spoken',
        'stolen', 'beaten', 'forbidden', 'hidden', 'bitten', 'ridden',
        'arisen', 'sworn', 'torn', 'worn', 'borne', 'sown', 'mown',
        'awoken', 'become', 'come', 'run', 'overcome', 'understood',
        'forgiven', 'shaken', 'mistaken', 'undertaken', 'withdrawn',
        'discharged', 'designed', 'considered', 'examined', 'reviewed',
    ];

    /** Common irregular plurals + plural-only nouns that confuse SVA. */
    private const SPECIAL_PLURALS = [
        'proceeds', 'news', 'scissors', 'trousers', 'jeans', 'glasses',
        'pyjamas', 'shorts', 'tights', 'tongs', 'police', 'people',
        'cattle', 'economics', 'politics', 'mathematics', 'physics',
        'media', 'data', 'criteria', 'phenomena', 'analyses', 'crises',
        'children', 'men', 'women', 'feet', 'teeth', 'mice', 'geese',
    ];

    /**
     * @return array<int, string>
     */
    public function classify(string $text): array
    {
        $clean = trim($text);
        if ($clean === '') {
            return [];
        }

        // Replace gap markers with a placeholder for cleaner regex work,
        // but keep the original text too because the position matters
        // (e.g. for passive detection we need "{...} <past participle>").
        $stripped = preg_replace('/\{[^}]+\}/u', '___', $clean) ?? $clean;
        $lower = ' ' . Str::lower($stripped) . ' ';

        $features = [];

        // Verb to Be paradigm — always present (questions in this seeder
        // are about am/is/are/was/were/will be choice).
        $features['verb_to_be_paradigm'] = true;

        // ── Articles ─────────────────────────────────────────────────────
        if (preg_match('/\b(?:a|an)\s+[a-z]/i', $stripped) === 1) {
            $features['articles_a_an'] = true;
        }
        if (preg_match('/\bthe\s+[a-z]/i', $stripped) === 1) {
            $features['articles_the'] = true;
        }

        // ── Possessive ───────────────────────────────────────────────────
        // Saxon genitive: word + 's followed by a noun, excluding contractions.
        if (preg_match("/\\b\\w+'s\\s+\\w+/u", $stripped) === 1
            && preg_match("/\\b(it|that|she|he|here|there|what|who|let|one|let)'s\\b/iu", $stripped) !== 1) {
            $features['possessive_s'] = true;
        }
        if (preg_match('/\b(my|your|his|her|its|our|their|whose)\s+[a-z]/i', $stripped) === 1) {
            $features['possessive_pronouns'] = true;
        }

        // ── Passive voice ────────────────────────────────────────────────
        // Marker followed by a past participle (regular -ed or known irregular).
        $passivePattern = '/\{[^}]+\}\s+(?:'
            . '[a-z]+ed' . '|'
            . implode('|', array_map('preg_quote', self::IRREGULAR_PAST_PARTICIPLES))
            . ')\b/iu';
        if (preg_match($passivePattern, $clean) === 1) {
            $features['passive_voice'] = true;
        }

        // ── Adjective order — multiple modifiers between det and noun ────
        if (preg_match(
            '/\b(?:a|an|the|my|your|his|her|its|our|their|some|several|few|many)\s+[a-z]+\s+[a-z]+\s+[a-z]+/i',
            $stripped
        ) === 1) {
            $features['adjective_order'] = true;
        }

        // ── Comparatives & superlatives ──────────────────────────────────
        if (preg_match(
            '/\b[a-z]+er\s+than\b|\bmore\s+[a-z]+\s+than\b|\b(?:less|fewer)\s+[a-z]+\s+than\b|\bthe\s+(?:most\s+[a-z]+|[a-z]+est)\b/i',
            $stripped
        ) === 1) {
            $features['comparatives_superlatives'] = true;
        }

        // ── Quantifiers ──────────────────────────────────────────────────
        if (preg_match(
            '/\b(?:many|much|some|any|few|little|several|all|both|none|each|every|enough|too|so)\s+[a-z]/i',
            $stripped
        ) === 1) {
            $features['quantifiers'] = true;
        }

        // ── Plural-only / irregular plural nouns ─────────────────────────
        $pluralPattern = '/\b(?:' . implode('|', array_map('preg_quote', self::SPECIAL_PLURALS)) . ')\b/i';
        if (preg_match($pluralPattern, $stripped) === 1) {
            $features['plural_nouns'] = true;
        }

        // ── Conjunctions ─────────────────────────────────────────────────
        if (preg_match(
            '/\b(?:and|but|or|because|although|while|when|since|though|whereas|whether|if|unless|so\s+that|provided\s+that|as\s+long\s+as)\b/i',
            $stripped
        ) === 1) {
            $features['conjunctions'] = true;
        }

        // ── Relative clauses ─────────────────────────────────────────────
        if (preg_match('/\b(?:who|which|whose|whom|that)\s+[a-z]+/i', $stripped) === 1) {
            // exclude "that" used as demonstrative (will overlap a bit; that's fine)
            $features['relative_clauses'] = true;
        }

        // ── Demonstratives ───────────────────────────────────────────────
        if (preg_match('/\b(?:this|that|these|those)\s+[a-z]/i', $stripped) === 1) {
            $features['demonstratives'] = true;
        }

        // ── There is / There are (existential) ───────────────────────────
        if (Str::startsWith(Str::lower(ltrim($stripped)), 'there ')
            || preg_match('/\bthere\s*(?:\{[^}]*\}|is|are|was|were|will\s+be|isn|aren|wasn|weren)\b/i', $clean) === 1) {
            $features['there_is_there_are'] = true;
        }

        // ── Inversion (fronting at sentence start, neither/nor, etc.) ────
        $startLower = Str::lower(ltrim($stripped));
        $inversionStarts = [
            'not only ', 'never ', 'rarely ', 'seldom ', 'hardly ',
            'no sooner ', 'gone ', 'blessed ', 'such ',
            'under no circumstances ', 'at no time ', 'in no way ',
            'nor ', 'only when ', 'only after ', 'only then ', 'little ',
        ];
        foreach ($inversionStarts as $marker) {
            if (Str::startsWith($startLower, $marker)) {
                $features['inversion'] = true;
                break;
            }
        }
        if (! isset($features['inversion'])
            && str_contains($lower, ' neither ')
            && str_contains($lower, ' nor ')) {
            $features['inversion'] = true;
        }

        // ── Prepositions (heuristic) ─────────────────────────────────────
        if (preg_match(
            '/\b(?:from|to|at|in|on|of|by|for|about|with|without|between|under|over|across|along|after|before|behind|beside|near|through|during|since|until|toward|towards|via|throughout|despite|notwithstanding)\b/i',
            $stripped
        ) === 1) {
            $features['prepositions'] = true;
        }

        // ── Negation ─────────────────────────────────────────────────────
        if (preg_match("/\\bnot\\b|\\b(?:isn|aren|wasn|weren|won|don|doesn|didn|haven|hasn|hadn)'t\\b/i", $stripped) === 1) {
            $features['negation'] = true;
        }

        // ── Question form (ends with ?) ──────────────────────────────────
        if (Str::endsWith(rtrim($stripped), '?')) {
            $features['questions'] = true;
        }

        return array_keys($features);
    }
}
