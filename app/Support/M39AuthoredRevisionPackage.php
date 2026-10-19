<?php

namespace App\Support;

use RuntimeException;

/** Three frozen M23 owners only. Unknown data retains the complete native basic. */
final class M39AuthoredRevisionPackage
{
    public const MASTER_PATH = 'docs/content/m23-authored-content.v1.json';
    public const MASTER_SHA = 'eae0e632ae528e3500eeb40188d4b61d275703bfe722cd2fa9fe495f1d2b1ff6';
    public const MASTER_BLOB = '34a03a7146141fbff50c66ec8e41f3fc2a59b787';
    public const NOTES_PATH = 'docs/content/m23-author-sources.md';
    public const NOTES_BLOB = '859c4263cb00c0ff318bf2b41f3e450ca65efc0d';

    public const BEFORE = 'database/content-patches/m39-m23-authored-revision-before.json';
    public const BEFORE_SHA = '76c445345923c1007c09806072a8a7421cdcab835bf492c1573b92c1d7c1a433';
    public const SOURCE = 'database/content-patches/m39-m23-authored-revision.v1.json';
    public const SOURCE_SHA = 'bbfe17773121f6d13cda6beda3d15b5285c54fe73360c605045080e8beddb817';

    public static function load(?string $root = null): array
    {
        $root ??= base_path(); $out = [];
        foreach ([self::BEFORE => self::BEFORE_SHA, self::SOURCE => self::SOURCE_SHA] as $path => $sha) {
            $bytes = file_get_contents($root.'/'.$path);
            if (!hash_equals($sha, hash('sha256', $bytes))) { throw new RuntimeException('M39 immutable source differs: '.$path); }
            $out[] = json_decode($bytes, true, flags: JSON_THROW_ON_ERROR);
        }
        self::authorMaster($out[0], $root);
        return $out;
    }


    /** Exact accepted Git-LF source snapshot, never an independently rewritten author copy.
     * The served ROOT historically has no author documents; do not create/modify them.
     * When an actual source file exists (CLI/tests/worktree), verify it independently.
     */
    public static function authorMaster(array $before, ?string $root = null): array
    {
        $source = $before['author_master_source'] ?? [];
        $bytes = $source['git_lf_bytes'] ?? '';
        if (($source['path'] ?? null) !== self::MASTER_PATH || ($source['git_blob'] ?? null) !== self::MASTER_BLOB
            || ($source['git_lf_sha256'] ?? null) !== self::MASTER_SHA
            || !is_string($bytes) || hash('sha256', $bytes) !== self::MASTER_SHA
            || hash('sha1', 'blob '.strlen($bytes)."\0".$bytes) !== self::MASTER_BLOB) {
            throw new RuntimeException('M39 immutable author-master snapshot differs.');
        }
        $root ??= base_path();
        if (is_file($root.'/'.self::MASTER_PATH)) {
            $actual = str_replace("\r\n", "\n", file_get_contents($root.'/'.self::MASTER_PATH));
            if ($actual !== $bytes) { throw new RuntimeException('M39 actual author master differs.'); }
        }
        $notes = $before['author_notes_source'] ?? [];
        if (($notes['path'] ?? null) !== self::NOTES_PATH || ($notes['git_blob'] ?? null) !== self::NOTES_BLOB) {
            throw new RuntimeException('M39 immutable author-notes identity differs.');
        }
        if (is_file($root.'/'.self::NOTES_PATH)) {
            $actual = str_replace("\r\n", "\n", file_get_contents($root.'/'.self::NOTES_PATH));
            if (hash('sha1', 'blob '.strlen($actual)."\0".$actual) !== self::NOTES_BLOB) {
                throw new RuntimeException('M39 actual author notes differ.');
            }
        }
        $master = json_decode($bytes, true, flags: JSON_THROW_ON_ERROR);
        foreach (['rewrite', 'summarise', 'translate_again', 'add_examples', 'add_exercises', 'production_write', 'mixed_question_bank_write'] as $key) {
            if (($master['content_policy'][$key] ?? null) !== false) { throw new RuntimeException('M39 frozen author policy differs.'); }
        }
        if (count($master['lessons'] ?? []) !== 3) { throw new RuntimeException('M39 master owner scope differs.'); }
        foreach ($master['lessons'] as $i => $lesson) {
            $definition = $before['targets'][$i]['before'] ?? [];
            if (hash('sha256', $lesson['body_html']) !== $lesson['body_sha256']
                || ($definition['seeder']['class'] ?? null) !== $lesson['seeder']
                || ($definition['page']['blocks'][1]['body'] ?? null) !== $lesson['body_html']
                || ($definition['page']['subtitle_html'] ?? null) !== $lesson['subtitle_html']
                || ($definition['page']['subtitle_text'] ?? null) !== $lesson['subtitle_text']
                || json_decode($definition['page']['blocks'][0]['body'] ?? '', true, flags: JSON_THROW_ON_ERROR) !== $lesson['hero']) {
                throw new RuntimeException('M39 master/accepted-definition learner fidelity differs.');
            }
        }
        return $master;
    }

