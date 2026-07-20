<?php

declare(strict_types=1);

/**
 * Synchronize the four Past Perfect Sentence Builder banks with their standard
 * counterparts while preserving Polyglot UUIDs, order, source, and tags.
 *
 * Safe to run repeatedly after a standard Past Perfect bank is updated.
 */

$root = dirname(__DIR__);
$prompts = require __DIR__.'/lib/past_perfect_polyglot_prompts.php';
$timeVerbs = require __DIR__.'/lib/past_perfect_polyglot_time_verbs.php';
$requestedBanks = array_slice($argv, 1);

$definitions = [
    'forms' => [
        'standard' => $root.'/database/seeders/V3/Tenses/PastPerfect/PastPerfectFormsAllLevelsV3Seeder/definition.json',
        'polyglot' => $root.'/database/seeders/V3/Polyglot/PolyglotPastPerfectFormsAllLevelsLessonSeeder/definition.json',
    ],
    'negatives' => [
        'standard' => $root.'/database/seeders/V3/Tenses/PastPerfect/PastPerfectNegativesAllLevelsV3Seeder/definition.json',
        'polyglot' => $root.'/database/seeders/V3/Polyglot/PolyglotPastPerfectNegativesAllLevelsLessonSeeder/definition.json',
    ],
    'questions' => [
        'standard' => $root.'/database/seeders/V3/Tenses/PastPerfect/PastPerfectQuestionsAllLevelsV3Seeder/definition.json',
        'polyglot' => $root.'/database/seeders/V3/Polyglot/PolyglotPastPerfectQuestionsAllLevelsLessonSeeder/definition.json',
    ],
    'time-expressions' => [
        'standard' => $root.'/database/seeders/V3/Tenses/PastPerfect/PastPerfectTimeExpressionsAllLevelsV3Seeder/definition.json',
        'polyglot' => $root.'/database/seeders/V3/Polyglot/PolyglotPastPerfectTimeExpressionsAllLevelsLessonSeeder/definition.json',
    ],
];

/** @return array<string, mixed> */
function ppPolyglotReadJson(string $path): array
{
    return json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
}

/** @param array<string, mixed> $data */
function ppPolyglotWriteJson(string $path, array $data): void
{
    file_put_contents(
        $path,
        json_encode(
            $data,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR
        ).PHP_EOL
    );
}

/** @return list<string> */
function ppPolyglotTokens(string $sentence): array
{
    $withoutPunctuation = preg_replace('/[.,!?;:]+/u', '', $sentence);

    return array_values(array_filter(
        preg_split('/\s+/u', trim((string) $withoutPunctuation)) ?: [],
        static fn (string $token): bool => $token !== ''
    ));
}

/** @return list<string> */
function ppPolyglotShortAnswerTokens(string $answer): array
{
    return array_values(array_filter(
        preg_split('/\s+/u', trim($answer)) ?: [],
        static fn (string $token): bool => $token !== ''
    ));
}

/** @param array<string, mixed> $standardQuestion */
function ppPolyglotIsShortAnswer(array $standardQuestion): bool
{
    return in_array('short_answers', $standardQuestion['tag_keys'] ?? [], true);
}

function ppPolyglotCounterpartKey(string $uuid): string
{
    preg_match('/-([a-c][12])-([0-9]{2})$/', $uuid, $matches);

    if (! isset($matches[1], $matches[2])) {
        throw new RuntimeException("Cannot derive level/index key from {$uuid}.");
    }

    return $matches[1].'-'.$matches[2];
}

function ppPolyglotUkrainianVerb(string $hint, string $uuid): ?string
{
    if (preg_match('/^Дієслово: «([^»]+)»\./u', $hint, $matches) !== 1) {
        return null;
    }

    return $matches[1];
}

