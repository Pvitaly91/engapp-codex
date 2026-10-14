<?php

namespace App\Support;

use App\Support\Database\QuestionUuidResolver;
use RuntimeException;

/** Pure finite row projection only. Never queries, seeds, updates or allocates database IDs. */
final class M51QuestionSupplement
{
    public const SOURCE = 'docs/content/m51-modal-meanings-widget-questions.v1.0.0.json';
    public const SOURCE_SHA = '9a8fd25f5e4f9c952a438aed2951dafbdb26551e52babf71aaeab2c09cd0a1e0';
    public const OWNERS = [
        'Database\\Seeders\\Page_V3\\ModalVerbs\\ModalVerbsDeductionTheorySeeder',
        'Database\\Seeders\\Page_V3\\ModalVerbs\\ModalVerbsPastModalsTheorySeeder',
        'Database\\Seeders\\Page_V3\\ModalVerbs\\ModalVerbsRequestsOffersSuggestionsTheorySeeder',
    ];
    public const BANKS = [
        'Database\\Seeders\\V3\\Polyglot\\PolyglotModalsOfDeductionThematicLessonSeeder',
        'Database\\Seeders\\V3\\Polyglot\\PolyglotPastModalsThematicLessonSeeder',
        'Database\\Seeders\\V3\\Polyglot\\PolyglotRequestsOffersSuggestionsThematicLessonSeeder',
    ];
    public const LEVELS = ['B1', 'B1', 'A2'];
    public const SLUGS = ['modals-of-deduction', 'past-modals', 'requests-offers-suggestions'];
    public const UID_PREFIXES = ['m51-ded-b1-', 'm51-past-b1-', 'm51-requests-a2-'];
    public const ROW_COLUMNS = [
        'questions' => ['id', 'uuid', 'question', 'difficulty', 'level', 'category_id', 'source_id', 'flag', 'type', 'options_by_marker', 'theory_text_block_uuid', 'seeder', 'created_at', 'updated_at'],
        'question_options' => ['id', 'option', 'created_at', 'updated_at'],
        'question_answers' => ['id', 'question_id', 'option_id', 'marker', 'created_at', 'updated_at'],
        'question_option_question' => ['id', 'question_id', 'option_id', 'flag'],
        'question_theory_text_blocks' => ['id', 'question_uuid', 'text_block_uuid', 'position', 'created_at', 'updated_at'],
    ];

    /** A draft may be previewed in memory, but cannot be loaded as an approved apply source. */
    public static function load(?string $root = null): array
    {
        if (preg_match('/^[a-f0-9]{64}$/D', self::SOURCE_SHA) !== 1) {
            throw new RuntimeException('M51 question supplement is not reviewed/frozen.');
        }
        $path = ($root ?? base_path()).'/'.self::SOURCE;
        if (!is_file($path) || is_link($path)) { throw new RuntimeException('M51 question source missing or linked.'); }
        $bytes = file_get_contents($path);
        if (!is_string($bytes) || !hash_equals(self::SOURCE_SHA, hash('sha256', $bytes))) {
            throw new RuntimeException('M51 frozen question source differs.');
        }
        $source = json_decode($bytes, true, flags: JSON_THROW_ON_ERROR);
        self::validate($source);
        if (!in_array($source['status'], ['approved', 'frozen'], true)) {
            throw new RuntimeException('M51 question source is not an approved release.');
        }

        return $source;
    }

