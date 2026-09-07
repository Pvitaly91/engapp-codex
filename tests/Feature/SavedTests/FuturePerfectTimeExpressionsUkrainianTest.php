<?php

namespace Tests\Feature\SavedTests;

use App\Models\Question;
use App\Models\SavedGrammarTest;
use App\Services\SavedTestResolver;
use App\Services\VirtualSavedTest;
use App\Support\AcceptedAnswerVariants;
use App\Support\FuturePerfectAnswerSynonyms;
use Tests\TestCase;

class FuturePerfectTimeExpressionsUkrainianTest extends TestCase
{
    private const LEVELS = ['A1', 'A2', 'B1', 'B2', 'C1', 'C2'];

    private const TIME_EXPRESSIONS = [
        'by',
        'by then',
        'by the time',
        'before',
        'by the end of',
        'within',
        'already',
        'no later than',
    ];

    public function test_future_perfect_time_expressions_ukrainian_mixed_test(): void
    {
        app()->setLocale('uk');

        $resolved = app(SavedTestResolver::class)->resolve('future-perfect/time-expressions');

        $this->assertInstanceOf(VirtualSavedTest::class, $resolved->model);
        $this->assertSame('future-perfect/time-expressions', $resolved->model->slug);
        $this->assertSame('Future Perfect: Time Expressions (Mixed A1-C2)', $resolved->model->name);
        $this->assertSame(84, $resolved->questionUuids->count());
        $this->assertSame(84, $resolved->questionUuids->unique()->count());

        $this->assertSame(
            [
                'Database\\Seeders\\V3\\FutureForms\\FuturePerfect\\UkrainianMixedFuturePerfectTimeExpressionsStandardSeeder',
                'Database\\Seeders\\V3\\Polyglot\\UkrainianMixedFuturePerfectTimeExpressionsBuilderSeeder',
            ],
            array_values($resolved->model->filters['seeder_classes'] ?? [])
        );

        $this->assertSame(
            42,
            SavedGrammarTest::query()
                ->where('uuid', 'ukm-fp-time-expressions-std')
                ->firstOrFail()
                ->questionLinks()
                ->count()
        );
        $this->assertSame(
            42,
            SavedGrammarTest::query()
                ->where('uuid', 'ukm-fp-time-expressions-bld')
                ->firstOrFail()
                ->questionLinks()
                ->count()
        );

        $questions = Question::query()
            ->with(['answers.option', 'verbHints.option'])
            ->whereIn('uuid', $resolved->questionUuids->all())
            ->get()
            ->keyBy('uuid');

        $this->assertCount(84, $questions, 'The route contains unresolved question UUIDs.');

        $ordered = $resolved->questionUuids->map(fn (string $uuid) => $questions->get($uuid));
        $fillGaps = $ordered->filter(fn (?Question $question): bool => (string) $question?->type === '0')->values();
        $builders = $ordered->filter(fn (?Question $question): bool => (string) $question?->type === '4')->values();

        $this->assertCount(42, $fillGaps);
        $this->assertCount(42, $builders);

        foreach (self::LEVELS as $level) {
            $levelQuestions = $ordered->where('level', $level);
            $this->assertCount(14, $levelQuestions, "{$level}: expected 14 questions");
            $this->assertCount(7, $levelQuestions->where('type', '0'), "{$level}: expected 7 fill-gap questions");
            $this->assertCount(7, $levelQuestions->where('type', '4'), "{$level}: expected 7 Sentence Builder questions");
        }

        $normalizedPairs = [];

        foreach ($fillGaps as $question) {
            $this->assertStringStartsWith('ukm-fpte-v3-', $question->uuid);
            $this->assertSame(1, substr_count($question->question, '{a1}'), "{$question->uuid}: invalid marker count");
            $this->assertMatchesRegularExpression(
                '/\bwill\s+(?:\{a1\}\s+)?have\s+[a-z-]+(?:ed|en|t|d|n|ne|wn|lt|st|pt|nt|ut)\b/iu',
                $question->question,
                "{$question->uuid}: Future Perfect form is not fully visible"
            );

            $answers = $question->answers->keyBy('marker');
            $this->assertSame(['a1'], $answers->keys()->values()->all());
            $answer = trim((string) $answers->get('a1')?->option?->option);
            $normalizedAnswer = $this->normalize($answer);
            $this->assertContains($normalizedAnswer, self::TIME_EXPRESSIONS, "{$question->uuid}: answer is not a time expression");
            $this->assertDoesNotMatchRegularExpression('/\b(?:will|have)\b/iu', $answer);
            $this->assertCount(1, $question->verbHints, "{$question->uuid}: contextual time-expression verb_hint is missing");
            $hint = trim((string) $question->verbHints->first()?->option?->option);
            $this->assertMatchesRegularExpression('/[А-Яа-яІіЇїЄєҐґ]/u', $hint, "{$question->uuid}: hint must be Ukrainian");
            $this->assertStringNotContainsString('Підмет:', $hint, "{$question->uuid}: obsolete subject hint returned");
            $this->assertStringNotContainsString('Базове дієслово', $hint, "{$question->uuid}: obsolete base-verb hint returned");

            $options = array_values((array) (($question->options_by_marker ?? [])['a1'] ?? []));
            $normalizedOptions = array_map(fn (mixed $option): string => $this->normalize((string) $option), $options);
            $this->assertNotEmpty($options, "{$question->uuid}: options_by_marker is missing");
            $this->assertSame(count($normalizedOptions), count(array_unique($normalizedOptions)), "{$question->uuid}: duplicate options");
            $this->assertSame(1, count(array_keys($normalizedOptions, $normalizedAnswer, true)), "{$question->uuid}: canonical answer must occur exactly once");
            foreach ($options as $option) {
                $this->assertDoesNotMatchRegularExpression('/\b(?:will|have)\b/iu', (string) $option);
            }

            $decorated = FuturePerfectAnswerSynonyms::decorate([
                'uuid' => $question->uuid,
                'type' => (string) $question->type,
                'markers' => ['a1'],
                'answers' => [$answer],
                'answer_map' => ['a1' => $answer],
                'accepted_answers_by_marker' => [],
            ]);
            $this->assertEmpty($decorated['answer_synonyms_by_marker'] ?? [], "{$question->uuid}: builder verb synonyms leaked into the time-expression gap");
            foreach (($decorated['accepted_answers_by_marker']['a1'] ?? AcceptedAnswerVariants::for($answer)) as $accepted) {
                $this->assertDoesNotMatchRegularExpression('/\b(?:will|have)\b/iu', (string) $accepted);
            }

            $pairKey = $this->normalize($question->question.'|'.$answer);
            $this->assertArrayNotHasKey($pairKey, $normalizedPairs, "{$question->uuid}: duplicate normalized question and answer");
            $normalizedPairs[$pairKey] = $question->uuid;
        }

        $this->get('/test/future-perfect/time-expressions')->assertOk();
    }

    private function normalize(string $value): string
    {
        return mb_strtolower(trim(AcceptedAnswerVariants::normalizeTypography($value)));
    }
}
