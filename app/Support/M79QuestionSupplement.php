<?php

namespace App\Support;

use App\Services\M26ContentPatch;
use RuntimeException;

/** Finite eight-question source and symbolic insert projection; no SQL, allocation or updates. */
final class M79QuestionSupplement
{
    public const SOURCE = 'docs/content/m79-question-bank-supplement.v1.json';
    public const SOURCE_SHA = '0c46dd5adcea37bcde0be929a4095ea24e3a818f622707fe2fcf69440a9a94e6';
    public const BASE_SHA = 'f33d1a0e2eff08e66044d9b746a46bc33f207bce';
    public const TARGET_INDICES = [2, 4, 5];
    public const OWNERS = [
        'Database\\Seeders\\Page_V3\\QuestionsNegations\\TypesOfQuestions\\TypesOfQuestionsNegativeQuestionsTheorySeeder',
        'Database\\Seeders\\Page_V3\\QuestionsNegations\\TypesOfQuestions\\TypesOfQuestionsEmbeddedQuestionsTheorySeeder',
        'Database\\Seeders\\Page_V3\\QuestionsNegations\\TypesOfQuestions\\TypesOfQuestionsIndirectQuestionsTheorySeeder',
    ];
    public const BANKS = [
        'Database\\Seeders\\V3\\Polyglot\\M79NegativeFactsSupplementSeeder',
        'Database\\Seeders\\V3\\Polyglot\\M79EmbeddedStructureSupplementSeeder',
        'Database\\Seeders\\V3\\Polyglot\\M79IndirectInformationSupplementSeeder',
    ];
    public const OLD_BANKS = [
        'Database\\Seeders\\V3\\Polyglot\\PolyglotNegativeQuestionsAllLevelsLessonSeeder',
        'Database\\Seeders\\V3\\Polyglot\\PolyglotEmbeddedQuestionsAllLevelsLessonSeeder',
        'Database\\Seeders\\V3\\Polyglot\\PolyglotIndirectQuestionsAllLevelsLessonSeeder',
    ];
    public const PAGE_IDS = [136, 134, 135];
    public const HERO_IDS = [4361, 4319, 4340];
    public const HERO_UUIDS = ['7118da16-2d63-5321-92ff-d3ca7a8b8739', 'b35614f3-6872-50d1-a33f-b247edcdd223', 'b22e897f-439e-5c16-8b16-0d31c2ee76d7'];
    public const SOURCE_IDS = [337, 338, 339];
    public const SLUGS = ['negative-questions-dont-you-know', 'embedded-questions', 'indirect-questions'];
    public const UUIDS = ['m79-neg-b1-01', 'm79-neg-b1-02', 'm79-emb-b1-01', 'm79-emb-b1-02', 'm79-emb-b1-03', 'm79-ind-b1-01', 'm79-ind-b1-02', 'm79-ind-b1-03'];
    public const ROW_COLUMNS = M51QuestionSupplement::ROW_COLUMNS;

    public static function load(?string $root = null): array
    {
        $path = ($root ?? base_path()).'/'.self::SOURCE;
        if (preg_match('/^[a-f0-9]{64}$/D', self::SOURCE_SHA) !== 1 || !is_file($path) || is_link($path)
            || !hash_equals(self::SOURCE_SHA, hash_file('sha256', $path))) {
            throw new RuntimeException('M79 question source is not reviewed/frozen or its exact bytes differ.');
        }
        $source = json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
        self::validate($source);
        if ($source['status'] !== 'frozen') { throw new RuntimeException('M79 apply source must be frozen.'); }
        return $source;
    }

    public static function bindings(): array
    {
        $out = [];
        foreach (self::OWNERS as $i => $owner) {
            $out[] = ['owner' => $owner, 'page_id' => self::PAGE_IDS[$i], 'page_category_id' => 22,
                'level' => 'B1', 'category_id' => 45, 'source_id' => self::SOURCE_IDS[$i],
                'theory_block_id' => self::HERO_IDS[$i], 'theory_block_uuid' => self::HERO_UUIDS[$i]];
        }
        return $out;
    }

