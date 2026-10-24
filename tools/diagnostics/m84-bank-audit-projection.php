<?php

/** Pure finite bank projection: pinned BEFORE + author + allocation inventory + independent selection. */
if (PHP_SAPI !== 'cli' || PHP_OS_FAMILY !== 'Windows' || count($argv) !== 8
    || preg_match('/^author-projection-v[1-9][0-9]*\.json$/D', $argv[1]) !== 1
    || preg_match('/^question-inventory-v[1-9][0-9]*\.json$/D', $argv[3]) !== 1
    || preg_match('/^widget-selection-v[1-9][0-9]*\.json$/D', $argv[5]) !== 1
    || preg_match('/^bank-audit-projection-v[1-9][0-9]*\.json$/D', $argv[7]) !== 1) {
    throw new RuntimeException('M84 requires author basename/SHA, inventory basename/SHA, selection basename/SHA, exclusive output basename.');
}
foreach ([2, 4, 6] as $n) {
    if (preg_match('/^[a-f0-9]{64}$/D', $argv[$n]) !== 1) { throw new RuntimeException('M84 requires exact independently reviewed input hashes.'); }
}
$root = 'D:/DEV/htdocs/gramlyze.loc'; $wt = $root.'/storage/app/m75-worktree'; $private = $root.'/storage/app/seo-m84-local';
$normal = static fn ($path) => strtolower(str_replace('\\', '/', (string) realpath($path)));
if ($normal(dirname(__DIR__, 2)) !== $normal($root) || !is_dir($private) || is_link($private) || is_link($wt)) {
    throw new RuntimeException('M84 bank projection exact served/source/private paths differ.');
}
$out = $private.'/'.$argv[7];
if (file_exists($out) || is_link($out)) { throw new RuntimeException('M84 exclusive bank output already exists or is linked.'); }
$read = static function (string $path, string $sha): array {
    if (preg_match('/^[a-f0-9]{64}$/D', $sha) !== 1 || !is_file($path) || is_link($path)
        || !hash_equals($sha, hash_file('sha256', $path))) {
        throw new RuntimeException('M84 exact bank input missing, linked or changed: '.basename($path));
    }
    return json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
};
$beforeSha = 'babac2b3efa88b87fc8ea50b1ad45fb9feb8cadfdf766c33e76ca7b24aa19916';
$pairedSha = '9066938a376b3f4eeae6fa65f6fea6afc7a2bdd624a4ed93dc32e37c59b8e47e';
$before = $read($private.'/before-v1.json', $beforeSha);
$paired = $read($private.'/independent-source-before-v1.json', $pairedSha);
$authorSha = $argv[2]; $inventorySha = $argv[4]; $selectionSha = $argv[6];
$author = $read($private.'/'.$argv[1], $authorSha);
$inventory = $read($private.'/'.$argv[3], $inventorySha);
$selection = $read($private.'/'.$argv[5], $selectionSha);
require $root.'/vendor/autoload.php';
require_once $wt.'/app/Support/TheoryNativeContentRelease.php';
require_once $wt.'/app/Support/TheoryBuilderSelectionPresentation.php';
// Current reviewed package guards live in served ROOT; frozen teaching JSON is read from the delivery worktree.
require_once $root.'/app/Support/M84WordOrderHaveGot.php';
require_once $root.'/app/Support/M84QuestionSupplement.php';

use App\Models\Question;
use App\Models\QuestionAnswer;
use App\Models\QuestionHint;
use App\Models\QuestionOption;
use App\Support\ComposeTokenCase;
use App\Support\M26DetailPackage as Digest;
use App\Support\M51QuestionSupplement as Rows;
use App\Support\M84QuestionSupplement as Supplement;
use App\Support\M84WordOrderHaveGot as Package;
use App\Support\TheoryBuilderSelectionPresentation;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Collection;

if (($before['base_sha'] ?? null) !== Package::BASE_SHA || ($author['source_before']['base_sha'] ?? null) !== Package::BASE_SHA
    || ($before['sql_before'] ?? null) !== ($before['sql_after_http'] ?? null)
    || ($inventory['base_sha'] ?? null) !== Package::BASE_SHA || ($inventory['before_sha256'] ?? null) !== $beforeSha
    || ($inventory['sql_before'] ?? null) !== $before['sql_before'] || ($inventory['identity'] ?? null) !== $before['identity']
    || ($selection['independent_before_sha256'] ?? null) !== $beforeSha
    || array_column($selection['targets'] ?? [], 'identity') !== Package::OWNERS
    || array_column($author['native_bindings']['bindings'] ?? [], 'identity') !== Package::OWNERS
    || ($author['db_connections'] ?? null) !== 0 || ($author['db_writes'] ?? null) !== 0 || ($author['http_requests'] ?? null) !== 0
    || ($inventory['db_writes'] ?? null) !== 0 || ($inventory['http_requests'] ?? null) !== 0) {
    throw new RuntimeException('M84 independent bank/author/allocation/selection lineage differs.');
}
if (isset($author['widget_selection_sha256']) && $author['widget_selection_sha256'] !== $selectionSha) {
    throw new RuntimeException('M84 author selected-question review hash differs.');
}
(new App\Support\TheoryNativeContentRelease(Package::draftProfile($author['native_bindings']['bindings'])))
    ->validate($author['source_before'], $author['patch'], $author['master'], $author['contract']);
