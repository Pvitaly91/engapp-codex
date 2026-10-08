<?php

namespace App\Support;

use Illuminate\Support\Collection;
use RuntimeException;

/** Exact M44 authorship and caller-owned native projection, independent of M41/M42/M43 registries. */
final class M44AuthoredFutureFormsPackage
{
    public const MASTER_PATH = 'docs/content/m44-authored-future-forms.v1.0.0.json';
    public const MASTER_SHA = '541db4dec57ed372d23832e849f84c0554fb40faf73693c5b7ab2d306fa7b888';
    public const MAPPING_PATH = 'docs/content/m44-native-mapping.v1.0.0.json';
    public const MAPPING_SHA = '9e9e3897d1858d5d305182cacd52e9da7761ab87c91c08876fed69fb46c8dcbd';
    public const BASE_SHA = 'd3c5fa811e3e7eb5de2e348a6cd2d0bed3a76ba0';
    public const BEFORE = 'database/content-patches/m44-authored-future-forms-before.json';
    public const BEFORE_SHA = '9ef93832e6d10d743e96965d5d38c49f7f8b2d531e0d2098720ee15106f5d176';
    public const SOURCE = 'database/content-patches/m44-authored-future-forms.v1.0.0.json';
    public const SOURCE_SHA = 'c86656dfe22adc99868e2e4e58a8d045d92691c6a511e43c034128e5a04cfc43';

    private const OWNERS = [
        'Database\\Seeders\\Page_V3\\FutureForms\\FutureFormsWillVsBeGoingToTheorySeeder',
        'Database\\Seeders\\Page_V3\\FutureForms\\FutureFormsPresentContinuousForFutureTheorySeeder',
        'Database\\Seeders\\Page_V3\\FutureForms\\FutureFormsChoosingTheRightFutureFormTheorySeeder',
    ];
    private static array $parsed = [];
    private static array $masters = [];
    private static array $validated = [];

    private static function root(?string $root = null): string { return $root ?? (function_exists('base_path') ? base_path() : dirname(__DIR__, 2)); }
    public static function json(array $value): string { return M26DetailPackage::json($value); }

    public static function hasStoredAuthor(array $data): bool
    {
        if (!isset($data['author_section']) && !isset($data['author_practice'])) { return false; }
        if (isset($data['m44_v1'])) { return true; }
        try {
            [$before] = self::load(); $master = self::authorMaster($before);
            foreach ($master['lessons'] as $lesson) {
                if (isset($data['author_section']['id']) && in_array($data['author_section']['id'], array_column($lesson['sections'], 'id'), true)) { return true; }
                if (isset($data['author_practice']) && is_array($data['author_practice']) && array_column($data['author_practice'], 'id') === array_column($lesson['practice'], 'id')) { return true; }
            }
        } catch (\Throwable) { /* Foreign author payloads keep their own complete fallback. */ }
        return false;
    }

