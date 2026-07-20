<?php

namespace Tests\Unit;

use App\Support\AcceptedAnswerVariants;
use PHPUnit\Framework\TestCase;

class AcceptedAnswerVariantsTest extends TestCase
{
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

        $this->assertSame(
            ['THEY HAVE NOT LEFT!', "THEY haven't LEFT!"],
            AcceptedAnswerVariants::for('THEY HAVE NOT LEFT!')
        );
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