Package::assertEditorial($author['source_before'], $author['patch'], $author['master'], $author['navigation_review'] ?? []);
$supplement = $read($wt.'/'.Supplement::SOURCE, $inventory['source_sha256'] ?? '');
Supplement::validate($supplement);
$expectedProjection = Supplement::project($supplement, $inventory['bindings'], $inventory['option_matches']);
if ($expectedProjection !== $inventory['projection']
    || Rows::materialize($expectedProjection, $inventory['ids'], $inventory['row_timestamp']) !== $inventory['materialized']
    || $inventory['bindings'] !== Supplement::bindings() || ($inventory['id_step_offset'] ?? null) !== [1, 1]) {
    throw new RuntimeException('M84 full symbolic/materialized inventory differs from its independently pinned source.');
}
Rows::assertAbsent($inventory['question_collisions']);
if (count($inventory['materialized']['questions'] ?? []) !== 25
    || array_column($inventory['materialized']['questions'], 'uuid') !== Supplement::UUIDS) {
    throw new RuntimeException('M84 complete twenty-five insert-only candidate inventory differs.');
}

/** Exact full original question and loaded relation snapshots, matching the unchanged presenter. */
$runtimeParts = static function (array $snapshot): array {
    $q = $snapshot['question']; $keys = array_keys($q); $expected = Rows::ROW_COLUMNS['questions'];
    sort($keys); sort($expected);
    if ($keys !== $expected) { throw new RuntimeException('M84 question snapshot is not the complete fourteen-column record.'); }
    $ordered = []; foreach (Rows::ROW_COLUMNS['questions'] as $key) { $ordered[$key] = $q[$key]; }
    $options = array_map(static fn ($r) => ['id' => $r['id'], 'option' => $r['option']], $snapshot['options']);
    usort($options, static fn ($a, $b) => $a['id'] <=> $b['id']);
    if (count(array_unique(array_column($options, 'id'))) !== count($options)) { throw new RuntimeException('M84 repeated original option identity.'); }
    $optionMap = array_column($options, 'option', 'id'); $answers = [];
    foreach ($snapshot['answers'] as $row) {
        if ($row['question_id'] !== $q['id'] || !isset($optionMap[$row['option_id']])) { throw new RuntimeException('M84 original answer/option ownership differs.'); }
        $answers[] = ['id' => $row['id'], 'marker' => $row['marker'], 'position' => $row['position'] ?? null,
            'option_id' => $row['option_id'], 'option' => $optionMap[$row['option_id']]];
    }
    usort($answers, static fn ($a, $b) => strnatcasecmp($a['marker'], $b['marker']));
    if ($answers !== $snapshot['ordered_answer_instances'] || count(array_unique(array_column($answers, 'marker'))) !== count($answers)) {
        throw new RuntimeException('M84 ordered answer instances differ from complete captured families.');
    }
    $pivots = array_map(static fn ($r) => ['question_id' => $r['question_id'], 'option_id' => $r['option_id'], 'flag' => $r['flag']], $snapshot['option_pivots']);
    usort($pivots, static fn ($a, $b) => $a['option_id'] <=> $b['option_id']);
    if (array_column($pivots, 'option_id') !== array_column($options, 'id')
        || array_filter($pivots, static fn ($r) => $r['question_id'] !== $q['id'] || !in_array($r['flag'], [null, 0], true))) {
        throw new RuntimeException('M84 original loaded option pivots differ.');
    }
    $hints = array_map(static fn ($r) => ['id' => $r['id'], 'question_id' => $r['question_id'],
        'provider' => $r['provider'], 'locale' => $r['locale'], 'hint' => $r['hint']], $snapshot['hints']);
    if ($snapshot['verb_hints'] !== []) { throw new RuntimeException('M84 compose candidate unexpectedly has verb hints.'); }
    return ['question' => $ordered, 'answers' => $answers, 'options' => $options, 'pivots' => $pivots, 'hints' => $hints];
};
$snapshotHashes = static function (array $snapshot) use ($runtimeParts): array {
    $r = $runtimeParts($snapshot);
    return ['question_snapshot' => Digest::digest($r['question']), 'ordered_answer_snapshot' => Digest::digest($r['answers']),
        'original_options_snapshot' => Digest::digest($r['options']), 'loaded_option_pivots' => Digest::digest($r['pivots']),
        'hints_snapshot' => Digest::digest($r['hints']), 'verb_hints_snapshot' => Digest::digest([])];
};
$publicHashes = static fn (array $s, array $links) => [
    'full_private_snapshot_sha256' => Digest::digest($s), 'snapshot_hashes' => $snapshotHashes($s),
    'variants_sha256' => Digest::digest($s['variants']), 'links_sha256' => Digest::digest($links),
    'all_original_option_attributes_sha256' => Digest::digest($s['options']),
    'all_original_answer_attributes_sha256' => Digest::digest($s['answers']),
    'all_original_option_pivot_attributes_sha256' => Digest::digest($s['option_pivots']),
    'all_original_tag_relations_sha256' => Digest::digest(['tags' => $s['tags'], 'marker_tags' => $s['marker_tags']]),
];

