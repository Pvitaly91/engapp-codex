<?php

/** M68 native body-only reconstruction and three practice inserts; no question-family exemptions. */
require __DIR__.'/frozen-readonly-profile.php';

$questionSection = <<<'PHP'
    // This method only SELECTs and validates existing frozen evidence. No writer or plan/apply entry is called.
    $service = new App\Services\M68ContentPatch($db, $root.'/database', $dir);
    $scope = (new ReflectionMethod($service, 'm68QuestionState'))->invoke($service, 'after', $patch, false);
    if (($scope['evidence']['projection'] ?? null) !== ($preview['question_supplement']['evidence']['projection'] ?? null)
        || ($scope['evidence']['ids'] ?? null) !== ($preview['question_supplement']['evidence']['ids'] ?? null)) {
        throw new RuntimeException('Applied question projection differs from reviewed preview.');
    }
    $timestamp = $scope['actual']['questions'][0]['created_at'] ?? '';
    $newRows = App\Support\M51QuestionSupplement::materialize($scope['evidence']['projection'], $scope['evidence']['ids'], $timestamp);
    App\Support\M51QuestionSupplement::assertApplied($newRows, $scope['actual']);
    $approved = []; foreach ($newRows as $table => $rows) { $approved[$table] = array_column($rows, null, 'id'); }
PHP;
$noQuestionSection = <<<'PHP'
    // M68 reuses all fifteen existing questions. No question, answer, option, hint,
    // saved-test or link row is exempted from the exact independent BEFORE scan.
    $newRows = []; $approved = [];
PHP;

runFrozenReadOnlyProfile('m53-postconditions.php', '8f22822fcb45f6f07398437a9483835fda12eccbfc4097a3cbdd9871aaef179d', [
    ['M53', 'M68', 22], ['m53', 'm68', 4],
    ['M68PassiveConstructionsPackage', 'M68ComparisonBasics', 7],
    [$questionSection."\n", $noQuestionSection."\n", 1],
    ["    'question_plan_sha256' => App\\Services\\M68ContentPatch::QUESTION_PLAN_SHA,\n", '', 1],
]);
