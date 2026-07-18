<?php

declare(strict_types=1);

/**
 * Canonical Ukrainian infinitives for Present Perfect questions whose gap
 * contains a lexical verb. The compact level arrays follow the stable
 * 01-12 suffixes used by the JSON question banks.
 *
 * @return array<string, string>
 */
function presentPerfectUkrainianVerbs(): array
{
    static $verbs = null;

    if (is_array($verbs)) {
        return $verbs;
    }

    $families = [
        'forms' => [
            'a1' => ['завершити', 'жити', 'прибрати', 'працювати', 'бувати', 'загубити', 'подорожувати', 'знайти', 'переглянути', 'купити', 'написати', 'закінчити їсти'],
            'a2' => ['їздити верхи', 'відвідувати', 'завершити заповнення', 'працювати', 'бачити', 'загубити', 'відвідати', 'знайти', 'переглянути', 'початися', 'забронювати', 'приготувати'],
            'b1' => ['знати', 'підвищувати', 'полагодити', 'завершити', 'бачити', 'забути', 'жити', 'відкрити', 'отримати', 'принести', 'зрости', 'спрацювати'],
            'b2' => ['виявити', 'досягти цілей', 'коштувати', 'працювати', 'доводитися', 'пошкодити', 'зіткнутися', 'оприлюднити', 'отримати', 'знизитися', 'відповісти', 'домовитися щодо умов'],
            'c1' => ['поставити під сумнів', 'розглянути', 'продемонструвати', 'залишатися', 'розглянути апеляцію', 'стабілізуватися', 'входити до складу комісії', 'узгодити', 'розкрити', 'показати', 'мати', 'сформувати'],
            'c2' => ['виявитися', 'стати', 'пояснити', 'отримати', 'перебувати під тиском', 'представляти як', 'здобути', 'ухвалити', 'підірвати', 'запропонувати', 'зірватися', 'спричинити'],
        ],
        'neg' => [
            'a1' => ['завершити', 'з’їсти', 'спакувати', 'бачити', 'надіслати', 'знайти', 'купити', 'прочитати', 'виконати', 'скуштувати', 'отримати', 'піти'],
            'a2' => ['завершити заповнення', 'прибрати', 'знайти', 'подорожувати', 'надіслати', 'прочитати', 'забронювати', 'переглянути', 'їхати цим маршрутом', 'водити', 'отримати', 'вийти з офісу'],
            'b1' => ['завершити', 'полагодити', 'ухвалити рішення', 'зіткнутися', 'відповісти', 'отримати', 'завантажити', 'перевірити', 'опрацювати', 'мати', 'отримати', 'затвердити'],
            'b2' => ['досягти цілей', 'усунути несправність', 'досягти', 'зіткнутися', 'подати', 'перевірити', 'відкинути', 'затвердити', 'зіткнутися', 'оприлюднити', 'оприлюднити', 'надати'],
            'c1' => ['розглянути', 'пояснити', 'відтворити', 'зафіксувати', 'розкрити', 'підтвердити', 'відкинути', 'затвердити', 'спостерігати', 'піддати', 'розв’язати', 'рецензувати'],
            'c2' => ['встановити', 'оцінити кількісно', 'надати', 'спричинити', 'розкрити', 'обґрунтувати', 'відкинути', 'схвалити', 'зіткнутися', 'перевірити', 'узгодити', 'винести рішення'],
        ],
        'q' => [
            'a1' => ['завершити', 'прочитати', 'зробити', 'бувати', 'надіслати', 'бачити', 'зрозуміти', 'відкрити', 'покласти', 'бачити', 'знайти', 'знайти'],
            'a2' => ['виконати', 'прочитати', 'пофарбувати', 'чути', 'надіслати', 'прибути', 'приготувати', 'спекти', 'залишити', 'їхати цим маршрутом', 'зателефонувати', 'увімкнути'],
            'b1' => ['завершити', 'переглянути', 'завантажити', 'працювати', 'відповісти', 'підтвердити', 'виявити', 'підписати', 'зберегти', 'зафіксувати', 'знайти', 'надіслати'],
            'b2' => ['остаточно підготувати', 'перевірити', 'завершити', 'аналізувати', 'надіслати', 'висловити', 'подати дані', 'підписати', 'зберегти', 'спостерігати', 'отримати', 'розгорнути'],
            'c1' => ['додати', 'вивчити', 'поставити під сумнів', 'зустрічати', 'надати', 'усунути', 'надати', 'виправити', 'перемістити', 'визнавати', 'прийняти', 'перевірити'],
            'c2' => ['змінити', 'розглянути', 'спричинити', 'застосувати', 'оприлюднити', 'підірвати', 'усунути', 'переглянути', 'задокументувати', 'надати', 'ухвалити', 'розкрити'],
        ],
    ];

    $verbs = [];
    foreach ($families as $family => $levels) {
        foreach ($levels as $level => $levelVerbs) {
            if (count($levelVerbs) !== 12) {
                throw new RuntimeException("Expected 12 {$family}/{$level} verbs.");
            }

            foreach ($levelVerbs as $index => $verb) {
                $uuid = sprintf('present-perfect-%s-v3-%s-%02d', $family, $level, $index + 1);
                $verbs[$uuid] = $verb;
            }
        }
    }

    $timeExpressionVerbs = [
        'present-perfect-time-v3-a1-10' => 'вивчити',
        'present-perfect-time-v3-a1-12' => 'загубити',
        'present-perfect-time-v3-a2-10' => 'прочитати',
        'present-perfect-time-v3-a2-12' => 'відвідати',
        'present-perfect-time-v3-b1-10' => 'відвідати',
        'present-perfect-time-v3-b1-12' => 'зустріти',
        'present-perfect-time-v3-b2-10' => 'опитати',
        'present-perfect-time-v3-b2-12' => 'відкликати',
        'present-perfect-time-v3-c1-10' => 'знайти',
        'present-perfect-time-v3-c1-12' => 'оголосити судове рішення',
        'present-perfect-time-v3-c2-10' => 'дійти висновків',
        'present-perfect-time-v3-c2-12' => 'скасувати',
    ];

    return $verbs = array_merge($verbs, $timeExpressionVerbs);
}

