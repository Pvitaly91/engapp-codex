<?php

namespace Tests\Unit;

use App\Support\AcceptedAnswerVariants;
use PHPUnit\Framework\TestCase;

class AcceptedAnswerVariantsTest extends TestCase
{
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
