<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Services\TheoryPagePromptLinkedTestsService;
use Database\Seeders\Page_V3\BasicGrammar\BasicGrammarCategorySeeder;
use Database\Seeders\Page_V3\BasicGrammar\BasicGrammarConjunctionsTheorySeeder;
use Database\Seeders\Page_V3\BasicGrammar\BasicGrammarImperativesTheorySeeder;
use Database\Seeders\Page_V3\BasicGrammar\BasicGrammarSentenceStructureSvoTheorySeeder;
use Database\Seeders\Page_V3\BasicGrammar\BasicGrammarSentenceTypesTheorySeeder;
use Database\Seeders\V3\AI\Copilot\Opus46\ConjunctionsAndButOrBecauseSoV3QuestionsOnlySeeder as Opus46ConjunctionsSeeder;
use Database\Seeders\V3\AI\Copilot\Opus46\ImperativesNakazoviRecenniaV3QuestionsOnlySeeder as Opus46ImperativesSeeder;
use Database\Seeders\V3\AI\Copilot\Opus46\SentenceStructureBudovaRecenniaSvoV3QuestionsOnlySeeder as Opus46SentenceStructureSeeder;
use Database\Seeders\V3\AI\Copilot\Opus46\SentenceTypesVidiRecenV3QuestionsOnlySeeder as Opus46SentenceTypesSeeder;
use Database\Seeders\V3\AI\Copilot\Sonate\ConjunctionsAndButOrBecauseSoV3QuestionsOnlySeeder as SonateConjunctionsSeeder;
use Database\Seeders\V3\AI\Copilot\Sonate\ImperativesNakazoviRecenniaV3QuestionsOnlySeeder as SonateImperativesSeeder;
use Database\Seeders\V3\AI\Copilot\Sonate\SentenceStructureBudovaRecenniaSvoV3QuestionsOnlySeeder as SonateSentenceStructureSeeder;
use Database\Seeders\V3\AI\Copilot\Sonate\SentenceTypesVidiRecenV3QuestionsOnlySeeder as SonateSentenceTypesSeeder;
use Database\Seeders\V3\Polyglot\PolyglotConjunctionsAllLevelsLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotImperativesAllLevelsLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotSentenceStructureSvoAllLevelsLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotSentenceTypesAllLevelsLessonSeeder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

