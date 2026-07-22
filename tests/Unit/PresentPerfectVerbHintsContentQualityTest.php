<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2).'/scripts/lib/present_perfect_verb_hints.php';

class PresentPerfectVerbHintsContentQualityTest extends TestCase
{
    private function readJson(string $relativePath): array
    {
        return json_decode(
            (string) file_get_contents(dirname(__DIR__, 2).'/'.$relativePath),
            true,
            512,
            JSON_THROW_ON_ERROR
        );
    }

    public function test_every_present_perfect_verb_gap_names_the_verb_in_ukrainian(): void
    {
        $definitions = [
            'database/seeders/V3/Tenses/PresentPerfect/PresentPerfectFormsAllLevelsV3Seeder/definition.json' => 72,
            'database/seeders/V3/Tenses/PresentPerfect/PresentPerfectNegativesAllLevelsV3Seeder/definition.json' => 72,
            'database/seeders/V3/Tenses/PresentPerfect/PresentPerfectQuestionsAllLevelsV3Seeder/definition.json' => 72,
            'database/seeders/V3/Tenses/PresentPerfect/PresentPerfectTimeExpressionsAllLevelsV3Seeder/definition.json' => 12,
        ];

        foreach ($definitions as $path => $expectedHintCount) {
            $questions = $this->readJson($path)['questions'];
            $checked = 0;

            foreach ($questions as $question) {
                $uuid = (string) $question['uuid'];
                $verb = presentPerfectUkrainianVerb($uuid);
                if ($verb === null) {
                    continue;
                }

                $hint = (string) ($question['markers']['a1']['verb_hint'] ?? '');
                $this->assertStringStartsWith("Дієслово: «{$verb}».", $hint, $uuid);
                $this->assertSame(1, preg_match('/^Дієслово: «[^»]+»\./u', $hint), $uuid);
                $checked++;
            }

            $this->assertSame($expectedHintCount, $checked, $path);
        }
    }

    public function test_present_perfect_question_builder_hints_are_attached_to_the_v3_token(): void
    {
        $questions = $this->readJson(
            'database/seeders/V3/Polyglot/PolyglotPresentPerfectQuestionsAllLevelsLessonSeeder/definition.json'
        )['questions'];

        $hinted = 0;
        foreach ($questions as $question) {
            $uuid = (string) $question['uuid'];
            $expected = presentPerfectQuestionPolyglotVerbHint($uuid);

            if ($expected === null) {
                $this->assertArrayNotHasKey('verb_hints', $question, $uuid);
                continue;
            }

            $this->assertSame(
                [$expected['marker'] => $expected['verb']],
                $question['verb_hints'] ?? null,
                $uuid
            );
            $this->assertArrayHasKey($expected['marker'], $question['answers'], $uuid);
            $this->assertNotContains(
                mb_strtolower((string) $question['answers'][$expected['marker']]),
                ['have', 'has', "haven't", "hasn't", 'not'],
                "Hint is attached to an auxiliary instead of the lexical verb in {$uuid}"
            );
            $hinted++;
        }

        $this->assertSame(66, $hinted);
    }

    public function test_hint_upgrader_adds_the_ukrainian_hidden_subject_exactly_once(): void
    {
        $context = 'Дієслово: «зробити». So far обмежує запит періодом від початку дня до цього моменту.';
        $expected = 'Дієслово: «зробити». Підмет у запитанні — «ти / ви». So far обмежує запит періодом від початку дня до цього моменту.';

        $this->assertSame($expected, presentPerfectVerbHint('present-perfect-q-v3-a1-03', $context));
        $this->assertSame($expected, presentPerfectVerbHint('present-perfect-q-v3-a1-03', $expected));
        $this->assertSame(
            'Дієслово: «завершити заповнення». Підмет у запитанні — «Мія». Yet у запитанні перевіряє наявність результату зараз; зважте на порядок слів.',
            presentPerfectVerbHint(
                'present-perfect-forms-v3-a2-03',
                'Дієслово: «завершити заповнення». Yet у запитанні перевіряє наявність результату зараз; зважте на порядок слів і підмет Mia.'
            )
        );
        $this->assertSame(
            'Дієслово: «бачити». У короткій відповіді не повторюйте смислове дієслово.',
            presentPerfectVerbHint(
                'present-perfect-q-v3-a1-06',
                'Дієслово: «бачити». У короткій відповіді не повторюйте смислове дієслово.'
            )
        );
    }

