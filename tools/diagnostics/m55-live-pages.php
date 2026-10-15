<?php

/** M55 anonymous HTTP/SSR profile; browser visibility remains separate. */
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
$selectedLoop = <<<'PHP'
            foreach ($bank['expected_selected_uuids'] as $q => $questionUuid) {
PHP;
$projectedSelectedLoop = <<<'PHP'
            $conditionProjection = $audit['condition_projection'];
            if ($owner === $conditionProjection['identity']) {
                $same($bank['expected_existing_selected_ids'], $conditionProjection['expected_selected_ids'], 'Finite context selected IDs');
                foreach ($conditionProjection['records'] as $record) {
                    $same($expectedQuestions[$record['question_uuid']]['id'] ?? null, $record['question_id'], 'Context question ID');
                    $same($expectedQuestions[$record['question_uuid']]['condition_uk'] ?? null, $record['before_condition_uk'], 'Original context condition preserved');
                    $expectedQuestions[$record['question_uuid']]['condition_uk'] = $record['condition_uk'];
                }
            }
            foreach ($bank['expected_selected_uuids'] as $q => $questionUuid) {
PHP;
runFrozenReadOnlyProfile('m53-live-pages.php', '66c4c1ca501fa4a99e6a15a6e254b146d32a259a6a009cc72ef2e1a034b6e4b6', [
    ['M53', 'M55', 13], ['m53', 'm55', 3],
    ['PassiveConstructions', 'PassiveReviewInfinitive', 1],
    ["use App\\Support\\M55BuilderHintPresentation;\n", '', 1],
    ['M55BuilderHintPresentation', 'M55ContentPatch', 4],
    ['~^http://gramlyze\\.loc/theory/passive-voice/theory-passive-voice-(?:causative|get-passive|impersonal-passive)$~D',
        '~^http://gramlyze\\.loc/theory/passive-voice/(?:theory-passive-voice-(?:common-mistakes|formality-style)|passive-voice-infinitives-gerund/theory-passive-voice-passive-infinitive)$~D', 1],
    ['~^/theory/passive-voice/[a-z0-9-]+$~D', '~^/theory/[a-z0-9-]+(?:/[a-z0-9-]+)*$~D', 1],
    ["\$type = \$author['type']; \$data = \$author['data']; \$label = \$author['key'];",
        "\$type = Package::profile()['bindings'][\$i]['component_overrides'][\$slot] ?? \$author['type']; \$data = \$author['data']; \$label = \$author['key'];", 1],
    ["\$same(\$lower(\$payload[\$q]['correct_tokens']), \$lower(\$expected['correct_tokens']), 'Actual widget ordered correct tokens');",
        "\$same(\$payload[\$q]['correct_tokens'], \$expected['correct_tokens'], 'Actual widget exact ordered tokens and capitalization');", 1],
    ["\$available = array_count_values(\$lower(\$payload[\$q]['options']));",
        "\$available = array_count_values(\$payload[\$q]['options']);", 1],
    ["array_count_values(\$lower(\$payload[\$q]['correct_tokens']))",
        "array_count_values(\$payload[\$q]['correct_tokens'])", 1],
    ["foreach (['title', 'intro', 'warning'] as \$field)", "foreach (['title', 'intro', 'warning', 'outro'] as \$field)", 1],
    [$oldDetailQuery, $ownedDetailQuery, 1],
    [$oldForms, $allFormFields, 1],
    [$selectedLoop, $projectedSelectedLoop, 1],
]);
