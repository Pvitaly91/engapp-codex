<?php

namespace Tests\Unit;

use App\Support\AcceptedAnswerVariants;
use PHPUnit\Framework\TestCase;

class AcceptedAnswerVariantsTest extends TestCase
{
    public function test_all24_zero_article_dash_answers_accept_the_literal_semantic_value_only(): void
    {
        $source = json_decode(file_get_contents(dirname(__DIR__, 2).'/database/seeders/V3/CommonMistakes/CommonMistakesArticlesAllLevelsV3Seeder/definition.json'), true, flags: JSON_THROW_ON_ERROR);
        $rows = array_values(array_filter($source['questions'], static fn ($row): bool => ($row['markers']['a1']['answer'] ?? null) === '-'));
        $this->assertCount(24, $rows);
        foreach ($rows as $row) {
            $this->assertTrue(AcceptedAnswerVariants::matches('-', '-'), $row['uuid']);
            $this->assertTrue(AcceptedAnswerVariants::matches('-', ' - '), $row['uuid']);
            foreach (['', '.', '?!', '—', '–', 'the', 'a', 'an'] as $wrong) {
                $this->assertFalse(AcceptedAnswerVariants::matches('-', $wrong), $row['uuid'].' / '.$wrong);
            }
        }
    }

    public function test_optional_sentence_punctuation_does_not_change_spelling_or_complete_a_partial_phrase(): void
    {
        foreach ([['Tomorrow,', 'Tomorrow'], ['“Tomorrow,” she said.', 'Tomorrow she said'],
            ['(Wait!)', 'wait'], ['first; then: stop!', 'first then stop'], ['red,blue', 'red blue'],
            ['will be made.', 'will be made'], ['will not be made!', "won't be made"],
            ["'Tomorrow,' we will leave.", 'Tomorrow we will leave'], ["'go' and 'stop'", 'go and stop'],
            ["'James' bike'", "James' bike"], ["Students’ books", "Students' books"],
            ["James' bike.", "“James’ bike.”"],
            ["John's well-known 3.14 example…", "John’s well-known 3.14 example"]] as [$expected, $answer]) {
            $this->assertTrue(AcceptedAnswerVariants::matches($expected, $answer), $expected.' -> '.$answer);
        }
        foreach ([['will be made', 'will'], ['will be made', 'will be'], ['will be made', 'will made'],
            ["won't be made", 'wont be made'], ["case's", 'cases'], ['well-known', 'well known'],
            ["students' books", 'students books'], ["James' bike", 'James bike'],
            ["students'", 'students'], ["James' bike", "'James' bike"], ["'90s music", '90s music'],
            ['3.14', '314'], ['1,000', '1000'], ['a.b', 'ab']] as [$expected, $answer]) {
            $this->assertFalse(AcceptedAnswerVariants::matches($expected, $answer), $expected.' -> '.$answer);
        }
    }

    public function test_sentence_ending_punctuation_does_not_license_contracted_positive_short_answers(): void
    {
        foreach (['', '.', '?', '!', '…', '. ”'] as $tail) {
            foreach ([['I am', "I'm"], ['they are', "they're"], ['she is', "she's"], ['we have', "we've"],
                ['I will', "I'll"], ['he would', "he'd"], ['it had', "it'd"]] as [$full, $short]) {
                $this->assertTrue(AcceptedAnswerVariants::matches($full, $full, $tail, 'Yes, '));
                $this->assertFalse(AcceptedAnswerVariants::matches($full, $short, $tail, 'Yes, '), $full.' / '.$tail);
            }
            foreach ([['it is not', "it isn't"], ['it is not', "it's not"], ['they are not', "they aren't"],
                ['we are not', "we're not"], ['I am not', "I'm not"], ['he was not', "he wasn't"],
                ['you were not', "you weren't"], ['I will not', "I won't"]] as [$full, $short]) {
                $this->assertTrue(AcceptedAnswerVariants::matches($full, $short, $tail, 'No, '), $full.' / '.$tail);
            }
        }
        $this->assertTrue(AcceptedAnswerVariants::matches('they are', "they're", ' ready.', 'Yes, '));
        $this->assertTrue(AcceptedAnswerVariants::matches('I am', "I'm", ' at home.', 'Yes, '));
    }

