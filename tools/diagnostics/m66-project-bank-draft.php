<?php

/** Pure finite M66 bank-author projection; private reviewed inputs only, no SQL/HTTP or app bootstrap. */
if (PHP_SAPI !== 'cli' || count($argv) !== 1) {
    throw new RuntimeException('M66 pure bank projection takes no arguments.');
}
$root = 'D:/DEV/htdocs/gramlyze.loc';
if (strcasecmp(str_replace('\\', '/', realpath(dirname(__DIR__, 2))), $root) !== 0) {
    throw new RuntimeException('M66 pure bank projection ROOT differs.');
}
require $root.'/vendor/autoload.php';

use App\Support\M26DetailPackage as Json;

$dir = $root.'/storage/app/seo-m66-local';
$inputs = [
    'before-v1.json' => '517118d0fe7cf764229b5926e82fa7044b10a92535923a7301bc46602311d6a3',
    'master-draft-v1.json' => 'ad8f9568a9445cbdb00c492b73aaa05fdbdec21fd4d37ddb4731d66ce427d890',
    'author-projection-v2.json' => '654f350263e83be2589c6640bd7f5f902880ea334321773d25843b6cd4478491',
    'builder-contract-draft-v1.json' => '702cb32d4dceb208baf7efb4512dd0443f0138b27f222aed1db3c60449d7d641',
    'draft-bank-review-v1.json' => 'f689f1de68e47bc39501e460795c3bc716166423ff6e46c7e0c0f8974d526f1f',
];
$read = static function (string $name) use ($dir, $inputs): array {
    $file = $dir.'/'.$name;
    if (!isset($inputs[$name]) || !is_file($file) || is_link($file)
        || !hash_equals($inputs[$name], hash_file('sha256', $file))) {
        throw new RuntimeException('M66 pure bank input differs: '.$name);
    }
    return json_decode(file_get_contents($file), true, flags: JSON_THROW_ON_ERROR);
};
$before = $read('before-v1.json');
$master = $read('master-draft-v1.json');
$author = $read('author-projection-v2.json');
$contract = $read('builder-contract-draft-v1.json');
$review = $read('draft-bank-review-v1.json');
$owners = [
    'Database\\Seeders\\Page_V3\\VerbPatterns\\VerbPatternsBareInfinitiveTheorySeeder',
    'Database\\Seeders\\Page_V3\\VerbPatterns\\VerbPatternsGerundVsInfinitiveTheorySeeder',
    'Database\\Seeders\\Page_V3\\VerbPatterns\\VerbPatternsVerbsPlusGerundTheorySeeder',
];
$base = 'c96e6b4e0c042f98fb0c105f61225f72d85a4850';
foreach ([$before, $master, $author] as $input) {
    if (($input['base_sha'] ?? null) !== $base) { throw new RuntimeException('M66 pure bank base differs.'); }
}
foreach ([$master['lessons'], $author['patch']['targets'], $contract['targets'], $review['targets']] as $targets) {
    if (array_column($targets, 'identity') !== $owners) { throw new RuntimeException('M66 exact owner order differs.'); }
}
if (($review['independent_before_sha256'] ?? null) !== $inputs['before-v1.json']
    || ($review['scope']['eligible_questions_read'] ?? null) !== 36
    || ($review['scope']['selected_existing_questions'] ?? null) !== 15
    || ($review['scope']['new_questions_needed'] ?? null) !== 0) {
    throw new RuntimeException('M66 finite bank review scope differs.');
}
$audit = [
    'schema_version' => 1, 'package' => 'm66-verb-patterns-core-bank-audit',
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
    if (count($pool) !== 12 || $target['expected_old_pool_ids'] !== $pool
        || $target['bank_seeders'] !== $declaration['bank_seeders']
        || $target['local_url'] !== $declaration['local_url']
        || $target['level'] !== $declaration['level']
        || $target['page_id'] !== $projected['page_binding']['id']
        || $target['category_id'] !== $projected['page_binding']['page_category_id']
        || count($target['all_eligible_review']) !== 12 || count($target['first_five']) !== 5
        || count(array_unique($target['expected_selected_ids'])) !== 5
        || array_values(array_diff($target['expected_selected_ids'], $pool)) !== []) {
        throw new RuntimeException('M66 captured bank/owner/level binding differs.');
    }
    foreach ($target['all_eligible_review'] as $decision) {
        $snapshot = $captured['relations']['question_snapshots'][$decision['uuid']] ?? null;
        if (!is_array($snapshot) || $snapshot['question']['id'] !== $decision['id']
            || !hash_equals($decision['full_private_snapshot_sha256'], Json::digest($snapshot))) {
            throw new RuntimeException('M66 complete eligible snapshot differs.');
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
            || $sample['displayed_condition_uk'] !== $question['question']
            || $sample['visible_condition_uk'] !== $sample['context_prefix_uk'].' '.$question['question']
            || $sample['correct_tokens'] !== $tokens || $sample['expected_word_key'] !== implode(' ', $tokens)
            || $sample['token_presentations'] !== [] || $sample['option_presentations'] !== []) {
            throw new RuntimeException('M66 selected semantic/opaque snapshot differs.');
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
        || ($authoredPractice['existing_source_index'] ?? null) !== null) {
        throw new RuntimeException('M66 practice append/source index differs.');
    }
    $data = json_decode($practice['body'], true, flags: JSON_THROW_ON_ERROR);
    if (($data['linked_practice'] ?? null) !== $authoredPractice['data']['linked_practice']
        || ($data['linked_practice']['seeder_classes'] ?? null) !== $declaration['bank_seeders']) {
        throw new RuntimeException('M66 exact linked practice data differs.');
    }
    $audit['practice_bindings'][] = ['identity' => $owner, 'page_id' => $target['page_id'],
        'category_id' => $target['category_id'], 'locale' => 'uk', 'source_index' => 6,
        'uuid' => Json::uuid($owner, $practice, 7), 'type' => 'practice-set', 'column' => $practice['column'],
        'heading' => $practice['heading'] ?? null, 'level' => $declaration['level'],
        'css_class' => $practice['css_class'] ?? null, 'body_sha256' => hash('sha256', $practice['body']),
        'linked_practice' => $data['linked_practice']];
}
if ($reviewedCount !== 36 || $selectedCount !== 15) { throw new RuntimeException('M66 pure bank totals differ.'); }
$out = $dir.'/bank-author-contract-draft-v1.json';
if (file_exists($out) || is_link($out)) { throw new RuntimeException('M66 bank author draft exists; no overwrite.'); }
$bytes = json_encode($audit, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n";
$handle = fopen($out, 'x');
try {
    if (!$handle || fwrite($handle, $bytes) !== strlen($bytes) || !fflush($handle)) {
        throw new RuntimeException('M66 incomplete private bank projection.');
    }
} finally { if (is_resource($handle)) { fclose($handle); } }
echo Json::json(['status' => 'pure-m66-bank-author-projection', 'file' => basename($out),
    'sha256' => hash('sha256', $bytes), 'reviewed' => $reviewedCount, 'selected' => $selectedCount,
    'practice_bindings' => $audit['practice_bindings'], 'scope' => $audit['scope'], 'db_writes' => 0])."\n";
