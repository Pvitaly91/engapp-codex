<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Question;

class QuestionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $questions = [
            [
                'question' => 'Walter __________ to school last week.',
                'correct_answer' => 'went',
                'options' => json_encode(['go', 'goes', 'will go', 'went', 'gone']),
                'tense' => 'past'
            ],
            [
                'question' => 'Why do you always __________ in front of the TV?',
                'correct_answer' => 'sleep',
                'options' => json_encode(['slept', 'sleep', 'sleeps', 'will sleep', 'sleeping']),
                'tense' => 'present'
            ],
            // Додайте інші питання аналогічно...
        ];

        foreach ($questions as $q) {
            Question::create($q);
        }
    }
}
