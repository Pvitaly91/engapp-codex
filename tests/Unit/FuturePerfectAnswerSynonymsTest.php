<?php

namespace Tests\Unit;

use App\Support\ComposeTokenCase;
use App\Support\FuturePerfectAnswerSynonyms;
use PHPUnit\Framework\TestCase;

class FuturePerfectAnswerSynonymsTest extends TestCase
{
    public function test_it_accepts_and_exposes_the_curated_synonyms_without_changing_canonical_tokens(): void
    {
        $payload = FuturePerfectAnswerSynonyms::decorate([
            'uuid' => 'ukm-fpq-poly-b1-05',
            'type' => 4,
            'answers' => ['How', 'many', 'applications', 'will', 'the', 'team', 'have', 'reviewed', 'by', 'Monday'],
            'answer_map' => [
                'a1' => 'How',
                'a2' => 'many',
                'a3' => 'applications',
                'a4' => 'will',
                'a5' => 'the',
                'a6' => 'team',
                'a7' => 'have',
                'a8' => 'reviewed',
                'a9' => 'by',
                'a10' => 'Monday',
            ],
            'markers' => ['a1', 'a2', 'a3', 'a4', 'a5', 'a6', 'a7', 'a8', 'a9', 'a10'],
            'accepted_answers_by_marker' => ['a6' => ['team'], 'a8' => ['reviewed']],
        ]);

        $this->assertSame('team', $payload['answer_map']['a6']);
        $this->assertSame('reviewed', $payload['answers'][7]);
        $this->assertSame(['crew'], $payload['answer_synonyms_by_marker']['a6']);
        $this->assertSame(['examined'], $payload['answer_synonyms_by_marker']['a8']);
        $this->assertSame(['team', 'crew'], $payload['accepted_answers_by_marker']['a6']);
        $this->assertSame(['reviewed', 'examined'], $payload['accepted_answers_by_marker']['a8']);
        $this->assertSame(['team', 'crew'], $payload['accepted_answers'][5]);
        $this->assertSame(['reviewed', 'examined'], $payload['accepted_answers'][7]);
    }

    public function test_cached_compose_payloads_are_decorated_again_after_normalization(): void
    {
        $payload = ComposeTokenCase::normalizeQuestionPayload([
            'uuid' => 'ukm-fpq-poly-b1-05',
            'type' => 4,
            'answers' => ['how', 'many', 'applications', 'will', 'the', 'team', 'have', 'reviewed', 'by', 'Monday'],
            'markers' => ['a1', 'a2', 'a3', 'a4', 'a5', 'a6', 'a7', 'a8', 'a9', 'a10'],
        ]);

        $this->assertSame('How', $payload['answers'][0]);
        $this->assertContains('crew', $payload['accepted_answers_by_marker']['a6']);
        $this->assertContains('examined', $payload['accepted_answers_by_marker']['a8']);
    }

    public function test_gap_answers_accept_every_valid_combination_and_expose_inline_labels(): void
    {
        $payload = FuturePerfectAnswerSynonyms::decorate([
            'uuid' => 'ukm-fpq-v3-b1-05',
            'type' => 0,
            'answers' => ['will the team have reviewed'],
            'answer_map' => ['a1' => 'will the team have reviewed'],
            'markers' => ['a1'],
            'accepted_answers_by_marker' => ['a1' => ['will the team have reviewed']],
        ]);

        $this->assertSame('will the team have reviewed', $payload['answers'][0]);
        $this->assertSame(
            ['team' => ['crew'], 'reviewed' => ['examined']],
            $payload['answer_synonym_tokens_by_marker']['a1']
        );
        $this->assertSame(['crew', 'examined'], $payload['answer_synonyms_by_marker']['a1']);
        $this->assertSame(
            [
                'will the team have reviewed',
                'will the crew have reviewed',
                'will the team have examined',
                'will the crew have examined',
            ],
            $payload['accepted_answers_by_marker']['a1']
        );
    }

    public function test_gap_answers_do_not_accept_a_synonym_for_text_outside_the_gap(): void
    {
        $payload = FuturePerfectAnswerSynonyms::decorate([
            'uuid' => 'ukm-fpf-v3-a2-01',
            'type' => 0,
            'answers' => ['Liam will have completed'],
            'answer_map' => ['a1' => 'Liam will have completed'],
            'markers' => ['a1'],
        ]);

        $this->assertSame(
            ['completed' => ['finished']],
            $payload['answer_synonym_tokens_by_marker']['a1']
        );
        $this->assertSame(
            ['Liam will have completed', 'Liam will have finished'],
            $payload['accepted_answers_by_marker']['a1']
        );
        $this->assertNotContains('form', $payload['accepted_answers_by_marker']['a1']);
    }

    public function test_negative_gap_answers_combine_lexical_synonyms_with_contractions(): void
    {
        $payload = FuturePerfectAnswerSynonyms::decorate([
            'uuid' => 'ukm-fpn-v3-b2-02',
            'type' => 0,
            'answers' => ["our legal team won't have reviewed"],
            'answer_map' => ['a1' => "our legal team won't have reviewed"],
            'markers' => ['a1'],
        ]);

        $accepted = $payload['accepted_answers_by_marker']['a1'];

        $this->assertCount(8, $accepted);
        $this->assertContains("our legal group won't have examined", $accepted);
        $this->assertContains('our legal group will not have examined', $accepted);
        $this->assertSame(['group', 'examined'], $payload['answer_synonyms_by_marker']['a1']);
    }