    /** Finite editorial shape; source UID, theme, CEFR and the exact canonical word sequence are independent expectations. */
    public static function validate(array $source): void
    {
        if (($source['schema_version'] ?? null) !== 1 || ($source['package'] ?? null) !== 'm51-modal-meanings-widget-questions'
            || ($source['version'] ?? null) !== '1.0.0' || ($source['locale'] ?? null) !== 'uk'
            || !in_array($source['status'] ?? null, ['draft', 'approved', 'frozen'], true)
            || !is_array($source['banks'] ?? null) || !array_is_list($source['banks']) || count($source['banks']) !== 3) {
            throw new RuntimeException('M51 question source schema/version/scope differs.');
        }
        $seen = []; $resolver = new QuestionUuidResolver;
        foreach ($source['banks'] as $i => $bank) {
            if (($bank['owner'] ?? null) !== self::OWNERS[$i] || ($bank['bank_seeder'] ?? null) !== self::BANKS[$i]
                || ($bank['level'] ?? null) !== self::LEVELS[$i] || ($bank['type'] ?? null) !== 4
                || ($bank['local_url'] ?? null) !== 'http://gramlyze.loc/theory/modal-verbs/'.self::SLUGS[$i]
                || !is_array($bank['questions'] ?? null) || !array_is_list($bank['questions']) || count($bank['questions']) !== 5) {
                throw new RuntimeException('M51 exact thematic five-question bank differs.');
            }
            foreach ($bank['questions'] as $n => $question) {
                $uid = self::UID_PREFIXES[$i].str_pad((string) ($n + 1), 2, '0', STR_PAD_LEFT);
                if (($question['source_uid'] ?? null) !== $uid || isset($seen[$uid]) || $resolver->toPersistent($uid) !== $uid) {
                    throw new RuntimeException('M51 stable question UID/order differs.');
                }
                $seen[$uid] = true;
                foreach (['question_uk', 'context_uk', 'criterion_uk', 'translation_uk', 'ambiguity_note_uk'] as $field) {
                    if (!is_string($question[$field] ?? null) || trim($question[$field]) !== $question[$field]
                        || preg_match('/[\x{0400}-\x{04FF}]/u', $question[$field]) !== 1
                        || preg_match('/<[^>]+>/', $question[$field]) === 1) {
                        throw new RuntimeException('M51 visible Ukrainian question/translation/criterion is missing.');
                    }
                }
                $tokens = self::tokens($question['tokens_correct'] ?? null);
                $shuffled = self::tokens($question['tokens_shuffled'] ?? null);
                $left = $tokens; $right = $shuffled; sort($left); sort($right);
                if ($left !== $right || $tokens === $shuffled || count($tokens) < 4
                    || !is_string($question['canonical_en'] ?? null)
                    || self::compose($tokens) !== $question['canonical_en']
                    || ComposeTokenCase::normalize($tokens) !== $tokens
                    || !is_array($question['distractors'] ?? null) || !array_is_list($question['distractors'])
                    || !is_array($question['learning_requirements'] ?? null) || $question['learning_requirements'] === []) {
                    throw new RuntimeException('M51 canonical/full-word/repeated-token contract differs.');
                }
                foreach ($question['distractors'] as $token) { self::tokens([$token]); }
                if (strlen($question['question_uk']) > 65535) { throw new RuntimeException('M51 question exceeds SQL TEXT capacity.'); }
            }
        }
    }

