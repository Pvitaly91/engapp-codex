<?php

namespace Tests\Feature;

use App\Services\PolyglotCourseBlueprintService;
use Database\Seeders\V2\Polyglot\PolyglotCanCannotLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotArticlesAAnTheLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotBeGoingToLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotComparativesLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotFinalDrillLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotFirstConditionalLessonSeeder;
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
use Database\Seeders\V2\Polyglot\PolyglotPresentPerfectBasicLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotPresentPerfectContinuousBasicsLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotPresentPerfectContinuousVsPresentPerfectLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotPastPerfectBasicsLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotNarrativeTensesBasicsLessonSeeder;
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

class PolyglotCourseReportCommandTest extends TestCase
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
    }

    public function test_course_report_command_outputs_planned_and_implemented_status(): void
    {
        $this->artisan('polyglot:course-report', [
            'courseSlug' => 'polyglot-english-a1',
        ])
            ->expectsOutputToContain('Planned total: 16')
            ->expectsOutputToContain('Implemented total: 16')
            ->expectsOutputToContain('Missing / planned lessons: none')
            ->expectsOutputToContain('Next recommended lesson: none')
            ->expectsOutputToContain('/test/polyglot-final-drill-a1/step/compose')
            ->expectsOutputToContain('Broken previous/next refs: none')
            ->assertExitCode(0);
    }

    public function test_a2_course_report_command_outputs_planned_and_implemented_status(): void
    {
        $this->artisan('polyglot:course-report', [
            'courseSlug' => 'polyglot-english-a2',
        ])
            ->expectsOutputToContain('Planned total: 16')
            ->expectsOutputToContain('Implemented total: 16')
            ->expectsOutputToContain('polyglot-present-perfect-basic-a2')
            ->expectsOutputToContain('polyglot-present-perfect-vs-past-simple-a2')
            ->expectsOutputToContain('polyglot-first-conditional-a2')
            ->expectsOutputToContain('polyglot-be-going-to-a2')
            ->expectsOutputToContain('polyglot-should-ought-to-a2')
            ->expectsOutputToContain('polyglot-must-have-to-a2')
            ->expectsOutputToContain('polyglot-gerund-vs-infinitive-a2')
            ->expectsOutputToContain('polyglot-past-continuous-a2')
            ->expectsOutputToContain('polyglot-present-perfect-time-expressions-a2')
            ->expectsOutputToContain('polyglot-relative-clauses-a2')
            ->expectsOutputToContain('polyglot-passive-voice-basics-a2')
            ->expectsOutputToContain('polyglot-reported-speech-basics-a2')
            ->expectsOutputToContain('polyglot-used-to-a2')
            ->expectsOutputToContain('polyglot-question-tags-basics-a2')
            ->expectsOutputToContain('polyglot-second-conditional-basics-a2')
            ->expectsOutputToContain('polyglot-final-drill-a2')
            ->expectsOutputToContain('Missing / planned lessons: none')
            ->expectsOutputToContain('Next recommended lesson: none')
            ->expectsOutputToContain('Broken previous/next refs: none')
            ->assertExitCode(0);
    }

    public function test_b1_course_report_command_outputs_planned_and_implemented_status(): void
    {
        $status = app(PolyglotCourseBlueprintService::class)
            ->buildCourseStatus('polyglot-english-b1');

        $this->assertSame('polyglot-future-continuous-basics-b1', $status['next_planned_lesson']['slug'] ?? null);

        $this->artisan('polyglot:course-report', [
            'courseSlug' => 'polyglot-english-b1',
        ])
            ->expectsOutputToContain('Planned total: 16')
            ->expectsOutputToContain('Implemented total: 4')
            ->expectsOutputToContain('polyglot-present-perfect-continuous-basics-b1')
            ->expectsOutputToContain('polyglot-present-perfect-continuous-vs-present-perfect-b1')
            ->expectsOutputToContain('polyglot-past-perfect-basics-b1')
            ->expectsOutputToContain('polyglot-narrative-tenses-basics-b1')
            ->expectsOutputToContain('polyglot-future-continuous-basics-b1')
            ->expectsOutputToContain('Broken previous/next refs: none')
            ->assertExitCode(0);
    }
}
