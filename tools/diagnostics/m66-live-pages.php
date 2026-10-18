<?php

/** M66 anonymous HTTP/SSR profile; browser visibility remains separate. */
require __DIR__.'/frozen-readonly-profile.php';

$context = [];
if (PHP_SAPI !== 'cli' || count($argv) !== 6
    || preg_match('/^live-pages-v[1-9][0-9]*\.json$/D', $argv[1] ?? '') !== 1
    || preg_match('/^final-readonly-v[1-9][0-9]*\.json$/D', $argv[2] ?? '') !== 1
    || preg_match('/^[a-f0-9]{64}$/D', $argv[3] ?? '') !== 1
    || preg_match('/^author-projection-v[1-9][0-9]*\.json$/D', $argv[4] ?? '') !== 1
    || preg_match('/^[a-f0-9]{64}$/D', $argv[5] ?? '') !== 1) {
    throw new RuntimeException('M66 requires a new live receipt, reviewed final basename/SHA and pre-apply projection basename/SHA.');
}
$context = ['final_file' => $argv[2], 'final_sha256' => $argv[3],
    'projection_file' => $argv[4], 'projection_sha256' => $argv[5]];
$argv = array_slice($argv, 0, 2);

$oldDetailQuery = <<<'PHP'
        $details = $xp->query('.//details[@data-theory-details]', $ownerBlock['node']);
        $same($details->length, 1, 'Owned point-detail count');
PHP;
$ownedDetailQuery = <<<'PHP'
        if (preg_match('/^[a-z0-9_-]+$/D', $detail['key']) !== 1
            || preg_match('~^/(?:sections|items)/([0-9]+)$~D', $detail['owner_pointer'], $point) !== 1) {
            throw new RuntimeException('Frozen detail key or owner pointer is outside its exact native field contract.');
        }
        $owned = $xp->query('.//*[@data-theory-section="'.$detail['key'].'" and @data-theory-point-index="'.$point[1].'"]', $ownerBlock['node']);
        $same($owned->length, 1, 'Exact detail-key/point-index wrapper count');
        $details = $xp->query('.//details[@data-theory-details]', $owned->item(0));
        $same($details->length, 1, 'Owned point-detail count');
PHP;
$oldForms = <<<'PHP'
            foreach ($data['items'] as $n => $item) { foreach (['label', 'title', 'subtitle'] as $field) { $contains($text, $item[$field], $label.'/items/'.$n.'/'.$field); } }
PHP;
$allFormFields = <<<'PHP'
            foreach ($data['items'] as $n => $item) {
                foreach (['label', 'title', 'subtitle'] as $field) { $contains($text, $item[$field] ?? null, $label.'/items/'.$n.'/'.$field); }
                foreach ($item['rules'] ?? [] as $r => $rule) {
                    foreach (['label', 'formula', 'text', 'example'] as $field) {
                        $contains($text, $rule[$field] ?? null, $label.'/items/'.$n.'/rules/'.$r.'/'.$field);
                    }
                }
            }
PHP;
$oldSame = <<<'PHP'
$same = static function (mixed $actual, mixed $expected, string $label): void {
    if ($actual !== $expected) { throw new RuntimeException($label.' differs.'); }
};
PHP;
$sameWithAppliedBindings = $oldSame.<<<'PHP'

