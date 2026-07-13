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
}
