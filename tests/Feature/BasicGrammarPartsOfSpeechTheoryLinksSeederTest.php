<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\Question;
use App\Models\TextBlock;
use App\Services\TheoryPagePromptLinkedTestsService;
use Database\Seeders\Page_V3\BasicGrammar\BasicGrammarCategorySeeder;
use Database\Seeders\Page_V3\BasicGrammar\BasicGrammarPartsOfSpeechTheorySeeder;
use Database\Seeders\V3\AI\Copilot\Opus46\PartsOfSpeechCastiniMoviV3QuestionsOnlySeeder as Opus46PartsOfSpeechSeeder;
use Database\Seeders\V3\AI\Copilot\Sonate\PartsOfSpeechCastiniMoviV3QuestionsOnlySeeder as SonatePartsOfSpeechSeeder;
use Database\Seeders\V3\Polyglot\PolyglotPartsOfSpeechAllLevelsLessonSeeder;
use Database\Seeders\V3\TheoryLinks\BasicGrammarPartsOfSpeechTheoryLinksSeeder;
use Illuminate\Support\Facades\DB;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

class BasicGrammarPartsOfSpeechTheoryLinksSeederTest extends TestCase
{
    use RebuildsComposeTestSchema;

    private const COVERED_SEEDERS = [
        SonatePartsOfSpeechSeeder::class,
        Opus46PartsOfSpeechSeeder::class,
        PolyglotPartsOfSpeechAllLevelsLessonSeeder::class,
    ];

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'coming-soon.enabled' => false,
            'tests.tech_info_enabled' => false,
        ]);

        $this->rebuildComposeTestSchema();
    }

    public function test_parts_of_speech_theory_links_manifest_links_direct_and_mixed_questions(): void
    {
        $this->seedPartsOfSpeechStack();
        $this->seed(BasicGrammarPartsOfSpeechTheoryLinksSeeder::class);

        $questions = Question::query()
            ->whereIn('seeder', self::COVERED_SEEDERS)
            ->get(['id', 'uuid', 'seeder', 'theory_text_block_uuid']);

        foreach (self::COVERED_SEEDERS as $seederClass) {
            $this->assertGreaterThan(
                0,
                $questions->where('seeder', $seederClass)->count(),
                $seederClass
            );
        }

        $pivotRows = DB::table('question_theory_text_blocks')
            ->whereIn('question_uuid', $questions->pluck('uuid'))
            ->get();

        $this->assertSame(
            $questions->count(),
            $pivotRows->pluck('question_uuid')->unique()->count(),
            'Every covered question should have at least one theory link.'
        );

        $questions = Question::query()
            ->whereIn('seeder', self::COVERED_SEEDERS)
            ->get(['id', 'uuid', 'seeder', 'theory_text_block_uuid']);

        $this->assertSame($questions->count(), $questions->whereNotNull('theory_text_block_uuid')->count());

        $this->assertSame(
            $pivotRows->pluck('text_block_uuid')->unique()->count(),
            TextBlock::query()
                ->whereIn('uuid', $pivotRows->pluck('text_block_uuid')->unique()->all())
                ->count(),
            'Every linked text_block_uuid should exist.'
        );

        $this->assertSame(
            $questions->pluck('theory_text_block_uuid')->filter()->unique()->count(),
            TextBlock::query()
                ->whereIn('uuid', $questions->pluck('theory_text_block_uuid')->filter()->unique()->all())
                ->count(),
            'Every legacy theory_text_block_uuid should exist.'
        );

        $this->assertTaggedQuestionHasLinkedBlock(['Noun recognition', 'Noun identification'], 3);
        $this->assertTaggedQuestionHasLinkedBlock(['Verb recognition', 'Verb identification'], 4);
        $this->assertTaggedQuestionHasLinkedBlock(['Adjective recognition', 'Adjective identification'], 5);
        $this->assertTaggedQuestionHasLinkedBlock(['Adverb recognition', 'Adverb identification'], 6);
        $this->assertTaggedQuestionHasLinkedBlock(['Pronoun usage', 'Pronoun identification', 'Reflexive pronoun'], 7);
        $this->assertTaggedQuestionHasLinkedBlock(['Preposition usage', 'Preposition identification'], 8);
        $this->assertTaggedQuestionHasLinkedBlock(['Conjunction usage', 'Conjunction identification'], 9);
        $this->assertTaggedQuestionHasLinkedBlock(['Interjection usage'], 10);

        $firstRunPivotRows = DB::table('question_theory_text_blocks')->count();
        $this->seed(BasicGrammarPartsOfSpeechTheoryLinksSeeder::class);
        $this->assertSame($firstRunPivotRows, DB::table('question_theory_text_blocks')->count());

        $page = Page::query()->where('slug', 'parts-of-speech')->firstOrFail();
        $response = $this->get(route('theory.show', ['basic-grammar', 'parts-of-speech']));

        $response->assertOk();
        $response->assertViewHas('topicTests', function ($topicTests) use ($page) {
            $slugs = collect($topicTests)->pluck('slug');

            return $slugs->contains('polyglot-parts-of-speech-all-levels')
                && $slugs->contains('theory-page-' . $page->id . '-mixed-a1-c2');
        });

        $tests = app(TheoryPagePromptLinkedTestsService::class)->buildForPage($page);
        $this->assertSame(
            [
                'polyglot-parts-of-speech-all-levels',
                'theory-page-' . $page->id . '-mixed-a1-c2',
            ],
            $tests->pluck('slug')->all()
        );

        $mixedTest = $tests->firstWhere('slug', 'theory-page-' . $page->id . '-mixed-a1-c2');
        $this->assertNotNull($mixedTest);
        $this->assertSame(
            collect(self::COVERED_SEEDERS)->sort()->values()->all(),
            collect($mixedTest->filters['seeder_classes'] ?? [])->sort()->values()->all(),
            'Mixed test should include Sonate + Opus46 + Polyglot seeders.'
        );

        $directResponse = $this->get('/test/polyglot-parts-of-speech-all-levels/step/compose');
        $directResponse->assertOk();
        $directResponse->assertSee(__('frontend.tests.question.show_theory'));

        $questionData = $directResponse->viewData('questionData');
        $this->assertIsArray($questionData);
        $this->assertNotEmpty($questionData);

        foreach ($questionData as $question) {
            $this->assertNotEmpty(
                $question['theory_blocks'] ?? [],
                'Direct Parts of Speech Polyglot question should expose theory blocks.'
            );
        }
    }

    private function seedPartsOfSpeechStack(): void
    {
        $this->seed(BasicGrammarCategorySeeder::class);
        $this->seed(BasicGrammarPartsOfSpeechTheorySeeder::class);
        $this->seed([
            PolyglotPartsOfSpeechAllLevelsLessonSeeder::class,
            SonatePartsOfSpeechSeeder::class,
            Opus46PartsOfSpeechSeeder::class,
        ]);
    }

    /**
     * @param  array<int, string>  $tagNames
     */
    private function assertTaggedQuestionHasLinkedBlock(array $tagNames, int $sortOrder): void
    {
        $question = Question::query()
            ->whereIn('seeder', self::COVERED_SEEDERS)
            ->whereHas('tags', fn ($query) => $query->whereIn('name', $tagNames))
            ->firstOrFail();

        $linkedSortOrders = TextBlock::query()
            ->join(
                'question_theory_text_blocks',
                'text_blocks.uuid',
                '=',
                'question_theory_text_blocks.text_block_uuid'
            )
            ->where('question_theory_text_blocks.question_uuid', $question->uuid)
            ->pluck('text_blocks.sort_order')
            ->map(fn ($value): int => (int) $value)
            ->all();

        $this->assertContains($sortOrder, $linkedSortOrders, implode(', ', $tagNames));
    }
}
