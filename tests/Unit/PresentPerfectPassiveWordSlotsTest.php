<?php
declare(strict_types=1);

namespace Tests\Unit;

use App\Support\PassiveVoicePracticeQuality as Quality;
use App\Support\PresentPerfectPassiveWordSlots;
use PHPUnit\Framework\TestCase;

/** File-only regression for the screenshot: no kernel, local DB or learner writes. */
final class PresentPerfectPassiveWordSlotsTest extends TestCase
{
    public function test_all_eighteen_builder_prompts_are_only_the_ukrainian_sentence(): void
    {
        $source = Quality::source('present-perfect');
        $rows = array_values(array_filter($source['questions'], static fn ($row) => $row['kind'] === 'compose'));
        self::assertCount(18, $rows);
        foreach ($rows as $row) {
            self::assertSame($row['uk'], $row['question'], $row['id']);
            self::assertDoesNotMatchRegularExpression('/Використай|Уведи|Напиши|Почни|Збережи|Склади|пасив|[A-Za-z]|<[^>]*>|\{a\d+\}/u', $row['question'], $row['id']);
        }
        self::assertSame('Методологічні обмеження вже визнали в анотації.', array_column($rows, 'question', 'id')['B2-c02']);
    }

    public function test_each_canonical_builder_word_has_its_own_marker_and_short_varied_choices(): void
    {
        $source = Quality::source('present-perfect');
        $authors = array_column($source['questions'], null, 'id');
        $definition = Quality::compile('present-perfect', 'builder');
        $choiceSets = [];
        self::assertCount(18, $definition['questions']);
        foreach ($definition['questions'] as $row) {
            $author = $authors[$row['author_id']];
            $words = preg_split('/\s+/u', $author['target']);
            self::assertSame($words, array_column($row['markers'], 'answer'), $row['author_id']);
            self::assertSame($words, $row['tokens_correct'], $row['author_id']);
            self::assertSame($author['question'], $row['source_text_uk']);
            foreach ($row['markers'] as $marker => $data) {
                self::assertDoesNotMatchRegularExpression('/\s/u', $data['answer'], $row['author_id'].'/'.$marker);
                $options = $row['options_by_marker'][$marker];
                self::assertCount(4, $options);
                self::assertCount(4, array_unique($options));
                self::assertContains(\App\Support\FiniteBuilderWordSlots::clean($data['answer']), $options);
                foreach ($options as $option) {
                    self::assertDoesNotMatchRegularExpression('/\s|[{}<>]/u', $option, $row['author_id'].'/'.$marker);
                }
                $choiceSets[json_encode($options)] = true;
            }
        }
        self::assertGreaterThan(60, count($choiceSets), 'The bank must not recycle one distractor list.');
        $reported = array_column($definition['questions'], null, 'author_id')['B2-c02'];
        self::assertSame(['The', 'methodological', 'limitations', 'have', 'been', 'acknowledged', 'in', 'the', 'abstract.'], array_column($reported['markers'], 'answer'));
    }

    public function test_all_six_levels_keep_all_fifty_four_distinct_topic_questions(): void
    {
        $source = Quality::source('present-perfect');
        self::assertCount(54, $source['questions']);
        self::assertCount(54, array_unique(array_column($source['questions'], 'id')));
        self::assertCount(54, array_unique(array_column($source['questions'], 'target')));
        foreach (Quality::LEVELS as $level) {
            $rows = array_values(array_filter($source['questions'], static fn ($row) => $row['level'] === $level));
            self::assertCount(6, array_filter($rows, static fn ($row) => $row['kind'] === 'gap'));
            self::assertCount(3, array_filter($rows, static fn ($row) => $row['kind'] === 'compose'));
        }
    }

    public function test_word_path_policy_requires_exact_current_educational_source(): void
    {
        foreach (Quality::compile('present-perfect', 'builder')['questions'] as $row) {
            $payload = ['uuid' => $row['uuid'], 'question' => $row['question'], 'type' => '4',
                'answers' => array_column($row['markers'], 'answer'),
                'psp_content_revision' => hash('sha256', json_encode($row, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES))];
            $decorated = PresentPerfectPassiveWordSlots::decorate($payload, $row);
            self::assertSame($payload['psp_content_revision'], $decorated['pvq_word_slots']['revision']);
            self::assertSame($row['accepted_target_texts'], $decorated['pvq_word_slots']['sentences']);
            self::assertSame($payload['answers'], $decorated['pvq_word_slots']['answers']);
            self::assertCount(4, $decorated['pvq_word_slots']['previous']['answers']);
            self::assertStringContainsString('Використай теперішній доконаний пасив.', $decorated['pvq_word_slots']['previous']['question']);
            foreach ([['uuid' => 'other-topic'], ['question' => 'Змінена умова.'], ['answers' => ['old grouped phrase']], ['psp_content_revision' => 'stale']] as $patch) {
                self::assertArrayNotHasKey('pvq_word_slots', PresentPerfectPassiveWordSlots::decorate(array_replace($payload, $patch), $row));
            }
        }
    }
}
