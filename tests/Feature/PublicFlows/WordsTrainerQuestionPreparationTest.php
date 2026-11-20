<?php

namespace Tests\Feature\PublicFlows;

use App\Models\Word;
use App\Modules\LanguageManager\Services\LocaleService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\PublicRouteMatrix;

class WordsTrainerQuestionPreparationTest extends SeededPublicFlowTestCase
{
    private const ZERO_STATS = ['correct' => 0, 'wrong' => 0, 'total' => 0];

    protected function setUp(): void
    {
        parent::setUp();

        // Keep the shared smoke fixture unchanged for the other tests in this process.
        DB::beginTransaction();
        Schema::create('languages', function (Blueprint $table): void {
            $table->id();
            $table->string('code')->unique();
            $table->boolean('is_default');
            $table->boolean('is_active');
            $table->unsignedInteger('sort_order');
        });
        foreach (['uk', 'en', 'pl'] as $order => $code) {
            DB::table('languages')->insert([
                'code' => $code, 'is_default' => $code === 'uk', 'is_active' => true, 'sort_order' => $order,
            ]);
        }
        LocaleService::clearCache();
    }

    protected function tearDown(): void
    {
        DB::rollBack();
        LocaleService::clearCache();

        parent::tearDown();
    }

    public static function difficultiesAndLocales(): array
    {
        return ['easy-uk' => ['easy', 'uk'], 'medium-en' => ['medium', 'en'], 'hard-pl' => ['hard', 'pl']];
    }

    #[DataProvider('difficultiesAndLocales')]
    public function test_new_question_reads_only_needed_relations_and_resume_does_not_rebuild(string $difficulty, string $locale): void
    {
        $main = $this->wordId('go');
        $next = $this->wordId('come');
        $this->withSession($this->attempt($difficulty, 'pl', [$main, $next]));
        $state = $this->endpoint('state', $difficulty, $locale);
        $check = $this->endpoint('check', $difficulty, $locale);

        [$fresh, $reads] = $this->recordQuestionReads(fn () => $this->getJson($state));
        $fresh->assertOk()->assertJsonPath('siteLocale', $locale)->assertJsonPath('studyLang', 'pl')
            ->assertJsonPath('difficulty', $difficulty)->assertJsonPath('stats', self::ZERO_STATS)
            ->assertJsonPath('totalCount', 2)->assertJsonPath('completed', false)->assertJsonPath('failed', false);
        $question = $this->assertQuestion($fresh, $main, 'go', 'iść', ['Movement']);
        $this->assertNewQuestionOptions($question['options'], $difficulty, 'iść');
        $this->assertSame(['pl', 'uk'], array_values(array_intersect(['pl', 'uk'], $fresh->json('availableStudyLangs'))));
        $this->assertQuestionReads($reads, $difficulty, $main);
        $fresh->assertSessionHas("words_queue_{$difficulty}_pl", [$next]);
        $fresh->assertSessionHas("words_current_question_{$difficulty}_pl.correct_answer", 'iść');

        [$resumed, $resumedReads] = $this->recordQuestionReads(fn () => $this->getJson($state));
        $this->assertSame($fresh->json(), $resumed->assertOk()->json());
        $this->assertSame([], $resumedReads, 'Returning the stored question should not read words or their relations.');
        $resumed->assertSessionHas("words_queue_{$difficulty}_pl", [$next]);

        [$advanced, $advanceReads] = $this->recordQuestionReads(fn () => $this->postJson($check, [
            'word_id' => $main, 'answer' => $difficulty === 'easy' ? '  iść  ' : '  Iść  ',
        ]));
        $advanced->assertOk()->assertJsonPath('result', [
            'isCorrect' => true, 'correctAnswer' => 'iść', 'word' => 'go', 'questionType' => 'en_to_translation', 'translation' => 'iść',
        ])->assertJsonPath('stats', ['correct' => 1, 'wrong' => 0, 'total' => 1])
            ->assertJsonPath('percentage', 100)->assertJsonPath('completed', false);
        $nextQuestion = $this->assertQuestion($advanced, $next, 'come', 'przychodzić', []);
        $this->assertNewQuestionOptions($nextQuestion['options'], $difficulty, 'przychodzić');
        $this->assertQuestionReads($advanceReads, $difficulty, $next);

        [$finished, $finishReads] = $this->recordQuestionReads(fn () => $this->postJson($check, [
            'word_id' => $next, 'answer' => $difficulty === 'easy' ? 'Przychodzić' : 'unrelated',
        ]));
        $finished->assertOk()->assertJsonPath('result.isCorrect', false)->assertJsonPath('result.correctAnswer', 'przychodzić')
            ->assertJsonPath('stats', ['correct' => 1, 'wrong' => 1, 'total' => 2])->assertJsonPath('percentage', 50)
            ->assertJsonPath('question', null)->assertJsonPath('completed', true)->assertJsonPath('failed', false);
        $this->assertSame([], $finishReads);
    }

