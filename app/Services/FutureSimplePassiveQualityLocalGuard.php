<?php

namespace App\Services;

/** Explicit local-only boundary for the Future Simple Passive revision. */
final class FutureSimplePassiveQualityLocalGuard extends TheoryLocalReleaseGuard
{
    protected const LABEL = 'Future Simple Passive quality';
    protected const PRIVATE_DIRECTORY = 'future-simple-passive-quality';
    protected const ENDPOINT = 'future-passive-quality-';

    public static function scope(): array
    {
        return [['identity' => 'Database\\Seeders\\Page_V3\\PassiveVoice\\Tenses\\PassiveVoiceFutureSimpleTheorySeeder',
            'locale' => 'uk', 'path' => '/theory/passive-voice/passive-voice-tenses/theory-passive-voice-future-simple']];
    }
}
