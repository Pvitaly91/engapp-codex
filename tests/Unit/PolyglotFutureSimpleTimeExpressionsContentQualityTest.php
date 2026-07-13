<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class PolyglotFutureSimpleTimeExpressionsContentQualityTest extends TestCase
{
    private const DEFINITION = '/database/seeders/V3/Polyglot/PolyglotFutureSimpleTimeExpressionsAllLevelsLessonSeeder/definition.json';

    private const TIME_TAGS = [
        'tomorrow_next' => [
            'fs-time-poly-a1-01', 'fs-time-poly-a1-03', 'fs-time-poly-a1-08', 'fs-time-poly-a1-09', 'fs-time-poly-a1-12',
            'fs-time-poly-a2-01', 'fs-time-poly-a2-03', 'fs-time-poly-a2-08', 'fs-time-poly-a2-12',
            'fs-time-poly-b1-01', 'fs-time-poly-b1-03', 'fs-time-poly-b1-07', 'fs-time-poly-b1-08', 'fs-time-poly-b1-12',
            'fs-time-poly-b2-01', 'fs-time-poly-b2-03', 'fs-time-poly-b2-07', 'fs-time-poly-b2-08', 'fs-time-poly-b2-12',
            'fs-time-poly-c1-03', 'fs-time-poly-c1-07', 'fs-time-poly-c1-08', 'fs-time-poly-c1-12',
            'fs-time-poly-c2-03', 'fs-time-poly-c2-07', 'fs-time-poly-c2-08', 'fs-time-poly-c2-12',
        ],
        'soon_later' => [
            'fs-time-poly-a1-04', 'fs-time-poly-a1-05',
            'fs-time-poly-a2-02', 'fs-time-poly-a2-04', 'fs-time-poly-a2-11',
            'fs-time-poly-b1-02', 'fs-time-poly-b1-10', 'fs-time-poly-b1-11',
            'fs-time-poly-b2-02', 'fs-time-poly-b2-09', 'fs-time-poly-b2-11',
            'fs-time-poly-c1-02', 'fs-time-poly-c1-09', 'fs-time-poly-c1-11',
            'fs-time-poly-c2-02', 'fs-time-poly-c2-09', 'fs-time-poly-c2-11',
        ],
        'in_period' => [
            'fs-time-poly-a1-06', 'fs-time-poly-a1-11',
            'fs-time-poly-a2-05', 'fs-time-poly-a2-09',
            'fs-time-poly-b1-04', 'fs-time-poly-b1-05', 'fs-time-poly-b1-09',
            'fs-time-poly-b2-04', 'fs-time-poly-b2-05',
            'fs-time-poly-c1-04', 'fs-time-poly-c1-05',
            'fs-time-poly-c2-04', 'fs-time-poly-c2-05',
        ],
        'future_marker' => [
            'fs-time-poly-a1-02', 'fs-time-poly-a1-07', 'fs-time-poly-a1-10',
            'fs-time-poly-a2-06', 'fs-time-poly-a2-07', 'fs-time-poly-a2-10',
            'fs-time-poly-b1-06',
            'fs-time-poly-b2-06', 'fs-time-poly-b2-10',
            'fs-time-poly-c1-01', 'fs-time-poly-c1-06', 'fs-time-poly-c1-10',
            'fs-time-poly-c2-01', 'fs-time-poly-c2-06', 'fs-time-poly-c2-10',
        ],
    ];

    public function test_every_compose_question_has_complete_tokens_contrastive_distractors_and_natural_prompt_shape(): void
    {
        $definition = $this->definition();

        $this->assertCount(72, $definition['questions']);
        $this->assertTrue($definition['saved_test']['filters']['supports_duplicate_tokens']);

        $distractorSets = [];
        $sentenceStartOnlyTokens = ['You', 'He', 'She', 'We', 'They', 'My', 'Our', 'The', 'Researchers'];

        foreach ($definition['questions'] as $question) {
            $uuid = $question['uuid'];
            $answerTokens = array_values($question['answers']);
            $uniqueAnswerTokens = array_values(array_unique($answerTokens));
            $distractors = array_values(array_diff($question['options'], $uniqueAnswerTokens));

            $this->assertMatchesRegularExpression('/[.!?…]$/u', $question['question'], "Missing punctuation for {$uuid}");
            $this->assertDoesNotMatchRegularExpression('/\bбуд(?:у|еш|е|емо|ете|уть)\b/u', $question['question'], "Mechanical analytic future remains in {$uuid}");
            $this->assertStringNotContainsString("'", $question['question'], "Use the Ukrainian apostrophe in {$uuid}");
            $this->assertMatchesRegularExpression('/^\p{Lu}/u', $answerTokens[0], "Sentence must start with uppercase in {$uuid}");

            foreach (array_slice($answerTokens, 1) as $token) {
                $this->assertNotContains($token, $sentenceStartOnlyTokens, "Unexpected uppercase token '{$token}' inside {$uuid}");
            }

            foreach ($uniqueAnswerTokens as $answerToken) {
                $this->assertContains($answerToken, $question['options'], "Answer token '{$answerToken}' is absent from options for {$uuid}");
            }

            $this->assertCount(3, $distractors, "Expected three distractors for {$uuid}");
            $legacyDistractors = $distractors;
            sort($legacyDistractors);
            $this->assertNotSame(['ago', 'did', 'yesterday'], $legacyDistractors, "Legacy distractors remain in {$uuid}");

            $distractorSets[implode('|', $distractors)] = true;
        }

        $this->assertGreaterThanOrEqual(50, count($distractorSets), 'Distractors should vary by time-expression pattern and level.');
    }

    public function test_time_expression_tags_match_the_actual_answer_patterns(): void
    {
        $expectedTags = [];
        foreach (self::TIME_TAGS as $tag => $uuids) {
            foreach ($uuids as $uuid) {
                $this->assertArrayNotHasKey($uuid, $expectedTags, "Duplicate expected time tag for {$uuid}");
                $expectedTags[$uuid] = $tag;
            }
        }

        $this->assertCount(72, $expectedTags);

        foreach ($this->definition()['questions'] as $question) {
            $actualTags = array_values(array_intersect(
                $question['tag_keys'],
                ['tomorrow_next', 'soon_later', 'in_period', 'future_marker']
            ));

            $this->assertCount(1, $actualTags, "Expected exactly one time-expression tag for {$question['uuid']}");
            $this->assertSame($expectedTags[$question['uuid']], $actualTags[0], "Wrong time-expression tag for {$question['uuid']}");
        }
    }

    public function test_audited_semantic_pairs_remain_natural_and_consistent(): void
    {
        $questions = [];
        foreach ($this->definition()['questions'] as $question) {
            $questions[$question['uuid']] = $question;
        }

        $expected = [
            'fs-time-poly-a1-03' => ['Вона відвідає свою бабусю наступного тижня.', 'She will visit her grandmother next week'],
            'fs-time-poly-a1-09' => ['Команда чекатиме надворі завтра вдень.', 'The team will wait outside tomorrow afternoon'],
            'fs-time-poly-a2-02' => ['Ти зустрінешся з нашими друзями пізніше сьогодні.', 'You will meet with our friends later today'],
            'fs-time-poly-a2-07' => ['Моя сестра надішле електронного листа у п’ятницю.', 'My sister will send the email on Friday'],
            'fs-time-poly-b1-04' => ['Він відвідає практичне заняття у найближчому майбутньому.', 'He will attend the workshop in the near future'],
            'fs-time-poly-b1-10' => ['Компанія розширить асортимент своїх послуг пізніше цього року.', 'The company will expand its range of services later this year'],
            'fs-time-poly-b2-02' => ['Ти розглянеш це проблемне питання пізніше в процесі.', 'You will address the concern later in the process'],
            'fs-time-poly-b2-03' => ['Вона проведе переговори щодо контракту під час наступного етапу.', 'She will negotiate the contract during the next phase'],
            'fs-time-poly-b2-06' => ['Вони відстежуватимуть процес до остаточного перегляду.', 'They will monitor the process before the final review'],
            'fs-time-poly-b2-09' => ['Команда скоординує дії у відповідь у належний час.', 'The team will coordinate the response in due course'],
            'fs-time-poly-b2-12' => ['Комітет оцінюватиме альтернативи протягом наступного циклу.', 'The committee will evaluate the alternatives throughout the coming cycle'],
            'fs-time-poly-c1-04' => ['Він виступить посередником у суперечці в середньостроковій перспективі.', 'He will mediate the dispute in the medium term'],
            'fs-time-poly-c1-07' => ['Моя сестра звірить рахунки до наступного слухання.', 'My sister will reconcile the accounts before the subsequent hearing'],
            'fs-time-poly-c1-08' => ['Наш викладач обґрунтує твердження під час майбутньої лекції.', 'Our teacher will substantiate the claim during the forthcoming lecture'],
            'fs-time-poly-c1-09' => ['Команда посприяє переговорам у належний час.', 'The team will facilitate the negotiations in due time'],
            'fs-time-poly-c1-12' => ['Комітет переглядатиме рекомендацію протягом наступного звітного періоду.', 'The committee will review the recommendation throughout the next reporting period'],
            'fs-time-poly-c2-02' => ['Ти оскаржиш засадничу передумову на певному пізнішому етапі.', 'You will contest the underlying premise at some later juncture'],
            'fs-time-poly-c2-04' => ['Він виступить арбітром у конституційній суперечці в довгостроковій перспективі.', 'He will arbitrate the constitutional dispute in the longer term'],
            'fs-time-poly-c2-08' => ['Наш викладач підкріпить твердження архівними доказами під час майбутнього семінару.', 'Our teacher will corroborate the claim with archival evidence during the forthcoming seminar'],
            'fs-time-poly-c2-09' => ['Коли настане слушний час, команда скоординує дипломатичну ініціативу.', 'The team will orchestrate the diplomatic initiative in the fullness of time'],
            'fs-time-poly-c2-10' => ['Компанія гарантуватиме фінансування довгострокової програми ближче до завершення розслідування.', 'The company will underwrite the long-term programme towards the culmination of the inquiry'],
            'fs-time-poly-c2-12' => ['Комітет уточнюватиме попередній висновок під час майбутнього конституційного перегляду.', 'The committee will refine its preliminary judgment throughout the forthcoming constitutional review'],
        ];

        foreach ($expected as $uuid => [$prompt, $answer]) {
            $this->assertArrayHasKey($uuid, $questions);
            $this->assertSame($prompt, $questions[$uuid]['question']);
            $this->assertSame($answer, implode(' ', array_values($questions[$uuid]['answers'])));
        }
    }

    private function definition(): array
    {
        return json_decode(
            file_get_contents(dirname(__DIR__, 2).self::DEFINITION),
            true,
            512,
            JSON_THROW_ON_ERROR
        );
    }
}
