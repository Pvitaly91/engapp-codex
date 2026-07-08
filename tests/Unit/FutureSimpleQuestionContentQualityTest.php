<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class FutureSimpleQuestionContentQualityTest extends TestCase
{
    public function test_standalone_future_simple_packages_use_complete_grammar_constructions(): void
    {
        $databasePath = dirname(__DIR__, 2).'/database';
        $packages = [
            'forms' => [
                'path' => $databasePath.'/seeders/V3/FutureForms/FutureSimple/FutureSimpleFormsAllLevelsV3Seeder/definition.json',
                'slug' => 'future-simple-forms-all-levels-v3',
            ],
            'negatives' => [
                'path' => $databasePath.'/seeders/V3/FutureForms/FutureSimple/FutureSimpleNegativesAllLevelsV3Seeder/definition.json',
                'slug' => 'future-simple-negatives-all-levels-v3',
            ],
            'questions' => [
                'path' => $databasePath.'/seeders/V3/FutureForms/FutureSimple/FutureSimpleQuestionsAllLevelsV3Seeder/definition.json',
                'slug' => 'future-simple-questions-all-levels-v3',
            ],
            'time' => [
                'path' => $databasePath.'/seeders/V3/FutureForms/FutureSimple/FutureSimpleTimeExpressionsAllLevelsV3Seeder/definition.json',
                'slug' => 'future-simple-time-all-levels-v3',
            ],
        ];

        $allUuids = [];

        foreach ($packages as $type => $package) {
            $definition = json_decode(file_get_contents($package['path']), true, 512, JSON_THROW_ON_ERROR);

            $this->assertSame($package['slug'], $definition['saved_test']['slug']);
            $this->assertSame(72, $definition['saved_test']['filters']['num_questions']);
            $this->assertCount(72, $definition['questions']);

            foreach ($definition['questions'] as $question) {
                $uuid = $question['uuid'];
                $marker = $question['markers']['a1'];
                $answer = trim($marker['answer']);
                $gapType = $marker['gap_tags'][0] ?? '';

                $this->assertArrayNotHasKey($uuid, $allUuids, "Duplicate Future Simple UUID: {$uuid}");
                $allUuids[$uuid] = true;
                $this->assertContains($answer, $marker['options'], "Correct option is missing for {$uuid}");
                $this->assertSame(
                    count($marker['options']),
                    count(array_unique(array_map('mb_strtolower', $marker['options']))),
                    "Duplicate options for {$uuid}"
                );
                $this->assertDoesNotMatchRegularExpression('/\bwill\s+will\b/i', $question['question'].' '.$answer);
                $this->assertSame($question['question'], $question['variants'][0] ?? null);

                if ($type === 'forms') {
                    $this->assertMatchesRegularExpression(
                        "/^(?:will(?:\\s+(?:probably|perhaps|personally|inevitably|eventually|far))?|[A-Za-z]+'ll)\\s+[A-Za-z-]+$/i",
                        $answer,
                        "Incomplete affirmative construction for {$uuid}"
                    );
                } elseif ($type === 'negatives') {
                    $this->assertMatchesRegularExpression(
                        "/^(?:won't|will not)\\s+[A-Za-z-]+$/i",
                        $answer,
                        "Incomplete negative construction for {$uuid}"
                    );
                } elseif ($type === 'questions') {
                    if ($gapType === 'short_answers') {
                        $this->assertMatchesRegularExpression(
                            "/^(?:Yes, (?:they|it) will|No, (?:they|it) won't)$/i",
                            $answer,
                            "Incomplete short answer for {$uuid}"
                        );
                    } else {
                        $this->assertMatchesRegularExpression(
                            '/^will\s+.+\s+[A-Za-z-]+(?:\s+.*)?$/i',
                            $answer,
                            "Incomplete Future Simple question for {$uuid}"
                        );
                    }
                } elseif ($gapType === 'future_marker') {
                    $this->assertMatchesRegularExpression(
                        '/^will\s+[A-Za-z-]+$/i',
                        $answer,
                        "Incomplete timed Future Simple construction for {$uuid}"
                    );
                }
            }
        }

        $this->assertCount(288, $allUuids);
    }
}
