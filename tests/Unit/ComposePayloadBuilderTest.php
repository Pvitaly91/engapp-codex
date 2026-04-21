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
use Database\Seeders\V3\Polyglot\PolyglotCanCannotLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotComparativesLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotFinalDrillLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotFutureSimpleWillLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotMuchManyALotOfLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotPastSimpleIrregularVerbsLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotPastSimpleRegularVerbsLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotPastSimpleToBeLessonSeeder;
use Database\Seeders\V3\Polyglot\PolyglotPresentContinuousLessonSeeder;
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
