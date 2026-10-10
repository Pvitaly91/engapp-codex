<?php

namespace App\Support;

use InvalidArgumentException;

/**
 * One theory-only content entry after the caller's existing source guards.
 * The registry is code-owned; stored fields never choose an executable view.
 * Practice and non-theory compatibility retain their original renderers.
 */
final class TheoryContentRenderer
{
    private const NATIVE_CONTENT_VIEWS = [
        'engram.theory.blocks-v3.forms-grid',
        'engram.theory.blocks-v3.lesson-rule-cards',
        'engram.theory.blocks-v3.usage-panels',
        'engram.theory.blocks-v3.comparison-table',
        'engram.theory.blocks-v3.mistakes-grid',
        'engram.theory.blocks-v3.summary-list',
        'engram.theory.blocks-v3.tense-forms-table',
    ];

    private const AUTHORED_CONTENT_VIEWS = [
        'engram.theory.blocks-v3.m41-author-section',
        'engram.theory.blocks-v3.m41-existing-design-section',
        'engram.theory.blocks-v3.m43-native-section',
        'engram.theory.blocks-v3.m44-native-section',
        'engram.theory.blocks-v3.m45-section',
    ];

    private const STATIC_FALLBACK_VIEWS = [
        'engram.theory.blocks-v3.m43-static-fallback',
        'engram.theory.blocks-v3.m44-static-fallback',
        'engram.theory.blocks-v3.m45-static-fallback',
    ];

    private const PRACTICE_VIEWS = [
        'engram.theory.blocks-v3.practice-set',
        'engram.theory.blocks-v3.m39-practice-ui',
        'engram.theory.blocks-v3.m40-practice-ui',
        'engram.theory.blocks-v3.m41-practice-ui',
        'engram.theory.blocks-v3.m43-practice-ui',
        'engram.theory.blocks-v3.m44-practice-ui',
        'engram.theory.blocks-v3.m45-practice-ui',
        'engram.theory.blocks-v3.authored-practice-ui',
    ];

    private const AUXILIARY_VIEWS = [
        'engram.theory.blocks-v3.hero',
        'engram.theory.blocks-v3.hero-v2',
        'engram.theory.blocks-v3.navigation-chips',
    ];

    public static function render(string $nativeView, array $bindings): string
    {
        if (($bindings['theoryCanonical'] ?? false) !== true) {
            return view($nativeView, $bindings)->render();
        }

        self::assertKnownView($nativeView);
        if (in_array($nativeView, self::PRACTICE_VIEWS, true)
            || in_array($nativeView, self::AUXILIARY_VIEWS, true)) {
            return view($nativeView, $bindings)->render();
        }

        $node = self::nativeNode($nativeView, $bindings);
        if ($node === null) {
            // This existing helper preserves every rejected author field. Its
            // noninteractive practice fallback is deliberately not refactored.
            return view('theory.partials.authored-fallback', $bindings)->render();
        }

        return view('theory.content', array_replace($bindings, [
            'content' => ['kind' => 'node', 'node' => $node],
        ]))->render();
    }

    /**
     * The same semantic node as the pre-existing thin content wrappers.
     * Null identifies compatibility, passthrough, or the complete author fallback.
     * Caller-provided pointSections retain their original detail ownership.
     */
    public static function nativeNode(string $nativeView, array $bindings): ?array
    {
        if (($bindings['theoryCanonical'] ?? false) !== true) {
            return null;
        }

        self::assertKnownView($nativeView);
        if (in_array($nativeView, self::PRACTICE_VIEWS, true)
            || in_array($nativeView, self::AUXILIARY_VIEWS, true)
            || in_array($nativeView, self::STATIC_FALLBACK_VIEWS, true)) {
            return null;
        }

        $block = $bindings['block'];
        $data = $bindings['data'] ?? json_decode($block->body ?? '[]', true) ?? [];
        if (in_array($nativeView, self::NATIVE_CONTENT_VIEWS, true)) {
            return TheoryLegacyAdapter::section(
                $block,
                $data,
                $bindings['pointSections'] ?? [],
                $bindings['lessonLinks'] ?? [],
                $bindings['embeddedDetail'] ?? false,
                $bindings['m42Design'] ?? null,
            );
        }

        $guarded = $bindings['guarded'] ?? true;
        $compact = $bindings['compact'] ?? null;
        if ($nativeView === 'engram.theory.blocks-v3.m41-existing-design-section') {
            $guarded = isset($data['m41_existing_design']);
        } elseif ($nativeView === 'engram.theory.blocks-v3.m43-native-section') {
            if (!isset($data['m43_native_design'])) {
                return null;
            }
        } elseif ($nativeView === 'engram.theory.blocks-v3.m44-native-section') {
            if (!isset($data['m44_native_design'])) {
                return null;
            }
            // Keep the original two-phase rule: the first detail-validation
            // render is full; the final render may use the accepted compact map.
            $hasAuthoredDetails = count(array_filter(
                $data['author_section']['points'],
                static fn ($point) => isset($point['detail']),
            )) > 0;
            $compact = !$hasAuthoredDetails || isset($bindings['pointSections'])
                ? M44SimplifiedPresentation::section($data['author_section']) : null;
        }

        return TheoryAuthoredAdapter::section(
            $block,
            $data,
            $bindings['pointSections'] ?? null,
            $compact,
            $guarded,
        );
    }

    private static function assertKnownView(string $nativeView): void
    {
        if (!in_array($nativeView, self::NATIVE_CONTENT_VIEWS, true)
            && !in_array($nativeView, self::AUTHORED_CONTENT_VIEWS, true)
            && !in_array($nativeView, self::STATIC_FALLBACK_VIEWS, true)
            && !in_array($nativeView, self::PRACTICE_VIEWS, true)
            && !in_array($nativeView, self::AUXILIARY_VIEWS, true)) {
            throw new InvalidArgumentException('Unknown canonical theory content view: '.$nativeView);
        }
    }
}
