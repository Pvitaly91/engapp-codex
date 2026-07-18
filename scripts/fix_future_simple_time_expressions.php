<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$directory = $root.'/database/seeders/V3/FutureForms/FutureSimple/FutureSimpleTimeExpressionsAllLevelsV3Seeder';

$timeExpressions = [
    'fs-time-v3-a1-07' => 'on Monday',
    'fs-time-v3-a1-08' => 'next month',
    'fs-time-v3-a1-09' => 'one day',
    'fs-time-v3-a1-10' => 'this evening',
    'fs-time-v3-a1-11' => 'in two days',
    'fs-time-v3-a1-12' => 'next year',
    'fs-time-v3-a2-07' => 'on Friday',
    'fs-time-v3-a2-08' => 'next summer',
    'fs-time-v3-a2-09' => 'in three weeks',
    'fs-time-v3-a2-10' => 'this afternoon',
    'fs-time-v3-a2-11' => 'before long',
    'fs-time-v3-a2-12' => 'next term',
    'fs-time-v3-b1-07' => 'on the following day',
    'fs-time-v3-b1-08' => 'next academic year',
    'fs-time-v3-b1-09' => 'in several months',
    'fs-time-v3-b1-10' => 'towards the evening',
    'fs-time-v3-b1-11' => 'eventually',
    'fs-time-v3-b1-12' => 'over the coming year',
    'fs-time-v3-b2-07' => 'at the next meeting',
    'fs-time-v3-b2-08' => 'over the next decade',
    'fs-time-v3-b2-09' => 'in due course',
    'fs-time-v3-b2-10' => 'towards the end of July',
    'fs-time-v3-b2-11' => 'sooner or later',
    'fs-time-v3-b2-12' => 'throughout the coming cycle',
    'fs-time-v3-c1-07' => 'at the subsequent hearing',
    'fs-time-v3-c1-08' => 'over the coming decades',
    'fs-time-v3-c1-09' => 'in due time',
    'fs-time-v3-c1-10' => 'towards the close of negotiations',
    'fs-time-v3-c1-11' => 'ultimately',
    'fs-time-v3-c1-12' => 'throughout the next reporting period',
    'fs-time-v3-c2-07' => 'at the subsequent plenary session',
    'fs-time-v3-c2-08' => 'over generations to come',
    'fs-time-v3-c2-09' => 'in the fullness of time',
    'fs-time-v3-c2-10' => 'towards the culmination of the inquiry',
    'fs-time-v3-c2-11' => 'eventually',
    'fs-time-v3-c2-12' => 'throughout the forthcoming constitutional cycle',
];

$ukTranslations = [
    'tomorrow' => 'завтра', 'tonight' => 'сьогодні ввечері', 'next week' => 'наступного тижня',
    'soon' => 'скоро', 'later' => 'пізніше', 'in an hour' => 'за годину',
    'on Monday' => 'у понеділок', 'next month' => 'наступного місяця', 'one day' => 'одного дня',
    'this evening' => 'сьогодні ввечері', 'in two days' => 'через два дні', 'next year' => 'наступного року',
    'tomorrow morning' => 'завтра вранці', 'later today' => 'пізніше сьогодні', 'next weekend' => 'наступних вихідних',
    'very soon' => 'дуже скоро', 'in a few minutes' => 'за кілька хвилин', 'by then' => 'до того часу',
    'on Friday' => 'у п’ятницю', 'next summer' => 'наступного літа', 'in three weeks' => 'через три тижні',
    'this afternoon' => 'сьогодні вдень', 'before long' => 'незабаром', 'next term' => 'наступного семестру',
    'by tomorrow' => 'до завтра', 'later this week' => 'пізніше цього тижня', 'next quarter' => 'наступного кварталу',
    'in the near future' => 'у найближчому майбутньому', 'within an hour' => 'протягом години', 'by the deadline' => 'до кінцевого терміну',
    'on the following day' => 'наступного дня', 'next academic year' => 'наступного навчального року', 'in several months' => 'через кілька місяців',
    'towards the evening' => 'ближче до вечора', 'eventually' => 'зрештою', 'over the coming year' => 'протягом наступного року',
    'by next Monday' => 'до наступного понеділка', 'later in the process' => 'пізніше в процесі', 'during the next phase' => 'під час наступного етапу',
    'in the foreseeable future' => 'в осяжному майбутньому', 'within two working days' => 'протягом двох робочих днів', 'before the final review' => 'до фінального перегляду',
    'at the next meeting' => 'на наступній зустрічі', 'over the next decade' => 'протягом наступного десятиліття', 'in due course' => 'у належний час',
    'towards the end of July' => 'ближче до кінця липня', 'sooner or later' => 'рано чи пізно', 'throughout the coming cycle' => 'протягом наступного циклу',
    'by the end of the week' => 'до кінця тижня', 'at a later stage' => 'на пізнішому етапі', 'during the forthcoming session' => 'під час майбутньої сесії',
    'in the medium term' => 'у середньостроковій перспективі', 'within the stipulated period' => 'протягом установленого строку', 'before the mandate expires' => 'до завершення строку повноважень',
    'at the subsequent hearing' => 'на наступному слуханні', 'over the coming decades' => 'протягом наступних десятиліть', 'in due time' => 'свого часу',
    'towards the close of negotiations' => 'ближче до завершення переговорів', 'ultimately' => 'зрештою', 'throughout the next reporting period' => 'протягом наступного звітного періоду',
    'by the conclusion of proceedings' => 'до завершення провадження', 'at some later juncture' => 'на якомусь пізнішому етапі', 'during the ensuing deliberations' => 'під час подальших обговорень',
    'in the longer term' => 'у довгостроковій перспективі', 'within the prescribed timeframe' => 'у встановлені строки', 'before the convention reconvenes' => 'до повторного скликання конвенції',
    'at the subsequent plenary session' => 'на наступному пленарному засіданні', 'over generations to come' => 'протягом наступних поколінь', 'in the fullness of time' => 'з плином часу',
    'towards the culmination of the inquiry' => 'ближче до завершення розслідування', 'throughout the forthcoming constitutional cycle' => 'протягом майбутнього конституційного циклу',
];

