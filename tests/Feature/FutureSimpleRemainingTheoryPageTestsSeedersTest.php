<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\Question;
use App\Models\SavedGrammarTest;
use App\Services\TheoryPagePromptLinkedTestsService;
use Database\Seeders\Page_V3\FutureForms\FutureFormsCategorySeeder;
use Database\Seeders\Page_V3\FutureForms\FutureSimple\FutureSimpleCategorySeeder;
use Database\Seeders\Page_V3\FutureForms\FutureSimple\FutureSimpleNegativesTheorySeeder;
use Database\Seeders\Page_V3\FutureForms\FutureSimple\FutureSimpleQuestionsTheorySeeder;
use Database\Seeders\Page_V3\FutureForms\FutureSimple\FutureSimpleTimeExpressionsTheorySeeder;
use Database\Seeders\V3\FutureForms\FutureSimple\FutureSimpleNegativesAllLevelsV3Seeder;
use Database\Seeders\V3\FutureForms\FutureSimple\FutureSimpleQuestionsAllLevelsV3Seeder;
use Database\Seeders\V3\FutureForms\FutureSimple\FutureSimpleTimeExpressionsAllLevelsV3Seeder;
use Database\Seeders\V3\Polyglot\PolyglotFutureSimpleNegativesAllLevelsLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotFutureSimpleQuestionsAllLevelsLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotFutureSimpleTimeExpressionsAllLevelsLessonSeeder;
use Database\Seeders\V3\TheoryLinks\FutureSimpleNegativesTheoryLinksSeeder;
use Database\Seeders\V3\TheoryLinks\FutureSimpleQuestionsTheoryLinksSeeder;
use Database\Seeders\V3\TheoryLinks\FutureSimpleTimeExpressionsTheoryLinksSeeder;
use Illuminate\Support\Facades\DB;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

class FutureSimpleRemainingTheoryPageTestsSeedersTest extends TestCase
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
            FutureSimpleNegativesTheorySeeder::class,
            FutureSimpleQuestionsTheorySeeder::class,
            FutureSimpleTimeExpressionsTheorySeeder::class,
            FutureSimpleNegativesAllLevelsV3Seeder::class,
            PolyglotFutureSimpleNegativesAllLevelsLessonSeeder::class,
            FutureSimpleQuestionsAllLevelsV3Seeder::class,
            PolyglotFutureSimpleQuestionsAllLevelsLessonSeeder::class,
            FutureSimpleTimeExpressionsAllLevelsV3Seeder::class,
            PolyglotFutureSimpleTimeExpressionsAllLevelsLessonSeeder::class,
            FutureSimpleNegativesTheoryLinksSeeder::class,
            FutureSimpleQuestionsTheoryLinksSeeder::class,
            FutureSimpleTimeExpressionsTheoryLinksSeeder::class,
        ]);
    }

    public function test_remaining_future_simple_pages_have_complete_linked_a1_c2_packages(): void
    {
        $topics = [
            [
                'page' => 'future-simple-negatives',
                'standard' => FutureSimpleNegativesAllLevelsV3Seeder::class,
                'polyglot' => PolyglotFutureSimpleNegativesAllLevelsLessonSeeder::class,
                'standard_slug' => 'future-simple-negatives-all-levels-v3',
                'polyglot_slug' => 'polyglot-future-simple-negatives-all-levels',
                'link_seeder' => FutureSimpleNegativesTheoryLinksSeeder::class,
            ],
            [
                'page' => 'future-simple-questions',
                'standard' => FutureSimpleQuestionsAllLevelsV3Seeder::class,
                'polyglot' => PolyglotFutureSimpleQuestionsAllLevelsLessonSeeder::class,
                'standard_slug' => 'future-simple-questions-all-levels-v3',
                'polyglot_slug' => 'polyglot-future-simple-questions-all-levels',
                'link_seeder' => FutureSimpleQuestionsTheoryLinksSeeder::class,
            ],
            [
                'page' => 'future-simple-time-expressions',
                'standard' => FutureSimpleTimeExpressionsAllLevelsV3Seeder::class,
                'polyglot' => PolyglotFutureSimpleTimeExpressionsAllLevelsLessonSeeder::class,
                'standard_slug' => 'future-simple-time-all-levels-v3',
                'polyglot_slug' => 'polyglot-future-simple-time-all-levels',
                'link_seeder' => FutureSimpleTimeExpressionsTheoryLinksSeeder::class,
            ],
        ];

        foreach ($topics as $topic) {
            foreach ([$topic['standard'], $topic['polyglot']] as $seederClass) {
                $questions = Question::query()->where('seeder', $seederClass)->get();

                $this->assertCount(72, $questions, $seederClass);
                foreach (self::LEVELS as $level) {
                    $this->assertCount(12, $questions->where('level', $level), $seederClass.' '.$level);
                }
            }

            $standard = SavedGrammarTest::query()->where('slug', $topic['standard_slug'])->firstOrFail();
            $polyglot = SavedGrammarTest::query()->where('slug', $topic['polyglot_slug'])->firstOrFail();
            $this->assertSame(72, $standard->questionLinks()->count());
            $this->assertSame(72, $polyglot->questionLinks()->count());
            $this->assertSame('compose_tokens', $polyglot->filters['mode'] ?? null);

            $questions = Question::query()
                ->whereIn('seeder', [$topic['standard'], $topic['polyglot']])
                ->get();
            $this->assertCount(144, $questions);
            $this->assertSame(144, $questions->whereNotNull('theory_text_block_uuid')->count());

            $pivot = DB::table('question_theory_text_blocks')
                ->whereIn('question_uuid', $questions->pluck('uuid'));
            $this->assertSame(144, $pivot->pluck('question_uuid')->unique()->count());

            $page = Page::query()->where('slug', $topic['page'])->firstOrFail();
            $tests = app(TheoryPagePromptLinkedTestsService::class)->buildForPage($page);
            $this->assertTrue($tests->pluck('slug')->contains($topic['polyglot_slug']));

            $mixed = $tests->firstWhere('slug', 'theory-page-'.$page->id.'-mixed-a1-c2');
            $this->assertNotNull($mixed);
            $this->assertTrue($mixed->isVirtual());
            $this->assertEqualsCanonicalizing(
                [$topic['standard'], $topic['polyglot']],
                $mixed->filters['seeder_classes'] ?? []
            );

            $before = $pivot->count();
            $this->seed($topic['link_seeder']);
            $this->assertSame($before, $pivot->count());
        }
    }
}
