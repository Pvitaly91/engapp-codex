<?php

namespace Database\Seeders\Page_v2\PassiveVoice\Basics;

use Database\Seeders\Page_v2\Concerns\GrammarPageSeeder;

abstract class PassiveVoiceBasicsPageSeeder extends GrammarPageSeeder
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
