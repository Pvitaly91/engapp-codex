<?php

namespace Tests\Unit;

use App\Http\Controllers\TestJsV2Controller;
use App\Models\Category;
use App\Models\Question;
use App\Models\QuestionAnswer;
use App\Models\QuestionOption;
use App\Services\PolyglotLessonImportService;
use App\Support\Database\QuestionUuidResolver;
use Database\Seeders\V3\Polyglot\PolyglotHaveGotHasGotLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotArticlesAAnTheLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotBeGoingToLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotCanCannotLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotComparativesLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotFinalDrillLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotFinalDrillA2LessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotFutureSimpleWillLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotGerundVsInfinitiveLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotMuchManyALotOfLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotFirstConditionalLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotPastContinuousLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotPassiveVoiceBasicsLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotPresentPerfectTimeExpressionsLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotQuestionTagsBasicsLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotReportedSpeechBasicsLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotRelativeClausesLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotSecondConditionalBasicsLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotShouldOughtToLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotMustHaveToLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotUsedToLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotPastSimpleIrregularVerbsLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotPastSimpleRegularVerbsLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotPastSimpleToBeLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotPresentContinuousLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotPresentPerfectBasicLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotPresentPerfectContinuousBasicsLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotPresentPerfectContinuousVsPresentPerfectLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotPastPerfectBasicsLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotNarrativeTensesBasicsLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotPresentPerfectVsPastSimpleLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotPresentSimpleVerbsLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotSomeAnyLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotSuperlativesLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotThereIsThereAreLessonSeeder;
use Illuminate\Support\Str;
use ReflectionMethod;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

