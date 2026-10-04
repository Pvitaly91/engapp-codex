<?php

// Finite interaction mapping of all eighteen accepted M19 prompts and explained keys.
// No universal semantic scorer. Full author criteria remain readable in the practice.
function m35Practice(int $i, array $author, array $bank): array
{
    $p = ['title' => 'Практика', 'choice_options' => ['a', 'b'], 'author_self_check' => $author,
        'selects' => [], 'choices' => [], 'inputs' => [],
        'select_title' => 'Вправа 1. Обери точну відповідь',
        'choice_title' => 'Вправа 2. Обери правильне твердження',
        'input_title' => 'Вправа 3. Склади відповідь',
        'linked_practice' => ['source' => 'theory_links', 'question_types' => [$bank['question_type']],
            'seeder_classes' => [$bank['seeder_class']], 'title' => 'Вправа 4. Побудуй речення',
            'intro' => 'Склади англійське речення за українським.', 'footer' => 'Завдання з наявного тесту цієї сторінки.']];
    if ($i === 0) {
        $belief = 'It is believed that the old lift is safe.';
        $times = '(а) to be empty; (б) to have left; (в) to be working.';
        $p['selects'] = [
            ['options' => [$belief, 'It is known that the old lift is safe.', 'It has been proved that the old lift is safe.'],
                'answer' => $belief, 'source_case' => 2],
            ['options' => [$times, '(а) to have been empty; (б) to leave; (в) to work.',
                '(а) to be empty; (б) to leave; (в) to have worked.'], 'answer' => $times, 'source_case' => 4],
        ];
        $p['choices'] = [
            ['prompt' => 'A) '.m35Plain($author['answers'][0]).'<br>B) Джерело — visitors; надворі чекає guide.',
                'answer' => 'a', 'source_case' => 1],
            ['prompt' => 'A) The two artists are reported to be painting a mural now.<br>B) The two artists are reported to have painted a mural now.',
                'answer' => 'a', 'source_case' => 3],
        ];
        $frames = 'The halls are believed to be empty. It is believed that the halls are empty.';
        $courier = 'On Friday, the courier was reported by the coordinator to have arrived at four on Thursday. This has not been independently confirmed.';
        $p['inputs'] = [
            m35TokenInput($frames, 5, [$frames, 'The halls are believed to be empty. It is believed the halls are empty.']),
            m35TokenInput($courier, 6, [$courier,
                'On Friday, the courier was reported by the coordinator to have arrived at four on Thursday. This report has not been independently confirmed.']),
        ];
    } elseif ($i === 1) {
        $invitations = 'Iryna had thirty invitations printed by the printing company on Wednesday.';
        $screen = 'Did you have the screen replaced yesterday? I did not have the screen replaced yesterday.';
        $p['selects'] = [
            ['options' => [$invitations, str_replace('thirty', '30', $invitations),
                'Iryna had thirty invitations print by the printing company on Wednesday.',
                'Thirty invitations were printed on Wednesday.'], 'answer' => $invitations,
                'accepted' => [$invitations, str_replace('thirty', '30', $invitations)], 'source_case' => 2],
            ['options' => [$screen, str_replace('did not', "didn't", $screen),
                'Did you had the screen replaced yesterday? I did not had the screen replaced yesterday.',
                'Did you have the screen replaced yesterday? I did have the screen replaced yesterday.'],
                'answer' => $screen, 'accepted' => [$screen, str_replace('did not', "didn't", $screen)], 'source_case' => 4],
        ];
        $p['choices'] = [
            ['prompt' => 'A) '.m35Plain($author['answers'][0]).'<br>B) Olena сама укоротила пальто; tailor лише замовив послугу.',
                'answer' => 'a', 'source_case' => 1],
            ['prompt' => 'A) '.m35Plain($author['answers'][4]).'<br>B) Гостя організувала крадіжку: вона замовила викрадення телефона.',
                'answer' => 'a', 'source_case' => 5],
        ];
        $scanner = 'I have booked my scanner in for repair on Thursday. It is expected to be ready on Friday.';
        $p['inputs'] = [
            m35TokenInput(m35Plain($author['answers'][2]), 3),
            m35TokenInput($scanner, 6, [$scanner,
                'I am going to have my scanner repaired on Thursday. It is expected to be ready on Friday.']),
        ];
    } else {
        $sensors = 'The technicians are reported to have calibrated the sensors yesterday. The sensors are reported to have been calibrated by the technicians yesterday.';
        $negation = m35Plain($author['answers'][3]);
        $p['selects'] = [
            ['options' => [$sensors,
                'The technicians are reported to have been calibrated yesterday. The sensors are reported to have calibrated the technicians yesterday.',
                'The technicians are reported to calibrate the sensors now. The sensors are reported to be calibrated now.'],
                'answer' => $sensors, 'source_case' => 2],
            ['options' => [$negation,
                'Обидва речення означають, що файл не видалили; перестановка not не змінює зміст.',
                'Перше: відомо, що файл не видалили. Друге: немає підтверджених відомостей, що файл видалили.'],
                'answer' => $negation, 'source_case' => 4],
        ];
        $parcel = 'On Friday, the parcel was reported by the archivist to have been sealed on Thursday.';
        $p['choices'] = [
            ['prompt' => 'A) '.m35Plain($author['answers'][0]).'<br>B) Curator реставрував карту в середу; conservator повідомив у понеділок.',
                'answer' => 'a', 'source_case' => 1],
            ['prompt' => 'A) '.$parcel.'<br>B) On Friday, the parcel was reported by the archivist to have been sealed by the archivist on Thursday.',
                'answer' => 'a', 'source_case' => 3],
        ];
        $update = 'Today’s update reports that two decorators are repainting the hall now.';
        $programmes = 'On Monday, the organiser reported that the printing company had printed forty programmes on Sunday. The number has not been independently checked, and there is no information about delivery.';
        $p['inputs'] = [
            m35TokenInput($update, 5, [$update,
                'Today’s update reports that the hall is being repainted by two decorators now.']),
            m35TokenInput($programmes, 6, [$programmes, str_replace('forty', '40', $programmes)]),
        ];
    }
    foreach (['selects', 'choices', 'inputs'] as $kind) {
        foreach ($p[$kind] as &$item) {
            $case = $item['source_case']; unset($item['source_case']);
            $item['source_index'] = $case; $item['context'] = $author['prompts'][$case - 1];
            $item['author_explanation'] = $author['answers'][$case - 1];
            if ($kind !== 'inputs') {
                $item['label'] = '';
                $options = $kind === 'choices' ? ($item['options'] ?? $p['choice_options']) : $item['options'];
                $item['feedback'] = array_fill_keys(array_map(fn ($option) => mb_strtolower($option, 'UTF-8'), $options),
                    m35Plain($author['answers'][$case - 1]));
            }
        }
        unset($item);
    }
    return $p;
}