    public function test_it_matches_whole_tokens_instead_of_substrings(): void
    {
        $payload = FuturePerfectAnswerSynonyms::decorate([
            'uuid' => 'ukm-fpf-v3-c1-06',
            'type' => 0,
            'answers' => ['the chairperson will have addressed'],
            'answer_map' => ['a1' => 'the chairperson will have addressed'],
            'markers' => ['a1'],
        ]);

        $this->assertSame(
            ['the chairperson will have addressed'],
            $payload['accepted_answers_by_marker']['a1']
        );
        $this->assertSame([], $payload['answer_synonym_tokens_by_marker']);
    }

    public function test_it_does_not_apply_a_global_synonym_rule(): void
    {
        $payload = [
            'uuid' => 'unrelated-question',
            'type' => 4,
            'answers' => ['The', 'team'],
            'answer_map' => ['a1' => 'The', 'a2' => 'team'],
            'markers' => ['a1', 'a2'],
        ];

        $this->assertSame($payload, FuturePerfectAnswerSynonyms::decorate($payload));
    }

    public function test_the_catalog_matches_the_authored_future_perfect_builder_banks(): void
    {
        $definitions = [];

        foreach ($this->definitionPaths() as $path) {
            $definition = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);

            foreach ($definition['questions'] ?? [] as $question) {
                $definitions[$question['uuid']] = $question;
            }
        }

        $catalog = FuturePerfectAnswerSynonyms::catalog();
        $this->assertCount(118, $catalog);
        $this->assertCount(168, $definitions);

        $canonicalTokenCount = 0;
        $synonymCount = 0;

        foreach ($catalog as $uuid => $tokens) {
            $this->assertArrayHasKey($uuid, $definitions, $uuid);
            $authoredAnswers = array_values(array_map(
                static fn (mixed $answer): string => mb_strtolower(trim((string) $answer)),
                $definitions[$uuid]['answers'] ?? []
            ));

            foreach ($tokens as $canonical => $synonyms) {
                $canonicalTokenCount++;
                $synonymCount += count($synonyms);
                $this->assertSame(1, count(array_keys($authoredAnswers, $canonical, true)), "{$uuid}: {$canonical}");
                $this->assertNotEmpty($synonyms, "{$uuid}: {$canonical}");
                $this->assertSame($synonyms, array_values(array_unique($synonyms)), "{$uuid}: {$canonical}");

                foreach ($synonyms as $synonym) {
                    $this->assertSame(0, preg_match('/\s/u', $synonym), "{$uuid}: {$synonym}");
                    $this->assertNotSame($canonical, mb_strtolower($synonym), "{$uuid}: {$synonym}");
                }
            }
        }

        $this->assertSame(171, $canonicalTokenCount);
        $this->assertSame(175, $synonymCount);
    }

    public function test_paired_gap_banks_receive_only_synonyms_that_are_inside_the_answer_marker(): void
    {
        $standardQuestions = [];

        foreach ($this->standardDefinitionPaths() as $path) {
            $definition = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);

            foreach ($definition['questions'] ?? [] as $question) {
                $standardQuestions[$question['uuid']] = $question;
            }
        }

        $affectedQuestions = 0;
        $matchedCanonicalTokens = 0;
        $matchedAliases = 0;

        foreach (FuturePerfectAnswerSynonyms::catalog() as $builderUuid => $catalogTokens) {
            $gapUuid = str_replace('-poly-', '-v3-', $builderUuid);
            $this->assertArrayHasKey($gapUuid, $standardQuestions, $gapUuid);

            $question = $standardQuestions[$gapUuid];
            $answer = (string) ($question['markers']['a1']['answer'] ?? '');
            $answerTokens = array_fill_keys(array_map(
                static fn (string $token): string => mb_strtolower($token),
                preg_split('/\s+/u', $answer) ?: []
            ), true);
            $expected = array_filter(
                $catalogTokens,
                static fn (string $canonical): bool => isset($answerTokens[$canonical]),
                ARRAY_FILTER_USE_KEY
            );
            $payload = FuturePerfectAnswerSynonyms::decorate([
                'uuid' => $gapUuid,
                'type' => 0,
                'answers' => [$answer],
                'answer_map' => ['a1' => $answer],
                'markers' => ['a1'],
            ]);

            $this->assertSame($answer, $payload['answers'][0], $gapUuid);
            $this->assertSame($expected, $payload['answer_synonym_tokens_by_marker']['a1'] ?? [], $gapUuid);

            if ($expected !== []) {
                $affectedQuestions++;
                $matchedCanonicalTokens += count($expected);
                $matchedAliases += array_sum(array_map('count', $expected));
            }
        }

        $this->assertSame(108, $affectedQuestions);
        $this->assertSame(132, $matchedCanonicalTokens);
        $this->assertSame(136, $matchedAliases);
    }

    /** @return array<int, string> */
    private function definitionPaths(): array
    {
        $base = dirname(__DIR__, 2).'/database/seeders/V3/Polyglot/';

        return [
            $base.'UkrainianMixedFuturePerfectFormsBuilderSeeder/definition.json',
            $base.'UkrainianMixedFuturePerfectNegativesBuilderSeeder/definition.json',
            $base.'UkrainianMixedFuturePerfectQuestionsBuilderSeeder/definition.json',
            $base.'UkrainianMixedFuturePerfectTimeExpressionsBuilderSeeder/definition.json',
        ];
    }

    /** @return array<int, string> */
    private function standardDefinitionPaths(): array
    {
        $base = dirname(__DIR__, 2).'/database/seeders/V3/FutureForms/FuturePerfect/';

        return [
            $base.'UkrainianMixedFuturePerfectFormsStandardSeeder/definition.json',
            $base.'UkrainianMixedFuturePerfectNegativesStandardSeeder/definition.json',
            $base.'UkrainianMixedFuturePerfectQuestionsStandardSeeder/definition.json',
            $base.'UkrainianMixedFuturePerfectTimeExpressionsStandardSeeder/definition.json',
        ];
    }
}
