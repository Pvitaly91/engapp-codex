<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\Question;
use App\Models\Tag;
use App\Models\TextBlock;
use App\Modules\SeedRunsV2\Services\SeedRunsService;
use App\Services\Theory\TextBlockToQuestionsMatcherService;
use App\Services\TheoryPagePromptLinkedTestsService;
use Database\Seeders\Page_V3\FutureForms\FutureFormsCategorySeeder;
use Database\Seeders\Page_V3\FutureForms\FuturePerfect\FuturePerfectCategorySeeder;
use Database\Seeders\Page_V3\FutureForms\FuturePerfect\FuturePerfectFormsTheorySeeder;
use Database\Seeders\Page_V3\FutureForms\FuturePerfect\FuturePerfectNegativesTheorySeeder;
use Database\Seeders\Page_V3\FutureForms\FuturePerfect\FuturePerfectQuestionsTheorySeeder;
use Database\Seeders\Page_V3\FutureForms\FuturePerfect\FuturePerfectTimeExpressionsTheorySeeder;
use Database\Seeders\V3\FutureForms\FuturePerfect\FuturePerfectFormsAllLevelsV3Seeder;
use Database\Seeders\V3\FutureForms\FuturePerfect\FuturePerfectNegativesAllLevelsV3Seeder;
use Database\Seeders\V3\FutureForms\FuturePerfect\FuturePerfectQuestionsAllLevelsV3Seeder;
use Database\Seeders\V3\FutureForms\FuturePerfect\FuturePerfectTimeExpressionsAllLevelsV3Seeder;
use Database\Seeders\V3\Polyglot\PolyglotFuturePerfectBasicsLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotFuturePerfectFormsAllLevelsLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotFuturePerfectNegativesAllLevelsLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotFuturePerfectQuestionsAllLevelsLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotFuturePerfectTimeExpressionsAllLevelsLessonSeeder;
use Database\Seeders\V3\TheoryLinks\FuturePerfectFormsTheoryLinksSeeder;
use Database\Seeders\V3\TheoryLinks\FuturePerfectNegativesTheoryLinksSeeder;
use Database\Seeders\V3\TheoryLinks\FuturePerfectQuestionsTheoryLinksSeeder;
use Database\Seeders\V3\TheoryLinks\FuturePerfectTimeExpressionsTheoryLinksSeeder;
use Illuminate\Support\Facades\DB;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