$optionRows = array_column($inventory['materialized']['question_options'], null, 'id');
foreach ($inventory['projection']['reused_options'] as $reuse) {
    if (isset($optionRows[$reuse['id']]) || $reuse['row']['id'] !== $reuse['id']
        || Digest::digest($reuse['row']) !== $reuse['sha256']) { throw new RuntimeException('M84 reused option complete snapshot differs.'); }
    $optionRows[$reuse['id']] = $reuse['row'];
}
$newSnapshots = [];
foreach ($inventory['materialized']['questions'] as $q) {
    $id = $q['id']; $uid = $q['uuid'];
    $answers = array_values(array_filter($inventory['materialized']['question_answers'], static fn ($r) => $r['question_id'] === $id));
    $pivots = array_values(array_filter($inventory['materialized']['question_option_question'], static fn ($r) => $r['question_id'] === $id));
    usort($answers, static fn ($a, $b) => $a['id'] <=> $b['id']); usort($pivots, static fn ($a, $b) => $a['id'] <=> $b['id']);
    $options = []; foreach ($pivots as $pivot) {
        if (!isset($optionRows[$pivot['option_id']])) { throw new RuntimeException('M84 materialized pivot refers to an unavailable complete option.'); }
        $options[] = $optionRows[$pivot['option_id']];
    }
    usort($options, static fn ($a, $b) => $a['id'] <=> $b['id']); $byId = array_column($options, 'option', 'id');
    $orderedAnswers = array_map(static fn ($r) => ['id' => $r['id'], 'marker' => $r['marker'], 'position' => null,
        'option_id' => $r['option_id'], 'option' => $byId[$r['option_id']]], $answers);
    usort($orderedAnswers, static fn ($a, $b) => strnatcasecmp($a['marker'], $b['marker']));
    $newSnapshots[$uid] = ['question' => $q, 'answers' => $answers, 'option_pivots' => $pivots,
        'verb_hints' => [], 'hints' => [], 'variants' => [], 'tags' => [], 'marker_tags' => [],
        'options' => $options, 'ordered_answer_instances' => $orderedAnswers];
    $runtimeParts($newSnapshots[$uid]);
}
$scope = '30 selected / 5 reused / 25 new; insert-only question supplement; no old question/answer/option/grader/progress writes';
$audit = ['schema_version' => 1, 'package' => 'm84-word-order-have-got-bank-audit', 'version' => '1.0.0', 'status' => 'draft', 'locale' => 'uk',
    'independent_before_sha256' => $beforeSha, 'scope' => $scope,
    'pending' => ['Independent main review/freeze, guarded local insert/apply, exact full-question/widget browser acceptance and old-row preservation proof.'],
    'privacy_policy' => 'Teaching identity/conditions/tokens/options and opaque snapshot hashes only; no raw SQL families, timestamps, runtime identity, secrets or progress.',
    'source_proof' => ['author_projection_sha256' => $authorSha, 'supplement_inventory_sha256' => $inventorySha,
        'widget_selection_sha256' => $selectionSha, 'supplement_source_sha256' => $inventory['source_sha256'],
        'supplement_source_status' => $supplement['status'], 'paired_source_before_sha256' => $pairedSha],
    'review_summary' => ['pagewide_original_type4_pool' => 432, 'actual_filtered_pool' => 457, 'selected' => 30,
        'reused' => 5, 'new' => 25, 'technical_filter' => Package::LEVELS, 'intended_selected_levels' => Package::FOCUS_LEVELS,
        'null_policy' => 'All six actual practices are SQL NULL; no exact CEFR filter. Editorial focus is not a filter rewrite. Each Word Order pool retains old72 plus new5; Have Got retains old72.',
        'boundary' => 'Old Word Order type4 fragment questions remain unchanged and out of the finite full-sentence selection. They are not declared globally invalid for their original consumers.', 'human_review' => false],
    'clone_only_policy' => 'Complete fourteen-column question plus loaded option/answer/pivot/hint snapshots must match; only visible Ukrainian condition may be projected on unsaved canonical-theory clones. Original models, formatter and grader remain unchanged.',
    'formatter_review' => ['native_case_source_sha256' => hash_file('sha256', $root.'/app/Support/ComposeTokenCase.php'),
        'settled_string_policy' => 'Actual unchanged native methods replayed in two pinned source profiles; lexical content must remain exact after Check. Case/punctuation normalization is recorded separately.',
        'browser_acceptance' => 'not-claimed-by-offline-projection'],
    'targets' => [], 'practice_bindings' => []];
