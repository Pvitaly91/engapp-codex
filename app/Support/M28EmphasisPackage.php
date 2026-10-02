<?php

namespace App\Support;

use RuntimeException;

/** Three frozen M12 owners only. Unknown data retains the complete native basic. */
final class M28EmphasisPackage
{
    public const BEFORE = 'database/content-patches/m28-m12-emphasis-inversion-before.json';
    public const BEFORE_SHA = 'b0ab16481e32b1bec409ea46075401153da20199d6842340c66b015a1f1efb4b';
    public const SOURCE = 'database/content-patches/m28-m12-emphasis-inversion.v1.json';
    public const SOURCE_SHA = '9765237a505097b9bd84c8d5896449cd6be514952e825f6c0ea4e101615cc99f';

    public static function load(?string $root = null): array
    {
        $root ??= base_path(); $out = [];
        foreach ([self::BEFORE => self::BEFORE_SHA, self::SOURCE => self::SOURCE_SHA] as $path => $sha) {
            $bytes = file_get_contents($root.'/'.$path);
            if (!hash_equals($sha, hash('sha256', $bytes))) { throw new RuntimeException('M28 immutable source differs: '.$path); }
            $out[] = json_decode($bytes, true, flags: JSON_THROW_ON_ERROR);
        }
        return $out;
    }

    public static function json(array $value): string { return M26DetailPackage::json($value); }

    /** Byte-level package identity also protects punctuation, translations and point ownership. */
    public static function validate(array $before, array $package): void
    {
        [$expectedBefore, $expectedPackage] = self::load();
        if ($before !== $expectedBefore || $package !== $expectedPackage) {
            throw new RuntimeException('M28 fidelity/point ownership differs from the accepted finite projection.');
        }
        foreach ($package['targets'] as $i => $target) {
            $original = $before['targets'][$i]['before']; $after = $target['after'];
            $restored = $after; $restored['page']['blocks'] = $original['page']['blocks'];
            if ($restored !== $original || $after['page']['blocks'][0] !== $original['page']['blocks'][0]) {
                throw new RuntimeException('M28 protected metadata/hero differs.');
            }
            $keys = [];
            foreach ($target['plans'] as $j => $plan) {
                $data = json_decode($after['page']['blocks'][$j + 1]['body'], true, flags: JSON_THROW_ON_ERROR);
                if (isset($keys[$plan['key']]) || $data['m28_v1']['key'] !== $plan['key']
                    || $plan['source_section'] !== $j + 1) { throw new RuntimeException('M28 duplicate/moved section anchor.'); }
                $keys[$plan['key']] = true;
                foreach ($plan['points'] as $k => $p) {
                    if ($p['basic'] === '' || $data['sections'][$k]['description'] !== $p['basic'].($p['detail'] !== '' ? '<br><br>'.$p['detail'] : '')) {
                        throw new RuntimeException('M28 missing basic or author fragment.');
                    }
                }
            }
        }
    }

    /** Returns only server-side, hash-bound fragments. DB metadata cannot select a view. */
    public static function presentation(object $block, array $data): ?array
    {
        if (!isset($data['m28_v1']) || ($block->locale ?? null) !== 'uk') { return null; }
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
                            ['id' => 'block-'.$key.'-detail', 'type' => 'm28-author-html', 'value' => $p['detail']],
                        ]];
                    }
                    return ['data' => $basic, 'points' => $points, 'legacy_section' => $plan['source_section'],
                        'legacy_practice_id' => $data['m28_v1']['legacy_practice_id'] ?? null];
                }
            }
        } catch (\Throwable) { /* Full author text remains in the stored native data. */ }
        return null;
    }
}
