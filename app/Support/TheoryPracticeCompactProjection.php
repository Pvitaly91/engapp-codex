<?php

namespace App\Support;

use RuntimeException;
use Throwable;

/** Shared exact-bound display projection; providers supply teaching data, never styling or grading. */
abstract class TheoryPracticeCompactProjection
{
    public const SOURCE = '';
    public const SOURCE_SHA = '';
    public const SUPPLEMENT = '';
    /** Optional finite owner subset; an empty list retains the complete source scope. */
    public const TOPICS = [];

    /** Each concrete provider owns a separately validated document in a shared PHP process. */
    private static array $documents = [];

    public static function load(): array
    {
        return self::documentForOwner(null);
    }

    private static function documentForOwner(?string $owner): ?array
    {
        $path = base_path(static::SOURCE);
        if (!is_file($path) || is_link($path) || !hash_equals(static::SOURCE_SHA, hash_file('sha256', $path))) {
            throw new RuntimeException('Compact practice source differs.');
        }
        $provider = static::class;
        if (!isset(self::$documents[$provider])) {
            $candidate = json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
            // The pinned document lists all possible bindings. An unrelated
            // owner cannot match, even if its supplement is fully compiled.
            if ($owner !== null && is_array($candidate) && !self::mayBindOwner($candidate, $owner)) {
                return null;
            }
            static::validate($candidate);
            self::$documents[$provider] = $candidate;
        }
        return self::$documents[$provider];
    }

    private static function mayBindOwner(array $candidate, string $owner): bool
    {
        $targets = $candidate['targets'] ?? null;
        if (!is_array($targets) || $targets === []) { return true; }
        foreach ($targets as $target) {
            $seeder = $target['binding']['seeder'] ?? null;
            // Unknown shapes retain the original validation/fallback path.
            if (!is_string($seeder) || $seeder === '' || $seeder === $owner) { return true; }
        }
        return false;
    }

