<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Services\TheoryPagePromptLinkedTestsService;
use Database\Seeders\Page_V3\Adjectives\AdjectivesCategorySeeder;
use Database\Seeders\Page_V3\Adjectives\AdjectivesComparativeVsSuperlativeTheorySeeder;
use Database\Seeders\Page_V3\Adjectives\AdjectivesDegreesOfComparisonTheorySeeder;
use Database\Seeders\Page_V3\BasicGrammar\BasicGrammarCategorySeeder;
use Database\Seeders\Page_V3\BasicGrammar\BasicGrammarA1MixedRevisionTheorySeeder;
use Database\Seeders\Page_V3\BasicGrammar\BasicGrammarHaveGotHasGotTheorySeeder;
use Database\Seeders\Page_V3\BasicGrammar\BasicGrammarSentenceTypesTheorySeeder;
use Database\Seeders\Page_V3\BasicGrammar\VerbToBe\ThereIsThereAreTheorySeeder;
use Database\Seeders\Page_V3\BasicGrammar\VerbToBe\VerbToBeCategorySeeder;
use Database\Seeders\Page_V3\BasicGrammar\VerbToBe\VerbToBePastTheorySeeder;
use Database\Seeders\Page_V3\BasicGrammar\VerbToBe\VerbToBePresentTheorySeeder;
use Database\Seeders\Page_V3\ClausesAndLinkingWords\ClausesAndLinkingWordsCategorySeeder;
use Database\Seeders\Page_V3\ClausesAndLinkingWords\ClausesAndLinkingWordsRelativeClausesTheorySeeder;
use Database\Seeders\Page_V3\Conditionals\ConditionalsCategorySeeder;
use Database\Seeders\Page_V3\Conditionals\ConditionalsFirstTheorySeeder;
use Database\Seeders\Page_V3\Articles\SomeAny\SomeAnyCategorySeeder;
use Database\Seeders\Page_V3\Articles\SomeAny\SomeAnyThingsTheorySeeder;
use Database\Seeders\Page_V3\CommonMistakes\CommonMistakesArticlesTheorySeeder;
use Database\Seeders\Page_V3\CommonMistakes\CommonMistakesCategorySeeder;
use Database\Seeders\Page_V3\FutureForms\FutureFormsCategorySeeder;
use Database\Seeders\Page_V3\FutureForms\FutureFormsWillVsBeGoingToTheorySeeder;
use Database\Seeders\Page_V3\ModalVerbs\ModalVerbsCanCouldTheorySeeder;
use Database\Seeders\Page_V3\ModalVerbs\ModalVerbsCategorySeeder;
use Database\Seeders\Page_V3\ModalVerbs\ModalVerbsShouldOughtToTheorySeeder;
use Database\Seeders\Page_V3\NounsArticlesQuantity\NounsArticlesQuantityCategorySeeder;
use Database\Seeders\Page_V3\NounsArticlesQuantity\NounsArticlesQuantityQuantifiersTheorySeeder;
use Database\Seeders\Page_V3\PassiveVoice\Basics\PassiveVoiceFormationRulesTheorySeeder;
use Database\Seeders\Page_V3\PassiveVoice\PassiveVoiceCategorySeeder;
use Database\Seeders\Page_V3\ReportedSpeech\ReportedSpeechCategorySeeder;
use Database\Seeders\Page_V3\ReportedSpeech\ReportedSpeechStatementsTheorySeeder;
use Database\Seeders\Page_V3\Tenses\PastSimple\PastSimpleCategorySeeder;
use Database\Seeders\Page_V3\Tenses\PastSimple\PastSimpleFormsTheorySeeder;
use Database\Seeders\Page_V3\Tenses\PastContinuous\PastContinuousCategorySeeder;
use Database\Seeders\Page_V3\Tenses\PastContinuous\PastContinuousFormsTheorySeeder;
use Database\Seeders\Page_V3\Tenses\PresentContinuous\PresentContinuousCategorySeeder;
use Database\Seeders\Page_V3\Tenses\PresentContinuous\PresentContinuousFormsTheorySeeder;
use Database\Seeders\Page_V3\Tenses\PresentPerfect\PresentPerfectCategorySeeder;
use Database\Seeders\Page_V3\Tenses\PresentPerfect\PresentPerfectFormsTheorySeeder;
use Database\Seeders\Page_V3\Tenses\PresentPerfect\PresentPerfectTimeExpressionsTheorySeeder;
use Database\Seeders\Page_V3\Tenses\PresentSimple\PresentSimpleCategorySeeder;
use Database\Seeders\Page_V3\Tenses\PresentSimple\PresentSimpleQuestionsTheorySeeder;
use Database\Seeders\Page_V3\Tenses\TensesCategorySeeder;
use Database\Seeders\Page_V3\Tenses\TensesPresentPerfectVsPastSimpleTheorySeeder;
use Database\Seeders\Page_V3\Tenses\TensesUsedToWouldTheorySeeder;
use Database\Seeders\Page_V3\VerbPatterns\VerbPatternsCategorySeeder;
use Database\Seeders\Page_V3\VerbPatterns\VerbPatternsGerundVsInfinitiveTheorySeeder;
use Database\Seeders\V3\Polyglot\PolyglotCanCannotLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotArticlesAAnTheLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotBeGoingToLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotComparativesLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotFinalDrillLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotFutureSimpleWillLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotGerundVsInfinitiveLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotHaveGotHasGotLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotMustHaveToLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotPastContinuousLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotPassiveVoiceBasicsLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotPresentPerfectTimeExpressionsLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotReportedSpeechBasicsLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotRelativeClausesLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotShouldOughtToLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotUsedToLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotPastSimpleIrregularVerbsLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotSuperlativesLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotSomeAnyLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotMuchManyALotOfLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotPastSimpleRegularVerbsLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotPastSimpleToBeLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotPresentContinuousLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotFirstConditionalLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotPresentPerfectBasicLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotPresentPerfectVsPastSimpleLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotPresentSimpleVerbsLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotThereIsThereAreLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotToBeLessonSeeder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

