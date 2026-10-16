<?php

/** M59 anonymous HTTP/SSR profile; browser visibility remains separate. */
require __DIR__.'/frozen-readonly-profile.php';

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
$oldBlockLookup = <<<'PHP'
            $old = $byOrder[$slot + 1] ?? null;
            if ($old === null) { throw new RuntimeException('Missing independently captured continuing block.'); }
            $node = $one('//*[@id="block-'.(int) $old['id'].'"]');
PHP;
$newBlockLookup = <<<'PHP'
            $old = $byOrder[$slot + 1] ?? null;
            if ($old !== null) { $node = $one('//*[@id="block-'.(int) $old['id'].'"]'); }
            else {
                $source = $target['after']['page']['blocks'][$slot];
                $uuid = M26DetailPackage::uuid($owner, $source, $slot + 1);
                $created = $newNativeByUuid[$uuid] ?? null;
                if ($created === null || $created['owner'] !== $owner || $created['type'] !== $source['type']
                    || $created['sort_order'] !== $slot + 1) {
                    throw new RuntimeException('Independent applied native-block binding is absent or differs.');
                }
                $node = $one('//*[@id="block-'.$created['id'].'"]');
            }
PHP;
$oldSame = <<<'PHP'
$same = static function (mixed $actual, mixed $expected, string $label): void {
    if ($actual !== $expected) { throw new RuntimeException($label.' differs.'); }
};
PHP;
$sameWithAppliedBindings = $oldSame.<<<'PHP'

