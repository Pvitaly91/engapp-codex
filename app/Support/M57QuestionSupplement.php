<?php

namespace App\Support;

use App\Services\M26ContentPatch;
use RuntimeException;

/** Finite three-topic B1 insert projection; M51 retains row materialization/transaction primitives. */
final class M57QuestionSupplement
{
    public const SOURCE = 'docs/content/m57-continuous-perfect-passives-widget-questions.v1.0.0.json';
    public const SOURCE_SHA = 'dbc4da47f98b0111ce5b9e0ab23dd29637c810744675f8a7a2bb392caa39d832';
    public const OWNERS = [
        'Database\\Seeders\\Page_V3\\PassiveVoice\\Tenses\\PassiveVoicePresentContinuousTheorySeeder',
        'Database\\Seeders\\Page_V3\\PassiveVoice\\Tenses\\PassiveVoicePastContinuousTheorySeeder',
        'Database\\Seeders\\Page_V3\\PassiveVoice\\Tenses\\PassiveVoicePresentPerfectTheorySeeder',
    ];
    public const BANKS = [
        'Database\\Seeders\\V3\\Polyglot\\M57PresentContinuousPassiveB1SupplementSeeder',
        'Database\\Seeders\\V3\\Polyglot\\M57PastContinuousPassiveB1SupplementSeeder',
        'Database\\Seeders\\V3\\Polyglot\\M57PresentPerfectPassiveB1SupplementSeeder',
    ];
    public const OLD_BANKS = [
        'Database\\Seeders\\V3\\Polyglot\\PolyglotPassiveVoicePresentContinuousAllLevelsLessonSeeder',
        'Database\\Seeders\\V3\\Polyglot\\PolyglotPassiveVoicePastContinuousAllLevelsLessonSeeder',
        'Database\\Seeders\\V3\\Polyglot\\PolyglotPassiveVoicePresentPerfectAllLevelsLessonSeeder',
    ];
    public const PAGE_IDS = [111, 108, 112];
    public const CATEGORY_IDS = [45, 45, 45];
    public const SOURCE_IDS = [346, 347, 344];
    public const SLUGS = ['present-continuous', 'past-continuous', 'present-perfect'];
    public const PREFIXES = ['m57-pc-b1-', 'm57-pac-b1-', 'm57-pp-b1-'];
    public const UUIDS = [
        'm57-pc-b1-01', 'm57-pc-b1-02', 'm57-pc-b1-03', 'm57-pc-b1-04', 'm57-pc-b1-05',
        'm57-pac-b1-01', 'm57-pac-b1-02', 'm57-pac-b1-03', 'm57-pac-b1-04', 'm57-pac-b1-05',
        'm57-pp-b1-01', 'm57-pp-b1-02', 'm57-pp-b1-03', 'm57-pp-b1-04', 'm57-pp-b1-05',
    ];
    public const ROW_COLUMNS = M51QuestionSupplement::ROW_COLUMNS;

    public static function load(?string $root = null): array
    {
        $path = ($root ?? base_path()).'/'.self::SOURCE;
        if (preg_match('/^[a-f0-9]{64}$/D', self::SOURCE_SHA) !== 1 || !is_file($path) || is_link($path)
            || !hash_equals(self::SOURCE_SHA, hash_file('sha256', $path))) {
            throw new RuntimeException('M57 question supplement is not reviewed/frozen.');
        }
        $source = json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
        self::validate($source);
        if (($source['status'] ?? null) !== 'frozen') { throw new RuntimeException('M57 question source is not frozen.'); }
        return $source;
    }