function ppPolyglotGapVerbToken(string $gapAnswer, string $uuid): string
{
    $tokens = ppPolyglotTokens($gapAnswer);
    $skip = ['not', 'already', 'just', 'never', 'still', 'yet'];

    foreach ($tokens as $index => $token) {
        if (! in_array(mb_strtolower($token), ['had', "hadn't"], true)) {
            continue;
        }

        for ($candidate = $index + 1; $candidate < count($tokens); $candidate++) {
            if (! in_array(mb_strtolower($tokens[$candidate]), $skip, true)) {
                return $tokens[$candidate];
            }
        }
    }

    throw new RuntimeException("Cannot locate the Past Perfect lexical verb in {$uuid}.");
}

function ppPolyglotQuestionVerbToken(string $gapAnswer, string $uuid): string
{
    $tokens = ppPolyglotTokens($gapAnswer);
    if ($tokens === []) {
        throw new RuntimeException("Cannot locate the question verb in {$uuid}.");
    }

    $particles = ['aside', 'away', 'back', 'down', 'in', 'off', 'on', 'out', 'over', 'through', 'up'];
    $index = count($tokens) - 1;
    if ($index > 0 && in_array(mb_strtolower($tokens[$index]), $particles, true)) {
        $index--;
    }

    return $tokens[$index];
}

/** @param list<string> $answerTokens */
function ppPolyglotCompletedVerbToken(array $answerTokens, string $uuid): string
{
    $skip = ['not', 'already', 'just', 'never', 'still', 'yet'];
    foreach ($answerTokens as $index => $token) {
        if (! in_array(mb_strtolower($token), ['had', "hadn't"], true)) {
            continue;
        }

        for ($candidate = $index + 1; $candidate < count($answerTokens); $candidate++) {
            if (! in_array(mb_strtolower($answerTokens[$candidate]), $skip, true)) {
                return $answerTokens[$candidate];
            }
        }
    }

    throw new RuntimeException("Cannot locate a completed Past Perfect lexical verb in {$uuid}.");
}

/**
 * @param list<string> $answerTokens
 * @return array<string, string>
 */
function ppPolyglotVerbHints(
    string $bank,
    array $standardQuestion,
    array $answerTokens,
    ?string $fallbackVerb = null
): array
{
    $uuid = (string) $standardQuestion['uuid'];
    $marker = $standardQuestion['markers']['a1'];
    $verb = ppPolyglotUkrainianVerb((string) $marker['verb_hint'], $uuid) ?? $fallbackVerb;
    if ($verb === null) {
        if ($bank === 'questions') {
            return [];
        }

        throw new RuntimeException("Standard hint does not name a Ukrainian verb in {$uuid}.");
    }

    $verbToken = match ($bank) {
        'questions' => ppPolyglotQuestionVerbToken((string) $marker['answer'], $uuid),
        'time-expressions' => ppPolyglotCompletedVerbToken($answerTokens, $uuid),
        default => ppPolyglotGapVerbToken((string) $marker['answer'], $uuid),
    };
    foreach ($answerTokens as $index => $token) {
        if (mb_strtolower($token) === mb_strtolower($verbToken)) {
            return ['a'.($index + 1) => $verb];
        }
    }

    throw new RuntimeException("Lexical token {$verbToken} is absent from the completed target in {$uuid}.");
}

/**
 * @param list<string> $answerTokens
 * @return list<string>
 */
function ppPolyglotOptions(array $standardQuestion, array $answerTokens): array
{
    $correctLookup = array_fill_keys(array_map('mb_strtolower', $answerTokens), true);
    $distractors = [];
    $seen = [];

    $append = static function (string $token) use (&$distractors, &$seen, $correctLookup): void {
        $value = trim($token);
        $key = mb_strtolower($value);
        if ($value === '' || preg_match('/\s/u', $value) || isset($correctLookup[$key]) || isset($seen[$key])) {
            return;
        }

        $seen[$key] = true;
        $distractors[] = $value;
    };

    foreach (array_slice($standardQuestion['markers']['a1']['options'], 1) as $option) {
        $optionTokens = ppPolyglotIsShortAnswer($standardQuestion)
            ? ppPolyglotShortAnswerTokens((string) $option)
            : ppPolyglotTokens((string) $option);
        foreach ($optionTokens as $token) {
            $append($token);
        }
    }

    foreach (['have', 'has', 'did', "didn't", 'was', 'were', 'not', 'already', 'yet', 'after', 'before', 'went', 'seen', 'done'] as $token) {
        $append($token);
    }

    if (count($distractors) < 4) {
        throw new RuntimeException('Could not build four atomic distractors for '.$standardQuestion['uuid'].'.');
    }

    return array_merge($answerTokens, array_slice($distractors, 0, 6));
}

