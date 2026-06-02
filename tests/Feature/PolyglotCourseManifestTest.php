<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Question;
use App\Models\QuestionAnswer;
use App\Models\QuestionOption;
use App\Models\SavedGrammarTest;
use App\Services\PolyglotCourseManifestService;
use Illuminate\Support\Str;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

class PolyglotCourseManifestTest extends TestCase
{
    use RebuildsComposeTestSchema;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'coming-soon.enabled' => false,
            'tests.tech_info_enabled' => false,
        ]);

        $this->rebuildComposeTestSchema();
    }

    public function test_only_compose_compatible_lessons_appear_in_course_manifest(): void
    {
        $this->createSavedTestWithQuestion('polyglot-course-lesson-1', 1);
        $this->createSavedTestWithQuestion('polyglot-course-lesson-2', 2);
        $this->createSavedTestWithQuestion(
            'polyglot-course-incompatible',
            3,
            [
                'mode' => 'step',
                'question_type' => 1,
            ],
            null,
            'I go home.',
            [['marker' => 'a1', 'value' => 'go']],
            ['go', 'goes']
        );

        $manifest = app(PolyglotCourseManifestService::class)->build('polyglot-english-a1');

        $this->assertCount(2, $manifest['lessons']);
        $this->assertSame(['polyglot-course-lesson-1', 'polyglot-course-lesson-2'], array_column($manifest['lessons'], 'slug'));
    }

    public function test_manifest_sort_by_lesson_order_is_stable(): void
    {
        $this->createSavedTestWithQuestion('polyglot-course-lesson-2', 2);
        $this->createSavedTestWithQuestion('polyglot-course-lesson-3', 3);
        $this->createSavedTestWithQuestion('polyglot-course-lesson-1', 1);

        $manifest = app(PolyglotCourseManifestService::class)->build('polyglot-english-a1');

        $this->assertSame([1, 2, 3], array_column($manifest['lessons'], 'lesson_order'));
        $this->assertSame('polyglot-course-lesson-1', $manifest['first_lesson']['slug']);
        $this->assertSame(3, app(PolyglotCourseManifestService::class)->totalLessons($manifest));
    }

    private function createSavedTestWithQuestion(
        string $slug,
        int $lessonOrder,
        array $filters = [],
        ?string $questionType = Question::TYPE_COMPOSE_TOKENS,
        string $questionText = 'Я готовий.',
        array $answers = [
            ['marker' => 'a1', 'value' => 'I'],
            ['marker' => 'a2', 'value' => 'am'],
            ['marker' => 'a3', 'value' => 'ready'],
        ],
        array $options = ['I', 'am', 'ready', 'are']
    ): SavedGrammarTest {
        $category = Category::query()->firstOrCreate([
            'name' => 'Polyglot Course Manifest Tests',
        ]);

        $question = Question::query()->create([
            'uuid' => (string) Str::uuid(),
            'question' => $questionText,
            'difficulty' => 1,
            'level' => 'A1',
            'category_id' => $category->id,
            'type' => $questionType,
        ]);

        foreach ($options as $optionValue) {
            $option = QuestionOption::query()->firstOrCreate([
                'option' => $optionValue,
            ]);

            $question->options()->syncWithoutDetaching([$option->id]);
        }

        foreach ($answers as $answer) {
            $optionId = QuestionOption::query()
                ->where('option', $answer['value'])
                ->value('id');

            QuestionAnswer::query()->create([
                'question_id' => $question->id,
                'option_id' => $optionId,
                'marker' => $answer['marker'],
            ]);
        }

        $test = SavedGrammarTest::query()->create([
            'uuid' => (string) Str::uuid(),
            'name' => Str::headline($slug),
            'slug' => $slug,
            'filters' => array_merge([
                'course_slug' => 'polyglot-english-a1',
                'lesson_order' => $lessonOrder,
                'previous_lesson_slug' => $lessonOrder > 1 ? 'polyglot-course-lesson-'.($lessonOrder - 1) : null,
                'next_lesson_slug' => null,
                'topic' => 'fixture topic',
                'level' => 'A1',
                'mode' => 'compose_tokens',
                'question_type' => Question::TYPE_COMPOSE_TOKENS,
                'completion' => [
                    'rolling_window' => 100,
                    'min_rating' => 4.5,
                ],
            ], $filters),
            'description' => 'Course manifest fixture',
        ]);

        $test->questionLinks()->create([
            'question_uuid' => $question->uuid,
            'position' => 0,
        ]);

        return $test;
    }
}
