<?php

namespace App\Support;

/**
 * Resolves accepted lesson data before rendering.
 *
 * Package identity/locale/body guards live in their existing presenters.
 * Neither stored content nor a page URL may choose a Blade view or stylesheet.
 */
final class TheoryContentSource
{
    private const PRESENTERS = [
        [M45FutureComparisonsPackage::class, 'm45StyleContext'],
        [M44AuthoredFutureFormsPackage::class, 'm44StyleContext'],
        [M43AuthoredTenseUsagePackage::class, 'm43StyleContext'],
        [M41AuthoredTenseComparisonsPackage::class, null],
        [M40TensesB1Package::class, null],
        [M39PracticeUiPackage::class, null],
        [M39AuthoredRevisionPackage::class, null],
        [M38ArticlesCollocationsPackage::class, null],
        [M37GrammarStructuresPackage::class, null],
        [M36ModalsSubjunctivePackage::class, null],
        [M35PassiveReportingPackage::class, null],
        [M34ArgumentationCohesionPackage::class, null],
        [M33AcademicEnglishPackage::class, null],
        [M32FormalEnglishPackage::class, null],
        [M31ConditionalsPackage::class, null],
        [M30ParticipleClausesPackage::class, null],
        [M29SentenceStructurePackage::class, null],
        [M28EmphasisPackage::class, null],
        [M27LinkingWordsPackage::class, null],
    ];

    public static function resolve(object $block, array $context = []): array
    {
        $data = json_decode($block->body ?? '', true);
        $view = TheoryPresentation::nativeView($block->type);
        $m45 = is_array($data) && M45FutureComparisonsPackage::hasStoredAuthor($data);
        $m44 = is_array($data) && M44AuthoredFutureFormsPackage::hasStoredAuthor($data);
        $m43 = is_array($data) && M43AuthoredTenseUsagePackage::hasStoredAuthor($data);
        if (!$view && $m45) { $view = 'engram.theory.blocks-v3.m45-static-fallback'; }
        if (!$view && $m44) { $view = 'engram.theory.blocks-v3.m44-static-fallback'; }
        if (!$view && $m43) { $view = 'engram.theory.blocks-v3.m43-static-fallback'; }

        $presentation = null;
        if ($view !== null && is_array($data)) {
            foreach (self::PRESENTERS as [$presenter, $requiredContext]) {
                if ($requiredContext !== null && !($context[$requiredContext] ?? false)) { continue; }
                $presentation = $presenter::presentation($block, $data);
                if ($presentation !== null) { break; }
            }
            $presentation = M42NativeDesignPackage::decorate(
                $block, $data, $presentation, $context['m42StyleContext'] ?? false
            ) ?? $presentation;
        }

        // These names are constants returned by guarded code, never data fields.
        if (($presentation['native_view'] ?? null) !== null) { $view = $presentation['native_view']; }
        if ($presentation === null && $m45) { $view = 'engram.theory.blocks-v3.m45-static-fallback'; }
        if ($presentation === null && $m44) { $view = 'engram.theory.blocks-v3.m44-static-fallback'; }
        if ($presentation === null && $m43) { $view = 'engram.theory.blocks-v3.m43-static-fallback'; }

        return [
            'view' => $view,
            'stored' => $data,
            'data' => $presentation['data'] ?? $data,
            'presentation' => $presentation,
            'design' => $presentation['m42_native_design'] ?? null,
            // Preserve the existing full-content fallback if detail validation fails.
            'detail_fallback_view' => $m44 ? 'engram.theory.blocks-v3.m44-static-fallback'
                : ($m43 ? 'engram.theory.blocks-v3.m43-static-fallback' : $view),
        ];
    }
}
