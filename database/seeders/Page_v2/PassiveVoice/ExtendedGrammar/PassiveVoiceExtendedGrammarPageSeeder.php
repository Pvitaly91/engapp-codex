<?php

namespace Database\Seeders\Page_v2\PassiveVoice\ExtendedGrammar;

use Database\Seeders\Pages\Concerns\GrammarPageSeeder;

abstract class PassiveVoiceExtendedGrammarPageSeeder extends GrammarPageSeeder
{
    protected function category(): array
    {
        return [
            'slug' => 'passive-voice-extended-grammar',
            'title' => 'Розширення граматики — Пасив у всіх часах',
            'language' => 'uk',
        ];
    }
}
