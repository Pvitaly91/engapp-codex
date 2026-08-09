<?php

declare(strict_types=1);

/**
 * Add explicit Ukrainian subjects to Past Perfect question hints whose gap
 * contains the subject. Deterministic and safe to run repeatedly.
 */

require_once __DIR__.'/lib/past_perfect_verb_hints.php';

$path = dirname(__DIR__).'/database/seeders/V3/Tenses/PastPerfect/PastPerfectQuestionsAllLevelsV3Seeder/definition.json';
$data = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
$updated = 0;

foreach ($data['questions'] as &$question) {
    $uuid = (string) ($question['uuid'] ?? '');
    if (pastPerfectHiddenSubject($uuid) === null) {
        continue;
    }

    if (! isset($question['markers']['a1']) || ! is_array($question['markers']['a1'])) {
        throw new RuntimeException("Missing a1 marker in {$uuid}.");
    }

    $currentHint = (string) ($question['markers']['a1']['verb_hint'] ?? '');
    $question['markers']['a1']['verb_hint'] = pastPerfectVerbHint($uuid, $currentHint);
    $updated++;
}
unset($question);

if ($updated !== 59) {
    throw new RuntimeException("Expected 59 Past Perfect subject hints, updated {$updated}.");
}

file_put_contents(
    $path,
    json_encode(
        $data,
        JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR
    ).PHP_EOL
);

echo "Updated {$updated} Past Perfect subject hints.".PHP_EOL;
