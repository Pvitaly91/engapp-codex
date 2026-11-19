<?php

namespace App\Support;

use App\Services\M26ContentPatch;
use RuntimeException;

/** Two finite Indefinite questions only; unchanged M51 primitives materialize insert-only rows. */
final class M75QuestionSupplement
{
    public const SOURCE = 'docs/content/m75-indefinite-question-supplement.v1.json';
    public const SOURCE_SHA = '84f09262cd8a20fba5ae6d4a505e93ec7463639dcb65407ac127a6c574695eea';
    public const BANK = 'Database\\Seeders\\V3\\PronounsDemonstratives\\M75IndefinitePronounsSupplementSeeder';
    public const OLD_BANK = 'Database\\Seeders\\V3\\Polyglot\\PolyglotIndefinitePronounsAllLevelsLessonSeeder';
    public const OWNER = M75PronounForms::OWNERS[2];
    public const UUIDS = ['m75-indefinite-a2-01', 'm75-indefinite-a2-02'];
    public const PAGE_ID = 122;
    public const PAGE_CATEGORY_ID = 20;
    public const PRACTICE_ID = 3924;
    public const PRACTICE_UUID = '57ef1463-3d71-5a26-971a-53022c7be0b7';
    public const CATEGORY_ID = 45;
    public const SOURCE_ID = 327;
    public const ROW_COLUMNS = M51QuestionSupplement::ROW_COLUMNS;
    public const TOKENS = [
        ['Nothing', 'is', 'on', 'the', 'shelf'],
        ['Anyone', 'can', 'use', 'this', 'computer'],
    ];

    /** Pending pins cannot load an apply source. Drafts may be validated/projected only in memory. */
    public static function load(?string $root = null): array
    {
        $path = ($root ?? base_path()).'/'.self::SOURCE;
        if (preg_match('/^[a-f0-9]{64}$/D', self::SOURCE_SHA) !== 1 || !is_file($path) || is_link($path)
            || !hash_equals(self::SOURCE_SHA, hash_file('sha256', $path))) {
            throw new RuntimeException('M75 question source is not independently reviewed/frozen.');
        }
        $source = json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
        self::validate($source);
        if ($source['status'] !== 'frozen') { throw new RuntimeException('M75 question source is not frozen.'); }
        return $source;
    }

    public static function binding(): array
    {
        return ['owner' => self::OWNER, 'level' => 'A2', 'category_id' => self::CATEGORY_ID,
            'source_id' => self::SOURCE_ID, 'practice_uuid' => self::PRACTICE_UUID];
    }

