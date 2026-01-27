<?php

namespace Database\Seeders\Pages\PassiveVoice;

use Database\Seeders\Pages\Concerns\GrammarPageSeeder;

abstract class PassiveVoicePageSeeder extends GrammarPageSeeder
{
    protected function category(): array
    {
        return [
            'slug' => 'pasyvnyi-stan',
            'title' => 'Пасивний стан (Passive Voice)',
            'language' => 'uk',
        ];
    }
}
