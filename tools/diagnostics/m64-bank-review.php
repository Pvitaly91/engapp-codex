<?php

/** Finite M64 editorial/snapshot projection. Reads reviewed files only; no bootstrap, SQL or HTTP. */
if (PHP_SAPI !== 'cli' || count($argv) !== 1) { throw new RuntimeException('M64 bank review takes no arguments.'); }
$root = 'D:/DEV/htdocs/gramlyze.loc';
if (strtolower(str_replace('\\', '/', (string) realpath(dirname(__DIR__, 2)))) !== strtolower($root)) { throw new RuntimeException('M64 review ROOT differs.'); }
require $root.'/vendor/autoload.php';
use App\Support\M26DetailPackage as Json;
use App\Support\M51QuestionSupplement as Rows;
use App\Support\M64QuestionSupplement as Supplement;
use App\Support\M64ReasonResultContrastPackage as Package;
$dir = $root.'/storage/app/seo-m64-local';
$read = static function (string $name, string $sha) use ($dir): array {
    $path = $dir.'/'.$name;
    if (!is_file($path) || is_link($path) || hash_file('sha256', $path) !== $sha) { throw new RuntimeException('M64 reviewed private input changed.'); }
    return json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
};
$before = $read('before-v1.json', '46296d1b282d4f5c5c54b4eeaf1640680a173bb3e81482f5109ded710609f63d');
$plan = $read('question-plan-v1.json', 'dbd7572184c855edcd88f6e426b51d96cc700737a3500d194095a2c5328b16a8');
[$old, $patch, $master, $contract] = Package::load($root);
Package::validate($old, $patch, $master, $contract);
$source = Supplement::load($root);
$newRows = Rows::materialize($plan['projection'], $plan['ids'], Supplement::ROW_TIMESTAMP);
$newOptions = array_column($newRows['question_options'], null, 'id');
foreach ($plan['projection']['reused_options'] as $option) { $newOptions[$option['id']] = $option['row']; }
$selectedOld = [
    30399 => ['The meeting was postponed because several people were absent.', 'Зустріч перенесли, тому що кілька людей були відсутні.', 'Відсутність людей — причина перенесення. Використай because; почни з The meeting.'],
    30407 => ['Since the deadline was changed, the team adjusted the plan.', 'Оскільки термін змінили, команда скоригувала план.', 'Зміна терміну — відома причина оновлення плану. Почни з Since.'],
    30471 => ['The road was icy, so the bus arrived late.', 'Дорога була слизькою, тому автобус прибув із запізненням.', 'За умовою саме слизька дорога спричинила запізнення. Поєднай частини через so; почни з The road.'],
    30475 => ['The deadline was changed, so the team adjusted the plan.', 'Термін змінили, тому команда скоригувала план.', 'Зміна терміну спричинила оновлення плану. Використай so; почни з The deadline.'],
    30480 => ['Several people were absent, so the meeting was postponed.', 'Кілька людей були відсутні, тому зустріч перенесли.', 'Відсутність учасників спричинила перенесення. Використай so; почни з Several people.'],
    30543 => ['Although the instructions looked simple, the task took two hours.', 'Хоча інструкції здавалися простими, виконання завдання тривало дві години.', 'Через прості на вигляд інструкції очікували швидшого виконання, але знадобилися дві години. Почни з Although.'],
    30548 => ['Despite several absences, the task took two hours.', 'Попри відсутність кількох учасників, виконання завдання тривало дві години.', 'Очікували, що через відсутність учасників робота триватиме довше. Дві години тут вважають швидким завершенням. Почни з Despite.'],
];
$reasons = [
    ['retained cause for postponed meeting', 'same event as selected meeting/because item', 'same meeting event with as; synonym is not a new situation', 'same meeting event with because of; new negative-travel context covers this form distinctly', 'due to is outside the core contrast and repeats meeting event', 'owing to is outside the core contrast and repeats meeting event', 'same meeting event in reversed order', 'duplicate meeting/because event', 'retained changed-deadline cause', 'absent people causing bus delay lacks specified actors/context', 'same underspecified bus-delay causal claim', 'duplicate meeting/because event'],
    ['retained icy-road result', 'isolated therefore outcome repeats bus delay without explicit local cause', 'isolated as a result outcome repeats bus delay', 'consequently item outside core and missing a self-contained cause', 'retained deadline/plan result', 'road/therefore repeats retained road event, unpunctuated display', 'deadline/therefore repeats retained deadline event', 'deadline/as a result repeats retained deadline event', 'duplicate consequently outcome', 'retained absence/postponement result', 'duplicate deadline/therefore event', 'duplicate icy-road/so event'],
    ['retained apparent simplicity versus two-hour duration', 'same instructions/task event with though', 'same instructions/task event with even though', 'isolated however outcome lacks contrast context and repeats task event', 'nevertheless outside core and repeats task outcome', 'retained distinct absence expectation with explicit two-hour success context', 'same absence/task event with in spite of', 'deadline changed then plan adjusted is normally causal, not a self-contained contrast', 'instructions/task however repeats retained event', 'same instructions/task event with punctuation-only source variation', 'duplicate although instructions/task event', 'same instructions/task event with postposed although'],
];
$makeSample = static function (array $snapshot, string $display, string $uk, string $context, bool $new) use ($selectedOld): array {
    $question = $snapshot['question']; $ordered = $snapshot['ordered_answer_instances'];
    $plans = []; $optionPlans = []; $tokens = array_column($ordered, 'option');
    if (!$new) {
        $displayTokens = explode(' ', $display);
        if (count($tokens) !== count($displayTokens)) { throw new RuntimeException('M64 existing token count changed.'); }
        foreach ($ordered as $n => $row) {
            // First-token capitalisation belongs to the unchanged ComposeTokenCase primitive.
            if ($n === 0 && mb_strtolower($displayTokens[$n]) === mb_strtolower($row['option'])) { continue; }
            if ($row['option'] === $displayTokens[$n]) { continue; }
            if (preg_replace('/[^\p{L}\p{N}]+/u', '', mb_strtolower($row['option']))
                !== preg_replace('/[^\p{L}\p{N}]+/u', '', mb_strtolower($displayTokens[$n]))) {
                throw new RuntimeException('M64 existing word replacement is forbidden.');
            }
            $plan = $row + ['raw_option_sha256' => hash('sha256', $row['option']), 'rendered' => $displayTokens[$n],
                'purpose' => 'punctuation_display'];
            if (isset($optionPlans[$row['option_id']])) { throw new RuntimeException('M64 repeated shared option needs conflicting display.'); }
            $plans[] = $plan;
            $optionPlans[$row['option_id']] = ['option_id' => $row['option_id'], 'option' => $row['option'],
                'raw_option_sha256' => $plan['raw_option_sha256'], 'rendered' => $plan['rendered']];
            $tokens[$n] = $displayTokens[$n];
        }
    }
    $tokens = App\Support\ComposeTokenCase::normalize($tokens);
    $sentence = preg_replace('/\s+([,.!?;:])/', '$1', implode(' ', $tokens));
    if ($sentence !== $display) { throw new RuntimeException('M64 strict canonical case/punctuation differs.'); }
    $sample = ['id' => $question['id'], 'uuid' => $question['uuid'], 'bank_seeder' => $question['seeder'], 'is_new' => $new,
        'condition_uk' => $question['question'], 'context_prefix_uk' => $context, 'displayed_condition_uk' => $uk,
        'visible_condition_uk' => $context.' '.$uk, 'semantic_case_review' => 'Reviewed finite M64 case: preserve participants, tense, not and causal/contrast direction; explicit required linker, no arbitrary synonym key.',
        'expected_display_sentence' => $display, 'expected_word_key' => implode(' ', $tokens), 'correct_tokens' => $tokens,
        'token_presentations' => $plans, 'option_presentations' => array_values($optionPlans),
        'question_snapshot' => $question, 'ordered_answer_snapshot' => $ordered,
        'original_options_snapshot' => array_map(static fn ($row) => ['id' => $row['id'], 'option' => $row['option']], $snapshot['options']),
        'option_pivots_snapshot' => $snapshot['option_pivots'], 'hints_snapshot' => [],
        'verb_hints_snapshot' => $snapshot['verb_hints'], 'variants_snapshot' => $snapshot['variants'],
        'theory_links_snapshot' => $snapshot['theory_links'], 'original_snapshot_sha256' => Json::digest($snapshot)];
    foreach (['question_snapshot', 'ordered_answer_snapshot', 'original_options_snapshot', 'option_pivots_snapshot',
        'hints_snapshot', 'verb_hints_snapshot', 'variants_snapshot', 'theory_links_snapshot'] as $field) {
        $sample['snapshot_hashes'][$field] = Json::digest($sample[$field]);
    }
    return $sample;
};
$audit = ['schema_version' => 1, 'package' => 'm64-reason-result-contrast-bank-audit', 'version' => '1.0.0', 'status' => 'frozen',
    'locale' => 'uk', 'independent_before_sha256' => '46296d1b282d4f5c5c54b4eeaf1640680a173bb3e81482f5109ded710609f63d',
    'scope' => ['selected_questions' => 15, 'reused_questions' => 7, 'new_questions' => 8,
        'question_writes' => 8, 'option_writes' => 0, 'answer_writes' => 85, 'option_link_writes' => 103, 'link_writes' => 8,
        'old_hint_writes' => 0, 'old_question_updates' => 0, 'old_option_updates' => 0, 'old_answer_updates' => 0,
        'grader_changes' => 0, 'progress_writes' => 0],
    'pending' => [], 'review_summary' => ['old_eligible_read' => 36, 'new_reviewed' => 8, 'selected' => 15,
        'policy' => 'Seven reused snapshots plus eight planned insert snapshots. Not a claim that planned rows already exist. The guard rechecks allocation/collisions before transaction; old rows and all options remain unchanged.'],
    'targets' => [], 'practice_bindings' => []];
