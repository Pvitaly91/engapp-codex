<?php

namespace Tests\Feature;

use Database\Seeders\Page_V3\Adjectives\AdjectivesCategorySeeder;
use Database\Seeders\Page_V3\Adjectives\AdjectivesComparativeVsSuperlativeTheorySeeder;
use Database\Seeders\Page_V3\Adjectives\AdjectivesDegreesOfComparisonTheorySeeder;
use Database\Seeders\Page_V3\Articles\SomeAny\SomeAnyCategorySeeder;
use Database\Seeders\Page_V3\Articles\SomeAny\SomeAnyThingsTheorySeeder;
use Database\Seeders\Page_V3\BasicGrammar\BasicGrammarA1MixedRevisionTheorySeeder;
use Database\Seeders\Page_V3\BasicGrammar\BasicGrammarCategorySeeder;
use Database\Seeders\Page_V3\BasicGrammar\BasicGrammarHaveGotHasGotTheorySeeder;
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
use Database\Seeders\Page_V3\NounsArticlesQuantity\NounsArticlesQuantityCategorySeeder;
use Database\Seeders\Page_V3\NounsArticlesQuantity\NounsArticlesQuantityQuantifiersTheorySeeder;
use Database\Seeders\Page_V3\Tenses\PastSimple\PastSimpleCategorySeeder;
use Database\Seeders\Page_V3\Tenses\PastSimple\PastSimpleFormsTheorySeeder;
use Database\Seeders\Page_V3\Tenses\PresentContinuous\PresentContinuousCategorySeeder;
use Database\Seeders\Page_V3\Tenses\PresentContinuous\PresentContinuousFormsTheorySeeder;
use Database\Seeders\Page_V3\Tenses\PresentSimple\PresentSimpleCategorySeeder;
use Database\Seeders\Page_V3\Tenses\PresentSimple\PresentSimpleQuestionsTheorySeeder;
use Database\Seeders\Page_V3\Tenses\TensesCategorySeeder;
use Database\Seeders\V2\Polyglot\PolyglotArticlesAAnTheLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotCanCannotLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotComparativesLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotFinalDrillLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotFutureSimpleWillLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotHaveGotHasGotLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotMuchManyALotOfLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotPastSimpleIrregularVerbsLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotPastSimpleRegularVerbsLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotPastSimpleToBeLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotPresentContinuousLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotPresentSimpleVerbsLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotSomeAnyLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotSuperlativesLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotThereIsThereAreLessonSeeder;
use Database\Seeders\V2\Polyglot\PolyglotToBeLessonSeeder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

class PolyglotReleaseCheckCommandTest extends TestCase
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
        $this->seedTheoryPages();
        $this->seedPolyglotLessons();
    }

    public function test_release_check_command_passes_for_the_fully_complete_course(): void
    {
        $this->artisan('polyglot:release-check', [
            'courseSlug' => 'polyglot-english-a1',
        ])
            ->expectsOutputToContain('Implemented total: 16 / 16')
            ->expectsOutputToContain('Course status: fully complete')
            ->expectsOutputToContain('Remaining planned lessons: none')
            ->expectsOutputToContain('Next recommended lesson: none')
            ->expectsOutputToContain('[PASS] No broken previous/next refs')
            ->expectsOutputToContain('[PASS] All implemented compose lesson routes resolve logically')
            ->expectsOutputToContain('[PASS] Relevant theory pages resolve')
            ->expectsOutputToContain('[PASS] Course landing route resolves')
            ->expectsOutputToContain('[PASS] Final lesson exists and has null next')
            ->assertExitCode(0);
    }

    public function test_release_check_can_emit_json_and_write_a_report_artifact(): void
    {
        Storage::fake('local');

        $exitCode = Artisan::call('polyglot:release-check', [
            'courseSlug' => 'polyglot-english-a1',
            '--json' => true,
            '--write-report' => true,
        ]);
        $reportPath = 'polyglot-reports/polyglot-english-a1-release-check.json';

        Storage::disk('local')->assertExists($reportPath);
        $storedReport = json_decode(Storage::disk('local')->get($reportPath), true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame(0, $exitCode);
        $this->assertSame(16, $storedReport['summary']['planned_total']);
        $this->assertSame(16, $storedReport['summary']['implemented_total']);
        $this->assertSame(0, $storedReport['summary']['planned_only_total']);
        $this->assertSame('none', $storedReport['summary']['next_recommended_lesson']);
        $this->assertTrue($storedReport['entry_points']['home']['found']);
        $this->assertTrue($storedReport['entry_points']['nav']['found']);
        $this->assertStringEndsWith('/courses/polyglot-english-a1', $storedReport['routes']['course']);
        $this->assertArrayHasKey('implemented_lessons', $storedReport['routes']);
        $this->assertSame('polyglot-final-drill-a1', $storedReport['lessons'][15]['slug']);
        $this->assertStringEndsWith('/theory/basic-grammar/a1-mixed-revision', $storedReport['lessons'][15]['theory_route']);
        $this->assertArrayHasKey('theory_binding', $storedReport['lessons'][15]);
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
        $this->seed(SomeAnyCategorySeeder::class);
        $this->seed(NounsArticlesQuantityCategorySeeder::class);
        $this->seed(AdjectivesCategorySeeder::class);
        $this->seed(VerbToBePresentTheorySeeder::class);
        $this->seed(ThereIsThereAreTheorySeeder::class);
        $this->seed(BasicGrammarHaveGotHasGotTheorySeeder::class);
        $this->seed(BasicGrammarA1MixedRevisionTheorySeeder::class);
        $this->seed(VerbToBePastTheorySeeder::class);
        $this->seed(PastSimpleFormsTheorySeeder::class);
        $this->seed(PresentContinuousFormsTheorySeeder::class);
        $this->seed(PresentSimpleQuestionsTheorySeeder::class);
        $this->seed(ModalVerbsCanCouldTheorySeeder::class);
        $this->seed(FutureFormsWillVsBeGoingToTheorySeeder::class);
        $this->seed(CommonMistakesArticlesTheorySeeder::class);
        $this->seed(SomeAnyThingsTheorySeeder::class);
        $this->seed(NounsArticlesQuantityQuantifiersTheorySeeder::class);
        $this->seed(AdjectivesDegreesOfComparisonTheorySeeder::class);
        $this->seed(AdjectivesComparativeVsSuperlativeTheorySeeder::class);
    }

    protected function seedPolyglotLessons(): void
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
    }
}
