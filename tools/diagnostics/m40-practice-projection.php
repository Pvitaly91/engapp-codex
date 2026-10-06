<?php

// Finite technical presentation of the eighteen immutable M24 self-checks.
// Full prompts/keys remain in the original author arrays, never in candidates.
function m40Practice(array $author, int $owner, array $bank): array
{
    $classes = [
        'Database\\Seeders\\V3\\Polyglot\\PolyglotPresentPerfectContinuousVsPresentPerfectLessonSeeder',
        'Database\\Seeders\\V3\\Polyglot\\PolyglotNarrativeTensesBasicsLessonSeeder',
        'Database\\Seeders\\V3\\Polyglot\\PolyglotFinalDrillB1LessonSeeder',
    ];
    if (!in_array($owner, [0, 1, 2], true) || count($author['prompts'] ?? []) !== 6
        || count($author['answers'] ?? []) !== 6 || !is_string($author['section_title'] ?? null)
        || ($bank['seeder_class'] ?? null) !== $classes[$owner]
        || (string) ($bank['question_type'] ?? '') !== '4' || ($bank['level'] ?? null) !== 'B1'
        || count($bank['question_ids'] ?? []) !== ($bank['question_count'] ?? null)) {
        throw new RuntimeException('M40 refuses unknown/incomplete author or actual-bank ownership.');
    }
    $options = static fn (array $labels): array => array_map(fn ($label) => ['value' => $label, 'label' => $label], $labels);
    $choice = static fn (string $id, string $label, array $labels, string $answer, string $kind = 'choice'): array =>
        ['id' => $id, 'kind' => $kind, 'label' => $label, 'options' => $options($labels), 'answer' => $answer];
    $manual = static function (string $id, string $label, string $answer, array $groups, array $accepted = []): array {
        if (implode(' ', $groups) !== $answer) { throw new RuntimeException('M40 exact answer/token-group identity differs.'); }
        foreach ($groups as $group) {
            $words = preg_match_all('/\S+/u', $group);
            if ($words < 1 || $words > 3 || preg_match('/[.!?]\s+\S/u', $group)) {
                throw new RuntimeException('M40 logical token crosses a sentence boundary or exceeds three words.');
            }
        }
        return ['id' => $id, 'kind' => 'manual', 'label' => $label, 'options' => [], 'answer' => $answer,
            'accepted' => $accepted !== [] ? $accepted : [$answer], 'tokens' => $groups];
    };
    $case = static fn (int $index, array $controls): array => ['source_index' => $index,
        'interaction' => count($controls) > 1 ? 'compound' : $controls[0]['kind'], 'controls' => $controls];

    if ($owner === 0) {
        $introduction = 'I have already translated the introduction. I have been translating the second chapter for two hours, but it is not ready yet.';
        $cases = [
            $case(1, [
                $choice('m40-p1-oleh', 'Oleh ___ four parcels.', ['has packed', 'has been packing'], 'has packed', 'select'),
                $choice('m40-p1-marta', 'Marta ___ parcels for forty minutes.', ['has packed', 'has been packing'], 'has been packing', 'select'),
            ]),
            $case(2, [
                $choice('m40-p2-duration', 'We have been waiting ___ twenty minutes.', ['for', 'since'], 'for', 'select'),
                $choice('m40-p2-start', 'I have known Danylo ___ September.', ['for', 'since'], 'since', 'select'),
            ]),
            $case(3, [$manual('m40-p3-answer', 'Виправлене речення', 'I have known the password since Monday.',
                ['I', 'have known', 'the password', 'since Monday.'])]),
            $case(4, [
                $choice('m40-p4-contradiction', 'Чи суперечать речення одне одному?', ['Ні', 'Так'], 'Ні'),
                $choice('m40-p4-completion', 'Чи всі стільці готові?', ['Невідомо', 'Так', 'Ні'], 'Невідомо'),
            ]),
            $case(5, [
                $choice('m40-p5-natural', 'Чи обидва варіанти можуть передати задані відомості?', ['Так', 'Ні'], 'Так'),
                $choice('m40-p5-intention', 'Чи доводить другий варіант намір переїхати?', ['Ні', 'Так'], 'Ні'),
            ]),
            $case(6, [$manual('m40-p6-answer', 'Два речення', $introduction,
                ['I have already', 'translated', 'the introduction.', 'I have been', 'translating', 'the second chapter',
                    'for two hours,', 'but it is', 'not ready yet.'],
                [$introduction, 'I have already translated the introduction. I have been translating the second chapter for two hours, but it is not yet ready.'])]),
        ];
    } elseif ($owner === 1) {
        $sequence = 'Yesterday I unlocked the gate, switched on the light and opened the window.';
        $story = 'Someone had removed the note before I arrived. Olena was talking on the phone when I arrived. Then I opened the cupboard.';
        $cases = [
            $case(1, [$manual('m40-n1-answer', 'Послідовність подій', $sequence,
                ['Yesterday I', 'unlocked the gate,', 'switched on', 'the light', 'and opened', 'the window.'],
                [$sequence, 'Yesterday I unlocked the gate. I switched on the light and opened the window.'])]),
            $case(2, [
                $choice('m40-n2-process', 'Mila ___ envelopes', ['was sorting', 'had sorted'], 'was sorting', 'select'),
                $choice('m40-n2-arrival', 'when Danylo ___.', ['arrived', 'was arriving'], 'arrived', 'select'),
                $choice('m40-n2-stopped', 'Чи припинила Міла сортувати конверти після цього?', ['Не сказано', 'Так', 'Ні'], 'Не сказано'),
            ]),
            $case(3, [$manual('m40-n3-answer', 'Речення з When we arrived,', 'When we arrived, the courier had already left.',
                ['When we arrived,', 'the courier', 'had already left.'],
                ['When we arrived, the courier had already left.', 'When we arrived, the courier had left.'])]),
            $case(4, [$choice('m40-n4-order', 'Чи обидва варіанти передають задану послідовність?', ['Так', 'Ні'], 'Так')]),
            $case(5, [
                $manual('m40-n5-question', 'Запитання', 'Did she open the box?', ['Did she', 'open', 'the box?']),
                $manual('m40-n5-guide', 'Речення про екскурсовода', 'The guide had gone home before I called.',
                    ['The guide', 'had gone home', 'before I called.']),
            ]),
            $case(6, [$manual('m40-n6-answer', 'Три речення', $story,
                ['Someone', 'had removed', 'the note', 'before I arrived.', 'Olena', 'was talking', 'on the phone',
                    'when I arrived.', 'Then I opened', 'the cupboard.'],
                [$story, 'Olena was talking on the phone when I arrived. Someone had removed the note before I arrived. Then I opened the cupboard.'])]),
        ];
    } else {
        $cases = [
            $case(1, [
                $choice('m40-b1-written', 'I ___ three addresses today.', ['have written', 'wrote'], 'have written', 'select'),
                $choice('m40-b1-prepared', 'I ___ the envelopes before Danylo arrived yesterday.', ['had prepared', 'prepared'], 'had prepared', 'select'),
            ]),
            $case(2, [
                $choice('m40-b2-process', 'At three, I ___ the books.', ['will be packing', 'will pack'], 'will be packing', 'select'),
                $choice('m40-b2-completion', 'By five, I ___ all the packing.', ['will have finished', 'will finish'], 'will have finished', 'select'),
            ]),
            $case(3, [
                $manual('m40-b3-question', 'Непряме запитання', 'Mila asked me if I was ready.', ['Mila asked me', 'if I was', 'ready.']),
                $manual('m40-b3-instruction', 'Негативна вказівка', 'She told me not to open the parcel.',
                    ['She told me', 'not to open', 'the parcel.']),
            ]),
            $case(4, [
                $manual('m40-b4-passive', 'А: пасивна вимога', 'The boxes must be labelled by the volunteers.',
                    ['The boxes', 'must be labelled', 'by the volunteers.'],
                    ['The boxes must be labelled by the volunteers.', 'The boxes must be labeled by the volunteers.']),
                $manual('m40-b4-causative', 'Б: замовлена послуга', 'I had my bicycle repaired yesterday.',
                    ['I had', 'my bicycle', 'repaired yesterday.']),
            ]),
            $case(5, [
                $manual('m40-b5-relative', 'Речення з who', 'Marta, who lives nearby, agreed to help.',
                    ['Marta,', 'who lives nearby,', 'agreed to help.']),
                $manual('m40-b5-although', 'Речення з although', 'Although it was late, we continued.',
                    ['Although', 'it was late,', 'we continued.']),
            ]),
            $case(6, [
                $manual('m40-b6-wish', 'А: бажаний теперішній стан', 'I wish I had a bigger desk.', ['I wish I', 'had', 'a bigger desk.']),
                $manual('m40-b6-conditional', 'Б: третя умовна модель', 'If we had left earlier, we would have caught the bus.',
                    ['If we', 'had left earlier,', 'we', 'would have caught', 'the bus.']),
                $manual('m40-b6-might', 'В: припущення з might', 'Lena might be in the reading room.', ['Lena', 'might be', 'in', 'the reading room.']),
            ]),
        ];
    }
    return ['title' => $author['section_title'], 'author_self_check' => $author,
        'm40_practice_ui_v1' => ['revision' => 1], 'cases' => $cases,
        'linked_practice' => ['source' => 'theory_links', 'question_types' => [(string) $bank['question_type']],
            'seeder_classes' => [$bank['seeder_class']], 'title' => 'Побудуй речення',
            'intro' => 'Склади англійське речення за українським.', 'footer' => 'Завдання з наявного тесту цієї сторінки.']];
}
