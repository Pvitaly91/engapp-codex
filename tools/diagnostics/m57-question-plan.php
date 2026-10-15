<?php

/** Pure M57 three-bank projection from independent option/schema/ID inventory. */
require __DIR__.'/frozen-readonly-profile.php';

runFrozenReadOnlyProfile('m53-question-plan.php', '295f0c0bea309e9bd0d6727dc22146c90ee665d8a14a95c8d8f3b8e17acf3a84', [
    ['M53', 'M57', 18],
    ['m53', 'm57', 1],
    ['question-inventory-v1.json', 'question-inventory-v2.json', 2],
    ['PassiveConstructions', 'ContinuousPerfectPassives', 2],
    ['M57BuilderHintPresentation::AUDIT_SHA', 'M57ContentPatch::AUDIT_SHA', 1],
    ['App\\Support\\M57ContentPatch::AUDIT_SHA', 'App\\Services\\M57ContentPatch::AUDIT_SHA', 1],
    ['$target = $patch[\'targets\'][1];
$position = count($target[\'after\'][\'page\'][\'blocks\']);
$practice = $target[\'after\'][\'page\'][\'blocks\'][$position - 1];
$binding = [\'owner\' => App\\Support\\M57QuestionSupplement::OWNER, \'level\' => \'B1\', \'category_id\' => 45,
    \'source_id\' => 354, \'practice_uuid\' => App\\Support\\M26DetailPackage::uuid($target[\'identity\'], $practice, $position)];
if (($source[\'scope\'][\'practice_uuid\'] ?? null) !== $binding[\'practice_uuid\']) { throw new RuntimeException(\'M57 exact practice UUID differs.\'); }
', '$bindings = [];
foreach ($patch[\'targets\'] as $i => $target) {
    $position = count($target[\'after\'][\'page\'][\'blocks\']);
    $practice = $target[\'after\'][\'page\'][\'blocks\'][$position - 1];
    $bindings[] = [\'owner\' => $target[\'identity\'], \'level\' => \'B1\',
        \'category_id\' => $source[\'banks\'][$i][\'category_id\'], \'source_id\' => $source[\'banks\'][$i][\'source_id\'],
        \'practice_uuid\' => App\\Support\\M26DetailPackage::uuid($target[\'identity\'], $practice, $position)];
}
', 1],
    ['::project($source, $binding,', '::project($source, $bindings,', 1],
    ['\'binding\' => $binding', '\'bindings\' => $bindings', 2],
]);
