<?php

namespace Database\Factories;

use App\Models\Word;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Translate>
 */
class TranslateFactory extends Factory
{
    protected $model = \App\Models\Translate::class;

    public function definition(): array
    {
        return [
            'word_id' => Word::factory(),
            'lang' => 'uk',
            'translation' => $this->faker->unique()->word(),
        ];
    }
}
