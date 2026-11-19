<?php

namespace App\Services;

/** Explicit local-only boundary for the Past Simple Passive revision. */
final class PastSimplePassiveQualityLocalGuard extends TheoryLocalReleaseGuard
{
    protected const LABEL = 'Past Simple Passive quality';
    protected const PRIVATE_DIRECTORY = 'past-simple-passive-quality';
    protected const ENDPOINT = 'past-passive-quality-';

    public static function scope(): array
    {
        return [['identity' => 'Database\\Seeders\\Page_V3\\PassiveVoice\\Tenses\\PassiveVoicePastSimpleTheorySeeder',
            'locale' => 'uk', 'path' => '/theory/passive-voice/theory-passive-voice-past-simple']];
    }
}
