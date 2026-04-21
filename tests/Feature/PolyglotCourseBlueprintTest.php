<?php

namespace Tests\Feature;

use App\Services\PolyglotCourseBlueprintService;
use Database\Seeders\V2\Polyglot\PolyglotCanCannotLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotArticlesAAnTheLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotFutureSimpleWillLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotHaveGotHasGotLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotPastSimpleIrregularVerbsLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotSomeAnyLessonSeeder;
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
    }

    public function test_blueprint_file_loads_with_unique_lesson_orders_and_slugs(): void
    {
        $blueprint = app(PolyglotCourseBlueprintService::class)
            ->getCourseBlueprint('polyglot-english-a1');
        $lessonTwelve = collect($blueprint['lessons'])
            ->firstWhere('slug', 'polyglot-some-any-a1');

        $orders = array_column($blueprint['lessons'], 'lesson_order');
        $slugs = array_column($blueprint['lessons'], 'slug');

        $this->assertSame(16, $blueprint['total_planned_lessons']);
        $this->assertCount(16, $orders);
        $this->assertCount(16, array_unique($orders));
        $this->assertCount(16, $slugs);
        $this->assertCount(16, array_unique($slugs));
        $this->assertIsArray($lessonTwelve);
        $this->assertSame(12, $lessonTwelve['lesson_order']);
        $this->assertSame('implemented', $lessonTwelve['status']);
        $this->assertSame('polyglot-articles-a-an-the-a1', $lessonTwelve['previous_lesson_slug']);
        $this->assertSame('polyglot-much-many-a-lot-of-a1', $lessonTwelve['next_lesson_slug']);
        $this->assertSame('some-any', $lessonTwelve['theory_category_slug']);
        $this->assertSame('theory-some-any-things', $lessonTwelve['theory_page_slug']);
    }

    public function test_blueprint_status_layer_reports_implemented_and_next_planned_lessons(): void
    {
        $status = app(PolyglotCourseBlueprintService::class)
            ->buildCourseStatus('polyglot-english-a1');

        $this->assertSame(16, $status['counts']['planned_total']);
        $this->assertSame(12, $status['counts']['implemented_total']);
        $this->assertSame(4, $status['counts']['planned_only_total']);
        $this->assertSame('polyglot-much-many-a-lot-of-a1', $status['next_planned_lesson']['slug'] ?? null);
        $this->assertSame([], $status['missing_lessons']);
        $this->assertSame([], $status['validation']['broken_previous_refs']);
        $this->assertSame([], $status['validation']['broken_next_refs']);
    }
}
