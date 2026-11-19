<?php

namespace App\Support;

/** Exact, hint-only UK overlay for36 retained Sentence Transformations gap rows. */
final class SentenceTransformationsReusedMixedHints
{
    public const SOURCE = 'docs/content/sentence-transformations-reused-mixed-hints.v1.json';
    public const SOURCE_SHA = 'd3e218711c91c71ff31978e16d16f6b78bdcc029b30ec3ce433ae8ce3e78364e';
    public const REUSED_SOURCE = 'docs/content/word-order-quality/transformations.v1.json';
    public const REUSED_SOURCE_SHA = '7f4f54c04daeaae55ec7e8e5c06db487c91a2790ed5b209e67cfd553f3b5c7f8';

    public static function digest(array $value): string
    {
        return hash('sha256', json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
    }

    private static function ownsUuid(mixed $uuid): bool
    {
        return is_string($uuid)
            && preg_match('/^woq-transformations-(?:a1|a2|b1|b2|c1|c2)-g0[1-6]$/D', $uuid) === 1;
    }

    private static function need(bool $ok, string $message): void
    {
        if (!$ok) { throw new \RuntimeException('Sentence Transformations reused hints: '.$message); }
    }

    /** Compiles only the unchanged retained standard bank; never loads models, config or a DB. */
    private static function canonicalRows(): array
    {
        static $rows;
        if (isset($rows)) { return $rows; }
        $root = dirname(__DIR__, 2);
        self::need(is_file($root.'/'.self::REUSED_SOURCE) && !is_link($root.'/'.self::REUSED_SOURCE)
            && hash_equals(self::REUSED_SOURCE_SHA, hash_file('sha256', $root.'/'.self::REUSED_SOURCE)), 'Frozen author source changed.');
        SentenceTransformationsPracticeQuality::auditOnlyDefinitions();
        $compiled = WordOrderPracticeQuality::compile('transformations', 'standard')['questions'];
        self::need(count($compiled) === 36, 'Exactly36 retained standard questions required.');
        $rows = [];
        foreach ($compiled as $row) {
            self::need(self::ownsUuid($row['uuid'] ?? null) && (string)($row['type'] ?? '') === '0'
                && !isset($rows[$row['uuid']]), 'Canonical gap identity differs.');
            $rows[$row['uuid']] = $row;
        }
        return $rows;
    }

    /** Finite immutable metadata, bound to exact frozen source and canonical row hashes. */
    public static function load(): array
    {
        static $document;
        if (isset($document)) { return $document; }
        $file = dirname(__DIR__, 2).'/'.self::SOURCE;
        self::need(is_file($file) && !is_link($file) && hash_equals(self::SOURCE_SHA, hash_file('sha256', $file)), 'Hint release bytes changed.');
        $value = json_decode(file_get_contents($file), true, flags: JSON_THROW_ON_ERROR);
        self::need(is_array($value) && ($value['schema_version'] ?? null) === 1
            && ($value['projection'] ?? null) === 'sentence-transformations-reused-mixed-hints-v1'
            && ($value['locale'] ?? null) === 'uk'
            && ($value['source']['path'] ?? null) === self::REUSED_SOURCE
            && ($value['source']['sha256'] ?? null) === self::REUSED_SOURCE_SHA
            && is_array($value['questions'] ?? null) && array_is_list($value['questions'])
            && count($value['questions']) === 36, 'Finite release identity differs.');
        $sources = self::canonicalRows(); $seen = [];
        foreach ($value['questions'] as $row) {
            self::need(is_array($row) && count($row) === 4 && isset($row['id'], $row['uuid'], $row['source_sha256'], $row['hint_uk'])
                && self::ownsUuid($row['uuid']) && isset($sources[$row['uuid']]) && !isset($seen[$row['uuid']]), 'Hint row identity differs.');
            $source = $sources[$row['uuid']]; $hint = $row['hint_uk'];
            self::need($row['id'] === strtolower($source['author_id']) && $row['source_sha256'] === self::digest($source), 'Hint row is not exact-source-bound.');
            self::need(is_string($hint) && trim($hint) === $hint && mb_strlen($hint) >= 12 && mb_strlen($hint) <= 255
                && preg_match('/[А-Яа-яІіЇїЄєҐґ]/u', $hint) === 1
                && preg_match('/[A-Za-z<>]|\{[^}]*\}|&(?:#[0-9]+|[a-z]+);/iu', $hint) !== 1, 'Hint must be useful plain Ukrainian.');
            $seen[$row['uuid']] = true;
        }
        $expected = array_keys($sources); $actual = array_keys($seen); sort($expected); sort($actual);
        self::need($expected === $actual, 'All36 exact retained gaps must be covered.');
        return $document = $value;
    }

    private static function boundRow(array $payload): ?array
    {
        if (!self::ownsUuid($payload['uuid'] ?? null) || !in_array($payload['type'] ?? null, ['0', 0], true)) { return null; }
        $source = self::canonicalRows()[$payload['uuid']] ?? null;
        if ($source === null || ($payload['question'] ?? null) !== $source['question']
            || ($payload['answer_map'] ?? null) !== array_map(static fn ($marker) => $marker['answer'], $source['markers'])
            || ($payload['psp_content_revision'] ?? null) !== self::digest($source)) { return null; }
        foreach (self::load()['questions'] as $row) { if ($row['uuid'] === $payload['uuid']) { return $row; } }
        return null;
    }

    /** Only these two hint fields and one exact display revision may change. */
    public static function project(array $payload, string $locale): array
    {
        if ($locale !== 'uk' || ($row = self::boundRow($payload)) === null) { return $payload; }
        $payload['verb_hint'] = $row['hint_uk'];
        $payload['verb_hints'] = array_fill_keys(array_keys($payload['answer_map']), $row['hint_uk']);
        $payload['stq_reused_hint_revision'] = self::digest($row);
        return $payload;
    }

    /** Invalid/foreign source payloads are never reinterpreted by this hint-only overlay. */
    public static function cachedNeedsRefresh(array $payloads): bool
    {
        foreach ($payloads as $payload) {
            if (!is_array($payload) || ($row = self::boundRow($payload)) === null) { continue; }
            if (($payload['stq_reused_hint_revision'] ?? null) !== self::digest($row)
                || ($payload['verb_hint'] ?? null) !== $row['hint_uk']
                || ($payload['verb_hints'] ?? null) !== array_fill_keys(array_keys($payload['answer_map']), $row['hint_uk'])) { return true; }
        }
        return false;
    }
}
