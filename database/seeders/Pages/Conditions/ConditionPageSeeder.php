<?php

namespace Database\Seeders\Pages\Conditions;

use Database\Seeders\Pages\Concerns\GrammarPageSeeder;

abstract class ConditionPageSeeder extends GrammarPageSeeder
{
    protected function category(): array
    {
        return [
            'slug' => 'conditions',
            'title' => 'Умовні речення',
            'language' => 'uk',
        ];
    }
}
