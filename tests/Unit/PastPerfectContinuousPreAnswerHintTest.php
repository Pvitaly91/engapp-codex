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

class PastPerfectContinuousPreAnswerHintTest extends TestCase
{
    private const HINTS = [
        'uk' => 'Лексична основа: swim — плавати. Опиши попередній тривалий процес.',
        'en' => 'Lexical verb: swim. Describe the earlier ongoing process.',
        'pl' => 'Czasownik podstawowy: swim — pływać. Opisz wcześniejszy trwający proces.',
    ];

    public static function locales(): array
    {
        return ['uk' => ['uk'], 'en' => ['en'], 'pl' => ['pl']];
    }

    #[DataProvider('locales')]
    public function test_native_optin_payload_exposes_the_active_locale_lexical_hint_before_answering(string $locale): void
    {
        app()->setLocale($locale);
        $payload = $this->payload($this->question(true));
        $this->assertTrue($payload['showPreAnswerHint']);
        $this->assertSame(self::HINTS[$locale], $payload['hintUk']);
        $this->assertStringContainsString('swim', $payload['hintUk']);
        $this->assertSame(['I', 'had', 'been', 'swimming'], $payload['correctTokenValues']);
    }

    #[DataProvider('locales')]
    public function test_legacy_and_out_of_scope_questions_never_gain_a_preanswer_hint(string $locale): void
    {
        app()->setLocale($locale);
        $this->assertFalse($this->payload($this->question(false))['showPreAnswerHint']);
        $outside = $this->question(true);
        $outside->seeder = 'Database\\Seeders\\V3\\Polyglot\\PolyglotPresentPerfectContinuousBasicsLessonSeeder';
        $this->assertFalse($this->payload($outside)['showPreAnswerHint']);
        $gap = $this->question(true);
        $gap->type = '0';
        $this->assertFalse($this->payload($gap)['showPreAnswerHint']);
    }

    public function test_the_hint_is_copied_to_collapsed_help_separately_from_the_source_and_feedback(): void
    {
        $view = file_get_contents(dirname(__DIR__, 2).'/resources/views/test-modes/step-compose.blade.php');
        $this->assertStringContainsString('id="compose-learning-hint" class="hidden mt-3 text-sm font-semibold"', $view);
        $this->assertStringContainsString('showPreAnswerHint: item?.showPreAnswerHint === true', $view);
        $this->assertStringContainsString('hintElement.textContent = hint;', $view);
        $this->assertStringContainsString("if (changed || hint === '') hintElement.classList.add('hidden');", $view);
        $this->assertStringContainsString('aria-expanded="false" aria-controls="compose-learning-hint"', $view);
        $this->assertStringContainsString('function toggleComposePreAnswerHint()', $view);
        $this->assertStringContainsString("button.setAttribute('aria-expanded', String(expanded));", $view);
        $this->assertStringContainsString("document.getElementById('compose-source-text').textContent = question.sourceTextUk;\n        renderComposePreAnswerHint(question);", str_replace("\r\n", "\n", $view));
    }

    private function payload(Question $question): array
    {
        $controller = (new ReflectionClass(TestJsV2Controller::class))->newInstanceWithoutConstructor();
        return (new ReflectionMethod(TestJsV2Controller::class, 'buildComposeQuestionPayload'))->invoke($controller, $question);
    }

    private function question(bool $optedIn): Question
    {
        $question = new Question([
            'uuid' => 'pre-answer-ppc-hint-fixture', 'question' => 'Я перед цим плавав.',
            'type' => Question::TYPE_COMPOSE_TOKENS, 'level' => 'A1',
            'seeder' => 'Database\\Seeders\\V3\\Polyglot\\PolyglotPastPerfectContinuousFormsAllLevelsLessonSeeder',
        ]);
        $question->id = 900002;
        $options = [];
        $answers = [];
        foreach (['I', 'had', 'been', 'swimming'] as $index => $token) {
            $option = new QuestionOption(['option' => $token]);
            $options[] = $option;
            $answers[] = (new QuestionAnswer(['marker' => 'a'.($index + 1)]))->setRelation('option', $option);
        }
        $hints = [];
        foreach (self::HINTS as $locale => $hint) {
            if ($optedIn) {
                $hints[] = new QuestionHint(['provider' => LocalizedComposeText::PROVIDER, 'locale' => $locale, 'hint' => 'Source '.$locale]);
            }
            $hints[] = new QuestionHint(['provider' => 'polyglot-v3', 'locale' => $locale, 'hint' => $hint]);
        }
        return $question
            ->setRelation('answers', new Collection($answers))
            ->setRelation('options', new Collection($options))
            ->setRelation('hints', new Collection($hints))
            ->setRelation('theoryTextBlocks', new Collection())
            ->setRelation('chatgptExplanations', new Collection());
    }
}