    public function test_every_shared_unambiguous_rule_works_in_both_directions(): void
    {
        $rules = AcceptedAnswerVariants::rules();
        foreach (array_merge($rules['negative'], $rules['positive']) as $short => $full) {
            $this->assertTrue(AcceptedAnswerVariants::matches($short.' work.', $full.' work'), $short);
            $this->assertTrue(AcceptedAnswerVariants::matches($full.' work.', str_replace("'", '’', $short).' work'), $full);
            $this->assertFalse(AcceptedAnswerVariants::matches($short.' work', 'different work'), $short);
        }
    }

    public function test_context_resolves_ambiguous_forms_and_preserves_negative_question_order(): void
    {
        foreach ([
            ["She's", 'She is', 'a singer'], ["he's", 'he has', 'been working'],
            ["You'd", 'You had', 'better hurry'], ["I'd", 'I would', 'like to go'],
            ['she has finished', "she's finished", ''], ["can't", 'can not', 'swim'],
            ["Don't you know?", 'Do you not know?', ''], ['Do you not know?', "Don't you know?", ''],
            ["Wouldn't a narrower reading be safer?", 'Would a narrower reading not be safer?', ''],
            ["Doesn't Mark know her?", 'Does Mark not know her?', ''],
            ["Isn't the shop open?", 'Is the shop not open?', ''],
            ["Aren't I right?", 'Am I not right?', ''], ['Am I not right?', "Aren't I right?", ''],
        ] as [$expected, $answer, $after]) {
            $this->assertTrue(AcceptedAnswerVariants::matches($expected, $answer, $after), $expected.' -> '.$answer);
        }
        foreach ([
            ["can't", 'can', ''], ["don't", 'do', ''], ["can't", 'cant', ''],
            ["he's", 'he has', 'a singer'], ["he's", 'he is', 'been working'],
            ["you'd", 'you would', 'better go'], ['she has finished', 'she is finished', ''],
            ["Don't you know?", 'Do not you know?', ''], ['Yes, I am.', "Yes, I'm.", ''],
            ['He has a car', "He's a car", ''], ['He had a car', "He'd a car", ''],
            ["Isn't the shop open?", 'Is not the shop open?', ''],
        ] as [$expected, $answer, $after]) {
            $this->assertFalse(AcceptedAnswerVariants::matches($expected, $answer, $after), $expected.' -> '.$answer);
        }
        $this->assertFalse(AcceptedAnswerVariants::matches('I am', "I'm", '', 'Yes, '));
    }

    public function test_it_accepts_contracted_uncontracted_and_curly_apostrophe_future_negatives(): void
    {
        $this->assertSame(
            ["won't call", 'will not call'],
            AcceptedAnswerVariants::for('won’t call')
        );

        $this->assertSame(
            ['will not travel', "won't travel"],
            AcceptedAnswerVariants::for('  will   not travel  ')
        );
    }

    public function test_it_accepts_contracted_and_uncontracted_present_perfect_negatives(): void
    {
        $this->assertSame(
            ["haven't finished", 'have not finished'],
            AcceptedAnswerVariants::for('haven’t finished')
        );

        $this->assertSame(
            ['have not arrived', "haven't arrived"],
            AcceptedAnswerVariants::for('  have   not arrived  ')
        );

        $this->assertSame(
            ["hasn't called", 'has not called'],
            AcceptedAnswerVariants::for('hasnʼt called')
        );

        $this->assertSame(
            ['has not changed', "hasn't changed"],
            AcceptedAnswerVariants::for('has not changed')
        );
    }

    public function test_present_perfect_variants_keep_surrounding_case_and_punctuation(): void
    {
        $this->assertSame(
            ["She hasn't finished.", 'She has not finished.'],
            AcceptedAnswerVariants::for('She hasn’t finished.')
        );

        $variants = AcceptedAnswerVariants::for('THEY HAVE NOT LEFT!');
        $this->assertContains('THEY HAVE NOT LEFT!', $variants);
        $this->assertContains("THEY haven't LEFT!", $variants);
        $this->assertContains("they've NOT LEFT!", $variants);
    }

    public function test_it_accepts_contracted_and_uncontracted_past_perfect_negatives(): void
    {
        $this->assertSame(
            ["hadn't finished", 'had not finished'],
            AcceptedAnswerVariants::for('hadn’t finished')
        );

        $this->assertSame(
            ['had not arrived', "hadn't arrived"],
            AcceptedAnswerVariants::for('  had   not arrived  ')
        );

        $this->assertSame(
            ["She hadn't called.", 'She had not called.'],
            AcceptedAnswerVariants::for('She hadnʼt called.')
        );
    }
}
