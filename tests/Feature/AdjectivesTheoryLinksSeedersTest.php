<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\Question;
use App\Models\SavedGrammarTest;
use App\Models\TextBlock;
use App\Services\SavedTestResolver;
use App\Services\TheoryPagePromptLinkedTestsService;
use Database\Seeders\Page_V3\Adjectives\AdjectivesCategorySeeder;
use Database\Seeders\Page_V3\Adjectives\AdjectivesComparativeVsSuperlativeTheorySeeder;
use Database\Seeders\Page_V3\Adjectives\AdjectivesDegreesOfComparisonTheorySeeder;
use Database\Seeders\Page_V3\Adjectives\AdjectivesEdIngAdjectivesTheorySeeder;
use Database\Seeders\Page_V3\Adjectives\AdjectivesEqualityComparisonAsAsNotAsAsTheorySeeder;
use Database\Seeders\Page_V3\Adjectives\AdjectivesIntensifiersTheorySeeder;
use Database\Seeders\Page_V3\Adjectives\AdjectivesOrderOfAdjectivesTheorySeeder;
use Database\Seeders\Page_V3\Adjectives\AdjectivesVsAdverbsTheorySeeder;
use Database\Seeders\V3\AdjectivesVsAdverbsAllLevelsV3Seeder;
use Database\Seeders\V3\ComparativeVsSuperlativeAllLevelsV3Seeder;
use Database\Seeders\V3\DegreesOfComparisonAllLevelsV3Seeder;
use Database\Seeders\V3\EdIngAdjectivesAllLevelsV3Seeder;
use Database\Seeders\V3\EqualityComparisonAsAsNotAsAsAllLevelsV3Seeder;
use Database\Seeders\V3\IntensifiersAllLevelsV3Seeder;
use Database\Seeders\V3\OrderOfAdjectivesAllLevelsV3Seeder;
use Database\Seeders\V3\Polyglot\PolyglotAdjectivesVsAdverbsAllLevelsLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotComparativeVsSuperlativeAllLevelsLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotComparativesLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotDegreesOfComparisonAllLevelsLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotEdIngAdjectivesAllLevelsLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotEqualityComparisonAsAsNotAsAsAllLevelsLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotIntensifiersAllLevelsLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotOrderOfAdjectivesAllLevelsLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotSuperlativesLessonSeeder;
use Database\Seeders\V3\TheoryLinks\AdjectivesComparativeVsSuperlativeTheoryLinksSeeder;
use Database\Seeders\V3\TheoryLinks\AdjectivesDegreesOfComparisonTheoryLinksSeeder;
use Database\Seeders\V3\TheoryLinks\AdjectivesEdIngAdjectivesTheoryLinksSeeder;
use Database\Seeders\V3\TheoryLinks\AdjectivesEqualityComparisonAsAsNotAsAsTheoryLinksSeeder;
use Database\Seeders\V3\TheoryLinks\AdjectivesIntensifiersTheoryLinksSeeder;
use Database\Seeders\V3\TheoryLinks\AdjectivesOrderOfAdjectivesTheoryLinksSeeder;
use Database\Seeders\V3\TheoryLinks\AdjectivesVsAdverbsTheoryLinksSeeder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

