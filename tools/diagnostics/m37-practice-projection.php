<?php

// Finite interaction mapping, not a new question bank. All original M21
// prompts, translations, subparts and explained keys stay author-owned.
function m37Practice(int $i, array $author, array $bank): array
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
        $forms = m37Plain($author['answers'][0]);
        $to = m37Plain($author['answers'][1]);
        $p['selects'] = [
            ['options' => [$forms,
                'We avoid to store paint near the heater. We decided moving the tins. Рішення доводить, що банки вже переставили.',
                'We avoid storing paint near the heater. We decided to move the tins. To move саме доводить завершене переставлення банок.'],
                'answer' => $forms, 'source_case' => 1],
            ['options' => [$to,
                'I look forward to meet the curator. I plan to meeting her on Friday. Обидва to — прийменники.',
                'I look forward to meeting the curator. I plan to meet her on Friday. Обидва to — маркери інфінітива.'],
                'answer' => $to, 'source_case' => 2],
        ];
        $p['choices'] = [
            ['prompt' => 'A) '.m37Plain($author['answers'][2]).'<br>B) I remember to pack the vase yesterday. Remember labelling the box before sending it. I remember returning the trolley yesterday. Усі три речення передають той самий заданий ракурс.',
                'answer' => 'a', 'source_case' => 3],
            ['prompt' => 'A) '.m37Plain($author['answers'][3]).'<br>B) The students stopped to whisper. The students stopped reading the sign. У б) припинилося читання, а не ходьба.',
                'answer' => 'a', 'source_case' => 4],
        ];
        $try = 'Mila tried to lift the heavy lid. Mila tried opening the side vent to cool the room.';
        $edit = 'Yesterday we decided to fix the shelf. We tried to loosen the screw. We look forward to seeing our helper.';
        $p['inputs'] = [
            m37TokenInput($try, 5, [$try,
                'Mila tried to lift the heavy lid. To cool the room, Mila tried opening the side vent.']),
            m37TokenInput($edit, 6, [$edit,
                'Yesterday, we decided to fix the shelf. We tried to loosen the screw. We look forward to seeing our helper.',
                'We decided to fix the shelf yesterday. We tried to loosen the screw. We look forward to seeing our helper.',
                'Yesterday we decided to fix the shelf and tried to loosen the screw. We look forward to seeing our helper.',
                'Yesterday, we decided to fix the shelf and tried to loosen the screw. We look forward to seeing our helper.',
                'We decided to fix the shelf yesterday and tried to loosen the screw. We look forward to seeing our helper.',
                'Yesterday we decided to fix the shelf. We tried to loosen the screw, and we look forward to seeing our helper.',
                'We decided to fix the shelf yesterday. We tried to loosen the screw, and we look forward to seeing our helper.']),
        ];
    } elseif ($i === 1) {
        $whose = m37Plain($author['answers'][0]);
        $prepositions = m37Plain($author['answers'][1]);
        $p['selects'] = [
            ['options' => [$whose,
                'We rented a studio who’s windows face the river. Who’s означає належність.',
                'We rented a studio whose its windows face the river. Whose може стосуватися лише людини.'],
                'answer' => $whose, 'source_case' => 1],
            ['options' => [$prepositions,
                'The conservator with whom we spoke recommended a softer brush. The frame to which the portrait rests is wooden. Прийменники обираємо лише для формального стилю.',
                'The conservator to that we spoke recommended a softer brush. The frame on that the portrait rests is wooden. That підходить після винесеного прийменника.'],
                'answer' => $prepositions, 'source_case' => 2],
        ];
        $p['choices'] = [
            ['prompt' => 'A) '.m37Plain($author['answers'][2]).'<br>B) The photographs, which have white frames, were moved. Коми відбирають лише дві з шести фотографій; решта не мають білих рамок.',
                'answer' => 'a', 'source_case' => 3],
            ['prompt' => 'A) '.m37Plain($author['answers'][3]).'<br>B) У а) і в) займенники можна опустити. The restorer whom we believe can repair the frame is away. Whom потрібне лише через сусіднє we believe.',
                'answer' => 'a', 'source_case' => 4],
        ];
        $folder = 'They placed the folder beside the lamp. The folder was damaged.';
        $workshop = 'The workshop whose three presses need servicing has hired Iva, who maintains them.';
        $p['inputs'] = [
            m37TokenInput($folder, 5, [$folder,
                'The folder was damaged. They placed it beside the lamp.',
                'The folder was damaged. They placed the folder beside the lamp.']),
            m37TokenInput($workshop, 6),
        ];
    } elseif ($i === 2) {
        $passive = m37Plain($author['answers'][0]);
        $only = m37Plain($author['answers'][3]);
        $p['selects'] = [
            ['options' => [$passive,
                'Rarely did the samples be stored outside the cold room. Збережено Present Perfect Passive.',
                'Never were the samples stored outside the cold room by technicians. Частотність, час і виконавці не змінилися.'],
                'answer' => $passive, 'source_case' => 1],
            ['options' => [$only,
                'Only could the archivist open the vault. Only after was the check complete could the vault be opened. Only завжди інвертує обидві частини.',
                'Only the archivist opened the vault. Only after the check was complete could the archivist open it. Можливість означає доведене відкриття; пасив не потрібен.'],
                'answer' => $only, 'source_case' => 4],
        ];
        $p['choices'] = [
            ['prompt' => 'A) '.m37Plain($author['answers'][1]).'<br>B) Hardly had the dispatcher finished checking the list than the alarm sounded. Це доводить причинність і так само підходить для проміжку в три дні.',
                'answer' => 'a', 'source_case' => 2],
            ['prompt' => 'A) '.m37Plain($author['answers'][4]).'<br>B) Under no circumstances may visitors not remove the seals. At no time during the trial may the access code be shared. Друге not і заміна was на may не змінюють заданого змісту.',
                'answer' => 'a', 'source_case' => 5],
        ];
        $after = 'Only after the editor had confirmed consent was the material released.';
        $edit = 'No sooner had the display been installed than the visitors arrived. Not only the architect but also the caretaker welcomed them. The visitors rarely speak loudly in this hall.';
        $p['inputs'] = [m37TokenInput($after, 3), m37TokenInput($edit, 6)];
    } else {
        throw new RuntimeException('M37 practice refuses an unknown author target.');
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
                    m37Plain($author['answers'][$case - 1]));
            }
        }
        unset($item);
    }
    return $p;
}
