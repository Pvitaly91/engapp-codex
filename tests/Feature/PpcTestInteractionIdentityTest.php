<?php

namespace Tests\Feature;

use App\Http\Controllers\GrammarTestController;
use App\Http\Controllers\NewDesignTestController;
use App\Models\Category;
use App\Models\Question;
use App\Models\QuestionAnswer;
use App\Models\QuestionOption;
use App\Models\Test;
use App\Support\PastPerfectContinuousTestIdentity;
use App\Support\SavedTestJsState;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

/** Public interaction identity must not depend on an administrator's diagnostics. */
class PpcTestInteractionIdentityTest extends TestCase
{
    use RebuildsComposeTestSchema;

    private const PPC_SEEDERS = [
        'Database\\Seeders\\V3\\Tenses\\PastPerfectContinuous\\PastPerfectContinuousFormsAllLevelsV3Seeder',
        'Database\\Seeders\\V3\\Tenses\\PastPerfectContinuous\\PastPerfectContinuousNegativesAllLevelsV3Seeder',
        'Database\\Seeders\\V3\\Tenses\\PastPerfectContinuous\\PastPerfectContinuousQuestionsAllLevelsV3Seeder',
        'Database\\Seeders\\V3\\Tenses\\PastPerfectContinuous\\PastPerfectContinuousTimeExpressionsAllLevelsV3Seeder',
        'Database\\Seeders\\V3\\Polyglot\\PolyglotPastPerfectContinuousFormsAllLevelsLessonSeeder',
        'Database\\Seeders\\V3\\Polyglot\\PolyglotPastPerfectContinuousNegativesAllLevelsLessonSeeder',
        'Database\\Seeders\\V3\\Polyglot\\PolyglotPastPerfectContinuousQuestionsAllLevelsLessonSeeder',
        'Database\\Seeders\\V3\\Polyglot\\PolyglotPastPerfectContinuousTimeExpressionsAllLevelsLessonSeeder',
        'Database\\Seeders\\V3\\Polyglot\\PolyglotPastPerfectContinuousBasicsB2LessonSeeder',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        // The shared trait asserts owned SQLite before any schema operation.
        Question::flushEventListeners();
        $this->rebuildComposeTestSchema();
        session()->start();
        session()->flush();
        config(['coming-soon.enabled' => false, 'tests.tech_info_enabled' => true]);
        app()->setLocale('uk');
        $this->assertGuest();
    }

    public function test_identity_accepts_only_exact_ppc_seeders_or_the_exact_final_category(): void
    {
        foreach (self::PPC_SEEDERS as $seeder) {
            $q = $this->modelIdentity($seeder, 'English Sentence Builder');
            $this->assertTrue(PastPerfectContinuousTestIdentity::forQuestion($q), $seeder);
            $this->assertFalse(PastPerfectContinuousTestIdentity::forQuestion(
                $this->modelIdentity($seeder.'Comparison', 'English Sentence Builder')
            ), 'A seeder-name substring must not opt another bank into PPC interaction.');
        }
        foreach (['Past Perfect Continuous', 'Tenses / Past Perfect Continuous', 'Tenses / PAST PERFECT CONTINUOUS '] as $category) {
            $this->assertTrue(PastPerfectContinuousTestIdentity::forQuestion($this->modelIdentity(null, $category)), $category);
        }
        foreach (['English Sentence Builder', 'Present Perfect Continuous', 'Past Perfect vs Past Perfect Continuous',
            'Past Perfect Continuous / Conditionals', 'Tenses / Past Perfect Continuous vs Past Perfect', ''] as $category) {
            $this->assertFalse(PastPerfectContinuousTestIdentity::forQuestion($this->modelIdentity(null, $category)), $category);
        }
        $missing = new Question(['seeder' => null]);
        $missing->setRelation('category', null);
        $this->assertFalse(PastPerfectContinuousTestIdentity::forQuestion($missing));
    }

