<?php

namespace App\Support;

/** Shared request/seeder integration for explicitly opted-in finite catalog migrations. */
final class FinitePracticeQualityCatalogs
{
    public const CATALOGS = [WordOrderPracticeQuality::class, VerbToBePracticeQuality::class, BasicGrammarPracticeQuality::class,
        NounsArticlesQuantityPracticeQuality::class, PronounsDemonstrativesPracticeQuality::class,
        FutureContinuousComparisonPracticeQuality::class, QuestionsNegationsPracticeQuality::class,
        AdjectivesAdverbsPracticeQuality::class, SomeAnyPracticeQuality::class, PassiveVoicePracticeQuality::class,
        ModalVerbsPracticeQuality::class, ConditionalsPracticeQuality::class, ReportedSpeechPracticeQuality::class,
        ClausesLinkingPracticeQuality::class, PrepositionsPhrasalPracticeQuality::class, CommonMistakesPracticeQuality::class,
        SentenceTransformationsPracticeQuality::class, VerbPatternsPracticeQuality::class, MixedRevisionPracticeQuality::class,
        AcademicEnglishPracticeQuality::class, ArticlesQuantifiersPracticeQuality::class, FormalEnglishPracticeQuality::class, RelativeClausesPracticeQuality::class, SentenceStructurePracticeQuality::class, VocabCollocationsPracticeQuality::class];

    public static function definitionMetadata(array $definition, string $locale = 'uk'): array
    {
        foreach (self::CATALOGS as $catalog) { $definition = $catalog::definitionMetadata($definition, $locale); }
        return $definition;
    }

    public static function savedTestFilters(array $payload, string $seeder, array $filters, string $locale = 'uk'): array
    {
        foreach (self::CATALOGS as $catalog) { $filters = $catalog::savedTestFilters($payload, $seeder, $filters, $locale); }
        return $filters;
    }

    public static function filtersForLocale(string $testUuid, array $filters, string $locale): array
    {
        foreach (self::CATALOGS as $catalog) { $filters = $catalog::filtersForLocale($testUuid, $filters, $locale); }
        return $filters;
    }

    public static function testContextNeedsRefresh(array $cached, array $filters, string $locale, string $uuid = ''): bool
    {
        foreach (self::CATALOGS as $catalog) {
            if ($catalog::testContextNeedsRefresh($cached, $filters, $locale, $uuid)) { return true; }
        }
        return false;
    }

    public static function stateNamespace(array $filters, string $locale, string $uuid = ''): ?string
    {
        foreach (self::CATALOGS as $catalog) {
            if (($namespace = $catalog::stateNamespace($filters, $locale, $uuid)) !== null) { return $namespace; }
        }
        return null;
    }

    public static function ownsCategoryProfile(int $categoryId, string $categorySlug, mixed $profile): bool
    {
        if (QuestionsNegationsPracticeQuality::ownsCategory($categoryId, $categorySlug)
            && $profile === QuestionsNegationsPracticeQuality::REVISION) { return true; }
        if (PassiveVoicePracticeQuality::ownsCategory($categoryId, $categorySlug)
            && $profile === PassiveVoicePracticeQuality::REVISION) { return true; }
        if ($categoryId === BasicGrammarPracticeQuality::CATEGORY_ID && $categorySlug === BasicGrammarPracticeQuality::CATEGORY_SLUG
            && in_array($profile, [BasicGrammarPracticeQuality::REVISION, VerbToBePracticeQuality::REVISION, WordOrderPracticeQuality::REVISION], true)) { return true; }
        foreach (self::CATALOGS as $catalog) {
            if ($categoryId === $catalog::CATEGORY_ID && $categorySlug === $catalog::CATEGORY_SLUG
                && $profile === $catalog::REVISION) { return true; }
        }
        return false;
    }

