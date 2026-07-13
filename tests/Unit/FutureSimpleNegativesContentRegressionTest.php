<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class FutureSimpleNegativesContentRegressionTest extends TestCase
{
    private function packagePath(): string
    {
        return dirname(__DIR__, 2).'/database/seeders/V3/FutureForms/FutureSimple/FutureSimpleNegativesAllLevelsV3Seeder';
    }

    private function readJson(string $path): array
    {
        return json_decode(file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
    }

    public function test_questions_have_clean_sentences_and_distractors(): void
    {
        $definition = $this->readJson($this->packagePath().'/definition.json');

        $this->assertCount(72, $definition['questions']);

        foreach ($definition['questions'] as $question) {
            $marker = $question['markers']['a1'];

            $this->assertStringNotContainsString(
                'Complete using the negative Future Simple: ',
                $question['question'],
                "Instruction prefix leaked into {$question['uuid']}"
            );
            $this->assertStringEndsWith('.', $question['question']);
            $this->assertSame($question['question'], $question['variants'][0] ?? null);
            $this->assertContains($marker['answer'], $marker['options']);
            $this->assertDoesNotMatchRegularExpression(
                '/\b(?:cookking|waitting|readding|meetting|joinning|leadding)\b/i',
                implode(' ', $marker['options']),
                "Misspelled distractor in {$question['uuid']}"
            );
        }

        $visitGrandmother = collect($definition['questions'])->firstWhere('uuid', 'fs-negatives-v3-a1-03');
        $this->assertStringContainsString('her grandmother', $visitGrandmother['question']);
        $this->assertStringNotContainsString('grandma', $visitGrandmother['question']);
    }

    public function test_every_hint_is_lexical_and_does_not_reveal_the_answer(): void
    {
        $definition = $this->readJson($this->packagePath().'/definition.json');
        $prefixes = [
            'uk' => 'Дієслово:',
            'pl' => 'Czasownik:',
            'en' => 'Verb:',
        ];

        $contractedCount = 0;
        $fullCount = 0;
        foreach ($definition['questions'] as $question) {
            $answer = $question['markers']['a1']['answer'];

            if (str_starts_with($answer, "won't ")) {
                ++$contractedCount;
            } elseif (str_starts_with($answer, 'will not ')) {
                ++$fullCount;
            } else {
                $this->fail("Unexpected negative form for {$question['uuid']}: {$answer}");
            }
        }

        $this->assertSame(54, $contractedCount);
        $this->assertSame(18, $fullCount);

        foreach (['uk', 'pl', 'en'] as $locale) {
            $localization = $this->readJson($this->packagePath()."/localizations/{$locale}.json");
            $this->assertCount(72, $localization['questions']);

            foreach ($localization['questions'] as $question) {
                $hint = $question['verb_hints']['a1'];

                $this->assertStringStartsWith($prefixes[$locale], $hint);
                $this->assertDoesNotMatchRegularExpression(
                    "/(?:\\bwill\\s+not\\b|\\bwon't\\b|Future Simple|\\bV1\\b)/i",
                    $hint,
                    "Hint reveals the answer for {$question['uuid']} ({$locale})"
                );
            }
        }

        $ukHints = collect($this->readJson($this->packagePath().'/localizations/uk.json')['questions'])
            ->keyBy('uuid')
            ->map(fn (array $question): string => $question['verb_hints']['a1']);
        foreach ($definition['questions'] as $question) {
            $this->assertSame($question['markers']['a1']['verb_hint'], $ukHints[$question['uuid']]);
        }
    }

    public function test_context_sensitive_hints_and_polish_verbs_are_natural(): void
    {
        $expectedPhrases = [
            'uk' => [
                'fs-negatives-v3-a1-03' => 'відвідати її бабусю',
                'fs-negatives-v3-a2-12' => 'повернути книжку',
                'fs-negatives-v3-c1-07' => 'звірити рахунки',
                'fs-negatives-v3-c1-11' => 'повторити експеримент',
                'fs-negatives-v3-c1-12' => 'внести зміни до рекомендації',
                'fs-negatives-v3-c2-04' => 'виступити арбітром у конституційному спорі',
                'fs-negatives-v3-c2-06' => 'критично дослідити панівні погляди',
                'fs-negatives-v3-c2-12' => 'пом’якшити категоричність остаточного судження',
            ],
            'pl' => [
                'fs-negatives-v3-a1-03' => 'odwiedzić swoją babcię',
                'fs-negatives-v3-a2-12' => 'zwrócić książkę',
                'fs-negatives-v3-c1-07' => 'uzgodnić salda',
                'fs-negatives-v3-c1-11' => 'powtórzyć eksperyment',
                'fs-negatives-v3-c1-12' => 'zmienić zalecenie',
                'fs-negatives-v3-c2-04' => 'rozstrzygnąć spór konstytucyjny jako arbiter',
                'fs-negatives-v3-c2-06' => 'krytycznie zbadać dominującą doktrynę',
                'fs-negatives-v3-c2-12' => 'ograniczyć kategoryczność ostatecznego osądu',
            ],
            'en' => [
                'fs-negatives-v3-a1-03' => 'visit her grandmother',
                'fs-negatives-v3-a2-12' => 'return the book',
                'fs-negatives-v3-c1-07' => 'reconcile the accounts',
                'fs-negatives-v3-c1-11' => 'replicate the experiment',
                'fs-negatives-v3-c1-12' => 'amend the recommendation',
                'fs-negatives-v3-c2-04' => 'arbitrate the constitutional dispute',
                'fs-negatives-v3-c2-06' => 'interrogate the prevailing orthodoxy',
                'fs-negatives-v3-c2-12' => 'qualify the final judgment',
            ],
        ];

        foreach ($expectedPhrases as $locale => $phrases) {
            $questions = collect($this->readJson($this->packagePath()."/localizations/{$locale}.json")['questions'])
                ->keyBy('uuid');

            foreach ($phrases as $uuid => $phrase) {
                $this->assertStringContainsString($phrase, $questions[$uuid]['verb_hints']['a1']);
            }
        }

        $polish = file_get_contents($this->packagePath().'/localizations/pl.json');
        $this->assertDoesNotMatchRegularExpression('/\?/u', $polish);
        $this->assertDoesNotMatchRegularExpression(
            '/„(?:zegarek|czysty|praca|wsparcie|recenzja|kontakt|aktualizacja|compare|adres|monitor|allocate|assess|coordinate|finanse|recommend|wyzwanie|oversee|mediate|facilitate|konkurs|ochrona|arbitraować|elucidate|przesłuchiwać|redress|orchestrate|underwrite|recontextualize|qualify)”/u',
            $polish
        );
    }
}
