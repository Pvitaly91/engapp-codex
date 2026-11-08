<?php

namespace App\Support;

use App\Services\M26ContentPatch;
use RuntimeException;

/** Three Get-only insert projections; old question families are never updated. */
final class M53QuestionSupplement
{
    public const SOURCE = 'docs/content/m53-get-passive-question-supplement.v1.0.0.json';
    public const SOURCE_SHA = '1c23bf05b8db505d737a2b6013c8c3680cf60047170f1a0f69477689678e7476';
    public const BANK = 'Database\\Seeders\\V3\\Polyglot\\M53GetPassiveB1SupplementSeeder';
    public const OWNER = 'Database\\Seeders\\Page_V3\\PassiveVoice\\Basics\\PassiveVoiceGetPassiveTheorySeeder';
    public const UUIDS = ['m53-get-b1-01', 'm53-get-b1-02', 'm53-get-b1-03'];
    public const ROW_COLUMNS = M51QuestionSupplement::ROW_COLUMNS;

    public static function load(?string $root = null): array
    {
        $path = ($root ?? base_path()).'/'.self::SOURCE;
        if (preg_match('/^[a-f0-9]{64}$/D', self::SOURCE_SHA) !== 1 || !is_file($path) || is_link($path)
            || !hash_equals(self::SOURCE_SHA, hash_file('sha256', $path))) {
            throw new RuntimeException('M53 question supplement is not reviewed/frozen.');
        }
        $source = json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
        self::validate($source);
        if (($source['status'] ?? null) !== 'frozen') { throw new RuntimeException('M53 question source is not frozen.'); }
        return $source;
    }

    public static function validate(array $source): void
    {
        if (($source['schema_version'] ?? null) !== 1 || ($source['seeder']['class'] ?? null) !== self::BANK
            || ($source['scope']['page_identity'] ?? null) !== self::OWNER || ($source['scope']['page_id'] ?? null) !== 95
            || ($source['scope']['locale'] ?? null) !== 'uk' || ($source['scope']['practice_level'] ?? null) !== 'B1'
            || array_column($source['questions'] ?? [], 'uuid') !== self::UUIDS) {
            throw new RuntimeException('M53 Get-only question scope differs.');
        }
        foreach (['saved_test_rows', 'saved_test_question_rows', 'question_tag_rows', 'question_marker_tag_rows',
            'old_question_updates', 'old_option_updates', 'old_answer_updates', 'old_theory_link_updates_or_deletes'] as $key) {
            if (($source['scope'][$key] ?? null) !== 0) { throw new RuntimeException('M53 question source broadens writes.'); }
        }
        foreach ($source['questions'] as $question) {
            $tokens = $question['tokens_correct'] ?? [];
            if (($question['level'] ?? null) !== 'B1' || ($question['type'] ?? null) !== 4
                || preg_match('/[\x{0400}-\x{04FF}]/u', $question['question'] ?? '') !== 1
                || ($question['source_text_uk'] ?? null) !== $question['question']
                || !array_is_list($tokens) || count($tokens) < 4 || ComposeTokenCase::normalize($tokens) !== $tokens
                || preg_replace('/\s+([?.!,;:])/', '$1', implode(' ', $tokens)) !== ($question['target_text'] ?? null)
                || array_keys($question['answers'] ?? []) !== array_map(static fn ($n) => 'a'.($n + 1), array_keys($tokens))
                || array_values($question['answers']) !== $tokens
                || ($question['options'] ?? null) !== [...$tokens, ...($question['distractors'] ?? [])]) {
                throw new RuntimeException('M53 complete Get question/translation/token sequence differs.');
            }
            foreach ($question['options'] as $token) {
                if (!is_string($token) || preg_match('/^(?:[A-Za-z]+(?:[\x{2019}\x{0027}][A-Za-z]+)?|[,.!?;:])$/uD', $token) !== 1
                    || strlen($token) > 255) { throw new RuntimeException('M53 requires complete English word tokens.'); }
            }
        }
    }

