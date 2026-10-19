<?php

/** M65 exact native metadata and three-question insert reconstruction; SELECT-only final audit. */
require __DIR__.'/frozen-readonly-profile.php';

$questionSection = <<<'PHP'
    // This method only SELECTs and validates existing frozen evidence. No writer or plan/apply entry is called.
    $service = new App\Services\M53ContentPatch($db, $root.'/database', $dir);
    $scope = (new ReflectionMethod($service, 'm53QuestionState'))->invoke($service, 'after', $patch, false);
    if (($scope['evidence']['projection'] ?? null) !== ($preview['question_supplement']['evidence']['projection'] ?? null)
        || ($scope['evidence']['ids'] ?? null) !== ($preview['question_supplement']['evidence']['ids'] ?? null)) {
        throw new RuntimeException('Applied question projection differs from reviewed preview.');
    }
    $timestamp = $scope['actual']['questions'][0]['created_at'] ?? '';
    $newRows = App\Support\M51QuestionSupplement::materialize($scope['evidence']['projection'], $scope['evidence']['ids'], $timestamp);
    App\Support\M51QuestionSupplement::assertApplied($newRows, $scope['actual']);
    $approved = []; foreach ($newRows as $table => $rows) { $approved[$table] = array_column($rows, null, 'id'); }
PHP;
$questionSection = str_replace(['M53', 'm53', 'PassiveConstructions'], ['M65', 'm65', 'PurposeGerundInfinitive'], $questionSection)."\n";
$rowBefore = <<<'PHP'
$ukBefore[$old['id']] = $old; $ukAfter[$old['id']] = array_replace($old, ['body' => $body]);
PHP;
$rowAfter = <<<'PHP'
$ukBefore[$old['id']] = $old; $fields = ['body' => $body];
            if ($position > 0) {
                $config = $target['after']['page']['blocks'][$position - 1];
                foreach (['type', 'column', 'heading', 'level', 'css_class'] as $field) { $fields[$field] = $config[$field] ?? null; }
            }
            $ukAfter[$old['id']] = array_replace($old, $fields);
PHP;
$insertBefore = <<<'PHP'
$target = $patch['targets'][$i]; $source = end($target['after']['page']['blocks']); $position = count($target['after']['page']['blocks']);
PHP;
$insertAfter = <<<'PHP'
$target = $patch['targets'][$i]; $source = null; $position = null;
        foreach (array_slice($target['after']['page']['blocks'], App\Support\M65PurposeGerundInfinitivePackage::CONTINUING_COUNTS[$i], null, true) as $slot => $candidate) {
            if (App\Support\M26DetailPackage::uuid($target['identity'], $candidate, $slot + 1) === $insert['fields']['uuid']) {
                if ($source !== null) { throw new RuntimeException('M65 duplicate appended UUID.'); }
                $source = $candidate; $position = $slot + 1;
            }
        }
        if ($source === null || $position === null) { throw new RuntimeException('M65 insert is not an exact frozen appended slot.'); }
PHP;
runFrozenReadOnlyProfile('m53-postconditions.php', '8f22822fcb45f6f07398437a9483835fda12eccbfc4097a3cbdd9871aaef179d', [
    ['M53', 'M65', 22], ['m53', 'm65', 4],
    ['PassiveConstructions', 'PurposeGerundInfinitive', 7],
    [$rowBefore, $rowAfter, 1],
    ["count(\$preview['inserts'] ?? []) !== count(\$patch['targets'])", <<<'PHP'
count($preview['inserts'] ?? []) !== array_sum(array_map(
        static fn ($binding) => count($binding['appended_blocks'] ?? [null]), App\Support\M65PurposeGerundInfinitivePackage::profile()['bindings']))
PHP, 1],
    [$insertBefore, $insertAfter, 1],
    [" || \$source['type'] !== 'practice-set'", '', 1],

    ['newPractices', 'newBlocks', 6], ['practiceFacts', 'blockFacts', 4],
    ["if (!hash_equals(\$sha, \$hash(\$file))) { throw new RuntimeException('Reviewed preview source/evidence changed: '.\$path); }", <<<'PHP'
$technical = [
        'app/Support/M65PurposeGerundInfinitivePackage.php' => ['d95d6fc65b99b8b5d0264d74b600ac9ff7311f4759e144f8ddca53adf53937a3', '23b62621bfb5769ae02f3c8455426929e02f249634cb43be696c0d41718b5572'],
        'app/Services/M65ContentPatch.php' => ['499c94b0822da612ebd2d1441caa4c30824a8bb1bd1b66b480b10f9687374fa6', '02cb20b768afd0e7fb2b4f8567870e934918e7dcbc067d2560f811b4d2731dd8'],
    ];
    // Technical revision 1.0.1 only: one EOF blank removed and a new exclusive parity receipt selected.
    // The original plan and all teaching/bank/evidence bytes remain immutable.
    if (isset($technical[$path])) {
        if ($sha !== $technical[$path][0]) { throw new RuntimeException('M65 historical preview technical predecessor differs.'); }
        $sha = $technical[$path][1];
    }
    if (!hash_equals($sha, $hash($file))) { throw new RuntimeException('Reviewed preview source/evidence changed: '.$path); }
PHP, 1],
    ["'new_practices'", "'new_native_blocks'", 2],
    ["'uuid' => \$uuid];", "'uuid' => \$uuid, 'type' => \$source['type'], 'sort_order' => \$position];", 1],
]);
