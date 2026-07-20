<?php

namespace Tests\Unit;

use App\Support\AcceptedAnswerVariants;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class PastPerfectDirectBanksContentQualityTest extends TestCase
{
    private const LEVELS = ['A1', 'A2', 'B1', 'B2', 'C1', 'C2'];

    private const BANKS = [
        'negatives' => 'database/seeders/V3/Tenses/PastPerfect/PastPerfectNegativesAllLevelsV3Seeder/definition.json',
        'questions' => 'database/seeders/V3/Tenses/PastPerfect/PastPerfectQuestionsAllLevelsV3Seeder/definition.json',
        'time-expressions' => 'database/seeders/V3/Tenses/PastPerfect/PastPerfectTimeExpressionsAllLevelsV3Seeder/definition.json',
    ];

    private const UUID_PREFIXES = [
        'negatives' => 'pp-negatives-v3',
        'questions' => 'pp-questions-v3',
        'time-expressions' => 'pp-time-expressions-v3',
    ];

    private function definition(string $bank): array
    {
        $path = dirname(__DIR__, 2).'/'.self::BANKS[$bank];

        return json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
    }

    /** @return array<string, array{string}> */
    public static function bankProvider(): array
    {
        return [
            'negatives' => ['negatives'],
            'questions' => ['questions'],
            'time expressions' => ['time-expressions'],
        ];
    }

    #[DataProvider('bankProvider')]
    public function test_direct_bank_is_complete_localized_and_unambiguous(string $bank): void
    {
        $definition = $this->definition($bank);
        $this->assertSame(1, $definition['schema_version'] ?? null, "Unexpected schema version in {$bank}");
        $questions = $definition['questions'];
        $this->assertCount(72, $questions, "{$bank} must contain 72 questions");

        $levelCounts = array_count_values(array_column($questions, 'level'));
        ksort($levelCounts);
        $this->assertSame(self::LEVELS, array_keys($levelCounts), "Unexpected levels in {$bank}");
        foreach (self::LEVELS as $level) {
            $this->assertSame(12, $levelCounts[$level] ?? 0, "{$bank} must have 12 {$level} questions");
        }

        $rawStems = [];
        $completed = [];
        foreach ($questions as $position => $question) {
            $uuid = (string) $question['uuid'];
            $levelIndex = $position % 12 + 1;
            $expectedUuid = sprintf(
                '%s-%s-%02d',
                self::UUID_PREFIXES[$bank],
                strtolower((string) $question['level']),
                $levelIndex
            );
            $this->assertSame($position + 1, $question['id'] ?? null, "Changed question order/id in {$bank}");
            $this->assertSame($expectedUuid, $uuid, "Changed UUID/order in {$bank}");
            $stem = (string) $question['question'];
            $this->assertSame(['a1'], array_keys($question['markers'] ?? []), "{$uuid} must have exactly one marker");
            $this->assertSame(1, substr_count($stem, '{a1}'), "{$uuid} must use {a1} exactly once");
            $this->assertMatchesRegularExpression('/[.!?]$/u', $stem, "{$uuid} needs terminal punctuation");
            $this->assertSame([$stem], $question['variants'] ?? null, "Stale variant in {$uuid}");

            $marker = $question['markers']['a1'];
            $answer = trim((string) ($marker['answer'] ?? ''));
            $options = array_values(array_map(
                static fn ($option): string => trim((string) $option),
                $marker['options'] ?? []
            ));
            $this->assertNotSame('', $answer, "Empty answer in {$uuid}");
            $this->assertCount(5, $options, "{$uuid} must have five options");
            $this->assertSame($answer, $options[0] ?? null, "Correct option must be first in {$uuid}");

            $normalized = array_map(
                static fn (string $option): string => mb_strtolower(AcceptedAnswerVariants::normalizeTypography($option)),
                $options
            );
            $this->assertCount(5, array_unique($normalized), "Duplicate/equivalent options in {$uuid}");
            $accepted = array_map(
                static fn (string $variant): string => mb_strtolower($variant),
                AcceptedAnswerVariants::for($answer)
            );
            foreach (array_slice($normalized, 1) as $distractor) {
                $this->assertNotContains($distractor, $accepted, "Accepted variant is a distractor in {$uuid}");
                $this->assertLessThanOrEqual(8, str_word_count($distractor), "Distractor is not compact in {$uuid}");
            }

            $hint = trim((string) ($marker['verb_hint'] ?? ''));
            $this->assertMatchesRegularExpression('/[А-Яа-яІіЇїЄєҐґ]/u', $hint, "Hint is not Ukrainian in {$uuid}");
            $this->assertStringNotContainsStringIgnoringCase($answer, $hint, "Hint leaks answer in {$uuid}");

            $isLexicalGap = $bank !== 'time-expressions'
                ? ! in_array('short_answers', $question['tag_keys'] ?? [], true)
                : preg_match('/\bhad\b/i', $answer) === 1;
            if ($isLexicalGap) {
                $this->assertMatchesRegularExpression(
                    '/^Дієслово: «[^»]+»\./u',
                    $hint,
                    "Lexical gap hint must begin with a Ukrainian verb in {$uuid}"
                );

                $normalizedAnswer = mb_strtolower(AcceptedAnswerVariants::normalizeTypography($answer));
                $normalizedHint = mb_strtolower(AcceptedAnswerVariants::normalizeTypography($hint));
                preg_match_all("/[a-z]+(?:'[a-z]+)?/u", $normalizedAnswer, $matches);
                $grammarTokens = [
                    'a', 'an', 'the', 'had', "hadn't", 'not', 'yes', 'no',
                    'i', 'you', 'he', 'she', 'it', 'we', 'they',
                    'who', 'what', 'which', 'where', 'when', 'why', 'how',
                    'many', 'much', 'already', 'just', 'ever', 'never', 'yet',
                    'before', 'after', 'by', 'until', 'once', 'as', 'soon', 'than',
                ];
                $lexicalTokens = array_values(array_unique(array_filter(
                    $matches[0],
                    static fn (string $token): bool => mb_strlen($token) >= 3
                        && ! in_array($token, $grammarTokens, true)
                )));
                $this->assertNotEmpty($lexicalTokens, "Could not identify a lexical answer token in {$uuid}");
                foreach ($lexicalTokens as $token) {
                    $this->assertDoesNotMatchRegularExpression(
                        '/(?<![a-z])'.preg_quote($token, '/').'(?![a-z])/iu',
                        $normalizedHint,
                        "Hint leaks lexical answer token '{$token}' in {$uuid}"
                    );
                }
            }

            $rawStems[] = $stem;
            $completed[] = str_replace('{a1}', $answer, $stem);
        }

        $this->assertCount(72, array_unique($rawStems), "Repeated stems in {$bank}");
        $this->assertCount(72, array_unique($completed), "Repeated completed sentences in {$bank}");
    }

    public function test_negative_distractors_do_not_offer_another_standard_english_tense(): void
    {
        foreach ($this->definition('negatives')['questions'] as $question) {
            $uuid = (string) $question['uuid'];
            $distractors = array_slice($question['markers']['a1']['options'], 1);
            foreach ($distractors as $distractor) {
                $this->assertDoesNotMatchRegularExpression(
                    '/^(?:did|didn[\'’]t|has|hasn[\'’]t|have|haven[\'’]t|was|wasn[\'’]t|were|weren[\'’]t)\b/iu',
                    (string) $distractor,
                    "A grammatically viable alternative tense remains in {$uuid}: {$distractor}"
                );
            }
        }
    }

    public function test_question_short_answers_have_evidence_based_lead_ins(): void
    {
        $expected = [
            'pp-questions-v3-a1-08' => 'Ella’s bag was already packed when the taxi came. Had Ella packed her bag before the taxi came? — {a1}.',
            'pp-questions-v3-a1-10' => 'Ben saw snow for the first time on that trip. Had Ben seen snow before the trip? — {a1}.',
            'pp-questions-v3-a2-08' => 'Leo had checked the address before leaving. Had Leo checked the address before leaving? — {a1}.',
            'pp-questions-v3-a2-10' => 'That school trip was the children’s first visit to the museum. Had the children visited the museum before that school trip? — {a1}.',
            'pp-questions-v3-b1-08' => 'Rosa had warned the team before the deadline changed. Had Rosa warned the team before the deadline changed? — {a1}.',
            'pp-questions-v3-b1-10' => 'The headline was published without the editors’ approval. Had the editors approved the headline before publication? — {a1}.',
            'pp-questions-v3-b2-08' => 'Rosa, the chair, had circulated the agenda before the delegates arrived. Had Rosa circulated the agenda before the delegates arrived? — {a1}.',
            'pp-questions-v3-b2-10' => 'The bias was discovered only after publication. Had the reviewers identified the bias before publication? — {a1}.',
            'pp-questions-v3-c1-08' => 'The agency’s disclosure of the conflict was already public when the inquiry opened. Had the agency disclosed the conflict before the inquiry opened? — {a1}.',
            'pp-questions-v3-c1-10' => 'The investigators cited the recording first and authenticated it later. Had the investigators authenticated the recording before citing it? — {a1}.',
            'pp-questions-v3-c2-08' => 'The panel’s draft opinion already stated a limiting principle. Had the panel articulated a limiting principle before publishing its opinion? — {a1}.',
            'pp-questions-v3-c2-10' => 'The commission reconciled the datasets only after drawing its inference. Had the commission reconciled the conflicting datasets before drawing its inference? — {a1}.',
        ];

        $actual = [];
        $hints = [];
        foreach ($this->definition('questions')['questions'] as $question) {
            if (in_array('short_answers', $question['tag_keys'] ?? [], true)) {
                $actual[(string) $question['uuid']] = (string) $question['question'];
                $hints[(string) $question['uuid']] = (string) $question['markers']['a1']['verb_hint'];
            }
        }

        $this->assertSame($expected, $actual);
        $neutralHint = 'Дайте коротку відповідь, що узгоджується з наведеним фактом; використайте had у потрібній формі.';
        $this->assertSame(array_fill_keys(array_keys($expected), $neutralHint), $hints);
        foreach ($hints as $uuid => $hint) {
            $this->assertDoesNotMatchRegularExpression(
                '/ствердн|заперечн|\b(?:Yes|No)\b/iu',
                $hint,
                "Short-answer hint leaks answer polarity in {$uuid}"
            );
        }
    }

    public function test_reviewed_never_contexts_make_the_time_expression_deducible(): void
    {
        $expected = [
            'pp-time-expressions-v3-a2-06' => 'At that concert, Eva heard live music for the first time; she had {a1} heard it live before.',
            'pp-time-expressions-v3-b1-06' => 'The assistant had previously worked only with public records; she had {a1} handled confidential records before that audit.',
            'pp-time-expressions-v3-b2-06' => 'The analyst had previously examined only corporate restructurings; he had {a1} examined a sovereign-debt restructuring before that case.',
        ];
        $questions = array_column($this->definition('time-expressions')['questions'], null, 'uuid');

        foreach ($expected as $uuid => $stem) {
            $this->assertSame($stem, $questions[$uuid]['question']);
            $this->assertSame([$stem], $questions[$uuid]['variants']);
            $this->assertSame('never', $questions[$uuid]['markers']['a1']['answer']);
        }
    }

    public function test_reviewed_questions_and_negatives_have_finite_past_anchors(): void
    {
        $questions = array_column($this->definition('questions')['questions'], null, 'uuid');
        $this->assertSame(
            '{a1} for breakfast before the school bus arrived?',
            $questions['pp-questions-v3-a1-03']['question']
        );
        $this->assertSame(
            'How many pages {a1} before she went to bed?',
            $questions['pp-questions-v3-a2-09']['question']
        );
        $this->assertSame(
            'Where {a1} the spare key before the family left?',
            $questions['pp-questions-v3-a2-11']['question']
        );
        $this->assertSame(
            'Why {a1} access before the audit began?',
            $questions['pp-questions-v3-b1-04']['question']
        );
        $this->assertSame(
            'How many rounds of revision {a1} before the editor accepted the article?',
            $questions['pp-questions-v3-b2-07']['question']
        );

        $negatives = array_column($this->definition('negatives')['questions'], null, 'uuid');
        $this->assertSame(
            'Counsel {a1} anyone cite that precedent until the appeal.',
            $negatives['pp-negatives-v3-c1-09']['question']
        );
        $this->assertSame(
            'The envoy {a1} the hearing room when the hearing began.',
            $negatives['pp-negatives-v3-c2-12']['question']
        );

        $negativeStems = implode("\n", array_column($negatives, 'question'));
        $this->assertDoesNotMatchRegularExpression(
            '/\bby (?:Friday|Monday|noon|the deadline|six o’clock|five o’clock|midnight)\./u',
            $negativeStems,
            'A bare deadline does not establish a finite past anchor.'
        );
    }
}