foreach ($prompts as $bank => $promptsByLevel) {
    if ($requestedBanks !== [] && ! in_array($bank, $requestedBanks, true)) {
        continue;
    }

    if (! isset($definitions[$bank])) {
        throw new RuntimeException("Unknown Past Perfect bank {$bank}.");
    }

    foreach (['A1', 'A2', 'B1', 'B2', 'C1', 'C2'] as $level) {
        if (count($promptsByLevel[$level] ?? []) !== 12) {
            throw new RuntimeException("Expected 12 {$level} prompts for {$bank}.");
        }
        if ($bank === 'time-expressions' && count($timeVerbs[$level] ?? []) !== 12) {
            throw new RuntimeException("Expected 12 {$level} time-expression verb hints.");
        }
    }

    $standard = ppPolyglotReadJson($definitions[$bank]['standard']);
    $polyglot = ppPolyglotReadJson($definitions[$bank]['polyglot']);
    if (count($standard['questions'] ?? []) !== 72 || count($polyglot['questions'] ?? []) !== 72) {
        throw new RuntimeException("Expected 72 standard and Polyglot questions for {$bank}.");
    }

    $standardByKey = [];
    foreach ($standard['questions'] as $standardQuestion) {
        $standardByKey[ppPolyglotCounterpartKey((string) $standardQuestion['uuid'])] = $standardQuestion;
    }

    foreach ($polyglot['questions'] as &$question) {
        $uuid = (string) $question['uuid'];
        $key = ppPolyglotCounterpartKey($uuid);
        $standardQuestion = $standardByKey[$key] ?? null;
        if (! is_array($standardQuestion)) {
            throw new RuntimeException("Missing standard counterpart for {$uuid}.");
        }

        $level = (string) $question['level'];
        $number = ((int) substr($key, -2)) - 1;
        $prompt = $promptsByLevel[$level][$number] ?? null;
        if (! is_string($prompt) || ! preg_match('/[.!?]$/u', $prompt)) {
            throw new RuntimeException("Missing punctuated Ukrainian prompt for {$uuid}.");
        }

        if ($bank === 'questions' && ppPolyglotIsShortAnswer($standardQuestion)) {
            $answerTokens = ppPolyglotShortAnswerTokens(
                (string) $standardQuestion['markers']['a1']['answer']
            );
        } else {
            $completed = str_replace(
                '{a1}',
                (string) $standardQuestion['markers']['a1']['answer'],
                (string) $standardQuestion['question']
            );
            $answerTokens = ppPolyglotTokens($completed);
        }
        $answers = [];
        foreach ($answerTokens as $index => $token) {
            $answers['a'.($index + 1)] = $token;
        }

        $question['question'] = $prompt;
        $question['answers'] = $answers;
        $question['options'] = ppPolyglotOptions($standardQuestion, $answerTokens);
        $question['variants'] = [];
        unset($question['verb_hints']);
        $fallbackVerb = $bank === 'time-expressions' ? ($timeVerbs[$level][$number] ?? null) : null;
        $verbHints = ppPolyglotVerbHints($bank, $standardQuestion, $answerTokens, $fallbackVerb);
        if ($verbHints !== []) {
            $question['verb_hints'] = $verbHints;
        }
    }
    unset($question);

    ppPolyglotWriteJson($definitions[$bank]['polyglot'], $polyglot);
    echo "Updated 72 Past Perfect {$bank} Sentence Builder questions.".PHP_EOL;
}
