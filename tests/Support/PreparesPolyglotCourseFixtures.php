<?php

namespace Tests\Support;

use Database\Seeders\V2\Polyglot\PolyglotArticlesAAnTheLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotBeGoingToLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotCanCannotLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotComparativesLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotFinalDrillA2LessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotFinalDrillLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotFirstConditionalLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotFutureContinuousBasicsLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotFuturePerfectBasicsLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotFutureSimpleWillLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotGerundVsInfinitiveLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotHaveGotHasGotLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotMuchManyALotOfLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotMustHaveToLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotNarrativeTensesBasicsLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotPassiveVoiceBasicsLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotPassiveVoiceWithModalsLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotPastContinuousLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotPastPerfectBasicsLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotPastSimpleIrregularVerbsLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotPastSimpleRegularVerbsLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotPastSimpleToBeLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotPresentContinuousLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotPresentPerfectBasicLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotPresentPerfectContinuousBasicsLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotPresentPerfectContinuousVsPresentPerfectLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotPresentPerfectTimeExpressionsLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotPresentPerfectVsPastSimpleLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotPresentSimpleVerbsLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotQuestionTagsBasicsLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotRelativeClausesLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotReportedCommandsAndRequestsLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotReportedQuestionsLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotReportedSpeechBasicsLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotSecondConditionalBasicsLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotShouldOughtToLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotSomeAnyLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotSuperlativesLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotThereIsThereAreLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotToBeLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotUsedToLessonSeeder;

/** Real V2 integration data; rebuilt once, restored independently for every method. */
trait PreparesPolyglotCourseFixtures
{
    use RebuildsComposeTestSchema;

    protected function preparePolyglotCourseFixtures(): void
    {
        CourseFixtureSnapshot::restore(function (): void {
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
        });
    }
}
