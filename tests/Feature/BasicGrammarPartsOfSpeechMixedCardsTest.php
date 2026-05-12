<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Services\TheoryPagePromptLinkedTestsService;
use Database\Seeders\Page_V3\BasicGrammar\BasicGrammarCategorySeeder;
use Database\Seeders\Page_V3\BasicGrammar\BasicGrammarPartsOfSpeechTheorySeeder;
use Database\Seeders\V3\AI\Copilot\Opus46\PartsOfSpeechCastiniMoviV3QuestionsOnlySeeder as Opus46PartsOfSpeechSeeder;
use Database\Seeders\V3\AI\Copilot\Sonate\PartsOfSpeechCastiniMoviV3QuestionsOnlySeeder as SonatePartsOfSpeechSeeder;
use Database\Seeders\V3\Polyglot\PolyglotPartsOfSpeechAllLevelsLessonSeeder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

class BasicGrammarPartsOfSpeechMixedCardsTest extends TestCase
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

    public function test_parts_of_speech_page_shows_sentence_builder_and_one_mixed_test(): void
    {
        $this->seed(BasicGrammarCategorySeeder::class);
        $this->seed(BasicGrammarPartsOfSpeechTheorySeeder::class);
        $this->seed([
            SonatePartsOfSpeechSeeder::class,
            Opus46PartsOfSpeechSeeder::class,
            PolyglotPartsOfSpeechAllLevelsLessonSeeder::class,
        ]);

        $page = Page::query()->where('slug', 'parts-of-speech')->firstOrFail();
        $tests = app(TheoryPagePromptLinkedTestsService::class)->buildForPage($page);

        $this->assertCount(2, $tests);

        $directTest = $tests->first(fn ($test) => ! method_exists($test, 'isVirtual') || ! $test->isVirtual());
        $mixedTest = $tests->firstWhere('slug', 'theory-page-' . $page->id . '-mixed-a1-c2');

        $this->assertNotNull($directTest);
        $this->assertNotNull($mixedTest);
        $this->assertSame('polyglot-theory-pages', $directTest->filters['course_slug'] ?? null);
        $this->assertSame(
            [PolyglotPartsOfSpeechAllLevelsLessonSeeder::class],
            array_values($directTest->filters['seeder_classes'] ?? [])
        );

        $mixedFilters = $mixedTest->filters ?? [];

        $this->assertTrue($mixedTest->isVirtual());
        $this->assertTrue(($mixedFilters['__meta']['theory_page_mixed_all_levels_test'] ?? false));
        $this->assertTrue(($mixedFilters['__meta']['theory_page_mixed_polyglot_test'] ?? false));
        $this->assertSame(['A1', 'A2', 'B1', 'B2', 'C1', 'C2'], $mixedFilters['levels'] ?? []);
        $this->assertSame(
            collect([
                SonatePartsOfSpeechSeeder::class,
                Opus46PartsOfSpeechSeeder::class,
                PolyglotPartsOfSpeechAllLevelsLessonSeeder::class,
            ])->sort()->values()->all(),
            collect($mixedFilters['seeder_classes'] ?? [])->sort()->values()->all()
        );

        $response = $this->get(route('theory.show', ['basic-grammar', 'parts-of-speech']));

        $response->assertOk();
        $response->assertViewHas('topicTests', function ($topicTests) use ($page) {
            $topicTests = collect($topicTests);
            $directTest = $topicTests->first(fn ($test) => ! method_exists($test, 'isVirtual') || ! $test->isVirtual());
            $mixedTest = $topicTests->firstWhere('slug', 'theory-page-' . $page->id . '-mixed-a1-c2');

            return $topicTests->count() === 2
                && ($directTest?->filters['course_slug'] ?? null) === 'polyglot-theory-pages'
                && ($mixedTest?->filters['__meta']['theory_page_mixed_all_levels_test'] ?? false) === true;
        });
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