$privateSelected = []; $totalNew = 0; $totalReused = 0;
foreach (Package::OWNERS as $i => $owner) {
    $page = $before['pages'][$owner]; $declaration = $author['contract']['targets'][$i]; $level = Package::LEVELS[$i];
    $slot = Package::PRACTICE_SOURCE_INDICES[$i]; $block = $author['patch']['targets'][$i]['after']['page']['blocks'][$slot];
    $data = json_decode($block['body'], true, flags: JSON_THROW_ON_ERROR);
    $banks = $i < 5 ? [Package::BANKS[$i], Supplement::BANKS[$i]] : [Package::BANKS[$i]];
    if ($declaration['identity'] !== $owner || $declaration['level'] !== $level || $declaration['bank_seeders'] !== $banks
        || ($block['level'] ?? null) !== $level || $page['page']['id'] !== Package::PAGE_IDS[$i]
        || ($data['linked_practice']['source'] ?? null) !== 'theory_links'
        || ($data['linked_practice']['question_types'] ?? null) !== ['4'] || ($data['linked_practice']['seeder_classes'] ?? null) !== $banks
        || array_column($page['category_ancestry'], 'slug') !== Package::ANCESTRIES[$i]) {
        throw new RuntimeException('M84 exact owner/category/ancestry/practice/bank/filter binding differs.');
    }
    $snapshots = $page['relations']['question_snapshots']; $links = $page['relations']['question_theory_text_blocks'];
    if ($i < 5) {
        foreach ($newSnapshots as $uid => $snapshot) {
            if ($snapshot['question']['seeder'] !== Supplement::BANKS[$i]) { continue; }
            if (isset($snapshots[$uid])) { throw new RuntimeException('M84 new candidate collides with an original snapshot.'); }
            $snapshots[$uid] = $snapshot;
        }
        $links = [...$links, ...array_values(array_filter($inventory['materialized']['question_theory_text_blocks'],
            static fn ($r) => isset($newSnapshots[$r['question_uuid']]) && $newSnapshots[$r['question_uuid']]['question']['seeder'] === Supplement::BANKS[$i]))];
    }
    $pool = []; $poolByBank = [];
    foreach ($banks as $bankIndex => $bank) {
        $rows = array_values(array_filter($snapshots, static fn ($s) => $s['question']['seeder'] === $bank
            && (string) $s['question']['type'] === '4' && ($level === null || $s['question']['level'] === $level)));
        $expected = $bankIndex === 0 ? 72 : 5;
        if (count($rows) !== $expected) { throw new RuntimeException('M84 exact old-plus-new eligible thematic population differs.'); }
        $poolByBank[$bank] = array_column(array_column($rows, 'question'), 'id'); sort($poolByBank[$bank]);
        foreach ($rows as $s) {
            $id = $s['question']['id'];
            if (isset($pool[$id])) { throw new RuntimeException('M84 duplicate pool question ID.'); }
            $pool[$id] = $s;
        }
    }
    $selected = $selection['targets'][$i]['selected'] ?? []; $selectedIds = array_column($selected, 'id');
    if (count($selected) !== 5 || count(array_unique($selectedIds)) !== 5) { throw new RuntimeException('M84 each owner needs an independent five-question selection.'); }
    $nativeOrder = $selectedIds;
    usort($nativeOrder, static fn ($a, $b) => strcmp($pool[$a]['question']['uuid'] ?? '', $pool[$b]['question']['uuid'] ?? ''));
    if ($nativeOrder !== $selectedIds) { throw new RuntimeException('M84 selected order differs from unchanged native UUID ordering.'); }
    $excluded = array_values(array_diff(array_keys($pool), $selectedIds)); sort($excluded);
    $linksFor = static fn ($uid) => array_values(array_filter($links, static fn ($r) => $r['question_uuid'] === $uid));
    $ukUuids = array_column(array_filter($page['blocks'], static fn ($r) => $r['locale'] === 'uk' && $r['seeder'] === $owner), 'uuid');
    $target = ['identity' => $owner, 'page_id' => Package::PAGE_IDS[$i], 'category_id' => Package::CATEGORY_IDS[$i],
        'category_ancestry' => array_map(static fn ($slug) => ['slug' => $slug], Package::ANCESTRIES[$i]),
        'local_url' => $declaration['local_url'], 'locale' => 'uk', 'level' => $level, 'intended_selected_level' => Package::FOCUS_LEVELS[$i],
        'selected_levels' => array_column($selected, 'level'), 'type' => 4, 'bank_seeders' => $banks,
        'expected_pool_ids_by_bank' => $poolByBank, 'expected_selected_ids' => $selectedIds,
        'expected_selected_uuids' => array_column($selected, 'uuid'), 'excluded_question_ids' => $excluded,
        'all_pool_snapshots' => [], 'all_eligible_review' => [],
        'scope_exclusions' => ['ids' => $excluded, 'reason' => 'Finite full-sentence selection only; original fragment models/keys/options retained for their consumers.'],
        'known_scope_exclusions' => [], 'first_five' => []];
    ksort($pool);
    foreach ($pool as $id => $snapshot) {
        $q = $snapshot['question'];
        $target['all_pool_snapshots'][] = ['id' => $id, 'uuid' => $q['uuid'], 'level' => $q['level'], ...$publicHashes($snapshot, $linksFor($q['uuid']))];
        if (in_array($id, $excluded, true)) {
            $target['all_eligible_review'][] = ['id' => $id, 'uuid' => $q['uuid'], 'decision' => 'scope_exclusion',
                'reason' => $i < 5 ? 'Original thematic type4 remains a fragment/gap model; it is not substituted for a required full-sentence widget question.'
                    : 'Outside this finite reviewed five; not a claim of global invalidity.'];
        }
    }
    foreach ($selected as $n => $sample) {
        $s = $pool[$sample['id']] ?? null; $q = $s['question'] ?? null;
        if (!is_array($s) || $q['uuid'] !== $sample['uuid'] || $q['level'] !== $sample['level']
            || $q['question'] !== $sample['source'] || $sample['level'] !== ($selection['targets'][$i]['selected_levels'][$n] ?? null)
            || $sample['level'] !== Package::FOCUS_LEVELS[$i]) { throw new RuntimeException('M84 selected identity/level/source differs from full snapshot.'); }
        $r = $runtimeParts($s); $rawTokens = array_column($r['answers'], 'option'); $tokens = ComposeTokenCase::normalize($rawTokens);
        if ($tokens !== $sample['tokens'] || $rawTokens !== $sample['raw_tokens'] || ComposeTokenCase::normalize($tokens) !== $tokens
            || array_intersect(array_column($linksFor($q['uuid']), 'text_block_uuid'), $ukUuids) === []) {
            throw new RuntimeException('M84 selected raw/native tokens, multiplicity or exact UK owner links differ.');
        }
        foreach (['condition', 'translation', 'settled', 'coverage'] as $key) {
            if (!is_string($sample[$key] ?? null) || trim($sample[$key]) === '') { throw new RuntimeException('M84 selected educational review field is empty.'); }
        }
        if (preg_match('/[\x{0400}-\x{04FF}]/u', $sample['condition']) !== 1
            || preg_match('/<[^>]+>|\{a[0-9]+\}|&(?:#[0-9]+|[a-z]+);/iu', $sample['condition'])) {
            throw new RuntimeException('M84 selected condition must be plain Ukrainian, not technical text.');
        }
        $isNew = isset($newSnapshots[$q['uuid']]);
        if ($isNew !== ($i < 5) || ($isNew && $q['seeder'] !== Supplement::BANKS[$i])) { throw new RuntimeException('M84 twenty-five new / five reused boundary differs.'); }
        $isNew ? $totalNew++ : $totalReused++;
        $target['first_five'][] = ['id' => $q['id'], 'uuid' => $q['uuid'], 'is_new' => $isNew,
            'question_attribute_keys' => Rows::ROW_COLUMNS['questions'], 'displayed_condition_uk' => $q['question'],
            'visible_condition_uk' => $sample['condition'], 'translation_uk' => $sample['translation'],
            'correct_tokens' => $tokens, 'raw_correct_tokens' => $rawTokens, 'expected_word_key' => implode(' ', $tokens),
            'actual_correct_visible' => $sample['settled'], 'actual_selected_visible' => $sample['settled'],
            'source_ordered_instance_count' => count($r['answers']), 'original_option_texts' => array_column($r['options'], 'option'),
            'token_bank_with_multiplicity' => ComposeTokenCase::mergeOptions(array_column($r['options'], 'option'), $rawTokens),
            'token_presentations' => [], 'option_presentations' => [], ...$publicHashes($s, $linksFor($q['uuid']))];
        $target['all_eligible_review'][] = ['id' => $q['id'], 'uuid' => $q['uuid'], 'decision' => 'selected',
            'function' => $sample['coverage'], 'key_policy' => 'Exact reviewed sequence and focused condition; no new parser, formatter or arbitrary permutation acceptance.'];
        $privateSelected[$i][] = $s;
    }
    $audit['targets'][] = $target;
    $audit['practice_bindings'][] = ['identity' => $owner, 'page_id' => Package::PAGE_IDS[$i], 'category_id' => Package::CATEGORY_IDS[$i],
        'locale' => 'uk', 'source_index' => $slot, 'uuid' => Digest::uuid($owner, $block, $slot + 1), 'type' => $block['type'],
        'column' => $block['column'], 'heading' => $block['heading'] ?? null, 'level' => $level, 'css_class' => $block['css_class'] ?? null,
        'body_sha256' => hash('sha256', $block['body']), 'linked_practice' => $data['linked_practice']];
}
if ([$totalNew, $totalReused] !== [25, 5]
    || array_map(static fn ($t) => array_sum(array_map('count', $t['expected_pool_ids_by_bank'])), $audit['targets']) !== [77, 77, 77, 77, 77, 72]) {
    throw new RuntimeException('M84 exact full-sentence selection and old/new pool vector differs.');
}