    public static function typedDifficulties(): array
    {
        return ['medium' => ['medium'], 'hard' => ['hard']];
    }

    #[DataProvider('typedDifficulties')]
    public function test_legacy_options_keep_the_saved_payload_and_are_not_required_for_grading(string $difficulty): void
    {
        $main = $this->wordId('go');
        $next = $this->wordId('come');
        $old = $this->storedQuestion($main);
        $this->withSession([
            ...$this->attempt($difficulty, 'pl', [$next, $main], ['correct' => 0, 'wrong' => 2, 'total' => 2]),
            "words_current_question_{$difficulty}_pl" => $old,
        ]);

        [$resume, $reads] = $this->recordQuestionReads(fn () => $this->getJson($this->endpoint('state', $difficulty)));
        $publicOld = $old;
        unset($publicOld['correct_answer']);
        $this->assertSame($publicOld, $resume->assertOk()->json('question'));
        $resume->assertSessionHas("words_current_question_{$difficulty}_pl", $old)
            ->assertSessionHas("words_queue_{$difficulty}_pl", [$next, $main]);
        $this->assertSame([], $reads);

        $check = $this->endpoint('check', $difficulty);
        $this->postJson($check, ['word_id' => $main, 'answer' => 'Iść'])
            ->assertOk()->assertJsonPath('result.isCorrect', true)->assertJsonPath('question.word_id', $next)
            ->assertJsonPath('question.options', [])->assertJsonPath('stats', ['correct' => 1, 'wrong' => 2, 'total' => 3]);

        [$failed, $reads] = $this->recordQuestionReads(fn () => $this->postJson($check, ['word_id' => $next, 'answer' => 'unrelated']));
        $failed->assertOk()->assertJsonPath('result.isCorrect', false)->assertJsonPath('question', null)
            ->assertJsonPath('stats', ['correct' => 1, 'wrong' => 3, 'total' => 4])->assertJsonPath('percentage', 25)
            ->assertJsonPath('failed', true)->assertJsonPath('completed', false)
            ->assertSessionHas("words_queue_{$difficulty}_pl", [])->assertSessionMissing("words_current_question_{$difficulty}_pl");
        $this->assertSame([], $reads, 'The third wrong answer stops the attempt without preparing another question.');
    }

    public function test_reset_keeps_language_and_does_not_touch_the_other_attempts(): void
    {
        $main = $this->wordId('go');
        $next = $this->wordId('come');
        $stats = ['correct' => 1, 'wrong' => 1, 'total' => 2];
        $this->withSession([
            ...$this->attempt('easy', 'pl', [$next], $stats),
            ...$this->attempt('medium', 'uk', [$next], $stats),
            ...$this->attempt('medium', 'pl', [$main], $stats),
            'words_current_question_medium_pl' => $this->storedQuestion($main),
        ]);
        $previous = $this->getJson($this->endpoint('state', 'medium', 'en'))->assertOk()->json();
        $this->postJson('/en/words/test/set-study-language', ['lang' => 'uk', 'difficulty' => 'medium'])->assertOk();
        $this->postJson($this->endpoint('reset', 'medium', 'en'))->assertOk()
            ->assertJsonPath('siteLocale', 'en')->assertJsonPath('studyLang', 'uk')->assertJsonPath('stats', self::ZERO_STATS)
            ->assertSessionHas('words_test_study_lang_medium', 'uk')->assertSessionHas('words_test_stats_easy_pl', $stats)
            ->assertSessionHas('words_queue_easy_pl', [$next]);
        $this->postJson('/en/words/test/set-study-language', ['lang' => 'pl', 'difficulty' => 'medium'])->assertOk();
        $this->assertSame($previous, $this->getJson($this->endpoint('state', 'medium', 'en'))->assertOk()->json());
    }

