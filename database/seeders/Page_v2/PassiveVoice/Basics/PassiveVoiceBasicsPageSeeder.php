<?php

namespace Database\Seeders\Page_v2\PassiveVoice\Basics;

use Database\Seeders\Pages\Concerns\GrammarPageSeeder;

abstract class PassiveVoiceBasicsPageSeeder extends GrammarPageSeeder
{
    protected function category(): array
    {
        return [
            'slug' => 'passive-voice-basics',
            'title' => 'База — Основи пасивного стану',
            'language' => 'uk',
        ];
    }
}
