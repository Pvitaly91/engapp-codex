<?php

namespace App\Support;

use RuntimeException;

/** Finite technical UI revision; the accepted M23 author master is never rewritten. */
final class M39PracticeUiPackage
{
    public const SOURCE = 'database/content-patches/m39-practice-ui.v1.json';
    public const SOURCE_SHA = 'a155e4d53a37ced5bffd7a427305748a68c9eb6f8c8ee33c6b2b0d6e0d589e5f';

    public static function load(?string $root = null): array
    {
        $root ??= base_path();
        $bytes = file_get_contents($root.'/'.self::SOURCE);
        if (!hash_equals(self::SOURCE_SHA, hash('sha256', $bytes))) {
            throw new RuntimeException('M39 practice UI projection identity differs.');
        }
        return json_decode($bytes, true, flags: JSON_THROW_ON_ERROR);
    }

    public static function validate(array $package): void
    {
        if ($package !== self::load()) {
            throw new RuntimeException('M39 practice UI differs from the finite accepted interaction mapping.');
        }
        [$before, $original] = M39AuthoredRevisionPackage::load();
        M39AuthoredRevisionPackage::validate($before, $original);
        if (count($package['targets'] ?? []) !== 3) {
            throw new RuntimeException('M39 practice UI requires exactly three accepted owners.');
        }
        foreach ($package['targets'] as $i => $target) {
            $old = $original['targets'][$i];
            if ($target['identity'] !== $old['identity'] || $target['path'] !== $old['path']
                || $target['before'] !== $old['after']) {
                throw new RuntimeException('M39 practice UI before-source/owner conflict.');
            }
            $restored = $target['after'];
            $oldPractice = null;
            foreach ($target['before']['page']['blocks'] as $j => $block) {
                if ($block['type'] === 'practice-set') {
                    if ($oldPractice !== null) { throw new RuntimeException('Ambiguous practice block.'); }
                    $oldPractice = $j;
                    $newBlock = $restored['page']['blocks'][$j];
                    $same = $newBlock; $same['body'] = $block['body'];
                    if ($same !== $block) { throw new RuntimeException('Protected practice metadata changed.'); }
                    $previous = json_decode($block['body'], true, flags: JSON_THROW_ON_ERROR);
                    $data = json_decode($newBlock['body'], true, flags: JSON_THROW_ON_ERROR);
                    if ($data['author_self_check'] !== $previous['author_self_check']
                        || $data['m39_v1'] !== $previous['m39_v1']
                        || $data['linked_practice'] !== $previous['linked_practice']
                        || $data['title'] !== $previous['title']
                        || array_column($data['cases'], 'source_index') !== range(1, 6)) {
                        throw new RuntimeException('Original author tasks, keys, order or own-bank scope changed.');
                    }
                    foreach ($data['cases'] as $case) {
                        $key = self::plain($data['author_self_check']['answers'][$case['source_index'] - 1]);
                        foreach ($case['controls'] as $control) {
                            if (!in_array($control['kind'], ['select', 'choice', 'multi', 'manual'], true)) {
                                throw new RuntimeException('Unknown interaction.');
                            }
                            foreach ($control['options'] ?? [] as $option) {
                                if (self::plain($option['label']) === $key || preg_match('/<[^>]+>/', $option['label'])) {
                                    throw new RuntimeException('Author key/HTML leaked into an answer candidate.');
                                }
                            }
                            if ($control['kind'] === 'manual') {
                                if (!in_array($control['answer'], $control['accepted'], true)
                                    || implode(' ', $control['tokens']) !== $control['answer']) {
                                    throw new RuntimeException('Manual answer/token fidelity differs.');
                                }
                                foreach ($control['tokens'] as $token) {
                                    if (count(preg_split('/\s+/u', $token)) > 3 || preg_match('/[.!?]\s+\p{L}/u', $token)) {
                                        throw new RuntimeException('Token groups cross a sentence boundary or exceed three words.');
                                    }
                                }
                            }
                        }
                    }
                    $restored['page']['blocks'][$j] = $block;
                }
            }
            if ($oldPractice === null || $restored !== $target['before']) {
                throw new RuntimeException('M39 practice UI changed non-practice content.');
            }
        }
    }

    /** Semantic/structural finite fragment contract, deliberately not a global length threshold. */
    public static function assertOptionContract(string $option, string $intendedAnswer, string $fullKey): void
    {
        if ($option !== $intendedAnswer || self::plain($option) === self::plain($fullKey)) {
            throw new RuntimeException('Selectable candidate contains something other than the intended answer fragment.');
        }
    }

    private static function plain(string $html): string
    {
        return trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
    }

    public static function presentation(object $block, array $data): ?array
    {
        if (!isset($data['m39_practice_ui_v1']) || ($block->locale ?? null) !== 'uk') { return null; }
        try {
            $package = self::load(); self::validate($package);
            foreach ($package['targets'] as $target) {
                if (($block->seeder ?? null) !== $target['identity']) { continue; }
                foreach ($target['after']['page']['blocks'] as $j => $source) {
                    if ($source['type'] !== 'practice-set') { continue; }
                    if (($block->uuid ?? null) === M26DetailPackage::uuid($target['identity'], $source, $j + 1)
                        && ($block->type ?? null) === 'practice-set' && (int) ($block->sort_order ?? -1) === $j + 1
                        && $data === json_decode($source['body'], true, flags: JSON_THROW_ON_ERROR)) {
                        return ['data' => $data, 'points' => [], 'legacy_section' => 7,
                            'legacy_practice_id' => $data['m39_v1']['legacy_practice_id'] ?? null];
                    }
                }
            }
        } catch (\Throwable) { /* The complete stored author prompts/keys remain readable in fallback. */ }
        return null;
    }
}
