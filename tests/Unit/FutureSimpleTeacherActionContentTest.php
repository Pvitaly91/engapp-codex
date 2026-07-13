<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class FutureSimpleTeacherActionContentTest extends TestCase
{
    public function test_teacher_questions_use_prepare_the_lesson(): void
    {
        $cases = [
            [
                'path' => '/database/seeders/V3/FutureForms/FutureSimple/FutureSimpleNegativesAllLevelsV3Seeder/definition.json',
                'uuid' => 'fs-negatives-v3-a1-08',
                'prompt' => 'Our teacher {a1} the lesson.',
                'answer' => "won't prepare",
            ],
            [
                'path' => '/database/seeders/V3/FutureForms/FutureSimple/FutureSimpleQuestionsAllLevelsV3Seeder/definition.json',
                'uuid' => 'fs-questions-v3-a1-08',
                'prompt' => '{a1} the lesson?',
                'answer' => 'Will our teacher prepare',
            ],
            [
                'path' => '/database/seeders/V3/Polyglot/PolyglotFutureSimpleNegativesAllLevelsLessonSeeder/definition.json',
                'uuid' => 'fs-negatives-poly-a1-08',
                'prompt' => 'Наш викладач не буде готувати урок.',
                'answer' => "Our teacher won't prepare the lesson",
            ],
            [
                'path' => '/database/seeders/V3/Polyglot/PolyglotFutureSimpleQuestionsAllLevelsLessonSeeder/definition.json',
                'uuid' => 'fs-questions-poly-a1-08',
                'prompt' => 'Чи готуватиме наш викладач урок',
                'answer' => 'Will our teacher prepare the lesson',
            ],
            [
                'path' => '/database/seeders/V3/Polyglot/PolyglotFutureSimpleTimeExpressionsAllLevelsLessonSeeder/definition.json',
                'uuid' => 'fs-time-poly-a1-08',
                'prompt' => 'Наш викладач підготує урок наступного місяця.',
                'answer' => 'Our teacher will prepare the lesson next month',
            ],
        ];

        foreach ($cases as $case) {
            $definition = json_decode(
                file_get_contents(dirname(__DIR__, 2).$case['path']),
                true,
                512,
                JSON_THROW_ON_ERROR
            );
            $questions = array_column($definition['questions'], null, 'uuid');

            $this->assertArrayHasKey($case['uuid'], $questions);
            $this->assertSame($case['prompt'], $questions[$case['uuid']]['question']);

            $answer = $questions[$case['uuid']]['markers']['a1']['answer']
                ?? implode(' ', array_values($questions[$case['uuid']]['answers']));
            $this->assertSame($case['answer'], $answer);
        }
    }
}
