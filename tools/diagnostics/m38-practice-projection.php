<?php

// Finite presentation of all 18 accepted M22 cases; not a new bank.
function m38Practice(int $i, array $author, array $bank): array
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
        $countability = m38Plain($author['answers'][0]);
        $quantity = m38Plain($author['answers'][1]);
        $p['selects'] = [
            ['options' => [$countability,
                'We received three informations and a useful research about the old bridge. A робить research злічуваним; відомо про одне дослідження.',
                'We received three pieces of information and a useful study about the old bridge. Кількість досліджень не змінилася.'],
                'answer' => $countability, 'source_case' => 1],
            ['options' => [$quantity,
                'Є суперечність: a few завжди означає достатню кількість; few означало б жодної етикетки.',
                'Суперечності немає, але a few доводить, що залишилося рівно п’ять етикеток; few означає нуль.'],
                'answer' => $quantity, 'source_case' => 2],
        ];
        $p['choices'] = [
            ['prompt' => 'A) '.m38Plain($author['answers'][2]).'<br>B) Please put the pencil beside a logbook. We need an information about yesterday’s delivery. The можливе лише після першої згадки.',
                'answer' => 'a', 'source_case' => 3],
            ['prompt' => 'A) '.m38Plain($author['answers'][3]).'<br>B) A number of labels is missing. The number of missing labels are not known. Перше речення встановлює рівно п’ять етикеток.',
                'answer' => 'a', 'source_case' => 4],
        ];
        $evidence = 'There is not much evidence for this explanation.';
        $catalogue = 'We have a little useful information. It is not enough for a complete catalogue.';
        $p['inputs'] = [m38TokenInput($evidence, 5, [$evidence,
                'For this explanation, there is not much evidence.']),
            m38TokenInput($catalogue, 6, [$catalogue,
                'We have a little useful information, but it is not enough for a complete catalogue.'])];
    } elseif ($i === 1) {
        $reference = m38Plain($author['answers'][0]);
        $articles = m38Plain($author['answers'][1]);
        $p['selects'] = [
            ['options' => [$reference,
                'The tray — будь-яка нова таця. The other four — усі інші чашки студії; текст доводить, що поза тацею чашок немає.',
                'The tray — принесена таця, а the other four — чотири нові чашки поза згаданими шістьма.'],
                'answer' => $reference, 'source_case' => 1],
            ['options' => [$articles,
                'We found the cabinet in an empty room. A cabinet was locked, so we needed the key. A furniture needs care. Усі предмети вже визначені.',
                'We found a cabinet in an empty room. The cabinet was locked, so we needed the key. The furniture needs care. Умова визначає конкретний ключ і конкретну групу меблів.'],
                'answer' => $articles, 'source_case' => 2],
        ];
        $p['choices'] = [
            ['prompt' => 'A) '.m38Plain($author['answers'][2]).'<br>B) Would you like any biscuits? Are there some lockers available? You may choose some cup from these five. Any буває тільки в запереченнях і питаннях; some автоматично доводить not all.',
                'answer' => 'a', 'source_case' => 3],
            ['prompt' => 'A) '.m38Plain($author['answers'][3]).'<br>B) Each of three drafts have a number. Every of the three drafts has a number. Both of the final layouts is ready. Either of the two layouts are suitable. Neither of them are signed. Це прямо заданий формальний зразок.',
                'answer' => 'a', 'source_case' => 4],
        ];
        $lamps = 'Not both lamps work.';
        $folders = 'Six folders were placed in the cabinet. Two of the six folders were checked. Both of the checked folders were dry.';
        $p['inputs'] = [m38TokenInput($lamps, 5),
            m38TokenInput($folders, 6, [$folders,
                'Six folders were placed in the cabinet. Two of the six folders were checked. The two checked folders were dry.'])];
        // The selected conclusion alone must not lose the author's second
        // semantic subpart: what is unknown about the untested second lamp.
        $unknown = '«Працює рівно одна» також не підтверджено: другу ще не перевірили.';
        $missing = 'Neither lamp works. — Жодна з двох ламп не працює — потребувало б знання, що друга теж несправна.';
        $p['inputs'][0]['m38_semantic_checks'] = [
            ['options' => [$missing,
                'Neither lamp works. підтверджено вже несправністю лише першої лампи.'], 'answer' => $missing],
            ['options' => [$unknown,
                'Друга лампа працює, тому працює рівно одна; це вже встановлено умовою.'], 'answer' => $unknown],
        ];
    } elseif ($i === 2) {
        $raise = m38Plain($author['answers'][0]);
        $roles = m38Plain($author['answers'][1]);
        $p['selects'] = [
            ['options' => [$raise,
                'I would like to answer a question about spare-key storage. Відповідь уже відома й питання вирішено.',
                'I would like to raise a question about spare-key storage. Raise означає, що відповідь уже надано.'],
                'answer' => $raise, 'source_case' => 1],
            ['options' => [$roles,
                'The narrow passage faces a challenge in moving the scenery. The team poses a challenge to the passage. Ролі залишилися тими самими.',
                'The narrow passage makes moving the scenery impossible. The team cannot move it through the passage. Труднощі завжди означають неможливість.'],
                'answer' => $roles, 'source_case' => 2],
        ];
        $p['choices'] = [
            ['prompt' => 'A) '.m38Plain($author['answers'][2]).'<br>B) Лише set a precedent і draws a distinction допустимі; established і makes завжди неправильні. Прецедент гарантує майбутній повтор рішення.',
                'answer' => 'a', 'source_case' => 3],
            ['prompt' => 'A) '.m38Plain($author['answers'][3]).'<br>B) The guide draws distinction among repair. It answers two question on the warranty. Артикль, друга сторона порівняння й число не мають значення.',
                'answer' => 'a', 'source_case' => 4],
        ];
        $edit = 'The team faces a challenge in using the cramped store. The note raises a question about storage but does not answer it. One dated invoice provides documentary evidence of the purchase, not a complete account of the object’s history.';
        $p['inputs'] = [m38TokenInput('a small difference', 5), m38TokenInput($edit, 6, [$edit,
            'The team faces a challenge in using the cramped store. The note raises a question about storage, but does not answer it. One dated invoice provides documentary evidence of the purchase, not a complete account of the object’s history.',
            'The team faces a challenge in using the cramped store. The note raises a question about storage but does not answer it. One dated invoice provides documentary evidence of the purchase but not a complete account of the object’s history.'])];
        $statistical = 'Без статистичного контексту додавати «статистично» не можна; сама числова різниця також не визначає статистичної значущості.';
        $p['inputs'][0]['m38_semantic_checks'] = [['options' => [$statistical,
            'Significant і різниця 1 000 / 1 002 уже доводять статистичну значущість без опису статистичної процедури.'], 'answer' => $statistical]];
    } else {
        throw new RuntimeException('M38 practice refuses an unknown author target.');
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
                    m38Plain($author['answers'][$case - 1]));
            }
        }
        unset($item);
    }
    return $p;
}