    public function test_every_hidden_subject_is_ukrainian_and_matches_the_authored_answer(): void
    {
        $definitions = [
            'database/seeders/V3/Tenses/PresentPerfect/PresentPerfectFormsAllLevelsV3Seeder/definition.json' => 15,
            'database/seeders/V3/Tenses/PresentPerfect/PresentPerfectNegativesAllLevelsV3Seeder/definition.json' => 2,
            'database/seeders/V3/Tenses/PresentPerfect/PresentPerfectQuestionsAllLevelsV3Seeder/definition.json' => 66,
            'database/seeders/V3/Tenses/PresentPerfect/PresentPerfectTimeExpressionsAllLevelsV3Seeder/definition.json' => 0,
        ];

        $total = 0;
        foreach ($definitions as $path => $expectedCount) {
            $count = 0;
            foreach ($this->readJson($path)['questions'] as $question) {
                $uuid = (string) $question['uuid'];
                $hint = (string) ($question['markers']['a1']['verb_hint'] ?? '');
                $subject = presentPerfectHiddenSubject($uuid);

                $this->assertLessThanOrEqual(255, mb_strlen($hint), "Hint exceeds the database limit in {$uuid}");
                if ($subject === null) {
                    $this->assertStringNotContainsString('Підмет у запитанні —', $hint, $uuid);
                    continue;
                }

                $this->assertMatchesRegularExpression('/^[^A-Za-z]*$/u', $subject, $uuid);
                $this->assertStringContainsString("Підмет у запитанні — «{$subject}».", $hint, $uuid);
                $this->assertSame(1, substr_count($hint, 'Підмет у запитанні —'), $uuid);
                $this->assertStringContainsStringIgnoringCase(
                    (string) presentPerfectHiddenAnswerSubject($uuid),
                    (string) $question['markers']['a1']['answer'],
                    $uuid
                );
                $count++;
            }

            $this->assertSame($expectedCount, $count, $path);
            $total += $count;
        }

        $this->assertSame(83, $total);
        $this->assertCount(83, presentPerfectHiddenSubjects());
    }

    public function test_legacy_english_subject_wording_is_removed_from_standard_hints(): void
    {
        $hintsByUuid = [];
        foreach ([
            'database/seeders/V3/Tenses/PresentPerfect/PresentPerfectFormsAllLevelsV3Seeder/definition.json',
            'database/seeders/V3/Tenses/PresentPerfect/PresentPerfectNegativesAllLevelsV3Seeder/definition.json',
            'database/seeders/V3/Tenses/PresentPerfect/PresentPerfectQuestionsAllLevelsV3Seeder/definition.json',
        ] as $path) {
            foreach ($this->readJson($path)['questions'] as $question) {
                $hintsByUuid[(string) $question['uuid']] = (string) $question['markers']['a1']['verb_hint'];
            }
        }

        foreach (presentPerfectSubjectWordingReplacements() as $uuid => $replacements) {
            $this->assertArrayHasKey($uuid, $hintsByUuid, $uuid);
            foreach (array_keys($replacements) as $legacyWording) {
                $this->assertStringNotContainsString($legacyWording, $hintsByUuid[$uuid], $uuid);
            }
        }

        foreach ($hintsByUuid as $uuid => $hint) {
            $this->assertDoesNotMatchRegularExpression(
                '/Підмет у запитанні — «[^»]*[A-Za-z][^»]*»\./u',
                $hint,
                $uuid
            );
        }
    }

    public function test_related_ukrainian_wording_is_natural_and_matches_the_english_target(): void
    {
        $questions = collect($this->readJson(
            'database/seeders/V3/Polyglot/PolyglotPresentPerfectQuestionsAllLevelsLessonSeeder/definition.json'
        )['questions'])->keyBy('uuid');

        $this->assertSame(
            'Даніель уже надіслав посилку, на яку ми досі чекаємо?',
            $questions['present-perfect-q-poly-a2-05']['question']
        );
        $this->assertSame(
            'Інші команди виявили таку закономірність у своїх даних?',
            $questions['present-perfect-q-poly-b1-07']['question']
        );

        $timeExpressions = collect($this->readJson(
            'database/seeders/V3/Polyglot/PolyglotPresentPerfectTimeExpressionsAllLevelsLessonSeeder/definition.json'
        )['questions'])->keyBy('uuid');
        $this->assertSame(
            ['a5' => 'поставити під сумнів'],
            $timeExpressions['present-perfect-time-poly-c2-04']['verb_hints']
        );

        $legacy = collect($this->readJson(
            'database/seeders/V3/Polyglot/PolyglotPresentPerfectTimeExpressionsLessonSeeder/definition.json'
        )['questions'])->keyBy('uuid');
        $this->assertSame(
            'Сьогодні вже йшов дощ?',
            $legacy['polyglot-present-perfect-time-expressions-a2-q18']['question']
        );
    }
}
