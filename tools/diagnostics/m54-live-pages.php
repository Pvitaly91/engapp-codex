<?php

/** M54 anonymous HTTP/SSR profile; browser visibility remains separate. */
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
runFrozenReadOnlyProfile('m53-live-pages.php', '66c4c1ca501fa4a99e6a15a6e254b146d32a259a6a009cc72ef2e1a034b6e4b6', [
    ['M53', 'M54', 13], ['m53', 'm54', 3],
    ['PassiveConstructions', 'PassivePatterns', 1],
    ["use App\\Support\\M54BuilderHintPresentation;\n", '', 1],
    ['M54BuilderHintPresentation', 'M54ContentPatch', 4],
    ['causative|get-passive|impersonal-passive', 'phrasal-verbs|restrictions|two-object-verbs', 1],
    ["foreach (['title', 'intro', 'warning'] as \$field)", "foreach (['title', 'intro', 'warning', 'outro'] as \$field)", 1],
    [$oldDetailQuery, $ownedDetailQuery, 1],
    [$oldForms, $allFormFields, 1],
]);