    public function test_easy_ignores_stale_words_and_keeps_unique_options_with_a_small_dictionary(): void
    {
        $main = $this->wordId('go');
        $duplicate = $this->wordId('went');
        $next = $this->wordId('come');
        $noTranslation = $this->wordId('see');
        DB::table('translates')->where('lang', 'pl')->whereNotIn('word_id', [$main, $duplicate, $next])->delete();
        DB::table('translates')->insert(['word_id' => $noTranslation, 'lang' => 'pl', 'translation' => '   ']);
        $this->withSession($this->attempt('easy', 'pl', [-1, $noTranslation, $main, $next]));

        $state = $this->getJson($this->endpoint('state', 'easy'))->assertOk()->assertSessionHas('words_queue_easy_pl', [$next]);
        $options = $this->assertQuestion($state, $main, 'go', 'iść', ['Movement'])['options'];
        sort($options);
        $this->assertSame(['iść', 'przychodzić'], $options);

        $check = $this->endpoint('check', 'easy');
        $this->postJson($check, ['word_id' => $main, 'answer' => 'iść'])->assertOk()->assertJsonPath('question.word_id', $next);
        $this->postJson($check, ['word_id' => $next, 'answer' => 'przychodzić'])->assertOk()->assertJsonPath('completed', true)
            ->assertJsonPath('question', null)->assertJsonPath('stats', ['correct' => 2, 'wrong' => 0, 'total' => 2]);
    }

    private function wordId(string $word): int
    {
        return Word::query()->where('word', $word)->value('id');
    }

    private function endpoint(string $action, string $difficulty, string $locale = 'uk'): string
    {
        return PublicRouteMatrix::localizedPath($locale, PublicRouteMatrix::wordsDifficultyPages()[$difficulty][$action]);
    }

    private function attempt(string $difficulty, string $lang, array $queue, array $stats = self::ZERO_STATS): array
    {
        return [
            "words_test_study_lang_{$difficulty}" => $lang,
            "words_queue_{$difficulty}_{$lang}" => $queue,
            "words_total_count_{$difficulty}_{$lang}" => count($queue),
            "words_test_stats_{$difficulty}_{$lang}" => $stats,
        ];
    }

    private function storedQuestion(int $wordId): array
    {
        return [
            'word_id' => $wordId, 'word' => 'go', 'translation' => 'iść', 'tags' => ['Movement'],
            'questionType' => 'en_to_translation', 'prompt' => 'go', 'options' => ['przychodzić', 'iść', 'widzieć'],
            'correct_answer' => 'iść', 'studyLang' => 'pl',
        ];
    }

    private function assertQuestion(TestResponse $response, int $id, string $word, string $translation, array $tags): array
    {
        $question = $this->currentQuestion($response->json());
        $this->assertIsArray(json_decode($response->getContent())->question->options, 'The wire options field must be a JSON array.');
        $this->assertSame([
            'word_id' => $id, 'word' => $word, 'translation' => $translation, 'tags' => $tags,
            'questionType' => 'en_to_translation', 'prompt' => $word, 'options' => $question['options'], 'studyLang' => 'pl',
        ], $question);

        return $question;
    }

    private function assertNewQuestionOptions(array $options, string $difficulty, string $answer): void
    {
        if ($difficulty !== 'easy') {
            $this->assertSame([], $options);

            return;
        }
        $this->assertContains($answer, $options);
        $this->assertLessThanOrEqual(5, count($options));
        $this->assertSame($options, array_values(array_unique($options)));
        $this->assertSame([], array_diff($options, ['iść', 'przychodzić', 'widzieć']));
    }

    private function recordQuestionReads(callable $request): array
    {
        $connection = DB::connection();
        $connection->flushQueryLog();
        $connection->enableQueryLog();
        try {
            $response = $request();
            $reads = array_values(array_filter($connection->getQueryLog(), static function (array $query): bool {
                // Exclude configuration, session and study-language availability reads.
                return str_starts_with($query['query'], 'select * from "words"')
                    || str_starts_with($query['query'], 'select * from "translates"')
                    || str_contains($query['query'], 'inner join "tag_word"');
            }));
        } finally {
            $connection->disableQueryLog();
            $connection->flushQueryLog();
        }

        return [$response, $reads];
    }

    private function assertQuestionReads(array $reads, string $difficulty, int $main): void
    {
        $this->assertCount($difficulty === 'easy' ? 5 : 3, $reads);
        $this->assertStringStartsWith('select * from "words"', $reads[0]['query']);
        $this->assertContains($main, $reads[0]['bindings']);
        $this->assertStringStartsWith('select * from "translates"', $reads[1]['query']);
        $this->assertStringContainsString('"translates"."word_id" in ('.$main.')', $reads[1]['query']);
        $this->assertStringContainsString('"tag_word"."word_id" in ('.$main.')', $reads[2]['query']);
        if ($difficulty === 'easy') {
            $this->assertStringStartsWith('select * from "words"', $reads[3]['query']);
            $this->assertStringContainsString('order by RANDOM() limit 4', $reads[3]['query']);
            $this->assertContains($main, $reads[3]['bindings']);
            $this->assertStringStartsWith('select * from "translates"', $reads[4]['query']);
        }
    }
}
