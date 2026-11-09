<?php

namespace App\Support;

/** Shared mechanics; each passive topic supplies its finite source-bound catalog. */
final class PassivePracticeQuality
{
    private const CATALOGS = [PresentSimplePassivePracticeQuality::class, PastSimplePassivePracticeQuality::class,
        FutureSimplePassivePracticeQuality::class, WordOrderPracticeQuality::class, VerbToBePracticeQuality::class, BasicGrammarPracticeQuality::class,
        NounsArticlesQuantityPracticeQuality::class, PronounsDemonstrativesPracticeQuality::class,
        FutureContinuousComparisonPracticeQuality::class, QuestionsNegationsPracticeQuality::class,
        AdjectivesAdverbsPracticeQuality::class, SomeAnyPracticeQuality::class, PassiveVoicePracticeQuality::class,
        ModalVerbsPracticeQuality::class, ConditionalsPracticeQuality::class, ReportedSpeechPracticeQuality::class,
        ClausesLinkingPracticeQuality::class, PrepositionsPhrasalPracticeQuality::class, CommonMistakesPracticeQuality::class,
        SentenceTransformationsPracticeQuality::class, VerbPatternsPracticeQuality::class, MixedRevisionPracticeQuality::class,
        AcademicEnglishPracticeQuality::class, ArticlesQuantifiersPracticeQuality::class, FormalEnglishPracticeQuality::class, RelativeClausesPracticeQuality::class, SentenceStructurePracticeQuality::class, VocabCollocationsPracticeQuality::class];

    public static function decorateKnown(array $payload): array
    {
        foreach (self::CATALOGS as $catalog) {
            if (is_subclass_of($catalog, FinitePracticeQualityCatalog::class)
                && !$catalog::mayOwnQuestionUuid($payload['uuid'] ?? '')) { continue; }
            $payload = $catalog::decorate($payload);
        }
        return $payload;
    }

    public static function knownCatalogNeedsRefresh(array $payloads): bool
    {
        foreach (self::CATALOGS as $catalog) {
            if (is_subclass_of($catalog, FinitePracticeQualityCatalog::class)
                && !array_any($payloads, static fn ($payload): bool => !is_array($payload)
                    || $catalog::mayOwnQuestionUuid($payload['uuid'] ?? ''))) { continue; }
            if ($catalog::cachedNeedsRefresh($payloads)) { return true; }
        }
        return false;
    }

    public static function decorate(array $payload, ?array $source): array
    {
        if (!$source || ($payload['uuid'] ?? null) !== ($source['uuid'] ?? null)
            || (string) ($payload['type'] ?? '') !== (string) ($source['type'] ?? '0')
            || !in_array((string) ($payload['type'] ?? ''), ['0', '4'], true)
            || ($payload['question'] ?? '') !== $source['question']) { return $payload; }
        $expected = array_map(static fn ($m) => $m['answer'], $source['markers']);
        $optionKey = static fn ($value) => mb_strtolower(\Illuminate\Support\Str::ascii($value));
        if (array_map($optionKey, array_values($payload['answer_map'] ?? [])) !== array_map($optionKey, array_values($expected))) { return $payload; }
        // Shared MySQL option rows are case-insensitive; restore only this
        // exact question's authored spelling without editing a shared option.
        $payload['answer_map'] = $expected;
        $payload['answers'] = array_values($expected);
        $payload['answer'] = reset($expected);
        $accepted = []; $synonyms = [];
        foreach ($expected as $marker => $answer) {
            $alternatives = $source['answer_synonyms'][$marker] ?? [];
            if ((string) $payload['type'] === '4') {
                $position = array_search($marker, array_keys($expected), true);
                foreach ($source['accepted_target_texts'] ?? [] as $text) {
                    $words = preg_split('/\s+/u', trim($text));
                    if (count($words) === count($expected) && $words[$position] !== $answer) { $alternatives[] = $words[$position]; }
                }
            }
            $alternatives = array_values(array_unique(array_filter($alternatives, fn ($v) => $v !== $answer)));
            $accepted[$marker] = [];
            foreach (array_merge([$answer], $alternatives) as $value) { $accepted[$marker] = array_merge($accepted[$marker], AcceptedAnswerVariants::for($value)); }
            $accepted[$marker] = array_values(array_unique($accepted[$marker]));
            if ($alternatives) { $synonyms[$marker] = $alternatives; }
        }
        $payload['accepted_answers_by_marker'] = $accepted;
        $payload['accepted_answers'] = array_values($accepted);
        // Whole-sentence alternatives are not automatically display synonyms
        // of unrelated words when their natural word order differs.
        $payload['answer_synonyms_by_marker'] = $source['display_answer_synonyms'] ?? $synonyms;
        if ((string) $payload['type'] === '4') { $payload['compose_accepted_sentences'] = $source['accepted_target_texts'] ?? [$source['target_text']]; }
        $payload['reorder_groups'] = $source['reorder_groups'] ?? [];
        if ((string) $payload['type'] === '0') {
            if (($source['quality_revision'] ?? null) === 'future-simple-passive-v1') { $payload['passive_gap_phrase_input'] = true; }
            $full = $source['question'];
            foreach ($expected as $marker => $answer) { $full = str_replace('{'.$marker.'}', $answer, $full); }
            $payload['reorder_accepted_answers'] = [$full];
            foreach ($synonyms as $marker => $variants) {
                foreach ($variants as $variant) { $payload['reorder_accepted_answers'][] = str_replace('{'.$marker.'}', $variant, $source['question']); }
            }
        }
        // Kept as the existing shared passive-client revision field. The
        // per-topic UUID/question/answer bindings above determine its scope.
        $payload['psp_content_revision'] = hash('sha256', json_encode($source, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        if ((string) $payload['type'] === '4') {
            $payload['compose_content_revision'] = $payload['psp_content_revision'];
            $payload['hint'] = $source['verb_hint'];
        }
        return FiniteBuilderWordSlots::decorate($payload, $source);
    }

    public static function cachedNeedsRefresh(array $payloads, array $sources): bool
    {
        foreach ($payloads as $payload) {
            $source = $sources[$payload['uuid'] ?? ''] ?? null;
            if ($source && ($payload['psp_content_revision'] ?? null) !== hash('sha256', json_encode($source, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES))) { return true; }
            if ($source && ($source['builder_word_slots_version'] ?? null) === 1
                && (FiniteBuilderWordSlots::decorate($payload, $source)['finite_word_slots'] ?? null) !== ($payload['finite_word_slots'] ?? null)) { return true; }
            if ($source && ($source['quality_revision'] ?? null) === 'future-simple-passive-v1'
                && (string) ($source['type'] ?? '0') === '0' && ($payload['passive_gap_phrase_input'] ?? null) !== true) { return true; }
        }
        return false;
    }
}
