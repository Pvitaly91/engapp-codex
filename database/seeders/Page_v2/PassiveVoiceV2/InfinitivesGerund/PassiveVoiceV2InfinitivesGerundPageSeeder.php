<?php

namespace Database\Seeders\Page_v2\PassiveVoiceV2\InfinitivesGerund;

use Database\Seeders\Pages\Concerns\GrammarPageSeeder;

abstract class PassiveVoiceV2InfinitivesGerundPageSeeder extends GrammarPageSeeder
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
