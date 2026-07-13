<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class PolyglotFutureSimpleNegativesContentQualityTest extends TestCase
{
    private const DEFINITION = '/database/seeders/V3/Polyglot/PolyglotFutureSimpleNegativesAllLevelsLessonSeeder/definition.json';

    public function test_compose_tokens_and_ukrainian_prompts_are_complete(): void
    {
        $definition = $this->definition();

        $this->assertCount(72, $definition['questions']);

        foreach ($definition['questions'] as $question) {
            $this->assertMatchesRegularExpression(
                '/[.!?…]$/u',
                $question['question'],
                "Missing terminal punctuation for {$question['uuid']}"
            );

            $availableTokens = array_count_values($question['options']);

            foreach (array_count_values(array_values($question['answers'])) as $token => $requiredCount) {
                $this->assertGreaterThanOrEqual(
                    $requiredCount,
                    $availableTokens[$token] ?? 0,
                    "Answer token '{$token}' is missing for {$question['uuid']}"
                );
            }
        }
    }

    public function test_audited_phrases_keep_their_natural_ukrainian_prompts_and_english_answers(): void
    {
        $questions = [];

        foreach ($this->definition()['questions'] as $question) {
            $questions[$question['uuid']] = $question;
        }

        $expected = [
            'fs-negatives-poly-a1-03' => ['Вона не буде відвідувати свою бабусю.', 'She will not visit her grandmother'],
            'fs-negatives-poly-a2-02' => ['Ти не будеш зустрічатися з нашими друзями.', "You won't meet with our friends"],
            'fs-negatives-poly-a2-07' => ['Моя сестра не буде надсилати цей електронний лист.', 'My sister will not send the email'],
            'fs-negatives-poly-b1-04' => ['Він не буде відвідувати практичний семінар.', "He won't attend the workshop"],
            'fs-negatives-poly-b1-10' => ['Компанія не буде розширювати спектр своїх послуг.', "The company won't expand its range of services"],
            'fs-negatives-poly-b2-02' => ['Ти не будеш реагувати на це занепокоєння.', "You won't address the concern"],
            'fs-negatives-poly-b2-03' => ['Вона не буде вести переговори щодо контракту.', 'She will not negotiate the contract'],
            'fs-negatives-poly-c1-04' => ['Він не буде виступати посередником у суперечці.', "He won't mediate the dispute"],
            'fs-negatives-poly-c1-07' => ['Моя сестра не буде звіряти рахунки.', 'My sister will not reconcile the accounts'],
            'fs-negatives-poly-c2-02' => ['Ти не будеш заперечувати основоположне припущення.', "You won't contest the underlying premise"],
            'fs-negatives-poly-c2-04' => ['Він не буде виступати арбітром у конституційній суперечці.', "He won't arbitrate the constitutional dispute"],
            'fs-negatives-poly-c2-08' => ['Наш викладач не буде підтверджувати твердження архівними свідченнями.', "Our teacher won't corroborate the claim with archival evidence"],
            'fs-negatives-poly-c2-12' => ['Комітет не буде робити застережень щодо остаточного висновку.', "The committee won't qualify the final judgment"],
        ];

        foreach ($expected as $uuid => [$prompt, $answer]) {
            $this->assertArrayHasKey($uuid, $questions);
            $this->assertSame($prompt, $questions[$uuid]['question']);
            $this->assertSame($answer, implode(' ', array_values($questions[$uuid]['answers'])));
        }
    }

    private function definition(): array
    {
        $path = dirname(__DIR__, 2).self::DEFINITION;

        return json_decode(file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
    }
}
