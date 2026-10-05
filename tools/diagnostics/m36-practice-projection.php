<?php

// Finite technical interaction mapping of all eighteen accepted M20 cases.
// Exact prompts, translations and complete explained keys remain author-owned.
function m36Practice(int $i, array $author, array $bank): array
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
        $signs = m36Plain($author['answers'][0]);
        $expectation = m36Plain($author['answers'][3]);
        $p['selects'] = [
            ['options' => [$signs,
                'Someone must have opened the box — незалежно доведений факт; відомо, хто, коли й чому її відкрив.',
                'Відкрита коробка й розірвана пломба — лише припущення, а конкретна особа вже відома.'],
                'answer' => $signs, 'source_case' => 1],
            ['options' => [$expectation,
                'В обох випадках should have доводить, що дію точно не виконано; посилка точно не прибула.',
                'Папки вже підписано, а прибуття посилки незалежно підтверджено.'],
                'answer' => $expectation, 'source_case' => 4],
        ];
        $repairs = 'She might have left the note. He must have gone home. They can’t have seen the rehearsal.';
        $p['choices'] = [
            ['prompt' => 'A) '.m36Plain($author['answers'][2]).'<br>B) Обидва could have завжди означають, що Раві не скористався автобусом.',
                'answer' => 'a', 'source_case' => 3],
            ['prompt' => 'A) '.$repairs.'<br>B) She might of left the note. He must have went home. They don’t can have seen the rehearsal.',
                'answer' => 'a', 'source_case' => 5],
        ];
        $deductions = 'Nora must have unlocked the door. Eli can’t have been at the studio at nine.';
        $cancellation = 'The studio was dark yesterday. The rehearsal may have been cancelled.';
        $p['inputs'] = [
            m36TokenInput($deductions, 2, [$deductions,
                'Nora must have unlocked the door. Eli cannot have been at the studio at nine.']),
            m36TokenInput($cancellation, 6, [$cancellation,
                str_replace('may have', 'might have', $cancellation),
                str_replace('may have', 'could have', $cancellation)]),
        ];
    } elseif ($i === 1) {
        $request = m36Plain($author['answers'][0]);
        $meanings = m36Plain($author['answers'][4]);
        $p['selects'] = [
            ['options' => [$request,
                'Requests that the entrance be kept clear доводить виконання; reports that the entrance is clear — лише бажаний стан.',
                'Обидва речення є вимогами й незалежно підтверджують виконання.'],
                'answer' => $request, 'source_case' => 1],
            ['options' => [$meanings,
                'У всіх трьох реченнях suggest/insist виражають вимогу: обидва is слід замінити на be; коробку вже перенесли.',
                'У всіх трьох реченнях ідеться про підтверджений виконаний факт; be moved не є вимогою.'],
                'answer' => $meanings, 'source_case' => 5],
        ];
        $requirements = 'The editor requires that the assistant not delete the comments. The editor requires that the reviewers be informed by the assistant by noon.';
        $past = 'On Monday, Leo recommended that Emma check the heading before publication.';
        $p['choices'] = [
            ['prompt' => 'A) '.$requirements.'<br>B) The editor requires that the assistant doesn’t delete the comments. The editor requires that the reviewers are informed by noon.',
                'answer' => 'a', 'source_case' => 3],
            ['prompt' => 'A) '.$past.'<br>B) On Monday, Leo recommended that Emma checked the heading before publication, and she checked it.',
                'answer' => 'a', 'source_case' => 4],
        ];
        $bare = 'The coordinator recommends that each reviewer read the brief by Friday.';
        $lest = 'She labelled both folders so that the reviewers would not confuse them.';
        $p['inputs'] = [
            m36TokenInput($bare, 2),
            m36TokenInput($lest, 6, [$lest, str_replace('would not', 'wouldn’t', $lest)]),
        ];
    } elseif ($i === 2) {
        $well = m36Plain($author['answers'][0]);
        $performance = m36Plain($author['answers'][3]);
        $p['selects'] = [
            ['options' => [$well,
                'May well explain доводить причину; might as well rehearse повідомляє, що репетиція вже відбулася.',
                'Well та as well мають однакову функцію й відрізняються лише ступенем упевненості.'],
                'answer' => $well, 'source_case' => 1],
            ['options' => [$performance,
                'Didn’t need to в усіх трьох випадках доводить невиконання й заборону друку.',
                'Didn’t need to в усіх трьох випадках доводить виконання; but I did є суперечністю.'],
                'answer' => $performance, 'source_case' => 4],
        ];
        $p['choices'] = [
            ['prompt' => 'A) '.m36Plain($author['answers'][1]).'<br>B) Just означає тут «щойно» й гарантує, що ескіз уже відібрано.',
                'answer' => 'a', 'source_case' => 2],
            ['prompt' => 'A) '.m36Plain($author['answers'][4]).'<br>B) Уже у випадку а) можна точно сказати Omar needn’t have booked another room: didn’t need to саме доводить бронювання.',
                'answer' => 'a', 'source_case' => 5],
        ];
        $advice = 'You might want to check the participant list before printing. It may be worth checking the participant list before printing. You would be wise to check the participant list before printing: the previous copy listed one name twice.';
        $notes = 'Ira needn’t have printed the extra poster yesterday. Missing clips could well explain today’s delay. It may be worth checking the cupboard.';
        $p['inputs'] = [
            m36TokenInput($advice, 3),
            m36TokenInput($notes, 6, [$notes, str_replace('needn’t', 'need not', $notes)]),
        ];
    } else {
        throw new RuntimeException('M36 practice refuses an unknown author target.');
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
                    m36Plain($author['answers'][$case - 1]));
            }
        }
        unset($item);
    }
    return $p;
}
