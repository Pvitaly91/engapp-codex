<?php

/**
 * M85 read-only adapter over the independently hash-pinned M84 audit primitive.
 * Reuses its full-row matcher and actual native JS replay unchanged. Removes the
 * M84 insert-only allocation path completely: M85 selects thirty old questions.
 */
if (PHP_SAPI !== 'cli' || PHP_OS_FAMILY !== 'Windows') { throw new RuntimeException('M85 bank projection is local CLI only.'); }
$primitiveFile = __DIR__.'/m84-bank-audit-projection.php';
$primitiveSha = 'ab1d72c756be8aa14d4692904537ad198f7969832f47c3a60f3ca93859401824';
if (is_link($primitiveFile) || !is_file($primitiveFile) || !hash_equals($primitiveSha, hash_file('sha256', $primitiveFile))) {
    throw new RuntimeException('M85 unchanged full-row/native-replay audit primitive differs.');
}
$code = str_replace("\r\n", "\n", file_get_contents($primitiveFile));
$replace = static function (string $from, string $to) use (&$code): void {
    if (substr_count($code, $from) !== 1) { throw new RuntimeException('M85 finite adapter unique replacement missing: '.substr($from, 0, 100)); }
    $code = str_replace($from, $to, $code);
};
$cut = static function (string $start, string $end, string $replacement = '') use (&$code): void {
    if (substr_count($code, $start) !== 1 || substr_count($code, $end) !== 1) { throw new RuntimeException('M85 finite adapter range is not unique.'); }
    $a = strpos($code, $start); $b = strpos($code, $end, $a);
    if ($b === false || $b <= $a) { throw new RuntimeException('M85 finite adapter range order differs.'); }
    $code = substr($code, 0, $a).$replacement.substr($code, $b);
};
$cut('/** Pure finite bank projection:', '$root = ', <<<'PHP'
/** Pure existing-bank projection: pinned BEFORE + author + independently reviewed selection. */
if (PHP_SAPI !== 'cli' || PHP_OS_FAMILY !== 'Windows' || count($argv) !== 6
    || preg_match('/^author-projection-v[1-9][0-9]*\.json$/D', $argv[1]) !== 1
    || preg_match('/^widget-selection-v[1-9][0-9]*\.json$/D', $argv[3]) !== 1
    || preg_match('/^bank-audit-projection-v[1-9][0-9]*\.json$/D', $argv[5]) !== 1) {
    throw new RuntimeException('M85 requires author basename/SHA, selection basename/SHA and exclusive output basename.');
}
foreach ([2, 4] as $n) {
    if (preg_match('/^[a-f0-9]{64}$/D', $argv[$n]) !== 1) { throw new RuntimeException('M85 requires exact independently reviewed input hashes.'); }
}

PHP);
$code = str_replace(['M84WordOrderHaveGot', 'm84-word-order-have-got-bank-audit', 'M84', 'seo-m84-local'],
    ['M85VerbToBe', 'm85-verb-to-be-bank-audit', 'M85', 'seo-m85-local'], $code);
$code = str_replace('babac2b3efa88b87fc8ea50b1ad45fb9feb8cadfdf766c33e76ca7b24aa19916',
    'fe639c06446c6fb46b5b4ab25ae654e3910b6733cc124a9e4bee262e41b411b7', $code);
$replace('9066938a376b3f4eeae6fa65f6fea6afc7a2bdd624a4ed93dc32e37c59b8e47e', '7f554bebc5c15ea86bef65592d35da75cc1fdbec3631e1f3060de55926508fe1');
$code = str_replace("\$argv[7]", "\$argv[5]", $code);
$replace('$authorSha = $argv[2]; $inventorySha = $argv[4]; $selectionSha = $argv[6];', '$authorSha = $argv[2]; $selectionSha = $argv[4];');
$replace('$inventory = $read($private.\'/\'.$argv[3], $inventorySha);'."\n", '');
$replace('$selection = $read($private.\'/\'.$argv[5], $selectionSha);', '$selection = $read($private.\'/\'.$argv[3], $selectionSha);');
$replace('require_once $root.\'/app/Support/M85QuestionSupplement.php\';'."\n", '');
$replace('use App\\Support\\M85QuestionSupplement as Supplement;'."\n", '');
$cut('    || ($inventory[\'base_sha\']', '    || ($selection[\'independent_before_sha256\']');
$replace("    || (\$inventory['db_writes'] ?? null) !== 0 || (\$inventory['http_requests'] ?? null) !== 0) {", ') {');
$cut('$supplement = $read(', '/** Exact full original question');
$cut('$optionRows = ', '$scope = ');
$code = str_replace('30 selected / 5 reused / 25 new; insert-only question supplement; no old question/answer/option/grader/progress writes',
    '30 selected existing questions; theory-only condition clones; no question/answer/option/grader/progress writes', $code);
