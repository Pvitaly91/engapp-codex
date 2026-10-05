<?php

namespace Tests\Unit;

use App\Http\Controllers\TestJsV2Controller;
use App\Models\Question;
use App\Models\QuestionAnswer;
use App\Models\QuestionHint;
use App\Models\QuestionOption;
use App\Support\LocalizedComposeText;
use Illuminate\Database\Eloquent\Collection;
use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionClass;
use ReflectionMethod;
use Tests\TestCase;

class PastPerfectContinuousTokenPresentationTest extends TestCase
{
    private const TOKENS = ['I', 'had', 'been', 'reading', 'When', 'Anna', 'arrived'];

    public static function locales(): array
    {
        return ['uk' => ['uk'], 'en' => ['en'], 'pl' => ['pl']];
    }

    public function test_only_finite_optin_noninitial_exact_when_is_changed(): void
    {
        $tokens = ['When', 'Anna', 'arrived', 'I', 'had', 'been', 'reading', 'When', 'Lev', 'called'];
        $expected = $tokens;
        $expected[7] = 'when';
        $seeders = (new ReflectionClass(LocalizedComposeText::class))->getConstant('REVISION_SEEDERS');
        foreach ($seeders as $seeder) {
            $question = $this->question();
            $question->seeder = $seeder;
            $this->assertSame($expected, LocalizedComposeText::normalizeTokens($question, $tokens));
        }
        foreach ([null, 'Database\\Seeders\\V3\\Polyglot\\PolyglotPastPerfectVsPastPerfectContinuousAllLevelsLessonSeeder'] as $seeder) {
            $question = $this->question();
            $question->seeder = $seeder;
            $this->assertSame($tokens, LocalizedComposeText::normalizeTokens($question, $tokens));
        }
        $legacy = $this->question();
        $legacy->setRelation('hints', new Collection());
        $this->assertSame($tokens, LocalizedComposeText::normalizeTokens($legacy, $tokens));
    }

    #[DataProvider('locales')]
    public function test_native_compose_bank_and_correct_text_use_the_same_presented_case(string $locale): void
    {
        app()->setLocale($locale);
        $question = $this->question();
        $controller = (new ReflectionClass(TestJsV2Controller::class))->newInstanceWithoutConstructor();
        $payload = (new ReflectionMethod(TestJsV2Controller::class, 'buildComposeQuestionPayload'))->invoke($controller, $question);
        $expected = self::TOKENS;
        $expected[4] = 'when';
        $this->assertSame($expected, $payload['correctTokenValues']);
        $this->assertSame($expected, array_column($payload['tokenBank'], 'value'));
        $this->assertSame('I had been reading when Anna arrived.', $payload['correctText']);
        $this->assertSame('When', $question->answers[4]->option->option);
    }

    #[DataProvider('locales')]
    public function test_linked_widget_bank_and_target_use_the_same_presented_case(string $locale): void
    {
        app()->setLocale($locale);
        $question = $this->question();
        $html = view('components.text-block-practice-questions', [
            'questions' => collect([$question]), 'blockUuid' => 'ppc-token-presentation-fixture',
        ])->render();
        $this->assertSame(1, preg_match('/questions:\s*(\[[^\r\n]+\]),/', $html, $matches));
        $payload = json_decode($matches[1], true, flags: JSON_THROW_ON_ERROR)[0];
        $expected = self::TOKENS;
        $expected[4] = 'when';
        $this->assertSame($expected, $payload['correct_tokens']);
        $this->assertSame($expected, $payload['options']);
        $this->assertSame('When', $question->answers[4]->option->option);
    }

    private function question(): Question
    {
        $question = new Question([
            'uuid' => 'ppc-token-case-fixture', 'question' => 'Я вже читав, коли прийшла Анна.',
            'type' => Question::TYPE_COMPOSE_TOKENS, 'level' => 'A1',
            'seeder' => 'Database\\Seeders\\V3\\Polyglot\\PolyglotPastPerfectContinuousFormsAllLevelsLessonSeeder',
        ]);
        $question->id = 900003;
        $answers = [];
        $options = [];
        foreach (self::TOKENS as $index => $token) {
            $option = new QuestionOption(['option' => $token]);
            $options[] = $option;
            $answers[] = (new QuestionAnswer(['marker' => 'a'.($index + 1)]))->setRelation('option', $option);
        }
        return $question
            ->setRelation('answers', new Collection($answers))
            ->setRelation('options', new Collection($options))
            ->setRelation('hints', new Collection([
                new QuestionHint(['provider' => LocalizedComposeText::PROVIDER, 'locale' => 'uk', 'hint' => $question->question]),
            ]))
            ->setRelation('tags', new Collection())
            ->setRelation('verbHints', new Collection())
            ->setRelation('theoryTextBlocks', new Collection())
            ->setRelation('chatgptExplanations', new Collection());
    }
}
