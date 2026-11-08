<?php

namespace App\Support;

use RuntimeException;

/** Explicit, finite upgrade of the four approved M26 practice blocks. */
final class M26InteractivePractice
{
    public const SOURCE = 'database/content-patches/m26-ppc-interactive-practice.v1.json';
    public const SOURCE_SHA = '8b105df87c0f81172c3315927a9b5d6a35e2f9dc1475d611d8e5ee4623055466';

    public static function load(?string $root = null): array
    {
        $root ??= base_path();
        [$master] = M26DetailPackage::load($root);
        $bytes = file_get_contents($root.'/'.self::SOURCE);
        if (!hash_equals(self::SOURCE_SHA, hash('sha256', $bytes))) {
            throw new RuntimeException('M26 interactive practice source differs.');
        }
        $payload = json_decode($bytes, true, flags: JSON_THROW_ON_ERROR);
        $targets = array_values(array_filter($master['targets'], fn ($t) => isset($t['practice_insert'])));
        if ($payload['schema_version'] !== 1 || $payload['package'] !== 'm26-ppc-interactive-practice-v1'
            || $payload['locale'] !== 'uk' || $payload['source_master_sha256'] !== M26DetailPackage::MASTER_SHA
            || count($payload['targets']) !== 4) {
            throw new RuntimeException('M26 interactive package scope differs.');
        }
        foreach ($targets as $i => $target) {
            $p = $payload['targets'][$i]; $practice = $p['practice']; $data = $practice['body_data'];
            foreach (['identity', 'definition_path', 'slug'] as $field) {
                if ($p[$field] !== $target[$field]) { throw new RuntimeException('M26 practice target differs.'); }
            }
            if ($practice['type'] !== 'practice-set' || $practice['heading'] !== null
                || $practice['uuid_key'] !== $target['practice_insert']['uuid_key']
                || $practice['column'] !== $target['practice_insert']['column']
                || $practice['level'] !== $target['practice_insert']['level']) {
                throw new RuntimeException('M26 practice identity differs.');
            }
            foreach (['selects', 'choices', 'inputs'] as $group) {
                if (count($data[$group]) !== 2 || count($p['authored_case_mapping'][$group]) !== 2) {
                    throw new RuntimeException('M26 practice must retain six authored cases.');
                }
            }
            foreach ($data['selects'] as $item) {
                if (!in_array($item['answer'], $data['options'], true)) { throw new RuntimeException('M26 select answer missing.'); }
            }
            foreach ($data['choices'] as $item) {
                if (!in_array($item['answer'], ['a', 'b'], true)) { throw new RuntimeException('M26 choice answer differs.'); }
            }
            $expectedSeeder = str_replace('Database\\Seeders\\Page_V3\\Tenses\\PastPerfectContinuous\\PastPerfectContinuous',
                'Database\\Seeders\\V3\\Polyglot\\PolyglotPastPerfectContinuous', $target['identity']);
            $expectedSeeder = str_replace('TheorySeeder', 'AllLevelsLessonSeeder', $expectedSeeder);
            $linked = $data['linked_practice'];
            if ($linked['source'] !== 'theory_links' || $linked['question_types'] !== ['4']
                || $linked['seeder_classes'] !== [$expectedSeeder]) {
                throw new RuntimeException('M26 linked practice must use its exact existing subtopic bank.');
            }
        }
        return $payload;
    }

    public static function definition(array $target, array $before): array
    {
        $after = M26DetailPackage::definition($target, $before);
        if (!isset($target['practice_insert'])) { return $after; }
        foreach (self::load()['targets'] as $p) {
            if ($p['identity'] !== $target['identity']) { continue; }
            $root = $target['source_content_root']; $i = count($after[$root]['blocks']) - 1;
            $old = $after[$root]['blocks'][$i]; $practice = $p['practice'];
            if ($old['uuid_key'] !== $practice['uuid_key']) { throw new RuntimeException('M26 practice replacement is not the final approved block.'); }
            $after[$root]['blocks'][$i] = array_replace($old, [
                'type' => $practice['type'], 'heading' => $practice['heading'],
                'body' => M26DetailPackage::json($practice['body_data']),
            ]);
            return $after;
        }
        throw new RuntimeException('Unknown M26 interactive practice target.');
    }
}
