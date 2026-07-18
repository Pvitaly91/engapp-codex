<?php

declare(strict_types=1);

/**
 * Add explicit Ukrainian lexical verbs to every Present Perfect verb gap and
 * normalize Sentence Builder hints. Deterministic and safe to run repeatedly.
 */

require_once __DIR__.'/lib/present_perfect_verb_hints.php';

$root = dirname(__DIR__);

/** @return array<string, mixed> */
function ppVerbHintsRead(string $path): array
{
    return json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
}

/** @param array<string, mixed> $data */
function ppVerbHintsWrite(string $path, array $data): void
{
    file_put_contents(
        $path,
        json_encode(
            $data,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR
        ).PHP_EOL
    );
}

$standardDefinitions = [
    'forms' => [
        'path' => $root.'/database/seeders/V3/Tenses/PresentPerfect/PresentPerfectFormsAllLevelsV3Seeder/definition.json',
        'expected' => 72,
    ],
    'negatives' => [
        'path' => $root.'/database/seeders/V3/Tenses/PresentPerfect/PresentPerfectNegativesAllLevelsV3Seeder/definition.json',
        'expected' => 72,
    ],
    'questions' => [
        'path' => $root.'/database/seeders/V3/Tenses/PresentPerfect/PresentPerfectQuestionsAllLevelsV3Seeder/definition.json',
        'expected' => 72,
    ],
    'time expressions' => [
        'path' => $root.'/database/seeders/V3/Tenses/PresentPerfect/PresentPerfectTimeExpressionsAllLevelsV3Seeder/definition.json',
        'expected' => 12,
    ],
];

foreach ($standardDefinitions as $label => $definition) {
    $data = ppVerbHintsRead($definition['path']);
    $updated = 0;

    foreach ($data['questions'] as &$question) {
        $uuid = (string) ($question['uuid'] ?? '');
        if (presentPerfectUkrainianVerb($uuid) === null) {
            continue;
        }

        if (! isset($question['markers']['a1']) || ! is_array($question['markers']['a1'])) {
            throw new RuntimeException("Missing a1 marker in {$uuid}.");
        }

        $currentHint = (string) ($question['markers']['a1']['verb_hint'] ?? '');
        $question['markers']['a1']['verb_hint'] = presentPerfectVerbHint($uuid, $currentHint);
        $updated++;
    }
    unset($question);

    if ($updated !== $definition['expected']) {
        throw new RuntimeException("Expected {$definition['expected']} {$label} hints, updated {$updated}.");
    }

    ppVerbHintsWrite($definition['path'], $data);
    echo "Updated {$updated} Present Perfect {$label} hints.".PHP_EOL;
}

$questionsPath = $root.'/database/seeders/V3/Polyglot/PolyglotPresentPerfectQuestionsAllLevelsLessonSeeder/definition.json';
$questions = ppVerbHintsRead($questionsPath);
$polyglotHints = 0;

foreach ($questions['questions'] as &$question) {
    $uuid = (string) ($question['uuid'] ?? '');
    unset($question['verb_hints']);

    $verbHint = presentPerfectQuestionPolyglotVerbHint($uuid);
    if ($verbHint !== null) {
        $question['verb_hints'] = [$verbHint['marker'] => $verbHint['verb']];
        $polyglotHints++;
    }

    if ($uuid === 'present-perfect-q-poly-a2-05') {
        $question['question'] = 'Даніель уже надіслав посилку, на яку ми досі чекаємо?';
    } elseif ($uuid === 'present-perfect-q-poly-b1-07') {
        $question['question'] = 'Інші команди виявили таку закономірність у своїх даних?';
    }
}
unset($question);

if ($polyglotHints !== 66) {
    throw new RuntimeException("Expected 66 Present Perfect Sentence Builder hints, updated {$polyglotHints}.");
}

ppVerbHintsWrite($questionsPath, $questions);
echo "Updated {$polyglotHints} Present Perfect Sentence Builder verb hints.".PHP_EOL;
