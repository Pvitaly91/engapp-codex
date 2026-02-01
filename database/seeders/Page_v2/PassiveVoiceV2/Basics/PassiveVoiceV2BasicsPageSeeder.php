<?php

namespace Database\Seeders\Page_v2\PassiveVoiceV2\Basics;

use Database\Seeders\Pages\Concerns\GrammarPageSeeder;

abstract class PassiveVoiceV2BasicsPageSeeder extends GrammarPageSeeder
{
    protected function category(): array
    {
        return [
            'slug' => 'passive-voice-v2',
            'title' => 'Пасивний стан V2',
            'language' => 'uk',
        ];
    }
}
