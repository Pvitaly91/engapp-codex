<?php

// Finite technical UI mapping only. Prompts and full explanations remain solely
// in the exact immutable M23 author_self_check arrays of the accepted M39 body.
function m39PracticeUiProjection(array $before, int $owner): array
{
    if (!isset($before['author_self_check']['prompts'], $before['author_self_check']['answers'])
        || count($before['author_self_check']['prompts']) !== 6 || count($before['author_self_check']['answers']) !== 6
        || !isset($before['m39_v1'], $before['linked_practice']) || !in_array($owner, [0, 1, 2], true)) {
        throw new RuntimeException('M39 practice UI refuses an unknown or incomplete author source.');
    }

    $options = static fn (array $labels): array => array_map(fn ($label) => ['value' => $label, 'label' => $label], $labels);
    $choice = static fn (string $id, string $label, array $labels, string $answer, string $kind = 'choice'): array =>
        ['id' => $id, 'kind' => $kind, 'label' => $label, 'options' => $options($labels), 'answer' => $answer];
    $manual = static function (string $id, string $answer, array $groups, array $accepted = [], string $label = 'Відповідь'): array {
        if (implode(' ', $groups) !== $answer) { throw new RuntimeException('M39 logical token groups differ from the exact answer.'); }
        foreach ($groups as $group) {
            if (preg_match_all('/\S+/u', $group) < 1 || preg_match_all('/\S+/u', $group) > 3
                || preg_match('/[.!?]\s+\S/u', $group)) {
                throw new RuntimeException('M39 token group crosses a sentence boundary or exceeds three words.');
            }
        }
        return ['id' => $id, 'kind' => 'manual', 'label' => $label, 'options' => [], 'answer' => $answer,
            'accepted' => $accepted !== [] ? $accepted : [$answer], 'tokens' => $groups];
    };
    $case = static fn (int $index, string $interaction, array $controls): array =>
        ['source_index' => $index, 'interaction' => $interaction, 'controls' => $controls];
    $existingInput = static function (int $index) use ($before): array {
        $found = array_values(array_filter($before['inputs'], fn ($item) => $item['source_index'] === $index));
        if (count($found) !== 1) { throw new RuntimeException('M39 accepted manual source index differs.'); }
        return $found[0];
    };

    if ($owner === 0) {
        $digitisation = $existingInput(5); $proposal = $existingInput(6);
        $cases = [
            $case(1, 'compound', [
                $choice('n1-form', 'Форма', ['is', 'are'], 'is', 'select'),
                $choice('n1-head', 'Головне слово', ['expansion', 'rooms'], 'expansion'),
            ]),
            $case(2, 'manual', [$manual('n2-answer',
                'A reassessment of the exhibition plan by the committee may take place in October.',
                ['A reassessment', 'of', 'the exhibition plan', 'by the committee', 'may take place', 'in October.'],
                ['A reassessment of the exhibition plan by the committee may take place in October.',
                    'A reassessment by the committee of the exhibition plan may take place in October.'])]),
            $case(3, 'compound', [
                $choice('n3-equivalence', 'Точне перефразування?', ['Ні.', 'Так.'], 'Ні.'),
                ['id' => 'n3-added-facts', 'kind' => 'multi', 'label' => 'Додані відомості',
                    'options' => $options(['час уже скоротився', 'обслуговування поліпшилося', 'лише план']),
                    'answer' => ['час уже скоротився', 'обслуговування поліпшилося']],
                $manual('n3-rewrite', 'The team plans a reduction in waiting times.',
                    ['The team', 'plans', 'a reduction', 'in waiting times.'], label: 'Редакція з reduction'),
            ]),
            $case(4, 'manual', [$manual('n4-answer',
                'The curator’s assessment of the insurance documents took place on Monday.',
                ['The curator’s assessment', 'of', 'the insurance documents', 'took place', 'on Monday.'],
                ['The curator’s assessment of the insurance documents took place on Monday.',
                    'The assessment of the insurance documents by the curator took place on Monday.'])]),
            $case(5, 'manual', [$manual('n5-answer', $digitisation['answer'],
                ['The digitisation', 'of the catalogue', 'by the volunteers', 'began in June.', 'The work', 'is still', 'in progress.'],
                $digitisation['accepted'])]),
            $case(6, 'manual', [$manual('n6-answer', $proposal['answer'],
                ['The team', 'has proposed', 'an expansion', 'of the hall.', 'No decision', 'has been made,', 'and', 'the',
                    'new waiting times', 'have not been', 'measured.'], $proposal['accepted'])]),
        ];
    } elseif ($owner === 1) {
        $leila = $existingInput(5); $request = $existingInput(6);
        $cases = [
            $case(1, 'manual', [$manual('c1-1-answer',
                'If our technician had saved the settings, we would be able to restore them now.',
                ['If', 'our technician', 'had saved', 'the settings,', 'we', 'would be able', 'to restore', 'them now.'])]),
            $case(2, 'manual', [$manual('c1-2-answer',
                'Only after the supervisor had approved the labels were the boxes dispatched.',
                ['Only after', 'the supervisor', 'had approved', 'the labels', 'were', 'the boxes', 'dispatched.'])]),
            $case(3, 'manual', [$manual('c1-3-answer',
                'The envelope is believed to have been opened before delivery.',
                ['The envelope', 'is believed', 'to have been', 'opened', 'before delivery.'])]),
            $case(4, 'manual', [$manual('c1-4-answer',
                'The two guides who have completed the course are leading the tour.',
                ['The two guides', 'who', 'have completed', 'the course', 'are leading', 'the tour.'])]),
            $case(5, 'manual', [$manual('c1-5-answer', $leila['answer'],
                ['Leila', 'may have sent', 'the draft', 'yesterday,', 'but', 'we', 'have not checked', 'the mailbox.'], $leila['accepted'])]),
            $case(6, 'manual', [$manual('c1-6-answer', $request['answer'],
                ['The dates', 'in the catalogue', 'were checked', 'yesterday.', 'The descriptions', 'have not yet', 'been checked.',
                    'Could you', 'send', 'the final file', 'by Friday?'], $request['accepted'])]),
        ];
    } else {
        $register = $existingInput(5); $anika = $existingInput(6);
        $cases = [
            $case(1, 'manual', [$manual('c2-1-answer',
                'Had the guide not brought a spare lamp, we could have been stranded underground.',
                ['Had', 'the guide', 'not brought', 'a spare lamp,', 'we', 'could have been', 'stranded underground.'])]),
            $case(2, 'manual', [$manual('c2-2-answer',
                'The portraits are thought to have been moved before the gallery closed.',
                ['The portraits', 'are thought', 'to have been', 'moved', 'before', 'the gallery closed.'])]),
            $case(3, 'compound', [
                $manual('c2-3-a', 'Marta needn’t have printed a second timetable.',
                    ['Marta', 'needn’t have', 'printed', 'a second timetable.'],
                    ['Marta needn’t have printed a second timetable.', 'Marta need not have printed a second timetable.'], 'А'),
                $choice('c2-3-b', 'Б: факт друку', ['факт друку не заданий', 'надрукувала', 'не надрукувала'], 'факт друку не заданий'),
            ]),
            $case(4, 'compound', [
                $choice('c2-4-guaranteed', 'Гарантоване твердження',
                    ['кожний непридатний', 'принаймні один непридатний', 'рівно один непридатний'], 'принаймні один непридатний'),
                $manual('c2-4-scope', 'Не встановлено точного числа непридатних і придатних.',
                    ['Не встановлено', 'точного числа', 'непридатних і придатних.'], label: 'Чого не встановлено?'),
            ]),
            $case(5, 'manual', [$manual('c2-5-answer', $register['answer'],
                ['The register', 'includes every member.', 'However,', 'two entries', 'have no', 'phone number.',
                    'These incomplete entries', 'need to be', 'updated.'], $register['accepted'])]),
            $case(6, 'manual', [$manual('c2-6-answer', $anika['answer'],
                ['Anika’s indoor test', 'of', 'the first prototype', 'on Monday', 'was successful.', 'The second prototype',
                    'has not been', 'tested.', 'According to Anika,', 'the first prototype', 'may also work', 'outdoors,', 'but',
                    'no outdoor test', 'has taken place.'], $anika['accepted'])]),
        ];
    }

    $after = $before;
    foreach (['selects', 'choices', 'inputs', 'choice_options', 'select_title', 'choice_title', 'input_title'] as $legacy) {
        unset($after[$legacy]);
    }
    $after['m39_practice_ui_v1'] = ['revision' => 1];
    $after['cases'] = $cases;
    if ($after['author_self_check'] !== $before['author_self_check']
        || $after['m39_v1'] !== $before['m39_v1'] || $after['linked_practice'] !== $before['linked_practice']) {
        throw new RuntimeException('M39 practice UI author/anchor/bank fidelity differs.');
    }
    return $after;
}