    /** Reuse exact SQL-collation matches; symbolic rows are materialized by the unchanged M51 primitive. */
    public static function project(array $source, array $binding, array $matches): array
    {
        self::validate($source);
        if (array_keys($binding) !== ['owner', 'level', 'category_id', 'source_id', 'practice_uuid']
            || $binding['owner'] !== self::OWNER || $binding['level'] !== 'B1' || $binding['category_id'] !== 45
            || $binding['source_id'] !== 354 || preg_match('/^[a-f0-9-]{36}$/D', $binding['practice_uuid']) !== 1) {
            throw new RuntimeException('M53 independently confirmed existing taxonomy/practice binding differs.');
        }
        $out = array_fill_keys(array_keys(self::ROW_COLUMNS), []);
        $out += ['reused_options' => [], 'translations' => [], 'provenance' => []];
        $options = []; $ids = []; $requested = [];
        foreach ($source['questions'] as $question) {
            $uid = $question['uuid']; $keys = []; $actualAnswers = [];
            foreach ($question['options'] as $n => $token) {
                $requested[$token] = true; $key = 'option-'.hash('sha256', mb_strtolower($token));
                $lookup = $matches[$token] ?? null;
                if (!is_array($lookup) || !array_is_list($lookup) || count($lookup) > 1) {
                    throw new RuntimeException('M53 missing or ambiguous complete option lookup.');
                }
                if ($lookup !== []) {
                    $row = $lookup[0]; $columns = array_keys($row); sort($columns); $expected = self::ROW_COLUMNS['question_options']; sort($expected);
                    if ($columns !== $expected || !is_int($row['id']) || $row['id'] < 1
                        || mb_strtolower($row['option']) !== mb_strtolower($token)
                        || (isset($ids[$row['id']]) && $ids[$row['id']] !== $key)) {
                        throw new RuntimeException('M53 original shared option collision differs.');
                    }
                    $ids[$row['id']] = $key;
                    $candidate = ['key' => $key, 'id' => $row['id'], 'row' => $row, 'sha256' => M26ContentPatch::digest($row)];
                } else { $candidate = ['key' => $key, 'fields' => ['option' => $token]]; }
                if (isset($options[$key]) && $options[$key] !== $candidate) { throw new RuntimeException('M53 option state/casing changed between instances.'); }
                $options[$key] = $candidate; $keys[$key] = true;
                if ($n < count($question['tokens_correct'])) { $actualAnswers[] = $candidate['row']['option'] ?? $token; }
            }
            if (ComposeTokenCase::normalize($actualAnswers) !== $question['tokens_correct']) {
                throw new RuntimeException('M53 original option casing cannot reproduce the approved answer.');
            }
            $byMarker = [];
            foreach ($question['tokens_correct'] as $n => $token) {
                $marker = 'a'.($n + 1); $byMarker[$marker] = [$token];
                $out['question_answers'][] = ['key' => $uid.':'.$marker, 'question_key' => $uid,
                    'option_key' => 'option-'.hash('sha256', mb_strtolower($token)), 'fields' => ['marker' => $marker]];
            }
            $out['questions'][] = ['key' => $uid, 'fields' => ['uuid' => $uid, 'question' => $question['question'],
                'difficulty' => 3, 'level' => 'B1', 'category_id' => 45, 'source_id' => 354, 'flag' => 0, 'type' => '4',
                'options_by_marker' => json_encode($byMarker, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR),
                'theory_text_block_uuid' => null, 'seeder' => self::BANK]];
            foreach (array_keys($keys) as $key) {
                $out['question_option_question'][] = ['key' => $uid.':'.$key, 'question_key' => $uid,
                    'option_key' => $key, 'fields' => ['flag' => null]];
            }
            $out['question_theory_text_blocks'][] = ['key' => $uid, 'fields' => ['question_uuid' => $uid,
                'text_block_uuid' => $binding['practice_uuid'], 'position' => 0]];
            $out['translations'][$uid] = ['en' => $question['target_text'], 'uk' => $question['source_text_uk']];
            $out['provenance'][$uid] = ['source' => self::SOURCE, 'source_uid' => $uid, 'bank_seeder' => self::BANK,
                'owner' => self::OWNER, 'question_sha256' => M26ContentPatch::digest($question)];
        }
        $actual = array_keys($matches); $expected = array_keys($requested); sort($actual); sort($expected);
        if ($actual !== $expected) { throw new RuntimeException('M53 unrelated/missing option lookup tokens.'); }
        foreach ($options as $option) { $out[isset($option['id']) ? 'reused_options' : 'question_options'][] = $option; }
        return $out;
    }
}