    public static function validate(array $document): void
    {
        $supplement = static::SUPPLEMENT;
        $source = $document['source'] ?? [];
        if (($document['schema_version'] ?? null) !== 1
            || ($source['path'] ?? null) !== $supplement::SOURCE
            || ($source['sha256'] ?? null) !== $supplement::SOURCE_SHA) {
            throw new RuntimeException('Compact practice source binding differs.');
        }
        $originals = array_column($supplement::load()['targets'], null, 'topic');
        if (static::TOPICS !== []) {
            if (count(static::TOPICS) !== count(array_unique(static::TOPICS))
                || array_diff(static::TOPICS, array_keys($originals)) !== []) {
                throw new RuntimeException('Compact topic scope differs.');
            }
            $originals = array_intersect_key($originals, array_flip(static::TOPICS));
        }
        $seenTopics = [];
        foreach ($document['targets'] ?? [] as $target) {
            $topic = $target['topic'] ?? '';
            $original = $originals[$topic] ?? null;
            if ($original === null || isset($seenTopics[$topic])
                || ($target['binding'] ?? null) !== $original['binding']
                || ($target['after_data_sha256'] ?? null) !== $original['after_data_sha256']) {
                throw new RuntimeException('Compact practice owner differs.');
            }
            $seenTopics[$topic] = true;
            $graded = []; $examples = []; $display = [];
            foreach (['selects', 'choices', 'inputs', 'rephrase'] as $kind) {
                $items = $original['after_data'][$kind] ?? [];
                $rows = $target['items'][$kind] ?? [];
                if (count($items) !== count($rows)) { throw new RuntimeException('Compact item coverage differs.'); }
                foreach ($rows as $position => $row) {
                    $index = $row['index'] ?? null;
                    $item = is_int($index) ? ($items[$index] ?? null) : null;
                    if ($index !== $position || !is_array($item) || isset($display[$kind][$index])
                        || ($row['source_item_sha256'] ?? null) !== $supplement::digest($item)) {
                        throw new RuntimeException('Compact item binding differs.');
                    }
                    self::plain($row['instruction_uk'] ?? null, true);
                    foreach ($row['stimuli'] ?? [] as $stimulus) {
                        if (!in_array($stimulus['lang'] ?? null, ['uk', 'en'], true)) { throw new RuntimeException('Unknown stimulus language.'); }
                        self::plain($stimulus['text'] ?? null);
                    }
                    $display[$kind][$index] = true;
                    if (!empty($item['answer']) || !empty($item['accepted'])) { $graded[$kind][] = $index; }
                    else { $examples[$kind][] = $index; }
                }
            }
            $covered = []; $exampleCovered = []; $keys = [];
            foreach ($target['groups'] ?? [] as $group) {
                $key = $group['key'] ?? ''; $kind = $group['kind'] ?? '';
                $indices = $group['indices'] ?? [];
                if (!is_string($key) || !preg_match('/^[a-z0-9-]+$/D', $key) || isset($keys[$key])
                    || !isset($graded[$kind]) || !array_is_list($indices) || count($indices) < 1 || count($indices) > 3) {
                    throw new RuntimeException('Compact exercise shape differs.');
                }
                // Preserve a source's sole editing task together with its ungraded demonstration.
                // Never split a larger bank into singleton cards or invent a second graded answer.
                if (count($indices) === 1 && ($kind !== 'rephrase' || count($graded[$kind]) !== 1
                    || empty($group['example_indices']))) {
                    throw new RuntimeException('Compact singleton needs its exact source example.');
                }
                $keys[$key] = true; self::plain($group['title'] ?? null, true); self::plain($group['instruction'] ?? null, true);
                foreach ($indices as $index) {
                    if (!is_int($index) || !in_array($index, $graded[$kind], true) || isset($covered[$kind][$index])) {
                        throw new RuntimeException('Compact exercise repeats or loses an answer.');
                    }
                    $covered[$kind][$index] = true;
                }
                foreach ($group['example_indices'] ?? [] as $index) {
                    if (!is_int($index) || !in_array($index, $examples[$kind] ?? [], true) || isset($exampleCovered[$kind][$index])) {
                        throw new RuntimeException('Compact example binding differs.');
                    }
                    $exampleCovered[$kind][$index] = true;
                }
            }
            foreach ($graded as $kind => $indices) {
                if (count($indices) !== count($covered[$kind] ?? [])) { throw new RuntimeException('Compact graded coverage incomplete.'); }
            }
            foreach ($examples as $kind => $indices) {
                if (count($indices) !== count($exampleCovered[$kind] ?? [])) { throw new RuntimeException('Compact example coverage incomplete.'); }
            }
        }
        if (count($seenTopics) !== count($originals)) { throw new RuntimeException('Compact topic coverage incomplete.'); }
    }

    private static function plain(mixed $text, bool $uk = false): void
    {
        if (!is_string($text) || trim($text) === '' || preg_match('/<[^>]+>|\{a\d+\}|&(?:nbsp|amp|quot);/u', $text)
            || ($uk && (!preg_match('/[А-Яа-яІіЇїЄєҐґ]/u', $text)
                || preg_match('/Subject|Base verb/iu', $text)
                || count(preg_split('/\s+/u', trim($text))) > 22))) {
            throw new RuntimeException('Compact practice needs short plain instructions.');
        }
    }

    public static function presentation(object $block, array $data, bool $canonicalCaller): ?array
    {
        if (!$canonicalCaller || app()->getLocale() !== 'uk') { return null; }
        $supplement = static::SUPPLEMENT;
        try {
            $owner = $block->seeder ?? null;
            $document = is_string($owner) && $owner !== ''
                ? self::documentForOwner($owner) : static::load();
            foreach ($document['targets'] ?? [] as $target) {
                foreach ($target['binding'] as $field => $expected) {
                    if ($field === 'body_sha256') { continue; }
                    if (($block->$field ?? null) !== $expected) { continue 2; }
                }
                if (!is_string($block->body ?? null)
                    || !hash_equals($target['binding']['body_sha256'], hash('sha256', $block->body))
                    || !hash_equals($target['after_data_sha256'], $supplement::digest($data))) { return null; }
                $target['groups_by_kind'] = [];
                foreach ($target['groups'] as $i => $group) {
                    $group['number'] = $i + 1;
                    $target['groups_by_kind'][$group['kind']][] = $group;
                }
                return $target;
            }
        } catch (Throwable) { /* Changed/foreign content retains its complete previous view. */ }
        return null;
    }
}
