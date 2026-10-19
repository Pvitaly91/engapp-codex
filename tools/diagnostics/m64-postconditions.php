<?php

/** M64 exact native metadata and eight-question insert reconstruction; SELECT-only final audit. */
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
$questionSection = str_replace(['M53', 'm53', 'PassiveConstructions'], ['M64', 'm64', 'ReasonResultContrast'], $questionSection)."\n";
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
        foreach (array_slice($target['after']['page']['blocks'], App\Support\M64ReasonResultContrastPackage::CONTINUING_COUNTS[$i], null, true) as $slot => $candidate) {
            if (App\Support\M26DetailPackage::uuid($target['identity'], $candidate, $slot + 1) === $insert['fields']['uuid']) {
                if ($source !== null) { throw new RuntimeException('M64 duplicate appended UUID.'); }
                $source = $candidate; $position = $slot + 1;
            }
        }
        if ($source === null || $position === null) { throw new RuntimeException('M64 insert is not an exact frozen appended slot.'); }
PHP;
runFrozenReadOnlyProfile('m53-postconditions.php', '8f22822fcb45f6f07398437a9483835fda12eccbfc4097a3cbdd9871aaef179d', [
    ['M53', 'M64', 22], ['m53', 'm64', 4],
    ['PassiveConstructions', 'ReasonResultContrast', 7],
    [$rowBefore, $rowAfter, 1],
    ["count(\$preview['inserts'] ?? []) !== count(\$patch['targets'])", <<<'PHP'
count($preview['inserts'] ?? []) !== array_sum(array_map(
        static fn ($binding) => count($binding['appended_blocks'] ?? [null]), App\Support\M64ReasonResultContrastPackage::profile()['bindings']))
PHP, 1],
    [$insertBefore, $insertAfter, 1],
    [" || \$source['type'] !== 'practice-set'", '', 1],
    ["if (!hash_equals(\$sha, \$hash(\$file))) { throw new RuntimeException('Reviewed preview source/evidence changed: '.\$path); }", <<<'PHP'
$technical = [
        'app/Support/M64BuilderSelectionPresentation.php' => ['04381f64f06424cd21f636be3e3f6627b987b4b82269fddc5c32db8ff662501d', '9e857d85a0eaa20e0d84926d4a547a816fa71c1f412d2d7ccebc60d701dde630'],
        'app/Services/M64ContentPatch.php' => ['9c8205f29242e999e88c6eec4bb78ac9ae60114e3af9521ee2af852418e5c0bb', 'aa93456e5fd35dd2b8aeb1e28a095e7fc1076d874a47c4808716efd004aeb706'],
    ];
    // Exact post-apply technical correction only; all original teaching/evidence hashes remain literal.
    if (isset($technical[$path])) {
        if ($sha !== $technical[$path][0]) { throw new RuntimeException('M64 historical preview technical source is not the reviewed predecessor.'); }
        $sha = $technical[$path][1];
    }
    if (!hash_equals($sha, $hash($file))) { throw new RuntimeException('Reviewed preview source/evidence changed: '.$path); }
PHP, 1],
    ['newPractices', 'newBlocks', 6], ['practiceFacts', 'blockFacts', 4],
    ["'new_practices'", "'new_native_blocks'", 2],
    ["'uuid' => \$uuid];", "'uuid' => \$uuid, 'type' => \$source['type'], 'sort_order' => \$position];", 1],
]);
