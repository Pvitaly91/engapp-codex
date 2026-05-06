<?php

return [
    'title' => 'What is wrong with the question?',
    'issue' => [
        'correct_answer_in_verb_hint' => 'Correct answer is shown in verb_hint',
        'missing_verb_hint' => 'Missing verb_hint',
        'wrong_accepted_answer' => 'Wrong accepted answer',
        'wrong_or_extra_option' => 'Wrong or extra answer option',
        'multiple_valid_answers_only_one_accepted' => 'Multiple answers are valid, but only one is accepted',
        'typo_or_bad_translation' => 'Typo or bad translation',
        'wrong_topic_or_level' => 'Question does not match the topic or level',
        'other' => 'Other',
    ],
    'issue_description' => [
        'correct_answer_in_verb_hint' => 'verb_hint should guide the learner, not reveal the accepted answer.',
        'missing_verb_hint' => 'Without verb_hint, more than one option may be grammatically possible.',
        'wrong_accepted_answer' => 'The test accepts an incorrect answer or rejects a correct one.',
        'wrong_or_extra_option' => 'An option is wrong, duplicated, contradictory, or misleading.',
        'multiple_valid_answers_only_one_accepted' => 'Several options are grammatically valid, but accepted answers contain only one.',
        'typo_or_bad_translation' => 'There is an issue in the Ukrainian or English text, grammar, punctuation, or translation.',
        'wrong_topic_or_level' => 'The question does not fit the test, topic, or declared CEFR level.',
        'other' => 'For issues that do not match the other categories.',
    ],
    'comment_label' => 'Comment',
    'comment_optional' => 'Comment is optional when an issue type is selected',
    'other_requires_comment_hint' => 'Describe the problem in the comment',
    'validation' => [
        'issue_or_comment_required' => 'Choose at least one issue type or write a comment',
    ],
    'submit' => 'Send report',
    'success' => 'Report saved',
    'admin_badge' => 'Has report',
    'admin_panel_title' => 'Question report',
    'no_comment' => 'No comment',
    'no_issue_type' => 'Issue type not specified',
    'saving' => 'Saving...',
    'error' => 'Unable to save report.',
];