    public function test_fresh_anonymous_shells_publish_identity_without_admin_metadata(): void
    {
        [$test, $ppc, $other] = $this->fixture();
        foreach ($this->shells() as [$controller, $method, $mode]) {
            session()->flush();
            $view = app($controller)->{$method}($test->slug);
            $this->assertInstanceOf(View::class, $view);
            $data = $view->getData();
            $this->assertFalse($data['isAdmin']);
            $this->assertFalse($data['showTechnicalInfo']);
            $this->assertGuestIdentity($data['questionData'], $ppc, $other);
            $this->assertNull($data['savedState']);
        }
    }

    public function test_real_anonymous_json_endpoint_includes_the_safe_boolean_only(): void
    {
        [$test, $ppc, $other] = $this->fixture();
        $response = $this->getJson(route('test.js.questions', $test->slug));
        $response->assertOk();
        $this->assertGuestIdentity($response->json('questions'), $ppc, $other);
        $this->assertGuest();
    }

    public function test_cached_guest_shells_refresh_missing_and_forged_flags_from_the_database(): void
    {
        [$test, $ppc, $other] = $this->fixture();
        foreach ($this->shells() as [$controller, $method, $mode]) {
            session()->flush();
            $cached = $this->cachedQuestions($ppc, $other);
            session(["saved_test_js_questions:{$test->slug}" => $cached]);
            $data = app($controller)->{$method}($test->slug)->getData();
            $this->assertGuestIdentity($data['questionData'], $ppc, $other);
            $byId = collect($data['questionData'])->keyBy('id');
            $this->assertSame('Cached builder sentence {a1}.', $byId[$ppc->id]['question']);
            $this->assertSame('Cached comparison sentence {a1}.', $byId[$other->id]['question']);
            $this->assertFalse($data['showTechnicalInfo']);
        }
    }

    public function test_both_guest_shells_refresh_restored_identity_without_altering_answer_history(): void
    {
        [$test, $ppc, $other] = $this->fixture();
        foreach ($this->shells() as [$controller, $method, $mode]) {
            session()->flush();
            $cached = $this->cachedQuestions($ppc, $other);
            $progress = [
                'chosen' => ['had been making'], 'done' => true, 'wrongAttempt' => true,
                'attemptsBySlot' => [0], 'manualInputsBySlot' => ['has been making'],
                'manualWordIndexBySlot' => [0], 'activeSlot' => 0,
                'feedback' => 'Incorrect', 'feedbackMeta' => [
                    'slotIndex' => 0, 'wordIndex' => null, 'result' => 'incorrect',
                    'submittedAnswer' => 'has been making', 'displayedAnswer' => 'had been making',
                ],
            ];
            $state = ['correct' => 0, 'answered' => 1, 'current' => 0, 'activeCardIdx' => 0,
                'items' => [array_merge($cached[0], $progress), $cached[1]],
                '__meta' => ['started' => true, 'question_data' => $cached]];
            session([
                "saved_test_js_questions:{$test->slug}" => $cached,
                "saved_test_js_state:{$test->slug}:{$mode}" => $state,
            ]);
            $data = app($controller)->{$method}($test->slug)->getData();
            $this->assertGuestIdentity($data['questionData'], $ppc, $other);
            $restored = $data['savedState'];
            $this->assertTrue($restored['items'][0]['is_past_perfect_continuous']);
            $this->assertFalse($restored['items'][1]['is_past_perfect_continuous']);
            foreach ($progress as $field => $value) {
                $this->assertSame($value, $restored['items'][0][$field], $controller.' '.$field);
            }
            $this->assertSame(0, $restored['correct']);
            $this->assertSame(1, $restored['answered']);
            $this->assertTrue($restored['__meta']['question_data'][0]['is_past_perfect_continuous']);
            $this->assertFalse($restored['__meta']['question_data'][1]['is_past_perfect_continuous']);
        }
    }

