<?php

namespace App\Support;

use App\Support\TextBlock\TextBlockUuidGenerator;
use RuntimeException;

/** Finite, exact next-version practice projection; M26 teaching/detail sources remain frozen. */
final class PastPerfectContinuousPracticeQuality
{
    public const SOURCE = 'database/content-patches/ppc-practice-quality.v2.json';
    public const SOURCE_SHA = '99f99e139401a9ef757af3f652eecc144caedb6f7378f3406c8dd6cac53271dc';

    public static function load(?string $root = null): array
    {
        $root ??= base_path();
        $frozen = M26InteractivePractice::load($root);
        $bytes = file_get_contents($root.'/'.self::SOURCE);
        if (!hash_equals(self::SOURCE_SHA, hash('sha256', $bytes))) {
            throw new RuntimeException('Past Perfect Continuous practice quality source differs.');
        }
        $payload = json_decode($bytes, true, flags: JSON_THROW_ON_ERROR);
        if ($payload['schema_version'] !== 2 || $payload['package'] !== 'past-perfect-continuous-practice-quality-v2'
            || $payload['source_package_sha256'] !== M26InteractivePractice::SOURCE_SHA
            || $payload['locales'] !== ['uk', 'en', 'pl'] || count($payload['targets']) !== 4) {
            throw new RuntimeException('Past Perfect Continuous practice quality scope differs.');
        }
        foreach ($payload['targets'] as $i => $target) {
            $old = $frozen['targets'][$i];
            foreach (['identity', 'definition_path', 'slug'] as $field) {
                if ($target[$field] !== $old[$field]) { throw new RuntimeException('Practice quality target differs.'); }
            }
            if ($target['uuid_key'] !== $old['practice']['uuid_key'] || $target['sort_order'] !== 7
                || array_keys($target['body_data']) !== $payload['locales']) {
                throw new RuntimeException('Practice quality identity differs.');
            }
            foreach ($payload['locales'] as $locale) {
                $data = $target['body_data'][$locale];
                foreach (['selects', 'choices', 'inputs'] as $group) {
                    if (count($data[$group]) !== 2) { throw new RuntimeException('Practice quality case count differs.'); }
                    foreach ($data[$group] as $j => $item) {
                        if ($item['answer'] !== $old['practice']['body_data'][$group][$j]['answer']) {
                            throw new RuntimeException('Practice quality answer identity differs.');
                        }
                    }
                }
                foreach ($data['inputs'] as $j => $item) {
                    if ($item['before'] !== $old['practice']['body_data']['inputs'][$j]['before']
                        || trim($item['prompt'] ?? '') === '') {
                        throw new RuntimeException('Practice quality token/prompt contract differs.');
                    }
                }
                foreach ($data['selects'] as $item) {
                    if (!in_array($item['answer'], $data['options'], true)) { throw new RuntimeException('Practice select answer missing.'); }
                }
                if ($data['choice_options'] !== ['a', 'b']) { throw new RuntimeException('Practice choice options differ.'); }
                foreach (['source', 'question_types', 'seeder_classes'] as $field) {
                    if ($data['linked_practice'][$field] !== $old['practice']['body_data']['linked_practice'][$field]) {
                        throw new RuntimeException('Practice linked bank differs.');
                    }
                }
            }
        }
        return $payload;
    }

    /** Build the new version from the same original author baseline, changing only practice. */
    public static function definition(array $target, array $before): array
    {
        $after = M26InteractivePractice::definition($target, $before);
        if (!isset($target['practice_insert'])) { return $after; }
        foreach (self::load()['targets'] as $practice) {
            if ($practice['identity'] !== $target['identity']) { continue; }
            $index = $practice['sort_order'] - 1; $root = $target['source_content_root'];
            if (($after[$root]['blocks'][$index]['uuid_key'] ?? null) !== $practice['uuid_key']) {
                throw new RuntimeException('Practice quality source position differs.');
            }
            $after[$root]['blocks'][$index]['body'] = M26DetailPackage::json($practice['body_data']['uk']);
            return $after;
        }
        throw new RuntimeException('Unknown practice quality definition target.');
    }

    /** Exact known bodies only. Unknown/foreign/mutated content keeps its entire stored fallback. */
    public static function presentation(object $block, array $data, ?string $locale = null): ?array
    {
        $locale ??= app()->getLocale();
        $locale = $locale === 'ua' ? 'uk' : $locale;
        if (!in_array($locale, ['uk', 'en', 'pl'], true) || ($block->type ?? null) !== 'practice-set') { return null; }
        try {
            $frozen = M26InteractivePractice::load();
            foreach (self::load()['targets'] as $i => $target) {
                $sourceLocale = ($block->locale ?? null) === 'ua' ? 'uk' : ($block->locale ?? null);
                if (!in_array($sourceLocale, ['uk', 'en', 'pl'], true)) { continue; }
                $owner = $sourceLocale === 'uk' ? $target['identity']
                    : 'Database\\Seeders\\Page_V3\\Localizations\\'.ucfirst($sourceLocale).'\\'
                        .str_replace('TheorySeeder', 'TheoryLocalizationSeeder', basename(str_replace('\\', '/', $target['identity'])));
                $uuid = TextBlockUuidGenerator::generateWithKey($target['identity'].'::'.$sourceLocale, $target['uuid_key']);
                if (($block->seeder ?? null) !== $owner || ($block->uuid ?? null) !== $uuid
                    || (int) ($block->sort_order ?? -1) !== $target['sort_order']) { continue; }
                $known = $data === $target['body_data'][$sourceLocale]
                    || ($sourceLocale === 'uk' && $data === $frozen['targets'][$i]['practice']['body_data']);
                return $known ? $target['body_data'][$locale] : null;
            }
        } catch (\Throwable) { /* Preserve full old body when exact source verification fails. */ }
        return null;
    }
}
