<?php

namespace Database\Seeders\Pages\Structures;

use Database\Seeders\Pages\Concerns\GrammarPageSeeder;

abstract class StructurePageSeeder extends GrammarPageSeeder
{
    protected function category(): array
    {
        return [
            'slug' => 'sentence-structures',
            'title' => 'Мовні конструкції',
            'language' => 'uk',
        ];
    }
}
