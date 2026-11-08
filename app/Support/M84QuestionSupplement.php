<?php

namespace App\Support;

use App\Services\M26ContentPatch;
use RuntimeException;

/** Finite twenty-five-question source and symbolic insert projection; no SQL, allocation or updates. */
final class M84QuestionSupplement
{
    public const SOURCE = 'docs/content/m84-question-bank-supplement.v1.json';
    public const SOURCE_SHA = '6c316edf96be3411387e06118e96832305357ce52ae1698e3e5b8b0b85db274a';
    public const BASE_SHA = '91c718a119a6d42f180c2a7121fbbbf94ef5afa4';
    public const TARGET_INDICES = [0, 1, 2, 3, 4];
    public const OWNERS = [M84WordOrderHaveGot::OWNERS[0], M84WordOrderHaveGot::OWNERS[1], M84WordOrderHaveGot::OWNERS[2], M84WordOrderHaveGot::OWNERS[3], M84WordOrderHaveGot::OWNERS[4]];
    public const BANKS = ['Database\\Seeders\\V3\\Polyglot\\M84BasicWordOrderSupplementSeeder', 'Database\\Seeders\\V3\\Polyglot\\M84QuestionsNegativesSupplementSeeder', 'Database\\Seeders\\V3\\Polyglot\\M84AdverbsAdverbialsSupplementSeeder', 'Database\\Seeders\\V3\\Polyglot\\M84VerbsObjectsSupplementSeeder', 'Database\\Seeders\\V3\\Polyglot\\M84AdvancedEmphasisSupplementSeeder'];
    public const OLD_BANKS = [M84WordOrderHaveGot::BANKS[0], M84WordOrderHaveGot::BANKS[1], M84WordOrderHaveGot::BANKS[2], M84WordOrderHaveGot::BANKS[3], M84WordOrderHaveGot::BANKS[4]];
    public const PAGE_IDS = [256, 258, 257, 259, 255];
    public const HERO_IDS = [7970, 8042, 8003, 8075, 7934];
    public const HERO_UUIDS = ['a6e3b9b8-9db4-59a4-a000-929b1c673fa5', '474c00a4-d13b-5bdc-bd99-c6be840e1a60', '7b755a6a-2cee-5830-8bc7-cfac4a54ac6a', 'd465b0ef-1348-52e6-b72e-d7d565429b25', '1fde1a96-29a8-5625-882c-91f68377e448'];
    public const SOURCE_IDS = [28, 29, 30, 31, 32];
    public const SLUGS = ['theory-basic-word-order', 'theory-word-order-questions-negatives', 'theory-word-order-adverbs-adverbials', 'theory-word-order-verbs-objects', 'theory-advanced-word-order-emphasis'];
    public const LEVELS = ['A1', 'A1', 'A2', 'A2', 'B2'];
    public const UID_PREFIXES = ['m84-bwo-a1-', 'm84-woqn-a1-', 'm84-woaa-a2-', 'm84-wovo-a2-', 'm84-woadv-b2-'];
    public const UUIDS = ['m84-bwo-a1-01', 'm84-bwo-a1-02', 'm84-bwo-a1-03', 'm84-bwo-a1-04', 'm84-bwo-a1-05', 'm84-woqn-a1-01', 'm84-woqn-a1-02', 'm84-woqn-a1-03', 'm84-woqn-a1-04', 'm84-woqn-a1-05', 'm84-woaa-a2-01', 'm84-woaa-a2-02', 'm84-woaa-a2-03', 'm84-woaa-a2-04', 'm84-woaa-a2-05', 'm84-wovo-a2-01', 'm84-wovo-a2-02', 'm84-wovo-a2-03', 'm84-wovo-a2-04', 'm84-wovo-a2-05', 'm84-woadv-b2-01', 'm84-woadv-b2-02', 'm84-woadv-b2-03', 'm84-woadv-b2-04', 'm84-woadv-b2-05'];
    public const ROW_COLUMNS = M51QuestionSupplement::ROW_COLUMNS;

    public static function load(?string $root = null): array
    {
        $path = ($root ?? base_path()).'/'.self::SOURCE;
        if (preg_match('/^[a-f0-9]{64}$/D', self::SOURCE_SHA) !== 1 || !is_file($path) || is_link($path)
            || !hash_equals(self::SOURCE_SHA, hash_file('sha256', $path))) {
            throw new RuntimeException('M84 question source is not reviewed/frozen or its exact bytes differ.');
        }
        $source = json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
        self::validate($source);
        if ($source['status'] !== 'frozen') { throw new RuntimeException('M84 apply source must be frozen.'); }
        return $source;
    }

