<?php

namespace Tests\Feature;

use App\Models\Page;
use Database\Seeders\Page_V3\BasicGrammar\BasicGrammarCategorySeeder;
use Database\Seeders\Page_V3\BasicGrammar\BasicGrammarHaveGotHasGotTheorySeeder;
use Database\Seeders\Page_V3\BasicGrammar\BasicGrammarSentenceTypesTheorySeeder;
use Database\Seeders\Page_V3\BasicGrammar\VerbToBe\ThereIsThereAreTheorySeeder;
use Database\Seeders\Page_V3\BasicGrammar\VerbToBe\VerbToBeCategorySeeder;
use Database\Seeders\Page_V3\BasicGrammar\VerbToBe\VerbToBePastTheorySeeder;
use Database\Seeders\Page_V3\BasicGrammar\VerbToBe\VerbToBePresentTheorySeeder;
use Database\Seeders\Page_V3\CommonMistakes\CommonMistakesArticlesTheorySeeder;
use Database\Seeders\Page_V3\CommonMistakes\CommonMistakesCategorySeeder;
use Database\Seeders\Page_V3\FutureForms\FutureFormsCategorySeeder;
use Database\Seeders\Page_V3\FutureForms\FutureFormsWillVsBeGoingToTheorySeeder;
use Database\Seeders\Page_V3\ModalVerbs\ModalVerbsCanCouldTheorySeeder;
use Database\Seeders\Page_V3\ModalVerbs\ModalVerbsCategorySeeder;
use Database\Seeders\Page_V3\Tenses\PastSimple\PastSimpleCategorySeeder;
use Database\Seeders\Page_V3\Tenses\PastSimple\PastSimpleFormsTheorySeeder;
use Database\Seeders\Page_V3\Tenses\PresentContinuous\PresentContinuousCategorySeeder;
use Database\Seeders\Page_V3\Tenses\PresentContinuous\PresentContinuousFormsTheorySeeder;
use Database\Seeders\Page_V3\Tenses\PresentSimple\PresentSimpleCategorySeeder;
use Database\Seeders\Page_V3\Tenses\PresentSimple\PresentSimpleQuestionsTheorySeeder;
use Database\Seeders\Page_V3\Tenses\TensesCategorySeeder;
use Database\Seeders\V3\Polyglot\PolyglotCanCannotLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotArticlesAAnTheLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotFutureSimpleWillLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotHaveGotHasGotLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotPastSimpleIrregularVerbsLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotPastSimpleRegularVerbsLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotPastSimpleToBeLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotPresentContinuousLessonSeeder;
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

    public function test_theory_page_shows_related_polyglot_lessons_in_tests_on_topic_block(): void
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
        $canCouldPage = Page::query()
            ->where('slug', 'can-could')
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
        $articlesPage = Page::query()
            ->where('slug', 'articles-common-mistakes')
            ->firstOrFail();

        $toBeResponse = $this->get(route('theory.show', ['verb-to-be', $toBePage->slug]));
        $thereResponse = $this->get(route('theory.show', ['verb-to-be', $therePage->slug]));
        $haveGotResponse = $this->get(route('theory.show', ['basic-grammar', $haveGotPage->slug]));
        $presentSimpleResponse = $this->get(route('theory.show', ['present-simple', $presentSimplePage->slug]));
        $canCouldResponse = $this->get(route('theory.show', ['modal-verbs', $canCouldPage->slug]));
        $presentContinuousResponse = $this->get(route('theory.show', ['present-continuous', $presentContinuousPage->slug]));
        $pastToBeResponse = $this->get(route('theory.show', ['verb-to-be', $pastToBePage->slug]));
        $pastSimpleRegularResponse = $this->get(route('theory.show', ['past-simple', $pastSimpleRegularPage->slug]));
        $futureSimpleResponse = $this->get(route('theory.show', ['maibutni-formy', $futureSimplePage->slug]));
        $articlesResponse = $this->get(route('theory.show', ['common-mistakes', $articlesPage->slug]));

        $toBeResponse->assertOk();
        $toBeResponse->assertSee('Polyglot: to be (A1)');
        $toBeResponse->assertViewHas('topicTests', function ($tests) {
            return collect($tests)->pluck('slug')->contains('polyglot-to-be-a1');
        });

        $thereResponse->assertOk();
        $thereResponse->assertSee('Polyglot: there is / there are (A1)');
        $thereResponse->assertViewHas('topicTests', function ($tests) {
            return collect($tests)->pluck('slug')->contains('polyglot-there-is-there-are-a1');
        });

        $haveGotResponse->assertOk();
        $haveGotResponse->assertSee('Polyglot: have got / has got (A1)');
        $haveGotResponse->assertViewHas('topicTests', function ($tests) {
            return collect($tests)->pluck('slug')->contains('polyglot-have-got-has-got-a1');
        });

        $presentSimpleResponse->assertOk();
        $presentSimpleResponse->assertSee('Polyglot: present simple verbs (A1)');
        $presentSimpleResponse->assertViewHas('topicTests', function ($tests) {
            return collect($tests)->pluck('slug')->contains('polyglot-present-simple-verbs-a1');
        });

        $canCouldResponse->assertOk();
        $canCouldResponse->assertSee('Polyglot: can / cannot (A1)');
        $canCouldResponse->assertViewHas('topicTests', function ($tests) {
            return collect($tests)->pluck('slug')->contains('polyglot-can-cannot-a1');
        });

        $presentContinuousResponse->assertOk();
        $presentContinuousResponse->assertSee('Polyglot: present continuous (A1)');
        $presentContinuousResponse->assertViewHas('topicTests', function ($tests) {
            return collect($tests)->pluck('slug')->contains('polyglot-present-continuous-a1');
        });

        $pastToBeResponse->assertOk();
        $pastToBeResponse->assertSee('Polyglot: past simple of to be (A1)');
        $pastToBeResponse->assertViewHas('topicTests', function ($tests) {
            return collect($tests)->pluck('slug')->contains('polyglot-past-simple-to-be-a1');
        });

        $pastSimpleRegularResponse->assertOk();
        $pastSimpleRegularResponse->assertSee('Polyglot: past simple regular verbs (A1)');
        $pastSimpleRegularResponse->assertSee('Polyglot: past simple irregular verbs (A1)');
        $pastSimpleRegularResponse->assertViewHas('topicTests', function ($tests) {
            $slugs = collect($tests)->pluck('slug');

            return $slugs->contains('polyglot-past-simple-regular-verbs-a1')
                && $slugs->contains('polyglot-past-simple-irregular-verbs-a1');
        });

        $futureSimpleResponse->assertOk();
        $futureSimpleResponse->assertSee('Polyglot: future simple with will (A1)');
        $futureSimpleResponse->assertViewHas('topicTests', function ($tests) {
            return collect($tests)->pluck('slug')->contains('polyglot-future-simple-will-a1');
        });

        $articlesResponse->assertOk();
        $articlesResponse->assertSee('Polyglot: articles a / an / the (A1)');
        $articlesResponse->assertViewHas('topicTests', function ($tests) {
            return collect($tests)->pluck('slug')->contains('polyglot-articles-a-an-the-a1');
        });
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
                ],
                true
            ));
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
        $this->seed(TensesCategorySeeder::class);
        $this->seed(PastSimpleCategorySeeder::class);
        $this->seed(PresentContinuousCategorySeeder::class);
        $this->seed(PresentSimpleCategorySeeder::class);
        $this->seed(ModalVerbsCategorySeeder::class);
        $this->seed(FutureFormsCategorySeeder::class);
        $this->seed(CommonMistakesCategorySeeder::class);
        $this->seed(VerbToBePresentTheorySeeder::class);
        $this->seed(ThereIsThereAreTheorySeeder::class);
        $this->seed(BasicGrammarHaveGotHasGotTheorySeeder::class);
        $this->seed(VerbToBePastTheorySeeder::class);
        $this->seed(PastSimpleFormsTheorySeeder::class);
        $this->seed(PresentContinuousFormsTheorySeeder::class);
        $this->seed(PresentSimpleQuestionsTheorySeeder::class);
        $this->seed(ModalVerbsCanCouldTheorySeeder::class);
        $this->seed(FutureFormsWillVsBeGoingToTheorySeeder::class);
        $this->seed(CommonMistakesArticlesTheorySeeder::class);
    }
}
