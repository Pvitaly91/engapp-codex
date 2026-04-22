<?php

namespace Tests\Feature;

use App\Services\PolyglotCourseBlueprintService;
use Database\Seeders\V2\Polyglot\PolyglotCanCannotLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotArticlesAAnTheLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotBeGoingToLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotComparativesLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotFinalDrillLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotShouldOughtToLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotFutureSimpleWillLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotHaveGotHasGotLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotFirstConditionalLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotPresentPerfectBasicLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotPresentPerfectVsPastSimpleLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotSuperlativesLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotPastSimpleIrregularVerbsLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotSomeAnyLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotMuchManyALotOfLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotPresentContinuousLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotPastSimpleRegularVerbsLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotPastSimpleToBeLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotPresentSimpleVerbsLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotThereIsThereAreLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotToBeLessonSeeder;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

class PolyglotCourseBlueprintTest extends TestCase
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
        $this->seed(PolyglotToBeLessonSeeder::class);
        $this->seed(PolyglotThereIsThereAreLessonSeeder::class);
        $this->seed(PolyglotHaveGotHasGotLessonSeeder::class);
        $this->seed(PolyglotPresentSimpleVerbsLessonSeeder::class);
        $this->seed(PolyglotCanCannotLessonSeeder::class);
        $this->seed(PolyglotPresentContinuousLessonSeeder::class);
        $this->seed(PolyglotPastSimpleToBeLessonSeeder::class);
        $this->seed(PolyglotPastSimpleRegularVerbsLessonSeeder::class);
        $this->seed(PolyglotPastSimpleIrregularVerbsLessonSeeder::class);
        $this->seed(PolyglotFutureSimpleWillLessonSeeder::class);
        $this->seed(PolyglotArticlesAAnTheLessonSeeder::class);
        $this->seed(PolyglotSomeAnyLessonSeeder::class);
        $this->seed(PolyglotMuchManyALotOfLessonSeeder::class);
        $this->seed(PolyglotComparativesLessonSeeder::class);
        $this->seed(PolyglotSuperlativesLessonSeeder::class);
        $this->seed(PolyglotFinalDrillLessonSeeder::class);
        $this->seed(PolyglotPresentPerfectBasicLessonSeeder::class);
        $this->seed(PolyglotPresentPerfectVsPastSimpleLessonSeeder::class);
        $this->seed(PolyglotFirstConditionalLessonSeeder::class);
        $this->seed(PolyglotBeGoingToLessonSeeder::class);
        $this->seed(PolyglotShouldOughtToLessonSeeder::class);
    }

    public function test_blueprint_file_loads_with_unique_lesson_orders_and_slugs(): void
    {
        $blueprint = app(PolyglotCourseBlueprintService::class)
            ->getCourseBlueprint('polyglot-english-a1');
        $lessonSixteen = collect($blueprint['lessons'])
            ->firstWhere('slug', 'polyglot-final-drill-a1');

        $orders = array_column($blueprint['lessons'], 'lesson_order');
        $slugs = array_column($blueprint['lessons'], 'slug');

        $this->assertSame(16, $blueprint['total_planned_lessons']);
        $this->assertCount(16, $orders);
        $this->assertCount(16, array_unique($orders));
        $this->assertCount(16, $slugs);
        $this->assertCount(16, array_unique($slugs));
        $this->assertIsArray($lessonSixteen);
        $this->assertSame(16, $lessonSixteen['lesson_order']);
        $this->assertSame('implemented', $lessonSixteen['status']);
        $this->assertSame('polyglot-superlatives-a1', $lessonSixteen['previous_lesson_slug']);
        $this->assertNull($lessonSixteen['next_lesson_slug']);
        $this->assertSame('basic-grammar', $lessonSixteen['theory_category_slug']);
        $this->assertSame('a1-mixed-revision', $lessonSixteen['theory_page_slug']);
    }

    public function test_blueprint_status_layer_reports_implemented_and_next_planned_lessons(): void
    {
        $status = app(PolyglotCourseBlueprintService::class)
            ->buildCourseStatus('polyglot-english-a1');

        $this->assertSame(16, $status['counts']['planned_total']);
        $this->assertSame(16, $status['counts']['implemented_total']);
        $this->assertSame(0, $status['counts']['planned_only_total']);
        $this->assertNull($status['next_planned_lesson']['slug'] ?? null);
        $this->assertSame([], $status['missing_lessons']);
        $this->assertSame([], $status['validation']['broken_previous_refs']);
        $this->assertSame([], $status['validation']['broken_next_refs']);
    }

    public function test_a2_blueprint_file_loads_with_unique_lesson_orders_and_slugs(): void
    {
        $blueprint = app(PolyglotCourseBlueprintService::class)
            ->getCourseBlueprint('polyglot-english-a2');
        $lessonOne = collect($blueprint['lessons'])
            ->firstWhere('slug', 'polyglot-present-perfect-basic-a2');
        $lessonTwo = collect($blueprint['lessons'])
            ->firstWhere('slug', 'polyglot-present-perfect-vs-past-simple-a2');
        $lessonThree = collect($blueprint['lessons'])
            ->firstWhere('slug', 'polyglot-first-conditional-a2');
        $lessonFour = collect($blueprint['lessons'])
            ->firstWhere('slug', 'polyglot-be-going-to-a2');
        $lessonFive = collect($blueprint['lessons'])
            ->firstWhere('slug', 'polyglot-should-ought-to-a2');
        $plannedLessons = collect($blueprint['lessons'])
            ->where('status', 'planned')
            ->values();

        $orders = array_column($blueprint['lessons'], 'lesson_order');
        $slugs = array_column($blueprint['lessons'], 'slug');

        $this->assertSame(16, $blueprint['total_planned_lessons']);
        $this->assertCount(16, $orders);
        $this->assertCount(16, array_unique($orders));
        $this->assertCount(16, $slugs);
        $this->assertCount(16, array_unique($slugs));
        $this->assertIsArray($lessonOne);
        $this->assertSame(1, $lessonOne['lesson_order']);
        $this->assertSame('implemented', $lessonOne['status']);
        $this->assertNull($lessonOne['previous_lesson_slug']);
        $this->assertSame('polyglot-present-perfect-vs-past-simple-a2', $lessonOne['next_lesson_slug']);
        $this->assertSame('present-perfect', $lessonOne['theory_category_slug']);
        $this->assertSame('present-perfect-forms', $lessonOne['theory_page_slug']);
        $this->assertIsArray($lessonTwo);
        $this->assertSame(2, $lessonTwo['lesson_order']);
        $this->assertSame('implemented', $lessonTwo['status']);
        $this->assertSame('polyglot-present-perfect-basic-a2', $lessonTwo['previous_lesson_slug']);
        $this->assertSame('polyglot-first-conditional-a2', $lessonTwo['next_lesson_slug']);
        $this->assertSame('tenses', $lessonTwo['theory_category_slug']);
        $this->assertSame('present-perfect-vs-past-simple', $lessonTwo['theory_page_slug']);
        $this->assertIsArray($lessonThree);
        $this->assertSame(3, $lessonThree['lesson_order']);
        $this->assertSame('implemented', $lessonThree['status']);
        $this->assertSame('polyglot-present-perfect-vs-past-simple-a2', $lessonThree['previous_lesson_slug']);
        $this->assertSame('polyglot-be-going-to-a2', $lessonThree['next_lesson_slug']);
        $this->assertSame('conditionals', $lessonThree['theory_category_slug']);
        $this->assertSame('first-conditional', $lessonThree['theory_page_slug']);
        $this->assertIsArray($lessonFour);
        $this->assertSame(4, $lessonFour['lesson_order']);
        $this->assertSame('implemented', $lessonFour['status']);
        $this->assertSame('polyglot-first-conditional-a2', $lessonFour['previous_lesson_slug']);
        $this->assertSame('polyglot-should-ought-to-a2', $lessonFour['next_lesson_slug']);
        $this->assertSame('maibutni-formy', $lessonFour['theory_category_slug']);
        $this->assertSame('will-vs-be-going-to', $lessonFour['theory_page_slug']);
        $this->assertIsArray($lessonFive);
        $this->assertSame(5, $lessonFive['lesson_order']);
        $this->assertSame('implemented', $lessonFive['status']);
        $this->assertSame('polyglot-be-going-to-a2', $lessonFive['previous_lesson_slug']);
        $this->assertSame('polyglot-must-have-to-a2', $lessonFive['next_lesson_slug']);
        $this->assertSame('modal-verbs', $lessonFive['theory_category_slug']);
        $this->assertSame('should-ought-to', $lessonFive['theory_page_slug']);
        $this->assertCount(11, $plannedLessons);
        $this->assertTrue($plannedLessons->every(
            fn (array $lesson) => ($lesson['status'] ?? null) === 'planned'
                && (int) ($lesson['lesson_order'] ?? 0) >= 6
        ));
    }

    public function test_a2_blueprint_status_layer_reports_five_implemented_and_next_planned_lesson(): void
    {
        $status = app(PolyglotCourseBlueprintService::class)
            ->buildCourseStatus('polyglot-english-a2');

        $this->assertSame(16, $status['counts']['planned_total']);
        $this->assertSame(5, $status['counts']['implemented_total']);
        $this->assertSame(11, $status['counts']['planned_only_total']);
        $this->assertSame('polyglot-must-have-to-a2', $status['next_planned_lesson']['slug'] ?? null);
        $this->assertSame([], $status['missing_lessons']);
        $this->assertSame([], $status['validation']['broken_previous_refs']);
        $this->assertSame([], $status['validation']['broken_next_refs']);
    }
}
