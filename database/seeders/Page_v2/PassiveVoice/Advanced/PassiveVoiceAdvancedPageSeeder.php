<?php

namespace Database\Seeders\Page_v2\PassiveVoice\Advanced;

use Database\Seeders\Pages\Concerns\GrammarPageSeeder;

abstract class PassiveVoiceAdvancedPageSeeder extends GrammarPageSeeder
{
    protected function category(): array
    {
        return [
            'slug' => 'passive-voice-advanced',
            'title' => 'Просунутий рівень — Складні конструкції',
            'language' => 'uk',
        ];
    }
}