    public static function validate(array $source): void
    {
        $scope = $source['scope'] ?? [];
        if (($source['schema_version'] ?? null) !== 1 || ($source['package'] ?? null) !== 'm75-indefinite-question-supplement'
            || ($source['version'] ?? null) !== '1.0.0' || ($source['locale'] ?? null) !== 'uk'
            || !in_array($source['status'] ?? null, ['draft', 'frozen'], true)
            || ($source['seeder']['class'] ?? null) !== self::BANK || ($scope['page_identity'] ?? null) !== self::OWNER
            || ($scope['page_id'] ?? null) !== self::PAGE_ID || ($scope['page_category_id'] ?? null) !== self::PAGE_CATEGORY_ID
            || ($scope['locale'] ?? null) !== 'uk' || ($scope['practice_uuid'] ?? null) !== self::PRACTICE_UUID
            || !array_key_exists('practice_level', $scope) || $scope['practice_level'] !== null
            || ($scope['question_level'] ?? null) !== 'A2' || ($scope['new_question_rows'] ?? null) !== 2
            || !is_int($scope['new_option_rows'] ?? null) || $scope['new_option_rows'] < 0 || $scope['new_option_rows'] > 20
            || !is_array($source['questions'] ?? null) || !array_is_list($source['questions'])
            || array_column($source['questions'], 'uuid') !== self::UUIDS) {
            throw new RuntimeException('M75 exact two-question Indefinite source scope differs.');
        }
        foreach (['old_question_updates', 'old_option_updates', 'old_answer_updates', 'old_theory_link_updates_or_deletes',
            'saved_test_rows', 'saved_test_question_rows', 'question_tag_rows', 'question_marker_tag_rows',
            'verb_hint_rows', 'question_hint_rows', 'question_variant_rows', 'progress_writes'] as $field) {
            if (($scope[$field] ?? null) !== 0) { throw new RuntimeException('M75 source broadens the insert-only exception.'); }
        }
        foreach ($source['questions'] as $i => $question) {
            $tokens = self::TOKENS[$i]; $wordKey = implode(' ', $tokens);
            if (($question['source_uid'] ?? null) !== self::UUIDS[$i] || ($question['level'] ?? null) !== 'A2'
                || ($question['type'] ?? null) !== 4 || ($question['tokens_correct'] ?? null) !== $tokens
                || ($question['target_text'] ?? null) !== $wordKey || ($question['canonical_en'] ?? null) !== $wordKey.'.'
                || ($question['source_text_uk'] ?? null) !== ($question['question'] ?? null)
                || array_keys($question['answers'] ?? []) !== ['a1', 'a2', 'a3', 'a4', 'a5']
                || array_values($question['answers']) !== $tokens || ComposeTokenCase::normalize($tokens) !== $tokens
                || !is_array($question['distractors'] ?? null) || !array_is_list($question['distractors'])
                || count($question['distractors']) < 2 || count($question['distractors']) > 5
                || ($question['options'] ?? null) !== [...$tokens, ...$question['distractors']]
                || !is_array($question['learning_requirements'] ?? null) || $question['learning_requirements'] === []) {
                throw new RuntimeException('M75 complete canonical words/answers/semantic requirements differ.');
            }
            foreach (['question', 'translation_uk', 'context_uk', 'criterion_uk', 'ambiguity_note_uk'] as $field) {
                $text = $question[$field] ?? null;
                if (!is_string($text) || trim($text) !== $text || preg_match('/[\x{0400}-\x{04FF}]/u', $text) !== 1
                    || preg_match('/<[^>]+>|&(?:#[0-9]+|#x[0-9a-f]+|[a-z][a-z0-9]+);|\{a[0-9]+\}/iu', $text)
                    || strlen($text) > 65535) {
                    throw new RuntimeException('M75 complete visible Ukrainian teaching fields are required.');
                }
            }
            $shuffled = $question['tokens_shuffled'] ?? null;
            if (!is_array($shuffled) || !array_is_list($shuffled) || $shuffled === $tokens) {
                throw new RuntimeException('M75 shuffled complete word instances are missing.');
            }
            $a = $tokens; $b = $shuffled; sort($a); sort($b);
            if ($a !== $b) { throw new RuntimeException('M75 shuffled token multiplicity differs.'); }
            $seen = [];
            foreach ($question['options'] as $token) {
                if (!is_string($token) || preg_match('/^[A-Za-z]+(?:[\x{2019}\x{0027}][A-Za-z]+)?$/uD', $token) !== 1
                    || strlen($token) > 255 || isset($seen[mb_strtolower($token)])) {
                    throw new RuntimeException('M75 requires distinct complete English word options, not fragments or collisions.');
                }
                $seen[mb_strtolower($token)] = true;
            }
        }
    }