    public static function validate(array $source): void
    {
        if (($source['schema_version'] ?? null) !== 1 || ($source['package'] ?? null) !== 'm57-continuous-perfect-passives-widget-questions'
            || ($source['version'] ?? null) !== '1.0.0' || ($source['locale'] ?? null) !== 'uk'
            || !in_array($source['status'] ?? null, ['draft', 'frozen'], true)
            || !is_array($source['banks'] ?? null) || !array_is_list($source['banks']) || count($source['banks']) !== 3
            || ($source['scope']['new_questions'] ?? null) !== 15) {
            throw new RuntimeException('M57 question source schema/version/scope differs.');
        }
        foreach (['new_saved_tests', 'old_question_updates', 'old_option_updates', 'old_answer_updates',
            'old_link_updates_or_deletes', 'grader_changes', 'progress_writes'] as $key) {
            if (($source['scope'][$key] ?? null) !== 0) { throw new RuntimeException('M57 question source broadens writes.'); }
        }
        $uids = [];
        foreach ($source['banks'] as $i => $bank) {
            if (!is_array($bank) || ($bank['owner'] ?? null) !== self::OWNERS[$i] || ($bank['bank_seeder'] ?? null) !== self::BANKS[$i]
                || ($bank['page_id'] ?? null) !== self::PAGE_IDS[$i] || ($bank['category_id'] ?? null) !== self::CATEGORY_IDS[$i]
                || ($bank['source_id'] ?? null) !== self::SOURCE_IDS[$i] || ($bank['level'] ?? null) !== 'B1' || ($bank['type'] ?? null) !== 4
                || ($bank['local_url'] ?? null) !== 'http://gramlyze.loc/theory/passive-voice/passive-voice-tenses/theory-passive-voice-'.self::SLUGS[$i]
                || !is_array($bank['questions'] ?? null) || !array_is_list($bank['questions']) || count($bank['questions']) !== 5) {
                throw new RuntimeException('M57 exact nested thematic B1 five differs.');
            }
            foreach ($bank['questions'] as $n => $question) {
                if (!is_array($question)) { throw new RuntimeException('M57 complete question record missing.'); }
                $uid = self::PREFIXES[$i].str_pad((string) ($n + 1), 2, '0', STR_PAD_LEFT); $tokens = $question['tokens_correct'] ?? [];
                foreach (['question', 'translation_uk', 'context_uk', 'criterion_uk', 'ambiguity_note_uk'] as $field) {
                    if (!is_string($question[$field] ?? null) || trim($question[$field]) !== $question[$field]
                        || preg_match('/[\x{0400}-\x{04FF}]/u', $question[$field]) !== 1
                        || preg_match('/<[^>]+>/', $question[$field]) === 1) {
                        throw new RuntimeException('M57 Ukrainian condition/translation/criterion missing.');
                    }
                }
                if (($question['uuid'] ?? null) !== $uid || ($question['source_uid'] ?? null) !== $uid
                    || ($question['level'] ?? null) !== 'B1' || ($question['type'] ?? null) !== 4
                    || ($question['source_text_uk'] ?? null) !== $question['question']
                    || !is_array($tokens) || !array_is_list($tokens) || count($tokens) < 4 || ComposeTokenCase::normalize($tokens) !== $tokens
                    || preg_replace('/\s+([?.!,;:])/', '$1', implode(' ', $tokens)) !== ($question['target_text'] ?? null)
                    || array_keys($question['answers'] ?? []) !== array_map(static fn ($n) => 'a'.($n + 1), array_keys($tokens))
                    || array_values($question['answers']) !== $tokens || !is_array($question['distractors'] ?? null)
                    || !array_is_list($question['distractors']) || ($question['options'] ?? null) !== [...$tokens, ...$question['distractors']]
                    || ($question['hints'] ?? null) !== [] || ($question['verb_hints'] ?? null) !== []
                    || str_contains($question['target_text'], 'in this passive context') || str_contains($question['target_text'], 'in this context')
                    || strlen($question['question']) > 65535) {
                    throw new RuntimeException('M57 complete semantic question/token contract differs.');
                }
                foreach ($question['options'] as $token) {
                    if (!is_string($token) || strlen($token) > 255
                        || preg_match('/^(?:[A-Za-z]+(?:[\x{2019}\x{0027}][A-Za-z]+)?|[,.!?;:])$/uD', $token) !== 1) {
                        throw new RuntimeException('M57 requires complete English word tokens.');
                    }
                }
                $uids[] = $uid;
            }
        }
        if ($uids !== self::UUIDS) { throw new RuntimeException('M57 stable 15-question order differs.'); }
    }
    /** Fresh inspected taxonomy and complete SQL-collation lookups; output remains symbolic and insert-only. */
    public static function project(array $source, array $bindings, array $matches): array
    {
        self::validate($source);
        if (!array_is_list($bindings) || count($bindings) !== 3) { throw new RuntimeException('M57 three fresh bindings required.'); }
        $out = array_fill_keys(array_keys(self::ROW_COLUMNS), []);
        $out += ['reused_options' => [], 'translations' => [], 'provenance' => []];
        $options = []; $ids = []; $requested = []; $practices = [];
        foreach ($source['banks'] as $i => $bank) {
            $binding = $bindings[$i];
            if (!is_array($binding) || array_keys($binding) !== ['owner', 'level', 'category_id', 'source_id', 'practice_uuid']
                || $binding['owner'] !== self::OWNERS[$i] || $binding['level'] !== 'B1'
                || $binding['category_id'] !== self::CATEGORY_IDS[$i] || $binding['source_id'] !== self::SOURCE_IDS[$i]
                || !is_string($binding['practice_uuid']) || preg_match('/^[a-f0-9-]{36}$/D', $binding['practice_uuid']) !== 1
                || isset($practices[$binding['practice_uuid']])) {
                throw new RuntimeException('M57 independently confirmed taxonomy/practice binding differs.');
            }
            $practices[$binding['practice_uuid']] = true;
            foreach ($bank['questions'] as $question) {
                $uid = $question['uuid']; $keys = []; $actualAnswers = [];
                foreach ($question['options'] as $n => $token) {
                    $requested[$token] = true; $key = 'option-'.hash('sha256', mb_strtolower($token));
                    $lookup = $matches[$token] ?? null;
                    if (!is_array($lookup) || !array_is_list($lookup) || count($lookup) > 1) {
                        throw new RuntimeException('M57 missing or ambiguous complete option lookup.');
                    }
                    if ($lookup !== []) {
                        $row = $lookup[0]; $columns = array_keys($row); sort($columns);
                        $expected = self::ROW_COLUMNS['question_options']; sort($expected);
                        if ($columns !== $expected || !is_int($row['id']) || $row['id'] < 1
                            || !is_string($row['option']) || mb_strtolower($row['option']) !== mb_strtolower($token)
                            || (isset($ids[$row['id']]) && $ids[$row['id']] !== $key)) {
                            throw new RuntimeException('M57 original shared option collision differs.');
                        }
                        $ids[$row['id']] = $key;
                        $candidate = ['key' => $key, 'id' => $row['id'], 'row' => $row, 'sha256' => M26ContentPatch::digest($row)];
                        if (isset($options[$key]) && $options[$key] !== $candidate) {
                            throw new RuntimeException('M57 original option lookup changed between instances.');
                        }
                        $options[$key] = $candidate;
                    } else {
                        if (isset($options[$key])) {
                            if (!isset($options[$key]['fields'])
                                || mb_strtolower($options[$key]['fields']['option']) !== mb_strtolower($token)) {
                                throw new RuntimeException('M57 absent/existing shared option state is mixed.');
                            }
                        } else { $options[$key] = ['key' => $key, 'fields' => ['option' => $token]]; }
                    }
                    $keys[$key] = true;
                    if ($n < count($question['tokens_correct'])) {
                        $actualAnswers[] = $options[$key]['row']['option'] ?? $options[$key]['fields']['option'];
                    }
                }
                if (ComposeTokenCase::normalize($actualAnswers) !== $question['tokens_correct']) {
                    throw new RuntimeException('M57 original option casing cannot reproduce the reviewed answer.');
                }
                $byMarker = [];
                foreach ($question['tokens_correct'] as $n => $token) {
                    $marker = 'a'.($n + 1); $byMarker[$marker] = [$token];
                    $out['question_answers'][] = ['key' => $uid.':'.$marker, 'question_key' => $uid,
                        'option_key' => 'option-'.hash('sha256', mb_strtolower($token)), 'fields' => ['marker' => $marker]];
                }
                $out['questions'][] = ['key' => $uid, 'fields' => ['uuid' => $uid, 'question' => $question['question'],
                    'difficulty' => 3, 'level' => 'B1', 'category_id' => self::CATEGORY_IDS[$i], 'source_id' => self::SOURCE_IDS[$i],
                    'flag' => 0, 'type' => '4',
                    'options_by_marker' => json_encode($byMarker, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR),
                    'theory_text_block_uuid' => null, 'seeder' => self::BANKS[$i]]];
                foreach (array_keys($keys) as $key) {
                    $out['question_option_question'][] = ['key' => $uid.':'.$key, 'question_key' => $uid,
                        'option_key' => $key, 'fields' => ['flag' => null]];
                }
                $out['question_theory_text_blocks'][] = ['key' => $uid, 'fields' => ['question_uuid' => $uid,
                    'text_block_uuid' => $binding['practice_uuid'], 'position' => 0]];
                $out['translations'][$uid] = ['en' => $question['target_text'], 'uk' => $question['translation_uk']];
                $out['provenance'][$uid] = ['source' => self::SOURCE, 'source_uid' => $uid, 'bank_seeder' => self::BANKS[$i],
                    'owner' => self::OWNERS[$i], 'question_sha256' => M26ContentPatch::digest($question)];
            }
        }
        $actual = array_keys($matches); $expected = array_keys($requested); sort($actual); sort($expected);
        if ($actual !== $expected) { throw new RuntimeException('M57 unrelated/missing option lookup tokens.'); }
        foreach ($options as $option) { $out[isset($option['id']) ? 'reused_options' : 'question_options'][] = $option; }
        return $out;
    }
}