$finalPath = $private.'/'.$profileContext['final_file'];
$finalSha = $profileContext['final_sha256'];
if (!hash_equals($finalSha, $fileHash($finalPath))) { throw new RuntimeException('Applied SQL/source receipt changed.'); }
$final = json_decode(file_get_contents($finalPath), true, flags: JSON_THROW_ON_ERROR);
$same($final['independent_before_sha256'] ?? null, M66ContentPatch::BEFORE_SHA, 'Applied independent BEFORE');
foreach (['all_old_rows_and_timestamps_exact', 'independent_before_projection_exact',
    'reviewed_protected_projection_exact', 'source_sync_unchanged'] as $field) {
    $same($final[$field] ?? null, true, 'Applied receipt '.$field);
}
$same($final['sql_tables'] ?? null, count($before['sql_before']), 'Applied SQL table coverage');
$same($final['normalized_before'] ?? null, $before['sql_before'], 'Exact independent BEFORE reconstruction');
$same($final['db_writes'] ?? null, 0, 'Read-only postcondition writes');
$same($final['http_requests'] ?? null, 0, 'Read-only postcondition HTTP');
$same($final['new_question_families'] ?? null, [], 'No question-family exemptions');
$expectedPractices = []; $oldIds = [];
foreach ($before['pages'] as $page) { foreach ($page['blocks'] as $row) { $oldIds[(int) $row['id']] = true; } }
foreach ($patch['targets'] as $i => $target) {
    $count = count($sourceBefore['targets'][$i]['before']['page']['blocks']);
    $appended = array_slice($target['after']['page']['blocks'], $count, null, true);
    $same(count($appended), 1, 'One appended practice per owner');
    foreach ($appended as $slot => $source) {
        $same($source['type'], 'practice-set', 'Only appended practice');
        $expectedPractices[$target['identity']] = M26DetailPackage::uuid($target['identity'], $source, $slot + 1);
    }
}
$newPracticeByOwner = []; $newIds = [];
if (!is_array($final['new_practices'] ?? null)) { throw new RuntimeException('Applied practice map absent.'); }
$same(count($final['new_practices']), count($expectedPractices), 'Applied practice map count');
foreach ($final['new_practices'] as $row) {
    if (!is_array($row) || array_keys($row) !== ['owner', 'id', 'uuid']
        || !is_int($row['id']) || $row['id'] < 1 || isset($oldIds[$row['id']]) || isset($newIds[$row['id']])
        || !is_string($row['owner']) || isset($newPracticeByOwner[$row['owner']])) {
        throw new RuntimeException('Applied practice map ambiguous or foreign.');
    }
    $same($row['uuid'], $expectedPractices[$row['owner']] ?? null, 'Frozen practice owner/UUID');
    $newPracticeByOwner[$row['owner']] = $row; $newIds[$row['id']] = true;
}
$inputs[$finalPath] = $finalSha;
PHP;
$oldComparisonFields = <<<'PHP'
            foreach ($data['headers'] as $header) { $contains($text, $header, $label.'/header'); }
            foreach ($data['rows'] as $n => $row) { foreach ($row['cells'] as $c => $cell) { $contains($text, $cell, $label.'/rows/'.$n.'/'.$c); } }
PHP;
$allComparisonFields = <<<'PHP'
            foreach ($data['headers'] ?? [] as $header) { $contains($text, $header, $label.'/header'); }
            foreach ($data['rows'] as $n => $row) {
                if (isset($row['cells'])) {
                    foreach ($row['cells'] as $c => $cell) { $contains($text, $cell, $label.'/rows/'.$n.'/'.$c); }
                } else {
                    foreach (['en', 'ua', 'note'] as $field) { $contains($text, $row[$field] ?? null, $label.'/rows/'.$n.'/'.$field); }
                }
            }
PHP;