    /**
     * $bindings are fresh confirmed per-owner category/source/practice UUID references, not auto-created taxonomy.
     * $optionMatches[token] is the complete fresh SQL collation-equality result (zero or one exact original row).
     * Returned references remain symbolic until the caller supplies reviewed IDs; every family is insert-only.
     */
    public static function project(array $source, array $bindings, array $optionMatches): array
    {
        self::validate($source);
        if (count($bindings) !== 3 || !array_is_list($bindings)) { throw new RuntimeException('M51 question bindings differ.'); }
        $projection = ['questions' => [], 'question_options' => [], 'question_answers' => [], 'question_option_question' => [],
            'question_theory_text_blocks' => [], 'reused_options' => [], 'translations' => [], 'provenance' => []];
        $options = []; $seenOptionIds = []; $seenPractice = []; $requestedTokens = [];
        foreach ($source['banks'] as $i => $bank) {
            $binding = $bindings[$i];
            if (($binding['owner'] ?? null) !== self::OWNERS[$i] || ($binding['level'] ?? null) !== self::LEVELS[$i]
                || !is_int($binding['category_id'] ?? null) || $binding['category_id'] < 1
                || (!is_int($binding['source_id'] ?? null) && ($binding['source_id'] ?? null) !== null)
                || (is_int($binding['source_id']) && $binding['source_id'] < 1)
                || !is_string($binding['practice_uuid'] ?? null) || preg_match('/^[a-f0-9-]{36}$/D', $binding['practice_uuid']) !== 1
                || isset($seenPractice[$binding['practice_uuid']])) { throw new RuntimeException('M51 fresh question/practice binding differs.'); }
            $seenPractice[$binding['practice_uuid']] = true;
            foreach ($bank['questions'] as $question) {
                $uid = $question['source_uid']; $correct = $question['tokens_correct']; $answerOptions = []; $optionKeys = [];
                foreach ([...$correct, ...$question['distractors']] as $token) {
                    $requestedTokens[$token] = true;
                    if (!array_key_exists($token, $optionMatches) || !is_array($optionMatches[$token])
                        || !array_is_list($optionMatches[$token]) || count($optionMatches[$token]) > 1) {
                        throw new RuntimeException('M51 complete fresh option collision lookup is missing/ambiguous.');
                    }
                    $key = 'option-'.hash('sha256', mb_strtolower($token));
                    $matches = $optionMatches[$token];
                    if ($matches !== []) {
                        $row = $matches[0]; self::assertColumns('question_options', $row);
                        if (!is_int($row['id']) || $row['id'] < 1 || mb_strtolower($row['option']) !== mb_strtolower($token)) {
                            throw new RuntimeException('M51 reused option ID/value collision differs.');
                        }
                        if (isset($seenOptionIds[$row['id']]) && $seenOptionIds[$row['id']] !== $key) {
                            throw new RuntimeException('M51 different token keys reuse the same option ID.');
                        }
                        $seenOptionIds[$row['id']] = $key;
                        $candidate = ['key' => $key, 'id' => $row['id'], 'row' => $row, 'sha256' => self::digest($row)];
                        if (isset($options[$key]) && $options[$key] !== $candidate) { throw new RuntimeException('M51 option reuse lookup changed between instances.'); }
                        $options[$key] = $candidate;
                    } else {
                        $candidate = ['key' => $key, 'fields' => ['option' => $token]];
                        if (isset($options[$key])) {
                            if (!isset($options[$key]['fields']) || mb_strtolower($options[$key]['fields']['option']) !== mb_strtolower($token)) {
                                throw new RuntimeException('M51 absent/existing shared option state is mixed.');
                            }
                        } else { $options[$key] = $candidate; }
                    }
                    $optionKeys[$key] = true;
                    if (count($answerOptions) < count($correct)) { $answerOptions[] = $options[$key]['row']['option'] ?? $options[$key]['fields']['option']; }
                }
                if (ComposeTokenCase::normalize($answerOptions) !== $correct) {
                    throw new RuntimeException('M51 shared option casing cannot reproduce the approved compose sequence.');
                }
                $byMarker = [];
                foreach ($correct as $n => $token) {
                    $marker = 'a'.($n + 1); $byMarker[$marker] = [$token];
                    $projection['question_answers'][] = ['key' => $uid.':'.$marker, 'question_key' => $uid,
                        'option_key' => 'option-'.hash('sha256', mb_strtolower($token)), 'fields' => ['marker' => $marker]];
                }
                $projection['questions'][] = ['key' => $uid, 'fields' => ['uuid' => (new QuestionUuidResolver)->toPersistent($uid),
                    'question' => $question['question_uk'], 'difficulty' => self::LEVELS[$i] === 'B1' ? 3 : 2,
                    'level' => self::LEVELS[$i], 'category_id' => $binding['category_id'], 'source_id' => $binding['source_id'], 'flag' => 0,
                    'type' => '4', 'options_by_marker' => json_encode($byMarker, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR),
                    'theory_text_block_uuid' => null, 'seeder' => self::BANKS[$i]]];
                foreach (array_keys($optionKeys) as $key) {
                    $projection['question_option_question'][] = ['key' => $uid.':'.$key, 'question_key' => $uid, 'option_key' => $key, 'fields' => ['flag' => null]];
                }
                $projection['question_theory_text_blocks'][] = ['key' => $uid, 'fields' => ['question_uuid' => $uid,
                    'text_block_uuid' => $binding['practice_uuid'], 'position' => 0]];
                $projection['translations'][$uid] = ['en' => $question['canonical_en'], 'uk' => $question['translation_uk']];
                $projection['provenance'][$uid] = ['source' => self::SOURCE, 'source_uid' => $uid, 'bank_seeder' => self::BANKS[$i],
                    'owner' => self::OWNERS[$i], 'question_sha256' => self::digest($question)];
            }
        }
        $actualKeys = array_keys($optionMatches); $expectedKeys = array_keys($requestedTokens); sort($actualKeys); sort($expectedKeys);
        if ($actualKeys !== $expectedKeys) { throw new RuntimeException('M51 option lookup has unrelated or missing tokens.'); }
        foreach ($options as $option) { $projection[isset($option['id']) ? 'reused_options' : 'question_options'][] = $option; }

        return $projection;
    }

