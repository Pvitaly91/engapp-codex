<?php

namespace App\Support;

use App\Models\Page;
use App\Models\PageCategory;
use Illuminate\Contracts\Session\Session;

/** Bound only reproducible Prepositions/Phrasal datasets; learner states and foreign session data are never removed. */
final class PrepositionsPhrasalDerivedQuestionCache
{
    private const PAIRS = ['a1-a2', 'a2-b1', 'b1-b2', 'b2-c1', 'c1-c2'];

    private static function namespaceFor(array $classes): string
    {
        $classes = array_values(array_unique(array_map('strtolower', $classes)));
        sort($classes);
        return 'ppq-'.substr(hash('sha256', PrepositionsPhrasalPracticeQuality::REVISION."\0uk\0".implode("\0", $classes)), 0, 20);
    }

    private static function bindings(): array
    {
        static $bindings;
        if (isset($bindings)) { return $bindings; }
        $slugs = []; $pages = []; $categories = [PrepositionsPhrasalPracticeQuality::CATEGORY_ID=>[]];
        foreach (PrepositionsPhrasalPracticeQuality::PAGES as $topic=>[$id, $categoryId, $slug]) {
            $map = PrepositionsPhrasalPracticeQuality::mapping($topic); $classes = [];
            foreach (['standard', 'builder'] as $kind) {
                $class = $map[$kind]['class']; $classes[] = $class;
                $savedSlug = PrepositionsPhrasalPracticeQuality::originalDefinition($topic, $kind)['saved_test']['slug'];
                $slugs[$savedSlug] = self::namespaceFor([$class]);
            }
            $namespace = self::namespaceFor($classes);
            $page = (new Page)->forceFill(['id'=>$id, 'slug'=>$slug]);
            $page->setRelation('category', (new PageCategory)->forceFill(['slug'=>PrepositionsPhrasalPracticeQuality::CATEGORY_SLUG]));
            $publicSlug = TheoryPageTestSlug::forPage($page);
            $slugs[$publicSlug] = $namespace;
            foreach (self::PAIRS as $pair) { $slugs[$publicSlug.'/'.$pair] = $namespace; }
            $pages[$id] = $namespace;
            $categories[$categoryId] = array_merge($categories[$categoryId], $classes);
        }
        return $bindings = ['slugs'=>$slugs, 'pages'=>$pages, 'categories'=>array_map(self::namespaceFor(...), $categories)];
    }

    private static function ownedKey(string $key): ?array
    {
        if (preg_match('/\Asaved_test_js_questions:([^:]+):(ppq-[a-f0-9]{20})(?::[A-Za-z0-9_-]{1,80})?\z/D', $key, $match) !== 1) { return null; }
        $bindings = self::bindings(); $slug = $match[1];
        $expected = $bindings['slugs'][$slug] ?? null;
        if ($expected === null && preg_match('/\Atheory-category-('.PrepositionsPhrasalPracticeQuality::CATEGORY_ID.')-(?:a1|a2|b1|b2|c1|c2)-[a-f0-9]{12}\z/D', $slug, $parts) === 1) {
            $expected = $bindings['categories'][(int) $parts[1]];
        }
        if ($expected === null && preg_match('/\Atheory-page-([1-9][0-9]*)-(?:mixed-a1-c2|a1-a2|a2-b1|b1-b2|b2-c1|c1-c2)-[a-f0-9]{12}\z/D', $slug, $parts) === 1) {
            $expected = $bindings['pages'][(int) $parts[1]] ?? null;
        }
        return $expected !== null && hash_equals($expected, $match[2]) ? [$slug, $expected] : null;
    }

    public static function prune(Session $session, object $test, string $locale, string $currentKey): int
    {
        if ($locale !== 'uk' || !is_array($test->filters ?? null)) { return 0; }
        $namespace = PrepositionsPhrasalPracticeQuality::stateNamespace($test->filters, $locale, (string) ($test->uuid ?? ''));
        if ($namespace === null) { return 0; }
        try { $current = self::ownedKey($currentKey); } catch (\Throwable) { return 0; }
        if ($current === null || $current[0] !== (string) ($test->slug ?? '') || $current[1] !== $namespace) { return 0; }
        $candidates = [];
        foreach ($session->all() as $key=>$value) {
            if (is_string($key) && is_array($value) && self::ownedKey($key) !== null) { $candidates[] = $key; }
        }
        // Reserve the current slot even on a miss: its dataset is restored or rebuilt immediately afterward.
        $keep = [$currentKey];
        foreach (array_reverse($candidates) as $key) {
            if ($key !== $currentKey) { $keep[] = $key; break; }
        }
        $removed = 0;
        foreach ($candidates as $key) {
            if (!in_array($key, $keep, true)) { $session->forget($key); ++$removed; }
        }
        return $removed;
    }
}