    public function test_server_state_merge_takes_fresh_false_identity_instead_of_stale_true(): void
    {
        $old = ['items' => [['id' => 1, 'uuid' => 'unrelated', 'is_past_perfect_continuous' => true,
            'chosen' => ['answer'], 'done' => true, 'wrongAttempt' => false]], 'correct' => 1,
            '__meta' => ['started' => true]];
        $merged = SavedTestJsState::mergeCurrentQuestionData($old, [[
            'id' => 1, 'uuid' => 'unrelated', 'is_past_perfect_continuous' => false,
        ]]);
        $this->assertFalse($merged['items'][0]['is_past_perfect_continuous']);
        $this->assertSame(['answer'], $merged['items'][0]['chosen']);
        $this->assertTrue($merged['items'][0]['done']);
        $this->assertSame(1, $merged['correct']);
    }

    private function modelIdentity(?string $seeder, string $category): Question
    {
        $question = new Question(['seeder' => $seeder]);
        $question->setRelation('category', new Category(['name' => $category]));

        return $question;
    }

    /** @return array{Test, Question, Question} */
    private function fixture(): array
    {
        $category = Category::create(['name' => 'English Sentence Builder']);
        $correct = QuestionOption::create(['option' => 'had been making']);
        $incorrect = QuestionOption::create(['option' => 'has been making']);
        $questions = [];
        foreach ([self::PPC_SEEDERS[4], 'Database\\Seeders\\V3\\Polyglot\\PolyglotPresentPerfectContinuousFormsAllLevelsLessonSeeder'] as $seeder) {
            $q = Question::create(['uuid' => (string) Str::uuid(), 'question' => 'Mia {a1} notes before lunch.',
                'category_id' => $category->id, 'level' => 'A1', 'type' => '0', 'flag' => 0,
                'difficulty' => 1, 'seeder' => $seeder,
                'options_by_marker' => ['a1' => [$correct->option, $incorrect->option]]]);
            foreach ([$correct, $incorrect] as $option) {
                DB::table('question_option_question')->insert(['question_id' => $q->id, 'option_id' => $option->id, 'flag' => null]);
            }
            QuestionAnswer::create(['question_id' => $q->id, 'marker' => 'a1', 'option_id' => $correct->id]);
            $questions[] = $q;
        }
        $test = Test::create(['name' => 'Identity fixture', 'slug' => 'ppc-identity-'.Str::lower(Str::random(12)),
            'questions' => array_map(fn (Question $q): int => $q->id, $questions), 'filters' => []]);

        return [$test, ...$questions];
    }

    private function shells(): array
    {
        return [[GrammarTestController::class, 'showSavedTestJs', 'saved-test-js'],
            [NewDesignTestController::class, 'showSavedTestJsNewDesign', 'saved-test-js-v2']];
    }

    private function cachedQuestions(Question $ppc, Question $other): array
    {
        $base = ['type' => '0', 'tense' => 'English Sentence Builder', 'answers' => ['had been making'],
            'answer_map' => ['a1' => 'had been making'], 'markers' => ['a1'], 'markers_count' => 1,
            'options' => ['had been making', 'has been making'], 'verb_hints' => [], 'verb_hint' => '', 'tech_info' => null];

        return [array_merge($base, ['id' => $ppc->id, 'uuid' => $ppc->uuid, 'question' => 'Cached builder sentence {a1}.']),
            array_merge($base, ['id' => $other->id, 'uuid' => $other->uuid,
                'question' => 'Cached comparison sentence {a1}.', 'is_past_perfect_continuous' => true])];
    }

    private function assertGuestIdentity(array $questions, Question $ppc, Question $other): void
    {
        $this->assertCount(2, $questions);
        $byId = collect($questions)->keyBy('id');
        $this->assertTrue($byId[$ppc->id]['is_past_perfect_continuous']);
        $this->assertFalse($byId[$other->id]['is_past_perfect_continuous']);
        foreach ($questions as $question) {
            $this->assertSame('English Sentence Builder', $question['tense']);
            $this->assertNull($question['tech_info']);
            $this->assertArrayNotHasKey('seeder', $question, 'No source/debug class name is needed in the guest payload.');
            $this->assertSame(['had been making'], $question['answers']);
        }
    }
}
