<?php

namespace Tests\Unit;

use App\Support\ComposeTokenCase;
use PHPUnit\Framework\TestCase;

class PresentPerfectFormsContentQualityTest extends TestCase
{
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

    public function test_standard_bank_is_complete_unique_and_contextual(): void
    {
        $definition = $this->readJson(
            'database/seeders/V3/Tenses/PresentPerfect/PresentPerfectFormsAllLevelsV3Seeder/definition.json'
        );

        $this->assertCount(72, $definition['questions']);
        $this->assertCount(72, array_unique(array_column($definition['questions'], 'question')));

        $levels = array_count_values(array_column($definition['questions'], 'level'));
        foreach (['A1', 'A2', 'B1', 'B2', 'C1', 'C2'] as $level) {
            $this->assertSame(12, $levels[$level] ?? 0, "Unexpected {$level} question count");
        }

        $completedSentences = [];
        $ambiguousLegacyOptions = [
            'finished', 'completed', 'lost', 'was', 'were', 'watched', 'bought', 'wrote', 'just found',
        ];

        foreach ($definition['questions'] as $question) {
            $uuid = $question['uuid'];
            $marker = $question['markers']['a1'];

            $this->assertStringContainsString('{a1}', $question['question'], "Missing marker in {$uuid}");
            $this->assertMatchesRegularExpression('/[.?]$/u', $question['question'], "Missing punctuation in {$uuid}");
            $this->assertSame([$question['question']], $question['variants'], "Stale variant in {$uuid}");
            $this->assertCount(5, $marker['options'], "Unexpected option count in {$uuid}");
            $this->assertCount(5, array_unique($marker['options']), "Duplicate option in {$uuid}");
            $this->assertSame($marker['answer'], $marker['options'][0], "Correct option is not authored first in {$uuid}");
            $this->assertNotContains($marker['answer'], array_slice($marker['options'], 1), "Duplicate answer in {$uuid}");
            $this->assertEmpty(
                array_intersect($ambiguousLegacyOptions, $marker['options']),
                "A standalone alternative tense remains valid in {$uuid}"
            );

            $hint = $marker['verb_hint'];
            $this->assertMatchesRegularExpression('/[А-Яа-яІіЇїЄє]/u', $hint, "Hint is not Ukrainian in {$uuid}");
            $this->assertStringNotContainsStringIgnoringCase($marker['answer'], $hint, "Hint reveals the full answer in {$uuid}");

            $answerTokens = preg_split('/\s+/u', trim($marker['answer'])) ?: [];
            $lastAnswerToken = (string) end($answerTokens);
            if (mb_strlen($lastAnswerToken) >= 4) {
                $this->assertStringNotContainsStringIgnoringCase(
                    $lastAnswerToken,
                    $hint,
                    "Hint reveals the target form in {$uuid}"
                );
            }

            $completedSentences[] = str_replace('{a1}', $marker['answer'], $question['question']);
        }

        $this->assertCount(72, array_unique($completedSentences), 'Completed sentences repeat across levels.');

        $answersByLevel = collect($definition['questions'])
            ->groupBy('level')
            ->map(fn ($questions): float => $questions->avg(
                fn (array $question): int => count(preg_split('/\s+/u', $question['markers']['a1']['answer']) ?: [])
            ));
        $this->assertGreaterThan($answersByLevel['A1'] + 0.5, $answersByLevel['C2']);

        $advancedContent = implode(' ', array_filter(
            $completedSentences,
            fn (string $sentence, int $index): bool => in_array($definition['questions'][$index]['level'], ['C1', 'C2'], true),
            ARRAY_FILTER_USE_BOTH
        ));
        $this->assertStringContainsString('Not until now have analysts had', $advancedContent);
        $this->assertStringContainsString('has been formed', $advancedContent);
        $this->assertStringContainsString('Rarely have policymakers been', $advancedContent);
        $this->assertStringContainsString('has just been adopted', $advancedContent);
        $this->assertStringContainsString('Never before has a single ruling generated', $advancedContent);
    }