/** Rehydrate complete rows in memory only, then use the unchanged exact private matcher. */
$model = static function (string $class, array $attributes): object { $m = new $class; $m->setRawAttributes($attributes, true); return $m; };
$hydrate = static function (array $s) use ($model): Question {
    $q = $model(Question::class, $s['question']); $options = []; $map = []; $pivots = array_column($s['option_pivots'], null, 'option_id');
    foreach ($s['options'] as $row) {
        $o = $model(QuestionOption::class, $row); $p = $pivots[$row['id']];
        $o->setRelation('pivot', $model(Pivot::class, ['question_id' => $p['question_id'], 'option_id' => $p['option_id'], 'flag' => $p['flag']]));
        $map[$row['id']] = $o; $options[] = $o;
    }
    $answers = [];
    foreach ($s['answers'] as $row) { $a = $model(QuestionAnswer::class, $row); $a->setRelation('option', $map[$row['option_id']]); $answers[] = $a; }
    $q->setRelation('answers', new Collection($answers)); $q->setRelation('options', new Collection($options));
    $q->setRelation('hints', new Collection(array_map(static fn ($r) => $model(QuestionHint::class, $r), $s['hints'])));
    $q->setRelation('verbHints', new Collection); return $q;
};
$matcher = new ReflectionMethod(TheoryBuilderSelectionPresentation::class, 'matchesQuestions'); $checks = 0;
foreach ($audit['targets'] as $i => $target) {
    $questions = new Collection(array_map($hydrate, $privateSelected[$i]));
    $originals = array_map(static fn ($q) => ['attributes' => $q->getAttributes(), 'answers' => array_map(static fn ($a) => $a->getAttributes(), $q->getRelation('answers')->all()),
        'options' => array_map(static fn ($o) => $o->getAttributes(), $q->getRelation('options')->all())], $questions->all());
    if ($matcher->invoke(null, $questions, $target) !== true) { throw new RuntimeException('M84 exact complete in-memory canonical selection rejected.'); } $checks++;
    $reject = static function (Collection $copy) use ($matcher, $target, &$checks): void {
        if ($matcher->invoke(null, $copy, $target) !== false) { throw new RuntimeException('M84 exact matcher accepted a deliberately stale/incomplete/foreign relation or decoration.'); }
        $checks++;
    };
    $copy = $questions->map(static fn ($q) => clone $q); $q = $copy->first();
    $q->setRawAttributes(array_replace($q->getAttributes(), ['question' => $q->question.' stale']), true); $reject($copy);
    $copy = $questions->map(static fn ($q) => clone $q); $q = $copy->first(); $options = $q->getRelation('options')->map(static fn ($o) => clone $o);
    $options->first()->setRawAttributes(array_replace($options->first()->getAttributes(), ['option' => $options->first()->option.' stale']), true);
    $q->setRelation('options', $options); $reject($copy);
    $copy = $questions->map(static fn ($q) => clone $q); $copy->first()->unsetRelation('hints'); $reject($copy);
    $copy = $questions->map(static fn ($q) => clone $q); $q = $copy->first(); $answers = $q->getRelation('answers')->map(static fn ($a) => clone $a);
    $answers->first()->setRawAttributes(array_replace($answers->first()->getAttributes(), ['marker' => 'a999']), true);
    $q->setRelation('answers', $answers); $reject($copy);
    $copy = $questions->map(static fn ($q) => clone $q); $q = $copy->first(); $options = $q->getRelation('options')->map(static fn ($o) => clone $o);
    $pivot = clone $options->first()->getRelation('pivot'); $pivot->setRawAttributes(array_replace($pivot->getAttributes(), ['flag' => 1]), true);
    $options->first()->setRelation('pivot', $pivot); $q->setRelation('options', $options); $reject($copy);
    $reject($questions->reverse()->values()); $reject($questions->slice(0, 4)->values());
    $copy = $questions->map(static fn ($q) => clone $q); $q = $copy->first();
    $q->setRawAttributes($q->getAttributes() + ['unreviewed_decoration' => null], true); $reject($copy);
    $copy = $questions->map(static fn ($q) => clone $q); $q = $copy->first();
    $q->setRawAttributes($q->getAttributes() + ['match_score' => 1, 'matched_tag_ids' => [], 'marker_tags' => []], true); $reject($copy);
    $copy = $questions->map(static fn ($q) => clone $q); $q = $copy->first();
    $q->setRawAttributes($q->getAttributes() + ['match_score' => null, 'matched_tag_ids' => [], 'marker_tags' => []], true);
    if ($matcher->invoke(null, $copy, $target) !== true) { throw new RuntimeException('M84 exact allowed NULL decoration rejected.'); } $checks++;
    $after = array_map(static fn ($q) => ['attributes' => $q->getAttributes(), 'answers' => array_map(static fn ($a) => $a->getAttributes(), $q->getRelation('answers')->all()),
        'options' => array_map(static fn ($o) => $o->getAttributes(), $q->getRelation('options')->all())], $questions->all());
    if ($originals !== $after) { throw new RuntimeException('M84 in-memory probes mutated original model attributes or relation rows.'); } $checks++;
}
$audit['shared_model_matcher_checks'] = $checks;