class FuturePerfectTheoryPracticeTest extends TestCase
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

    public function test_every_future_perfect_page_renders_only_its_linked_sentence_builder_pool_and_refreshes_idempotently(): void
    {
        $this->seedStack();

        $this->get('/theory/future-perfect')->assertOk();

        foreach ($this->cases() as $caseName => $case) {
            $page = Page::query()->where('slug', $case['slug'])->firstOrFail();
            $practiceBlocks = TextBlock::query()
                ->where('page_id', $page->id)
                ->where('type', 'practice-set')
                ->orderBy('locale')
                ->get();

            $this->assertCount(3, $practiceBlocks, $caseName.': UK, EN, and PL practice blocks.');
            $this->assertEqualsCanonicalizing(['uk', 'en', 'pl'], $practiceBlocks->pluck('locale')->all());

            foreach ($practiceBlocks as $practiceBlock) {
                $pool = app(TextBlockToQuestionsMatcherService::class)
                    ->findBestQuestionsForTextBlock($practiceBlock, 500);

                $this->assertCount(72, $pool, $caseName.': complete inline pool.');
                $this->assertTrue(
                    $pool->every(fn (Question $question): bool => $question->seeder === $case['builder_seeder']),
                    $caseName.': pool must use the exact page Sentence Builder seeder.'
                );
                $this->assertTrue(
                    $pool->every(fn (Question $question): bool => (string) $question->type === Question::TYPE_COMPOSE_TOKENS),
                    $caseName.': pool must contain only compose-token questions.'
                );

                $this->assertPoolGrammar($pool, $case['topic'], $caseName);
            }

            $mixed = app(TheoryPagePromptLinkedTestsService::class)
                ->buildForPage($page)
                ->firstWhere('slug', 'theory-page-'.$page->id.'-mixed-a1-c2');

            $this->assertNotNull($mixed, $caseName.': mixed test exists.');
            $this->assertEqualsCanonicalizing(
                $case['mixed_seeders'],
                $mixed->filters['seeder_classes'] ?? [],
                $caseName.': mixed pool excludes legacy or sibling topic seeders.'
            );

            foreach (['', '/en', '/pl'] as $localePrefix) {
                $response = $this->get($localePrefix.$case['route']);
                $response->assertOk();
                $html = (string) $response->getContent();

                $this->assertStringContainsString('data-sentence-builder', $html, $caseName);
                $this->assertStringContainsString('correct_tokens', $html, $caseName);
                $this->assertSentenceBuilderHasNoTextInput($html, $caseName);

                foreach ($this->removedManualPracticeTexts() as $removedText) {
                    $this->assertStringNotContainsString($removedText, $html, $caseName);
                }
            }
        }

        $beforeRefresh = $this->contentSnapshot();
        $pageSeeders = $this->pageSeeders();

        foreach ([1, 2] as $run) {
            $result = app(SeedRunsService::class)->refreshSeedersByClassNames($pageSeeders, [
                'atomic' => true,
                'rollback_on_error' => true,
            ]);

            $this->assertTrue((bool) ($result['success'] ?? false), "Refresh $run should succeed.");
            $this->assertEmpty(collect($result['errors'] ?? [])->all(), "Refresh $run errors.");
            $this->assertEqualsCanonicalizing($pageSeeders, collect($result['selected_ran'] ?? [])->all());
            $this->assertSame($beforeRefresh, $this->contentSnapshot(), "Refresh $run must be duplicate-safe.");
        }

        foreach ($this->cases() as $caseName => $case) {
            $page = Page::query()->where('slug', $case['slug'])->firstOrFail();
            $practiceBlock = TextBlock::query()
                ->where('page_id', $page->id)
                ->where('type', 'practice-set')
                ->where('locale', 'uk')
                ->firstOrFail();
            $pool = app(TextBlockToQuestionsMatcherService::class)
                ->findBestQuestionsForTextBlock($practiceBlock, 500);

            $this->assertCount(72, $pool, $caseName.': linked pool survives repeated page refresh.');
        }
    }

    private function seedStack(): void
    {
        $this->seed([
            FutureFormsCategorySeeder::class,
            FuturePerfectCategorySeeder::class,
            ...$this->pageSeeders(),
            FuturePerfectFormsAllLevelsV3Seeder::class,
            FuturePerfectNegativesAllLevelsV3Seeder::class,
            FuturePerfectQuestionsAllLevelsV3Seeder::class,
            FuturePerfectTimeExpressionsAllLevelsV3Seeder::class,
            PolyglotFuturePerfectFormsAllLevelsLessonSeeder::class,
            PolyglotFuturePerfectNegativesAllLevelsLessonSeeder::class,
            PolyglotFuturePerfectQuestionsAllLevelsLessonSeeder::class,
            PolyglotFuturePerfectTimeExpressionsAllLevelsLessonSeeder::class,
            PolyglotFuturePerfectBasicsLessonSeeder::class,
            FuturePerfectFormsTheoryLinksSeeder::class,
            FuturePerfectNegativesTheoryLinksSeeder::class,
            FuturePerfectQuestionsTheoryLinksSeeder::class,
            FuturePerfectTimeExpressionsTheoryLinksSeeder::class,
        ]);
    }

    /** @return array<int, class-string> */
    private function pageSeeders(): array
    {
        return [
            FuturePerfectFormsTheorySeeder::class,
            FuturePerfectNegativesTheorySeeder::class,
            FuturePerfectQuestionsTheorySeeder::class,
            FuturePerfectTimeExpressionsTheorySeeder::class,
        ];
    }

    /** @return array<string, array<string, mixed>> */
    private function cases(): array
    {
        return [
            'forms' => [
                'slug' => 'future-perfect-forms',
                'route' => '/theory/maibutni-formy/future-perfect/future-perfect-forms',
                'topic' => 'forms',
                'builder_seeder' => PolyglotFuturePerfectFormsAllLevelsLessonSeeder::class,
                'mixed_seeders' => [
                    FuturePerfectFormsAllLevelsV3Seeder::class,
                    PolyglotFuturePerfectFormsAllLevelsLessonSeeder::class,
                ],
            ],
            'negatives' => [
                'slug' => 'future-perfect-negatives',
                'route' => '/theory/maibutni-formy/future-perfect/future-perfect-negatives',
                'topic' => 'negatives',
                'builder_seeder' => PolyglotFuturePerfectNegativesAllLevelsLessonSeeder::class,
                'mixed_seeders' => [
                    FuturePerfectNegativesAllLevelsV3Seeder::class,
                    PolyglotFuturePerfectNegativesAllLevelsLessonSeeder::class,
                ],
            ],
            'questions' => [
                'slug' => 'future-perfect-questions',
                'route' => '/theory/maibutni-formy/future-perfect/future-perfect-questions',
                'topic' => 'questions',
                'builder_seeder' => PolyglotFuturePerfectQuestionsAllLevelsLessonSeeder::class,
                'mixed_seeders' => [
                    FuturePerfectQuestionsAllLevelsV3Seeder::class,
                    PolyglotFuturePerfectQuestionsAllLevelsLessonSeeder::class,
                ],
            ],
            'time expressions' => [
                'slug' => 'future-perfect-time-expressions',
                'route' => '/theory/maibutni-formy/future-perfect/future-perfect-time-expressions',
                'topic' => 'time',
                'builder_seeder' => PolyglotFuturePerfectTimeExpressionsAllLevelsLessonSeeder::class,
                'mixed_seeders' => [
                    FuturePerfectTimeExpressionsAllLevelsV3Seeder::class,
                    PolyglotFuturePerfectTimeExpressionsAllLevelsLessonSeeder::class,
                ],
            ],
        ];
    }

    private function assertPoolGrammar($pool, string $topic, string $caseName): void
    {
        foreach ($pool as $question) {
            $answer = $question->answers
                ->sortBy(fn ($item): string => sprintf('%s%08d', preg_replace('/\d+$/', '', $item->marker), (int) preg_replace('/\D+/', '', $item->marker)))
                ->map(fn ($item): string => trim((string) ($item->option->option ?? '')))
                ->filter()
                ->implode(' ');
            $normalized = mb_strtolower($answer);

            $this->assertStringNotContainsString('will have been', $normalized, $caseName.': no Future Perfect Continuous.');

            if ($topic === 'forms') {
                $this->assertMatchesRegularExpression('/\bwill\s+have\b/u', $normalized);
                $this->assertDoesNotMatchRegularExpression('/\b(?:will\s+not|won[’\']?t)\s+have\b/u', $normalized);
            } elseif ($topic === 'negatives') {
                $this->assertMatchesRegularExpression('/\b(?:will\s+not|won[’\']?t)\s+have\b/u', $normalized);
            } elseif ($topic === 'questions') {
                $this->assertMatchesRegularExpression('/^(?:(?:by\s+(?:when|what\s+time))|will|what|when|where|why|who|which|how)\b/u', $normalized);
                $this->assertMatchesRegularExpression('/\bwill\b.+\bhave\b/u', $normalized);
            } else {
                $this->assertMatchesRegularExpression('/\bwill\s+have\b/u', $normalized);
                $this->assertMatchesRegularExpression('/\b(?:by|before|in)\b/u', $normalized);
            }
        }
    }

    private function assertSentenceBuilderHasNoTextInput(string $html, string $caseName): void
    {
        $document = new \DOMDocument;
        @$document->loadHTML($html);
        $xpath = new \DOMXPath($document);
        $builders = $xpath->query('//*[@data-sentence-builder]');

        $this->assertNotFalse($builders);
        $this->assertGreaterThan(0, $builders->length, $caseName.': builder node.');

        foreach ($builders as $builder) {
            $this->assertSame(0, $xpath->query('.//input[@type="text"]', $builder)->length, $caseName.': no full-sentence input.');
        }
    }

    /** @return array<int, string> */
    private function removedManualPracticeTexts(): array
    {
        return [
            'Переклади речення',
            'Напиши англійською',
            'Translate the sentence',
            'Write in English',
            'Przetłumacz zdanie',
            'Napisz po angielsku',
            'data-word-suggestion-input="rephrase-',
            'data-word-suggestion-input="inputs-',
        ];
    }

    /** @return array<string, mixed> */
    private function contentSnapshot(): array
    {
        $pageIds = Page::query()->whereIn('slug', collect($this->cases())->pluck('slug'))->pluck('id');
        $questionSeeders = collect($this->cases())
            ->flatMap(fn (array $case): array => $case['mixed_seeders'])
            ->push(PolyglotFuturePerfectBasicsLessonSeeder::class)
            ->unique()
            ->values();
        $questionUuids = Question::query()->whereIn('seeder', $questionSeeders)->pluck('uuid');
        $blockUuids = TextBlock::query()->whereIn('page_id', $pageIds)->orderBy('uuid')->pluck('uuid')->all();
        $pivots = DB::table('question_theory_text_blocks')
            ->whereIn('question_uuid', $questionUuids)
            ->get(['question_uuid', 'text_block_uuid']);

        return [
            'pages' => $pageIds->count(),
            'blocks' => count($blockUuids),
            'block_uuids' => $blockUuids,
            'questions' => $questionUuids->count(),
            'tags' => Tag::query()->count(),
            'pivots' => $pivots->count(),
            'unique_pivots' => $pivots
                ->unique(fn ($pivot): string => $pivot->question_uuid.':'.$pivot->text_block_uuid)
                ->count(),
            'seed_runs' => DB::table('seed_runs')->whereIn('class_name', $this->pageSeeders())->count(),
        ];
    }
}
