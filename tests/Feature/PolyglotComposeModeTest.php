<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Question;
use App\Models\QuestionAnswer;
use App\Models\QuestionOption;
use App\Models\SavedGrammarTest;
use Database\Seeders\V2\Polyglot\PolyglotHaveGotHasGotLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotArticlesAAnTheLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotBeGoingToLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotCanCannotLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotComparativesLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotFinalDrillLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotFutureSimpleWillLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotGerundVsInfinitiveLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotMustHaveToLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotPastContinuousLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotPassiveVoiceBasicsLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotPresentPerfectTimeExpressionsLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotQuestionTagsBasicsLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotReportedSpeechBasicsLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotRelativeClausesLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotSecondConditionalBasicsLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotShouldOughtToLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotUsedToLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotPastSimpleIrregularVerbsLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotFirstConditionalLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotPresentPerfectBasicLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotPresentPerfectVsPastSimpleLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotSuperlativesLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotPresentContinuousLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotPastSimpleRegularVerbsLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotPastSimpleToBeLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotPresentSimpleVerbsLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotSomeAnyLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotMuchManyALotOfLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotThereIsThereAreLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotToBeLessonSeeder;
use Illuminate\Support\Str;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

class PolyglotComposeModeTest extends TestCase
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

    public function test_compose_route_works_for_seeded_polyglot_lesson(): void
    {
        $this->seed(PolyglotToBeLessonSeeder::class);

        $response = $this->get('/test/polyglot-to-be-a1/step/compose');

        $response->assertOk();
        $response->assertSee('window.__INITIAL_JS_TEST_QUESTIONS__', false);
        $response->assertSee('data-action="reset-progress"', false);

        $questionData = $response->viewData('questionData');

        $this->assertIsArray($questionData);
        $this->assertNotEmpty($questionData);
        $this->assertArrayHasKey('correctTokenValues', $questionData[0]);
        $this->assertArrayHasKey('correctTokenIds', $questionData[0]);
        $this->assertArrayHasKey('tokenBank', $questionData[0]);
        $this->assertSame('test-modes.step-compose', $response->viewData('templateView'));
    }

    public function test_compose_route_works_for_generator_driven_have_got_lesson(): void
    {
        $this->seed(PolyglotHaveGotHasGotLessonSeeder::class);

        $response = $this->get('/test/polyglot-have-got-has-got-a1/step/compose');

        $response->assertOk();
        $response->assertSee('window.__INITIAL_JS_TEST_QUESTIONS__', false);

        $questionData = $response->viewData('questionData');

        $this->assertIsArray($questionData);
        $this->assertNotEmpty($questionData);
        $this->assertSame(
            ['I', 'have', 'got', 'a', 'book'],
            $questionData[0]['correctTokenValues']
        );
    }

    public function test_compose_route_works_for_generator_driven_present_simple_verbs_lesson(): void
    {
        $this->seed(PolyglotPresentSimpleVerbsLessonSeeder::class);

        $response = $this->get('/test/polyglot-present-simple-verbs-a1/step/compose');

        $response->assertOk();
        $response->assertSee('window.__INITIAL_JS_TEST_QUESTIONS__', false);

        $questionData = $response->viewData('questionData');

        $this->assertIsArray($questionData);
        $this->assertNotEmpty($questionData);
        $this->assertSame(
            ['I', 'live', 'in', 'London'],
            $questionData[0]['correctTokenValues']
        );
    }

    public function test_compose_route_works_for_generator_driven_can_cannot_lesson(): void
    {
        $this->seed(PolyglotCanCannotLessonSeeder::class);

        $response = $this->get('/test/polyglot-can-cannot-a1/step/compose');

        $response->assertOk();
        $response->assertSee('window.__INITIAL_JS_TEST_QUESTIONS__', false);

        $questionData = $response->viewData('questionData');

        $this->assertIsArray($questionData);
        $this->assertNotEmpty($questionData);
        $this->assertSame(
            ['I', 'can', 'swim'],
            $questionData[0]['correctTokenValues']
        );
    }

    public function test_compose_route_works_for_generator_driven_present_continuous_lesson(): void
    {
        $this->seed(PolyglotPresentContinuousLessonSeeder::class);

        $response = $this->get('/test/polyglot-present-continuous-a1/step/compose');

        $response->assertOk();
        $response->assertSee('window.__INITIAL_JS_TEST_QUESTIONS__', false);

        $questionData = $response->viewData('questionData');

        $this->assertIsArray($questionData);
        $this->assertNotEmpty($questionData);
        $this->assertSame(
            ['I', 'am', 'reading', 'now'],
            $questionData[0]['correctTokenValues']
        );
    }

    public function test_compose_route_works_for_generator_driven_past_simple_to_be_lesson(): void
    {
        $this->seed(PolyglotPastSimpleToBeLessonSeeder::class);

        $response = $this->get('/test/polyglot-past-simple-to-be-a1/step/compose');

        $response->assertOk();
        $response->assertSee('window.__INITIAL_JS_TEST_QUESTIONS__', false);

        $questionData = $response->viewData('questionData');

        $this->assertIsArray($questionData);
        $this->assertNotEmpty($questionData);
        $this->assertSame(
            ['I', 'was', 'at', 'home', 'yesterday'],
            $questionData[0]['correctTokenValues']
        );
    }

    public function test_compose_route_works_for_generator_driven_past_simple_regular_verbs_lesson(): void
    {
        $this->seed(PolyglotPastSimpleRegularVerbsLessonSeeder::class);

        $response = $this->get('/test/polyglot-past-simple-regular-verbs-a1/step/compose');

        $response->assertOk();
        $response->assertSee('window.__INITIAL_JS_TEST_QUESTIONS__', false);

        $questionData = $response->viewData('questionData');

        $this->assertIsArray($questionData);
        $this->assertNotEmpty($questionData);
        $this->assertSame(
            ['I', 'worked', 'yesterday'],
            $questionData[0]['correctTokenValues']
        );
    }

    public function test_compose_route_works_for_generator_driven_past_simple_irregular_verbs_lesson(): void
    {
        $this->seed(PolyglotPastSimpleIrregularVerbsLessonSeeder::class);

        $response = $this->get('/test/polyglot-past-simple-irregular-verbs-a1/step/compose');

        $response->assertOk();
        $response->assertSee('window.__INITIAL_JS_TEST_QUESTIONS__', false);

        $questionData = $response->viewData('questionData');

        $this->assertIsArray($questionData);
        $this->assertNotEmpty($questionData);
        $this->assertSame(
            ['I', 'went', 'home', 'yesterday'],
            $questionData[0]['correctTokenValues']
        );
    }

    public function test_compose_route_works_for_generator_driven_future_simple_will_lesson(): void
    {
        $this->seed(PolyglotFutureSimpleWillLessonSeeder::class);

        $response = $this->get('/test/polyglot-future-simple-will-a1/step/compose');

        $response->assertOk();
        $response->assertSee('window.__INITIAL_JS_TEST_QUESTIONS__', false);

        $questionData = $response->viewData('questionData');

        $this->assertIsArray($questionData);
        $this->assertNotEmpty($questionData);
        $this->assertSame(
            ['I', 'will', 'come', 'tomorrow'],
            $questionData[0]['correctTokenValues']
        );
    }

    public function test_compose_route_works_for_generator_driven_articles_a_an_the_lesson(): void
    {
        $this->seed(PolyglotArticlesAAnTheLessonSeeder::class);

        $response = $this->get('/test/polyglot-articles-a-an-the-a1/step/compose');

        $response->assertOk();
        $response->assertSee('window.__INITIAL_JS_TEST_QUESTIONS__', false);

        $questionData = $response->viewData('questionData');

        $this->assertIsArray($questionData);
        $this->assertNotEmpty($questionData);
        $this->assertSame(
            ['It', 'is', 'a', 'book'],
            $questionData[0]['correctTokenValues']
        );
    }

    public function test_compose_route_works_for_generator_driven_some_any_lesson(): void
    {
        $this->seed(PolyglotSomeAnyLessonSeeder::class);

        $response = $this->get('/test/polyglot-some-any-a1/step/compose');

        $response->assertOk();
        $response->assertSee('window.__INITIAL_JS_TEST_QUESTIONS__', false);

        $questionData = $response->viewData('questionData');

        $this->assertIsArray($questionData);
        $this->assertNotEmpty($questionData);
        $this->assertSame(
            ['There', 'is', 'some', 'water', 'on', 'the', 'table'],
            $questionData[0]['correctTokenValues']
        );
    }

    public function test_compose_route_works_for_generator_driven_much_many_a_lot_of_lesson(): void
    {
        $this->seed(PolyglotMuchManyALotOfLessonSeeder::class);

        $response = $this->get('/test/polyglot-much-many-a-lot-of-a1/step/compose');

        $response->assertOk();
        $response->assertSee('window.__INITIAL_JS_TEST_QUESTIONS__', false);

        $questionData = $response->viewData('questionData');

        $this->assertIsArray($questionData);
        $this->assertNotEmpty($questionData);
        $this->assertSame(
            ['I', 'have', 'a', 'lot', 'of', 'books'],
            $questionData[0]['correctTokenValues']
        );
    }

    public function test_compose_route_works_for_generator_driven_comparatives_lesson(): void
    {
        $this->seed(PolyglotComparativesLessonSeeder::class);

        $response = $this->get('/test/polyglot-comparatives-a1/step/compose');

        $response->assertOk();
        $response->assertSee('window.__INITIAL_JS_TEST_QUESTIONS__', false);

        $questionData = $response->viewData('questionData');

        $this->assertIsArray($questionData);
        $this->assertNotEmpty($questionData);
        $this->assertSame(
            ['This', 'house', 'is', 'bigger', 'than', 'that', 'house'],
            $questionData[0]['correctTokenValues']
        );
    }

    public function test_compose_route_works_for_generator_driven_superlatives_lesson(): void
    {
        $this->seed(PolyglotSuperlativesLessonSeeder::class);

        $response = $this->get('/test/polyglot-superlatives-a1/step/compose');

        $response->assertOk();
        $response->assertSee('window.__INITIAL_JS_TEST_QUESTIONS__', false);

        $questionData = $response->viewData('questionData');

        $this->assertIsArray($questionData);
        $this->assertNotEmpty($questionData);
        $this->assertSame(
            ['This', 'house', 'is', 'the', 'biggest'],
            $questionData[0]['correctTokenValues']
        );
    }

    public function test_compose_route_works_for_generator_driven_final_drill_lesson(): void
    {
        $this->seed(PolyglotFinalDrillLessonSeeder::class);

        $response = $this->get('/test/polyglot-final-drill-a1/step/compose');

        $response->assertOk();
        $response->assertSee('window.__INITIAL_JS_TEST_QUESTIONS__', false);

        $questionData = $response->viewData('questionData');

        $this->assertIsArray($questionData);
        $this->assertNotEmpty($questionData);
        $this->assertSame(
            ['It', 'is', 'an', 'apple'],
            $questionData[0]['correctTokenValues']
        );
    }

    public function test_compose_route_works_for_generator_driven_present_perfect_basic_a2_lesson(): void
    {
        $this->seed(PolyglotPresentPerfectBasicLessonSeeder::class);

        $response = $this->get('/test/polyglot-present-perfect-basic-a2/step/compose');

        $response->assertOk();
        $response->assertSee('window.__INITIAL_JS_TEST_QUESTIONS__', false);

        $questionData = $response->viewData('questionData');
        $courseContext = $response->viewData('courseContext');

        $this->assertIsArray($questionData);
        $this->assertNotEmpty($questionData);
        $this->assertSame(
            ['I', 'have', 'already', 'finished', 'work'],
            $questionData[0]['correctTokenValues']
        );
        $this->assertIsArray($courseContext);
        $this->assertSame('polyglot-english-a2', $courseContext['course_slug']);
    }

    public function test_compose_route_works_for_generator_driven_present_perfect_vs_past_simple_a2_lesson(): void
    {
        $this->seed(PolyglotPresentPerfectBasicLessonSeeder::class);
        $this->seed(PolyglotPresentPerfectVsPastSimpleLessonSeeder::class);

        $response = $this->get('/test/polyglot-present-perfect-vs-past-simple-a2/step/compose');

        $response->assertOk();
        $response->assertSee('window.__INITIAL_JS_TEST_QUESTIONS__', false);

        $questionData = $response->viewData('questionData');
        $courseContext = $response->viewData('courseContext');

        $this->assertIsArray($questionData);
        $this->assertNotEmpty($questionData);
        $this->assertSame(
            ['I', 'have', 'already', 'finished', 'my', 'homework'],
            $questionData[0]['correctTokenValues']
        );
        $this->assertIsArray($courseContext);
        $this->assertSame('polyglot-english-a2', $courseContext['course_slug']);
    }

    public function test_compose_route_works_for_generator_driven_first_conditional_a2_lesson(): void
    {
        $this->seed(PolyglotPresentPerfectBasicLessonSeeder::class);
        $this->seed(PolyglotPresentPerfectVsPastSimpleLessonSeeder::class);
        $this->seed(PolyglotFirstConditionalLessonSeeder::class);

        $response = $this->get('/test/polyglot-first-conditional-a2/step/compose');

        $response->assertOk();
        $response->assertSee('window.__INITIAL_JS_TEST_QUESTIONS__', false);

        $questionData = $response->viewData('questionData');
        $courseContext = $response->viewData('courseContext');

        $this->assertIsArray($questionData);
        $this->assertNotEmpty($questionData);
        $this->assertSame(
            ['I', 'will', 'stay', 'at', 'home', 'if', 'it', 'rains'],
            $questionData[0]['correctTokenValues']
        );
        $this->assertIsArray($courseContext);
        $this->assertSame('polyglot-english-a2', $courseContext['course_slug']);
        $this->assertSame('polyglot-be-going-to-a2', $courseContext['next_lesson_slug']);
    }

    public function test_compose_route_works_for_generator_driven_be_going_to_a2_lesson(): void
    {
        $this->seed(PolyglotPresentPerfectBasicLessonSeeder::class);
        $this->seed(PolyglotPresentPerfectVsPastSimpleLessonSeeder::class);
        $this->seed(PolyglotFirstConditionalLessonSeeder::class);
        $this->seed(PolyglotBeGoingToLessonSeeder::class);

        $response = $this->get('/test/polyglot-be-going-to-a2/step/compose');

        $response->assertOk();
        $response->assertSee('window.__INITIAL_JS_TEST_QUESTIONS__', false);

        $questionData = $response->viewData('questionData');
        $courseContext = $response->viewData('courseContext');

        $this->assertIsArray($questionData);
        $this->assertNotEmpty($questionData);
        $this->assertSame(
            ['I', 'am', 'going', 'to', 'visit', 'my', 'aunt', 'tomorrow'],
            $questionData[0]['correctTokenValues']
        );
        $this->assertIsArray($courseContext);
        $this->assertSame('polyglot-english-a2', $courseContext['course_slug']);
        $this->assertSame('polyglot-should-ought-to-a2', $courseContext['next_lesson_slug']);
    }

    public function test_compose_route_works_for_generator_driven_should_ought_to_a2_lesson(): void
    {
        $this->seed(PolyglotPresentPerfectBasicLessonSeeder::class);
        $this->seed(PolyglotPresentPerfectVsPastSimpleLessonSeeder::class);
        $this->seed(PolyglotFirstConditionalLessonSeeder::class);
        $this->seed(PolyglotBeGoingToLessonSeeder::class);
        $this->seed(PolyglotShouldOughtToLessonSeeder::class);

        $response = $this->get('/test/polyglot-should-ought-to-a2/step/compose');

        $response->assertOk();
        $response->assertSee('window.__INITIAL_JS_TEST_QUESTIONS__', false);

        $questionData = $response->viewData('questionData');
        $courseContext = $response->viewData('courseContext');

        $this->assertIsArray($questionData);
        $this->assertNotEmpty($questionData);
        $this->assertSame(
            ['You', 'should', 'see', 'a', 'doctor'],
            $questionData[0]['correctTokenValues']
        );
        $this->assertIsArray($courseContext);
        $this->assertSame('polyglot-english-a2', $courseContext['course_slug']);
        $this->assertSame('polyglot-must-have-to-a2', $courseContext['next_lesson_slug']);
    }

    public function test_compose_route_works_for_generator_driven_must_have_to_a2_lesson(): void
    {
        $this->seed(PolyglotPresentPerfectBasicLessonSeeder::class);
        $this->seed(PolyglotPresentPerfectVsPastSimpleLessonSeeder::class);
        $this->seed(PolyglotFirstConditionalLessonSeeder::class);
        $this->seed(PolyglotBeGoingToLessonSeeder::class);
        $this->seed(PolyglotShouldOughtToLessonSeeder::class);
        $this->seed(PolyglotMustHaveToLessonSeeder::class);

        $response = $this->get('/test/polyglot-must-have-to-a2/step/compose');

        $response->assertOk();
        $response->assertSee('window.__INITIAL_JS_TEST_QUESTIONS__', false);

        $questionData = $response->viewData('questionData');
        $courseContext = $response->viewData('courseContext');

        $this->assertIsArray($questionData);
        $this->assertNotEmpty($questionData);
        $this->assertSame(
            ['You', 'must', 'wear', 'a', 'helmet', 'on', 'this', 'road'],
            $questionData[0]['correctTokenValues']
        );
        $this->assertIsArray($courseContext);
        $this->assertSame('polyglot-english-a2', $courseContext['course_slug']);
        $this->assertSame('polyglot-gerund-vs-infinitive-a2', $courseContext['next_lesson_slug']);
    }

    public function test_compose_route_works_for_generator_driven_gerund_vs_infinitive_a2_lesson(): void
    {
        $this->seed(PolyglotPresentPerfectBasicLessonSeeder::class);
        $this->seed(PolyglotPresentPerfectVsPastSimpleLessonSeeder::class);
        $this->seed(PolyglotFirstConditionalLessonSeeder::class);
        $this->seed(PolyglotBeGoingToLessonSeeder::class);
        $this->seed(PolyglotShouldOughtToLessonSeeder::class);
        $this->seed(PolyglotMustHaveToLessonSeeder::class);
        $this->seed(PolyglotGerundVsInfinitiveLessonSeeder::class);

        $response = $this->get('/test/polyglot-gerund-vs-infinitive-a2/step/compose');

        $response->assertOk();
        $response->assertSee('window.__INITIAL_JS_TEST_QUESTIONS__', false);

        $questionData = $response->viewData('questionData');
        $courseContext = $response->viewData('courseContext');

        $this->assertIsArray($questionData);
        $this->assertNotEmpty($questionData);
        $this->assertSame(
            ['I', 'enjoy', 'reading', 'in', 'the', 'evening'],
            $questionData[0]['correctTokenValues']
        );
        $this->assertIsArray($courseContext);
        $this->assertSame('polyglot-english-a2', $courseContext['course_slug']);
        $this->assertSame('polyglot-past-continuous-a2', $courseContext['next_lesson_slug']);
    }

    public function test_compose_route_works_for_generator_driven_past_continuous_a2_lesson(): void
    {
        $this->seed(PolyglotPresentPerfectBasicLessonSeeder::class);
        $this->seed(PolyglotPresentPerfectVsPastSimpleLessonSeeder::class);
        $this->seed(PolyglotFirstConditionalLessonSeeder::class);
        $this->seed(PolyglotBeGoingToLessonSeeder::class);
        $this->seed(PolyglotShouldOughtToLessonSeeder::class);
        $this->seed(PolyglotMustHaveToLessonSeeder::class);
        $this->seed(PolyglotGerundVsInfinitiveLessonSeeder::class);
        $this->seed(PolyglotPastContinuousLessonSeeder::class);

        $response = $this->get('/test/polyglot-past-continuous-a2/step/compose');

        $response->assertOk();
        $response->assertSee('window.__INITIAL_JS_TEST_QUESTIONS__', false);

        $questionData = $response->viewData('questionData');
        $courseContext = $response->viewData('courseContext');

        $this->assertIsArray($questionData);
        $this->assertNotEmpty($questionData);
        $this->assertSame(
            ['I', 'was', 'reading', 'a', 'book', 'at', 'eight', "o'clock", 'yesterday', 'evening'],
            $questionData[0]['correctTokenValues']
        );
        $this->assertIsArray($courseContext);
        $this->assertSame('polyglot-english-a2', $courseContext['course_slug']);
        $this->assertSame('polyglot-present-perfect-time-expressions-a2', $courseContext['next_lesson_slug']);
    }

    public function test_compose_route_works_for_generator_driven_present_perfect_time_expressions_a2_lesson(): void
    {
        $this->seed(PolyglotPresentPerfectBasicLessonSeeder::class);
        $this->seed(PolyglotPresentPerfectVsPastSimpleLessonSeeder::class);
        $this->seed(PolyglotFirstConditionalLessonSeeder::class);
        $this->seed(PolyglotBeGoingToLessonSeeder::class);
        $this->seed(PolyglotShouldOughtToLessonSeeder::class);
        $this->seed(PolyglotMustHaveToLessonSeeder::class);
        $this->seed(PolyglotGerundVsInfinitiveLessonSeeder::class);
        $this->seed(PolyglotPastContinuousLessonSeeder::class);
        $this->seed(PolyglotPresentPerfectTimeExpressionsLessonSeeder::class);

        $response = $this->get('/test/polyglot-present-perfect-time-expressions-a2/step/compose');

        $response->assertOk();
        $response->assertSee('window.__INITIAL_JS_TEST_QUESTIONS__', false);

        $questionData = $response->viewData('questionData');
        $courseContext = $response->viewData('courseContext');

        $this->assertIsArray($questionData);
        $this->assertNotEmpty($questionData);
        $this->assertSame(
            ['I', 'have', 'lived', 'here', 'for', 'five', 'years'],
            $questionData[0]['correctTokenValues']
        );
        $this->assertIsArray($courseContext);
        $this->assertSame('polyglot-english-a2', $courseContext['course_slug']);
        $this->assertSame('polyglot-relative-clauses-a2', $courseContext['next_lesson_slug']);
    }

    public function test_compose_route_works_for_generator_driven_relative_clauses_a2_lesson(): void
    {
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

        $response = $this->get('/test/polyglot-relative-clauses-a2/step/compose');

        $response->assertOk();
        $response->assertSee('window.__INITIAL_JS_TEST_QUESTIONS__', false);

        $questionData = $response->viewData('questionData');
        $courseContext = $response->viewData('courseContext');

        $this->assertIsArray($questionData);
        $this->assertNotEmpty($questionData);
        $this->assertSame(
            ['This', 'is', 'the', 'man', 'who', 'lives', 'next', 'door'],
            $questionData[0]['correctTokenValues']
        );
        $this->assertIsArray($courseContext);
        $this->assertSame('polyglot-english-a2', $courseContext['course_slug']);
        $this->assertSame('polyglot-passive-voice-basics-a2', $courseContext['next_lesson_slug']);
    }

    public function test_compose_route_works_for_generator_driven_passive_voice_basics_a2_lesson(): void
    {
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

        $response = $this->get('/test/polyglot-passive-voice-basics-a2/step/compose');

        $response->assertOk();
        $response->assertSee('window.__INITIAL_JS_TEST_QUESTIONS__', false);

        $questionData = $response->viewData('questionData');
        $courseContext = $response->viewData('courseContext');

        $this->assertIsArray($questionData);
        $this->assertNotEmpty($questionData);
        $this->assertSame(
            ['Glass', 'is', 'made', 'from', 'sand'],
            $questionData[0]['correctTokenValues']
        );
        $this->assertIsArray($courseContext);
        $this->assertSame('polyglot-english-a2', $courseContext['course_slug']);
        $this->assertSame('polyglot-reported-speech-basics-a2', $courseContext['next_lesson_slug']);
    }

    public function test_compose_route_works_for_generator_driven_reported_speech_basics_a2_lesson(): void
    {
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

        $response = $this->get('/test/polyglot-reported-speech-basics-a2/step/compose');

        $response->assertOk();
        $response->assertSee('window.__INITIAL_JS_TEST_QUESTIONS__', false);

        $questionData = $response->viewData('questionData');
        $courseContext = $response->viewData('courseContext');

        $this->assertIsArray($questionData);
        $this->assertNotEmpty($questionData);
        $this->assertSame(
            ['He', 'said', 'that', 'he', 'was', 'tired'],
            $questionData[0]['correctTokenValues']
        );
        $this->assertIsArray($courseContext);
        $this->assertSame('polyglot-english-a2', $courseContext['course_slug']);
        $this->assertSame('polyglot-used-to-a2', $courseContext['next_lesson_slug']);
    }

    public function test_compose_route_works_for_generator_driven_used_to_a2_lesson(): void
    {
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

        $response = $this->get('/test/polyglot-used-to-a2/step/compose');

        $response->assertOk();
        $response->assertSee('window.__INITIAL_JS_TEST_QUESTIONS__', false);

        $questionData = $response->viewData('questionData');
        $courseContext = $response->viewData('courseContext');

        $this->assertIsArray($questionData);
        $this->assertNotEmpty($questionData);
        $this->assertSame(
            ['I', 'used', 'to', 'live', 'in', 'a', 'small', 'village'],
            $questionData[0]['correctTokenValues']
        );
        $this->assertIsArray($courseContext);
        $this->assertSame('polyglot-english-a2', $courseContext['course_slug']);
        $this->assertSame('polyglot-question-tags-basics-a2', $courseContext['next_lesson_slug']);
    }

    public function test_compose_route_works_for_generator_driven_question_tags_basics_a2_lesson(): void
    {
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

        $response = $this->get('/test/polyglot-question-tags-basics-a2/step/compose');

        $response->assertOk();
        $response->assertSee('window.__INITIAL_JS_TEST_QUESTIONS__', false);

        $questionData = $response->viewData('questionData');
        $courseContext = $response->viewData('courseContext');

        $this->assertIsArray($questionData);
        $this->assertNotEmpty($questionData);
        $this->assertSame(
            ['You', 'are', 'tired', 'are', 'you', 'not'],
            $questionData[0]['correctTokenValues']
        );
        $this->assertIsArray($courseContext);
        $this->assertSame('polyglot-english-a2', $courseContext['course_slug']);
        $this->assertSame('polyglot-second-conditional-basics-a2', $courseContext['next_lesson_slug']);
    }

    public function test_compose_route_works_for_generator_driven_second_conditional_basics_a2_lesson(): void
    {
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

        $response = $this->get('/test/polyglot-second-conditional-basics-a2/step/compose');

        $response->assertOk();
        $response->assertSee('window.__INITIAL_JS_TEST_QUESTIONS__', false);

        $questionData = $response->viewData('questionData');
        $courseContext = $response->viewData('courseContext');

        $this->assertIsArray($questionData);
        $this->assertNotEmpty($questionData);
        $this->assertSame(
            ['I', 'would', 'learn', 'Japanese', 'if', 'I', 'had', 'more', 'time'],
            $questionData[0]['correctTokenValues']
        );
        $this->assertIsArray($courseContext);
        $this->assertSame('polyglot-english-a2', $courseContext['course_slug']);
        $this->assertSame('polyglot-final-drill-a2', $courseContext['next_lesson_slug']);
    }

    public function test_polyglot_compose_routes_bypass_coming_soon_gate_without_opening_generic_tests(): void
    {
        config(['coming-soon.enabled' => true]);

        $this->seed(PolyglotFinalDrillLessonSeeder::class);

        $publicPolyglotResponse = $this->get('/test/polyglot-final-drill-a1/step/compose');
        $genericTest = $this->createSavedTestWithQuestion('standard-step-preview-test');
        $genericResponse = $this->get("/test/{$genericTest->slug}");

        $publicPolyglotResponse->assertOk();
        $publicPolyglotResponse->assertSee('window.__INITIAL_JS_TEST_QUESTIONS__', false);
        $genericResponse->assertStatus(503);
    }

    public function test_final_lesson_compose_route_exposes_course_complete_hooks(): void
    {
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

        $response = $this->get('/test/polyglot-final-drill-a1/step/compose');

        $response->assertOk();
        $response->assertSee('data-polyglot-is-final-lesson="1"', false);
        $response->assertSee('data-polyglot-course-completion-kind="course"', false);
        $response->assertSee('data-polyglot-first-lesson-url="http://engapp-codex.loc/test/polyglot-to-be-a1/step/compose"', false);
        $response->assertSee('"isFinalLesson":true', false);
        $response->assertSee('"firstLessonUrl":"http:\\/\\/engapp-codex.loc\\/test\\/polyglot-to-be-a1\\/step\\/compose"', false);
        $response->assertSee('"continueCourseUrl":"http:\\/\\/engapp-codex.loc\\/courses\\/polyglot-english-a2"', false);
        $response->assertSee('data-action="restart-course"', false);
    }

    public function test_standard_polyglot_route_enables_translation_preview_for_polyglot_lessons(): void
    {
        $this->seed(PolyglotFutureSimpleWillLessonSeeder::class);

        $response = $this->get('/test/polyglot-future-simple-will-a1');

        $response->assertOk();
        $response->assertSee('const IS_POLYGLOT_STEP_PREVIEW = true;', false);
        $response->assertSee('const POLYGLOT_OPTIONS_PER_SLOT = 4;', false);
        $response->assertSee('data-polyglot-translation-preview="true"', false);
        $response->assertSee('data-polyglot-translation-status="done"', false);
    }

    public function test_standard_non_polyglot_route_keeps_translation_preview_disabled(): void
    {
        $test = $this->createSavedTestWithQuestion('standard-step-preview-test');

        $response = $this->get("/test/{$test->slug}");

        $response->assertOk();
        $response->assertSee('const IS_POLYGLOT_STEP_PREVIEW = false;', false);
    }

    public function test_incompatible_test_is_guarded_from_compose_route(): void
    {
        $test = $this->createSavedTestWithQuestion('incompatible-grammar-test');

        $this->get("/test/{$test->slug}/step/compose")->assertNotFound();
    }

    public function test_mode_navigation_shows_compose_only_for_compatible_tests(): void
    {
        $compatible = $this->createSavedTestWithQuestion(
            'compatible-compose-test',
            [
                'mode' => 'compose_tokens',
                'question_type' => Question::TYPE_COMPOSE_TOKENS,
            ],
            Question::TYPE_COMPOSE_TOKENS,
            'Я готовий.',
            [
                ['marker' => 'a1', 'value' => 'I'],
                ['marker' => 'a2', 'value' => 'am'],
                ['marker' => 'a3', 'value' => 'ready'],
            ],
            ['I', 'am', 'ready', 'are']
        );
        $incompatible = $this->createSavedTestWithQuestion('standard-step-test');

        $compatibleHtml = view('components.test-mode-nav-new-design', [
            'test' => $compatible->fresh('questionLinks'),
        ])->render();
        $incompatibleHtml = view('components.test-mode-nav-new-design', [
            'test' => $incompatible->fresh('questionLinks'),
        ])->render();

        $this->assertStringContainsString("/test/{$compatible->slug}/step/compose", $compatibleHtml);
        $this->assertStringNotContainsString("/test/{$incompatible->slug}/step/compose", $incompatibleHtml);
        $this->assertStringContainsString(__('frontend.tests.mode.step'), $incompatibleHtml);
    }

    public function test_polyglot_to_be_seeder_is_idempotent(): void
    {
        $this->seed(PolyglotToBeLessonSeeder::class);
        $this->seed(PolyglotToBeLessonSeeder::class);

        $test = SavedGrammarTest::query()
            ->with('questionLinks')
            ->where('slug', 'polyglot-to-be-a1')
            ->firstOrFail();

        $uuids = collect(range(1, 24))
            ->map(fn (int $number) => sprintf('polyglot-to-be-q%02d', $number))
            ->all();

        $this->assertSame(1, SavedGrammarTest::query()->where('slug', 'polyglot-to-be-a1')->count());
        $this->assertSame(24, Question::query()->whereIn('uuid', $uuids)->count());
        $this->assertCount(24, $test->questionLinks);
        $this->assertSame($uuids, $test->questionLinks->pluck('question_uuid')->all());
        $this->assertSame(2, $test->filters['payload_version']);
        $this->assertTrue((bool) ($test->filters['supports_duplicate_tokens'] ?? false));
        $this->assertSame('polyglot-there-is-there-are-a1', $test->filters['next_lesson_slug']);
    }

    public function test_step_compose_receives_course_context(): void
    {
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

        $response = $this->get('/test/polyglot-to-be-a1/step/compose');
        $courseContext = $response->viewData('courseContext');

        $response->assertOk();
        $this->assertIsArray($courseContext);
        $this->assertSame('polyglot-english-a1', $courseContext['course_slug']);
        $this->assertSame(localized_route('courses.show', 'polyglot-english-a1'), $courseContext['course_url']);
        $this->assertSame(1, $courseContext['lesson_order']);
        $this->assertNull($courseContext['previous_lesson_slug']);
        $this->assertSame('polyglot-there-is-there-are-a1', $courseContext['next_lesson_slug']);
        $this->assertSame(16, $courseContext['total_lessons']);
    }

    public function test_step_compose_renders_polyglot_progress_data_hooks(): void
    {
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

        $response = $this->get('/test/polyglot-there-is-there-are-a1/step/compose');

        $response->assertOk();
        $response->assertSee('data-polyglot-lesson-root', false);
        $response->assertSee('data-polyglot-lesson-slug="polyglot-there-is-there-are-a1"', false);
        $response->assertSee('data-polyglot-course-slug="polyglot-english-a1"', false);
        $response->assertSee('data-polyglot-previous-lesson-slug="polyglot-to-be-a1"', false);
        $response->assertSee('data-polyglot-next-lesson-slug="polyglot-have-got-has-got-a1"', false);
        $response->assertSee('data-polyglot-lock-state=', false);
    }

    private function createSavedTestWithQuestion(
        string $slug,
        array $filters = [],
        ?string $questionType = null,
        string $questionText = 'I {a1} every day.',
        array $answers = [['marker' => 'a1', 'value' => 'go']],
        array $options = ['go', 'goes', 'went']
    ): SavedGrammarTest {
        $category = Category::query()->firstOrCreate([
            'name' => 'Polyglot Feature Test',
        ]);

        $question = Question::query()->create([
            'uuid' => (string) Str::uuid(),
            'question' => $questionText,
            'difficulty' => 1,
            'level' => 'A1',
            'category_id' => $category->id,
            'type' => $questionType,
        ]);

        $optionIds = collect($options)->map(function (string $option) use ($question) {
            $record = QuestionOption::query()->firstOrCreate([
                'option' => $option,
            ]);

            $question->options()->syncWithoutDetaching([$record->id]);

            return $record->id;
        })->all();

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
            'filters' => $filters,
            'description' => 'Test fixture',
        ]);

        $test->questionLinks()->create([
            'question_uuid' => $question->uuid,
            'position' => 0,
        ]);

        return $test;
    }
}