function presentPerfectUkrainianVerb(string $uuid): ?string
{
    return presentPerfectUkrainianVerbs()[strtolower($uuid)] ?? null;
}

function presentPerfectVerbHint(string $uuid, string $context): string
{
    $verb = presentPerfectUkrainianVerb($uuid);
    if ($verb === null) {
        return $context;
    }

    $context = trim((string) preg_replace('/^Дієслово: «[^»]+»\.\s*/u', '', trim($context)));
    $prefix = "Дієслово: «{$verb}».";

    return $context === '' ? $prefix : $prefix.' '.$context;
}

/**
 * Sentence Builder hints belong next to the actual V3 token and contain only
 * the Ukrainian infinitive; the view already supplies parentheses and styling.
 *
 * @return array{marker: string, verb: string}|null
 */
function presentPerfectQuestionPolyglotVerbHint(string $uuid): ?array
{
    if (! preg_match('/^present-perfect-q-poly-(a1|a2|b1|b2|c1|c2)-(\d{2})$/', strtolower($uuid), $matches)) {
        return null;
    }

    $markers = [
        'a1' => [3, 3, 4, 4, 3, null, 3, 3, 4, 4, 4, 3],
        'a2' => [3, 3, 4, 4, 3, null, 3, 3, 4, 4, 4, 3],
        'b1' => [3, 3, 5, 4, 3, null, 5, 3, 4, 4, 4, 3],
        'b2' => [3, 3, 6, 4, 3, null, 4, 3, 4, 4, 5, 3],
        'c1' => [3, 3, 6, 4, 3, null, 4, 4, 5, 5, 5, 3],
        'c2' => [6, 4, 3, 6, 4, null, 5, 4, 5, 5, 5, 4],
    ];

    $level = $matches[1];
    $number = (int) $matches[2];
    $marker = $markers[$level][$number - 1] ?? null;
    if (! is_int($marker)) {
        return null;
    }

    $standardUuid = sprintf('present-perfect-q-v3-%s-%02d', $level, $number);
    $verb = presentPerfectUkrainianVerb($standardUuid);
    if ($verb === null) {
        throw new RuntimeException("Missing Present Perfect verb for {$uuid}.");
    }

    return ['marker' => 'a'.$marker, 'verb' => $verb];
}
