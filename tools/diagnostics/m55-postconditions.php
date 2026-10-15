<?php

/** M55 content-only profile of the exact M53 46-table reconstruction procedure. */
require __DIR__.'/frozen-readonly-profile.php';

$oldQuestionScope = <<<'PHP'
    // This method only SELECTs and validates existing frozen evidence. No writer or plan/apply entry is called.
    $service = new App\Services\M55ContentPatch($db, $root.'/database', $dir);
    $scope = (new ReflectionMethod($service, 'm55QuestionState'))->invoke($service, 'after', $patch, false);
    if (($scope['evidence']['projection'] ?? null) !== ($preview['question_supplement']['evidence']['projection'] ?? null)
        || ($scope['evidence']['ids'] ?? null) !== ($preview['question_supplement']['evidence']['ids'] ?? null)) {
        throw new RuntimeException('Applied question projection differs from reviewed preview.');
    }
    $timestamp = $scope['actual']['questions'][0]['created_at'] ?? '';
    $newRows = App\Support\M51QuestionSupplement::materialize($scope['evidence']['projection'], $scope['evidence']['ids'], $timestamp);
    App\Support\M51QuestionSupplement::assertApplied($newRows, $scope['actual']);
    $approved = []; foreach ($newRows as $table => $rows) { $approved[$table] = array_column($rows, null, 'id'); }
PHP;
$noQuestionScope = <<<'PHP'
    // The independently audited existing thematic pools suffice. No question-family
    // rows may be excluded: every existing row, link and timestamp hashes exactly.
    if (isset($preview['question_supplement']) || isset($preview['question_projection'])) {
        throw new RuntimeException('M55 content-only preview cannot authorize question-family changes.');
    }
    $newRows = array_fill_keys(['questions', 'question_answers', 'question_options',
        'question_option_question', 'question_theory_text_blocks'], []);
    $approved = [];
PHP;
runFrozenReadOnlyProfile('m53-postconditions.php', '8f22822fcb45f6f07398437a9483835fda12eccbfc4097a3cbdd9871aaef179d', [
    ['M53', 'M55', 22], ['m53', 'm55', 4],
    ['PassiveConstructions', 'PassiveReviewInfinitive', 7],
    [$oldQuestionScope, $noQuestionScope, 1],
    ["    'question_plan_sha256' => App\\Services\\M55ContentPatch::QUESTION_PLAN_SHA,\n", '', 1],
]);