$finalPath = $private.'/final-readonly-v1.json';
$finalSha = '22abaafa0c37eacea33226287227ad234864562167272a98a1fcb42eaf358332';
if (!hash_equals($finalSha, $fileHash($finalPath))) {
    throw new RuntimeException('Independent applied SQL/source postconditions bytes differ.');
}
$final = json_decode(file_get_contents($finalPath), true, flags: JSON_THROW_ON_ERROR);
$same($final['independent_before_sha256'] ?? null, M59ContentPatch::BEFORE_SHA, 'Applied receipt independent BEFORE');
foreach (['all_old_rows_and_timestamps_exact', 'independent_before_projection_exact',
    'reviewed_protected_projection_exact', 'source_sync_unchanged'] as $field) {
    $same($final[$field] ?? null, true, 'Applied receipt '.$field);
}
$same($final['sql_tables'] ?? null, 46, 'Applied receipt exact SQL table coverage');
$same($final['db_writes'] ?? null, 0, 'Read-only postconditions writes');
$same($final['new_question_families'] ?? null, [], 'No new question families');
$expectedNative = []; $oldIds = [];
foreach ($before['pages'] as $page) {
    foreach ($page['blocks'] as $row) { $oldIds[(int) $row['id']] = true; }
}
foreach ($patch['targets'] as $i => $target) {
    $count = count($sourceBefore['targets'][$i]['before']['page']['blocks']);
    foreach (array_slice($target['after']['page']['blocks'], $count, null, true) as $slot => $source) {
        $uuid = M26DetailPackage::uuid($target['identity'], $source, $slot + 1);
        if (isset($expectedNative[$uuid])) { throw new RuntimeException('Duplicate frozen appended native UUID.'); }
        $expectedNative[$uuid] = ['owner' => $target['identity'], 'uuid' => $uuid,
            'type' => $source['type'], 'sort_order' => $slot + 1];
    }
}
$newNativeByUuid = []; $newIds = [];
if (!is_array($final['new_native_blocks'] ?? null)) { throw new RuntimeException('Applied native-block map is absent.'); }
$same(count($final['new_native_blocks']), count($expectedNative), 'Applied native-block map count');
foreach ($final['new_native_blocks'] as $row) {
    if (!is_array($row) || array_keys($row) !== ['owner', 'id', 'uuid', 'type', 'sort_order']
        || !is_int($row['id']) || $row['id'] < 1 || isset($oldIds[$row['id']]) || isset($newIds[$row['id']])
        || !is_string($row['uuid']) || isset($newNativeByUuid[$row['uuid']])) {
        throw new RuntimeException('Applied native-block ID/UUID map is ambiguous or foreign.');
    }
    $binding = $row; unset($binding['id']);
    $same($binding, $expectedNative[$row['uuid']] ?? null, 'Applied native-block frozen owner/UUID/type/order');
    $newNativeByUuid[$row['uuid']] = $row; $newIds[$row['id']] = true;
}
$inputs[$finalPath] = $finalSha;
PHP;
runFrozenReadOnlyProfile('m53-live-pages.php', '66c4c1ca501fa4a99e6a15a6e254b146d32a259a6a009cc72ef2e1a034b6e4b6', [
    ['M53', 'M59', 13], ['m53', 'm59', 3],
    ['PassiveConstructions', 'PassiveFutureAgentReview', 1],
    ["use App\\Support\\M59BuilderHintPresentation;\n", '', 1],
    ['M59BuilderHintPresentation', 'M59ContentPatch', 4],
    ['~^http://gramlyze\\.loc/theory/passive-voice/theory-passive-voice-(?:causative|get-passive|impersonal-passive)$~D',
        '~^http://gramlyze\\.loc/theory/passive-voice/(?:passive-voice-tenses/theory-passive-voice-future-continuous|theory-passive-voice-by-phrase|advanced-passive-voice)$~D', 1],
    ['~^/theory/passive-voice/[a-z0-9-]+$~D', '~^/theory/[a-z0-9-]+(?:/[a-z0-9-]+)*$~D', 1],
    ["\$same(\$lower(\$payload[\$q]['correct_tokens']), \$lower(\$expected['correct_tokens']), 'Actual widget ordered correct tokens');",
        "\$same(\$payload[\$q]['correct_tokens'], \$expected['correct_tokens'], 'Actual widget exact ordered tokens and capitalization');", 1],
    ["\$available = array_count_values(\$lower(\$payload[\$q]['options']));",
        "\$available = array_count_values(\$payload[\$q]['options']);", 1],
    ["array_count_values(\$lower(\$payload[\$q]['correct_tokens']))",
        "array_count_values(\$payload[\$q]['correct_tokens'])", 1],
    ["foreach (['title', 'intro', 'warning'] as \$field)", "foreach (['title', 'intro', 'warning', 'outro'] as \$field)", 1],
    [$oldDetailQuery, $ownedDetailQuery, 1],
    [$oldForms, $allFormFields, 1],
    ["\$contains(\$text, \$item[\$field], \$label.'/items/'.\$n.'/'.\$field);",
        "\$contains(\$text, \$item[\$field] ?? null, \$label.'/items/'.\$n.'/'.\$field);", 1],
    ["\$bank = \$audit['targets'][\$i]; \$same(\$bank['identity'], \$owner, 'Independent bank audit owner');",
        "\$bank = \$audit['targets'][\$i]; \$same(\$bank['identity'], \$owner, 'Independent bank audit owner');\n            \$same(array_column(\$payload, 'id'), \$bank['expected_existing_selected_ids'], 'Actual widget exact reviewed reused IDs');", 1],
    [$oldBlockLookup, $newBlockLookup, 1],
    ["\$plain(\$expected['condition_uk'])", "\$plain(\$expected['visible_condition_uk'])", 1],
    [$oldSame, $sameWithAppliedBindings, 1],
    ["'master_sha256' => Package::MASTER_SHA, 'patch_sha256' => Package::SOURCE_SHA,",
        "'applied_postconditions_sha256' => \$finalSha, 'new_native_block_bindings_checked' => count(\$newNativeByUuid),\n    'master_sha256' => Package::MASTER_SHA, 'patch_sha256' => Package::SOURCE_SHA,", 1],
]);
