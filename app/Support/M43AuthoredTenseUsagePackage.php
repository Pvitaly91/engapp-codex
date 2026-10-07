<?php

namespace App\Support;

use Illuminate\Support\Collection;
use RuntimeException;

/** Frozen authored M43 projection: explicit owners, native semantics and point decisions. */
final class M43AuthoredTenseUsagePackage
{
    public const MASTER_PATH = 'docs/content/m43-authored-tense-usage.v1.0.0.json';
    public const MASTER_SHA = 'd9afc130ec223202ea83781147f9966855227d3194ccd7707c9b3c037b62e2f5';
    public const MAPPING_PATH = 'docs/content/m43-native-mapping.v1.0.0.json';
    public const MAPPING_SHA = 'c66cd63305d39753b98c5aaa4c53a1814ab4a60006a9c923dbba2b1fbb02cd9f';
    public const BASE_SHA = 'f1a552303cbc46713fa4a34e71fd6be01cc25a42';
    public const BEFORE = 'database/content-patches/m43-authored-tense-usage-before.json';
    public const BEFORE_SHA = 'c737d07ebf0818028c5456f8639ed880f7c5de5d3d8960b14116d70f581f3f96';
    public const SOURCE = 'database/content-patches/m43-authored-tense-usage.v1.0.0.json';
    public const SOURCE_SHA = '419ab6384bba5cde3a11b6030e0e5de136a63beda55b4fac80255d800a487f46';

    private static function root(?string $root = null): string
    {
        return $root ?? (function_exists('base_path') ? base_path() : dirname(__DIR__, 2));
    }

    public static function json(array $value): string { return M26DetailPackage::json($value); }

    /** A static fallback is scoped to M43's own marker or exact frozen author IDs, never M41 keys. */
    public static function hasStoredAuthor(array $data): bool
    {
        if (!isset($data['author_section']) && !isset($data['author_practice'])) { return false; }
        if (isset($data['m43_v1'])) { return true; }
        try {
            [$before] = self::load(); $master = self::authorMaster($before);
            foreach ($master['lessons'] as $lesson) {
                if (isset($data['author_section']['id']) && in_array($data['author_section']['id'], array_column($lesson['sections'], 'id'), true)) { return true; }
                if (isset($data['author_practice']) && is_array($data['author_practice'])
                    && array_column($data['author_practice'], 'id') === array_column($lesson['practice'], 'id')) { return true; }
            }
        } catch (\Throwable) { /* Foreign packages keep their original fallback. */ }
        return false;
    }

    public static function load(?string $root = null): array
    {
        $root = self::root($root); $out = [];
        foreach ([self::BEFORE => self::BEFORE_SHA, self::SOURCE => self::SOURCE_SHA] as $path => $sha) {
            $bytes = file_get_contents($root.'/'.$path);
            if (!is_string($bytes) || !hash_equals($sha, hash('sha256', $bytes))) { throw new RuntimeException('M43 immutable package differs: '.$path); }
            $out[] = json_decode($bytes, true, flags: JSON_THROW_ON_ERROR);
        }
        self::authorMaster($out[0], $root);
        return $out;
    }

    public static function authorMaster(array $before, ?string $root = null): array
    {
        $root = self::root($root);
        foreach (['author_master_source' => [self::MASTER_PATH, self::MASTER_SHA], 'native_mapping_source' => [self::MAPPING_PATH, self::MAPPING_SHA]] as $key => [$path, $sha]) {
            $record = $before[$key] ?? []; $bytes = $record['bytes'] ?? null;
            if (($record['path'] ?? null) !== $path || ($record['sha256'] ?? null) !== $sha || !is_string($bytes)
                || hash('sha256', $bytes) !== $sha || !is_file($root.'/'.$path) || file_get_contents($root.'/'.$path) !== $bytes) {
                throw new RuntimeException('M43 exact frozen author/native snapshot differs.');
            }
        }
        if (($before['base_sha'] ?? null) !== self::BASE_SHA || count($before['targets'] ?? []) !== 3) { throw new RuntimeException('M43 baseline scope differs.'); }
        require_once dirname(__DIR__, 2).'/tools/diagnostics/m43-author-projection.php';
        return \m43AuthorMaster($root);
    }

