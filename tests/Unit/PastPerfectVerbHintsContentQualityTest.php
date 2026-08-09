<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2).'/scripts/lib/past_perfect_verb_hints.php';

class PastPerfectVerbHintsContentQualityTest extends TestCase
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

    public function test_every_hidden_question_subject_is_named_in_ukrainian(): void
    {
        $questions = $this->readJson(
            'database/seeders/V3/Tenses/PastPerfect/PastPerfectQuestionsAllLevelsV3Seeder/definition.json'
        )['questions'];

        $checked = 0;
        foreach ($questions as $question) {
            $uuid = (string) $question['uuid'];
            $answer = (string) $question['markers']['a1']['answer'];
            $hint = (string) $question['markers']['a1']['verb_hint'];
            $subject = pastPerfectHiddenSubject($uuid);

            $this->assertLessThanOrEqual(255, mb_strlen($hint), "Hint exceeds the database limit in {$uuid}");
            if ($subject === null) {
                $this->assertStringNotContainsString('Підмет у запитанні —', $hint, $uuid);
                continue;
            }

            $this->assertMatchesRegularExpression('/^[^A-Za-z]*$/u', $subject, $uuid);
            $this->assertStringContainsString("Підмет у запитанні — «{$subject}».", $hint, $uuid);
            $this->assertSame(1, substr_count($hint, 'Підмет у запитанні —'), $uuid);
            $this->assertStringContainsStringIgnoringCase(
                (string) pastPerfectHiddenAnswerSubject($uuid),
                $answer,
                $uuid
            );
            $checked++;
        }

        $this->assertSame(59, $checked);
        $this->assertCount(59, pastPerfectHiddenSubjects());
    }

    public function test_reported_b2_questions_explicitly_identify_their_subjects(): void
    {
        $questions = collect($this->readJson(
            'database/seeders/V3/Tenses/PastPerfect/PastPerfectQuestionsAllLevelsV3Seeder/definition.json'
        )['questions'])->keyBy('uuid');

        $expected = [
            'pp-questions-v3-b2-01' => 'Підмет у запитанні — «ти / ви».',
            'pp-questions-v3-b2-03' => 'Підмет у запитанні — «дослідники».',
            'pp-questions-v3-b2-05' => 'Підмет у запитанні — «відділ».',
        ];

        foreach ($expected as $uuid => $subjectHint) {
            $this->assertStringContainsString(
                $subjectHint,
                (string) $questions[$uuid]['markers']['a1']['verb_hint'],
                $uuid
            );
        }
    }

    public function test_subject_hint_upgrader_is_idempotent(): void
    {
        $context = 'Дієслово: «вивчити». Поставте питання про ступінь перевірки.';
        $expected = 'Дієслово: «вивчити». Підмет у запитанні — «ти / ви». Поставте питання про ступінь перевірки.';

        $this->assertSame($expected, pastPerfectVerbHint('pp-questions-v3-b2-01', $context));
        $this->assertSame($expected, pastPerfectVerbHint('pp-questions-v3-b2-01', $expected));
    }

    public function test_all_past_perfect_standard_hints_fit_the_database_column(): void
    {
        $paths = [
            'database/seeders/V3/Tenses/PastPerfect/PastPerfectFormsAllLevelsV3Seeder/definition.json',
            'database/seeders/V3/Tenses/PastPerfect/PastPerfectNegativesAllLevelsV3Seeder/definition.json',
            'database/seeders/V3/Tenses/PastPerfect/PastPerfectQuestionsAllLevelsV3Seeder/definition.json',
            'database/seeders/V3/Tenses/PastPerfect/PastPerfectTimeExpressionsAllLevelsV3Seeder/definition.json',
        ];

        foreach ($paths as $path) {
            foreach ($this->readJson($path)['questions'] as $question) {
                foreach ($question['markers'] as $marker => $definition) {
                    $hint = trim((string) ($definition['verb_hint'] ?? ''));
                    $this->assertNotSame('', $hint, $question['uuid'].':'.$marker);
                    $this->assertLessThanOrEqual(255, mb_strlen($hint), $question['uuid'].':'.$marker);
                }
            }
        }
    }
}
