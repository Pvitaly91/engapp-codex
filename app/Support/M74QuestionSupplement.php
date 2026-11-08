<?php

namespace App\Support;

use App\Services\M26ContentPatch;
use RuntimeException;

/** Pure one-question insert projection; no SQL, allocation, original-model mutation or apply. */
final class M74QuestionSupplement
{
    public const SOURCE = 'docs/content/m74-another-bank-completion.v1.0.1.json';
    public const SOURCE_SHA = '06f867aa942ca27c8c2f2b8fbba8a0b2810f845303c9ed6e194f46d43b84bbe0';
    public const OWNER = 'Database\\Seeders\\Page_V3\\NounsArticlesQuantity\\NounsArticlesQuantityAnotherOtherTheOtherTheorySeeder';
    public const OLD_BANK = 'Database\\Seeders\\V3\\Polyglot\\PolyglotAnotherOtherTheOtherAllLevelsLessonSeeder';
    public const BANK = 'Database\\Seeders\\V3\\Polyglot\\M74AnotherRemainderSupplementSeeder';
    public const UUID = 'm74-another-b1-remainder-01';
    public const UUIDS = [self::UUID];
    public const LEVEL = 'B1';
    public const PAGE_ID = 81;
    public const PAGE_CATEGORY_ID = 16;
    public const CATEGORY_ID = 45;
    public const SOURCE_ID = 321;
    public const PRACTICE_UUID = '19b7df8b-7c55-5725-a78f-f5935f988baa';
    public const ROW_COLUMNS = M51QuestionSupplement::ROW_COLUMNS;
    public const TOKENS = ['The', 'others', 'are', 'outside'];
    public const DISTRACTORS = ['other', 'another', 'is', 'them'];
    public const QUESTION_UK = 'У відомій групі п’ятеро учасників. Двоє вже всередині; усі троє, що залишилися, надворі. Скажи: Решта надворі.';
    public const ALTERNATIVE_UK = 'Тобі принесли чашку надто солодкого чаю. Ти не питимеш цю порцію й просиш іншу замість неї, а не додаткову до випитої. Почни з модального дієслова: Можна мені іншу чашку чаю?';

    public static function load(?string $root = null): array
    {
        $path = ($root ?? base_path()).'/'.self::SOURCE;
        if (!is_file($path) || is_link($path) || !hash_equals(self::SOURCE_SHA, hash_file('sha256', $path))) {
            throw new RuntimeException('M74 one-question source is missing, linked or not the reviewed raw bytes.');
        }
        $source = json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
        self::validate($source);
        if ($source['status'] !== 'frozen') { throw new RuntimeException('M74 one-question source is not frozen.'); }
        return $source;
    }