    /** Exact finite reconstruction; does not infer content, aliases, banks or detail quality. */
    public static function validate(array $before, array $package, ?string $root = null): void
    {
        $root = self::root($root);
        [$expectedBefore, $expectedPackage] = self::load($root);
        if ($before !== $expectedBefore || $package !== $expectedPackage) { throw new RuntimeException('M43 finite projection differs.'); }
        $master = self::authorMaster($before, $root); $mapping = \m43NativeMapping($root);
        if ($package['schema'] !== 'gramlyze.authored-tense-usage-projection.v1' || $package['version'] !== '1.0.0'
            || $package['base_sha'] !== self::BASE_SHA || $package['master_sha256'] !== self::MASTER_SHA
            || $package['mapping_sha256'] !== self::MAPPING_SHA || count($package['targets']) !== 3) { throw new RuntimeException('M43 package identity differs.'); }
        foreach ($package['targets'] as $i => $target) {
            $record = $before['targets'][$i]; $old = $record['before']; $after = $target['after']; $lesson = $master['lessons'][$i];
            if ($record['path'] !== $lesson['definition_path'] || $record['source_git_blob'] !== M43_BASE_BLOBS[$i]
                || $target !== \m43Target($old, $lesson, $target['bank'], $i, $mapping['targets'][$i])) { throw new RuntimeException('M43 deterministic author transfer differs.'); }
            $restore = $after; $restore['page']['blocks'] = $old['page']['blocks'];
            $restore['page']['subtitle_html'] = $old['page']['subtitle_html']; $restore['page']['subtitle_text'] = $old['page']['subtitle_text'];
            if ($restore !== $old) { throw new RuntimeException('M43 protected definition metadata differs.'); }
            foreach ($old['page']['blocks'] as $slot => $original) {
                $config = $after['page']['blocks'][$slot]; $config['type'] = $original['type']; $config['body'] = $original['body'];
                if ($config !== $original || M26DetailPackage::uuid($target['identity'], $original, $slot + 1)
                    !== M26DetailPackage::uuid($target['identity'], $after['page']['blocks'][$slot], $slot + 1)) {
                    throw new RuntimeException('M43 changed an existing UUID, column, technical level or configuration.');
                }
            }
            if ($after['page']['blocks'][$target['navigation_slot']] !== $old['page']['blocks'][$target['navigation_slot']]) {
                throw new RuntimeException('M43 changed existing related navigation.');
            }
        }
    }

    private static function matches(object $block, string $identity, array $source, int $position): bool
    {
        return ($block->locale ?? null) === 'uk' && ($block->seeder ?? null) === $identity
            && ($block->uuid ?? null) === M26DetailPackage::uuid($identity, $source, $position)
            && ($block->type ?? null) === $source['type'] && (int) ($block->sort_order ?? -1) === $position
            && ($block->column ?? null) === ($source['column'] ?? 'left')
            && ($block->heading ?? null) === ($source['heading'] ?? null)
            && ($block->css_class ?? null) === ($source['css_class'] ?? null)
            && ($block->level ?? null) === ($source['level'] ?? null);
    }

    /** Only these two code-owned views can be selected after full package/owner validation. */
    public static function presentation(object $block, array $data): ?array
    {
        if (!isset($data['m43_v1']) || ($block->locale ?? null) !== 'uk') { return null; }
        try {
            [$before, $package] = self::load(); self::validate($before, $package);
            foreach ($package['targets'] as $target) {
                if (($block->seeder ?? null) !== $target['identity']) { continue; }
                foreach ($target['plans'] as $plan) {
                    $slot = $plan['slot']; $source = $target['after']['page']['blocks'][$slot];
                    if (($block->uuid ?? null) !== M26DetailPackage::uuid($target['identity'], $source, $slot + 1)) { continue; }
                    if (!self::matches($block, $target['identity'], $source, $slot + 1)
                        || $data !== json_decode($source['body'], true, flags: JSON_THROW_ON_ERROR)) { return null; }
                    $points = [];
                    foreach ($plan['points'] as $point) {
                        if ($point['decision'] !== 'point_detail') { continue; }
                        $points[$point['point_index']] = ['key' => $point['id'], 'title' => $point['title'], 'fragments' => [
                            ['id' => 'block-'.$point['detail']['id'], 'type' => 'm43-author-html', 'value' => $point['detail_html']],
                        ]];
                    }
                    return ['data' => $data, 'points' => $points, 'legacy_section' => null, 'legacy_practice_id' => null,
                        'native_view' => match ($plan['role']) {
                            'section' => 'engram.theory.blocks-v3.m43-native-section',
                            'practice' => 'engram.theory.blocks-v3.m43-practice-ui',
                            default => null,
                        }];
                }
            }
        } catch (\Throwable) { /* Unvalidated data gets a complete static fallback, never disclosure authority. */ }
        return null;
    }

