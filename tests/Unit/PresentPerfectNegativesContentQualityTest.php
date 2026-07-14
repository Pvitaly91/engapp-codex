<?php

namespace Tests\Unit;

use App\Support\AcceptedAnswerVariants;
use App\Support\ComposeTokenCase;
use PHPUnit\Framework\TestCase;

class PresentPerfectNegativesContentQualityTest extends TestCase
{
    private const LEVELS = ['A1', 'A2', 'B1', 'B2', 'C1', 'C2'];

    private const STANDARD_DEFINITION =
        'database/seeders/V3/Tenses/PresentPerfect/PresentPerfectNegativesAllLevelsV3Seeder/definition.json';

    private const COMPOSE_DEFINITION =
        'database/seeders/V3/Polyglot/PolyglotPresentPerfectNegativesAllLevelsLessonSeeder/definition.json';

    private function rootPath(): string
    {
        return dirname(__DIR__, 2);
    }

    private function readJson(string $relativePath): array
    {
        return json_decode(
            (string) file_get_contents($this->rootPath().'/'.$relativePath),
            true,
            512,
            JSON_THROW_ON_ERROR
        );
    }

    /** @return list<string> */
    private function sentenceTokens(string $sentence): array
    {
        $withoutPunctuation = preg_replace('/[.,!?;:]+/u', '', $sentence);

        return array_values(array_filter(
            preg_split('/\s+/u', trim((string) $withoutPunctuation)) ?: [],
            static fn (string $token): bool => $token !== ''
        ));
    }

    /** @return list<string> */
    private function orderedAnswers(array $question): array
    {
        $answers = $question['answers'];
        uksort(
            $answers,
            static fn (string $left, string $right): int => ((int) substr($left, 1)) <=> ((int) substr($right, 1))
        );

        return array_values($answers);
    }

    private function questionKey(string $uuid): string
    {
        $matched = preg_match('/-([a-c][12])-([0-9]{2})$/', $uuid, $matches);
        $this->assertSame(1, $matched, "Unexpected question UUID: {$uuid}");

        return $matches[1].'-'.$matches[2];
    }

    /** @param array<int, array<string, mixed>> $questions */
    private function assertBalancedLevels(array $questions, string $bank): void
    {
        $this->assertCount(72, $questions, "{$bank} must contain 72 questions");

        $counts = array_count_values(array_column($questions, 'level'));
        ksort($counts);
        $this->assertSame(self::LEVELS, array_keys($counts), "{$bank} has unexpected levels");

        foreach (self::LEVELS as $level) {
            $this->assertSame(12, $counts[$level] ?? 0, "{$bank} must contain 12 {$level} questions");
        }
    }

    /** @param array<int, array<string, mixed>> $questions */
    private function averageTokenCount(array $questions, string $level, callable $value): float
    {
        $counts = [];

        foreach ($questions as $question) {
            if (($question['level'] ?? null) !== $level) {
                continue;
            }

            $counts[] = count($this->sentenceTokens((string) $value($question)));
        }

        $this->assertNotEmpty($counts, "No {$level} questions available for progression check");

        return array_sum($counts) / count($counts);
    }