class AdjectivesTheoryLinksSeedersTest extends TestCase
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

    public function test_adjectives_theory_links_seeders_link_direct_polyglot_and_mixed_questions(): void
    {
        $this->seedAdjectivesStack();

        $this->seed([
            AdjectivesVsAdverbsTheoryLinksSeeder::class,
            AdjectivesDegreesOfComparisonTheoryLinksSeeder::class,
            AdjectivesComparativeVsSuperlativeTheoryLinksSeeder::class,
            AdjectivesEqualityComparisonAsAsNotAsAsTheoryLinksSeeder::class,
            AdjectivesOrderOfAdjectivesTheoryLinksSeeder::class,
            AdjectivesEdIngAdjectivesTheoryLinksSeeder::class,
            AdjectivesIntensifiersTheoryLinksSeeder::class,
        ]);

        foreach ($this->cases() as $caseName => $case) {
            $page = Page::query()->where('slug', $case['page_slug'])->firstOrFail();
            $directTest = SavedGrammarTest::query()->where('slug', $case['direct_slug'])->first();

            $this->assertNotNull($directTest, $caseName . ': direct Sentence Builder test exists.');

            $tests = app(TheoryPagePromptLinkedTestsService::class)->buildForPage($page);
            $mixedTest = $tests->firstWhere('slug', 'theory-page-' . $page->id . '-mixed-a1-c2');

            $this->assertNotNull($mixedTest, $caseName . ': Mixed A1-C2 test exists.');
            $this->assertTrue($mixedTest->isVirtual(), $caseName);

            $mixedSeederClasses = collect($mixedTest->filters['seeder_classes'] ?? [])->values();
            foreach ($case['mixed_filter_seeders'] as $expectedSeeder) {
                $this->assertTrue($mixedSeederClasses->contains($expectedSeeder), $caseName . ': ' . $expectedSeeder);
            }

            $this->assertEveryCoveredQuestionHasTheoryLinks($case['covered_seeders'], $caseName);
            $this->assertDirectSentenceBuilderQuestionsExposeTheoryBlocks($case['direct_slug'], $caseName);
            $this->assertMixedResolvedQuestionsExposeTheoryBlocks($mixedTest, $case['covered_seeders'], $caseName);

            $questionUuids = Question::query()
                ->whereIn('seeder', $case['covered_seeders'])
                ->pluck('uuid');

            $firstRunPivotRows = DB::table('question_theory_text_blocks')
                ->whereIn('question_uuid', $questionUuids)
                ->count();

            $this->seed($case['links_seeder']);

            $this->assertSame(
                $firstRunPivotRows,
                DB::table('question_theory_text_blocks')
                    ->whereIn('question_uuid', $questionUuids)
                    ->count(),
                $caseName . ': idempotency check failed.'
            );
        }

        $this->assertAuditReportsAdjectivesRoutesAsOk();
    }

    private function seedAdjectivesStack(): void
    {
        $this->seed([
            AdjectivesCategorySeeder::class,
            AdjectivesVsAdverbsTheorySeeder::class,
            AdjectivesDegreesOfComparisonTheorySeeder::class,
            AdjectivesComparativeVsSuperlativeTheorySeeder::class,
            AdjectivesEqualityComparisonAsAsNotAsAsTheorySeeder::class,
            AdjectivesOrderOfAdjectivesTheorySeeder::class,
            AdjectivesEdIngAdjectivesTheorySeeder::class,
            AdjectivesIntensifiersTheorySeeder::class,
            AdjectivesVsAdverbsAllLevelsV3Seeder::class,
            DegreesOfComparisonAllLevelsV3Seeder::class,
            ComparativeVsSuperlativeAllLevelsV3Seeder::class,
            EqualityComparisonAsAsNotAsAsAllLevelsV3Seeder::class,
            OrderOfAdjectivesAllLevelsV3Seeder::class,
            EdIngAdjectivesAllLevelsV3Seeder::class,
            IntensifiersAllLevelsV3Seeder::class,
            PolyglotComparativesLessonSeeder::class,
            PolyglotSuperlativesLessonSeeder::class,
            PolyglotAdjectivesVsAdverbsAllLevelsLessonSeeder::class,
            PolyglotDegreesOfComparisonAllLevelsLessonSeeder::class,
            PolyglotComparativeVsSuperlativeAllLevelsLessonSeeder::class,
            PolyglotEqualityComparisonAsAsNotAsAsAllLevelsLessonSeeder::class,
            PolyglotOrderOfAdjectivesAllLevelsLessonSeeder::class,
            PolyglotEdIngAdjectivesAllLevelsLessonSeeder::class,
            PolyglotIntensifiersAllLevelsLessonSeeder::class,
        ]);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function cases(): array
    {
        return [
            'adjectives_vs_adverbs' => [
                'route' => '/theory/prykmetniky-ta-pryslinknyky/adjectives-vs-adverbs',
                'page_slug' => 'adjectives-vs-adverbs',
                'direct_slug' => 'polyglot-adjectives-vs-adverbs-all-levels',
                'mixed_filter_seeders' => [
                    AdjectivesVsAdverbsAllLevelsV3Seeder::class,
                    PolyglotAdjectivesVsAdverbsAllLevelsLessonSeeder::class,
                ],
                'covered_seeders' => [
                    AdjectivesVsAdverbsAllLevelsV3Seeder::class,
                    PolyglotAdjectivesVsAdverbsAllLevelsLessonSeeder::class,
                ],
                'links_seeder' => AdjectivesVsAdverbsTheoryLinksSeeder::class,
            ],
            'degrees_of_comparison' => [
                'route' => '/theory/prykmetniky-ta-pryslinknyky/theory-degrees-of-comparison',
                'page_slug' => 'theory-degrees-of-comparison',
                'direct_slug' => 'polyglot-degrees-of-comparison-all-levels',
                'mixed_filter_seeders' => [
                    DegreesOfComparisonAllLevelsV3Seeder::class,
                    PolyglotDegreesOfComparisonAllLevelsLessonSeeder::class,
                ],
                'covered_seeders' => [
                    DegreesOfComparisonAllLevelsV3Seeder::class,
                    PolyglotDegreesOfComparisonAllLevelsLessonSeeder::class,
                    PolyglotComparativesLessonSeeder::class,
                ],
                'links_seeder' => AdjectivesDegreesOfComparisonTheoryLinksSeeder::class,
            ],
            'comparative_vs_superlative' => [
                'route' => '/theory/prykmetniky-ta-pryslinknyky/comparative-vs-superlative',
                'page_slug' => 'comparative-vs-superlative',
                'direct_slug' => 'polyglot-comparative-vs-superlative-all-levels',
                'mixed_filter_seeders' => [
                    ComparativeVsSuperlativeAllLevelsV3Seeder::class,
                    PolyglotComparativeVsSuperlativeAllLevelsLessonSeeder::class,
                ],
                'covered_seeders' => [
                    ComparativeVsSuperlativeAllLevelsV3Seeder::class,
                    PolyglotComparativeVsSuperlativeAllLevelsLessonSeeder::class,
                    PolyglotSuperlativesLessonSeeder::class,
                ],
                'links_seeder' => AdjectivesComparativeVsSuperlativeTheoryLinksSeeder::class,
            ],
            'equality_comparison' => [
                'route' => '/theory/prykmetniky-ta-pryslinknyky/equality-comparison-asas-not-asas',
                'page_slug' => 'equality-comparison-asas-not-asas',
                'direct_slug' => 'polyglot-equality-comparison-all-levels',
                'mixed_filter_seeders' => [
                    EqualityComparisonAsAsNotAsAsAllLevelsV3Seeder::class,
                    PolyglotEqualityComparisonAsAsNotAsAsAllLevelsLessonSeeder::class,
                ],
                'covered_seeders' => [
                    EqualityComparisonAsAsNotAsAsAllLevelsV3Seeder::class,
                    PolyglotEqualityComparisonAsAsNotAsAsAllLevelsLessonSeeder::class,
                ],
                'links_seeder' => AdjectivesEqualityComparisonAsAsNotAsAsTheoryLinksSeeder::class,
            ],
            'order_of_adjectives' => [
                'route' => '/theory/prykmetniky-ta-pryslinknyky/order-of-adjectives',
                'page_slug' => 'order-of-adjectives',
                'direct_slug' => 'polyglot-order-of-adjectives-all-levels',
                'mixed_filter_seeders' => [
                    OrderOfAdjectivesAllLevelsV3Seeder::class,
                    PolyglotOrderOfAdjectivesAllLevelsLessonSeeder::class,
                ],
                'covered_seeders' => [
                    OrderOfAdjectivesAllLevelsV3Seeder::class,
                    PolyglotOrderOfAdjectivesAllLevelsLessonSeeder::class,
                ],
                'links_seeder' => AdjectivesOrderOfAdjectivesTheoryLinksSeeder::class,
            ],
            'ed_ing_adjectives' => [
                'route' => '/theory/prykmetniky-ta-pryslinknyky/ed-ing-adjectives',
                'page_slug' => 'ed-ing-adjectives',
                'direct_slug' => 'polyglot-ed-ing-adjectives-all-levels',
                'mixed_filter_seeders' => [
                    EdIngAdjectivesAllLevelsV3Seeder::class,
                    PolyglotEdIngAdjectivesAllLevelsLessonSeeder::class,
                ],
                'covered_seeders' => [
                    EdIngAdjectivesAllLevelsV3Seeder::class,
                    PolyglotEdIngAdjectivesAllLevelsLessonSeeder::class,
                ],
                'links_seeder' => AdjectivesEdIngAdjectivesTheoryLinksSeeder::class,
            ],
            'intensifiers' => [
                'route' => '/theory/prykmetniky-ta-pryslinknyky/intensifiers',
                'page_slug' => 'intensifiers',
                'direct_slug' => 'polyglot-intensifiers-all-levels',
                'mixed_filter_seeders' => [
                    IntensifiersAllLevelsV3Seeder::class,
                    PolyglotIntensifiersAllLevelsLessonSeeder::class,
                ],
                'covered_seeders' => [
                    IntensifiersAllLevelsV3Seeder::class,
                    PolyglotIntensifiersAllLevelsLessonSeeder::class,
                ],
                'links_seeder' => AdjectivesIntensifiersTheoryLinksSeeder::class,
            ],
        ];
    }

    /**
     * @param  array<int, class-string>  $seederClasses
     */
    private function assertEveryCoveredQuestionHasTheoryLinks(array $seederClasses, string $caseName): void
    {
        $questions = Question::query()
            ->whereIn('seeder', $seederClasses)
            ->get(['id', 'uuid', 'seeder', 'theory_text_block_uuid']);

        foreach ($seederClasses as $seederClass) {
            $this->assertGreaterThan(
                0,
                $questions->where('seeder', $seederClass)->count(),
                $caseName . ': ' . $seederClass
            );
        }

        $pivotRows = DB::table('question_theory_text_blocks')
            ->whereIn('question_uuid', $questions->pluck('uuid'))
            ->get();

        $this->assertSame(
            $questions->count(),
            $pivotRows->pluck('question_uuid')->unique()->count(),
            $caseName . ': every covered question should have pivot rows.'
        );

        $this->assertSame(
            $questions->count(),
            $questions->whereNotNull('theory_text_block_uuid')->count(),
            $caseName . ': every covered question should set the legacy theory_text_block_uuid.'
        );

        $linkedBlockUuids = $pivotRows->pluck('text_block_uuid')->unique()->values();
        $this->assertSame(
            $linkedBlockUuids->count(),
            TextBlock::query()->whereIn('uuid', $linkedBlockUuids->all())->count(),
            $caseName . ': every linked text_block_uuid should exist.'
        );

        $legacyBlockUuids = $questions->pluck('theory_text_block_uuid')->filter()->unique()->values();
        $this->assertSame(
            $legacyBlockUuids->count(),
            TextBlock::query()->whereIn('uuid', $legacyBlockUuids->all())->count(),
            $caseName . ': every legacy text_block_uuid should exist.'
        );
    }

    private function assertDirectSentenceBuilderQuestionsExposeTheoryBlocks(string $directSlug, string $caseName): void
    {
        $resolved = app(SavedTestResolver::class)->resolve($directSlug);
        $questions = app(SavedTestResolver::class)->loadQuestions($resolved, ['theoryTextBlocks']);

        $this->assertGreaterThan(0, $questions->count(), $caseName . ': direct Sentence Builder questions.');
        $this->assertTrue(
            $questions->every(fn (Question $question): bool => $question->theoryTextBlocks->isNotEmpty()),
            $caseName . ': direct Sentence Builder questions should expose theory blocks.'
        );
    }

    /**
     * @param  array<int, class-string>  $coveredSeeders
     */
    private function assertMixedResolvedQuestionsExposeTheoryBlocks(object $mixedTest, array $coveredSeeders, string $caseName): void
    {
        $filters = $mixedTest->filters ?? [];
        $filters['num_questions'] = 84;
        $filters['randomize_filtered'] = false;

        $query = http_build_query([
            'filters' => base64_encode((string) json_encode($filters, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)),
            'name' => $mixedTest->name,
        ]);

        $request = Request::create('/test/' . $mixedTest->slug . '?' . $query, 'GET');
        $this->app->instance('request', $request);

        $resolver = app(SavedTestResolver::class);
        $resolved = $resolver->resolve($mixedTest->slug);
        $questions = $resolver->loadQuestions($resolved, ['theoryTextBlocks']);

        $this->assertGreaterThan(0, $questions->count(), $caseName . ': mixed resolved questions.');
        foreach ($coveredSeeders as $coveredSeeder) {
            $this->assertTrue(
                $questions->pluck('seeder')->contains($coveredSeeder),
                $caseName . ': mixed resolved questions should include ' . $coveredSeeder
            );
        }

        $this->assertTrue(
            $questions->every(fn (Question $question): bool => $question->theoryTextBlocks->isNotEmpty()),
            $caseName . ': mixed questions should expose theory blocks.'
        );
    }

    private function assertAuditReportsAdjectivesRoutesAsOk(): void
    {
        $jsonPath = storage_path('framework/testing/adjectives-theory-links-audit.json');
        $markdownPath = storage_path('framework/testing/adjectives-theory-links-audit.md');
        File::delete([$jsonPath, $markdownPath]);

        $this->artisan('theory-pages:audit-tests-unification', [
            '--json' => $jsonPath,
            '--md' => $markdownPath,
        ])->assertExitCode(0);

        $audit = json_decode((string) File::get($jsonPath), true);
        $this->assertIsArray($audit);

        $auditRows = collect($audit['pages'] ?? []);
        $rowsByRoute = $auditRows->keyBy('route');
        $rowsBySlug = $auditRows->keyBy('page_slug');

        foreach ($this->cases() as $caseName => $case) {
            $row = $rowsByRoute->get($case['route']) ?? $rowsBySlug->get($case['page_slug']);

            $this->assertNotNull($row, $caseName . ': route should be present in audit.');
            $this->assertSame('OK', $row['status'] ?? null, $caseName . ': audit status.');
            $this->assertEmpty(
                collect($row['missing'] ?? [])->intersect([
                    'theory_links',
                    'polyglot_theory_links',
                    'question_theory_text_blocks',
                ])->all(),
                $caseName . ': audit should not report missing theory links.'
            );
        }
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