    public static function json(array $value): string { return M26DetailPackage::json($value); }

    /** Byte-level package identity also protects punctuation, translations and point ownership. */
    public static function validate(array $before, array $package): void
    {
        [$expectedBefore, $expectedPackage] = self::load();
        if ($before !== $expectedBefore || $package !== $expectedPackage) {
            throw new RuntimeException('M39 fidelity/point ownership differs from the accepted finite projection.');
        }
        $detailOwners = [];
        foreach ($package['targets'] as $i => $target) {
            $original = $before['targets'][$i]['before']; $after = $target['after'];
            $restored = $after; $restored['page']['blocks'] = $original['page']['blocks'];
            if ($restored !== $original || $after['page']['blocks'][0] !== $original['page']['blocks'][0]) {
                throw new RuntimeException('M39 protected metadata/hero differs.');
            }
            $keys = [];
            foreach ($target['plans'] as $j => $plan) {
                $data = json_decode($after['page']['blocks'][$j + 1]['body'], true, flags: JSON_THROW_ON_ERROR);
                if (isset($keys[$plan['key']]) || $data['m39_v1']['key'] !== $plan['key']
                    || $plan['source_section'] !== $j + 1) { throw new RuntimeException('M39 duplicate/moved section anchor.'); }
                $keys[$plan['key']] = true;
                foreach ($plan['points'] as $k => $p) {
                    if ($p['basic'] === '' || $data['sections'][$k]['description'] !== $p['basic'].($p['detail'] !== '' ? '<br><br>'.$p['detail'] : '')) {
                        throw new RuntimeException('M39 missing basic or author fragment.');
                    }
                    if ($p['detail'] !== '') { $detailOwners[] = [$i, $plan['key'], $plan['source_section'], $k]; }
                }
            }
        }
        // Explicit editorial decisions, never a runtime word-count heuristic.
        if ($detailOwners !== []) {
            throw new RuntimeException('M39 finite meaningful detail ownership differs.');
        }
    }

    /** Returns only server-side, hash-bound fragments. DB metadata cannot select a view. */
    public static function presentation(object $block, array $data): ?array
    {
        if (!isset($data['m39_v1']) || ($block->locale ?? null) !== 'uk') { return null; }
        try {
            [, $package] = self::load();
            foreach ($package['targets'] as $target) {
                if (($block->seeder ?? null) !== $target['identity']) { continue; }
                foreach ($target['plans'] as $j => $plan) {
                    $source = $target['after']['page']['blocks'][$j + 1]; $position = $j + 2;
                    if (($block->uuid ?? null) !== M26DetailPackage::uuid($target['identity'], $source, $position)) { continue; }
                    if (($block->type ?? null) !== $source['type'] || (int) ($block->sort_order ?? -1) !== $position
                        || $data !== json_decode($source['body'], true, flags: JSON_THROW_ON_ERROR)) { return null; }
                    $basic = $data; $points = [];
                    foreach ($plan['points'] as $k => $p) {
                        if ($p['detail'] === '') { continue; }
                        $basic['sections'][$k]['description'] = $p['basic'];
                        $key = $plan['key'].'-point-'.($k + 1);
                        $points[$k] = ['key' => $key, 'title' => $data['title'], 'fragments' => [
                            ['id' => 'block-'.$key.'-detail', 'type' => 'm39-author-html', 'value' => $p['detail']],
                        ]];
                    }
                    return ['data' => $basic, 'points' => $points, 'legacy_section' => $plan['source_section'],
                        'legacy_practice_id' => $data['m39_v1']['legacy_practice_id'] ?? null];
                }
            }
        } catch (\Throwable) { /* Full author text remains in the stored native data. */ }
        return null;
    }
}
