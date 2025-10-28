<?php

namespace Database\Seeders\Pages\Demonstratives;

use Database\Seeders\Pages\Concerns\GrammarPageSeeder;

abstract class DemonstrativePageSeeder extends GrammarPageSeeder
{
    protected function category(): array
    {
        return [
            'slug' => 'demonstratives',
            'title' => 'Вказівні займенники',
            'language' => 'uk',
        ];
    }
}
