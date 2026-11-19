<?php

namespace App\Support;

use App\Support\TextBlock\TextBlockUuidGenerator;
use RuntimeException;

/** Finite, immutable author binding. Database metadata never chooses a view. */
final class M26DetailPackage
{
    public const MASTER = 'docs/content/m26-past-perfect-continuous-detail-master.v1.json';
    public const MASTER_SHA = '2aeb5d65b07f05dc540e8d35fb6779a8775ac8f364a1b906d2d93a4cdac891d0';
    public const BEFORE = 'database/content-patches/m26-ppc-before.json';
    public const BEFORE_SHA = 'd4328511e38d9ff10edfb98158e5fd18ebef04aefa448d2a1bacf0c93d9b6aa4';

    public static function digest(array $data): string
    {
        // Ordered JSON: author key/row order is part of the fidelity contract.
        return hash('sha256', self::json($data));
    }

    public static function json(array $data): string
    {
        return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    }

    public static function load(?string $root = null): array
    {
        $root ??= base_path();
        $read = static function (string $path, string $sha) use ($root): array {
            $bytes = file_get_contents($root.'/'.$path);
            if (!hash_equals($sha, hash('sha256', $bytes))) { throw new RuntimeException('M26 immutable source differs: '.$path); }
            return json_decode($bytes, true, flags: JSON_THROW_ON_ERROR);
        };
        return [$read(self::MASTER, self::MASTER_SHA), $read(self::BEFORE, self::BEFORE_SHA)];
    }

    public static function metadata(array $item): array
    {
        return ['version' => 2, 'key' => $item['progressive_v1']['key'], 'mode' => 'rich-basic-plus-detail',
            'initial_state' => 'closed', 'detail_native_data' => $item['native_data'],
            'detail_native_sha256' => self::digest($item['native_data'])];
    }

    public static function definition(array $target, array $before): array
    {
        $after = $before; $root = $target['source_content_root'];
        foreach ($target['blocks'] as $item) {
            if (!isset($item['progressive_v1'])) { continue; }
            $i = $item['source_index'];
            if ($after[$root]['blocks'][$i]['type'] !== $item['type']) { throw new RuntimeException('M26 native type differs.'); }
            $data = json_decode($before[$root]['blocks'][$i]['body'], true, flags: JSON_THROW_ON_ERROR);
            $data['progressive_v2'] = self::metadata($item);
            $after[$root]['blocks'][$i]['body'] = self::json($data);
        }
        if (isset($target['practice_insert'])) {
            $p = $target['practice_insert'];
            $opening = '<details class="practice-disclosure" data-theory-details><summary>Відкрити 6 завдань</summary>';
            if (substr_count($p['body_html'], $opening) !== 1 || !str_ends_with($p['body_html'], '</details>')
                || hash('sha256', $p['body_html']) !== $p['body_sha256']) { throw new RuntimeException('M26 practice wrapper differs.'); }
            // Only the obsolete outer UI wrapper is removed; prompts and keys stay byte-identical.
            $body = substr(str_replace($opening, '', $p['body_html']), 0, -strlen('</details>'));
            $after[$root]['blocks'][] = ['type' => $p['type'], 'column' => $p['column'], 'heading' => $p['heading'],
                'level' => $p['level'], 'body' => $body, 'uuid_key' => $p['uuid_key'],
                'inherit_base_tags' => $p['inherit_base_tags'], 'tags' => $p['tags']];
        }
        return $after;
    }

    public static function uuid(string $seeder, array $block, int $position): string
    {
        return isset($block['uuid']) ? $block['uuid'] : (isset($block['uuid_key'])
            ? TextBlockUuidGenerator::generateWithKey($seeder.'::uk', $block['uuid_key'])
            : TextBlockUuidGenerator::generate($seeder.'::uk', $position));
    }

    /** Invalid/unknown/foreign metadata returns full basic rendering without a disclosure. */
    public static function detailFor(object $block, array $data): ?array
    {
        if (!isset($data['progressive_v2']) || ($block->locale ?? null) !== 'uk') { return null; }
        try {
            [$master, $manifest] = self::load();
            foreach ($master['targets'] as $target) {
                if (($block->seeder ?? null) !== $target['identity']) { continue; }
                $before = $manifest['definitions'][$target['identity']];
                foreach ($target['blocks'] as $item) {
                    if (!isset($item['progressive_v1'])) { continue; }
                    $i = $item['source_index']; $old = $before[$target['source_content_root']]['blocks'][$i];
                    if (($block->uuid ?? null) !== self::uuid($target['identity'], $old, $i + 1)) { continue; }
                    $basic = $data; unset($basic['progressive_v2']);
                    if (($block->type ?? null) !== $item['type'] || (int) ($block->sort_order ?? -1) !== $i + 1
                        || $basic !== json_decode($old['body'], true, flags: JSON_THROW_ON_ERROR)
                        || $data['progressive_v2'] !== self::metadata($item)) { return null; }
                    return $data['progressive_v2'];
                }
            }
        } catch (\Throwable) { /* fail closed, preserving the basic source */ }
        return null;
    }
}
