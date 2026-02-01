<?php

namespace Database\Seeders\Page_v2\PassiveVoiceV2\Basics;

use Database\Seeders\Pages\Concerns\GrammarPageSeeder;

abstract class PassiveVoiceV2BasicsPageSeeder extends GrammarPageSeeder
{
    protected function category(): array
    {
        return [
            'slug' => 'passive-voice',
            'title' => 'Пасивний стан',
            'language' => 'uk',
        ];
    }
}
