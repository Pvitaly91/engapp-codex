<?php

namespace Tests\Unit;

use App\Models\Question;
use App\Models\QuestionAnswer;
use App\Models\QuestionHint;
use App\Models\QuestionOption;
use App\Services\TheoryCourseTestPoolService;
use App\Support\LocalizedComposeText;
use Illuminate\Database\Eloquent\Collection;
use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionClass;
use ReflectionMethod;
use Tests\TestCase;

final class PastPerfectContinuousTheoryMixedPresentationTest extends TestCase
{
    public static function locales(): array
    {
        return ['uk' => ['uk'], 'en' => ['en'], 'pl' => ['pl']];
    }

    #[DataProvider('locales')]
    public function test_actual_alternate_payload_keeps_short_source_and_local_help_separate_without_changing_tokens(string $locale): void
    {
        app()->setLocale($locale);
        [$question, $entry, $canonical] = $this->question();
        $payload = $this->payload($question);
        $this->assertTrue($payload['showPreAnswerHint']);
        $this->assertSame($entry['locales'][$locale]['display_source'], $payload['sourceTextUk']);
        $this->assertSame($canonical['question'], $payload['question']);
        $this->assertSame($entry['locales'][$locale]['instructions']."\n\n".$canonical['localizations'][$locale]['hints'][0], $payload['hintUk']);
        $this->assertSame($canonical['tokens_correct'], $payload['correctTokens']);
        $this->assertSame($canonical['target_text'], $payload['correctText']);
        $this->assertCount(count($canonical['tokens_correct']), $payload['correctTokenIds']);
    }

    #[DataProvider('locales')]
    public function test_legacy_and_non_opted_in_alternate_payloads_keep_their_previous_source_and_no_finite_help_flag(string $locale): void
    {
        app()->setLocale($locale);
        [$question, $entry] = $this->question();
        $question->seeder = 'Database\\Seeders\\V3\\Polyglot\\PolyglotPresentPerfectFormsAllLevelsLessonSeeder';
        $payload = $this->payload($question);
        $this->assertFalse($payload['showPreAnswerHint']);
        $this->assertSame($entry['locales'][$locale]['expected_source'], $payload['sourceTextUk']);
        [$question] = $this->question();
        $question->setRelation('hints', $question->hints->reject(fn ($hint): bool => $hint->provider === LocalizedComposeText::PROVIDER));
        $payload = $this->payload($question);
        $this->assertFalse($payload['showPreAnswerHint']);
        $this->assertSame($question->question, $payload['sourceTextUk']);
    }

    private function payload(Question $question): array
    {
        $service = (new ReflectionClass(TheoryCourseTestPoolService::class))->newInstanceWithoutConstructor();
        return (new ReflectionMethod(TheoryCourseTestPoolService::class, 'composeQuestionPayload'))->invoke($service, $question, []);
    }

    private function question(): array
    {
        $package = json_decode(file_get_contents(base_path('database/content-patches/ppc-compose-presentation/builder.json')), true, flags: JSON_THROW_ON_ERROR);
        $entry = collect($package['questions'])->firstWhere('editorial_uuid', 'pastpc-forms-poly-a1-01');
        $definition = json_decode(file_get_contents(base_path('database/seeders/V3/Polyglot/PolyglotPastPerfectContinuousFormsAllLevelsLessonSeeder/definition.json')), true, flags: JSON_THROW_ON_ERROR);
        $canonical = collect($definition['questions'])->firstWhere('uuid', $entry['editorial_uuid']);
        $question = new Question(['uuid' => $entry['persistent_uuid'], 'seeder' => $entry['seeder_class'],
            'type' => Question::TYPE_COMPOSE_TOKENS, 'question' => $canonical['question'], 'level' => $canonical['level']]);
        $answers = [];
        foreach ($canonical['answers'] as $marker => $value) {
            $answers[] = (new QuestionAnswer(['marker' => $marker]))->setRelation('option', new QuestionOption(['option' => $value]));
        }
        $options = array_map(fn (string $value): QuestionOption => new QuestionOption(['option' => $value]), $canonical['options']);
        $hints = [];
        foreach (['uk', 'en', 'pl'] as $locale) {
            $hints[] = new QuestionHint(['provider' => LocalizedComposeText::PROVIDER, 'locale' => $locale, 'hint' => $entry['locales'][$locale]['expected_source']]);
            $hints[] = new QuestionHint(['provider' => 'polyglot-v3', 'locale' => $locale, 'hint' => $canonical['localizations'][$locale]['hints'][0]]);
        }
        $question->setRelation('answers', new Collection($answers))->setRelation('options', new Collection($options))
            ->setRelation('hints', new Collection($hints))->setRelation('chatgptExplanations', new Collection());
        return [$question, $entry, $canonical];
    }
}
