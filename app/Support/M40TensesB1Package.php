<?php

namespace App\Support;

use RuntimeException;

/** Three immutable M24 owners. Existing native configuration remains byte-exact. */
final class M40TensesB1Package
{
    public const MASTER_PATH = 'docs/content/m24-authored-content.v1.json';
    public const MASTER_SHA = '33373412ed077bf7fa0aea8dde8a6d2c211c126288b6cedfdde299ded43bf5a2';
    public const MASTER_BLOB = '2d7c450533795bdab3267bd029b54fb325ff7cbb';
    public const NOTES_PATH = 'docs/content/m24-author-sources.md';
    public const NOTES_BLOB = '3330b22b22e401eec9c36275651e60645b4b87f6';
    public const BEFORE = 'database/content-patches/m40-m24-tenses-b1-before.json';
    public const BEFORE_SHA = '67cb3dfe2f499479d7249df55e92c7a53bfc25c1afbf9db79e3992b71c390036';
    public const SOURCE = 'database/content-patches/m40-m24-tenses-b1.v1.json';
    public const SOURCE_SHA = '3b0734505576ff7628cd767c15f61c17aa9df59aa5e7726cddd44d1e1155ec9f';

    public static function load(?string $root = null): array
    {
        $root ??= base_path(); $out = [];
        foreach ([self::BEFORE => self::BEFORE_SHA, self::SOURCE => self::SOURCE_SHA] as $path => $sha) {
            $bytes = file_get_contents($root.'/'.$path);
            if (!hash_equals($sha, hash('sha256', $bytes))) { throw new RuntimeException('M40 immutable source differs: '.$path); }
            $out[] = json_decode($bytes, true, flags: JSON_THROW_ON_ERROR);
        }
        self::authorMaster($out[0], $root);
        return $out;
    }

    /** Accepted Git-LF snapshot; independently verify actual documents when present. */
    public static function authorMaster(array $before, ?string $root = null): array
    {
        $source = $before['author_master_source'] ?? []; $bytes = $source['git_lf_bytes'] ?? '';
        if (($source['path'] ?? null) !== self::MASTER_PATH || ($source['git_blob'] ?? null) !== self::MASTER_BLOB
            || ($source['git_lf_sha256'] ?? null) !== self::MASTER_SHA || !is_string($bytes)
            || hash('sha256', $bytes) !== self::MASTER_SHA
            || hash('sha1', 'blob '.strlen($bytes)."\0".$bytes) !== self::MASTER_BLOB) {
            throw new RuntimeException('M40 immutable author-master snapshot differs.');
        }
        $root ??= base_path();
        if (is_file($root.'/'.self::MASTER_PATH)
            && str_replace("\r\n", "\n", file_get_contents($root.'/'.self::MASTER_PATH)) !== $bytes) {
            throw new RuntimeException('M40 actual author master differs.');
        }
        $notes = $before['author_notes_source'] ?? [];
        if (($notes['path'] ?? null) !== self::NOTES_PATH || ($notes['git_blob'] ?? null) !== self::NOTES_BLOB) {
            throw new RuntimeException('M40 immutable author-notes identity differs.');
        }
        if (is_file($root.'/'.self::NOTES_PATH)) {
            $actual = str_replace("\r\n", "\n", file_get_contents($root.'/'.self::NOTES_PATH));
            if (hash('sha1', 'blob '.strlen($actual)."\0".$actual) !== self::NOTES_BLOB) {
                throw new RuntimeException('M40 actual author notes differ.');
            }
        }
        $master = json_decode($bytes, true, flags: JSON_THROW_ON_ERROR);
        foreach (['rewrite', 'summarise', 'translate_again', 'add_examples', 'add_exercises', 'production_write', 'mixed_question_bank_write'] as $key) {
            if (($master['content_policy'][$key] ?? null) !== false) { throw new RuntimeException('M40 frozen author policy differs.'); }
        }
        if (count($master['lessons'] ?? []) !== 3 || count($before['targets'] ?? []) !== 3) {
            throw new RuntimeException('M40 master owner scope differs.');
        }
        foreach ($master['lessons'] as $i => $lesson) {
            $record = $before['targets'][$i]; $definition = $record['before'];
            if ($record['path'] !== $lesson['definition_path'] || $definition['seeder']['class'] !== $lesson['seeder']
                || $definition['page']['title'] !== $lesson['preserve_page_title']
                || $definition['page']['subtitle_html'] !== $lesson['subtitle_html']
                || $definition['page']['subtitle_text'] !== $lesson['subtitle_text']
                || $definition['page']['locale'] !== 'uk'
                || $definition['page']['category']['slug'] !== $lesson['category_path'][0]) {
                throw new RuntimeException('M40 master/accepted-definition metadata differs.');
            }
            foreach ($lesson['existing_blocks'] as $config) {
                $block = $definition['page']['blocks'][$config['source_index']];
                if ($block['type'] !== $config['preserve_type']
                    || json_decode($block['body'], true, flags: JSON_THROW_ON_ERROR) !== $config['replacement_body_json']) {
                    throw new RuntimeException('M40 original native learner source differs.');
                }
            }
            $append = $lesson['append_blocks'][0]; $last = $definition['page']['blocks'][count($lesson['existing_blocks'])];
            if ($last['body'] !== $append['body_html'] || hash('sha256', $last['body']) !== $append['body_sha256']
                || count($definition['page']['blocks']) !== count($lesson['existing_blocks']) + 1) {
                throw new RuntimeException('M40 appended author source differs.');
            }
        }
        return $master;
    }