    /** Complete AFTER ownership/configuration/content validation shared by both callers. */
    private static function validatedPage(Collection $blocks): ?array
    {
        if ($blocks->isEmpty() || $blocks->contains(fn ($b) => ($b->locale ?? null) !== 'uk')) { return null; }
        $owners = $blocks->map(fn ($b) => $b->seeder ?? null)->unique()->values();
        if ($owners->count() !== 1 || !in_array($owners[0], [
            'Database\\Seeders\\Page_V3\\Tenses\\TensesPastPerfectVsPastPerfectContinuousTheorySeeder',
            'Database\\Seeders\\Page_V3\\Tenses\\TensesStativeVerbsTheorySeeder',
            'Database\\Seeders\\Page_V3\\Tenses\\TensesUsedToWouldTheorySeeder',
        ], true)) { return null; }
        try {
            [$before, $package] = self::load(); self::validate($before, $package);
            foreach ($package['targets'] as $targetIndex => $target) {
                if ($owners[0] !== $target['identity']) { continue; }
                $page = $target['after']['page']; $sources = $page['blocks'];
                if ($blocks->count() !== count($sources) + 1 || $blocks->map(fn ($b) => $b->uuid ?? null)->unique()->count() !== $blocks->count()) { return null; }
                $subtitle = ['type' => 'subtitle', 'column' => 'header', 'heading' => null, 'css_class' => null,
                    'level' => $page['subtitle_level'] ?? null, 'uuid_key' => $page['subtitle_uuid_key'] ?? 'subtitle', 'body' => $page['subtitle_html']];
                $expected = [M26DetailPackage::uuid($target['identity'], $subtitle, 0) => ['source' => $subtitle, 'position' => 0]];
                foreach ($sources as $slot => $source) {
                    $expected[M26DetailPackage::uuid($target['identity'], $source, $slot + 1)] = ['source' => $source, 'position' => $slot + 1];
                }
                foreach ($blocks as $block) {
                    $entry = $expected[$block->uuid ?? ''] ?? null;
                    if (!$entry || !self::matches($block, $target['identity'], $entry['source'], $entry['position'])) { return null; }
                    if ($entry['position'] === 0) {
                        if (($block->body ?? null) !== $entry['source']['body']) { return null; }
                    } elseif (!is_string($block->body ?? null) || json_decode($block->body, true, flags: JSON_THROW_ON_ERROR)
                        !== json_decode($entry['source']['body'], true, flags: JSON_THROW_ON_ERROR)) { return null; }
                }
                $rank = [M26DetailPackage::uuid($target['identity'], $subtitle, 0) => 0];
                foreach (array_merge([0], $target['section_slots'], [$target['practice_slot'], $target['navigation_slot']]) as $slot) {
                    $rank[M26DetailPackage::uuid($target['identity'], $sources[$slot], $slot + 1)] = count($rank);
                }
                if (count($rank) !== $blocks->count()) { return null; }
                return ['target' => $target, 'before' => $before['targets'][$targetIndex]['before'], 'rank' => $rank];
            }
        } catch (\Throwable) { /* Keep exact existing collection order on incomplete/foreign/tampered pages. */ }
        return null;
    }

    /** Presentation order only. Never changes stored anchors, UUIDs or DB sort_order. */
    public static function orderBlocks(Collection $blocks): Collection
    {
        $validated = self::validatedPage($blocks);
        return $validated === null ? $blocks : $blocks->sortBy(fn ($block) => $validated['rank'][$block->uuid])->values();
    }

    /**
     * Courses are excluded from M43. Clone exact old rows for their existing
     * renderer, never change the models supplied by the controller or the DB.
     */
    public static function preserveCourseBlocks(Collection $blocks): Collection
    {
        $validated = self::validatedPage($blocks);
        if ($validated === null) { return $blocks; }
        $before = $validated['before']['page']; $identity = $validated['target']['identity'];
        $subtitle = ['type' => 'subtitle', 'body' => $before['subtitle_html'], 'uuid_key' => $before['subtitle_uuid_key'] ?? 'subtitle'];
        $originals = [M26DetailPackage::uuid($identity, $subtitle, 0) => $subtitle];
        foreach ($before['blocks'] as $slot => $source) { $originals[M26DetailPackage::uuid($identity, $source, $slot + 1)] = $source; }
        return $blocks->filter(fn ($block) => isset($originals[$block->uuid]))->map(function ($block) use ($originals) {
            $copy = clone $block; $source = $originals[$block->uuid];
            $copy->type = $source['type']; $copy->body = $source['body'];
            return $copy;
        })->sortBy('sort_order')->values();
    }
}
