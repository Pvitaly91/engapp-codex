<?php

/** M69 body-only reconstruction: three practices plus one independently absent Page275 subtitle. No question-family exemptions. */
require __DIR__.'/frozen-readonly-profile.php';

$oldInsertRegion = <<<'PHP'
    if (count($pageBefore) !== count($patch['targets']) || count($preview['inserts'] ?? []) !== count($patch['targets'])) {
        throw new RuntimeException('Exact three-owner/native practice scope differs.');
    }
    $newPractices = []; $practiceFacts = [];
    foreach ($preview['inserts'] as $insert) {
        $i = array_search($insert['seeder'], App\Support\M69AdjectivesAdverbs::OWNERS, true);
        if ($i === false || $insert['table'] !== 'text_blocks' || $insert['sha256'] !== $digest($insert['fields'])) {
            throw new RuntimeException('Reviewed practice insert scope differs.');
        }
        $target = $patch['targets'][$i]; $source = end($target['after']['page']['blocks']); $position = count($target['after']['page']['blocks']);
        $uuid = App\Support\M26DetailPackage::uuid($target['identity'], $source, $position);
        if ($insert['fields']['uuid'] !== $uuid || $source['type'] !== 'practice-set') { throw new RuntimeException('Reviewed practice UUID differs from frozen source.'); }
        $actual = (array) $db->table('text_blocks')->where('uuid', $uuid)->sole();
        if (!is_int($actual['id']) || $actual['id'] < 1 || isset($newPractices[$actual['id']])
            || !is_string($actual['created_at']) || $actual['created_at'] === '' || $actual['created_at'] !== $actual['updated_at']) {
            throw new RuntimeException('New practice identity/timestamps differ.');
        }
        $expected = $insert['fields'] + ['id' => $actual['id'], 'created_at' => $actual['created_at'], 'updated_at' => $actual['created_at']];
        $same($actual, $expected, 'New practice row has an unreviewed field/value.'); $newPractices[$actual['id']] = $expected;
        $practiceFacts[] = ['owner' => $target['identity'], 'id' => $actual['id'], 'uuid' => $uuid];
    }