class ComposePayloadBuilderTest extends TestCase
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

    public function test_numeric_marker_sort_is_stable_for_compose_payload(): void
    {
        $question = $this->createComposeQuestion(
            'Сортування маркерів.',
            [
                ['marker' => 'a10', 'value' => 'token-10'],
                ['marker' => 'a2', 'value' => 'token-2'],
                ['marker' => 'a1', 'value' => 'token-1'],
                ['marker' => 'a9', 'value' => 'token-9'],
                ['marker' => 'a3', 'value' => 'token-3'],
                ['marker' => 'a4', 'value' => 'token-4'],
                ['marker' => 'a5', 'value' => 'token-5'],
                ['marker' => 'a6', 'value' => 'token-6'],
                ['marker' => 'a7', 'value' => 'token-7'],
                ['marker' => 'a8', 'value' => 'token-8'],
            ],
            ['token-1', 'token-2', 'token-3', 'token-4', 'token-5', 'token-6', 'token-7', 'token-8', 'token-9', 'token-10', 'noise']
        );

        $payload = $this->buildComposePayload($question);

        $this->assertSame(
            ['token-1', 'token-2', 'token-3', 'token-4', 'token-5', 'token-6', 'token-7', 'token-8', 'token-9', 'token-10'],
            $payload['correctTokenValues']
        );
        $this->assertSame(
            ['c1', 'c2', 'c3', 'c4', 'c5', 'c6', 'c7', 'c8', 'c9', 'c10'],
            $payload['correctTokenIds']
        );
    }

    public function test_duplicate_tokens_are_preserved_as_separate_instances(): void
    {
        $question = $this->createComposeQuestion(
            'Ми дуже готові.',
            [
                ['marker' => 'a1', 'value' => 'We'],
                ['marker' => 'a2', 'value' => 'are'],
                ['marker' => 'a3', 'value' => 'are'],
                ['marker' => 'a4', 'value' => 'ready'],
            ],
            ['We', 'are', 'ready', 'not']
        );

        $payload = $this->buildComposePayload($question);
        $areInstances = collect($payload['tokenBank'])->where('value', 'are')->values();

        $this->assertSame(['We', 'are', 'are', 'ready'], $payload['correctTokenValues']);
        $this->assertCount(4, $payload['correctTokenIds']);
        $this->assertSame(4, count(array_unique($payload['correctTokenIds'])));
        $this->assertCount(2, $areInstances);
        $this->assertTrue($areInstances->every(fn (array $token) => $token['isCorrect'] === true));
    }

    public function test_imported_lesson_payload_remains_compose_compatible(): void
    {
        $definition = [
            'test' => [
                'name' => 'Polyglot: duplicate token fixture',
                'slug' => 'polyglot-duplicate-token-fixture',
                'description_uk' => 'Fixture for duplicate token import.',
                'course_slug' => 'polyglot-english-a1',
                'lesson_order' => 99,
                'previous_lesson_slug' => null,
                'next_lesson_slug' => null,
                'topic' => 'to be',
                'level' => 'A1',
                'interface_locale' => 'uk',
                'study_locale' => 'uk',
                'target_locale' => 'en',
                'mode' => 'compose_tokens',
                'question_type' => 4,
                'completion' => [
                    'rolling_window' => 100,
                    'min_rating' => 4.5,
                ],
            ],
            'items' => [
                [
                    'uuid' => 'polyglot-duplicate-token-q01',
                    'source_text_uk' => 'Вони там і готові.',
                    'target_text' => 'They are there and are ready.',
                    'tokens_correct' => ['They', 'are', 'there', 'and', 'are', 'ready'],
                    'distractors' => ['is', 'not', 'here'],
                    'hint_uk' => 'Схема: they + are + there + and + are + ready',
                    'grammar_tags' => ['to-be', 'present-simple', 'affirmative', 'a1'],
                ],
            ],
        ];

        app(PolyglotLessonImportService::class)->import($definition, true);

        $question = Question::query()
            ->where('uuid', 'polyglot-duplicate-token-q01')
            ->firstOrFail()
            ->load(['answers.option', 'options', 'hints', 'chatgptExplanations']);

        $payload = $this->buildComposePayload($question);
        $areInstances = collect($payload['tokenBank'])
            ->where('value', 'are')
            ->where('isCorrect', true)
            ->values();

        $this->assertSame(['They', 'are', 'there', 'and', 'are', 'ready'], $payload['correctTokenValues']);
        $this->assertSame(['c1', 'c2', 'c3', 'c4', 'c5', 'c6'], $payload['correctTokenIds']);
        $this->assertCount(2, $areInstances);
        $this->assertSame(
            ['They', 'are', 'there', 'and', 'are', 'ready', 'is', 'not', 'here'],
            collect($payload['tokenBank'])->pluck('value')->all()
        );
        $this->assertSame('They are there and are ready.', $payload['correctText']);
    }

    public function test_v3_polyglot_lesson_payload_remains_compose_compatible(): void
    {
        $this->seed(PolyglotThereIsThereAreLessonSeeder::class);

        $question = Question::query()
            ->where('uuid', 'polyglot-there-is-are-q23')
            ->firstOrFail()
            ->load(['answers.option', 'options', 'hints', 'chatgptExplanations']);

        $payload = $this->buildComposePayload($question);

        $this->assertSame(
            ['How', 'many', 'chairs', 'are', 'there', 'in', 'the', 'room'],
            $payload['correctTokenValues']
        );
        $this->assertSame(
            ['How', 'many', 'chairs', 'are', 'there', 'in', 'the', 'room', 'is', 'a', 'box'],
            collect($payload['tokenBank'])->pluck('value')->all()
        );
        $this->assertSame('How many chairs are there in the room?', $payload['correctText']);
        $this->assertSame(
            'Схема wh-питання: How many + іменник + are there + місце?',
            $payload['hintUk']
        );
    }

    public function test_have_got_v3_polyglot_lesson_payload_remains_compose_compatible(): void
    {
        $this->seed(PolyglotHaveGotHasGotLessonSeeder::class);

        $question = Question::query()
            ->where('uuid', 'polyglot-have-got-has-got-q24')
            ->firstOrFail()
            ->load(['answers.option', 'options', 'hints', 'chatgptExplanations']);

        $payload = $this->buildComposePayload($question);

        $this->assertSame(
            ['How', 'many', 'books', 'has', 'she', 'got'],
            $payload['correctTokenValues']
        );
        $this->assertSame(
            ['How', 'many', 'books', 'has', 'she', 'got', 'have', 'a', 'the'],
            collect($payload['tokenBank'])->pluck('value')->all()
        );
        $this->assertSame('How many books has she got?', $payload['correctText']);
        $this->assertSame(
            'Схема wh-питання: How many + іменник + has + she + got?',
            $payload['hintUk']
        );
    }

    public function test_present_simple_v3_polyglot_lesson_payload_remains_compose_compatible(): void
    {
        $this->seed(PolyglotPresentSimpleVerbsLessonSeeder::class);

        $question = Question::query()
            ->where('uuid', 'polyglot-present-simple-verbs-q23')
            ->firstOrFail()
            ->load(['answers.option', 'options', 'hints', 'chatgptExplanations']);

        $payload = $this->buildComposePayload($question);

        $this->assertSame(
            ['Where', 'does', 'she', 'work'],
            $payload['correctTokenValues']
        );
        $this->assertEqualsCanonicalizing(
            ['Where', 'does', 'she', 'work', 'do', 'they', 'works'],
            collect($payload['tokenBank'])->pluck('value')->all()
        );
        $this->assertSame('Where does she work?', $payload['correctText']);
        $this->assertSame(
            'Схема wh-питання: Where + does + she + V1?',
            $payload['hintUk']
        );
    }

    public function test_can_cannot_v3_polyglot_lesson_payload_remains_compose_compatible(): void
    {
        $this->seed(PolyglotCanCannotLessonSeeder::class);

        $question = Question::query()
            ->where('uuid', 'polyglot-can-cannot-q23')
            ->firstOrFail()
            ->load(['answers.option', 'options', 'hints', 'chatgptExplanations']);

        $payload = $this->buildComposePayload($question);

        $this->assertSame(
            ['Where', 'can', 'he', 'work'],
            $payload['correctTokenValues']
        );
        $this->assertEqualsCanonicalizing(
            ['Where', 'can', 'he', 'work', 'cannot', 'does', 'they'],
            collect($payload['tokenBank'])->pluck('value')->all()
        );
        $this->assertSame('Where can he work?', $payload['correctText']);
        $this->assertSame(
            'Схема wh-питання: Where + can + he + V1?',
            $payload['hintUk']
        );
    }

    public function test_present_continuous_v3_polyglot_lesson_payload_remains_compose_compatible(): void
    {
        $this->seed(PolyglotPresentContinuousLessonSeeder::class);

        $question = Question::query()
            ->where('uuid', 'polyglot-present-continuous-q23')
            ->firstOrFail()
            ->load(['answers.option', 'options', 'hints', 'chatgptExplanations']);

        $payload = $this->buildComposePayload($question);

        $this->assertSame(
            ['What', 'is', 'she', 'reading', 'now'],
            $payload['correctTokenValues']
        );
        $this->assertSame(
            ['What', 'is', 'she', 'reading', 'now', 'are', 'they', 'book'],
            collect($payload['tokenBank'])->pluck('value')->all()
        );
        $this->assertSame('What is she reading now?', $payload['correctText']);
        $this->assertSame(
            'Схема wh-питання: What + is + she + V-ing + now?',
            $payload['hintUk']
        );
    }

    public function test_past_simple_to_be_v3_polyglot_lesson_payload_remains_compose_compatible(): void
    {
        $this->seed(PolyglotPastSimpleToBeLessonSeeder::class);

        $question = Question::query()
            ->where('uuid', 'polyglot-past-simple-to-be-q24')
            ->firstOrFail()
            ->load(['answers.option', 'options', 'hints', 'chatgptExplanations']);

        $payload = $this->buildComposePayload($question);

        $this->assertSame(
            ['Why', 'were', 'we', 'here', 'yesterday'],
            $payload['correctTokenValues']
        );
        $this->assertEqualsCanonicalizing(
            ['Why', 'were', 'we', 'here', 'yesterday', 'was', 'am', 'he'],
            collect($payload['tokenBank'])->pluck('value')->all()
        );
        $this->assertSame('Why were we here yesterday?', $payload['correctText']);
        $this->assertSame(
            'Схема wh-питання: Why + were + we + here + yesterday?',
            $payload['hintUk']
        );
    }

    public function test_past_simple_regular_verbs_v3_polyglot_lesson_payload_remains_compose_compatible(): void
    {
        $this->seed(PolyglotPastSimpleRegularVerbsLessonSeeder::class);

        $question = Question::query()
            ->where('uuid', app(QuestionUuidResolver::class)->toPersistent('polyglot-past-simple-regular-verbs-q24'))
            ->firstOrFail()
            ->load(['answers.option', 'options', 'hints', 'chatgptExplanations']);

        $payload = $this->buildComposePayload($question);

        $this->assertSame(
            ['Why', 'did', 'they', 'open', 'the', 'window', 'yesterday'],
            $payload['correctTokenValues']
        );
        $this->assertEqualsCanonicalizing(
            ['Why', 'did', 'they', 'open', 'the', 'window', 'yesterday', 'does', 'he', 'opened'],
            collect($payload['tokenBank'])->pluck('value')->all()
        );
        $this->assertSame('Why did they open the window yesterday?', $payload['correctText']);
        $this->assertSame(
            'Схема wh-питання: Why + did + they + V1 + the + додаток + yesterday?',
            $payload['hintUk']
        );
    }

    public function test_past_simple_irregular_verbs_v3_polyglot_lesson_payload_remains_compose_compatible(): void
    {
        $this->seed(PolyglotPastSimpleIrregularVerbsLessonSeeder::class);

        $question = Question::query()
            ->where('uuid', app(QuestionUuidResolver::class)->toPersistent('polyglot-past-simple-irregular-verbs-q24'))
            ->firstOrFail()
            ->load(['answers.option', 'options', 'hints', 'chatgptExplanations']);

        $payload = $this->buildComposePayload($question);

        $this->assertSame(
            ['Why', 'did', 'they', 'come', 'early', 'yesterday'],
            $payload['correctTokenValues']
        );
        $this->assertEqualsCanonicalizing(
            ['Why', 'did', 'they', 'come', 'early', 'yesterday', 'does', 'he', 'came'],
            collect($payload['tokenBank'])->pluck('value')->all()
        );
        $this->assertSame('Why did they come early yesterday?', $payload['correctText']);
        $this->assertSame(
            'Схема wh-питання: Why + did + they + V1 + обставина + yesterday?',
            $payload['hintUk']
        );
    }

    public function test_future_simple_will_v3_polyglot_lesson_payload_remains_compose_compatible(): void
    {
        $this->seed(PolyglotFutureSimpleWillLessonSeeder::class);

        $question = Question::query()
            ->where('uuid', app(QuestionUuidResolver::class)->toPersistent('polyglot-future-simple-will-q24'))
            ->firstOrFail()
            ->load(['answers.option', 'options', 'hints', 'chatgptExplanations']);

        $payload = $this->buildComposePayload($question);

        $this->assertSame(
            ['What', 'will', 'we', 'see', 'tomorrow'],
            $payload['correctTokenValues']
        );
        $this->assertEqualsCanonicalizing(
            ['What', 'will', 'we', 'see', 'tomorrow', 'do', 'does', 'he'],
            collect($payload['tokenBank'])->pluck('value')->all()
        );
        $this->assertSame('What will we see tomorrow?', $payload['correctText']);
        $this->assertSame(
            'Схема wh-питання: What + will + we + V1 + tomorrow?',
            $payload['hintUk']
        );
    }

    public function test_articles_a_an_the_v3_polyglot_lesson_payload_remains_compose_compatible(): void
    {
        $this->seed(PolyglotArticlesAAnTheLessonSeeder::class);

        $question = Question::query()
            ->where('uuid', app(QuestionUuidResolver::class)->toPersistent('polyglot-articles-a-an-the-q24'))
            ->firstOrFail()
            ->load(['answers.option', 'options', 'hints', 'chatgptExplanations']);

        $payload = $this->buildComposePayload($question);
        $correctTheInstances = collect($payload['tokenBank'])
            ->where('value', 'the')
            ->where('isCorrect', true)
            ->values();

        $this->assertSame(
            ['Is', 'the', 'book', 'on', 'the', 'table'],
            $payload['correctTokenValues']
        );
        $this->assertSame(
            ['Is', 'the', 'book', 'on', 'the', 'table', 'a', 'an', 'under'],
            collect($payload['tokenBank'])->pluck('value')->all()
        );
        $this->assertCount(2, $correctTheInstances);
        $this->assertSame('Is the book on the table?', $payload['correctText']);
        $this->assertSame(
            'Ставимо the, коли говоримо про конкретні або вже відомі предмети; тут правильна форма містить два the.',
            $payload['hintUk']
        );
    }

    public function test_some_any_v3_polyglot_lesson_payload_remains_compose_compatible(): void
    {
        $this->seed(PolyglotSomeAnyLessonSeeder::class);

        $question = Question::query()
            ->where('uuid', app(QuestionUuidResolver::class)->toPersistent('polyglot-some-any-q24'))
            ->firstOrFail()
            ->load(['answers.option', 'options', 'hints', 'chatgptExplanations']);

        $payload = $this->buildComposePayload($question);

        $this->assertSame(
            ['Do', 'you', 'see', 'any', 'birds'],
            $payload['correctTokenValues']
        );
        $this->assertEqualsCanonicalizing(
            ['Do', 'you', 'see', 'any', 'birds', 'some', 'Does', 'cat'],
            collect($payload['tokenBank'])->pluck('value')->all()
        );
        $this->assertSame('Do you see any birds?', $payload['correctText']);
        $this->assertSame(
            'Схема питання: Do + you + see + any + plural noun?',
            $payload['hintUk']
        );
    }

    public function test_much_many_a_lot_of_v3_polyglot_lesson_payload_remains_compose_compatible(): void
    {
        $this->seed(PolyglotMuchManyALotOfLessonSeeder::class);

        $aLotOfQuestion = Question::query()
            ->where('uuid', 'polyglot-much-many-a-lot-of-q01')
            ->firstOrFail()
            ->load(['answers.option', 'options', 'hints', 'chatgptExplanations']);
        $howManyQuestion = Question::query()
            ->where('uuid', 'polyglot-much-many-a-lot-of-q24')
            ->firstOrFail()
            ->load(['answers.option', 'options', 'hints', 'chatgptExplanations']);

        $aLotOfPayload = $this->buildComposePayload($aLotOfQuestion);
        $howManyPayload = $this->buildComposePayload($howManyQuestion);

        $this->assertSame(
            ['I', 'have', 'a', 'lot', 'of', 'books'],
            $aLotOfPayload['correctTokenValues']
        );
        $this->assertSame(
            ['I', 'have', 'a', 'lot', 'of', 'books', 'many', 'much', 'book'],
            collect($aLotOfPayload['tokenBank'])->pluck('value')->all()
        );
        $this->assertSame('I have a lot of books.', $aLotOfPayload['correctText']);
        $this->assertSame(
            ['How', 'many', 'cars', 'do', 'you', 'see', 'here'],
            $howManyPayload['correctTokenValues']
        );
        $this->assertEqualsCanonicalizing(
            ['How', 'many', 'cars', 'do', 'you', 'see', 'here', 'much', 'does', 'water'],
            collect($howManyPayload['tokenBank'])->pluck('value')->all()
        );
        $this->assertSame('How many cars do you see here?', $howManyPayload['correctText']);
        $this->assertSame(
            'How many + plural noun + do/does ...?',
            $howManyPayload['hintUk']
        );
    }

    public function test_comparatives_v3_polyglot_lesson_payload_remains_compose_compatible(): void
    {
        $this->seed(PolyglotComparativesLessonSeeder::class);

        $moreQuestion = Question::query()
            ->where('uuid', 'polyglot-comparatives-q08')
            ->firstOrFail()
            ->load(['answers.option', 'options', 'hints', 'chatgptExplanations']);
        $duplicateQuestion = Question::query()
            ->where('uuid', 'polyglot-comparatives-q01')
            ->firstOrFail()
            ->load(['answers.option', 'options', 'hints', 'chatgptExplanations']);

        $morePayload = $this->buildComposePayload($moreQuestion);
        $duplicatePayload = $this->buildComposePayload($duplicateQuestion);
        $bookInstances = collect($morePayload['tokenBank'])
            ->where('value', 'book')
            ->where('isCorrect', true)
            ->values();
        $houseInstances = collect($duplicatePayload['tokenBank'])
            ->where('value', 'house')
            ->where('isCorrect', true)
            ->values();

        $this->assertSame(
            ['This', 'book', 'is', 'more', 'interesting', 'than', 'that', 'book'],
            $morePayload['correctTokenValues']
        );
        $this->assertSame(
            ['This', 'book', 'is', 'more', 'interesting', 'than', 'that', 'book', 'car', 'most', 'boring'],
            collect($morePayload['tokenBank'])->pluck('value')->all()
        );
        $this->assertCount(2, $bookInstances);
        $this->assertSame('This book is more interesting than that book.', $morePayload['correctText']);
        $this->assertSame(
            'Для довших прикметників використовуємо more + adjective.',
            $morePayload['hintUk']
        );

        $this->assertSame(
            ['This', 'house', 'is', 'bigger', 'than', 'that', 'house'],
            $duplicatePayload['correctTokenValues']
        );
        $this->assertCount(2, $houseInstances);
        $this->assertSame('This house is bigger than that house.', $duplicatePayload['correctText']);
    }

    public function test_superlatives_v3_polyglot_lesson_payload_remains_compose_compatible(): void
    {
        $this->seed(PolyglotSuperlativesLessonSeeder::class);

        $mostQuestion = Question::query()
            ->where('uuid', 'polyglot-superlatives-q08')
            ->firstOrFail()
            ->load(['answers.option', 'options', 'hints', 'chatgptExplanations']);
        $duplicateQuestion = Question::query()
            ->where('uuid', 'polyglot-superlatives-q15')
            ->firstOrFail()
            ->load(['answers.option', 'options', 'hints', 'chatgptExplanations']);

        $mostPayload = $this->buildComposePayload($mostQuestion);
        $duplicatePayload = $this->buildComposePayload($duplicateQuestion);
        $theInstances = collect($duplicatePayload['tokenBank'])
            ->where('value', 'the')
            ->where('isCorrect', true)
            ->values();

        $this->assertSame(
            ['This', 'book', 'is', 'the', 'most', 'interesting'],
            $mostPayload['correctTokenValues']
        );
        $this->assertSame(
            ['This', 'book', 'is', 'the', 'most', 'interesting', 'more', 'boring', 'car'],
            collect($mostPayload['tokenBank'])->pluck('value')->all()
        );
        $this->assertSame('This book is the most interesting.', $mostPayload['correctText']);
        $this->assertSame(
            'Для довших прикметників використовуємо the most + adjective.',
            $mostPayload['hintUk']
        );

        $this->assertSame(
            ['Is', 'the', 'train', 'the', 'fastest'],
            $duplicatePayload['correctTokenValues']
        );
        $this->assertCount(2, $theInstances);
        $this->assertSame('Is the train the fastest?', $duplicatePayload['correctText']);
    }

    public function test_final_drill_v3_polyglot_lesson_payload_remains_compose_compatible(): void
    {
        $this->seed(PolyglotFinalDrillLessonSeeder::class);

        $aLotOfQuestion = Question::query()
            ->where('uuid', 'polyglot-final-drill-q02')
            ->firstOrFail()
            ->load(['answers.option', 'options', 'hints', 'chatgptExplanations']);
        $duplicateQuestion = Question::query()
            ->where('uuid', 'polyglot-final-drill-q13')
            ->firstOrFail()
            ->load(['answers.option', 'options', 'hints', 'chatgptExplanations']);
        $mostQuestion = Question::query()
            ->where('uuid', 'polyglot-final-drill-q23')
            ->firstOrFail()
            ->load(['answers.option', 'options', 'hints', 'chatgptExplanations']);
        $articleQuestion = Question::query()
            ->where('uuid', 'polyglot-final-drill-q15')
            ->firstOrFail()
            ->load(['answers.option', 'options', 'hints', 'chatgptExplanations']);

        $aLotOfPayload = $this->buildComposePayload($aLotOfQuestion);
        $duplicatePayload = $this->buildComposePayload($duplicateQuestion);
        $mostPayload = $this->buildComposePayload($mostQuestion);
        $articlePayload = $this->buildComposePayload($articleQuestion);

        $houseInstances = collect($duplicatePayload['tokenBank'])
            ->where('value', 'house')
            ->where('isCorrect', true)
            ->values();

        $this->assertSame(
            ['There', 'are', 'a', 'lot', 'of', 'apples', 'on', 'the', 'table'],
            $aLotOfPayload['correctTokenValues']
        );
        $this->assertSame(
            ['There', 'are', 'a', 'lot', 'of', 'apples', 'on', 'the', 'table', 'is', 'many', 'water'],
            collect($aLotOfPayload['tokenBank'])->pluck('value')->all()
        );

        $this->assertSame(
            ['Is', 'this', 'house', 'bigger', 'than', 'that', 'house'],
            $duplicatePayload['correctTokenValues']
        );
        $this->assertCount(2, $houseInstances);
        $this->assertSame('Is this house bigger than that house?', $duplicatePayload['correctText']);

        $this->assertSame(
            ['Which', 'chair', 'is', 'the', 'most', 'comfortable'],
            $mostPayload['correctTokenValues']
        );
        $this->assertSame(
            ['Which', 'chair', 'is', 'the', 'most', 'comfortable', 'more', 'What', 'hard'],
            collect($mostPayload['tokenBank'])->pluck('value')->all()
        );
        $this->assertSame('Which chair is the most comfortable?', $mostPayload['correctText']);

        $this->assertSame(
            ['Did', 'he', 'see', 'the', 'dog', 'yesterday'],
            $articlePayload['correctTokenValues']
        );
        $this->assertEqualsCanonicalizing(
            ['Did', 'he', 'see', 'the', 'dog', 'yesterday', 'Does', 'saw', 'they'],
            collect($articlePayload['tokenBank'])->pluck('value')->all()
        );
        $this->assertSame('Did he see the dog yesterday?', $articlePayload['correctText']);
    }

    public function test_present_perfect_basic_a2_v3_polyglot_lesson_payload_remains_compose_compatible(): void
    {
        $this->seed(PolyglotPresentPerfectBasicLessonSeeder::class);

        $resolver = app(QuestionUuidResolver::class);
        $affirmativeQuestion = Question::query()
            ->where('uuid', $resolver->toPersistent('polyglot-present-perfect-basic-a2-q01'))
            ->firstOrFail()
            ->load(['answers.option', 'options', 'hints', 'chatgptExplanations']);
        $questionQuestion = Question::query()
            ->where('uuid', $resolver->toPersistent('polyglot-present-perfect-basic-a2-q15'))
            ->firstOrFail()
            ->load(['answers.option', 'options', 'hints', 'chatgptExplanations']);
        $whQuestion = Question::query()
            ->where('uuid', $resolver->toPersistent('polyglot-present-perfect-basic-a2-q24'))
            ->firstOrFail()
            ->load(['answers.option', 'options', 'hints', 'chatgptExplanations']);

        $affirmativePayload = $this->buildComposePayload($affirmativeQuestion);
        $questionPayload = $this->buildComposePayload($questionQuestion);
        $whPayload = $this->buildComposePayload($whQuestion);

        $this->assertSame(
            ['I', 'have', 'already', 'finished', 'work'],
            $affirmativePayload['correctTokenValues']
        );
        $this->assertSame(
            ['I', 'have', 'already', 'finished', 'work', 'has', 'finish', 'the'],
            collect($affirmativePayload['tokenBank'])->pluck('value')->all()
        );
        $this->assertSame('I have already finished work.', $affirmativePayload['correctText']);

        $this->assertSame(
            ['Have', 'you', 'finished', 'work', 'yet'],
            $questionPayload['correctTokenValues']
        );
        $this->assertEqualsCanonicalizing(
            ['Have', 'you', 'finished', 'work', 'yet', 'Has', 'finish', 'already'],
            collect($questionPayload['tokenBank'])->pluck('value')->all()
        );
        $this->assertSame('Have you finished work yet?', $questionPayload['correctText']);

        $this->assertSame(
            ['How', 'many', 'letters', 'have', 'you', 'written', 'today'],
            $whPayload['correctTokenValues']
        );
        $this->assertEqualsCanonicalizing(
            ['How', 'many', 'letters', 'have', 'you', 'written', 'today', 'has', 'wrote', 'already'],
            collect($whPayload['tokenBank'])->pluck('value')->all()
        );
        $this->assertSame('How many letters have you written today?', $whPayload['correctText']);
        $this->assertSame(
            'Схема wh-питання: How many + noun + have + you + V3 + today?',
            $whPayload['hintUk']
        );
    }

    public function test_present_perfect_vs_past_simple_a2_v3_polyglot_lesson_payload_remains_compose_compatible(): void
    {
        $this->seed(PolyglotPresentPerfectVsPastSimpleLessonSeeder::class);

        $resolver = app(QuestionUuidResolver::class);
        $presentPerfectQuestion = Question::query()
            ->where('uuid', $resolver->toPersistent('polyglot-present-perfect-vs-past-simple-a2-q01'))
            ->firstOrFail()
            ->load(['answers.option', 'options', 'hints', 'chatgptExplanations']);
        $pastSimpleQuestion = Question::query()
            ->where('uuid', $resolver->toPersistent('polyglot-present-perfect-vs-past-simple-a2-q13'))
            ->firstOrFail()
            ->load(['answers.option', 'options', 'hints', 'chatgptExplanations']);
        $pastSimpleWhQuestion = Question::query()
            ->where('uuid', $resolver->toPersistent('polyglot-present-perfect-vs-past-simple-a2-q24'))
            ->firstOrFail()
            ->load(['answers.option', 'options', 'hints', 'chatgptExplanations']);

        $presentPerfectPayload = $this->buildComposePayload($presentPerfectQuestion);
        $pastSimplePayload = $this->buildComposePayload($pastSimpleQuestion);
        $pastSimpleWhPayload = $this->buildComposePayload($pastSimpleWhQuestion);

        $this->assertSame(
            ['I', 'have', 'already', 'finished', 'my', 'homework'],
            $presentPerfectPayload['correctTokenValues']
        );
        $this->assertSame(
            ['I', 'have', 'already', 'finished', 'my', 'homework', 'has', 'finish', 'yesterday'],
            collect($presentPerfectPayload['tokenBank'])->pluck('value')->all()
        );
        $this->assertSame('I have already finished my homework.', $presentPerfectPayload['correctText']);

        $this->assertSame(
            ['I', 'finished', 'my', 'homework', 'yesterday'],
            $pastSimplePayload['correctTokenValues']
        );
        $this->assertSame(
            ['I', 'finished', 'my', 'homework', 'yesterday', 'have', 'already', 'finish'],
            collect($pastSimplePayload['tokenBank'])->pluck('value')->all()
        );
        $this->assertSame('I finished my homework yesterday.', $pastSimplePayload['correctText']);

        $this->assertSame(
            ['How', 'many', 'books', 'did', 'you', 'read', 'last', 'month'],
            $pastSimpleWhPayload['correctTokenValues']
        );
        $this->assertEqualsCanonicalizing(
            ['How', 'many', 'books', 'did', 'you', 'read', 'last', 'month', 'have', 'this', 'reads'],
            collect($pastSimpleWhPayload['tokenBank'])->pluck('value')->all()
        );
        $this->assertSame('How many books did you read last month?', $pastSimpleWhPayload['correctText']);
        $this->assertSame(
            'Past Simple wh-question with last month.',
            $pastSimpleWhPayload['hintUk']
        );
    }

    public function test_first_conditional_a2_v3_polyglot_lesson_payload_remains_compose_compatible(): void
    {
        $this->seed(PolyglotFirstConditionalLessonSeeder::class);

        $resolver = app(QuestionUuidResolver::class);
        $questionPayload = $this->buildComposePayload(
            Question::query()
                ->where('uuid', $resolver->toPersistent('polyglot-first-conditional-a2-q20'))
                ->firstOrFail()
                ->load(['answers.option', 'options', 'hints', 'chatgptExplanations'])
        );
        $whPayload = $this->buildComposePayload(
            Question::query()
                ->where('uuid', $resolver->toPersistent('polyglot-first-conditional-a2-q24'))
                ->firstOrFail()
                ->load(['answers.option', 'options', 'hints', 'chatgptExplanations'])
        );

        $youInstances = collect($questionPayload['tokenBank'])
            ->where('value', 'you')
            ->where('isCorrect', true)
            ->values();
        $sheInstances = collect($whPayload['tokenBank'])
            ->where('value', 'she')
            ->where('isCorrect', true)
            ->values();

        $this->assertSame(
            ['Will', 'you', 'tell', 'Anna', 'if', 'you', 'see', 'her'],
            $questionPayload['correctTokenValues']
        );
        $this->assertEqualsCanonicalizing(
            ['Will', 'you', 'tell', 'Anna', 'if', 'you', 'see', 'her', 'Do', 'would', 'saw'],
            collect($questionPayload['tokenBank'])->pluck('value')->all()
        );
        $this->assertCount(2, $youInstances);
        $this->assertSame('Will you tell Anna if you see her?', $questionPayload['correctText']);

        $this->assertSame(
            ['What', 'will', 'she', 'do', 'if', 'she', 'misses', 'the', 'bus'],
            $whPayload['correctTokenValues']
        );
        $this->assertEqualsCanonicalizing(
            ['What', 'will', 'she', 'do', 'if', 'she', 'misses', 'the', 'bus', 'would', 'did', 'missed'],
            collect($whPayload['tokenBank'])->pluck('value')->all()
        );
        $this->assertCount(2, $sheInstances);
        $this->assertSame('What will she do if she misses the bus?', $whPayload['correctText']);
        $this->assertSame(
            'Схема wh-питання: What + will + subject + V1 + if + Present Simple?',
            $whPayload['hintUk']
        );
    }

    public function test_be_going_to_a2_v3_polyglot_lesson_payload_remains_compose_compatible(): void
    {
        $this->seed(PolyglotBeGoingToLessonSeeder::class);

        $resolver = app(QuestionUuidResolver::class);
        $questionPayload = $this->buildComposePayload(
            Question::query()
                ->where('uuid', $resolver->toPersistent('polyglot-be-going-to-a2-q20'))
                ->firstOrFail()
                ->load(['answers.option', 'options', 'hints', 'chatgptExplanations'])
        );
        $whPayload = $this->buildComposePayload(
            Question::query()
                ->where('uuid', $resolver->toPersistent('polyglot-be-going-to-a2-q24'))
                ->firstOrFail()
                ->load(['answers.option', 'options', 'hints', 'chatgptExplanations'])
        );

        $sheInstances = collect($questionPayload['tokenBank'])
            ->where('value', 'she')
            ->where('isCorrect', true)
            ->values();
        $goingInstances = collect($questionPayload['tokenBank'])
            ->where('value', 'going')
            ->where('isCorrect', true)
            ->values();
        $youInstances = collect($whPayload['tokenBank'])
            ->where('value', 'you')
            ->where('isCorrect', true)
            ->values();
        $toInstances = collect($whPayload['tokenBank'])
            ->where('value', 'to')
            ->where('isCorrect', true)
            ->values();

        $this->assertSame(
            ['Is', 'she', 'going', 'to', 'start', 'a', 'new', 'job', 'next', 'month'],
            $questionPayload['correctTokenValues']
        );
        $this->assertEqualsCanonicalizing(
            ['Is', 'she', 'going', 'to', 'start', 'a', 'new', 'job', 'next', 'month', 'Will', 'starting', 'last'],
            collect($questionPayload['tokenBank'])->pluck('value')->all()
        );
        $this->assertCount(1, $sheInstances);
        $this->assertCount(1, $goingInstances);
        $this->assertSame('Is she going to start a new job next month?', $questionPayload['correctText']);

        $this->assertSame(
            ['Who', 'are', 'you', 'going', 'to', 'invite', 'to', 'the', 'party'],
            $whPayload['correctTokenValues']
        );
        $this->assertEqualsCanonicalizing(
            ['Who', 'are', 'you', 'going', 'to', 'invite', 'to', 'the', 'party', 'Will', 'inviting', 'yesterday'],
            collect($whPayload['tokenBank'])->pluck('value')->all()
        );
        $this->assertCount(1, $youInstances);
        $this->assertCount(2, $toInstances);
        $this->assertSame('Who are you going to invite to the party?', $whPayload['correctText']);
        $this->assertSame(
            'Схема wh-питання: Wh-word + be + subject + going to + V1?',
            $whPayload['hintUk']
        );
    }

    public function test_should_ought_to_a2_v3_polyglot_lesson_payload_remains_compose_compatible(): void
    {
        $this->seed(PolyglotShouldOughtToLessonSeeder::class);

        $resolver = app(QuestionUuidResolver::class);
        $questionPayload = $this->buildComposePayload(
            Question::query()
                ->where('uuid', $resolver->toPersistent('polyglot-should-ought-to-a2-q21'))
                ->firstOrFail()
                ->load(['answers.option', 'options', 'hints', 'chatgptExplanations'])
        );
        $whPayload = $this->buildComposePayload(
            Question::query()
                ->where('uuid', $resolver->toPersistent('polyglot-should-ought-to-a2-q24'))
                ->firstOrFail()
                ->load(['answers.option', 'options', 'hints', 'chatgptExplanations'])
        );

        $oughtInstances = collect($questionPayload['tokenBank'])
            ->where('value', 'Ought')
            ->where('isCorrect', true)
            ->values();
        $toInstances = collect($questionPayload['tokenBank'])
            ->where('value', 'to')
            ->where('isCorrect', true)
            ->values();
        $oughtLowerInstances = collect($whPayload['tokenBank'])
            ->where('value', 'ought')
            ->where('isCorrect', true)
            ->values();
        $shouldDistractors = collect($whPayload['tokenBank'])
            ->where('value', 'should')
            ->where('isCorrect', false)
            ->values();

        $this->assertSame(
            ['Ought', 'I', 'to', 'take', 'a', 'taxi'],
            $questionPayload['correctTokenValues']
        );
        $this->assertEqualsCanonicalizing(
            ['Ought', 'I', 'to', 'take', 'a', 'taxi', 'Should', 'did', 'must'],
            collect($questionPayload['tokenBank'])->pluck('value')->all()
        );
        $this->assertCount(1, $oughtInstances);
        $this->assertCount(1, $toInstances);
        $this->assertSame('Ought I to take a taxi?', $questionPayload['correctText']);

        $this->assertSame(
            ['Who', 'ought', 'to', 'call', 'the', 'police'],
            $whPayload['correctTokenValues']
        );
        $this->assertEqualsCanonicalizing(
            ['Who', 'ought', 'to', 'call', 'the', 'police', 'should', 'did', 'must'],
            collect($whPayload['tokenBank'])->pluck('value')->all()
        );
        $this->assertCount(1, $oughtLowerInstances);
        $this->assertCount(1, $shouldDistractors);
        $this->assertSame('Who ought to call the police?', $whPayload['correctText']);
        $this->assertSame(
            'Схема wh-питання: Who + ought to + V1?',
            $whPayload['hintUk']
        );
    }

    public function test_must_have_to_a2_v3_polyglot_lesson_payload_remains_compose_compatible(): void
    {
        $this->seed(PolyglotMustHaveToLessonSeeder::class);

        $resolver = app(QuestionUuidResolver::class);
        $negativePayload = $this->buildComposePayload(
            Question::query()
                ->where('uuid', $resolver->toPersistent('polyglot-must-have-to-a2-q15'))
                ->firstOrFail()
                ->load(['answers.option', 'options', 'hints', 'chatgptExplanations'])
        );
        $whPayload = $this->buildComposePayload(
            Question::query()
                ->where('uuid', $resolver->toPersistent('polyglot-must-have-to-a2-q23'))
                ->firstOrFail()
                ->load(['answers.option', 'options', 'hints', 'chatgptExplanations'])
        );

        $doInstances = collect($whPayload['tokenBank'])
            ->where('value', 'do')
            ->where('isCorrect', true)
            ->values();
        $doesDistractors = collect($negativePayload['tokenBank'])
            ->where('value', 'do')
            ->where('isCorrect', false)
            ->values();

        $this->assertSame(
            ['She', 'does', 'not', 'have', 'to', 'cook', 'tonight'],
            $negativePayload['correctTokenValues']
        );
        $this->assertEqualsCanonicalizing(
            ['She', 'does', 'not', 'have', 'to', 'cook', 'tonight', 'must', 'should', 'do'],
            collect($negativePayload['tokenBank'])->pluck('value')->all()
        );
        $this->assertCount(1, $doesDistractors);
        $this->assertSame('She does not have to cook tonight.', $negativePayload['correctText']);

        $this->assertSame(
            ['What', 'do', 'I', 'have', 'to', 'do', 'now'],
            $whPayload['correctTokenValues']
        );
        $this->assertEqualsCanonicalizing(
            ['What', 'do', 'I', 'have', 'to', 'do', 'now', 'must', 'does', 'should'],
            collect($whPayload['tokenBank'])->pluck('value')->all()
        );
        $this->assertCount(2, $doInstances);
        $this->assertSame('What do I have to do now?', $whPayload['correctText']);
        $this->assertSame(
            'Схема wh-питання: Wh-word + do/does + subject + have to + V1?',
            $whPayload['hintUk']
        );
    }

    public function test_gerund_vs_infinitive_a2_v3_polyglot_lesson_payload_remains_compose_compatible(): void
    {
        $this->seed(PolyglotGerundVsInfinitiveLessonSeeder::class);

        $resolver = app(QuestionUuidResolver::class);
        $planPayload = $this->buildComposePayload(
            Question::query()
                ->where('uuid', $resolver->toPersistent('polyglot-gerund-vs-infinitive-a2-q17'))
                ->firstOrFail()
                ->load(['answers.option', 'options', 'hints', 'chatgptExplanations'])
        );
        $whPayload = $this->buildComposePayload(
            Question::query()
                ->where('uuid', $resolver->toPersistent('polyglot-gerund-vs-infinitive-a2-q23'))
                ->firstOrFail()
                ->load(['answers.option', 'options', 'hints', 'chatgptExplanations'])
        );

        $toInstances = collect($planPayload['tokenBank'])
            ->where('value', 'to')
            ->where('isCorrect', true)
            ->values();
        $doInstances = collect($whPayload['tokenBank'])
            ->where('value', 'do')
            ->where('isCorrect', true)
            ->values();

        $this->assertSame(
            ['He', 'plans', 'to', 'travel', 'to', 'Italy', 'next', 'summer'],
            $planPayload['correctTokenValues']
        );
        $this->assertEqualsCanonicalizing(
            ['He', 'plans', 'to', 'travel', 'to', 'Italy', 'next', 'summer', 'travelling', 'go', 'France'],
            collect($planPayload['tokenBank'])->pluck('value')->all()
        );
        $this->assertCount(2, $toInstances);
        $this->assertSame('He plans to travel to Italy next summer.', $planPayload['correctText']);

        $this->assertSame(
            ['What', 'do', 'you', 'hope', 'to', 'do', 'after', 'university'],
            $whPayload['correctTokenValues']
        );
        $this->assertEqualsCanonicalizing(
            ['What', 'do', 'you', 'hope', 'to', 'do', 'after', 'university', 'doing', 'did', 'school'],
            collect($whPayload['tokenBank'])->pluck('value')->all()
        );
        $this->assertCount(2, $doInstances);
        $this->assertSame('What do you hope to do after university?', $whPayload['correctText']);
        $this->assertSame(
            'Схема wh-питання: hope + infinitive',
            $whPayload['hintUk']
        );
    }

    public function test_past_continuous_a2_v3_polyglot_lesson_payload_remains_compose_compatible(): void
    {
        $this->seed(PolyglotPastContinuousLessonSeeder::class);

        $resolver = app(QuestionUuidResolver::class);
        $waitingPayload = $this->buildComposePayload(
            Question::query()
                ->where('uuid', $resolver->toPersistent('polyglot-past-continuous-a2-q03'))
                ->firstOrFail()
                ->load(['answers.option', 'options', 'hints', 'chatgptExplanations'])
        );
        $workingPayload = $this->buildComposePayload(
            Question::query()
                ->where('uuid', $resolver->toPersistent('polyglot-past-continuous-a2-q05'))
                ->firstOrFail()
                ->load(['answers.option', 'options', 'hints', 'chatgptExplanations'])
        );

        $correctTheInstances = collect($waitingPayload['tokenBank'])
            ->where('value', 'the')
            ->where('isCorrect', true)
            ->values();
        $correctAtInstances = collect($workingPayload['tokenBank'])
            ->where('value', 'at')
            ->where('isCorrect', true)
            ->values();

        $this->assertSame(
            ['We', 'were', 'waiting', 'for', 'the', 'bus', 'at', 'seven', "o'clock", 'in', 'the', 'morning'],
            $waitingPayload['correctTokenValues']
        );
        $this->assertSame(
            ['We', 'were', 'waiting', 'for', 'the', 'bus', 'at', 'seven', "o'clock", 'in', 'the', 'morning', 'was', 'wait', 'car'],
            collect($waitingPayload['tokenBank'])->pluck('value')->all()
        );
        $this->assertCount(2, $correctTheInstances);
        $this->assertSame(
            'We were waiting for the bus at seven o\'clock in the morning.',
            $waitingPayload['correctText']
        );

        $this->assertSame(
            ['He', 'was', 'working', 'at', 'home', 'at', 'that', 'moment'],
            $workingPayload['correctTokenValues']
        );
        $this->assertSame(
            ['He', 'was', 'working', 'at', 'home', 'at', 'that', 'moment', 'were', 'work', 'office'],
            collect($workingPayload['tokenBank'])->pluck('value')->all()
        );
        $this->assertCount(2, $correctAtInstances);
        $this->assertSame('He was working at home at that moment.', $workingPayload['correctText']);
        $this->assertSame(
            'Схема: subject + was/were + V-ing + time phrase',
            $workingPayload['hintUk']
        );
    }

    public function test_present_perfect_time_expressions_a2_v3_polyglot_lesson_payload_remains_compose_compatible(): void
    {
        $this->seed(PolyglotPresentPerfectTimeExpressionsLessonSeeder::class);

        $resolver = app(QuestionUuidResolver::class);
        $forPayload = $this->buildComposePayload(
            Question::query()
                ->where('uuid', $resolver->toPersistent('polyglot-present-perfect-time-expressions-a2-q13'))
                ->firstOrFail()
                ->load(['answers.option', 'options', 'hints', 'chatgptExplanations'])
        );
        $unfinishedTimePayload = $this->buildComposePayload(
            Question::query()
                ->where('uuid', $resolver->toPersistent('polyglot-present-perfect-time-expressions-a2-q24'))
                ->firstOrFail()
                ->load(['answers.option', 'options', 'hints', 'chatgptExplanations'])
        );

        $this->assertSame(
            ['Have', 'you', 'lived', 'here', 'for', 'a', 'long', 'time'],
            $forPayload['correctTokenValues']
        );
        $this->assertSame(
            ['Have', 'you', 'lived', 'here', 'for', 'a', 'long', 'time', 'since', 'live', 'Has'],
            collect($forPayload['tokenBank'])->pluck('value')->all()
        );
        $this->assertSame('Have you lived here for a long time?', $forPayload['correctText']);

        $this->assertSame(
            ['How', 'many', 'books', 'have', 'they', 'read', 'this', 'month'],
            $unfinishedTimePayload['correctTokenValues']
        );
        $this->assertSame(
            ['How', 'many', 'books', 'have', 'they', 'read', 'this', 'month', 'has', 'last', 'did'],
            collect($unfinishedTimePayload['tokenBank'])->pluck('value')->all()
        );
        $this->assertSame('How many books have they read this month?', $unfinishedTimePayload['correctText']);
        $this->assertSame(
            'This month = незавершений період.',
            $unfinishedTimePayload['hintUk']
        );
    }

    public function test_relative_clauses_a2_v3_polyglot_lesson_payload_remains_compose_compatible(): void
    {
        $this->seed(PolyglotRelativeClausesLessonSeeder::class);

        $resolver = app(QuestionUuidResolver::class);
        $placePayload = $this->buildComposePayload(
            Question::query()
                ->where('uuid', $resolver->toPersistent('polyglot-relative-clauses-a2-q12'))
                ->firstOrFail()
                ->load(['answers.option', 'options', 'hints', 'chatgptExplanations'])
        );
        $whPayload = $this->buildComposePayload(
            Question::query()
                ->where('uuid', $resolver->toPersistent('polyglot-relative-clauses-a2-q22'))
                ->firstOrFail()
                ->load(['answers.option', 'options', 'hints', 'chatgptExplanations'])
        );

        $theInstances = collect($placePayload['tokenBank'])
            ->where('value', 'the')
            ->where('isCorrect', true)
            ->values();
        $isInstances = collect($whPayload['tokenBank'])
            ->where('value', 'is')
            ->where('isCorrect', true)
            ->values();

        $this->assertSame(
            ['I', 'remember', 'the', 'town', 'where', 'we', 'spent', 'the', 'summer'],
            $placePayload['correctTokenValues']
        );
        $this->assertSame(
            ['I', 'remember', 'the', 'town', 'where', 'we', 'spent', 'the', 'summer', 'who', 'that', 'winter'],
            collect($placePayload['tokenBank'])->pluck('value')->all()
        );
        $this->assertCount(2, $theInstances);
        $this->assertSame('I remember the town where we spent the summer.', $placePayload['correctText']);

        $this->assertSame(
            ['Who', 'is', 'the', 'woman', 'who', 'is', 'talking', 'to', 'Tom'],
            $whPayload['correctTokenValues']
        );
        $this->assertSame(
            ['Who', 'is', 'the', 'woman', 'who', 'is', 'talking', 'to', 'Tom', 'man', 'What', 'are'],
            collect($whPayload['tokenBank'])->pluck('value')->all()
        );
        $this->assertCount(2, $isInstances);
        $this->assertSame('Who is the woman who is talking to Tom?', $whPayload['correctText']);
        $this->assertSame(
            'Схема wh-питання: Who/What/Where ... + relative clause',
            $whPayload['hintUk']
        );
    }

    public function test_passive_voice_basics_a2_v3_polyglot_lesson_payload_remains_compose_compatible(): void
    {
        $this->seed(PolyglotPassiveVoiceBasicsLessonSeeder::class);

        $resolver = app(QuestionUuidResolver::class);
        $wasPayload = $this->buildComposePayload(
            Question::query()
                ->where('uuid', $resolver->toPersistent('polyglot-passive-voice-basics-a2-q19'))
                ->firstOrFail()
                ->load(['answers.option', 'options', 'hints', 'chatgptExplanations'])
        );
        $werePayload = $this->buildComposePayload(
            Question::query()
                ->where('uuid', $resolver->toPersistent('polyglot-passive-voice-basics-a2-q20'))
                ->firstOrFail()
                ->load(['answers.option', 'options', 'hints', 'chatgptExplanations'])
        );
        $isPayload = $this->buildComposePayload(
            Question::query()
                ->where('uuid', $resolver->toPersistent('polyglot-passive-voice-basics-a2-q23'))
                ->firstOrFail()
                ->load(['answers.option', 'options', 'hints', 'chatgptExplanations'])
        );
        $arePayload = $this->buildComposePayload(
            Question::query()
                ->where('uuid', $resolver->toPersistent('polyglot-passive-voice-basics-a2-q24'))
                ->firstOrFail()
                ->load(['answers.option', 'options', 'hints', 'chatgptExplanations'])
        );

        $this->assertSame(
            ['Was', 'the', 'bridge', 'built', 'in', '1990'],
            $wasPayload['correctTokenValues']
        );
        $this->assertSame(
            ['Was', 'the', 'bridge', 'built', 'in', '1990', 'yesterday', 'build', 'Were'],
            collect($wasPayload['tokenBank'])->pluck('value')->all()
        );
        $this->assertSame('Was the bridge built in 1990?', $wasPayload['correctText']);

        $this->assertSame(
            ['Were', 'the', 'windows', 'washed', 'last', 'week'],
            $werePayload['correctTokenValues']
        );
        $this->assertSame(
            ['Were', 'the', 'windows', 'washed', 'last', 'week', 'today', 'wash', 'Was'],
            collect($werePayload['tokenBank'])->pluck('value')->all()
        );
        $this->assertSame('Were the windows washed last week?', $werePayload['correctText']);

        $this->assertSame(
            ['How', 'often', 'is', 'this', 'room', 'cleaned'],
            $isPayload['correctTokenValues']
        );
        $this->assertSame(
            ['How', 'often', 'is', 'this', 'room', 'cleaned', 'are', 'clean', 'today'],
            collect($isPayload['tokenBank'])->pluck('value')->all()
        );
        $this->assertSame('How often is this room cleaned?', $isPayload['correctText']);

        $this->assertSame(
            ['Where', 'are', 'these', 'cars', 'sold'],
            $arePayload['correctTokenValues']
        );
        $this->assertSame(
            ['Where', 'are', 'these', 'cars', 'sold', 'is', 'sell', 'there'],
            collect($arePayload['tokenBank'])->pluck('value')->all()
        );
        $this->assertSame('Where are these cars sold?', $arePayload['correctText']);
        $this->assertSame(
            'Схема wh-питання: Wh-word + is/are + subject + V3?',
            $arePayload['hintUk']
        );
    }

    public function test_reported_speech_basics_a2_v3_polyglot_lesson_payload_remains_compose_compatible(): void
    {
        $this->seed(PolyglotReportedSpeechBasicsLessonSeeder::class);

        $resolver = app(QuestionUuidResolver::class);
        $toldPayload = $this->buildComposePayload(
            Question::query()
                ->where('uuid', $resolver->toPersistent('polyglot-reported-speech-basics-a2-q08'))
                ->firstOrFail()
                ->load(['answers.option', 'options', 'hints', 'chatgptExplanations'])
        );
        $ifPayload = $this->buildComposePayload(
            Question::query()
                ->where('uuid', $resolver->toPersistent('polyglot-reported-speech-basics-a2-q13'))
                ->firstOrFail()
                ->load(['answers.option', 'options', 'hints', 'chatgptExplanations'])
        );
        $whPayload = $this->buildComposePayload(
            Question::query()
                ->where('uuid', $resolver->toPersistent('polyglot-reported-speech-basics-a2-q24'))
                ->firstOrFail()
                ->load(['answers.option', 'options', 'hints', 'chatgptExplanations'])
        );

        $iInstances = collect($toldPayload['tokenBank'])
            ->where('value', 'I')
            ->where('isCorrect', true)
            ->values();

        $this->assertSame(
            ['I', 'told', 'him', 'that', 'I', 'needed', 'help'],
            $toldPayload['correctTokenValues']
        );
        $this->assertCount(7, $toldPayload['correctTokenIds']);
        $this->assertSame(7, count(array_unique($toldPayload['correctTokenIds'])));
        $this->assertCount(2, $iInstances);
        $this->assertSame('I told him that I needed help.', $toldPayload['correctText']);

        $this->assertSame(
            ['He', 'asked', 'me', 'if', 'I', 'was', 'tired'],
            $ifPayload['correctTokenValues']
        );
        $this->assertSame('He asked me if I was tired.', $ifPayload['correctText']);
        $this->assertSame(
            'Схема: asked + object + if + clause',
            $ifPayload['hintUk']
        );

        $this->assertSame(
            ['I', 'asked', 'him', 'who', 'lived', 'in', 'that', 'house'],
            $whPayload['correctTokenValues']
        );
        $this->assertSame('I asked him who lived in that house.', $whPayload['correctText']);
        $this->assertSame(
            'Схема: asked + wh-word + clause',
            $whPayload['hintUk']
        );
    }

    public function test_used_to_a2_v3_polyglot_lesson_payload_remains_compose_compatible(): void
    {
        $this->seed(PolyglotUsedToLessonSeeder::class);

        $resolver = app(QuestionUuidResolver::class);
        $questionPayload = $this->buildComposePayload(
            Question::query()
                ->where('uuid', $resolver->toPersistent('polyglot-used-to-a2-q15'))
                ->firstOrFail()
                ->load(['answers.option', 'options', 'hints', 'chatgptExplanations'])
        );
        $shePayload = $this->buildComposePayload(
            Question::query()
                ->where('uuid', $resolver->toPersistent('polyglot-used-to-a2-q20'))
                ->firstOrFail()
                ->load(['answers.option', 'options', 'hints', 'chatgptExplanations'])
        );
        $whPayload = $this->buildComposePayload(
            Question::query()
                ->where('uuid', $resolver->toPersistent('polyglot-used-to-a2-q24'))
                ->firstOrFail()
                ->load(['answers.option', 'options', 'hints', 'chatgptExplanations'])
        );

        $toInstances = collect($whPayload['tokenBank'])
            ->where('value', 'to')
            ->where('isCorrect', true)
            ->values();

        $this->assertSame(
            ['Did', 'you', 'use', 'to', 'play', 'football', 'after', 'school'],
            $questionPayload['correctTokenValues']
        );
        $this->assertSame('Did you use to play football after school?', $questionPayload['correctText']);
        $this->assertSame(
            'Схема питання: Did + subject + use to + V1?',
            $questionPayload['hintUk']
        );

        $this->assertSame(
            ['Did', 'she', 'use', 'to', 'have', 'long', 'hair'],
            $shePayload['correctTokenValues']
        );
        $this->assertSame('Did she use to have long hair?', $shePayload['correctText']);

        $this->assertSame(
            ['Why', 'did', 'you', 'use', 'to', 'walk', 'to', 'work'],
            $whPayload['correctTokenValues']
        );
        $this->assertCount(8, $whPayload['correctTokenIds']);
        $this->assertSame(8, count(array_unique($whPayload['correctTokenIds'])));
        $this->assertCount(2, $toInstances);
        $this->assertSame('Why did you use to walk to work?', $whPayload['correctText']);
        $this->assertSame(
            'Схема wh-питання: Wh-word + did + subject + use to + V1?',
            $whPayload['hintUk']
        );
    }

    public function test_question_tags_basics_a2_v3_polyglot_lesson_payload_remains_compose_compatible(): void
    {
        $this->seed(PolyglotQuestionTagsBasicsLessonSeeder::class);

        $resolver = app(QuestionUuidResolver::class);
        $annaPayload = $this->buildComposePayload(
            Question::query()
                ->where('uuid', $resolver->toPersistent('polyglot-question-tags-basics-a2-q02'))
                ->firstOrFail()
                ->load(['answers.option', 'options', 'hints', 'chatgptExplanations'])
        );
        $negativePayload = $this->buildComposePayload(
            Question::query()
                ->where('uuid', $resolver->toPersistent('polyglot-question-tags-basics-a2-q19'))
                ->firstOrFail()
                ->load(['answers.option', 'options', 'hints', 'chatgptExplanations'])
        );
        $therePayload = $this->buildComposePayload(
            Question::query()
                ->where('uuid', $resolver->toPersistent('polyglot-question-tags-basics-a2-q24'))
                ->firstOrFail()
                ->load(['answers.option', 'options', 'hints', 'chatgptExplanations'])
        );

        $correctAreInstances = collect($therePayload['tokenBank'])
            ->where('value', 'are')
            ->where('isCorrect', true)
            ->values();
        $correctThereInstances = collect($therePayload['tokenBank'])
            ->filter(fn (array $token) => strtolower((string) ($token['value'] ?? '')) === 'there')
            ->where('isCorrect', true)
            ->values();

        $this->assertSame(
            ['Anna', 'is', 'a', 'doctor', 'is', 'she', 'not'],
            $annaPayload['correctTokenValues']
        );
        $this->assertSame('Anna is a doctor is she not?', $annaPayload['correctText']);

        $this->assertSame(
            ['You', 'do', 'not', 'like', 'coffee', 'do', 'you'],
            $negativePayload['correctTokenValues']
        );
        $this->assertSame('You do not like coffee do you?', $negativePayload['correctText']);

        $this->assertSame(
            ['There', 'are', 'not', 'many', 'people', 'here', 'are', 'there'],
            $therePayload['correctTokenValues']
        );
        $this->assertCount(8, $therePayload['correctTokenIds']);
        $this->assertSame(8, count(array_unique($therePayload['correctTokenIds'])));
        $this->assertCount(2, $correctAreInstances);
        $this->assertCount(2, $correctThereInstances);
        $this->assertSame('There are not many people here are there?', $therePayload['correctText']);
        $this->assertSame(
            'Для there are у tag теж повторюємо there.',
            $therePayload['hintUk']
        );
    }

    public function test_second_conditional_basics_a2_v3_polyglot_lesson_payload_remains_compose_compatible(): void
    {
        $this->seed(PolyglotSecondConditionalBasicsLessonSeeder::class);

        $resolver = app(QuestionUuidResolver::class);
        $affirmativePayload = $this->buildComposePayload(
            Question::query()
                ->where('uuid', $resolver->toPersistent('polyglot-second-conditional-basics-a2-q01'))
                ->firstOrFail()
                ->load(['answers.option', 'options', 'hints', 'chatgptExplanations'])
        );
        $questionPayload = $this->buildComposePayload(
            Question::query()
                ->where('uuid', $resolver->toPersistent('polyglot-second-conditional-basics-a2-q15'))
                ->firstOrFail()
                ->load(['answers.option', 'options', 'hints', 'chatgptExplanations'])
        );
        $whPayload = $this->buildComposePayload(
            Question::query()
                ->where('uuid', $resolver->toPersistent('polyglot-second-conditional-basics-a2-q24'))
                ->firstOrFail()
                ->load(['answers.option', 'options', 'hints', 'chatgptExplanations'])
        );

        $correctIInstances = collect($affirmativePayload['tokenBank'])
            ->where('value', 'I')
            ->where('isCorrect', true)
            ->values();
        $correctYouInstances = collect($whPayload['tokenBank'])
            ->where('value', 'you')
            ->where('isCorrect', true)
            ->values();

        $this->assertSame(
            ['I', 'would', 'learn', 'Japanese', 'if', 'I', 'had', 'more', 'time'],
            $affirmativePayload['correctTokenValues']
        );
        $this->assertCount(9, $affirmativePayload['correctTokenIds']);
        $this->assertSame(9, count(array_unique($affirmativePayload['correctTokenIds'])));
        $this->assertCount(2, $correctIInstances);
        $this->assertSame('I would learn Japanese if I had more time.', $affirmativePayload['correctText']);

        $this->assertSame(
            ['Would', 'you', 'move', 'abroad', 'if', 'you', 'found', 'a', 'good', 'job'],
            $questionPayload['correctTokenValues']
        );
        $this->assertSame('Would you move abroad if you found a good job?', $questionPayload['correctText']);
        $this->assertSame(
            'Схема питання: Would + subject + V1 + if + past simple?',
            $questionPayload['hintUk']
        );

        $this->assertSame(
            ['How', 'would', 'you', 'travel', 'if', 'you', 'did', 'not', 'have', 'a', 'car'],
            $whPayload['correctTokenValues']
        );
        $this->assertCount(11, $whPayload['correctTokenIds']);
        $this->assertSame(11, count(array_unique($whPayload['correctTokenIds'])));
        $this->assertCount(2, $correctYouInstances);
        $this->assertSame('How would you travel if you did not have a car?', $whPayload['correctText']);
        $this->assertSame(
            'Схема wh-питання: Wh-word + would + subject + V1 + if + did not + V1?',
            $whPayload['hintUk']
        );
    }

    public function test_final_drill_a2_v3_polyglot_lesson_payload_remains_compose_compatible(): void
    {
        $this->seed(PolyglotFinalDrillA2LessonSeeder::class);

        $resolver = app(QuestionUuidResolver::class);
        $tagPayload = $this->buildComposePayload(
            Question::query()
                ->where('uuid', $resolver->toPersistent('polyglot-final-drill-a2-q14'))
                ->firstOrFail()
                ->load(['answers.option', 'options', 'hints', 'chatgptExplanations'])
        );
        $modalTagPayload = $this->buildComposePayload(
            Question::query()
                ->where('uuid', $resolver->toPersistent('polyglot-final-drill-a2-q18'))
                ->firstOrFail()
                ->load(['answers.option', 'options', 'hints', 'chatgptExplanations'])
        );
        $duplicateIPayload = $this->buildComposePayload(
            Question::query()
                ->where('uuid', $resolver->toPersistent('polyglot-final-drill-a2-q15'))
                ->firstOrFail()
                ->load(['answers.option', 'options', 'hints', 'chatgptExplanations'])
        );
        $duplicateYouPayload = $this->buildComposePayload(
            Question::query()
                ->where('uuid', $resolver->toPersistent('polyglot-final-drill-a2-q23'))
                ->firstOrFail()
                ->load(['answers.option', 'options', 'hints', 'chatgptExplanations'])
        );
        $repeatedThePayload = $this->buildComposePayload(
            Question::query()
                ->where('uuid', $resolver->toPersistent('polyglot-final-drill-a2-q10'))
                ->firstOrFail()
                ->load(['answers.option', 'options', 'hints', 'chatgptExplanations'])
        );
        $passivePayload = $this->buildComposePayload(
            Question::query()
                ->where('uuid', $resolver->toPersistent('polyglot-final-drill-a2-q22'))
                ->firstOrFail()
                ->load(['answers.option', 'options', 'hints', 'chatgptExplanations'])
        );
        $haveToPayload = $this->buildComposePayload(
            Question::query()
                ->where('uuid', $resolver->toPersistent('polyglot-final-drill-a2-q24'))
                ->firstOrFail()
                ->load(['answers.option', 'options', 'hints', 'chatgptExplanations'])
        );
        $gerundPayload = $this->buildComposePayload(
            Question::query()
                ->where('uuid', $resolver->toPersistent('polyglot-final-drill-a2-q21'))
                ->firstOrFail()
                ->load(['answers.option', 'options', 'hints', 'chatgptExplanations'])
        );

        $correctAreInstances = collect($tagPayload['tokenBank'])
            ->where('value', 'are')
            ->where('isCorrect', true)
            ->values();
        $correctIInstances = collect($duplicateIPayload['tokenBank'])
            ->where('value', 'I')
            ->where('isCorrect', true)
            ->values();
        $correctYouInstances = collect($duplicateYouPayload['tokenBank'])
            ->where('value', 'you')
            ->where('isCorrect', true)
            ->values();
        $correctTheInstances = collect($repeatedThePayload['tokenBank'])
            ->where('value', 'the')
            ->where('isCorrect', true)
            ->values();
        $passiveTheInstances = collect($passivePayload['tokenBank'])
            ->where('value', 'the')
            ->where('isCorrect', true)
            ->values();

        $this->assertSame(
            ['You', 'are', 'not', 'ready', 'are', 'you'],
            $tagPayload['correctTokenValues']
        );
        $this->assertCount(2, $correctAreInstances);
        $this->assertSame('You are not ready are you?', $tagPayload['correctText']);

        $this->assertSame(
            ['Tom', 'cannot', 'swim', 'can', 'he'],
            $modalTagPayload['correctTokenValues']
        );
        $this->assertSame('Tom cannot swim can he?', $modalTagPayload['correctText']);

        $this->assertSame(
            ['I', 'would', 'buy', 'this', 'house', 'if', 'I', 'had', 'more', 'money'],
            $duplicateIPayload['correctTokenValues']
        );
        $this->assertCount(2, $correctIInstances);
        $this->assertSame('I would buy this house if I had more money.', $duplicateIPayload['correctText']);

        $this->assertSame(
            ['Where', 'would', 'you', 'go', 'if', 'you', 'had', 'more', 'time'],
            $duplicateYouPayload['correctTokenValues']
        );
        $this->assertCount(2, $correctYouInstances);
        $this->assertSame('Where would you go if you had more time?', $duplicateYouPayload['correctText']);

        $this->assertSame(
            ['This', 'is', 'the', 'woman', 'who', 'works', 'with', 'my', 'brother'],
            $repeatedThePayload['correctTokenValues']
        );
        $this->assertCount(1, $correctTheInstances);
        $this->assertSame('This is the woman who works with my brother.', $repeatedThePayload['correctText']);

        $this->assertSame(
            ['Were', 'the', 'windows', 'washed', 'last', 'week'],
            $passivePayload['correctTokenValues']
        );
        $this->assertCount(1, $passiveTheInstances);
        $this->assertSame('Were the windows washed last week?', $passivePayload['correctText']);

        $this->assertSame(
            ['Do', 'we', 'have', 'to', 'book', 'tickets', 'online'],
            $haveToPayload['correctTokenValues']
        );
        $this->assertSame('Do we have to book tickets online?', $haveToPayload['correctText']);

        $this->assertSame(
            ['Do', 'you', 'mind', 'waiting', 'a', 'few', 'minutes'],
            $gerundPayload['correctTokenValues']
        );
        $this->assertSame('Do you mind waiting a few minutes?', $gerundPayload['correctText']);
        $this->assertSame('Повторення: gerund pattern.', $gerundPayload['hintUk']);
    }

    public function test_present_perfect_continuous_basics_b1_v3_polyglot_lesson_payload_remains_compose_compatible(): void
    {
        $this->seed(PolyglotPresentPerfectContinuousBasicsLessonSeeder::class);

        $resolver = app(QuestionUuidResolver::class);
        $durationPayload = $this->buildComposePayload(
            Question::query()
                ->where('uuid', $resolver->toPersistent('polyglot-present-perfect-continuous-basics-b1-q03'))
                ->firstOrFail()
                ->load(['answers.option', 'options', 'hints', 'chatgptExplanations'])
        );
        $whPayload = $this->buildComposePayload(
            Question::query()
                ->where('uuid', $resolver->toPersistent('polyglot-present-perfect-continuous-basics-b1-q19'))
                ->firstOrFail()
                ->load(['answers.option', 'options', 'hints', 'chatgptExplanations'])
        );

        $correctForInstances = collect($durationPayload['tokenBank'])
            ->where('value', 'for')
            ->where('isCorrect', true)
            ->values();

        $this->assertSame(
            ['We', 'have', 'been', 'waiting', 'for', 'you', 'for', 'half', 'an', 'hour'],
            $durationPayload['correctTokenValues']
        );
        $this->assertCount(10, $durationPayload['correctTokenIds']);
        $this->assertSame(10, count(array_unique($durationPayload['correctTokenIds'])));
        $this->assertCount(2, $correctForInstances);
        $this->assertEqualsCanonicalizing(
            ['We', 'have', 'been', 'waiting', 'for', 'you', 'for', 'half', 'an', 'hour', 'has', 'waited', 'since'],
            collect($durationPayload['tokenBank'])->pluck('value')->all()
        );
        $this->assertSame('We have been waiting for you for half an hour.', $durationPayload['correctText']);
        $this->assertSame(
            'Схема: have/has been + V-ing + for + duration',
            $durationPayload['hintUk']
        );

        $this->assertSame(
            ['How', 'long', 'have', 'you', 'been', 'studying', 'here'],
            $whPayload['correctTokenValues']
        );
        $this->assertEqualsCanonicalizing(
            ['How', 'long', 'have', 'you', 'been', 'studying', 'here', 'has', 'studied', 'since'],
            collect($whPayload['tokenBank'])->pluck('value')->all()
        );
        $this->assertSame('How long have you been studying here?', $whPayload['correctText']);
        $this->assertSame(
            'How long + have/has + subject + been + V-ing?',
            $whPayload['hintUk']
        );
    }

    public function test_present_perfect_continuous_vs_present_perfect_b1_v3_polyglot_lesson_payload_remains_compose_compatible(): void
    {
        $this->seed(PolyglotPresentPerfectContinuousBasicsLessonSeeder::class);
        $this->seed(PolyglotPresentPerfectContinuousVsPresentPerfectLessonSeeder::class);

        $resolver = app(QuestionUuidResolver::class);
        $durationPayload = $this->buildComposePayload(
            Question::query()
                ->where('uuid', $resolver->toPersistent('polyglot-present-perfect-continuous-vs-present-perfect-b1-q03'))
                ->firstOrFail()
                ->load(['answers.option', 'options', 'hints', 'chatgptExplanations'])
        );
        $resultPayload = $this->buildComposePayload(
            Question::query()
                ->where('uuid', $resolver->toPersistent('polyglot-present-perfect-continuous-vs-present-perfect-b1-q24'))
                ->firstOrFail()
                ->load(['answers.option', 'options', 'hints', 'chatgptExplanations'])
        );
        $whPayload = $this->buildComposePayload(
            Question::query()
                ->where('uuid', $resolver->toPersistent('polyglot-present-perfect-continuous-vs-present-perfect-b1-q21'))
                ->firstOrFail()
                ->load(['answers.option', 'options', 'hints', 'chatgptExplanations'])
        );

        $correctForInstances = collect($durationPayload['tokenBank'])
            ->where('value', 'for')
            ->where('isCorrect', true)
            ->values();

        $this->assertSame(
            ['We', 'have', 'been', 'waiting', 'for', 'you', 'for', 'half', 'an', 'hour'],
            $durationPayload['correctTokenValues']
        );
        $this->assertCount(10, $durationPayload['correctTokenIds']);
        $this->assertSame(10, count(array_unique($durationPayload['correctTokenIds'])));
        $this->assertCount(2, $correctForInstances);
        $this->assertEqualsCanonicalizing(
            ['We', 'have', 'been', 'waiting', 'for', 'you', 'for', 'half', 'an', 'hour', 'has', 'waited', 'since'],
            collect($durationPayload['tokenBank'])->pluck('value')->all()
        );
        $this->assertSame('We have been waiting for you for half an hour.', $durationPayload['correctText']);
        $this->assertSame('For + duration і дія ще триває.', $durationPayload['hintUk']);

        $this->assertSame(
            ['What', 'have', 'they', 'changed', 'in', 'the', 'plan'],
            $resultPayload['correctTokenValues']
        );
        $this->assertEqualsCanonicalizing(
            ['What', 'have', 'they', 'changed', 'in', 'the', 'plan', 'been', 'changing', 'already'],
            collect($resultPayload['tokenBank'])->pluck('value')->all()
        );
        $this->assertSame('What have they changed in the plan?', $resultPayload['correctText']);
        $this->assertSame('Wh-question with focus on completed result.', $resultPayload['hintUk']);

        $this->assertSame(
            ['How', 'long', 'have', 'you', 'been', 'learning', 'French'],
            $whPayload['correctTokenValues']
        );
        $this->assertEqualsCanonicalizing(
            ['How', 'long', 'have', 'you', 'been', 'learning', 'French', 'has', 'studied', 'since'],
            collect($whPayload['tokenBank'])->pluck('value')->all()
        );
        $this->assertSame('How long have you been learning French?', $whPayload['correctText']);
        $this->assertSame('How long + process/duration → Present Perfect Continuous.', $whPayload['hintUk']);
    }

    public function test_past_perfect_basics_b1_v3_polyglot_lesson_payload_remains_compose_compatible(): void
    {
        $this->seed(PolyglotPresentPerfectContinuousBasicsLessonSeeder::class);
        $this->seed(PolyglotPresentPerfectContinuousVsPresentPerfectLessonSeeder::class);
        $this->seed(PolyglotPastPerfectBasicsLessonSeeder::class);

        $resolver = app(QuestionUuidResolver::class);
        $byTimePayload = $this->buildComposePayload(
            Question::query()
                ->where('uuid', $resolver->toPersistent('polyglot-past-perfect-basics-b1-q04'))
                ->firstOrFail()
                ->load(['answers.option', 'options', 'hints', 'chatgptExplanations'])
        );
        $affirmativePayload = $this->buildComposePayload(
            Question::query()
                ->where('uuid', $resolver->toPersistent('polyglot-past-perfect-basics-b1-q06'))
                ->firstOrFail()
                ->load(['answers.option', 'options', 'hints', 'chatgptExplanations'])
        );
        $questionPayload = $this->buildComposePayload(
            Question::query()
                ->where('uuid', $resolver->toPersistent('polyglot-past-perfect-basics-b1-q13'))
                ->firstOrFail()
                ->load(['answers.option', 'options', 'hints', 'chatgptExplanations'])
        );

        $correctTheInstances = collect($byTimePayload['tokenBank'])
            ->where('value', 'the')
            ->where('isCorrect', true)
            ->values();
        $correctIInstances = collect($affirmativePayload['tokenBank'])
            ->where('value', 'I')
            ->where('isCorrect', true)
            ->values();

        $this->assertSame(
            ['They', 'had', 'sold', 'the', 'house', 'by', 'the', 'end', 'of', 'the', 'year'],
            $byTimePayload['correctTokenValues']
        );
        $this->assertCount(11, $byTimePayload['correctTokenIds']);
        $this->assertSame(11, count(array_unique($byTimePayload['correctTokenIds'])));
        $this->assertCount(3, $correctTheInstances);
        $this->assertEqualsCanonicalizing(
            ['They', 'had', 'sold', 'the', 'house', 'by', 'the', 'end', 'of', 'the', 'year', 'have', 'sell', 'month'],
            collect($byTimePayload['tokenBank'])->pluck('value')->all()
        );
        $this->assertSame('They had sold the house by the end of the year.', $byTimePayload['correctText']);
        $this->assertSame(
            'By the end of... часто вказує на момент, до якого дія вже завершилася.',
            $byTimePayload['hintUk']
        );

        $this->assertSame(
            ['I', 'had', 'learned', 'the', 'rule', 'before', 'I', 'did', 'the', 'exercise'],
            $affirmativePayload['correctTokenValues']
        );
        $this->assertCount(2, $correctIInstances);
        $this->assertSame('I had learned the rule before I did the exercise.', $affirmativePayload['correctText']);
        $this->assertSame('Схема: had + V3 before + Past Simple.', $affirmativePayload['hintUk']);

        $this->assertSame(
            ['Had', 'you', 'already', 'eaten', 'lunch', 'when', 'she', 'arrived'],
            $questionPayload['correctTokenValues']
        );
        $this->assertEqualsCanonicalizing(
            ['Had', 'you', 'already', 'eaten', 'lunch', 'when', 'she', 'arrived', 'Have', 'ate', 'arrives'],
            collect($questionPayload['tokenBank'])->pluck('value')->all()
        );
        $this->assertSame('Had you already eaten lunch when she arrived?', $questionPayload['correctText']);
        $this->assertSame('Питання: Had + subject + V3?', $questionPayload['hintUk']);
    }

    public function test_narrative_tenses_basics_b1_v3_polyglot_lesson_payload_remains_compose_compatible(): void
    {
        $this->seed(PolyglotPresentPerfectContinuousBasicsLessonSeeder::class);
        $this->seed(PolyglotPresentPerfectContinuousVsPresentPerfectLessonSeeder::class);
        $this->seed(PolyglotPastPerfectBasicsLessonSeeder::class);
        $this->seed(PolyglotNarrativeTensesBasicsLessonSeeder::class);

        $resolver = app(QuestionUuidResolver::class);
        $openedPayload = $this->buildComposePayload(
            Question::query()
                ->where('uuid', $resolver->toPersistent('polyglot-narrative-tenses-basics-b1-q08'))
                ->firstOrFail()
                ->load(['answers.option', 'options', 'hints', 'chatgptExplanations'])
        );
        $foundPayload = $this->buildComposePayload(
            Question::query()
                ->where('uuid', $resolver->toPersistent('polyglot-narrative-tenses-basics-b1-q15'))
                ->firstOrFail()
                ->load(['answers.option', 'options', 'hints', 'chatgptExplanations'])
        );
        $trainPayload = $this->buildComposePayload(
            Question::query()
                ->where('uuid', $resolver->toPersistent('polyglot-narrative-tenses-basics-b1-q21'))
                ->firstOrFail()
                ->load(['answers.option', 'options', 'hints', 'chatgptExplanations'])
        );
        $walkingPayload = $this->buildComposePayload(
            Question::query()
                ->where('uuid', $resolver->toPersistent('polyglot-narrative-tenses-basics-b1-q24'))
                ->firstOrFail()
                ->load(['answers.option', 'options', 'hints', 'chatgptExplanations'])
        );

        $correctTheOpened = collect($openedPayload['tokenBank'])
            ->filter(fn (array $token): bool => strtolower((string) $token['value']) === 'the' && (bool) $token['isCorrect'])
            ->values();
        $correctTheyFound = collect($foundPayload['tokenBank'])
            ->filter(fn (array $token): bool => strtolower((string) $token['value']) === 'they' && (bool) $token['isCorrect'])
            ->values();
        $correctTheTrain = collect($trainPayload['tokenBank'])
            ->filter(fn (array $token): bool => strtolower((string) $token['value']) === 'the' && (bool) $token['isCorrect'])
            ->values();
        $correctWeWalking = collect($walkingPayload['tokenBank'])
            ->filter(fn (array $token): bool => strtolower((string) $token['value']) === 'we' && (bool) $token['isCorrect'])
            ->values();

        $this->assertSame(
            ['She', 'opened', 'the', 'door', 'and', 'saw', 'that', 'someone', 'had', 'broken', 'the', 'window'],
            $openedPayload['correctTokenValues']
        );
        $this->assertCount(12, $openedPayload['correctTokenIds']);
        $this->assertSame(12, count(array_unique($openedPayload['correctTokenIds'])));
        $this->assertCount(2, $correctTheOpened);
        $this->assertEqualsCanonicalizing(
            ['She', 'opened', 'the', 'door', 'and', 'saw', 'that', 'someone', 'had', 'broken', 'the', 'window', 'broke', 'has'],
            collect($openedPayload['tokenBank'])->pluck('value')->all()
        );
        $this->assertSame(
            'She opened the door and saw that someone had broken the window.',
            $openedPayload['correctText']
        );
        $this->assertSame(
            'Past Perfect пояснює результат, який існував до моменту в історії.',
            $openedPayload['hintUk']
        );

        $this->assertSame(
            ['Did', 'they', 'find', 'the', 'bag', 'that', 'they', 'had', 'lost'],
            $foundPayload['correctTokenValues']
        );
        $this->assertCount(2, $correctTheyFound);
        $this->assertSame('Did they find the bag that they had lost?', $foundPayload['correctText']);

        $this->assertSame(
            ['The', 'train', 'had', 'already', 'left', 'when', 'we', 'reached', 'the', 'station'],
            $trainPayload['correctTokenValues']
        );
        $this->assertCount(2, $correctTheTrain);
        $this->assertSame('The train had already left when we reached the station.', $trainPayload['correctText']);

        $this->assertSame(
            ['We', 'were', 'walking', 'in', 'the', 'park', 'when', 'we', 'saw', 'an', 'old', 'friend'],
            $walkingPayload['correctTokenValues']
        );
        $this->assertCount(2, $correctWeWalking);
        $this->assertSame('We were walking in the park when we saw an old friend.', $walkingPayload['correctText']);
        $this->assertSame(
            'Past Continuous for the background action; Past Simple for the event.',
            $walkingPayload['hintUk']
        );
    }

    private function createComposeQuestion(string $questionText, array $answers, array $options): Question
    {
        $category = Category::query()->firstOrCreate([
            'name' => 'Compose Payload Tests',
        ]);

        $question = Question::query()->create([
            'uuid' => (string) Str::uuid(),
            'question' => $questionText,
            'difficulty' => 1,
            'level' => 'A1',
            'category_id' => $category->id,
            'type' => Question::TYPE_COMPOSE_TOKENS,
        ]);

        foreach ($options as $optionValue) {
            $option = QuestionOption::query()->firstOrCreate([
                'option' => $optionValue,
            ]);

            $question->options()->syncWithoutDetaching([$option->id]);
        }

        foreach ($answers as $answer) {
            $optionId = QuestionOption::query()
                ->where('option', $answer['value'])
                ->value('id');

            QuestionAnswer::query()->create([
                'question_id' => $question->id,
                'option_id' => $optionId,
                'marker' => $answer['marker'],
            ]);
        }

        return $question->load(['answers.option', 'options', 'hints', 'chatgptExplanations']);
    }

    private function buildComposePayload(Question $question): array
    {
        $controller = $this->app->make(TestJsV2Controller::class);
        $method = new ReflectionMethod(TestJsV2Controller::class, 'buildComposeQuestionPayload');
        $method->setAccessible(true);

        return $method->invoke($controller, $question, null);
    }
}
