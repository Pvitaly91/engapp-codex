<?php

declare(strict_types=1);

/**
 * Subjects that are hidden inside the answer gap in Past Perfect questions.
 * The English fragment is used only to validate the authored answer; learners
 * see the Ukrainian subject in the verb hint.
 *
 * @return array<string, array{answer: string, hint: string}>
 */
function pastPerfectHiddenSubjects(): array
{
    static $subjects = null;

    if (is_array($subjects)) {
        return $subjects;
    }

    $levels = [
        'a1' => [
            ['you', 'ти / ви'], ['Mia', 'Мія'], ['Leo', 'Лео'], ['Sam', 'Сем'],
            ['they', 'вони'], ['Ella', 'Елла'], ['the train', 'потяг'], null,
            ['the teacher', 'вчитель'], null, null, ['Lucy', 'Люсі'],
        ],
        'a2' => [
            ['you', 'ти / ви'], ['Nora', 'Нора'], ['the driver', 'водій'], ['Amir', 'Амір'],
            ['the students', 'учні'], ['Leo', 'Лео'], ['the film', 'фільм'], null,
            ['Mia', 'Мія'], null, ['Dad', 'тато'], ['Ben', 'Бен'],
        ],
        'b1' => [
            ['you', 'ти / ви'], ['the technician', 'технік'], ['the reporter', 'журналіст'], ['the administrator', 'адміністратор'],
            ['the engineers', 'інженери'], ['Rosa', 'Роза'], ['the passengers', 'пасажири'], null,
            ['the team', 'команда'], null, ['the coordinator', 'координатор'], ['Daniel', 'Даніель'],
        ],
        'b2' => [
            ['you', 'ти / ви'], ['the analyst', 'аналітик'], ['the researchers', 'дослідники'], ['the director', 'директор'],
            ['the department', 'відділ'], ['the chair', 'голова'], ['the author', 'автор'], null,
            ['the team', 'команда'], null, ['the consultant', 'консультант'], ['the delegates', 'делегати'],
        ],
        'c1' => [
            ['counsel', 'юрисконсульт'], ['the policy team', 'команда з питань політики'], ['the committee', 'комітет'], ['the editor', 'редактор'],
            ['the panel', 'колегія'], ['the agency', 'агентство'], ['the researchers', 'дослідники'], null,
            ['each side', 'кожна сторона'], null, ['the ministry', 'міністерство'], ['the agency', 'агентство'],
        ],
        'c2' => [
            ['the tribunal', 'трибунал'], ['the task force', 'робоча група'], ['the authors', 'автори'], ['the claimant', 'заявник'],
            ['the board', 'рада директорів'], ['the panel', 'колегія'], ['the investigators', 'слідчі'], null,
            ['each delegate', 'кожен делегат'], null, ['the analysts', 'аналітики'], ['counsel', 'юрисконсульт'],
        ],
    ];

    $subjects = [];
    foreach ($levels as $level => $levelSubjects) {
        if (count($levelSubjects) !== 12) {
            throw new RuntimeException("Expected 12 Past Perfect question subjects for {$level}.");
        }

        foreach ($levelSubjects as $index => $subject) {
            if ($subject === null) {
                continue;
            }

            $uuid = sprintf('pp-questions-v3-%s-%02d', $level, $index + 1);
            $subjects[$uuid] = ['answer' => $subject[0], 'hint' => $subject[1]];
        }
    }

    return $subjects;
}

function pastPerfectHiddenSubject(string $uuid): ?string
{
    return pastPerfectHiddenSubjects()[strtolower($uuid)]['hint'] ?? null;
}

function pastPerfectHiddenAnswerSubject(string $uuid): ?string
{
    return pastPerfectHiddenSubjects()[strtolower($uuid)]['answer'] ?? null;
}

/**
 * Insert the Ukrainian subject immediately after the lexical-verb prefix.
 * The transformation is deterministic and safe to run repeatedly.
 */
function pastPerfectVerbHint(string $uuid, string $context): string
{
    $subject = pastPerfectHiddenSubject($uuid);
    if ($subject === null) {
        return $context;
    }

    $context = trim((string) preg_replace(
        '/\s*Підмет у запитанні — «[^»]+»\.\s*/u',
        ' ',
        trim($context)
    ));
    $subjectHint = "Підмет у запитанні — «{$subject}».";

    if (preg_match('/^(Дієслово: «[^»]+»\.)(?:\s+(.*))?$/u', $context, $matches) === 1) {
        $tail = trim((string) ($matches[2] ?? ''));

        return $tail === ''
            ? $matches[1].' '.$subjectHint
            : $matches[1].' '.$subjectHint.' '.$tail;
    }

    return $context === '' ? $subjectHint : $subjectHint.' '.$context;
}
