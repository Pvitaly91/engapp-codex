<?php

/** Pure finite M68 bank-author projection; private reviewed inputs only, no SQL/HTTP or app bootstrap. */
if (PHP_SAPI !== 'cli' || count($argv) !== 1) {
    throw new RuntimeException('M68 pure bank projection takes no arguments.');
}
$root = 'D:/DEV/htdocs/gramlyze.loc';
if (strcasecmp(str_replace('\\', '/', realpath(dirname(__DIR__, 2))), $root) !== 0) {
    throw new RuntimeException('M68 pure bank projection ROOT differs.');
}
require $root.'/vendor/autoload.php';

use App\Support\M26DetailPackage as Json;
use App\Support\M51QuestionSupplement;

$dir = $root.'/storage/app/seo-m68-local';
$inputs = [
    'before-v1.json' => 'e23f859491c59bcb182dc22a73b69b2b8bad14fb85b565e3bd8fd6cbcc1dac46',
    'master-draft-v1.json' => 'ff05b205d73961eddbdf0fae5cceac3d8913e471da44225efad02d2198f5be76',
    'author-projection-v5.json' => 'd2a8a7c970a364aca94eb5dd5a23dc41345b8ab3654615b9f8982ce5ac449ffb',
    'builder-contract-draft-v1.json' => 'd76d289f343c6f2a4bb588ba8e0909c8e852ab0affb0bb4c61788b9d4832b91a',
    'draft-bank-review-v1.json' => '7817c52af89f2bc632a57926780ea1be775883ab0b7c4ff67d7d69756ff5ce95',
];
$read = static function (string $name) use ($dir, $inputs): array {
    $file = $dir.'/'.$name;
    if (!isset($inputs[$name]) || !is_file($file) || is_link($file)
        || !hash_equals($inputs[$name], hash_file('sha256', $file))) {
        throw new RuntimeException('M68 pure bank input differs: '.$name);
    }
    return json_decode(file_get_contents($file), true, flags: JSON_THROW_ON_ERROR);
};
$before = $read('before-v1.json');
$master = $read('master-draft-v1.json');
$author = $read('author-projection-v5.json');
$contract = $read('builder-contract-draft-v1.json');
$review = $read('draft-bank-review-v1.json');
$owners = [
    'Database\\Seeders\\Page_V3\\Adjectives\\AdjectivesDegreesOfComparisonTheorySeeder',
    'Database\\Seeders\\Page_V3\\Adjectives\\AdjectivesComparativeVsSuperlativeTheorySeeder',
    'Database\\Seeders\\Page_V3\\Adjectives\\AdjectivesEqualityComparisonAsAsNotAsAsTheorySeeder',
];
$banks = [
    'Database\\Seeders\\V3\\Polyglot\\PolyglotDegreesOfComparisonAllLevelsLessonSeeder',
    'Database\\Seeders\\V3\\Polyglot\\PolyglotComparativeVsSuperlativeAllLevelsLessonSeeder',
    'Database\\Seeders\\V3\\Polyglot\\PolyglotEqualityComparisonAsAsNotAsAsAllLevelsLessonSeeder',
];
$expectedPools = [range(16250, 16261), range(2640, 2651), range(2712, 2723)];
$expectedSelected = [[16251, 16253, 16255, 16256, 16258], [2640, 2641, 2642, 2647, 2649], [2712, 2713, 2714, 2718, 2720]];
$pageIds = [274, 273, 276];
$continuingCounts = [9, 11, 12];
$slugs = ['theory-degrees-of-comparison', 'comparative-vs-superlative', 'equality-comparison-asas-not-asas'];
$base = '2161cb0b03afd203933ad022ccb4e77e8e8ca363';
foreach ([$before, $master, $author, $review] as $input) {
    if (($input['base_sha'] ?? null) !== $base) { throw new RuntimeException('M68 pure bank base differs.'); }
}
foreach ([$master['lessons'], $author['patch']['targets'], $contract['targets'], $review['targets']] as $targets) {
    if (array_column($targets, 'identity') !== $owners) { throw new RuntimeException('M68 exact owner order differs.'); }
}
if (($master['version'] ?? null) !== '1.0.0'
    || ($review['package'] ?? null) !== 'm68-comparison-basics-bank-review'
    || ($review['independent_before_sha256'] ?? null) !== $inputs['before-v1.json']
    || ($review['scope']['eligible_questions_read'] ?? null) !== 36
    || ($review['scope']['selected_existing_questions'] ?? null) !== 15
    || ($review['scope']['new_questions_needed'] ?? null) !== 0) {
    throw new RuntimeException('M68 finite bank review scope differs.');
}
$audit = [
    'schema_version' => 1, 'package' => 'm68-comparison-basics-bank-audit',
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
        || $target['page_id'] !== $pageIds[$i] || $target['category_id'] !== 52
        || $captured['continuing_blocks'] !== $continuingCounts[$i]
        || $target['continuing_source_blocks'] !== $continuingCounts[$i]
        || $target['practice_append_source_index'] !== $continuingCounts[$i]
        || $target['locale'] !== 'uk' || $target['type'] !== 4 || $target['level'] !== 'A2'
        || $target['local_url'] !== 'http://gramlyze.loc/theory/prykmetniky-ta-pryslinknyky/'.$slugs[$i]
        || $target['bank_seeders'] !== $declaration['bank_seeders']
        || $target['local_url'] !== $declaration['local_url']
        || $target['level'] !== $declaration['level']
        || $target['local_url'] !== ($captured['target_route']['url'] ?? null)
        || array_column($captured['category_ancestry'], 'slug') !== ['prykmetniky-ta-pryslinknyky']
        || $target['page_id'] !== $projected['page_binding']['id']
        || $target['category_id'] !== $projected['page_binding']['page_category_id']
        || count($target['all_eligible_review']) !== 12 || count($target['first_five']) !== 5
        || count(array_unique($target['expected_selected_ids'])) !== 5
        || array_values(array_diff($target['expected_selected_ids'], $pool)) !== []) {
        throw new RuntimeException('M68 captured bank/owner/level binding differs.');
    }
    foreach ($target['all_eligible_review'] as $decision) {
        $snapshot = $captured['relations']['question_snapshots'][$decision['uuid']] ?? null;
        if (!is_array($snapshot) || $snapshot['question']['id'] !== $decision['id']
            || $snapshot['question']['seeder'] !== $bank || $snapshot['question']['type'] !== '4'
            || $snapshot['question']['level'] !== 'A2'
            || $decision['condition_uk'] !== $snapshot['question']['question']
            || $decision['actual_ordered_key'] !== implode(' ', array_column($snapshot['ordered_answer_instances'], 'option'))
            || $decision['native_case_word_key'] !== implode(' ', App\Support\ComposeTokenCase::normalize(array_column($snapshot['ordered_answer_instances'], 'option')))
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
            throw new RuntimeException('M68 complete eligible snapshot differs.');
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
            || $sample['raw_correct_tokens'] !== array_column($snapshot['ordered_answer_instances'], 'option')
            || $sample['original_ordered_instances'] !== $snapshot['ordered_answer_instances']
            || $sample['token_bank_with_multiplicity'] !== App\Support\ComposeTokenCase::mergeOptions(array_column($options, 'option'), $sample['raw_correct_tokens'])
            || $sample['source_ordered_instance_count'] !== count($snapshot['ordered_answer_instances'])
            || App\Support\ComposeTokenCase::normalize($tokens) !== $tokens) {
            throw new RuntimeException('M68 selected semantic/opaque snapshot differs.');
        }
        $selectedCount++;
    }
    $target['category_ancestry'] = array_map(static fn ($category) => array_intersect_key($category,
        array_flip(['id', 'slug', 'language', 'type', 'parent_id'])), $captured['category_ancestry']);
    $audit['targets'][] = $target;
    $practice = $projected['after']['page']['blocks'][$continuingCounts[$i]] ?? null;
    $authoredPractice = $lesson['blocks'][$continuingCounts[$i]] ?? null;
    if (count($projected['after']['page']['blocks']) !== $continuingCounts[$i] + 1 || !is_array($practice)
        || ($practice['type'] ?? null) !== 'practice-set' || ($practice['column'] ?? null) !== 'left'
        || ($practice['level'] ?? null) !== $declaration['level']
        || ($authoredPractice['type'] ?? null) !== 'practice-set'
        || ($authoredPractice['existing_source_index'] ?? null) !== null
        || count($lesson['blocks']) !== $continuingCounts[$i] + 1) {
        throw new RuntimeException('M68 practice append/source index differs.');
    }
    $data = json_decode($practice['body'], true, flags: JSON_THROW_ON_ERROR);
    if (($data['linked_practice'] ?? null) !== $authoredPractice['data']['linked_practice']
        || ($data['linked_practice']['seeder_classes'] ?? null) !== $declaration['bank_seeders']) {
        throw new RuntimeException('M68 exact linked practice data differs.');
    }
    $audit['practice_bindings'][] = ['identity' => $owner, 'page_id' => $target['page_id'],
        'category_id' => $target['category_id'], 'locale' => 'uk', 'source_index' => $continuingCounts[$i],
        'uuid' => Json::uuid($owner, $practice, $continuingCounts[$i] + 1), 'type' => 'practice-set', 'column' => $practice['column'],
        'heading' => $practice['heading'] ?? null, 'level' => $declaration['level'],
        'css_class' => $practice['css_class'] ?? null, 'body_sha256' => hash('sha256', $practice['body']),
        'linked_practice' => $data['linked_practice']];
}
if ($reviewedCount !== 36 || $selectedCount !== 15) { throw new RuntimeException('M68 pure bank totals differ.'); }
$out = $dir.'/bank-author-contract-draft-v3.json';
if (file_exists($out) || is_link($out)) { throw new RuntimeException('M68 bank author draft exists; no overwrite.'); }
$bytes = json_encode($audit, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n";
$handle = fopen($out, 'x');
try {
    if (!$handle || fwrite($handle, $bytes) !== strlen($bytes) || !fflush($handle)) {
        throw new RuntimeException('M68 incomplete private bank projection.');
    }
} finally { if (is_resource($handle)) { fclose($handle); } }
echo Json::json(['status' => 'pure-m68-bank-author-projection', 'file' => basename($out),
    'sha256' => hash('sha256', $bytes), 'reviewed' => $reviewedCount, 'selected' => $selectedCount,
    'practice_bindings' => $audit['practice_bindings'], 'scope' => $audit['scope'], 'db_writes' => 0])."\n";