    /** A category opts in only when every current page group names an exact owned pair. */
    public static function categoryProfile(int $categoryId, string $categorySlug, array $groups): ?string
    {
        if ($groups === []) { return null; }
        if (QuestionsNegationsPracticeQuality::ownsCategory($categoryId, $categorySlug)) {
            return QuestionsNegationsPracticeQuality::categoryProfile($categoryId, $categorySlug, $groups);
        }
        if (PassiveVoicePracticeQuality::ownsCategory($categoryId, $categorySlug)) {
            return PassiveVoicePracticeQuality::categoryProfile($categoryId, $categorySlug, $groups);
        }
        if (ModalVerbsPracticeQuality::ownsCategory($categoryId, $categorySlug)) {
            return ModalVerbsPracticeQuality::categoryProfile($categoryId, $categorySlug, $groups);
        }
        if (ConditionalsPracticeQuality::ownsCategory($categoryId, $categorySlug)) {
            return ConditionalsPracticeQuality::categoryProfile($categoryId, $categorySlug, $groups);
        }
        if (ReportedSpeechPracticeQuality::ownsCategory($categoryId, $categorySlug)) {
            return ReportedSpeechPracticeQuality::categoryProfile($categoryId, $categorySlug, $groups);
        }
        if (ClausesLinkingPracticeQuality::ownsCategory($categoryId, $categorySlug)) {
            return ClausesLinkingPracticeQuality::categoryProfile($categoryId, $categorySlug, $groups);
        }
        if (PrepositionsPhrasalPracticeQuality::ownsCategory($categoryId, $categorySlug)) {
            return PrepositionsPhrasalPracticeQuality::categoryProfile($categoryId, $categorySlug, $groups);
        }
        if (CommonMistakesPracticeQuality::ownsCategory($categoryId, $categorySlug)) {
            return CommonMistakesPracticeQuality::categoryProfile($categoryId, $categorySlug, $groups);
        }
        if (SentenceTransformationsPracticeQuality::ownsCategory($categoryId, $categorySlug)) {
            return SentenceTransformationsPracticeQuality::categoryProfile($categoryId, $categorySlug, $groups);
        }
        if (VerbPatternsPracticeQuality::ownsCategory($categoryId, $categorySlug)) {
            return VerbPatternsPracticeQuality::categoryProfile($categoryId, $categorySlug, $groups);
        }
        if (MixedRevisionPracticeQuality::ownsCategory($categoryId, $categorySlug)) {
            return MixedRevisionPracticeQuality::categoryProfile($categoryId, $categorySlug, $groups);
        }
        if (AcademicEnglishPracticeQuality::ownsCategory($categoryId, $categorySlug)) {
            return AcademicEnglishPracticeQuality::categoryProfile($categoryId, $categorySlug, $groups);
        }
        if (ArticlesQuantifiersPracticeQuality::ownsCategory($categoryId, $categorySlug)) {
            return ArticlesQuantifiersPracticeQuality::categoryProfile($categoryId, $categorySlug, $groups);
        }
        if (FormalEnglishPracticeQuality::ownsCategory($categoryId, $categorySlug)) {
            return FormalEnglishPracticeQuality::categoryProfile($categoryId, $categorySlug, $groups);
        }
        if (RelativeClausesPracticeQuality::ownsCategory($categoryId, $categorySlug)) {
            return RelativeClausesPracticeQuality::categoryProfile($categoryId, $categorySlug, $groups);
        }
        if (SentenceStructurePracticeQuality::ownsCategory($categoryId, $categorySlug)) {
            return SentenceStructurePracticeQuality::categoryProfile($categoryId, $categorySlug, $groups);
        }
        if (VocabCollocationsPracticeQuality::ownsCategory($categoryId, $categorySlug)) {
            return VocabCollocationsPracticeQuality::categoryProfile($categoryId, $categorySlug, $groups);
        }
        if ($categoryId === BasicGrammarPracticeQuality::CATEGORY_ID && $categorySlug === BasicGrammarPracticeQuality::CATEGORY_SLUG) {
            return BasicGrammarPracticeQuality::compositeCategoryProfile($groups);
        }
        foreach (self::CATALOGS as $catalog) {
            if ($categoryId !== $catalog::CATEGORY_ID || $categorySlug !== $catalog::CATEGORY_SLUG) { continue; }
            $pairs = [];
            foreach ($catalog::CATEGORY_TOPICS as $topic) {
                $map = $catalog::mapping($topic);
                $pair = [strtolower($map['standard']['class']), strtolower($map['builder']['class'])];
                sort($pair); $pairs[] = $pair;
            }
            foreach ($groups as $group) {
                if (!is_array($group) || ($group['base_filters']['theory_page_mixed_type_profile'] ?? '') !== $catalog::REVISION
                    || !is_array($group['seeder_classes'] ?? null)) { continue 2; }
                $classes = $group['seeder_classes'];
                if (array_filter($classes, static fn ($value) => !is_string($value)) !== []) { continue 2; }
                $classes = array_values(array_unique(array_map('strtolower', $classes))); sort($classes);
                if (!in_array($classes, $pairs, true)) { continue 2; }
            }
            return $catalog::REVISION;
        }
        return null;
    }
}
