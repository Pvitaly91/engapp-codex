<?php

namespace Tests\Feature;

use App\Services\PolyglotCourseBlueprintService;
use Database\Seeders\V2\Polyglot\PolyglotCanCannotLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotArticlesAAnTheLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotBeGoingToLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotComparativesLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotFinalDrillLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotGerundVsInfinitiveLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotMustHaveToLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotPastContinuousLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotPassiveVoiceBasicsLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotFinalDrillA2LessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotPresentPerfectTimeExpressionsLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotQuestionTagsBasicsLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotReportedSpeechBasicsLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotRelativeClausesLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotSecondConditionalBasicsLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotShouldOughtToLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotUsedToLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotFutureSimpleWillLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotHaveGotHasGotLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotFirstConditionalLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotPresentPerfectBasicLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotPresentPerfectContinuousBasicsLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotPresentPerfectContinuousVsPresentPerfectLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotPresentPerfectVsPastSimpleLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotPastPerfectBasicsLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotNarrativeTensesBasicsLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotFutureContinuousBasicsLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotFuturePerfectBasicsLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotPassiveVoiceWithModalsLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotReportedQuestionsLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotReportedCommandsAndRequestsLessonSeeder;
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
        $this->seed(PolyglotMustHaveToLessonSeeder::class);
        $this->seed(PolyglotGerundVsInfinitiveLessonSeeder::class);
        $this->seed(PolyglotPastContinuousLessonSeeder::class);
        $this->seed(PolyglotPresentPerfectTimeExpressionsLessonSeeder::class);
        $this->seed(PolyglotRelativeClausesLessonSeeder::class);
        $this->seed(PolyglotPassiveVoiceBasicsLessonSeeder::class);
        $this->seed(PolyglotReportedSpeechBasicsLessonSeeder::class);
        $this->seed(PolyglotUsedToLessonSeeder::class);
        $this->seed(PolyglotQuestionTagsBasicsLessonSeeder::class);
        $this->seed(PolyglotSecondConditionalBasicsLessonSeeder::class);
        $this->seed(PolyglotFinalDrillA2LessonSeeder::class);
        $this->seed(PolyglotPresentPerfectContinuousBasicsLessonSeeder::class);
        $this->seed(PolyglotPresentPerfectContinuousVsPresentPerfectLessonSeeder::class);
        $this->seed(PolyglotPastPerfectBasicsLessonSeeder::class);
        $this->seed(PolyglotNarrativeTensesBasicsLessonSeeder::class);
        $this->seed(PolyglotFutureContinuousBasicsLessonSeeder::class);
        $this->seed(PolyglotFuturePerfectBasicsLessonSeeder::class);
        $this->seed(PolyglotPassiveVoiceWithModalsLessonSeeder::class);
        $this->seed(PolyglotReportedQuestionsLessonSeeder::class);
        $this->seed(PolyglotReportedCommandsAndRequestsLessonSeeder::class);
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
        $lessonSix = collect($blueprint['lessons'])
            ->firstWhere('slug', 'polyglot-must-have-to-a2');
        $lessonSeven = collect($blueprint['lessons'])
            ->firstWhere('slug', 'polyglot-gerund-vs-infinitive-a2');
        $lessonEight = collect($blueprint['lessons'])
            ->firstWhere('slug', 'polyglot-past-continuous-a2');
        $lessonNine = collect($blueprint['lessons'])
            ->firstWhere('slug', 'polyglot-present-perfect-time-expressions-a2');
        $lessonTen = collect($blueprint['lessons'])
            ->firstWhere('slug', 'polyglot-relative-clauses-a2');
        $lessonEleven = collect($blueprint['lessons'])
            ->firstWhere('slug', 'polyglot-passive-voice-basics-a2');
        $lessonTwelve = collect($blueprint['lessons'])
            ->firstWhere('slug', 'polyglot-reported-speech-basics-a2');
        $lessonThirteen = collect($blueprint['lessons'])
            ->firstWhere('slug', 'polyglot-used-to-a2');
        $lessonFourteen = collect($blueprint['lessons'])
            ->firstWhere('slug', 'polyglot-question-tags-basics-a2');
        $lessonFifteen = collect($blueprint['lessons'])
            ->firstWhere('slug', 'polyglot-second-conditional-basics-a2');
        $lessonSixteen = collect($blueprint['lessons'])
            ->firstWhere('slug', 'polyglot-final-drill-a2');

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
        $this->assertIsArray($lessonSix);
        $this->assertSame(6, $lessonSix['lesson_order']);
        $this->assertSame('implemented', $lessonSix['status']);
        $this->assertSame('polyglot-should-ought-to-a2', $lessonSix['previous_lesson_slug']);
        $this->assertSame('polyglot-gerund-vs-infinitive-a2', $lessonSix['next_lesson_slug']);
        $this->assertSame('modal-verbs', $lessonSix['theory_category_slug']);
        $this->assertSame('must-have-to', $lessonSix['theory_page_slug']);
        $this->assertIsArray($lessonSeven);
        $this->assertSame(7, $lessonSeven['lesson_order']);
        $this->assertSame('implemented', $lessonSeven['status']);
        $this->assertSame('polyglot-must-have-to-a2', $lessonSeven['previous_lesson_slug']);
        $this->assertSame('polyglot-past-continuous-a2', $lessonSeven['next_lesson_slug']);
        $this->assertSame('verb-patterns', $lessonSeven['theory_category_slug']);
        $this->assertSame('gerund-vs-infinitive', $lessonSeven['theory_page_slug']);
        $this->assertIsArray($lessonEight);
        $this->assertSame(8, $lessonEight['lesson_order']);
        $this->assertSame('implemented', $lessonEight['status']);
        $this->assertSame('polyglot-gerund-vs-infinitive-a2', $lessonEight['previous_lesson_slug']);
        $this->assertSame('polyglot-present-perfect-time-expressions-a2', $lessonEight['next_lesson_slug']);
        $this->assertSame('past-continuous', $lessonEight['theory_category_slug']);
        $this->assertSame('past-continuous-forms', $lessonEight['theory_page_slug']);
        $this->assertIsArray($lessonNine);
        $this->assertSame(9, $lessonNine['lesson_order']);
        $this->assertSame('implemented', $lessonNine['status']);
        $this->assertSame('polyglot-past-continuous-a2', $lessonNine['previous_lesson_slug']);
        $this->assertSame('polyglot-relative-clauses-a2', $lessonNine['next_lesson_slug']);
        $this->assertSame('present-perfect', $lessonNine['theory_category_slug']);
        $this->assertSame('present-perfect-time-expressions', $lessonNine['theory_page_slug']);
        $this->assertIsArray($lessonTen);
        $this->assertSame(10, $lessonTen['lesson_order']);
        $this->assertSame('implemented', $lessonTen['status']);
        $this->assertSame('polyglot-present-perfect-time-expressions-a2', $lessonTen['previous_lesson_slug']);
        $this->assertSame('polyglot-passive-voice-basics-a2', $lessonTen['next_lesson_slug']);
        $this->assertSame('clauses-and-linking-words', $lessonTen['theory_category_slug']);
        $this->assertSame('relative-clauses', $lessonTen['theory_page_slug']);
        $this->assertIsArray($lessonEleven);
        $this->assertSame(11, $lessonEleven['lesson_order']);
        $this->assertSame('implemented', $lessonEleven['status']);
        $this->assertSame('polyglot-relative-clauses-a2', $lessonEleven['previous_lesson_slug']);
        $this->assertSame('polyglot-reported-speech-basics-a2', $lessonEleven['next_lesson_slug']);
        $this->assertSame('passive-voice', $lessonEleven['theory_category_slug']);
        $this->assertSame('theory-passive-voice-formation-rules', $lessonEleven['theory_page_slug']);
        $this->assertIsArray($lessonTwelve);
        $this->assertSame(12, $lessonTwelve['lesson_order']);
        $this->assertSame('implemented', $lessonTwelve['status']);
        $this->assertSame('polyglot-passive-voice-basics-a2', $lessonTwelve['previous_lesson_slug']);
        $this->assertSame('polyglot-used-to-a2', $lessonTwelve['next_lesson_slug']);
        $this->assertSame('reported-speech', $lessonTwelve['theory_category_slug']);
        $this->assertSame('reported-statements', $lessonTwelve['theory_page_slug']);
        $this->assertIsArray($lessonThirteen);
        $this->assertSame(13, $lessonThirteen['lesson_order']);
        $this->assertSame('implemented', $lessonThirteen['status']);
        $this->assertSame('polyglot-reported-speech-basics-a2', $lessonThirteen['previous_lesson_slug']);
        $this->assertSame('polyglot-question-tags-basics-a2', $lessonThirteen['next_lesson_slug']);
        $this->assertSame('tenses', $lessonThirteen['theory_category_slug']);
        $this->assertSame('used-to-would', $lessonThirteen['theory_page_slug']);
        $this->assertIsArray($lessonFourteen);
        $this->assertSame(14, $lessonFourteen['lesson_order']);
        $this->assertSame('implemented', $lessonFourteen['status']);
        $this->assertSame('polyglot-used-to-a2', $lessonFourteen['previous_lesson_slug']);
        $this->assertSame('polyglot-second-conditional-basics-a2', $lessonFourteen['next_lesson_slug']);
        $this->assertSame('types-of-questions', $lessonFourteen['theory_category_slug']);
        $this->assertSame('question-tags-disjunctive-questions-dont-you-isnt-it', $lessonFourteen['theory_page_slug']);
        $this->assertIsArray($lessonFifteen);
        $this->assertSame(15, $lessonFifteen['lesson_order']);
        $this->assertSame('implemented', $lessonFifteen['status']);
        $this->assertSame('polyglot-question-tags-basics-a2', $lessonFifteen['previous_lesson_slug']);
        $this->assertSame('polyglot-final-drill-a2', $lessonFifteen['next_lesson_slug']);
        $this->assertSame('conditionals', $lessonFifteen['theory_category_slug']);
        $this->assertSame('second-conditional', $lessonFifteen['theory_page_slug']);
        $this->assertIsArray($lessonSixteen);
        $this->assertSame(16, $lessonSixteen['lesson_order']);
        $this->assertSame('implemented', $lessonSixteen['status']);
        $this->assertSame('polyglot-second-conditional-basics-a2', $lessonSixteen['previous_lesson_slug']);
        $this->assertNull($lessonSixteen['next_lesson_slug']);
        $this->assertSame('basic-grammar', $lessonSixteen['theory_category_slug']);
        $this->assertSame('a2-mixed-revision', $lessonSixteen['theory_page_slug']);
        $this->assertFalse(collect($blueprint['lessons'])->contains(
            fn (array $lesson) => ($lesson['status'] ?? null) === 'planned'
        ));
    }

    public function test_a2_blueprint_status_layer_reports_fully_implemented_course_without_next_planned_lesson(): void
    {
        $status = app(PolyglotCourseBlueprintService::class)
            ->buildCourseStatus('polyglot-english-a2');

        $this->assertSame(16, $status['counts']['planned_total']);
        $this->assertSame(16, $status['counts']['implemented_total']);
        $this->assertSame(0, $status['counts']['planned_only_total']);
        $this->assertNull($status['next_planned_lesson']['slug'] ?? null);
        $this->assertSame([], $status['missing_lessons']);
        $this->assertSame([], $status['validation']['broken_previous_refs']);
        $this->assertSame([], $status['validation']['broken_next_refs']);
    }

    public function test_b1_blueprint_file_loads_with_unique_lesson_orders_and_slugs(): void
    {
        $blueprint = app(PolyglotCourseBlueprintService::class)
            ->getCourseBlueprint('polyglot-english-b1');
        $lessonOne = collect($blueprint['lessons'])
            ->firstWhere('slug', 'polyglot-present-perfect-continuous-basics-b1');
        $lessonTwo = collect($blueprint['lessons'])
            ->firstWhere('slug', 'polyglot-present-perfect-continuous-vs-present-perfect-b1');
        $lessonThree = collect($blueprint['lessons'])
            ->firstWhere('slug', 'polyglot-past-perfect-basics-b1');
        $lessonFour = collect($blueprint['lessons'])
            ->firstWhere('slug', 'polyglot-narrative-tenses-basics-b1');
        $lessonFive = collect($blueprint['lessons'])
            ->firstWhere('slug', 'polyglot-future-continuous-basics-b1');
        $lessonSix = collect($blueprint['lessons'])
            ->firstWhere('slug', 'polyglot-future-perfect-basics-b1');
        $lessonSeven = collect($blueprint['lessons'])
            ->firstWhere('slug', 'polyglot-passive-voice-with-modals-b1');
        $lessonEight = collect($blueprint['lessons'])
            ->firstWhere('slug', 'polyglot-reported-questions-b1');
        $lessonNine = collect($blueprint['lessons'])
            ->firstWhere('slug', 'polyglot-reported-commands-and-requests-b1');
        $lessonSixteen = collect($blueprint['lessons'])
            ->firstWhere('slug', 'polyglot-final-drill-b1');

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
        $this->assertSame('polyglot-present-perfect-continuous-vs-present-perfect-b1', $lessonOne['next_lesson_slug']);
        $this->assertSame('present-perfect-continuous', $lessonOne['theory_category_slug']);
        $this->assertSame('present-perfect-continuous-forms', $lessonOne['theory_page_slug']);
        $this->assertIsArray($lessonTwo);
        $this->assertSame(2, $lessonTwo['lesson_order']);
        $this->assertSame('implemented', $lessonTwo['status']);
        $this->assertSame('polyglot-present-perfect-continuous-basics-b1', $lessonTwo['previous_lesson_slug']);
        $this->assertSame('polyglot-past-perfect-basics-b1', $lessonTwo['next_lesson_slug']);
        $this->assertSame('tenses', $lessonTwo['theory_category_slug']);
        $this->assertSame('present-perfect-vs-present-perfect-continuous', $lessonTwo['theory_page_slug']);
        $this->assertIsArray($lessonThree);
        $this->assertSame(3, $lessonThree['lesson_order']);
        $this->assertSame('implemented', $lessonThree['status']);
        $this->assertSame('polyglot-present-perfect-continuous-vs-present-perfect-b1', $lessonThree['previous_lesson_slug']);
        $this->assertSame('polyglot-narrative-tenses-basics-b1', $lessonThree['next_lesson_slug']);
        $this->assertSame('past-perfect', $lessonThree['theory_category_slug']);
        $this->assertSame('past-perfect-forms', $lessonThree['theory_page_slug']);
        $this->assertIsArray($lessonFour);
        $this->assertSame(4, $lessonFour['lesson_order']);
        $this->assertSame('implemented', $lessonFour['status']);
        $this->assertSame('polyglot-past-perfect-basics-b1', $lessonFour['previous_lesson_slug']);
        $this->assertSame('polyglot-future-continuous-basics-b1', $lessonFour['next_lesson_slug']);
        $this->assertSame('tenses', $lessonFour['theory_category_slug']);
        $this->assertSame('narrative-tenses', $lessonFour['theory_page_slug']);
        $this->assertIsArray($lessonFive);
        $this->assertSame(5, $lessonFive['lesson_order']);
        $this->assertSame('implemented', $lessonFive['status']);
        $this->assertSame('polyglot-narrative-tenses-basics-b1', $lessonFive['previous_lesson_slug']);
        $this->assertSame('polyglot-future-perfect-basics-b1', $lessonFive['next_lesson_slug']);
        $this->assertSame('future-continuous', $lessonFive['theory_category_slug']);
        $this->assertSame('future-continuous-forms', $lessonFive['theory_page_slug']);
        $this->assertIsArray($lessonSix);
        $this->assertSame(6, $lessonSix['lesson_order']);
        $this->assertSame('implemented', $lessonSix['status']);
        $this->assertSame('polyglot-future-continuous-basics-b1', $lessonSix['previous_lesson_slug']);
        $this->assertSame('polyglot-passive-voice-with-modals-b1', $lessonSix['next_lesson_slug']);
        $this->assertSame('future-perfect', $lessonSix['theory_category_slug']);
        $this->assertSame('future-perfect-forms', $lessonSix['theory_page_slug']);
        $this->assertIsArray($lessonSeven);
        $this->assertSame(7, $lessonSeven['lesson_order']);
        $this->assertSame('implemented', $lessonSeven['status']);
        $this->assertSame('polyglot-future-perfect-basics-b1', $lessonSeven['previous_lesson_slug']);
        $this->assertSame('polyglot-reported-questions-b1', $lessonSeven['next_lesson_slug']);
        $this->assertSame('passive-voice', $lessonSeven['theory_category_slug']);
        $this->assertSame('theory-passive-voice-modal-verbs', $lessonSeven['theory_page_slug']);
        $this->assertIsArray($lessonEight);
        $this->assertSame(8, $lessonEight['lesson_order']);
        $this->assertSame('implemented', $lessonEight['status']);
        $this->assertSame('polyglot-passive-voice-with-modals-b1', $lessonEight['previous_lesson_slug']);
        $this->assertSame('polyglot-reported-commands-and-requests-b1', $lessonEight['next_lesson_slug']);
        $this->assertSame('reported-speech', $lessonEight['theory_category_slug']);
        $this->assertSame('reported-questions', $lessonEight['theory_page_slug']);
        $this->assertIsArray($lessonNine);
        $this->assertSame(9, $lessonNine['lesson_order']);
        $this->assertSame('implemented', $lessonNine['status']);
        $this->assertSame('polyglot-reported-questions-b1', $lessonNine['previous_lesson_slug']);
        $this->assertSame('polyglot-non-defining-relative-clauses-b1', $lessonNine['next_lesson_slug']);
        $this->assertSame('reported-speech', $lessonNine['theory_category_slug']);
        $this->assertSame('reported-commands-and-requests', $lessonNine['theory_page_slug']);
        $this->assertIsArray($lessonSixteen);
        $this->assertSame(16, $lessonSixteen['lesson_order']);
        $this->assertSame('planned', $lessonSixteen['status']);
        $this->assertSame('polyglot-linking-words-and-contrast-b1', $lessonSixteen['previous_lesson_slug']);
        $this->assertNull($lessonSixteen['next_lesson_slug']);
        $this->assertFalse(collect($blueprint['lessons'])->contains(
            fn (array $lesson) => ($lesson['lesson_order'] ?? null) <= 9 && ($lesson['status'] ?? null) !== 'implemented'
        ));
        $this->assertFalse(collect($blueprint['lessons'])->contains(
            fn (array $lesson) => ($lesson['lesson_order'] ?? null) >= 10 && ($lesson['status'] ?? null) !== 'planned'
        ));
    }

    public function test_b1_blueprint_status_layer_reports_nine_implemented_lessons_and_next_planned_lesson(): void
    {
        $status = app(PolyglotCourseBlueprintService::class)
            ->buildCourseStatus('polyglot-english-b1');

        $this->assertSame(16, $status['counts']['planned_total']);
        $this->assertSame(9, $status['counts']['implemented_total']);
        $this->assertSame(7, $status['counts']['planned_only_total']);
        $this->assertSame(
            'polyglot-non-defining-relative-clauses-b1',
            $status['next_planned_lesson']['slug'] ?? null
        );
        $this->assertSame([], $status['missing_lessons']);
        $this->assertSame([], $status['validation']['broken_previous_refs']);
        $this->assertSame([], $status['validation']['broken_next_refs']);
    }
}
