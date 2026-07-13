<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class FutureSimpleTimeExpressionsContentRegressionTest extends TestCase
{
    private function packagePath(): string
    {
        return dirname(__DIR__, 2).'/database/seeders/V3/FutureForms/FutureSimple/FutureSimpleTimeExpressionsAllLevelsV3Seeder';
    }

    private function readJson(string $path): array
    {
        return json_decode(file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
    }

    private function expectedTag(string $answer): string
    {
        if (preg_match('/\b(?:tomorrow|next|following|coming|forthcoming|subsequent|ensuing)\b|\bto come\b/', $answer)) {
            return 'tomorrow_next';
        }

        if (preg_match('/(?:soon|later|before long|eventually|ultimately|due (?:course|time)|fullness of time)/', $answer)) {
            return 'soon_later';
        }

        if (str_starts_with($answer, 'in ') || str_starts_with($answer, 'within ')) {
            return 'in_period';
        }

        return 'future_marker';
    }

    public function test_all_questions_are_clean_and_have_one_future_compatible_option(): void
    {
        $definition = $this->readJson($this->packagePath().'/definition.json');

        $this->assertCount(72, $definition['questions']);

        foreach ($definition['questions'] as $question) {
            $marker = $question['markers']['a1'];
            $options = $marker['options'];

            $this->assertStringEndsWith('.', $question['question'], "Missing punctuation in {$question['uuid']}");
            $this->assertCount(2, $question['variants'], "Expected a real alternate variant in {$question['uuid']}");
            $this->assertSame($question['question'], $question['variants'][0] ?? null);
            $this->assertNotSame($question['variants'][0], $question['variants'][1]);
            $this->assertStringContainsString(' will probably ', $question['variants'][1]);
            $this->assertStringEndsWith('.', $question['variants'][1]);
            $this->assertSame(1, substr_count($question['question'], '{a1}'));
            $this->assertCount(4, $options, "Wrong option count in {$question['uuid']}");
            $this->assertCount(4, array_unique(array_map('mb_strtolower', $options)), "Duplicate option in {$question['uuid']}");
            $this->assertSame($marker['answer'], $options[0], "Correct answer must remain first in source for {$question['uuid']}");

            foreach (array_slice($options, 1) as $distractor) {
                $this->assertMatchesRegularExpression(
                    '/(?:\byesterday\b|\blast\b|\bago\b|\bprevious(?:ly)?\b|\bearlier\b|\brecent(?:ly)?\b|\bpast\b|\bbefore now\b|\bbefore the .+ began\b|\bafter the previous\b|\boutside the previous\b|\bover generations past\b)/i',
                    $distractor,
                    "Distractor is not a clear past-time contrast in {$question['uuid']}: {$distractor}"
                );
            }

            $expectedTag = $this->expectedTag($marker['answer']);
            $this->assertSame([$expectedTag], $marker['gap_tags'], "Wrong gap tag in {$question['uuid']}");
            $this->assertSame([$expectedTag, 'level_'.strtolower($question['level'])], $question['tag_keys']);
        }
    }

    public function test_every_locale_has_a_specific_non_spoiling_time_hint(): void
    {
        $definition = $this->readJson($this->packagePath().'/definition.json');
        $questions = collect($definition['questions'])->keyBy('uuid');
        $localizations = [];

        foreach (['uk', 'pl', 'en'] as $locale) {
            $localization = $this->readJson($this->packagePath()."/localizations/{$locale}.json");
            $this->assertCount(72, $localization['questions']);
            $localizations[$locale] = collect($localization['questions'])->keyBy('uuid');
        }

        foreach ($questions as $uuid => $question) {
            $uk = $localizations['uk'][$uuid]['verb_hints']['a1'];
            $pl = $localizations['pl'][$uuid]['verb_hints']['a1'];
            $en = $localizations['en'][$uuid]['verb_hints']['a1'];

            $this->assertSame($question['markers']['a1']['verb_hint'], $uk);
            foreach ([$uk, $pl, $en] as $hint) {
                $this->assertNotSame('', trim($hint));
                $this->assertStringNotContainsString(
                    mb_strtolower($question['markers']['a1']['answer']),
                    mb_strtolower($hint),
                    "Hint leaks the English answer in {$uuid}"
                );
            }
        }

        $this->assertSame('Часовий вираз: «наступний день, період або майбутня подія»', $localizations['uk']['fs-time-v3-b1-01']['verb_hints']['a1']);
        $this->assertSame('Time expression: “unspecified point further ahead”', $localizations['en']['fs-time-v3-b2-09']['verb_hints']['a1']);
        $this->assertSame('Określenie czasu: „przyszły moment lub termin”', $localizations['pl']['fs-time-v3-a1-07']['verb_hints']['a1']);
    }

    public function test_semantically_unnatural_stems_were_rewritten(): void
    {
        $questions = collect($this->readJson($this->packagePath().'/definition.json')['questions'])->keyBy('uuid');
        $expected = [
            'fs-time-v3-a1-03' => 'She will visit her grandmother {a1}.',
            'fs-time-v3-a1-09' => 'The team will understand the reason {a1}.',
            'fs-time-v3-b1-10' => 'The shop will become busier {a1}.',
            'fs-time-v3-b2-12' => 'The committee will review the alternatives {a1}.',
            'fs-time-v3-c1-08' => 'Researchers will study the long-term trend {a1}.',
            'fs-time-v3-c1-12' => 'The committee will monitor the recommendation {a1}.',
            'fs-time-v3-c2-08' => 'Future scholars will reinterpret the archival evidence {a1}.',
            'fs-time-v3-c2-10' => 'The commission will publish its conclusions {a1}.',
            'fs-time-v3-c2-12' => 'Legal scholars will debate the final judgment {a1}.',
        ];

        foreach ($expected as $uuid => $stem) {
            $this->assertSame($stem, $questions[$uuid]['question']);
        }

        $allStems = implode(' ', $questions->pluck('question')->all());
        $this->assertStringNotContainsString('visit grandma', $allStems);
        $this->assertStringNotContainsString('expand the service towards the evening', $allStems);
        $this->assertStringNotContainsString('recommend an alternative', $allStems);
        $this->assertStringNotContainsString('corroborate the archival evidence', $allStems);
        $this->assertStringNotContainsString('qualify the final judgment', $allStems);
    }
}
