<?php

namespace Tests\Unit;

use App\Support\ComposeTokenCase;
use PHPUnit\Framework\TestCase;

class PastPerfectBasicsPolyglotContentQualityTest extends TestCase
{
    private const DEFINITION = 'database/seeders/V3/Polyglot/PolyglotPastPerfectBasicsLessonSeeder/definition.json';

    /** @var array<string, array{prompt: string, target: string, marker: string, hint: string}> */
    private const EXPECTED_CONTENT = [
        'polyglot-past-perfect-basics-b1-q01' => ['prompt' => 'Я вже пообідав, коли вона приїхала.', 'target' => 'I had already eaten lunch when she arrived', 'marker' => 'a4', 'hint' => 'їсти'],
        'polyglot-past-perfect-basics-b1-q02' => ['prompt' => 'Вона закінчила звіт до того, як ми прийшли.', 'target' => 'She had finished the report before we arrived', 'marker' => 'a3', 'hint' => 'закінчити'],
        'polyglot-past-perfect-basics-b1-q03' => ['prompt' => 'Ми вже пішли, коли Том подзвонив.', 'target' => 'We had already left when Tom called', 'marker' => 'a4', 'hint' => 'піти'],
        'polyglot-past-perfect-basics-b1-q04' => ['prompt' => 'Вони продали будинок до кінця того року.', 'target' => 'They had sold the house by the end of that year', 'marker' => 'a3', 'hint' => 'продати'],
        'polyglot-past-perfect-basics-b1-q05' => ['prompt' => 'Він прочитав книгу перед тим, як подивився фільм.', 'target' => 'He had read the book before he watched the film', 'marker' => 'a3', 'hint' => 'прочитати'],
        'polyglot-past-perfect-basics-b1-q06' => ['prompt' => 'Я вивчив правило, перш ніж зробив вправу.', 'target' => 'I had learned the rule before I did the exercise', 'marker' => 'a3', 'hint' => 'вивчити'],
        'polyglot-past-perfect-basics-b1-q07' => ['prompt' => 'Я не закінчив свою роботу, коли вона прийшла.', 'target' => 'I had not finished my work when she came', 'marker' => 'a4', 'hint' => 'закінчити'],
        'polyglot-past-perfect-basics-b1-q08' => ['prompt' => 'До того вечора вона ще не бачила цього фільму.', 'target' => 'She had not seen this film before that evening', 'marker' => 'a4', 'hint' => 'бачити'],
        'polyglot-past-perfect-basics-b1-q09' => ['prompt' => 'Ми не забронювали квитки до того, як почалася подорож.', 'target' => 'We had not booked the tickets before the trip began', 'marker' => 'a4', 'hint' => 'забронювати'],
        'polyglot-past-perfect-basics-b1-q10' => ['prompt' => 'Вони не прибрали кімнату до приїзду гостей.', 'target' => 'They had not cleaned the room before the guests arrived', 'marker' => 'a4', 'hint' => 'прибрати'],
        'polyglot-past-perfect-basics-b1-q11' => ['prompt' => 'Він не вивчив слова до того, як почався тест.', 'target' => 'He had not learned the words before the test began', 'marker' => 'a4', 'hint' => 'вивчити'],
        'polyglot-past-perfect-basics-b1-q12' => ['prompt' => 'Я не зустрічав її до того, як почалася вечірка.', 'target' => 'I had not met her before the party began', 'marker' => 'a4', 'hint' => 'зустріти'],
        'polyglot-past-perfect-basics-b1-q13' => ['prompt' => 'Ти вже пообідав, коли вона приїхала?', 'target' => 'Had you already eaten lunch when she arrived', 'marker' => 'a4', 'hint' => 'їсти'],
        'polyglot-past-perfect-basics-b1-q14' => ['prompt' => 'Вона закінчила звіт до того, як почалася зустріч?', 'target' => 'Had she finished the report before the meeting started', 'marker' => 'a3', 'hint' => 'закінчити'],
        'polyglot-past-perfect-basics-b1-q15' => ['prompt' => 'Ми вже пішли, коли Том подзвонив?', 'target' => 'Had we already left when Tom called', 'marker' => 'a4', 'hint' => 'піти'],
        'polyglot-past-perfect-basics-b1-q16' => ['prompt' => 'Вони продали будинок до кінця того року?', 'target' => 'Had they sold the house by the end of that year', 'marker' => 'a3', 'hint' => 'продати'],
        'polyglot-past-perfect-basics-b1-q17' => ['prompt' => 'Він прочитав книгу до того, як подивився фільм?', 'target' => 'Had he read the book before he watched the film', 'marker' => 'a3', 'hint' => 'прочитати'],
        'polyglot-past-perfect-basics-b1-q18' => ['prompt' => 'Ти вивчив правило до того, як виконав вправу?', 'target' => 'Had you learned the rule before you did the exercise', 'marker' => 'a3', 'hint' => 'вивчити'],
        'polyglot-past-perfect-basics-b1-q19' => ['prompt' => 'Що ти зробив до того, як почалася зустріч?', 'target' => 'What had you done before the meeting started', 'marker' => 'a4', 'hint' => 'зробити'],
        'polyglot-past-perfect-basics-b1-q20' => ['prompt' => 'Де вона жила до того, як переїхала до Лондона?', 'target' => 'Where had she lived before she moved to London', 'marker' => 'a4', 'hint' => 'жити'],
        'polyglot-past-perfect-basics-b1-q21' => ['prompt' => 'Скільки книжок вони прочитали до кінця того літа?', 'target' => 'How many books had they read by the end of that summer', 'marker' => 'a6', 'hint' => 'прочитати'],
        'polyglot-past-perfect-basics-b1-q22' => ['prompt' => 'Чому він пішов до того, як ми приїхали?', 'target' => 'Why had he left before we arrived', 'marker' => 'a4', 'hint' => 'піти'],
        'polyglot-past-perfect-basics-b1-q23' => ['prompt' => 'Кого ти зустрів до початку курсу?', 'target' => 'Who had you met before the course started', 'marker' => 'a4', 'hint' => 'зустріти'],
        'polyglot-past-perfect-basics-b1-q24' => ['prompt' => 'Що вони підготували до того, як почалася вечірка?', 'target' => 'What had they prepared before the party started', 'marker' => 'a4', 'hint' => 'підготувати'],
    ];