    public function test_standard_bank_is_complete_unambiguous_and_contextual(): void
    {
        $definition = $this->readJson(self::STANDARD_DEFINITION);
        $questions = $definition['questions'];

        $this->assertBalancedLevels($questions, 'Standard bank');
        $this->assertCount(72, array_unique(array_column($questions, 'question')), 'Standard questions repeat.');

        $completedSentences = [];

        foreach ($questions as $question) {
            $uuid = (string) $question['uuid'];

            $this->assertSame(['a1'], array_keys($question['markers']), "{$uuid} must have exactly one marker");
            $this->assertSame(1, substr_count($question['question'], '{a1}'), "{$uuid} must contain {a1} once");
            $this->assertMatchesRegularExpression('/[.!?]$/u', $question['question'], "Missing punctuation in {$uuid}");
            $this->assertSame([$question['question']], $question['variants'], "Stale variant in {$uuid}");

            $marker = $question['markers']['a1'];
            $answer = trim((string) $marker['answer']);
            $options = array_map(static fn ($option): string => trim((string) $option), $marker['options']);

            $this->assertNotSame('', $answer, "Empty answer in {$uuid}");
            $this->assertCount(5, $options, "{$uuid} must have five options");
            $this->assertCount(5, array_unique($options), "Duplicate standard option in {$uuid}");
            $normalizedOptions = array_map(
                static fn (string $option): string => mb_strtolower(AcceptedAnswerVariants::normalizeTypography($option)),
                $options
            );
            $this->assertCount(5, array_unique($normalizedOptions), "Equivalent duplicate option in {$uuid}");
            $this->assertSame($answer, $options[0], "The authored correct option must be first in {$uuid}");
            $this->assertSame(1, array_count_values($options)[$answer] ?? 0, "Answer must occur once in {$uuid}");

            $acceptedAnswerVariants = array_map(
                static fn (string $variant): string => mb_strtolower($variant),
                AcceptedAnswerVariants::for($answer)
            );
            foreach (array_slice($normalizedOptions, 1) as $distractor) {
                $this->assertNotContains(
                    $distractor,
                    $acceptedAnswerVariants,
                    "Distractor duplicates an accepted full/contracted answer in {$uuid}"
                );
            }

            $hint = trim((string) ($marker['verb_hint'] ?? ''));
            $this->assertMatchesRegularExpression('/[А-Яа-яІіЇїЄєҐґ]/u', $hint, "Hint is not Ukrainian in {$uuid}");
            $this->assertStringNotContainsStringIgnoringCase($answer, $hint, "Hint reveals the answer in {$uuid}");
            $this->assertDoesNotMatchRegularExpression(
                '/\b(?:have|has)\s+not\b|\bhaven[\'’]t\b|\bhasn[\'’]t\b/iu',
                $hint,
                "Hint reveals the negative auxiliary in {$uuid}"
            );

            $answerTokens = $this->sentenceTokens($answer);
            $lexicalToken = (string) end($answerTokens);
            if (mb_strlen($lexicalToken) >= 4) {
                $this->assertStringNotContainsStringIgnoringCase(
                    $lexicalToken,
                    $hint,
                    "Hint reveals the target participle in {$uuid}"
                );
            }

            $completedSentences[] = str_replace('{a1}', $answer, $question['question']);
        }

        $this->assertCount(72, array_unique($completedSentences), 'Completed standard sentences repeat.');
    }

    public function test_standard_bank_has_real_level_progression(): void
    {
        $questions = $this->readJson(self::STANDARD_DEFINITION)['questions'];
        $answerText = static fn (array $question): string => $question['markers']['a1']['answer'];
        $completedText = static fn (array $question): string => str_replace(
            '{a1}',
            $question['markers']['a1']['answer'],
            $question['question']
        );

        $this->assertGreaterThan(
            $this->averageTokenCount($questions, 'A1', $answerText) + 0.5,
            $this->averageTokenCount($questions, 'C2', $answerText),
            'C2 gap answers are not structurally richer than A1 answers.'
        );
        $this->assertGreaterThan(
            $this->averageTokenCount($questions, 'A1', $completedText) + 2,
            $this->averageTokenCount($questions, 'C2', $completedText),
            'C2 sentences are not contextually richer than A1 sentences.'
        );

        $advancedContent = implode(' ', array_map(
            $completedText,
            array_values(array_filter(
                $questions,
                static fn (array $question): bool => in_array($question['level'], ['C1', 'C2'], true)
            ))
        ));

        $this->assertMatchesRegularExpression(
            '/\b(?:(?:has|have)\s+not|(?:hasn|haven)[\'’]t)\s+(?:\p{L}+\s+){0,2}been\s+(?:\p{L}+\s+){0,2}\p{L}+(?:ed|en)\b/iu',
            $advancedContent,
            'C1-C2 content does not exercise a Present Perfect negative passive.'
        );
        $this->assertMatchesRegularExpression(
            '/(?:^|[.!?]\s*)(?:never|rarely|seldom|not\s+(?:once|until|only)|at no time|under no circumstances)\b[^.!?]{0,100}\b(?:has|have)\b/iu',
            $advancedContent,
            'C1-C2 content does not exercise negative inversion.'
        );
    }

