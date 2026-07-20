<?php

namespace Tests\Unit;

use App\Support\ComposeTokenCase;
use PHPUnit\Framework\TestCase;

class PastPerfectPolyglotContentQualityTest extends TestCase
{
    /** @return array<string, array{standard: string, polyglot: string}> */
    private function definitions(): array
    {
        return [
            'forms' => [
                'standard' => 'database/seeders/V3/Tenses/PastPerfect/PastPerfectFormsAllLevelsV3Seeder/definition.json',
                'polyglot' => 'database/seeders/V3/Polyglot/PolyglotPastPerfectFormsAllLevelsLessonSeeder/definition.json',
            ],
            'negatives' => [
                'standard' => 'database/seeders/V3/Tenses/PastPerfect/PastPerfectNegativesAllLevelsV3Seeder/definition.json',
                'polyglot' => 'database/seeders/V3/Polyglot/PolyglotPastPerfectNegativesAllLevelsLessonSeeder/definition.json',
            ],
            'questions' => [
                'standard' => 'database/seeders/V3/Tenses/PastPerfect/PastPerfectQuestionsAllLevelsV3Seeder/definition.json',
                'polyglot' => 'database/seeders/V3/Polyglot/PolyglotPastPerfectQuestionsAllLevelsLessonSeeder/definition.json',
            ],
            'time-expressions' => [
                'standard' => 'database/seeders/V3/Tenses/PastPerfect/PastPerfectTimeExpressionsAllLevelsV3Seeder/definition.json',
                'polyglot' => 'database/seeders/V3/Polyglot/PolyglotPastPerfectTimeExpressionsAllLevelsLessonSeeder/definition.json',
            ],
        ];
    }