    public static function load(?string $root = null): array
    {
        $root = self::root($root); $out = [];
        foreach ([self::BEFORE => self::BEFORE_SHA, self::SOURCE => self::SOURCE_SHA] as $path => $sha) {
            $bytes = file_get_contents($root.'/'.$path);
            if (!is_string($bytes) || !hash_equals($sha, hash('sha256', $bytes)) || str_contains($bytes, "\r")
                || str_starts_with($bytes, "\xef\xbb\xbf") || !str_ends_with($bytes, "\n") || str_ends_with($bytes, "\n\n")) { throw new RuntimeException('M44 immutable source differs: '.$path); }
            // Hash/format checks remain fresh. Only immutable JSON parsing is memoized.
            $key = $root.'/'.$path.':'.$sha;
            if (!isset(self::$parsed[$key])) { self::$parsed[$key] = json_decode($bytes, true, flags: JSON_THROW_ON_ERROR); }
            $out[] = self::$parsed[$key];
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
                || hash('sha256', $bytes) !== $sha || !is_file($root.'/'.$path) || file_get_contents($root.'/'.$path) !== $bytes) { throw new RuntimeException('M44 exact author/mapping snapshot differs.'); }
        }
        if (($before['base_sha'] ?? null) !== self::BASE_SHA || array_column($before['targets'] ?? [], 'identity') !== self::OWNERS) { throw new RuntimeException('M44 baseline owners/base differ.'); }
        require_once dirname(__DIR__, 2).'/tools/diagnostics/m44-author-projection.php';
        if (!isset(self::$masters[$root])) { self::$masters[$root] = \m44AuthorMaster($root); }
        return self::$masters[$root];
    }

    public static function validate(array $before, array $package, ?string $root = null): void
    {
        $root = self::root($root); [$expectedBefore, $expectedPackage] = self::load($root);
        if ($before !== $expectedBefore || $package !== $expectedPackage) { throw new RuntimeException('M44 finite package differs.'); }
        // Full incoming equality and fresh frozen file checks precede the cache.
        // Each immutable projection is reconstructed once per source root/request.
        if (isset(self::$validated[$root])) { return; }
        $master = self::authorMaster($before, $root); $mapping = \m44NativeMapping($root);
        if ($package['schema'] !== 'gramlyze.authored-future-forms-projection.v1' || $package['version'] !== '1.0.0'
            || $package['base_sha'] !== self::BASE_SHA || $package['master_sha256'] !== self::MASTER_SHA || $package['mapping_sha256'] !== self::MAPPING_SHA
            || array_column($package['targets'], 'identity') !== self::OWNERS) { throw new RuntimeException('M44 projection identity differs.'); }
        foreach ($package['targets'] as $i => $target) {
            $record = $before['targets'][$i]; $old = $record['before']; $after = $target['after']; $lesson = $master['lessons'][$i];
            if ($record['path'] !== $lesson['definition_path'] || $record['definition_sha256'] !== $lesson['baseline_definition_sha256']
                || $target !== \m44Target($old, $lesson, $target['bank'], $i, $mapping['targets'][$i])) { throw new RuntimeException('M44 mechanical author transfer differs.'); }
            $restore = $after; $restore['page']['blocks'] = $old['page']['blocks'];
            $restore['page']['subtitle_html'] = $old['page']['subtitle_html']; $restore['page']['subtitle_text'] = $old['page']['subtitle_text'];
            if ($restore !== $old) { throw new RuntimeException('M44 protected identity/metadata differs.'); }
            foreach ($old['page']['blocks'] as $slot => $original) {
                $config = $after['page']['blocks'][$slot]; $config['type'] = $original['type']; $config['body'] = $original['body'];
                if ($config !== $original || M26DetailPackage::uuid($target['identity'], $original, $slot + 1)
                    !== M26DetailPackage::uuid($target['identity'], $after['page']['blocks'][$slot], $slot + 1)) { throw new RuntimeException('M44 existing UUID/configuration changed.'); }
            }
            $nav = $target['navigation_slot']; $oldNav = json_decode($old['page']['blocks'][$nav]['body'], true, flags: JSON_THROW_ON_ERROR);
            $newNav = json_decode($after['page']['blocks'][$nav]['body'], true, flags: JSON_THROW_ON_ERROR);
            foreach ($newNav['items'] as $j => &$item) { $item['url'] = $oldNav['items'][$j]['url']; } unset($item);
            if ($newNav !== $oldNav) { throw new RuntimeException('M44 navigation changed more than reviewed resolved URLs.'); }
        }
        self::$validated[$root] = true;
    }

    private static function matches(object $block, string $identity, array $source, int $position): bool
    {
        return ($block->locale ?? null) === 'uk' && ($block->seeder ?? null) === $identity
            && ($block->uuid ?? null) === M26DetailPackage::uuid($identity, $source, $position)
            && ($block->type ?? null) === $source['type'] && (int) ($block->sort_order ?? -1) === $position
            && ($block->column ?? null) === ($source['column'] ?? 'left') && ($block->heading ?? null) === ($source['heading'] ?? null)
            && ($block->css_class ?? null) === ($source['css_class'] ?? null) && ($block->level ?? null) === ($source['level'] ?? null);
    }

    public static function presentation(object $block, array $data): ?array
    {
        if (!isset($data['m44_v1']) || ($block->locale ?? null) !== 'uk') { return null; }
        try {
            [$before, $package] = self::load(); self::validate($before, $package);
            foreach ($package['targets'] as $target) {
                if (($block->seeder ?? null) !== $target['identity']) { continue; }
                foreach ($target['plans'] as $plan) {
                    $slot = $plan['slot']; $source = $target['after']['page']['blocks'][$slot];
                    if (($block->uuid ?? null) !== M26DetailPackage::uuid($target['identity'], $source, $slot + 1)) { continue; }
                    if (!self::matches($block, $target['identity'], $source, $slot + 1) || $data !== json_decode($source['body'], true, flags: JSON_THROW_ON_ERROR)) { return null; }
                    $points = [];
                    foreach ($plan['points'] as $point) if ($point['decision'] === 'point_detail') {
                        $points[$point['point_index']] = ['key' => $point['id'], 'title' => $point['title'], 'fragments' => [
                            ['id' => 'block-'.$point['detail']['id'], 'type' => 'm44-author-html', 'value' => $point['detail_html']],
                        ]];
                    }
                    return ['data' => $data, 'points' => $points, 'legacy_section' => null, 'legacy_practice_id' => null,
                        'native_view' => match ($plan['role']) { 'section' => 'engram.theory.blocks-v3.m44-native-section',
                            'practice' => 'engram.theory.blocks-v3.m44-practice-ui', default => null }];
                }
            }
        } catch (\Throwable) { /* Complete static content, never fallback disclosure authority. */ }
        return null;
    }

    private static function validatedPage(Collection $blocks): ?array
    {
        if ($blocks->isEmpty() || $blocks->contains(fn ($b) => ($b->locale ?? null) !== 'uk')) { return null; }
        $owners = $blocks->map(fn ($b) => $b->seeder ?? null)->unique()->values();
        if ($owners->count() !== 1 || !in_array($owners[0], self::OWNERS, true)) { return null; }
        try {
            [$before, $package] = self::load(); self::validate($before, $package);
            foreach ($package['targets'] as $i => $target) {
                if ($owners[0] !== $target['identity']) { continue; }
                $page = $target['after']['page']; $sources = $page['blocks'];
                if ($blocks->count() !== count($sources) + 1 || $blocks->map(fn ($b) => $b->uuid ?? null)->unique()->count() !== $blocks->count()) { return null; }
                $subtitle = ['type' => 'subtitle', 'column' => 'header', 'heading' => null, 'css_class' => null,
                    'level' => $page['subtitle_level'] ?? null, 'uuid_key' => $page['subtitle_uuid_key'] ?? 'subtitle', 'body' => $page['subtitle_html']];
                $expected = [M26DetailPackage::uuid($target['identity'], $subtitle, 0) => ['source' => $subtitle, 'position' => 0]];
                foreach ($sources as $slot => $source) { $expected[M26DetailPackage::uuid($target['identity'], $source, $slot + 1)] = ['source' => $source, 'position' => $slot + 1]; }
                foreach ($blocks as $block) {
                    $entry = $expected[$block->uuid ?? ''] ?? null;
                    if (!$entry || !self::matches($block, $target['identity'], $entry['source'], $entry['position'])) { return null; }
                    if ($entry['position'] === 0) { if (($block->body ?? null) !== $entry['source']['body']) { return null; } }
                    elseif (!is_string($block->body ?? null) || json_decode($block->body, true, flags: JSON_THROW_ON_ERROR) !== json_decode($entry['source']['body'], true, flags: JSON_THROW_ON_ERROR)) { return null; }
                }
                $rank = [M26DetailPackage::uuid($target['identity'], $subtitle, 0) => 0];
                foreach (array_merge([0], $target['section_slots'], [$target['practice_slot'], $target['navigation_slot']]) as $slot) { $rank[M26DetailPackage::uuid($target['identity'], $sources[$slot], $slot + 1)] = count($rank); }
                if (count($rank) !== $blocks->count()) { return null; }
                return ['target' => $target, 'before' => $before['targets'][$i]['before'], 'rank' => $rank];
            }
        } catch (\Throwable) { /* Keep foreign/incomplete/tampered collections intact. */ }
        return null;
    }

    public static function orderBlocks(Collection $blocks): Collection
    {
        $validated = self::validatedPage($blocks);
        return $validated === null ? $blocks : $blocks->sortBy(fn ($b) => $validated['rank'][$b->uuid])->values();
    }

    public static function allowsTheoryContext(Collection $blocks, string $locale): bool
    {
        return $locale === 'uk' && self::validatedPage($blocks) !== null;
    }

    public static function preserveCourseBlocks(Collection $blocks): Collection
    {
        $validated = self::validatedPage($blocks); if ($validated === null) { return $blocks; }
        $before = $validated['before']['page']; $identity = $validated['target']['identity'];
        $subtitle = ['type' => 'subtitle', 'body' => $before['subtitle_html'], 'uuid_key' => $before['subtitle_uuid_key'] ?? 'subtitle'];
        $originals = [M26DetailPackage::uuid($identity, $subtitle, 0) => $subtitle];
        foreach ($before['blocks'] as $slot => $source) { $originals[M26DetailPackage::uuid($identity, $source, $slot + 1)] = $source; }
        return $blocks->filter(fn ($b) => isset($originals[$b->uuid]))->map(function ($block) use ($originals) {
            $copy = clone $block; $source = $originals[$block->uuid]; $copy->type = $source['type']; $copy->body = $source['body']; return $copy;
        })->sortBy('sort_order')->values();
    }
}