foreach (Package::OWNERS as $i => $owner) {
    $captured = $before['pages'][$owner]; $oldBank = Supplement::OLD_BANKS[$i]; $newBank = Supplement::BANKS[$i];
    $pool = $captured['bank_inventory'][$oldBank]['eligible_ids_uuid_order']; $snapshots = $captured['relations']['question_snapshots'];
    if (count($pool) !== 12) { throw new RuntimeException('M64 original twelve-question pool differs.'); }
    $samples = []; $reviews = [];
    foreach ($pool as $n => $id) {
        $snap = collect($snapshots)->first(static fn ($s) => $s['question']['id'] === $id);
        if (!is_array($snap) || $snap['hints'] !== [] || $snap['verb_hints'] !== []) { throw new RuntimeException('M64 original snapshot/hints differ.'); }
        $reviews[] = ['id' => $id, 'uuid' => $snap['question']['uuid'], 'is_new' => false,
            'condition_uk' => $snap['question']['question'], 'actual_ordered_key' => implode(' ', array_column($snap['ordered_answer_instances'], 'option')),
            'selected' => isset($selectedOld[$id]), 'review' => $reasons[$i][$n], 'full_original_snapshot_sha256' => Json::digest($snap)];
        if (isset($selectedOld[$id])) { $samples[] = $makeSample($snap, ...[...$selectedOld[$id], false]); }
    }
    $newIds = [];
    foreach ($source['banks'][$i]['questions'] as $authored) {
        $id = $plan['ids']['questions'][$authored['uuid']]; $newIds[] = $id;
        $question = collect($newRows['questions'])->firstWhere('id', $id);
        $answers = array_values(array_filter($newRows['question_answers'], static fn ($r) => $r['question_id'] === $id));
        usort($answers, static fn ($a, $b) => strnatcasecmp($a['marker'], $b['marker']));
        $pivots = array_values(array_filter($newRows['question_option_question'], static fn ($r) => $r['question_id'] === $id));
        $options = array_map(static fn ($r) => $newOptions[$r['option_id']], $pivots);
        usort($options, static fn ($a, $b) => $a['id'] <=> $b['id']);
        $snap = ['question' => $question, 'answers' => $answers, 'option_pivots' => $pivots, 'verb_hints' => [], 'hints' => [],
            'variants' => [], 'tags' => [], 'marker_tags' => [], 'options' => $options,
            'theory_links' => array_values(array_filter($newRows['question_theory_text_blocks'], static fn ($r) => $r['question_uuid'] === $authored['uuid'])),
            'ordered_answer_instances' => array_map(static fn ($r) => ['id' => $r['id'], 'marker' => $r['marker'], 'position' => null,
                'option_id' => $r['option_id'], 'option' => $newOptions[$r['option_id']]['option']], $answers)];
        $samples[] = $makeSample($snap, $authored['target_text'], $authored['translation_uk'], $authored['context_uk'], true);
        $reviews[] = ['id' => $id, 'uuid' => $authored['uuid'], 'is_new' => true, 'condition_uk' => $authored['question'],
            'actual_ordered_key' => $authored['target_text'], 'selected' => true, 'review' => 'New distinct M64 situation with explicit syntax and meaning criterion.',
            'planned_full_snapshot_sha256' => Json::digest($snap)];
    }
    usort($samples, static fn ($a, $b) => strcmp($a['uuid'], $b['uuid']));
    $selected = array_column($samples, 'id'); $excluded = array_values(array_diff($pool, $selected)); sort($excluded);
    $audit['targets'][] = ['identity' => $owner, 'local_url' => $captured['target_route']['url'], 'page_id' => Package::PAGE_IDS[$i],
        'category_id' => 34, 'category_ancestry' => $captured['category_ancestry'], 'locale' => 'uk', 'level' => 'B1', 'type' => 4,
        'bank_seeders' => [$oldBank, $newBank], 'old_bank_seeder' => $oldBank, 'expected_old_pool_ids' => $pool,
        'expected_pool_ids_by_bank' => [$oldBank => $pool, $newBank => $newIds],
        'expected_selected_ids' => $selected, 'expected_selected_uuids' => array_column($samples, 'uuid'),
        'excluded_question_ids' => $excluded, 'first_five' => $samples, 'all_eligible_review' => $reviews];
    $practice = $patch['targets'][$i]['after']['page']['blocks'][7];
    $audit['practice_bindings'][] = ['identity' => $owner, 'page_id' => Package::PAGE_IDS[$i], 'category_id' => 34,
        'locale' => 'uk', 'source_index' => 7, 'uuid' => Json::uuid($owner, $practice, 8),
        'type' => 'practice-set', 'column' => $practice['column'], 'heading' => $practice['heading'] ?? null,
        'level' => 'B1', 'css_class' => $practice['css_class'] ?? null, 'body_sha256' => hash('sha256', $practice['body']),
        'linked_practice' => json_decode($practice['body'], true, flags: JSON_THROW_ON_ERROR)['linked_practice']];
}
$path = $dir.'/bank-review-projection-v1.json';
if (file_exists($path) || is_link($path)) { throw new RuntimeException('M64 review receipt exists; no overwrite.'); }
$bytes = json_encode($audit, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n";
$file = fopen($path, 'x');
try { if (!$file || fwrite($file, $bytes) !== strlen($bytes) || !fflush($file)) { throw new RuntimeException('M64 bank review receipt incomplete.'); } }
finally { if (is_resource($file)) { fclose($file); } }
echo Json::json(['status' => 'pure-bank-review', 'file' => basename($path), 'sha256' => hash('sha256', $bytes),
    'targets_sha256' => Json::digest($audit['targets']), 'selected_ids' => array_column($audit['targets'], 'expected_selected_ids'),
    'db_writes' => 0])."\n";