    /** Full task-bound context is visible before Check, not hidden in author-only metadata. */
    public static function condition(array $question): string
    {
        foreach (['question_uk', 'context_uk', 'criterion_uk', 'ambiguity_note_uk'] as $field) {
            if (!is_string($question[$field] ?? null) || trim($question[$field]) === '') { throw new RuntimeException('M79 visible condition is incomplete.'); }
        }
        $text = $question['question_uk'].' '.$question['context_uk'].' '.$question['criterion_uk'].' '.$question['ambiguity_note_uk'];
        if (strlen($text) > 65535) { throw new RuntimeException('M79 combined visible condition exceeds SQL TEXT capacity.'); }
        return $text;
    }

    public static function validate(array $source): void
    {
        if (($source['schema_version'] ?? null) !== 1 || ($source['package'] ?? null) !== 'm79-question-bank-supplement'
            || ($source['version'] ?? null) !== '1.0.0' || ($source['locale'] ?? null) !== 'uk'
            || !in_array($source['status'] ?? null, ['draft', 'frozen'], true)
            || !is_array($source['banks'] ?? null) || !array_is_list($source['banks']) || count($source['banks']) !== 3) {
            throw new RuntimeException('M79 finite question source schema/version/scope differs.');
        }
        $all = []; $conditions = []; $canonical = [];
        foreach ($source['banks'] as $i => $bank) {
            $count = [2, 3, 3][$i]; $prefix = ['m79-neg-b1-', 'm79-emb-b1-', 'm79-ind-b1-'][$i];
            if (($bank['owner'] ?? null) !== self::OWNERS[$i] || ($bank['bank_seeder'] ?? null) !== self::BANKS[$i]
                || ($bank['level'] ?? null) !== 'B1' || ($bank['type'] ?? null) !== 4
                || ($bank['local_url'] ?? null) !== 'http://gramlyze.loc/theory/pytalni-rechennia-ta-zaperechennia/types-of-questions/'.self::SLUGS[$i]
                || !is_array($bank['questions'] ?? null) || !array_is_list($bank['questions']) || count($bank['questions']) !== $count) {
                throw new RuntimeException('M79 owner/bank/level/URL/question cardinality differs.');
            }
            foreach ($bank['questions'] as $n => $q) {
                $uid = $prefix.str_pad((string) ($n + 1), 2, '0', STR_PAD_LEFT);
                if (($q['source_uid'] ?? null) !== $uid || (new Database\QuestionUuidResolver)->toPersistent($uid) !== $uid) {
                    throw new RuntimeException('M79 stable question UID or order differs.');
                }
                $all[] = $uid;
                foreach (['question_uk', 'context_uk', 'criterion_uk', 'translation_uk', 'ambiguity_note_uk'] as $field) {
                    $text = $q[$field] ?? null;
                    if (!is_string($text) || trim($text) !== $text || strlen($text) > 65535
                        || preg_match('/[\x{0400}-\x{04FF}]/u', $text) !== 1
                        || preg_match('/<[^>]+>|&(?:#[0-9]+|#x[0-9a-f]+|[a-z][a-z0-9]+);|\{a[0-9]+\}/iu', $text)) {
                        throw new RuntimeException('M79 Ukrainian visible condition/context/criterion/translation is invalid.');
                    }
                }
                $correct = self::tokens($q['tokens_correct'] ?? null); $shuffled = self::tokens($q['tokens_shuffled'] ?? null);
                $left = $correct; $right = $shuffled; sort($left); sort($right);
                $en = $q['canonical_en'] ?? null;
                $minimum = $i === 0 && $n === 0 ? 3 : 4;
                if ($left !== $right || $correct === $shuffled || count($correct) < $minimum
                    || !is_string($en) || self::compose($correct) !== $en || ComposeTokenCase::normalize($correct) !== $correct
                    || !is_array($q['distractors'] ?? null) || !array_is_list($q['distractors'])
                    || !is_array($q['learning_requirements'] ?? null) || !array_is_list($q['learning_requirements'])
                    || $q['learning_requirements'] === [] || isset($conditions[$q['question_uk']]) || isset($canonical[$en])) {
                    throw new RuntimeException('M79 canonical/shuffle/case/multiplicity/unique question contract differs.');
                }
                $conditions[$q['question_uk']] = true; $canonical[$en] = true;
                if ($q['distractors'] !== []) { self::tokens($q['distractors']); }
                foreach ($q['learning_requirements'] as $requirement) {
                    if (!is_string($requirement) || trim($requirement) === '') { throw new RuntimeException('M79 semantic requirement is empty.'); }
                }
            }
        }
        if ($all !== self::UUIDS) { throw new RuntimeException('M79 complete ordered eight-question scope differs.'); }
    }

