<?php

namespace App\Services;

/** Explicit local-only boundary for the Present Simple Passive revision. */
final class PresentSimplePassiveQualityLocalGuard extends TheoryLocalReleaseGuard
{
    protected const LABEL = 'Present Simple Passive quality';
    protected const PRIVATE_DIRECTORY = 'present-simple-passive-quality';
    protected const ENDPOINT = 'psp-quality-';

    public static function scope(): array
    {
        return [['identity' => 'Database\\Seeders\\Page_V3\\PassiveVoice\\Tenses\\PassiveVoicePresentSimpleTheorySeeder',
            'locale' => 'uk', 'path' => '/theory/passive-voice/theory-passive-voice-present-simple']];
    }
}