    public function test_sentence_builder_matches_standard_content_and_preserves_tokens(): void
    {
        $standard = $this->readJson(
            'database/seeders/V3/Tenses/PresentPerfect/PresentPerfectFormsAllLevelsV3Seeder/definition.json'
        );
        $polyglot = $this->readJson(
            'database/seeders/V3/Polyglot/PolyglotPresentPerfectFormsAllLevelsLessonSeeder/definition.json'
        );

        $this->assertCount(72, $polyglot['questions']);
        $this->assertCount(72, array_unique(array_column($polyglot['questions'], 'question')));
        $this->assertTrue((bool) $polyglot['saved_test']['filters']['supports_duplicate_tokens']);

        $standardByKey = [];
        foreach ($standard['questions'] as $question) {
            preg_match('/-([a-c][12])-([0-9]{2})$/', $question['uuid'], $matches);
            $standardByKey[$matches[1].'-'.$matches[2]] = $question;
        }

        foreach ($polyglot['questions'] as $question) {
            $uuid = $question['uuid'];
            $this->assertSame(4, $question['type'], "Wrong compose type in {$uuid}");
            $this->assertMatchesRegularExpression('/[А-Яа-яІіЇїЄє]/u', $question['question']);
            $this->assertMatchesRegularExpression('/[.!?]$/u', $question['question']);

            preg_match('/-([a-c][12])-([0-9]{2})$/', $uuid, $matches);
            $standardQuestion = $standardByKey[$matches[1].'-'.$matches[2]] ?? null;
            $this->assertNotNull($standardQuestion, "No standard counterpart for {$uuid}");

            $answers = $this->orderedAnswers($question);
            $this->assertNotEmpty($answers);
            $this->assertSame($answers, ComposeTokenCase::normalize($answers), "Incorrect sentence case in {$uuid}");
            $this->assertSame(
                $this->sentenceTokens(str_replace(
                    '{a1}',
                    $standardQuestion['markers']['a1']['answer'],
                    $standardQuestion['question']
                )),
                $answers,
                "Sentence Builder answer diverges from the standard question in {$uuid}"
            );

            foreach ($question['options'] as $option) {
                $this->assertStringNotContainsString(' ', $option, "Multiword tile in {$uuid}");
            }

            $correctLookup = array_fill_keys(array_map('mb_strtolower', $answers), true);
            $distractors = array_values(array_unique(array_map(
                'mb_strtolower',
                array_filter(
                    $question['options'],
                    static fn (string $option): bool => ! isset($correctLookup[mb_strtolower($option)])
                )
            )));
            $this->assertGreaterThanOrEqual(4, count($distractors), "Too few distractors in {$uuid}");

            $answerMultiplicity = array_count_values($answers);
            $optionMultiplicity = array_count_values($question['options']);
            foreach ($answerMultiplicity as $token => $count) {
                $this->assertGreaterThanOrEqual(
                    $count,
                    $optionMultiplicity[$token] ?? 0,
                    "Missing duplicate token {$token} in {$uuid}"
                );
            }
        }
    }

    public function test_basic_a2_questions_keep_natural_wording_and_sentence_case(): void
    {
        $definition = $this->readJson(
            'database/seeders/V3/Polyglot/PolyglotPresentPerfectBasicLessonSeeder/definition.json'
        );

        $this->assertCount(24, $definition['questions']);
        $questions = collect($definition['questions'])->keyBy('uuid');
        $this->assertSame(
            'Що ти зробив сьогодні?',
            $questions['polyglot-present-perfect-basic-a2-q21']['question']
        );

        foreach (range(15, 24) as $number) {
            $uuid = sprintf('polyglot-present-perfect-basic-a2-q%02d', $number);
            $question = $questions[$uuid];
            $answers = $this->orderedAnswers($question);

            $this->assertSame($answers, ComposeTokenCase::normalize($answers), "Incorrect question case in {$uuid}");
            foreach (array_unique($answers) as $answer) {
                $this->assertContains($answer, $question['options'], "Missing option {$answer} in {$uuid}");
            }
        }
    }

    public function test_legacy_broken_phrases_are_absent(): void
    {
        $content = json_encode([
            $this->readJson('database/seeders/V3/Tenses/PresentPerfect/PresentPerfectFormsAllLevelsV3Seeder/definition.json'),
            $this->readJson('database/seeders/V3/Polyglot/PolyglotPresentPerfectFormsAllLevelsLessonSeeder/definition.json'),
        ], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);

        $forbiddenPatterns = [
            '/not wrote it/i',
            '/completed (?:her room|the kitchen|the methodology|the final amendment)/i',
            '/cannot open the door because I .*?(?:phone|notes|dataset|evidence)/i',
            '/bought (?:the files|the contract|the preliminary results|the revised agreement)/i',
            '/Вони живемо/u',
            '/Том (?:завершила|була|написала)/u',
            '/а не wrote it/ui',
            '/купили (?:файли|контракт|попередні результати|оновлену угоду)/ui',
        ];

        foreach ($forbiddenPatterns as $pattern) {
            $this->assertDoesNotMatchRegularExpression($pattern, $content, "Legacy content matched {$pattern}");
        }
    }

    public function test_embedded_compose_bank_keeps_duplicate_correct_tokens(): void
    {
        $view = (string) file_get_contents(
            $this->rootPath().'/resources/views/components/text-block-practice-questions.blade.php'
        );

        $this->assertStringContainsString(
            'const seen = new Set(correct.map(value => value.toLowerCase()));',
            $view
        );
        $this->assertStringContainsString('return [...correct, ...uniqueDistractors];', $view);
        $this->assertStringNotContainsString('return [...correct, ...distractors]', $view);
    }
}
