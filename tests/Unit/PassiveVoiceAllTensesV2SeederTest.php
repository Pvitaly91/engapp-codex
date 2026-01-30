<?php

namespace Tests\Unit;

use Database\Seeders\V2\PassiveVoiceAllTensesV2Seeder;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class PassiveVoiceAllTensesV2SeederTest extends TestCase
{
    public function test_seeder_has_correct_number_of_questions(): void
    {
        $seeder = new PassiveVoiceAllTensesV2Seeder();
        $method = (new ReflectionClass($seeder))->getMethod('questionEntries');
        $method->setAccessible(true);

        $questions = $method->invoke($seeder);

        // Total questions: 10 (page1) + 10 (page2) + 10 (page3) = 30
        $this->assertCount(30, $questions, 'Seeder should have 30 questions');
    }

    public function test_all_questions_have_required_fields(): void
    {
        $seeder = new PassiveVoiceAllTensesV2Seeder();
        $method = (new ReflectionClass($seeder))->getMethod('questionEntries');
        $method->setAccessible(true);

        $questions = $method->invoke($seeder);

        foreach ($questions as $index => $question) {
            $this->assertArrayHasKey('question', $question, "Question $index is missing 'question' field");
            $this->assertArrayHasKey('options', $question, "Question $index is missing 'options' field");
            $this->assertArrayHasKey('answers', $question, "Question $index is missing 'answers' field");
            $this->assertArrayHasKey('level', $question, "Question $index is missing 'level' field");
            $this->assertArrayHasKey('source', $question, "Question $index is missing 'source' field");
            $this->assertArrayHasKey('verb_hints', $question, "Question $index is missing 'verb_hints' field");
            $this->assertArrayHasKey('hints', $question, "Question $index is missing 'hints' field");
            $this->assertArrayHasKey('explanations', $question, "Question $index is missing 'explanations' field");
        }
    }

    public function test_all_questions_have_markers_in_text(): void
    {
        $seeder = new PassiveVoiceAllTensesV2Seeder();
        $method = (new ReflectionClass($seeder))->getMethod('questionEntries');
        $method->setAccessible(true);

        $questions = $method->invoke($seeder);

        foreach ($questions as $index => $question) {
            $this->assertMatchesRegularExpression(
                '/\{a\d+\}/',
                $question['question'],
                "Question $index should contain at least one marker like {a1}"
            );
        }
    }

    public function test_all_questions_have_correct_difficulty_level(): void
    {
        $seeder = new PassiveVoiceAllTensesV2Seeder();
        $method = (new ReflectionClass($seeder))->getMethod('questionEntries');
        $method->setAccessible(true);

        $questions = $method->invoke($seeder);
        $validLevels = ['A1', 'A2', 'B1', 'B2', 'C1', 'C2'];

        foreach ($questions as $index => $question) {
            $this->assertContains(
                $question['level'],
                $validLevels,
                "Question $index has invalid level: {$question['level']}"
            );
        }
    }

    public function test_all_questions_have_hints(): void
    {
        $seeder = new PassiveVoiceAllTensesV2Seeder();
        $method = (new ReflectionClass($seeder))->getMethod('questionEntries');
        $method->setAccessible(true);

        $questions = $method->invoke($seeder);

        foreach ($questions as $index => $question) {
            $this->assertNotEmpty($question['hints'], "Question $index should have hints");
            $this->assertIsArray($question['hints'], "Question $index hints should be an array");
        }
    }

    public function test_all_questions_have_explanations_for_each_option(): void
    {
        $seeder = new PassiveVoiceAllTensesV2Seeder();
        $method = (new ReflectionClass($seeder))->getMethod('questionEntries');
        $method->setAccessible(true);

        $questions = $method->invoke($seeder);

        foreach ($questions as $index => $question) {
            $this->assertNotEmpty($question['explanations'], "Question $index should have explanations");
            
            // Check that explanations exist for each marker
            foreach ($question['answers'] as $marker => $answer) {
                $this->assertArrayHasKey(
                    $marker,
                    $question['explanations'],
                    "Question $index is missing explanations for marker $marker"
                );
                
                // Check that each explanation set has explanations for each option
                $options = $question['options'][$marker];
                foreach ($options as $option) {
                    $this->assertArrayHasKey(
                        $option,
                        $question['explanations'][$marker],
                        "Question $index is missing explanation for option '$option' in marker $marker"
                    );
                }
            }
        }
    }

    public function test_questions_distribution_across_pages(): void
    {
        $seeder = new PassiveVoiceAllTensesV2Seeder();
        $method = (new ReflectionClass($seeder))->getMethod('questionEntries');
        $method->setAccessible(true);

        $questions = $method->invoke($seeder);

        $pageDistribution = [
            'page1' => 0,
            'page2' => 0,
            'page3' => 0,
        ];

        foreach ($questions as $question) {
            $pageDistribution[$question['source']]++;
        }

        $this->assertEquals(10, $pageDistribution['page1'], 'Page 1 should have 10 questions');
        $this->assertEquals(10, $pageDistribution['page2'], 'Page 2 should have 10 questions');
        $this->assertEquals(10, $pageDistribution['page3'], 'Page 3 should have 10 questions');
    }

    public function test_all_questions_have_verb_hints_for_answers(): void
    {
        $seeder = new PassiveVoiceAllTensesV2Seeder();
        $method = (new ReflectionClass($seeder))->getMethod('questionEntries');
        $method->setAccessible(true);

        $questions = $method->invoke($seeder);

        foreach ($questions as $index => $question) {
            foreach ($question['answers'] as $marker => $answer) {
                $this->assertArrayHasKey(
                    $marker,
                    $question['verb_hints'],
                    "Question $index is missing verb_hint for marker $marker"
                );
                $this->assertNotEmpty(
                    $question['verb_hints'][$marker],
                    "Question $index has empty verb_hint for marker $marker"
                );
            }
        }
    }
}