$replace('Independent main review/freeze, guarded local insert/apply, exact full-question/widget browser acceptance and old-row preservation proof.',
    'Independent main review/freeze, guarded local content apply, exact full-question/widget browser acceptance and all-old-question-row preservation proof.');
$cut("    'source_proof' => ", "    'review_summary' => ", <<<'PHP'
    'source_proof' => ['author_projection_sha256' => $authorSha, 'widget_selection_sha256' => $selectionSha,
        'paired_source_before_sha256' => $pairedSha, 'existing_source_sha256' => array_column($selection['targets'], 'source_sha256'),
        'source_SQL_condition_drifts' => $selection['source_SQL_condition_drifts'], 'selection_source_findings' => $selection['source_findings'],
        'unchanged_audit_primitive_sha256' => 'ab1d72c756be8aa14d4692904537ad198f7969832f47c3a60f3ca93859401824'],

PHP);
$cut("    'review_summary' => ", "    'clone_only_policy' => ", <<<'PHP'
    'review_summary' => ['source_type4_pool' => 432, 'pagewide_original_type4_pool' => 429, 'actual_filtered_pool' => 429,
        'selected' => 30, 'reused' => 30, 'new' => 0, 'technical_filter' => Package::LEVELS, 'intended_selected_levels' => Package::FOCUS_LEVELS,
        'null_policy' => 'All six actual practices use SQL NULL; no exact CEFR filter. Editorial focus is not a filter rewrite. Exact old eligible pools are71/72/72/72/72/70, not a manufactured symmetric vector.',
        'boundary' => 'Finite thirty existing full-sentence questions only. Three source UUIDs absent from page-linked pools remain absent; all old rows/links stay unchanged. Five There A1 models cover affirmative countable existence; own controls cover the remaining explicitly declared functions.',
        'human_review' => false],

PHP);
$replace('$banks = $i < 5 ? [Package::BANKS[$i], Supplement::BANKS[$i]] : [Package::BANKS[$i]];', '$banks = [Package::BANKS[$i]];');
$cut("    if (\$i < 5) {\n        foreach (\$newSnapshots", '    $pool = []; $poolByBank = [];');
$replace('$expected = $bankIndex === 0 ? 72 : 5;', '$expected = [71, 72, 72, 72, 72, 70][$i];');
$replace('Finite full-sentence selection only; original fragment models/keys/options retained for their consumers.',
    'Outside this finite reviewed five; all original models/keys/options/links remain unchanged for their consumers.');
$replace("'reason' => \$i < 5 ? 'Original thematic type4 remains a fragment/gap model; it is not substituted for a required full-sentence widget question.'\n                    : 'Outside this finite reviewed five; not a claim of global invalidity.'",
    "'reason' => 'Outside this finite reviewed five; not a claim of global invalidity.'");
$replace("        \$isNew = isset(\$newSnapshots[\$q['uuid']]);\n        if (\$isNew !== (\$i < 5) || (\$isNew && \$q['seeder'] !== Supplement::BANKS[\$i])) { throw new RuntimeException('M85 twenty-five new / five reused boundary differs.'); }\n        \$isNew ? \$totalNew++ : \$totalReused++;",
    "        \$isNew = false; \$totalReused++;");
$replace('[$totalNew, $totalReused] !== [25, 5]', '[$totalNew, $totalReused] !== [0, 30]');
$replace('!== [77, 77, 77, 77, 77, 72]', '!== [71, 72, 72, 72, 72, 70]');
if (str_contains($code, 'Supplement::') || str_contains($code, '$inventory') || str_contains($code, '$newSnapshots')
    || str_contains($code, 'M84QuestionSupplement') || str_contains($code, 'M85QuestionSupplement')) {
    throw new RuntimeException('M85 adapter retained an unauthorized insert-only allocation dependency.');
}
// Strong lineage for the selection itself, independent of all generated public metadata.
$replace("\$selection = \$read(\$private.'/'.\$argv[3], \$selectionSha);", <<<'PHP'
$selection = $read($private.'/'.$argv[3], $selectionSha);
if (($selection['base_sha'] ?? null) !== '6f7751612a3bc9d8bec2315c052e81f9e2fc0973'
    || ($selection['new_questions'] ?? null) !== 0 || ($selection['selected_existing'] ?? null) !== 30
    || ($selection['actual_pool_counts'] ?? null) !== [71, 72, 72, 72, 72, 70]
    || ($selection['db_connections'] ?? null) !== 0 || ($selection['db_writes'] ?? null) !== 0
    || ($selection['http_requests'] ?? null) !== 0 || ($selection['active_source_writes'] ?? null) !== 0) {
    throw new RuntimeException('M85 exact zero-insert independent selection boundary differs.');
}
PHP);
if (!str_starts_with($code, "<?php\n")) { throw new RuntimeException('M85 pinned audit primitive opening differs.'); }
eval(substr($code, strlen("<?php\n")));
