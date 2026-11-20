<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Question;
use App\Models\QuestionAnswer;
use App\Models\QuestionOption;
use App\Models\SavedGrammarTest;
use App\Modules\LanguageManager\Models\Language;
use App\Modules\LanguageManager\Services\LocaleService;
use App\Services\PolyglotCourseManifestService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
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

    public function test_course_links_are_loaded_in_one_batch_for_exact_course_candidate_ids(): void
    {
        $linked = $this->createSavedTestWithQuestion('polyglot-fixture-linked', 2, [
            'course_slug' => ' polyglot-english-a1 ',
            'mode' => 'step',
            'question_type' => null,
        ]);
        // Eloquent's JSON cast also supports a stored JSON string that decodes to filters.
        $linked->update(['filters' => json_encode($linked->filters, JSON_THROW_ON_ERROR)]);
        $this->assertIsString($linked->fresh()->filters);
        $empty = $this->createSavedTest('polyglot-fixture-empty', 1);
        $ineligible = $this->createSavedTestWithQuestion('polyglot-fixture-ineligible', 3, [
            'mode' => 'step',
            'question_type' => null,
        ], null);
        $this->createSavedTestWithQuestion('polyglot-fixture-other-course', 1, ['course_slug' => 'polyglot-english-a2']);
        $this->createSavedTestWithQuestion('polyglot-fixture-other-case', 1, ['course_slug' => ' POLYGLOT-ENGLISH-A1 ']);
        $this->createSavedTestWithQuestion('polyglot-fixture-no-course', 1, ['course_slug' => null]);

        [$manifest, $linkQueries] = $this->manifestWithLinkQueries('sentence-builder-english-a1');

        $this->assertSame([$empty->slug, $linked->slug], array_column($manifest['lessons'], 'slug'));
        $this->assertSame([0, 1], array_column($manifest['lessons'], 'question_count'));
        $this->assertCount(1, $linkQueries, 'Eligibility and normalization must share a single batch of links.');
        $this->assertSame(1, preg_match('/"saved_grammar_test_id" in \(([0-9, ]+)\)/i', $linkQueries[0], $match));
        $this->assertEqualsCanonicalizing(
            [$linked->id, $empty->id, $ineligible->id],
            array_map('intval', explode(',', $match[1])),
            'Even candidates later rejected by eligibility belong to this batch; other courses do not.'
        );
        $this->assertMatchesRegularExpression('/order by "position" asc$/i', $linkQueries[0]);
    }

    public function test_empty_and_absent_courses_do_not_read_links_even_when_other_courses_exist(): void
    {
        $this->createSavedTestWithQuestion('polyglot-fixture-other-course', 1);
        $this->createSavedTestWithQuestion('polyglot-fixture-ineligible-other-course', 1, [
            'course_slug' => 'polyglot-english-a2',
            'mode' => 'step',
            'question_type' => null,
        ], null);

        foreach (['   ' => '', 'missing-course' => 'missing-course'] as $input => $slug) {
            [$manifest, $linkQueries] = $this->manifestWithLinkQueries($input);
            $this->assertSame($slug, $manifest['course']['slug']);
            $this->assertSame([], $manifest['lessons']);
            $this->assertSame([], $manifest['by_slug']);
            $this->assertNull($manifest['first_lesson']);
            $this->assertSame(0, $manifest['total_lessons']);
            $this->assertSame([], $linkQueries);
        }

        [$ineligible, $linkQueries] = $this->manifestWithLinkQueries('sentence-builder-english-a2');
        $this->assertSame('polyglot-english-a2', $ineligible['course']['slug']);
        $this->assertSame(0, $ineligible['total_lessons']);
        $this->assertCount(1, $linkQueries, 'Candidates with unknown eligibility still require their links.');
    }

    public function test_manifest_preserves_tie_order_link_counts_navigation_and_localized_aliases(): void
    {
        // The production migration permits duplicate pairs. The generic fixture helper adds a constraint.
        Schema::table('saved_grammar_test_questions', static function (Blueprint $table): void {
            $table->dropUnique('saved_grammar_test_questions_unique');
        });
        (require app_path('Modules/LanguageManager/database/migrations/2025_12_23_000001_create_languages_table.php'))->up();
        foreach (['uk', 'en', 'pl'] as $locale) {
            Language::query()->create([
                'code' => $locale,
                'name' => $locale,
                'native_name' => $locale,
                'is_default' => $locale === 'uk',
            ]);
        }
        LocaleService::clearCache();

        $second = $this->createSavedTestWithQuestion('polyglot-fixture-z', 2, [
            'previous_lesson_slug' => 'polyglot-fixture-a',
        ]);
        $uuid = $second->questionLinks()->value('question_uuid');
        $second->questionLinks()->create(['question_uuid' => $uuid, 'position' => 10]);
        $second->questionLinks()->create(['question_uuid' => (string) Str::uuid(), 'position' => 5]);
        $first = $this->createSavedTest('polyglot-fixture-a', 2, [
            'previous_lesson_slug' => null,
            'next_lesson_slug' => 'polyglot-fixture-z',
            'completion' => '{"rolling_window":75,"min_rating":4.75}',
        ]);
        $first->update(['name' => 'Polyglot: fixture name', 'description' => ' Course description ']);
        $service = app(PolyglotCourseManifestService::class);

        try {
            foreach (['uk' => '', 'en' => '/en', 'pl' => '/pl'] as $locale => $prefix) {
                app()->setLocale($locale);
                $manifest = $service->build('polyglot-english-a1');
                $this->assertSame($manifest, $service->build('sentence-builder-english-a1'));
                $this->assertSame([$first->slug, $second->slug], array_keys($manifest['by_slug']));
                $this->assertSame([2, 2], array_column($manifest['lessons'], 'lesson_order'));
                $this->assertSame([0, 3], array_column($manifest['lessons'], 'question_count'));
                $this->assertSame($manifest['lessons'][0], $manifest['first_lesson']);
                $this->assertSame($manifest['lessons'][0], $service->findLesson($manifest, 'sentence-builder-fixture-a'));
                $this->assertSame($manifest['lessons'][0], $service->previousLesson($manifest, 'sentence-builder-fixture-z'));
                $this->assertSame($manifest['lessons'][1], $service->nextLesson($manifest, $first->slug));
                $this->assertSame('Sentence Builder: fixture name', $manifest['first_lesson']['name']);
                $this->assertSame('Course description', $manifest['course']['description']);
                $this->assertSame('A1', $manifest['course']['level']);
                $this->assertSame(['rolling_window' => 75, 'min_rating' => 4.75], $manifest['first_lesson']['completion']);
                $this->assertSame($prefix.'/courses/sentence-builder-english-a1', parse_url($manifest['course']['compose_url'], PHP_URL_PATH));
                $this->assertSame($prefix.'/test/sentence-builder-fixture-a/step/compose', parse_url($manifest['first_lesson']['compose_url'], PHP_URL_PATH));
            }
        } finally {
            LocaleService::clearCache();
        }
    }

    private function manifestWithLinkQueries(string $course): array
    {
        $connection = DB::connection();
        $connection->flushQueryLog();
        $connection->enableQueryLog();

        try {
            $manifest = app(PolyglotCourseManifestService::class)->build($course);
            $queries = array_column($connection->getRawQueryLog(), 'raw_query');

            return [$manifest, array_values(array_filter($queries, static fn (string $query): bool =>
                str_contains(strtolower($query), 'from "saved_grammar_test_questions"')))];
        } finally {
            $connection->disableQueryLog();
            $connection->flushQueryLog();
        }
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

        $test = $this->createSavedTest($slug, $lessonOrder, $filters);
        $test->questionLinks()->create([
            'question_uuid' => $question->uuid,
            'position' => 0,
        ]);

        return $test;
    }

    private function createSavedTest(string $slug, int $lessonOrder, array $filters = []): SavedGrammarTest
    {
        return SavedGrammarTest::query()->create([
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
    }
}