    public function test_sentence_builder_is_atomic_and_matches_standard_bank_exactly(): void
    {
        $standard = $this->readJson(self::STANDARD_DEFINITION);
        $compose = $this->readJson(self::COMPOSE_DEFINITION);

        $this->assertBalancedLevels($compose['questions'], 'Sentence Builder bank');
        $this->assertCount(
            72,
            array_unique(array_column($compose['questions'], 'question')),
            'Ukrainian Sentence Builder prompts repeat.'
        );
        $this->assertTrue(
            (bool) ($compose['saved_test']['filters']['supports_duplicate_tokens'] ?? false),
            'Sentence Builder must preserve repeated correct tokens.'
        );

        $standardByKey = [];
        foreach ($standard['questions'] as $question) {
            $key = $this->questionKey((string) $question['uuid']);
            $this->assertArrayNotHasKey($key, $standardByKey, "Duplicate standard pair key {$key}");
            $standardByKey[$key] = $question;
        }

        $composeSentences = [];
        $composeKeys = [];
        foreach ($compose['questions'] as $question) {
            $uuid = (string) $question['uuid'];
            $key = $this->questionKey($uuid);
            $standardQuestion = $standardByKey[$key] ?? null;

            $this->assertArrayNotHasKey($key, $composeKeys, "Duplicate Sentence Builder pair key {$key}");
            $composeKeys[$key] = true;
            $this->assertNotNull($standardQuestion, "No standard counterpart for {$uuid}");
            $this->assertSame(4, $question['type'], "Wrong compose type in {$uuid}");
            $this->assertMatchesRegularExpression('/[А-Яа-яІіЇїЄєҐґ]/u', $question['question'], "Prompt is not Ukrainian in {$uuid}");
            $this->assertMatchesRegularExpression('/[.!?]$/u', $question['question'], "Missing prompt punctuation in {$uuid}");

            $answers = $this->orderedAnswers($question);
            $this->assertNotEmpty($answers, "Missing correct tokens in {$uuid}");
            $this->assertSame($answers, ComposeTokenCase::normalize($answers), "Incorrect sentence case in {$uuid}");

            $completedStandard = str_replace(
                '{a1}',
                $standardQuestion['markers']['a1']['answer'],
                $standardQuestion['question']
            );
            $this->assertDoesNotMatchRegularExpression(
                '/[,;:]/u',
                $completedStandard,
                "Sentence Builder cannot preserve internal punctuation in {$uuid}"
            );
            $this->assertSame(
                $this->sentenceTokens($completedStandard),
                $answers,
                "Sentence Builder answer diverges from the standard sentence in {$uuid}"
            );

            $options = array_map(static fn ($option): string => trim((string) $option), $question['options']);
            foreach ($options as $option) {
                $this->assertNotSame('', $option, "Empty compose option in {$uuid}");
                $this->assertDoesNotMatchRegularExpression('/\s/u', $option, "Multiword compose tile in {$uuid}: {$option}");
            }

            $answerMultiplicity = array_count_values($answers);
            $optionMultiplicity = array_count_values($options);
            foreach ($answerMultiplicity as $token => $count) {
                $this->assertSame(
                    $count,
                    $optionMultiplicity[$token] ?? 0,
                    "Incorrect multiplicity for correct token {$token} in {$uuid}"
                );
            }

            $correctLookup = array_fill_keys(array_map('mb_strtolower', $answers), true);
            $distractors = array_values(array_filter(
                $options,
                static fn (string $option): bool => ! isset($correctLookup[mb_strtolower($option)])
            ));
            $normalizedDistractors = array_map('mb_strtolower', $distractors);
            $dangerousAlternativeAuxiliaries = [
                'do', 'does', 'did', "don't", "doesn't", "didn't",
                'am', 'is', 'are', 'was', 'were', "isn't", "aren't", "wasn't", "weren't",
                'had', "hadn't", 'will', "won't",
            ];

            $this->assertCount(
                count(array_unique($normalizedDistractors)),
                $normalizedDistractors,
                "Duplicate distractors in {$uuid}"
            );
            $this->assertGreaterThanOrEqual(4, count($distractors), "Too few distractors in {$uuid}");
            $this->assertLessThanOrEqual(8, count($distractors), "Too many distractors in {$uuid}");
            foreach ($normalizedDistractors as $distractor) {
                $this->assertNotContains(
                    $distractor,
                    $dangerousAlternativeAuxiliaries,
                    "Distractor enables another defensible tense in {$uuid}: {$distractor}"
                );
            }

            foreach (['not', 'never', 'ever'] as $negativeToken) {
                if (! isset($correctLookup[$negativeToken])) {
                    $this->assertNotContains(
                        $negativeToken,
                        $normalizedDistractors,
                        "Distractor enables an equivalent negative sentence in {$uuid}: {$negativeToken}"
                    );
                }
            }

            $equivalentAuxiliaries = [
                "haven't" => 'have',
                "hasn't" => 'has',
            ];
            foreach ($equivalentAuxiliaries as $contracted => $full) {
                if (isset($correctLookup[$contracted])) {
                    $this->assertNotContains(
                        $full,
                        $normalizedDistractors,
                        "Distractor enables the full equivalent of {$contracted} in {$uuid}"
                    );
                }
                if (isset($correctLookup[$full], $correctLookup['not'])) {
                    $this->assertNotContains(
                        $contracted,
                        $normalizedDistractors,
                        "Distractor enables the contracted equivalent of {$full} not in {$uuid}"
                    );
                }
            }

            $composeSentences[] = implode(' ', $answers);
        }

        $this->assertCount(72, array_unique($composeSentences), 'Sentence Builder answer sentences repeat.');
        $standardKeys = array_keys($standardByKey);
        $composeKeyList = array_keys($composeKeys);
        sort($standardKeys);
        sort($composeKeyList);
        $this->assertSame($standardKeys, $composeKeyList, 'Standard and Sentence Builder pair keys differ.');
    }

