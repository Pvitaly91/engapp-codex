<?php
declare(strict_types=1);

namespace Tests\Unit;

use App\Support\FiniteBuilderWordSlots;
use App\Support\FinitePracticeQualityCatalog;
use App\Support\PassivePracticeQuality;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class FiniteBuilderWordSlotsTest extends TestCase
{
    public function test_every_registered_finite_builder_uses_real_words_and_reviewed_complete_sentence_paths(): void
    {
        $catalogs = (new ReflectionClass(PassivePracticeQuality::class))->getConstant('CATALOGS');
        $counts = ['catalogs' => 0, 'banks' => 0, 'questions' => 0, 'slots' => 0];
        foreach ($catalogs as $catalog) {
            if (!is_subclass_of($catalog, FinitePracticeQualityCatalog::class)) { continue; }
            ++$counts['catalogs'];
            foreach (array_keys($catalog::TOPICS) as $topic) {
                ++$counts['banks']; $authors = array_column($catalog::source($topic)['questions'], null, 'id');
                $choicesSeen = [];
                foreach ($catalog::compile($topic, 'builder')['questions'] as $row) {
                    ++$counts['questions']; $author = $authors[$row['author_id']];
                    $words = FiniteBuilderWordSlots::words($row['target_text']);
                    self::assertSame(1, $row['builder_word_slots_version'], $row['uuid']);
                    self::assertSame($words, array_column($row['markers'], 'answer'));
                    self::assertSame($words, $row['tokens_correct']);
                    self::assertSame($author['question'], $row['question']);
                    self::assertSame($author['groups'], $row['reorder_groups']);
                    self::assertSame($author['groups'], $row['token_groups']);
                    self::assertSame(array_values(array_unique(array_merge([$author['target']], $author['accepted_targets'] ?? []))), $row['accepted_target_texts']);
                    foreach ($row['markers'] as $key => $marker) {
                        ++$counts['slots']; $choices = $row['options_by_marker'][$key];
                        self::assertDoesNotMatchRegularExpression('/\s/u', $marker['answer']);
                        self::assertSame($choices, $marker['options']);
                        self::assertCount(4, $choices);
                        self::assertCount(4, array_unique(array_map('mb_strtolower', $choices)));
                        self::assertContains(FiniteBuilderWordSlots::clean($marker['answer']), $choices);
                        foreach ($choices as $choice) {
                            self::assertSame($choice, FiniteBuilderWordSlots::clean($choice));
                            self::assertDoesNotMatchRegularExpression('/\s|[{}<>]/u', $choice);
                        }
                        $choicesSeen[json_encode($choices)] = true;
                    }
                    $payload = PassivePracticeQuality::decorate(['uuid' => $row['uuid'], 'question' => $row['question'],
                        'type' => '4', 'answer_map' => array_map(static fn ($marker) => $marker['answer'], $row['markers'])], $row);
                    self::assertSame($words, $payload['finite_word_slots']['answers']);
                    self::assertSame($row['uuid'], $payload['finite_word_slots']['uuid']);
                    self::assertSame($row['accepted_target_texts'], $payload['finite_word_slots']['sentences']);
                    self::assertNotEmpty($payload['finite_word_slots']['previous_versions']);
                }
                self::assertGreaterThan(12, count($choicesSeen), $catalog.'/'.$topic.' must not repeat one distractor bank.');
                foreach ($catalog::compile($topic, 'standard')['questions'] as $row) {
                    self::assertArrayNotHasKey('builder_word_slots_version', $row, 'Gap rows are outside this projection.');
                }
            }
        }
        self::assertSame(['catalogs' => 25, 'banks' => 193, 'questions' => 2652, 'slots' => 23351], $counts);
    }

    public function test_word_policy_is_exact_bound_and_does_not_enable_unregistered_or_changed_payloads(): void
    {
        $row = \App\Support\PassiveVoicePracticeQuality::compile('present-perfect', 'builder')['questions'][0];
        $payload = ['uuid' => $row['uuid'], 'question' => $row['question'], 'type' => '4', 'answers' => array_column($row['markers'], 'answer'),
            'psp_content_revision' => hash('sha256', json_encode($row, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES))];
        self::assertArrayHasKey('finite_word_slots', FiniteBuilderWordSlots::decorate($payload, $row));
        foreach ([['uuid' => 'unknown'], ['question' => 'Changed'], ['answers' => ['a grouped phrase']], ['psp_content_revision' => 'stale']] as $change) {
            self::assertArrayNotHasKey('finite_word_slots', FiniteBuilderWordSlots::decorate(array_replace($payload, $change), $row));
        }
        unset($row['builder_word_slots_version']);
        self::assertArrayNotHasKey('finite_word_slots', FiniteBuilderWordSlots::decorate($payload, $row));
    }

    public function test_auxiliaries_and_determiners_use_short_grammatical_family_distractors(): void
    {
        $rows = \App\Support\PassiveVoicePracticeQuality::compile('present-perfect', 'builder')['questions'];
        foreach ($rows as $row) foreach ($row['markers'] as $marker) {
            $answer = mb_strtolower(FiniteBuilderWordSlots::clean($marker['answer']));
            if ($answer === 'has' || $answer === 'have') {
                foreach ($marker['options'] as $option) {
                    self::assertMatchesRegularExpression('/^(?:have|has|had|haven\x{0027}t|hasn\x{0027}t|hadn\x{0027}t|do|does|did|don\x{0027}t|doesn\x{0027}t|didn\x{0027}t)$/iu', $option);
                }
            }
        }
    }

    public function test_beginner_choices_do_not_fall_back_to_advanced_cross_part_of_speech_words(): void
    {
        $rows = \App\Support\WordOrderPracticeQuality::compile('basic', 'builder')['questions'];
        $row = array_column($rows, null, 'author_id')['A1-c01'];
        self::assertSame(['Ben', 'has', 'a', 'red', 'bike.'], array_column($row['markers'], 'answer'));
        foreach ($row['options_by_marker']['a1'] as $option) {
            self::assertMatchesRegularExpression('/^(?:Ben|Anna|Leo|Luca|Emma|Max|Mia|Nora|Sam|Tom|Alex|Olena|Mark|Iryna|Olia|Oleh|Danylo|Sofia|Marta|Taras|Nina|Lena|Ivan|Maria|Petro|Alice|John|Mary|Jane|Paul|Kate|Iva)$/D', $option);
        }
        foreach ($row['options_by_marker']['a4'] as $option) {
            self::assertMatchesRegularExpression('/^(?:red|blue|green|black|white|yellow|silver|golden|brown|pink|grey|gray|purple|orange)$/D', $option);
        }
        foreach ($row['options_by_marker']['a5'] as $option) {
            self::assertMatchesRegularExpression('/^(?:bike|car|bus|train|tram|taxi|van|bicycle|plane|boat|ship)$/D', $option);
        }
    }

    public function test_irregular_verbs_do_not_fall_through_to_noun_choices(): void
    {
        $class = new ReflectionClass(FiniteBuilderWordSlots::class);
        $classify = $class->getMethod('lexicalRole');
        foreach ($class->getConstant('IRREGULAR_PAST') as $word) {
            self::assertSame('past', $classify->invoke(null, $word, 0, [$word], 'past_reporting'), $word);
        }
        foreach ($class->getConstant('IRREGULAR_PARTICIPLES') as $word) {
            self::assertSame('participle', $classify->invoke(null, $word, 1, ['has', $word], null), $word);
        }
        self::assertSame('verb', $classify->invoke(null, 'read', 0, ['read'], null));
        foreach ([
            [\App\Support\ReportedSpeechPracticeQuality::class, 'say-tell-ask', 'rsq-saytell-a1-c01', 'told'],
            [\App\Support\ReportedSpeechPracticeQuality::class, 'reported-commands-and-requests', 'rsq-commands-a2-c02', 'told'],
            [\App\Support\ReportedSpeechPracticeQuality::class, 'reported-questions', 'rsq-questions-b1-c01', 'kept'],
            [\App\Support\WordOrderPracticeQuality::class, 'verbs-objects', 'woq-verbs-objects-b1-c01', 'put'],
            [\App\Support\WordOrderPracticeQuality::class, 'common-mistakes', 'woq-common-mistakes-a2-c01', 'paid'],
        ] as [$catalog, $topic, $uuid, $word]) {
            $row = array_column($catalog::compile($topic, 'builder')['questions'], null, 'uuid')[$uuid];
            $found = false;
            foreach ($row['markers'] as $marker) {
                if (mb_strtolower(FiniteBuilderWordSlots::clean($marker['answer'])) !== $word) { continue; }
                $found = true;
                foreach ($marker['options'] as $option) {
                    self::assertContains($classify->invoke(null, $option, 0, [$option], null), ['past', 'participle'], $uuid.'/'.$option);
                }
            }
            self::assertTrue($found, $uuid);
        }
    }
}
