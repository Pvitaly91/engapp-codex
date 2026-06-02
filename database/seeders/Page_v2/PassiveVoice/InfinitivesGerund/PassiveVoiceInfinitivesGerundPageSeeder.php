<?php

namespace Database\Seeders\Page_v2\PassiveVoice\InfinitivesGerund;

use Database\Seeders\Page_v2\Concerns\GrammarPageSeeder;

abstract class PassiveVoiceInfinitivesGerundPageSeeder extends GrammarPageSeeder
{
    protected function category(): array
    {
        return [
            'slug' => 'passive-voice-infinitives-gerund',
            'title' => 'Інфінітив та герундій у пасиві',
            'language' => 'uk',
        ];
    }
}