    private function readJson(string $relativePath): array
    {
        return json_decode(
            (string) file_get_contents(dirname(__DIR__, 2).'/'.$relativePath),
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

    private function counterpartKey(string $uuid): string
    {
        preg_match('/-([a-c][12])-([0-9]{2})$/', $uuid, $matches);

        return ($matches[1] ?? '').'-'.($matches[2] ?? '');
    }

    private function isShortAnswer(array $question): bool
    {
        return in_array('short_answers', $question['tag_keys'] ?? [], true);
    }

    /** @return list<string> */
    private function shortAnswerTokens(string $answer): array
    {
        return array_values(array_filter(
            preg_split('/\s+/u', trim($answer)) ?: [],
            static fn (string $token): bool => $token !== ''
        ));
    }

    public function test_all_sentence_builder_banks_match_the_standard_questions(): void
    {
        foreach ($this->definitions() as $label => $paths) {
            $standard = $this->readJson($paths['standard']);
            $polyglot = $this->readJson($paths['polyglot']);

            $this->assertCount(72, $standard['questions'], "Unexpected standard count for {$label}");
            $this->assertCount(72, $polyglot['questions'], "Unexpected Polyglot count for {$label}");
            $this->assertTrue(
                (bool) ($polyglot['saved_test']['filters']['supports_duplicate_tokens'] ?? false),
                "Duplicate-token support is disabled for {$label}"
            );
            $this->assertCount(
                72,
                array_unique(array_column($polyglot['questions'], 'question')),
                "Ukrainian prompts repeat in {$label}"
            );

            $levels = array_count_values(array_column($polyglot['questions'], 'level'));
            foreach (['A1', 'A2', 'B1', 'B2', 'C1', 'C2'] as $level) {
                $this->assertSame(12, $levels[$level] ?? 0, "Unexpected {$level} count for {$label}");
            }

            $standardByKey = [];
            foreach ($standard['questions'] as $question) {
                $standardByKey[$this->counterpartKey((string) $question['uuid'])] = $question;
            }

            foreach ($polyglot['questions'] as $question) {
                $uuid = (string) $question['uuid'];
                $this->assertSame(4, $question['type'], "Wrong compose type in {$uuid}");
                $this->assertSame([], $question['variants'], "Compose variants must stay empty in {$uuid}");
                $this->assertMatchesRegularExpression('/[А-Яа-яІіЇїЄєҐґ]/u', $question['question'], $uuid);
                $this->assertMatchesRegularExpression('/[.!?]$/u', $question['question'], "Missing punctuation in {$uuid}");

                $standardQuestion = $standardByKey[$this->counterpartKey($uuid)] ?? null;
                $this->assertNotNull($standardQuestion, "No standard counterpart for {$uuid}");

                $answers = $this->orderedAnswers($question);
                $this->assertNotEmpty($answers, "Missing answer tokens in {$uuid}");
                $this->assertSame($answers, ComposeTokenCase::normalize($answers), "Incorrect sentence case in {$uuid}");

                $isShortAnswer = $label === 'questions' && $this->isShortAnswer($standardQuestion);
                $completed = str_replace(
                    '{a1}',
                    $standardQuestion['markers']['a1']['answer'],
                    $standardQuestion['question']
                );
                $this->assertSame(
                    $isShortAnswer
                        ? $this->shortAnswerTokens((string) $standardQuestion['markers']['a1']['answer'])
                        : $this->sentenceTokens($completed),
                    $answers,
                    "Sentence Builder answer diverges from the standard question in {$uuid}"
                );

                $options = $question['options'];
                $this->assertSame(
                    $answers,
                    array_slice($options, 0, count($answers)),
                    "Correct tokens are not authored first in {$uuid}"
                );
                foreach ($options as $option) {
                    $this->assertDoesNotMatchRegularExpression('/\s/u', $option, "Multiword tile in {$uuid}: {$option}");
                }

                $answerMultiplicity = array_count_values($answers);
                $optionMultiplicity = array_count_values($options);
                foreach ($answerMultiplicity as $token => $count) {
                    $this->assertGreaterThanOrEqual(
                        $count,
                        $optionMultiplicity[$token] ?? 0,
                        "Missing duplicate token {$token} in {$uuid}"
                    );
                }

                $correctLookup = array_fill_keys(array_map('mb_strtolower', $answers), true);
                $distractors = array_values(array_unique(array_map(
                    'mb_strtolower',
                    array_filter(
                        $options,
                        static fn (string $option): bool => ! isset($correctLookup[mb_strtolower($option)])
                    )
                )));
                $this->assertGreaterThanOrEqual(4, count($distractors), "Too few distractors in {$uuid}");

                $verbHints = $question['verb_hints'] ?? null;
                $standardHint = (string) ($standardQuestion['markers']['a1']['verb_hint'] ?? '');
                if ($isShortAnswer) {
                    $this->assertNull($verbHints, "Non-lexical item must not expose a verb hint in {$uuid}");
                    $this->assertCount(3, $answers, "Short answer must contain exactly three tiles in {$uuid}");
                    $this->assertMatchesRegularExpression('/^(Yes|No),$/', $answers[0], $uuid);
                    $this->assertNotContains('—', $answers, "Dash leaked into short-answer tiles in {$uuid}");
                    $this->assertNotContains('—', $options, "Dash leaked into short-answer options in {$uuid}");
                    $this->assertStringContainsString(
                        '. ',
                        $question['question'],
                        "Short-answer evidence lead-in is missing in {$uuid}"
                    );
                    $this->assertMatchesRegularExpression(
                        '/\? — (Так|Ні)\.$/u',
                        $question['question'],
                        "Short-answer Ukrainian prompt is incomplete in {$uuid}"
                    );

                    continue;
                }

                if ($label !== 'time-expressions' && ! str_starts_with($standardHint, 'Дієслово: «')) {
                    $this->assertNull($verbHints, "Non-lexical item must not expose a verb hint in {$uuid}");

                    continue;
                }

                $this->assertIsArray($verbHints, "Missing Ukrainian verb hint in {$uuid}");
                $this->assertCount(1, $verbHints, "Expected one lexical-verb hint in {$uuid}");
                $marker = (string) array_key_first($verbHints);
                $hint = (string) $verbHints[$marker];
                $this->assertArrayHasKey($marker, $question['answers'], "Hint marker does not exist in {$uuid}");
                $this->assertMatchesRegularExpression(
                    '/^[А-Яа-яІіЇїЄєҐґʼ’\'\- ]+$/u',
                    $hint,
                    "Verb hint must be a Ukrainian infinitive in {$uuid}"
                );
                $this->assertNotContains(
                    mb_strtolower((string) $question['answers'][$marker]),
                    ['had', "hadn't", 'not', 'already', 'just', 'never'],
                    "Hint is attached to a grammar marker instead of the lexical verb in {$uuid}"
                );
            }
        }
    }


    public function test_questions_bank_has_twelve_answer_only_short_answer_items(): void
    {
        $standard = $this->readJson(
            'database/seeders/V3/Tenses/PastPerfect/PastPerfectQuestionsAllLevelsV3Seeder/definition.json'
        );
        $polyglot = $this->readJson(
            'database/seeders/V3/Polyglot/PolyglotPastPerfectQuestionsAllLevelsLessonSeeder/definition.json'
        );
        $standardByKey = [];
        foreach ($standard['questions'] as $question) {
            $standardByKey[$this->counterpartKey((string) $question['uuid'])] = $question;
        }

        $shortAnswerCount = 0;
        foreach ($polyglot['questions'] as $question) {
            $key = $this->counterpartKey((string) $question['uuid']);
            $standardQuestion = $standardByKey[$key];
            if (! $this->isShortAnswer($standardQuestion)) {
                continue;
            }

            $shortAnswerCount++;
            $this->assertMatchesRegularExpression('/-(08|10)$/', (string) $question['uuid']);
            $this->assertSame(
                $this->shortAnswerTokens((string) $standardQuestion['markers']['a1']['answer']),
                $this->orderedAnswers($question),
                "Short answer must contain only marker.answer in {$question['uuid']}"
            );
            $this->assertArrayNotHasKey('verb_hints', $question, "Short answer exposes a hint in {$question['uuid']}");
        }

        $this->assertSame(12, $shortAnswerCount);
    }

    public function test_revised_short_answer_prompts_keep_the_full_ukrainian_evidence(): void
    {
        $definition = $this->readJson(
            'database/seeders/V3/Polyglot/PolyglotPastPerfectQuestionsAllLevelsLessonSeeder/definition.json'
        );
        $questions = array_column($definition['questions'], null, 'uuid');
        $expected = [
            'pp-questions-poly-a1-08' => 'Коли приїхало таксі, сумка Елли вже була спакована. Елла спакувала сумку до приїзду таксі? — Так.',
            'pp-questions-poly-a1-10' => 'Бен уперше побачив сніг під час тієї подорожі. Він бачив сніг до тієї подорожі? — Ні.',
            'pp-questions-poly-a2-08' => 'Лео перевірив адресу перед від’їздом. Він перевірив адресу перед від’їздом? — Так.',
            'pp-questions-poly-a2-10' => 'Під час тієї шкільної поїздки діти вперше відвідали музей. Вони відвідували музей до тієї шкільної поїздки? — Ні.',
            'pp-questions-poly-b1-08' => 'Роза попередила команду до зміни дедлайну. Вона попередила команду до зміни дедлайну? — Так.',
            'pp-questions-poly-b2-08' => 'Роза, голова засідання, розіслала порядок денний до прибуття делегатів. Роза розіслала порядок денний до прибуття делегатів? — Так.',
            'pp-questions-poly-c1-08' => 'Коли почалося розслідування, агентство вже оприлюднило інформацію про конфлікт. Агентство оприлюднило інформацію про конфлікт до початку розслідування? — Так.',
            'pp-questions-poly-c2-08' => 'У проєкті висновку колегії вже було сформульовано обмежувальний принцип. Колегія сформулювала обмежувальний принцип до публікації свого висновку? — Так.',
        ];

        foreach ($expected as $uuid => $prompt) {
            $this->assertSame($prompt, $questions[$uuid]['question'] ?? null, $uuid);
        }
    }

    public function test_final_reviewed_time_expression_wording_is_preserved(): void
    {
        $definition = $this->readJson(
            'database/seeders/V3/Polyglot/PolyglotPastPerfectTimeExpressionsAllLevelsLessonSeeder/definition.json'
        );
        $questions = array_column($definition['questions'], null, 'uuid');

        $this->assertSame(
            'Після того як Лео поснідав, він пішов до школи.',
            $questions['pp-time-expressions-poly-a1-02']['question']
        );
        $this->assertContains(
            'поснідати',
            $questions['pp-time-expressions-poly-a1-02']['verb_hints']
        );
        $this->assertSame(
            'Журналіст залучив два незалежні джерела до схвалення матеріалу редактором.',
            $questions['pp-time-expressions-poly-b1-09']['question']
        );
        $this->assertContains(
            'залучити',
            $questions['pp-time-expressions-poly-b1-09']['verb_hints']
        );
        $this->assertStringContainsString(
            'етапу тендеру',
            $questions['pp-time-expressions-poly-c1-11']['question']
        );
        $this->assertStringContainsString(
            'обмеженого компромісу',
            $questions['pp-time-expressions-poly-c2-05']['question']
        );
    }
}
