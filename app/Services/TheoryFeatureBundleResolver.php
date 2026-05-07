<?php

namespace App\Services;

use App\Models\TextBlock;
use RuntimeException;

/**
 * Maps grammar feature slugs (produced by EnglishSentenceGrammarClassifier)
 * to ordered lists of theory text-block UUIDs, by looking up TextBlock
 * records via (seederClass, sortOrder) pairs.
 *
 * The "primary" feature (passed via $primaryFeature) drives the head of the
 * bundle so the test page surfaces the test's own topic first; additional
 * features append at the end so the learner gets a full grammar breakdown
 * of the sentence (articles, possessives, passive voice, plural nouns, etc.).
 *
 * If a feature has no seeded theory page (or any of its target blocks are
 * missing), it is silently skipped — never breaks the seeder run.
 */
class TheoryFeatureBundleResolver
{
    /**
     * Feature slug → ordered list of [seederClass, sortOrder].
     * Each entry pulls one theory block from a specific page.
     *
     * @var array<string, array<int, array{0: string, 1: int}>>
     */
    private const FEATURE_BLOCKS = [
        'verb_to_be_paradigm' => [
            ['Database\\Seeders\\Page_V3\\BasicGrammar\\VerbToBe\\VerbToBePresentTheorySeeder', 2],
            ['Database\\Seeders\\Page_V3\\BasicGrammar\\VerbToBe\\VerbToBePresentTheorySeeder', 3],
            ['Database\\Seeders\\Page_V3\\BasicGrammar\\VerbToBe\\VerbToBePresentTheorySeeder', 4],
            ['Database\\Seeders\\Page_V3\\BasicGrammar\\VerbToBe\\VerbToBePresentTheorySeeder', 1],
            ['Database\\Seeders\\Page_V3\\BasicGrammar\\VerbToBe\\VerbToBePresentTheorySeeder', 5],
            ['Database\\Seeders\\Page_V3\\BasicGrammar\\VerbToBe\\VerbToBePresentTheorySeeder', 6],
        ],
        'verb_to_be_negatives' => [
            // 1 hero, 2 forms-grid (заперечення), 3 usage, 4 comparison-table
            // (ствердження vs заперечення), 5 mistakes, 6 summary.
            ['Database\\Seeders\\Page_V3\\BasicGrammar\\VerbToBe\\VerbToBeNegativesTheorySeeder', 2],
            ['Database\\Seeders\\Page_V3\\BasicGrammar\\VerbToBe\\VerbToBeNegativesTheorySeeder', 4],
            ['Database\\Seeders\\Page_V3\\BasicGrammar\\VerbToBe\\VerbToBeNegativesTheorySeeder', 3],
            ['Database\\Seeders\\Page_V3\\BasicGrammar\\VerbToBe\\VerbToBeNegativesTheorySeeder', 1],
            ['Database\\Seeders\\Page_V3\\BasicGrammar\\VerbToBe\\VerbToBeNegativesTheorySeeder', 5],
            ['Database\\Seeders\\Page_V3\\BasicGrammar\\VerbToBe\\VerbToBeNegativesTheorySeeder', 6],
        ],
        'verb_to_be_past' => [
            // 1 hero, 2 forms-grid (was/were), 3 negatives, 4 questions,
            // 5 short-answers, 6 mistakes, 7 summary.
            ['Database\\Seeders\\Page_V3\\BasicGrammar\\VerbToBe\\VerbToBePastTheorySeeder', 2],
            ['Database\\Seeders\\Page_V3\\BasicGrammar\\VerbToBe\\VerbToBePastTheorySeeder', 3],
            ['Database\\Seeders\\Page_V3\\BasicGrammar\\VerbToBe\\VerbToBePastTheorySeeder', 4],
            ['Database\\Seeders\\Page_V3\\BasicGrammar\\VerbToBe\\VerbToBePastTheorySeeder', 1],
            ['Database\\Seeders\\Page_V3\\BasicGrammar\\VerbToBe\\VerbToBePastTheorySeeder', 5],
            ['Database\\Seeders\\Page_V3\\BasicGrammar\\VerbToBe\\VerbToBePastTheorySeeder', 6],
            ['Database\\Seeders\\Page_V3\\BasicGrammar\\VerbToBe\\VerbToBePastTheorySeeder', 7],
        ],
        'verb_to_be_future' => [
            // Same shape as Past: 1 hero, 2 forms (will be), 3 negatives,
            // 4 questions, 5 short-answers, 6 mistakes, 7 summary.
            ['Database\\Seeders\\Page_V3\\BasicGrammar\\VerbToBe\\VerbToBeFutureTheorySeeder', 2],
            ['Database\\Seeders\\Page_V3\\BasicGrammar\\VerbToBe\\VerbToBeFutureTheorySeeder', 3],
            ['Database\\Seeders\\Page_V3\\BasicGrammar\\VerbToBe\\VerbToBeFutureTheorySeeder', 4],
            ['Database\\Seeders\\Page_V3\\BasicGrammar\\VerbToBe\\VerbToBeFutureTheorySeeder', 1],
            ['Database\\Seeders\\Page_V3\\BasicGrammar\\VerbToBe\\VerbToBeFutureTheorySeeder', 5],
            ['Database\\Seeders\\Page_V3\\BasicGrammar\\VerbToBe\\VerbToBeFutureTheorySeeder', 6],
            ['Database\\Seeders\\Page_V3\\BasicGrammar\\VerbToBe\\VerbToBeFutureTheorySeeder', 7],
        ],
        'verb_to_be_questions' => [
            // 1 hero, 2 forms (yes/no + WH), 3 short-answers usage,
            // 4 short-answer comparison, 5 mistakes, 6 summary.
            ['Database\\Seeders\\Page_V3\\BasicGrammar\\VerbToBe\\VerbToBeQuestionsTheorySeeder', 2],
            ['Database\\Seeders\\Page_V3\\BasicGrammar\\VerbToBe\\VerbToBeQuestionsTheorySeeder', 3],
            ['Database\\Seeders\\Page_V3\\BasicGrammar\\VerbToBe\\VerbToBeQuestionsTheorySeeder', 4],
            ['Database\\Seeders\\Page_V3\\BasicGrammar\\VerbToBe\\VerbToBeQuestionsTheorySeeder', 1],
            ['Database\\Seeders\\Page_V3\\BasicGrammar\\VerbToBe\\VerbToBeQuestionsTheorySeeder', 5],
            ['Database\\Seeders\\Page_V3\\BasicGrammar\\VerbToBe\\VerbToBeQuestionsTheorySeeder', 6],
        ],
        'articles_a_an' => [
            ['Database\\Seeders\\Page_V3\\NounsArticlesQuantity\\NounsArticlesQuantityArticlesTheorySeeder', 1],
            ['Database\\Seeders\\Page_V3\\NounsArticlesQuantity\\NounsArticlesQuantityArticlesTheorySeeder', 3],
            ['Database\\Seeders\\Page_V3\\NounsArticlesQuantity\\NounsArticlesQuantityArticlesTheorySeeder', 4],
        ],
        'articles_the' => [
            ['Database\\Seeders\\Page_V3\\NounsArticlesQuantity\\NounsArticlesQuantityArticlesTheorySeeder', 1],
            ['Database\\Seeders\\Page_V3\\NounsArticlesQuantity\\NounsArticlesQuantityArticlesTheorySeeder', 5],
        ],
        'possessive_s' => [
            ['Database\\Seeders\\Page_V3\\PronounsDemonstratives\\PronounsDemonstrativesPossessiveAdjectivesVsPronounsTheorySeeder', 1],
            ['Database\\Seeders\\Page_V3\\PronounsDemonstratives\\PronounsDemonstrativesPossessiveAdjectivesVsPronounsTheorySeeder', 2],
        ],
        'possessive_pronouns' => [
            ['Database\\Seeders\\Page_V3\\PronounsDemonstratives\\PronounsDemonstrativesPossessiveAdjectivesVsPronounsTheorySeeder', 1],
            ['Database\\Seeders\\Page_V3\\PronounsDemonstratives\\PronounsDemonstrativesPossessiveAdjectivesVsPronounsTheorySeeder', 3],
        ],
        'passive_voice' => [
            ['Database\\Seeders\\Page_V3\\PassiveVoice\\Tenses\\PassiveVoicePresentSimpleTheorySeeder', 1],
            ['Database\\Seeders\\Page_V3\\PassiveVoice\\Tenses\\PassiveVoicePresentSimpleTheorySeeder', 2],
            ['Database\\Seeders\\Page_V3\\PassiveVoice\\Basics\\PassiveVoiceFormationRulesTheorySeeder', 2],
        ],
        'adjective_order' => [
            ['Database\\Seeders\\Page_V3\\Adjectives\\AdjectivesOrderOfAdjectivesTheorySeeder', 1],
            ['Database\\Seeders\\Page_V3\\Adjectives\\AdjectivesOrderOfAdjectivesTheorySeeder', 2],
        ],
        'comparatives_superlatives' => [
            ['Database\\Seeders\\Page_V3\\Adjectives\\AdjectivesComparativeVsSuperlativeTheorySeeder', 1],
            ['Database\\Seeders\\Page_V3\\Adjectives\\AdjectivesComparativeVsSuperlativeTheorySeeder', 2],
        ],
        'quantifiers' => [
            ['Database\\Seeders\\Page_V3\\NounsArticlesQuantity\\NounsArticlesQuantityQuantifiersTheorySeeder', 1],
            ['Database\\Seeders\\Page_V3\\NounsArticlesQuantity\\NounsArticlesQuantityQuantifiersTheorySeeder', 2],
        ],
        'plural_nouns' => [
            ['Database\\Seeders\\Page_V3\\NounsArticlesQuantity\\NounsArticlesQuantityPluralNounsTheorySeeder', 1],
            ['Database\\Seeders\\Page_V3\\NounsArticlesQuantity\\NounsArticlesQuantityPluralNounsTheorySeeder', 2],
        ],
        'conjunctions' => [
            ['Database\\Seeders\\Page_V3\\BasicGrammar\\BasicGrammarConjunctionsTheorySeeder', 1],
            ['Database\\Seeders\\Page_V3\\BasicGrammar\\BasicGrammarConjunctionsTheorySeeder', 2],
        ],
        'relative_clauses' => [
            ['Database\\Seeders\\Page_V3\\PronounsDemonstratives\\PronounsDemonstrativesRelativePronounsTheorySeeder', 1],
            ['Database\\Seeders\\Page_V3\\PronounsDemonstratives\\PronounsDemonstrativesRelativePronounsTheorySeeder', 2],
        ],
        'demonstratives' => [
            ['Database\\Seeders\\Page_V3\\PronounsDemonstratives\\PronounsDemonstrativesThisThatTheseThoseTheorySeeder', 1],
            ['Database\\Seeders\\Page_V3\\PronounsDemonstratives\\PronounsDemonstrativesThisThatTheseThoseTheorySeeder', 2],
        ],
        'there_is_there_are' => [
            ['Database\\Seeders\\Page_V3\\BasicGrammar\\VerbToBe\\ThereIsThereAreTheorySeeder', 1],
            ['Database\\Seeders\\Page_V3\\BasicGrammar\\VerbToBe\\ThereIsThereAreTheorySeeder', 2],
            ['Database\\Seeders\\Page_V3\\BasicGrammar\\VerbToBe\\ThereIsThereAreTheorySeeder', 4],
        ],
        'inversion' => [
            ['Database\\Seeders\\Page_V3\\BasicGrammar\\WordOrder\\InversionBasicsTheorySeeder', 1],
            ['Database\\Seeders\\Page_V3\\BasicGrammar\\WordOrder\\InversionBasicsTheorySeeder', 2],
        ],
        // No theory page seeded for prepositions / negation / questions yet —
        // they fall through silently. Add entries here when those pages land.
    ];