/** Validate the full public policy through the unchanged shared validator, without loading active artifacts. */
final class M84BankProjectionPackage
{
    public const OWNERS = Package::OWNERS;
    public const PAGE_IDS = Package::PAGE_IDS;
    public const CATEGORY_IDS = Package::CATEGORY_IDS;
    public const LEVELS = Package::LEVELS;
    public const ANCESTRIES = Package::ANCESTRIES;
    public const CONTINUING_COUNTS = Package::CONTINUING_COUNTS;
    public const PRACTICE_SOURCE_INDICES = Package::PRACTICE_SOURCE_INDICES;
}
final class M84BankAuditProjectionValidator extends TheoryBuilderSelectionPresentation
{
    public static string $targetDigest;
    protected static function packageClass(): string { return M84BankProjectionPackage::class; }
    protected static function policy(): array
    {
        return ['label' => 'M84 projection', 'package' => 'm84-word-order-have-got-bank-audit', 'version' => '1.0.0',
            'scope' => '30 selected / 5 reused / 25 new; insert-only question supplement; no old question/answer/option/grader/progress writes',
            'before_sha256' => 'babac2b3efa88b87fc8ea50b1ad45fb9feb8cadfdf766c33e76ca7b24aa19916', 'targets_sha256' => self::$targetDigest];
    }
}
M84BankAuditProjectionValidator::$targetDigest = Digest::digest($audit['targets']);
$validation = $audit; $validation['status'] = 'frozen'; $validation['pending'] = [];
M84BankAuditProjectionValidator::validateAudit($validation, $author['patch'], $author['contract']);