    public static function validate(array $source): void
    {
        if (($source['schema_version'] ?? null) !== 1 || ($source['package'] ?? null) !== 'm74-another-bank-completion'
            || ($source['version'] ?? null) !== '1.0.1' || !in_array($source['status'] ?? null, ['draft', 'frozen'], true)
            || ($source['locale'] ?? null) !== 'uk' || ($source['seeder']['class'] ?? null) !== self::BANK
            || ($source['scope']['page_identity'] ?? null) !== self::OWNER || ($source['scope']['page_id'] ?? null) !== self::PAGE_ID
            || ($source['scope']['page_category_id'] ?? null) !== self::PAGE_CATEGORY_ID || ($source['scope']['locale'] ?? null) !== 'uk'
            || ($source['scope']['practice_uuid'] ?? null) !== self::PRACTICE_UUID || ($source['scope']['practice_level'] ?? null) !== self::LEVEL
            || ($source['scope']['local_url'] ?? null) !== 'http://gramlyze.loc/theory/imennyky-artykli-ta-kilkist/another-other-the-other'
            || !is_array($source['questions'] ?? null) || !array_is_list($source['questions']) || count($source['questions']) !== 1) {
            throw new RuntimeException('M74 one-question source exact identity/version/practice scope differs.');
        }
        foreach (['new_question_rows' => 1, 'new_option_rows' => 0, 'new_answer_rows' => 4, 'new_option_pivot_rows' => 8,
            'new_theory_link_rows' => 1, 'practice_body_changes' => 1, 'teaching_body_changes' => 0,
            'old_question_updates' => 0, 'old_option_updates' => 0, 'old_answer_updates' => 0, 'old_link_updates_or_deletes' => 0,
            'new_saved_tests' => 0, 'question_tag_rows' => 0, 'question_marker_tag_rows' => 0, 'question_hint_rows' => 0,
            'verb_hint_rows' => 0, 'question_variant_rows' => 0, 'progress_writes' => 0, 'formatter_changes' => 0, 'grader_changes' => 0] as $key => $count) {
            if (($source['scope'][$key] ?? null) !== $count) { throw new RuntimeException('M74 one-question write scope differs: '.$key); }
        }
        $taxonomy = $source['existing_taxonomy'] ?? [];
        if (($taxonomy['captured_question_id'] ?? null) !== 32707 || ($taxonomy['captured_question_uuid'] ?? null) !== 'aot-poly-b1-05'
            || ($taxonomy['difficulty'] ?? null) !== 3 || ($taxonomy['level'] ?? null) !== self::LEVEL
            || ($taxonomy['question_category_id'] ?? null) !== self::CATEGORY_ID || ($taxonomy['source_id'] ?? null) !== self::SOURCE_ID
            || ($taxonomy['flag'] ?? null) !== 0 || ($taxonomy['type'] ?? null) !== '4' || ($taxonomy['bank_seeder'] ?? null) !== self::OLD_BANK) {
            throw new RuntimeException('M74 independently captured question taxonomy differs from Page taxonomy.');
        }
        $question = $source['questions'][0];
        foreach (['question', 'translation_uk', 'context_uk', 'criterion_uk', 'ambiguity_note_uk'] as $field) {
            if (!is_string($question[$field] ?? null) || trim($question[$field]) !== $question[$field]
                || preg_match('/[\x{0400}-\x{04FF}]/u', $question[$field]) !== 1 || preg_match('/<[^>]+>/', $question[$field]) === 1) {
                throw new RuntimeException('M74 complete plain Ukrainian question/meaning review is required.');
            }
        }
        if (($question['uuid'] ?? null) !== self::UUID || ($question['source_uid'] ?? null) !== self::UUID
            || ($question['level'] ?? null) !== self::LEVEL || ($question['type'] ?? null) !== 4
            || $question['question'] !== self::QUESTION_UK || $question['translation_uk'] !== 'Решта надворі.'
            || ($question['tokens_correct'] ?? null) !== self::TOKENS || ($question['distractors'] ?? null) !== self::DISTRACTORS
            || ($question['tokens_shuffled'] ?? null) !== ['outside', 'are', 'The', 'others']
            || ($question['word_key'] ?? null) !== 'The others are outside' || ($question['canonical_en'] ?? null) !== 'The others are outside.'
            || ($question['answers'] ?? null) !== ['a1' => 'The', 'a2' => 'others', 'a3' => 'are', 'a4' => 'outside']
            || ($question['options'] ?? null) !== [...self::TOKENS, ...self::DISTRACTORS]
            || ($question['hints'] ?? null) !== [] || ($question['verb_hints'] ?? null) !== [] || ($question['variants'] ?? null) !== []) {
            throw new RuntimeException('M74 exact complete native remainder question/token contract differs.');
        }
        $revision = $source['selection_revision'] ?? [];
        $clone = $revision['existing_question_clone_revision'] ?? [];
        if (($revision['bank_seeders'] ?? null) !== [self::OLD_BANK, self::BANK]
            || ($revision['before_selected_ids'] ?? null) !== [32707, 32708, 32711, 32712, 32714]
            || ($revision['after_reused_ids'] ?? null) !== [32707, 32708, 32711, 32714]
            || ($revision['after_new_uuid'] ?? null) !== self::UUID || ($revision['removed_from_selection_only'] ?? null) !== 32712
            || ($revision['after_uuid_order'] ?? null) !== ['aot-poly-b1-05', 'aot-poly-b1-06', 'aot-poly-b1-09', 'aot-poly-b1-12', self::UUID]
            || ($clone['id'] ?? null) !== 32711 || ($clone['uuid'] ?? null) !== 'aot-poly-b1-09'
            || ($clone['new_visible_condition_uk'] ?? null) !== self::ALTERNATIVE_UK
            || ($clone['translation_uk'] ?? null) !== 'Можна мені іншу чашку чаю?'
            || ($clone['unchanged_word_key'] ?? null) !== 'Can I have another cup of tea'
            || ($clone['unchanged_native_visible_en'] ?? null) !== 'Can I have another cup of tea?'
            || ($clone['original_row_writes'] ?? null) !== 0 || ($clone['original_key_option_answer_changes'] ?? null) !== 0
            || ($clone['render_only_condition'] ?? null) !== true || !str_ends_with($clone['new_visible_condition_uk'], '?')) {
            throw new RuntimeException('M74 finite additional/alternative/remainder selection revision differs.');
        }
    }

