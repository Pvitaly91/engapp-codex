<?php

namespace Tests\Unit;

use App\Support\AcceptedAnswerVariants;
use App\Support\ComposeTokenCase;
use PHPUnit\Framework\TestCase;

class PresentPerfectQuestionsContentQualityTest extends TestCase
{
    private const LEVELS = ['A1', 'A2', 'B1', 'B2', 'C1', 'C2'];

    private const STANDARD_DEFINITION =
        'database/seeders/V3/Tenses/PresentPerfect/PresentPerfectQuestionsAllLevelsV3Seeder/definition.json';

    private const COMPOSE_DEFINITION =
        'database/seeders/V3/Polyglot/PolyglotPresentPerfectQuestionsAllLevelsLessonSeeder/definition.json';

    private const THEORY_LINKS =
        'database/seeders/V3/TheoryLinks/data/present-perfect-questions-theory-links.json';

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
        $withoutTerminalPunctuation = preg_replace('/[.!?]$/u', '', trim($sentence));

        return array_values(array_filter(
            preg_split('/\s+/u', trim((string) $withoutTerminalPunctuation)) ?: [],
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
            if (($question['level'] ?? null) === $level) {
                $counts[] = count($this->sentenceTokens((string) $value($question)));
            }
        }

        $this->assertNotEmpty($counts, "No {$level} questions available");

        return array_sum($counts) / count($counts);
    }

    public function test_standard_bank_is_contextual_unambiguous_and_localized(): void
    {
        $questions = $this->readJson(self::STANDARD_DEFINITION)['questions'];
        $this->assertBalancedLevels($questions, 'Standard bank');
        $this->assertCount(72, array_unique(array_column($questions, 'question')), 'Standard questions repeat.');

        $completed = [];
        foreach ($questions as $question) {
            $uuid = (string) $question['uuid'];
            $this->assertSame(['a1'], array_keys($question['markers']), "{$uuid} must have one marker");
            $this->assertSame(1, substr_count($question['question'], '{a1}'), "Wrong marker count in {$uuid}");
            $this->assertMatchesRegularExpression('/[.!?]$/u', $question['question'], "Missing punctuation in {$uuid}");
            $this->assertSame([$question['question']], $question['variants'], "Stale variant in {$uuid}");

            $marker = $question['markers']['a1'];
            $answer = trim((string) $marker['answer']);
            $options = array_map(static fn ($value): string => trim((string) $value), $marker['options']);
            $this->assertCount(5, $options, "{$uuid} must have five options");
            $this->assertCount(5, array_unique($options), "Duplicate option in {$uuid}");
            $this->assertSame($answer, $options[0], "Correct option must be authored first in {$uuid}");

            $normalized = array_map(
                static fn (string $option): string => mb_strtolower(AcceptedAnswerVariants::normalizeTypography($option)),
                $options
            );
            $this->assertCount(5, array_unique($normalized), "Equivalent options in {$uuid}");
            $accepted = array_map('mb_strtolower', AcceptedAnswerVariants::for($answer));
            foreach (array_slice($normalized, 1) as $distractor) {
                $this->assertNotContains($distractor, $accepted, "Accepted alternative used as a distractor in {$uuid}");
                $this->assertDoesNotMatchRegularExpression(
                    '/^(?:I|you|he|she|it|we|they|[A-Z][\p{L}.-]*)\s+(?:have|has)\b/u',
                    $distractor,
                    "Echo-question distractor in {$uuid}"
                );
            }

            $hint = trim((string) ($marker['verb_hint'] ?? ''));
            $this->assertMatchesRegularExpression('/[А-Яа-яІіЇїЄєҐґ]/u', $hint, "Hint is not Ukrainian in {$uuid}");
            $this->assertDoesNotMatchRegularExpression('/\b(?:have|has|did)\b/iu', $hint, "Hint reveals an auxiliary in {$uuid}");
            $this->assertStringNotContainsStringIgnoringCase($answer, $hint, "Hint reveals the answer in {$uuid}");

            $completed[] = str_replace('{a1}', $answer, $question['question']);
        }

        $this->assertCount(72, array_unique($completed), 'Completed standard questions repeat.');
    }

    public function test_levels_have_real_structural_progression(): void
    {
        $questions = $this->readJson(self::STANDARD_DEFINITION)['questions'];
        $completed = static fn (array $question): string => str_replace(
            '{a1}',
            $question['markers']['a1']['answer'],
            $question['question']
        );

        $this->assertGreaterThan(
            $this->averageTokenCount($questions, 'A1', $completed) + 4,
            $this->averageTokenCount($questions, 'C2', $completed),
            'C2 questions are not contextually richer than A1 questions.'
        );

        $advanced = implode(' ', array_map(
            $completed,
            array_values(array_filter(
                $questions,
                static fn (array $question): bool => in_array($question['level'], ['C1', 'C2'], true)
            ))
        ));
        $this->assertMatchesRegularExpression(
            '/\b(?:How|Which|What|Where)\s+(?:has|have)\b/u',
            $advanced,
            'C1-C2 bank lacks advanced wh-question patterns.'
        );
        $this->assertStringContainsString('been applied', $advanced, 'C2 bank lacks a passive question.');
        $this->assertStringContainsString('even though', $advanced, 'C2 bank lacks concessive context.');
    }