    /** Complete fresh SQL-collation matches; absent options alone may become new symbolic rows. */
    public static function project(array $source, array $binding, array $matches): array
    {
        self::validate($source);
        if ($binding !== self::binding()) { throw new RuntimeException('M75 exact existing taxonomy/practice binding differs.'); }
        $out = array_fill_keys(array_keys(self::ROW_COLUMNS), []);
        $out += ['reused_options' => [], 'translations' => [], 'provenance' => []];
        $options = []; $seenIds = []; $requested = [];
        foreach ($source['questions'] as $question) {
            $uid = $question['uuid']; $keys = []; $actualAnswers = [];
            foreach ($question['options'] as $n => $token) {
                $requested[$token] = true; $key = 'option-'.hash('sha256', mb_strtolower($token));
                $lookup = $matches[$token] ?? null;
                if (!is_array($lookup) || !array_is_list($lookup) || count($lookup) > 1) {
                    throw new RuntimeException('M75 missing or ambiguous complete SQL option lookup.');
                }
                if ($lookup !== []) {
                    $row = $lookup[0]; $columns = array_keys($row); $expected = self::ROW_COLUMNS['question_options'];
                    sort($columns); sort($expected);
                    if ($columns !== $expected || !is_int($row['id']) || $row['id'] < 1
                        || !is_string($row['option']) || mb_strtolower($row['option']) !== mb_strtolower($token)
                        || (isset($seenIds[$row['id']]) && $seenIds[$row['id']] !== $key)) {
                        throw new RuntimeException('M75 reused shared option row/collision differs.');
                    }
                    $seenIds[$row['id']] = $key;
                    $candidate = ['key' => $key, 'id' => $row['id'], 'row' => $row, 'sha256' => M26ContentPatch::digest($row)];
                } else { $candidate = ['key' => $key, 'fields' => ['option' => $token]]; }
                if (isset($options[$key]) && $options[$key] !== $candidate) {
                    throw new RuntimeException('M75 shared option state/casing differs between instances.');
                }
                $options[$key] = $candidate; $keys[$key] = true;
                if ($n < 5) { $actualAnswers[] = $candidate['row']['option'] ?? $token; }
            }
            if (ComposeTokenCase::normalize($actualAnswers) !== $question['tokens_correct']) {
                throw new RuntimeException('M75 reused option case cannot produce the exact approved visible sentence.');
            }
            $byMarker = [];
            foreach ($question['tokens_correct'] as $n => $token) {
                $marker = 'a'.($n + 1); $byMarker[$marker] = [$token];
                $out['question_answers'][] = ['key' => $uid.':'.$marker, 'question_key' => $uid,
                    'option_key' => 'option-'.hash('sha256', mb_strtolower($token)), 'fields' => ['marker' => $marker]];
            }
            $out['questions'][] = ['key' => $uid, 'fields' => ['uuid' => $uid, 'question' => $question['question'],
                'difficulty' => 2, 'level' => 'A2', 'category_id' => self::CATEGORY_ID, 'source_id' => self::SOURCE_ID,
                'flag' => 0, 'type' => '4', 'options_by_marker' => json_encode($byMarker, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR),
                'theory_text_block_uuid' => null, 'seeder' => self::BANK]];
            foreach (array_keys($keys) as $key) {
                $out['question_option_question'][] = ['key' => $uid.':'.$key, 'question_key' => $uid, 'option_key' => $key, 'fields' => ['flag' => null]];
            }
            $out['question_theory_text_blocks'][] = ['key' => $uid, 'fields' => ['question_uuid' => $uid,
                'text_block_uuid' => self::PRACTICE_UUID, 'position' => 0]];
            $out['translations'][$uid] = ['en' => $question['canonical_en'], 'uk' => $question['translation_uk']];
            $out['provenance'][$uid] = ['source' => self::SOURCE, 'source_uid' => $uid, 'bank_seeder' => self::BANK,
                'owner' => self::OWNER, 'question_sha256' => M26ContentPatch::digest($question)];
        }
        $actual = array_keys($matches); $expected = array_keys($requested); sort($actual); sort($expected);
        if ($actual !== $expected) { throw new RuntimeException('M75 unrelated or missing token lookups.'); }
        foreach ($options as $option) { $out[isset($option['id']) ? 'reused_options' : 'question_options'][] = $option; }
        if (count($out['question_options']) !== $source['scope']['new_option_rows']) {
            throw new RuntimeException('M75 new-option count differs from the explicitly reviewed genuinely-absent token scope.');
        }
        return $out;
    }
}