    /** Caller supplies complete fresh collation-equality rows; no lookup, allocation or fallback is performed here. */
    public static function project(array $source, array $binding, array $matches): array
    {
        self::validate($source);
        $expectedBinding = ['owner' => self::OWNER, 'level' => self::LEVEL, 'category_id' => self::CATEGORY_ID,
            'source_id' => self::SOURCE_ID, 'practice_uuid' => self::PRACTICE_UUID];
        if ($binding !== $expectedBinding) { throw new RuntimeException('M74 exact fresh question-taxonomy/practice binding differs.'); }
        $requested = [...self::TOKENS, ...self::DISTRACTORS]; $keys = array_keys($matches); $expectedKeys = $requested;
        sort($keys); sort($expectedKeys);
        if ($keys !== $expectedKeys) { throw new RuntimeException('M74 option lookup contains unrelated or missing tokens.'); }
        $out = array_fill_keys(array_keys(self::ROW_COLUMNS), []);
        $out += ['reused_options' => [], 'translations' => [], 'provenance' => []];
        $seenIds = []; $answerOptions = [];
        foreach ($requested as $n => $token) {
            $lookup = $matches[$token];
            if (!is_array($lookup) || !array_is_list($lookup) || count($lookup) !== 1 || !is_array($lookup[0])) {
                throw new RuntimeException('M74 requires exactly one existing complete collation match per token; new options forbidden.');
            }
            $row = $lookup[0]; $columns = array_keys($row); $expectedColumns = self::ROW_COLUMNS['question_options'];
            sort($columns); sort($expectedColumns);
            if ($columns !== $expectedColumns || !is_int($row['id']) || $row['id'] < 1 || !is_string($row['option'])
                || mb_strtolower($row['option']) !== mb_strtolower($token) || isset($seenIds[$row['id']])) {
                throw new RuntimeException('M74 reused option row/collation identity is invalid or duplicated.');
            }
            $seenIds[$row['id']] = true; $key = 'option-'.hash('sha256', mb_strtolower($token));
            $out['reused_options'][] = ['key' => $key, 'id' => $row['id'], 'row' => $row, 'sha256' => M26ContentPatch::digest($row)];
            $out['question_option_question'][] = ['key' => self::UUID.':'.$key, 'question_key' => self::UUID,
                'option_key' => $key, 'fields' => ['flag' => null]];
            if ($n < count(self::TOKENS)) {
                $answerOptions[] = $row['option']; $marker = 'a'.($n + 1);
                $out['question_answers'][] = ['key' => self::UUID.':'.$marker, 'question_key' => self::UUID,
                    'option_key' => $key, 'fields' => ['marker' => $marker]];
            }
        }
        if (ComposeTokenCase::normalize($answerOptions) !== self::TOKENS) {
            throw new RuntimeException('M74 unchanged original option casing cannot reproduce the complete canonical answer.');
        }
        $byMarker = []; foreach (self::TOKENS as $n => $token) { $byMarker['a'.($n + 1)] = [$token]; }
        $out['questions'][] = ['key' => self::UUID, 'fields' => ['uuid' => self::UUID,
            'question' => $source['questions'][0]['question'], 'difficulty' => 3, 'level' => self::LEVEL,
            'category_id' => self::CATEGORY_ID, 'source_id' => self::SOURCE_ID, 'flag' => 0, 'type' => '4',
            'options_by_marker' => json_encode($byMarker, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR),
            'theory_text_block_uuid' => null, 'seeder' => self::BANK]];
        $out['question_theory_text_blocks'][] = ['key' => self::UUID, 'fields' => ['question_uuid' => self::UUID,
            'text_block_uuid' => self::PRACTICE_UUID, 'position' => 0]];
        $out['translations'][self::UUID] = ['en' => 'The others are outside.', 'uk' => 'Решта надворі.'];
        $out['provenance'][self::UUID] = ['source' => self::SOURCE, 'source_uid' => self::UUID,
            'bank_seeder' => self::BANK, 'owner' => self::OWNER, 'question_sha256' => M26ContentPatch::digest($source['questions'][0])];
        return $out;
    }
}