    private function definition(): array
    {
        return json_decode(
            (string) file_get_contents(dirname(__DIR__, 2).'/'.self::DEFINITION),
            true,
            512,
            JSON_THROW_ON_ERROR
        );
    }

    /** @return list<string> */
    private function orderedAnswers(array $question): array
    {
        $answers = $question['answers'];
        uksort(
            $answers,
            static fn (string $left, string $right): int => ((int) substr($left, 1)) <=> ((int) substr($right, 1))
        );

        return array_values($answers);
    }

    public function test_basics_bank_keeps_its_identity_and_complete_b1_inventory(): void
    {
        $definition = $this->definition();
        $questions = $definition['questions'];
        $expectedUuids = array_keys(self::EXPECTED_CONTENT);

        $this->assertCount(24, $questions);
        $this->assertSame(range(1, 24), array_column($questions, 'id'));
        $this->assertSame($expectedUuids, array_column($questions, 'uuid'));
        $this->assertSame($expectedUuids, $definition['saved_test']['question_uuids']);
        $this->assertSame('polyglot-past-perfect-basics-bd5fa7', $definition['saved_test']['uuid']);
        $this->assertSame('polyglot-past-perfect-basics-b1', $definition['saved_test']['slug']);
        $this->assertTrue($definition['saved_test']['filters']['supports_duplicate_tokens']);
        $this->assertSame(['B1' => 24], array_count_values(array_column($questions, 'level')));
        $this->assertCount(24, array_unique(array_column($questions, 'question')));
    }

    public function test_prompts_targets_options_and_ukrainian_verb_hints_are_user_ready(): void
    {
        foreach ($this->definition()['questions'] as $question) {
            $uuid = (string) $question['uuid'];
            $expected = self::EXPECTED_CONTENT[$uuid];
            $answers = $this->orderedAnswers($question);
            $options = $question['options'];

            $this->assertSame($expected['prompt'], $question['question'], $uuid);
            $this->assertSame($expected['target'], implode(' ', $answers), $uuid);
            $this->assertSame(4, $question['type'], $uuid);
            $this->assertSame([], $question['variants'], $uuid);
            $this->assertMatchesRegularExpression('/[А-Яа-яІіЇїЄєҐґ]/u', $question['question'], $uuid);
            $this->assertMatchesRegularExpression('/[.!?]$/u', $question['question'], $uuid);
            $this->assertSame($answers, ComposeTokenCase::normalize($answers), $uuid);
            $this->assertSame($answers, array_slice($options, 0, count($answers)), $uuid);

            foreach ($options as $option) {
                $this->assertDoesNotMatchRegularExpression('/\s/u', $option, "Multiword tile in {$uuid}: {$option}");
            }

            $answerMultiplicity = array_count_values($answers);
            $optionMultiplicity = array_count_values($options);
            foreach ($answerMultiplicity as $token => $count) {
                $this->assertGreaterThanOrEqual($count, $optionMultiplicity[$token] ?? 0, "Missing {$token} in {$uuid}");
            }

            $correctLookup = array_fill_keys(array_map('mb_strtolower', $answers), true);
            $distractors = array_values(array_unique(array_map(
                'mb_strtolower',
                array_filter(
                    $options,
                    static fn (string $option): bool => ! isset($correctLookup[mb_strtolower($option)])
                )
            )));
            $this->assertCount(5, $distractors, "Unexpected distractor count in {$uuid}");

            $this->assertSame(
                [$expected['marker'] => $expected['hint']],
                $question['verb_hints'] ?? null,
                $uuid
            );
            $this->assertArrayHasKey($expected['marker'], $question['answers'], $uuid);
            $this->assertMatchesRegularExpression('/^[А-Яа-яІіЇїЄєҐґʼ’\'\- ]+$/u', $expected['hint'], $uuid);
            $this->assertNotContains(
                mb_strtolower((string) $question['answers'][$expected['marker']]),
                ['had', "hadn't", 'not', 'already'],
                "Hint must be attached to the lexical V3 token in {$uuid}"
            );
        }
    }
}