function readJson(string $path): array
{
    return json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
}

function writeJson(string $path, array $data): void
{
    file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR).PHP_EOL);
}

$definitionPath = $directory.'/definition.json';
$definition = readJson($definitionPath);
$pools = [];

foreach ($definition['questions'] as &$question) {
    $uuid = (string) $question['uuid'];
    $marker =& $question['markers']['a1'];
    $level = (string) $question['level'];

    if (isset($timeExpressions[$uuid])) {
        $timeExpression = $timeExpressions[$uuid];
        $completeVerb = (string) $marker['answer'];
        $text = str_replace('{a1}', $completeVerb, (string) $question['question']);
        if (! str_contains($text, $timeExpression)) {
            throw new RuntimeException("Time expression not found in {$uuid}: {$timeExpression}");
        }
        $question['question'] = str_replace($timeExpression, '{a1}', $text);
        $question['variants'] = [$question['question']];
        $marker['answer'] = $timeExpression;
    }

    $pools[$level][] = (string) $marker['answer'];
}
unset($question, $marker);

foreach ($definition['questions'] as &$question) {
    $uuid = (string) $question['uuid'];
    $marker =& $question['markers']['a1'];
    $answer = (string) $marker['answer'];
    $translation = $ukTranslations[$answer] ?? throw new RuntimeException("Missing Ukrainian translation for {$uuid}: {$answer}");
    $marker['verb_hint'] = "Часовий вираз: «{$translation}»";

    $options = [$answer];
    $pool = array_values(array_unique($pools[(string) $question['level']]));
    $start = ((int) $question['id'] * 5) % count($pool);
    for ($offset = 0; count($options) < 4 && $offset < count($pool); $offset++) {
        $candidate = $pool[($start + $offset) % count($pool)];
        if (! in_array($candidate, $options, true)) {
            $options[] = $candidate;
        }
    }
    $marker['options'] = $options;
    $question['variants'] = [$question['question']];
}
unset($question, $marker);

writeJson($definitionPath, $definition);

$localizedHints = [];
foreach ($definition['questions'] as $question) {
    $answer = (string) $question['markers']['a1']['answer'];
    $translation = $ukTranslations[$answer];
    $localizedHints[(string) $question['uuid']] = [
        'uk' => "Часовий вираз: «{$translation}»",
        'en' => 'Time expression: “future time”',
        'pl' => 'Określenie czasu: „przyszłość”',
    ];
}

foreach (['uk', 'en', 'pl'] as $locale) {
    $path = $directory.'/localizations/'.$locale.'.json';
    $localization = readJson($path);
    foreach ($localization['questions'] as &$question) {
        $question['verb_hints']['a1'] = $localizedHints[(string) $question['uuid']][$locale];
    }
    unset($question);
    writeJson($path, $localization);
}

echo 'Future Simple time-expression questions updated: '.count($definition['questions']).PHP_EOL;