    /**
     * Order in which features get appended after the primary feature, so
     * the panel's flow reads from "core paradigm" → "syntax" → "lexis".
     *
     * @var array<int, string>
     */
    private const FEATURE_DISPLAY_ORDER = [
        'verb_to_be_paradigm',
        'verb_to_be_negatives',
        'verb_to_be_questions',
        'verb_to_be_past',
        'verb_to_be_future',
        'there_is_there_are',
        'passive_voice',
        'inversion',
        'relative_clauses',
        'comparatives_superlatives',
        'adjective_order',
        'articles_a_an',
        'articles_the',
        'demonstratives',
        'possessive_pronouns',
        'possessive_s',
        'quantifiers',
        'plural_nouns',
        'conjunctions',
    ];

    /** @var array<string, ?\App\Models\TextBlock> */
    private array $cache = [];

    /**
     * Build an ordered, deduplicated list of TextBlock UUIDs covering every
     * feature in $features. The $primaryFeature (if provided) is rendered
     * first; remaining features follow in FEATURE_DISPLAY_ORDER.
     *
     * Missing pages/blocks are skipped silently.
     *
     * @param  array<int, string>  $features
     * @return array<int, string>
     */
    public function resolve(array $features, ?string $primaryFeature = null): array
    {
        $featureSet = array_fill_keys($features, true);
        $ordered = [];

        if ($primaryFeature !== null && isset($featureSet[$primaryFeature])) {
            $ordered[] = $primaryFeature;
            unset($featureSet[$primaryFeature]);
        }

        foreach (self::FEATURE_DISPLAY_ORDER as $slug) {
            if (! isset($featureSet[$slug])) {
                continue;
            }
            $ordered[] = $slug;
            unset($featureSet[$slug]);
        }

        // Any leftover features (not in display order list) get appended.
        foreach (array_keys($featureSet) as $slug) {
            $ordered[] = $slug;
        }

        $seenUuid = [];
        $uuids = [];
        foreach ($ordered as $slug) {
            $specs = self::FEATURE_BLOCKS[$slug] ?? [];
            foreach ($specs as [$seederClass, $sortOrder]) {
                $block = $this->resolveBlock($seederClass, $sortOrder);
                if ($block === null) {
                    continue; // skip silently
                }
                if (isset($seenUuid[$block->uuid])) {
                    continue;
                }
                $seenUuid[$block->uuid] = true;
                $uuids[] = $block->uuid;
            }
        }

        return $uuids;
    }

    /**
     * Inspect available feature slugs at runtime (used for diagnostics).
     *
     * @return array<int, string>
     */
    public function knownFeatures(): array
    {
        return array_keys(self::FEATURE_BLOCKS);
    }

    private function resolveBlock(string $seederClass, int $sortOrder): ?TextBlock
    {
        $key = $seederClass . '#' . $sortOrder;

        if (! array_key_exists($key, $this->cache)) {
            $this->cache[$key] = TextBlock::query()
                ->where('seeder', $seederClass)
                ->where('sort_order', $sortOrder)
                ->first();
        }

        return $this->cache[$key];
    }
}
