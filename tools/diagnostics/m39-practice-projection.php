<?php

// M39 is technical presentation only: all cases and keys come from frozen M23.
// Distractors below are source counterexamples or mechanical forms of the same item.
function m39Practice(int $i, array $author, array $bank): array
{
    $p = ['title' => 'Практика', 'choice_options' => ['a', 'b'], 'author_self_check' => $author,
        'selects' => [], 'choices' => [], 'inputs' => [],
        'select_title' => 'Вправа 1. Обери точну відповідь',
        'choice_title' => 'Вправа 2. Обери правильне твердження',
        'input_title' => 'Вправа 3. Склади відповідь',
        'linked_practice' => ['source' => 'theory_links', 'question_types' => [$bank['question_type']],
            'seeder_classes' => [$bank['seeder_class']], 'title' => 'Вправа 4. Побудуй речення',
            'intro' => 'Склади англійське речення за українським.', 'footer' => 'Завдання з наявного тесту цієї сторінки.']];
    $keys = array_map('m39Plain', $author['answers']);
    if ($i === 0) {
        $p['selects'] = [
            ['options' => [$keys[0], 'The gradual expansion of the reading rooms are scheduled for autumn.'],
                'answer' => $keys[0], 'source_case' => 1],
            ['options' => [$keys[1],
                'A reassessment of the exhibition plan by the committee will take place in October.',
                'A reassessment of the exhibition plan by the committee took place in October.'],
                'answer' => $keys[1], 'source_case' => 2],
        ];
        $p['choices'] = [
            ['prompt' => 'A) '.$keys[2].'<br>B) The reduction in waiting times has improved the service.',
                'answer' => 'a', 'source_case' => 3],
            ['prompt' => 'A) '.$keys[3].'<br>B) The curator assessed the insurance documents on Monday.',
                'answer' => 'a', 'source_case' => 4],
        ];
        $digitisation = 'The digitisation of the catalogue by the volunteers began in June. The work is still in progress.';
        $proposal = 'The team has proposed an expansion of the hall. No decision has been made, and the new waiting times have not been measured.';
        $p['inputs'] = [
            m39TokenInput($digitisation, 5, [$digitisation,
                'The digitisation by the volunteers of the catalogue began in June. The work is still in progress.']),
            m39TokenInput($proposal, 6, [$proposal,
                'The team has proposed an expansion of the hall. No decision has been made. The new waiting times have not been measured.']),
        ];
    } elseif ($i === 1) {
        $p['selects'] = [
            ['options' => [$keys[0],
                'If our technician had saved the settings, we would have been able to restore them now.'],
                'answer' => $keys[0], 'source_case' => 1],
            ['options' => [$keys[1],
                'Only after the supervisor had approved the labels did the boxes dispatched.'],
                'answer' => $keys[1], 'source_case' => 2],
        ];
        $p['choices'] = [
            ['prompt' => 'A) '.$keys[2].'<br>B) It is believed that the envelope was opened before delivery.',
                'answer' => 'a', 'source_case' => 3],
            ['prompt' => 'A) '.$keys[3].'<br>B) The two guides who they have completed the course are leading the tour.',
                'answer' => 'a', 'source_case' => 4],
        ];
        $leila = 'Leila may have sent the draft yesterday, but we have not checked the mailbox.';
        $request = 'The dates in the catalogue were checked yesterday. The descriptions have not yet been checked. Could you send the final file by Friday?';
        $p['inputs'] = [
            m39TokenInput($leila, 5, [$leila]),
            m39TokenInput($request, 6, [$request,
                'The dates in the catalogue were checked yesterday. The descriptions have not been checked yet. Could you send the final file by Friday?']),
        ];
    } elseif ($i === 2) {
        $p['selects'] = [
            ['options' => [$keys[0],
                'Had the guide not brought a spare lamp, we would have been stranded underground.'],
                'answer' => $keys[0], 'source_case' => 1],
            ['options' => [$keys[1],
                'It is thought that the portraits were moved before the gallery closed.'],
                'answer' => $keys[1], 'source_case' => 2],
        ];
        // Full author keys retain BOTH Marta subparts, the need not alternative,
        // guaranteed b, and the unknown distribution; no case is reduced to a phrase.
        $p['choices'] = [
            ['prompt' => 'A) '.$keys[2].'<br>B) Marta didn’t need to print a second timetable.',
                'answer' => 'a', 'source_case' => 3],
            ['prompt' => 'A) кожний непридатний<br>B) '.$keys[3].'<br>C) рівно один непридатний',
                'options' => ['a', 'b', 'c'], 'answer' => 'b', 'source_case' => 4],
        ];
        $register = 'The register includes every member. However, two entries have no phone number. These incomplete entries need to be updated.';
        $anika = 'Anika’s indoor test of the first prototype on Monday was successful. The second prototype has not been tested. According to Anika, the first prototype may also work outdoors, but no outdoor test has taken place.';
        $p['inputs'] = [
            m39TokenInput($register, 5, [$register,
                'The register includes every member. However, two entries have no phone number. The two entries without phone numbers need to be updated.']),
            m39TokenInput($anika, 6, [$anika,
                'Anika’s indoor test of the first prototype on Monday was successful. The second prototype has not been tested. According to Anika, the first prototype may also work outdoors. No outdoor test has taken place.',
                'Anika’s indoor test of the first prototype on Monday was successful. The second prototype remains untested. According to Anika, the first prototype may also work outdoors; however, no outdoor test has taken place.']),
        ];
    } else {
        throw new RuntimeException('M39 practice refuses an unknown author target.');
    }
    foreach (['selects', 'choices', 'inputs'] as $kind) {
        foreach ($p[$kind] as &$item) {
            $case = $item['source_case']; unset($item['source_case']);
            $item['source_index'] = $case; $item['context'] = $author['prompts'][$case - 1];
            $item['author_explanation'] = $author['answers'][$case - 1];
            if ($kind !== 'inputs') {
                $item['label'] = '';
                $options = $kind === 'choices' ? ($item['options'] ?? $p['choice_options']) : $item['options'];
                $item['feedback'] = array_fill_keys(array_map(fn ($option) => mb_strtolower($option, 'UTF-8'), $options), $keys[$case - 1]);
            }
        }
        unset($item);
    }
    return $p;
}
