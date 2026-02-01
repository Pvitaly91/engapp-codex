<?php

namespace Database\Seeders\Page_v2\PassiveVoiceV2\Tenses;

use Database\Seeders\Pages\Concerns\GrammarPageSeeder;

abstract class PassiveVoiceV2TensesPageSeeder extends GrammarPageSeeder
{
    protected function category(): array
    {
        return [
            'slug' => 'passive-voice-v2-tenses',
            'title' => 'Пасив у різних часах',
            'language' => 'uk',
        ];
    }
}
