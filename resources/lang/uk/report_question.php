<?php

return [
    'title' => 'Що не так із питанням?',
    'issue' => [
        'correct_answer_in_verb_hint' => 'Правильна відповідь показана у verb_hint',
        'missing_verb_hint' => 'Відсутній verb_hint',
        'wrong_accepted_answer' => 'Неправильна правильна відповідь',
        'wrong_or_extra_option' => 'Неправильний або зайвий варіант відповіді',
        'multiple_valid_answers_only_one_accepted' => 'Є кілька правильних варіантів, але тест приймає тільки один',
        'typo_or_bad_translation' => 'Помилка в тексті питання або перекладі',
        'wrong_topic_or_level' => 'Питання не відповідає темі або рівню',
        'other' => 'Інше',
    ],
    'issue_description' => [
        'correct_answer_in_verb_hint' => 'verb_hint має бути підказкою, а не готовою правильною відповіддю.',
        'missing_verb_hint' => 'Без verb_hint серед варіантів може бути кілька граматично можливих відповідей.',
        'wrong_accepted_answer' => 'Тест приймає неправильну відповідь або не приймає правильну.',
        'wrong_or_extra_option' => 'Серед options є неправильний, зайвий, дубльований або misleading варіант.',
        'multiple_valid_answers_only_one_accepted' => 'Кілька варіантів граматично підходять, але accepted answer тільки один.',
        'typo_or_bad_translation' => 'Помилка в українському чи англійському тексті, граматиці, пунктуації або перекладі.',
        'wrong_topic_or_level' => 'Питання не відповідає тесту, темі або заявленому CEFR-рівню.',
        'other' => 'Для проблем, які не підпадають під попередні категорії.',
    ],
    'comment_label' => 'Коментар',
    'comment_optional' => 'Коментар необов’язковий, якщо вибрано тип помилки',
    'other_requires_comment_hint' => 'Опишіть проблему в коментарі',
    'validation' => [
        'issue_or_comment_required' => 'Виберіть хоча б один тип помилки або напишіть коментар',
    ],
    'submit' => 'Надіслати report',
    'success' => 'Report збережено',
    'admin_badge' => 'Є report',
    'admin_panel_title' => 'Report питання',
    'no_comment' => 'Коментар відсутній',
    'no_issue_type' => 'Тип помилки не вказано',
    'saving' => 'Зберігаємо...',
    'error' => 'Не вдалося зберегти report.',
];
