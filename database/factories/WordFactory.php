<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Word>
 */
class WordFactory extends Factory
{
    protected $model = \App\Models\Word::class;

    public function definition(): array
    {
        return [
            'word' => $this->faker->unique()->word(),
        ];
    }
}
