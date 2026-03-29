<?php

namespace Database\Seeders\Page_v2\PassiveVoice\Tenses;

use Database\Seeders\Page_v2\Concerns\GrammarPageSeeder;

abstract class PassiveVoiceTensesPageSeeder extends GrammarPageSeeder
{
    protected function category(): array
    {
        return [
            'slug' => 'passive-voice-tenses',
            'title' => 'Пасив у різних часах',
            'language' => 'uk',
        ];
    }
}