    /** Supply the exact caller-reviewed ID maps and one transaction timestamp; original rows are never materialized for update. */
    public static function materialize(array $projection, array $ids, string $timestamp): array
    {
        self::assertTimestamp($timestamp);
        $rows = []; $questionIds = $ids['questions'] ?? []; $optionIds = $ids['question_options'] ?? [];
        foreach ($projection['reused_options'] as $option) {
            if (isset($optionIds[$option['key']])) { throw new RuntimeException('M51 new option ID map includes a reused original.'); }
            $optionIds[$option['key']] = $option['id'];
        }
        foreach (self::ROW_COLUMNS as $table => $_) {
            $expectedKeys = array_column($projection[$table], 'key'); $actualKeys = array_keys($ids[$table] ?? []);
            sort($expectedKeys); sort($actualKeys);
            if ($expectedKeys !== $actualKeys) { throw new RuntimeException('M51 exact new-row ID inventory differs: '.$table); }
            $seen = [];
            foreach ($projection[$table] as $record) {
                $id = $ids[$table][$record['key']];
                if (!is_int($id) || $id < 1 || isset($seen[$id])) { throw new RuntimeException('M51 new row ID is invalid/duplicated.'); }
                $seen[$id] = true; $row = ['id' => $id] + $record['fields'];
                if (isset($record['question_key'])) { $row['question_id'] = $questionIds[$record['question_key']]; }
                if (isset($record['option_key'])) { $row['option_id'] = $optionIds[$record['option_key']]; }
                if ($table !== 'question_option_question') { $row += ['created_at' => $timestamp, 'updated_at' => $timestamp]; }
                self::assertColumns($table, $row); $ordered = [];
                foreach (self::ROW_COLUMNS[$table] as $column) { $ordered[$column] = $row[$column]; }
                $rows[$table][] = $ordered;
            }
            $rows[$table] ??= [];
        }
        foreach ($projection['reused_options'] as $option) {
            foreach ($rows['question_options'] as $row) { if ($row['id'] === $option['id']) { throw new RuntimeException('M51 new option ID collides with a reused original.'); } }
        }

        return $rows;
    }

    /** Collision queries are performed by the caller; any scoped hit before insertion refuses the supplement. */
    public static function assertAbsent(array $candidateHits): void
    {
        $keys = array_keys($candidateHits); $expected = array_keys(self::ROW_COLUMNS); sort($keys); sort($expected);
        if ($keys !== $expected) { throw new RuntimeException('M51 absence collision inventory is incomplete.'); }
        foreach ($candidateHits as $rows) { if ($rows !== []) { throw new RuntimeException('M51 new question/row/UUID/bank collision; no updates permitted.'); } }
    }

    /** Exact caller-scoped inserted rows, IDs, relation multiplicity and timestamps; no excluded metadata fields. */
    public static function assertApplied(array $expected, array $actual): void
    {
        $tables = array_keys(self::ROW_COLUMNS); $expectedTables = array_keys($expected); $actualTables = array_keys($actual);
        sort($tables); sort($expectedTables); sort($actualTables);
        if ($tables !== $expectedTables || $tables !== $actualTables) { throw new RuntimeException('M51 exact applied family scope differs.'); }
        foreach (self::ROW_COLUMNS as $table => $_) {
            if (!array_key_exists($table, $expected) || !array_key_exists($table, $actual)) { throw new RuntimeException('M51 applied row inventory is incomplete.'); }
            foreach ($actual[$table] as $row) {
                self::assertColumns($table, $row);
                if ($table !== 'question_option_question') {
                    self::assertTimestamp($row['created_at']);
                    if ($row['created_at'] !== $row['updated_at']) { throw new RuntimeException('M51 new question row timestamps were edited.'); }
                }
            }
            $before = $expected[$table]; $after = $actual[$table];
            usort($before, static fn ($a, $b) => $a['id'] <=> $b['id']); usort($after, static fn ($a, $b) => $a['id'] <=> $b['id']);
            if ($before !== $after) { throw new RuntimeException('M51 exact inserted question family differs: '.$table); }
        }
    }

    public static function assertOriginalsUnchanged(array $before, array $after): void
    {
        if ($before !== $after) { throw new RuntimeException('M51 original question/options/answers/links/taxonomy/progress changed.'); }
    }

    public static function digest(array $value): string { return \App\Services\M26ContentPatch::digest($value); }

    private static function tokens(mixed $tokens): array
    {
        if (!is_array($tokens) || !array_is_list($tokens) || $tokens === []) { throw new RuntimeException('M51 complete token list missing.'); }
        foreach ($tokens as $token) {
            if (!is_string($token) || $token === '' || trim($token) !== $token || strlen($token) > 255
                || preg_match('/^(?:[A-Za-z]+(?:[\x{2019}\x{0027}][A-Za-z]+)?|[,.!?;:])$/uD', $token) !== 1) {
                throw new RuntimeException('M51 token is not one complete English word or punctuation.');
            }
        }

        return $tokens;
    }

    private static function compose(array $tokens): string
    {
        return preg_replace('/\s+([?.!,;:])/', '$1', implode(' ', $tokens));
    }

    private static function assertColumns(string $table, array $row): void
    {
        $actual = array_keys($row); $expected = self::ROW_COLUMNS[$table]; sort($actual); sort($expected);
        if ($actual !== $expected) { throw new RuntimeException('M51 inspected row columns differ: '.$table); }
    }

    private static function assertTimestamp(mixed $timestamp): void
    {
        if (!is_string($timestamp) || preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/D', $timestamp) !== 1) {
            throw new RuntimeException('M51 explicit new-row timestamp differs.');
        }
    }
}