PHP;
$newInsertRegion = <<<'PHP'
    $expectedSubtitleOwners = [App\Support\M69AdjectivesAdverbs::OWNERS[2]];
    if (count($pageBefore) !== 3 || count($patch['targets']) !== 3
        || count($preview['inserts'] ?? []) !== 3 + count($expectedSubtitleOwners)) {
        throw new RuntimeException('Exact three-owner/three-practice/one-new-subtitle scope differs.');
    }
    $newPractices = []; $practiceFacts = []; $newSubtitles = []; $subtitleFacts = [];
    foreach ($preview['inserts'] as $insert) {
        $i = array_search($insert['seeder'], App\Support\M69AdjectivesAdverbs::OWNERS, true);
        if ($i === false || $insert['table'] !== 'text_blocks' || $insert['sha256'] !== $digest($insert['fields'])) {
            throw new RuntimeException('Reviewed new text-block insert scope differs.');
        }
        $target = $patch['targets'][$i]; $isSubtitle = ($insert['fields']['type'] ?? null) === 'subtitle';
        if ($isSubtitle) {
            $owner = $target['identity']; $old = $sourceBefore['targets'][$i]['before'];
            $binding = App\Support\M69AdjectivesAdverbs::profile()['bindings'][$i];
            if (!in_array($owner, $expectedSubtitleOwners, true) || $i !== 2 || $target['page_binding']['id'] !== 275
                || ($binding['before_subtitle']['mode'] ?? null) !== 'captured-absent'
                || $before['pages'][$owner]['page']['text'] !== null
                || array_filter($before['pages'][$owner]['blocks'], static fn ($row) => $row['locale'] === 'uk' && $row['type'] === 'subtitle') !== []) {
                throw new RuntimeException('New subtitle owner/absence differs from independent BEFORE.');
            }
            $source = ['type'=>'subtitle', 'column'=>'header', 'heading'=>null, 'css_class'=>null,
                'level'=>$old['page']['subtitle_level'] ?? null, 'uuid_key'=>$old['page']['subtitle_uuid_key'] ?? 'subtitle',
                'body'=>$target['after']['page']['subtitle_html']];
            $position = 0;
        } else {
            $source = end($target['after']['page']['blocks']); $position = count($target['after']['page']['blocks']);
            if ($source['type'] !== 'practice-set') { throw new RuntimeException('Reviewed appended practice source differs.'); }
        }
        $uuid = App\Support\M26DetailPackage::uuid($target['identity'], $source, $position);
        $expectedFields = ['uuid'=>$uuid, 'page_id'=>$target['page_binding']['id'],
            'page_category_id'=>$target['page_binding']['page_category_id'], 'locale'=>'uk', 'type'=>$source['type'],
            'column'=>$source['column'], 'heading'=>$source['heading'] ?? null, 'css_class'=>$source['css_class'] ?? null,
            'sort_order'=>$position, 'body'=>$source['body'], 'level'=>$source['level'] ?? null, 'seeder'=>$target['identity']];
        $same($insert['fields'], $expectedFields, 'Reviewed new text-block fields differ from frozen source.');
        $actual = (array) $db->table('text_blocks')->where('uuid', $uuid)->sole();
        if (!is_int($actual['id']) || $actual['id'] < 1 || isset($newPractices[$actual['id']]) || isset($newSubtitles[$actual['id']])
            || !is_string($actual['created_at']) || $actual['created_at'] === '' || $actual['created_at'] !== $actual['updated_at']) {
            throw new RuntimeException('New text-block identity/timestamps differ.');
        }
        $expected = $insert['fields'] + ['id'=>$actual['id'], 'created_at'=>$actual['created_at'], 'updated_at'=>$actual['created_at']];
        $same($actual, $expected, 'New text-block row has an unreviewed field/value.');
        $fact = ['owner'=>$target['identity'], 'id'=>$actual['id'], 'uuid'=>$uuid];
        if ($isSubtitle) { $newSubtitles[$actual['id']] = $expected; $subtitleFacts[] = $fact; }
        else { $newPractices[$actual['id']] = $expected; $practiceFacts[] = $fact; }
    }
    if (count($newPractices) !== 3 || count(array_unique(array_column($practiceFacts, 'owner'))) !== 3
        || count($newSubtitles) !== 1 || array_column($subtitleFacts, 'owner') !== $expectedSubtitleOwners) {
        throw new RuntimeException('Exact one-per-owner practice and one new Page275 subtitle partition differs.');
    }
    $newTextBlocks = $newPractices + $newSubtitles;
PHP;
$questionSection = <<<'PHP'
    // This method only SELECTs and validates existing frozen evidence. No writer or plan/apply entry is called.
    $service = new App\Services\M69ContentPatch($db, $root.'/database', $dir);
    $scope = (new ReflectionMethod($service, 'm69QuestionState'))->invoke($service, 'after', $patch, false);
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
    // All fifteen M69 sentence-builder questions already exist. No question,
    // answer, option, hint, saved-test, progress or link row is exempted.
    $newRows = []; $approved = [];
PHP;

runFrozenReadOnlyProfile('m53-postconditions.php', '8f22822fcb45f6f07398437a9483835fda12eccbfc4097a3cbdd9871aaef179d', [
    ['M53', 'M69', 22], ['m53', 'm69', 4],
    ['M69PassiveConstructionsPackage', 'M69AdjectivesAdverbs', 7],
    [$oldInsertRegion."\n", $newInsertRegion."\n", 1],
    [$questionSection."\n", $noQuestionSection."\n", 1],
    ["    'question_plan_sha256' => App\\Services\\M69ContentPatch::QUESTION_PLAN_SHA,\n", '', 1],
    ['isset($newPractices[$id])', 'isset($newTextBlocks[$id])', 1],
    ["$"."same($"."row, $"."newPractices[$"."id], 'New practice changed during final scan.');",
        "$"."same($"."row, $"."newTextBlocks[$"."id], 'New practice/subtitle changed during final scan.');", 1],
    ['count($seenPractices) !== count($newPractices)', 'count($seenPractices) !== count($newTextBlocks)', 1],
    ["'A target continuing Page/block/practice disappeared from the final scan.'",
        "'A target continuing Page/block/new practice/subtitle disappeared from the final scan.'", 1],
    ["'new_practices' => $"."practiceFacts,", "'new_practices' => $"."practiceFacts, 'new_subtitles' => $"."subtitleFacts,", 1],
    ["'new_practices' => count($"."practiceFacts),", "'new_practices' => count($"."practiceFacts), 'new_subtitles' => count($"."subtitleFacts),", 1],
]);