    public static function bindings(): array
    {
        $out = [];
        foreach (self::OWNERS as $i => $owner) {
            $out[] = ['owner' => $owner, 'page_id' => self::PAGE_IDS[$i], 'page_category_id' => 48,
                'level' => self::LEVELS[$i], 'category_id' => 45, 'source_id' => self::SOURCE_IDS[$i],
                'theory_block_id' => self::HERO_IDS[$i], 'theory_block_uuid' => self::HERO_UUIDS[$i]];
        }
        return $out;
    }

    /** Full task-bound context is visible before Check, not hidden in author-only metadata. */
    public static function condition(array $question): string
    {
        foreach (['question_uk', 'context_uk', 'criterion_uk', 'ambiguity_note_uk'] as $field) {
            if (!is_string($question[$field] ?? null) || trim($question[$field]) === '') { throw new RuntimeException('M84 visible condition is incomplete.'); }
        }
        $text = $question['question_uk'].' '.$question['context_uk'].' '.$question['criterion_uk'].' '.$question['ambiguity_note_uk'];
        if (strlen($text) > 65535) { throw new RuntimeException('M84 combined visible condition exceeds SQL TEXT capacity.'); }
        return $text;
    }

    public static function validate(array $source): void
    {
        if (($source['schema_version'] ?? null) !== 1 || ($source['package'] ?? null) !== 'm84-question-bank-supplement'
            || ($source['version'] ?? null) !== '1.0.0' || ($source['locale'] ?? null) !== 'uk'
            || !in_array($source['status'] ?? null, ['draft', 'frozen'], true)
            || !is_array($source['banks'] ?? null) || !array_is_list($source['banks']) || count($source['banks']) !== 5) {
            throw new RuntimeException('M84 finite question source schema/version/scope differs.');
        }
        $all = []; $conditions = []; $canonical = [];
        foreach ($source['banks'] as $i => $bank) {
            $count = 5; $prefix = self::UID_PREFIXES[$i];
            if (($bank['owner'] ?? null) !== self::OWNERS[$i] || ($bank['bank_seeder'] ?? null) !== self::BANKS[$i]
                || ($bank['level'] ?? null) !== self::LEVELS[$i] || ($bank['type'] ?? null) !== 4
                || ($bank['local_url'] ?? null) !== 'http://gramlyze.loc/theory/basic-grammar/word-order/'.self::SLUGS[$i]
                || !is_array($bank['questions'] ?? null) || !array_is_list($bank['questions']) || count($bank['questions']) !== $count) {
                throw new RuntimeException('M84 owner/bank/level/URL/question cardinality differs.');
            }
            foreach ($bank['questions'] as $n => $q) {
                $uid = $prefix.str_pad((string) ($n + 1), 2, '0', STR_PAD_LEFT);
                if (($q['source_uid'] ?? null) !== $uid || (new Database\QuestionUuidResolver)->toPersistent($uid) !== $uid) {
                    throw new RuntimeException('M84 stable question UID or order differs.');
                }
                $all[] = $uid;
                foreach (['question_uk', 'context_uk', 'criterion_uk', 'translation_uk', 'ambiguity_note_uk'] as $field) {
                    $text = $q[$field] ?? null;
                    if (!is_string($text) || trim($text) !== $text || strlen($text) > 65535
                        || preg_match('/[\x{0400}-\x{04FF}]/u', $text) !== 1
                        || preg_match('/<[^>]+>|&(?:#[0-9]+|#x[0-9a-f]+|[a-z][a-z0-9]+);|\{a[0-9]+\}/iu', $text)) {
                        throw new RuntimeException('M84 Ukrainian visible condition/context/criterion/translation is invalid.');
                    }
                }
                $correct = self::tokens($q['tokens_correct'] ?? null); $shuffled = self::tokens($q['tokens_shuffled'] ?? null);
                $left = $correct; $right = $shuffled; sort($left); sort($right);
                $en = $q['canonical_en'] ?? null;
                $minimum = 3;
                if ($left !== $right || $correct === $shuffled || count($correct) < $minimum
                    || !is_string($en) || self::compose($correct) !== $en || ComposeTokenCase::normalize($correct) !== $correct
                    || !is_array($q['distractors'] ?? null) || !array_is_list($q['distractors'])
                    || !is_array($q['learning_requirements'] ?? null) || !array_is_list($q['learning_requirements'])
                    || $q['learning_requirements'] === [] || isset($conditions[$q['question_uk']]) || isset($canonical[$en])) {
                    throw new RuntimeException('M84 canonical/shuffle/case/multiplicity/unique question contract differs.');
                }
                $conditions[$q['question_uk']] = true; $canonical[$en] = true;
                if ($q['distractors'] !== []) { self::tokens($q['distractors']); }
                foreach ($q['learning_requirements'] as $requirement) {
                    if (!is_string($requirement) || trim($requirement) === '') { throw new RuntimeException('M84 semantic requirement is empty.'); }
                }
            }
        }
        if ($all !== self::UUIDS) { throw new RuntimeException('M84 complete ordered twenty-five-question scope differs.'); }
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
                    if (!is_array($lookup) || !array_is_list($lookup) || count($lookup) > 1) { throw new RuntimeException('M84 case review requires complete unambiguous option lookups.'); }
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
        if ($bindings !== self::bindings()) { throw new RuntimeException('M84 exact captured hero/taxonomy bindings differ.'); }
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
                        throw new RuntimeException('M84 missing/ambiguous complete SQL-collation option lookup.');
                    }
                    if ($lookup !== []) {
                        $row = $lookup[0]; $columns = array_keys($row); $expected = self::ROW_COLUMNS['question_options'];
                        sort($columns); sort($expected);
                        if ($columns !== $expected || !is_int($row['id']) || $row['id'] < 1 || !is_string($row['option'])
                            || mb_strtolower($row['option']) !== mb_strtolower($token)
                            || (isset($seenIds[$row['id']]) && $seenIds[$row['id']] !== $key)) {
                            throw new RuntimeException('M84 reused option identity/value/collision differs.');
                        }
                        $seenIds[$row['id']] = $key;
                        $candidate = ['key' => $key, 'id' => $row['id'], 'row' => $row, 'sha256' => M26ContentPatch::digest($row)];
                    } else { $candidate = ['key' => $key, 'fields' => ['option' => $token]]; }
                    if (isset($options[$key]) && $options[$key] !== $candidate) { throw new RuntimeException('M84 shared option state/casing is inconsistent across instances.'); }
                    $options[$key] = $candidate; $keys[$key] = true;
                    if ($n < count($correct)) { $actualAnswers[] = $candidate['row']['option'] ?? $token; }
                }
                if (ComposeTokenCase::normalize($actualAnswers) !== $correct || self::compose(ComposeTokenCase::normalize($actualAnswers)) !== $q['canonical_en']) {
                    throw new RuntimeException('M84 reused option casing cannot reproduce the exact approved visible sentence: '.$uid.'; expected '.$q['canonical_en'].'; actual '.self::compose(ComposeTokenCase::normalize($actualAnswers)));
                }
                $byMarker = [];
                foreach ($correct as $n => $token) {
                    $marker = 'a'.($n + 1); $byMarker[$marker] = [$token];
                    $out['question_answers'][] = ['key' => $uid.':'.$marker, 'question_key' => $uid,
                        'option_key' => 'option-'.hash('sha256', mb_strtolower($token)), 'fields' => ['marker' => $marker]];
                }
                $out['questions'][] = ['key' => $uid, 'fields' => ['uuid' => $uid, 'question' => self::condition($q),
                    'difficulty' => ['A1'=>1,'A2'=>2,'B2'=>4][self::LEVELS[$i]], 'level' => self::LEVELS[$i], 'category_id' => $bindings[$i]['category_id'], 'source_id' => $bindings[$i]['source_id'],
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
        if ($actual !== $expected) { throw new RuntimeException('M84 unrelated or missing option lookups.'); }
        foreach ($options as $option) { $out[isset($option['id']) ? 'reused_options' : 'question_options'][] = $option; }
        return $out;
    }

    private static function tokens(mixed $tokens): array
    {
        if (!is_array($tokens) || !array_is_list($tokens) || $tokens === []) { throw new RuntimeException('M84 complete token instances are missing.'); }
        foreach ($tokens as $token) {
            if (!is_string($token) || strlen($token) > 255
                || preg_match('/^(?:[A-Za-z]+(?:[\x{2019}\x{0027}][A-Za-z]+)?[,.!?;:]?(?: [A-Za-z]+(?:[\x{2019}\x{0027}][A-Za-z]+)?[,.!?;:]?){0,2}|[,.!?;:])$/uD', $token) !== 1) {
                throw new RuntimeException('M84 token must contain one to three complete English words, with native punctuation.');
            }
        }
        return $tokens;
    }

    private static function compose(array $tokens): string
    {
        return preg_replace('/\s+([?.!,;:])/', '$1', implode(' ', $tokens));
    }
}
