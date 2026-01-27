<?php

namespace Database\Seeders\Page_v2\PassiveVoice\TypicalConstructions;

use Database\Seeders\Pages\Concerns\GrammarPageSeeder;

abstract class PassiveVoiceTypicalConstructionsPageSeeder extends GrammarPageSeeder
{
    protected function category(): array
    {
        return [
            'slug' => 'passive-voice-typical-constructions',
            'title' => 'Типові конструкції й "фішки"',
            'language' => 'uk',
        ];
    }
}