/** Actual unchanged native formatter/checker methods; no browser transport or application state. */
$bladeRelative = 'resources/views/components/text-block-practice-questions.blade.php'; $profiles = [];
foreach (['served_root' => $root, 'worktree' => $wt] as $context => $base) {
    $sha = $paired['files'][$bladeRelative][$context]['raw_sha256'] ?? null;
    if (!is_string($sha) || !hash_equals($sha, hash_file('sha256', $base.'/'.$bladeRelative))) {
        throw new RuntimeException('M84 unchanged native browser component differs from fresh paired BEFORE.');
    }
    $profiles[] = ['label' => $context, 'blade' => $base.'/'.$bladeRelative, 'raw_sha256' => $sha];
}
$probeSamples = [];
foreach ($audit['targets'] as $target) { foreach ($target['first_five'] as $sample) {
    $probeSamples[] = ['id' => $sample['id'], 'condition' => $sample['visible_condition_uk'], 'correct' => $sample['correct_tokens'],
        'bank' => $sample['token_bank_with_multiplicity'], 'settled' => $sample['actual_correct_visible']];
} }
$node = <<<'JS'
const fs=require('node:fs'),vm=require('node:vm'),crypto=require('node:crypto'),assert=require('node:assert/strict');
const payload=JSON.parse(fs.readFileSync(0,'utf8')),hash=x=>crypto.createHash('sha256').update(x).digest('hex');
function profile(p){
 const bytes=fs.readFileSync(p.blade);assert.equal(hash(bytes),p.raw_sha256);const text=bytes.toString('utf8').replace(/\r\n/g,'\n');
 function take(a,b){const i=text.indexOf(a),j=text.indexOf(b,i);assert.ok(i>=0&&j>i);assert.equal(text.indexOf(a,i+a.length),-1);return text.slice(i,j);}
 const source=take('                composeCorrectText() {','                getDisplayText() {')+take('                checkAnswer() {','                hasMoreQuestions() {');
 const methods=vm.runInNewContext('({'+source+'})',{window:{}},{timeout:1000});
 const lexical=x=>String(x).toLowerCase().replace(/[\u2018\u2019]/g,"'").replace(/[^\p{L}\p{N}']+/gu,' ').replace(/\s+/g,' ').trim();
 function run(s,tokens){const m={...methods,currentQuestion:{question:s.condition,correct_tokens:s.correct,type:'4',authored_compose:false},
  selectedTokens:tokens.map((value,id)=>({id,value})),answered:false,isCorrect:false,correctCount:0,currentIndex:0,answeredIndices:[],
  isComposeQuestion(){return true;},persistState(){}};
  m.correctAnswer=m.composeCorrectText();assert.equal(m.correctAnswer,s.settled,'Canonical actual formatter string: '+s.id);m.checkAnswer();
  const score=m.correctCount;m.checkAnswer();assert.equal(m.correctCount,score);return{correct:m.isCorrect,display:m.composeSelectedText()};}
 const selected=payload.selected.map(s=>{
  const canonical=run(s,s.correct);assert.equal(canonical.correct,true);assert.equal(canonical.display,s.settled);assert.equal(run(s,[]).correct,false);
  const capacity=new Map;for(const t of s.bank)capacity.set(t,(capacity.get(t)||0)+1);
  const possible=tokens=>{const seen=new Map;for(const t of tokens){const n=(seen.get(t)||0)+1;seen.set(t,n);if(n>(capacity.get(t)||0))return false;}return true;};
  const cases=new Map,add=tokens=>{if(possible(tokens))cases.set(JSON.stringify(tokens),tokens);};add([]);
  for(let i=0;i<s.correct.length;i++){
   add([...s.correct.slice(0,i),...s.correct.slice(i+1)]);
   for(let len=1;len<=3&&i+len<=s.correct.length;len++)for(const option of s.bank)add([...s.correct.slice(0,i),option,...s.correct.slice(i+len)]);
   for(let j=i+1;j<s.correct.length;j++){const copy=[...s.correct];[copy[i],copy[j]]=[copy[j],copy[i]];add(copy);}
   if(/^[?.!,;:]$/.test(s.correct[i]))for(let at=0;at<s.correct.length;at++){const copy=[...s.correct];const[t]=copy.splice(i,1);copy.splice(at,0,t);add(copy);}
  }
  const wrong=[],formatting=[];let accepted=0;for(const tokens of cases.values()){const r=run(s,tokens);if(r.correct){accepted++;if(r.display!==s.settled){
   const item={tokens,visible:r.display};(lexical(r.display)===lexical(s.settled)?formatting:wrong).push(item);}}}
  assert.equal(wrong.length,0,'Correct score with wrong lexical visible string: '+s.id);
  return{id:s.id,canonical_check:true,empty_rejected:true,repeated_check_stable:true,adversarial_sequences:cases.size,
   accepted_sequences:accepted,wrong_lexical_visible_scored_correct:wrong,formatting_only_normalization_matches:formatting.length,formatting_only_examples:formatting.slice(0,3)};
 });
 return{profile:p.label,kind:'actual-unchanged-native-method-replay',native_raw_sha256:hash(bytes),native_lf_sha256:hash(Buffer.from(text)),
  method_source_sha256:hash(Buffer.from(source)),finite_adversarial_scope:'Empty; each token removal; all pairwise swaps; standalone punctuation moves; all available bank-option replacements of one-to-three-token spans. Not exhaustive permutations.',
  selected,formatting_boundary:'Case and non-apostrophe punctuation normalization matches are recorded separately, not claimed strict assessment.',db_connections:0,db_writes:0,http_requests:0,file_writes:0};
}
process.stdout.write(JSON.stringify({kind:'actual-unchanged-native-method-replay',profiles:payload.profiles.map(profile),db_connections:0,db_writes:0,http_requests:0,file_writes:0}));
JS;
$nodePath = 'C:/Users/admin/.cache/codex-runtimes/codex-primary-runtime/dependencies/node/bin/node.exe';
if (!is_file($nodePath)) { throw new RuntimeException('M84 configured local Node runtime is unavailable.'); }
$process = new Symfony\Component\Process\Process([$nodePath, '-e', $node], $wt); $process->setTimeout(60);
$process->setInput(json_encode(['profiles' => $profiles, 'selected' => $probeSamples], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
$process->run();
if (!$process->isSuccessful()) { throw new RuntimeException('M84 actual unchanged native replay failed: '.trim($process->getErrorOutput())); }
$probe = json_decode($process->getOutput(), true, flags: JSON_THROW_ON_ERROR);
if (($probe['kind'] ?? null) !== 'actual-unchanged-native-method-replay' || array_column($probe['profiles'] ?? [], 'profile') !== ['served_root', 'worktree']
    || ($probe['db_connections'] ?? null) !== 0 || ($probe['db_writes'] ?? null) !== 0 || ($probe['http_requests'] ?? null) !== 0 || ($probe['file_writes'] ?? null) !== 0) {
    throw new RuntimeException('M84 native replay profile/effect boundary differs.');
}
foreach ($probe['profiles'] as $i => $profile) {
    if ($profile['native_raw_sha256'] !== $profiles[$i]['raw_sha256'] || array_column($profile['selected'], 'id') !== array_column($probeSamples, 'id')) {
        throw new RuntimeException('M84 exact native profile/selected replay lineage differs.');
    }
    foreach ($profile['selected'] as $result) {
        if (!$result['canonical_check'] || !$result['empty_rejected'] || !$result['repeated_check_stable']
            || $result['wrong_lexical_visible_scored_correct'] !== []) { throw new RuntimeException('M84 canonical/empty/recheck/visible lexical replay failed.'); }
    }
}
$audit['native_runtime_probe'] = $probe;
$privacy = static function ($value) use (&$privacy): void {
    if (!is_array($value)) { return; }
    foreach ($value as $key => $child) {
        if (is_string($key) && preg_match('/^(?:created_at|updated_at|started_at|finished_at|row_timestamp|served_root|source_worktree|sql_before|sql_after_http|password|secret|connection|credentials|physical|runtime_identity)$/iD', $key)) {
            throw new RuntimeException('M84 public audit contains forbidden raw/private/runtime data.');
        }
        $privacy($child);
    }
};
$privacy($audit);
$json = json_encode($audit, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n";
if (file_exists($out) || is_link($out)) { throw new RuntimeException('M84 exclusive bank receipt collided.'); }
$handle = fopen($out, 'x'); if (!$handle) { throw new RuntimeException('M84 exclusive private bank output unavailable.'); }
try { if (fwrite($handle, $json) !== strlen($json) || !fflush($handle)) { throw new RuntimeException('M84 private bank projection incomplete.'); } }
finally { fclose($handle); }
echo json_encode(['status' => 'draft-source-only-projection', 'file' => $argv[7], 'sha256' => hash('sha256', $json),
    'targets_sha256' => Digest::digest($audit['targets']), 'selected_counts' => array_map(static fn ($t) => count($t['first_five']), $audit['targets']),
    'pool_counts' => array_map(static fn ($t) => array_sum(array_map('count', $t['expected_pool_ids_by_bank'])), $audit['targets']),
    'selected_new' => $totalNew, 'selected_reused' => $totalReused, 'shared_model_matcher_checks' => $checks,
    'db_connections' => 0, 'db_writes' => 0, 'http_requests' => 0, 'active_source_writes' => 0], JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR)."\n";