    /** Educational diagnostics only: review every original SQL option label before refusing any case mismatch. */
    public static function optionCaseReview(array $source, array $matches): array
    {
        self::validate($source); $out = [];
        foreach ($source['banks'] as $bank) {
            foreach ($bank['questions'] as $q) {
                $labels = [];
                foreach ($q['tokens_correct'] as $token) {
                    $lookup = $matches[$token] ?? null;
                    if (!is_array($lookup) || !array_is_list($lookup) || count($lookup) > 1) { throw new RuntimeException('M79 case review requires complete unambiguous option lookups.'); }
                    $labels[] = $lookup === [] ? $token : $lookup[0]['option'];
                }
                $normalized = ComposeTokenCase::normalize($labels); $visible = self::compose($normalized);
                $out[] = ['source_uid' => $q['source_uid'], 'stored_or_new_labels' => $labels, 'normalized_tokens' => $normalized,
                    'expected_tokens' => $q['tokens_correct'], 'visible_en' => $visible, 'expected_en' => $q['canonical_en'],
                    'exact' => $normalized === $q['tokens_correct'] && $visible === $q['canonical_en']];
            }
        }
        return $out;
    }

    /** Fresh full SQL-collation matches; only genuinely absent options become symbolic inserts. */
    public static function project(array $source, array $bindings, array $matches): array
    {
        self::validate($source);
        if ($bindings !== self::bindings()) { throw new RuntimeException('M79 exact captured hero/taxonomy bindings differ.'); }
        $out = array_fill_keys(array_keys(self::ROW_COLUMNS), []);
        $out += ['reused_options' => [], 'translations' => [], 'provenance' => []];
        $options = []; $seenIds = []; $requested = [];
        foreach ($source['banks'] as $i => $bank) {
            foreach ($bank['questions'] as $q) {
                $uid = $q['source_uid']; $keys = []; $actualAnswers = []; $correct = $q['tokens_correct'];
                foreach ([...$correct, ...$q['distractors']] as $n => $token) {
                    $requested[$token] = true; $key = 'option-'.hash('sha256', mb_strtolower($token));
                    $lookup = $matches[$token] ?? null;
                    if (!is_array($lookup) || !array_is_list($lookup) || count($lookup) > 1) {
                        throw new RuntimeException('M79 missing/ambiguous complete SQL-collation option lookup.');
                    }
                    if ($lookup !== []) {
                        $row = $lookup[0]; $columns = array_keys($row); $expected = self::ROW_COLUMNS['question_options'];
                        sort($columns); sort($expected);
                        if ($columns !== $expected || !is_int($row['id']) || $row['id'] < 1 || !is_string($row['option'])
                            || mb_strtolower($row['option']) !== mb_strtolower($token)
                            || (isset($seenIds[$row['id']]) && $seenIds[$row['id']] !== $key)) {
                            throw new RuntimeException('M79 reused option identity/value/collision differs.');
                        }
                        $seenIds[$row['id']] = $key;
                        $candidate = ['key' => $key, 'id' => $row['id'], 'row' => $row, 'sha256' => M26ContentPatch::digest($row)];
                    } else { $candidate = ['key' => $key, 'fields' => ['option' => $token]]; }
                    if (isset($options[$key]) && $options[$key] !== $candidate) { throw new RuntimeException('M79 shared option state/casing is inconsistent across instances.'); }
                    $options[$key] = $candidate; $keys[$key] = true;
                    if ($n < count($correct)) { $actualAnswers[] = $candidate['row']['option'] ?? $token; }
                }
                if (ComposeTokenCase::normalize($actualAnswers) !== $correct || self::compose(ComposeTokenCase::normalize($actualAnswers)) !== $q['canonical_en']) {
                    throw new RuntimeException('M79 reused option casing cannot reproduce the exact approved visible sentence: '.$uid.'; expected '.$q['canonical_en'].'; actual '.self::compose(ComposeTokenCase::normalize($actualAnswers)));
                }
                $byMarker = [];
                foreach ($correct as $n => $token) {
                    $marker = 'a'.($n + 1); $byMarker[$marker] = [$token];
                    $out['question_answers'][] = ['key' => $uid.':'.$marker, 'question_key' => $uid,
                        'option_key' => 'option-'.hash('sha256', mb_strtolower($token)), 'fields' => ['marker' => $marker]];
                }
                $out['questions'][] = ['key' => $uid, 'fields' => ['uuid' => $uid, 'question' => self::condition($q),
                    'difficulty' => 3, 'level' => 'B1', 'category_id' => $bindings[$i]['category_id'], 'source_id' => $bindings[$i]['source_id'],
                    'flag' => 0, 'type' => '4', 'options_by_marker' => json_encode($byMarker, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR),
                    'theory_text_block_uuid' => null, 'seeder' => self::BANKS[$i]]];
                foreach (array_keys($keys) as $key) {
                    $out['question_option_question'][] = ['key' => $uid.':'.$key, 'question_key' => $uid, 'option_key' => $key, 'fields' => ['flag' => null]];
                }
                $out['question_theory_text_blocks'][] = ['key' => $uid, 'fields' => ['question_uuid' => $uid,
                    'text_block_uuid' => $bindings[$i]['theory_block_uuid'], 'position' => 0]];
                $out['translations'][$uid] = ['en' => $q['canonical_en'], 'uk' => $q['translation_uk']];
                $out['provenance'][$uid] = ['source' => self::SOURCE, 'source_uid' => $uid, 'bank_seeder' => self::BANKS[$i],
                    'owner' => self::OWNERS[$i], 'question_sha256' => M26ContentPatch::digest($q)];
            }
        }
        $actual = array_keys($matches); $expected = array_keys($requested); sort($actual); sort($expected);
        if ($actual !== $expected) { throw new RuntimeException('M79 unrelated or missing option lookups.'); }
        foreach ($options as $option) { $out[isset($option['id']) ? 'reused_options' : 'question_options'][] = $option; }
        return $out;
    }

    private static function tokens(mixed $tokens): array
    {
        if (!is_array($tokens) || !array_is_list($tokens) || $tokens === []) { throw new RuntimeException('M79 complete token instances are missing.'); }
        foreach ($tokens as $token) {
            if (!is_string($token) || strlen($token) > 255
                || preg_match('/^(?:[A-Za-z]+(?:[\x{2019}\x{0027}][A-Za-z]+)?[,.!?;:]?(?: [A-Za-z]+(?:[\x{2019}\x{0027}][A-Za-z]+)?[,.!?;:]?){0,2}|[,.!?;:])$/uD', $token) !== 1) {
                throw new RuntimeException('M79 token must contain one to three complete English words, with native punctuation.');
            }
        }
        return $tokens;
    }

    private static function compose(array $tokens): string
    {
        return preg_replace('/\s+([?.!,;:])/', '$1', implode(' ', $tokens));
    }
}