    public function test_sentence_builder_matches_standard_bank_and_uses_compact_atomic_options(): void
    {
        $standard = $this->readJson(self::STANDARD_DEFINITION)['questions'];
        $compose = $this->readJson(self::COMPOSE_DEFINITION)['questions'];
        $this->assertBalancedLevels($compose, 'Sentence Builder bank');
        $this->assertCount(72, array_unique(array_column($compose, 'question')), 'Ukrainian prompts repeat.');

        $standardByKey = [];
        foreach ($standard as $question) {
            $standardByKey[$this->questionKey((string) $question['uuid'])] = $question;
        }

        foreach ($compose as $question) {
            $uuid = (string) $question['uuid'];
            $key = $this->questionKey($uuid);
            $counterpart = $standardByKey[$key] ?? null;
            $this->assertNotNull($counterpart, "Missing standard counterpart for {$uuid}");
            $this->assertSame(4, $question['type'], "Wrong type in {$uuid}");
            $this->assertMatchesRegularExpression('/[А-Яа-яІіЇїЄєҐґ]/u', $question['question'], "Prompt is not Ukrainian in {$uuid}");
            $this->assertMatchesRegularExpression('/[.!?]$/u', $question['question'], "Missing prompt punctuation in {$uuid}");

            $answers = $this->orderedAnswers($question);
            $this->assertSame($answers, ComposeTokenCase::normalize($answers), "Wrong token case in {$uuid}");

            $completed = str_replace('{a1}', $counterpart['markers']['a1']['answer'], $counterpart['question']);
            if (str_contains($completed, '—')) {
                $completed = trim((string) preg_replace('/^.*—\s*/u', '', $completed));
            }
            $this->assertSame($this->sentenceTokens($completed), $answers, "Compose target differs in {$uuid}");

            $options = array_values(array_map('strval', $question['options']));
            foreach ($options as $option) {
                $this->assertNotSame('', trim($option), "Empty compose option in {$uuid}");
                $this->assertDoesNotMatchRegularExpression('/\s/u', $option, "Multiword compose tile in {$uuid}: {$option}");
            }

            $remaining = $options;
            foreach ($answers as $answer) {
                $position = array_search($answer, $remaining, true);
                $this->assertNotFalse($position, "Missing correct token {$answer} in {$uuid}");
                unset($remaining[$position]);
            }
            $distractors = array_values($remaining);
            $normalizedDistractors = array_map('mb_strtolower', $distractors);
            $correctLookup = array_fill_keys(array_map('mb_strtolower', $answers), true);

            $this->assertGreaterThanOrEqual(4, count($distractors), "Too few distractors in {$uuid}");
            $this->assertLessThanOrEqual(8, count($distractors), "Too many distractors in {$uuid}");
            $this->assertCount(count($normalizedDistractors), array_unique($normalizedDistractors), "Duplicate distractors in {$uuid}");
            foreach ($normalizedDistractors as $distractor) {
                $this->assertArrayNotHasKey($distractor, $correctLookup, "Distractor duplicates a correct token in {$uuid}");
            }

            $hint = trim((string) ($question['verb_hints']['a1'] ?? ''));
            $this->assertMatchesRegularExpression('/[А-Яа-яІіЇїЄєҐґ]/u', $hint, "Missing Ukrainian compose hint in {$uuid}");
            $this->assertDoesNotMatchRegularExpression('/\b(?:have|has|did)\b/iu', $hint, "Compose hint reveals an auxiliary in {$uuid}");
        }
    }

    public function test_legacy_meta_text_bad_collocations_and_ambiguous_fallback_are_absent(): void
    {
        $standard = $this->readJson(self::STANDARD_DEFINITION);
        $compose = $this->readJson(self::COMPOSE_DEFINITION);
        $links = $this->readJson(self::THEORY_LINKS);
        $content = json_encode([$standard, $compose], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);

        foreach ([
            '/not\s+Did you finished/i',
            '/помилкове питання через допоміжне дієслово/iu',
            '/bought (?:the )?(?:files|contract|preliminary results|revised agreement)\b/i',
            '/купила (?:файли|контракт|попередні результати|оновлену угоду)\b/iu',
            '/finished my homework/i',
            '/Ти завершив звіт а не/iu',
        ] as $pattern) {
            $this->assertDoesNotMatchRegularExpression($pattern, $content, "Legacy content matched {$pattern}");
        }

        $mixedTest = collect($links['tests_on_page'] ?? [])->firstWhere('kind', 'virtual_mixed_test') ?? [];
        $this->assertSame([], $mixedTest['answer_value_to_bundle_fallback'] ?? null);
    }
}
