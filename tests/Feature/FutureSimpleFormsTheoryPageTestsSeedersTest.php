<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\Question;
use App\Models\SavedGrammarTest;
use App\Models\TextBlock;
use App\Services\TheoryPagePromptLinkedTestsService;
use Database\Seeders\Page_V3\FutureForms\FutureFormsCategorySeeder;
use Database\Seeders\Page_V3\FutureForms\FutureSimple\FutureSimpleCategorySeeder;
use Database\Seeders\Page_V3\FutureForms\FutureSimple\FutureSimpleFormsTheorySeeder;
use Database\Seeders\V3\FutureForms\FutureSimple\FutureSimpleFormsAllLevelsV3Seeder;
use Database\Seeders\V3\Polyglot\PolyglotFutureSimpleFormsAllLevelsLessonSeeder;
use Database\Seeders\V3\TheoryLinks\FutureSimpleFormsTheoryLinksSeeder;
use Illuminate\Support\Facades\DB;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

class FutureSimpleFormsTheoryPageTestsSeedersTest extends TestCase
{
    use RebuildsComposeTestSchema;

    private const LEVELS = ['A1', 'A2', 'B1', 'B2', 'C1', 'C2'];

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'coming-soon.enabled' => false,
            'tests.tech_info_enabled' => false,
        ]);

        $this->rebuildComposeTestSchema();
        $this->seed([
            FutureFormsCategorySeeder::class,
            FutureSimpleCategorySeeder::class,
            FutureSimpleFormsTheorySeeder::class,
            FutureSimpleFormsAllLevelsV3Seeder::class,
            PolyglotFutureSimpleFormsAllLevelsLessonSeeder::class,
            FutureSimpleFormsTheoryLinksSeeder::class,
        ]);
    }

    public function test_future_simple_forms_has_two_complete_a1_c2_test_packages(): void
    {
        foreach ([
            FutureSimpleFormsAllLevelsV3Seeder::class,
            PolyglotFutureSimpleFormsAllLevelsLessonSeeder::class,
        ] as $seederClass) {
            $questions = Question::query()->where('seeder', $seederClass)->get();

            $this->assertCount(72, $questions);
            foreach (self::LEVELS as $level) {
                $this->assertCount(12, $questions->where('level', $level));
            }
        }

        $standard = SavedGrammarTest::query()
            ->where('slug', 'future-simple-forms-all-levels-v3')
            ->firstOrFail();
        $polyglot = SavedGrammarTest::query()
            ->where('slug', 'polyglot-future-simple-forms-all-levels')
            ->firstOrFail();

        $this->assertSame(72, $standard->questionLinks()->count());
        $this->assertSame(72, $polyglot->questionLinks()->count());
        $this->assertSame('compose_tokens', $polyglot->filters['mode'] ?? null);
    }

    public function test_questions_are_linked_to_theory_and_surface_on_the_page(): void
    {
        $page = Page::query()->where('slug', 'future-simple-forms')->firstOrFail();
        $questions = Question::query()
            ->whereIn('seeder', [
                FutureSimpleFormsAllLevelsV3Seeder::class,
                PolyglotFutureSimpleFormsAllLevelsLessonSeeder::class,
            ])
            ->get();

        $this->assertCount(144, $questions);
        $this->assertSame(144, $questions->whereNotNull('theory_text_block_uuid')->count());

        $pivotRows = DB::table('question_theory_text_blocks')
            ->whereIn('question_uuid', $questions->pluck('uuid'))
            ->get();

        $this->assertSame(144, $pivotRows->pluck('question_uuid')->unique()->count());
        $this->assertSame(
            $pivotRows->pluck('text_block_uuid')->unique()->count(),
            TextBlock::query()->whereIn('uuid', $pivotRows->pluck('text_block_uuid')->unique())->count()
        );

        $tests = app(TheoryPagePromptLinkedTestsService::class)->buildForPage($page);
        $slugs = $tests->pluck('slug');

        $this->assertTrue($slugs->contains('polyglot-future-simple-forms-all-levels'));
        $mixed = $tests->firstWhere('slug', 'theory-page-'.$page->id.'-mixed-a1-c2');
        $this->assertNotNull($mixed);
        $this->assertTrue($mixed->isVirtual());
        $this->assertEqualsCanonicalizing([
            FutureSimpleFormsAllLevelsV3Seeder::class,
            PolyglotFutureSimpleFormsAllLevelsLessonSeeder::class,
        ], $mixed->filters['seeder_classes'] ?? []);

    }

    public function test_theory_links_seeder_is_idempotent(): void
    {
        $questionUuids = Question::query()
            ->whereIn('seeder', [
                FutureSimpleFormsAllLevelsV3Seeder::class,
                PolyglotFutureSimpleFormsAllLevelsLessonSeeder::class,
            ])
            ->pluck('uuid');

        $before = DB::table('question_theory_text_blocks')
            ->whereIn('question_uuid', $questionUuids)
            ->count();

        $this->seed(FutureSimpleFormsTheoryLinksSeeder::class);

        $this->assertSame(
            $before,
            DB::table('question_theory_text_blocks')
                ->whereIn('question_uuid', $questionUuids)
                ->count()
        );
    }
}