class PolyglotTheoryPageTest extends TestCase
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
        $this->augmentTheorySchema();
    }

    public function test_theory_page_merges_related_polyglot_lessons_into_topic_tests_block(): void
    {
        $this->seedTheoryPages();
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
        $this->seed(PolyglotFirstConditionalLessonSeeder::class);
        $this->seed(PolyglotBeGoingToLessonSeeder::class);
        $this->seed(PolyglotShouldOughtToLessonSeeder::class);
        $this->seed(PolyglotMustHaveToLessonSeeder::class);
        $this->seed(PolyglotPastContinuousLessonSeeder::class);
        $this->seed(PolyglotPresentPerfectTimeExpressionsLessonSeeder::class);
        $this->seed(PolyglotRelativeClausesLessonSeeder::class);
        $this->seed(PolyglotPassiveVoiceBasicsLessonSeeder::class);

        $toBePage = Page::query()
            ->where('slug', 'verb-to-be-present')
            ->firstOrFail();
        $therePage = Page::query()
            ->where('slug', 'there-is-there-are')
            ->firstOrFail();
        $haveGotPage = Page::query()
            ->where('slug', 'have-got-has-got')
            ->firstOrFail();
        $presentSimplePage = Page::query()
            ->where('slug', 'present-simple-questions')
            ->firstOrFail();
        $presentPerfectTimeExpressionsPage = Page::query()
            ->where('slug', 'present-perfect-time-expressions')
            ->firstOrFail();
        $relativeClausesPage = Page::query()
            ->where('slug', 'relative-clauses')
            ->firstOrFail();
        $passiveVoicePage = Page::query()
            ->where('slug', 'theory-passive-voice-formation-rules')
            ->firstOrFail();
        $canCouldPage = Page::query()
            ->where('slug', 'can-could')
            ->firstOrFail();
        $shouldOughtPage = Page::query()
            ->where('slug', 'should-ought-to')
            ->firstOrFail();
        $presentContinuousPage = Page::query()
            ->where('slug', 'present-continuous-forms')
            ->firstOrFail();
        $pastToBePage = Page::query()
            ->where('slug', 'verb-to-be-past')
            ->firstOrFail();
        $pastSimpleRegularPage = Page::query()
            ->where('slug', 'past-simple-forms')
            ->firstOrFail();
        $futureSimplePage = Page::query()
            ->where('slug', 'will-vs-be-going-to')
            ->firstOrFail();
        $firstConditionalPage = Page::query()
            ->where('slug', 'first-conditional')
            ->firstOrFail();
        $articlesPage = Page::query()
            ->where('slug', 'articles-common-mistakes')
            ->firstOrFail();
        $someAnyPage = Page::query()
            ->where('slug', 'theory-some-any-things')
            ->firstOrFail();
        $quantifiersPage = Page::query()
            ->where('slug', 'quantifiers-much-many-a-lot-few-little')
            ->firstOrFail();
        $comparativesPage = Page::query()
            ->where('slug', 'theory-degrees-of-comparison')
            ->firstOrFail();
        $superlativesPage = Page::query()
            ->where('slug', 'comparative-vs-superlative')
            ->firstOrFail();
        $finalDrillPage = Page::query()
            ->where('slug', 'a1-mixed-revision')
            ->firstOrFail();

        $toBeResponse = $this->get(route('theory.show', ['verb-to-be', $toBePage->slug]));
        $thereResponse = $this->get(route('theory.show', ['verb-to-be', $therePage->slug]));
        $haveGotResponse = $this->get(route('theory.show', ['basic-grammar', $haveGotPage->slug]));
        $presentSimpleResponse = $this->get(route('theory.show', ['present-simple', $presentSimplePage->slug]));
        $presentPerfectTimeExpressionsResponse = $this->get(route('theory.show', ['present-perfect', $presentPerfectTimeExpressionsPage->slug]));
        $relativeClausesResponse = $this->get(route('theory.show', ['clauses-and-linking-words', $relativeClausesPage->slug]));
        $passiveVoiceResponse = $this->get(route('theory.show', ['passive-voice', $passiveVoicePage->slug]));
        $canCouldResponse = $this->get(route('theory.show', ['modal-verbs', $canCouldPage->slug]));
        $shouldOughtResponse = $this->get(route('theory.show', ['modal-verbs', $shouldOughtPage->slug]));
        $presentContinuousResponse = $this->get(route('theory.show', ['present-continuous', $presentContinuousPage->slug]));
        $pastToBeResponse = $this->get(route('theory.show', ['verb-to-be', $pastToBePage->slug]));
        $pastSimpleRegularResponse = $this->get(route('theory.show', ['past-simple', $pastSimpleRegularPage->slug]));
        $futureSimpleResponse = $this->get(route('theory.show', ['maibutni-formy', $futureSimplePage->slug]));
        $firstConditionalResponse = $this->get(route('theory.show', ['conditionals', $firstConditionalPage->slug]));
        $articlesResponse = $this->get(route('theory.show', ['common-mistakes', $articlesPage->slug]));
        $someAnyResponse = $this->get(route('theory.show', ['some-any', $someAnyPage->slug]));
        $quantifiersResponse = $this->get(route('theory.show', ['imennyky-artykli-ta-kilkist', $quantifiersPage->slug]));
        $comparativesResponse = $this->get(route('theory.show', ['prykmetniky-ta-pryslinknyky', $comparativesPage->slug]));
        $superlativesResponse = $this->get(route('theory.show', ['prykmetniky-ta-pryslinknyky', $superlativesPage->slug]));
        $finalDrillResponse = $this->get(route('theory.show', ['basic-grammar', $finalDrillPage->slug]));

        $assertPolyglotTopicTests = function ($response, array $expectedSlugs): void {
            $response->assertOk();
            $response->assertViewHas('topicTests', function ($tests) use ($expectedSlugs) {
                $topicTests = collect($tests);
                $slugs = $topicTests->pluck('slug')
                    ->filter()
                    ->values();

                return $topicTests->isNotEmpty()
                    && collect($expectedSlugs)->diff($slugs)->isEmpty()
                    && $slugs->every(fn ($slug) => is_string($slug) && str_starts_with($slug, 'polyglot-'));
            });
        };

        $assertPolyglotTopicTests($toBeResponse, ['polyglot-to-be-a1']);
        $assertPolyglotTopicTests($thereResponse, ['polyglot-there-is-there-are-a1']);
        $assertPolyglotTopicTests($haveGotResponse, ['polyglot-have-got-has-got-a1']);
        $assertPolyglotTopicTests($presentSimpleResponse, ['polyglot-present-simple-verbs-a1']);
        $assertPolyglotTopicTests($presentPerfectTimeExpressionsResponse, ['polyglot-present-perfect-time-expressions-a2']);
        $assertPolyglotTopicTests($relativeClausesResponse, ['polyglot-relative-clauses-a2']);
        $assertPolyglotTopicTests($passiveVoiceResponse, ['polyglot-passive-voice-basics-a2']);
        $assertPolyglotTopicTests($canCouldResponse, ['polyglot-can-cannot-a1']);
        $assertPolyglotTopicTests($shouldOughtResponse, ['polyglot-should-ought-to-a2']);
        $assertPolyglotTopicTests($presentContinuousResponse, ['polyglot-present-continuous-a1']);
        $assertPolyglotTopicTests($pastToBeResponse, ['polyglot-past-simple-to-be-a1']);
        $assertPolyglotTopicTests($pastSimpleRegularResponse, [
            'polyglot-past-simple-regular-verbs-a1',
            'polyglot-past-simple-irregular-verbs-a1',
        ]);
        $assertPolyglotTopicTests($futureSimpleResponse, [
            'polyglot-future-simple-will-a1',
            'polyglot-be-going-to-a2',
        ]);
        $assertPolyglotTopicTests($firstConditionalResponse, ['polyglot-first-conditional-a2']);
        $assertPolyglotTopicTests($articlesResponse, ['polyglot-articles-a-an-the-a1']);
        $assertPolyglotTopicTests($someAnyResponse, ['polyglot-some-any-a1']);
        $assertPolyglotTopicTests($quantifiersResponse, ['polyglot-much-many-a-lot-of-a1']);
        $assertPolyglotTopicTests($comparativesResponse, ['polyglot-comparatives-a1']);
        $assertPolyglotTopicTests($superlativesResponse, ['polyglot-superlatives-a1']);
        $assertPolyglotTopicTests($finalDrillResponse, ['polyglot-final-drill-a1']);
    }

    public function test_non_related_theory_page_does_not_show_unrelated_polyglot_lessons(): void
    {
        $this->seedTheoryPages();
        $this->seed(BasicGrammarSentenceTypesTheorySeeder::class);
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
        $this->seed(PolyglotFirstConditionalLessonSeeder::class);
        $this->seed(PolyglotBeGoingToLessonSeeder::class);
        $this->seed(PolyglotShouldOughtToLessonSeeder::class);
        $this->seed(PolyglotMustHaveToLessonSeeder::class);
        $this->seed(PolyglotPastContinuousLessonSeeder::class);
        $this->seed(PolyglotPresentPerfectTimeExpressionsLessonSeeder::class);
        $this->seed(PolyglotRelativeClausesLessonSeeder::class);
        $this->seed(PolyglotPassiveVoiceBasicsLessonSeeder::class);

        $page = Page::query()
            ->where('slug', 'sentence-types')
            ->firstOrFail();

        $response = $this->get(route('theory.show', ['basic-grammar', $page->slug]));

        $response->assertOk();
        $response->assertDontSee('Polyglot: to be (A1)');
        $response->assertDontSee('Polyglot: there is / there are (A1)');
        $response->assertDontSee('Polyglot: have got / has got (A1)');
        $response->assertDontSee('Polyglot: present simple verbs (A1)');
        $response->assertDontSee('Polyglot: can / cannot (A1)');
        $response->assertDontSee('Polyglot: present continuous (A1)');
        $response->assertDontSee('Polyglot: past simple of to be (A1)');
        $response->assertDontSee('Polyglot: past simple regular verbs (A1)');
        $response->assertDontSee('Polyglot: past simple irregular verbs (A1)');
        $response->assertDontSee('Polyglot: future simple with will (A1)');
        $response->assertDontSee('Polyglot: articles a / an / the (A1)');
        $response->assertDontSee('Polyglot: some / any (A1)');
        $response->assertDontSee('Polyglot: much / many / a lot of (A1)');
        $response->assertDontSee('Polyglot: comparative adjectives (A1)');
        $response->assertDontSee('Polyglot: superlatives (A1)');
        $response->assertDontSee('Polyglot: mixed revision / final drill (A1)');
        $response->assertDontSee('Polyglot: first conditional (A2)');
        $response->assertDontSee('Polyglot: be going to (A2)');
        $response->assertDontSee('Polyglot: should / ought to (A2)');
        $response->assertDontSee('Polyglot: must / have to (A2)');
        $response->assertDontSee('Polyglot: gerund vs infinitive basics (A2)');
        $response->assertDontSee('Polyglot: past continuous (A2)');
        $response->assertDontSee('Polyglot: present perfect time expressions (A2)');
        $response->assertDontSee('Polyglot: relative clauses basics (A2)');
        $response->assertDontSee('Polyglot: passive voice basics (A2)');
        $response->assertViewHas('topicTests', function ($tests) {
            return collect($tests)->doesntContain(fn ($test) => in_array(
                $test->slug ?? null,
                [
                    'polyglot-to-be-a1',
                    'polyglot-there-is-there-are-a1',
                    'polyglot-have-got-has-got-a1',
                    'polyglot-present-simple-verbs-a1',
                    'polyglot-can-cannot-a1',
                    'polyglot-present-continuous-a1',
                    'polyglot-past-simple-to-be-a1',
                    'polyglot-past-simple-regular-verbs-a1',
                    'polyglot-past-simple-irregular-verbs-a1',
                    'polyglot-future-simple-will-a1',
                    'polyglot-articles-a-an-the-a1',
                    'polyglot-some-any-a1',
                    'polyglot-much-many-a-lot-of-a1',
                    'polyglot-comparatives-a1',
                    'polyglot-superlatives-a1',
                    'polyglot-final-drill-a1',
                    'polyglot-first-conditional-a2',
                    'polyglot-be-going-to-a2',
                    'polyglot-should-ought-to-a2',
                    'polyglot-must-have-to-a2',
                    'polyglot-gerund-vs-infinitive-a2',
                    'polyglot-past-continuous-a2',
                    'polyglot-present-perfect-time-expressions-a2',
                    'polyglot-relative-clauses-a2',
                    'polyglot-passive-voice-basics-a2',
                ],
                true
            ));
        });
    }

    public function test_present_perfect_theory_page_resolves_a2_polyglot_topic_tests_without_leaking_to_unrelated_pages(): void
    {
        $this->seedTheoryPages();
        $this->seed(BasicGrammarSentenceTypesTheorySeeder::class);
        $this->seed(PolyglotPresentPerfectBasicLessonSeeder::class);

        $presentPerfectPage = Page::query()
            ->where('slug', 'present-perfect-forms')
            ->firstOrFail();
        $sentenceTypesPage = Page::query()
            ->where('slug', 'sentence-types')
            ->firstOrFail();
        $expectedDirectSlug = 'polyglot-present-perfect-basic-a2';
        $service = app(TheoryPagePromptLinkedTestsService::class);

        $this->assertTrue(
            $service->buildForPage($presentPerfectPage)->contains(
                fn ($test) => ($test->slug ?? null) === $expectedDirectSlug
            )
        );
        $this->assertFalse(
            $service->buildForPage($sentenceTypesPage)->contains(
                fn ($test) => ($test->slug ?? null) === $expectedDirectSlug
            )
        );

        $relevantResponse = $this->get(route('theory.show', ['present-perfect', $presentPerfectPage->slug]));
        $unrelatedResponse = $this->get(route('theory.show', ['basic-grammar', $sentenceTypesPage->slug]));

        $relevantResponse->assertOk();
        $relevantResponse->assertViewHas('topicTests', function ($tests) use ($expectedDirectSlug) {
            return collect($tests)->contains(
                fn ($test) => ($test->slug ?? null) === $expectedDirectSlug
            );
        });

        $unrelatedResponse->assertOk();
        $unrelatedResponse->assertViewHas('topicTests', function ($tests) use ($expectedDirectSlug) {
            return collect($tests)->doesntContain(
                fn ($test) => ($test->slug ?? null) === $expectedDirectSlug
            );
        });
    }

    public function test_present_perfect_vs_past_simple_theory_page_shows_a2_lesson_two_without_leaking_to_unrelated_pages(): void
    {
        $this->seedTheoryPages();
        $this->seed(BasicGrammarSentenceTypesTheorySeeder::class);
        $this->seed(PolyglotPresentPerfectVsPastSimpleLessonSeeder::class);

        $relevantPage = Page::query()
            ->where('slug', 'present-perfect-vs-past-simple')
            ->firstOrFail();
        $sentenceTypesPage = Page::query()
            ->where('slug', 'sentence-types')
            ->firstOrFail();
        $service = app(TheoryPagePromptLinkedTestsService::class);

        $this->assertTrue(
            $service->buildForPage($relevantPage)->contains(
                fn ($test) => ($test->slug ?? null) === 'polyglot-present-perfect-vs-past-simple-a2'
            )
        );
        $this->assertFalse(
            $service->buildForPage($sentenceTypesPage)->contains(
                fn ($test) => ($test->slug ?? null) === 'polyglot-present-perfect-vs-past-simple-a2'
            )
        );

        $relevantResponse = $this->get(route('theory.show', ['tenses', $relevantPage->slug]));
        $unrelatedResponse = $this->get(route('theory.show', ['basic-grammar', $sentenceTypesPage->slug]));

        $relevantResponse->assertOk();
        $relevantResponse->assertViewHas('topicTests', function ($tests) {
            return collect($tests)->contains(
                fn ($test) => ($test->slug ?? null) === 'polyglot-present-perfect-vs-past-simple-a2'
            );
        });

        $unrelatedResponse->assertOk();
        $unrelatedResponse->assertViewHas('topicTests', function ($tests) {
            return collect($tests)->doesntContain(
                fn ($test) => ($test->slug ?? null) === 'polyglot-present-perfect-vs-past-simple-a2'
            );
        });
    }

    public function test_first_conditional_theory_page_shows_a2_lesson_three_without_leaking_to_unrelated_pages(): void
    {
        $this->seedTheoryPages();
        $this->seed(BasicGrammarSentenceTypesTheorySeeder::class);
        $this->seed(PolyglotFirstConditionalLessonSeeder::class);

        $relevantPage = Page::query()
            ->where('slug', 'first-conditional')
            ->firstOrFail();
        $sentenceTypesPage = Page::query()
            ->where('slug', 'sentence-types')
            ->firstOrFail();
        $service = app(TheoryPagePromptLinkedTestsService::class);

        $this->assertTrue(
            $service->buildForPage($relevantPage)->contains(
                fn ($test) => ($test->slug ?? null) === 'polyglot-first-conditional-a2'
            )
        );
        $this->assertFalse(
            $service->buildForPage($sentenceTypesPage)->contains(
                fn ($test) => ($test->slug ?? null) === 'polyglot-first-conditional-a2'
            )
        );

        $relevantResponse = $this->get(route('theory.show', ['conditionals', $relevantPage->slug]));
        $unrelatedResponse = $this->get(route('theory.show', ['basic-grammar', $sentenceTypesPage->slug]));

        $relevantResponse->assertOk();
        $relevantResponse->assertViewHas('topicTests', function ($tests) {
            return collect($tests)->contains(
                fn ($test) => ($test->slug ?? null) === 'polyglot-first-conditional-a2'
            );
        });

        $unrelatedResponse->assertOk();
        $unrelatedResponse->assertViewHas('topicTests', function ($tests) {
            return collect($tests)->doesntContain(
                fn ($test) => ($test->slug ?? null) === 'polyglot-first-conditional-a2'
            );
        });
    }

    public function test_be_going_to_theory_page_shows_a2_lesson_four_without_leaking_to_unrelated_pages(): void
    {
        $this->seedTheoryPages();
        $this->seed(BasicGrammarSentenceTypesTheorySeeder::class);
        $this->seed(PolyglotBeGoingToLessonSeeder::class);

        $relevantPage = Page::query()
            ->where('slug', 'will-vs-be-going-to')
            ->firstOrFail();
        $sentenceTypesPage = Page::query()
            ->where('slug', 'sentence-types')
            ->firstOrFail();
        $service = app(TheoryPagePromptLinkedTestsService::class);

        $this->assertTrue(
            $service->buildForPage($relevantPage)->contains(
                fn ($test) => ($test->slug ?? null) === 'polyglot-be-going-to-a2'
            )
        );
        $this->assertFalse(
            $service->buildForPage($sentenceTypesPage)->contains(
                fn ($test) => ($test->slug ?? null) === 'polyglot-be-going-to-a2'
            )
        );

        $relevantResponse = $this->get(route('theory.show', ['maibutni-formy', $relevantPage->slug]));
        $unrelatedResponse = $this->get(route('theory.show', ['basic-grammar', $sentenceTypesPage->slug]));

        $relevantResponse->assertOk();
        $relevantResponse->assertViewHas('topicTests', function ($tests) {
            return collect($tests)->contains(
                fn ($test) => ($test->slug ?? null) === 'polyglot-be-going-to-a2'
            );
        });

        $unrelatedResponse->assertOk();
        $unrelatedResponse->assertViewHas('topicTests', function ($tests) {
            return collect($tests)->doesntContain(
                fn ($test) => ($test->slug ?? null) === 'polyglot-be-going-to-a2'
            );
        });
    }

    public function test_should_ought_to_theory_page_shows_a2_lesson_five_without_leaking_to_unrelated_pages(): void
    {
        $this->seedTheoryPages();
        $this->seed(BasicGrammarSentenceTypesTheorySeeder::class);
        $this->seed(PolyglotShouldOughtToLessonSeeder::class);

        $relevantPage = Page::query()
            ->where('slug', 'should-ought-to')
            ->firstOrFail();
        $sentenceTypesPage = Page::query()
            ->where('slug', 'sentence-types')
            ->firstOrFail();
        $service = app(TheoryPagePromptLinkedTestsService::class);

        $this->assertTrue(
            $service->buildForPage($relevantPage)->contains(
                fn ($test) => ($test->slug ?? null) === 'polyglot-should-ought-to-a2'
            )
        );
        $this->assertFalse(
            $service->buildForPage($sentenceTypesPage)->contains(
                fn ($test) => ($test->slug ?? null) === 'polyglot-should-ought-to-a2'
            )
        );

        $relevantResponse = $this->get(route('theory.show', ['modal-verbs', $relevantPage->slug]));
        $unrelatedResponse = $this->get(route('theory.show', ['basic-grammar', $sentenceTypesPage->slug]));

        $relevantResponse->assertOk();
        $relevantResponse->assertViewHas('topicTests', function ($tests) {
            return collect($tests)->contains(
                fn ($test) => ($test->slug ?? null) === 'polyglot-should-ought-to-a2'
            );
        });

        $unrelatedResponse->assertOk();
        $unrelatedResponse->assertViewHas('topicTests', function ($tests) {
            return collect($tests)->doesntContain(
                fn ($test) => ($test->slug ?? null) === 'polyglot-should-ought-to-a2'
            );
        });
    }

    public function test_gerund_vs_infinitive_theory_page_shows_a2_lesson_seven_without_leaking_to_unrelated_pages(): void
    {
        $this->seedTheoryPages();
        $this->seed(BasicGrammarSentenceTypesTheorySeeder::class);
        $this->seed(PolyglotGerundVsInfinitiveLessonSeeder::class);

        $relevantPage = Page::query()
            ->where('slug', 'gerund-vs-infinitive')
            ->firstOrFail();
        $sentenceTypesPage = Page::query()
            ->where('slug', 'sentence-types')
            ->firstOrFail();
        $service = app(TheoryPagePromptLinkedTestsService::class);

        $this->assertTrue(
            $service->buildForPage($relevantPage)->contains(
                fn ($test) => ($test->slug ?? null) === 'polyglot-gerund-vs-infinitive-a2'
            )
        );
        $this->assertFalse(
            $service->buildForPage($sentenceTypesPage)->contains(
                fn ($test) => ($test->slug ?? null) === 'polyglot-gerund-vs-infinitive-a2'
            )
        );

        $relevantResponse = $this->get(route('theory.show', ['verb-patterns', $relevantPage->slug]));
        $unrelatedResponse = $this->get(route('theory.show', ['basic-grammar', $sentenceTypesPage->slug]));

        $relevantResponse->assertOk();
        $relevantResponse->assertViewHas('topicTests', function ($tests) {
            return collect($tests)->contains(
                fn ($test) => ($test->slug ?? null) === 'polyglot-gerund-vs-infinitive-a2'
            );
        });

        $unrelatedResponse->assertOk();
        $unrelatedResponse->assertViewHas('topicTests', function ($tests) {
            return collect($tests)->doesntContain(
                fn ($test) => ($test->slug ?? null) === 'polyglot-gerund-vs-infinitive-a2'
            );
        });
    }

    public function test_past_continuous_theory_page_shows_a2_lesson_eight_without_leaking_to_unrelated_pages(): void
    {
        $this->seedTheoryPages();
        $this->seed(BasicGrammarSentenceTypesTheorySeeder::class);
        $this->seed(PolyglotPastContinuousLessonSeeder::class);

        $relevantPage = Page::query()
            ->where('slug', 'past-continuous-forms')
            ->firstOrFail();
        $sentenceTypesPage = Page::query()
            ->where('slug', 'sentence-types')
            ->firstOrFail();
        $service = app(TheoryPagePromptLinkedTestsService::class);

        $this->assertTrue(
            $service->buildForPage($relevantPage)->contains(
                fn ($test) => ($test->slug ?? null) === 'polyglot-past-continuous-a2'
            )
        );
        $this->assertFalse(
            $service->buildForPage($sentenceTypesPage)->contains(
                fn ($test) => ($test->slug ?? null) === 'polyglot-past-continuous-a2'
            )
        );

        $relevantResponse = $this->get(route('theory.show', ['past-continuous', $relevantPage->slug]));
        $unrelatedResponse = $this->get(route('theory.show', ['basic-grammar', $sentenceTypesPage->slug]));

        $relevantResponse->assertOk();
        $relevantResponse->assertViewHas('topicTests', function ($tests) {
            return collect($tests)->contains(
                fn ($test) => ($test->slug ?? null) === 'polyglot-past-continuous-a2'
            );
        });

        $unrelatedResponse->assertOk();
        $unrelatedResponse->assertViewHas('topicTests', function ($tests) {
            return collect($tests)->doesntContain(
                fn ($test) => ($test->slug ?? null) === 'polyglot-past-continuous-a2'
            );
        });
    }

    public function test_present_perfect_time_expressions_theory_page_shows_a2_lesson_nine_without_leaking_to_unrelated_pages(): void
    {
        $this->seedTheoryPages();
        $this->seed(BasicGrammarSentenceTypesTheorySeeder::class);
        $this->seed(PolyglotPresentPerfectTimeExpressionsLessonSeeder::class);

        $relevantPage = Page::query()
            ->where('slug', 'present-perfect-time-expressions')
            ->firstOrFail();
        $sentenceTypesPage = Page::query()
            ->where('slug', 'sentence-types')
            ->firstOrFail();
        $service = app(TheoryPagePromptLinkedTestsService::class);

        $this->assertTrue(
            $service->buildForPage($relevantPage)->contains(
                fn ($test) => ($test->slug ?? null) === 'polyglot-present-perfect-time-expressions-a2'
            )
        );
        $this->assertFalse(
            $service->buildForPage($sentenceTypesPage)->contains(
                fn ($test) => ($test->slug ?? null) === 'polyglot-present-perfect-time-expressions-a2'
            )
        );

        $relevantResponse = $this->get(route('theory.show', ['present-perfect', $relevantPage->slug]));
        $unrelatedResponse = $this->get(route('theory.show', ['basic-grammar', $sentenceTypesPage->slug]));

        $relevantResponse->assertOk();
        $relevantResponse->assertViewHas('topicTests', function ($tests) {
            return collect($tests)->contains(
                fn ($test) => ($test->slug ?? null) === 'polyglot-present-perfect-time-expressions-a2'
            );
        });

        $unrelatedResponse->assertOk();
        $unrelatedResponse->assertViewHas('topicTests', function ($tests) {
            return collect($tests)->doesntContain(
                fn ($test) => ($test->slug ?? null) === 'polyglot-present-perfect-time-expressions-a2'
            );
        });
    }

    public function test_relative_clauses_theory_page_shows_a2_lesson_ten_without_leaking_to_unrelated_pages(): void
    {
        $this->seedTheoryPages();
        $this->seed(BasicGrammarSentenceTypesTheorySeeder::class);
        $this->seed(PolyglotRelativeClausesLessonSeeder::class);

        $relevantPage = Page::query()
            ->where('slug', 'relative-clauses')
            ->firstOrFail();
        $sentenceTypesPage = Page::query()
            ->where('slug', 'sentence-types')
            ->firstOrFail();
        $service = app(TheoryPagePromptLinkedTestsService::class);

        $this->assertTrue(
            $service->buildForPage($relevantPage)->contains(
                fn ($test) => ($test->slug ?? null) === 'polyglot-relative-clauses-a2'
            )
        );
        $this->assertFalse(
            $service->buildForPage($sentenceTypesPage)->contains(
                fn ($test) => ($test->slug ?? null) === 'polyglot-relative-clauses-a2'
            )
        );

        $relevantResponse = $this->get(route('theory.show', ['clauses-and-linking-words', $relevantPage->slug]));
        $unrelatedResponse = $this->get(route('theory.show', ['basic-grammar', $sentenceTypesPage->slug]));

        $relevantResponse->assertOk();
        $relevantResponse->assertViewHas('topicTests', function ($tests) {
            return collect($tests)->contains(
                fn ($test) => ($test->slug ?? null) === 'polyglot-relative-clauses-a2'
            );
        });

        $unrelatedResponse->assertOk();
        $unrelatedResponse->assertViewHas('topicTests', function ($tests) {
            return collect($tests)->doesntContain(
                fn ($test) => ($test->slug ?? null) === 'polyglot-relative-clauses-a2'
            );
        });
    }

    public function test_passive_voice_theory_page_shows_a2_lesson_eleven_without_leaking_to_unrelated_pages(): void
    {
        $this->seedTheoryPages();
        $this->seed(BasicGrammarSentenceTypesTheorySeeder::class);
        $this->seed(PolyglotPassiveVoiceBasicsLessonSeeder::class);

        $relevantPage = Page::query()
            ->where('slug', 'theory-passive-voice-formation-rules')
            ->firstOrFail();
        $sentenceTypesPage = Page::query()
            ->where('slug', 'sentence-types')
            ->firstOrFail();
        $service = app(TheoryPagePromptLinkedTestsService::class);

        $this->assertTrue(
            $service->buildForPage($relevantPage)->contains(
                fn ($test) => ($test->slug ?? null) === 'polyglot-passive-voice-basics-a2'
            )
        );
        $this->assertFalse(
            $service->buildForPage($sentenceTypesPage)->contains(
                fn ($test) => ($test->slug ?? null) === 'polyglot-passive-voice-basics-a2'
            )
        );

        $relevantResponse = $this->get(route('theory.show', ['passive-voice', $relevantPage->slug]));
        $unrelatedResponse = $this->get(route('theory.show', ['basic-grammar', $sentenceTypesPage->slug]));

        $relevantResponse->assertOk();
        $relevantResponse->assertViewHas('topicTests', function ($tests) {
            return collect($tests)->contains(
                fn ($test) => ($test->slug ?? null) === 'polyglot-passive-voice-basics-a2'
            );
        });

        $unrelatedResponse->assertOk();
        $unrelatedResponse->assertViewHas('topicTests', function ($tests) {
            return collect($tests)->doesntContain(
                fn ($test) => ($test->slug ?? null) === 'polyglot-passive-voice-basics-a2'
            );
        });
    }

    public function test_reported_speech_theory_page_shows_a2_lesson_twelve_without_leaking_to_unrelated_pages(): void
    {
        $this->seedTheoryPages();
        $this->seed(BasicGrammarSentenceTypesTheorySeeder::class);
        $this->seed(PolyglotReportedSpeechBasicsLessonSeeder::class);

        $relevantPage = Page::query()
            ->where('slug', 'reported-statements')
            ->firstOrFail();
        $sentenceTypesPage = Page::query()
            ->where('slug', 'sentence-types')
            ->firstOrFail();
        $service = app(TheoryPagePromptLinkedTestsService::class);

        $this->assertTrue(
            $service->buildForPage($relevantPage)->contains(
                fn ($test) => ($test->slug ?? null) === 'polyglot-reported-speech-basics-a2'
            )
        );
        $this->assertFalse(
            $service->buildForPage($sentenceTypesPage)->contains(
                fn ($test) => ($test->slug ?? null) === 'polyglot-reported-speech-basics-a2'
            )
        );

        $relevantResponse = $this->get(route('theory.show', ['reported-speech', $relevantPage->slug]));
        $unrelatedResponse = $this->get(route('theory.show', ['basic-grammar', $sentenceTypesPage->slug]));

        $relevantResponse->assertOk();
        $relevantResponse->assertViewHas('topicTests', function ($tests) {
            return collect($tests)->contains(
                fn ($test) => ($test->slug ?? null) === 'polyglot-reported-speech-basics-a2'
            );
        });

        $unrelatedResponse->assertOk();
        $unrelatedResponse->assertViewHas('topicTests', function ($tests) {
            return collect($tests)->doesntContain(
                fn ($test) => ($test->slug ?? null) === 'polyglot-reported-speech-basics-a2'
            );
        });
    }

    public function test_used_to_theory_page_shows_a2_lesson_thirteen_without_leaking_to_unrelated_pages(): void
    {
        $this->seedTheoryPages();
        $this->seed(BasicGrammarSentenceTypesTheorySeeder::class);
        $this->seed(PolyglotUsedToLessonSeeder::class);

        $relevantPage = Page::query()
            ->where('slug', 'used-to-would')
            ->firstOrFail();
        $sentenceTypesPage = Page::query()
            ->where('slug', 'sentence-types')
            ->firstOrFail();
        $service = app(TheoryPagePromptLinkedTestsService::class);

        $this->assertTrue(
            $service->buildForPage($relevantPage)->contains(
                fn ($test) => ($test->slug ?? null) === 'polyglot-used-to-a2'
            )
        );
        $this->assertFalse(
            $service->buildForPage($sentenceTypesPage)->contains(
                fn ($test) => ($test->slug ?? null) === 'polyglot-used-to-a2'
            )
        );

        $relevantResponse = $this->get(route('theory.show', ['tenses', $relevantPage->slug]));
        $unrelatedResponse = $this->get(route('theory.show', ['basic-grammar', $sentenceTypesPage->slug]));

        $relevantResponse->assertOk();
        $relevantResponse->assertViewHas('topicTests', function ($tests) {
            return collect($tests)->contains(
                fn ($test) => ($test->slug ?? null) === 'polyglot-used-to-a2'
            );
        });

        $unrelatedResponse->assertOk();
        $unrelatedResponse->assertViewHas('topicTests', function ($tests) {
            return collect($tests)->doesntContain(
                fn ($test) => ($test->slug ?? null) === 'polyglot-used-to-a2'
            );
        });
    }

    protected function augmentTheorySchema(): void
    {
        Schema::create('text_blocks', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->nullable()->unique();
            $table->unsignedBigInteger('page_id')->nullable();
            $table->unsignedBigInteger('page_category_id')->nullable();
            $table->string('locale', 8)->nullable();
            $table->string('type')->nullable();
            $table->string('column')->nullable();
            $table->string('heading')->nullable();
            $table->string('css_class')->nullable();
            $table->integer('sort_order')->default(0);
            $table->text('body')->nullable();
            $table->string('level')->nullable();
            $table->string('seeder')->nullable();
            $table->timestamps();
        });

        Schema::create('page_tag', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tag_id');
            $table->unsignedBigInteger('page_id');
            $table->timestamps();
            $table->unique(['tag_id', 'page_id']);
        });

        Schema::create('page_category_tag', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tag_id');
            $table->unsignedBigInteger('page_category_id');
            $table->timestamps();
            $table->unique(['tag_id', 'page_category_id']);
        });

        Schema::create('tag_text_block', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tag_id');
            $table->unsignedBigInteger('text_block_id');
            $table->timestamps();
            $table->unique(['tag_id', 'text_block_id']);
        });

        Schema::create('site_tree_variants', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->nullable();
            $table->boolean('is_base')->default(false);
            $table->timestamps();
        });

        Schema::create('site_tree_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('variant_id')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('title')->nullable();
            $table->string('linked_page_title')->nullable();
            $table->string('linked_page_url')->nullable();
            $table->string('link_method')->nullable();
            $table->unsignedInteger('level')->default(0);
            $table->boolean('is_checked')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    protected function seedTheoryPages(): void
    {
        $this->seed(BasicGrammarCategorySeeder::class);
        $this->seed(VerbToBeCategorySeeder::class);
        $this->seed(ClausesAndLinkingWordsCategorySeeder::class);
        $this->seed(ConditionalsCategorySeeder::class);
        $this->seed(TensesCategorySeeder::class);
        $this->seed(PastSimpleCategorySeeder::class);
        $this->seed(PastContinuousCategorySeeder::class);
        $this->seed(PresentContinuousCategorySeeder::class);
        $this->seed(PresentPerfectCategorySeeder::class);
        $this->seed(PresentSimpleCategorySeeder::class);
        $this->seed(ModalVerbsCategorySeeder::class);
        $this->seed(FutureFormsCategorySeeder::class);
        $this->seed(CommonMistakesCategorySeeder::class);
        $this->seed(SomeAnyCategorySeeder::class);
        $this->seed(NounsArticlesQuantityCategorySeeder::class);
        $this->seed(AdjectivesCategorySeeder::class);
        $this->seed(VerbPatternsCategorySeeder::class);
        $this->seed(PassiveVoiceCategorySeeder::class);
        $this->seed(ReportedSpeechCategorySeeder::class);
        $this->seed(VerbToBePresentTheorySeeder::class);
        $this->seed(ThereIsThereAreTheorySeeder::class);
        $this->seed(BasicGrammarHaveGotHasGotTheorySeeder::class);
        $this->seed(BasicGrammarA1MixedRevisionTheorySeeder::class);
        $this->seed(VerbToBePastTheorySeeder::class);
        $this->seed(PastSimpleFormsTheorySeeder::class);
        $this->seed(PastContinuousFormsTheorySeeder::class);
        $this->seed(PresentContinuousFormsTheorySeeder::class);
        $this->seed(PresentPerfectFormsTheorySeeder::class);
        $this->seed(PresentPerfectTimeExpressionsTheorySeeder::class);
        $this->seed(TensesUsedToWouldTheorySeeder::class);
        $this->seed(PresentSimpleQuestionsTheorySeeder::class);
        $this->seed(ClausesAndLinkingWordsRelativeClausesTheorySeeder::class);
        $this->seed(ConditionalsFirstTheorySeeder::class);
        $this->seed(TensesPresentPerfectVsPastSimpleTheorySeeder::class);
        $this->seed(ModalVerbsCanCouldTheorySeeder::class);
        $this->seed(ModalVerbsShouldOughtToTheorySeeder::class);
        $this->seed(FutureFormsWillVsBeGoingToTheorySeeder::class);
        $this->seed(CommonMistakesArticlesTheorySeeder::class);
        $this->seed(SomeAnyThingsTheorySeeder::class);
        $this->seed(NounsArticlesQuantityQuantifiersTheorySeeder::class);
        $this->seed(AdjectivesDegreesOfComparisonTheorySeeder::class);
        $this->seed(AdjectivesComparativeVsSuperlativeTheorySeeder::class);
        $this->seed(VerbPatternsGerundVsInfinitiveTheorySeeder::class);
        $this->seed(PassiveVoiceFormationRulesTheorySeeder::class);
        $this->seed(ReportedSpeechStatementsTheorySeeder::class);
    }
}