    public function test_legacy_meta_text_bad_collocations_and_gender_errors_are_absent(): void
    {
        $content = json_encode([
            $this->readJson(self::STANDARD_DEFINITION),
            $this->readJson(self::COMPOSE_DEFINITION),
        ], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);

        $forbiddenPatterns = [
            '/\bUse the (?:full|contracted) form\b/i',
            '/\bComplete using the negative Present Perfect\b/i',
            '/\bdo not use\b/i',
            '/;\s*not\b/i',
            '/not\s+hasn[\'’]t\s+went\b/i',
            '/completed (?:her room|the kitchen|the methodology|the final amendment)\b/i',
            '/bought (?:the )?(?:files|contract|preliminary results|revised agreement)\b/i',
            '/завершил[аи] (?:свою кімнату|кухню|методологію|фінальну поправку)\b/ui',
            '/купили (?:файли|контракт|попередні результати|оновлену угоду)\b/ui',
            '/Том(?![А-Яа-яІіЇїЄєҐґ])[^.!?]{0,80}(?:завершила|надіслала|бачила|прочитала|робила|пішла)\b/u',
            '/Менеджер(?![А-Яа-яІіЇїЄєҐґ])[^.!?]{0,80}(?:завершила|надіслала|бачила|прочитала|робила|пішла)\b/u',
        ];

        foreach ($forbiddenPatterns as $pattern) {
            $this->assertDoesNotMatchRegularExpression($pattern, $content, "Legacy content matched {$pattern}");
        }
    }
}