class BasicGrammarSentencePagesMixedCardsTest extends TestCase
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

    public function test_basic_grammar_sentence_pages_show_sentence_builder_and_one_mixed_test(): void
    {
        $this->seedTheoryPages();
        $this->seedQuestionPackages();

        $service = app(TheoryPagePromptLinkedTestsService::class);
        $cases = [
            'sentence-structure-svo' => [
                'direct_slug' => 'polyglot-sentence-structure-svo-all-levels',
                'polyglot_seeder' => PolyglotSentenceStructureSvoAllLevelsLessonSeeder::class,
                'seeders' => [
                    SonateSentenceStructureSeeder::class,
                    Opus46SentenceStructureSeeder::class,
                    PolyglotSentenceStructureSvoAllLevelsLessonSeeder::class,
                ],
            ],
            'sentence-types' => [
                'direct_slug' => 'polyglot-sentence-types-all-levels',
                'polyglot_seeder' => PolyglotSentenceTypesAllLevelsLessonSeeder::class,
                'seeders' => [
                    SonateSentenceTypesSeeder::class,
                    Opus46SentenceTypesSeeder::class,
                    PolyglotSentenceTypesAllLevelsLessonSeeder::class,
                ],
            ],
            'imperatives-sit-down-dont-open-it' => [
                'direct_slug' => 'polyglot-imperatives-all-levels',
                'polyglot_seeder' => PolyglotImperativesAllLevelsLessonSeeder::class,
                'seeders' => [
                    SonateImperativesSeeder::class,
                    Opus46ImperativesSeeder::class,
                    PolyglotImperativesAllLevelsLessonSeeder::class,
                ],
            ],
            'basic-conjunctions-and-but-or-because-so' => [
                'direct_slug' => 'polyglot-conjunctions-all-levels',
                'polyglot_seeder' => PolyglotConjunctionsAllLevelsLessonSeeder::class,
                'seeders' => [
                    SonateConjunctionsSeeder::class,
                    Opus46ConjunctionsSeeder::class,
                    PolyglotConjunctionsAllLevelsLessonSeeder::class,
                ],
            ],
        ];

        foreach ($cases as $slug => $case) {
            $page = Page::query()->where('slug', $slug)->firstOrFail();
            $tests = $service->buildForPage($page);

            $this->assertCount(2, $tests, $slug);

            $directTest = $tests->firstWhere('slug', $case['direct_slug']);
            $mixedTest = $tests->firstWhere('slug', 'theory-page-' . $page->id . '-mixed-a1-c2');

            $this->assertNotNull($directTest, $slug);
            $this->assertNotNull($mixedTest, $slug);

            $directFilters = $directTest->filters ?? [];
            $mixedFilters = $mixedTest->filters ?? [];

            $this->assertSame('polyglot-theory-pages', $directFilters['course_slug'] ?? null, $slug);
            $this->assertSame([$case['polyglot_seeder']], array_values($directFilters['seeder_classes'] ?? []), $slug);
            $this->assertTrue($mixedTest->isVirtual(), $slug);
            $this->assertTrue(($mixedFilters['__meta']['aggregated_theory_page_test'] ?? false), $slug);
            $this->assertTrue(($mixedFilters['__meta']['theory_page_mixed_all_levels_test'] ?? false), $slug);
            $this->assertTrue(($mixedFilters['__meta']['theory_page_mixed_polyglot_test'] ?? false), $slug);
            $this->assertSame(['A1', 'A2', 'B1', 'B2', 'C1', 'C2'], $mixedFilters['levels'] ?? [], $slug);
            $this->assertSame(
                collect($case['seeders'])->sort()->values()->all(),
                collect($mixedFilters['seeder_classes'] ?? [])->sort()->values()->all(),
                $slug
            );

            $response = $this->get(route('theory.show', ['basic-grammar', $slug]));

            $response->assertOk();
            $response->assertViewHas('topicTests', function ($topicTests) use ($case, $page) {
                $slugs = collect($topicTests)->pluck('slug')->values();

                return $slugs->count() === 2
                    && $slugs->contains($case['direct_slug'])
                    && $slugs->contains('theory-page-' . $page->id . '-mixed-a1-c2')
                    && ! $slugs->contains('theory-page-' . $page->id . '-a1-a2');
            });
        }
    }

    protected function seedTheoryPages(): void
    {
        $this->seed(BasicGrammarCategorySeeder::class);
        $this->seed(BasicGrammarSentenceStructureSvoTheorySeeder::class);
        $this->seed(BasicGrammarSentenceTypesTheorySeeder::class);
        $this->seed(BasicGrammarImperativesTheorySeeder::class);
        $this->seed(BasicGrammarConjunctionsTheorySeeder::class);
    }

    protected function seedQuestionPackages(): void
    {
        $this->seed([
            SonateSentenceStructureSeeder::class,
            Opus46SentenceStructureSeeder::class,
            PolyglotSentenceStructureSvoAllLevelsLessonSeeder::class,
            SonateSentenceTypesSeeder::class,
            Opus46SentenceTypesSeeder::class,
            PolyglotSentenceTypesAllLevelsLessonSeeder::class,
            SonateImperativesSeeder::class,
            Opus46ImperativesSeeder::class,
            PolyglotImperativesAllLevelsLessonSeeder::class,
            SonateConjunctionsSeeder::class,
            Opus46ConjunctionsSeeder::class,
            PolyglotConjunctionsAllLevelsLessonSeeder::class,
        ]);
    }

    protected function augmentTheorySchema(): void
    {
        Schema::disableForeignKeyConstraints();
        foreach (['site_tree_items', 'site_tree_variants', 'page_category_tag', 'page_tag', 'tag_text_block', 'text_blocks'] as $table) {
            Schema::dropIfExists($table);
        }
        Schema::enableForeignKeyConstraints();

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
}
