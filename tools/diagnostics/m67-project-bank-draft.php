<?php

/** Pure finite M67 bank-author projection; private reviewed inputs only, no SQL/HTTP or app bootstrap. */
if (PHP_SAPI !== 'cli' || count($argv) !== 1) {
    throw new RuntimeException('M67 pure bank projection takes no arguments.');
}
$root = 'D:/DEV/htdocs/gramlyze.loc';
if (strcasecmp(str_replace('\\', '/', realpath(dirname(__DIR__, 2))), $root) !== 0) {
    throw new RuntimeException('M67 pure bank projection ROOT differs.');
}
require $root.'/vendor/autoload.php';

use App\Support\M26DetailPackage as Json;
use App\Support\M51QuestionSupplement;

$dir = $root.'/storage/app/seo-m67-local';
$inputs = [
    'before-v1.json' => '55d8e940ce68287b31cd6522e8377023778092ac265bf5ae92c6adc6661e04b0',
    'master-draft-v1.json' => '53f4dff003413a0b0730072f1911d2781baa5c6eae108c65f197359c9d43ebf2',
    'author-projection-v1.json' => 'f27e45b883e3dc7ae44478025a186b9822ff784a1e46b8f9c811afeba5868f1d',
    'builder-contract-draft-v1.json' => '2d964cd1ecf4d4ea4e2c29297994bdca1806e54395369825c24a0e106535fe5d',
    'draft-bank-review-v1.json' => '9f464fa2653e17bcd5744935ed663730502e60f1ead825225650596c1d2495d6',
];
$read = static function (string $name) use ($dir, $inputs): array {
    $file = $dir.'/'.$name;
    if (!isset($inputs[$name]) || !is_file($file) || is_link($file)
        || !hash_equals($inputs[$name], hash_file('sha256', $file))) {
        throw new RuntimeException('M67 pure bank input differs: '.$name);
    }
    return json_decode(file_get_contents($file), true, flags: JSON_THROW_ON_ERROR);
};
$before = $read('before-v1.json');
$master = $read('master-draft-v1.json');
$author = $read('author-projection-v1.json');
$contract = $read('builder-contract-draft-v1.json');
$review = $read('draft-bank-review-v1.json');
$owners = [
    'Database\\Seeders\\Page_V3\\VerbPatterns\\VerbPatternsVerbsPlusInfinitiveTheorySeeder',
    'Database\\Seeders\\Page_V3\\VerbPatterns\\VerbPatternsStopRememberForgetTryRegretTheorySeeder',
    'Database\\Seeders\\Page_V3\\VerbPatterns\\VerbPatternsBeUsedToGetUsedToUsedToTheorySeeder',
];
$banks = [
    'Database\\Seeders\\V3\\Polyglot\\PolyglotVerbsPlusInfinitiveAllLevelsLessonSeeder',
    'Database\\Seeders\\V3\\Polyglot\\PolyglotStopRememberForgetTryRegretAllLevelsLessonSeeder',
    'Database\\Seeders\\V3\\Polyglot\\PolyglotBeUsedToGetUsedToUsedToAllLevelsLessonSeeder',
];
$expectedPools = [range(39615, 39626), range(40191, 40202), range(40263, 40274)];
$expectedSelected = [[39616, 39617, 39620, 39623, 39626], [40192, 40193, 40195, 40198, 40199], [40264, 40265, 40267, 40269, 40274]];
$pageIds = [200, 197, 194];
$slugs = ['verbs-plus-infinitive', 'stop-remember-forget-try-regret', 'be-used-to-get-used-to-used-to'];
$base = 'a8db1d50a01005b9a519ee33a655cf65c20d13aa';
foreach ([$before, $master, $author, $review] as $input) {
    if (($input['base_sha'] ?? null) !== $base) { throw new RuntimeException('M67 pure bank base differs.'); }
}
foreach ([$master['lessons'], $author['patch']['targets'], $contract['targets'], $review['targets']] as $targets) {
    if (array_column($targets, 'identity') !== $owners) { throw new RuntimeException('M67 exact owner order differs.'); }
}
if (($master['version'] ?? null) !== '1.0.0'
    || ($review['package'] ?? null) !== 'm67-verb-patterns-completion-bank-review'
    || ($review['independent_before_sha256'] ?? null) !== $inputs['before-v1.json']
    || ($review['scope']['eligible_questions_read'] ?? null) !== 36
    || ($review['scope']['selected_existing_questions'] ?? null) !== 15
    || ($review['scope']['new_questions_needed'] ?? null) !== 0) {
    throw new RuntimeException('M67 finite bank review scope differs.');
}
$audit = [
    'schema_version' => 1, 'package' => 'm67-verb-patterns-completion-bank-audit',
    'version' => '1.0.0', 'status' => 'private-draft-not-frozen', 'locale' => 'uk',
    'independent_before_sha256' => $inputs['before-v1.json'],
    'input_checksums' => $inputs,
    'scope' => ['selected_questions' => 15, 'reused_questions' => 15, 'new_questions' => 0,
        'question_writes' => 0, 'option_writes' => 0, 'answer_writes' => 0, 'option_link_writes' => 0,
        'link_writes' => 0, 'old_hint_writes' => 0, 'old_question_updates' => 0,
        'old_option_updates' => 0, 'old_answer_updates' => 0, 'grader_changes' => 0, 'progress_writes' => 0],
    'pending' => [],
    'privacy_policy' => 'Teaching-only conditions, expected ordered tokens, IDs and opaque ordered-JSON snapshots. No raw SQL rows, timestamps, runtime identity, secrets, progress or private diagnostics.',
    'review_summary' => ['old_eligible_read' => 36, 'new_reviewed' => 0, 'selected' => 15,
        'policy' => 'Reuse fifteen sound and distinct existing thematic cases; no new question or link writes. Bounded Ukrainian criteria distinguish grammatical alternatives from the exact target.',
        'checks' => $review['review_summary']],
    'clone_only_policy' => $review['clone_only_policy'],
    'targets' => [], 'practice_bindings' => [],
];
$reviewedCount = 0; $selectedCount = 0;
foreach ($owners as $i => $owner) {
    $target = $review['targets'][$i]; $captured = $before['pages'][$owner];
    $declaration = $contract['targets'][$i]; $projected = $author['patch']['targets'][$i];
    $lesson = $master['lessons'][$i]; $bank = $declaration['bank_seeders'][0];
    $pool = $captured['bank_inventory'][$bank]['eligible_ids_uuid_order'];
    if ($pool !== $expectedPools[$i] || $bank !== $banks[$i] || $target['expected_old_pool_ids'] !== $pool
        || $target['expected_pool_ids_by_bank'] !== [$bank => $pool]
        || $target['expected_selected_ids'] !== $expectedSelected[$i]
        || array_column($target['first_five'], 'id') !== $expectedSelected[$i]
        || array_column($target['first_five'], 'uuid') !== $target['expected_selected_uuids']
        || array_column($target['all_eligible_review'], 'id') !== $pool
        || $target['page_id'] !== $pageIds[$i] || $target['category_id'] !== 33
        || $target['locale'] !== 'uk' || $target['type'] !== 4 || $target['level'] !== 'B1'
        || $target['local_url'] !== 'http://gramlyze.loc/theory/verb-patterns/'.$slugs[$i]
        || $target['bank_seeders'] !== $declaration['bank_seeders']
        || $target['local_url'] !== $declaration['local_url']
        || $target['level'] !== $declaration['level']
        || $target['page_id'] !== $projected['page_binding']['id']
        || $target['category_id'] !== $projected['page_binding']['page_category_id']
        || count($target['all_eligible_review']) !== 12 || count($target['first_five']) !== 5
        || count(array_unique($target['expected_selected_ids'])) !== 5
        || array_values(array_diff($target['expected_selected_ids'], $pool)) !== []) {
        throw new RuntimeException('M67 captured bank/owner/level binding differs.');
    }
    foreach ($target['all_eligible_review'] as $decision) {
        $snapshot = $captured['relations']['question_snapshots'][$decision['uuid']] ?? null;
        if (!is_array($snapshot) || $snapshot['question']['id'] !== $decision['id']
            || $snapshot['question']['seeder'] !== $bank || $snapshot['question']['type'] !== '4'
            || $snapshot['question']['level'] !== 'B1'
            || $decision['condition_uk'] !== $snapshot['question']['question']
            || $decision['actual_ordered_key'] !== implode(' ', array_column($snapshot['ordered_answer_instances'], 'option'))
            || $decision['selected'] !== in_array($decision['id'], $expectedSelected[$i], true)
            || $decision['question_attribute_keys'] !== array_keys($snapshot['question'])
            || array_keys($snapshot['question']) !== M51QuestionSupplement::ROW_COLUMNS['questions']
            || !hash_equals($decision['full_private_snapshot_sha256'], Json::digest($snapshot))
            || !hash_equals($decision['variants_sha256'], Json::digest($snapshot['variants']))
            || !hash_equals($decision['links_sha256'], Json::digest($snapshot['theory_links']))
            || !hash_equals($decision['raw_answers_sha256'], Json::digest($snapshot['answers']))
            || !hash_equals($decision['raw_option_pivots_sha256'], Json::digest($snapshot['option_pivots']))
            || $decision['option_count'] !== count($snapshot['options'])
            || $decision['ordered_instance_count'] !== count($snapshot['ordered_answer_instances'])
            || $decision['hint_counts'] !== [count($snapshot['hints']), count($snapshot['verb_hints'])]) {
            throw new RuntimeException('M67 complete eligible snapshot differs.');
        }
        $reviewedCount++;
    }
    foreach ($target['first_five'] as $sample) {
        $snapshot = $captured['relations']['question_snapshots'][$sample['uuid']];
        $question = $snapshot['question'];
        $options = array_map(static fn ($row) => ['id' => $row['id'], 'option' => $row['option']], $snapshot['options']);
        usort($options, static fn ($a, $b) => $a['id'] <=> $b['id']);
        $pivots = array_map(static fn ($row) => ['question_id' => $row['question_id'],
            'option_id' => $row['option_id'], 'flag' => $row['flag']], $snapshot['option_pivots']);
        usort($pivots, static fn ($a, $b) => $a['option_id'] <=> $b['option_id']);
        $fields = ['question_snapshot' => $question, 'ordered_answer_snapshot' => $snapshot['ordered_answer_instances'],
            'original_options_snapshot' => $options, 'hints_snapshot' => $snapshot['hints'],
            'verb_hints_snapshot' => $snapshot['verb_hints'], 'loaded_option_pivots' => $pivots];
        $tokens = App\Support\ComposeTokenCase::normalize(array_column($snapshot['ordered_answer_instances'], 'option'));
        if ($question['id'] !== $sample['id'] || $question['seeder'] !== $bank || $question['type'] !== '4'
            || $question['level'] !== $declaration['level'] || $sample['is_new'] !== false
            || $sample['question_attribute_keys'] !== array_keys($question)
            || $sample['snapshot_hashes'] !== array_map(Json::digest(...), $fields)
            || !hash_equals($sample['full_private_snapshot_sha256'], Json::digest($snapshot))
            || !hash_equals($sample['variants_sha256'], Json::digest($snapshot['variants']))
            || !hash_equals($sample['links_sha256'], Json::digest($snapshot['theory_links']))
            || $sample['displayed_condition_uk'] !== $question['question']
            || $sample['visible_condition_uk'] !== $sample['context_prefix_uk'].' '.$question['question']
            || $sample['correct_tokens'] !== $tokens || $sample['expected_word_key'] !== implode(' ', $tokens)
            || $sample['token_presentations'] !== [] || $sample['option_presentations'] !== []
            || ($sample['id'] === 39623 && !str_contains($sample['context_prefix_uk'], 'у порядку not + to'))) {
            throw new RuntimeException('M67 selected semantic/opaque snapshot differs.');
        }
        $selectedCount++;
    }
    $target['category_ancestry'] = array_map(static fn ($category) => array_intersect_key($category,
        array_flip(['id', 'slug', 'language', 'type', 'parent_id'])), $captured['category_ancestry']);
    $audit['targets'][] = $target;
    $practice = $projected['after']['page']['blocks'][6] ?? null;
    $authoredPractice = $lesson['blocks'][6] ?? null;
    if (count($projected['after']['page']['blocks']) !== 7 || !is_array($practice)
        || ($practice['type'] ?? null) !== 'practice-set' || ($practice['column'] ?? null) !== 'left'
        || ($practice['level'] ?? null) !== $declaration['level']
        || ($authoredPractice['type'] ?? null) !== 'practice-set'
        || ($authoredPractice['existing_source_index'] ?? null) !== null
        || count($lesson['blocks']) !== 7) {
        throw new RuntimeException('M67 practice append/source index differs.');
    }
    $data = json_decode($practice['body'], true, flags: JSON_THROW_ON_ERROR);
    if (($data['linked_practice'] ?? null) !== $authoredPractice['data']['linked_practice']
        || ($data['linked_practice']['seeder_classes'] ?? null) !== $declaration['bank_seeders']) {
        throw new RuntimeException('M67 exact linked practice data differs.');
    }
    $audit['practice_bindings'][] = ['identity' => $owner, 'page_id' => $target['page_id'],
        'category_id' => $target['category_id'], 'locale' => 'uk', 'source_index' => 6,
        'uuid' => Json::uuid($owner, $practice, 7), 'type' => 'practice-set', 'column' => $practice['column'],
        'heading' => $practice['heading'] ?? null, 'level' => $declaration['level'],
        'css_class' => $practice['css_class'] ?? null, 'body_sha256' => hash('sha256', $practice['body']),
        'linked_practice' => $data['linked_practice']];
}
if ($reviewedCount !== 36 || $selectedCount !== 15) { throw new RuntimeException('M67 pure bank totals differ.'); }
$out = $dir.'/bank-author-contract-draft-v1.json';
if (file_exists($out) || is_link($out)) { throw new RuntimeException('M67 bank author draft exists; no overwrite.'); }
$bytes = json_encode($audit, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n";
$handle = fopen($out, 'x');
try {
    if (!$handle || fwrite($handle, $bytes) !== strlen($bytes) || !fflush($handle)) {
        throw new RuntimeException('M67 incomplete private bank projection.');
    }
} finally { if (is_resource($handle)) { fclose($handle); } }
echo Json::json(['status' => 'pure-m67-bank-author-projection', 'file' => basename($out),
    'sha256' => hash('sha256', $bytes), 'reviewed' => $reviewedCount, 'selected' => $selectedCount,
    'practice_bindings' => $audit['practice_bindings'], 'scope' => $audit['scope'], 'db_writes' => 0])."\n";
