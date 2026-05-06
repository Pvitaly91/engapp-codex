<?php

return [
    'title' => 'Co jest nie tak z pytaniem?',
    'issue' => [
        'correct_answer_in_verb_hint' => 'Prawidłowa odpowiedź jest widoczna w verb_hint',
        'missing_verb_hint' => 'Brak verb_hint',
        'wrong_accepted_answer' => 'Nieprawidłowa zaakceptowana odpowiedź',
        'wrong_or_extra_option' => 'Nieprawidłowa lub zbędna opcja odpowiedzi',
        'multiple_valid_answers_only_one_accepted' => 'Kilka odpowiedzi jest poprawnych, ale test akceptuje tylko jedną',
        'typo_or_bad_translation' => 'Literówka albo błędne tłumaczenie',
        'wrong_topic_or_level' => 'Pytanie nie pasuje do tematu lub poziomu',
        'other' => 'Inne',
    ],
    'issue_description' => [
        'correct_answer_in_verb_hint' => 'verb_hint powinien naprowadzać, a nie ujawniać zaakceptowaną odpowiedź.',
        'missing_verb_hint' => 'Bez verb_hint więcej niż jedna opcja może być gramatycznie możliwa.',
        'wrong_accepted_answer' => 'Test akceptuje błędną odpowiedź albo odrzuca poprawną.',
        'wrong_or_extra_option' => 'Opcja jest błędna, zdublowana, sprzeczna albo myląca.',
        'multiple_valid_answers_only_one_accepted' => 'Kilka opcji jest gramatycznie poprawnych, ale accepted answers zawiera tylko jedną.',
        'typo_or_bad_translation' => 'Problem w tekście ukraińskim lub angielskim, gramatyce, interpunkcji albo tłumaczeniu.',
        'wrong_topic_or_level' => 'Pytanie nie pasuje do testu, tematu albo zadeklarowanego poziomu CEFR.',
        'other' => 'Dla problemów, które nie pasują do pozostałych kategorii.',
    ],
    'comment_label' => 'Komentarz',
    'comment_optional' => 'Komentarz jest opcjonalny, jeśli wybrano typ błędu',
    'other_requires_comment_hint' => 'Opisz problem w komentarzu',
    'validation' => [
        'issue_or_comment_required' => 'Wybierz co najmniej jeden typ błędu albo wpisz komentarz',
    ],
    'submit' => 'Wyślij report',
    'success' => 'Report zapisany',
    'admin_badge' => 'Jest report',
    'admin_panel_title' => 'Report pytania',
    'no_comment' => 'Brak komentarza',
    'no_issue_type' => 'Nie podano typu błędu',
    'saving' => 'Zapisywanie...',
    'error' => 'Nie udało się zapisać reportu.',
];