    public static function json(array $value): string { return M26DetailPackage::json($value); }

    public static function validate(array $before, array $package): void
    {
        [$expectedBefore, $expectedPackage] = self::load();
        if ($before !== $expectedBefore || $package !== $expectedPackage) {
            throw new RuntimeException('M40 differs from the hash-bound finite author projection.');
        }
        foreach ($package['targets'] as $i => $target) {
            $original = $before['targets'][$i]['before']; $after = $target['after']; $native = $target['native_count'];
            $restored = $after; $restored['page']['blocks'] = $original['page']['blocks'];
            if ($restored !== $original || count($after['page']['blocks']) !== $native + count($target['plans'])
                || count($original['page']['blocks']) !== $native + 1) {
                throw new RuntimeException('M40 protected metadata or block scope differs.');
            }
            foreach (array_slice($after['page']['blocks'], 0, $native) as $j => $config) {
                if ($config !== $original['page']['blocks'][$j]) { throw new RuntimeException('M40 original native config/body bytes changed.'); }
            }
            $first = $after['page']['blocks'][$native];
            $first['type'] = $original['page']['blocks'][$native]['type'];
            $first['body'] = $original['page']['blocks'][$native]['body'];
            if ($first !== $original['page']['blocks'][$native]) { throw new RuntimeException('M40 original final-box identity/metadata changed.'); }
            $keys = [];
            foreach ($target['plans'] as $j => $plan) {
                $source = $after['page']['blocks'][$native + $j]; $data = json_decode($source['body'], true, flags: JSON_THROW_ON_ERROR);
                if (isset($keys[$plan['key']]) || $data['m40_v1']['key'] !== $plan['key']
                    || $plan['legacy_section'] !== $j + 1 || $plan['points'] !== []) {
                    throw new RuntimeException('M40 duplicate/moved section or unapproved detail.');
                }
                $keys[$plan['key']] = true;
                if ($source['type'] === 'practice-set') { self::validatePractice($data); }
            }
            foreach ($target['detail_quality_audit'] as $candidate) {
                if ($candidate['decision'] !== 'visible_basic' || $candidate['reason'] === '') {
                    throw new RuntimeException('M40 finite semantic detail decision differs.');
                }
            }
        }
    }

    private static function validatePractice(array $data): void
    {
        if (count($data['author_self_check']['prompts']) !== 6 || count($data['author_self_check']['answers']) !== 6
            || array_column($data['cases'], 'source_index') !== range(1, 6)) {
            throw new RuntimeException('M40 author task/key mapping differs.');
        }
        foreach ($data['cases'] as $case) {
            if ($case['controls'] === []) { throw new RuntimeException('M40 task has no required control.'); }
            foreach ($case['controls'] as $control) {
                if (!in_array($control['kind'], ['select', 'choice', 'manual'], true)) { throw new RuntimeException('M40 unknown interaction.'); }
                foreach ($control['options'] ?? [] as $option) {
                    if (preg_match('/<[^>]+>/', $option['label'])) { throw new RuntimeException('M40 raw HTML in answer candidate.'); }
                }
                if ($control['kind'] === 'manual') {
                    if (!in_array($control['answer'], $control['accepted'], true) || implode(' ', $control['tokens']) !== $control['answer']) {
                        throw new RuntimeException('M40 manual answer/token fidelity differs.');
                    }
                    foreach ($control['tokens'] as $token) {
                        if (count(preg_split('/\s+/u', $token)) > 3 || preg_match('/[.!?]\s+\p{L}/u', $token)) {
                            throw new RuntimeException('M40 token group exceeds three words or crosses a sentence.');
                        }
                    }
                }
            }
        }
    }

    /** DB marker never chooses an executable view; identity and every data byte must match. */
    public static function presentation(object $block, array $data): ?array
    {
        if (!isset($data['m40_v1']) || ($block->locale ?? null) !== 'uk') { return null; }
        try {
            [$before, $package] = self::load(); self::validate($before, $package);
            foreach ($package['targets'] as $i => $target) {
                if (($block->seeder ?? null) !== $target['identity']) { continue; }
                $native = $target['native_count'];
                foreach ($target['plans'] as $j => $plan) {
                    $source = $target['after']['page']['blocks'][$native + $j]; $position = $native + $j + 1;
                    if (($block->uuid ?? null) !== M26DetailPackage::uuid($target['identity'], $source, $position)) { continue; }
                    if (($block->type ?? null) !== $source['type'] || (int) ($block->sort_order ?? -1) !== $position
                        || $data !== json_decode($source['body'], true, flags: JSON_THROW_ON_ERROR)) { return null; }
                    return ['data' => $data, 'points' => [], 'legacy_section' => $plan['legacy_section'],
                        'legacy_block_uuid' => M26DetailPackage::uuid($target['identity'], $before['targets'][$i]['before']['page']['blocks'][$native], $native + 1),
                        'legacy_practice_id' => $data['m40_v1']['legacy_practice_id'] ?? null];
                }
            }
        } catch (\Throwable) { /* Keep complete stored author paragraphs/prompts/keys readable. */ }
        return null;
    }
}