runFrozenReadOnlyProfile('m53-live-pages.php', '66c4c1ca501fa4a99e6a15a6e254b146d32a259a6a009cc72ef2e1a034b6e4b6', [
    ['M53', 'M66', 13], ['m53', 'm66', 3],
    ['PassiveConstructions', 'VerbPatternsCore', 1],
    ["use App\\Support\\M66BuilderHintPresentation;\n", '', 1],
    ['M66BuilderHintPresentation', 'M66ContentPatch', 4],
    ['~^http://gramlyze\\.loc/theory/passive-voice/theory-passive-voice-(?:causative|get-passive|impersonal-passive)$~D',
        '~^http://gramlyze\\.loc/theory/verb-patterns/(?:bare-infinitive|gerund-vs-infinitive|verbs-plus-gerund)$~D', 1],
    ['~^/theory/passive-voice/[a-z0-9-]+$~D', '~^/theory/[a-z0-9-]+(?:/[a-z0-9-]+)*$~D', 1],
    ["\$resources[] = array_intersect_key(\$value, array_flip(['@type', 'url', 'name', 'headline', 'inLanguage']));",
        "\$resources[] = array_intersect_key(\$value, array_flip(['@type', 'url', 'name', 'headline', 'inLanguage', 'description']));", 1],
    ["\$same(count(\$resources), 1, 'LearningResource count'); \$same(\$resources, \$oldResources, 'Stable LearningResource identity');",
        "\$same(count(\$resources), 1, 'LearningResource count');\n    \$same(\$resources[0]['description'] ?? null, \$metadata['expected_learning_resource_description'], 'Reviewed LearningResource description');\n    \$resources = array_map(static fn (\$value) => array_diff_key(\$value, ['description' => true]), \$resources);\n    \$same(\$resources, \$oldResources, 'Stable LearningResource identity');", 1],
    ["\$same(\$lower(\$payload[\$q]['correct_tokens']), \$lower(\$expected['correct_tokens']), 'Actual widget ordered correct tokens');",
        "\$same(\$payload[\$q]['correct_tokens'], \$expected['correct_tokens'], 'Actual widget exact ordered tokens and capitalization');", 1],
    ["\$available = array_count_values(\$lower(\$payload[\$q]['options']));",
        "\$available = array_count_values(\$payload[\$q]['options']);", 1],
    ["array_count_values(\$lower(\$payload[\$q]['correct_tokens']))",
        "array_count_values(\$payload[\$q]['correct_tokens'])", 1],
    ["foreach (['title', 'intro', 'warning'] as \$field)", "foreach (['title', 'intro', 'warning', 'outro'] as \$field)", 1],
    [$oldDetailQuery, $ownedDetailQuery, 1],
    [$oldForms, $allFormFields, 1],
    [$oldComparisonFields, $allComparisonFields, 1],
    ["\$contains(\$text, \$item[\$field], \$label.'/items/'.\$n.'/'.\$field);",
        "\$contains(\$text, \$item[\$field] ?? null, \$label.'/items/'.\$n.'/'.\$field);", 1],
    ["\$bank = \$audit['targets'][\$i]; \$same(\$bank['identity'], \$owner, 'Independent bank audit owner');",
        "\$bank = \$audit['targets'][\$i]; \$same(\$bank['identity'], \$owner, 'Independent bank audit owner');\n            \$same(array_column(\$payload, 'id'), \$bank['expected_selected_ids'], 'Actual widget exact reviewed selected IDs');", 1],
    ["\$plain(\$expected['condition_uk'])", "\$plain(\$expected['visible_condition_uk'])", 1],
    [$oldSame, $sameWithAppliedBindings, 1],
    [<<<'PHP'
            $node = $one('//*[@data-sentence-builder]/ancestor::section[contains(concat(" ",normalize-space(@class)," ")," theory-native-block ")][1]');
PHP, <<<'PHP'
            $node = $one('//*[@data-sentence-builder]/ancestor::section[contains(concat(" ",normalize-space(@class)," ")," theory-native-block ")][1]');
            $same($node->getAttribute('id'), 'block-'.$newPracticeByOwner[$owner]['id'], 'Exact applied practice DOM owner/ID');
PHP, 1],
    ["'master_sha256' => Package::MASTER_SHA, 'patch_sha256' => Package::SOURCE_SHA,",
        "'applied_postconditions_sha256' => \$finalSha, 'new_native_practice_bindings_checked' => count(\$newPracticeByOwner),\n    'master_sha256' => Package::MASTER_SHA, 'patch_sha256' => Package::SOURCE_SHA,", 1],
    [<<<'PHP'
$inputs = [$beforePath => M66ContentPatch::BEFORE_SHA, $auditPath => M66ContentPatch::AUDIT_SHA];
PHP, <<<'PHP'
$inputs = [$beforePath => M66ContentPatch::BEFORE_SHA, $auditPath => M66ContentPatch::AUDIT_SHA];
$projectionPath = $private.'/'.$profileContext['projection_file'];
$projectionSha = $profileContext['projection_sha256'];
if (!hash_equals($projectionSha, $fileHash($projectionPath))) {
    throw new RuntimeException('M66 independently reviewed pre-apply metadata projection differs.');
}
$reviewedProjection = json_decode(file_get_contents($projectionPath), true, flags: JSON_THROW_ON_ERROR);
if (($reviewedProjection['base_sha'] ?? null) !== Package::BASE_SHA
    || ($sourceBefore['base_sha'] ?? null) !== Package::BASE_SHA
    || ($sourceBefore['content_type'] ?? null) !== 'M66_source_before'
    || ($reviewedProjection['source_before']['content_type'] ?? null) !== 'M66_source_before'
    || ($reviewedProjection['patch'] ?? null) !== $patch
    || ($reviewedProjection['source_before']['targets'] ?? null) !== ($sourceBefore['targets'] ?? null)
    || ($reviewedProjection['db_writes'] ?? null) !== 0) {
    throw new RuntimeException('M66 metadata evidence has foreign source bindings.');
}
$inputs[$projectionPath] = $projectionSha;
PHP, 1],
    [<<<'PHP'
$metadata = array_values(array_filter($master['editorial_review']['pre_apply_metadata'] ?? [], static fn ($record) => ($record['identity'] ?? null) === $owner));
PHP, <<<'PHP'
$metadata = array_values(array_filter($reviewedProjection['pre_apply_metadata'] ?? [], static fn ($record) => ($record['identity'] ?? null) === $owner));
PHP, 1],
    [<<<'PHP'
$metadata = $metadata[0];
PHP, <<<'PHP'
$metadata = $metadata[0];
    if (!is_string($metadata['expected_learning_resource_description'] ?? null)
        || $metadata['expected_learning_resource_description'] === '') {
        throw new RuntimeException('M66 requires an independently reviewed pre-apply LearningResource description.');
    }
PHP, 1],
], $context);
